<?php

/**
 * Created by PhpStorm.
 * User: GYC
 * Date: 2018/8/6
 * Time: 10:56
 */

namespace app\api\controller;

use think\Exception;

class OrderList extends Common {

    /**
     * 唤起支付
     * @throws \Exception
     */
    public function pay() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $row = check_session_id($data['session_id']);
            $openid = $row['openid'];
            $order_id = $data['order_id'];
            check_empty($order_id, 'order_id参数错误');
            //获取订单号
            $order = db('order')->find($order_id);
            $order_number = $order['order_sn'];
            check_empty($order_number, '订单不存在');
            $total_amount = $data['total_amount'];
            if (empty($total_amount)) {
                throw new Exception('价格参数错误');
            }
            $company_id = $data['company_id'];
            //支付验证
            $type = 0;
            if ($order['order_type'] == 1 && $order['distribution'] == 0) {
                $type = 1;
            } else if ($order['order_type'] == 1 && $order['distribution'] == 1) {
                $type = 2;
            } else if ($order['order_type'] == 2) {
                $type = 3;
            } else if ($order['order_type'] == 3) {
                $type = 4;
            }
            $user_id = $row['id'];
            $address_id = db('order')->where('order_id', $order_id)->field('address_id')->find()['address_id'];
            $orderModel = new \app\api\model\Order();
            //验证价格
            $isnot_nots = $order['isnot_nots'];
            $coupon_log = db('coupon_log')->where('order_id', $order_id)->find();
            if (!empty($coupon_log)) {
                $coupon_id = $coupon_log['id'];
            } else {
                $coupon_id = '';
            }
            $integral_log = db('integral_log')->where('order_id', $order_id)->where('type', 1)->find();
            if (!empty($integral_log)) {
                $intergarl = 1;
            } else {
                $intergarl = 0;
            }
            $result_price = $orderModel->confirm($user_id, $type, $company_id, $address_id, 2, $order_id, $isnot_nots, $coupon_id, $intergarl);
            if ($order['total_amount'] != $total_amount) {
                throw new Exception('价格参数错误');
            }
            import('pay.Pay');
            $pay = new \pay\Wxpay();
            //支付成功后返回页面
            $host = input('server.SERVER_PORT') == 443 ? 'https://' : 'http://';
            $url = $host . input('server.HTTP_HOST') . url('Order/notify');
            $return = $pay->payMp($openid, $order_number, $total_amount, $url);
            if (!empty($return)) {
                $prepay_id_array = explode("=", $return['package']);
                //使用返回的prepay_id存入
                $data_or = array(
                    'prepay_id' => $prepay_id_array[1]
                );
                db('order')->where('order_id', $order_id)->update($data_or);
            }
            return json_encode([
                'result' => 0,
                'data' => $return,
            ]);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 充值唤起支付
     * @throws \Exception
     */
    public function cz_pay() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $row = check_session_id($data['session_id']);
            $openid = $row['openid'];
            $order_id = $data['order_id'];
            check_empty($order_id, 'order_id参数错误');
            //获取订单号
            $order = db('order')->find($order_id);
            $order_number = $order['order_sn'];
            check_empty($order_number, '订单不存在');
            //验证价格
            $total_amount = $order['total_amount'];
            import('pay.Pay');
            $pay = new \pay\Wxpay();
            //支付成功后返回页面
            $host = input('server.SERVER_PORT') == 443 ? 'https://' : 'http://';
            $url = $host . input('server.HTTP_HOST') . url('Order/cz_notify');
            $return = $pay->payMp($openid, $order_number, $total_amount, $url);
            if (!empty($return)) {
                $prepay_id_array = explode("=", $return['package']);
                //使用返回的prepay_id存入
                $data_or = array(
                    'prepay_id' => $prepay_id_array[1]
                );
                db('order')->where('order_id', $order_id)->update($data_or);
            }
            return json_encode([
                'result' => 0,
                'data' => $return,
            ]);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
    }

        /**
     * vip续费付款
     * @throws \Exception
     */
    public function vip_pay() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $row = check_session_id($data['session_id']);
            $order_id = $data['order_id'];
            check_empty($order_id, 'order_id参数错误');
            //获取订单号
            $order = db('order')->find($order_id);
            $order_number = $order['order_sn'];
            check_empty($order_number, '订单不存在');
            //验证价格
            $total_amount = $order['total_amount'];
            $stamps = db('wx_user')->where('session_id', $data['session_id'])->value('stamps');
            if (intval($stamps) < intval($order['total_amount'])) {
                return json_encode([
                    'result' => 2,
                    'message' => '点券余额不足',
                ]);
            }

            $torage_value = db('vip')->where("id", $order['torage_id'])->find();
            $user = db('wx_user')->where('id', $order['user_id'])->find();
            $data3 = array(
                'type' => 2,
                'user_id' => $order['user_id'],
                'order_id' => $order['order_id'],
                'vip_id' => $order['torage_id'],
                'create_time' => time()
            );
            $user_torecharge = db('user_torecharge')->insert($data3);
            if (!$user_torecharge) {
                return json_encode([
                    'result' => 2,
                    'message' => '记录增加失败',
                ]);
            }
            

            $data2 = array(
                'stamps' => intval($user['stamps']) - intval($order['total_amount']),
                'update_time' => time()
            );
            $wx_user = db('wx_user')->where('id', $order['user_id'])->update($data2);
            if (!$wx_user) {
                return json_encode([
                    'result' => 2,
                    'message' => '修改用户点券失败',
                ]);
            }
            return json_encode([
                'result' => 0,
                'data' => $return,
            ]);
            $data1 = array(
                'order_status' => 5,
                'pay_amount' => $order['total_amount'],
                'update_time' => time()
            );
            $orderresult = db('order')->where('order_id', $order['order_id'])->update($data1);
            if (!$orderresult) {
                exception("修改订单失败");
            }
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
    }

}
