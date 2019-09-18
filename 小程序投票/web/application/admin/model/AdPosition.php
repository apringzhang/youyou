<?php

namespace app\admin\model;

use think\Validate;


class AdPosition {

    public function getList($adp_name, $pageNum, $num_per_page) {
        $list = db('ad_position')
                ->where(function($query) use ($adp_name) {
                    if (!empty($adp_name)) {
                        $query->where('adp_name', 'like', "%{$adp_name}%");
                    }
                })
                ->where('is_delete', 0)
                ->order('update_time desc')
                ->page($pageNum, $num_per_page)
                ->select();
        return $list;
    }

    public function getCount($adp_name) {
        $count = db('ad_position')
                ->where(function($query) use ($adp_name) {
                    if (!empty($adp_name)) {
                        $query->where('adp_name', 'like', "%{$adp_name}%");
                    }
                })
                ->where('is_delete', 0)
                ->order('update_time desc')
                ->page($pageNum, $num_per_page)
                ->count();
        return $count;
    }
    
    public function doAdd($data){
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('ad_position')
            ->insert($data);
        if(!$result){
            exception('添加失败');
        }
    }
    
    public function modify($id){
        $data = db('ad_position')->where('id',$id)->find();
        return $data;
    }
    
    public function doModify($id,$data){
        $data['update_time'] = time();
        $result = db('ad_position')
            ->where('id',$id)
            ->update($data);
        if(!$result){
            exception('修改失败');
        }
    }
    
    public function doDelete($id){
        $result = db('ad_position')
            ->where('id', $id)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        if (!$result) {
            exception('删除失败');
        }
    }

}
