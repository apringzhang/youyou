<?php
/**
 * 投票
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/2/7
 * Time: 15:50
 */

require '../include/db.php';
require '../include/function.php';
require '../include/config.php';

try{
    $data = json_decode(file_get_contents('php://input'), true);
    $time = time();
    /*//测试数据
    $data['sign_id'] = 31;
    $data['session_id'] = '0117e702d21fb66ab000f28c1ece4d370a6a0edc';
    $data['appid'] = 'wx203278c5d20b52a4';
    $data['activity_id'] = 26;*/
    $raw = check_session_id($data['session_id']);
    check_empty($data['sign_id'], 'sign_id参数错误');
    $openid = $raw['openid'];
    check_empty($openid, 'openid参数错误');
    $appid = CONFIG['APPID'];
    check_empty($appid, 'appid参数错误');
    $activity_id = $data['activity_id'];
    check_empty($activity_id, 'activity_id参数错误');
    //获取活动
    $sql = "SELECT `start_time`, `stop_time` ,`rule_id` ,`online_flag`,`vote_score` FROM `wangluo_activity` WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        throw new Exception('获取活动失败');
    }
    $activity_item = $sth->fetch(PDO::FETCH_ASSOC);
    //判断投票是否结束
    if ($activity_item['start_time'] > $time)
    {
        throw new Exception('活动未开始');
    }
    if ($activity_item['stop_time'] < $time)
    {
        throw new Exception('活动已结束');
    }
    if ($activity_item['online_flag'] == 0)
    {
        throw new Exception('活动已下线');
    }
    //黑名单
    $black_list = [
        '182.110.30.89',
        '59.53.231.109',
        '113.116.112.225'
    ];
    if (in_array($_SERVER["REMOTE_ADDR"], $black_list)) {
        throw new Exception('IP已被封禁');
    }
    //检测锁定,非法投票
    check_sign($data['sign_id']);
    //验证投票并添加LOG表
    check_vote($appid,$openid,$activity_item['rule_id'],$data['sign_id'],$activity_id);
    //增加投票数
    $sql = "INSERT INTO `wangluo_vote` SET `create_time` = ?, `sign_id` = ?, `appid` = ?, 
`activity_id` = ?, `voter_openid` = ? ,`voter_ip` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([time(),$data['sign_id'],$appid,$activity_id,$openid,$_SERVER["REMOTE_ADDR"]]);
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('数据错误');
    }
    //增加活动投票数
    $sql = "UPDATE wangluo_activity set vote_count = vote_count+1,total_count=total_count+1 where id = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('系统错误');
    }
    //增加用户投票数
    $sql = "UPDATE `wangluo_activity_sign` set vote_count = vote_count+1,total_count=total_count+1,`update_time` = ?
 where id = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$time,$data['sign_id']]);
    if (!$result) {
        $dbh->rollBack();
        throw new Exception('系统错误');
    }
    //增加积分
    //do_score($openid,$activity_id,$activity_item['vote_score']);
    //增加红包do_red($data['sign_id'],$activity_id,$openid);;
    echo json_encode([
        'result' => 0,
        'message' => '投票成功',
    ]);
}  catch (Exception $e) {
    echo json_encode([
        'result' => 2,
        'message' => $e->getMessage(),
    ]);
}

function do_score($openid,$activity_id,$vote_score)
{
    global $dbh;
    check_score($openid,$activity_id);
    $sql = "update wangluo_user_score set score = score+? where `openid` = ? and `activity_id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$vote_score,$openid,$activity_id]);
    if (!$result) {
        throw new Exception('积分添加错误');
    }
}
/**
 *  查询投票规则/验证投票用户信息
 * @param $appid
 * @param $openid
 * @param $rule_id
 * @throws Exception
 */
function check_vote($appid,$openid, $rule_id,$sign_id,$activity_id)
{
    //查询投票规则
    global $dbh;
    $sql = "SELECT * FROM `wangluo_activity_rule` WHERE `id` = ? AND `is_delete` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$rule_id,0]);
    if (!$result) {
        throw new Exception('获取规则失败');
    }
    $rule_item = $sth->fetch(PDO::FETCH_ASSOC);
    //获取投票记录
    $vote_date = date('Y-m-d',time());
    $sql = "SELECT * FROM `wangluo_vote_log` WHERE  `appid` = ? AND `openid` = ? AND `activity_id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$appid,$openid,$activity_id]);
    if (!$result) {
        throw new Exception('获取记录失败');
    }
    $vote_item = $sth->fetch(PDO::FETCH_ASSOC);
    if (!empty($vote_item['vote_sign_ids']))
    {
        $sign_ids = $vote_item['vote_sign_ids'].','.$sign_id;
    } else {
        $sign_ids = $sign_id;
    }
    if ($vote_item)
    {
        //投票规则为1时
        if ($rule_item['rule_type'] == 1)
        {
            throw new Exception($rule_item['msg_fail']);
        }
        //判断投票日期
        if ($vote_item['vote_date'] == $vote_date)
        {
            switch ($rule_item['rule_type'])
            {
                case '2':
                    if ($vote_item['vote_sign_num'] >= $rule_item['vote_num'])
                    {
                        throw new Exception($rule_item['msg_fail']);
                    } else {
                        $sql = "UPDATE `wangluo_vote_log` SET `vote_time` = ? ,`vote_num` = vote_num+1 ,`vote_sign_num` = vote_sign_num+1, 
`vote_sign_ids` = ? where `appid` = ? AND `openid` = ? AND `activity_id` = ?";
                        $sth = $dbh->prepare($sql);
                        $result = $sth->execute([time(),$sign_ids,$appid,$openid,$activity_id]);
                        if (!$result) {
                            throw new Exception('规则2更新失败');
                        }
                    }
                    break;
                case '3':
                    //1：分钟 60 、2：小时 3600、3：天' 86400,
                    if ($rule_item['time_unit'] == 1)
                    {
                        $time_rule = $vote_item['vote_time'] + $rule_item['time_interval']*60;
                    }
                    if($rule_item['time_unit'] == 2)
                    {
                        $time_rule =$vote_item['vote_time'] +  $rule_item['time_interval']*3600;
                    }
                    if ($rule_item['time_unit'] == 3)
                    {
                        $time_rule =$vote_item['vote_time'] +  $rule_item['time_interval']*86400;
                    }
                    //判断时间
                    $time = time();
                    if ($time_rule < $time)
                    {
                        $sql = "UPDATE `wangluo_vote_log` SET `vote_time` = ? ,`vote_num` = vote_num+1 ,`vote_sign_num` = vote_sign_num+1, 
`vote_sign_ids` = ? where `appid` = ? AND `openid` = ? AND `activity_id` = ?";
                        $sth = $dbh->prepare($sql);
                        $result = $sth->execute([time(),$sign_ids,$appid,$openid,$activity_id]);
                        if (!$result) {
                            throw new Exception('规则3更新失败');
                        }
                    } else {
                        throw new Exception($rule_item['msg_fail']);
                    }
                    break;
                case '4':
                    $ids_list = explode(',',$vote_item['vote_sign_ids']);
                    foreach ($ids_list as $key => $value)
                    {
                        if ($ids_list[$key] == $sign_id)
                        {
                            throw new Exception($rule_item['msg_fail']);
                        }
                    }
                    //判断人数
                    if ($vote_item['vote_sign_num'] >= $rule_item['user_num']) {
                        throw new Exception($rule_item['irregularities']);
                    }
                    $sql = "UPDATE `wangluo_vote_log` SET `vote_time` = ? ,`vote_num` = vote_num+1 ,`vote_sign_num` = vote_sign_num+1, 
`vote_sign_ids` = ? where `appid` = ? AND `openid` = ? AND `activity_id` = ?";
                    $sth = $dbh->prepare($sql);
                    $result = $sth->execute([time(),$sign_ids,$appid,$openid,$activity_id]);
                    if (!$result) {
                        throw new Exception('规则4更新失败');
                    }
                    break;
            }
        } else {
            $sql = "UPDATE `wangluo_vote_log` SET `vote_date` = ? , `vote_time` = ? ,`vote_num` = ? ,`vote_sign_num` = ? ,`vote_sign_ids` = ?
where `appid` = ? AND `openid` = ? AND `activity_id` = ?";
            $sth = $dbh->prepare($sql);
            $result = $sth->execute([$vote_date,time(),1,1,$sign_id,$appid,$openid,$activity_id]);
            if (!$result) {
                throw new Exception('更新失败');
            }
        }
    } else {
        $sql = "INSERT INTO `wangluo_vote_log` SET `appid` = ?, `openid` = ?, `vote_date` = ?, `vote_time` = ?, 
`vote_num` = ?, `vote_sign_num` = ? ,`vote_sign_ids` = ?,`activity_id` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$appid,$openid,$vote_date,time(),1,1,$sign_id,$activity_id]);
        if (!$result) {
            throw new Exception('创建失败');
        }
    }
}

function do_red($sign_id,$activity_id,$openid)
{
    global $dbh;
    /*获取活动单个拉票红包最大金额*/
    $sql = "SELECT `max_red_packet` from `wangluo_activity` where `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$activity_id]);
    if (!$result) {
            throw new Exception('查询活动失败');
    }
    $activity_item = $sth->fetch(PDO::FETCH_ASSOC);

    /*获取拉票红包总数*/
    $sql = "SELECT `red_packet` from `wangluo_activity_sign` where `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$sign_id]);
    if (!$result) {
            throw new Exception('查询个人失败');
    }
    $red_packet = $sth->fetch(PDO::FETCH_ASSOC);
    /*红包数*/
    $max_red = $activity_item['max_red_packet']*100;
    $red = mt_rand(1,$max_red)/100;

    /*增加个人红包*/
    $sql = "UPDATE `wangluo_wx_user` SET `red_packet` = red_packet+? WHERE `openid` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$red,$openid]);
    if (!$result) {
            throw new Exception('增加金额失败');
    }

    /*减少报名红包*/
    $sql = "UPDATE `wangluo_activity_sign` SET `red_packet` = red_packet-? WHERE `id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$red,$sign_id]);
    if (!$result) {
        throw new Exception('增加金额失败');
    }

}

function check_sign($sign_id)
{
    global $dbh;
    $sql = "SELECT * FROM `wangluo_activity_sign` WHERE `id` = ? AND `is_delete` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$sign_id,0]);
    $check_sign = $sth->fetch(PDO::FETCH_ASSOC);
    if ($check_sign['is_lock'] == 1) {
        throw new Exception('请勿非法投票');
    }
}