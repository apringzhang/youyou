<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/6
 * Time: 10:56
 */

namespace app\api\controller;

use think\Exception;

class Login extends Common
{
    /**
     * 获取openid及session_token并返回session_id
     * @return string
     */
    public function code()
    {
        $data = input('get.');
        //$session_id = $data['session_id'];
        //$this->session_id_check($session_id);
        $code = $data['code'];
        $appid = config('APPID');

        $param = [
            'appid' => $appid,
            'secret' => config('SECRET'),
            'js_code' => $code,
            'grant_type' => 'authorization_code',
        ];
        $res = \tool\Curl::get('https://api.weixin.qq.com/sns/jscode2session',$param);
        $res = json_decode($res,true);
        $activityModel = new \app\api\model\Code();
        try {
            $session_id = $activityModel->check_code($res,$appid);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => '添加成功',
            'session_id' => $session_id
        ]);
    }

    /**
     * 更新保存用户信息
     */
    public function user_info()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $session_id = $data['session_id'];
        if (empty($session_id)) {
            return json_encode([
                'result' => 2,
                'message' => 'session_id参数错误',
            ]);
        }
        $activityModel = new \app\api\model\Code();
        try {
            $activityModel->user_info($data['info'],$session_id);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'message' => '修改成功',
        ]);
    }
}