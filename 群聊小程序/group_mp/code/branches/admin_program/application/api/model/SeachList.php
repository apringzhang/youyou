<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/14
 * Time: 11:16
 */

namespace app\api\model;

class SeachList {


    /**
     * 获取商品列表
     * @param $company_id 商家ID
     * @param $check_status '点餐:1,外卖2',
     * @param $goods_name 查询商品名
     * @param $session_id 用户session_id
     * @param $car_status 购物车商品类型
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function get_list($company_id,$check_status,$goods_name,$session_id,$car_status)
    {
        if ($check_status == 1)
        {
            $map['order'] = 1;
        } else if($check_status == 2) {
            $map['takeout'] = 1;
        }
        $map['company_id'] = $company_id;
        $map['goods_name'] = ['LIKE','%'.$goods_name.'%'];
        $map['is_on_sale'] = 1;
        $map['is_delete'] = 0;
        $goods_list = db('goods')->field('goods_id,goods_name,store_count,shop_price,original_img,isnot_discout,discout,sales_sum, sort, isnot_stick')->where($map)->order('isnot_stick desc, sort, sales_sum desc')->select();
        $list = array();
        //购物车数量  商品名 图片  价格
        foreach($goods_list as $key=>$value)
        {
            $goods_list[$key]['sales_sum'] = $goods_list[$key]['sales_sum'];
            if ($goods_list[$key]['isnot_discout'] == 1) {
                $goods_list[$key]['discout_price'] = number_format($goods_list[$key]['shop_price'] * ($goods_list[$key]['discout'] / 100), 2, ".", "");
                $goods_list[$key]['zhekou'] = $goods_list[$key]['discout'] / 10;
            }
            //购物车参数查询
            $cart_map['session_id'] = $session_id;
            $cart_map['goods_id'] = $goods_list[$key]['goods_id'];
            $cart_map['type'] = $car_status;
            $cart = db('cart')->where($cart_map)->find();
            $spec = db('spec_goods')->where('goods_id', $goods_list[$key]['goods_id'])->where('is_delete', 0)->find();
            if (!empty($spec))
            {
                $goods_list[$key]['is_spec'] = 1;
                $goods_list[$key]['goods_num'] = '';
                $goods_list[$key]['store_count'] = '';
            } else {
                $goods_list[$key]['store_count'] = $goods_list[$key]['store_count'];
                $goods_list[$key]['is_spec'] = 2;
                $goods_list[$key]['goods_num'] = intval($cart['goods_num']);
            }
        }
        return $goods_list;
    }


}