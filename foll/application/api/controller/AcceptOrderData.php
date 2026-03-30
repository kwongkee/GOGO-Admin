<?php

namespace app\api\controller;

use think\Controller;
use think\Request;
use think\Response;
use think\Db;
use think\Log;
use think\Cache;

class AcceptOrderData extends Controller
{
    protected static $requestData;
    protected        $payStatus = 0;
    protected        $isResfund = 101;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        self::$requestData['user'] = $request->header('user');
        self::$requestData['pwd']  = $request->header('pwd');
        self::$requestData['data'] = $request->post('data');
    }

    public function acceptOrderData(Request $request, Response $response)
    {
        $packResult = validationPacket(self::$requestData);
        if (!$packResult['error']) {
            return json($packResult['errorMsg']);
        }
        $positionId  = Db::name("parking_space")->where("numbers", $packResult['errorMsg']['parkCode'])->field(["pid", "park_code"])->find();
        $isExitOrder = Db::name('parking_order')->where('devs_ordersn', $packResult['errorMsg']['ordersn'])->field('id')->find();

        if (!empty($isExitOrder)) {
            return json(['statusCode' => 1002, 'msg' => "已存在相同订单", 'data' => '']);
        }

        if (empty($positionId)) {
            return json(['statusCode' => 1002, 'msg' => "该车位未在平台作登记", 'data' => '']);
        }
        $uniacid = isset($packResult['errorMsg']['uniacid']) ? $packResult['errorMsg']['uniacid'] : 14;
        $this->isPayStatus($packResult);
        $addrRes = Db::name("parking_position")->where("id", $positionId['pid'])->find();
//        $orderid = 'mb'.date("YmdHis",time()).mt_rand(1111,9999);
        $orderid = 'mb' . date("YmdHis", time()) . mt_rand(1111, 9999) . mt_rand(1111, 9999) . mt_rand(111111, 999999);
        $receive = [
            'ordersn'     => $orderid, //订单编号
            'user_id'     => $orderid,//用户openid
            'business_id' => $uniacid,
            'uniacid'     => $uniacid,//公众号id
            'application' => "parking",
            'goods_name'  => "路内停车",
            'pay_type'    => $packResult['errorMsg']['payType'],
            'pay_status'  => $this->payStatus,
            'pay_time'    => $packResult['errorMsg']['payTime'],
            'pay_account' => $packResult['errorMsg']['payAmount'],
            'body'        => '停车服务',//消费项目
            'create_time' => time(),//订单创建时间
            'total'       => $packResult['errorMsg']['payAmount'],
            'IsWrite'     => $this->isResfund,
            'address'     => $addrRes['Province'] . $addrRes['City'] . $addrRes['Area'] . $addrRes['Town'] . $addrRes['Committee'] . $addrRes['Road'] . $addrRes['Road_num'] . '号',
        ];
        try {
            Db::name("foll_order")->insert($receive);
            $parkPayStatus = $this->payStatus === 1 ? '已结算' : '未结算';
            $receivePark   = [
                'ordersn'       => $orderid,
                'number'        => $positionId['park_code'],
                'starttime'     => $packResult['errorMsg']['stime'],
                'endtime'       => $packResult['errorMsg']['etime'],
                'duration'      => $packResult['errorMsg']['totalMinute'],
                'status'        => $parkPayStatus,
                'charge_type'   => 0,
                'charge_status' => 1,
                'dev_ordersn'   => 'dev',
                'devs_ordersn'  => $packResult['errorMsg']['ordersn'],
            ];
            Db::name("parking_order")->insert($receivePark);
            unset($receivePark, $receive);
            //Cache::inc('devPay');
            return json(['statusCode' => 1001, 'msg' => '成功', 'data' => '']);
        } catch (\Exception $e) {
            Log::write($e->getMessage());
            return json(['statusCode' => 1002, 'msg' => "异常", 'data' => '']);
        }

    }

    /**
     * @param $packResult
     */
    protected function isPayStatus($packResult)
    {
        switch ($packResult['errorMsg']['payStatus']) {
            case 0:
                $this->payStatus = 0;
                $this->isResfund = 101;
                break;
            case 1:
                $this->payStatus = 1;
                $this->isResfund = 101;
                break;
            case 2:
                $this->payStatus = 1;
                $this->isResfund = 102;
                break;
            case 3:
                $this->payStatus = 1;
                $this->isResfund = 100;
                break;
        }
    }
}
