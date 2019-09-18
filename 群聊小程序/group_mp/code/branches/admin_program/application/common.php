<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Request;

// 应用公共文件
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
 * 获取小程序access_token
 */
function get_access_token($appid) {
    $row = db('access_token')->where('appid',$appid)->select();
    if (!$row) {
        throw new Exception('appid不存在');
    }
    $access_token = $row['access_token'];
    //未获取access_token
    if (empty($access_token) || $row['access_token_create_time'] + $row['access_token_expire'] < time()) {
        //申请新access_token
        $param = [
            'grant_type' => 'client_credential',
            'appid' => config('APPID'),
            'secret' => config('SECRET'),
        ];
        import('tool.Curl');
        $curl = new \tool\Curl();
        $result = $curl->get('https://api.weixin.qq.com/cgi-bin/token', $param);
        
        $result = json_decode($result, true);
        $access_token = $result['access_token'];
        $access_token_expire = $result['expires_in'];
        if (empty($access_token) || empty($access_token_expire)) {
            throw new Exception('获取access_token失败');
        }
        //更新数据
        $map['access_token'] = $access_token;
        $map['access_token_create_time'] = time();
        $map['access_token_expire'] = $access_token_expire;
        $result = db('access_token')->where('appid',$appid)->update($map);
        if (!$result) {
            throw new Exception('修改数据失败');
        }
    }
    return $access_token;
}