<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/10
 * Time: 14:11
 */
namespace app\api\controller;

use think\Exception;

class SeachList extends Common {

    public function index()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        //参数在model里
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $check_status = $data['check_status'];
        check_empty($check_status, 'check_status参数错误');
        $goods_name = $data['goods_name'];
        check_empty($goods_name, '请输入商品名称');
        $session_id = $data['session_id'];
        $this->session_id_check($session_id);
        $car_status = $data['car_status'];
        check_empty($car_status, 'car_status参数错误');
        $Seach = new \app\api\model\SeachList();
        try {
            $list = $Seach->get_list($company_id, $check_status, $goods_name,$session_id,$car_status);
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
