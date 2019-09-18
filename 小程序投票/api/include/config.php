<?php
//配置文件
define('CONFIG', [
    //APPID
    'APPID' => 'wxbca9f8bd6e704988',
    //SECRET
    'SECRET' => 'bf8f9b7291870a05ec6e07e86153f97c',
    //图片上传路径
    'UPLOAD_IMAGE_PATH' => './image/',
    //视频上传路径
    'UPLOAD_VIDEO_PATH' => './video/',
    //音频上传路径
    'UPLOAD_AUDIO_PATH' => './audio/',
    //分页数
    'PAGE_SIZE' => '10',
    //订单前缀
    'ORDER_PREFIX' => 'TS_',
    //回调地址
    'NOTIFY_URL' => 'http://localhost/vote_mp/api/gift/notify.php',
    //红包回调地址
    'RED_NOTIFY_URL' => 'http://localhost/vote_mp/api/red_packet/notify.php',
]);