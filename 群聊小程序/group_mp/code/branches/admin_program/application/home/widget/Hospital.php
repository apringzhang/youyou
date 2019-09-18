<?php

/**
* @Author: 玛瑙
* @Date:   2018-07-05 08:33:11
* @Last Modified by:   玛瑙
* @Last Modified time: 2018-07-09 17:47:34
*/
namespace app\admin\widget;

use think\View;

class Hospital {

	/**
     * 选择上级分类树
     * @param int $pid
     * @return string|void
     */
	public function pidListTree($pid = 0, $current = 0)
	{
		$data['list'] = db('hospital_cat')
		->where('is_delete', 0)
		->order('update_time desc')
		->select();
		$data['current'] = $current;
		$view = new View();
		return $view->fetch('hospital/pidlisttree', $data);
	}

}