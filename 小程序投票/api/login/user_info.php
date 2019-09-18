<?php
/**
 * 更新保存用户信息
 */
require '../include/db.php';
try {
    $data = json_decode(file_get_contents('php://input'), true);
    $session_id = $data['session_id'];
    if (empty($session_id)) {
        throw new Exception('session_id参数错误');
    }
    $info = $data['info'];
    //验证数据完整性
    $sql = "SELECT `id`, `session_key` FROM `wangluo_wx_user` WHERE `session_id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$session_id]);
    if (!$result) {
        throw new Exception('查询数据失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception('session_id错误');
    }
    $session_key = $row['session_key'];
    $id = $row['id'];
    $sign = sha1($info['rawData'] . $session_key);
    if ($info['signature'] != $sign) {
        throw new Exception('签名错误');
    }
    //修改数据
    $sql = "UPDATE `wangluo_wx_user` SET "
        . "`nick_name` = ?, `avatar_url` = ?, `gender` = ?, `city` = ?, `province` = ?, `country` = ?, `language` = ? "
        . "WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $user_info = $info['userInfo'];
    $result = $sth->execute([
        $user_info['nickName'],
        $user_info['avatarUrl'],
        $user_info['gender'],
        $user_info['city'],
        $user_info['province'],
        $user_info['country'],
        $user_info['language'],
        $id,
    ]);
    if (!$result) {
        throw new Exception('修改数据失败');
    }
    echo json_encode([
        'result' => 0,
        'message' => '修改成功',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}

