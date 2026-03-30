<?php

namespace app\api\logic;
use think\Model;
use think\Db;


class OrderLogic extends Model{
    protected static $orderModel=null;
    protected static $duration=0;
    protected static $uniacid;
    public function __construct () {
        parent::__construct();
        self::$orderModel = model('OrderModel','model');
    }

    /**
     * @param $data
     * @return bool
     * @throws \Exception
     * 生成违法订单
     */
    public static function grenerateVoltOrder($data){

        if(!isset($data['orderId'])){
            throw new \Exception("参数错误");
        }

        $parkInfo = self::$orderModel->getWxParkCode($data['parkCode']);
        self::$uniacid =  $parkInfo['uniacid'];
        if (is_null($parkInfo)){
            throw new \Exception('泊位信息为空');
        }
        $chargeInfo = self::$orderModel->getChargeFromParkCode($parkInfo['cid']);

        if (is_null($chargeInfo)){
            throw new \Exception('收费信息为空');
        }
        $data['cardNo']=self::unicodeDecode($data['cardNo']);
        //处于未离开状态
        if ($data['etime']==0){

            $isOrder = self::$orderModel->hasDevOrderTow($data['orderId']);
            if (!empty($isOrder)) {
                return true;
            }
            $userInfo = self::fetchUserToCarNo($data['cardNo']);

            $addr    = self::$orderModel->getAddr($parkInfo['pid']);

            $millisecond = round(explode(" ", microtime())[0]*1000);

            $order_id = 'G99198'  . date('Ymd', time()) .str_pad($millisecond,3,'0',STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111,999);

            Db::startTrans();
            try{
                self::insertFollOrder($order_id,$userInfo,$addr);
                self::insertParkOrder($order_id,$parkInfo,$data);
                self::saveVioatInfo($order_id,$data,$parkInfo['park_code']);
                Db::commit();
            }catch (\Exception $exception){
                Db::rollback();
                throw new \Exception('系统异常'.$exception->getMessage().$exception->getLine());
            }
        }else{
            $isOrder = self::$orderModel->hasDevOrder($data['orderId']);
            if (empty($isOrder)) {
                return true;
            }
            $orderRes  = self::$orderModel->fetchOrderRes($data['orderId']);
            if (empty($orderRes)){
                throw new \Exception('查询不到订单信息');
            }

            //查询是否预付费超时
            $isPrepTimeOut = self::$orderModel->getDevPayOrderByParkCode($parkInfo['park_code'],$data['stime']);
            if (!empty($isPrepTimeOut)) {
                $data['stime'] = $data['warnTime'];
            }

            //计算费用
            $payMiuMoney = self::countMinutetime($chargeInfo,$data);
            $payMiuMoney['disMoney'] =$payMiuMoney['money'];
            //计算半价优惠
            $disRes             = self::$orderModel->getDisRes(['status' => 2]);
            if ( !empty($disRes) && $payMiuMoney['money'] > 0 ) {
                if ( (time() >= $disRes['startDate']) && (time() <= $disRes['endDate']) ) {
                    $payMiuMoney['disMoney'] = $payMiuMoney['money'] * ($disRes['discount'] / 10);
                }
            }
            Db::startTrans();
            try{
                self::updateFollOrder($orderRes,$payMiuMoney['disMoney'],$payMiuMoney['money']);
                self::updateParkOrder($orderRes,$payMiuMoney['minute'],$data['etime'],$payMiuMoney['disMoney']);
                self::updateVioatInfo($orderRes['ordersn'],$data['etime']);
                Db::commit();
            }catch (\Exception $exception){
                Db::rollback();
                throw new \Exception('系统异常'.$exception->getMessage());
            }
            //发送设备补缴成功信息
            if($payMiuMoney['disMoney']==0){
                sendPayInfoDev($orderRes['ordersn']);
            }else{
                if ($orderRes['user_id']!=''||$orderRes['user_id']!='0'){
                    self::sendWxIrregulMsg($orderRes['user_id'],$payMiuMoney,$parkInfo['park_code'],$parkInfo['uniacid']);
                }
            }

        }

    }


    /**
     * @param $changes
     * @param $data
     * @return array
     *
     * 计算时间
     */
    public static function countMinutetime($changes,$data)
    {

        $charge =json_decode($changes['payPeriod'],true);
        $tem = null;
        $periodIndex = 0;
        $start = $data['stime'];
        $end   = $data['etime'];
        $amount = 0;
        //判断入场在那个段
        foreach ($charge as $key => $value) {
            $sp = explode(":", $value['starTime']);
            $ep = explode(":", $value['endTime']);
            $periodBeet = self::between_hour($sp[0], $ep[0]);
            if (in_array(date('H', $start), $periodBeet)) {
                $tem = $value;
                $periodIndex = $key;
            }
        }
        $allCapped = $changes['Allcapped']; //全天封顶
        $topStart = $start;
        $flag = true;
        $list = [];
        $min = 0;
        $mon = 0;

        while ($flag) {
            if ($topStart <= $end) {
                $topStart += 86400;
                self::$duration +=1440;
                $amount+=30;
            } elseif ($topStart > $end) {
                //减回多加一天
                $topStart = $topStart - 86400;
                $amount = $amount-30;
                self::$duration=self::$duration-1440;
                $flag = false;
                if ($periodIndex == (count($charge) - 1)) {
                    $tempStart = date("Y-m-d", $topStart + 18000) . ' ' . $tem['endTime'];
                } else {
                    $tempStart = date("Y-m-d", $topStart) . ' ' . $tem['endTime'];
                }

                if ($tempStart >= date("Y-m-d H:i", $end)) {
                    array_push($list, [date('Y-m-d H:i', $topStart), date("Y-m-d H:i", $end)]);
                } else {
                    //                        array_push($list, [date('Y-m-d H:i', $topStart), date("Y-m-d H:i", strtotime($tempStart)) . ' ' . $tem['endTime']))]);
                    array_push($list, [date('Y-m-d H:i', $topStart), date("Y-m-d", strtotime($tempStart)) . ' ' . $tem['endTime']]);
                    $periodStart = strtotime(date("Y-m-d", $topStart) . ' ' . $tem['endTime']) + 60;
                    $periodStartPar = date("H:i", $periodStart);
                    foreach ($charge as $key => $value) {
                        $sp = explode(":", $value['starTime']);
                        $ep = explode(":", $value['endTime']);
                        $periodBeet = self::between_hour($sp[0], $ep[0]);
                        if (in_array(abs(explode(":", $periodStartPar)[0]), $periodBeet)) {
                            if ($key == (count($charge) - 1)) {
                                $tempStart = date("Y-m-d", strtotime($tempStart) + 86400) . ' ' . $value['endTime'];
                                $tempStart = strtotime($tempStart);
                            } else {
                                $tempStart = date("Y-m-d", strtotime($tempStart)) . ' ' . $value['endTime'];
                                $tempStart = strtotime($tempStart);
                            }
                            if ($tempStart >= $end) {
                                if ($periodIndex == (count($charge) - 1)) {

                                    array_push($list, [date('Y-m-d', $end) . ' ' . $value['starTime'], date("Y-m-d H:i", $end)]);

                                } else {
                                    array_push($list, [date('Y-m-d', $periodStart) . ' ' . $value['starTime'], date("Y-m-d H:i", $end)]);
                                }
                                //                                    array_push($list, [date('Y-m-d', $end) . ' ' . $value['starTime'], date("Y-m-d H:i", $end)]);
                            } else {

                                array_push($list, [date('Y-m-d', $topStart) . ' ' . $value['starTime'], date('Y-m-d', $tempStart) . ' ' . $value['endTime']]);
                                $periodStart = strtotime(date("Y-m-d", $tempStart) . ' ' . $value['endTime']) + 60;
                                $periodStartPar = date("H:i", $periodStart);
                                foreach ($charge as $k => $val) {
                                    $sp1 = explode(":", $val['starTime']);
                                    $ep1 = explode(":", $val['endTime']);
                                    $periodBeet = self::between_hour($sp1[0], $ep1[0]);
                                    if (in_array(abs(explode(":", $periodStartPar)[0]), $periodBeet)) {
                                        array_push($list, [date('Y-m-d', $end) . ' ' . $val['starTime'], date("Y-m-d H:i", $end)]);
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }
        $dayMoney = 0;
        $mon = 0;
        foreach ($list as $key => $value) {
            foreach ($charge as $k => $v) {
                $sp = explode(":", $v['starTime']);
                $ep = explode(":", $v['endTime']);
                $periodBeet = self::between_hour($sp[0], $ep[0]);
                if (in_array(abs(date("H", strtotime($value[0]))), $periodBeet)) {
                    $min = ceil((strtotime($value[1]) - strtotime($value[0])) / 60);
                    self::$duration+=$min;
                    if ($min < $v['free']) {
                        $min = 0;
                    }
                    $mon = ceil(($min / $v['minute'])) * $v['price'];
                    if ($mon > $v['capped']) {
                        $dayMoney += $v['capped'];
                    } else {
                        $dayMoney += $mon;
                    }
                }
            }
        }

        if ($dayMoney > $allCapped) {
            $amount += $allCapped;
        } else {
            $amount += $dayMoney;
        }
        $nMinute = (count($list)-1);
        if ($nMinute<0){
            $nMinute=0;
        }

        self::$duration+=$nMinute;
        return ['money'=>$amount,'minute'=>self::$duration];
    }

//    /**
//     * 计算金额
//     */
//    public static function countMoney()
//    {
//        return 0;
//    }
    protected static function between_hour ( $s, $e )
    {
        $s         = abs($s);
        $e         = abs($e);
        $hourArray = [];
        if ( $s > $e ) {
            for ($i = $s; $i <= 23; $i++) {
                array_push($hourArray, $i);
            }
            for ($i = 0; $i <= $e; $i++) {
                array_push($hourArray, $i);
            }
            return $hourArray;
        }
        for ($i = $s; $i <= $e; $i++) {
            array_push($hourArray, $i);
        }
        return $hourArray;
    }

    /**
     * @param $cardNo
     * @return mixed
     * 根据车牌获取用户信息
     */
    public static function fetchUserToCarNo($cardNo){
        return self::$orderModel->getUserByCard($cardNo);
    }

    /**
     * @param $order_id
     * @param $userInfo
     * @param $addressData
     * 插入foll订单表
     */
    public static function insertFollOrder($order_id,$userInfo,$addressData){

        $order = [
            'ordersn'   => $order_id,
            'user_id'   => isset($userInfo['openid'])?$userInfo['openid']:0,
            'business_id'=>self::$uniacid,
            'uniacid'   => self::$uniacid,
            'application'=>'parking',
            'goods_name' =>'路内停车',
            'pay_type'   =>null,
            'pay_status' => 0,
            'pay_account'=>0,
            'body'      =>'违规停车',
            'returnUrl' => 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.parking_orderdetails.violition',
            'total'     =>0,
            'create_time'=>time(),
            'address'   =>$addressData['Province'] . $addressData['City'] . $addressData['Area'] . $addressData['Town'] . $addressData['Committee'] . $addressData['Road'] . $addressData['Road_num'] . '号',
            'original' =>$order_id
        ];
        self::$orderModel->saveFollOrder($order);
    }

    /**
     * @param $order_id
     * @param $parkInfo
     * @param $data
     * 插入parking订单表
     */
    public static function insertParkOrder($order_id,$parkInfo,$data){
        $order = [
            'ordersn'   => $order_id,
            'CarNo'     => self::unicodeDecode($data['cardNo']),
            'number'    => $parkInfo['park_code'],
            'starttime' => $data['stime'],
            'moncard'   =>0,
            'status'    => '已停车',
            'charge_type'=>1,
            'charge_status'=>1,
            'OthSeq'    => date('YmdHis', time()) . rand(111111, 999999),
            'devs_ordersn'  =>$data['orderId'],
            'is_violation' =>1,
            'original' => $order_id
        ];
        self::$orderModel->saveParkOrder($order);
    }

    /**
     * @param $orderId
     * @param $data
     * @param $code
     * 保存车主违规信息
     */
    public static function saveVioatInfo($orderId,$data,$code){
        $data = [
            'ordersn'   => $orderId,
            'park_code' =>$code,
            'picture'   => $data['picture'],
            'warn_time' =>$data['warnTime'],
            'law_time'  =>$data['lawTime'],
            'stime'     =>$data['stime'],
            'etime'     => $data['etime'],
            'cardNo'    => $data['cardNo'],
            'dev_order' =>$data['orderId']
        ];
        self::$orderModel->saveViolation($data);
    }

    public static function unicodeDecode($unicode_str){
        $json = '{"str":"'.$unicode_str.'"}';
        $arr = json_decode($json,true);
        if(empty($arr)) return '';
        return $arr['str'];
    }



    public static function updateFollOrder($orderData,$money,$toMoney){
        $disMoney = $money;
        $data = [
            'pay_type'   => $disMoney==0?'other':null,
            'pay_status'  =>$disMoney==0?1:2,
            'pay_time'   => $disMoney==0?time():null,
            'pay_account'=>$disMoney,
            'total'     =>$toMoney,
        ];

        self::$orderModel->updateFollOrder(['ordersn'=>$orderData['ordersn']],$data);
    }

    public static function updateParkOrder($orderData,$minute,$endTime,$money){
        $parm = [
            'endtime'   => $endTime,
            'duration'  => $minute,
            'status'    =>$money==0?'已结算':'已出账',
        ];
        self::$orderModel->updateParkOrder(['ordersn'=>$orderData['ordersn']],$parm);
    }

    public static function updateVioatInfo($orderId,$etime){
        $parm = [
            'etime'   => $etime
        ];
        self::$orderModel->updateVioatInfo(['ordersn'=>$orderId],$parm);
    }


    /**
     * 发送违规订单信息模板
     */
    public static function sendWxIrregulMsg($openid,$payInfo,$parkCode,$uniacid){
        $template=[
            'touser'=>$openid,
            'template_id'=>'fo3NMCuQP8rRwRqBfxvfSWHmbA7aaNqwvCahpTXyM8E',
            'url'=>'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.parking_orderdetails',
            'data'=>array(
                'first'=>array(
                    'value'=>"您好，您的车辆已超时，请补缴！",
                    'color'=>'#173177'),
                'keyword1'=>array(
                    'value'=>$parkCode,
                    'color'=>'#436EEE'),
                'keyword2'=>array(
                    'value'=>$payInfo['minute'].' 分钟',
                    'color'=>'#173177'),
                'keyword3'=>array(
                    'value'=>$payInfo['disMoney'].' 元',
                    'color'=>'#173177'),
                'remark'=>array(
                    'value'=>"请点击详情，继续完成支付!",
                    'color'=>'#808080'))
        ];//消息模板
        $t = ['template'=>serialize($template),'uniacid'=>$uniacid];
        httpRequest('http://shop.gogo198.cn/foll/public/?s=api/wechat/template',json_encode($t), array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        ));
    }

}
