<?php

namespace app\declares\controller;

use think\Controller;
use think\Request;
use think\Loader;
use think\Db;
use think\Log;

define("JAVA_DEBUG", true); //调试设置
define("JAVA_HOSTS", "127.0.0.1:8881");
define("JAVA_LOG_LEVEL", 2);

class OrderCustoms extends Controller
{

    protected $tmpOrder = [];

    protected $tmpOrderTwo = [];

    /*
  * 申报订单海关
  */
    public function sendElcOrder ( Request $request )
    {
        
        $detailAndHead = array();
        @file_put_contents('../runtime/log/gzeportlog/'.date('Ymd',time()).'_pay.txt',date('Y-m-d H:i:s',time()).'---'.json_encode($request->post())."\n",FILE_APPEND);
        $orderLogic = Loader::model("OrderCustoms", "logic");
        list($order_detail, $order_head) = $orderLogic->getOrderDetail($request->post('orderNo'));
        
        if (empty($order_detail)||empty($order_head)){
            return json(['code'=>-1]);
        }
        
        $detailAndHead = array_merge($order_detail, $order_head);
        unset($detailAndHead['Pay_time'],$order_detail['Pay_time']);
        $batch_num = $detailAndHead['batch_num'];
        if($request->post('chkMark')!=2){
            $uid = isset($detailAndHead['uid'])?$detailAndHead['uid']:null;
            $orderLogic->addErrCount($request->post('orderNo'),$batch_num,'errPay','batchErrPay',$request->post('failInfo'));
            $orderLogic->updateStatus($request->post('orderNo'),['PayStatus'=>2])->sendMail($uid,'支付失败','该笔订单号支付失败：'.$request->post('orderNo').',请从新提交,'.",错误内容：".$request->post('failInfo'));
            return json(['code'=>-1]);
        }
        
        
        $ginfo = json_decode($order_detail['goodsNo'],true)[0];
        $detailAndHead['PayStatus'] = 0;
        $detailAndHead['Qty']       = $ginfo['num'];
        $order_detail['PayStatus']  = 0;
        $order_detail['Qty']        = $ginfo['num'];
        unset($ginfo,$detailAndHead["expro_status"],$order_detail["expro_status"],$detailAndHead['batch_num'],$order_detail['batch_num'],$order_detail['err_msg'],$detailAndHead['err_msg']);
        
         $orderLogic->updateStatus($request->post('orderNo'),['PayStatus'=>0,'PayNo'=>$request->post('payTransactionNo'),'Pay_time'=>strtotime($request->post('completeTime'))])
                    ->AutogenerateRegUser($order_detail['OrderDocTel'],$detailAndHead['uid'],$detailAndHead['OrderDocName']);


        $encryResult = $this->generateOrderRsaEncryXml($order_head, $order_detail, $request->post('payTransactionNo'));
        
        
        if ( !$encryResult ) {
            $orderLogic->addErrCount($request->post('orderNo'),$batch_num,'errElec','batchErrELec','数据加签失败');
            $orderLogic->updateStatus($request->post('orderNo'), ['elecStatus' => 0])->sendMail($detailAndHead['uid'], '申报失败','数据加签失败，该笔订单号：' . $request->post('orderNo') . ',请从新提交');
        }
        
        
        $elecRequestResult = $this->sendCustoms($order_head, file_get_contents($encryResult));
        @file_put_contents('../runtime/log/gzeportlog/'.date('Ymd',time()).'_shenbao.txt',date('Y-m-d H:i:s',time())."|".$encryResult."|".$elecRequestResult."\n",FILE_APPEND);
        $elecRequestResult = json_decode($elecRequestResult,true);
        
        if(!$elecRequestResult['result']){
            $orderLogic->addErrCount($request->post('orderNo'),$batch_num,'errElec','batchErrELec',$elecRequestResult['description']);
            $orderLogic->sendMail($detailAndHead['uid'],'申报失败',$elecRequestResult['description'].':' . ',该笔订单号'.$request->post('orderNo').'请从新提交');
            return json(['code'=>-1]);
        }
        $orderLogic->generateOrder($detailAndHead,$request->post('payTransactionNo'));
        $orderLogic->addSuccessCount($batch_num);
        $orderLogic->updateStatus($request->post('orderNo'),['elecStatus'=>1]);
        return json(['code'=>0]);
    }

    /*
    * 申报海关
    */
    public function sendCustoms ( $parm, $data )
    {
        $url  = 'https://open.singlewindow.gz.cn/swcbes/client/declare/sendMessage.action?clientid=' . $parm['DeclEntNo'] . '&key=12345678&messageType=KJ881111';
//        $url = 'http://58.63.50.170:18080/cbt/client/declare/sendMessage.action?clientid='. $parm['DeclEntNo'] . '&key=12345678&messageType=KJ881111';
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_SSL_VERIFYHOST=>false,
            CURLOPT_HTTPHEADER => ["Content-Type:text/xml;charset=UTF-8", "Connection:Keep-Alive"],
            ]
        );
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);
        if ( $err ) {
            Log::write($err);
        } else {
            return $response;
        }
    }

    /*
     * 生成加签xml
     */

    public function generateOrderRsaEncryXml ( $order_heads, $order_details, $payTransactionNo )
    {
        $waybillCommpany = Db::name('foll_cross_border')->where('uid',$order_heads['uid'])->field('subject')->find();
        $waybillCommpany['subject'] = json_decode($waybillCommpany['subject'],true);
        $keys                                               = null;
        $order_heads['Request_head']                        = json_decode($order_heads['Request_head'], true);
        $request_head                                       = $order_heads['Request_head'];
        $order_details                                      = $this->formatOrderDetail($order_details);
        $order_details['OrderContent']['InvoiceAmount']     = null;
        $order_details['OrderContent']['InvoiceDate']       = null;
        $order_details['OrderPaymentRel']['PayNo']          = $payTransactionNo;
        $order_details['OrderPaymentRel']['PayEntName']     = '邦付宝支付科技有限公司';
        $order_details['OrderPaymentRel']['PayEntNo']       = 'C011111100394556';
        $order_details['OrderPaymentRel']['Notes']          = null;
        $order_details['OrderWaybillRel_two']['EHSEntNo']   = $waybillCommpany['subject']['gzeportCode'];
        $order_details['OrderWaybillRel_two']['EHSEntName'] = $waybillCommpany['subject']['enterprisename'];
        $order_details['OrderWaybillRel_two']['Notes']      = null;
        $dom                                                = new \DOMDocument('1.0', 'UTF-8');
        $interNation                                        = $dom->createElement('InternationalTrade');
        $head                                               = $dom->createElement('Head');
        foreach ($order_heads['Request_head'] as $key => $val) {
            $keys            = $dom->createElement($key);
            $keys->nodeValue = $val;
            $head->appendChild($keys);
        }
        unset($order_heads['Request_head'], $order_heads['uid'],$waybillCommpany);
        $interNation->appendChild($head);
        $Declaration = $dom->createElement('Declaration');
        $interNation->appendChild($Declaration);
        $orderHead = $dom->createElement('OrderHead');
        foreach ($order_heads as $key => $val) {
            if($key == 'DeclTime'){
                $val = date('YmdHis',strtotime($val));
            }
            $keys            = $dom->createElement($key);
            $keys->nodeValue = $val;
            $orderHead->appendChild($keys);
        }
        $Declaration->appendChild($orderHead);
        $orderList    = $dom->createElement('OrderList');
        $orderContent = $dom->createElement('OrderContent');
        $orderDetail  = $dom->createElement('OrderDetail');
        foreach ($order_details['OrderContent'] as $key => $val) {
            if($key ==  'OrderDate'){
                $val = date('YmdHis',strtotime($val));
            }else if($key == 'InvoiceDate'){
                $val =(new \DateTime(date('Y-m-d H:i:s',time())))->format('Y-m-d\TH:i:s\Z');
            }else if($key == 'InvoiceAmount'){
                $val = 0;
            }
            $keys            = $dom->createElement($key);
            $keys->nodeValue = $val;
            $orderDetail->appendChild($keys);
        }
   
     
        $goodsList                  = $dom->createElement('GoodsList');
        $goodsNo =  json_decode($order_details['OrderWaybillRel']['goodsNo'],true);
        foreach ($goodsNo as $k=> $value){
            $OrderGoodsList             = $dom->createElement('OrderGoodsList');
            $goodsInfo                  = $this->getOrderList($value['goodNo']);
            $goodsInfo['Seq']           = 0;
            $goodsInfo['Seq']           = $k+1;
            $goodsInfo['GoodsDescribe'] = '';
            $goodsInfo['Qty']           = $value['num'];
            $goodsInfo['Unit']          = $goodsInfo['GUnit'];
            $goodsInfo['Price']         = $value['price'];
            $goodsInfo['Total']         = $value['num'] * $value['price'];
            unset($goodsInfo['GUnit'],$goodsInfo['RegPrice']);
            $goodsInfo['CurrCode']      = 142;
            $goodsInfo['CusGoodsNo']    = $goodsInfo['HSCode'];
            foreach ($goodsInfo as $key => $val) {
                $keys            = $dom->createElement($key);
                $keys->nodeValue = htmlspecialchars($val);
                $OrderGoodsList->appendChild($keys);
                $goodsList->appendChild($OrderGoodsList);
            }
        }
      
        $goodsList->appendChild($OrderGoodsList);
        $orderDetail->appendChild($goodsList);
        $orderContent->appendChild($orderDetail);
        $OrderWaybillRel = $dom->createElement('OrderWaybillRel');
        foreach ($order_details['OrderWaybillRel_two'] as $key => $val) {
            $keys            = $dom->createElement($key);
            $keys->nodeValue = $val;
            $OrderWaybillRel->appendChild($keys);
        }
        $orderContent->appendChild($OrderWaybillRel);
        $OrderPaymentRel = $dom->createElement('OrderPaymentRel');
        foreach ($order_details['OrderPaymentRel'] as $key => $val) {
            $keys            = $dom->createElement($key);
            $keys->nodeValue = $val;
            $OrderPaymentRel->appendChild($keys);
        }
        $orderContent->appendChild($OrderPaymentRel);
        $orderList->appendChild($orderContent);
        $Declaration->appendChild($orderList);
        $interNation->appendChild($Declaration);
        $dom->appendChild($interNation);
        $xmlString = $dom->saveXML();
        $xmlString = base64_encode($xmlString);
        include('../javaBridge/Java.inc');
        java_require('../javaBridge/XmlDigitalSignatureGenerator.jar');
        try {
            $GzeportTransfer_origin = file_get_contents('./uploads/encryxml/GzeportTransfer_origin.xml');
            $gzeportTransfer_origin = simplexml_load_string($GzeportTransfer_origin);
            unset($GzeportTransfer_origin);
        } catch (\Exception $exception) {
            Log::write($exception->getMessage());
        }
        $gzeportTransfer_origin->Data                         = $xmlString;
        $gzeportTransfer_origin->Head->MessageID              = $request_head['MessageID'];
        $gzeportTransfer_origin->Head->MessageType            = $request_head['MessageType'];
        $gzeportTransfer_origin->Head->Sender                 = $request_head['Sender'];
        $gzeportTransfer_origin->Head->Receivers->Receiver[0] = $request_head['Receiver'];
        $gzeportTransfer_origin->Head->Receivers->Receiver[1] = $request_head['Receiver'];
        $gzeportTransfer_origin->Head->SendTime               = $request_head['SendTime'];
        $gzeportTransfer_origin->Head->Version                = '3.0';
        $originFIlePath                                       = getcwd() . '/uploads/encryxml/' . $request_head['MessageID'] . '_origin.xml';
        $signedXmlFilePath                                    = getcwd() . '/uploads/encryxml/' . $request_head['MessageID'] . '_sign.xml';
        $privateKeyFilePath                                   = getcwd() . '/../key/privatekey.key';
        $publicKeyFilePath                                    = getcwd() . '/../key/publickey.key';
        $gzeportTransfer_origin->saveXML($originFIlePath);
        try {
            $xmlSig = new \java("com.ddlab.rnd.xml.digsig.XmlDigitalSignatureGenerator");
            $xmlSig->generateXMLDigitalSignature($originFIlePath, $signedXmlFilePath, $privateKeyFilePath, $publicKeyFilePath);
            unset($xmlSig, $xmlString, $dom);
            @unlink($originFIlePath);
            return $signedXmlFilePath;
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            return false;
        }
    }

    protected function formatOrderDetail ( $data )
    {
        $isWayKey  = ['id', 'head_id', 'goodsNo', 'GoodsName', 'elecStatus', 'wayStatus', 'InvoiceAmount', 'InvoiceDate', 'Qty', 'insuredFree', 'senderName', 'senderTel', 'senderAddr', 'senderCountry', 'senderProvincesCode', 'weight', 'grossWeight', 'packageType', 'unit', 'GoodsStyle', 'Province', 'city', 'county', 'BarCode', 'create_at', 'OriginCountry', 'Price'];
        $isWayKey2 = ['EHSEntNo', 'EHSEntName', 'WaybillNo'];
        $isPayKey  = ['PayEntNo', 'PayEntName', 'PayNo'];
        foreach ($data as $k => $v) {
            if ( in_array($k, $isWayKey) ) {
                $data['OrderWaybillRel'][$k] = $v;
                unset($data[$k]);
            } else if ( in_array($k, $isWayKey2) ) {
                $data['OrderWaybillRel_two'][$k] = $v;
                unset($data[$k]);
            } else if ( in_array($k, $isPayKey) ) {
                $data['OrderPaymentRel'][$k] = $v;
                unset($data[$k]);
            } else {
                $data['OrderContent'][$k] = $v;
                unset($data[$k]);
            }
        }
        return $data;
    }

    protected function getOrderList ( $gid )
    {
        return Db::name('foll_goodsreglist')->where('EntGoodsNo', $gid)->field(['Seq', 'EntGoodsNo', 'CIQGoodsNo', 'CusGoodsNo', 'HSCode', 'GoodsName', 'GoodsStyle', 'OriginCountry', 'BarCode', 'Brand', 'Notes','GUnit','RegPrice'])->find();
    }
}