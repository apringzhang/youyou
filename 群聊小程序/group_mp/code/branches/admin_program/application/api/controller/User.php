<?php

/**
 * @Author: 玛瑙
 * @Date:   2018-08-07 18:37:36
 * @Last Modified by:   玛瑙
 * @Last Modified time: 2018-08-13 14:07:47
 */

namespace app\api\controller;

use think\Exception;

class User extends Common {

    /**
     * 获取数据库用户信息
     */
    public function get_user_info() {

        $data = json_decode(file_get_contents('php://input'), true);

        check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\User();

        try {
            $info = $model->get_user_info($data['session_id']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'message' => $info,
        ]);
    }

    /**
     * 获取商家信息
     */
    public function get_company_info() {

        $data = json_decode(file_get_contents('php://input'), true);

        check_empty($data['company_id'], 'company_id参数错误');

        $model = new \app\api\model\User();

        try {
            $info = $model->get_company_info($data['company_id']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'message' => $info,
        ]);
    }

    /**
     * 获取某个商品信息
     */
    public function get_goods_detail() {

        $data = json_decode(file_get_contents('php://input'), true);

        check_empty($data['goods_id'], 'goods_id参数错误');

        $model = new \app\api\model\User();

        try {
            $info = $model->get_goods_detail($data['goods_id'], $data['dian'], $data['company'], $data['session_id']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'message' => $info,
        ]);
    }

    /**
     * 获取首页标签商品列表
     */
    public function get_goods_list() {

        $data = json_decode(file_get_contents('php://input'), true);

        check_empty($data['company_id'], 'company_id参数错误');

        $model = new \app\api\model\User();

        try {
            $list = $model->get_goods_list($data['company_id']);
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
     * 首页排队取号
     */
    public function get_line_order() {

        $data = json_decode(file_get_contents('php://input'), true);

        $data['company_id'] = 1;
        $data['person_num'] = 1;
        $data['user_id'] = 1;

        check_empty($data['company_id'], 'company_id参数错误');
        check_empty($data['person_num'], 'person_num参数错误');
        check_empty($data['user_id'], 'user_id参数错误');

        $model = new \app\api\model\User();

        try {
            $info = $model->get_line_order($data);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'message' => $info,
        ]);
    }

    /**
     * 获取本地收货地址列表
     */
    public function get_address_list() {

        $data = json_decode(file_get_contents('php://input'), true);

        $model = new \app\api\model\User();

        try {
            $list = $model->get_address_list($data['session_id']);
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
     * 删除收货地址
     */
    public function delete_address() {

        $data = json_decode(file_get_contents('php://input'), true);

        check_empty($data['id'], 'id参数错误');
        check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\User();

        try {
            $model->delete_address($data);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'message' => "删除成功",
        ]);
    }

    /**
     * 添加收货地址
     */
    public function do_add_address() {

        $data = json_decode(file_get_contents('php://input'), true);

        check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\User();

        try {
            $result = $model->do_add_address($data);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        $add_id = $result;
        return json_encode([
            'result' => 0,
            'message' => "添加成功",
            'addressid' => $add_id
        ]);
    }

    /**
     * 包房类型
     */
    public function room_type_list() {

        $data = json_decode(file_get_contents('php://input'), true);
        $data['c_type'] = 1;
        $data['is_delete'] = 0;

        $model = new \app\api\model\User();

        try {
            $list = $model->room_type_list($data);
            $name_list = $model->room_type_name($data);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'list' => $list,
            'name_list' => $name_list
        ]);
    }

    /**
     * 加密解密
     */
    public function decode() {

        $data = json_decode(file_get_contents('php://input'), true);

        check_empty($data['string'], 'string参数错误');

        $key = config('aes_key');
        $iv = 'w2wJCnctEG09danPPI7SxQ==';

        $encrypted = base64_decode($data['string']);
        $string = openssl_decrypt($encrypted, 'aes-256-ecb', base64_decode($key), OPENSSL_RAW_DATA, base64_decode($iv));

        return json_encode([
            'result' => 0,
            'message' => $string
        ]);
    }

    /**
     * 获取某个照片商品信息
     */
    public function get_goods_content() {

        $data = json_decode(file_get_contents('php://input'), true);

        check_empty($data['goods_id'], 'goods_id参数错误');

        try {
            $info = db('goods')
                    ->where('is_delete', 0)
                    ->where('goods_id', $data['goods_id'])
                    ->where('company_id', $data['company'])
                    ->value('goods_content');
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'message' => $info,
        ]);
    }

    /**
     * 默认收货地址
     */
    public function get_default_address() {

        $data = json_decode(file_get_contents('php://input'), true);

        // check_empty($data['goods_id'], 'goods_id参数错误');
        $user_id = db('wx_user')->where('session_id', $data['session_id'])->value('id');
        try {
            db('address')->where('is_delete', 0)->where('user_id', $user_id)->setField('is_default', 0);
            $info = db('address')->where('is_delete', 0)->where('id', $data['address_id'])->setField('is_default', 1);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'message' => '设置成功',
        ]);
    }

    /**
     * 获取默认收货地址
     */
    public function getaddress_default() {

        $data = json_decode(file_get_contents('php://input'), true);

        // check_empty($data['goods_id'], 'goods_id参数错误');
        $user_id = db('wx_user')->where('session_id', $data['session_id'])->value('id');
        try {
            $info = db('address')->where('is_delete', 0)->where('user_id', $user_id)->where('is_default', 1)->find();
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        if (!$info) {
            $info = ['id' => ''];
        }
        return json_encode([
            'result' => 0,
            'message' => '设置成功',
            'data' => $info
        ]);
    }

    /**
     * 查询用户储值金金额
     */
    public function torage() {

        $data = json_decode(file_get_contents('php://input'), true);
        $session_id = $data['session_id'];
        $model = new \app\api\model\User();
        try {
            $list = $model->torage_list($session_id);
            $torage_value = number_format(floatval($list['torage_value']), 2, ".", "");
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'list' => $torage_value
        ]);
    }

    /**
     * 通过session_id获取用户使用储值金日志
     */
    public function torage_log() {
        $data = json_decode(file_get_contents('php://input'), true);
        $row = $this->session_id_check($data['session_id']);
        $user_id = $row['id'];
        //分页
        $page = intval($data['page']);
        if (empty($page)) {
            $page = 1;
        }
        $page_size = 10;
        $model = new \app\api\model\User();
        try {
            $list = $model->toragelog_list($user_id, $page, $page_size);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'list' => $list
        ]);
    }

    /**
     * 查询充值储值金列表
     */
    public function torage_value() {
        $model = new \app\api\model\User();
        try {
            $list = $model->toragevalue();
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'list' => $list
        ]);
    }

    /**
     * 获取储值金详情
     */
    public function torage_detail() {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $model = new \app\api\model\User();
        try {
            $list = $model->toragedetail($id);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'list' => $list
        ]);
    }

    /**
     * 领取成功
     * @return type
     */
    public function getCoupon() {
        $data = json_decode(file_get_contents('php://input'), true);
        check_empty($data['session_id'], 'session_id参数错误');
        check_empty($data['coupon_id'], 'coupon_id参数错误');
        try {
            //优惠券信息
            $coupon_info = db('coupon')->where('id', $data['coupon_id'])->find();
            if($coupon_info['status'] == '0'){
                return json_encode([
                    'result' => 0,
                    'status' => 4,
                    'coupon' => $coupon_info,
                    'message' => '该优惠活动已结束',
                ]);
            }
            $user_id = db('wx_user')->where('session_id', $data['session_id'])->value('id');
            //判断是否领取过
            $map1['user_id'] = $user_id;
            $map1['coupon_id'] = $data['coupon_id'];
            $old_coupon = db('coupon_log')->where($map1)->count();
            if (intval($old_coupon) == intval($coupon_info['person_count']) || intval($old_coupon) > intval($coupon_info['person_count'])) {
                //根据user_id和优惠券id查找领取记录
                $coupon_log_info = db('coupon_log')->where('coupon_id',$data['coupon_id'])->where('user_id',$user_id)->find();
                $coupon_info['create_time'] = date("Y-m-d", $coupon_log_info['end_time']);
                return json_encode([
                    'result' => 0,
                    'status' => 2,
                    'coupon' => $coupon_info,
                    'message' => '领取次数已达上限',
                ]);
            }
            //是否在领取期间
            $now_time = time();
            if ($now_time < $coupon_info['start_time']) {
                return json_encode([
                    'result' => 0,
                    'status' => 3,
                    'coupon' => $coupon_info,
                    'message' => '该优惠活动尚未开始',
                ]);
            }
            if ($now_time > $coupon_info['end_time']) {
                return json_encode([
                    'result' => 0,
                    'status' => 4,
                    'coupon' => $coupon_info,
                    'message' => '该优惠活动已结束',
                ]);
            }
            $all_old_coupon_count = db('coupon_log')->where('coupon_id',$data['coupon_id'])->count();
            if (intval($all_old_coupon_count) > $coupon_info['count'] || intval($all_old_coupon_count) == $coupon_info['count']) {
                return json_encode([
                    'result' => 0,
                    'status' => 5,
                    'coupon' => $coupon_info,
                    'message' => '该优惠活动已被领取完毕，请下次参加.',
                ]);
            }
            
            //领取时间
            $receive_time = time();
            //增加领取数量
            db('coupon')->where('id', $data['coupon_id'])->setInc('received_count',1);
            //有效期
            $validity_time = $coupon_info['validity_time'];
            //结束时间等于当前日期加上一天，然后再转换成日期格式 在加上59:59:59再转换成时间戳
            $end_time = strtotime(date('Y-m-d', 86400 * intval($validity_time) + intval($receive_time))." 23:59:59");
            $map['coupon_id'] = $data['coupon_id'];
            $map['status'] = 0;
            $map['user_id'] = $user_id;
            $map['receive_time'] = $receive_time;
            $map['end_time'] = $end_time;
            db('coupon_log')->insert($map);
            $coupon_info['create_time'] = date("Y-m-d", $end_time);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'status' => 1,
            'coupon' => $coupon_info,
            'message' => '领取成功',
        ]);
    }

}
