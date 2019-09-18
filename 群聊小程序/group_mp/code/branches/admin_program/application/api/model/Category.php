<?php

namespace app\api\model;

class Category {

	/**
	 * 获取文章分类
	 */
	public function get_category() {

		$list = db('article_type')
		->order('sort')
		->select();
		return $list;
	}

	public function get_categorytitle($category_id) {

		$list = db('article')
		->where('type', $category_id)
		->order('sort')
		->select();
		return $list;
	}

	public function get_advert($is_homeshow) {

		$list = db('advertisement')
		->where('is_delete', 0)
		->where('is_homeshow', $is_homeshow)
		->order('sort')
		->select();
		return $list;
	}
}