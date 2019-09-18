<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 16:24
 */

namespace app\admin\model;

/**
 * 留言模型
 * Class User
 * @package app\admin\model
 */
class Guestbook
{
    /**
     * 获取列表
     * @param $pageNum
     * @param $numPerPage
     * @param $order_sn
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pageNum, $numPerPage, $activity_id, $start, $stop)
    {
        $list = db('guestbook')
            ->where('activity_id', $activity_id)
            ->where('is_delete', 0)
            ->where(function ($query) use ($start, $stop) {
                if ($start) {
                    $query->where('create_time', 'between', [$start,$stop]);
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('create_time desc')
            ->select();
        return $list;
    }

    /**
     * 获取总数
     * @param $order_sn
     * @return int|string
     */
    public function getCount($activity_id, $start, $stop)
    {
        $count = db('guestbook')
            ->where('activity_id', $activity_id)
            ->where('is_delete', 0)
            ->where(function ($query) use ($start, $stop) {
                if ($start) {
                    $query->where('create_time', 'between', [$start,$stop]);
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
        $data['audit_flag'] = 1;
        $count = db('activity_sign')->where('activity_id', $data['activity_id'])->count();
        $data['sign_code'] = $count+1;
        $result = db('activity_sign')->insert($data);
        if (!$result) {
            exception('添加失败');
        } else {
            db('activity')->where('id', $data['activity_id'])->setInc('apply_count', 1);
        }
    }

    /**
     * 执行修改
     */
    public function doModify($data)
    {
        $data['update_time'] = time();
        $result = db('activity_sign')->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    /**
     * 执行删除
     * @param $giftId
     */
    public function doDelete($id, $activity_id)
    {
        $arr = db('activity_sign')
            ->field('vote_count, gift_count, total_count')
            ->where('id', $id)
            ->find();
        $result = db('activity_sign')
            ->where('id', $id)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        $db = db('activity');
        $db->where('id', $activity_id)->setDec('vote_count', $arr['vote_count']);
        $db->where('id', $activity_id)->setDec('gift_count', $arr['gift_count']);
        $db->where('id', $activity_id)->setDec('total_count', $arr['total_count']);
        $db->where('id', $activity_id)->setDec('apply_count', 1);
        if (!$result) {
            exception('删除失败');
        }
    }
}