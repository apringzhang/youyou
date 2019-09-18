<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/23
 * Time: 14:40
 */

namespace app\admin\controller;

class Index extends Common
{
    public function index()
    {
        return view();
    }

    public function menu()
    {
        return view();
    }

    /**
     * 文件上传
     * @return \think\response\Json
     */
    public function upload()
    {
        $file = array_values(request()->file())[0];

        $info = $file->validate(['ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'upload');
        if ($info) {
            return json([
                'result' => 0,
                'url' => $info->getSaveName(),
            ]);
        } else {
            return json([
                'result' => 2,
                'message' => $file->getError(),
            ]);
        }
    }

    public function kindEditorUpload()
    {
        $file = array_values(request()->file())[0];

        $info = $file->validate(['ext' => 'jpg,png,gif,avi,mp4,swf'])->move(ROOT_PATH . 'public' . DS . 'upload');
        if ($info) {
            return json([
                'error' => 0,
                'url' => config('image_url') . DS . $info->getSaveName(),
            ]);
        } else {
            return json([
                'error' => 1,
                'message' => $file->getError(),
            ]);
        }
    }
}