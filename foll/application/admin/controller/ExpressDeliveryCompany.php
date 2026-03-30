<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;
use Excel5;
use PHPExcel_IOFactory;


class ExpressDeliveryCompany extends Auth
{
    public function index(Request $request)
    {
        $list = Db::name('customs_express_company_code')->order('id','desc')->select();
        return $this->fetch('express_delivery_company/index', ['title' => '上传快递公司编码','list'=>$list]);
    }


    /**
     * 保存快递编号
     * @param  Request  $request
     * @return \think\response\Json
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function upload(Request $request)
    {
        $file = $request->file('file');
        if (!$file) {
            return json(['code' => -1, 'message' => '请上传文件']);
        }
        $path = ROOT_PATH.'public'.DS.'uploads';
        $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
        if (!$saveResult) {
            return json(['code' => -1, 'message' => $file->getError()]);
        }
        $fileName = $path.'/'.$saveResult->getSaveName();
        $data = [];
        $inputFileType = PHPExcel_IOFactory::identify($fileName);
        $objRead = PHPExcel_IOFactory::createReader($inputFileType);
        $objRead->setReadDataOnly(true);
        $PHPRead = $objRead->load($fileName);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        for ($currentRow = 3; $currentRow <= $allRow; $currentRow++) {
            array_push($data, [
                'name' => $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue(),
                'code' => $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue()
            ]);
        }
        Db::name('customs_express_company_code')->insertAll($data);
        @unlink($fileName);
        return json(['code'=>0,'message'=>'已保存']);
    }

}

