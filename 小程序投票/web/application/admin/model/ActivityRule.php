<?php

namespace app\admin\model;

class ActivityRule {

    public function getList($rule_name, $pageNum, $num_per_page) {
        $list = db('activity_rule')
                ->where(function($query) use ($rule_name) {
                    if (!empty($rule_name)) {
                        $query->where('rule_name','like' ,"%{$rule_name}%");
                    }
                })
                ->where('is_delete',0)
                ->page($page, $num_per_page)
                ->order('update_time desc')
                ->select();
                return $list;
    }
    
    public function getCount($rule_name){
        $count = db('activity_rule')
                ->where(function($query) use ($rule_name) {
                    if (!empty($rule_name)) {
                        $query->where('rule_name', $rule_name);
                    }
                })
                ->where('is_delete',0)
                ->count();
                return $count;
    }
    
    public function doAdd($data){
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('activity_rule')->insert($data);
        if(!$result){
            exception('添加失败');
        }
    }
    
    public function modify($id){
        $info = db('activity_rule')->where('id',$id)->find();
        return $info;
    }
    
    public function doModify($id,$data){
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['is_delete'] = 0;
        $result = db('activity_rule')->where('id',$id)->update($data);
        if(!$result){
            exception('修改失败');
        }
    }
    
    public function doDelete($id){
        $result = db('activity_rule')
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
