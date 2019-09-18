<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 13:22
 */

namespace app\admin\model;


class Article
{
    public function getCgList($name, $pageNum, $num_per_page)
    {
        $list = db('article_type')->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        })
            ->page($pageNum, $num_per_page)
            ->order('sort')
            ->select();
        return $list;
    }

    public function getCgListCount($name)
    {
        $list = db('article_type')->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        })
            ->count();
        return $list;
    }

    public function doCgAdd($data)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $result = db('article_type')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    public function doCgmodify($data)
    {
        $data['update_time'] = time();
        $result = db('article_type')->where('id', $data['id'])->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    public function CgdoDelete($id)
    {
        $data['update_time'] = time();
        $article = db('article')->where('type', $id)->find();
        if (!empty($article)) {
            exception('文章分类下存在相应文章，请先删除文章后删除分类');
        }
        $result = db('article_type')->where('id', $id)->delete();
        if (!$result) {
            exception('删除失败');
        }
    }

    public function getList($name, $pageNum, $num_per_page)
    {
        $list = db('article')->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        })
            ->page($pageNum, $num_per_page)
            ->order('sort')
            ->select();
        return $list;
    }

    public function getListCount($name)
    {
        $list = db('article')->where(function ($query) use ($name) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
        })
            ->count();
        return $list;
    }

    public function doAdd($data)
    {
        if (!$data['type']) {
            exception('请选择文章分类');
        }
        if (!$data['details']) {
            exception('请填写文章详情');
        }
        $data['create_time'] = time();
        $data['update_time'] = time();
        $result = db('article')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    public function doModify($data)
    {
        if (!$data['type']) {
            exception('请选择文章分类');
        }
        if (!$data['details']) {
            exception('请填写文章详情');
        }
        $data['update_time'] = time();
        $result = db('article')->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    public function doDelete($id)
    {
        $data['update_time'] = time();
        $result = db('article')->where('id', $id)->delete();
        if (!$result) {
            exception('删除失败');
        }
    }
}