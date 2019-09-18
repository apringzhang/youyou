<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/26
 * Time: 11:17
 */
/**
 * 根据角色ID获取角色名
 * @param $roleId
 * @return mixed
 */
function get_role_name($roleId)
{
    return db('role')->where('id', $roleId)->find()['role_name'];
}

/**
 * 判断帐号是否已存在
 * @param $userAccount
 * @return bool
 */
function account_exist($userAccount)
{
    $user = db('user')->where('user_account', $userAccount)->find();
    if ($user) {
        return true;
    } else {
        return false;
    }
}

/**
 * 根据节点ID检查权限
 * @param $nodeId
 * @param $roleId
 * @return bool
 */
function check_id($nodeId, $roleId)
{
    $access = db('access')
        ->where('node_id', $nodeId)
        ->where('role_id', $roleId)
        ->find();
    if ($access) {
        return true;
    } else {
        return false;
    }
}