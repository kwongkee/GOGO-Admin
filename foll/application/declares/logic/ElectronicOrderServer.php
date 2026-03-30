<?php

namespace app\declares\logic;
use think\Model;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Db;

class ElectronicOrderServer extends Model
{
    const uploadPath = './uploads/excel/';
    public function withFile($objFile,$uid)
    {
        $payMent = $this->getUserPaymenNati($uid['id']);
        if(empty($payMent))return [false,'请去收银台支付订单'];
        if(!is_object($objFile))return [false,'请上传文件'];
        $verifFileResult = $objFile->validate(['ext'=>'xls,csv,xlsx','size'=>10485760])->rule('md5')->move('./uploads/excel/');
        if(false === $verifFileResult)return [false,'文件格式不正确'];
        $excInfo = $this->getExcelData($verifFileResult->getSaveName());
        $excInfo['payInfo'] = $payMent;
        return [true,$excInfo];
    }

    protected function getExcelData($file=null){
        $file = explode('/',$file);
        $files = self::uploadPath.$file[0].'/'.$file[1];
        try{
            $reader = PHPExcel_IOFactory::createReader(PHPExcel_IOFactory::identify($files));
            $reader->setReadDataOnly(true);
            $objPHPExcel = $reader->load($files);
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }

        $row = $objPHPExcel->getActiveSheet()->getHighestRow();
        $data=array();
        $n=0;
        for ($i=2;$i<=$row;$i++){
            $data[$n]['ordersn']    = strlen($objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue())>18?substr($objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue(),0,18):$objPHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
            $data[$n]['number']     = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
            $data[$n]['shopNames']  = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
            $data[$n]['shopOrd']    = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
            $data[$n]['shopType']   = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
            $data[$n]['shopMoney']  = (int)($objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue()*100);
            $data[$n]['shopCNY']    = $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
            $data[$n]['shopName']   = $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
            $data[$n]['CardType']   = $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
            $data[$n]['Cards']      = $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
            $data[$n]['account']    = $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
            $n+=1;
        }
        @unlink($files);
        @rmdir(self::uploadPath.$file[0]);
        unset($objPHPExcel);
        return $data;
    }
    protected function getUserPaymenNati($uid)
    {
        return Db::name('foll_payment_nativepay')
            ->where(['uid'=>$uid,'payStatus'=>'1004','isUse'=>1])
            ->field(['orderId','orderAmount','orderCurrencyCode'])
            ->order('id','desc')
            ->find();
    }

    public function saveDetailInfo($data,$pordersn)
    {
            foreach ($data as $ke => &$val){
                $val['pordersn'] = $pordersn;
            }
            try{
                Db::name('foll_detail_list')->insertAll($data);
            }catch (\Exception $exception){
               throw new \Exception($exception->getMessage());
            }
    }
}