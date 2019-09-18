<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/4/12
 * Time: 10:15
 */
require '../include/db.php';
require '../include/function.php';
try {
    $data = json_decode(file_get_contents('php://input'), true);
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    $sql = "SELECT `max_red_packet`,`red_packet_rule_image` FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('规则获取失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    echo json_encode([
        'result' => 0,
        'data' => $row,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}