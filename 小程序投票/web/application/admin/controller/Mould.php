<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/2
 * Time: 9:56
 */

namespace app\admin\controller;

use think\Exception;


class Mould extends Common
{
    /**
     * 列表
     * @return \think\response\View
     */
    public function index()
    {
        $data = [];
        //搜索
        $MouldName = input('post.mouldName');
        $data['mouldName'] = $MouldName;
        //页码
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $mouldModel = new \app\admin\model\Mould();
        //列表数据
        $list = $mouldModel->getList($pageNum, $numPerPage, $MouldName);
        $data['list'] = $list;
        //数据总数
        $count = $mouldModel->getCount($MouldName);
        $data['count'] = $count;
        return view('', $data);
    }

    /**
     * 添加
     * @return \think\response\View
     */
    public function add()
    {
        $data = [];
        return view('', $data);
    }

    /**
     * 执行添加
     * @return \think\response\Json
     */
    public function doAdd()
    {
        $data = input('post.');
        $giftModel = new \app\admin\model\Mould();
        try {
            $giftModel->doAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'mouldManage',
            'callbackType' => 'closeCurrent',
        ]);
    }


    /**
     * 修改
     * @return \think\response\View
     */
    public function modify()
    {
        $data = db('model')->where('id', input('get.id'))->find();
        return view('', $data);
    }

    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doModify()
    {
        $data = input('post.');
        $giftModel = new \app\admin\model\Mould();
        try {
            $giftModel->doModify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'mouldManage',
            'callbackType' => 'closeCurrent',
        ]);
    }


    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete()
    {
        $MouldId = input('get.id');
        $giftModel = new \app\admin\model\Mould();
        try {
            $giftModel->doDelete($MouldId);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'mouldManage',
        ]);
    }
}