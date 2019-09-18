<?php
/**
 * 检查节点权限
 * @param $nodeName
 * @param int $roleId
 * @return bool
 */
function check($nodeName, $roleId = 0)
{
    if (empty($roleId)) {
        $roleId = session('admin.role_id');
    }
    //节点状态检查
    $node = db('node')
        ->where('node_name', $nodeName)
        ->where('is_delete', 0)
        ->find();
    if (!$node) {
        return false;
    }
    //角色状态检查
    $role = db('role')
        ->where('id', $roleId)
        ->where('is_delete', 0)
        ->find();
    if (!$role) {
        return false;
    }
    //权限检查
    $access = db('access')
        ->where('node_id', $node['id'])
        ->where('role_id', $role['id'])
        ->find();
    if ($access) {
        return true;
    } else {
        //检查子节点
        $nodeList = db('node')
            ->where('pid', $node['id'])
            ->where('is_delete', 0)
            ->select();
        foreach ($nodeList as $node) {
            if (check($node['node_name'], $role['id'])) {
                return true;
            }
        }
        return false;
    }
}

/**
 * 获取小程序access_token
 */
function get_mp_access_token($id)
{
    //第三方信息获取
    $component = db('component')->where('id',1)->find();
    $row = db('mp')->where('id',$id)->find();
    //判断access_token是否有效
    if ($row['access_token_create_time'] + $row['access_token_expire'] >= time()) {
        return $row['access_token'];
    }
    //刷新access_token
    $param = [
        'component_appid' => $component['appid'],
        'authorizer_appid' => $row['appid'],
        'authorizer_refresh_token' => $row['refresh_token'],
    ];
    $result = https_request('https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token='
        . $component['access_token'], json_encode($param));
    $result = json_decode($result, true);
    $data['access_token'] = $result['authorizer_access_token'];
    $data['access_token_expire'] = $result['expires_in'];
    $data['access_token_create_time'] = time();
    $refresh_token = $result['authorizer_refresh_token'];
    if (empty($data['access_token']) || empty($data['access_token_expire']) || empty($refresh_token)) {
        throw new Exception('获取access_token失败');
    }
    $mpInfo = db('mp')->where('id',$id)->update($data);
    if (!$mpInfo)
    {
        throw new Exception('修改mp表信息失败');
    }
    return $data['access_token'];
}

//https请求（支持GET和POST）
function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}