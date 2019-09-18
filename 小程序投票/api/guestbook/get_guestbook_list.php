<?php
/**
 * 获取留言信息
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
    $data['activity_id'] = 26;
    $data['sign_id'] = 10;*/

    //接口需要数据activity_id、sign_id/session_id、page
    check_empty($data['activity_id'], '无活动参数');
    $data['activity_id'] = $dbh->quote($data['activity_id']);
    $where = "`is_delete` = 0 AND `activity_id` = {$data['activity_id']}";
    if (!empty($data['sign_id'])) {
    	$sign_id = $dbh->quote($data['sign_id']);
        $where .= " AND `sign_id` = {$sign_id} ";
    }
    if (!empty($data['session_id'])) {
    	$raw = check_session_id($data['session_id']);
        $where .= " AND `openid` = '{$raw['openid']}' ";
    }
    $page = intval($data['page']);
    //分页
    if (empty($page)) {
        $page = 1;
    } else {
    	$page = intval($data['page']);
    }
    $page_size = CONFIG['PAGE_SIZE'];
    $limit = ($page - 1) * $page_size;
	$sql = "SELECT `id`,`openid`,`content`,`create_time`,`sign_id` FROM `wangluo_guestbook` WHERE {$where} ORDER BY create_time desc
LIMIT {$limit},{$page_size}";
	$sth = $dbh->prepare($sql);
	$result = $sth->execute();
	if (!$result) {
        throw new Exception('列表获取失败');
    }
    $list = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($list as $key => $value) {
        $sql = "SELECT `nick_name`,`avatar_url` FROM `wangluo_wx_user` WHERE `openid` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$list[$key]['openid']]);
        if (!$result) {
            throw new Exception('用户获取失败');
        }
        $users = $sth->fetch(PDO::FETCH_ASSOC);
		$list[$key]['nick_name'] = $users['nick_name'];
		$list[$key]['avatar_url'] = $users['avatar_url'];
		$list[$key]['create_time'] = date('Y-m-d H:i:s',$list[$key]['create_time']);
		unset($list[$key]['openid']);
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