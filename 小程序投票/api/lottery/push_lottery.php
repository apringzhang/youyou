<?php
/**
 * 大转盘接口
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/4/8
 * Time: 10:29
 */

require '../include/db.php';
require '../include/function.php';

try {
    $data = $_POST;
    /*测试数据*/
    // $data['session_id'] = '789d7ae61c17049b11c88cd17f7fb1eef478250c';
    // $data['activity_id'] = 26;
    $raw = check_session_id($data['session_id']);
    $time = time();
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    //检查用户积分表是否存在
    check_score($raw['openid'],$activity_id);
    //获取用户积分
    $sql = "SELECT * FROM `wangluo_user_score` WHERE `openid` = ? and `activity_id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['openid'],$activity_id]);
    $user_score = $sth->fetch(PDO::FETCH_ASSOC);
    //获取抽奖一次积分
    $sql = "SELECT `start_score` FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取活动失败');
    }
    $activity = $sth->fetch(PDO::FETCH_ASSOC);
    if ($user_score['score'] < $activity['start_score'])
    {
        throw new Exception('您的积分不足');
    }
    $rand = rand(1,100);
    //查询所有奖项
    $sql = "select * from `wangluo_award_rule` where `activity_id` = ? order by `odds` asc";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取奖项失败');
    }
    $award_rule = $sth->fetchAll(PDO::FETCH_ASSOC);
    check_empty($award_rule, '启动失败');
    foreach ($award_rule as $key => $value){
        if ($award_rule[$key]['num'] == -1)
        {
            //谢谢参与ID
            $participation_id =$award_rule[$key]['award_id'];
        }
    }
    //初始值
    $award['status'] = 0;
    $num = 0;
    foreach ($award_rule as $key => $value) {
        //初始数字
        $previous_num = $num;
        //结束数字
        $num += $award_rule[$key]['odds'];
        if ($previous_num < $rand && $rand <= $num && $award_rule[$key]['num'] > 0){
            $award['award_id'] = $award_rule[$key]['award_id'];
            $award['status'] = 1;
        }
    }
    if ($award['status'] == 1)
    {
        //修改规则数量
        $award_id = $award['award_id'];
        $sql ="UPDATE `wangluo_award_rule` set `num` = `num`-1 where `award_id` = ? and `activity_id` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$award_id,$activity_id]);
        if (!$result) {
            throw new Exception('更新失败');
        }
    } else {
        $award_id = $participation_id;
    }
    //修改用户积分
    $sql ="UPDATE `wangluo_user_score` set `score` = score-{$activity['start_score']} where `openid` = ? and `activity_id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['openid'],$activity_id]);
    if (!$result) {
        throw new Exception('修改信息失败');
    }
    //添加抽奖记录
    $sql = "INSERT INTO `wangluo_user_award` SET `openid` = ?, `activity_id` = ?, `award_id` = ?,`create_time` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$raw['openid'],$activity_id,$award_id,$time]);
    if (!$result) {
        throw new Exception('添加信息失败');
    }
    //查询中奖信息
    $sql = "select * from `wangluo_award` where `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$award_id]);
    $wangluo_award = $sth->fetch(PDO::FETCH_ASSOC);
    echo json_encode([
        'result' => $award['status'],
        'data' => $wangluo_award,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}