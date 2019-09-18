<?php

/**
 */

namespace app\api\controller;

use think\Exception;
use think\Controller;
use think\Db;

class Group extends Common {

    /**
     * 获取数据库用户信息
     */
    public function group_message() {

        $data = json_decode(file_get_contents('php://input'), true);
        $uid = $this->session_id_check($data['session_id']);
        // $data['uid'] = 186;
        // $data['group'] = 6;
        try {
            $year = date('Y-m-d', time());
            $xs = date('H', time());
            $dir = "../groupmessage/". $data['group'];
            
            if (!file_exists($dir)){
                mkdir ($dir,0777,true);
            }
            $dir = "../groupmessage/". $data['group']."/".$year;
            if (!file_exists($dir)){
                mkdir ($dir,0777,true);
            }
            $dir = "../groupmessage/". $data['group']."/".$year."/".$xs.".db";
            if (!file_exists($dir)){
                copy('../groupmessage/tpl.db', $dir);
            }
            $res = [
                'g_id' => $data['group'],
                'content' => $data['data'],
                'g_from_userid' => $uid,
                'type' => $data['type'],
                'create_time' => time()
            ];
            // dump(ROOT_PATH);
            $sql = Db::connect([
                // 数据库类型
                'type'        => 'sqlite',
                // 数据库名
                'database'    => $xs,
                'dsn'         => "sqlite:".ROOT_PATH."groupmessage/". $data['group']."/".$year."/".$xs.".db",
                'charset'        => 'utf8',
            ]);
            $sql->table('cy_message')->insert($res);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }

        return json_encode([
            'result' => 0,
            'message' => '添加成功',
        ]);
    }

    public function get_group_list() {

        $data['session_id'] = input('post.session_id');

        try {
            $uid = db('wx_user')->where('session_id', $data['session_id'])->where('is_delete', 0)->value('id');
            $g_id = db('groups_touser')->where('g_userid', $uid)->column('g_id');
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $g_id,
            'uid' => $uid
        ]);
    }

    //创建超级群
    public function add_group() {
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        $data['uid'] = $this->session_id_check($data['sessionId']);

        $model = new \app\api\model\Group();

        try {
            $list = $model->add_group($data);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $list,
        ]);
    }

    public function group_list() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        $uid = $this->session_id_check($data['sessionId']);

        $model = new \app\api\model\Group();

        try {
            $list = $model->group_list($uid);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $list,
        ]);
    }

    public function group_like() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        $uid = $this->session_id_check($data['sessionId']);

        $model = new \app\api\model\Group();

        try {
            $list = $model->group_like($data['like'], $uid);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $list,
        ]);
    }

    /**
     * 文件上传
     * @return \think\response\Json
     */
    public function upload()
    {
        $file = array_values(request()->file())[0];

        $info = $file->move(ROOT_PATH . 'public' . DS . 'video');
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

    /**
     * 文件上传
     * @return \think\response\Json
     */
    public function upload_image()
    {
        $upload_path = config('image_url');
        $tmp_name = $_FILES['image']['tmp_name'];
        if (empty($tmp_name) || !file_exists($tmp_name)) {
            throw new Exception('上传图片错误');
        }
        $image = file_get_contents($_FILES['image']['tmp_name']);
        $info = getimagesizefromstring($image);
        //判断图片大小
        if (empty($info['bits'])) {
            throw new Exception('图片大小错误');
        }
        //判断图片格式
        if (!in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            throw new Exception('图片格式错误');
        }
        //计算图片SHA1
        $sha1 = sha1($image);
        $save_path = './upload/' . $sha1 . '.jpg';
        if (!file_exists($save_path)) {
            $image_resource = imagecreatefromstring($image);
            //获取图片宽高
            $image_width = $info[0];
            $image_height = $info[1];
            //创建缩略图
            $width = 1024;
            $height = $image_height * ($width / $image_width);
            $thumb = imagecreatetruecolor($width, $height);
            $color = imagecolorallocate($thumb, 255, 255, 255);
            imagefill($thumb, 0, 0, $color);
            //复制图片
            imagecopyresampled($thumb, $image_resource, 0, 0, 0, 0, $width, $height, $image_width, $image_height);
            imagejpeg($thumb, $save_path);
            imagedestroy($thumb);
            imagedestroy($image_resource);
            // dump($save_path);
            // move_uploaded_file($tmp_name,$save_path);
        }
        echo json_encode([
            'result' => 0,
            'data' => $sha1 . '.jpg',
        ]);
    }

    public function groups_touser() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\Group();

        try {
            $list = $model->groups_touser($data['g_id']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $list,
        ]);
    }

    public function groups_admtouser() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\Group();

        try {
            $list = $model->groups_admtouser($data['g_id']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $list,
        ]);
    }

    public function groups_admtwo() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\Group();

        try {
            $list = $model->groups_admtwo($data['g_id'], $data['like']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $list,
        ]);
    }

    public function delete_groups_touser() {

        $data = json_decode(file_get_contents('php://input'), true);
        //check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\Group();

        try {
            $list = $model->delete_groups_touser($data['g_id'], $data['uid']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $list,
        ]);
    }

    public function groups_name() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\Group();

        try {
            $result = $model->groups_name($data['g_id'], $data['group_name']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function groups_notice() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\Group();

        try {
            $result = $model->groups_notice($data['g_id'], $data['notice']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function groups_isaudit() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\Group();

        try {
            $result = $model->groups_isaudit($data['g_id'], $data['isaudit']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function groups_yn() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');

        try {
            $result = db('groups')->where('id', $data['g_id'])->value('isaudit');
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function groups_invitation() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $like = $data['title'] ?? '';
        $model = new \app\api\model\Group();

        try {
            $result = $model->groups_invitation($data['g_id'], $like);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function ratify_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $data['uid'];
        $model = new \app\api\model\Group();

        try {
            $result = $model->ratify_group($data['g_id'], $uid);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function transfer_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uidtwo = db('wx_user')->where('session_id', $data['session_id'])->where('is_delete', 0)->value('id');
        
        $uid = $data['uid'];
        $model = new \app\api\model\Group();

        try {
            $result = $model->transfer_group($data['g_id'], $uid, $uidtwo);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function manage_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();

        try {
            $result = $model->manage_group($data['g_id'], $uid);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function trouble_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();

        try {
            $result = $model->trouble_group($data['g_id'], $uid, $data['is_promottone']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function sticky_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();

        try {
            $result = $model->sticky_group($data['g_id'], $uid, $data['is_top']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function remarks_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();

        try {
            $result = $model->remarks_group($data['g_id'], $uid, $data['is_dis_remarks']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function del_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();

        try {
            $result = $model->del_group($data['g_id'], $uid);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function cli_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();

        try {
            $result = $model->cli_group($data['g_id'], $uid);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function create_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();
        $reason = $data['reason'] ?? '';
        try {
            $result = $model->create_group($data['g_id'], $uid, $data['fromuid'], $reason);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function info_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        $uid = $data['uid'];
        //check_empty($data['session_id'], 'session_id参数错误');

        $model = new \app\api\model\Group();

        try {
            $result = $model->info_group($data['g_id'], $uid);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function mybei_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();

        try {
            $result = $model->mybei_group($data['g_id'], $uid, $data['username']);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function and_group() {

        $data = json_decode(file_get_contents('php://input'), true);
        $like = $data['like'] ?? '';
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);
        $model = new \app\api\model\Group();

        try {
            $result = $model->and_group($uid, $data['fuid'], $like);
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function mydian() {

        $data = json_decode(file_get_contents('php://input'), true);
        
        //check_empty($data['session_id'], 'session_id参数错误');
        $uid = $this->session_id_check($data['session_id']);

        try {
            $result = db('wx_user')->where('id', $uid)->value('stamps');
        } catch (Exception $e) {
            return json_encode([
                'result' => 2,
                'message' => $e->getMessage(),
            ]);
        }
        return json_encode([
            'result' => 0,
            'data' => $result,
        ]);
    }

    public function code() {
        //获取分享二维码
        $appid = $_GET['appid'];
        $gid = $_GET['gid'];
        $session_id = $_GET['session_id'];
        $uid = $this->session_id_check($session_id);
        $access_token = get_access_token($appid);
        $param = [
            'scene' => base64_encode($gid . "_" . $uid),
            'page' => 'pages/apply/apply',
        ];
        $tool = new \tool\Curl; 
        $qrcode_string = $tool::post('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token, json_encode($param));
        // $qrcode_size = getimagesizefromstring($qrcode_string);
        // $qrcode = imagecreatefromstring($qrcode_string);
        // ob_end_clean();
        
        $qrcode = imagecreatefromstring($qrcode_string);
        header('Content-Type: image/png');
        ob_end_clean();
        imagepng($qrcode);
    }
}
