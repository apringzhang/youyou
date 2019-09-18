<?php
/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 13:04
 */

namespace app\admin\widget;

use think\View;

class HyAdCategory
{
    /**
     * 列表树
     * @param int $pid
     * @param int $level
     * @return string|void
     */
    public function listTree($pid = 0, $level = 0)
    {
        $categoryModel = new \app\admin\model\Category();
        $data['list'] = $categoryModel->getList($pid);
        if (empty($data['list'])) {
            return;
        }
        $data['level'] = $level;
        $view = new View();
        return $view->fetch('category/listtree', $data);
    }

    /**
     * 选择上级分类树
     * @param int $pid
     * @return string|void
     */
    public function pidListTree($pid = 0, $current = 0)
    {
        $HyadModel = new \app\admin\model\Hyad();
        $data['list'] = $HyadModel->getCategoryList($pid);
        if (empty($data['list'])) {
            return;
        }
        $data['current'] = $current;
        $view = new View();
        return $view->fetch('hyad/pidlisttree', $data);
    }

    /**
     * 选择上级分类树
     * @return string|void
     */
    public function chooseListTree($pid = 0)
    {
        $categoryModel = new \app\admin\model\Category();
        $data['list'] = $categoryModel->getList($pid);
        if (empty($data['list'])) {
            return;
        }
        $view = new View();
        return $view->fetch('category/chooselisttree', $data);
    }

    /**
     * 选择上级分类树只可选择下级
     * @return string|void
     */
    public function chooseLowestListTree($pid = 0)
    {
        $categoryModel = new \app\admin\model\Category();
        $data['list'] = $categoryModel->getList($pid);
        if (empty($data['list'])) {
            return;
        }
        $view = new View();
        return $view->fetch('category/chooselowestlisttree', $data);
    }

    /**
     * 随便选
     * @return string|void
     */
    public function choosersListTree($pid = 0)
    {
        $categoryModel = new \app\admin\model\Category();
        $data['list'] = $categoryModel->getList($pid);
        if (empty($data['list'])) {
            return;
        }
        $view = new View();
        return $view->fetch('category/chooserslisttree', $data);
    }
}