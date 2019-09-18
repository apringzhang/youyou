<?php
//更新警告地市
set_time_limit(-1);

require '../include/db.php';
require '../include/function.php';
require '../include/config.php';

try {
    $sql = 'SELECT `id`, `voter_ip`, `voter_province`, `voter_city`, `voter_county` FROM `wangluo_vote_warnning`';
    $sth = $dbh->prepare($sql);
    $result = $sth->execute([]);
    if (!$result) {
        throw new Exception('获取列表失败');
    }
    $list = $sth->fetchAll(PDO::FETCH_ASSOC);
    foreach ($list as $key => $value) {
        if (!empty($value['voter_county'])) {
            continue;
        }
        $param = [
            'format' => 'json',
            'ip' => $value['voter_ip'],
        ];
        $location = json_decode(get('http://int.dpool.sina.com.cn/iplookup/iplookup.php', $param), true);
        $country = $location['country'];
        $province = $location['province'];
        $city = $location['city'];
        $sql = "UPDATE `wangluo_vote_warnning` SET `voter_county` = ?, `voter_province` = ?, `voter_city` = ? WHERE `id` = ?";
        $sth = $dbh->prepare($sql);
        $result = $sth->execute([$country, $province, $city, $value['id']]);
        if (!$result) {
            throw new Exception('更新数据失败');
        }
    }
} catch (Exception $e) {
    echo $e->getMessage();
}