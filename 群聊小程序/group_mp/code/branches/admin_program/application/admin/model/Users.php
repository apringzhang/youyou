<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/27
 * Time: 10:26
 */

namespace app\admin\model;


class Users
{
    public function getUsersList($nick_name, $is_vip ,$pageNum, $num_per_page)
    {
        $list = db('wx_user')->where(function ($query) use ($nick_name,$is_vip) {
            if (!empty($nick_name)) {
                $query->where('nick_name', 'like', "%{$nick_name}%");
            }
            if (!empty($is_vip)) {
                $query->where('is_vip', $is_vip);
            }
        })
            ->page($pageNum, $num_per_page)
            ->order('create_time desc')
            ->select();
        return $list;
    }

    public function getUsersListCount($nick_name, $is_vip)
    {
        $list = db('wx_user')->where(function ($query) use ($nick_name,$is_vip) {
            if (!empty($nick_name)) {
                $query->where('nick_name', 'like', "%{$nick_name}%");
            }
            if (!empty($is_vip)) {
                $query->where('is_vip', $is_vip);
            }
        })
            ->count();
        return $list;
    }

    public function getGiftList($nick_name, $state ,$activity,$pageNum, $num_per_page)
    {
        $list = db('user_toprize')->where(function ($query) use ($nick_name,$activity,$state) {
            if (!empty($nick_name)) {
                $user_id = db('wx_user')->where('nick_name', 'like', "%{$nick_name}%")->column('id');
                $query->where('user_id', 'in', $user_id);
            }
            if (is_numeric($state)) {
                $query->where('state', $state);
            }
            if(!empty($activity))
            {
                $query->where('ac_id', $activity);
            }
        })
            ->page($pageNum, $num_per_page)
            ->order('create_time desc')
            ->select();
        return $list;
    }

    public function getGiftListCount($nick_name, $state,$activity)
    {
        $list = db('user_toprize')->where(function ($query) use ($nick_name,$state,$activity) {
            if (!empty($nick_name)) {
                $user_id = db('wx_user')->where('nick_name', 'like', "%{$nick_name}%")->column('id');
                $query->where('user_id', 'in', $user_id);
            }
            if (!empty($state)) {
                $query->where('state', $state);
            }
            if(!empty($activity))
            {
                $query->where('ac_id', $activity);
            }
        })
            ->count();
        return $list;
    }

    public function getTorechargeList($nick_name, $type ,$activity,$pageNum, $num_per_page)
    {
        $list = db('user_torecharge')->where(function ($query) use ($nick_name,$activity,$type) {
            if (!empty($nick_name)) {
                $user_id = db('wx_user')->where('nick_name', 'like', "%{$nick_name}%")->column('id');
                $query->where('user_id', 'in', $user_id);
            }
            if (is_numeric($type)) {
                $query->where('type', $type);
            }
            if(!empty($activity))
            {
                $query->where('ac_id', $activity);
            }
        })
            ->page($pageNum, $num_per_page)
            ->order('create_time desc')
            ->select();
        return $list;
    }

    public function getTorechargeListCount($nick_name, $type,$activity)
    {
        $list = db('user_torecharge')->where(function ($query) use ($nick_name,$type,$activity) {
            if (!empty($nick_name)) {
                $user_id = db('wx_user')->where('nick_name', 'like', "%{$nick_name}%")->column('id');
                $query->where('user_id', 'in', $user_id);
            }
            if (!empty($type)) {
                $query->where('type', $type);
            }
            if(!empty($activity))
            {
                $query->where('ac_id', $activity);
            }
        })
            ->count();
        return $list;
    }

}