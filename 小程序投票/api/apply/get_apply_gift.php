<?php
/**
 * 礼物列表页
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/5
 * Time: 11:27
 */

require '../include/db.php';
require '../include/function.php';

try {
    //暂时屏蔽详情礼物分页
	$data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    if (!is_numeric($id)) {
        throw new Exception('id参数错误');
    }
    $appid = $data['appid'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    //分页
    $page = intval($data['page']);
    if (empty($page)) {
        $page = 1;
    }
    $page_size = 10;
    $limit = ($page - 1) * $page_size;
    if ($page > (100 / $page_size)) {
        $list = [];
        echo json_encode([
            'result' => 0,
            'data' => $list,
        ]);
        die;
    }
    // AND `appid` = ?
    $sql = "SELECT `id`,`name`,`headimgurl`,`gift_name`,`gift_num`,`create_time` FROM `wangluo_order` 
WHERE `sign_id` = ? AND `activity_id` = ? AND `order_status` = 2 ORDER BY create_time desc LIMIT {$limit},{$page_size}";
	$sth = $dbh->prepare($sql);
	//,$appid
    $result = $sth->execute([$id,$activity_id]);
    if (!$result) {
        throw new Exception('获取礼物失败');
    }
    $info = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($info as $key => &$value)
    {
        $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        if (empty($value['name'])) {
            $value['name'] = '匿名用户';
        }
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