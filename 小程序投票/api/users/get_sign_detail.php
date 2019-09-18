<?php
/**
 * 获取用户报名信息详情
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
    /*$data['session_id'] =  '5c2d97cb0cea717f70d00ccc4e5e710726391e56';*/
    $raw = check_session_id($data['session_id']);
    $sql = "SELECT `id`, `username`, `sign_image`, `sex`, `sign_unit`, `sign_class`, `mobile`, `sign_declaration`, `sign_introduce`, `sign_video`, `sign_introduce_image`, `sign_openid`, `sign_audio`, `sign_duration` FROM `wangluo_activity_sign` WHERE `sign_openid` = ? AND `activity_id` = ? AND `id` = ? AND `is_delete` = 0";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['openid'], $data['activity_id'], $data['sign_id']]);
    $list =  $sth->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        throw new Exception('获取列表失败');
    }
    echo json_encode([
        'result' => 0,
        'data' => $list,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}