<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/4/10
 * Time: 11:17
 */

require '../include/db.php';
require '../include/function.php';
require '../sdk/config.php';
require '../sdk/WxPay.Data.php';
require '../sdk/WxPay.Api.php';
require '../sdk/WxPay.Exception.php';
require '../sdk/WxPay.Notify.php';
require '../sdk/NotifyCallback.php';

try {
    $notify = new PayNotifyCallBack();
    $notify->Handle(false);
    $data = $notify->getData();
    $transaction_id = '';
    $order_sn = '';
    if ($data['result_code'] == 'SUCCESS' && $data['return_code'] == 'SUCCESS') {
        $transaction_id = $data['transaction_id'];
        $order_sn = $data['out_trade_no'];
    } else {
        die;
    }
    $dbh->beginTransaction();
    $time = time();
    //修改订单状态
    $sql = "UPDATE `wangluo_red_packet` SET `transaction_id` = ?, `update_time` = ? ,`order_status` = ? WHERE `order_sn` = ? AND 
`order_status` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$transaction_id, $time, 1, $order_sn, 0]);
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('修改订单失败');
    }
    //查询订单
    $sql = "SELECT `sign_id`,`amount` FROM `wangluo_red_packet` WHERE  `transaction_id` =?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$transaction_id]);
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('查询订单失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    //增加个人红包
    $sql = "UPDATE wangluo_activity_sign set red_packet = red_packet+? where id = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$row['amount'],$row['sign_id']]);
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('更新数据失败');
    }
    $dbh->commit();
} catch (Exception $e) {
    echo $e->getMessage();
}
