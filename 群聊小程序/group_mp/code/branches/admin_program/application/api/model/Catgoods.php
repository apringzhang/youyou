<?php

namespace app\api\model;

class Catgoods {

    /**
     * 获取分类下商品
     * $dian 1为点餐其他为外卖
     */
    public function get_catgoods($cat_id, $company_id, $dian, $session_id) {
        if ($dian == 1) {
            $map['order'] = 1;
        } elseif ($dian == 2) {
            $map['takeout'] = 1;
        }
        $user = check_session_id($session_id);
        $list = db('goods')
                ->field('goods_id,goods_name,store_count,shop_price,original_img,isnot_discout,discout,sales_sum, sort, isnot_stick')
                ->where('cat_id', $cat_id)
                ->where('is_on_sale', 1)
                ->where('is_delete', 0)
                ->where('company_id', $company_id)
                ->where($map)
                ->order('isnot_stick desc, sort, sales_sum desc')
                ->select();
        if ($list) {
            foreach ($list as $key => $value) {
                $list[$key]['style'] = 'none';
                if ($value['isnot_discout'] == 1) {
                    $list[$key]['discout_price'] = number_format($value['shop_price'] * ($value['discout'] / 100), 2, ".", "");
                    $list[$key]['zhekou'] = $value['discout'] / 10;
                }
                $list[$key]['goods_num'] = db('cart')->where('goods_id', $value['goods_id'])->where('user_id', $user['id'])->where('type', $dian)->where('company_id', $company_id)->value('goods_num') ?? 0;
                $list[$key]['spec_goods'] = db('spec_goods')->where('goods_id', $value['goods_id'])->where('is_delete', 0)->select() ?? '';
                $list[$key]['original_img'] = str_replace('\\', '/', $value['original_img']);
            }
        }
        // var_dump($lists);
        // halt($lists);die;
        return $list;
    }

    public function get_prom($company_id, $dian) {
        if ($dian == 1) {
            $info['dian'] = db('prom')->field('full_money, reduction_money')->where('company_id', $company_id)->where('type', 1)->where('is_close', 0)->where('is_delete', 0)->where('start_time', 'lt', time())->where('end_time', 'gt', time())->order('full_money')->find();
            $info['qu'] = db('prom')->field('full_money, reduction_money')->where('company_id', $company_id)->where('type', 2)->where('is_close', 0)->where('is_delete', 0)->where('start_time', 'lt', time())->where('end_time', 'gt', time())->order('full_money')->find();
        }
        if ($dian == 2) {
            $info['price'] = db('company')->where('id', $company_id)->value('takeout_price');
            $info['wai'] = db('prom')->field('full_money, reduction_money')->where('company_id', $company_id)->where('type', 3)->where('is_close', 0)->where('is_delete', 0)->where('start_time', 'lt', time())->where('end_time', 'gt', time())->order('full_money')->find();
        }

        return $info;
    }

    public function get_start($company_id) {
        $info = db('company')->field('opening_hours, closing_hours, company_name, company_addr, in_switch, pick_switch')->where('id', $company_id)->where('is_delete', 0)->find();

        return $info;
    }

    public function get_address($id) {
        $info = db('address')->where('id', $id)->where('is_delete', 0)->find();
        return $info;
    }

    public function specgoods($id, $session_id, $dian, $company_id) {
        $list['list'] = db('spec_goods')->where('goods_id', $id)->where('is_delete', 0)->select();
        $user = check_session_id($session_id);
        if ($list['list']) {
            foreach ($list['list'] as $key => $value) {
                $list['list'][$key]['goods_num'] = db('cart')->where('spec_goods_id', $value['id'])->where('goods_id', $value['goods_id'])->where('user_id', $user['id'])->where('type', $dian)->where('company_id', $company_id)->value('goods_num') ?? 0;
            }
        }
        $zq = db("goods")->where('goods_id', $id)->where('isnot_discout', 1)->find();
        if ($zq) {
            $list['price'] = number_format($list['list'][0]['price'] * ($zq['discout'] / 100), 2, ".", "");
            foreach ($list['list'] as $k => $val) {
                $list['list'][$k]['price'] = number_format($val['price'] * ($zq['discout'] / 100), 2, ".", "");
            }
        } else {
            $list['price'] = $list['list'][0]['price'];
        }

        return $list;
    }

    public function specgoodsinfo($goods_id, $session_id, $dian, $company_id, $spec_id) {
        $user = check_session_id($session_id);
        $info = db('cart')->where('spec_goods_id', $spec_id)->where('goods_id', $goods_id)->where('user_id', $user['id'])->where('type', $dian)->where('company_id', $company_id)->value('goods_num') ?? 0;
        return $info;
    }

    public function get_prom_list($company_id, $dian) {
        if ($dian == 1) {
            $info['dian'] = db('prom')->field('id, full_money, reduction_money')->where('company_id', $company_id)->where('type', 1)->where('is_close', 0)->where('is_delete', 0)->where('start_time', 'lt', time())->where('end_time', 'gt', time())->order('full_money')->select();
            $info['qu'] = db('prom')->field('id, full_money, reduction_money')->where('company_id', $company_id)->where('type', 2)->where('is_close', 0)->where('is_delete', 0)->where('start_time', 'lt', time())->where('end_time', 'gt', time())->order('full_money')->select();
        }
        if ($dian == 2) {
            $info['price'] = db('company')->where('id', $company_id)->value('takeout_price');
            $info['wai'] = db('prom')->field('id, full_money, reduction_money')->where('company_id', $company_id)->where('type', 3)->where('is_close', 0)->where('is_delete', 0)->where('start_time', 'lt', time())->where('end_time', 'gt', time())->order('full_money')->select();
        }

        return $info;
    }

}
