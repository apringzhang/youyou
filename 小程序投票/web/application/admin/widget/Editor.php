<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/28
 * Time: 9:57
 */
namespace app\admin\widget;

use think\View;

class Editor
{
    public function show($name, $value, $rows = 100) {
        $data = [
            'name' => $name,
            'value' => $value,
            'rows' => $rows,
        ];
        $view = new View();
        return $view->fetch('editor/show', $data);
    }
}