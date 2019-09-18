<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 16:24
 */

namespace app\admin\model;

/**
 * 礼物模型
 * Class User
 * @package app\admin\model
 */
class Gift
{
    /**
     * 获取列表
     * @param $pageNum
     * @param $numPerPage
     * @param $giftName
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pageNum, $numPerPage, $giftName)
    {
        $list = db('gift')
            ->where('is_delete', 0)
            ->where(function ($query) use ($giftName) {
                if (!empty($giftName)) {
                    $query->whereOr('gift_name', 'like', "%{$giftName}%");
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('sort, create_time')
            ->select();
        return $list;
    }

    /**
     * 获取总数
     * @param $giftName
     * @return int|string
     */
    public function getCount($giftName)
    {
        $count = db('gift')
            ->where('is_delete', 0)
            ->where(function ($query) use ($giftName) {
                if (!empty($giftName)) {
                    $query->whereOr('gift_name', 'like', "%{$giftName}%");
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
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('gift')->insert($data);
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
        $data['update_time'] = time();
        $result = db('gift')->update($data);
        if (!$result) {
            exception('修改失败');
        } else {
            db('order')->where('gift_id', $data['id'])->update([
                'gift_name' => $data['gift_name'],
            ]);
        }
    }

    /**
     * 执行删除
     * @param $giftId
     */
    public function doDelete($giftId)
    {
        $result = db('gift')
            ->where('id', $giftId)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        if (!$result) {
            exception('删除失败');
        }
    }
}