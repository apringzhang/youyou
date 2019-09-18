<?php
/**
 * 获取礼物列表
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/6
 * Time: 13:11
 */

require '../include/db.php';
require '../include/function.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $appid = $data['appid'];
    check_empty($appid, 'appid参数错误');
    $id = $data['id'];
    check_empty($id, 'id参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    //获取活动表礼物ID
    $sql = "SELECT `gift_ids` FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取礼物信息失败');
    }
    $gift_ids = $sth->fetch(PDO::FETCH_ASSOC);
    check_empty($gift_ids['gift_ids'], '该活动未选择礼物');
    //获取礼物列表
    $sql = "SELECT `id`, `gift_name`, `gift_value`, `gift_image`, `vote_num` FROM `wangluo_gift` WHERE `id` IN(".$gift_ids['gift_ids'].")
AND `is_delete` = 0 ORDER BY `sort` ASC";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute();
    if (!$result) {
        throw new Exception('获取礼物失败');
    }
    $list['gift_list'] = $sth->fetchAll(PDO::FETCH_ASSOC);
    //获取被投票用户信息
    $sql = "SELECT `username`, `sign_image`, `sign_code`, `total_count` FROM `wangluo_activity_sign` WHERE `id`=?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$id]);
    if (!$result) {
        throw new Exception('获取用户信息失败');
    }
    $list['user'] = $sth->fetch(PDO::FETCH_ASSOC);
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