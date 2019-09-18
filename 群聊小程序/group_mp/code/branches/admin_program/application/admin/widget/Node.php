<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 13:04
 */

namespace app\admin\widget;

use think\View;

class Node
{
    public function listTree($pid = 0, $level = 0)
    {
        $nodeModel = new \app\admin\model\Node();
        $data['list'] = $nodeModel->getList($pid);
        if (empty($data['list'])) {
            return;
        }
        $data['level'] = $level;
        $view = new View();
        return $view->fetch('node/listtree', $data);
    }

    /**
     * 选择上级节点树
     * @param int $pid
     * @return string|void
     */
    public function pidListTree($pid = 0, $current = 0)
    {
        $nodeModel = new \app\admin\model\Node();
        $data['list'] = $nodeModel->getList($pid);
        if (empty($data['list'])) {
            return;
        }
        $data['current'] = $current;
        $view = new View();
        return $view->fetch('node/pidlisttree', $data);
    }

    /**
     * 修改角色权限节点列表树
     * @param int $pid
     * @param int $roleId
     * @return string|void
     */
    public function roleAccessListTree($pid = 0, $roleId = 0) {
        $nodeModel = new \app\admin\model\Node();
        $data['list'] = $nodeModel->getList($pid);
        if (empty($data['list'])) {
            return;
        }
        $data['roleId'] = $roleId;
        $view = new View();
        return $view->fetch('node/roleaccesslisttree', $data);
    }
}