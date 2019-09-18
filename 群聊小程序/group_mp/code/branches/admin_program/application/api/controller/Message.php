<?php

namespace app\api\controller;

use think\Exception;
use think\Loader;

class Message extends Common {

	/**
	 * 聊天记录
	 */
	public function get_message() {

		$data = json_decode(file_get_contents('php://input'), true);

		$model = Loader::model('Message');
		$data['g_id'] = 6;
		$data['dates'] = '2019-05-20';
		try {
			$list = $model->get_message($data['g_id'], $data['dates']);
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

	//收藏
	public function collection() {

		$data = json_decode(file_get_contents('php://input'), true);
		$uid = $this->session_id_check($data['session_id']);
		$model = Loader::model('Message');
		try {
			$list = $model->collection($uid, $data['touid'],$data['groups_id']);
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

	//删除收藏
	public function del_collection() {

		$data = json_decode(file_get_contents('php://input'), true);
		$uid = $this->session_id_check($data['session_id']);
		$model = Loader::model('Message');
		try {
			$list = $model->del_collection($uid, $data['touid'], $data['gid']);
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

	//用户是否被收藏
	public function collection_yn() {

		$data = json_decode(file_get_contents('php://input'), true);
		$uid = $this->session_id_check($data['session_id']);
		try {
			$res = db('user_collection')->where('g_from_userid', $uid)->where('group_id', $data['gid'])->where('g_to_userid', $data['touid'])->find();
			if ($res) {
				$res = 1;
			} else {
				$res = 2;
			}
		} catch (Exception $e) {
			return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
		return json_encode([
			'result' => 0,
			'data' => $res,
		]);
	}

	//我的收藏
	public function my_collection() {

		$data = json_decode(file_get_contents('php://input'), true);
		$uid = $this->session_id_check($data['session_id']);
		$model = Loader::model('Message');
		try {
			$list = $model->my_collection($uid);
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

	//奖品列表
	public function prize_list() {

		$data = json_decode(file_get_contents('php://input'), true);
		$uid = $this->session_id_check($data['session_id']);
		$model = Loader::model('Message');
		try {
			$list = $model->prize_list($uid);
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

	public function toprize() {

		$data = json_decode(file_get_contents('php://input'), true);
		$uid = $this->session_id_check($data['session_id']);
		$model = Loader::model('Message');
		try {
			$list = $model->toprize($uid, $data['id'], $data['address']);
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

	public function add_toprize() {

		$data = json_decode(file_get_contents('php://input'), true);
		$uid = $this->session_id_check($data['session_id']);
		$model = Loader::model('Message');
		try {
			$list = $model->add_toprize($uid, $data['ac_id'], $data['pr_id']);
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

	public function vip_list() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $list = db('vip')
                    ->where("is_delete", 0)
                    ->select();
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => $list,
        ]);
    }

    public function torecharge() {
        $data = json_decode(file_get_contents('php://input'), true);
        $uid = $this->session_id_check($data['session_id']);
        try {
            $list = db('user_torecharge')
            		->alias('t')
            		->field('FROM_UNIXTIME(t.create_time) create_time, t.id, t.user_id, t.type, t.vip_id, t.rec_id, t.order_id, r.price')
            		->join('recharge r', 't.rec_id = r.id')
            		->where('t.type', 1)
                    ->where("t.user_id", $uid)
                    ->select();
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => $list,
        ]);
    }
}