<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 9:41
 */

namespace app\admin\controller;

use think\Exception;

class Gift extends Common
{
    /**
     * 列表
     * @return \think\response\View
     */
    public function index()
    {
        $data = [];
        //搜索
        $giftName = input('post.giftName');
        $data['giftName'] = $giftName;
        //页码
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $giftModel = new \app\admin\model\Gift();
        //列表数据
        $list = $giftModel->getList($pageNum, $numPerPage, $giftName);
        $data['list'] = $list;
        //数据总数
        $count = $giftModel->getCount($giftName);
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
        if ($data['gift_value'] <= 0) {
            return json([
                'statusCode' => 300,
                'message' => '礼物价值必须大于0',
            ]);
        }
        if ($data['vote_num'] < 1) {
            return json([
                'statusCode' => 300,
                'message' => '可兑换票数必须大于1',
            ]);
        }
        $giftModel = new \app\admin\model\Gift();
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
            'navTabId' => 'giftManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 修改
     * @return \think\response\View
     */
    public function modify()
    {
        $data = db('gift')->where('id', input('get.id'))->find();
        return view('', $data);
    }

    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doModify()
    {
        $data = input('post.');
        if ($data['gift_value'] <= 0) {
            return json([
                'statusCode' => 300,
                'message' => '礼物价值必须大于0',
            ]);
        }
        if ($data['vote_num'] < 1) {
            return json([
                'statusCode' => 300,
                'message' => '可兑换票数必须大于1',
            ]);
        }
        $giftModel = new \app\admin\model\Gift();
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
            'navTabId' => 'giftManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete()
    {
        $giftId = input('get.id');
        $giftModel = new \app\admin\model\Gift();
        try {
            $giftModel->doDelete($giftId);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'giftManage',
        ]);
    }
}