<?php

/**
 * Created by PhpStorm.
 * User: xunuo
 * Date: 2017/6/27
 * Time: 9:41
 */

namespace app\admin\controller;

use think\Exception;
use think\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Sign extends Common {

    /**
     * 列表
     * @return \think\response\View
     */
    public function index() {
        $data = [];
        $id = input('id');
        $s_id = input('get.s_id');
        if ($s_id) {
            db('activity_sign')->where('id', $s_id)->setField('audit_flag', 1);
        }
        //搜索
        $signName = input('request.signName');
        $data['signName'] = $signName;
        $auditFlag = input('request.auditFlag');
        $data['auditFlag'] = $auditFlag;
        //页码
        $pageNum = 1;
        if (is_numeric(input('request.pageNum'))) {
            $pageNum = input('request.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $signModel = new \app\admin\model\Sign();
        //列表数据
        $list = $signModel->getList($pageNum, $numPerPage, $signName, $id, $auditFlag);
        if ($list) {
            foreach ($list as &$value) {
                $value['activity_name'] = db('activity')->where('id', $id)->value('activity_name');
            }
        }
        $data['list'] = $list;
        //数据总数
        $count = $signModel->getCount($signName, $id, $auditFlag);
        $data['count'] = $count;
        return view('', $data);
    }

    /**
     * 添加
     * @return \think\response\View
     */
    public function add() {
        $data = [];
        $data['attr_values'] = db('activity_rule')->where('is_delete', 0)->select();
        return view('', $data);
    }

    /**
     * 执行添加
     * @return \think\response\Json
     */
    public function doAdd() {
        $data = input('post.');
        // if (!preg_match("/^1[3578]{1}[0-9]{9}$|14[57]{1}[0-9]{8}$/", $data['mobile'])) {
        //     return json([
        //         'statusCode' => 300,
        //         'message' => '联系电话格式不正确',
        //     ]);
        // }
        $signModel = new \app\admin\model\Sign();
        try {
            $signModel->doAdd($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '添加成功',
            'navTabId' => 'signManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 修改
     * @return \think\response\View
     */
    public function modify() {
        $data = db('activity_sign')->where('id', input('get.id'))->find();
        return view('', $data);
    }

    /**
     * 执行修改
     * @return \think\response\Json
     */
    public function doModify() {
        $data = input('post.');
        // if (!preg_match("/^1[3578]{1}[0-9]{9}$|14[57]{1}[0-9]{8}$/", $data['mobile'])) {
        //     return json([
        //         'statusCode' => 300,
        //         'message' => '联系电话格式不正确',
        //     ]);
        // }
        $signModel = new \app\admin\model\Sign();
        try {
            $signModel->doModify($data);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '修改成功',
            'navTabId' => 'signManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 执行删除
     * @return \think\response\Json
     */
    public function doDelete() {
        $id = input('get.id');
        $activity_id = input('get.activity_id');
        $signModel = new \app\admin\model\Sign();
        try {
            $signModel->doDelete($id, $activity_id);
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '删除成功',
            'navTabId' => 'signManage',
        ]);
    }

    /**
     * 调整票数
     */
    public function adjust() {
        $data['admin_count'] = db('activity_sign')->where('id', input('get.id'))->value('admin_count');
        $data['total_count'] = db('activity_sign')->where('id', input('get.id'))->value('total_count');
        return view('', $data);
    }

    //^(-)?[1-9][0-9]*$
    /**
     * 执行调整票数
     */
    public function doAdjust() {
        $data = input('post.');
        if (!preg_match("/^(-|\+)?\d+$/", $data['num'])) {
            return json([
                'statusCode' => 300,
                'message' => '调整的票数只能是正整数或负整数',
            ]);
        }
        if ($data['num'] < 0) {
            $num2 = $data['total_count'] - $data['admin_count'];
            $num = $num2 + $data['num'];
            if ($num < 0) {
                if ($num2 == 0) {
                    return json([
                        'statusCode' => 300,
                        'message' => '调整的票数不能小于'.$num2,
                    ]);
                } else {
                    return json([
                        'statusCode' => 300,
                        'message' => '调整的票数不能小于-'.$num2,
                    ]);
                }
            }
        }
        try {
            if ($data['admin_count'] >= 0) {
                db('activity_sign')->where('id', $data['id'])->setDec('total_count', $data['admin_count']);
                db('activity')->where('id', $data['activity_id'])->setDec('total_count', $data['admin_count']);
            } else {
                db('activity_sign')->where('id', $data['id'])->setInc('total_count', abs($data['admin_count']));
                db('activity')->where('id', $data['activity_id'])->setInc('total_count', abs($data['admin_count']));
            }
            $res = db('activity_sign')->where('id', $data['id'])->setField('admin_count', $data['num']);
            if ($data['num'] >= 0) {
                db('activity_sign')->where('id', $data['id'])->setInc('total_count', $data['num']);
                db('activity')->where('id', $data['activity_id'])->setInc('total_count', $data['num']);
            } else {
                db('activity_sign')->where('id', $data['id'])->setDec('total_count', abs($data['num']));
                db('activity')->where('id', $data['activity_id'])->setDec('total_count', abs($data['num']));
            }
        } catch (Exception $e) {
            return json([
                'statusCode' => 300,
                'message' => $e->getMessage(),
            ]);
        }
        return json([
            'statusCode' => 200,
            'message' => '调整成功',
            'navTabId' => 'signManage',
            'callbackType' => 'closeCurrent',
        ]);
    }

    /**
     * 红包列表
     * @return \think\response\View
     */
    public function packet() {
        $data = [];
        $activity_id = input('activity_id');
        //搜索
        $order_sn = input('request.order_sn');
        $data['order_sn'] = $order_sn;
        $order_status = input('request.order_status');
        $data['order_status'] = $order_status;
        
        //页码
        $pageNum = 1;
        if (is_numeric(input('request.pageNum'))) {
            $pageNum = input('request.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $signModel = new \app\admin\model\Sign();
        //列表数据
        $list = $signModel->getPacket($pageNum, $numPerPage, $order_sn, $activity_id, $order_status);
        if ($list) {
            foreach ($list as &$value) {
                $value['activity_name'] = db('activity')->where('id', $activity_id)->value('activity_name');
                $value['username'] = db('activity_sign')->where('id', $value['sign_id'])->value('username');
            }
        }
        $data['list'] = $list;
        //数据总数
        $count = $signModel->getPacketCount($order_sn, $activity_id, $order_status);
        $data['count'] = $count;
        return view('', $data);
    }

    /**
     * 领取红包
     * @return \think\response\View
     */
    public function userpacket() {
        $data = [];
        $activity_id = input('activity_id');
        //搜索
        $action = input('request.action');
        $data['action'] = $action;
        //页码
        $pageNum = 1;
        if (is_numeric(input('request.pageNum'))) {
            $pageNum = input('request.pageNum');
        }
        $data['pageNum'] = $pageNum;
        //每页数量
        $numPerPage = 20;
        $data['numPerPage'] = $numPerPage;
        $signModel = new \app\admin\model\Sign();
        //列表数据
        $list = $signModel->getUserPacket($pageNum, $numPerPage, $activity_id, $action);
        if ($list) {
            foreach ($list as &$value) {
                $value['activity_name'] = db('activity')->where('id', $activity_id)->value('activity_name');
                $value['username'] = db('activity_sign')->where('id', $value['sign_id'])->value('username');
                $value['gift_name'] = db('gift')->where('id', $value['gift_id'])->value('gift_name');
            }
        }
        $data['list'] = $list;
        //数据总数
        $count = $signModel->getUserPacketCount($activity_id, $action);
        $data['count'] = $count;
        return view('', $data);
    }

    /**
     * 导出报名
     */
    public function derive() {
        $data = input('get.');
        $signName = $data['signName'];
        $auditFlag = $data['auditFlag'];
        $activity_name = db('activity')->where('id', $data['id'])->value('activity_name');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        //header("Content-Disposition:filename=" . $activity_name . "活动报名表.xlsx");
        header('Content-Disposition: attachment;filename=' . $activity_name . '活动报名表.xlsx');
        $list = db('activity_sign')
                ->where('is_delete', 0)
                ->where('activity_id', $data['id'])
                ->where(function ($query) use ($signName, $auditFlag) {
                    if (!empty($signName)) {
                        $query->whereOr('username', 'like', "%{$signName}%");
                    }
                    if (!empty($auditFlag)) {
                        $query->where('audit_flag', $auditFlag);
                    }
                })
                ->order('create_time desc')
                ->select();
        if (!$list) {
            die('没有查询的数据');
        }
        $sheet->setCellValue('A1', '报名人员编号');
        $sheet->setCellValue('B1', '报名名称');
        $sheet->setCellValue('C1', '性别');
        $sheet->setCellValue('D1', '联系电话');
        $sheet->setCellValue('E1', '用户投票数');
        $sheet->setCellValue('F1', '礼物票数');
        $sheet->setCellValue('G1', '调整票数');
        $sheet->setCellValue('H1', '总票数');
        $sheet->setCellValue('I1', '报名人所属机构');
        $sheet->setCellValue('J1', '参赛宣言');
        $sheet->setCellValue('K1', '风采介绍');
        $sheet->setCellValue('L1', '审核状态');
        $i = 2;
        foreach ($list as &$value) {
            if ($value['sex'] == 1) {
                $sex = '男';
            } elseif ($value['sex'] == 2) {
                $sex = '女';
            }
            if ($value['audit_flag'] == 1) {
                $audit_flag = '审核通过';
            } elseif ($value['audit_flag'] == 2) {
                $audit_flag = '待审核';
            }
            $sheet->setCellValue('A' . $i, $value['sign_code']);
            $sheet->setCellValue('B' . $i, $value['username']);
            $sheet->setCellValue('C' . $i, $sex);
            $sheet->setCellValue('D' . $i, $value['mobile']);
            $sheet->setCellValue('E' . $i, $value['vote_count']);
            $sheet->setCellValue('F' . $i, $value['gift_count']);
            $sheet->setCellValue('G' . $i, $value['admin_count']);
            $sheet->setCellValue('H' . $i, $value['total_count']);
            $sheet->setCellValue('I' . $i, $value['sign_unit']);
            $sheet->setCellValue('J' . $i, $value['sign_declaration']);
            $sheet->setCellValue('K' . $i, $value['sign_introduce']);
            $sheet->setCellValue('L' . $i, $audit_flag);
            ++$i;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
        //file_put_contents($activity_name . "活动报名表.xlsx", $writer);
    }

    /**
     * 导出礼物列表
     */
    public function exportgift() {
        $activtiy_id = input('get.id');
        $signName = input('get.signName');
//        dump($signName);
        $sign_ids = array();
        if (!empty($signName)) {
            $sign_id = db('activity_sign')->where('username', 'like', "%{$signName}%")->field('id')->select();
            foreach ($sign_id as $value2){
                array_push($sign_ids,$value2['id']);
            }
            
        }
        $activity_name = db('activity')->where('id', $activtiy_id)->value('activity_name');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment;filename=' . $activity_name . '活动礼物表.xlsx');
        $list = db('order')
                ->where(function ($query) use ($sign_ids) {
                    if (!empty($sign_ids)) {
                        $query->whereOr('sign_id', 'in', $sign_ids);
                    }
                })
                ->where('activity_id', $activtiy_id)
                ->where('order_status', 2)
                ->order('create_time desc')
                ->select();
        if (!$list) {
            die('没有查询的数据');
        }

        $sheet->setCellValue('A1', '创建时间');
        $sheet->setCellValue('B1', '活动类型');
        $sheet->setCellValue('C1', '活动名称');
        $sheet->setCellValue('D1', '礼物名称');
        $sheet->setCellValue('E1', '礼物个数');
        $sheet->setCellValue('F1', '报名人');
        $sheet->setCellValue('G1', '总价');
        $sheet->setCellValue('H1', '订单编号');
        $sheet->setCellValue('I1', '流水号');
        $sheet->setCellValue('J1', '昵称');

        $i = 2;
        foreach ($list as &$value) {

            switch ($value['activity_type']) {
                case 1:
                    $value['activity_type'] = "个人";
                    break;
                case 2:
                    $value['activity_type'] = "商家";
                    break;
                case 3:
                    $value['activity_type'] = "景物";
                    break;
            }
            $value['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
            $value['activity_name'] = db('activity')->where('id', $value['activity_id'])->value['activity_name'];
            $value['username'] = db('activity_sign')->where('id', $value['sign_id'])->value('username');
            $sheet->setCellValue('A' . $i, $value['update_time']);
            $sheet->setCellValue('B' . $i, $value['activity_type']);
            $sheet->setCellValue('C' . $i, $activity_name);
            $sheet->setCellValue('D' . $i, $value['gift_name']);
            $sheet->setCellValue('E' . $i, $value['gift_num']);
            $sheet->setCellValue('F' . $i, $value['username']);
            $sheet->setCellValue('G' . $i, $value['total_amount']);
            $sheet->setCellValue('H' . $i, $value['order_sn']);
            $sheet->setCellValue('I' . $i, $value['transaction_id']);
            $sheet->setCellValue('J' . $i, $value['name']);
            ++$i;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
    }

    /**
     * 导出所有礼物列表
     */
    public function exportall() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment;filename=所有礼物表.xlsx');
        $list = db('order')
                ->where('order_status', 2)
                ->order('create_time desc, activity_id desc')
                ->select();
        if (!$list) {
            die('没有查询的数据');
        }

        $sheet->setCellValue('A1', '创建时间');
        $sheet->setCellValue('B1', '活动类型');
        $sheet->setCellValue('C1', '活动名称');
        $sheet->setCellValue('D1', '礼物名称');
        $sheet->setCellValue('E1', '礼物个数');
        $sheet->setCellValue('F1', '报名人');
        $sheet->setCellValue('G1', '总价');
        $sheet->setCellValue('H1', '订单编号');
        $sheet->setCellValue('I1', '流水号');
        $sheet->setCellValue('J1', '昵称');

        $i = 2;
        foreach ($list as &$value) {

            switch ($value['activity_type']) {
                case 1:
                    $value['activity_type'] = "个人";
                    break;
                case 2:
                    $value['activity_type'] = "商家";
                    break;
                case 3:
                    $value['activity_type'] = "景物";
                    break;
            }
            $value['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
            $activity_name = db('activity')->where('id', $value['activity_id'])->value(['activity_name']);
            $value['username'] = db('activity_sign')->where('id', $value['sign_id'])->value('username');
            $sheet->setCellValue('A' . $i, $value['update_time']);
            $sheet->setCellValue('B' . $i, $value['activity_type']);
            $sheet->setCellValue('C' . $i, $activity_name);
            $sheet->setCellValue('D' . $i, $value['gift_name']);
            $sheet->setCellValue('E' . $i, $value['gift_num']);
            $sheet->setCellValue('F' . $i, $value['username']);
            $sheet->setCellValue('G' . $i, $value['total_amount']);
            $sheet->setCellValue('H' . $i, $value['order_sn']);
            $sheet->setCellValue('I' . $i, $value['transaction_id']);
            $sheet->setCellValue('J' . $i, $value['name']);
            ++$i;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
    }

    /**
     * 选择导出日期
     */
    public function exporttime() {
        return view();
    }

    /**
     * 导出时间范围内礼物列表
     */
    public function doExporttime() {
        $start_time = $this->request->request('start_time');
        $stop_time = $this->request->request('stop_time');
        $start = date("Y/m/d", $start_time);
        $stop = date("Y/m/d", $stop_time);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        header("Content-type:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment;filename='.$start.'——'.$stop.'礼物表.xlsx');
        $list = db('order')
                ->where('order_status', 2)
                ->where('update_time', 'between', [$start_time,$stop_time])
                ->order('create_time desc, activity_id desc')
                ->select();
        if (!$list) {
            die('没有查询的数据');
        }

        $sheet->setCellValue('A1', '创建时间');
        $sheet->setCellValue('B1', '活动类型');
        $sheet->setCellValue('C1', '活动名称');
        $sheet->setCellValue('D1', '礼物名称');
        $sheet->setCellValue('E1', '礼物个数');
        $sheet->setCellValue('F1', '报名人');
        $sheet->setCellValue('G1', '总价');
        $sheet->setCellValue('H1', '订单编号');
        $sheet->setCellValue('I1', '流水号');
        $sheet->setCellValue('J1', '昵称');

        $i = 2;
        foreach ($list as &$value) {

            switch ($value['activity_type']) {
                case 1:
                    $value['activity_type'] = "个人";
                    break;
                case 2:
                    $value['activity_type'] = "商家";
                    break;
                case 3:
                    $value['activity_type'] = "景物";
                    break;
            }
            $value['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
            $activity_name = db('activity')->where('id', $value['activity_id'])->value(['activity_name']);
            $value['username'] = db('activity_sign')->where('id', $value['sign_id'])->value('username');
            $sheet->setCellValue('A' . $i, $value['update_time']);
            $sheet->setCellValue('B' . $i, $value['activity_type']);
            $sheet->setCellValue('C' . $i, $activity_name);
            $sheet->setCellValue('D' . $i, $value['gift_name']);
            $sheet->setCellValue('E' . $i, $value['gift_num']);
            $sheet->setCellValue('F' . $i, $value['username']);
            $sheet->setCellValue('G' . $i, $value['total_amount']);
            $sheet->setCellValue('H' . $i, $value['order_sn']);
            $sheet->setCellValue('I' . $i, $value['transaction_id']);
            $sheet->setCellValue('J' . $i, $value['name']);
            ++$i;
        }
        $writer = new Xlsx($spreadsheet);
        $writer->save("php://output");
    }
}
