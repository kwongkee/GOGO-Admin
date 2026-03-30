<?php

namespace app\declares\logic;

use think\Model;
use PHPExcel;
use PHPExcel_IOFactory;

class ReadElcOrderFile extends Model
{

    public $savePath = './uploads/excel/';

    public $FileName = '';

    public $headData = '';

    public $activeSheet = [];

    protected $ExcelObj;

    protected $time;

    /*
     * 上传保存文件
     */
    public function moveFile ( $file )
    {
        $moveResult = $file->validate(['ext' => 'xls'])->rule('unique')->move($this->savePath);
        if ( !$moveResult ) throw new \Exception($file->getErroe());
        $this->FileName = $moveResult->getSaveName();
        return $this;
    }

    /**
     * @return object
     */
    public function PHPExcelObj ()
    {
        $fileNameArr = explode('/', $this->FileName);
        $filePath = $this->savePath . $fileNameArr[0] . '/' . $fileNameArr[1];
        try {
            $reder = PHPExcel_IOFactory::createReader(PHPExcel_IOFactory::identify($filePath));
            $reder->setReadDataOnly(true);
            $reder->setLoadAllSheets();
            $this->ExcelObj = $reder->load($filePath);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage() . $exception->getCode());
        }
        $this->activeSheet = $this->ExcelObj->getSheetNames();
        @unlink($filePath);
        return $this;
    }

    /*
     * 获取第一个工作区数据
     */

    protected function getOneSheet ( $postDatas )
    {
        $this->time = date('YmdHis',time());
        $this->ExcelObj->setActiveSheetIndexByName($this->activeSheet[0]);
        return ['DeclEntNo' => $postDatas['DeclEntNo'], 'DeclEntName' => $postDatas['DeclEntName'], 'EBEntNo' => $this->ExcelObj->getActiveSheet()->getCell('B2')->getValue(), 'EBEntName' => $this->ExcelObj->getActiveSheet()->getCell('D2')->getValue(), 'EBPEntNo' => $this->ExcelObj->getActiveSheet()->getCell('B3')->getValue(), 'EBPEntName' => $this->ExcelObj->getActiveSheet()->getCell('D3')->getValue(), 'InternetDomainName' => $this->ExcelObj->getActiveSheet()->getCell('B4')->getValue(), 'DeclTime' => $this->time, 'OpType' => 'A', 'IeFlag' => $this->ExcelObj->getActiveSheet()->getCell('F4')->getValue(), 'CustomsCode' => (int)$this->ExcelObj->getActiveSheet()->getCell('F2')->getValue(), 'CIQOrgCode' => (int)$this->ExcelObj->getActiveSheet()->getCell('F3')->getValue(),];
    }


    /*
     * 获取第二个工作区数据
     */
    public function getTwoSheet ( $postData )
    {

        $data = ['OrderList'=>array()];
        $orderHead = $this->getOneSheet($postData);
        $sheet = $this->ExcelObj->setActiveSheetIndexByName($this->activeSheet[1]);
        $row = (int)$sheet->getHighestRow();
        for ($i = 2; $i <= $row; $i++) {
                $tmp = [];
                $tmp = [
                    'EntOrderNo' => 'GC' .Session('admin.order_prix'). date('YmdHis',time()) . mt_rand(111111, 999999),//电子订单编号
                    'OrderDate'  => $this->ExcelObj->getActiveSheet()->getCell('B' . $i)->getValue(),//订单生成时间
                    'WaybillNo' =>  $this->ExcelObj->getActiveSheet()->getCell('C' . $i)->getValue(),//物流运单号码
                    'goodsNo'   => $this->ExcelObj->getActiveSheet()->getCell('D' . $i)->getValue(),//商品货号
                    'BarCode'   => $this->ExcelObj->getActiveSheet()->getCell('E' . $i)->getValue(),//条形码
                    'GoodsName' => $this->ExcelObj->getActiveSheet()->getCell('F' . $i)->getValue(),//商品名称
                    'GoodsStyle'=> $this->ExcelObj->getActiveSheet()->getCell('G' . $i)->getValue(),//型号规格
                    'OriginCountry'=> $this->ExcelObj->getActiveSheet()->getCell('H' . $i)->getValue(),//原产国
                    'packageType'  => $this->ExcelObj->getActiveSheet()->getCell('I' . $i)->getValue(),//包装类型
                    'unit'      => $this->ExcelObj->getActiveSheet()->getCell('J' . $i)->getValue(),//单位
                    'Qty'       => $this->ExcelObj->getActiveSheet()->getCell('K' . $i)->getValue(),//数量
                    'Price'     => $this->ExcelObj->getActiveSheet()->getCell('L' . $i)->getValue(),//单价
                    'OrderGoodTotal' => $this->ExcelObj->getActiveSheet()->getCell('M' . $i)->getValue(),//贷款金额
                    'Tax'       => $this->ExcelObj->getActiveSheet()->getCell('N' . $i)->getValue(),//税费
                    'OrderGoodTotalCurr' => $this->ExcelObj->getActiveSheet()->getCell('O' . $i)->getValue(),//币制
                    'Freight'   => $this->ExcelObj->getActiveSheet()->getCell('P' . $i)->getValue(),//运费
                    'insuredFree' => $this->ExcelObj->getActiveSheet()->getCell('Q' . $i)->getValue(),//保价费
                    'OrderDocAcount' => $this->ExcelObj->getActiveSheet()->getCell('R' . $i)->getValue(),//订购人用户名
                    'OrderDocName'  => $this->ExcelObj->getActiveSheet()->getCell('S' . $i)->getValue(),//订购人姓名
                    'OrderDocId'    => $this->ExcelObj->getActiveSheet()->getCell('T' . $i)->getValue(),//订购人证件号码
                    'weight'    => $this->ExcelObj->getActiveSheet()->getCell('U' . $i)->getValue(),//净重
                    'grossWeight' => $this->ExcelObj->getActiveSheet()->getCell('V' . $i)->getValue(),//毛重
                    'OrderDocTel' => $this->ExcelObj->getActiveSheet()->getCell('W' . $i)->getValue(),//收货人电话
                    'Province'  => $this->ExcelObj->getActiveSheet()->getCell('X' . $i)->getValue(),//收货人省
                    'city'      => $this->ExcelObj->getActiveSheet()->getCell('Y' . $i)->getValue(),//收货人城市
                    'county'    => $this->ExcelObj->getActiveSheet()->getCell('Z' . $i)->getValue(),//收货人区/县
                    'RecipientAddr' => $this->ExcelObj->getActiveSheet()->getCell('AA' . $i)->getValue(),//收货人地址
                    'RecipientCountry' =>$this->ExcelObj->getActiveSheet()->getCell('AB' . $i)->getValue(),
                    'RecipientProvincesCode' => $this->ExcelObj->getActiveSheet()->getCell('AC' . $i)->getValue(),
                    'senderName'    => $this->ExcelObj->getActiveSheet()->getCell('AD' . $i)->getValue(),//发货人姓名
                    'senderTel' => $this->ExcelObj->getActiveSheet()->getCell('AE' . $i)->getValue(),//发货人电话
                    'senderAddr' => $this->ExcelObj->getActiveSheet()->getCell('AF' . $i)->getValue(),//发货人地址
                    'senderCountry' => $this->ExcelObj->getActiveSheet()->getCell('AG' . $i)->getValue(),//发货人所在国
                    'senderProvincesCode'   => $this->ExcelObj->getActiveSheet()->getCell('AH' . $i)->getValue(),//发货人省市区代码
                    'ActualAmountPaid'  => $this->ExcelObj->getActiveSheet()->getCell('M' . $i)->getValue(),
                    'RecipientName' =>$this->ExcelObj->getActiveSheet()->getCell('R' . $i)->getValue(),
                    'RecipientTel'  =>$this->ExcelObj->getActiveSheet()->getCell('W' . $i)->getValue(),
                ];
                array_push($data['OrderList'], $tmp);
            }
            $data['OrderHead'] = $orderHead;
            unset($orderGoods, $orderHead);
            return $data;
    }

}