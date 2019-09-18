<?php
/**
 * 获取用户信息
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
    /*$data['session_id'] =  '509d4adb9d46be8b5759f1a6461ff9f8916fb1cf';
    $data['activity_id'] =  27;*/
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    $raw = check_session_id($data['session_id']);
    check_score($raw['openid'],$activity_id);
    $sql = "SELECT `avatar_url`,`nick_name`,`red_packet` FROM `wangluo_wx_user` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['id']]);
    $info =  $sth->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        throw new Exception('获取信息失败');
    }
    //获取积分
    $sql = "SELECT `score` FROM `wangluo_user_score` WHERE `openid` = ? and `activity_id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['openid'],$activity_id]);
    $score =  $sth->fetch(PDO::FETCH_ASSOC);
    $info['score'] = $score['score'];
    echo json_encode([
        'result' => 0,
        'data' => $info,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}