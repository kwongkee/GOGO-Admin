<?php

namespace app\api\controller;

use think\Response;
use think\Request;
use think\Controller;
use think\Db;
use think\Log;
use think\Cache;

class AcceptExcepInfo extends Controller
{
    protected static $requestData;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        self::$requestData['user'] = $request->header('user');
        self::$requestData['pwd'] = $request->header('pwd');
        self::$requestData['data'] = $request->post('data');
        @file_put_contents('../runtime/log/out/' . date('Ymd', time()) . '.txt', '异常订单数据：' . date('Y-m-d H:i:s', time()) . '---' . json_encode($request->post()) . "\n", FILE_APPEND);
    }

    /**
     * 处理订单异常
     * @param Request $request
     * @param Response $response
     * @return mixed
     */
    public function orderExcepHandling(Request $request, Response $response)
    {
            $packResult = validationPacket(self::$requestData);
            if (!$packResult['error']) {
                ResponseResult($response, $packResult['errorMsg']);
            }

        if (!isset($packResult['errorMsg']['parkCode']) || empty($packResult['errorMsg']['Etime'])) {
            return json(['statusCode' => 1002, 'msg' => '参数错误', 'data' => '']);
        }
        $parkingInfo = $this->getParkingCode($packResult['errorMsg']['parkCode']);
        $parkingOrderInfo = $this->getParkingOrderInfo($parkingInfo['park_code'], $packResult['errorMsg']['Etime']);
        if (empty($parkingOrderInfo)) return json(['statusCode' => 1002, 'msg' => '没有订单信息', 'data' => '']);
        if ($parkingOrderInfo['charge_type'] == 0) {
//            if($parkingOrderInfo['path_oid']!=0){
//                $allOrderInfo = Db::name('foll_order')->where("path_oid={$parkingOrderInfo['path_oid']} or id={$parkingOrderInfo['path_oid']}")->select();
//                foreach ($allOrderInfo as $value){
//                    $this->updateOrderInfo($value['ordersn'],$packResult['errorMsg']['Etime']);
//                    $this->refund($value['ordersn'],$value['pay_account']);
//                }
//                $this->sendExcepWxMessage($parkingOrderInfo);//发送异常信息
//            }else{
            $parm = [
                'pay_status' => 1,
                'pay_account' => 0,
                'isError' => 0,
                'ref_auto'=>2
            ];
            $this->updateOrderInfo($parkingOrderInfo['ordersn'], $packResult['errorMsg']['Etime'], $parm);
            $this->refund($parkingOrderInfo['ordersn'], $parkingOrderInfo['pay_account']);
            $this->sendExcepWxMessage($parkingOrderInfo);//发送异常信息
//            }
        } else {
            $parm = [
                'pay_type' => 'other',
                'pay_status' => 1,
                'pay_time' => time(),
                'pay_account' => 0,
                'total' => 0,
                'isError' => 0,
            ];
            $this->updateOrderInfo($parkingOrderInfo['ordersn'], $packResult['errorMsg']['Etime'], $parm);
            $this->sendExcepWxMessage($parkingOrderInfo);//发送异常信息
        }
        // Cache::inc('excepOrder');
        return json(['statusCode' => 1001, 'msg' => '成功', 'data' => '']);
    }

    protected function getParkingCode($parkCode)
    {
        return Db::name("parking_space")->where("numbers", $parkCode)->find();
    }

    /**
     * 更新泊位状态
     * @param $parkCode
     */
    protected function updateParkingSatatus($parkCode)
    {
        Db::name("parking_space")->where("numbers", $parkCode)->update(['status' => 2]);
    }

    /**
     * 获取当前泊位未结订单
     * @param $parkCode
     * @param $endTime
     * @return mixed
     */
    protected function getParkingOrderInfo($parkCode, $endTime)
    {
        return Db::name("parking_order")
            ->alias("tb1")
            ->join("foll_order tb2", "tb1.ordersn=tb2.ordersn")
            ->where("tb1.number", $parkCode)
            ->where("tb1.starttime", "<", $endTime)
            ->where("tb2.pay_status = 0 or tb1.charge_status = 0")
            ->field(['tb1.charge_type', 'tb1.charge_status', 'tb2.*'])
            ->order('tb2.id', 'desc')
            ->find();
    }

    /*
     * 退款
     */
    protected function refund($oid, $money)
    {
        $url = 'http://shop.gogo198.cn/payment/wechat/refund.php';
        $postdata = array(
            'token' => 'refund',
            'ordersn' => $oid,
            'refundMoney' => $money,
        );
        $res = httpRequest($url, $postdata);
        Log::write($res);
        $res = json_decode($res, true);
        if (isset($res['status']) && $res['status'] == 100) {
            Db::name("foll_order")->where('ordersn', $oid)->update(['IsWrite' => 100]);
        } else {
            Db::name("foll_order")->where('ordersn', $oid)->update(['IsWrite' => 102]);
        }
    }

    /*
     * 发送异常订单模板信息
     */

    protected function sendExcepWxMessage($order)
    {
        $sendArr = array(
            'body' => $order['body'],//商品描述
            'paytime' => date('Y-m-d H:i', time()),//消费时间
            'touser' => $order['user_id'],//接收消息的用户
            'uniacid' => $order['uniacid'],//公众号ID
            'parkTime' => '0 分钟',//停车时长
            'realTime' => '0 分钟',//实计时长
            'payableMoney' => '0.00',//应付金额
            'deducMoney' => '0.00',//抵扣金额
            'payMoney' => '0.00',//交易金额  实付金额
        );
        $sendArr['first'] = '您好，由于系统升级维护，您本次停车服务费免费！';
        $sendArr['remark'] = '欢迎您下次继续使用！';
        $template = [
            'touser' => $sendArr['touser'],
            'template_id' => '671nviCnAMjycHKkjzeUg3NqnM0HwIBnt8bKnDEjf8g',
            'url' => '',
            'data' => array(
                'first' => array(
                    'value' => $sendArr['first'],
                    'color' => '#173177'),

                'keyword1' => array(
                    'value' => $sendArr['parkTime'],//停车时长 12小时22分
                    'color' => '#436EEE'),

                'keyword2' => array(
                    'value' => $sendArr['realTime'],//实计时长：10小时20分钟
                    'color' => '#173177'),

                'keyword3' => array(
                    'value' => '￥' . $sendArr['payableMoney'] . '元',//应付金额：$12元
                    'color' => '#173177'),

//                'keyword4'  =>array(
//                    'value' => '-￥'.$sendArr['deducMoney'].'元',//抵扣金额：-￥2元
//                    'color' =>'#173177'),
                'keyword4' => array(
                    'value' => '￥' . $sendArr['payMoney'] . '元',//实付金额：￥10元
                    'color' => '#173177')),
        ];//消息模板
        $ASSESS_TOKEN = $this->RequestAccessToken($sendArr['uniacid']);
        $hosts = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=" . $ASSESS_TOKEN;
        $wxResult = httpRequest($hosts, json_encode($template));
        @file_put_contents("../runtime/log/wx/wx.txt", $wxResult);
    }


    protected function RequestAccessToken($uniacid)
    {
        return RequestAccessToken($uniacid);

    }

    /*
     * 更新订单信息
     */

    protected function updateOrderInfo($oid, $etime, $fParm)
    {
        try {
            Db::name("foll_order")->where("ordersn", $oid)->update($fParm);
            Db::name("parking_order")->where("ordersn", $oid)->update([
                'endtime' => $etime,
                'duration' => 0,
                'status' => '已结算',
                'charge_status' => 1,
            ]);
        } catch (\Exception $exception) {
            Log::write($exception->getCode() . ":" . $exception->getMessage());
        }
    }


}
