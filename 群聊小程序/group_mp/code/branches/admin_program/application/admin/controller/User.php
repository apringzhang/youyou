<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/26
 * Time: 10:15
 */

namespace app\admin\controller;

use think\Exception;

/**
 * 用户控制器
 * @package app\admin\controller
 */
class User extends Common
{
    /**
     * 修改密码
     * @return \think\response\View
     */
    public function modifyPassword()
    {
        return view();
    }

    /**
     * 执行修改密码
     * @return \think\response\Json
     */
    public function doModifyPassword()
    {
        $password = input('post.password');
        $newPassword = input('post.newPassword');
        $userModel = new \app\admin\model\User();
        try {
            $userModel->doModifyPassword(session('admin.id'), $password, $newPassword);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => '',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 用户列表
     * @return \think\response\View
     */
    public function index()
    {
        $data = [];
        //搜索
        $userName = input('post.userName');
        $data['userName'] = $userName;
        $userAccount = input('post.userAccount');
        $data['userAccount'] = $userAccount;
        $roleId = input('post.roleId');
        $data['roleId'] = $roleId;

        //页码
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $userModel = new \app\admin\model\User();
        //列表数据
        $list = $userModel->getList($pageNum, $numPerPage, $userName, $userAccount, $roleId);
        $data['list'] = $list;
        //数据总数
        $count = $userModel->getCount($userName, $userAccount, $roleId);
        $data['count'] = $count;
        $roleModel = new \app\admin\model\Role();
        $data['roleList'] = $roleModel->getAllList();
        return view('', $data);
    }

    /**
     * 添加
     * @return \think\response\View
     */
    public function add()
    {
        $data = [];
        $roleModel = new \app\admin\model\Role();
        $data['roleList'] = $roleModel->getAllList();
        return view('', $data);
    }

    /**
     * 执行添加
     * @return \think\response\Json
     */
    public function doAdd()
    {
        $userName = input('post.userName');
        $userAccount = input('post.userAccount');
        $userPassword = input('post.userPassword');
        $roleId = input('post.roleId');
        $userModel = new \app\admin\model\User();
        try {
            $userModel->doAdd($userName, $userAccount, $userPassword, $roleId);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'userManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 修改
     * @return \think\response\View
     */
    public function modify()
    {
        $data = db('user')->where('id', input('get.id'))->find();
        $roleModel = new \app\admin\model\Role();
        $data['roleList'] = $roleModel->getAllList();
        return view('', $data);
    }

    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doModify()
    {
        $userId = input('post.userId');
        $userName = input('post.userName');
        $userAccount = input('post.userAccount');
        $userPassword = input('post.userPassword');
        $roleId = input('post.roleId');
        $userModel = new \app\admin\model\User();
        try {
            $userModel->doModify($userId, $userName, $userAccount, $userPassword, $roleId);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'userManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete()
    {
        $userId = input('get.id');
        $userModel = new \app\admin\model\User();
        try {
            $userModel->doDelete($userId);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'userManage',
        ]);
    }
}