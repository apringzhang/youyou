<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 16:02
 */

namespace app\common\taglib;

use think\template\TagLib;

class Access extends TagLib
{
    protected $tags = [
        'check' => array('attr' => 'name', 'close' => 1),
    ];

    public function tagCheck($tag, $content)
    {
        $name = $tag['name'];
        $parse = "<?php if (check('{$name}')): ?>{$content}<?php endif; ?>";
        return $parse;
    }
}