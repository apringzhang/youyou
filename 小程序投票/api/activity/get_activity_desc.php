<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/3
 * Time: 15:04
 */

require '../include/db.php';
require '../include/function.php';


/**
 * 个人详情
 * 已测2018年2月5日11:26:25
 */
try {
    $appid = $_GET['appid'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $_GET['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    $sql = "SELECT `activity_desc` FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('活动说明获取失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    echo json_encode([
        'result' => 0,
        'message' => $row['activity_desc'],
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}