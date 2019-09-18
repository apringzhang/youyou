<?php

namespace app\api\model;

use think\Db;

class Message {

    /**
     * 通过session_id获取积分
     */
    public function get_message($g_id, $year) {
        $dir = "../groupmessage/".$g_id;
        if (!file_exists($dir)){
            exception('无聊天记录');
        }
        $dir = "../groupmessage/".$g_id."/".$year;
        if (!file_exists($dir)){
            exception('无当前日期聊天记录');
        }
        //1、首先先读取文件夹
        $temp=scandir($dir);
        $array = [];
        //遍历文件夹
        foreach($temp as $v){
            if ($v != '.' && $v != '..') {
                $a=$year.'/'.$v;   
                $sql = Db::connect([
                    // 数据库类型
                    'type'        => 'sqlite',
                    // 数据库名
                    'database'    => $v,
                    'dsn'         => "sqlite:".ROOT_PATH."groupmessage/". $g_id."/".$year."/".$v,
                    'charset'        => 'utf8',
                ]);
                $list = $sql->table('cd_message')->select();  
                $array = array_merge($array, $list);
                
            }
            
        }
        return $array;
    }

    public function collection($uid, $touid,$groups_id) {
        $res = db('user_collection')->where('g_from_userid', $uid)->where('group_id', $groups_id)->where('g_to_userid', $touid)->find();
        if (!$res) {
            $data = [
                'g_from_userid' => $uid,
                'g_to_userid' => $touid,
                'group_id'   => $groups_id,
                'create_time' => time()
            ];
            $result = db('user_collection')->insert($data);

        } else {
            exception('请勿重复收藏该成员');
        }
        
        return $result;
    }

    public function del_collection($uid, $touid, $gid) {
        $res = db('user_collection')
            ->where('g_from_userid', $uid)
            ->where('group_id', $gid)
            ->where('g_to_userid', $touid)->find();
        if (!$res) {
            exception('成员已被删除');
        } else {
            db('user_collection')->where('g_from_userid', $uid)->where('group_id', $gid)->where('g_to_userid', $touid)->delete();
        }
        return $res;
    }

    public function my_collection($uid) {
        $list = db('user_collection')->where('g_from_userid', $uid)->select();
        if ($list) {
            $res = array();
            foreach($list as $key=>$value)
            {
                $user = db('wx_user')
                    ->where('id', $list[$key]['g_to_userid'])
                    ->where('is_delete',0)
                    ->find();
                $groups = db('groups')
                    ->where('id',$list[$key]['group_id'])
                    ->find();
                $res[$key]['id'] = $list[$key]['id'];
                $res[$key]['touid'] = $list[$key]['g_to_userid'];
                $res[$key]['nick_name'] = $user['nick_name'];
                $res[$key]['avatar_url'] = $user['avatar_url'];
                $res[$key]['name'] = $groups['name'];
                $res[$key]['icon'] = config('image_url').'/'.$groups['icon'];
            }
            $result = $res;
        } else {
            $result = [];
        }
        return $result;
    }

    public function prize_list($uid) {
        $list = db('user_toprize')
                ->alias('ut')
                ->field('ut.id, ut.ac_id, ut.pr_id, ut.state, p.name, p.image, p.type')
                ->join('prize p', 'ut.pr_id = p.id')
                ->where('ut.user_id', $uid)
                ->select();

        return $list;
    }

    public function toprize($uid, $id, $address) {
        $info = db('user_toprize')->where('id', $id)->find();
        if ($info['state'] == 0) {
            db('user_toprize')->where('id', $id)->setField('state', 2);
            db('user_toprize')->where('id', $id)->setField('address', $address);
            $result = '兑换成功';
        } else {
            $result = '您已兑换奖品';
        }
        return $result;
    }

    public function add_toprize($uid, $ac_id, $pr_id) {
        $data = [
            'user_id' => $uid,
            'ac_id' => $ac_id,
            'pr_id' => $pr_id,
            'state' => 0,
            'create_time' => time()
        ];

        $result = db('user_toprize')->insert($data);
        

        return $result;
    }
}
