<?php
/**
 * 获取收货用户地址
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
    $sql = "SELECT `address` FROM `wangluo_wx_user` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['id']]);
    $info =  $sth->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        throw new Exception('获取信息失败');
    }
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