<?php

namespace app\admin\logic;

use think\Model;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;

class ElistExportExcel extends Model
{


    /**
     * @deprecated 导出类型1
     * @param $input
     * @return \PHPExcel_Writer_IWriter
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function orderElistExproTypeOne($input)
    {
        $where = null;
        $data = null;

        if (isset($input['bill_num']) && $input['bill_num'] != '') {
            $batchRes = Db::name('customs_batch')->where('bill_num', $input['bill_num'])->field('batch_num')->select();
            $bid = null;
            foreach ($batchRes as $val) {
                $bid .= $val['batch_num'] . ',';
            }
            $where['batch_num'] = ['in', trim($bid, ',')];
            //$where['elecStatus'] = ['eq',1];
            unset($batchRes, $bid);
        } elseif (isset($input['batch_num']) && $input['batch_num'] != '') {
            $where = ['batch_num' => $input['batch_num']];//,'elecStatus'=>1
        } else {
            return '导出失败';
        }
        
        $data = Db::name('customs_elec_order_detail')
            ->where($where)
            ->field(['id', 'EntOrderNo', 'OrderDate', 'WaybillNo', 'goodsNo', 'BarCode', 'GoodsName', 'GoodsStyle', 'OriginCountry', 'packageType', 'unit', 'Qty', 'Price', 'OrderGoodTotal', 'Tax', 'OrderGoodTotalCurr', 'Freight', 'insuredFree', 'OrderDocAcount', 'OrderDocName', 'OrderDocId', 'weight', 'grossWeight', 'OrderDocTel', 'Province', 'city', 'county', 'RecipientAddr', 'senderName', 'senderTel', 'senderAddr', 'senderCountry', 'senderProvincesCode', 'PayNo', 'Pay_time', 'err_msg','PayStatus','elecStatus','sbStatus'])
            ->select();

        if (empty($data)) {
            return '暂无数据';
        }

        $headlist = ['A1' => '订单编号', 'B1' => '订单生成时间', 'C1' => '物流运单号码', 'D1' => '商品货号', 'E1' => '条形码', 'F1' => '商品名称', 'G1' => '型号规格', 'H1' => '原产国', 'I1' => '包装类型', 'J1' => '单位', 'K1' => '数量', 'L1' => '单价', 'M1' => '货款金额', 'N1' => '税费', 'O1' => '币制', 'P1' => '运费', 'Q1' => '保价费', 'R1' => '订购人用户名', 'S1' => '订购人姓名', 'T1' => '订购人证件号码', 'U1' => '净重', 'V1' => '毛重', 'W1' => '收货人电话', 'X1' => '收货人省', 'Y1' => '收货人城市', 'Z1' => '收货人区/县', 'AA1' => '收货人地址', 'AB1' => '发货人姓名', 'AC1' => '发货人电话', 'AD1' => '发货人地址', 'AE1' => '发货人所在国', 'AF1' => '发货人省市区代码', 'AG1' => '支付交易号', 'AH1' => '支付时间', 'AI1' => '商品序号', 'AJ1' => '备注','AK1'=>'支付状态','AL1'=>'转发状态','AM1'=>'申报状态'];

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

        // 支付状态
        $paystatus = ['未付款','已付款','支付失败'];
        // 转发状态
        $elecstatus = ['待转发','已转发','转发失败'];
        // 申报状态
        $sbStatus   = ['未申报','已申报','申报失败'];

        $goodInfo = Db::name('foll_goodsreglist')->where('EntGoodsNo', 'in', trim($gid, ','))->select();

        foreach ($goodInfo as $val) {
            $goodsArray[$val['EntGoodsNo']] = $val;
        }
        foreach ($data as $key => $val) {
            $gods = json_decode($val['goodsNo'], true);

            foreach ($gods as $k => $v) {
                if (!isset($goodsArray[$v['goodNo']])){
                    continue;
                }
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
                    ->setCellValue('AK' .$n,$paystatus[$val['PayStatus']])
                    ->setCellValue('AL' .$n,$elecstatus[$val['elecStatus']])
                    ->setCellValue('AM' .$n,$sbStatus[$val['sbStatus']]);
                $n += 1;
            }
        }

        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        unset($goodsArray, $data, $goodInfo);
        return $ExcelWrite;
    }


    /**
     * @deprecated 清单导出类型二
     * @param $input
     * @return \PHPExcel_Writer_IWriter
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function orderElistExproTypeTwo($input)
    {

        $where = null;
        $data = null;
        $time = date('Y-m-d H:i:s', time());

        if (isset($input['bill_num']) && $input['bill_num'] != '') {

            $batchRes = Db::name('customs_batch')->where('bill_num', $input['bill_num'])->field('batch_num')->select();
            $bid = null;
            foreach ($batchRes as $val) {
                $bid .= $val['batch_num'] . ',';
            }
            $where['batch_num'] = ['in', trim($bid, ',')];
            $where['elecStatus'] = ['eq',1];
            $billNum = $input['bill_num'];
            unset($batchRes, $bid);

        } elseif (isset($input['batch_num']) && $input['batch_num'] != '') {
            $where = ['batch_num' => $input['batch_num'],'elecStatus'=>1];
            $billNum = Db::name('customs_batch')->where('batch_num', $input['batch_num'])->field('bill_num')->find()['bill_num'];//提单编号
        } else {
            return '导出失败';
        }

        $data = Db::name('customs_elec_order_detail')
            ->where($where)
            ->select();

        if (empty($data)) {
            return '暂无数据';
        }
        $headlist = ['A1' => '电子运单号', 'B1' => '电子订单编号', 'C1' => '提运单号', 'D1' => '物流批次号', 'E1' => '运单创建时间', 'F1' => '电子运单状态', 'G1' => '路由状态', 'H1' => '出仓进境日期', 'I1' => '货物存放地', 'J1' => '件数', 'K1' => '毛重', 'L1' => '运费/率', 'M1' => '运费币制', 'N1' => '运费标志', 'O1' => '保费/率', 'P1' => '杂费率', 'Q1' => '运送货物总价', 'R1' => '收货人名称', 'S1' => '收货人地址', 'T1' => '收货人电话', 'U1' => '收件人省市区代码', 'V1' => '收货人所在国', 'W1' => '发货人名称', 'X1' => '发货人地址', 'Y1' => '发货人电话', 'Z1' => '发货人所在国', 'AA1' => '发货人省市区代码', 'AB1' => '商品信息', 'AC1' => '净重', 'AD1' => '备注',];
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
                if (!isset($goodsArray[$v['goodNo']])){
                    continue;
                }
                $PHPSheet->setCellValue("A" . $n, $val['WaybillNo'])
                    ->setCellValue("B" . $n, $val['EntOrderNo'])
                    ->setCellValue('C' . $n, $billNum)
                    ->setCellValue('D' . $n, '')
                    ->setCellValue('E' . $n, $time)
                    ->setCellValue('F' . $n, '')
                    ->setCellValue('G' . $n, '')
                    ->setCellValue('H' . $n, '')
                    ->setCellValue('I' . $n, '')
                    ->setCellValue('J' . $n, $v['num'])
                    ->setCellValue('K' . $n, $goodsArray[$v['goodNo']]['GrossWt'])
                    ->setCellValue('L' . $n, 0)
                    ->setCellValue('M' . $n, 0)
                    ->setCellValue('N' . $n, 0)
                    ->setCellValue('O' . $n, 0)
                    ->setCellValue('P' . $n, 0)
                    ->setCellValue('Q' . $n, $v['prices'])
                    ->setCellValue('R' . $n, $val['RecipientName'])
                    ->setCellValue('S' . $n, $val['RecipientAddr'])
                    ->setCellValue('T' . $n, $val['RecipientTel'])
                    ->setCellValue('U' . $n, $val['RecipientProvincesCode'])
                    ->setCellValue('V' . $n, $val['RecipientCountry'])
                    ->setCellValue('W' . $n, $val['senderName'])
                    ->setCellValue('X' . $n, $val['senderAddr'])
                    ->setCellValue('Y' . $n, $val['senderTel'])
                    ->setCellValue('Z' . $n, $val['senderCountry'])
                    ->setCellValue('AA' . $n, $val['senderProvincesCode'])
                    ->setCellValue('AB' . $n, $goodsArray[$v['goodNo']]['ShelfGName'])
                    ->setCellValue('AC' . $n, $goodsArray[$v['goodNo']]['NetWt'])
                    ->setCellValue('AD' . $n, $val['err_msg']);
                $n += 1;
            }
        }

        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        unset($goodsArray, $data, $goodInfo);
        return $ExcelWrite;
    }


    /**
     * 导出错误申报信息
     * @param $batch_num
     * @return \PHPExcel_Writer_IWriter|string
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */

    public function exproOrderDeErrMsg($batch_num){

        $Excel = new PHPExcel();
        $title1 = '格式验证失败';
        $Excel->setactiveSheetindex(0);
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        //$PHPSheet->setTitle($title1); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '批次');
        $PHPSheet->setCellValue('B1', '物流单号');
        $PHPSheet->setCellValue('C1', '订单号');
        $PHPSheet->setCellValue('D1', '错误信息');
        //$ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');

        $failTable = Db::name('decl_fail_decl_batch')->where('batch_num',$batch_num)->select();
        $n = 2;
        if (!empty($failTable)){
            foreach ($failTable as $value){
                $PHPSheet->setCellValue('A'.$n,$value['batch_num'])
                    ->setCellValue('B'.$n,$value['waybill_id'])
                    ->setCellValue('C'.$n,$value['order_id'])
                    ->setCellValue('D'.$n,$value['message']);
                $n +=1;
            }
        }
        //保存到第一个sheet
        $PHPSheet->setTitle($title1);


        // 创建sheet
        $Excel->createSheet();
        $title2 = '申报失败';
        $Excel->setactiveSheetIndex(1);
        $PHPSheet1 = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        //$PHPSheet1->setTitle(''); //给当前活动sheet设置名称
        $PHPSheet1->setCellValue('A1', '批次');
        $PHPSheet1->setCellValue('B1', '物流单号');
        $PHPSheet1->setCellValue('C1', '订单编号');
        $PHPSheet1->setCellValue('D1', '错误信息');
        $orderTable = Db::name('customs_elec_order_detail')->where("batch_num='".$batch_num."' and (elecStatus=2 or sbStatus=2)")->select();

        $l = 2;
        if (!empty($orderTable)){
            foreach ($orderTable as $value){
                $PHPSheet1->setCellValue('A'.$l,$value['batch_num'])
                    ->setCellValue('B'.$l,"\t".$value['WaybillNo']."\t")
                    ->setCellValue('C'.$l,$value['EntOrderNo'])
                    ->setCellValue('D'.$l,$value['err_msg']);
                $l +=1;
            }
        }
        $PHPSheet1->setTitle($title2);


        // 创建sheet
        $Excel->createSheet();
        $title3 = '支付身份验证失败';
        $Excel->setactiveSheetIndex(2);
        $PHPSheet2 = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        //$PHPSheet1->setTitle(''); //给当前活动sheet设置名称
        $PHPSheet2->setCellValue('A1', '批次编号');
        $PHPSheet2->setCellValue('B1', '用户姓名');
        $PHPSheet2->setCellValue('C1', '证件号');
        $PHPSheet2->setCellValue('D1', '物流单号');
        $PHPSheet2->setCellValue('E1', '错误信息');
        $orderTable = Db::name('foll_payment_userinfo_error')->where('batch_num',$batch_num)->select();

        $k = 2;
        if (!empty($orderTable)){
            foreach ($orderTable as $value){
                $PHPSheet2->setCellValue('A'.$k,$value['batch_num'])
                    ->setCellValue('B'.$k,$value['userName'])
                    ->setCellValue('C'.$k,"\t".$value['userId']."\t")
                    ->setCellValue('D'.$k,"\t".$value['WaybillNo']."\t")
                    ->setCellValue('E'.$k,$value['resultMsg']);
                $k +=1;
            }
        }
        $PHPSheet2->setTitle($title3);


        // 创建sheet
        $Excel->createSheet();
        $title4 = '支付身份验证失败';
        $Excel->setactiveSheetIndex(3);
        $PHPSheet3 = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        //$PHPSheet1->setTitle(''); //给当前活动sheet设置名称
        $PHPSheet3->setCellValue('A1', '批次编号');
        $PHPSheet3->setCellValue('B1', '用户姓名');
        $PHPSheet3->setCellValue('C1', '证件号');
        $PHPSheet3->setCellValue('D1', '物流单号');
        $PHPSheet3->setCellValue('E1', '错误信息');
        $orderTable = Db::name('customs_realname_error')->where('title',$batch_num)->select();

        $m = 2;
        if (!empty($orderTable)){
            foreach ($orderTable as $value){
                $PHPSheet3->setCellValue('A'.$m,$value['title'])
                    ->setCellValue('B'.$m,$value['userName'])
                    ->setCellValue('C'.$m,"\t".$value['userId']."\t")
                    ->setCellValue('D'.$m,"\t".$value['WaybillNo']."\t")
                    ->setCellValue('E'.$m,$value['resultMsg']);
                $m +=1;
            }
        }
        $PHPSheet3->setTitle($title4);


        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        return $ExcelWrite;
    }


    /**
     * 导出错误申报信息
     * @param $batch_num
     * @return \PHPExcel_Writer_IWriter|string
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     */
    public function exproOrderDeErrMsg11($batch_num){

        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('Sheet1'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '批次');
        $PHPSheet->setCellValue('B1', '物流单号');
        $PHPSheet->setCellValue('C1', '订单号');
        $PHPSheet->setCellValue('D1', '错误信息');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');

        $failTable = Db::name('decl_fail_decl_batch')->where('batch_num',$batch_num)->select();

        $orderTable = Db::name('customs_elec_order_detail')->where("batch_num='".$batch_num."' and (elecStatus=2 or sbStatus=2)")->select();


        /*if (empty($failTable)&&empty($orderTable)){
            return '暂无错误数据';
        }*/

        $n = 2;
        if (!empty($failTable)){
            foreach ($failTable as $value){
                $PHPSheet->setCellValue('A'.$n,$value['batch_num'])
                    ->setCellValue('B'.$n,$value['waybill_id'])
                    ->setCellValue('C'.$n,$value['order_id'])
                    ->setCellValue('D'.$n,$value['message']);
                $n +=1;
            }
        }


        if (!empty($orderTable)){
            foreach ($orderTable as $value){
                $PHPSheet->setCellValue('A'.$n,$value['batch_num'])
                    ->setCellValue('B'.$n,$value['WaybillNo'])
                    ->setCellValue('C'.$n,$value['EntOrderNo'])
                    ->setCellValue('D'.$n,$value['err_msg']);
            }
        }

        return $ExcelWrite;
    }


    /**
     * @param $batch_num
     * @return \PHPExcel_Writer_IWriter
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 导出税费
     * 2019-11-15
     */
    public function getExportax($bill_num){

        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('综合税费'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '提单编号');
        $PHPSheet->setCellValue('B1', '关税');
        $PHPSheet->setCellValue('C1', '增值税');
        $PHPSheet->setCellValue('D1', '消费税');
        $PHPSheet->setCellValue('E1', '综合税');
        $PHPSheet->setCellValue('F1', '总交易额');
        $PHPSheet->setCellValue('G1', '生成时间');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');

        $failTable = Db::name('customs_tax')->where('bill_num',$bill_num)->select();

        /*if (empty($failTable)&&empty($orderTable)){
            return '暂无错误数据';
        }*/

        $n = 2;
        if (!empty($failTable)) {
            foreach ($failTable as $value) {
                $PHPSheet->setCellValue('A'.$n,"\t" .$value['bill_num']."\t")
                    ->setCellValue('B'.$n,$value['tariff'])
                    ->setCellValue('C'.$n,$value['addtax'])
                    ->setCellValue('D'.$n,$value['excisetax'])
                    ->setCellValue('E'.$n,$value['comptax'])
                    ->setCellValue('F'.$n,$value['total'])
                    ->setCellValue('G'.$n,date('Y-m-d H:i:s',$value['add_time']));
                $n +=1;
            }
        }

        return $ExcelWrite;
    }



    /**
     *
     * @param $batch_num
     * @return \PHPExcel_Writer_IWriter
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 导出购买风险；
     * 2019-11-25
     */
    public function getOrderPurch($bill_num){

        $Excel = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('购买风险导出'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1', '批次编号');
        $PHPSheet->setCellValue('B1', '物流运单号');
        $PHPSheet->setCellValue('C1', '用户姓名');
        $PHPSheet->setCellValue('D1', '用户身份证');
        $PHPSheet->setCellValue('E1', '商品货号');
        $PHPSheet->setCellValue('F1', '风险类型');
        $PHPSheet->setCellValue('G1', '生成时间');
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');

        $failTable = Db::name('customs_elec_order_risk')->where('batch_num',$bill_num)->select();

        // 风险类型
        $type = [
            'ArtNo'     =>'同品多购',
            'monthMoney'=>'月超金额',
            'yearMoney' =>'年超金额',
            'WayNum'    =>'一单多品',
            'orderPurch'=>'一人多票',
        ];

        $n = 2;
        if (!empty($failTable)) {
            foreach ($failTable as $value) {
                $PHPSheet->setCellValue('A'.$n,"\t" .$value['batch_num']."\t")
                    ->setCellValue('B'.$n,"\t" .$value['waybillno']."\t")
                    ->setCellValue('C'.$n,$value['uname'])
                    ->setCellValue('D'.$n,"\t" .$value['idCard']."\t")
                    ->setCellValue('E'.$n,$value['goodNo'])
                    ->setCellValue('F'.$n,$type[$value['type']])
                    ->setCellValue('G'.$n,date('Y-m-d H:i:s',$value['add_time']));
                $n +=1;
            }
        }

        return $ExcelWrite;
    }


}
