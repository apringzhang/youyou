<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/27
 * Time: 15:09
 */


namespace app\admin\model;


class Gift
{
    public function getList($name, $pageNum, $num_per_page)
    {
        $list = db('prize')->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        })
            ->page($pageNum, $num_per_page)
            ->order('sort')
            ->select();
        foreach ($list as $key => $value)
        {
            if($list[$key]['type'] == 0)
            {
                $list[$key]['type_name'] ='谢谢参与';
            }
            if($list[$key]['type'] == 1)
            {
                $list[$key]['type_name'] ='礼品奖励';
            }
        }
        return $list;
    }

    public function getListCount($name)
    {
        $list = db('prize')->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        })
            ->count();
        return $list;
    }

    public function doAdd($data)
    {
        if(empty($data['image']))
        {
            exception('请上传商品原始图');
        }
        $data['create_time'] = time();
        $data['update_time'] = time();
        $result = db('prize')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    public function doModify($data)
    {
        if(empty($data['image']))
        {
            exception('请上传商品原始图');
        }
        $data['update_time'] = time();
        $result = db('prize')->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    public function doDelete($id)
    {
        $info = db('activity_toprize')->where('pr_id',$id)->find();
        if($info)
        {
            exception('该奖品存在活动下，请先删除活动后再删除该奖品');
        }
        $data['update_time'] = time();
        $result = db('prize')->where('id', $id)->delete();
        if (!$result) {
            exception('删除失败');
        }
    }
}