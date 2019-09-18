<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/27
 * Time: 14:49
 */

namespace app\home\controller;

use think\Controller;
use think\Exception;


class Index extends controller
{
    public function index()
    {
        //轮播
        $data['carousel_list'] = db('carousel')
            ->where('is_delete',0)
            ->where('is_homeshow',0)
            ->order('sort asc')
            ->select();
        //文章
        $data['article_list'] = db('article')
            ->order('sort asc')
            ->select();
        //广告
        $data['advertisement_list'] = db('advertisement')
            ->where('is_delete',0)
            ->where('is_homeshow',0)
            ->order('sort asc')
            ->select();
        return view('',$data);
    }

    public function detail($id)
    {
        $info = db('article')
            ->where('id',$id)
            ->find();

        return view('',$info);
    }
}