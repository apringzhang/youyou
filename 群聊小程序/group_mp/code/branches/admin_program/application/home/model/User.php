<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 16:24
 */

namespace app\admin\model;

use think\Validate;

/**
 * 用户模型
 * Class User
 * @package app\admin\model
 */
class User
{
    /**
     * 执行登录
     * @param $userName
     * @param $userPassword
     * @param $captcha
     */
    public function doLogin($userName, $userPassword, $captcha)
    {
        //验证
        $rule = [
            'user_account' => 'require',
            'user_password' => 'require',
            'captcha' => 'require|captcha',
        ];
        $data = [
            'user_account' => $userName,
            'user_password' => $userPassword,
            'captcha' => $captcha,
        ];
        $msg = [
            'user_account.require' => '用户名不能为空',
            'user_password.require' => '密码不能为空',
            'captcha.require' => '验证码不能为空',
            'captcha.captcha' => '验证码错误',
        ];
        $validate = new Validate($rule, $msg);


        if (!$validate->check($data)) {
            exception($validate->getError());
        }

        $user = db('user')
            ->where('user_account', $userName)
            ->where('user_password', sha1($userPassword))
            ->where('is_delete', 0)
            ->find();

        if (!$user) {
            exception('用户名或密码错误');
        }
        session('admin', $user);
    }

    /**
     * 执行修改密码
     * @param $userId
     * @param $password
     * @param $newPassword
     */
    public function doModifyPassword($userId, $password, $newPassword)
    {
        $user = db('user')
            ->where('id', $userId)
            ->where('user_password', sha1($password))
            ->find();
        if (!$user) {
            exception('密码错误');
        }
        $result = db('user')
            ->where('id', $userId)
            ->update([
                'user_password' => sha1($newPassword),
            ]);
        if (!$result) {
            exception('修改失败');
        }
    }

    /**
     * 获取列表
     * @param $pageNum
     * @param $numPerPage
     * @param $userName
     * @param $userAccount
     * @param $roleId
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getList($pageNum, $numPerPage, $userName, $userAccount, $roleId)
    {
        $list = db('user')
            ->where('is_delete', 0)
            ->where(function ($query) use ($userName, $userAccount, $roleId) {
                if (!empty($userName)) {
                    $query->whereOr('user_name', 'like', "%{$userName}%");
                }
                if (!empty($userAccount)) {
                    $query->whereOr('user_account', 'like', "%{$userAccount}%");
                }
                if (!empty($roleId) && is_numeric($roleId)) {
                    $query->where('role_id', $roleId);
                }
            })
            ->page($pageNum, $numPerPage)
            ->order('create_time desc')
            ->select();
        return $list;
    }

    /**
     * 获取总数
     * @param $userName
     * @param $userAccount
     * @param $roleId
     * @return int|string
     */
    public function getCount($userName, $userAccount, $roleId)
    {
        $count = db('user')
            ->where('is_delete', 0)
            ->where(function ($query) use ($userName, $userAccount, $roleId) {
                if (!empty($userName)) {
                    $query->whereOr('user_name', 'like', "%{$userName}%");
                }
                if (!empty($userAccount)) {
                    $query->whereOr('user_account', 'like', "%{$userAccount}%");
                }
                if (!empty($roleId) && is_numeric($roleId)) {
                    $query->where('role_id', $roleId);
                }
            })
            ->count();
        return $count;
    }

    /**
     * 执行添加
     * @param $userName
     * @param $userAccount
     * @param $userPassword
     * @param $roleId
     */
    public function doAdd($userName, $userAccount, $userPassword, $roleId)
    {
        if (account_exist($userAccount)) {
            exception('帐号已存在');
        }
        $result = db('user')->insert([
            'user_name' => $userName,
            'user_account' => $userAccount,
            'user_password' => sha1($userPassword),
            'role_id' => $roleId,
            'create_time' => time(),
            'update_time' => time(),
        ]);
        if (!$result) {
            exception('添加失败');
        }
    }

    /**
     * 执行修改
     * @param $userId
     * @param $userName
     * @param $userAccount
     * @param $userPassword
     * @param $roleId
     */
    public function doModify($userId, $userName, $userAccount, $userPassword, $roleId)
    {
        if (account_exist($userAccount)) {
            exception('帐号已存在');
        }
        $data = [
            'user_name' => $userName,
            'user_account' => $userAccount,
            'role_id' => $roleId,
            'update_time' => time(),
        ];
        if (!empty($userPassword)) {
            $data['user_password'] = sha1($userPassword);
        }
        $result = db('user')
            ->where('id', $userId)
            ->update($data);
        if (!$result) {
            exception('修改失败');
        }
    }

    /**
     * 执行删除
     * @param $userId
     */
    public function doDelete($userId)
    {
        $result = db('user')
            ->where('id', $userId)
            ->update([
                'is_delete' => 1,
                'update_time' => time(),
            ]);
        if (!$result) {
            exception('删除失败');
        }
    }
}