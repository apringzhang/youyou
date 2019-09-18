<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/2
 * Time: 14:48
 */

namespace app\admin\controller;

use think\Exception;

class Mp extends Common
{
    public function index()
    {
        $data = [];
        //搜索
        $MpAppid = input('post.MpAppid');
        $data['MpAppid'] = $MpAppid;
        //页码
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $MpModel = new \app\admin\model\Mp();
        //列表数据
        $list = $MpModel->getList($pageNum, $numPerPage, $MpAppid);
        $data['list'] = $list;
        //数据总数
        $count = $MpModel->getCount($MpAppid);
        $data['count'] = $count;
        return view('', $data);
    }

    public function add()
    {
        return view('');
    }

    public function doAdd()
    {
        $data = input('post.');
        $mp = db('mp')->where('appid',$data['appid'])->find();
        if ($mp)
        {
            return json([
                'statusCode' => 300,
                'message' => 'appid已存在',
            ]);
        }
        $MpModel = new \app\admin\model\Mp();
        try {
            $MpModel->doAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'mpManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 修改
     * @return \think\response\View
     */
    public function modify()
    {
        $data = db('mp')->where('id', input('get.id'))->find();
        return view('', $data);
    }

    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doModify()
    {
        $data = input('post.');
        $mp = db('mp')
            ->where('id','neq',$data['id'])
            ->where('appid',$data['appid'])
            ->find();
        if ($mp)
        {
            return json([
                'statusCode' => 300,
                'message' => 'appid已存在',
            ]);
        }
        $MpModel = new \app\admin\model\Mp();
        try {
            $MpModel->doModify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'mpManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete()
    {
        $MpId = input('get.id');
        $MpModel = new \app\admin\model\Mp();
        try {
            $MpModel->doDelete($MpId);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'mpManage',
        ]);
    }
}