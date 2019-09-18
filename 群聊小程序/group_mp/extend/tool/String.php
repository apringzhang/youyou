<?php

namespace tool;


/**
 * 字符串工具类
 * @author 许诺
 */
class String
{

    /**
     * 将数组中所有的非字符串转换为字符串
     * @param type $array
     * @return array
     */
    public static function parseArrayNotString($array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = \Tools::parseArrayNotString($value);
            } else {
                if (is_null($value)) {
                    $array[$key] = '';
                }
                if (!is_string($value)) {
                    $array[$key] = strval($value);
                }
            }
        }
        return $array;
    }

}
