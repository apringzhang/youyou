<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/23
 * Time: 16:37
 */
namespace app\admin\model;

class Recharge
{
    public function getList($pageNum, $num_per_page)
    {
        $list = db('recharge')
            ->where('is_delete',0)
            ->page($pageNum, $num_per_page)
            ->order('sort')
            ->select();
        return $list;
    }

    public function getListCount()
    {
        $list = db('recharge')
            ->where('is_delete',0)
            ->count();
        return $list;
    }

    public function doAdd($data)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('recharge')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    public function doModify($data)
    {
        $data['update_time'] = time();
        $result = db('recharge')->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    public function doDelete($id)
    {
        $data['id'] = $id;
        $data['update_time'] = time();
        $data['is_delete']   = 1;
        $result = db('recharge')->update($data);
        if (!$result) {
            exception('删除失败');
        }
    }
}