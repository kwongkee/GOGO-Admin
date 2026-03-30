<?php

namespace app\admin\logic;

use think\Model;
use app\admin\model\GoodsModel;
use app\admin\model\GoodsClassModel;
use PHPExcel;
use Excel5;
use PHPExcel_IOFactory;
use think\MongoDB;
use think\Config;
use think\Db;

/**
 * 读取上传的商品excel文件
 * Class LoadGoodsFile
 * @package app\admin\logic
 */
class LoadGoodsFile extends Model
{
    public $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new GoodsModel();
    }

    /**
     * 保存上传文件
     * @param $file
     * @return string
     * @throws \Exception
     */
    public function saveUploadExcel($file)
    {
        if (!$file) {
            throw new \Exception('请上传文件');
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
        if (!$saveResult) {
            throw new \Exception($file->getError());
        }
        return $path . '/' . $saveResult->getSaveName();
    }

    public function GoodsDataHandle($request)
    {
        $fileName = $this->saveUploadExcel($request->file('file'));
        list($data,$errGoods) = $this->readGoodsInfoByExcel($fileName, $request->post('code'));
        if (!empty($data)){
            $this->model->saveAll($data,false);
        }
        @unlink($fileName);
        return $errGoods;
    }
    
    /**
     * 读取商品excel文件数据
     * @param null $file
     * @param null $code
     * @return array
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function readGoodsInfoByExcel($file = null, $code = null)
    {
        $data = [];
        $errorGoods = '';
        // $isdocument = false;
        $inputFileType = PHPExcel_IOFactory::identify($file);
        $objRead = PHPExcel_IOFactory::createReader($inputFileType);
        $objRead->setReadDataOnly(true);
        $PHPRead = $objRead->load($file);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        $classModel = new GoodsClassModel();
        $mgo = MongoDB::init(Config::load(APP_PATH . 'config/config.php')['order_mongo_dsn']);
        $bulk = new \MongoDB\Driver\BulkWrite();
        
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $goodsStyle=$PHPRead->getActiveSheet()->getCell("G" . $currentRow)->getValue();
            $goodName = $PHPRead->getActiveSheet()->getCell("E" . $currentRow)->getValue();
            $goodNameEn = $PHPRead->getActiveSheet()->getCell("F" . $currentRow)->getValue(); //新增商品名称-英文
            $hsCode = $PHPRead->getActiveSheet()->getCell("C" . $currentRow)->getValue();
            $isComsumption = Db::name('customs_hscode_tariffschedule')->where('hscode',$hsCode)->field('consumption_tax_rate')->find();
            if ($isComsumption['consumption_tax_rate']!=""||$isComsumption['consumption_tax_rate']!=0){
                if ($isComsumption['consumption_tax_rate']!="-"){
                    if (strstr($goodsStyle,"*")){
                        $errorGoods .= "商品规格错误:".$goodName."<br>";
                        continue;
                    }
                    if(!preg_match('/\d+/',$goodsStyle)){
                        $errorGoods .= "商品规格错误:".$goodName.'<br>';
                        continue;
                    }
                }
              
            }
          
            $millisecond = round(explode(" ", microtime())[0] * 1000);
            $goodssn    = 'GG' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                    999) . mt_rand(111, 999);
            array_push($data,[
                'goodssn' => $goodssn,
                'netwt' => $PHPRead->getActiveSheet()->getCell("A" . $currentRow)->getValue(),
                'grosswt' => $PHPRead->getActiveSheet()->getCell("B" . $currentRow)->getValue(),
                'hscode' => $hsCode,
                'ncad_code' => $PHPRead->getActiveSheet()->getCell("D" . $currentRow)->getValue(),
                'goods_name' => $goodName,
                'goods_name_en' => $goodNameEn,
                'goods_style' => $goodsStyle,
                'origin_country' => $PHPRead->getActiveSheet()->getCell("H" . $currentRow)->getValue(),
                'brand' => $PHPRead->getActiveSheet()->getCell("I" . $currentRow)->getValue(),
                'brand_en' => $PHPRead->getActiveSheet()->getCell("J" . $currentRow)->getValue(), //新增品牌-英文
                'gunit' => $PHPRead->getActiveSheet()->getCell("K" . $currentRow)->getValue(),
                'std_unit' => $PHPRead->getActiveSheet()->getCell("L" . $currentRow)->getValue(),
                'sec_unit' => $PHPRead->getActiveSheet()->getCell("M" . $currentRow)->getValue() == '' ? 011 : $PHPRead->getActiveSheet()->getCell("M" . $currentRow)->getValue(),
                'qty' => 999999,
                'price' => "0.00",
                'total_price' => "0.00",
                'ciq_goodsno' => mt_rand(0000000000, 9999999999),
                'cus_goodsno' => mt_rand(0000000000, 9999999999),
                'curr_code' => $PHPRead->getActiveSheet()->getCell("Q" . $currentRow)->getValue() == '' ? 142 : $PHPRead->getActiveSheet()->getCell("Q" . $currentRow)->getValue(),
                'goods_images' => json_encode([
                    "thumb" => "images/total_image/{$goodName}.jpg",
                    "content" => '<p><img src="/images/goodetail/head.jpg" width="100%" style=""/></p><p><img src="/images/total_image/' . $goodName . '_01.jpg" width="100%" style=""/></p><p><img src="/images/total_image/' . $goodName . '.jpg" width="100%" style=""/></p><p><img src="/images/total_image/' . $goodName . '_02.jpg" width="100%" style=""/></p><p><img src="/images/goodetail/footer.jpg" width="100%" style=""/></p><p><br/></p>'
                ]),
                'create_time' => time(),
                'district_code' => $code,
                'class' => $PHPRead->getActiveSheet()->getCell("N" . $currentRow)->getValue(),
                'factory' => $PHPRead->getActiveSheet()->getCell("O" . $currentRow)->getValue(),
                'third_party_url' => $PHPRead->getActiveSheet()->getCell("P" . $currentRow)->getValue(),
                'status' => 1,
                'qr_code'=>$PHPRead->getActiveSheet()->getCell("S" . $currentRow)->getValue(),
                'maxbuy'=>$PHPRead->getActiveSheet()->getCell('T'.$currentRow)->getValue(),
                'type'=>1
            ]);
            if ($PHPRead->getActiveSheet()->getCell("N" . $currentRow)->getValue()!=''){
                $classId = $classModel->where('name', $PHPRead->getActiveSheet()->getCell("N" . $currentRow)->getValue())->find();
                if (empty($classId)) {
                    $classModel->data(['name' => $PHPRead->getActiveSheet()->getCell("N" . $currentRow)->getValue(), "pirce_ratio" => "0"]);
                    $classModel->isUpdate(false)->save();
                }
            }

            $bulk->insert([
                '_id' => new \MongoDB\BSON\ObjectID,
                'goodssn' => $goodssn,
                'url' => $PHPRead->getActiveSheet()->getCell("P" . $currentRow)->getValue(),
                'good_name' => $goodName,
                'status'=>0
            ]);
        }
        try {
            if ($bulk->count()>=1){
                $writeConcern = new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000);
                $mgo->executeBulkWrite('order.goods', $bulk, $writeConcern);
            }
        } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
            if ($writeConcernError = $e->getWriteConcernError()) {
                throw new \Exception(printf("%s (%d): %s\n", $writeConcernError->getMessage(), $writeConcernError->getCode(), var_export($writeConcernError->getInfo(), true)));
            }
        }
        return [$data,$errorGoods];
    }


    public function readExcelAndUpdateTable($file)
    {
        $inputFileType = PHPExcel_IOFactory::identify($file);
        $objRead = PHPExcel_IOFactory::createReader($inputFileType);
        $objRead->setReadDataOnly(true);
        $PHPRead = $objRead->load($file);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $goodsn=trim($PHPRead->getActiveSheet()->getCell('A' . $currentRow)->getValue());
            $goods_name=trim($PHPRead->getActiveSheet()->getCell('B' . $currentRow)->getValue());
            $goods_style=trim($PHPRead->getActiveSheet()->getCell('C' . $currentRow)->getValue());
            $origin_country=trim($PHPRead->getActiveSheet()->getCell('D' . $currentRow)->getValue());
            $brand=trim($PHPRead->getActiveSheet()->getCell('E' . $currentRow)->getValue());
            $price=trim($PHPRead->getActiveSheet()->getCell('F' . $currentRow)->getValue());
            $gunit=trim($PHPRead->getActiveSheet()->getCell('G'.$currentRow)->getValue());
            $std_unit=trim($PHPRead->getActiveSheet()->getCell('H'.$currentRow)->getValue());
            $sec_unit=trim($PHPRead->getActiveSheet()->getCell('I'.$currentRow)->getValue());
            $qty=$PHPRead->getActiveSheet()->getCell('J'.$currentRow)->getValue();
            $sec_qty=$PHPRead->getActiveSheet()->getCell('K'.$currentRow)->getValue();
            $ciq_goodsno=trim($PHPRead->getActiveSheet()->getCell('L'.$currentRow)->getValue());
            $cus_goodsno=trim($PHPRead->getActiveSheet()->getCell('M'.$currentRow)->getValue());
            $ncad_code=trim($PHPRead->getActiveSheet()->getCell('N'.$currentRow)->getValue());
            $qr_code=trim($PHPRead->getActiveSheet()->getCell('O'.$currentRow)->getValue());
            $maxbuy=trim($PHPRead->getActiveSheet()->getCell('P'.$currentRow)->getValue());
            $hscode=trim($PHPRead->getActiveSheet()->getCell('Q'.$currentRow)->getValue());
            $netwt=trim($PHPRead->getActiveSheet()->getCell('R'.$currentRow)->getValue());
            $grosswt=trim($PHPRead->getActiveSheet()->getCell('S'.$currentRow)->getValue());
            $params=[];
            if (empty($goodsn)){
                continue;
            }
            if (!empty($goods_name)){
                $params['goods_name']=$goods_name;
            }
            if (!empty($goods_style)){
                $params['goods_style']=$goods_style;
            }
            if (!empty($origin_country)){
                $params['origin_country']=$origin_country;
            }
            if (!empty($brand)){
                $params['brand']=$brand;
            }
            if (!empty($price)){
                $params['price']=$price;
            }
            if (!empty($gunit)){
                $params['gunit']=$gunit;
            }
            if (!empty($std_unit)){
                $params['std_unit']=$std_unit;
            }
            if (!empty($sec_unit)){
                $params['sec_unit']=$sec_unit;
            }
            if (!empty($qty)){
                $params['qty']=$qty;
            }
            if (!empty($sec_qty)){
                $params['sec_qty']=$sec_qty;
            }
            if (!empty($ciq_goodsno)){
                $params['ciq_goodsno']=$ciq_goodsno;
            }
            if (!empty($cus_goodsno)){
                $params['cus_goodsno']=$cus_goodsno;
            }
            if (!empty($ncad_code)){
                $params['ncad_code']=$ncad_code;
            }
            if (!empty($qr_code)){
                $params['qr_code']=$qr_code;
            }
            if (!empty($maxbuy)){
                $params['maxbuy']=$maxbuy;
            }
            if (!empty($hscode)){
                $params['hscode']=$hscode;
            }
            if (!empty($netwt)){
                $params['netwt']=$netwt;
            }
            if (!empty($grosswt)){
                $params['grosswt']=$grosswt;
            }
            if (!empty($params)){
                $this->model->where('goodssn',$goodsn)->update($params);
            }
        }
    }

    //验证正面数据
    public function hasHsCode($code)
    {
        return Db::name('tax_number')->where('tax_number', $code)->field('id')->find();
    }
}
