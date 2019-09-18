<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/2
 * Time: 14:57
 */

namespace app\admin\model;


class Mp
{
    /**
     * 获取列表
     * @param $pageNum
     * @param $numPerPage
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pageNum, $numPerPage, $MpAppid)
    {
        $list = db('mp')
            ->where('is_delete',0)
            ->where(function ($query) use ($MpAppid) {
                if (!empty($MpAppid)) {
                    $query->whereOr('appid', 'like', "%{$MpAppid}%");
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('create_time')
            ->select();
        return $list;
    }

    /**
     * 获取总数
     * @return int|string
     * ->where('is_delete', 0)
     */
    public function getCount($MpAppid)
    {
        $count = db('mp')
            ->where('is_delete',0)
            ->where(function ($query) use ($MpAppid) {
                if (!empty($MpAppid)) {
                    $query->whereOr('appid', 'like', "%{$MpAppid}%");
                }
            })
            ->count();
        return $count;
    }


    /**
     * 执行添加
     * @param $roleName
     * @param $sort
     */
    public function doAdd($data)
    {

        $data['user_id'] = session('admin.id');
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('mp')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    /**
     * 执行修改
     * @param $giftId
     * @param $giftName
     * @param $sort
     */
    public function doModify($data)
    {
        $data['user_id'] = session('admin.id');
        $data['update_time'] = time();
        $result = db('mp')->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    /**
     * 执行删除
     * @param $giftId
     */
    public function doDelete($Mpid)
    {
        $result = db('mp')
            ->where('id', $Mpid)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        if (!$result) {
            exception('删除失败');
        }
    }
}