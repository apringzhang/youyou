<?php
/**
 * 列表页
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/5
 * Time: 11:27
 */

require '../include/db.php';
require '../include/function.php';
require '../include/config.php';

try {

    $data = json_decode(file_get_contents('php://input'), true);
    $appid = $data['appid'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    $appid = $dbh->quote("{$appid}");
    $activity_id = $dbh->quote("{$activity_id}");
    //查询
    $name = $data['keyword'];
    // AND `appid` = {$appid}
    $where = "`is_delete` = 0 AND `audit_flag` = 1 AND `activity_id` = {$activity_id}";
    if ($name) {
        if(is_numeric($name))
        {
            $where .= " AND `sign_code` = {$name} ";
        } else {
            $name = $dbh->quote("%{$name}%");
            $where .= " AND `username` LIKE {$name} ";
        }
    }
    //分页
    $page = intval($data['page']);
    if (empty($page)) {
        $page = 1;
    }
    $page_size = CONFIG['PAGE_SIZE'];
    $limit = ($page - 1) * $page_size;
    //排序
    if ($data['rank'] == 1 && empty($data['keyword'])) {
        $sql = "SELECT `vote_bottom`, `vote_rank_bottom` FROM `wangluo_activity` WHERE `id` = $activity_id";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute();
        if (!$result) {
            throw new Exception('活动信息获取失败');
        }
        $activity = $sth->fetch(PDO::FETCH_ASSOC);
        $where .= "AND `total_count` >= ".$activity['vote_bottom'];
        $order = "total_count DESC, sign_code ASC";
        if ($page > ($activity['vote_rank_bottom'] / $page_size)) {
            $list = [];
            echo json_encode([
                'result' => 0,
                'data' => $list,
            ]);
            die;
        }
    } else {
        $order = "sign_code ASC";
    }
    $sql = <<<EOT
SELECT `id`, `username`, `sign_image`, `sign_unit`,`total_count`,`sign_code`,`red_packet`
FROM `wangluo_activity_sign` WHERE  {$where} ORDER BY {$order}
LIMIT {$limit},{$page_size}
EOT;
    $sth = $dbh->prepare($sql);
    $result = $sth->execute();
    if (!$result) {
        throw new Exception('系统繁忙');
    }
    $list = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($list as $key => $value) {
        $list[$key]['sign_unit'] = check_substr($value['sign_unit'], 9);
        $list[$key]['username'] = check_substr($value['username'], 8);
        if ($list[$key]['red_packet'] > 0) {
           $list[$key]['red_status'] = 1;
        } else {
           $list[$key]['red_status'] = 0;
        }
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