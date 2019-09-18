<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/6
 * Time: 16:11
 */

namespace app\api\model;

class Navigation
{
    /**
     * 获取导航标签信息
     * @param $company_id
     */
    public function get_navigation()
    {
        $data['is_delete'] = 0;
        $navigation = db('company')->where($data)->order('sort')->select();
        if (!$navigation)
        {
            exception('查询失败');
        }
        return $navigation;
    }

    public function get_navigation($nav_id)
    {
        $inf_id = db('navigationToinform')->where('nav_id', $nav_id)->column('inf_id');
        if ($inf_id)
        {
            $list = db('information')->where('id', 'in', $inf_id)->where('is_delete', 0)->order('sort')->select();
        } else {
            $list = [];
        }
        return $list;
    }

    public function get_carousel()
    {
        $list = db('carousel')->where('is_homeshow', 1)->where('is_delete', 0)->order('sort')->select();
        
        return $list;
    }
}