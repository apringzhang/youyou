<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 15:41
 */

namespace app\admin\controller;

use think\Controller;

class Common extends Controller
{
    protected function _initialize()
    {
        if (empty(session('admin'))) {
            $this->redirect('Open/login');
        }
    }
}