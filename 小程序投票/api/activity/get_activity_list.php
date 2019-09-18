<?php
/**
 * 获取活动信息
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/3/5
 * Time: 10:13
 */

require '../include/db.php';
require '../include/function.php';

try{
    $sql = "SELECT `id`,`activity_name` FROM `wangluo_activity` WHERE `online_flag` = ? AND `is_delete` = ? ORDER BY sort ASC";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([1,0]);
    if (!$result) {
        throw new Exception('配置获取失败');
    }
    $row = $sth->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        'result' => 0,
        'data' => $row,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}