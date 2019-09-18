<?php

/**
 * Created by PhpStorm.
 * User: GYC
 * Date: 2018/8/6
 * Time: 11:16
 */

namespace app\api\model;

class OrderList {

    public function getOrderList($user_id, $type, $page, $page_size, $company_id) {
        if ($type == 1 || $type == 2 || $type == 3) {//1点餐
            $list = db('order')
                    ->where("user_id", $user_id)
                    ->where("company_id", $company_id)
                    ->where("is_delete", 0)
                    ->where(function ($query) use ($type) {
                        if (!empty($type)) {
                            $query->where('order_type', $type);
                        }
                    })
                    ->order("create_time desc")
                    ->page($page, $page_size)
                    ->select();
            if (!empty($list)) {
                foreach ($list as $key => &$value) {
                    //增加取消状态 2018年8月11日14:00:54
                    //可以取消0 不可以取消1
                    if (in_array($value['order_status'], [0, 1])) {
                        $value['cancel_status'] = 1;
                    } else {
                        $value['cancel_status'] = 0;
                    }
                    //订单状态0待付款、1待接单、2待配送、3已配送、4已签收、5已完成、6已取消、7商家拒单、8待到店、9服务中
                    switch ($value['order_status']) {
                        case 0:
                            $value['order_status_name'] = '待付款';
                            break;
                        case 1:
                            $value['order_status_name'] = '待接单';
                            break;
                        case 2:
                            $value['order_status_name'] = '待配送';
                            break;
                        case 3:
                            $value['order_status_name'] = '已配送';
                            break;
                        case 4:
                            $value['order_status_name'] = '已签收';
                            break;
                        case 5:
                            $value['order_status_name'] = '已完成';
                            break;
                        case 6:
                            $value['order_status_name'] = '已取消';
                            break;
                        case 7:
                            $value['order_status_name'] = '商家拒单';
                            break;
                        case 8:
                            $value['order_status_name'] = '待到店';
                            break;
                        case 9:
                            $value['order_status_name'] = '服务中';
                            break;
                        case 10:
                            $value['order_status_name'] = '待下单';
                            break;
                        case 11:
                            $value['order_status_name'] = '已下单';
                            break;
                        default:
                            break;
                    }
                    switch ($value['distribution']) {//0到店就餐、1到店取餐、2商家自配送、4达达配送'
                        case 0:
                            $value['distribution_name'] = '到店就餐';
                            break;
                        case 1:
                            $value['distribution_name'] = '到店取餐';
                            break;
                        case 2:
                            $value['distribution_name'] = '商家自配送';
                            break;
                        case 4:
                            $value['distribution_name'] = '达达配送';
                            break;
                        default:
                            break;
                    }
                    switch ($value['pay_status']) {//0未支付，1全额已支付，2定金已支付',
                        case 0:
                            $value['pay_status_name'] = '未支付';
                            break;
                        case 1:
                            $value['pay_status_name'] = '全额已支付';
                            break;
                        case 2:
                            $value['pay_status_name'] = '定金已支付';
                            break;
                        default:
                            break;
                    }
                    $order_goods = db('order_goods')->where('order_id', $value['order_id'])->select();
                    $company = db('company')->where('id', $value['company_id'])->find();
                    $list[$key]['company'] = $company['company_name'];
                    $list[$key]['goods_list'] = $order_goods;
                    $value['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
                    $value['book_time'] = date("Y-m-d H:i:s", $value['book_time']);
                    $coupon_log = db('coupon_log')->where('order_id', $value['order_id'])->find();
                    if (!empty($coupon_log)) {
                        if ($coupon_log['end_time'] < time()) {
                            $value['is_guoqi'] = 1;
                        } else {
                            $value['is_guoqi'] = 0;
                        }
                    } else {
                        $value['is_guoqi'] = 2;
                    }
                }
            }
        }
        if ($type == 4) {//排队
            $list = db('line_order')
                    ->where("user_id", $user_id)
                    ->where("company_id", $company_id)
                    ->order("create_time desc")
                    ->page($page, $page_size)
                    ->select();
            if (!empty($list)) {
                foreach ($list as $key => &$value) {
                    switch ($value['order_status']) {//1排队中、2已取消、3已完成、4已过号',
                        case 1:
                            $value['order_status_name'] = '排队中';
                            break;
                        case 2:
                            $value['order_status_name'] = '已取消';
                            break;
                        case 3:
                            $value['order_status_name'] = '已完成';
                            break;
                        case 4:
                            $value['order_status_name'] = '已过号';
                            break;
                        default:
                            break;
                    }
                    $company = db('company')->where('id', $value['company_id'])->find();
                    $list[$key]['company'] = $company['company_name'];
                    $value['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
                    //根据商家id获取商家是否可以排号信息
                    $company_config = db('company')->where('id', $value['company_id'])->find()['queue_switch'];
                    $value['queue_switch'] = $company_config;
                }
            }
        }

        return $list;
    }

    public function getOrderDetail($order_id, $type) {
        if ($type == 1) {
            $list = db('order')->where("order_id", $order_id)->find();
            //可以取消0 不可以取消1
            if (in_array($list['order_status'], [0, 1])) {
                $list['cancel_status'] = 1;
            } else {
                $list['cancel_status'] = 0;
            }
            switch ($list['order_status']) {
                case 0:
                    $list['order_status_name'] = '待付款';
                    break;
                case 1:
                    $list['order_status_name'] = '待接单';
                    break;
                case 2:
                    $list['order_status_name'] = '待配送';
                    break;
                case 3:
                    $list['order_status_name'] = '已配送';
                    break;
                case 4:
                    $list['order_status_name'] = '已签收';
                    break;
                case 5:
                    $list['order_status_name'] = '已完成';
                    break;
                case 6:
                    $list['order_status_name'] = '已取消';
                    break;
                case 7:
                    $list['order_status_name'] = '商家拒单';
                    break;
                case 8:
                    $list['order_status_name'] = '待到店';
                    break;
                case 9:
                    $list['order_status_name'] = '服务中';
                    break;
                case 10:
                    $list['order_status_name'] = '待下单';
                    break;
                case 11:
                    $list['order_status_name'] = '已下单';
                    break;
                default:
                    break;
            }
            switch ($list['distribution']) {//0到店就餐、1到店取餐、2商家自配送、4达达配送'
                case 0:
                    $list['distribution_name'] = '到店就餐';
                    break;
                case 1:
                    $list['distribution_name'] = '到店取餐';
                    break;
                case 2:
                    $list['distribution_name'] = '商家自配送';
                    break;
                case 4:
                    $list['distribution_name'] = '达达配送';
                    break;
                default:
                    break;
            }
            switch ($list['pay_status']) {//0未支付，1全额已支付，2定金已支付',
                case 0:
                    $list['pay_status_name'] = '未支付';
                    break;
                case 1:
                    $list['pay_status_name'] = '全额已支付';
                    break;
                case 2:
                    $list['pay_status_name'] = '定金已支付';
                    break;
                default:
                    break;
            }
            $order_goods = db('order_goods')->where('order_id', $order_id)->select();
            foreach ($order_goods as &$val_goods) {
                $image = db('goods')->where('goods_id', $val_goods['goods_id'])->find()['original_img'];
                $val_goods['image_url'] = $image;
            }
            $list['goods_list'] = $order_goods;
            $company = db('company')->where('id', $list['company_id'])->find();
            $list['company'] = $company['company_name'];
            $list['company_phone'] = $company['company_phone'];
            $list['latitude'] = $company['latitude'];
            $list['longitude'] = $company['longitude'];
            $list['create_time'] = date('Y-m-d H:i:s', $list['create_time']);
            if (!empty($list['bring_time'])) {
                $list['bring_time'] = date('Y-m-d H:i:s', $list['bring_time']);
            }
            if (!empty($list['room_type'])) {
                //根据包房类型id查找名称
                $room_type_name = db('company_config')->where('id', $list['room_type'])->find()['c_name'];
                $list['room_type_name'] = $room_type_name;
            }
            if (!empty($list['table_id'])) {
                //根据桌台id查找桌台名称
                $table_name = db('tables')->where('id', $list['table_id'])->find()['table_name'];
                $list['table_name'] = $table_name;
                $table_num = db('tables')->where('id', $list['table_id'])->find()['table_sn'];
                $list['table_num'] = $table_num;
            }
            //根据送货单查找地址
            $address = db('delivery')->where('order_id', $order_id)->find();
            if (!empty($address)) {
                $list['address'] = $address['province'] . $address['city'] . $address['district'] . $address['address'];
                $list['address_phone'] = $address['mobile'];
                $list['address_username'] = $address['consignee'];
            }
            if (empty($list['user_note'])) {
                $list['user_note'] = "无";
            }
            if (empty($list['advance_remark'])) {
                $list['advance_remark'] = "无";
            }
            $list['book_time'] = date("Y-m-d H:i:s", $list['book_time']);
            //根据订单信息查找该订单是否使用了优惠券
            $data_coupon_log = db('coupon_log')->where('order_id', $order_id)->find();
            if (!empty($data_coupon_log)) {
                //根据优惠券id查找优惠券金额
                $coupon_price = db('coupon')->where('id', $data_coupon_log['coupon_id'])->field('amount')->find();
                $list['coupon_price'] = $coupon_price['amount'];
                if ($data_coupon_log['end_time'] < time()) {
                    $list['is_guoqi'] = 1;
                } else {
                    $list['is_guoqi'] = 0;
                }
            } else {
                $list['coupon_price'] = 0;
                $list['is_guoqi'] = 2;
            }

            //根据订单查找积分使用记录
            $data_integral_log = array(
                'order_id' => $order_id,
                'type' => 1,
            );
            $integral_log = db('integral_log')->where($data_integral_log)->find();
            if (!empty($integral_log['integral'])) {
                $list['integral_price'] = number_format(floatval($integral_log['integral'] * 0.01), 2, ".", "");
            } else {
                $list['integral'] = 0;
            }
        }
        if ($type == 2) {
            $list = db('line_order')->where("id", $order_id)->find();
            switch ($list['order_status']) {//1排队中、2已取消、3已完成、4已过号',
                case 1:
                    $list['order_status_name'] = '排队中';
                    break;
                case 2:
                    $list['order_status_name'] = '已取消';
                    break;
                case 3:
                    $list['order_status_name'] = '已完成';
                    break;
                case 4:
                    $list['order_status_name'] = '已过号';
                    break;
                default:
                    break;
            }
            $company = db('company')->where('id', $list['company_id'])->find();
            $list['company'] = $company['company_name'];
            $date = date('Y-m-d', time());
            $data = array(
                'company_id' => $list['company_id'],
                'order_status' => 1, //排队中,
                'create_time' => array('egt', strtotime($date)), //即大于当天日期
                'create_time' => array('elt', time()), //创建时间在当前时间之前,且是当天创建的即大于当天日期
            );
            $count = db('line_order')->where($data)->where('create_time', 'egt', strtotime($date))->where('create_time', 'elt', time())->count();
            $list['num_count'] = $count;
            //根据商家id获取商家是否可以排号信息
            $company_config = db('company')->where('id', $list['company_id'])->find()['queue_switch'];
            $list['queue_switch'] = $company_config;
            $list['create_time'] = date("Y-m-d H:i:s", $list['create_time']);
            $list['book_time'] = date("Y-m-d H:i:s", $list['book_time']);
        }
        return $list;
    }

    public function confirm($user_id, $type, $company_id, $param, $address_id) {
//        $goods_lists = str_replace('","', '"."', $param);
//        $goods_lists = substr($goods_lists, 0, strlen($goods_lists) - 1);
//        $goods_lists = substr($goods_lists, 1);
//        $goods_lists = str_replace('"', '', $goods_lists);
//        $aa = explode('.', $goods_lists);
//        foreach ($aa as $key1 => $value1) {
//            $goods_list[] = explode(',', $value1);
//        }
        if ($type == 1 || $type == 2) {//点餐
            //查询商家信息
            $company = db('company')->field('company_name,company_addr,take_time,take_limit_time')->where('id', $company_id)->find();
            $list['company'] = $company;
            //查询商品信息和总价
            $totle_price = 0;
            foreach ($param as $key => $value) {
                $goods = db('goods')->field('goods_name,shop_price,original_img')->where('goods_id', $value[0])->find();
                $goods['goods_num'] = $value[1];
                $list['goods_list'][$key] = $goods;
                $totle_price+=$goods['shop_price'] * $value[1];
            }
            //判断是否有活动
            if ($type == 1) {//店内就餐
                $prom = db('prom')->field('full_money, reduction_money')
                        ->where('company_id', $company_id)
                        ->where('type', 1)
                        ->where('is_close', 0)
                        ->where('is_delete', 0)
                        ->where('start_time', 'lt', time())
                        ->where('end_time', 'gt', time())
                        ->order('full_money desc')
                        ->select();
                if (!empty($prom)) {
                    foreach ($prom as $key => $value) {
                        if ($totle_price >= $value['full_money']) {
                            $prom_price = $value['reduction_money'];
                        }
                    }
                }
                $now_totle_price = $totle_price - $prom_price;
            }
            if ($type == 2) {//到店取餐
                $prom = db('prom')->field('full_money, reduction_money')
                        ->where('company_id', $company_id)
                        ->where('type', 2)
                        ->where('is_close', 0)
                        ->where('is_delete', 0)
                        ->where('start_time', 'lt', time())
                        ->where('end_time', 'gt', time())
                        ->order('full_money desc')
                        ->select();
                if (!empty($prom)) {
                    foreach ($prom as $key => $value) {
                        if ($totle_price >= $value['full_money']) {
                            $prom_price = $value['reduction_money'];
                        }
                    }
                }
                $take_packing_price = $company['take_packing_price']; //到店取餐包装费
                $now_totle_price = $totle_price - $prom_price + $take_packing_price;
            }

            $list['totle_price'] = number_format(floatval($now_totle_price), 2, ".", "");
        }
        if ($type == 3) {//外卖
            $company = db('company')->where('id', $company_id)->find();
            //查询商品信息和总价
            $totle_price = 0;
            foreach ($param as $key => $value) {
                $goods = db('goods')->field('goods_name,shop_price,original_img')->where('goods_id', $value[0])->find();
                $goods['goods_num'] = $value[1];
                $list['goods_list'][$key] = $goods;
                $totle_price+=$goods['shop_price'] * $value[1];
            }
            $prom = db('prom')->field('full_money, reduction_money')
                    ->where('company_id', $company_id)
                    ->where('type', 3)
                    ->where('is_close', 0)
                    ->where('is_delete', 0)
                    ->where('start_time', 'lt', time())
                    ->where('end_time', 'gt', time())
                    ->order('full_money desc')
                    ->select();
            if (!empty($prom)) {
                foreach ($prom as $key => $value) {
                    if ($totle_price >= $value['full_money']) {
                        $prom_price = $value['reduction_money'];
                    }
                }
            }
            // 包装费
            $takeout_packing_price = $company['takeout_packing_price'];
            //配送费
            addresstolatlag();

            $now_totle_price = $totle_price - $prom_price + $takeout_packing_price;
            $list['totle_price'] = number_format(floatval($now_totle_price), 2, ".", "");
        }
        if ($type == 4) {//预定
            //查询商家信息
            $company = db('company')->field('company_name,company_addr,take_time,take_limit_time')->where('id', $company_id)->find();
            $list['company'] = $company;
        }
        return $list;
    }

    /**
     * 根据company_id和user_id查找前面排队人数
     * @param type $company_id
     * @param type $user_id
     */
    public function get_wait_page($company_id, $user_id) {
        $date = date('Y-m-d', time());
        $data_user = array(
            'company_id' => $company_id,
            'order_status' => 1, //排队中,
            'user_id' => $user_id,
            'create_time' => array('egt', strtotime($date)), //大于当天日期
        );
        $list = db('line_order')->where($data_user)->select();
        $data = array(
            'company_id' => $company_id,
            'order_status' => 1, //排队中,
            'create_time' => array('egt', strtotime($date)), //即大于当天日期
            'create_time' => array('elt', time()), //创建时间在当前时间之前,且是当天创建的即大于当天日期
        );
        $count = db('line_order')->where($data)->where('create_time', 'egt', strtotime($date))->where('create_time', 'elt', time())->count();
        if (!empty($list)) {
            $data['is_wait'] = 1; //是否已有有效排队0否1是
        } else {
            $data['is_wait'] = 0; //是否已有有效排队0否1是
        }
        $data['count'] = $count;
        return $data;
    }

    /**
     * 执行排队订单提交
     * @param type $company_id 企业id
     * @param type $user_id 用户id
     * @param type $person_num 就餐人数
     */
    public function do_wait($company_id, $user_id, $person_num) {
        $date = date('Y-m-d', time());
        //根据company_id获取当天最后一条记录的排号若无则1若有则+1
        $data_company = array(
            'company_id' => $company_id,
            'create_time' => array('egt', strtotime($date)), //大于当天日期
        );
        $list = db('line_order')->where($data_company)->order('create_time desc')->limit(1)->select();
        $data = array(
            'create_time' => time(),
            'update_time' => time(),
            'person_num' => $person_num,
            'user_id' => $user_id,
            'company_id' => $company_id,
            'order_status' => 1
        );
        if (empty($list)) {
            $data['line_num'] = 1;
        } else {
            $data['line_num'] = intval($list[0]['line_num']) + 1;
        }
        $order_id = db('line_order')->insertGetId($data);
        if (!$order_id) {
            exception("网络错误！");
        }
        //根据order_id查询记录并返回。
        $line_num = db('line_order')->where('id', $order_id)->find();
        $result['line_num'] = $line_num['line_num'];
        $result['id'] = $line_num['id'];
        return $result;
    }

    /**
     * 取消排号
     * @param type $id id
     */
    public function queueCancel($id) {
        $data = array(
            'update_time' => time(),
            'order_status' => 2,
        );
        $result = db('line_order')->where('id', $id)->update($data);
        if (!$result) {
            exception("网络错误");
        }
    }

    /**
     * 删除订单
     * @param type $id id
     */
    public function queueDele($id) {
        $result = db('line_order')->delete($id);
        if (!$result) {
            exception("网络错误");
        }
    }

    /**
     * 确认到店
     * @param type $id
     */
    public function orderGo($id) {
        $data = array(
            'order_status' => 9, //服务中
            'update_time' => time()
        );
        $result = db('order')->where('order_id', $id)->update($data);
        if (!$result) {
            exception("网络错误");
        }
    }

    /**
     * 确认到店
     * @param type $id
     */
    public function orderComplete($id) {
        $data = array(
            'order_status' => 5, //已完成
            'update_time' => time()
        );
        //根据订单id查询订单类型，如果为外卖订单，修改一下确认送达时间
        $order_detail = db('order')->field('order_type')->where('order_id', $id)->find();
        if($order_detail['order_type'] == 2){
            $data['confirm_time'] = time();
        }
        $result = db('order')->where('order_id', $id)->update($data);
        if (!$result) {
            exception("网络错误");
        }
    }

    /**
     * 删除订单
     * @param type $id id
     */
    public function queueDelete($id) {
        $data = array(
            'is_delete' => 1,
            'update_time' => time()
        );
        $result = db('order')->where('order_id', $id)->update($data);
        if (!$result) {
            exception("网络错误");
        }
    }

    /**
     * 插入消息记录
     * @param type $order_id 订单id
     * @param type $type 类型 0支付1取消
     * @param type $message 提示信息
     */
    public function add_message($order_id, $type, $message) {
        $data = array(
            'order_id' => $order_id,
            'type' => $type,
            'message' => $message,
            'create_time' => time()
        );
        $result = db('admin_notice')->insert($data);
        if (!$result) {
            exception("操作错误！");
        }
    }

}
