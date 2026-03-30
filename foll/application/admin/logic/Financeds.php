<?php
namespace app\admin\logic;

use think\Model;
use think\DB;
use PHPExcel;
use PHPExcel_IOFactory;

class Financeds extends Model
{
    // 批次导出
    public function Exports($batch_num) {

        if(empty($batch_num)) {
            return '查询批次号为空';
        }
        $sbStatus = ['未申报','已申报','申报失败'];
        $payStatus = ['待支付','已支付','支付失败'];
        $fields = ['id', 'EntOrderNo', 'OrderDate', 'WaybillNo', 'goodsNo', 'BarCode', 'GoodsName', 'GoodsStyle', 'OriginCountry', 'packageType', 'unit', 'Qty', 'Price', 'OrderGoodTotal', 'Tax', 'OrderGoodTotalCurr', 'Freight', 'insuredFree', 'OrderDocAcount', 'OrderDocName', 'OrderDocId', 'weight', 'grossWeight', 'OrderDocTel', 'Province', 'city', 'county', 'RecipientAddr', 'senderName', 'senderTel', 'senderAddr', 'senderCountry', 'senderProvincesCode', 'PayNo', 'Pay_time', 'err_msg','PayStatus','sbStatus'];

        $data = DB::name('customs_elec_order_detail')->where('batch_num',$batch_num)->field($fields)->select();
        if(empty($data)) {
            return '没有查到该批次号的数据';
        }

        $headlist = ['A1' => '订单编号', 'B1' => '订单生成时间', 'C1' => '物流运单号码', 'D1' => '商品货号', 'E1' => '条形码', 'F1' => '商品名称', 'G1' => '型号规格', 'H1' => '原产国', 'I1' => '包装类型', 'J1' => '单位', 'K1' => '数量', 'L1' => '单价', 'M1' => '货款金额', 'N1' => '税费', 'O1' => '币制', 'P1' => '运费', 'Q1' => '保价费', 'R1' => '订购人用户名', 'S1' => '订购人姓名', 'T1' => '订购人证件号码', 'U1' => '净重', 'V1' => '毛重', 'W1' => '收货人电话', 'X1' => '收货人省', 'Y1' => '收货人城市', 'Z1' => '收货人区/县', 'AA1' => '收货人地址', 'AB1' => '发货人姓名', 'AC1' => '发货人电话', 'AD1' => '发货人地址', 'AE1' => '发货人所在国', 'AF1' => '发货人省市区代码', 'AG1' => '支付交易号', 'AH1' => '支付时间', 'AI1' => '商品序号', 'AJ1' => '备注','AK1'=>'支付状态','AL1'=>'申报状态'];

        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('Sheet1'); //给当前活动sheet设置名称
        foreach ($headlist as $key => $val) {
            $PHPSheet->setCellValue($key, $val);
        }
        $n = 2;
        $gid = null;//商品号
        $goodsArray = [];//新的商品数组
        foreach ($data as $key => $val) {
            $Gno = json_decode($val['goodsNo'], true);
            foreach ($Gno as $value) {
                $gid .= $value['goodNo'] . ',';
            }
        }

        $goodInfo = Db::name('foll_goodsreglist')->where('EntGoodsNo', 'in', trim($gid, ','))->select();

        foreach ($goodInfo as $val) {
            $goodsArray[$val['EntGoodsNo']] = $val;
        }
        foreach ($data as $key => $val) {
            $gods = json_decode($val['goodsNo'], true);
            foreach ($gods as $k => $v) {
                $PHPSheet->setCellValue("A" . $n, $val['EntOrderNo'])
                    ->setCellValue("B" . $n, $val['OrderDate'])
                    ->setCellValue("C" . $n, "\t" . $val['WaybillNo'] . "\t")
                    ->setCellValue("D" . $n, $v['goodNo'])
                    ->setCellValue("E" . $n, $goodsArray[$v['goodNo']]['BarCode'])
                    ->setCellValue("F" . $n, $goodsArray[$v['goodNo']]['GoodsName'])
                    ->setCellValue("G" . $n, $goodsArray[$v['goodNo']]['GoodsStyle'])
                    ->setCellValue("H" . $n, $goodsArray[$v['goodNo']]['OriginCountry'])
                    ->setCellValue("I" . $n, $val['packageType'])
                    ->setCellValue("J" . $n, $goodsArray[$v['goodNo']]['GUnit'])
                    ->setCellValue("K" . $n, $v['num'])
                    ->setCellValue("L" . $n, $v['price'])
                    ->setCellValue("M" . $n, sprintf("%2.f", $v['price']) * $v['num'])
                    ->setCellValue("N" . $n, $val['Tax'])
                    ->setCellValue("O" . $n, $val['OrderGoodTotalCurr'])
                    ->setCellValue("P" . $n, $val['Freight'])
                    ->setCellValue("Q" . $n, $val['insuredFree'])
                    ->setCellValue("R" . $n, $val['OrderDocName'])
                    ->setCellValue("S" . $n, $val['OrderDocName'])
                    ->setCellValue("T" . $n, "\t" . $val['OrderDocId'] . "\t")
                    ->setCellValue("U" . $n, $goodsArray[$v['goodNo']]['NetWt'])
                    ->setCellValue("V" . $n, $goodsArray[$v['goodNo']]['GrossWt'])
                    ->setCellValue("W" . $n, $val['OrderDocTel'])
                    ->setCellValue("X" . $n, $val['Province'])
                    ->setCellValue("Y" . $n, $val['city'])
                    ->setCellValue("Z" . $n, $val['county'])
                    ->setCellValue("AA" . $n, $val['RecipientAddr'])
                    ->setCellValue("AB" . $n, $val['senderName'])
                    ->setCellValue("AC" . $n, "'" . $val['senderTel'])
                    ->setCellValue("AD" . $n, $val['senderAddr'])
                    ->setCellValue("AE" . $n, $val['senderCountry'])
                    ->setCellValue("AF" . $n, $val['senderProvincesCode'])
                    ->setCellValue("AG" . $n, "\t" . $val['PayNo'] . "\t")
                    ->setCellValue("AH" . $n, $val['OrderDate'])
                    ->setCellValue("AI" . $n, $k + 1)
                    ->setCellValue('AJ' . $n, $val['err_msg'])
                    ->setCellValue('AK' . $n,$payStatus[$val['PayStatus']])
                    ->setCellValue('AL' . $n,$sbStatus[$val['sbStatus']]);
                $n += 1;
            }
        }

        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        unset($goodsArray, $data, $goodInfo);
        return $ExcelWrite;

    }

    // 按提单号导出
    public function Mexports($bill_num,$bill_id)
    {
        $months = DB::name('customs_reconcils')
                        ->field('accountDay')
                        ->where('bill_id',$bill_id)->find();
        $firstday = date('Y-m-01 00:00:01',strtotime($months['accountDay']));
        $lastday  = date('Y-m-d 23:59:59',strtotime("$firstday+1 month -1 day"));

        $start  = strtotime($firstday);
        $ends   = strtotime($lastday);

        $batch = DB::name('customs_batch')
                ->field('batch_num')
                ->where('bill_num',$bill_num)->whereBetween('Timerub',[$start,$ends],'AND')->select();

        $ins = '';
        // 如果是一个数组，直接取，否则循环拼接数据
        if(count($batch) >1 ) {
            foreach($batch as $k=>$v) {
                $ins = $v['batch_num'].',';
            }
        } else if(count($batch) > 0) {
            $ins = $batch[0]['batch_num'];
        }
        $ins = trim($ins);

        $where['batch_num'] = ['in',$ins];
        $data = Db::name('customs_elec_order_detail')
            ->where($where)
            ->field(['id', 'EntOrderNo', 'OrderDate', 'WaybillNo', 'goodsNo', 'BarCode', 'GoodsName', 'GoodsStyle', 'OriginCountry', 'packageType', 'unit', 'Qty', 'Price', 'OrderGoodTotal', 'Tax', 'OrderGoodTotalCurr', 'Freight', 'insuredFree', 'OrderDocAcount', 'OrderDocName', 'OrderDocId', 'weight', 'grossWeight', 'OrderDocTel', 'Province', 'city', 'county', 'RecipientAddr', 'senderName', 'senderTel', 'senderAddr', 'senderCountry', 'senderProvincesCode', 'PayNo', 'Pay_time', 'err_msg','sbStatus'])
            ->select();
        if (empty($data)) {
            return '暂无数据';
        }

        $headlist = ['A1' => '订单编号', 'B1' => '订单生成时间', 'C1' => '物流运单号码', 'D1' => '商品货号', 'E1' => '条形码', 'F1' => '商品名称', 'G1' => '型号规格', 'H1' => '原产国', 'I1' => '包装类型', 'J1' => '单位', 'K1' => '数量', 'L1' => '单价', 'M1' => '货款金额', 'N1' => '税费', 'O1' => '币制', 'P1' => '运费', 'Q1' => '保价费', 'R1' => '订购人用户名', 'S1' => '订购人姓名', 'T1' => '订购人证件号码', 'U1' => '净重', 'V1' => '毛重', 'W1' => '收货人电话', 'X1' => '收货人省', 'Y1' => '收货人城市', 'Z1' => '收货人区/县', 'AA1' => '收货人地址', 'AB1' => '发货人姓名', 'AC1' => '发货人电话', 'AD1' => '发货人地址', 'AE1' => '发货人所在国', 'AF1' => '发货人省市区代码', 'AG1' => '支付交易号', 'AH1' => '支付时间', 'AI1' => '商品序号', 'AJ1' => '备注','AK1'=>'申报状态'];

        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('Sheet1'); //给当前活动sheet设置名称
        foreach ($headlist as $key => $val) {
            $PHPSheet->setCellValue($key, $val);
        }
        $n = 2;
        $gid = null;//商品号
        $goodsArray = [];//新的商品数组
        foreach ($data as $key => $val) {
            $Gno = json_decode($val['goodsNo'], true);
            foreach ($Gno as $value) {
                $gid .= $value['goodNo'] . ',';
            }
        }

        $goodInfo = Db::name('foll_goodsreglist')->where('EntGoodsNo', 'in', trim($gid, ','))->select();

        foreach ($goodInfo as $val) {
            $goodsArray[$val['EntGoodsNo']] = $val;
        }
        //0、未申报，1已申报、2：申报失败
        $sbStatus = ['未申报','已申报','申报失败'];

        foreach ($data as $key => $val) {
            $gods = json_decode($val['goodsNo'], true);
            foreach ($gods as $k => $v) {
                $PHPSheet->setCellValue("A" . $n, $val['EntOrderNo'])
                    ->setCellValue("B" . $n, $val['OrderDate'])
                    ->setCellValue("C" . $n, "\t" . $val['WaybillNo'] . "\t")
                    ->setCellValue("D" . $n, $v['goodNo'])
                    ->setCellValue("E" . $n, $goodsArray[$v['goodNo']]['BarCode'])
                    ->setCellValue("F" . $n, $goodsArray[$v['goodNo']]['GoodsName'])
                    ->setCellValue("G" . $n, $goodsArray[$v['goodNo']]['GoodsStyle'])
                    ->setCellValue("H" . $n, $goodsArray[$v['goodNo']]['OriginCountry'])
                    ->setCellValue("I" . $n, $val['packageType'])
                    ->setCellValue("J" . $n, $goodsArray[$v['goodNo']]['GUnit'])
                    ->setCellValue("K" . $n, $v['num'])
                    ->setCellValue("L" . $n, $v['price'])
                    ->setCellValue("M" . $n, sprintf("%2.f", $v['price']) * $v['num'])
                    ->setCellValue("N" . $n, $val['Tax'])
                    ->setCellValue("O" . $n, $val['OrderGoodTotalCurr'])
                    ->setCellValue("P" . $n, $val['Freight'])
                    ->setCellValue("Q" . $n, $val['insuredFree'])
                    ->setCellValue("R" . $n, $val['OrderDocName'])
                    ->setCellValue("S" . $n, $val['OrderDocName'])
                    ->setCellValue("T" . $n, "\t" . $val['OrderDocId'] . "\t")
                    ->setCellValue("U" . $n, $goodsArray[$v['goodNo']]['NetWt'])
                    ->setCellValue("V" . $n, $goodsArray[$v['goodNo']]['GrossWt'])
                    ->setCellValue("W" . $n, $val['OrderDocTel'])
                    ->setCellValue("X" . $n, $val['Province'])
                    ->setCellValue("Y" . $n, $val['city'])
                    ->setCellValue("Z" . $n, $val['county'])
                    ->setCellValue("AA" . $n, $val['RecipientAddr'])
                    ->setCellValue("AB" . $n, $val['senderName'])
                    ->setCellValue("AC" . $n, "'" . $val['senderTel'])
                    ->setCellValue("AD" . $n, $val['senderAddr'])
                    ->setCellValue("AE" . $n, $val['senderCountry'])
                    ->setCellValue("AF" . $n, $val['senderProvincesCode'])
                    ->setCellValue("AG" . $n, "\t" . $val['PayNo'] . "\t")
                    ->setCellValue("AH" . $n, $val['OrderDate'])
                    ->setCellValue("AI" . $n, $k + 1)
                    ->setCellValue('AJ' . $n, $val['err_msg'])
                    ->setCellValue('AK' . $n, $sbStatus[$val['sbStatus']]);
                $n += 1;
            }
        }

        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        unset($goodsArray, $data, $goodInfo);
        return $ExcelWrite;
    }
}

?>