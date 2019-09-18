<?php

/**
 * 添加留言信息
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/6
 * Time: 10:13
 */
require '../include/db.php';
require '../include/function.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    //测试数据
    /* $data['session_id'] =  'cf6222ab815f7a48961cab275294b9623318804a';
      $data['activity_id'] = 26;
      $data['sign_id'] = 10;
      $data['content'] = '留言测试信息'; */
    //接口需要数据
    $raw = check_session_id($data['session_id']);
    check_empty($data['activity_id'], '无活动参数');
    check_empty($data['sign_id'], '无报名参数');
    check_empty($data['content'], '内容不能为空');
    $time = time();
    //获取活动是否审核
    $sql = "SELECT `audit_flag`, `is_sign`, `start_time` ,`stop_time` ,`online_flag` FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$data['activity_id']]);
    if (!$result) {
        throw new Exception('获取活动失败');
    }
    $activity = $sth->fetch(PDO::FETCH_ASSOC);
    if ($activity['start_time'] > $time) {
        throw new Exception('活动未开始');
    }
    if ($activity['stop_time'] < $time) {
        throw new Exception('活动已结束');
    }
    if ($activity['online_flag'] == 0) {
        throw new Exception('活动已下线');
    }
    $time = time();
    $openid = $raw['openid'];
    $sql = "INSERT INTO `wangluo_guestbook` SET `activity_id` = ?, `sign_id` = ?, `openid` = ?,`content` = ?,
`create_time` = ?, `update_time` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$data['activity_id'], $data['sign_id'], $openid, $data['content'], $time, $time,]);
    if (!$result) {
        throw new Exception('系统繁忙');
    }
    //增加报名数
    $sql = "UPDATE `wangluo_activity_sign` set `message_count` = message_count+1 where `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$data['sign_id']]);
    if (!$result) {
        throw new Exception('更新信息失败');
    }
    echo json_encode([
        'result' => 0,
        'message' => '留言成功',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}