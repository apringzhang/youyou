<?php
/**
 * 生成订单
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/6
 * Time: 13:59
 */

require '../include/db.php';
require '../include/function.php';
require '../include/config.php';
require '../sdk/config.php';
require '../sdk/WxPay.Data.php';
require '../sdk/WxPay.Api.php';
require '../sdk/WxPay.Exception.php';

try {
    $data  = json_decode(file_get_contents('php://input'), true);
    $row   = check_session_id($data['session_id']);
    $appid = $data['appid'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    //判断投票是否结束
    $time   = time();
    $sql    = "SELECT `start_time`, `stop_time`, `activity_type`, `is_gift`,`online_flag` ,`order_prefix` FROM `wangluo_activity` WHERE `id` = ?";
    $sth    = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取活动失败');
    }
    $activity_item = $sth->fetch(PDO::FETCH_ASSOC);
    if ($activity_item['start_time'] > $time) {
        throw new Exception('活动未开始');
    }
    if ($activity_item['stop_time'] < $time) {
        throw new Exception('活动已结束');
    }
    if ($activity_item['is_gift'] != 1) {
        throw new Exception('不支持送礼物');
    }
    if ($activity_item['online_flag'] == 0) {
        throw new Exception('活动已下线');
    }
    if($activity_item['order_prefix'])
    {
        $order_sn      = CONFIG['ORDER_PREFIX'] . $activity_item['order_prefix'] . '_' . date('YmdHis') . mt_rand();
    } else {
        $order_sn      = CONFIG['ORDER_PREFIX'] . date('YmdHis') . mt_rand();
    }
    //生成订单
    $order_status  = 1;
    $activity_type = $activity_item['activity_type'];
    $gift_id       = $data['gift_id'];
    $sign_id       = $data['sign_id'];
    $openid        = $row['openid'];
    $name          = $row['nick_name'];
    $headimgurl    = $row['avatar_url'];
    $gift_num      = $data['gift_num'];
    //获取礼物价格、礼物名称
    $sql    = "SELECT `gift_value`,`gift_name` FROM `wangluo_gift` WHERE `id` = ?";
    $sth    = $dbh->prepare($sql);
    $result = $sth->execute([$gift_id]);
    if (!$result) {
        throw new Exception('礼物获取失败');
    }
    $gift_row     = $sth->fetch(PDO::FETCH_ASSOC);
    $total_amount = $gift_row['gift_value'] * $gift_num;
    $gift_name    = $gift_row['gift_name'];
    //开发人员模式
    if ($row['is_developer'] == 1) {
        $total_amount = 0.01;
    }
    //添加订单表
    $sql = "INSERT INTO `wangluo_order` SET `appid` = ?,`activity_type` = ?,`activity_id` = ?, `openid` = ?, `gift_id` = ?,
`gift_name` = ?, `sign_id` = ?, `gift_num` = ?, `total_amount` = ?, `order_sn` = ?, `order_status` = ?,
 `name` = ?,`headimgurl` = ?,`create_time` = ?,`update_time` = ?";
    $sth    = $dbh->prepare($sql);
    $result = $sth->execute([$appid, $activity_type, $activity_id, $openid, $gift_id, $gift_name, $sign_id, $gift_num
        , $total_amount, $order_sn, $order_status, $name, $headimgurl, $time, $time]);
    if (!$result) {
        throw new Exception('生成订单失败');
    }
    $data = pay($order_sn, $total_amount, CONFIG['NOTIFY_URL'], $openid, $activity_id);
    echo json_encode([
        'result' => 0,
        'data'   => $data,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result'  => 2,
        'message' => $e->getMessage(),
    ]);
}

/**
 * 小程序支付
 * @param string $order_number 订单号
 * @param float $total_amount 总金额(0.00)
 * @param string $notify_url 回调地址
 * @param string $openid openid
 * @param int $activity_id 活动ID
 * @return array
 * @throws Exception
 */
function pay($order_number, $total_amount, $notify_url, $openid, $activity_id)
{
    //获取活动支付信息
    global $dbh;
    $sql    = "SELECT `pay_appid`, `pay_mchid`, `pay_key`, `pay_appsecret`, `pay_body` FROM `wangluo_activity` WHERE `id` = ?";
    $sth    = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取参数失败');
    }
    $row                    = $sth->fetch(PDO::FETCH_ASSOC);
    WxPayConfig::$BODY      = $row['pay_body'];
    WxPayConfig::$DETAIL    = $row['pay_body'];
    // WxPayConfig::$APPID     = $row['pay_appid'];
    // WxPayConfig::$MCHID     = $row['pay_mchid'];
    // WxPayConfig::$KEY       = $row['pay_key'];
    // WxPayConfig::$APPSECRET = $row['pay_appsecret'];

    $data     = new \WxPayUnifiedOrder();
    $nonceStr = \WxPayApi::getNonceStr();
    $data->SetNonce_str($nonceStr);
    $data->SetBody(WxPayConfig::$BODY);
    $data->SetDetail(WxPayConfig::$DETAIL);
    $data->SetOut_trade_no($order_number);
    $data->SetTotal_fee(intval($total_amount * 100));
    $data->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);
    $data->SetNotify_url($notify_url);
    $data->SetTrade_type('JSAPI');
    $data->SetOpenid($openid);
    $data->SetSign();
    $result = \WxPayApi::unifiedOrder($data);
    if ($result['return_code'] != 'SUCCESS') {
        throw new \Exception($result['return_msg']);
    }
    if ($result['result_code'] != 'SUCCESS') {
        throw new \Exception($result['err_code_des']);
    }
    $nonceStr = \WxPayApi::getNonceStr();
    $return   = array(
        'appId'     => $result['appid'],
        'timeStamp' => time(),
        'nonceStr'  => $nonceStr,
        'package'   => 'prepay_id=' . $result['prepay_id'],
        'signType'  => 'MD5',
    );
    //计算签名
    ksort($return);
    $sign              = http_build_query($return);
    $sign              = urldecode($sign);
    $sign              = $sign . "&key=" . WxPayConfig::$KEY;
    $sign              = md5($sign);
    $sign              = strtoupper($sign);
    $return['paySign'] = $sign;
    return $return;
}
