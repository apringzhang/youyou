<?php

/**
 * 获取活动信息
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/6
 * Time: 10:13
 */
require '../include/db.php';
require '../include/function.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $appid = $data['appid'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    $sql = "SELECT `activity_notice`,`activity_name`,`activity_image`,`apply_start_time`, `apply_stop_time`, `start_time`,`stop_time`,`activity_type`,`theme_color`,`check_color`,
    `pay_background_image`,`activity_desc`,`receive_side`,`apply_count`,`total_count`,`visit_count` FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('配置获取失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    $row['apply_start_timestamp'] = $row['apply_start_time'];
    $row['apply_stop_timestamp'] = $row['apply_stop_time'];
    $row['apply_start_time'] = date('Y-m-d H:i:s', $row['apply_start_time']);
    $row['apply_stop_time'] = date('Y-m-d H:i:s', $row['apply_stop_time']);
    $row['start_timestamp'] = $row['start_time'];
    $row['stop_timestamp'] = $row['stop_time'];
    $row['start_time'] = date('Y-m-d H:i:s', $row['start_time']);
    $row['stop_time'] = date('Y-m-d H:i:s', $row['stop_time']);

    $row['now_timestamp'] = time();
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