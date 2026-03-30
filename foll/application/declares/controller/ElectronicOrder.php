<?php

namespace app\declares\controller;

use app\declares\controller;
use http\Url;
use think\Request;
use think\loader;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;

class ElectronicOrder extends BaseAdmin
{
    
    public function orderIndex ( Request $request )
    {
        return view('electronicorder/index');
    }
    
    
    public function getList ( Request $request )
    {
        $page  = ((int)$request->get('page') - 1) * (int)$request->get('limit');
        $limit = $request->get('limit');
        $total = Db::name('foll_elec_order_head')->where('uid', Session('admin')['id'])->count('id');
        $data  = Db::name('foll_elec_order_head')->where('uid', Session('admin')['id'])->order('id', 'desc')->limit($page, $limit)->select();
        return json(['code' => 0, 'msg' => '', 'count' => $total, 'data' => $data]);
    }
    
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function uploadOrderData ( Request $request )
    {
        $electronicOrderServerLogic = loader::model('ElectronicOrderServer', 'logic');
        list($status, $message) = $electronicOrderServerLogic->withFile($request->file('file'), $this->admin);
        
        if ( !$status ) {
            return json(['code' => -1, 'msg' => $message]);
        }
        
        $payInfo                = $message['payInfo'];
        $payInfo['orderAmount'] = (int)($payInfo['orderAmount'] * 100);
        unset($message['payInfo']);
        $payResult = $this->payDetail($message, $payInfo);
        
        
        if ( $payResult['code'] == 0 ) {
            return json(['code' => -1, 'msg' => $payResult['msg']['resultMsg'] . ':' . $payResult['msg']['failInfo']]);
        }
        $electronicOrderServerLogic->saveDetailInfo($message, $payInfo['orderId']);
        return json(['code' => 0, 'msg' => $payResult['msg']['resultMsg']]);
    }
    
    
    /**
     * @param Request $request
     * @return mixed
     */
    public function getOrderAllInfo ( Request $request )
    {
        $payStatus  = ['已支付', '未付款', '失败'];
        $elecStatus = ['未申报', '已申报', '申报完成', '已转发', '入库失败'];
        $data       = Db::name("foll_elec_order_detail")->where('head_id', $request->get('id'))->select();
        foreach ($data as &$val) {
            $val['goodsNo'] = json_decode($val['goodsNo'], true);
            $g              = null;
            foreach ($val['goodsNo'] as $value) {
                $g .= $value['goodNo'] . ',';
            }
            $val['goodsNo'] = $g;
        }
        return view('electronicorder/info', ['data' => $data, 'payStatus' => $payStatus, 'elecStatus' => $elecStatus]);
    }
    
    /*
     * rediect
     */
    public function pushDeclarationOrder ( Request $request )
    {
        if ( $request->isGet() ) {
            return view('electronicorder/decl_order');
        }
        if ( $request->isPost() ) {
            $this->loadWithFileAndRequest($request);
        }
    }
    
    /*
     * 请求支付企业
     * return true/false
     */
    protected function loadWithFileAndRequest ( $request )
    {
        $withResult = null;
        $errOrder   = null;
        
        if ( empty($request->post('enterprisename')) || empty($request->post('gzeportCode')) ) {
            $this->error('物流企业信息必填', Url('electronicorder/pushDeclarationOrder'));
        }
        if ( empty($request->post('OrderDate')) ) {
            $this->error('请填写订单时间', Url('electronicorder/pushDeclarationOrder'));
        }
        if($request->post('OrderDate')>date("Ymd",time())){
            $this->error('订单日期超出今天', Url('electronicorder/pushDeclarationOrder'));
        }
        if(empty($request->post('DeclEntName'))||empty($request->post('DeclEntNo'))){
            $this->error('请填写企业名称或编号', Url('electronicorder/pushDeclarationOrder'));
        }
        
        if (empty($request->post('batch_num'))){
            $this->error('请填写批次编号', Url('electronicorder/pushDeclarationOrder'));
        }
        
        if (empty($request->file('file'))){
            $this->error('请选择文件', Url('electronicorder/pushDeclarationOrder'));
        }
        
        $isBatchNum            = Db::name('foll_elec_count')->where('batch_num',$request->post('batch_num'))->field('id')->find();
        
        if (!empty($isBatchNum)){
            $this->error('批次编号重复', Url('electronicorder/pushDeclarationOrder'));
        }
        
        $getElecOrderLogic     = loader::model("GetElecOrder", "logic");
        $sub                   = Db::name('foll_cross_border')->where('uid', Session('admin.id'))->field('subject')->find()['subject'];
        
        if (!empty($sub)){
            $sub                   = json_decode($sub, true);
        }
        $sub['enterprisename'] = $request->post('enterprisename');
        $sub['gzeportCode']    = $request->post('gzeportCode');
        Db::name('foll_cross_border')->where('uid', Session('admin.id'))->update(['subject' => json_encode($sub)]);
        
        try{
            $withResult = $getElecOrderLogic->inits($request->file('file'), $request->post())->getDatas();
        }catch (\Exception $exception){
            $this->error($exception->getMessage(), Url('electronicorder/pushDeclarationOrder'));
        }
        $this->success('提交成功', Url('electronicorder/pushDeclarationOrder'));
    }
    
    /*
     * 更新进库
     */
//    protected function saveElecOrderData ( $data, $headId )
//    {
//        Db::startTrans();
//        try {
//            $data['head_id']   = $headId;
//            $data['create_at'] = time();
//            Db::name('foll_elec_order_detail')->insert($data);
//            Db::commit();
//        } catch (\Exception $e) {
//            Db::rollback();
//            throw new \Exception($e->getMessage());
//        }
//    }
    
    /*
     * 更新订单头进库
     */
    
    protected function saveElecOrderHead ( $head, $req_head )
    {
        $head['request_head'] = json_encode($req_head);
        $head['uid']          = Session('admin')['id'];
        $id                   = Db::name('foll_elec_order_head')->insertGetId($head);
        return $id;
    }
    
    public function exportWayExcel ( Request $request )
    {
        $where = null;
        $fileName =null;
        $data  = [];
        if (empty($request->get('batch_num'))){
            $this->error('请填写批号', Url('electronicorder/order_index'));
        }
        switch ($request->get('type')){
            case 1:
                $fileName = md5(date('YmdHis', time()) . mt_rand(1111, 9999)).'error';
                $where = ['a.uid'=>Session('admin.id'),'b.PayStatus'=>2,'b.elecStatus'=>4,'b.batch_num'=>$request->get('batch_num')];
                break;
            case 2:
                $fileName = md5(date('YmdHis', time()) . mt_rand(1111, 9999));
                $where = ['a.uid'=>Session('admin.id'),'b.PayStatus'=>0,'b.elecStatus'=>2,'b.batch_num'=>$request->get('batch_num')];
                break;
            default:
                $this->error('错误', Url('electronicorder/order_index'));
                break;
        }
        $data = $this->fetchOrderDateil($where);
        if (empty($data)){
            $this->error('暂无数据', Url('electronicorder/order_index'));
        }
       
        
        if ( empty($data) ) {
            $this->error('请等待申报完成', Url('electronicorder/order_index'));
        }

        $headlist = $this->getHeadList();
        $email    = Db::name('foll_cross_border')->where('uid', Session('admin')['id'])->field('subject')->find();
        $email    = json_decode($email['subject'], true)['conmpanyemail'];
        
        if ( empty($email) ) {
            $this->error('请前往企业信息填写邮箱！', Url('electronicorder/order_index'));
        }
        
        $Excel    = new PHPExcel();
        $PHPSheet = $Excel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('Sheet1'); //给当前活动sheet设置名称
        foreach ($headlist as $key => $val) {
            $PHPSheet->setCellValue($key, $val);
        }
        $n = 2;
        $gid =null;//商品号
        $goodsArray = [];//新的商品数组
        foreach ($data as $key => $val) {
            $Gno = json_decode($val['goodsNo'],true);
            foreach ($Gno as $value){
                $gid .= $value['goodNo'].',';
            }
        }

        $goodInfo = Db::name('foll_goodsreglist')->where('EntGoodsNo','in',trim($gid,','))->select();


        foreach ($goodInfo as $val){
            $goodsArray[$val['EntGoodsNo']]=$val;
        }
        foreach ($data as $key=>$val){
            $gods = json_decode($val['goodsNo'], true);
            foreach ($gods as $k => $v) {
                $PHPSheet->setCellValue("A" . $n, $val['EntOrderNo'])
                    ->setCellValue("B" . $n, $val['OrderDate'])
                    ->setCellValue("C" . $n, "\t" . $val['WaybillNo']."\t")
                    ->setCellValue("D" . $n, $v['goodNo'])
                    ->setCellValue("E" . $n, $goodsArray[$v['goodNo']]['BarCode'])
                    ->setCellValue("F" . $n, $goodsArray[$v['goodNo']]['GoodsName'])
                    ->setCellValue("G" . $n, $goodsArray[$v['goodNo']]['GoodsStyle'])
                    ->setCellValue("H" . $n, $goodsArray[$v['goodNo']]['OriginCountry'])
                    ->setCellValue("I" . $n, $val['packageType'])
                    ->setCellValue("J" . $n, $goodsArray[$v['goodNo']]['GUnit'])
                    ->setCellValue("K" . $n, $v['num'])
                    ->setCellValue("L" . $n, $v['price'])
                    ->setCellValue("M" . $n, sprintf("%2.f",$v['price'])*$v['num'])
                    ->setCellValue("N" . $n, $val['Tax'])
                    ->setCellValue("O" . $n, $val['OrderGoodTotalCurr'])
                    ->setCellValue("P" . $n, $val['Freight'])
                    ->setCellValue("Q" . $n, $val['insuredFree'])
                    ->setCellValue("R" . $n, $val['OrderDocName'])
                    ->setCellValue("S" . $n, $val['OrderDocName'])
                    ->setCellValue("T" . $n, "\t".$val['OrderDocId']."\t")
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
                    ->setCellValue("AG" . $n,"\t". $val['PayNo']."\t")
                    ->setCellValue("AH" . $n, $val['OrderDate'])
                    ->setCellValue("AI" . $n, $k+1);
                $n += 1;
            }
        }
       
        $ExcelWrite = PHPExcel_IOFactory::createWriter($Excel, 'Excel2007');
        $path       = './uploads/excel/' . $fileName . '.xlsx';
        $ExcelWrite->save($path);
        $this->sendMail($path, $email);
//        $this->sendMail($path, 'kali20@126.com');
        foreach ($data as $val){
            Db::name('foll_elec_order_detail')->where('id',$val['id'])->update(['expro_status'=>1]);
        }
        unset($goodsArray,$data,$goodInfo);
//        $this->sendMail($path,'kali20@126.com');
        $this->success('发送成功，请查收', Url('electronicorder/order_index'));
    }
    
    protected function sendMail ( $path, $email )
    {
        $name    = '系统';
        $subject = '物流报文';
        $content = '请查收';
        $status  = send_mail($email, $name, $subject, $content, ['0' => $path]);
        if ( $status ) {
            unlink($path);
        }
    }
    
    protected function getHeadList ()
    {
        $list = ['A1' => '订单编号', 'B1' => '订单生成时间', 'C1' => '物流运单号码', 'D1' => '商品货号', 'E1' => '条形码', 'F1' => '商品名称', 'G1' => '型号规格', 'H1' => '原产国', 'I1' => '包装类型', 'J1' => '单位', 'K1' => '数量', 'L1' => '单价', 'M1' => '货款金额', 'N1' => '税费', 'O1' => '币制', 'P1' => '运费', 'Q1' => '保价费', 'R1' => '订购人用户名', 'S1' => '订购人姓名', 'T1' => '订购人证件号码', 'U1' => '净重', 'V1' => '毛重', 'W1' => '收货人电话', 'X1' => '收货人省', 'Y1' => '收货人城市', 'Z1' => '收货人区/县', 'AA1' => '收货人地址', 'AB1' => '发货人姓名', 'AC1' => '发货人电话', 'AD1' => '发货人地址', 'AE1' => '发货人所在国', 'AF1' => '发货人省市区代码', 'AG1' => '支付交易号', 'AH1' => '支付时间','AI1'=>'商品序号'];
        return $list;
    }
    
    /**
     * @return mixed
     */
    protected function fetchOrderDateil ($where)
    {
        $data = Db::name('foll_elec_order_head')
            ->alias('a')
            ->join('foll_elec_order_detail b','a.id=b.head_id')
            ->field(['b.id', 'b.EntOrderNo', 'b.OrderDate', 'b.WaybillNo', 'b.goodsNo', 'b.BarCode', 'b.GoodsName', 'b.GoodsStyle', 'b.OriginCountry', 'b.packageType', 'b.unit', 'b.Qty', 'b.Price', 'b.OrderGoodTotal', 'b.Tax', 'b.OrderGoodTotalCurr', 'b.Freight', 'b.insuredFree', 'b.OrderDocAcount', 'b.OrderDocName', 'b.OrderDocId', 'b.weight', 'b.grossWeight', 'b.OrderDocTel', 'b.Province', 'b.city', 'b.county', 'b.RecipientAddr', 'b.senderName', 'b.senderTel', 'b.senderAddr', 'b.senderCountry', 'b.senderProvincesCode', 'b.PayNo', 'b.Pay_time'])
            ->where($where)
            ->select();
        return $data;
    }
}
