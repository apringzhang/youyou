<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/5/23
 * Time: 15:00
 */
namespace app\admin\model;

use think\Validate;

/**
 * 用户模型
 * Class User
 * @package app\admin\model
 */
class Vote
{
    /**
     * 获取列表页
     */
    public function get_list($pageNum, $numPerPage,$sign_id)
    {
        if ($sign_id)
        {
            $votemap['sign_id'] = $sign_id;
        }
        $votemap['is_delete'] = 0;
        $list = db('vote_maker')
            ->where($votemap)
            ->page($pageNum, $numPerPage)
            ->order('create_time desc')
            ->select();
        foreach ($list as $key=>$value) {
            $sign = db('activity_sign')->where('id',$list[$key]['sign_id'])->find();
            $list[$key]['username'] = $sign['username'];
        }
        return $list;
    }

    public function get_count()
    {
        $list = db('vote_maker')
            ->where('is_delete', 0)
            ->count();
        return $list;
    }

}