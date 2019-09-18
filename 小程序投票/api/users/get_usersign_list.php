<?php
/**
 * 获取用户报名信息列表
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/6
 * Time: 10:13
 */

require '../include/db.php';
require '../include/function.php';
require '../include/config.php';


try{
    $data = json_decode(file_get_contents('php://input'), true);
    //测试数据
    /*$data['session_id'] =  'a2c4f38d53dc8df46cd8c12e7d270bbb3fc69e27';
    $data['activity_id'] =  25;*/
    check_empty($data['activity_id'], '参数错误');
    $raw = check_session_id($data['session_id']);
    //分页
    $page = intval($data['page']);
    if (empty($page)) {
        $page = 1;
    } else {
        $page = intval($data['page']);
    }
    $page_size = CONFIG['PAGE_SIZE'];
    $limit = ($page - 1) * $page_size;
    $sql = "SELECT `id`,`username`,`sign_image` ,`create_time` FROM `wangluo_activity_sign` WHERE
 `sign_openid` = ? AND `activity_id` = ? AND `is_delete` = 0 ORDER BY create_time desc
LIMIT {$limit},{$page_size}";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['openid'], $data['activity_id']]);
    $list =  $sth->fetchAll(PDO::FETCH_ASSOC);
    if (!$result) {
        throw new Exception('获取列表失败');
    }
    foreach ($list as $key => $value) {
        $list[$key]['create_time'] = date('Y-m-d H:i:s',$list[$key]['create_time']);
    }
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