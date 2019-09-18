<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 14:40
 */

namespace app\api\controller;

use think\Exception;


class Navigation extends Common
{
    /**
     * 获取导航信息
     */
    public function navigation_info()
    {
        $data = json_decode(input('post.'),true);
        $NavigationModel = new \app\api\model\Navigation();
        try {
            $res = $NavigationModel->get_navigation();
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $res,
        ]);
    }





    public function get_information()
    {
        $data = json_decode(input('post.'),true);
        $NavigationModel = new \app\api\model\Navigation($data['nav_id']);
        try {
            $res = $NavigationModel->get_information();
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $res,
        ]);
    }

    //首页轮播图
    public function get_carousel()
    {
        $data = json_decode(input('post.'),true);
        $NavigationModel = new \app\api\model\Navigation();
        try {
            $res = $NavigationModel->get_carousel();
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $res,
        ]);
    }
}