<?php
/**
 * POST请求
 * @param $url
 * @param $param
 * @return mixed
 */
function post($url, $param)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * GET请求
 * @param $url
 * @param $param
 * @return mixed
 */
function get($url, $param)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($param));
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

/**
 * 获取小程序access_token
 */
function get_access_token($appid)
{
    global $dbh;
    $sql = "SELECT * FROM `wangluo_mp` WHERE `appid` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$appid]);
    if (!$result) {
        throw new Exception('查询数据失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception('appid不存在');
    }
    $access_token = $row['access_token'];
    //未获取access_token
    if (empty($access_token) || $row['access_token_create_time'] + $row['access_token_expire'] < time()) {
        //申请新access_token
        $param = [
            'grant_type' => 'client_credential',
            'appid' => CONFIG['APPID'],
            'secret' => CONFIG['SECRET'],
        ];
        $result = get('https://api.weixin.qq.com/cgi-bin/token', $param);
        $result = json_decode($result, true);
        $access_token = $result['access_token'];
        $access_token_expire = $result['expires_in'];
        if (empty($access_token) || empty($access_token_expire)) {
            throw new Exception('获取access_token失败');
        }
        //更新数据
        $sql = "UPDATE `wangluo_mp` SET `access_token` = ?, `access_token_create_time` = ?, `access_token_expire` = ?"
            . "WHERE `appid` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$access_token, time(), $access_token_expire, $appid]);
        if (!$result) {
            throw new Exception('修改数据失败');
        }
    }
    return $access_token;
}

/**
 * 验证session_id合法性
 * @param $session_id
 * @return array
 * @throws Exception
 */
function check_session_id($session_id)
{
    global $dbh;
    $sql = "SELECT * FROM `wangluo_wx_user` where `session_id` = ? LIMIT 1";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$session_id]);
    if (!$result) {
        throw new Exception("查询失败");
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception("验证失败");
    }
    return $row;
}


/**
 * 非空验证
 * @param $item
 * @param $message
 * @throws Exception
 */
function check_empty($item, $message)
{
    if (empty($item)) {
        throw new Exception($message);
    }
}

/**
 * 手机号验证
 * @param $item
 * @param $message
 * @throws Exception
 */
function check_mobile($item, $message)
{
    if (!preg_match('/^1[34578]\d{9}$/', $item)) {
        throw new Exception($message);
    }
}

/**
 * 01字段验证
 * @param $item
 * @param $message
 * @throws Exception
 */
function check_01($item, $message)
{
    if (!in_array(strval($item), ['0', '1'])) {
        throw new Exception($message);
    }
}

/**
 * 截取字符串长度
 * @param $str
 * @param int $length
 * @return string
 */
function check_substr($str, $length = 9)
{
    if (mb_strlen($str, 'utf8') > $length) {
        return mb_substr($str, 0, $length, 'utf-8') . '…';
    } else {
        return $str;
    }
}

/**
 * 判断用户积分是否存在积分表
 * @param $openid
 * @param $activity_id
 * @throws Exception
 */
function check_score($openid, $activity_id)
{
    global $dbh;
    //查询是否已有积分数据
    $sql = "SELECT * FROM `wangluo_user_score` WHERE `openid` = ? AND `activity_id` = ?";
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([$openid, $activity_id]);
    if (!$result) {
        throw new Exception('查询数据失败');
    }
    $row = $sth->fetch(PDO::FETCH_ASSOC);
    $time = time();
    if (!$row) {
        //插入数据
        $sql = "INSERT INTO `wangluo_user_score` SET `openid` = ?, `activity_id` = ?, `create_time` = ?, `update_time` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$openid, $activity_id, $time, $time]);
        if (!$result) {
            var_dump($sth->errorInfo());
            die;
            throw new Exception('插入数据失败2');
        }
    }
}
