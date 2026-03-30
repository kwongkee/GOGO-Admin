<?php

namespace app\admin\logic;


use app\admin\model\GoodsImportTypeModel;
use app\admin\model\CustomsHscodeTariffScheduleModel;
use app\admin\model\CustomsHsCodeRateModel;
/**
 * 商品进口类型检测服务类
 * Class GoodsImportTypeLogic
 * @package app\admin\logic
 */
class GoodsImportTypeLogic {


    /**
     * save upload excel file
     * @param $request
     * @return string
     * @throws \Exception
     */
    public function saveFile($request) {
        $file = $request->file('file');
        if (!$file) {
            throw new \Exception('参数错误');
        }
        $dirPath  = ROOT_PATH . 'public/uploads/excel';
        $saveInfo = $file->validate(['ext' => 'xlsx'])->move($dirPath);
        if (!$saveInfo) {
            throw new \Exception($file->getError());
        }
        return $dirPath . '/' . $saveInfo->getSaveName();
    }


    /**
     * read excel file
     * @param $file
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function readFileContent($file) {
        $type      = \PHPExcel_IOFactory::identify($file);
        $objReader = \PHPExcel_IOFactory::createReader($type);
        $objReader->setReadDataOnly(true);
        $phpReader = $objReader->load($file);
        $sheet     = $phpReader->getSheet(0);
        $row       = $sheet->getHighestRow();
        $data      = [];
        for ($i = 2; $i <= $row; $i++) {
            if ($sheet->getCellByColumnAndRow(0, $i)->getValue() == "") {
                continue;
            }
            array_push($data, [
                'goodssn'   => $sheet->getCellByColumnAndRow(0, $i)->getValue(),
                'goodsName' => $sheet->getCellByColumnAndRow(1, $i)->getValue(),
                'price'     => $sheet->getCellByColumnAndRow(2, $i)->getValue(),
                'hsCode'    => $sheet->getCellByColumnAndRow(3, $i)->getValue()
            ]);
        }
        return $data;
    }


    /**
     * 计算费率并决定商品那种进口方式
     * @param $data
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function countRate($data) {
        $hsCodeRate = CustomsHscodeTariffScheduleModel::where('hscode', $data['hsCode'])->field(['consumption_tax_rate', 'vat_rate'])->find()->toArray();
        if (!$hsCodeRate) {
            $data['declType'] = '不可以';
            return $data;
        }
        $hsCodeRate['consumption_tax_rate'] = ($hsCodeRate['consumption_tax_rate'] == "" || $hsCodeRate['consumption_tax_rate'] == '-') ? 0 : $hsCodeRate['consumption_tax_rate'];
        $hsCodeRate['vat_rate'] = ($hsCodeRate['vat_rate'] == "" || $hsCodeRate['vat_rate'] == '-') ? 0 : $hsCodeRate['vat_rate'];
        $comprehensiveTaxRate = (($hsCodeRate['consumption_tax_rate'] + $hsCodeRate['vat_rate']) / (1 - $hsCodeRate['consumption_tax_rate'])) * 0.7;//[（消费税率 + 增值税率）/（1-消费税率）]×70%
        $comprehensiveTaxRate = sprintf("%.2f", $comprehensiveTaxRate);//跨境综合税率
        $comprehensiveRate = $data['price'] * $comprehensiveTaxRate;//跨境综合税=零售价格 x 跨境综合税率
        $hsCodeTwo = substr($data['hsCode'], 0, 2);
        $hsCodeFour = substr($data['hsCode'], 0, 4);
        $hsCodeSix = substr($data['hsCode'], 0, 6);
        $rateRes = CustomsHsCodeRateModel::where("code='{$hsCodeTwo}' or code='{$hsCodeFour}' or code='{$hsCodeSix}'")->select();
        if (empty($rateRes)){
            $data['declType']='不可以';
            return $data;
        }
        $isRate = 0;
        foreach ($rateRes as $v){
            $isRate += sprintf("%.2f",$comprehensiveRate*($v['rate']/100));
        }
        if ($isRate<=50) {
            $data['declType'] = 'CC';
        }else{
            $data['declType']='BC';
        }
        return $data;
    }

    public function save($data) {
        GoodsImportTypeModel::create([
                'goodssn'=>$data['goodssn'],
                'goods_name'=>$data['goodsName'],
                'price'=>$data['price'],
                'hs_code'=>$data['hsCode'],
                'decl_type'=>$data['declType'],
                'create_time'=>time()
        ]);
    }
}