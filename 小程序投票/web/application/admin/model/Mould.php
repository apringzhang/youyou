<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/2
 * Time: 10:07
 */

namespace app\admin\model;

class Mould
{
    /**
     * 获取列表
     * @param $pageNum
     * @param $numPerPage
     * @param $MouldName
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pageNum, $numPerPage, $MouldName)
    {
        $list = db('model')
            ->where('is_delete', 0)
            ->where(function ($query) use ($MouldName) {
                if (!empty($MouldName)) {
                    $query->whereOr('model_name', 'like', "%{$MouldName}%");
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('create_time')
            ->select();
        return $list;
    }

    /**
     * 获取总数
     * @param $MouldName
     * @return int|string
     */
    public function getCount($MouldName)
    {
        $count = db('model')
            ->where('is_delete', 0)
            ->where(function ($query) use ($MouldName) {
                if (!empty($MouldName)) {
                    $query->whereOr('model_name', 'like', "%{$MouldName}%");
                }
            })
            ->count();
        return $count;
    }

    /**
     * 执行添加
     */
    public function doAdd($data)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('model')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    /**
     * 执行修改
     */
    public function doModify($data)
    {
        $data['update_time'] = time();
        $result = db('model')->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    /**
     * 执行删除
     * @param $MouldId
     */
    public function doDelete($MouldId)
    {
        $result = db('model')
            ->where('id', $MouldId)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        if (!$result) {
            exception('删除失败');
        }
    }
}