<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 9:41
 */

namespace app\admin\controller;

use think\Exception;

class Activity extends Common
{
    /**
     * 列表
     * @return \think\response\View
     */
    public function index()
    {
        $data = [];
        //搜索
        $activityName = input('post.activityName');
        $data['activityName'] = $activityName;
        $activityType = input('post.activityType');
        $data['activityType'] = $activityType;
        //页码
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $activityModel = new \app\admin\model\Activity();
        //列表数据
        $list = $activityModel->getList($pageNum, $numPerPage, $activityName, $activityType);
        $data['list'] = $list;
        //数据总数
        $count = $activityModel->getCount($activityName , $activityType);
        $data['count'] = $count;
        return view('', $data);
    }


    /**
     * 添加
     * @return \think\response\View
     */
    public function add()
    {
        $data = [];
        $activityModel = new \app\admin\model\Activity();
        $data['attr_values'] = db('activity_rule')->where('is_delete', 0)->select();
        $data['activity_category'] = db('activity_category')->where('is_delete', 0)->select();
        $data['mp_value'] =$activityModel->getActivityWechatList();
        return view('', $data);
    }

    /**
     * 执行添加
     * @return \think\response\Json
     */
    public function doAdd()
    {
        $data = input('post.');
        $data['apply_start_time'] = strtotime(input('post.apply_start_time'));
        $data['apply_stop_time'] = strtotime(input('post.apply_stop_time'));
        $data['start_time'] = strtotime(input('post.start_time'));
        $data['stop_time'] = strtotime(input('post.stop_time'));
        unset($data['gift_name']);
        if ($data['apply_start_time'] > $data['apply_stop_time']) {
            return json([
                'statusCode' => 300,
                'message' => '报名开始时间不能超过报名结束时间',
            ]);
        }
        if ($data['apply_stop_time'] > $data['start_time']) {
            return json([
                'statusCode' => 300,
                'message' => '报名结束时间不能超过活动开始时间',
            ]);
        }
        if ($data['start_time'] > $data['stop_time']) {
            return json([
                'statusCode' => 300,
                'message' => '活动开始时间不能超过活动结束时间',
            ]);
        }
        if (!$data['rule_id']) {
            return json([
                'statusCode' => 300,
                'message' => '请选择活动规则',
            ]);
        }
        if (!$data['category_id']) {
            return json([
                'statusCode' => 300,
                'message' => '请选择活动分类',
            ]);
        }
        // if (!$data['theme_color']) {
        //     return json([
        //         'statusCode' => 300,
        //         'message' => '请按正确操作选择您的主题颜色',
        //     ]);
        // }
        // if (!$data['check_color']) {
        //     return json([
        //         'statusCode' => 300,
        //         'message' => '请按正确操作选择您的选中颜色',
        //     ]);
        // }
        // if (!preg_match("/^#([0-9a-fA-F]{6}|[0-9a-fA-F]{3})$/", $data['theme_color'])) {
        //     return json([
        //         'statusCode' => 300,
        //         'message' => '主题颜色格式不正确',
        //     ]);
        // }
        $activityModel = new \app\admin\model\Activity();
        try {
            $activityModel->doAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'activityManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 修改
     * @return \think\response\View
     */
    public function modify()
    {
        $data = db('activity')->where('id', input('get.id'))->find();
        $data['attr_values'] = db('activity_rule')->where('is_delete', 0)->select();
        $arr = explode(',', $data['gift_ids']);
        foreach ($arr as $value) {
            $gift_name[] = db('gift')->where('id', $value)->value('gift_name');
        }
        $data['gift_name'] = implode(',', $gift_name);
        //小程序列表
        $activityModel = new \app\admin\model\Activity();
        $data['mp_value'] =$activityModel->getActivityWechatList();
        $data['mp_id'] =$activityModel->getActivityWechat(input('get.id'));
        $data['activity_category'] = db('activity_category')->where('is_delete', 0)->select();
        return view('', $data);
    }

    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doModify()
    {
        $data = input('post.');
        $data['apply_start_time'] = strtotime(input('post.apply_start_time'));
        $data['apply_stop_time'] = strtotime(input('post.apply_stop_time'));
        $data['start_time'] = strtotime(input('post.start_time'));
        $data['stop_time'] = strtotime(input('post.stop_time'));
        unset($data['gift_name']);
        if ($data['apply_start_time'] > $data['apply_stop_time']) {
            return json([
                'statusCode' => 300,
                'message' => '报名开始时间不能超过报名结束时间',
            ]);
        }
        if ($data['apply_stop_time'] > $data['start_time']) {
            return json([
                'statusCode' => 300,
                'message' => '报名结束时间不能超过活动开始时间',
            ]);
        }
        if ($data['start_time'] > $data['stop_time']) {
            return json([
                'statusCode' => 300,
                'message' => '活动开始时间不能超过活动结束时间',
            ]);
        }
        if (!$data['rule_id']) {
            return json([
                'statusCode' => 300,
                'message' => '请选择活动规则',
            ]);
        }
        if (!$data['category_id']) {
            return json([
                'statusCode' => 300,
                'message' => '请选择活动分类',
            ]);
        }
        // if (!$data['theme_color']) {
        //     return json([
        //         'statusCode' => 300,
        //         'message' => '请按正确操作选择您的主题颜色',
        //     ]);
        // }
        // if (!$data['check_color']) {
        //     return json([
        //         'statusCode' => 300,
        //         'message' => '请按正确操作选择您的选中颜色',
        //     ]);
        // }
        $activityModel = new \app\admin\model\Activity();
        try {
            $activityModel->doModify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'activityManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete()
    {
        $id = input('get.id');
        $activityModel = new \app\admin\model\Activity();
        try {
            $activityModel->doDelete($id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'activityManage',
        ]);
    }

    /**
     * 活动颜色
     */
    public function pay()
    {
         return view();
    }

    /**
     * 复制活动
     */
    public function copy()
    {
         return view();
    }

    /**
     * 复制活动添加
     */
    public function doCopy()
    {
        $id = input('post.id');
        $activity_name = input('post.activity_name');
        $info = db('activity')->where('id', $id)->find();
        $mp = db('activity_wechat')->where('activity_id',$info['id'])->find();
        $data = [
            'activity_name' => $activity_name,
            'activity_image' => $info['activity_image'],
            'start_time' => $info['start_time'],
            'stop_time' => $info['stop_time'],
            'activity_type' => $info['activity_type'],
            'rule_id' => $info['rule_id'],
            'audit_flag' => $info['audit_flag'],
            'gift_ids' => $info['gift_ids'],
            'activity_desc' => $info['activity_desc'],
            'vote_bottom' => $info['vote_bottom'],
            'order_prefix' => $info['order_prefix'],
            'receive_side' => $info['receive_side'],
            'pay_appid' => $info['pay_appid'],
            'pay_mchid' => $info['pay_mchid'],
            'pay_key' => $info['pay_key'],
            'pay_appsecret' => $info['pay_appsecret'],
            'pay_body' => $info['pay_body'],
            'start_score' => $info['start_score'],
            'vote_score' => $info['vote_score'],
            'max_red_packet' => $info['max_red_packet'],
            'is_gift' => $info['is_gift'],
            'is_sign' => $info['is_sign'],
            'is_coerce' => $info['is_coerce'],
            'online_flag' => $info['online_flag'],
            'activity_notice' => $info['activity_notice'],
            'mp_id' => $mp['mp_id'],
        ];
        $activityModel = new \app\admin\model\Activity();
        try {
            $activityModel->doAdd($data, $id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '复制成功',
            'navTabId' => 'activityManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 选择礼物
     * @return [type] [description]
     */
    public function gift()
    {
        //搜索
        $giftName = input('post.giftName');
        $data['giftName'] = $giftName;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        $list = db('gift')
        ->where('is_delete', 0)
        ->where(function($query) use ($giftName) {
            if ($giftName) {
                $query->where('gift_name', 'like', "%{$giftName}%");
            }
        }) 
        ->page($pageNum, 20)
        ->select();
        $count = db('gift')->where('is_delete', 0)->count();
        $data['list'] = $list;
        $data['count'] = $count;
        return view('', $data);
    }

    /**
     * 积分管理
     * @return [type] [description]
     */
    public function score()
    {
        $id = input('get.id');
        $data['vote_score'] = db('activity')->where('id', $id)->value('vote_score');
        $gift_ids = db('activity')->where('id', $id)->value('gift_ids');
        $gift = explode(',', $gift_ids);
        $data['gift_ids'] = [];
        if ($gift_ids) {
            foreach ($gift as $key => $value) {
                $data['gift_ids'][$key]['id'] = $value;
                $data['gift_ids'][$key]['name'] = db('gift')->where('id', $value)->value('gift_name');
                $data['gift_ids'][$key]['score'] = db('score_rule')->where('activity_id', $id)->where('gift_id', $value)->value('score');
            }
        }
        return view('', $data);
    }

    /**
     * 添加积分管理
     * @return [type] [description]
     */
    public function doscore()
    {
        $activity_id = input('post.activity_id');
        $data['gift_id'] = input('post.gift_id/a');
        $data['score'] = input('post.score/a');
        db('score_rule')->where('activity_id', $activity_id)->delete();
        db('activity')->where('id', $activity_id)->setField('vote_score', input('post.vote_score'));
        $arr = [];
        foreach ($data['gift_id'] as $key => $value) {
            $arr['activity_id'] = $activity_id;
            $arr['gift_id'] = $value;
            $arr['score'] = $data['score'][$key];
            db('score_rule')->insert($arr);
        }
        return json([
            'statusCode' => 200,
            'message' => '操作成功',
            'navTabId' => 'activityManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 抽奖管理
     * @return [type] [description]
     */
    public function award()
    {
        $id = input('get.id');
        //$data['vote_score'] = db('activity')->where('id', $id)->value('vote_score');
        $award = db('award')->column('id');
        $data['award'] = [];
        if ($award) {
            foreach ($award as $key => $value) {
                $data['award'][$key]['award_id'] = $value;
                $data['award'][$key]['name'] = db('award')->where('id', $value)->value('name');
                $data['award'][$key]['odds'] = db('award_rule')->where('activity_id', $id)->where('award_id', $value)->value('odds');
                $data['award'][$key]['num'] = db('award_rule')->where('activity_id', $id)->where('award_id', $value)->value('num');
            }
        }
        return view('', $data);
    }

    /**
     * 添加抽奖管理
     * @return [type] [description]
     */
    public function doaward()
    {
        $activity_id = input('post.activity_id');
        $data['award_id'] = input('post.award_id/a');
        $data['odds'] = input('post.odds/a');
        $data['num'] = input('post.num/a');
        $num = array_sum($data['odds']);
        if ($num !== 100) {
            return json([
                'statusCode' => 300,
                'message' => '奖品百分比总和必须是100%',
            ]);
        }
        $i = 0;
        foreach ($data['num'] as $val) {
            if ($val == -1) {
                $i++;
            }
            if (!preg_match('/^\d|-1$/', $val)) {
                return json([
                    'statusCode' => 300,
                    'message' => '奖品数量只能是正整数和-1',
                ]);
            }
        }
        if ($i != 1) {
            return json([
                'statusCode' => 300,
                'message' => '必须且仅能设置一件数量为-1的不限制奖品！',
            ]);
        }
        db('award_rule')->where('activity_id', $activity_id)->delete();
        //db('activity')->where('id', $activity_id)->setField('vote_score', input('post.vote_score'));
        $arr = [];
        foreach ($data['award_id'] as $key => $value) {
            $arr['activity_id'] = $activity_id;
            $arr['award_id'] = $value;
            $arr['odds'] = $data['odds'][$key];
            $arr['num'] = $data['num'][$key];
            db('award_rule')->insert($arr);
        }
        return json([
            'statusCode' => 200,
            'message' => '操作成功',
            'navTabId' => 'activityManage',
            'callbackType' => 'closeCurrent',
        ]);
    }
    
    public function code()
    {
        return view();
    }
    
    /**
     * 二维码
     */
    public function codes()
    {
        $id = input('get.id');
        //获取分享二维码
        $access_token = self::get_access_token(config('APPID'));
        $param = [
            'scene' => urlencode($id),
            'page' => 'pages/index/index',
        ];
        $qrcode_string = self::post('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token, json_encode($param));
        $qrcode_size = getimagesizefromstring($qrcode_string);
        $qrcode = imagecreatefromstring($qrcode_string);
        header('Content-Type: image/png');
        imagepng($qrcode);
    }
    
    /**
     * 活动分类
     */
    public function category()
    {

        $data = [];
        //搜索
        $activityName = input('post.activityName');
        $data['activityName'] = $activityName;
        //页码
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $activityModel = new \app\admin\model\Activity();
        //列表数据
        $list = $activityModel->getCategoryList($pageNum, $numPerPage, $activityName);
        $data['list'] = $list;
        //数据总数
        $count = $activityModel->getCategoryCount($activityName , $activityType);
        $data['count'] = $count;
        return view('', $data);
    }

    public function category_add()
    {
        return view('', $data);
        
    }

    public function do_category_add()
    {
        $data = input('post.');
        $activityModel = new \app\admin\model\Activity();
        try {
            $activityModel->doCategoryAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'categoryManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 修改
     * @return \think\response\View
     */
    public function category_modify($id)
    {
        $data = db('activity_category')->where('id',$id)->find();
        return view('', $data);
    }


    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doCategoryModify()
    {
        $data = input('post.');
        $activityModel = new \app\admin\model\Activity();
        try {
            $activityModel->doCategoryModify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'categoryManage',
            'callbackType' => 'closeCurrent',
        ]);
    }


    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doCategoryDelete()
    {
        $id = input('get.id');
        $activityModel = new \app\admin\model\Activity();
        try {
            $activityModel->doCategoryDelete($id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'categoryManage',
        ]);
    }
    
}