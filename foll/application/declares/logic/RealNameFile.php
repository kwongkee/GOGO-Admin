<?php

namespace app\declares\logic;

use think\Model;
use PHPExcel;
use PHPExcel_IOFactory;

class RealNameFile extends Model
{

    public $savePath = './uploads/realname/';

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
        $reder = null;
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
//        @unlink($filePath);
        return $this;
    }

    /*
     * 获取第一个工作区数据
     */

    protected function getOneSheet ( $postDatas )
    {
        $this->time = date('YmdHis',time());
        $this->ExcelObj->setActiveSheetIndexByName($this->activeSheet[0]);
        return [
        'DeclEntNo' => $postDatas['DeclEntNo'], 
        'DeclEntName' => $postDatas['DeclEntName'], 
        'EBEntNo' => $this->ExcelObj->getActiveSheet()->getCell('B2')->getValue(),
        'EBEntName' => $this->ExcelObj->getActiveSheet()->getCell('D2')->getValue(),
        'EBPEntNo' => $this->ExcelObj->getActiveSheet()->getCell('B3')->getValue(),
        'EBPEntName' => $this->ExcelObj->getActiveSheet()->getCell('D3')->getValue(),
        'InternetDomainName' => $this->ExcelObj->getActiveSheet()->getCell('B4')->getValue(),
        'DeclTime' => $this->time, 'OpType' => 'A', 'IeFlag' => $this->ExcelObj->getActiveSheet()->getCell('F4')->getValue(), 'CustomsCode' => (int)$this->ExcelObj->getActiveSheet()->getCell('F2')->getValue(), 'CIQOrgCode' => (int)$this->ExcelObj->getActiveSheet()->getCell('F3')->getValue(),];
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
        	
            //$orID = $postData['OrderDate']." ".mt_rand(0,23).':'.mt_rand(1,55).':'.mt_rand(1,55);
                $tmp = [];
                
                $tmp = [
                    'orderId' => 'GC' .Session('admin.order_prix'). date('YmdHis',strtotime($orID)) . mt_rand(111111, 999999),//电子订单编号
                    'OrderDate'  =>  date('Y-m-d H:i:s'),//订单生成时间
                    'WaybillNo'  =>  $this->ExcelObj->getActiveSheet()->getCell('A' . $i)->getValue(),//物流运单号码
                    'userName' => $this->ExcelObj->getActiveSheet()->getCell('B' . $i)->getValue(),//币制
                    'userId'   => $this->ExcelObj->getActiveSheet()->getCell('C' . $i)->getValue(),//运费
                    'title'       => $this->ExcelObj->getActiveSheet()->getCell('D' . $i)->getValue(),//税费
                ];
                array_push($data['OrderList'], $tmp);
            }
            $data['OrderHead'] = $orderHead;
            unset($orderGoods, $orderHead);
            return $data;
    }
    
    
    public function getGoodsSheet()
    {
        $sheet = $this->ExcelObj->setActiveSheetIndexByName($this->activeSheet[2]);
        $row = (int)$sheet->getHighestRow();
        $goodsInfo = [];
        for ($i=2;$i<=$row;$i++){
            $goodsInfo[$this->ExcelObj->getActiveSheet()->getCell('A'.$i)->getValue()][]=[
               'goodNo' =>$this->ExcelObj->getActiveSheet()->getCell('B'.$i)->getValue(),
                'num'   => $this->ExcelObj->getActiveSheet()->getCell('C'.$i)->getValue()
            ];
        }
        return $goodsInfo;
    }

}