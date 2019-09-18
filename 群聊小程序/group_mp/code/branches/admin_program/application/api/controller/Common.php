<?php
/**
 * Created by PhpStorm.
 * User: LS
 * Date: 2018/8/6
 * Time: 14:06
 */


namespace app\api\controller;

use think\Controller;
use think\Exception;


class Common extends Controller
{
    protected function _initialize()
    {

    }

    /**
     * 验证session_id 并返回用户信息
     * @param $session_id
     * @return array
     * @throws \Exception
     */
    public function session_id_check($session_id)
    {
        try {
            $user = check_session_id($session_id);
        } catch (Exception $e) {
            echo json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);die;
        }
        return $user['id'];
    }
}
