<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/11
 * Time: 14:04
 */


namespace app\api\controller;

use think\Exception;

class Cancellation extends Common
{

    /**
     * 取消接单
     */
    public function cancel_order()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        try {
            $user = $this->session_id_check($data['session_id']);
            $order = db('order')->find($data['order_id']);
            if (!$order) {
                throw new Exception('订单不存在');
            }
            if ($user['id'] != $order['user_id']) {
                throw new Exception('没有权限');
            }
            if (!in_array($order['order_status'], [0, 1])) {
                throw new Exception('订单状态不可取消');
            }
            $order_cancel_data['order_status'] = 6;
            $order_cancel_data['update_time'] = time();
            $order_cancel = db('order')->where('order_id', $data['order_id'])->update($order_cancel_data);
            //退回优惠券
            $map['status'] = 0;
            $map['used_time'] = 0;
            db('coupon_log')->where('order_id',$data['order_id'])->update($map);
            //退回积分
            $integral = db('integral_log')->where('order_id',$data['order_id'])->where('type',1)->find();
            if($integral != ''){
                $old_integral = db('wx_user')->where('id',$integral['user_id'])->value('intergarl');
                db('wx_user')->where('id',$integral['user_id'])->setInc('intergarl',$integral['integral']);
                $map2['user_id'] = $integral['user_id'];
                $map2['order_id'] = $data['order_id'];
                $map2['type'] = 0;
                $map2['integral'] = $integral['integral'];
                $map2['current_integral'] = intval($old_integral) +$integral['integral'];
                $map2['create_time'] = time();
                db('integral_log')->insert($map2);
            }
            //向推送消息表中插入一条记录
            $OrderList = new \app\api\model\OrderList();
            $OrderList->add_message($data['order_id'], 1, "用户取消订单");
            if ($order_cancel && $order['order_status'] == 1) {
                //推送取消订单未写 2018年8月11日14:34:02
                try {
                    /**
                     * 退款
                     * @param $orderNumber 订单号，不是支付单号
                     * @param $tradeNumber 交易流水号
                     * @param $totalAmount 退款金额 1分钱为0.01
                     * @param $termId 支付方式ID 对应payment_term表
                     * @param $paymentAmount 微信退款支付单总额
                     * @param $userId 微信退款操作人ID
                     */
                    $totalAmount = $order['pay_amount'];
                    $paymentAmount = $order['pay_amount'];

                    $orderNumber = date('YmdHi') . sprintf('%04d', mt_rand(0, 9999));
                    $tradeNumber = $order['trade_sn'];

                    import('pay.Pay');
                    $pay = new \pay\Wxpay();
                    $result =$pay->refundMp($orderNumber, $tradeNumber, $totalAmount, $paymentAmount, $order['user_id']);
                    if ($result['status'] == 1) {
                        $map4['refund_price'] = $totalAmount;
                        $map4['update_time'] = time();
                        $map4['refund_status'] = 2;
                        db('order')->where('order_id', $data['order_id'])->update($map4);
                        //退款表
                        $map5['company_id'] = 1;
                        $map5['out_trade_no'] = $orderNumber;
                        $map5['user_id'] = $order['user_id'];
                        $map5['order_id'] = $data['order_id'];
                        $map5['trade_no'] = $result['refund_id'];
                        $map5['refund_amount'] = $totalAmount;
                        $map5['remark'] = "拒单";
                        $map5['create_time'] = time();
                        db('refund')->insert($map5);
                    }else{
                        exception($result['msg']);
                    }
                } catch (Exception $e) {
                    return json([
                        'statusCode' => 300,
                        'message' => $e->getMessage(),
                    ]);
                }
            } else if (!$order_cancel) {
                throw new Exception('订单修改失败');
            } else {
                return json_encode([
                    'status' => 0,
                    'message' => '订单取消成功',
                ]);
            }
        } catch (Exception $e) {
            return json_encode([
                'status' => 2,
                'message' => $e->getMessage()
            ]);
        }
        return json_encode([
            'status' => 0,
            'message' => '订单取消成功',
        ]);
    }

}