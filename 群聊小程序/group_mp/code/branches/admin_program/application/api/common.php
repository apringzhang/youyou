<?php
/**
 * 验证session_id合法性
 * @param $session_id
 * @return array
 * @throws Exception
 */
function check_session_id($session_id)
{
	$result = db('wx_user')->where('session_id',$session_id)->find();
    if (!$result) {
        exception("验证失败");
    }
    return $result;
}

/**
 * 手机号验证
 * @param $item
 * @param $message
 * @throws Exception
 */
function check_mobile($item, $message)
{
    if (!preg_match('/^1[34578]\d{9}$/', $item)) {
        throw new Exception($message);
    }
}

/**
 * 01字段验证
 * @param $item
 * @param $message
 * @throws Exception
 */
function check_01($item, $message)
{
    if (!in_array(strval($item), ['0', '1'])) {
        throw new Exception($message);
    }
}

/**
 * 截取字符串长度
 * @param $str
 * @param int $length
 * @return string
 */
function check_substr($str, $length = 9)
{
    if (mb_strlen($str, 'utf8') > $length) {
        return mb_substr($str, 0, $length, 'utf-8') . '…';
    } else {
        return $str;
    }
}

/**
 * 获取商品分类列表名称
 * @param $id 商品分类表ID
 */
function getGoodsCat($id)
{
    $goods_cat = db('goods_cat')->where('id',$id)->find();
    return $goods_cat['name'];
}

//二维数组按照指定的键值进行排序
function array_sorts($arr,$keys,$type='asc'){
    $keysvalue = $new_array = array();
    foreach ($arr as $k=>$v){
        $keysvalue[$k] = $v[$keys];
    }
    if($type == 'asc'){
        asort($keysvalue);
    }else{
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k=>$v){
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}