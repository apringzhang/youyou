<?php
/**
 * 上传音频
 */
require '../include/db.php';
require '../include/function.php';
require '../include/config.php';

try {
    $upload_path = CONFIG['UPLOAD_AUDIO_PATH'];
    $name        = sha1(uniqid()) . '.' . pathinfo($_FILES['audio']['name'])['extension'];
    $tmp_name    = $_FILES['audio']['tmp_name'];
    /*$type        = $_FILES['audio']['type'];
    if ($type != 'video/mp4') {
        throw new Exception('视频格式错误');
    }*/
    if (empty($tmp_name) || !file_exists($tmp_name)) {
        throw new Exception('上传音频失败');
    }
    $save_path = $upload_path . '/' . $name;
    if (!file_exists($save_path)) {
        if (!move_uploaded_file($tmp_name, $save_path)) {
            throw new Exception('创建文件失败');
        }
    }
    echo json_encode([
        'result' => 0,
        'data'   => $name,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result'  => 2,
        'message' => $e->getMessage(),
    ]);
}
