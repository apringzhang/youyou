<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/7/6
 * Time: 8:59
 */

namespace app\admin\controller;

use think\Exception;

class Article extends Common
{
    public function category()
    {
        $name = input('post.name');
        $data['name'] = $name;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Article();
        $data['pageNum'] = $pageNum;
        $list = $model->getCgList($name, $pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getCgListCount($name);
        $data['count'] = $count;
        return view('',$data);
    }

    public function Cgadd()
    {

        return view('');
    }

    public function doCgAdd()
    {
        $data = input('post.');
        $model = new \app\admin\model\Article();
        try {
            $model->doCgAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'articleCgManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function Cgmodify($id)
    {
        if(!$id)
        {
            exception('修改失败');
        }
        $data = db('article_type')->where('id',$id)->find();
        return view('',$data);
    }

    public function doCgmodify()
    {
        $data = input('post.');
        $model = new \app\admin\model\Article();
        try {
            $model->doCgmodify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'articleCgManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function CgdoDelete($id)
    {
        $model = new \app\admin\model\Article();
        try {
            $model->CgdoDelete($id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'articleCgManage',
        ]);
    }

    public function index()
    {
        $name = input('post.name');
        $data['name'] = $name;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Article();
        $data['pageNum'] = $pageNum;
        $list = $model->getList($name, $pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getListCount($name);
        $data['count'] = $count;
        return view('',$data);
    }

    public function add()
    {
        $data['cglist'] = db('article_type')->field('id,name')->order('sort')->select();
        return view('',$data);
    }

    public function doAdd()
    {
        $data = input('post.');
        $model = new \app\admin\model\Article();
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
            'navTabId' => 'articleManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function modify($id)
    {
        if(!$id)
        {
            exception('修改失败');
        }
        $data = db('article')->where('id',$id)->find();
        $data['cglist'] = db('article_type')->field('id,name')->order('sort')->select();
        return view('',$data);
    }

    public function doModify()
    {
        $data = input('post.');
        $model = new \app\admin\model\Article();
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
            'message' => '添加成功',
            'navTabId' => 'articleManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function doDelete($id)
    {
        $model = new \app\admin\model\Article();
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
            'navTabId' => 'articleManage',
        ]);
    }
}