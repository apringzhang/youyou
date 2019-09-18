<?php
/**
 * 获取拉票用户红包列表及报名信息
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
    /*$data['session_id'] =  '5c2d97cb0cea717f70d00ccc4e5e710726391e56';*/
    check_empty($data['activity_id'], '活动参数错误');
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
    $sql = "SELECT `id`,`sign_id`,`amount`,`create_time` FROM `wangluo_red_packet` WHERE `openid` = ? AND 
`order_status` = 1 AND `activity_id` = ? ORDER BY create_time desc
LIMIT {$limit},{$page_size}";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['openid'],$data['activity_id']]);
    if (!$result) {
        throw new Exception('获取信息失败');
    }
    $list =  $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($list as $key => $value) {
        $sql = "SELECT `username`,`sign_image` FROM `wangluo_activity_sign` WHERE `id` = {$list[$key]['sign_id']}";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute();
        if (!$result) {
            throw new Exception('获取报名失败');
        }
        $info =  $sth->fetch(PDO::FETCH_ASSOC);   
        $list[$key]['username'] = $info['username'];
        $list[$key]['sign_image'] = $info['sign_image'];
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