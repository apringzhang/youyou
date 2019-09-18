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
        $userEmail = input('post.userEmail');
        $roleId = input('post.roleId');
        $userModel = new \app\admin\model\User();
        try {
            $userModel->doAdd($userName, $userAccount, $userPassword, $roleId, $userEmail);
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
        $data = db('admin_user')->where('id', input('get.id'))->find();
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
        $userEmail = input('post.userEmail');
        $roleId = input('post.roleId');
        $userModel = new \app\admin\model\User();
        try {
            $userModel->doModify($userId, $userName, $userAccount, $userPassword, $roleId, $userEmail);
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

    /**
     * 微信用户列表
     * @return \think\response\View
     */
    public function weixin() {
        $data = [];
        //搜索
        $nick_name = input('request.nick_name');
        $data['nick_name'] = $nick_name;
        //页码
        $pageNum = 1;
        if (is_numeric(input('request.pageNum'))) {
            $pageNum = input('request.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $userModel = new \app\admin\model\User();
        //列表数据
        $list = $userModel->getWeixin($pageNum, $numPerPage, $nick_name);
        $data['list'] = $list;
        //数据总数
        $count = $userModel->getWeixinCount($nick_name);
        $data['count'] = $count;
        return view('', $data);
    }

    /**
     * 设置开发者
     * @return \think\response\View
     */
    public function detail()
    {
        $data['is_developer'] = db('wx_user')->where('id', input('get.id'))->value('is_developer');
        return view('', $data);
    }

    /**
     * 设置开发者
     * @return \think\response\View
     */
    public function doDetail()
    {
        $is_developer = input('post.is_developer');
        $data = [
            'is_developer' => $is_developer,
            'update_time' => time()
        ];
        db('wx_user')->where('id', input('post.id'))->update($data);
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'weixinManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 微信用户列表
     * @return \think\response\View
     */
    public function useraward() {
        $data = [];
        //搜索
        $openid = input('request.openid');
        $data['openid'] = $openid;
        //页码
        $pageNum = 1;
        if (is_numeric(input('request.pageNum'))) {
            $pageNum = input('request.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $userModel = new \app\admin\model\User();
        //列表数据
        $list = $userModel->getUserAward($pageNum, $numPerPage, $openid);
        if ($list) {
            foreach ($list as &$value) {
                $value['activity_name'] = db('activity')->where('id', $value['activity_id'])->value('activity_name');
                $value['award_name'] = db('award')->where('id', $value['award_id'])->value('name');
            }
        }
        //dump($list);
        $data['list'] = $list;
        //数据总数
        $count = $userModel->getUserAwardCount($openid);
        $data['count'] = $count;
        return view('', $data);
    }

    /**
     * 确认奖品管理
     * @return \think\response\View
     */
    public function confirm()
    {
        $id = input('get.id');
        $data['confirm_time'] = time();

        db('user_award')->where('id', $id)->update($data);
        return json([
            'statusCode' => 200,
            'message' => '确认成功',
            'navTabId' => 'userawardManage',
        ]);
    }

}