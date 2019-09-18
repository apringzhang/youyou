<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/10
 * Time: 14:11
 */
namespace app\api\controller;

use think\Exception;

class Printxp extends Common {

    /**
     * 整单打印
     * @return string|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        //订单ID
        $id = input('get.id');
        //打印机ID
        $print_id = input('get.print_id');
        if (empty($id))
        {
            $return['code'] = 2;
            $return['msg'] = '参数错误';
            return  json_encode($return);
        }
        //查询打印机
        $data['print']  = db('printer')->where('id',$print_id)->find();
        //查询订单
        $data['order'] = db('order')->where('order_sn',$id)->find();
        //查询商户信息
        $data['company'] = db('company')->where('id',1)->find();
        //查询购买商品列表
        $data['goods_list'] = db('order_goods')->where('order_id',$data['order']['order_id'])->select();
        //查询优惠活动
        $data['prom'] = db('prom')->where('id',$data['order']['prom_id'])->find();
        //查询发货单
        $data['delivery'] = db('delivery')->where('order_id',$data['order']['order_id'])->find();
        return view('', $data);
    }


    /**
     * 分单打印
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function printing()
    {
        //打印机ID
        $print_id = input('get.print_id');
        //商品ID
        $goods_id = input('get.id');
            
        //订单号
        $order_sn = input('get.order_sn');

        $data['goods_num'] = input('get.goods_num');
        //查询打印机
        $data['print']  = db('printer')->where('id',$print_id)->find();
        //查询订单
        $data['order'] = db('order')->field('order_sn,create_time,table_id,order_type')->where('order_sn',$order_sn)->find();
        //查询桌台
        $data['table'] = db('tables')->field('table_name')->where('id',$data['order']['table_id'])->find();
        //查询订单
        $data['goods'] = db('goods')->where('goods_id',$goods_id)->find();
        return view('', $data);
    }

    /**
     * 不带价格打印整单
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function price()
    {
        //订单号
        $order_sn = input('get.id');
        //打印机ID
        $print_id = input('get.print_id');
        if (empty($order_sn))
        {
            $return['code'] = 2;
            $return['msg'] = '参数错误';
            return  json_encode($return);
        }
        //查询订单
        $data['order'] = db('order')->where('order_sn',$order_sn)->find();
        //查询桌台
        $data['table'] = db('tables')->field('table_name')->where('id',$data['order']['table_id'])->find();
        //查询商户信息
        $data['company'] = db('company')->where('id',1)->find();
        //查询商品列表
        $goods_list = db('order_goods')->where('order_id',$data['order']['order_id'])->select();
        //查询打印机
        $data['print']  = db('printer')->where('id',$print_id)->find();
        if ($data['print']['isshow_category'] == 1)
        {
            //查询分类
            $catMap['is_delete'] = 0;
            $cat = db('goods_cat')->where($catMap)->select();
            foreach($cat as $key => $value)
            {
                $goods_num = 0;
                foreach($goods_list as $goodskey => $goodsvalue)
                {
                    $goods = db('goods')->where('goods_id',$goodsvalue['goods_id'])->find();
                    if ($goods['cat_id'] == $value['id'])
                    {
                        $data['print']['goodslist'][$cat[$key]['id']]['cat_id'] = $cat[$key]['id'];
                        $data['print']['goodslist'][$cat[$key]['id']]['list'][$goods_num]['goods_id'] = $goods['goods_id'];
                        $data['print']['goodslist'][$cat[$key]['id']]['list'][$goods_num]['goods_name'] = $goods['goods_name'];
                        $data['print']['goodslist'][$cat[$key]['id']]['list'][$goods_num]['goods_num'] = $goodsvalue['goods_num'];
                        $goods_num += 1;
                    }
                }
            }
        } else {
            $data['print']['goodslist'] = $goods_list;
        }
        return view('', $data);
    }
}