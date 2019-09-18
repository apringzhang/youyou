<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 13:22
 */

namespace app\admin\model;
/**
 * 节点模型
 * @package app\admin\model
 */
class Node
{
    /**
     * 获取节点列表
     * @param int $pid
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pid = 0)
    {
        $list = db('node')
            ->where('pid', $pid)
            ->where('is_delete', 0)
            ->order('sort, create_time')
            ->select();
        //检查是否有下级
        foreach ($list as &$val) {
            $sub = db('node')
                ->where('pid', $val['id'])
                ->where('is_delete', 0)
                ->find();
            if ($sub) {
                $val['sub'] = true;
            }
        }
        return $list;
    }

    /**
     * 执行添加
     * @param $pid
     * @param $nodeName
     * @param $nodeTitle
     * @param $sort
     */
    public function doAdd($pid, $nodeName, $nodeTitle, $sort)
    {
        $data = [
            'pid' => $pid,
            'node_name' => $nodeName,
            'node_title' => $nodeTitle,
            'sort' => $sort,
            'create_time' => time(),
            'update_time' => time(),
        ];
        $result = db('node')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    /**
     * 执行修改
     * @param $nodeId
     * @param $pid
     * @param $nodeName
     * @param $nodeTitle
     * @param $sort
     */
    public function doModify($nodeId, $pid, $nodeName, $nodeTitle, $sort)
    {
        $data = [
            'pid' => $pid,
            'node_name' => $nodeName,
            'node_title' => $nodeTitle,
            'sort' => $sort,
            'update_time' => time(),
        ];
        $result = db('node')->where('id', $nodeId)->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    /**
     * 执行删除
     * @param $nodeId
     */
    public function doDelete($nodeId)
    {
        $result = db('node')
            ->where('id', $nodeId)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        if (!$result) {
            exception('删除失败');
        }
    }
}