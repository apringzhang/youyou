<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/23
 * Time: 13:37
 */
namespace app\admin\model;

class Money
{
    public function getList($pageNum, $num_per_page)
    {
        $list = db('vip')
            ->where('is_delete',0)
            ->page($pageNum, $num_per_page)
            ->order('sort')
            ->select();
        return $list;
    }

    public function getListCount()
    {
        $list = db('vip')
            ->where('is_delete',0)
            ->count();
        return $list;
    }

    public function doAdd($data)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('vip')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    public function doModify($data)
    {
        $data['update_time'] = time();
        $result = db('vip')->update($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    public function doDelete($id)
    {
        $data['id'] = $id;
        $data['update_time'] = time();
        $data['is_delete']   = 1;
        $result = db('vip')->update($data);
        if (!$result) {
            exception('删除失败');
        }
    }
}