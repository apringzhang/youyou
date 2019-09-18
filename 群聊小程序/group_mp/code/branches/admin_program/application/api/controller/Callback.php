<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/10
 * Time: 19:35
 */

namespace app\api\controller;

use think\Exception;

class Callback extends Common
{
    public function call_back()
    {
        $data = input('post.');
        //file_put_contents('./upload/ceshi.php', json_encode($data));
        //$data = json_decode(file_get_contents('./upload/ceshi.php'),true);
        //签名屏蔽
        /*$strData = [
            'client_id' => $data['client_id'],
            'order_id' => $data['order_id'],
            'update_time' => $data['update_time'],
        ];
        sort($strData);
        $signStr = implode('', $strData);
        if (md5($signStr) !== $data['signature']) {
            halt('非法请求');
        }*/
        switch ($data['order_status']) {
            //发送订单
            case 1:
                $order_date['order_status'] = 2;
                $order_date['shipping_status'] = 0;
                break;
            //骑手接受订单
            case 2:
                $order_date['order_status'] = 3;
                $order_date['shipping_status'] = 1;
                break;
            //骑手取货订单
            case 3:
                $order_date['order_status'] = 3;
                $order_date['shipping_status'] = 1;
                break;
            //骑手完成订单
            case 4:
                $order_date['order_status'] = 4;
                $order_date['shipping_status'] = 2;
                break;
            //取消订单
            case 5:
                $order_date['order_status'] = 2;
                $order_date['shipping_status'] = 0;
                break;
            //订单过期('需要重新下单')
            case 7:
                $order = db('order')->where('order_sn', $data['order_id'])->find();
                $order_date['dada_cancel_num'] = $order['dada_cancel_num']+1;
                $dada_config = db('dada_config')->where('company_id',1)->find();
                $delivery = db('delivery')->where('order_id',$order['order_id'])->find();
                $receiver_address = $delivery['province'].$delivery['city'].$delivery['district'].$delivery['address'];
                $location =  addresstolatlag($receiver_address);
                $receiver_lat = $location[1];
                $receiver_lng = $location[0];
                $url = config('dada_callback_url');
                $shop_no = $dada_config['company_sn'];
                $origin_id = $data['order_id'];
                $cargo_price = $order['total_amount'];
                $info = $order['user_note'];
                $receiver_name = $delivery['consignee'];
                $receiver_phone = $delivery['mobile'];
                import('dada.Dada');
                $shop = new \dada\Dada();
                $shop->reAddOrder($shop_no,$origin_id,$cargo_price,$info,
                $receiver_name,$receiver_address,$receiver_phone,$receiver_lat,$receiver_lng,$url);
                break;
        }
        $order_date['update_time'] = time();
        db('order')->where('order_sn', $data['order_id'])->update($order_date);
    }

    /**
     * 测试模拟
     */
    public function simulation()
    {
        return view('', $data);
    }

    /**
     * 模拟回调
     */
    public function back_simulation()
    {
        $data_name = input('post.data_name');
        $order_sn  = input('post.order_sn');
        import('dada.Dada');
        $shop = new \dada\Dada();
        $data = $shop->accept($order_sn,$data_name);
        return $data;
    }
}