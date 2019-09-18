<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 9:41
 */

namespace app\admin\controller;

use think\Exception;

class Role extends Common
{
    /**
     * 列表
     * @return \think\response\View
     */
    public function index()
    {
        $data = [];
        //搜索
        $roleName = input('post.roleName');
        $data['roleName'] = $roleName;
        //页码
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $roleModel = new \app\admin\model\Role();
        //列表数据
        $list = $roleModel->getList($pageNum, $numPerPage, $roleName);
        $data['list'] = $list;
        //数据总数
        $count = $roleModel->getCount($roleName);
        $data['count'] = $count;
        return view('', $data);
    }


    /**
     * 添加
     * @return \think\response\View
     */
    public function add()
    {
        $data = [];
        return view('', $data);
    }

    /**
     * 执行添加
     * @return \think\response\Json
     */
    public function doAdd()
    {
        $roleName = input('post.roleName');
        $sort = input('post.sort');
        $roleModel = new \app\admin\model\Role();
        try {
            $roleModel->doAdd($roleName, $sort);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'roleManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 修改
     * @return \think\response\View
     */
    public function modify()
    {
        $data = db('role')->where('id', input('get.id'))->find();
        return view('', $data);
    }

    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doModify()
    {
        $roleId = input('post.roleId');
        $roleName = input('post.roleName');
        $sort = input('post.sort');
        $roleModel = new \app\admin\model\Role();
        try {
            $roleModel->doModify($roleId, $roleName, $sort);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'roleManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete()
    {
        $roleId = input('get.id');
        $roleModel = new \app\admin\model\Role();
        //根据角色查找是否有用户
        $list = $roleModel->getUserList($roleId);
        if(!empty($list)){
            return json([
                'statusCode' => 300,
                'message' => "该角色下有用户，删除失败！",
            ]);
        }
        try {
            $roleModel->doDelete($roleId);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'roleManage',
        ]);
    }

    /**
     * 修改权限
     * @return \think\response\View
     */
    public function modifyaccess()
    {
        return view();
    }

    /**
     * 执行修改权限
     * @return \think\response\Json
     */
    public function doModifyAccess()
    {
        $roleId = input('post.roleId');
        $nodeIdArray = input('post.nodeIdArray/a');
        $roleModel = new \app\admin\model\Role();
        try {
            $roleModel->doModifyAccess($roleId, $nodeIdArray);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改权限成功',
            'navTabId' => 'roleManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    public function count_list()
    {
        $data = db('count_list')->where('id', 1)->find();
        return view('', $data);
    }
}