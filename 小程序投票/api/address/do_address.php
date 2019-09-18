<?php
/**
 * 添加收货用户地址
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
    /*$data['session_id'] =  '5c2d97cb0cea717f70d00ccc4e5e710726391e56';
    $data['address'] = '地球左转1';*/
    $raw = check_session_id($data['session_id']);
    $time = time();  
    check_empty($data['address'], '地址参数错误');
    $address = $dbh->quote($data['address']);
    $sql ="UPDATE `wangluo_wx_user` set `address` = {$address} ,update_time= ? where `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$time,$raw['id']]);
    if (!$result) {
        throw new Exception('更新信息失败');
    }
    echo json_encode([
        'result' => 0,
        'message' => '更新成功',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}