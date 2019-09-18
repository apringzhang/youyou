<?php

/**
 * @Author: 玛瑙
 * @Date:   2018-08-07 18:44:35
 * @Last Modified by:   玛瑙
 * @Last Modified time: 2018-08-13 13:25:25
 */

namespace app\api\model;

class User {

    /**
     * 通过session_id获取用户信息
     */
    public function get_user_info($session_id) {

        $info = db('wx_user')
                ->field('id,avatar_url,nick_name,is_admin, province, city, gender, is_vip, vip_time')
                ->where('session_id', $session_id)
                ->find();
        if ($info['gender'] == 1) {
            $info['genders'] = '男';
        }
        if ($info['gender'] == 2) {
            $info['genders'] = '女';
        }
        if ($info['gender'] == 3) {
            $info['genders'] = '保密';
        }
        return $info;
    }

    /**
     * 通过商家id获取商家信息
     */
    public function get_company_info($company_id) {

        $info = db('company')
                ->where('is_delete', 0)
                ->where('id', $company_id)
                ->find();
        if (!empty($info)) {
            $info['company_image'] = db('company_image')
                    ->where('company_id', $company_id)
                    ->where('is_delete', 0)
                    ->order('sort_id')
                    ->select();
            foreach ($info['company_image'] as $key => $value) {
                $info['company_image'][$key]['company_image'] = str_replace('\\', '/', $value['company_image']);
            }
        }
        return $info;
    }

    /**
     * 通过商品id获取商品信息
     */
    public function get_goods_detail($goods_id, $dian, $company_id, $session_id) {

        $info = db('goods')
                ->where('is_delete', 0)
                ->where('goods_id', $goods_id)
                ->find();
        if ($info['isnot_discout'] == 1) {
            $info['discout_price'] = number_format($info['shop_price'] * ($info['discout'] / 100), 2, ".", "");
            $info['discout_zq'] = $info['discout'] / 10;
        }
        $info['goods_num'] = db('cart')->where('goods_id', $goods_id)->where('session_id', $session_id)->where('type', $dian)->where('company_id', $company_id)->value('goods_num') ?? 0;
        $info['original_img'] = str_replace('\\', '/', $info['original_img']);
        $spec = db('spec_goods')->where('goods_id', $goods_id)->where('is_delete', 0)->find();
        if ($spec) {
            $info['is_spec'] = 1;
        } else {
            $info['is_spec'] = 2;
        }
        if (!empty($info)) {
            $info['goods_images'] = db('goods_images')
                    ->where('goods_id', $goods_id)
                    ->order('orders')
                    ->select();
            foreach ($info['goods_images'] as $key => $value) {
                $info['goods_images'][$key]['image_url'] = str_replace('\\', '/', $value['image_url']);
            }
        }
        return $info;
    }

    /**
     * 通过商家id获取首页列表信息
     */
    public function get_goods_list() {

        $tag_list = db('tag')
                ->field('id')
                ->where('status', 1)
                ->order('orders')
                ->select();
        if (!empty($tag_list)) {
            foreach ($tag_list as $key_tag => &$val_tag) {
                $tag_detail = db('tag')
                        ->field('name')
                        ->where('id', $val_tag['id'])
                        ->find();
                $val_tag['tag_name'] = $tag_detail['name'];
                $goods_list = db('goods')
                        ->where('tag_id', $val_tag['id'])
                        ->where('store_count', 'neq', 0)//库存数量
                        ->where('is_on_sale', 1)  //是否商家
                        ->where('is_home_show', 1)//是否首页展示
                        ->where('is_delete', 0)
                        ->order('isnot_stick desc, sort,sales_sum desc')
                        ->select();
                foreach ($goods_list as $key => $val) {
                    $goods_list[$key]['original_img'] = str_replace('\\', '/', $val['original_img']);
                    if ($val['isnot_discout'] == 1) {
                        $goods_list[$key]['discout_price'] = number_format($val['shop_price'] * ($val['discout'] / 100), 2, ".", "");
                    }
                }
                $val_tag['goods_list'] = $goods_list;

                if (empty($goods_list)) {
                    unset($tag_list[$key_tag]);
                }
            }
        } else {
            $tag_list = array();
        }
        return $tag_list;
    }

    /**
     * 首页排队取号
     * 如果并发量高会出现重复数据
     */
    public function get_line_order($data) {
        //获取当天最新排队号码
        $map['company_id'] = $data['company_id'];
        $map['create_time'] = ['egt', strtotime(date('Y-m-d', time()))];

        $line_num = db('line_order')
                ->where($map)
                ->order('create_time desc')
                ->value('line_num');

        //插入排号数据
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['line_num'] = $line_num + 1;
        $data['order_status'] = 1;

        $id = db('line_order')->insertGetId($data);
        if (!$id) {
            exception("网络错误！");
        }

        return $data['line_num'];
    }

    /**
     * 通过session_id获取本地收货地址
     */
    public function get_address_list($session_id) {

        if (empty($session_id)) {
            $list = [];
        } else {
            $list = db('address')
                    ->alias('a')
                    ->field('a.*')
                    ->join('wx_user u', 'u.id = a.user_id')
                    ->where('u.session_id', $session_id)
                    ->where('a.is_delete', 0)
                    ->order('a.create_time desc')
                    ->select();
        }
        return $list;
    }

    /**
     * 删除收货地址
     */
    public function delete_address($data) {

        $map['is_delete'] = 1;
        $map['update_time'] = time();

        $user_id = db('wx_user')
                ->where('session_id', $data['session_id'])
                ->value('id');

        $result = db('address')
                ->where('id', $data['id'])
                ->where('user_id', $user_id)
                ->update($map);

        if (!$result) {
            exception('操作失败');
        }
    }

    /**
     * 添加收货地址
     * userName:收货人姓名
     * postalCode:邮编
     * provinceName:国标收货地址第一级地址
     * cityName:国标收货地址第二级地址
     * countyName:国标收货地址第三级地址
     * detailInfo:详细收货地址信息
     * nationalCode:收货地址国家码
     * telNumber:收货人手机号码
     */
    public function do_add_address($data) {


        $data['user_id'] = db('wx_user')
                ->where('session_id', $data['session_id'])
                ->value('id');
        $info = db('address')->where('user_id', $data['user_id'])->where('userName', $data['userName'])->where('detailInfo', $data['detailInfo'])->where('telNumber', $data['telNumber'])->where('is_delete', 0)->find();
        if ($info) {
            return $info['id'];
        } else {
            unset($data['session_id']);
            $data['create_time'] = time();
            $data['update_time'] = time();

            $address = $data['provinceName'] . $data['cityName'] . $data['countyName'] . $data['detailInfo'];

            $addresstolatlag = addresstolatlag($address);
            $data['latitude'] = $addresstolatlag[1];
            $data['longitude'] = $addresstolatlag[0];

            $result = db('address')->insertGetId($data);
            if (!$result) {
                exception('操作失败');
            }
            return $result;
        }
    }

    /**
     * 包房类型
     * company_id商家id
     * c_type=1包房类型
     * is_delete是否删除
     */
    public function room_type_list($data) {

        $list = db('company_config')
                ->field('id,c_name')
                ->where($data)
                ->order('c_sort asc , update_time desc')
                ->select();

        return $list;
    }

    /**
     * 包房类型名称
     */
    public function room_type_name($data) {

        $name_list = db('company_config')
                ->field('c_name')
                ->where($data)
                ->order('c_sort asc , update_time desc')
                ->select();

        $list = array();
        foreach ($name_list as $key => $value) {
            array_push($list, $value['c_name']);
        }

        return $list;
    }

    /**
     * 通过session_id获取用户余额
     */
    public function torage_list($session_id) {

        $info = db('wx_user')
                ->field('torage_value')
                ->where('session_id', $session_id)
                ->find();

        return $info;
    }

    /**
     * 通过session_id获取用户使用储值金日志
     */
    public function toragelog_list($user_id, $page, $page_size) {
        $torage_value_log = db('torage_value_log')
                ->where('user_id', $user_id)
                ->order('create_time desc')
                ->page($page, $page_size)
                ->select();
        foreach ($torage_value_log as $key => &$value) {
            switch ($value['type']) {
                case 1:
                    $value['type_name'] = '充值';
                    $value['hj_price'] = number_format(floatval($value['amount'] + $value['give_amount']), 2, ".", "");
                    break;
                case 2:
                    $value['type_name'] = '消费';
                    break;
                case 3:
                    $value['type_name'] = '退款';
                    break;
                default:
                    break;
            }
            $value['create_time'] = date("Y-m-d H:i:s", $value['create_time']);
            $value['amountnew'] = number_format(floatval($value['amount']), 2, ".", "");
        }
        return $torage_value_log;
    }

    /**
     * 查询充值储值金列表
     */
    public function toragevalue() {

        $info = db('torage_value')
                ->where('is_delete', 0)
                ->order('amount asc')
                ->select();
        return $info;
    }

    /**
     * 获取储值金详情
     */
    public function toragedetail($id) {
        $torage_value_log = db('torage_value_log')
                ->where('id', $id)
                ->find();
        switch ($torage_value_log['type']) {
            case 1:
                $torage_value_log['type_name'] = '充值';
                $torage_value_log['hj_price'] = number_format(floatval($torage_value_log['amount'] + $torage_value_log['give_amount']), 2, ".", "");
                break;
            case 2:
                $order = db('order')->where('order_id', $torage_value_log['order_id'])->find();
                $torage_value_log['type_name'] = '消费';
                $torage_value_log['order_sn'] = $order['order_sn'];
                break;
            case 3:
                $order = db('order')->where('order_id', $torage_value_log['order_id'])->find();
                $torage_value_log['type_name'] = '退款';
                $torage_value_log['order_sn'] = $order['order_sn'];
                break;
            default:
                break;
        }
        $torage_value_log['create_time'] = date("Y-m-d H:i:s", $torage_value_log['create_time']);
        $torage_value_log['amountnew'] = number_format(floatval($torage_value_log['amount']), 2, ".", "");
        return $torage_value_log;
    }

}
