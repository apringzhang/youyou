<?php
/**
 * Created by PhpStorm.
 * User: XUNUO
 * Date: 2018/2/27
 * Time: 14:44
 */
require '../include/db.php';
require '../include/function.php';
require '../include/config.php';
try {
    error_reporting(0);
    $id = $_GET['id'];
    $appid = $_GET['appid'];
    $activity_id = $_GET['activity_id'];
   // $id = 36;
   // $activity_id = 12;
   // $appid = 'wxbca9f8bd6e704988';
    if (!is_numeric($id)) {
        throw new Exception('id参数错误');
    }
    check_empty($appid, 'appid参数错误');
    check_empty($activity_id, 'activity_id参数错误');
    $sql = "SELECT * FROM `wangluo_activity_sign` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$id]);
    if (!$result) {
        throw new Exception('查询失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    //获取分享海报文件
    $sql = "SELECT * FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('查询失败');
    }
    $activity = $sth->fetch(PDO::FETCH_ASSOC);
    $template_string = file_get_contents('../../web/public/upload/' . $activity['activity_sign_share_image']);
    $template = imagecreatefromstring($template_string);
    $font = realpath('../font/simhei.ttf');
    //用户名
    imagettftext($template, 17, 0, 250, 180, 000000, $font, $row['username']);
    //参赛宣言
    //自动折行
     $str_array = [];
     $row_char_num = 20;
     $row_num = floor(mb_strlen($row['sign_declaration']) / $row_char_num);
     for ($i = 0; $i <= $row_num; ++$i) {
         $str_array[$i] = mb_substr($row['sign_declaration'], $i * $row_char_num, $row_char_num);
     }
     $row_str = '';
     foreach ($str_array as $str) {
         $row_str .= $str . "\n";
     }
     imagettftext($template, 17, 0, 45, 220, 0x000000, $font, $row_str);
    
     $sql = "SELECT * FROM `wangluo_wx_user` WHERE `openid` = ?";
     $sth = $dbh->prepare($sql);
     $result = $sth->execute([$row['sign_openid']]);
     if (!$result) {
         throw new Exception('查询失败');
     }
     $res = $sth->fetch(PDO::FETCH_ASSOC);

    //用户头像
    $cover_string = file_get_contents('../upload/image/' . $row['sign_image']);
    $cover_size = getimagesizefromstring($cover_string);
    $cover = imagecreatefromstring($cover_string);
    imagecopyresampled($template, $cover, 196, 11, 0, 0, 159, 144, $cover_size[0], $cover_size[1]);
    //获取分享二维码
    $access_token = get_access_token($appid);
    $param = [
        'scene' => urlencode($activity_id . '-' . $id),
        'page' => 'pages/detail/index',
    ];
    $qrcode_string = post('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token, json_encode($param));
    $qrcode_size = getimagesizefromstring($qrcode_string);
    $qrcode = imagecreatefromstring($qrcode_string);
    imagecopyresampled($template, $qrcode, 164, 283, 0, 0, 225, 225, $qrcode_size[0], $qrcode_size[1]);
    header('Content-Type: image/png');
    imagepng($template);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}