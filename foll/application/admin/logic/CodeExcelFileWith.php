<?php

namespace app\admin\logic;

use think\Model;
use PHPExcel;
use Excel5;
use PHPExcel_IOFactory;
use think\Db;

class CodeExcelFileWith extends Model
{


    /**
     * 保存代码文档
     * @param null $excelObj
     */
    public function saveCodeTable($excelObj = null)
    {
        //获取表1数据关区代码
        $this->customDisc($excelObj);
        //获取表2数据检验检疫
        $this->inspection($excelObj);
        //表3数据国家代码
        $this->countryCode($excelObj);
        //表4数据港口代码
        $this->portCode($excelObj);
        //表5数据行政区域
        $this->administrativeCode($excelObj);
        //表6数据监管场所
        $this->supervisoryAreas($excelObj);
        //表7数据监管点
        $this->regulatoryPoint($excelObj);
        //表8数据口岸
        $this->port($excelObj);
        //表9数据运输工具
        $this->transport($excelObj);
        //表10数据包装种类
        $this->packingType($excelObj);
        //表11数据计量单位
        $this->unit($excelObj);
        //表12数据币制
        $this->monetary($excelObj);
        //表13数据贸易方式
        $this->tradeWay($excelObj);
        //表14数据用途代码
        $this->useCode($excelObj);
        //表15数据成交方式
        $this->dealWay($excelObj);
        //表16数据征税方式
        $this->taxWay($excelObj);
    }

    /**
     * 保存正面清单代码
     * @param $data
     */
    public function saveCodeListTable($excelObj = null)
    {
        $data   = null;
        $sheet  = $excelObj->getSheet(0);
        $allRow = $sheet->getHighestRow();
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {

            $num = $excelObj->getActiveSheet()->getCell("A" . $currentRow)->getValue();
            $taxId = Db::name('tax_number')->where('tax_number',$num)->field('id')->find();
            if(empty($taxId)){
                $data = [
                    'tax_number'      => $excelObj->getActiveSheet()->getCell("A" . $currentRow)->getValue(),
                    'goods_name'      => trim($excelObj->getActiveSheet()->getCell("B" . $currentRow)->getValue()),
                    'license'         => $excelObj->getActiveSheet()->getCell("C" . $currentRow)->getValue(),
                    'ordinary'        => $excelObj->getActiveSheet()->getCell("D" . $currentRow)->getValue(),
                    'preferential'    => $excelObj->getActiveSheet()->getCell("E" . $currentRow)->getValue(),
                    'remark'          => $excelObj->getActiveSheet()->getCell("F" . $currentRow)->getValue(),
                    'export_tax_rate' => $excelObj->getActiveSheet()->getCell("G" . $currentRow)->getValue(),
                    'vat_rate'        => $excelObj->getActiveSheet()->getCell("H" . $currentRow)->getValue(),
                    'std_unit'        => $excelObj->getActiveSheet()->getCell("J" . $currentRow)->getValue(),
                    'sec_unit'        => $excelObj->getActiveSheet()->getCell("K" . $currentRow)->getValue(),
                ];
                Db::name('tax_number')->insert($data);
            }else{
                $data = [
                    'tax_number'      => $excelObj->getActiveSheet()->getCell("A" . $currentRow)->getValue(),
                    'goods_name'      => trim($excelObj->getActiveSheet()->getCell("B" . $currentRow)->getValue()),
                    'license'         => $excelObj->getActiveSheet()->getCell("C" . $currentRow)->getValue(),
                    'ordinary'        => $excelObj->getActiveSheet()->getCell("D" . $currentRow)->getValue(),
                    'preferential'    => $excelObj->getActiveSheet()->getCell("E" . $currentRow)->getValue(),
                    'remark'          => $excelObj->getActiveSheet()->getCell("F" . $currentRow)->getValue(),
                    'export_tax_rate' => $excelObj->getActiveSheet()->getCell("G" . $currentRow)->getValue(),
                    'vat_rate'        => $excelObj->getActiveSheet()->getCell("H" . $currentRow)->getValue(),
                    'std_unit'        => $excelObj->getActiveSheet()->getCell("J" . $currentRow)->getValue(),
                    'sec_unit'        => $excelObj->getActiveSheet()->getCell("K" . $currentRow)->getValue(),
                ];
                Db::name('tax_number')->where('id',$taxId['id'])->update($data);
            }

        }
    }


    /**
     * 读取excel文件
     * @param null $file
     * @return object
     * @throws \Exception
     */
    public function readCodeFile($file = null)
    {
        if (!file_exists($file)) {
            throw new \Exception('读取文件失败');
        }
        $type      = pathinfo($file);
        $type      = strtolower($type["extension"]);
        $type      = $type === 'csv' ? $type : 'Excel5';
        $objReader = PHPExcel_IOFactory::createReader($type);
        $objReader->setReadDataOnly(true);
        $PHPReader = $objReader->load($file);
        return $PHPReader;
    }


    /**
     * @param $excelObj
     * @param $sheet
     * @return void
     */
    protected function customDisc($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(0)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('custom_district')->insertAll($data);
    }

    /**
     * @param $excelObj
     * @param $sheet
     */
    protected function inspection($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(1)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'       => $value[2],
                'code_value'      => $value[1],
                'code_path_value' => $value[3],
                'code_path_name'  => $value[4],
            ];
        }
        Db::name('inspection')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function countryCode($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(2)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('country_code')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function portCode($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(3)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('port_code')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function administrativeCode($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(4)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'       => $value[3],
                'code_value'      => $value[2],
                'code_path_name'  => $value[5],
                'code_path_value' => $value[4],
            ];
        }
        Db::name('administrative_code')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function supervisoryAreas($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(5)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('supervisory_areas')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function regulatoryPoint($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(6)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('regulatory_point')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function port($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(7)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('port')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function transport($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(8)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('transport')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function packingType($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(9)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('packing_type')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function unit($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(10)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('unit')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function monetary($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(11)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('monetary')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function tradeWay($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(12)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('tradeway')->insertAll($data);
    }

    /**
     * @param $excelObj
     *
     */
    protected function dealWay($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(14)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('dealway')->insertAll($data);

    }

    /**
     * @param $excelObj
     *
     */
    protected function useCode($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(13)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('use_code')->insertAll($data);

    }

    /**
     * @param $excelObj
     */
    protected function taxWay($excelObj)
    {
        $data   = [];
        $allRow = $excelObj->getSheet(15)->toArray();
        unset($allRow[0]);
        foreach ($allRow as $value) {
            $data[] = [
                'code_name'  => $value[3],
                'code_value' => $value[2],
            ];
        }
        Db::name('taxway')->insertAll($data);
    }
}
