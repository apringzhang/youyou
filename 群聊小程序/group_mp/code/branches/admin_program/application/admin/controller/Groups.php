<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/23
 * Time: 16:45
 */
namespace app\admin\controller;

use think\Exception;

class Groups extends Common
{
    public function index()
    {
        $name = input('post.name');
        $data['name'] = $name;
        $admin_name = input('post.admin_name');
        $data['admin_name'] = $admin_name;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Groups();
        $data['pageNum'] = $pageNum;
        $list = $model->getList($name, $admin_name, $pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getListCount($name, $admin_name);
        $data['count'] = $count;
        $data['url'] = config('SITE_URL');
        return view('',$data);
    }

    public function Memberlist()
    {
        $id = input('get.id');
        if(!$id)
        {
            $id = input('post.id');
        }
        $data['id'] = $id;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Groups();
        $data['pageNum'] = $pageNum;
        $list = $model->getMemberList($id,$pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getMemberCount($id);
        $data['count'] = $count;
        $data['url'] = config('SITE_URL');
        return view('',$data);
    }
}