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
function account_exist($userAccount,$id='')
{
    $map['id'] = ['neq',$id];
    $map['user_account'] = $userAccount;
    $user = db('user')->where( $map)->where('is_delete',0)->find();
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

function get_article_type($id)
{
    $info = db('article_type')->where('id', $id)->find();
    return $info['name'];
}

function get_Nav_type($type)
{
    if ($type == 1) {
        return "外部链接";
    } else if ($type == 2) {
        return "资讯文章";
    }
}

function get_infor_name($id)
{
    $navTab = db('navigation_toinform')->where('nav_id', $id)->find();
    $info = db('information')->where('id', $navTab['inf_id'])->find();
    return $info['title'];
}

function get_homeshow_name($type)
{
    if ($type == 1) {
        $data = '否';
    } else {
        $data = '是';
    }
    return $data;
}

function get_adminuser_name($id)
{
    $info = db('wx_user')->where('id', $id)->find();
    return $info['nick_name'];
}

function get_sex($gender)
{
    $data = '未知';
    if ($gender == 1) {
        $data = '男';
    }
    if ($gender == 2) {
        $data = '女';
    }
    return $data;
}

function get_user_type($id)
{
    $info = db('wx_user')->where('id', $id)->find();
    if ($info['is_vip'] == 0) {
        $data = '普通用户';
    }
    if ($id == 1) {
        $data = 'VIP用户';
    }
    return $data;
}

function get_vip($id)
{
    if ($id == 0) {
        $data = '普通用户';
    }
    if ($id == 1) {
        $data = 'VIP用户';
    }
    return $data;
}

function get_activity_name($id)
{
    $info = db('activity')->where('id', $id)->find();
    return $info['name'];
}

function get_user_address($id)
{
    $info = db('wx_user')->where('id', $id)->find();
    return $info['address'];
}

function get_gift_name($id)
{
    $info = db('prize')->where('id', $id)->find();
    return $info['name'];
}

function get_state($id)
{
    switch ($id) {
        case 0:
            $return = '未兑换';
            break;
        case 1:
            $return = '待领取';
            break;
        case 2:
            $return = '已领取';
            break;
        default:
            $return = '非法状态';
    }
    return $return;
}

function get_gift_type($id)
{
    $info = db('prize')->where('id', $id)->find();
    switch ($info['type']) {
        case 0:
            $return = '谢谢参与';
            break;
        case 1:
            $return = '礼品奖励';
            break;
        default:
            $return = '非法状态';
    }
    return $return;
}

function get_orderSn($id)
{
    $order = db('order')->where('order_id', $id)->find();
    return $order['order_sn'];
}

function get_type_coupon($id)
{
    $info = db('user_torecharge')->where('id', $id)->find();
    switch ($info['type']) {
        case 1:
            $return_name = '(充值)元';
            break;
        case 2:
            $return_name = '(购买vip)点卷';
            break;
        case 3:
            $return_name = '其他';
            break;
    }
    if($info['type'] == 1)
    {
        $recharge = db('recharge')->where('id',$info['rec_id'])->find();
        $return = $recharge['price'].$return_name;
    }
    if($info['type'] == 2)
    {
        $recharge = db('vip')->where('id',$info['vip_id'])->find();
        $return = $recharge['price'].$return_name;
    }
    if($info['type'] == 3)
    {
        $return = '无';
    }
    return $return;

}

function get_vip_time($id)
{
    $info = db('user_torecharge')->where('id', $id)->find();
    if($info['type'] == 2)
    {
        $vip = db('vip')->where('id',$info['vip_id'])->find();
        $return = $vip['times'].'个月';
    } else {
        $return = '无';
    }
    return $return;

}