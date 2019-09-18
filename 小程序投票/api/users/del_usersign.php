<?php
/**
 * 删除用户报名列表
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
    $data['id'] = 2;*/
    $row = check_session_id($data['session_id']);
    $time = time();  
    $sql ="UPDATE `wangluo_activity_sign` set `is_delete` = 1 ,update_time= ? where `id` = ? and `openid` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$time,$data['id'],$row['openid']]);
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