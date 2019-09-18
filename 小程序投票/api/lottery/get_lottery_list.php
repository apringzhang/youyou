<?php
/**
 * 获取大转盘中奖纪录列表
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/4/9
 * Time: 9:28
 */
require '../include/db.php';
require '../include/config.php';
require '../include/function.php';

try{
    $data = json_decode(file_get_contents('php://input'), true);
    //测试数据
    /*$data['session_id'] =  '946779ce399d0ac30a16e6f21952700d89f68223';
    $data['activity_id'] = 26;*/
    $raw = check_session_id($data['session_id']);
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    //分页
    $page = intval($data['page']);
    if (empty($page)) {
        $page = 1;
    } else {
        $page = intval($data['page']);
    }
    $page_size = 20;
    $limit = ($page - 1) * $page_size;
    $sql = "select * from `wangluo_user_award` where `activity_id` = ? and `openid` = ?
 order by `create_time` desc LIMIT {$limit},{$page_size}";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id,$raw['openid']]);
    if (!$result) {
        throw new Exception('获取信息失败');
    }
    $list = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($list as $key => $value){
        $sql = "select * from `wangluo_award` where `id` = ? ";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$list[$key]['award_id']]);
        if (!$result) {
            throw new Exception('获取奖项失败');
        }
        $info = $sth->fetch(PDO::FETCH_ASSOC);
        $list[$key]['award_name'] = $info['name'];
        $list[$key]['create_time'] = date('Y-m-d',$list[$key]['create_time']);
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