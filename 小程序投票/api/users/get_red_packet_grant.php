<?php
/**
 * 用户拉票红包领取列表
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
    /*$data['session_id'] =  '5c2d97cb0cea717f70d00ccc4e5e710726391e56';
    $data['sign_id'] = 1;
    $data['activity_id'] = 26;*/
    check_empty($data['sign_id'], '参数错误');
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
    $sql = "SELECT `id`,`sign_id`,`amount`,`action`,`openid`,`create_time` FROM `wangluo_user_red_packet` 
WHERE `sign_id` = ? AND `activity_id` = ? ORDER BY create_time desc
LIMIT {$limit},{$page_size}";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$data['sign_id'],$data['activity_id']]);
    if (!$result) {
        throw new Exception('获取信息失败');
    }
    $list =  $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($list as $key => $value) {
        if ($list[$key]['action'] == 1) {
            $list[$key]['action_type'] = '投票';
        } else {
            $list[$key]['action_type'] = '礼物';
        }
        $sql = "SELECT `nick_name`,`avatar_url` FROM `wangluo_wx_user` WHERE `openid` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$list[$key]['openid']]);
        if (!$result) {
            throw new Exception('获取用户失败');
        }
        $info =  $sth->fetch(PDO::FETCH_ASSOC);
        $list[$key]['nick_name'] = $info['nick_name'];
        $list[$key]['avatar_url'] = $info['avatar_url'];
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