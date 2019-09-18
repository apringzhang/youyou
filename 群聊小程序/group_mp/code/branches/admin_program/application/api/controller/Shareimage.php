<?php

namespace app\api\controller;

use think\Exception;

class Shareimage {

	/**
	 * 分享图片
	 */
	public function get_share_image() {
		$data = json_decode(file_get_contents('php://input'), true);
		try {
		    $appid = $_GET['appid'];
		    $company_id = $_GET['company_id'];

		   	// $company_id = 1;
		   	// $appid = 'wxb020c51ed274161d';

		    check_empty($appid, 'appid参数错误');
		    check_empty($company_id, 'company_id参数错误');
		   	$row = db('company')->where('id', $company_id)->find();

		    $template_string = file_get_contents(config('image_url').'/temporary/fx.png');
		    // var_dump($row['company_name']);die;
		    $template = imagecreatefromstring($template_string);
		    $font = realpath('./simhei.ttf');
		    //用户名
		    imagettftext($template, 35, 0, 280, 550, 0x333333, $font, $row['company_name']);
		    //参赛宣言
		    //自动折行
		    $str_array = [];
		    $row_char_num = 13;
		    $row_num = floor(mb_strlen($row['company_addr']) / $row_char_num);
		    for ($i = 0; $i <= $row_num; ++$i) {
		        $str_array[$i] = mb_substr($row['company_addr'], $i * $row_char_num, $row_char_num);
		    }
		    $row_str = '';
		    foreach ($str_array as $str) {
		        $row_str .= $str . "\n";
		    }
		    imagettftext($template, 21, 0, 315, 609, 0x333333, $font, $row_str);
		    //电话
		    imagettftext($template, 25, 0, 315, 682, 0x333333, $font, $row['company_phone']);
		    //小程序宣传图片
		    // var_dump(config('image_url') . '/' . $row['picture']);die;
		    $cover_string = file_get_contents(config('image_url') . '/' . $row['picture']);
		    $cover_size = getimagesizefromstring($cover_string);
		    $cover = imagecreatefromstring($cover_string);
		    imagecopyresampled($template, $cover, 32, 100, 0, 0, 609, 295, $cover_size[0], $cover_size[1]);
		    
		    //获取分享二维码
		    $access_token = get_access_token($appid);
		    $param = [
		        'scene' => urlencode($company_id),
		        'page' => 'pages/index/index',
		    ];
		    $tool = new \tool\Curl; 
		    $qrcode_string = $tool::post('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token, json_encode($param));
		    $qrcode_size = getimagesizefromstring($qrcode_string);
		    $qrcode = imagecreatefromstring($qrcode_string);
		    imagecopyresampled($template, $qrcode, 26, 502, 0, 0, 180, 180, $qrcode_size[0], $qrcode_size[1]);
		    ob_end_clean();
		    header("Content-Type: image/png");
		    imagepng($template);
		} catch (Exception $e) {
		    return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
		
	}


	public function share_image() {
		$data = json_decode(file_get_contents('php://input'), true);
		$appid = $_GET['appid'];
		$param = $_GET['param'];
		try {
			$access_token = get_access_token($appid);
	        $param = [
	            'scene' => urlencode(base64_encode(json_encode($param))),
	            'page' => 'pages/index/index',
	        ];
	        $tool = new \tool\Curl;
	        $qrcode_string = $tool::post('https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token, json_encode($param));
	//            dump($qrcode_string);die;
	        $qrcode_size = getimagesizefromstring($qrcode_string);
	        $qrcode = imagecreatefromstring($qrcode_string);
	        ob_end_clean();
			header("Content-Type: image/png");
	//		    imagepng($template);
	        $image_name = time() . ".png";
	        imagepng($qrcode, ROOT_PATH . 'public' . DS . 'upload' . DS . 'coupon_share' . DS . $image_name);
        } catch (Exception $e) {
		    return json_encode([
				'result' => 2,
				'message' => $e->getMessage(),
			]);
		}
	}
}