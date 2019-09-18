<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 16:43
 */

namespace app\admin\widget;

use think\View;

class Upload
{
    /**
     * 上传图片Widget
     * @param $name
     * @param $value
     * @throws \Exception
     * @return string
     */
    public function image($name, $value)
    {
        $data = [
            'name' => $name,
            'value' => $value,
        ];
        $view = new View();
        return $view->fetch('upload/image', $data);
    }

    /**
     * 小程序上传图片Widget
     * @param $name
     * @param $value
     * @throws \Exception
     * @return string
     */
    public function mpImage($name, $value)
    {
        $data = [
            'name' => $name,
            'value' => $value,
        ];
        $view = new View();
        return $view->fetch('upload/mpImage', $data);
    }

    /**
     * 小程序上传视频Widget
     * @param $name
     * @param $value
     * @throws \Exception
     * @return string
     */
    public function mpVideo($name, $value)
    {
        $data = [
            'name' => $name,
            'value' => $value,
        ];
        $view = new View();
        return $view->fetch('upload/mpVideo', $data);
    }

     /**
     * 小程序上传音频Widget
     * @param $name
     * @param $value
     * @throws \Exception
     * @return string
     */
    public function mpAudio($name, $value)
    {
        $data = [
            'name' => $name,
            'value' => $value,
        ];
        $view = new View();
        return $view->fetch('upload/mpAudio', $data);
    }
    /**
     * 小程序多图片上传Widget
     * @param string $name 上传空间名
     * @throws \Exception
     * @return string
     */
    public function mpMultiImage($name, $value)
    {
        $data = [
            'name' => $name,
            'value' => $value,
            'list' => json_decode($value, true),
        ];
        $view = new View();
        return $view->fetch('upload/mpMultiImage', $data);
    }
}