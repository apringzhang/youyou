<?php

namespace app\admin\controller;

use think\Exception;

class AdPosition extends Common {

    public function index() {
        $adp_name = input('post.adp_name');
        $data['adp_name'] = $adp_name;
        //页码
        $pageNum = 1;
        
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = (int)input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        $data['page_num'] = $pageNum;
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $adPostionModel = new \app\admin\model\AdPosition();
        $list = $adPostionModel->getList($adp_name, $pageNum, $num_per_page);
        $data['list'] = $list;

        $count = $adPostionModel->getCount($adp_name);
        $data['count'] = $count;
        return view('', $data);
    }

    public function add() {
        return view();
    }

    public function doAdd() {
        $data = input('post.');
        $adPostionModel = new \app\admin\model\AdPosition();       
        try {
            $result = $adPostionModel->doAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'adPositionManage',
            'callbackType' => 'closeCurrent',
        ]);
    }
    
    /**
     * 修改页
     */
    public function modify(){
        $id = input('get.id');
        
        $adPostionModel = new \app\admin\model\AdPosition();
        
        $data = $adPostionModel->modify($id);

        return view('',$data);
    }
    
    public function doModify(){
        $id = input('get.id');
        $data = input('post.');
        $adPostionModel = new \app\admin\model\AdPosition();   
        try {
            $result = $adPostionModel->doModify($id,$data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'adPositionManage',
            'callbackType' => 'closeCurrent',
        ]);
    }
    
    public function doDelete(){
        $id = input('get.id');
        $adPostionModel = new \app\admin\model\AdPosition();   
        try {
            $result = $adPostionModel->doDelete($id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'adPositionManage',
        ]);
    }

}
