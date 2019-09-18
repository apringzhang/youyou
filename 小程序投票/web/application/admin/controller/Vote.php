<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/5/23
 * Time: 14:58
 */

namespace app\admin\controller;

use think\Exception;

/**
 * 用户控制器
 * @package app\admin\controller
 */
class Vote extends Common
{
    /**
     * 列表页
     */
    public function maker()
    {
        //搜索
        $sign_id = input('post.sign_id');
        $data['sign_id'] = $sign_id;
        $VoteModel = new \app\admin\model\Vote();
        //页码
        $pageNum = 1;
        if (is_numeric(input('post.pageNum'))) {
            $pageNum = input('post.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $data['list'] = $VoteModel->get_list($pageNum,$numPerPage,$data['sign_id']);
        $data['count'] = $VoteModel->get_count();
        return view('', $data);
    }

    /**
     * 添加
     */
    public function add()
    {
        return view('');
    }

    /**
     * 执行添加
     */
    public function doadd()
    {
        $post = input('post.');
        $post['create_time'] = time();
        $post['update_time'] = time();
        $post['start_time'] = strtotime($post['start_time']);
        $post['stop_time'] = strtotime($post['stop_time']);
        /*$post['vote_per_sec'] =round(($post['vote_count']/($post['stop_time']-$post['start_time']))*20);
        if ($post['vote_per_sec'] > $post['vote_count'])
        {
            $post['vote_per_sec'] = $post['vote_count'];
        } else if ($post['vote_per_sec'] == 0)
        {
            $post['vote_per_sec'] = 1;
        }*/
        $vote_mark = db('vote_maker')->insert($post);
        if ($vote_mark)
        {
            return json([
                'statusCode' => 200,
                'message' => '添加成功',
                'navTabId' => 'votemark',
                'callbackType' => 'closeCurrent',
            ]);
        } else {
            return json([
                'statusCode' => 300,
                'message' => '添加失败',
            ]);
        }
    }

    /**
     * @param $id
     * @return \think\response\Json
     */
    public function doDelete($id)
    {
        $data['update_time'] = time();
        $data['is_delete'] = 1;
        $vote_maker = db('vote_maker')->where('id',$id)->update($data);
        if ($vote_maker)
        {
            return json([
                'statusCode' => 200,
                'message' => '删除成功',
                'navTabId' => 'votemark',
            ]);
        } else {
            return json([
                'statusCode' => 300,
                'message' => '删除失败',
            ]);
        }
    }

    public function check_time($time)
    {
    	$time = strtotime(input('post.time'));
    	$count = input('post.count');
    	$num = input('post.num');
    	$time_end = $count*20/$num+$time;
		$data = date('Y-m-d H:i:s',$time_end);
    	return $data;
    }

}