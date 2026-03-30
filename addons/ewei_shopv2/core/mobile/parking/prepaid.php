<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}
class Prepaid_EweiShopV2Page extends mobilePage{
    public function __construct ()
    {
        parent::__construct();
        load()->classs("curl");
        load()->classs('des');
    }
    public function main(){
        global $_W;
        global $_GPC;
        $num   = $_GPC['num'];
        $ms    = $_GPC['ms'];
        $title = '预付费付款';
        $times = time();
        $payTime = strtotime('2018-10-01 00:01:00');
        $ChargingTime    = json_decode($_COOKIE['Prepayment'],true);
        if (empty($ChargingTime)){
            message('未知错误');
        }
        
        if ($times<=$payTime){
            $this->message('将于'.date('Y-m-d H:i',$payTime).'开放功能',mobileUrl('parking/info').'&num='.$_GPC['num'],'success');
        }
        
        $total           = (int)$_GPC['ms']/(int)$ChargingTime['minute']; //获取时间计算收款金额
        $total           = $total*floatval($ChargingTime['price']);
        include $this->template('parking/periodPay');
    }
    public function prepaidPay()
    {
        global $_GPC;
        global $_W;
        load()->classs("redis");
        $total=null;
        $type = null;
        $time  = time();
        
        if(!empty($this->isOrder($_W['openid']))){
            show_json(460,'已存在订单');
        }
        
        switch ($_GPC['type']) {
            case 'wechat':
                $type = 'wechat';
                $devOrderId = 'wp'.date('ymdHi') . substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                break;
            case 'alipay':
                $type = 'alipay';
                $devOrderId ='al'.date('ymdHi') . substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
                break;
            default:
                show_json(460, '支付错误');
        }
        
        
        $isParkCode         = pdo_get("parking_space",array('park_code'=>$_GPC['num']));
        $RequestResult = $this->getTimeFromDevice($isParkCode['numbers']);//请求设备获取入场时间
        $RequestResult['msg']['data'] = json_decode($RequestResult['msg']['data'], true);
        if ( $RequestResult['error'] ) {
            show_json(460,'请求异常');
        }
        if ( $RequestResult['msg']['resCode'] != 0 || empty($RequestResult['msg']) || null == $RequestResult['msg']['data'][0]['InTime']) {
            show_json(460,'异常时间');
        }
        $Stime              = strtotime($RequestResult['msg']['data'][0]['InTime']);//请求设备获取入场时间
        $ChargingTime       = json_decode($_COOKIE['Prepayment'],true);
        if (empty($ChargingTime)){
            show_json(460, '错误支付');
        }
        setcookie('Prepayment',null);
        $addressData        = pdo_get("parking_position",array('id'=>$isParkCode['pid']));
        $parkOrder          = pdo_get("parking_order",array('number'=>$_GPC['num'],'charge_status'=>0));
        $follOrder          = pdo_get("foll_order",array('ordersn'=>$parkOrder['ordersn']),array('pay_account','total','pay_status','id'));
        $total           = (int)$_GPC['ms']/(int)$ChargingTime['minute']; //获取时间计算收款金额
        $total           = $total*floatval($ChargingTime['price']);
        $payAccounts     = $total;
        $millisecond = round(explode(" ", microtime())[0]*1000);
        $order_id = 'G99198'  . date('Ymd', time()) .str_pad($millisecond,3,'0',STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111,999);
//        $isMonthCard = empty(m('parking')->verifMonthCard()) ? 0 : 1;
        $carno           = pdo_get("parking_authorize",array("openid"=>$_W['openid']),array("CarNo"));
        $disRes  = pdo_get('parking_operate',['uniacid'=>$_W['uniacid'],'status'=>2],['discount','startDate','endDate']);
//        $testers = pdo_get('Testers',['user_id'=>$_W['openid']]);
//        if (empty($testers)){
            if (!empty($disRes)&&$payAccounts>0){
                if (($time >= $disRes['startDate']) && ($time <= $disRes['endDate'])){
                    $payAccounts =  $payAccounts*($disRes['discount']/10);
                }
            }
//        }
        
        $OrderData = [
            'ordersn'    =>$order_id,//订单编号
            'user_id'    =>$_W['openid'],
            'uniacid'    =>$_W['uniacid'],
            'business_id'=>$_W['uniacid'],
            'application'=>'parking',
            'goods_name' => '路内停车',
            'goods_price'=>0.00,
            'pay_type'   =>$type,
            'pay_status' =>$payAccounts==0?1:0,
            'pay_time'   => $time,
            'path_oid'   =>0,
            'total'      =>$total,
            'pay_account'=>$payAccounts,
            'body'       =>'停车服务',
            'business_name' => $_W['account']['name'],
            'create_time'=>time(),
            'nickname'   => $_W['fans']['nickname'],
            'address'   =>$addressData['Province'].$addressData['City'].$addressData['Area'].$addressData['Town'].$addressData['Committee'].$addressData['Road'].$addressData['Road_num'].'号'
        ];
        //生成订单
        pdo_insert("foll_order",$OrderData);
        pdo_insert("parking_order",[
            'ordersn'    =>$order_id,
            'carNo'      =>$carno['CarNo'],
            'number'     =>$_GPC['num'],
            'starttime'  =>$Stime,
            'endtime'    =>intval($Stime)+(intval($_GPC['ms'])*60),
            'moncard'    => 0,
            'duration'   =>$_GPC['ms'],
            'status'     =>'已停车',
            'charge_type'=>0,
            'charge_status'=>0,
            'OthSeq'     =>date('YmdHis',time()).rand(111111,999999),
            'devs_ordersn'=>$devOrderId,
            'card_time'  =>0
        ]);
        Rediss::getInstance()->incr('wxConfirm');
        Rediss::getInstance()->hSet("userData", $_W['openid'], json_encode(['stime' => $Stime, 'etime' => intval($Stime) + (intval($_GPC['ms']) * 60), 'openid' => $_W['openid'], 'Parnum' => $_GPC['num']]));
        //        $this->WXRegisterPush($devOrderId, $isParkCode['numbers'], $RequestResult['msg']['data'][0]['InTime']);
        
        if ($payAccounts==0){
            $this->sendPayInfoDev($order_id,$type);
            $this->sendPaySuccTep($_W['openid'],$_W['uniacid'],$_GPC['ms'],$total,$payAccounts);
            show_json(400, '缴费成功');
        }
        
        $payurl   = 'http://shop.gogo198.cn/payment/wechat/Tgpay.php';
        $postdata = ['token' => $type, 'ordersn' => $order_id];
        $res = json_decode($this->request_pay($payurl, $postdata), true);
        if ( $res['msg'] == 'success' ) {

            if($type == 'wechat') {

                show_json(200, $res['pay_info']);

            } else {
                show_json(200, $res['payurl']);
            }

        } else if ( $res['msg'] == 'error' ) {

            show_json(400, $res['info']);

        } else {

            pdo_delete('parking_order', ['ordersn' => $order_id]);
            pdo_delete('foll_order', ['ordersn' => $order_id]);
            show_json(460, '支付异常');

        }
    }
    
    protected function request_pay($url,$post_data) {
       $curl = new Curl();
       $curl->post($url,$post_data);
       @file_put_contents('../data/logs/'.date('Ymd',time()).'_s.txt', '预付费支付:'.date("Y-m-d H:i:s",time())."---".$curl->response ."\n", FILE_APPEND);
       return $curl->response;
    }

    public function getTimeFromDevice ( $code )
    {
        global $_GPC;
        global $_W;
        $timeFormat = date('Ymd', time());
        $apiUrl = pdo_get("parking_api_url", array('uniacid' => $_W['uniacid'], 'alias' => 'GetParkingInfo'));
        $key = mb_convert_encoding($apiUrl['key'], 'GB2312', 'UTF-8');
        $iv = mb_convert_encoding($timeFormat, 'GB2312', 'UTF-8');
        $RequestDatas = $this->encryRequestData(['PlaceNum' => $code], $apiUrl);
        $resData = $this->curl($apiUrl, $RequestDatas);
        $ResData = json_decode($resData, true);
        $str = Des3::init($key, $iv)->decrypt($ResData['data']);
        $str = mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        unset($ResData['data']);
        $ResData['data'] = $str;
        if ( $curl->error_code > 0 ) {
            return ['error' => true, 'msg' => $curl->error_message];
        }
        return ['error' => false, 'msg' => $ResData];
    }
    /**
     * @param data
     * @param $apiUrl
     *
     * @return mixed
     */
    protected function encryRequestData ( $data, $apiUrls )
    {
        $timeFormat = date('Ymd', time());
        $key = mb_convert_encoding($apiUrls['key'], 'GB2312', 'UTF-8');
        $iv = mb_convert_encoding($timeFormat, 'GB2312', 'UTF-8');
        $data = json_encode($data);
        $data = mb_convert_encoding($data, 'GB2312', 'UTF-8');
        return Des3::init($key, $iv)->encrypt($data);
    }

    /**
     * @param $apiUrl
     * @param $RequestDatas
     * @return Curl
     */
    protected function curl ( $apiUrls, $RequestDatas )
    {
        $curl = new Curl();
        $curl->setHeader("Content-type", "application/json");
        $curl->setHeader("user", $apiUrls['user']);
        $curl->setHeader("pwd", $apiUrls['passwd']);
        $curl->post("http://" . $apiUrls['url'], json_encode(['data' => $RequestDatas]));
        @file_put_contents('../data/logs/'.date('Ymd',time()).'_s.txt', '获取入场时间:'.date("Y-m-d H:i:s",time())."---".$curl->response ."\n", FILE_APPEND);
        return $curl->response;
    }

    public function isOrder($user_id)
    {
        return pdo_fetchall("select * from " . tablename('foll_order') . " where application='parking' and user_id='" .$user_id . "'" . " and (pay_status=0 or pay_status=2)");
    }

    
    
     //发送支付完成给设备
    protected function sendPayInfoDev($ordersn,$type) {
        $postData = [
            'ordersn'=>$ordersn,
            'type'=>$type
        ];
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/pullOnlinePayStatusApi",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Content-Type: application/json",
            ),
        ));
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
    }
    
    /**发送成功付费信息
     * @param $userId
     * @param $uniacid
     * @param $duration
     * @param $total
     * @param $payMoney
     */
    protected function sendPaySuccTep($userId,$uniacid,$duration,$total,$payMoney){
        load()->func('diysend');
        $sendArr = array(
            'body'   => '预付费',//商品描述  a
            'paytime'  => date('Y-m-d H:i:s',time()),//消费时间
            'touser'  => $userId,//接收消息的用户 a
            'uniacid'  => $uniacid,//公众号ID a
        
            'parkTime'   => $duration.' 分钟',//停车时长
            'realTime'   => $duration,//实计时长  b
            'payableMoney'  => sprintf("%.2f",$total),//应付金额 a
            'deducMoney'  => sprintf('%.2f',($total- $payMoney)),//抵扣金额 a
            'payMoney'   => $payMoney,//sprintf("%.2f",$user['pay_account']),//交易金额  实付金额 a
        );
        $sendArr['first']   = '您好，您的停车服务费扣费成功！';
        $sendArr['remark']  = '欢迎您再次使用智能无感路内停车服务！';
        sendSuccesTempl($sendArr);
    }
    
    /*
   * 通知设备亮灯
   */
//    protected function WXRegisterPush ( $orderDev, $parkCode, $InTime )
//    {
//        global $_W;
//        $apiUrl = pdo_get("parking_api_url", array('uniacid' => $_W['uniacid'], 'alias' => 'WXRegisterPush'));
//        $RequestDatas = $this->encryRequestData(["Order_No" => $orderDev, "PlaceNum" => $parkCode, "InTime" => $InTime], $apiUrl);
//        $resData = $this->curl($apiUrl, $RequestDatas);
//        $ResData = json_decode($resData, true);
//        if ( $ResData['resCode'] != 0 ) {
//            return false;
//        }
//        return true;
//    }
}