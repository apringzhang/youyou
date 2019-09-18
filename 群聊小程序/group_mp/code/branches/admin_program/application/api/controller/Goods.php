<?php

/**
 * Created by PhpStorm.
 * User: GYC
 * Date: 2018/8/6
 * Time: 10:56
 */

namespace app\api\controller;

use think\Exception;

class Goods extends Common {

    public function addcart() {
        $data = json_decode(file_get_contents('php://input'), true);
        $row = $this->session_id_check($data['session_id']);
        $goods_id = $data['goods_id'];
//        check_empty($goods_id, 'goods_id参数错误');
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $type = $data['carttype'];
        check_empty($type, 'carttype参数错误');
        $user_id = $row['id'];
        $goods_num = $data['goods_num'];
//        check_empty($goods_num, 'goods_num参数错误');
        $session_id = $data['session_id'];
        $spec_goods_id = $data['spec_goods_id'];
//        $user_id = 13;
//        $company_id = 1;
//        $type = 1;
        $Goods = new \app\api\model\Goods();
        try {
            $list = $Goods->addCart($user_id, $company_id, $goods_id, $goods_num, $session_id, $type, $spec_goods_id);
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

    public function delcart() {
        $data = json_decode(file_get_contents('php://input'), true);
        $row = $this->session_id_check($data['session_id']);
        $user_id = $row['id'];
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $type = $data['carttype'];
        check_empty($type, 'carttype参数错误');
        $Goods = new \app\api\model\Goods();
//        $user_id=1;
//        $company_id=1;
//        $type=1;
        try {
            $Goods->delCart($user_id, $company_id, $type);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => '删除成功',
        ]);
    }

}
