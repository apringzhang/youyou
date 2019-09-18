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
    $data = json_decode(file_get_contents('php://input'), true);
    $appid = $data['appid'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    $adp_code = $data['adp_code'];
    check_empty($adp_code, 'adp_code参数错误');
    //获取广告位
    $sql = "SELECT `id` FROM `wangluo_ad_position` WHERE `adp_code` = ? AND `is_delete` = 0";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$adp_code]);
    if (!$result) {
        throw new Exception('获取广告位失败');
    }
    $ad_position = $sth->fetch(PDO::FETCH_ASSOC);
    //获取广告AND `activity_id` =? $activity_id
    $adp_id = $ad_position['id'];
    $sql = "SELECT `id` ,`ad_image`, `ad_linkcontent`, `ad_introduce` FROM `wangluo_ad` WHERE `adp_id` = ? AND `appid` = ? AND `activity_id` = ? AND `is_delete` = 0 
ORDER BY sort ASC";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$adp_id, $appid, $activity_id]);
    $info = $sth->fetchAll(PDO::FETCH_ASSOC);
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