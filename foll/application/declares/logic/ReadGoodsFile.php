<?php

namespace app\declares\logic;

use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Model;

class ReadGoodsFile extends Model
{
    
    protected $objReaders;
    
    protected $PHPExcel;
    
    public function handleFile ( $file, $inData )
    {
        
        try {
            $this->objReaders = PHPExcel_IOFactory::createReader(PHPExcel_IOFactory::identify($file));
            $this->objReaders->setReadDataOnly(true);
            $this->PHPExcel = $this->objReaders->load($file);
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
        // 获取头信息
        $hid      = $this->saveHeaInfo($this->fetchHeadInfo($inData));
        $d        = $this->fetchHeadInfo($inData);
        $bodyInfo = $this->fetchBodyInfo($hid);
        $uniacid  = Session('admin.uniacid');
        unset($this->objReaders,$this->PHPExcel);
        foreach ($bodyInfo as $value) {
            if (empty($value['EntGoodsNo'])){
                continue;
            }
            // 添加订单信息
            $this->addGoods($value,$uniacid);
            unset($value['pcate']);
            $this->saveGoodsInfo($value);
        }
        @unlink($file);
        return null;
    }
    
    
    /**
     * 读取备案信息
     */
    protected function fetchHeadInfo ( $input )
    {
        $data      = [];
        $currSheet = $this->PHPExcel->getSheet(0);

        $data      = [
            'DeclEntNo' => $input['DeclEntNo'], 'DeclEntName' => $input['DeclEntName'],
            'EBEntNo' => $currSheet->getCell('B2')->getValue(),
            'EBEntName' => $currSheet->getCell('D2')->getValue(),
            'OpType' => 'A', 'CustomsCode' => $currSheet->getCell('F2')->getValue(),
            'CIQOrgCode' => $currSheet->getCell('B3')->getValue(),
            'EBPEntNo' => $currSheet->getCell('D3')->getValue(),
            'EBPEntName' => $currSheet->getCell('F3')->getValue(),
            'CurrCode' => $currSheet->getCell('B4')->getValue(),
            'BusinessType' => $currSheet->getCell('D4')->getValue(),
            'InputDate' => time(), 'DeclTime' => $input['SendTime'],
            'IeFlag' => $currSheet->getCell('F4')->getValue(),
            'uid' => Session('admin.id'), 'declare_status' => 0,
            'g_check' => 1, 'message_header' => '无'];
        return $data;
        
    }
    
    
    /**保存备案头
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    protected function saveHeaInfo ( $data )
    {
        Db::startTrans();
        try {
            $id = Db::name('foll_goodsreghead')->insertGetId($data);
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            throw new \Exception($exception->getMessage());
        }
        return $id;
    }
    
    
    /**
     * 读取商品详情信息
     * @param $hid
     * @return array
     */
    protected function fetchBodyInfo ( $hid )
    {
        $currSheet = $this->PHPExcel->getSheet(1);
        $row       = $currSheet->getHighestRow();
        $col       = $currSheet->getHighestColumn();
        $data      = [];
        $time      = time();
        for ($i = 2; $i <= $row; $i++) {
            $roInfo                   = [];
            $roInfo['head_id']        = $hid;
            $roInfo['Seq']            = $currSheet->getCell('A' . $i)->getValue();
            $roInfo['EntGoodsNo']     = $currSheet->getCell('B' . $i)->getValue();
            $roInfo['CIQGoodsNo']     = $currSheet->getCell('C' . $i)->getValue();
            $roInfo['CusGoodsNo']     = $currSheet->getCell('D' . $i)->getValue();
            $roInfo['EmsNo']          = $currSheet->getCell('U' . $i)->getValue();
            $roInfo['ItemNo']         = $currSheet->getCell('V' . $i)->getValue();
            $roInfo['ShelfGName']     = $currSheet->getCell('E' . $i)->getValue();
            $roInfo['NcadCode']       = $currSheet->getCell('F' . $i)->getValue();
            $roInfo['HSCode']         = $currSheet->getCell('G' . $i)->getValue();
            $roInfo['BarCode']        = $currSheet->getCell('W' . $i)->getValue();
            $roInfo['GoodsName']      = $currSheet->getCell('H' . $i)->getValue();
            $roInfo['GoodsStyle']     = $currSheet->getCell('I' . $i)->getValue();
            $roInfo['Brand']          = $currSheet->getCell('J' . $i)->getValue();
            $roInfo['GUnit']          = $currSheet->getCell('K' . $i)->getValue();
            $roInfo['StdUnit']        = $currSheet->getCell('L' . $i)->getValue();
            $roInfo['SecUnit']        = $currSheet->getCell('M' . $i)->getValue();
            $roInfo['RegPrice']       = $currSheet->getCell('N' . $i)->getValue();
            $roInfo['GiftFlag']       = $currSheet->getCell('O' . $i)->getValue();
            $roInfo['OriginCountry']  = $currSheet->getCell('P' . $i)->getValue();
            $roInfo['Quality']        = $currSheet->getCell('X' . $i)->getValue();
            $roInfo['QualityCertify'] = $currSheet->getCell('Q' . $i)->getValue();
            $roInfo['Manufactory']    = $currSheet->getCell('R' . $i)->getValue();
            $roInfo['NetWt']          = $currSheet->getCell('S' . $i)->getValue();

            $roInfo['GrossWt']        = $currSheet->getCell('T' . $i)->getValue();
            $roInfo['Notes']          = $currSheet->getCell('Y' . $i)->getValue();
            $roInfo['CIQGRegStatus']  = 'C';
            $roInfo['OpType']         = 1;
            $roInfo['OpTime']         = $time;
            $roInfo['pcate']          = $currSheet->getCell('Z'.$i)->getValue();
            $data[]                   = $roInfo;
        }
        return $data;
    }
    
    
    /**
     * 保存备案表
     * @param $data
     */
    protected function saveGoodsInfo ( $data )
    {
        try{
            Db::name('foll_goodsreglist')->insert($data);
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
        
    }
    
    
    /**
     * 插入订单表
     * @param $datas
     * @param $uniacid
     */
    protected function addGoods ( $datas, $uniacid )
    {
        // 查询订单表中是否存在
        $isGoods =  Db::name('sz_yi_goods')->where(['goodssn'=>$datas['EntGoodsNo'],'deleted'=>0])->field('id')->find();
        if (!empty($isGoods)){// 存在直接返回
            return false;
        }

        $year = date('Y',time());
        $month = date('m',time());
        $saleCount  = 20;
        $viewCount = ceil($saleCount/ 0.3+999);
        $totalCount = 999;
        $time       = time();

        $id         = Db::name("sz_yi_goods")->insertGetId([
            'uniacid' => $uniacid,
            'pcate' =>$datas['pcate']==null?0:$datas['pcate'],
            'type' => 1,
            'status' => 1,
            'title' => $datas['ShelfGName'],
            'thumb' => 'images/' . $uniacid . '/' . $year . '/' . $month . '/' . $datas['EntGoodsNo'] . '.jpg',
            'content' => '<p><img src="/images/goodetail/head.jpg" width="100%" style=""/></p><p><img src="/images/' . $uniacid . '/' . $year . '/' . $month . '/' . $datas['EntGoodsNo'] . '.jpg" width="100%" style=""/></p><p><img src="/images/goodetail/footer.jpg" width="100%" style=""/></p><p><br/></p>',
            'goodssn' => $datas['EntGoodsNo'],
            'productsn' => $datas['BarCode'],
            'marketprice' => $datas['RegPrice'],
            'total' => $totalCount,
            'totalcnf' => 1,
            'sales' => $saleCount,
            'createtime' => $time,
            'timestart' => $time,
            'timeend' => $time,
            'viewcount' => $viewCount,
            'cash' => 1,
            'isverify' => 1,
            'noticetype' => 0,
            'supplier_uid' => 6,
            'diyformtype' => 1,
            'dispatchtype' => 1,
            'diyformid' => 5,
            'CIQGoodsNo' => $datas['CIQGoodsNo'],
            'CusGoodsNo' => $datas['CusGoodsNo']
            ]);

        $param      = [
                ['uniacid' => $uniacid, 'goodsid' => $id, 'title' => '产品名称', 'value' => $datas['ShelfGName'], 'displayorder' => 0],
                ['uniacid' => $uniacid, 'goodsid' => $id, 'title' => '型号规格', 'value' => $datas['GoodsStyle'], 'displayorder' => 0],
                ['uniacid' => $uniacid, 'goodsid' => $id, 'title' => '厂家', 'value' => $datas['Manufactory'], 'displayorder' => 1],
                ['uniacid'=>$uniacid, 'goodsid'=>$id, 'title'=>'进口备案', 'value'=>$datas['CIQGoodsNo'], 'displayorder'=>0],
                ['uniacid'=>$uniacid, 'goodsid'=>$id, 'title'=>'毛重', 'value'=>$datas['GrossWt'], 'displayorder'=>1],
                ['uniacid' => $uniacid, 'goodsid' => $id, 'title' => '品牌', 'value' => $datas['Brand'], 'displayorder' => 2],
                ['uniacid' => $uniacid, 'goodsid' => $id, 'title' => '品质', 'value' => $datas['QualityCertify'], 'displayorder' => 3],
            ];
        Db::name("sz_yi_goods_param")->insertAll($param);
    }
    
    
}