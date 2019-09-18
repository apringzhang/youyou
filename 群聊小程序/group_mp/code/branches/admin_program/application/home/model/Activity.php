<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/27
 * Time: 15:09
 */


namespace app\admin\model;


class Activity
{
    public function getList($name, $pageNum, $num_per_page)
    {
        $list = db('activity')->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        })
            ->page($pageNum, $num_per_page)
            ->order('create_time desc')
            ->select();
        return $list;
    }

    public function getListCount($name)
    {
        $list = db('activity')->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        })
            ->count();
        return $list;
    }

    public function doAdd($data)
    {
        $pr_id = $data['pr_id'];
        if (!$data['pr_id']) {
            exception('请选择奖品');
        }
        if($data['rate'] > 100 || $data['rate']<0)
        {
            exception('请填写0-100内的数字');
        }
        unset($data['pr_id']);
        $data['create_time'] = time();
        $data['update_time'] = time();
        $result = db('activity')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
        $ac_id = db('activity')->getLastInsID();
        $toprize_data['ac_id'] = $ac_id;
        $toprize_data['pr_id'] = $pr_id;
        $activity_toprize = db('activity_toprize')->insert($toprize_data);
        if (!$activity_toprize) {
            exception('添加失败');
        }
    }

    public function doModify($data)
    {
        $pr_id = $data['pr_id'];
        if (!$data['pr_id']) {
            exception('请选择奖品');
        }
        if($data['rate'] > 100 || $data['rate']<0)
        {
            exception('请填写0-100内的数字');
        }
        unset($data['pr_id']);
        $data['update_time'] = time();
        $result = db('activity')->update($data);
        if (!$result) {
            exception('修改失败');
        }
        $ac_id = db('activity_toprize')->where('ac_id',$data['id'])->find();
        $toprize_data['pr_id'] = $pr_id;
        $toprize_data['id'] = $ac_id['id'];
        $activity_toprize = db('activity_toprize')->update($toprize_data);
        if (!$activity_toprize) {
            exception('修改失败');
        }
    }

    public function doDelete($id)
    {
        $info = db('activity_toprize')->where('ac_id',$id)->delete();
        if (!$info) {
            exception('删除失败');
        }
        $result = db('activity')->where('id', $id)->delete();
        if (!$result) {
            exception('删除失败');
        }
    }
}