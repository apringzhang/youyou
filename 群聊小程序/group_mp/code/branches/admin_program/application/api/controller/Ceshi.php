<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/10
 * Time: 19:35
 */

namespace app\api\controller;

use think\Exception;

class Ceshi extends Common
{
	public function index()
	{
		import('dui.Dui');
        $pay = new \dui\Dui();
        $data = $pay->printHtmlContent(1,2);
	}
}