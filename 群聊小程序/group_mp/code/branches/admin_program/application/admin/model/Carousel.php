<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/22
 * Time: 17:10
 */

namespace app\admin\model;

class Carousel
{
    public function getList($title, $pageNum, $num_per_page)
    {
        $list = db('carousel')->where(function ($query) use ($title) {
            if (!empty($title)) {
                $query->where('title', 'like', "%{$title}%");
            }
        })
            ->where('is_delete',0)
            ->page($pageNum, $num_per_page)
            ->order('sort')
            ->select();
        return $list;
    }

    public function getListCount($title)
    {
        $list = db('carousel')->where(function ($query) use ($title) {
            if (!empty($title)) {
                $query->where('title', 'like', "%{$title}%");
            }
        })
            ->where('is_delete',0)
            ->count();
        return $list;
    }

    public function doAdd($data)
    {
        if (!$data['image']) {
            exception('上传轮播图片');
        }
        $data['create_time'] = time();
        $data['update_time'] = time();
        $result = db('carousel')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }


    public function doModify($data)
    {
        if (!$data['image']) {
            exception('上传轮播图片');
        }
        $data['update_time'] = time();
        $result = db('carousel')->update($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    public function doDelete($id)
    {
        $data['id'] = $id;
        $data['update_time'] = time();
        $data['is_delete']   = 1;
        $result = db('carousel')->update($data);
        if (!$result) {
            exception('删除失败');
        }
    }

}
