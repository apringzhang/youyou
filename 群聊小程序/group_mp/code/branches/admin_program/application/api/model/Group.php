<?php

/**
 */

namespace app\api\model;

use GatewayClient\Gateway;

class Group {

    /**
     * 
     */
    public function add_group($data) {
        $res = [
            'name' => $data['name'],
            'admin_id' => $data['uid'],
            'icon' => $data['icon'],
            'notice' => $data['notice'],
            'isaudit' => $data['isaudit'],
            'create_time' => time(),
            'update_time' => time()
        ];

        if(empty(trim($res['name']))) {
            exception('群名不能为空');
        }
        if(strlen($res['name'])>15)
        {
            exception('群名最多为15个字符');
        }

        $groups = db('groups')->insert($res);
            
        if (!$groups) {
            exception('插入数据失败');
        } else {
            $id = db('groups')->getLastInsID();
            $arr = [
                'g_id' => $id,
                'g_userid' => $data['uid'],
                'g_nick_name' => db('wx_user')->where('id', $data['uid'])->value('nick_name')
            ];
            db('groups_touser')->insert($arr);
        }
        return $groups;
    }

    public function group_list($uid) {
        $list = db('groups_touser')
                ->alias('gt')
                ->field('g.*, gt.is_top')
                ->join('groups g', 'gt.g_id = g.id')
                ->where('gt.g_userid', $uid)
                ->order('is_top desc')
                ->select();
        if ($list) {
            foreach ($list as &$value) {
                $value['icon'] = str_replace('\\', '/', $value['icon']);
            } 
        } else {
            $list = [];
        }
        return $list;
        
    }

    public function group_like($title, $uid) {
        $gid = db('groups_touser')->where('g_userid', $uid)->column('g_id');
        if ($gid) {
           $list = db('groups')->where('id', 'in', $gid)->where('name', 'LIKE', "%{$title}%")->select();
            foreach ($list as &$value) {
                $value['icon'] = str_replace('\\', '/', $value['icon']);
            }  
        } else {
            $list = [];
        }
        return $list;
    }

    public function groups_touser($g_id) {
        
        $list = db('groups_touser')
                ->alias('g')
                ->field('g.*, w.avatar_url')
                ->join('wx_user w', 'g.g_userid = w.id')
                ->where('g.g_id', $g_id)
                ->select();

        return $list;
    }

    public function groups_admtouser($g_id) {
        $admin_id = db('groups')->where('id', $g_id)->value('admin_id');
        $list = db('groups_touser')
                ->alias('g')
                ->field('g.*, w.avatar_url')
                ->join('wx_user w', 'g.g_userid = w.id')
                ->where('g.g_userid', 'neq', $admin_id)
                ->where('g.g_id', $g_id)
                ->select();

        return $list;
    }

    public function groups_admtwo($g_id, $like) {
        $admin_id = db('groups')->where('id', $g_id)->value('admin_id');
        $list = db('groups_touser')
                ->alias('g')
                ->field('g.*, w.avatar_url')
                ->join('wx_user w', 'g.g_userid = w.id')
                ->where('g.g_userid', 'neq', $admin_id)
                ->where('g.g_nick_name', 'LIKE', "%{$like}%")
                ->where('g.g_id', $g_id)
                ->select();

        return $list;
    }

    public function delete_groups_touser($g_id, $uid) {
        
        $list = db('groups_touser')->where('g_id', $g_id)->where('g_userid', 'in',  $uid)->delete();
        Gateway::$registerAddress = config('worker_url');
        foreach ($uid as $key => $value) {
            $client_id = Gateway::getClientIdByUid($value);
            if ($client_id) {
                Gateway::leaveGroup($client_id[0], $g_id);
            }
        }
        return $list;
    }

    public function groups_name($g_id, $group_name) {
        $data = [
            'name' => $group_name,
            'update_time' => time(),
        ];
        $result = db('groups')->where('id', $g_id)->update($data);
        
        return $result;
    }

    public function groups_notice($g_id, $notice) {
        $data = [
            'notice' => $notice,
            'update_time' => time(),
        ];
        $result = db('groups')->where('id', $g_id)->update($data);
        
        return $result;
    }

    public function groups_isaudit($g_id, $isaudit) {
        $data = [
            'isaudit' => $isaudit,
            'update_time' => time(),
        ];
        $result = db('groups')->where('id', $g_id)->update($data);
        
        return $result;
    }

    public function groups_invitation($g_id, $like) {
        $list = db('invitation')
                ->alias('i')
                ->field('i.*, w.avatar_url, w.nick_name')
                ->join('wx_user w', 'i.g_to_userid = w.id')
                ->where('w.nick_name', 'LIKE', "%{$like}%")
                ->where('i.g_id', $g_id)
                ->where('i.isaudit', 1)
                ->where('i.is_add', 0)
                ->select();

        return $list;
    }

    public function ratify_group($g_id, $uid) {

        db('invitation')->where('g_id', $g_id)->where('g_to_userid', $uid)->setField('is_add', 1);

        $nickname = db('wx_user')->where('id', $uid)->value('nick_name');
        $data = [
            'g_id' => $g_id,
            'g_userid' => $uid,
            'is_promottone' => 0,
            'is_top' => 0,
            'is_dis_remarks' => 0,
            'g_nick_name' => $nickname
        ];
        $info = db('groups_touser')->insert($data);
        if (!$info) {
            exception('审批失败');
        }
        Gateway::$registerAddress = config('worker_url');
        $client_id = Gateway::getClientIdByUid($uid);
        if ($client_id) {
            Gateway::joinGroup($client_id[0], $g_id);
        }
        return $info;
    }

    public function transfer_group($g_id, $uid, $uidtwo) {
        $info  = self::manage_group($g_id, $uidtwo);
        // dump($uidtwo);
        if ($info != 1) {
            exception('您不是管理员没有转让权限');
        }
        $res = [
            'admin_id' => $uid,
            'update_time' => time(),
        ];
        
        $result = db('groups')->where('id', $g_id)->update($res);
        if (!$result) {
            exception('转让失败');
        }
        return $result;
    }

    public function manage_group($g_id, $uid) {

        if (db('groups')->where('id', $g_id)->where('admin_id', $uid)->find()) {
            $result = 1;
        } else {
            $result = -1;
        }
        return $result;
    }

    public function trouble_group($g_id, $uid, $is_promottone) {
        $result = db('groups_touser')->where('g_id', $g_id)->where('g_userid', $uid)->setField('is_promottone', $is_promottone);
        if (!$result) {
            exception('设置失败');
        }
        return $result;
    }

    public function sticky_group($g_id, $uid, $is_top) {
        $result = db('groups_touser')->where('g_id', $g_id)->where('g_userid', $uid)->setField('is_top', $is_top);
        if (!$result) {
            exception('置顶失败');
        }
        return $result;
    }

    public function remarks_group($g_id, $uid, $is_dis_remarks) {
        $result = db('groups_touser')->where('g_id', $g_id)->where('g_userid', $uid)->setField('is_dis_remarks', $is_dis_remarks);
        if (!$result) {
            exception('置顶失败');
        }
        return $result;
    }

    public function del_group($g_id, $uid) {

        $admin = db('groups')->where('id', $g_id)->value('admin_id');
        if ($admin == $uid) {
            exception('管理员无法删除');
        }
        $list = db('groups_touser')->where('g_id', $g_id)->where('g_userid', $uid)->delete();
        if ($list) {
            Gateway::$registerAddress = config('worker_url');
            $client_id = Gateway::getClientIdByUid($uid);
            if ($client_id) {
                Gateway::leaveGroup($client_id[0], $g_id);
            }
        }
        
        return $list;
    }

    public function cli_group($g_id, $uid) {
        
        $info = db('groups')
            ->alias('g')
            ->field('g.id, g.name, g.icon, g.notice, gt.is_promottone, gt.is_top, gt.is_dis_remarks, gt.g_nick_name, g.isaudit')
            ->join('groups_touser gt', 'gt.g_id = g.id')
            ->where('g.id', $g_id)
            ->where('gt.g_userid', $uid)
            ->find();
        
        return $info;
    }

    public function create_group($g_id, $uid, $fromuid, $reason) {

        $isaudit = db('groups')->where('id', $g_id)->value('isaudit');
        $count = db('groups_touser')->where('g_id',$g_id)->count();
        if (db('groups_touser')->where('g_id', $g_id)->where('g_userid', $uid)->find()) {
            exception('请勿重复进群');
        }
        if($count < 5000)
        {
            if ($isaudit == 1) {
            //需要审核
            $res = [
                'g_id' => $g_id,
                'g_from_userid' => $fromuid,
                'g_to_userid' => $uid,
                'reason' => $reason,
                'isaudit' => 1,
                'is_add' => 0,
                'create_time' => time()
            ];
            db('invitation')->insert($res);
            return 1;
            } else {

                //不需要审核
                $res = [
                    'g_id' => $g_id,
                    'g_userid' => $uid,
                    'is_promottone' => 0,
                    'is_top' => 0,
                    'is_dis_remarks' => 0,
                    'g_nick_name' => db('wx_user')->where('id', $uid)->where('is_delete', 0)->value('nick_name'),
                ];
                
                $groups = db('groups_touser')->insert($res);
                // dump($groups);
                // die;
                if (!$groups) {
                    exception('入群失败');
                }
                $arr = [
                    'g_id' => $g_id,
                    'g_from_userid' => $fromuid,
                    'g_to_userid' => $uid,
                    'reason' => $reason,
                    'isaudit' => 0,
                    'is_add' => 1,
                    'create_time' => time()
                ];

                db('invitation')->insert($arr);
                Gateway::$registerAddress = config('worker_url');
                $client_id = Gateway::getClientIdByUid($uid);

                if ($client_id) {
                    Gateway::joinGroup($client_id[0], $g_id);
                }
                return 2;
            }
        } else {
            exception('该群已满');
        }


        
    }

    public function info_group($g_id, $uid) {
        
        $info = db('wx_user')
            ->where('id', $uid)
            ->find();
        $info['g_nick_name'] = db('groups_touser')->where('g_id', $g_id)->where('g_userid', $uid)->value('g_nick_name');
        if ($info['gender'] == 1) {
            $info['genders'] = '男';
        }
        if ($info['gender'] == 2) {
            $info['genders'] = '女';
        }
        if ($info['gender'] == 3) {
            $info['genders'] = '保密';
        }
        return $info;
    }

    public function mybei_group($g_id, $uid, $username) {
        
        $info = db('groups_touser')
            ->where('g_id', $g_id)
            ->where('g_userid', $uid)
            ->setField('g_nick_name', $username);
        return $info;
    }

    public function and_group($uid, $fuid, $like) {
        
        $one = db('groups_touser')->where('g_userid', $uid)->column('g_id');
        $two = db('groups_touser')->where('g_userid', $fuid)->column('g_id');
        $group = array_intersect($one, $two);
        if ($group) {
            $result = db('groups')->where('id', 'in', $group)->where('name', 'LIKE', "%{$like}%")->select();
        } else {
            $result = [];
        }
        return $result;
    }
}
