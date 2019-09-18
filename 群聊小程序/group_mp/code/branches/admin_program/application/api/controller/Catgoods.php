<?php

namespace app\api\controller;

use think\Exception;

class Catgoods extends Common {

	/**
	 * 获取分类下商品
	 */
	public function get_catgoods() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Catgoods();

		try {
			$list = $model->get_catgoods($data['cat_id'], $data['company_id'], $data['dian'], $data['session_id']);
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

	public function get_prom() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Catgoods();

		try {
			$list = $model->get_prom($data['company_id'], $data['dian']);
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

	public function goods_start() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Catgoods();

		try {
			$list = $model->get_start($data['company_id']);
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

	public function address() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Catgoods();

		try {
			$info = $model->get_address($data['id']);
		} catch (Exception $e) {
			return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
		return json_encode([
			'result' => 0,
			'data' => $info,
		]);
	}

	public function specgoods() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Catgoods();

		try {
			$list = $model->specgoods($data['goods_id'], $data['session_id'], $data['dian'], $data['company_id']);
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

	public function specgoodsinfo() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Catgoods();

		try {
			$info = $model->specgoodsinfo($data['goods_id'], $data['session_id'], $data['dian'], $data['company_id'], $data['spec_id']);
		} catch (Exception $e) {
			return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
		return json_encode([
			'result' => 0,
			'data' => $info,
		]);
	}

	public function get_prom_list() {

		$data = json_decode(file_get_contents('php://input'), true);

		//check_empty($data['session_id'], 'session_id参数错误');

		$model = new \app\api\model\Catgoods();

		try {
			$list = $model->get_prom_list($data['company_id'], $data['dian']);
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

	public function get_tableid() {

		$data = json_decode(file_get_contents('php://input'), true);

		try {
			$table_sn = db('tables')->where('id', $data['id'])->where('company_id', $data['company_id'])->where('is_delete', 0)->value('table_sn');
		} catch (Exception $e) {
			return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
		if ($table_sn) {
			return json_encode([
				'result' => 0,
				'data' => $table_sn,
			]);
		} else {
			return json_encode([
				'result' => 2,
				'message' => '请扫描正确的桌台二维码',
			]);
		}
		
	}
}