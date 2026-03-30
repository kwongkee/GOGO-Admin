<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Des3;
use think\Db;
use think\Log;
use think\Curl;

class RequestDevApi extends Controller
{
    
    /*
     * 发送停车卡绑定解绑状态
     */
    protected $n = 0;
    
    public function pullParkingCardBindStatusApi ( Request $request )
    {
    
        
        $userInfo = Db::name('parking_authorize')->where('credit_accout', $request->post('credit_accout'))->find();
        $apiUrl   = Db::name("parking_api_url")->where('uniacid', $userInfo['uniacid'])->where('alias', 'ParkingCardBind')->find();
     
//        if ($userInfo['auth_status']==0){
//            Db::name('parking_authorize')->where('id',$userInfo['id'])->update(['credit_accout'=>null]);
//        }
        $key     = mb_convert_encoding($apiUrl['key'], 'GB2312', 'UTF-8');
        $iv      = mb_convert_encoding(date("Ymd", time()), 'GB2312', 'UTF-8');
        $data    = json_encode(["UserName" => $userInfo['name'], "CardNo" => $request->post("credit_accout"), "UserState" => $userInfo['auth_status']]);
        $data    = mb_convert_encoding($data, 'GB2312', 'UTF-8');
        $des     = new Des3($key, $iv);
        $encry   = $des->encrypt($data);
        $header  = ['Content-Type' => 'application/json;charset=utf-8', 'user' => $apiUrl['user'], 'pwd' => $apiUrl['passwd']];
        $ReqData = json_encode(["data" => $encry]);
        $curl    = new Curl();
        $curl->setHeader("Content-type", "application/json");
        $curl->setHeader("user", $apiUrl['user']);
        $curl->setHeader("pwd", $apiUrl['passwd']);
        $curl->post("http://" . $apiUrl['url'], $ReqData);
        if ($userInfo['auth_status'] == 1){
            @file_put_contents('../runtime/log/result_'.date('Ym',time()).'.log', '绑定卡:'.$request->post('credit_accout')."--".$curl->response  . date('Y-m-d H:i', time()) . "\n", FILE_APPEND);
        }else{
            @file_put_contents('../runtime/log/result_'.date('Ym',time()).'.log', '解绑卡:'.$request->post('credit_accout')."--".$curl->response  . date('Y-m-d H:i', time()) . "\n", FILE_APPEND);
        }
        $res = json_decode($curl->response, true);
        if ( empty($res) ) {
            return json(['resCode' => -1]);
        }
        if ( isset($res['resCode']) ) {
            if ( $res['resCode'] == 0 ) {
                return json(['resCode' => 0]);
            }
        }
        unset($encry, $ReqData, $res);
        return json(['resCode' => -1]);
    }
    
    /*
     * 发送支付状态
     */
    public function pullOnlinePayStatusApi ( Request $request )
    {
        file_put_contents('../runtime/log/result_'.date('Ym',time()).'.log', '接受支付参数-' . json_encode($request->post()) . "\n", FILE_APPEND);
        $this->encryData($request->post("ordersn"), $request->post("type"));
        return json(['error' => 0]);
    }
    
    protected function encryData ( $oid, $type )
    {
        try {
            $orderInfo     = Db::name("foll_order")->where("ordersn", $oid)->field(['uniacid', 'pay_account', 'pay_type'])->find();
            $parkOrderInfo = Db::name("parking_order")->where("ordersn", $oid)->field(['number', 'duration', 'starttime', 'endtime', 'charge_type','devs_ordersn'])->find();
            $PlaceNum      = Db::name("parking_space")->where("park_code", $parkOrderInfo['number'])->field("numbers")->find();
            $apiUrl        = Db::name("parking_api_url")->where(['uniacid' => $orderInfo['uniacid'], 'alias' => 'OnlinePayPush'])->find();
//            Db::name('parking_order')->where('ordersn', $oid)->update(['devs_ordersn' => $parkOrderInfo['devs_ordersn']]);
        } catch (\Exception $e) {
//            file_put_contents('../runtime/log/result.txt', $e->getMessage() . "支付\n", FILE_APPEND);
            return false;
        }
        $key      = mb_convert_encoding($apiUrl['key'], 'GB2312', 'UTF-8');
        $iv       = mb_convert_encoding(date("Ymd", time()), 'GB2312', 'UTF-8');
        $payMoney = $orderInfo['pay_account'] * 100;
        $data     = json_encode(["Out_Trade_No" => $parkOrderInfo['devs_ordersn'], "PlaceNum" => $PlaceNum['numbers'], "PayMoney" => $payMoney, "PayMinutes" => $parkOrderInfo['duration'], "PayType" => $parkOrderInfo['charge_type'], "orderType" => $orderInfo['pay_type'], "PayBeginTime" => $parkOrderInfo['starttime'], "PayEndTime" => $parkOrderInfo['endtime']]);
        $data     = mb_convert_encoding($data, 'GB2312', 'UTF-8');
        $des      = new Des3($key, $iv);
        $ReqData  = json_encode(["data" => $des->encrypt($data)]);
        $resp     = $this->pushPayInfo($apiUrl, $ReqData);
        @file_put_contents('../runtime/log/result_'.date('Ym',time()).'.txt', json_encode($resp) . '|' . date('Y-m-d H:i', time()).'|订单：'.$oid.'|设备订单|'.$parkOrderInfo['devs_ordersn'].'|泊位编号:'.$PlaceNum['numbers'] . "\n", FILE_APPEND);
        if ( $resp['resCode'] == 0 ) return false;
        return true;
    }
    
    /**
     * @param $apiUrl
     * @param $ReqData
     * @return array
     */
    protected function pushPayInfo ( $apiUrl, $ReqData )
    {
        $curl = new Curl();
        $curl->setHeader("Content-type", "application/json");
        $curl->setHeader("user", $apiUrl['user']);
        $curl->setHeader("pwd", $apiUrl['passwd']);
        $curl->post("http://" . $apiUrl['url'], $ReqData);
        $resp = json_decode($curl->response, true);
        return $resp;
    }
    
    /*
     * 预付费通知亮灯
     */
    public function lights_up ( Request $request )
    {
        $ordersn = Db::name('parking_order')->where('ordersn', $request->post('ordersn'))->where('charge_status', 0)->find();
        if ( empty($ordersn) ) {
            exit();
        }
        $uniacid = Db::name('foll_order')->where('ordersn', $request->post('ordersn'))->field('uniacid')->find();
        $space   = Db::name('parking_space')->where('park_code', $ordersn['number'])->field('numbers')->find();
        $apiUrl  = Db::name("parking_api_url")->where(['uniacid' => $uniacid['uniacid'], 'alias' => 'WXRegisterPush'])->find();
        $key     = mb_convert_encoding($apiUrl['key'], 'GB2312', 'UTF-8');
        $iv      = mb_convert_encoding(date("Ymd", time()), 'GB2312', 'UTF-8');
        $data    = json_encode(["Order_No" => $ordersn['devs_ordersn'], "PlaceNum" => $space['numbers'], "InTime" => date('Y-m-d H:i:s', $ordersn['starttime'])]);
        $data    = mb_convert_encoding($data, 'GB2312', 'UTF-8');
        $des     = new Des3($key, $iv);
        $ReqData = json_encode(["data" => $des->encrypt($data)]);
        $resp    = $this->pushPayInfo($apiUrl, $ReqData);
        file_put_contents('../runtime/log/result_'.date('Ym',time()).'.log', json_encode($resp) . '-' . date('Y-m-d H:i', time()) . "\n", FILE_APPEND);
        return json(['code' => 200]);
    }
}
