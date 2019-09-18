<?php

/**
 * Created by PhpStorm.
 * User: GYC
 * Date: 2018/8/6
 * Time: 11:16
 */

namespace app\api\model;

class Goods {

    public function addCart($user_id, $company_id, $goods_id, $goods_num, $session_id, $type, $spec_goods_id) {
        if (!empty($goods_id)) {
            $cart = db('cart')->where("goods_id", $goods_id)->where("user_id", $user_id)->where("spec_goods_id", $spec_goods_id)->where("company_id", $company_id)->where("type", $type)->find();
            $id = $cart['id'];
            if (!empty($cart)) {
                if ($goods_num > 0) {
                    $data['user_id'] = $user_id;
                    $data['company_id'] = $company_id;
                    $data['session_id'] = $session_id;
                    $data['goods_id'] = $goods_id;
                    $data['goods_num'] = $goods_num;
                    $data['type'] = $type;
                    $data['spec_goods_id'] = $spec_goods_id;
                    $data['add_time'] = time();
                    $result3 = db('cart')->where('id', $id)->update($data);
                    if (!$result3) {
                        exception('操作失败');
                    }
                }
                if ($goods_num == 0) {
                    $result1 = db('cart')->where('id', $id)->delete();
                    if (!$result1) {
                        exception('操作失败');
                    }
                }
            }
            if (empty($cart)) {
                if ($goods_num > 0) {
                    $data['user_id'] = $user_id;
                    $data['company_id'] = $company_id;
                    $data['session_id'] = $session_id;
                    $data['goods_id'] = $goods_id;
                    $data['goods_num'] = $goods_num;
                    $data['spec_goods_id'] = $spec_goods_id;
                    $data['add_time'] = time();
                    $data['type'] = $type;
                    $result2 = db('cart')->insert($data);
                    if (!$result2) {
                        exception('操作失败');
                    }
                }
            }
        }
        //购物车列表
        $array = [];
        $list = db('cart')->where("user_id", $user_id)->where("company_id", $company_id)->where("type", $type)->select();
        $goods_id_list = db('cart')->Distinct(true)->field('goods_id')->where("user_id", $user_id)->where("company_id", $company_id)->where("type", $type)->select();
        $totle_prices = 0;
        $totle_prices_y = 0;
        $count = 0;
        foreach ($goods_id_list as $keys => &$values) {
            $lists[$values['goods_id']] = $values;
            $goods = db('goods')->where("goods_id", $values['goods_id'])->find();
            $lists[$values['goods_id']]['goods_list'] = $goods;
            $lists[$values['goods_id']]['goods_name'] = $goods['goods_name'];
            $lists[$values['goods_id']]['original_img'] = str_replace('\\', '/', $goods['original_img']);
            $cart_spec_list = db('cart')->where("user_id", $user_id)->where("company_id", $company_id)->where("type", $type)->where("goods_id", $values['goods_id'])->select();
            foreach ($cart_spec_list as $key => $value) {
                if (empty($value['spec_goods_id'])) {
                    $lists[$values['goods_id']]['is_spec'] = 2;
                    $lists[$values['goods_id']]['goods_num'] = db('cart')->where("user_id", $user_id)->where("company_id", $company_id)->where("type", $type)->where('goods_id', $values['goods_id'])->value('goods_num');
                    if ($goods['isnot_discout'] == 1) {//打折
                        $lists[$values['goods_id']]['discout_price'] = number_format(floatval($goods['shop_price'] / 100 * $goods['discout']), 2, ".", "");
                        $totle_price = $goods['shop_price'] / 100 * $goods['discout'] * $value['goods_num'];
                    } else {
                        $totle_price = $goods['shop_price'] * $value['goods_num'];
                    }
                    $spec_goods['totle_price'] = $totle_price;
                    $totle_prices_y = $goods['shop_price'] * $value['goods_num'];
                }
                if (!empty($value['spec_goods_id'])) {
                    $spec_goods = db('spec_goods')->where("id", $value['spec_goods_id'])->where("is_delete", 0)->find();
                    if ($goods['isnot_discout'] == 1) {//打折
                        $spec_goods['discout_price'] = number_format(floatval(($spec_goods['price'] / 100) * $goods['discout']), 2, ".", "");
                        $totle_price = $spec_goods['price'] / 100 * $goods['discout'] * $value['goods_num'];
                    } else {
                        $totle_price = $spec_goods['price'] * $value['goods_num'];
                    }
                    $lists[$values['goods_id']]['is_spec'] = 1;
                    $spec_goods['totle_price'] = $totle_price;
                    $spec_goods['goods_num'] = $value['goods_num'];
                    $lists[$values['goods_id']]['spec_list'][$key] = $spec_goods;
                    $totle_prices_y = $spec_goods['price'] * $value['goods_num'];
                }
                $totle_prices+=$totle_price;
                $totle_prices_yuan+=$totle_prices_y;
            }
            $count += count($cart_spec_list);
        }
        $array['list'] = $lists;
        $array['totle_prices_yuan'] = number_format(floatval($totle_prices_yuan), 2, ".", "");
        $array['totle_price'] = number_format(floatval($totle_prices), 2, ".", "");
        $array['count'] = $count;
        if ($type == 2) {//外卖
            $prom = db('prom')->field('id,full_money, reduction_money')
                    ->where('company_id', $company_id)
                    ->where('type', 3)
                    ->where('is_close', 0)
                    ->where('is_delete', 0)
                    ->where('start_time', 'lt', time())
                    ->where('end_time', 'gt', time())
                    ->order('full_money asc')
                    ->select();
            $company = db('company')->where('id', $company_id)->find();
            $count = count($prom) - 1;
            $prom_min = -1;
            //判断商品是否打折
            $isnot_discout = [];
            foreach ($goods_id_list as $key => $value) {
                $goods_isnot_discout = db('goods')->where('goods_id', $value['goods_id'])->find();
                $isnot_discout[] = $goods_isnot_discout['isnot_discout'];
            }
            $isin_discout = in_array('1', $isnot_discout);
            if ($isin_discout) {
                $array['manjian'] = '折扣已减' . number_format(floatval($totle_prices_yuan - $totle_prices), 2, ".", "") . '元';
                $array['totle_price_mj'] = number_format(floatval($totle_prices), 2, ".", "");
            } else {
                if (!empty($prom)) {
                    foreach ($prom as $key => $value) {
                        if (floatval($totle_prices) < floatval($value['full_money'])) {
                            $prom_min = $key;
                            break;
                        }
                    }
                    if ($prom_min != -1) {
                        if ($prom_min == 0) {
                            $cha = $prom[0]['full_money'] - $totle_prices;
                            $jian = $prom[0]['reduction_money'];
                            $array['manjian'] = '买' . number_format(floatval($cha), 2, ".", "") . '元,减' . number_format(floatval($jian), 2, ".", "") . '元';
                            $array['totle_price_mj'] = number_format(floatval($totle_prices), 2, ".", "");
                        } else {
                            $cha = $prom[$prom_min]['full_money'] - $totle_prices;
                            $jian = $prom[$prom_min - 1]['reduction_money'];
                            $jian_last = $prom[$prom_min]['reduction_money'];
                            $array['manjian'] = '下单减' . number_format(floatval($jian), 2, ".", "") . '元,' . '再买' . number_format(floatval($cha), 2, ".", "") . '元减' . number_format(floatval($jian_last), 2, ".", "") . '元';
                            $array['totle_price_mj'] = number_format(floatval($totle_prices - $jian), 2, ".", "");
                        }
                    } else {
                        $cha = $prom[$count]['full_money'] - $totle_prices;
                        $jian = $prom[$count]['reduction_money'];
                        $array['manjian'] = '已满' . number_format(floatval($prom[$count]['full_money']), 2, ".", "") . '元' . ',减' . number_format(floatval($jian), 2, ".", "") . '元';
                        $array['totle_price_mj'] = number_format(floatval($totle_prices - $jian), 2, ".", "");
                    }
                    // dump($prom_min);
                } else {
                    $array['totle_price_mj'] = number_format(floatval($totle_prices), 2, ".", "");
                    $array['manjian'] = '';
                }
            }
            //起送价
            if ($totle_prices > 0) {
                if ($totle_prices < $company['takeout_price']) {
                    $array['cha_qsj'] = number_format(floatval($company['takeout_price'] - $totle_prices), 2, ".", "");
                } else {
                    $array['cha_qsj'] = 'undefined';
                }
            }
            if ($totle_prices == 0) {
                $array['cha_qsj'] = number_format(floatval($company['takeout_price']), 2, ".", "");
            }
            if ($array['totle_price_mj'] >= $company['free_distrib_price']) {
                $array['pei_sf'] = '0.00';
            } else {
                $array['pei_sf'] = number_format(floatval($company['takeout_distance_price']), 2, ".", "");
            }
        }
//        dump($array);
        return $array;
    }

    public function delCart($user_id, $company_id, $type) {
        $cart = db('cart')->where("user_id", $user_id)->where("company_id", $company_id)->where("type", $type)->select();
        foreach ($cart as $value) {
            $result = db('cart')->where('id', $value['id'])->delete();
            if (!$result) {
                exception('操作失败');
            }
        }
    }
}
