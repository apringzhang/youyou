<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 14:49
 */
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
//域名
define('SITE_URL', 'http://localhost/vote_mp/web/public');
return [
    'app_debug' => true,
    //小程序上传图片地址
    'mp_upload_url' => 'http://localhost/vote_mp/api/upload/image.php',
    //小程序上传图片前缀
    'mp_image_url' => 'http://localhost/vote_mp/api/upload/image/',
    //小程序上传视频地址
    'mp_upload_video_url' => 'http://localhost/vote_mp/api/upload/video.php',
    //小程序上传音频地址
    'mp_upload_audio_url' => 'http://localhost/vote_mp/api/upload/audio.php',
    //小程序上传视频前缀
    'mp_video_url' => 'http://localhost/vote_mp/api/upload/video/',
    //小程序上传音频前缀
    'mp_audio_url' => 'http://localhost/vote_mp/api/upload/audio/',
    //小程序上传音频本地目录
    'mp_audio_dir' => '../../api/upload/audio/',
    //接口AES加密KEY
    'aes_key' => 'D8yZvupMj9u5o4WyD3jbATOMtHvWeQaI',
    //APPID
    'APPID' => 'wxbca9f8bd6e704988',
    //SECRET
    'SECRET' => 'bf8f9b7291870a05ec6e07e86153f97c',
    //站点配置
    'site_url' => SITE_URL,
    'static_url' => SITE_URL . '/static',
    'image_url' => SITE_URL . '/upload',
    //验证码
    'captcha' => [
        'codeSet' => '0123456789',
        'useCurve' => false,
        'length' => 4,
        'reset' => true
    ],
    //标签库
    'template' => [
        'taglib_build_in' => 'cx,app\common\taglib\Access',
    ],
    'test' => 123,
];