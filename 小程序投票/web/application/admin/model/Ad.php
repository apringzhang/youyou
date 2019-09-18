<?php

namespace app\admin\model;

use think\Validate;

class Ad {

    public function getList($keyword, $ad_position, $activity_id, $pageNum, $num_per_page) {
        $list = db('ad')->where(function ($query) use ($keyword, $ad_position, $activity_id) {
                    if (!empty($keyword)) {
                        $query->where('ad_name', 'like', "%{$keyword}%");
                    }
                    if ($ad_position != '') {
                        $query->where('adp_id', $ad_position);
                    }
                    if ($ad_position == '') {
                        $positions = db('ad_position')
                                ->field('id')
                                ->select();
                        $new_positions = [];
                        foreach ($positions as $value) {
                            array_push($new_positions, $value['id']);
                        }
                        $query->where('adp_id', 'in', $new_positions);
                    }
                    if($activity_id != ''){
                        $query->where('activity_id', $activity_id);
                    }
                })
                ->where('is_delete', 0)
                ->page($pageNum, $num_per_page)
                ->order('update_time desc')
                ->select();
        foreach ($list as &$value){
           $value['adp_name'] = db('ad_position')->where('id',$value['adp_id'])->value('adp_name'); 
           $value['activity_name'] = db('activity')->where('id',$value['activity_id'])->value('activity_name');
           $value['mp_name'] = db('mp')->where('appid',$value['appid'])->value('mp_name');
        }
        return $list;
    }

    public function getCount($keyword, $ad_position, $activity_id) {
        $count = db('ad')
                ->where(function ($query) use ($keyword, $ad_position) {
                    if (!empty($keyword)) {
                        $query->where('ad_name', 'like', "%{$keyword}%");
                    }
                    if ($ad_position != '') {
                        $query->where('adp_id', $ad_position);
                    }
                    if ($ad_position == '') {
                        $positions = db('ad_position')
                                ->field('id')
                                ->select();
                        $new_positions = [];
                        foreach ($positions as $value) {
                            array_push($new_positions, $value['id']);
                        }
                        $query->where('adp_id', 'in', $new_positions);
                    }
                    if($activity_id != ''){
                        $query->where('activity_id', $activity_id);
                    }
                })
                ->where("is_delete", 0)
                ->count();
        return $count;
    }
    
    public function doAdd($data){
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('ad')
            ->insert($data);
        if(!$result){
            exception('添加失败');
        }
    }
    
    public function modify($id){
        $info = db('ad')->where('id',$id)->find();
        return $info;
    }
    
    public function doModify($id,$data){
        $data['update_time'] = time();
        $result = db('ad')
            ->where('id',$id)
            ->update($data);
        if(!$result){
            exception('修改失败');
        }
    }
    
    public function doDelete($id){
        $result = db('ad')
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
