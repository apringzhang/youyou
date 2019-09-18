<?php
/**
 * 获取openid及session_token并返回session_id
 */
require '../include/db.php';
require '../include/function.php';
require '../include/config.php';
try {

    $code = $_GET['code'];
    $appid = $_GET['appid'];
    $activity_id = $_GET['activity_id'];
    //code换取seession_key
    $param = [
        'appid' => CONFIG['APPID'],
        'secret' => CONFIG['SECRET'],
        'js_code' => $_GET['code'],
        'grant_type' => 'authorization_code',
    ];
    $data = json_decode(get('https://api.weixin.qq.com/sns/jscode2session', $param), true);
    //创建本地数据
    $session_key = $data['session_key'];
    $openid = $data['openid'];
    if (empty($session_key) || empty($openid)) {
        throw new Exception('接口调用失败');
    }
    $session_id = sha1(uniqid());

    //查询是否已有数据
    $sql = "SELECT * FROM `wangluo_wx_user` WHERE `openid` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$openid]);
    if (!$result) {
        throw new Exception('查询数据失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    $time = time();
    if (!$row) {
        //插入数据
        $sql = "INSERT INTO `wangluo_wx_user` SET `appid` = ?, `openid` = ?, `session_key` = ?, `session_id` = ?, `create_time` = ?, `update_time` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$appid, $openid, $session_key, $session_id, $time, $time]);
        if (!$result) {
            throw new Exception('插入数据失败');
        }
    } else {
        //更新数据
        $sql = "UPDATE `wangluo_wx_user` SET `session_key` = ?, `session_id` = ?, `update_time` = ? WHERE `id` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$session_key, $session_id, $time, $row['id']]);
        if (!$result) {
            throw new Exception('修改数据失败');
        }
    }
    //增加活动访问量
    $sql = "UPDATE `wangluo_activity` SET visit_count = visit_count+? where id = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([3, $activity_id]);
    echo json_encode([
        'result' => 0,
        'session_id' => $session_id,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}