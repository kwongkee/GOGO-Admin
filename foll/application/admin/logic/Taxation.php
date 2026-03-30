<?php

namespace app\admin\logic;

use think\Model;
use PHPExcel;
use Excel5;
use PHPExcel_IOFactory;
use think\Db;

class Taxation extends Model
{
    // 获取文件
    public function getFile($file)
    {
        // 获取Excel表数据
        if (!file_exists($file)) {
            throw new \Exception('读取文件失败');
        }
        $types      = pathinfo($file);
        $types      = strtolower($types["extension"]);
        $type = '';
        switch ($types){
            case 'csv':
                $type = 'csv';
                break;
            case 'xls':
                $type = 'Excel5';
                break;
            case 'xlsx':
                $type = 'Excel2007';
                break;
        }
        //$type      = $type === 'csv' ? $type : 'Excel5';
        $objReader = PHPExcel_IOFactory::createReader($type);
        $objReader->setReadDataOnly(true);
        $PHPReader = $objReader->load($file);
        return $PHPReader;

        // 过滤空格等

        // 将数据写入表内
    }


    // 导入并保存数据
    public function saveCodeListTable($excelObj = null)
    {
        $data   = null;
        $tmp    = [];
        $sheet  = $excelObj->getSheet(0);
        $allRow = $sheet->getHighestRow();
        // 查询分类
        $cates  = Db::name('customs_taxcate')->field('type')->select();
        $cate = [];
        if(!empty($cates)) {
            foreach ($cates as $tys) {
                $cate[$tys['type']] = $tys['type'];
            }
        }
        // 删除原数据
        unset($cates);

        $hsCodes = [];
        // HS表数据；
        $hscode = Db::name('customs_taxnums')->field('hscode')->select();
        if(!empty($hscode)) {
            foreach($hscode as $hs) {
                $hsCodes[$hs['hscode']] = $hs['hscode'];
            }
        }
        // 删除原数据
        unset($hscode);

        // 类型
        $typed = [
            'A'=>'其他',
            'B'=>'奢侈品',
            'C'=>'红酒',
            'D'=>'化妆品',
            'E'=>'个护',
            'F'=>'3C',
            'G'=>'服装包类',
            'H'=>'婴儿奶粉',
            'I'=>'成人奶粉',
            'J'=>'纸尿裤',
            'K'=>'保健品',
            'L'=>'食品',
        ];

        $typeg = [];

        $hcode = [];
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            // 获取类型
            $ty      = trim($excelObj->getActiveSheet()->getCell("A" . $currentRow)->getValue());
            $hscode  = $excelObj->getActiveSheet()->getCell("B" . $currentRow)->getValue();

            array_push($hcode,$hscode);

            // 如果存在数据则不进行添加，否则就添加
            if(!isset($cate[$ty])) {
                $t = isset($typed[$ty]) ? $typed[$ty] : '其他';
                $typeg[$ty]  =  [
                    'type'  => $ty,
                    'title' => $t
                ];
                // 编码表
            }

            // 编码是否存在，存在则不写入
            if(!isset($hsCodes[$hscode])) {
                $data = [
                    'type'      => trim($excelObj->getActiveSheet()->getCell("A" . $currentRow)->getValue()),
                    'hscode'    => $excelObj->getActiveSheet()->getCell("B" . $currentRow)->getValue(),
                    'tariff'    => trim($excelObj->getActiveSheet()->getCell("C" . $currentRow)->getValue()),
                    'tariffdis' => trim($excelObj->getActiveSheet()->getCell("D" . $currentRow)->getValue()),
                    'addtax'    => trim($excelObj->getActiveSheet()->getCell("E" . $currentRow)->getValue()),
                    'addtaxdis' => trim($excelObj->getActiveSheet()->getCell("F" . $currentRow)->getValue()),
                    'excisetax' => trim($excelObj->getActiveSheet()->getCell("G" . $currentRow)->getValue()),
                    'excisetaxdis'  => trim($excelObj->getActiveSheet()->getCell("H" . $currentRow)->getValue()),
                    'add_time'      =>time(),
                ];

                array_push($tmp,$data);
            }

        }

        //file_put_contents("./log/tmp.txt", print_r($tmp,true)."\r\n",FILE_APPEND);
        //file_put_contents("./log/hcode.txt", print_r($hcode,true)."\r\n",FILE_APPEND);

        $flat = false;
        // 写入HS表
        if(!empty($tmp)) {
            Db::name('customs_taxnums')->insertAll($tmp);
            $flat = true;
        }

        // 写入HS分类表
        if(!empty($typeg)) {
            // 写入
            //file_put_contents("./log/type.txt", print_r($typeg,true)."\r\n",FILE_APPEND);
            Db::name('customs_taxcate')->insertAll($typeg);
            $flat = true;
        }
        return $flat;
    }



    // 更新数据
    public function upFile($excelObj = null) {
        $data   = null;
        // 获取hscode
        $hscode = Db::name('customs_taxnums')->field('hscode')->select();
        if(empty($hscode)) {
            return ['code'=>3,'msg'=>'更新失败，数据表为空'];
        }

        $hsCodes = [];
        foreach ($hscode as $iv) {
            $hsCodes[$iv['hscode']] = $iv['hscode'];
        }

        $sheet  = $excelObj->getSheet(0);
        $allRow = $sheet->getHighestRow();
        $code   = null;
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            // 获取类型
            $hscode  = $excelObj->getActiveSheet()->getCell("B" . $currentRow)->getValue();
            //$hscode  = (int)$hscode;
            // 编码是否存在，存在则不写入
            if(isset($hsCodes[$hscode])) {
                $data = [
                    'type'          =>  trim($excelObj->getActiveSheet()->getCell("A" . $currentRow)->getValue()),
                    //'hscode'        =>  trim($excelObj->getActiveSheet()->getCell("B" . $currentRow)->getValue()),
                    'tariff'        =>  trim($excelObj->getActiveSheet()->getCell("C" . $currentRow)->getValue()),
                    'tariffdis'     =>  trim($excelObj->getActiveSheet()->getCell("D" . $currentRow)->getValue()),
                    'addtax'        =>  trim($excelObj->getActiveSheet()->getCell("E" . $currentRow)->getValue()),
                    'addtaxdis'     =>  trim($excelObj->getActiveSheet()->getCell("F" . $currentRow)->getValue()),
                    'excisetax'     =>  trim($excelObj->getActiveSheet()->getCell("G" . $currentRow)->getValue()),
                    'excisetaxdis'  =>  trim($excelObj->getActiveSheet()->getCell("H" . $currentRow)->getValue()),
                    'up_time'       =>  time(),
                ];

                // 更新
                Db::name('customs_taxnums')->where(['hscode'=>$hscode])->update($data);

            } else {
                $code .= $hscode.',';
            }

        }

        // 如果不为空，则有不存在的数据
        if(!empty($code)) {
            return ['code'=>1,'msg'=>'更新成功','hscode'=>trim($code,',')];
        }
        return ['code'=>2,'msg'=>'更新成功'];

    }


}