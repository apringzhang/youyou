<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/29
 * Time: 14:01
 */

namespace app\api\controller;

use think\Controller;

class Common extends Controller
{
    //请求参数数组
    protected $param;

    protected function _initialize()
    {
        $param = input('post.param');
        if (empty($param)) {
            $this->returnData('2', 'param参数错误');
        }
        $aes = new \crypt\Aes(config('aes_key'));
        $this->param = json_decode($aes->decode($param), true);
        $action = $this->param['action'];
        if (empty($action)) {
            $this->returnData('2', 'action参数错误');
        }
        $this->$action();
        die;
    }

    public function __call($method, $args)
    {
        $this->returnData('2', $method . '操作不存在');
    }

    /**
     * 返回数据
     * @param $result
     * @param $data
     */
    protected function returnData($result, $data)
    {
        $return['result'] = strval($result);
        if ($result == '0' && empty($data)) {
            $return['result'] = '1';
            $return['message'] = 'empty';
            $return['data'] = '';
        } elseif (is_string($data)) {
            $return['message'] = $data;
            $return['data'] = '';
        } else {
            $return['message'] = 'success';
            import('Common.Util.Tools');
            $return['data'] = $return['data'] = \tool\String::parseArrayNotString($data);
        }
        $aes = new \crypt\Aes(config('aes_key'));
        echo $aes->encode(json_encode($return));
        die;
    }

}