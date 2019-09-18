<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/21
 * Time: 17:04
 */
namespace app\admin\model;

/**
 * 分类模型
 * @package app\admin\model
 */

class Navigation
{
    public function getLableList($name, $type, $pageNum, $num_per_page)
    {
        $list = db('navigation')->where(function ($query) use ($name,$type) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
            if (!empty($type)) {
                $query->where('type', $type);
            }
        })
            ->where('is_delete',0)
            ->page($pageNum, $num_per_page)
            ->order('sort')
            ->select();
        return $list;
    }

    public function getLableCount($name, $type)
    {
        $list = db('navigation')->where(function ($query) use ($name,$type) {
            if (!empty($name)) {
                $query->where('name', 'like', "%{$name}%");
            }
            if (!empty($type)) {
                $query->where('type', $type);
            }
        })
            ->where('is_delete',0)
            ->count();
        return $list;
    }

    public function doLaAdd($data)
    {
        if($data['type'] == 1){
            unset($data['information']);
            if(empty($data['url']))
            {
                exception('请填写外部链接！');
            }
            $data['create_time'] = time();
            $data['update_time'] = time();
            $result = db('navigation')->insert($data);
            if (!$result) {
                exception('添加失败');
            }
        } elseif ($data['type'] == 2) {
            $inf_id = $data['information'];
            unset($data['information']);
            unset($data['url']);
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['is_delete'] = 0;
            $info = db('navigation')->where('name',$data['name'])->find();
            if($info)
            {
                exception('已存在相同标签名称');
            }
            $result = db('navigation')->insert($data);
            $nav_id = db('navigation')->getLastInsID();
            if (!$result) {
                exception('添加失败');
            } else {
                $navinfordata['nav_id'] = $nav_id;
                $navinfordata['inf_id'] = $inf_id;
                $navigationToinform = db('navigation_toinform')->insert($navinfordata);
                if(!$navigationToinform)
                {
                    exception('添加失败');
                }
            }
        }
    }

    public function DoLaModify($data)
    {
        $info = db('navigation')->where('id',$data['id'])->find();
        if($info['type'] == 1)
        {
            if($data['type'] == 2)
            {
                $inf_id = $data['information'];
                unset($data['information']);
                unset($data['url']);
                $data['update_time'] = time();
                $data['url'] = '';
                $info = db('navigation')->where('name',$data['name'])->where('id','neq',$data['id'])->find();
                if($info)
                {
                    exception('已存在相同标签名称');
                }
                $result = db('navigation')->update($data);
                if (!$result) {
                    exception('修改失败');
                } else {
                    $navinfordata['nav_id'] = $data['id'];
                    $navinfordata['inf_id'] = $inf_id;
                    $navigationToinform = db('navigation_toinform')->insert($navinfordata);
                    if(!$navigationToinform)
                    {
                        exception('修改失败');
                    }
                }
            } else {
                unset($data['information']);
                if(empty($data['url']))
                {
                    exception('请填写外部链接！');
                }
                $info = db('navigation')->where('name',$data['name'])->where('id','neq',$data['id'])->find();
                if($info)
                {
                    exception('已存在相同标签名称');
                }
                $data['update_time'] = time();
                $result = db('navigation')->update($data);
                if (!$result) {
                    exception('修改失败');
                }
            }
        } elseif ($info['type'] == 2)
        {
            if($data['type'] == 2)
            {
                $navigation_toinform = db('navigation_toinform')->where('nav_id',$info['id'])->find();
                $inf_id = $data['information'];
                unset($data['information']);
                unset($data['url']);
                if($navigation_toinform['inf_id'] == $inf_id)
                {
                    $data['update_time'] = time();
                    $info = db('navigation')->where('name',$data['name'])->where('id','neq',$data['id'])->find();
                    if($info)
                    {
                        exception('已存在相同标签名称');
                    }
                    $result = db('navigation')->update($data);
                    if (!$result) {
                        exception('修改失败11');
                    }
                } else {
                    $navigation_toinform_data['inf_id'] =  $inf_id;
                    $navigation_toinform = db('navigation_toinform')->where('nav_id',$data['id'])->update($navigation_toinform_data);
                    if(!$navigation_toinform)
                    {
                        exception('修改失败');
                    }
                    $info = db('navigation')->where('name',$data['name'])->where('id','neq',$data['id'])->find();
                    if($info)
                    {
                        exception('已存在相同标签名称');
                    }
                    $data['update_time'] = time();
                    $result = db('navigation')->update($data);
                    if (!$result) {
                        exception('修改失败');
                    }
                }
            } else {
                $navigation_toinform = db('navigation_toinform')->where('nav_id',$data['id'])->delete();
                if(!$navigation_toinform)
                {
                    exception('修改失败');
                }
                unset($data['information']);
                if(empty($data['url']))
                {
                    exception('请填写外部链接！');
                }
                $info = db('navigation')->where('name',$data['name'])->where('id','neq',$data['id'])->find();
                if($info)
                {
                    exception('已存在相同标签名称');
                }
                $data['update_time'] = time();
                $result = db('navigation')->update($data);
                if (!$result) {
                    exception('修改失败');
                }
            }
        }

    }

    public function labeldoDelete($id)
    {
        $data['id'] = $id;
        $data['update_time'] = time();
        $data['is_delete']   = 1;
        $info = db('navigation')->where('id',$id)->find();
        if($info['type'] != 1)
        {
            $navigation_toinform = db('navigation_toinform')->where('nav_id',$info['id'])->delete();
            if(!$navigation_toinform)
            {
                exception('删除失败');
            }
        }
        $result = db('navigation')->update($data);
        if (!$result) {
            exception('删除失败');
        }
    }



    public function getInformationList($title, $pageNum, $num_per_page)
    {
        $list = db('information')->where(function ($query) use ($title) {
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

    public function getInformationCount($title)
    {
        $list = db('information')->where(function ($query) use ($title) {
            if (!empty($title)) {
                $query->where('title', 'like', "%{$title}%");
            }
        })
            ->where('is_delete',0)
            ->count();
        return $list;
    }

    public function doImAdd($data)
    {
        if (!$data['details']) {
            exception('请填写文章详情');
        }
        $data['create_time'] = time();
        $data['update_time'] = time();
        $result = db('information')->insert($data);
        if (!$result) {
            exception('添加失败');
        }
    }

    public function doIMmodify($data)
    {
        if (!$data['details']) {
            exception('请填写文章详情');
        }
        $data['update_time'] = time();
        $result = db('information')->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    public function doIMDelete($id)
    {
        $info = db('navigation_toinform')->where('inf_id',$id)->find();
        $navigation = db('navigation')->where('id',$info['nav_id'])->where('is_delete',0)->find();
        if($navigation)
        {
            exception('该资讯已存在标签管理中，请先删除标签或修改标签资讯');
        }
        $data['update_time'] = time();
        $data['is_delete'] = 1;
        $data['id'] = $id;
        $result = db('information')->update($data);
        if (!$result) {
            exception('删除失败');
        }
    }


}