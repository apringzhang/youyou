<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 16:24
 */

namespace app\admin\model;

/**
 * 用户模型
 * Class User
 * @package app\admin\model
 */
class Role
{
    /**
     * 获取列表
     * @param $pageNum
     * @param $numPerPage
     * @param $roleName
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pageNum, $numPerPage, $roleName)
    {
        $list = db('role')
            ->where('is_delete', 0)
            ->where(function ($query) use ($roleName) {
                if (!empty($roleName)) {
                    $query->whereOr('role_name', 'like', "%{$roleName}%");
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('sort, create_time')
            ->select();
        return $list;
    }

    /**
     * 获取总数
     * @param $roleName
     * @return int|string
     */
    public function getCount($roleName)
    {
        $count = db('role')
            ->where('is_delete', 0)
            ->where(function ($query) use ($roleName) {
                if (!empty($roleName)) {
                    $query->whereOr('role_name', 'like', "%{$roleName}%");
                }
            })
            ->count();
        return $count;
    }

    /**
     * 执行添加
     * @param $roleName
     * @param $sort
     */
    public function doAdd($roleName, $sort)
    {
        $result = db('role')->insert([
            'role_name' => $roleName,
            'sort' => $sort,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        if (!$result) {
            exception('添加失败');
        }
    }

    /**
     * 执行修改
     * @param $roleId
     * @param $roleName
     * @param $sort
     */
    public function doModify($roleId, $roleName, $sort)
    {
        $data = [
            'role_name' => $roleName,
            'sort' => $sort,
            'update_time' => time(),
        ];
        $result = db('role')
            ->where('id', $roleId)
            ->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    /**
     * 执行删除
     * @param $roleId
     */
    public function doDelete($roleId)
    {
        $result = db('role')
            ->where('id', $roleId)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        if (!$result) {
            exception('删除失败');
        }
    }

    /**
     * 获取全部角色
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getAllList()
    {
        return db('role')
            ->where('is_delete', 0)
            ->order('sort, create_time')
            ->select();
    }

    /**
     * 执行修改权限
     * @param $roleId
     * @param $nodeIdArray
     */
    public function doModifyAccess($roleId, $nodeIdArray)
    {
        if (!$nodeIdArray) {
            $nodeIdArray = [];
        }
        //取已存在的权限列表
        $accessList = db('access')
            ->where('role_id', $roleId)
            ->select();
        $accessNodeIdArray = [];
        foreach ($accessList as $access) {
            $accessNodeIdArray[] = $access['node_id'];
        }
        //添加新权限
        foreach ($nodeIdArray as $nodeId) {
            if (!in_array($nodeId, $accessNodeIdArray)) {
                db('access')->insert([
                    'role_id' => $roleId,
                    'node_id' => $nodeId,
                ]);
            }
        }
        //删除权限
        foreach ($accessNodeIdArray as $accessNodeId) {
            if (!in_array($accessNodeId, $nodeIdArray)) {
                db('access')
                    ->where('role_id', $roleId)
                    ->where('node_id', $accessNodeId)
                    ->delete();
            }
        }
    }

    /**根据角色查找是否有用户
     * @param $roleId
     */
    public function getUserList($roleId){
        $list = db('admin_user')
            ->where('is_delete',0)
            ->where('role_id',$roleId)
            ->select();
        return $list;
    }
}