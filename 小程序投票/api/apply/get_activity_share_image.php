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
    $activity_id = $_GET['activity_id'];
    $appid = $_GET['appid'];
   // $activity_id = 1;
   // $appid = 'wxbca9f8bd6e704988';
    check_empty($activity_id, 'activity_id参数错误');
    check_empty($appid, 'appid参数错误');
    $sql = "SELECT * FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('查询失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    $template_string = file_get_contents('../../web/public/upload/' . $row['activity_share_image']);
    $template = imagecreatefromstring($template_string);
    
    $font = realpath('../font/simhei.ttf');
    //活动信息
//    imagettftext($template, 12, 0, 40, 270, 0x000000, $font, $row['apply_count']);
//    imagettftext($template, 12, 0, 170, 270, 0x000000, $font, $row['total_count']);
//    imagettftext($template, 12, 0, 318, 270, 0x000000, $font, $row['visit_count']);
    //获取分享二维码
    $access_token = get_access_token($appid);
    $param = [
        'scene' => urlencode($activity_id),
        'page' => 'pages/index/index',
    ];

    $qrcode_string = post('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token, json_encode($param));
    $qrcode_size = getimagesizefromstring($qrcode_string);

    $qrcode = imagecreatefromstring($qrcode_string);
    
    imagecopyresampled($template, $qrcode, 32, 515, 0, 0, 130, 130, $qrcode_size[0], $qrcode_size[1]);
    header('Content-Type: image/png');
    imagepng($template);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}