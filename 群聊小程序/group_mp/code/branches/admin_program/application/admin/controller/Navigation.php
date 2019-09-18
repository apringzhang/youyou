<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/21
 * Time: 16:57
 */
namespace app\admin\controller;

use think\Exception;

class Navigation extends Common
{
    public function label()
    {
        $name = input('post.name');
        $data['name'] = $name;
        $type = input('post.type');
        $data['type'] = $type;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Navigation();
        $data['pageNum'] = $pageNum;
        $list = $model->getLableList($name, $type, $pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getLableCount($name, $type);
        $data['count'] = $count;
        return view('',$data);
    }

    public function labeladd()
    {
        $data['info'] = db('information')->where('is_delete',0)->field('id,title')->order('sort')->select();
        return view('',$data);
    }

    public function doLaAdd()
    {
        $data = input('post.');
        $model = new \app\admin\model\Navigation();
        try {
            $model->doLaAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'labelManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function labelmodify($id)
    {
        $data = db('navigation')->where('id',$id)->find();
        $data['info'] = db('information')->where('is_delete',0)->field('id,title')->order('sort')->select();
        $data['navigation'] = db('navigation_toinform')->where('nav_id',$id)->find();
        return view('',$data);
    }

    public function DoLaModify()
    {
        $data = input('post.');
        $model = new \app\admin\model\Navigation();
        try {
            $model->DoLaModify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'labelManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function labeldoDelete($id)
    {
        $model = new \app\admin\model\Navigation();
        try {
            $model->labeldoDelete($id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'labelManage',
        ]);
    }

    public function information()
    {
        $title = input('post.title');
        $data['title'] = $title;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Navigation();
        $data['pageNum'] = $pageNum;
        $list = $model->getInformationList($title, $pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getInformationCount($title);
        $data['count'] = $count;
        return view('',$data);
    }

    public function informationadd()
    {
        return view('');
    }

    public  function doImadd()
    {
        $data = input('post.');
        $model = new \app\admin\model\Navigation();
        try {
            $model->doImAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'informationManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function informationmodify($id)
    {
        $data = db('information')->where('id',$id)->find();
        return view('',$data);
    }

    public function doIMmodify()
    {
        $data = input('post.');
        $model = new \app\admin\model\Navigation();
        try {
            $model->doIMmodify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'informationManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function informationdoDelete($id)
    {
        $model = new \app\admin\model\Navigation();
        try {
            $model->doIMDelete($id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'informationManage',
        ]);
    }
}