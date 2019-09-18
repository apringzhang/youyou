<?php

/**
 * Created by PhpStorm.
 * User: GYC
 * Date: 2018/8/6
 * Time: 10:56
 */

namespace app\api\controller;

use think\Exception;

class Order extends Common {

    public function orderlist() {
        $data = json_decode(file_get_contents('php://input'), true);
        $row = $this->session_id_check($data['session_id']);
        $type = $data['type'];
        check_empty($type, 'type参数错误');
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $user_id = $row['id'];
        //分页
        $page = intval($data['page']);
        if (empty($page)) {
            $page = 1;
        }
        $page_size = 10;
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->getOrderList($user_id, $type, $page, $page_size, $company_id);
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

    public function orderdetail() {
        $data = json_decode(file_get_contents('php://input'), true);
        $order_id = $data['order_id'];
        check_empty($order_id, 'order_id参数错误');
        $type = $data['type'];
        check_empty($type, 'type参数错误');
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->getOrderDetail($order_id, $type);
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

    public function confirmorder() {
        $data = json_decode(file_get_contents('php://input'), true);
        $row = $this->session_id_check($data['session_id']);
        $user_id = $row['id'];
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $type = $data['order_type'];
        check_empty($type, 'type参数错误');
        $address_id = $data['address_id'];
        $isnot_nots = $data['isnot_nots']; //是否开发票
        $coupon_id = $data['coupon_id'];
        $intergarl = $data['intergarl']; //0否 1是
        $fromtype = 1;
        $order_id = '';
        
//        $user_id = 13;
//        $coupon_id = 3;
//        $intergarl = 1;
//        $type = 1;
//        $company_id = 1;
//        $address_id = 75;
//        $isnot_nots = 0;
//        $fromtype = 1;
//        $order_id = '';
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->confirm($user_id, $type, $company_id, $address_id, $fromtype, $order_id, $isnot_nots, $coupon_id, $intergarl);
//           dump($list);die;
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

    /**
     * 排号订单页面
     */
    public function wait_page() {
        //company_id 和 session_id
        $data = json_decode(file_get_contents('php://input'), true);
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $row = $this->session_id_check($data['session_id']);
        $user_id = $row['id'];
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->get_wait_page($company_id, $user_id);
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

    /**
     * 提交排队订单
     */
    public function do_wait() {
        //company_id、session_id、person_num（就餐人数）
        $data = json_decode(file_get_contents('php://input'), true);
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $row = $this->session_id_check($data['session_id']);
        $user_id = $row['id'];
        $person_num = $data['person_num'];
        if (empty($person_num)) {
            return json_encode([
                'result' => 2,
                'message' => "请输入就餐人数！",
            ]);
        }
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->do_wait($company_id, $user_id, $person_num);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => "取号成功",
            'line_num' => $list['line_num'], //号码
            'order_id' => $list['id'], //id
        ]);
    }

   

    /**
     * 支付回调
     */
    public function notify() {
        import('pay.Pay');
        $pay = new \pay\Wxpay();
        $result = $pay->notifyMp();
        file_put_contents('./static/notify_' . time(), json_encode($result, JSON_PRETTY_PRINT)); //将返回数据存储到文件中
        //修改订单状态
        $Order = new \app\api\model\Order();
        try {
            $Order->paynotify($result['trade_no'], $result['out_trade_no']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 获取包房类型
     */
    public function roomtype() {
        $data = json_decode(file_get_contents('php://input'), true);
        $company_id = $data['company_id'];
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->getroomtype($company_id);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => $list
        ]);
    }

    /**
     * 判断是否在配送范围
     */
    public function get_psfw() {
        //company_id 和 session_id
        $data = json_decode(file_get_contents('php://input'), true);
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $address_id = $data['address_id'];
        check_empty($address_id, 'address_id参数错误');
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->getaddfw($company_id, $address_id);
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


    /**
     * 判断是否在配送范围内
     */
    public function is_distance() {
        $data = json_decode(file_get_contents('php://input'), true);
        $company_id = $data['company_id'];
        check_empty($company_id, 'company_id参数错误');
        $address_id = $data['address_id'];
        check_empty($address_id, 'address_id参数错误');
        try {
            $address = db('address')->where('id', $address_id)->find();
            $company = db('company')->where('id', $company_id)->find();
            if (!empty($address)) {
                $takeout_distance_price = $company['takeout_distance_price'];
                $psfkm = getdistance($company['longitude'], $company['latitude'], $address['longitude'], $address['latitude'], 2);
                $takeout_distance = $company['takeout_distance']; //外卖配送距离
                if ($psfkm > $takeout_distance) {
                    return json_encode([
                        'result' => 1,
                        'message' => '超出配送范围',
                    ]);
                } else {
                    return json_encode([
                        'result' => 0,
                        'message' => '在配送范围内',
                    ]);
                }
            }
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 储值金支付
     */
    public function torage_pay() {
        $data = json_decode(file_get_contents('php://input'), true);
        $row = $this->session_id_check($data['session_id']);
        $user_id = $row['id'];
        $order_id = $data['order_id'];
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->paytorage($user_id, $order_id);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => '支付成功',
        ]);
    }

    /**
     * 充值回调
     */
    public function cz_notify() {
        import('pay.Pay');
        $pay = new \pay\Wxpay();
        $result = $pay->notifyMp();
        file_put_contents('./static/notify_' . time(), json_encode($result, JSON_PRETTY_PRINT)); //将返回数据存储到文件中
        //修改订单状态
        $Order = new \app\api\model\Order();
        try {
            $Order->paycznotify($result['trade_no'], $result['out_trade_no']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * 提交充值订单
     */
    public function cz_doorder() {
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = $this->session_id_check($data['session_id']);
        $total_amount = $data['total_amount'];
        $torage_id = $data['torage_id'];
        $prom_id = $data['prom_id'] ?? 0;
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->do_czorder($user_id, $total_amount, $recharge_id, $prom_id);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => "提交成功",
            'order_id' => $list,
        ]);
    }

    /**
     * 通过订单id获取订单信息
     */
    public function orderbyid() {
        $data = json_decode(file_get_contents('php://input'), true);
        $order_id = $data['order_id'];
        try {
            $list = db('order')
                    ->where("order_id", $order_id)
                    ->find();
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

    public function recharge_list() {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $list = db('recharge')
                    ->where("is_delete", 0)
                    ->order('sort')
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

    /**
     * 提交充值订单
     */
    public function vip_doorder() {
        $data = json_decode(file_get_contents('php://input'), true);
        $user_id = $this->session_id_check($data['session_id']);
        $total_amount = $data['total_amount'];
        $torage_id = $data['torage_id'];
        $prom_id = $data['prom_id'] ?? 0;
        $Order = new \app\api\model\Order();
        try {
            $list = $Order->vip_doorder($user_id, $total_amount, $torage_id, $prom_id);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => "提交成功",
            'order_id' => $list,
        ]);
    }

    /**
     * vip充值回调
     */
    public function vip_notify() {
        import('pay.Pay');
        $pay = new \pay\Wxpay();
        $result = $pay->notifyMp();
        file_put_contents('./static/notify_' . time(), json_encode($result, JSON_PRETTY_PRINT)); //将返回数据存储到文件中
        //修改订单状态
        $Order = new \app\api\model\Order();
        try {
            $Order->payvipnotify($result['trade_no'], $result['out_trade_no']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
