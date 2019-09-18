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
    $id = $data['id'];
    if (!is_numeric($id)) {
        throw new Exception('id参数错误');
    }
    $appid = $data['appid'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    // AND `appid` = ?
    $sql = "SELECT `id`, `username`, `sign_image`, `sign_video`, `sign_declaration`, `sign_code`, `sign_unit`,`sign_audio`,`sign_duration`,
 `total_count` ,`sign_introduce`, `sign_introduce_image` ,`is_lock` ,`gift_num` ,`message_count` FROM `wangluo_activity_sign`
  WHERE `id` = ? AND `is_delete` = 0 AND `audit_flag` = 1 AND `activity_id` = ?";
    $sth = $dbh->prepare($sql);
    //,$appid
    $result = $sth->execute([$id,$activity_id]);
    if (!$result) {
        throw new Exception('请求失败');
    }
    $info = $sth->fetch(PDO::FETCH_ASSOC);
    //差上一名多少票
    $sql = "SELECT `total_count` FROM `wangluo_activity_sign` WHERE `total_count` > ? ORDER BY `total_count` ASC LIMIT 1";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$info['total_count']]);
    if (!$result) {
        throw new Exception('上一名票数失败');
    }
    $last_row = $sth->fetch(PDO::FETCH_ASSOC);
    if ($last_row)
    {
        $info['prev_vote'] = $last_row['total_count'] - $info['total_count'];
    } else {
        $info['prev_vote'] = 0;
    }
    //当前排名
    $sql = "SELECT COUNT(*) FROM `wangluo_activity_sign` WHERE  `total_count` > ?  ORDER BY `total_count`";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$info['total_count']]);
    if (!$result) {
        throw new Exception('获取排名失败');
    }
    $count = $sth->fetch(PDO::FETCH_NUM)[0];
    $info['rank'] = $count + 1;
    //获取礼物列表并获得信息
    // AND `appid` = ?
    $sql = "SELECT `id`,`name`,`headimgurl`,`gift_name`,`gift_num`,`create_time` FROM `wangluo_order` 
WHERE `sign_id` = ? AND `activity_id` = ? AND `order_status` = 2 ORDER BY create_time desc LIMIT 10";
    $sth = $dbh->prepare($sql);
    //,$appid
    $result = $sth->execute([$info['id'],$activity_id]);
    if (!$result) {
        throw new Exception('获取礼物失败');
    }
    $gift_order_list = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($gift_order_list as $key => &$value)
    {
        $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        if (empty($value['name'])) {
            $value['name'] = '匿名用户';
        }
    }
    $info['gift_list'] = $gift_order_list;
    //查询
    $sql = "SELECT `is_gift` FROM `wangluo_activity` WHERE `id` = ? ";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取活动失败');
    }
    $activity = $sth->fetch(PDO::FETCH_ASSOC);
    $info['is_gift'] = $activity['is_gift'];
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