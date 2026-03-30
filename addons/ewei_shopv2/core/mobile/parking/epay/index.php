<?php
if (!defined("IN_IA")) {
    exit("Access Denied");
}

class Index_EweiShopV2Page extends Page {
    const KEY = "test";

    function main()
    {
        exit(json_encode('404'));
    }

    // 离场接受结束时间
    public function leave()
    {
        global $_W;
        global $_GPC;
        load()->func('logging');
        load()->func("common");
        load()->func("diysend");
        load()->classs("money");
        logging_run($_GPC['__input']);
        $sigin = null;
        if (isset($_GPC['__input'])) {
            $openid = $_GPC['__input']['openid'];
            $endTime = $_GPC['__input']['endtime'];
            $num = $_GPC['__input']['num'];
            $sigin = (string)$_GPC['__input']['Sigin'];
            $apitoken=(string)md5("test".date("Ymd",time()));
            if (!($sigin === $apitoken)) {
                die(json_encode(array('code' => '401', 'message' => 'token验证失败')));
            }
            $res = pdo_get("parking_authorize", array("openid" => $openid));
//             $order = pdo_fetchall("SELECT * FROM " . tablename("parking_order") . " WHERE openid='" . $openid . "'and number='" . $num . "' and pay_status=0 or ORDER BY id DESC limit 1");
            $order=pdo_fetchall("select * from ims_parking_order where openid='".$openid."' and number='".$num."' and (pay_status=0 or charge_status=0)");
            $total = Money::total($openid, $num, $order['0'], $endTime);//总价格函数
            switch ($order['0']['charge_type']) {
                case 0:
                    $price = $order['0']['total'] - $total['total'];
                    if ($price < 0) {
                        $sendArr = array(
                            'touser' => $openid,//接收消息的用户
                            'payMoney' => $price,
                            'uniacid' => $_W['uniacid'],//公众号ID
                            'body' => '停车服务费',//商品描述
                            'paytime' => date("Y-m-d H:i", $order['0']['starttime']) . "至" . date("Y-m-d H:i", $endTime),//离场时间  开始时间跟结束时间date('Ymdhi',time()).'至'.date('Ymdhi',time()),
                        );
                        $this->sendMessagess($sendArr);
                        pdo_update("parking_order", array("total" => $total['total'], "PayAmount" => $price, "endtime" => $endTime), array("ordersn" => $order['0']['ordersn']));
                    } else if ($price > 0) {
                        $url = 'http://shop.gogo198.cn/payment/wechat/refund.php';
                        $postdata = array(
                            'token' => 'refund',
                            'ordersn' => $order['0']['ordersn'],
                            'refundMoney' => $price,
                        );
                        $res = $this->ihttp_post($url, $postdata);
                        $res = json_decode($res, true);
                        if ($res['status'] == 100) {
                            pdo_update("parking_order", array('PayAmount' => abs($order['0']['total'] - $price), 'endtime' => $endTime,'charge_status'=>1), array('ordersn' => $order['0']['ordersn']));
                        } else {
                            pdo_update("parking_order", array('endtime' => $endTime, array('ordersn' => $order['0']['ordersn'])));
                        }
                    }
                    break;
                case 1:
                    $price = $total['total'] == 0 ? 0 : Discount($openid, $total['total']);//优惠函数
                    if ($total['total'] == 0) {
                        $T = timediff($order['0']['starttime'], $endTime);
                        $sendArr = array(
                            'body' => $order['0']['body'],//商品描述
                            'paytime' => date('Y-m-d H:i:s', time()),//消费时间
                            'touser' => $order['0']['openid'],//接收消息的用户
                            'uniacid' => $order['0']['uniacid'],//公众号ID
                            'parkTime' => $T['day'] . '天' . $T['hour'] . '小时' . $T['min'] . '分',//停车时长
                            'realTime' => $total['length'],//实计时长
                            'payableMoney' => 0,//应付金额
                            'deducMoney' => 0,//抵扣金额
                            'payMoney' => 0,//交易金额  实付金额
                        );
                        $sendArr['first'] = '您好，您的停车服务费扣费成功！';
                        $sendArr['remark'] = '欢迎您下次继续使用！';
                        pdo_update("parking_order", array("endtime" => $endTime, "total" => 0, "payAmount" => 0, "paytime" => time(), "pay_status" => 1, "duration" => $total['length'],"status"=>"已缴费"), array('id' => $order['0']['id']));
                        sendMessagess($sendArr);
                        die(json_encode(array('code' => '400', 'message' => '扣费成功')));
                    }
                    pdo_update('parking_order',
                        array('endtime' => $endTime, 'total' => $total['total'], 'PayAmount' => abs($total['total'] - $price), 'duration' => $total['length'],"status"=>"已出账"),
                        array('id' => $order['0']['id'])
                    );//更新订单表
                    if ($res['auth_status'] == 1 && $this->FreePay($res, $order['0']['ordersn'])) {
                        die(json_encode(array('code' => '400', 'message' => '扣费成功')));
                    } else {      // 没有免密就主动
                        $sendArr = array(
                            'touser' => $openid,//接收消息的用户
                            'payMoney' => $total['total'] - $price,
                            'uniacid' => $_W['uniacid'],//公众号ID
                            'body' => '停车服务费',//商品描述
                            'paytime' => date("Y-m-d H:i", $order['0']['starttime']) . "至" . date("Y-m-d H:i", $endTime),//离场时间  开始时间跟结束时间date('Ymdhi',time()).'至'.date('Ymdhi',time()),
                        );
                        $this->sendMessagess($sendArr);
                        die(json_encode(array('code' => '401', 'message' => '授权扣费失败')));
                    }
                    break;
            }
        }
    }


    protected function FreePay($res, $orderId = null)
    {
        $type = unserialize($res['auth_type']);
        if (in_array('Credit_Card', $type)) {
            $url = "http://shop.gogo198.cn/payment/sign/Togrand.php";
            $postdata = [
                'token' => 'Parks',//停车支付  Parks
                'ordersn' => $orderId,//
            ];
            pdo_update("parking_order",array("pay_type"=>"Parks"),array("ordersn"=>$orderId));
            $data = json_decode($this->ihttp_post($url, $postdata), true);
            if ($data['Message']['Plain']['Result']['ResultCode'] == '00') {
                return true;
            }
        }
        return false;
        // 调用信用卡
        // 调用网络支付
    }


    public function failurePay()
    {
        global $_W;
        global $_GPC;
        load()->func('logging');
        load()->func('common');
        load()->func("diysend");
        load()->classs("money");
        logging_run($_GPC);
        if (!$_GPC['__input']['Sigin'] === md5(KEY . date("Ymd", time()))) {
            die(json_encode(array('code' => '401', 'message' => 'token验证失败')));
        }
        $number = isset($_GPC['__input']['Number']) ? $_GPC['__input']['Number'] : die(json_encode(array('code' => '401', 'message' => '值不能空')));//卡号编号
        $account = pdo_get("parking_authorize", array("parking_account" => $number));
        if (!$account) {
            die(json_encode(array('code' => '403', 'message' => '该用户没签约')));
        }
        $starttime = $_GPC['__input']['starTime'];//开始时间
        $endtime = $_GPC['__input']['endTime'];//结束时间
        $num = $_GPC['__input']['ParNum'];//车位编号
        $openid = $account['openid'];
        $time = time();
        $surce = [
            'openid' => $openid,
            'uniacid' => $account['uniacid'],
            'ordersn' => 'G99198' . '商务号' . date('Ymd', time()) . rand(1, time()),
            'CarNo' => $account['CarNo'],
            'number' => $num,
            'starttime' => $starttime,
            'endtime' => $endtime,
            'total' => Total($openid, $num, $starttime, $endtime),
            'PayAmount' => Discount($openid),
            'create_time' => time()
        ];
//      $total=Money::total($openid,$num,$order['0'],$endTime);//总价格函数
//      $price=$total['total']===0?0:Discount($openid,$total['total']);//优惠函数
        // 插入订单
        // pdo_insert("parking_order",$surce);
    }


    // 发送消息方法
    protected function sendMessagess($sendArr)
    {
        // $template_id = 'nTvUH_Pyld7lZr43NzyuvVSFOv7Ksl6li1lo9yvO2NQ';
        $template_id = 'trXaMaikj3VTVCmx4l9urCvridhOrD_95q2Z1NG3ae0';//支付结果通知
        $postdata = array(
            'first' => array(
                'value' => '抱歉，你的停车服务费扣费失败！',
                'color' => '#173177'),

            'keyword1' => array(
                'value' => $sendArr['body'],
                'color' => '#000000'),

            'keyword2' => array(
                'value' => '￥' . $sendArr['payMoney'] . '元',
                'color' => '#000000'),

            'keyword3' => array(
                'value' => $sendArr['paytime'],
                'color' => '#000000'),

            'remark' => array(
                'value' => '请点击详情，继续完成支付！',
                'color' => '#000000'));

        $sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
        $account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
        $urls = 'http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
        load()->func('diysend');
        $abs = new WeiXinAccount($account);
        $status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $urls, $topcolor = '#000000');
    }


    public function ihttp_post($url, $post_data)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}
