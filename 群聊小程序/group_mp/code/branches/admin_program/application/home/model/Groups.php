<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/24
 * Time: 10:23
 */

namespace app\admin\model;

class Groups
{
    public function getList($name, $admin_name, $pageNum, $num_per_page)
    {
        $list = db('groups')->where(function ($query) use ($name,$admin_name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
            if(!empty($admin_name))
            {
                $user_ids = db('wx_user')
                    ->where('nick_name','like', "%{$admin_name}%")
                    ->where('is_admin',0)
                    ->column('id');
                $query->where('admin_id', 'in', $user_ids);
            }
        })
            ->page($pageNum, $num_per_page)
            ->order('create_time desc')
            ->select();
        foreach ($list as $key =>$value)
        {
            $list[$key]['member_count'] = db('groups_touser')->where('g_id',$list[$key]['id'])->count();
        }
        return $list;
    }

    public function getListCount($name,$admin_name)
    {
        $list = db('groups')->where(function ($query) use ($name,$admin_name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
            if(!empty($admin_name))
            {
                $user_ids = db('wx_user')
                    ->where('nick_name','like', "%{$name}%")
                    ->where('is_admin',0)
                    ->column('id');
                $query->where('admin_id', 'in', $user_ids);
            }
        })
            ->count();
        return $list;
    }


    public function getMemberList($id,$pageNum, $num_per_page)
    {
        $list  = db('groups_touser')
                    ->where('g_id',$id)
                    ->page($pageNum, $num_per_page)
                    ->select();
        foreach($list as $key =>$value)
        {
            $info = db('wx_user')->where('id',$list[$key]['g_userid'])->field('nick_name,avatar_url,gender,country,province,city')->find();
            $list[$key]['user'] = $info;
        }
        return $list;
    }

    public function getMemberCount($id)
    {
        $list  = db('groups_touser')
            ->where('g_id',$id)
            ->count();
        return $list;
    }
}