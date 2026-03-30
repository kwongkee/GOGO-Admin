<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;
use think\Loader;
use PHPExcel;
use PHPExcel_IOFactory;

/**
 * 买家信息防盗（买家验核）
 * Class MemberIdentityVerif
 * @package app\admin\controller
 */
class MemberIdentityVerif extends Auth
{

    /**
     * 验核列表
     * @return mixed
     */
    public function verif_list(Request $request)
    {
        $title = '买家验核';
        $where = "";
        $status = ['已通知、未验核', '已验核、已成功', '已验核、已失败'];
        $input = $request->get();
        if (isset($input['status']) && !empty($input['status'])) {
            $where = "a1.status=" . $input['status'];
        }
        if (isset($input['name']) && !empty($input['name'])) {
            $where = "a1.name='" . $input['name'] . "' or a1.mobile='" . $input['name'] . "' or a2.bill_num='" . $input['name'] . "'";
        }
        $list = Db::name('customs_member_verifidentity')
            ->alias('a1')
            ->join("customs_batch a2", "a1.batch_num=a2.batch_num")
            ->field(["a1.*", "a2.bill_num"])
            ->where($where)
            ->paginate(10, true, [
                'query' => ['s' => 'admin/MemberIdentityVerif/verif_list'],
                'var_page' => 'page',
                'type' => 'Layui',
                'newstyle' => true
            ]);
        $page = $list->render();
        $data = $list->toArray()['data'];
        return view("member/verif_list", compact('title', 'status', 'page', 'data'));
    }


    /**
     * 导入用户清单
     * @param Request $request
     * @return mixed
     */
    public function loadUserInfoByExcel(Request $request)
    {
        try {
            $logic = new \app\admin\logic\MemberIdentityVerifLogic();
            $logic->saveAndSendMessage($logic->readExcelFile($request->file('file')));
        } catch (\Exception $e) {
            return json(['code' => 0, 'message' => $e->getMessage() . $e->getLine()]);
        }
        return json(['code' => 0, 'message' => '完成']);
    }

    public function exportUserVerifInfo(Request $request)
    {
        $billNum = $request->get('billNum');
        $batchNum = Db::name('customs_batch')->where([
            'bill_num' => $billNum,
            'status' => 0
        ])->field('batch_num')->select();
        if (empty($batchNum)) {
            $this->error('暂无数据');
        }
        $batchList = "";
        foreach ($batchNum as $value) {
            $batchList .= $value['batch_num'] . ",";
        }
        $data = Db::name('customs_member_verifidentity')->where('batch_num', 'in', $batchList)->select();
        $headlist = ['A1' => '提单号', 'B1' => '运单号', 'C1' => '姓名', 'D1' => '手机号', 'E1' => '状态', 'F1' => '验证时间'];
        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('Sheet1'); //给当前活动sheet设置名称
        $status = ['已通知、未验核', '已验核、已成功', '已验核、已失败'];
        $n = 2;
        foreach ($headlist as $key => $val) {
            $PHPSheet->setCellValue($key, $val);
        }
        foreach ($data as $val) {
            $PHPSheet->setCellValue("A" . $n, $billNum)
                ->setCellValue("B" . $n, $val['waybill_no'])
                ->setCellValue("C" . $n, $val['name'])
                ->setCellValue("D" . $n, $val['mobile'])
                ->setCellValue("E" . $n, $status[$val['status']])
                ->setCellValue("F" . $n, $val['update_time'] == "" ? "无" : date("Y-m-d H:i", $val['update_time']));
            $n+=1;
        }
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $fileName = $billNum;
        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $fileName . '.xlsx"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打
        $ExcelWrite->save('php://output');
    }
}