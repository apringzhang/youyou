<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 12:59
 */

namespace app\admin\controller;

use think\Exception;

/**
 * 节点控制器
 * @package app\admin\controller
 */
class Node extends Common
{
    /**
     * 节点列表
     * @return \think\response\View
     */
    public function index()
    {
        return view();
    }

    /**
     * 添加节点
     * @return \think\response\View
     */
    public function add()
    {
        return view();
    }

    /**
     * 执行添加
     * @return \think\response\Json
     */
    public function doAdd()
    {
        $pid = input('post.pid');
        //input name nodeName与jquery冲突
        $nodeName = input('post.nodeName1');
        $nodeTitle = input('post.nodeTitle');
        $sort = input('post.sort');
        $nodeModel = new \app\admin\model\Node();
        try {
            $nodeModel->doAdd($pid, $nodeName, $nodeTitle, $sort);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'nodeManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 选择上级节点
     * @return \think\response\View
     */
    public function pid()
    {
        return view();
    }

    /**
     * 修改
     * @return \think\response\View
     */
    public function modify()
    {
        $data = db('node')->where('id', input('get.id'))->find();
        $data['parent_node_title'] = db('node')->find($data['pid'])['node_title'];
        return view('', $data);
    }

    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doModify()
    {
        $nodeId = input('post.nodeId');
        $pid = input('post.pid');
        //input name nodeName与jquery冲突
        $nodeName = input('post.nodeName1');
        $nodeTitle = input('post.nodeTitle');
        $sort = input('post.sort');
        $nodeModel = new \app\admin\model\Node();
        try {
            $nodeModel->doModify($nodeId, $pid, $nodeName, $nodeTitle, $sort);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'nodeManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete()
    {
        $nodeId = input('get.id');
        $nodeModel = new \app\admin\model\Node();
        try {
            $nodeModel->doDelete($nodeId);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'nodeManage',
        ]);
    }
}