<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 15:41
 */

namespace app\admin\controller;

use think\Controller;
use think\Request;

class Common extends Controller
{
    protected function _initialize()
    {
        if (empty(session('admin'))) {
            $this->redirect('Open/login');
        } else {
            $this->record();
        }

    }

    /**
     * 记录后台操作
     */
    protected function record()
    {
        $request = Request::instance();
        $data['user_account'] = session('admin.id');
        $data['action'] = $request->controller() .'/'. $request->action();
        $data['create_time'] = time();
        db('admin_user_log')->insert($data);
    }
    
    public function get_access_token($appid)
    { 
        $row = db('mp')->where('appid', $appid)->find();
        if (!$row) {
            exception('appid不存在');
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
            $result = self::get('https://api.weixin.qq.com/cgi-bin/token', $param);
            $result = json_decode($result, true);
            $access_token = $result['access_token'];
            $access_token_expire = $result['expires_in'];
            if (empty($access_token) || empty($access_token_expire)) {
                exception('获取access_token失败');
            }
            //更新数据
            $arr = [
                'access_token' => $access_token,
                'access_token_create_time' => time(),
                'access_token_expire' => $access_token_expire
            ];
            $result = db('mp')->where('appid', $appid)->update($arr);
            if (!$result) {
                exception('修改数据失败');
            }
        }
        return $access_token;
    }
    
    /**
     * POST请求
     * @param $url
     * @param $param
     * @return mixed
     */
    public static function post($url, $param)
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
    public static function get($url, $param)
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
}