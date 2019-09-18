<?php
/**
 * 上传图片
 */
require '../include/db.php';
require '../include/function.php';
require '../include/config.php';

try {
    $upload_path = CONFIG['UPLOAD_IMAGE_PATH'];
    $tmp_name = $_FILES['image']['tmp_name'];
    if (empty($tmp_name) || !file_exists($tmp_name)) {
        throw new Exception('上传图片错误');
    }
    $image = file_get_contents($_FILES['image']['tmp_name']);
    $info = getimagesizefromstring($image);
    //判断图片大小
    if (empty($info['bits'])) {
        throw new Exception('图片大小错误');
    }
    //判断图片格式
    if (!in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
        throw new Exception('图片格式错误');
    }
    //计算图片SHA1
    $sha1 = sha1($image);
    $save_path = $upload_path . '/' . $sha1 . '.jpg';
    if (!file_exists($save_path)) {
        $image_resource = imagecreatefromstring($image);
        //获取图片宽高
        $image_width = $info[0];
        $image_height = $info[1];
        //创建缩略图
        $width = 800;
        $height = $image_height * ($width / $image_width);
        $thumb = imagecreatetruecolor($width, $height);
        $color = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $color);
        //复制图片
        imagecopyresampled($thumb, $image_resource, 0, 0, 0, 0, $width, $height, $image_width, $image_height);
        imagejpeg($thumb, $save_path);
        imagedestroy($thumb);
        imagedestroy($image_resource);
    }
    echo json_encode([
        'result' => 0,
        'data' => $sha1 . '.jpg',
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}