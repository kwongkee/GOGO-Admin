<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

class Info_EweiShopV2Page extends mobilePage
{

    protected $curls = null;

    public function __construct()
    {

        parent::__construct();
        load()->func("common");
        isUserReg();
        load()->classs("head");
        load()->classs("curl");
        load()->classs('des');
        if (is_null($this->curls)) {
            $this->curls = new Curl();
        }
    }


    public function main()
    {
        global $_W;
        global $_GPC;
        $title          = '停车确认';
        $num            = $_GPC['num'];
        $isVerifNotNull = 0;
        $data           = [];
        $errorMsg       = null;
        $faceid         = $_W['fans']['follow'] != 1 ? 0 : 1;
        $announcement   = Head::announcement($_W['uniacid']);//公告
        $carousel       = Head::carousel($_W['uniacid'], 1);//广告
        //输出广告
        $time      = time();
        $isPeriod  = null;
        $video     = pdo_fetchall("SELECT a.* FROM " . tablename('foll_advertising_content') . ' as a LEFT JOIN ' . tablename('foll_advertising_order') . ' as b ON a.order_id = b.id LEFT JOIN ' . tablename('foll_advertising') . " as c ON b.adv_id = c.id WHERE b.s_time <= $time and $time <= b.e_time and c.uniacid = " . $_W['uniacid'] . " and a.status = 1 limit 3");
        $spaceInfo = pdo_get("parking_space", ['park_code' => $num]);
        $data['auth'] = m('parking')->auth($_W['openid']);    //查询授权

        // if($_W['openid'] == 'oR-IB0g3w57me3fDRAT2nSZG08VY')
        // {
        //   $order = pdo_get('foll_order',array('ordersn'=>'G9919820200423134765343'),array('id','deductMode'));
        //   var_dump($order);
        // }
        if (!empty($this->isOrder($_W['openid']))) {
            $isVerifNotNull = 1003;//未支付订单
            include $this->template('parking/park');
            exit();
        } else {
            if (empty($num)) {
                $isVerifNotNull = 1001;
                include $this->template('parking/park');
                exit();
            } else {
                if ($this->isPeriod($_W['openid'])) {
                    $isVerifNotNull = 1008;//预付费订单
                    include $this->template('parking/park');
                    exit();

                }
            }
        }


        if (empty($spaceInfo)) {//该车位是否空位或占用；
            $isVerifNotNull = 1004;
            $errorMsg       = '泊位不可用';
            include $this->template('parking/park');
            exit();
        }

        if ($spaceInfo['status'] == 3) {
            $isVerifNotNull = 1004;
            $errorMsg       = '当前泊位已有车辆停入';
            include $this->template('parking/park');
            exit();
        }

        if ($spaceInfo['status'] == 4) {
            $isVerifNotNull = 1004;
            $errorMsg       = '当前泊位已超时,离场后补缴';
            include $this->template('parking/park');
            exit();
        }

        if ($spaceInfo['status'] == 2) {
            $isVerifNotNull = 1004;
            $errorMsg       = '当前泊位已停用';
            include $this->template('parking/park');
            exit();
        }

        $RequestResult         = $this->getTimeFromDevice($spaceInfo['numbers']);//入场时间
        $RequestResult['data'] = json_decode($RequestResult['data'], true);
        if ($RequestResult['resCode'] != 0 || empty($RequestResult['data'])) {
            $isVerifNotNull = 1006;
        }
        // 原来授权位置
        // $data['card']    = empty(m('parking')->verifMonthCard()) ? 'no' : 'month';
        $data['charges'] = m('parking')->charger($num);    //查询车位收费标准
        if (!empty($data['charges']['char'])) {
            $data['charges']['char']['payPeriod'] = json_decode($data['charges']['char']['payPeriod'], true);
            $data['stime']                        = $RequestResult['data'][0]['InTime'];
            $newTime                              = date("H", time());
            foreach ($data['charges']['char']['payPeriod'] as $k => $val) {
                $s         = explode(":", $val['starTime'])[0];
                $e         = explode(":", $val['endTime'])[0];
                $s         = abs($s);
                $e         = abs($e);
                $hourArray = [];
                if ($s > $e) {
                    for ($i = $s; $i <= 23; $i++) {
                        $hourArray[] = $i;
                    }
                    for ($i = 0; $i <= $e; $i++) {
                        $hourArray[] = $i;
                    }
                } else {
                    for ($i = $s; $i <= $e; $i++) {
                        $hourArray[] = $i;
                    }
                }
                if (in_array($newTime, $hourArray)) {
                    $data['filtered'] = $val;
                }
            }
            setcookie('Prepayment', json_encode($data['filtered']));
        }
        unset($filtered, $RequestResult);
        include $this->template('parking/park');
    }


    /*
    确认停车
     */
    public function ReallyOrder()
    {
        global $_W;
        global $_GPC;
        load()->func('diysend');
        load()->classs("redis");
        $isParkCodeIsset = null;
        $isParkCodeIsset = pdo_get("parking_space", ['park_code' => $_GPC['parCode']]);

        if (empty($isParkCodeIsset)) {
            show_json(401, '该车位不存在！');
        }

        if ($isParkCodeIsset['status'] == 3 || $isParkCodeIsset['status'] == 2 || $isParkCodeIsset['status'] == 4) {
            show_json(401, '该车位已被占！');
        }

        if (!empty($this->isPeriod($_W['openid']))) {
            show_json(401, '已存在预付费订单！');
        }


        if (!empty($this->isOrder($_W['openid']))) {
            show_json(401, '你已停入！');
        }

        pdo_begin();
        try {
            pdo_update('parking_space', ['status' => 3], ['id' => $isParkCodeIsset['id']]);
            pdo_commit();
        } catch (Exception $exception) {
            pdo_rollback();
            show_json(401, '操作失败!');
        }

        $isAuthStatus = pdo_get("parking_authorize", ['openid' => $_W['openid']]);
        //get entry time
        $RequestResult = $this->getTimeFromDevice($isParkCodeIsset['numbers']);
        //json decode data
        $RequestResult['data'] = json_decode($RequestResult['data'], true);

        //        if ( $RequestResult['error'] ) show_json(401, $RequestResult['msg']);
        if ($RequestResult['resCode'] != 0 || empty($RequestResult['data'])) {
            show_json(401, '该车位未有车停入！');
        }


        //if time is empty
        if (null === $RequestResult['data'][0]['InTime']) {
            show_json(401, '异常时间');
        }


        $addressData = pdo_get("parking_position", ['id' => $isParkCodeIsset['pid']]);
        $isMonthCard = empty(m('parking')->verifMonthCard(date('Y-m-d',strtotime($RequestResult['data'][0]['InTime'])))) ? 0 : 1;
        //generate order number
        $millisecond = round(explode(" ", microtime())[0] * 1000);

        $order_id    = 'G99198' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);

        //generate device order number
        // $devOrderId = date('YmdHis') . substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $devOrderId=generaOrderNo();


        //push info device
        if (!$this->WXRegisterPush($devOrderId, $isParkCodeIsset['numbers'], $RequestResult['data'][0]['InTime'])) {
            show_json(401, '操作失败!');
        }

        $receive = [
            'original'      =>  $order_id,// 原始订单号
            'ordersn'       => $order_id,
            'user_id'       => $_W['openid'],
            'business_id'   => $_W['uniacid'],
            'uniacid'       => $_W['uniacid'],
            'application'   => "parking",
            'goods_name'    => "路内停车",
            'goods_price'   => 0.00,
            'body'          => '停车服务',
            'business_name' => $_W['account']['name'],
            'create_time'   => time(),
            'nickname'      => $_W['fans']['nickname'],
            'address'       => $addressData['Province'] . $addressData['City'] . $addressData['Area'] . $addressData['Town'] . $addressData['Committee'] . $addressData['Road'] . $addressData['Road_num'] . '号'
        ];


        try {
            pdo_begin();
            pdo_insert('foll_order', $receive);
            pdo_insert("parking_order", [
                'original'      => $order_id,// 原始订单号
                'ordersn'       => $order_id,
                'CarNo'         => $isAuthStatus['CarNo'],
                'number'        => $_GPC['parCode'],
                'starttime'     => strtotime($RequestResult['data'][0]['InTime']),
                'moncard'       => $isMonthCard,
                'status'        => '已停车',
                'charge_type'   => 1,
                'charge_status' => 1,
                'OthSeq'        => date('YmdHis', time()) . rand(111111, 999999),
                'devs_ordersn'  => $devOrderId
            ]);
            pdo_commit();
        } catch (Exception $exception) {
            pdo_rollback();
            show_json(400, '数据异常');
        }

        $str         = [
            'template_id' => 'OAPfnX36eT6AbwSNf-y6XdNXFOAt6B6ohnFH7vHzRVc',
            'touser'      => $_W['openid'],
            'Reurl'       => 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.parking_orderdetails',
            'uniacid'     => $_W['uniacid'],
            'address'     => $addressData['Road'] . $addressData['Road_num'] . '号',
            'first'       => '您好，您已成功停入车位',
            'code'        => $_GPC['parCode'],
            'time'        => $RequestResult['data'][0]['InTime'],
            'remark'      => '点击详情，查看订单信息'
        ];
        $serAuthType = unserialize($isAuthStatus['auth_type']);
        if ($isAuthStatus['auth_status'] == 1 && in_array('Fwechat', $serAuthType)) {
            $this->reqWxInParking($order_id);
            // if($_W['openid']=='oR-IB0g3w57me3fDRAT2nSZG08VY')
            // {
            //   $this->reqWxInParkingTest($order_id);
            // }else {
            //   $this->reqWxInParking($order_id);
            // }
        }

        unset($RequestResult, $serAuthType);
        sendPkmsg($str);//            发起微信消息状态
        Rediss::getInstance()->incr('wxConfirm');
        show_json(200, '成功,开始计算。。');
    }


    /*
    public function sendPkmsg($str)
    {
        $tem = array(
            'touser'=>$str['touser'],
            'template_id'=>$str['template_id'],
            "url"=>$str['Reurl'],
            'data' => [
                'first' => array(
                    'value' => $str['first'],
                    'color' => ''
                ),

                'keyword1' => array(
                    'value' => $str['address'],//车位地址
                    'color' => ''
                ),

                'keyword2' => array(
                    'value' => $str['code'],//车位编码
                    'color' => ''
                ),

                'keyword3' => array(
                    'value' => $str['time'],//停入时间
                    'color' => ''
                ),

                'remark' => array(
                    'value' => $str['remark'],//详细
                    'color' => ''
                ),
            ]

        );
        $tems = ['template'=>serialize($tem),'uniacid'=>$str['uniacid']];
        $reponse = $this->reqWx(json_encode($tems));
        unset($tem,$tems);
        return $reponse;
    }
    */



    // 生成唯一订单号
    private function OrderNo() {
        return date('YmdHi',time()).str_pad(mt_rand(1,999999),5,'0',STR_PAD_LEFT).substr(microtime(),2,6).substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0,
                6);
    }


    protected function isOrder($openid)
    {
        return pdo_fetchall("select * from " . tablename('foll_order') . " where application='parking' and user_id='" . $openid . "'" . " and (pay_status=0 or pay_status=2)");
    }

    protected function isPeriod($openid)
    {
        return pdo_fetchall("select b.id from " . tablename('foll_order') . " as a left join " . tablename('parking_order') . " as b on b.ordersn = a.ordersn where a.user_id='" . $openid . "' and  b.charge_status=0");
    }

    /*
   请求入场时间
    */
    public function getTimeFromDevice($code)
    {
        global $_GPC;
        global $_W;
        $timeFormat   = date('Ymd', time());
        $apiUrl       = pdo_get("parking_api_url", ['uniacid' => $_W['uniacid'], 'alias' => 'GetParkingInfo']);
        $key          = mb_convert_encoding($apiUrl['key'], 'GB2312', 'UTF-8');
        $iv           = mb_convert_encoding($timeFormat, 'GB2312', 'UTF-8');
        $RequestDatas = $this->encryRequestData(['PlaceNum' => $code], $apiUrl);
        @file_put_contents('../data/logs/In' . date('Ymd', time()) . '_s.txt',
            '请求入场数据:' . date("Y-m-d H:i:s", time()) . "---" . json_encode($RequestDatas) . "\n", FILE_APPEND);
        $resData      = $this->curl($apiUrl, $RequestDatas);
        @file_put_contents('../data/logs/Res' . date('Ymd', time()) . '_s.txt',
            '请求入场返回数据:' . date("Y-m-d H:i:s", time()) . "---" . $resData . "\n", FILE_APPEND);
        $ResData      = json_decode($resData, true);
        $str          = Des3::init($key, $iv)->decrypt($ResData['data']);
        $str          = mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');
        unset($ResData['data']);
        $ResData['data'] = $str;
        return $ResData;
    }

    /*
     * 通知设备亮灯
     */
    protected function WXRegisterPush($orderDev, $parkCode, $InTime)
    {
        global $_W;
        $apiUrl       = pdo_get("parking_api_url", ['uniacid' => $_W['uniacid'], 'alias' => 'WXRegisterPush']);
        $RequestDatas = $this->encryRequestData(["Order_No" => $orderDev, "PlaceNum" => $parkCode, "InTime" => $InTime],
            $apiUrl);
        $resData      = $this->curl($apiUrl, $RequestDatas);
        @file_put_contents('../data/logs/' . date('Ymd', time()) . '_s.txt', '通知亮灯返回数据:' . date("Y-m-d H:i:s", time()) . "---" . $resData . "\n", FILE_APPEND);
        $ResData      = json_decode($resData, true);
        if ($ResData['resCode'] == 1) {
            return false;
        }
        return true;
    }

    /**
     * @param data
     * @param $apiUrl
     * @return mixed
     */
    protected function encryRequestData($data, $apiUrls)
    {
        $timeFormat = date('Ymd', time());
        $key        = mb_convert_encoding($apiUrls['key'], 'GB2312', 'UTF-8');
        $iv         = mb_convert_encoding($timeFormat, 'GB2312', 'UTF-8');
        $data       = json_encode($data);
        $data       = mb_convert_encoding($data, 'GB2312', 'UTF-8');
        return Des3::init($key, $iv)->encrypt($data);
    }

    /**
     * @param $apiUrl
     * @param $RequestDatas
     * @return Curl
     */
    protected function curl($apiUrls, $RequestDatas)
    {

        $this->curls->setHeader("Content-type", "application/json");
        $this->curls->setHeader("user", $apiUrls['user']);
        $this->curls->setHeader("pwd", $apiUrls['passwd']);
        $this->curls->post("http://" . $apiUrls['url'], json_encode(['data' => $RequestDatas]));
        @file_put_contents('../data/logs/' . date('Ymd', time()) . '_s.txt',
            '请求数据:' . date("Y-m-d H:i:s", time()) . "---" . $RequestDatas . "\n", FILE_APPEND);
        @file_put_contents('../data/logs/' . date('Ymd', time()) . '_s.txt',
            '返回数据:' . date("Y-m-d H:i:s", time()) . "---" . $this->curls->response . "\n", FILE_APPEND);
        return $this->curls->response;
    }

    protected function reqWxInParking($orderid)
    {
        $data = [
            'Token'   => 'inPark', //停车类型；
            'inType'  => 'PARKING',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
            'orderSn' => $orderid,//订单编号；
        ];
        $this->curls->reset();
        $this->curls->post("http://shop.gogo198.cn/payment/Frx/Frx.php", $data);
        @file_put_contents('../data/logs/' . date('Ymd', time()) . '_s.txt',
            "微信免密入场推送：" . date("Y-m-d H:i:s", time()) . "---" . $this->curls->response . "---" . $orderid . "\n",
            FILE_APPEND);
    }

    protected function reqWxInParkingTest($orderid)
    {
        $data = [
            'Token'   => 'inPark', //停车类型；
            'inType'  => 'PARKING',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
            'orderSn' => $orderid,//订单编号；
        ];
        $this->curls->reset();
        $this->curls->post("http://shop.gogo198.cn/payment/Frx/testFrx.php", $data);
        @file_put_contents('../data/logs/' . date('Ymd', time()) . '_s.txt',
            "微信免密入场推送：" . date("Y-m-d H:i:s", time()) . "---" . $this->curls->response . "---" . $orderid . "\n",
            FILE_APPEND);
    }

    public function adv_view()
    {
        global $_W;
        global $_GPC;
        $id       = $_GPC['id'];
        $adv      = pdo_get('foll_advertising_content', ['id' => $id]);
        $adv_data = ['view' => ($adv['view'] + 1),];

        $result = pdo_update('foll_advertising_content', $adv_data, ['id' => $id]);
        if (!empty($result)) {
            show_json(101, '成功');
        }
    }

    public function period_details()
    {
        global $_W;
        global $_GPC;
        $title = '收费详情';
        if (empty($_GPC['number'])) {
            exit(-1);
        }
        $space               = pdo_get('parking_space', ['park_code' => $_GPC['number']]);
        $postion             = pdo_get('parking_position', ['id' => $space['pid']]);
        $charge              = pdo_get('parking_charge', ['id' => $space['cid']]);
        $charge['payPeriod'] = json_decode($charge['payPeriod'], true);
        include $this->template('parking/period_details');
    }


/*新改版*/
public function testparkmain()
    {
        global $_W;
        global $_GPC;
        $title          = '停车确认';
        $num            = $_GPC['num'];
        $isVerifNotNull = 0;
        $data           = [];
        $errorMsg       = null;
        $faceid         = $_W['fans']['follow'] != 1 ? 0 : 1;
        $announcement   = Head::announcement($_W['uniacid']);//公告
        $carousel       = Head::carousel($_W['uniacid'], 1);//广告
        //输出广告
        $time      = time();
        $isPeriod  = null;
        $video     = pdo_fetchall("SELECT a.* FROM " . tablename('foll_advertising_content') . ' as a LEFT JOIN ' . tablename('foll_advertising_order') . ' as b ON a.order_id = b.id LEFT JOIN ' . tablename('foll_advertising') . " as c ON b.adv_id = c.id WHERE b.s_time <= $time and $time <= b.e_time and c.uniacid = " . $_W['uniacid'] . " and a.status = 1 limit 3");
        $spaceInfo = pdo_get("parking_space", ['park_code' => $num]);
        if (!empty($this->isOrder($_W['openid']))) {
            $isVerifNotNull = 1003;//未支付订单
            include $this->template('parking/park_gai');
            exit();
        } else {
            if (empty($num)) {
                $isVerifNotNull = 1001;
                include $this->template('parking/park_gai');
                exit();
            } else {
                if ($this->isPeriod($_W['openid'])) {
                    $isVerifNotNull = 1008;//预付费订单
                    include $this->template('parking/park_gai');
                    exit();

                }
            }
        }


        if (empty($spaceInfo)) {//该车位是否空位或占用；
            $isVerifNotNull = 1004;
            $errorMsg       = '泊位不可用';
            include $this->template('parking/park_gai');
            exit();
        }

        if ($spaceInfo['status'] == 3) {
            $isVerifNotNull = 1004;
            $errorMsg       = '当前泊位已有车辆停入';
            include $this->template('parking/park_gai');
            exit();
        }

        if ($spaceInfo['status'] == 4) {
            $isVerifNotNull = 1004;
            $errorMsg       = '当前泊位已超时,离场后补缴';
            include $this->template('parking/park_gai');
            exit();
        }

        if ($spaceInfo['status'] == 2) {
            $isVerifNotNull = 1004;
            $errorMsg       = '当前泊位已停用';
            include $this->template('parking/park_gai');
            exit();
        }

        $RequestResult         = $this->getTimeFromDevice($spaceInfo['numbers']);//入场时间
        $RequestResult['data'] = json_decode($RequestResult['data'], true);
        if ($RequestResult['resCode'] != 0 || empty($RequestResult['data'])) {
            $isVerifNotNull = 1006;
        }
        $data['auth']    = m('parking')->auth($_W['openid']);    //查询授权
        // $data['card']    = empty(m('parking')->verifMonthCard()) ? 'no' : 'month';
        $data['charges'] = m('parking')->charger($num);    //查询车位收费标准
        if (!empty($data['charges']['char'])) {
            $data['charges']['char']['payPeriod'] = json_decode($data['charges']['char']['payPeriod'], true);
            $data['stime']                        = $RequestResult['data'][0]['InTime'];
            $newTime                              = date("H", time());
            foreach ($data['charges']['char']['payPeriod'] as $k => $val) {
                $s         = explode(":", $val['starTime'])[0];
                $e         = explode(":", $val['endTime'])[0];
                $s         = abs($s);
                $e         = abs($e);
                $hourArray = [];
                if ($s > $e) {
                    for ($i = $s; $i <= 23; $i++) {
                        $hourArray[] = $i;
                    }
                    for ($i = 0; $i <= $e; $i++) {
                        $hourArray[] = $i;
                    }
                } else {
                    for ($i = $s; $i <= $e; $i++) {
                        $hourArray[] = $i;
                    }
                }
                if (in_array($newTime, $hourArray)) {
                    $data['filtered'] = $val;
                }
            }
            setcookie('Prepayment', json_encode($data['filtered']));
        }
        unset($filtered, $RequestResult);
        include $this->template('parking/park_gai');
    }


}
