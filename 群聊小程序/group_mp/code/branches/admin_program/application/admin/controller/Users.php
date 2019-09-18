<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2019/5/27
 * Time: 10:24
 */

namespace app\admin\controller;

use think\Exception;

class Users extends Common
{
    public function index()
    {
        $name = input('post.name');
        $data['name'] = $name;
        $is_vip = input('post.is_vip');
        $data['is_vip'] = $is_vip;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Users();
        $data['pageNum'] = $pageNum;
        $list = $model->getUsersList($name, $is_vip, $pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getUsersListCount($name, $is_vip);
        $data['count'] = $count;
        return view('',$data);
    }

    public function gift()
    {
        $nick_name = input('post.nick_name');
        $data['nick_name'] = $nick_name;
        $state = input('post.state');
        $data['state'] = $state;
        $activity = input('post.activity');
        $data['activity'] = $activity;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Users();
        $data['pageNum'] = $pageNum;
        $list = $model->getGiftList($nick_name, $state, $activity,$pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getGiftListCount($nick_name, $state,$activity);
        $data['count'] = $count;
        $data['activitylist'] = db('activity')->select();
        return view('',$data);
    }

    public function Coupon()
    {
        $nick_name = input('post.nick_name');
        $data['nick_name'] = $nick_name;
        $type = input('post.type');
        $data['type'] = $type;
        $activity = input('post.activity');
        $data['activity'] = $activity;
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $this->assign('page_num', $pageNum);
        $num_per_page = 20;
        $data['num_per_page'] = $num_per_page;
        $model = new \app\admin\model\Users();
        $data['pageNum'] = $pageNum;
        $list = $model->getTorechargeList($nick_name, $type, $activity,$pageNum, $num_per_page);
        $data['list'] = $list;
        $count = $model->getTorechargeListCount($nick_name, $type,$activity);
        $data['count'] = $count;
        $data['activitylist'] = db('activity')->select();
        return view('',$data);
    }

}