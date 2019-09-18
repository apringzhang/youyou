<?php

/**
 * Created by PhpStorm.
 * User: GYC
 * Date: 2018/8/6
 * Time: 11:16
 */

namespace app\api\model;

class Order {
    
    /**
     */
    public function getroomtype($company_id) {
        $list = db('company_config')->where('company_id', $company_id)->where('c_type', 1)->where('is_delete', 0)->select();
        return $list;
    }

    /**
     * 判断收货地址范围
     */
    public function getaddfw($company_id, $address_id) {
        //配送费
        $address = db('address')->where('id', $address_id)->find();
        $company = db('company')->where("id", $company_id)->find();
        if (!empty($address)) {
            $takeout_distance_price = $company['takeout_distance_price'];
            $psfkm = getdistance($company['longitude'], $company['latitude'], $address['longitude'], $address['latitude'], 2);
            $takeout_distance = $company['takeout_distance']; //外卖配送距离
            if ($psfkm > $takeout_distance) {
                exception("超过商家配送范围");
            } else {
                return 0;
            }
        } else {
            exception("参数错误");
        }
    }

    

    /**
     * 充值生成订单
     * @param type $transaction_id 
     * @param type $order_sn 
     */
    public function do_czorder($user_id, $total_amount, $torage_id, $prom_id) {
        $order_sn = config('order_prefix') . date('YmdHi') . sprintf('%04d', mt_rand(0, 9999));
        $data = array(
            'create_time' => time(),
            'update_time' => time(),
            'order_sn' => $order_sn,
            'user_id' => $user_id,
            'order_type' => 1,
            'prom_id' => $prom_id,
            'total_amount' => $total_amount,
            'torage_id' => $torage_id,
        );
        $order_id = db('order')->insertGetId($data);
        if (!$order_id) {
            exception("提交订单失败");
        }
        return $order_id;
    }

    /**
     * 充值回调
     * @param type $transaction_id 
     * @param type $order_sn 
     */
    public function paycznotify($transaction_id, $order_sn) {
        $order = db('order')->where("order_sn", $order_sn)->find();
        // $flow_number = 'cz' . date('YmdHi') . sprintf('%04d', mt_rand(0, 9999));
        $torage_value = db('recharge')->where("id", $order['torage_id'])->find();
        $user = db('wx_user')->where('id', $order['user_id'])->find();
        $data = array(
            'type' => 1,
            'user_id' => $order['user_id'],
            'order_id' => $order['order_id'],
            'rec_id' => $order['torage_id'],
            'create_time' => time()
        );
        $user_torecharge = db('user_torecharge')->insert($data);
        if (!$user_torecharge) {
            exception("记录增加失败");
        }
        $data1 = array(
            'trade_sn' => $transaction_id,
            'order_status' => 5,
            'pay_amount' => $order['total_amount'],
            'pay_type' => 1,
            'update_time' => time()
        );
        $orderresult = db('order')->where('order_id', $order['order_id'])->update($data1);
        if (!$orderresult) {
            exception("修改订单失败");
        }

        $data2 = array(
            'stamps' => $user['stamps'] + $torage_value['stamps'],
            'update_time' => time()
        );
        $wx_user = db('wx_user')->where('id', $order['user_id'])->update($data2);
        if (!$wx_user) {
            exception("修改用户点券失败");
        }
    }

    /**
     * vip续费生成订单
     * @param type $transaction_id 
     * @param type $order_sn 
     */
    public function vip_doorder($user_id, $total_amount, $torage_id, $prom_id) {
        $order_sn = config('order_prefix') . date('YmdHi') . sprintf('%04d', mt_rand(0, 9999));
        $data = array(
            'create_time' => time(),
            'update_time' => time(),
            'order_sn' => $order_sn,
            'order_type' => 2,
            'user_id' => $user_id,
            'prom_id' => $prom_id,
            'total_amount' => $total_amount,
            'torage_id' => $torage_id,
        );
        $order_id = db('order')->insertGetId($data);
        if (!$order_id) {
            exception("提交订单失败");
        }
        return $order_id;
    }

}
