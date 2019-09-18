<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 15:50
 */

namespace app\admin\controller;

use think\Controller;
use think\Exception;

class Open extends Controller
{
    /**
     * 登录页面
     * @return \think\response\View
     */
    public function login()
    {
        if (session('?admin')) {
            $this->redirect('Index/index');
        }
        return view();
    }

    /**
     * 执行登录
     * @return \think\response\Json
     */
    public function doLogin()
    {
        $userName = input('post.username');
        $userPassword = input('post.password');
        $captcha = input('post.captcha');
        $userModel = new \app\admin\model\User();
        try {
            $userModel->doLogin($userName, $userPassword, $captcha);
        } catch (Exception $e) {
            return ['result' => 2, 'message' => $e->getMessage()];
        }

        return json(['result' => 0, 'message' => '登录成功']);
    }

    public function doLogout()
    {
        session('admin', null);
        $this->redirect('Open/login');
    }
}