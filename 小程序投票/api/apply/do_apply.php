<?php
/**
 * 报名
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/5
 * Time: 14:58
 */

require '../include/db.php';
require '../include/function.php';

try{
    $data = json_decode(file_get_contents('php://input'), true);
    $raw = check_session_id($data['session_id']);
    //判断用户是否已报名
    $sql = "SELECT `id` FROM `wangluo_activity_sign` WHERE `sign_openid` = ? AND is_delete = '0'";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['openid']]);
    if (!$result) {
        throw new Exception('系统繁忙');
    }
    $info = $sth->fetch(PDO::FETCH_ASSOC);
    if (!empty($info['id'])) {
        throw new Exception('仅可报名一次');
    }
    $appid = $data['appid'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    $time = time();
    //获取活动是否审核
    $sql = "SELECT `audit_flag`, `is_sign`, `start_time` ,`stop_time` ,`online_flag` FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取活动失败');
    }
    $activity = $sth->fetch(PDO::FETCH_ASSOC);
    if ($activity['stop_time'] < $time)
    {
        throw new Exception('活动已结束');
    }
    if ($activity['online_flag'] == 0)
    {
        throw new Exception('活动已下线');
    }
    if ($activity['is_sign'] != 1)
    {
        throw new Exception('不支持报名');
    }
    if ($activity['audit_flag'] == 1)
    {
        $audit_flag = 2;
    } else {
        $audit_flag = 1;
    }

    check_empty($data['sign_unit'], '请输入学校名称');
    check_empty($data['sign_class'], '请输入班级名称');
    check_empty($data['username'], '请输入姓名');
    if (!in_array($data['sex'], [1, 2])) {
        throw new Exception('性别参数错误');
    }
    check_empty($data['mobile'], '请输入手机号');
    check_mobile($data['mobile'], '手机号格式错误');
    check_empty($data['sign_introduce'], '请输入故事名称');
    check_empty($data['sign_declaration'], '请输入参赛宣言');
    check_empty($data['sign_image'], '请上传封面图');
    check_empty(json_decode($data['sign_introduce_image'], true), '请上传风采图');
    check_empty($data['sign_audio'], '请上传音频');
    //获取编号
    $sql = "SELECT `sign_code` FROM `wangluo_activity_sign` WHERE `activity_id` = ? ORDER BY `sign_code` DESC LIMIT 1";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取信息失败');
    }
    $activity_sign = $sth->fetch(PDO::FETCH_ASSOC);
    $sign_code = $activity_sign['sign_code'] + 1;
    $sql = "INSERT INTO `wangluo_activity_sign` SET `username` = ?, `sex` = ?, `mobile` = ?,`sign_openid` = ?,
`sign_unit` = ?, `sign_class` = ?,`sign_image` = ?, `sign_declaration` = ?, `sign_introduce` = ?,`sign_introduce_image` = ?,
`create_time` = ?, `update_time` = ?, `sign_code` = ?, `audit_flag` = ? , `activity_id` = ?, `appid` = ? ,`is_lock` = ?,
`sign_video` = ?,`sign_audio` = ?,`sign_duration` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([
        $data['username'],
        $data['sex'],
        $data['mobile'],
        $raw['openid'],
        $data['sign_unit'],
        $data['sign_class'],
        $data['sign_image'],
        $data['sign_declaration'],
        $data['sign_introduce'],
        $data['sign_introduce_image'],
        $time,
        $time,
        $sign_code,
        $audit_flag,
        $activity_id,
        $appid,
        0,
        $data['sign_video'],
        $data['sign_audio'],
        $data['sign_duration']
    ]);
    if (!$result) {
        throw new Exception('系统繁忙');
    }
    //增加报名数
    $sql ="UPDATE wangluo_activity set `apply_count` = apply_count+1 where `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('更新信息失败');
    }
    echo json_encode([
        'result' => 0,
        'message' => '报名成功',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}