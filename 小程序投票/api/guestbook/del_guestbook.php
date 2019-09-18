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

try{
    $data = json_decode(file_get_contents('php://input'), true);
    //测试数据
    /*$data['session_id'] =  'a2c4f38d53dc8df46cd8c12e7d270bbb3fc69e27';
    $data['activity_id'] = 26;
    $data['sign_id'] = 10;
    $data['id'] = 6;*/
    //接口需要数据
    $raw = check_session_id($data['session_id']);
    check_empty($data['activity_id'], '无报名参数');
    check_empty($data['id'], 'id不能为空');
    check_empty($data['sign_id'], 'sign_id不能为空');
    $time = time();
    $openid = $raw['openid'];
    $sql ="UPDATE `wangluo_guestbook` set `is_delete` = ? , `update_time` = ? where `id` = ? and `activity_id` = ? 
and `openid` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([1,$time,$data['id'],$data['activity_id'],$raw['openid']]);
    if (!$result) {
        throw new Exception('系统繁忙');
    }
    //减少留言数
    $sql ="UPDATE `wangluo_activity_sign` set `message_count` = message_count-1 where `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$data['sign_id']]);
    if (!$result) {
        throw new Exception('更新信息失败');
    }
    echo json_encode([
        'result' => 0,
        'message' => '删除成功',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}