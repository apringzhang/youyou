<?php

/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 9:41
 */

namespace app\admin\controller;

use think\Exception;
use think\Request;

class Guestbook extends Common {

    /**
     * 留言列表
     * @return \think\response\View
     */
    public function index() {
        $data = [];
        $activity_id = input('activity_id');
        //搜索
        $start_time = input('request.start_time');
        $data['start_time'] = $start_time;
        $stop_time = input('request.stop_time');
        $data['stop_time'] = $stop_time;
        if ($start_time && $stop_time) {
            $start = strtotime($start_time.'00:00:00');
            $stop = strtotime($stop_time.'23:59:59');
            if ($start > $stop) {
                return json([
                    'statusCode' => 300,
                    'message' => '起始日期不可大于结束日期',
                ]);
            }
        }
        
        if ($start_time && !$stop_time) {
            return json([
                'statusCode' => 300,
                'message' => '请选择结束日期',
            ]);
        }
        if (!$start_time && $stop_time) {
            return json([
                'statusCode' => 300,
                'message' => '请选择起始日期',
            ]);
        }
        //页码
        $pageNum = 1;
        if (is_numeric(input('request.pageNum'))) {
            $pageNum = input('request.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $Guestbook = new \app\admin\model\Guestbook();
        //列表数据
        $list = $Guestbook->getList($pageNum, $numPerPage, $activity_id, $start, $stop);
        if ($list) {
            foreach ($list as &$value) {
                $value['activity_name'] = db('activity')->where('id', $activity_id)->value('activity_name');
                $value['username'] = db('activity_sign')->where('id', $value['sign_id'])->value('username');
            }
        }
        $data['list'] = $list;
        //数据总数
        $count = $Guestbook->getCount($activity_id, $start, $stop);
        $data['count'] = $count;
        return view('', $data);
    }

    /**
     * 查看留言内容
     * @return \think\response\View
     */
    public function detail() {
        $data['content'] = db('guestbook')->where('id', input('get.id'))->value('content');
        return view('', $data);
    }

    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete() {
        $id = input('get.id');
        $activity_id = input('get.activity_id');
        $Guestbook = new \app\admin\model\Guestbook();
        try {
            $Guestbook->doDelete($id, $activity_id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'signManage',
        ]);
    }


}
