<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 16:24
 */

namespace app\admin\model;

/**
 * 活动模型
 * Class User
 * @package app\admin\model
 */
class Activity
{
    /**
     * 获取列表
     * @param $pageNum
     * @param $numPerPage
     * @param $activity_name
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pageNum, $numPerPage, $activity_name, $activityType)
    {
        $list = db('activity')
            ->where('is_delete', 0)
            ->where(function ($query) use ($activity_name, $activityType) {
                if (!empty($activity_name)) {
                    $query->where('activity_name', 'like', "%{$activity_name}%");
                }
                if (!empty($activityType)) {
                    $query->where('activity_type', $activityType);
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('sort asc,create_time desc')
            ->select();
        foreach ($list as $key => $value) {
            $activity_category = db('activity_category')->where('id',$list[$key]['category_id'])->find();
            $list[$key]['category_name'] = $activity_category['category_name'];
        }
        return $list;
    }

    /**
     * 获取小程序列表
     * @return mixed
     */
    public function getActivityWechatList()
    {
        $list = db('mp')
            ->where('is_delete', 0)
            ->order('create_time')
            ->select();
        return $list;
    }

    /**
     * 获取小程序
     * @return mixed
     */
    public function getActivityWechat($id)
    {
        $list = db('activity_wechat')
            ->where('activity_id', $id)
            ->find();
        return $list['appid'];
    }

    /**
     * 获取总数
     * @param $activity_name
     * @return int|string
     */
    public function getCount($activity_name, $activityType)
    {
        $count = db('activity')
            ->where('is_delete', 0)
            ->where(function ($query) use ($activity_name, $activityType) {
                if (!empty($activity_name)) {
                    $query->where('activity_name', 'like', "%{$activity_name}%");
                }
                if (!empty($activityType)) {
                    $query->where('activity_type', $activityType);
                }
            })
            ->count();
        return $count;
    }

    /**
     * 执行添加
     */
    public function doAdd($data, $id=false)
    {
        $mp_id = $data['mp_id'];
        unset($data['mp_id']);
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('activity')->insert($data);
        $activityId = db('activity')->getLastInsID();
        $this->doMpAdd($activityId,$mp_id);
        if (!$result) {
            exception('添加失败');
        } else {
            if ($id) {
                $this->doCopy($id, $activityId);
            }
        }
    }
    
    /**
     * 执行复制抽奖 积分管理
     */
    public function doCopy($id, $activityId)
    {
        //复制积分管理
        $score = db('score_rule')->where('activity_id',$id)->select();
        $data = [];
        foreach ($score as $key => $value) {
            $data['activity_id'] = $activityId;
            $data['gift_id'] = $value['gift_id'];
            $data['score'] = $value['score'];
            $result = db('score_rule')->insert($data);
            if (!$result) {
                exception('添加失败');
            }
        }
        //复制抽奖管理
        $award = db('award_rule')->where('activity_id',$id)->select();
        $datas = [];
        foreach ($award as $key => $value) {
            $datas['activity_id'] = $activityId;
            $datas['award_id'] = $value['award_id'];
            $datas['odds'] = $value['odds'];
            $datas['num'] = $value['num'];
            $result = db('award_rule')->insert($datas);
            if (!$result) {
                exception('添加失败');
            }
        }
    }

    /*
     *添加activity_wechat表
     */
    public function doMpAdd($activityId,$mp_id)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['activity_id'] = $activityId;
        $data['mp_id'] = $mp_id;
        $result = db('activity_wechat')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }


    /**
     * 执行修改
     */
    public function doModify($data)
    {
        $mp_id = $data['mp_id'];
        unset($data['mp_id']);
        $data['update_time'] = time();
        $result = db('activity')->update($data);
        $this->doMpModify($mp_id,$data['id']);
        if (!$result) {
            exception('修改失败');
        }
    }

    /**
     * 执行activity_wechat表修改
     */
    public function doMpModify($mp_id,$activity_id)
    {
        $data['mp_id'] = $mp_id;
        $data['activity_id'] = $activity_id;
        $data['update_time'] = time();
        $result = db('activity_wechat')->where('activity_id',$activity_id)->update($data);
        if (!$result) {
            exception('修改失败1');
        }
    }

    /**
     * 执行删除
     * @param $giftId
     */
    public function doDelete($id)
    {
        $result = db('activity')
            ->where('id', $id)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        $this->doMpDelete($id);
        if (!$result) {
            exception('删除失败');
        }
    }

    public function doMpDelete($id)
    {
        $result = db('activity_wechat')->where('activity_id',$id)->delete();
        if (!$result) {
            exception('删除失败');
        }
    }

    public function getCategoryList($pageNum, $numPerPage, $activity_name)
    {
        $list = db('activity_category')
            ->where('is_delete', 0)
            ->where(function ($query) use ($activity_name) {
                if (!empty($activity_name)) {
                    $query->where('category_name', 'like', "%{$activity_name}%");
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('create_time desc')
            ->select();
        return $list;
    }

    /**
     * 获取总数
     * @param $activity_name
     * @return int|string
     */
    public function getCategoryCount($activity_name)
    {
        $count = db('activity_category')
            ->where('is_delete', 0)
            ->where(function ($query) use ($activity_name) {
                if (!empty($activity_name)) {
                    $query->where('category_name', 'like', "%{$activity_name}%");
                }
            })
            ->count();
        return $count;
    }

    public function doCategoryAdd($data)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $data['status'] = 0;
        $result = db('activity_category')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }  

    /**
     * 执行修改
     */
    public function doCategoryModify($data)
    {
        $data['update_time'] = time();
        $result = db('activity_category')->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }


    /**
     * 执行删除
     * @param $giftId
     */
    public function doCategoryDelete($id)
    {
        $result = db('activity_category')
            ->where('id', $id)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        if (!$result) {
            exception('删除失败');
        }
    }
    
}