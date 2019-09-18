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
}