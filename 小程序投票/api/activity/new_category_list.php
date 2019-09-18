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
    $sql = "select id,category_name from wangluo_activity_category where status = 0 and is_delete = 0 order by sort asc";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute();
    if (!$result) {
        throw new Exception('配置获取失败');
    }
    $category = $sth->fetchAll(PDO::FETCH_ASSOC);
    $count = count($category)+1;
    $key = 0;
    $array = [];
    for ($i=1; $i < $count; $i++) {
        $array[$key][$i-1] =  $category[$i-1];
        if($i%4 == 0){
           $key = $key+1;
        }
        
    }
    echo json_encode([
        'result' => 0,
        'data' => $array,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}