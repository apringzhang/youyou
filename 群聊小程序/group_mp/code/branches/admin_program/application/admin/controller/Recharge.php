<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/23
 * Time: 16:35
 */
namespace app\admin\controller;

use think\Exception;

class Recharge extends Common
{
    public function index()
    {
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Recharge();
        $data['pageNum'] = $pageNum;
        $list = $model->getList($pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getListCount();
        $data['count'] = $count;
        return view('',$data);
    }

    public function add()
    {
        return view('');
    }

    public function doAdd()
    {
        $data = input('post.');
        $model = new \app\admin\model\Recharge();
        try {
            $model->doAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'rechargeManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function modify($id)
    {
        $data = db('recharge')->where('id',$id)->find();
        return view('',$data);
    }

    public function doModify()
    {
        $data = input('post.');
        $model = new \app\admin\model\Recharge();
        try {
            $model->doModify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'rechargeManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function doDelete($id)
    {
        $model = new \app\admin\model\Recharge();
        try {
            $model->doDelete($id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'rechargeManage',
        ]);
    }
}