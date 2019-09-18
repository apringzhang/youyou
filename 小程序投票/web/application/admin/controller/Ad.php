<?php

namespace app\admin\controller;

use think\Exception;

class Ad extends Common {

    public function index() {
        $ad_model= new \app\admin\model\Ad();
        $keyword = input('post.keyword');
        $data['keyword'] = $keyword;
        //广告位置
        $ad_position = input('post.ad_position');

        $data['ad_position_id'] = $ad_position;

        $ad_position_list = db('ad_position')->where('is_delete', 0)->select();
        $data['ad_position'] = $ad_position_list;
        
        //活动
        $activity_id = input('post.activity_id');

        $data['activity_id'] = $activity_id;

        $activity = db('activity')->where('is_delete', 0)->select();
        $data['activity'] = $activity;
        
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;

        $data['pageNum'] = $pageNum;
        $list = $ad_model->getList($keyword, $ad_position, $activity_id, $pageNum, $num_per_page);
        $data['list'] = $list;

        $count = $ad_model->getCount($keyword, $ad_position, $activity_id);
        $data['count'] = $count;

        return view('', $data);
    }
    
    public function add(){
        $ad_position = db('ad_position')->where('is_delete',0)->select();
        $activity = db('activity')->where('is_delete',0)->select();
        $mp = db('mp')->select();
        $data['ad_position'] = $ad_position;
        $data['activity'] = $activity;
        $data['mp'] = $mp;
        return view('',$data);
    }
    
    public function doAdd(){
        $data = input('post.');
//        dump($data['appid']);
        if ($data['adp_id'] == 0) {
            return json([
                'statusCode' => 300,
                'message' => '请选择广告位',
            ]);
        }
        if ($data['activity_id'] == 0) {
            return json([
                'statusCode' => 300,
                'message' => '请选择活动',
            ]);
        }
        if ($data['appid'] == "") {
            return json([
                'statusCode' => 300,
                'message' => '请选择小程序',
            ]);
        }
        if ($data['ad_image'] == '') {
            return json([
                'statusCode' => 300,
                'message' => '请上传图片',
            ]);
        }
        if ($data['ad_linkcontent'] == '') {
            return json([
                'statusCode' => 300,
                'message' => '请填写链接内容',
            ]);
        }
        $ad_model= new \app\admin\model\Ad();
        try{
            $ad_model->doAdd($data);
        } catch (Exception $e){
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'adManage',
            'callbackType' => 'closeCurrent',
        ]);
    }
    
    public function modify(){
        $ad_model= new \app\admin\model\Ad();
        $id = input('get.id');
        $data['id'] = $id;
        $data = $ad_model->modify($id);
        $ad_position = db('ad_position')->where('is_delete',0)->select();
        $activity = db('activity')->where('is_delete',0)->select();
        $mp = db('mp')->select();
        $data['ad_position'] = $ad_position;
        $data['activity'] = $activity;
        $data['mp'] = $mp;
        return view('',$data);
    }
    
    public function doModify(){
        $ad_model= new \app\admin\model\Ad();
        $id = input('get.id');
        $data = input('post.');
        if ($data['adp_id'] == 0) {
            return json([
                'statusCode' => 300,
                'message' => '请选择广告位',
            ]);
        }
        if ($data['activity_id'] == 0) {
            return json([
                'statusCode' => 300,
                'message' => '请选择活动',
            ]);
        }
        if ($data['appid'] == "") {
            return json([
                'statusCode' => 300,
                'message' => '请选择小程序',
            ]);
        }
        if ($data['ad_image'] == 0) {
            return json([
                'statusCode' => 300,
                'message' => '请上传图片',
            ]);
        }
        if ($data['ad_linkcontent'] == '') {
            return json([
                'statusCode' => 300,
                'message' => '请填写链接内容',
            ]);
        }
        try{
            $ad_model->doModify($id,$data);
        } catch (Exception $e){
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'adManage',
            'callbackType' => 'closeCurrent',
        ]);
    }
    
    public function doDelete(){
        $id = input('get.id');
        $ad_model= new \app\admin\model\Ad();
        try {
            $ad_model->doDelete($id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'adManage',
        ]);
    }

}
