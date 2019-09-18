<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/6
 * Time: 11:16
 */

namespace app\api\model;

class Code
{
    /**
     * 验证用户信息
     * @param $data
     * @param $appid
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function check_code($data,$appid)
    {
        //创建本地数据
    	$session_key = $data['session_key'];
    	$openid = $data['openid'];
    	if (empty($session_key) || empty($openid)) {
    		exception('接口调用失败');
    	}
    	$session_id = sha1(uniqid());
        //查询是否已有数据
        $user = db('wx_user')->where('openid',$openid)->find();
        $user_weixin_data['appid'] = $appid;
        $user_weixin_data['openid'] = $openid;
        $user_weixin_data['session_key'] = $session_key;
        $user_weixin_data['session_id'] = $session_id;
        $user_weixin_data['create_time'] = time();
        $user_weixin_data['update_time'] = time();
        if (!$user) {
            $user_weixin = db('wx_user')->insert($user_weixin_data);
            if (!$user_weixin) {
                exception('插入数据失败');
            }
        } else {
            //更新数据
          $user_weixin = db('wx_user')->where('openid',$openid)->update($user_weixin_data);
          if (!$user_weixin) {
             exception('修改数据失败');
         }
     }

     return $session_id;
 }

    /**
     * 更改用户信息
     * @param $data
     */
    public function user_info($info,$session_id)
    {
        $wx_user = db('wx_user')->where('session_id',$session_id)->find();
        if (!$wx_user) {
            exception('session_id错误');
        }

        $session_key = $wx_user['session_key'];
        $sign = sha1($info['rawData'] . $session_key);
        if ($info['signature'] != $sign) {
            exception('签名错误');
        }
        //修改数据
        $user_info['nick_name'] = $info['userInfo']['nickName'];
        $user_info['avatar_url'] = $info['userInfo']['avatarUrl'];
        $user_info['gender'] = $info['userInfo']['gender'];
        $user_info['city'] = $info['userInfo']['city'];
        $user_info['province'] = $info['userInfo']['province'];
        $user_info['country'] = $info['userInfo']['country'];
        $user_info['language'] = $info['userInfo']['language'];
        $user = db('wx_user')->where('id',$wx_user['id'])->update($user_info);
    	//用户openid
        $openid = db('wx_user')->where('id',$wx_user['id'])->value('openid');

        if (!$user) {
    		//如果没有openid表示数据库中没有该用户信息
    		//如果有openid表示没有信息可修改
          if(empty($openid)) {
             exception('修改数据失败');
         }
         // else {
         //     exception('暂无信息修改');
         // }
     }
 }
}
