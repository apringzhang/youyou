<?php
/**
 * 支付回调
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/7
 * Time: 15:35
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
    $sql = "UPDATE `wangluo_order` SET `transaction_id` = ?, `update_time` = ? ,`order_status` = ? WHERE `order_sn` = ? AND 
`order_status` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$transaction_id, $time, 2, $order_sn, 1]);
    if (!$result) {
        throw new Exception('生成订单失败');
    }
    //查询订单
    $sql = "SELECT `gift_id`,`sign_id`,`activity_id`,`gift_num`,`openid` FROM `wangluo_order` WHERE  `transaction_id` =?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$transaction_id]);
    if (!$result) {
        throw new Exception('查询订单失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    //查询礼物
    $sql = "SELECT `vote_num` FROM `wangluo_gift` WHERE  `id` =?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$row['gift_id']]);
    $gift_id = $row['gift_id'];
    $openid = $row['openid'];
    $activity_id = $row['activity_id'];
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('查询礼物失败');
    }
    $gift_row = $sth->fetch(PDO::FETCH_ASSOC);
    //增加个人投票数
    $row['vote_num'] = $gift_row['vote_num'] * $row['gift_num'];
    $sql = "UPDATE wangluo_activity_sign set gift_num = gift_num+?, gift_count = gift_count+?,total_count=total_count+?,`update_time` = ? 
where id = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$row['gift_num'], $row['vote_num'], $row['vote_num'], $time, $row['sign_id']]);
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('更新数据失败');
    }
    //增加活动投票数
    $sql = "UPDATE wangluo_activity set gift_count = gift_count+?,total_count=total_count+? where id = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$row['vote_num'], $row['vote_num'], $row['activity_id']]);
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('更新数据失败');
    }
    $dbh->commit();
    //更新用户积分
    //do_score($openid,$activity_id,$gift_id);
} catch (Exception $e) {
    echo $e->getMessage();
}

function do_score($openid,$activity_id,$gift_id)
{
    global $dbh;
    check_score($openid,$activity_id);
    //查询礼物赠送积分
    $sql = "select score from wangluo_score_rule where gift_id = ? and activity_id = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$gift_id,$activity_id]);
    if (!$result) {
        throw new Exception('礼物积分失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    $gift_score =$row['score'];
    $sql = "update wangluo_user_score set score = score+? where `openid` = ? and `activity_id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$gift_score,$openid,$activity_id]);
    if (!$result) {
        throw new Exception('积分添加错误');
    }
}