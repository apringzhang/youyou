<?php

namespace app\api\controller;

use think\Exception;

class Category extends Common {

	/**
	 * 获取所有分类
	 */
	public function get_category() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Category();

		try {
			$list = $model->get_category();
		} catch (Exception $e) {
			return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
		return json_encode([
			'result' => 0,
			'data' => $list,
		]);
	}

	public function get_categorytitle() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Category();

		try {
			$list = $model->get_categorytitle($data['category_id']);
		} catch (Exception $e) {
			return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
		return json_encode([
			'result' => 0,
			'data' => $list,
		]);
	}

	//获取广告位
	public function get_advert() {

		$data = json_decode(file_get_contents('php://input'), true);
		$is_homeshow = $data['is_homeshow'] ?? 0;
		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Category();

		try {
			$list = $model->get_advert($is_homeshow);
		} catch (Exception $e) {
			return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
		return json_encode([
			'result' => 0,
			'data' => $list,
		]);
	}
}