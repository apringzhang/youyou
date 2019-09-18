<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 16:24
 */

namespace app\admin\model;
use wapmorgan\Mp3Info\Mp3Info;
/**
 * 活动报名模型
 * Class User
 * @package app\admin\model
 */
class Sign
{
    /**
     * 获取列表
     * @param $pageNum
     * @param $numPerPage
     * @param $username
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pageNum, $numPerPage, $username, $id, $auditFlag)
    {
        $list = db('activity_sign')
            ->where('is_delete', 0)
            ->where('activity_id', $id)
            ->where(function ($query) use ($username, $auditFlag) {
                if (!empty($username)) {
                    $query->whereOr('username', 'like', "%{$username}%");
                }
                if (!empty($auditFlag)) {
                    $query->where('audit_flag', $auditFlag);
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('create_time desc')
            ->select();
        return $list;
    }

    /**
     * 获取总数
     * @param $username
     * @return int|string
     */
    public function getCount($username, $id, $auditFlag)
    {
        $count = db('activity_sign')
            ->where('is_delete', 0)
            ->where('activity_id', $id)
            ->where(function ($query) use ($username, $auditFlag) {
                if (!empty($username)) {
                    $query->whereOr('username', 'like', "%{$username}%");
                }
                if (!empty($auditFlag)) {
                    $query->where('audit_flag', $auditFlag);
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
        if (!empty($data['sign_audio'])) {
            //获取声音文件长度
            $audio = new Mp3Info(config('mp_audio_dir') . $data['sign_audio']);
            $duration = $audio->duration;
            $data['sign_duration'] = $duration;
        }
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
        if (!empty($data['sign_audio'])) {
            //获取声音文件长度
            $audio = new Mp3Info(config('mp_audio_dir') . $data['sign_audio']);
            $duration = $audio->duration;
            $data['sign_duration'] = $duration;
        }
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

    /**
     * 获取红包列表
     * @param $pageNum
     * @param $numPerPage
     * @param $order_sn
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getPacket($pageNum, $numPerPage, $order_sn, $activity_id, $order_status)
    {
        $list = db('red_packet')
            ->where('activity_id', $activity_id)
            ->where(function ($query) use ($order_sn, $order_status) {
                if (!empty($order_sn)) {
                    $query->whereOr('order_sn', 'like', "%{$order_sn}%");
                }
                if ($order_status) {
                    if ($order_status == 2) {
                        $order_status = 0;
                    }
                    $query->where('order_status', $order_status);
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('id desc')
            ->select();
        return $list;
    }

    /**
     * 获取红包总数
     * @param $order_sn
     * @return int|string
     */
    public function getPacketCount($order_sn, $activity_id, $order_status)
    {
        $count = db('red_packet')
            ->where('activity_id', $activity_id)
            ->where(function ($query) use ($order_sn, $order_status) {
                if (!empty($order_sn)) {
                    $query->whereOr('order_sn', 'like', "%{$order_sn}%");
                }
                if ($order_status) {
                    $query->where('order_status', $order_status);
                }
            })
            ->count();
        return $count;
    }

    /**
     * 领取红包列表
     * @param $pageNum
     * @param $numPerPage
     * @param $order_sn
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getUserPacket($pageNum, $numPerPage, $activity_id, $action)
    {
        $list = db('user_red_packet')
            ->where('activity_id', $activity_id)
            ->where(function ($query) use ($action) {
                if ($action) {
                    $query->where('action', $action);
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('id desc')
            ->select();
        return $list;
    }

    /**
     * 领取红包总数
     * @param $order_sn
     * @return int|string
     */
    public function getUserPacketCount($activity_id, $action)
    {
        $count = db('user_red_packet')
            ->where('activity_id', $activity_id)
            ->where(function ($query) use ($action) {
                if ($action) {
                    $query->where('action', $action);
                }
            })
            ->count();
        return $count;
    }
}