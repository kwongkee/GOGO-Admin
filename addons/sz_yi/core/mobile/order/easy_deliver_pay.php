<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
load()->classs('curl');

/**
 * Class order
 */
class order {


    public $bussId = 0;
    public $cartRes;
    public $chargingRes;
    public $onlinePayPrice = 0;
    public $offlinePayPrice = 0;
    public $oid;
    public $user;

    public function __construct($data) {
        $cid = '';
        foreach ($data['list'] as $value) {
            $cid .= $value . ',';
        }
        foreach ($data['buss'] as $val) {
            $this->bussId = trim($val);
        }
        print_r($cid);die;
        $cid           = rtrim($cid, ',');
        $this->cartRes = $this->getCart($cid);//购物车信息
        $this->user    = $this->_getUser()[0];
        preg_match("/(.*?(?:省|区|自治区))(.*?市|.*?州|.*?自治州)(.*?(?:区|县))(.*)/", $this->user['address'], $match);
        $this->user['address'] = $match;
        $this->chargingRes     = $this->getCharging($this->bussId);//商户计费配置
        $this->delCart($cid);
    }

    /**
     * 订购
     * @param $uniacid
     * @param $openid
     * @return string|null
     */
    public function subscribe($uniacid, $openid) {
        $result = $this->saveOrder($uniacid, $openid);
        if (empty($result)) {
            return '订购失败';
        }
        $oid = pdo_insertid();
        foreach ($this->cartRes as $value) {
            pdo_insert('sz_yi_order_goods', [
                    'uniacid'               => $uniacid,
                    'orderid'               => $oid,
                    'goodsid'               => $value['goodsid'],
                    'price'                 => $value['marketprice'],
                    'total'                 => $value['total'],
                    'createtime'            => time(),
                    'realprice'             => $value['marketprice'],
                    'goodssn'               => $value['goodssn'],
                    'oldprice'              => $value['marketprice'],
                    'supplier_uid'          => $this->bussId,
                    'supplier_apply_status' => 0,
                    'channel_apply_status'  => 0,
                    'ischannelpay'          => 0,
                    'declaration_mid'       => 0,
                    'rankingstatus'         => 0,
                    'openid'                => $openid
                ]
            );//保存订单商品
        }
        return null;
    }

    /**
     * 获取购物车信息
     * @param $cid
     * @return mixed
     */
    public function getCart($cid) {
        $sql = "select a1.*,a2.goodssn,a2.directType,a2.packingType,a2.paymentType from " . tablename('sz_yi_member_cart') . " as a1 left join " . tablename('sz_yi_goods') . " as a2 on a1.goodsid=a2.id where a1.id in({$cid})";
        return pdo_fetchall($sql);
    }

    /**
     * 获取商户计费配置
     * @param $merId 商户id
     * @return mixed
     */
    public function getCharging($merId) {
        return pdo_fetchall("select * from" . tablename('customs_charging') . " where merchatId=" . $merId)[0];
    }


    private function _getUser() {
        global $_W;
        return pdo_fetchall("select a2.* from " . tablename('member') . " a1 left join " . tablename('member_family') . " a2 on a1.phone=a2.phone where a1.unionid='{$_W['fans']['unionid']}'");
    }

    /**
     * 计算费用
     */
    public function countPrice() {
        //计算总金额
        foreach ($this->cartRes as $val) {
            if (empty($this->chargingRes)) {
                //未配置,正常计费
                if ($val['paymentType'] == "1") {
                    //在线
                    $this->onlinePayPrice += sprintf('%.2f', ($val['marketprice'] * $val['total']));
                } else {
                    //线下
                    $this->offlinePayPrice += sprintf('%.2f', ($val['marketprice'] * $val['total']));
                }
            }
            if ($this->chargingRes['isShop'] === "1") {
                //计费
                if ($val['paymentType'] == "1") {
                    //在线
                    $this->onlinePayPrice += $this->billingMethod($this->chargingRes, $val);
                } else {
                    //线下
                    $this->offlinePayPrice += $this->billingMethod($this->chargingRes, $val);
                }
            }
        }
    }

    /**
     * 计费方法
     * @param $chargingRes
     * @param $val
     * @return string
     */
    public function billingMethod($chargingRes, $val) {
        if ($chargingRes['ifFull'] > 0) {
            //比例付款
            return sprintf('%.2f', ($chargingRes['fullFee'] * $val['total']));
        } else {
            //全额付款
            return sprintf('%.2f', ($val['marketprice'] * $val['total']));
        }
    }
    

    /**
     * 保存订单
     * @param $onlinePayPrice
     * @param $offlinePayPrice
     * @param int $bussId
     * @return mixed
     */
    public function saveOrder($uniacid, $openid) {
        global $_GPC;
        $this->oid = 'GC' .generateOrderSn();
        return pdo_insert('sz_yi_order', [
                'uniacid'           => $uniacid,
                'openid'            => $openid,
                'ordersn'           => $this->oid,
                'price'             => $this->onlinePayPrice,
                'goodsprice'        => $this->onlinePayPrice + $this->offlinePayPrice,
                'status'            => $this->onlinePayPrice > 0 ? 0 : 1,
                'paytype'           => $this->onlinePayPrice > 0 ? 0 : 3,
                'remark'            => $_GPC['data']['remark'],
                'createtime'        => time(),
                'paytime'           => $this->onlinePayPrice > 0 ? 0 : time(),
                'oldprice'          => $this->onlinePayPrice,
                'address'           => serialize(
                    [
                        'realname' => $this->user['name'],
                        'mobile'   => $this->user['phone'],
                        'address'  => $this->user['address'][0],
                        'province' => $this->user['address'][1],
                        'city'     => $this->user['address'][2],
                        'area'     => $this->user['address'][3]
                    ]
                ),
                'isvirtual'         => 1,
                'realprice'         => $this->onlinePayPrice,
                'ordersn_general'   => $this->oid,
                'supplier_uid'      => $this->bussId,
                'expresscom'        => 0,
                'expresssn'         => 0,
                'address_send'      => 0,
                'deductyunbimoney'  => 0.00,
                'deductyunbi'       => 0.00,
                'offline_pay_price' => $this->offlinePayPrice,
                'logistics_status'  => $this->onlinePayPrice > 0 ? '已选购,待订购' : '已订购,待打包',
                'logistics_time'    => time()
            ]
        );
    }

    public function delCart($id) {
        pdo_query("DELETE FROM " . tablename('sz_yi_member_cart') . " WHERE id in(:id)", [':id' => $id]);
    }

    /**
     * 计算使用优惠卷后金额
     * @param $unionid
     * @param $receiveId
     * @param $amount
     * @return mixed
     */
    public function use_coupon($unionid, $receiveId, $amount) {
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('https://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/deductibleDiscountAmount', json_encode(['unionid' => $unionid, 'receive_id' => $receiveId, 'amount' => $amount]));
        return $curl->response;
    }

    public function saveUseCoupon($data) {
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('https://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/saveUseCoupon', json_encode($data));
        return $curl->response;
    }

    public function isPay($userid, $receiveId) {
        //获取支付地址
        if ($this->onlinePayPrice > 0) {
            //请求支付
            $res = json_decode($this->use_coupon($userid, $receiveId, $this->onlinePayPrice), true);
            if (empty($res) && $res['status'] == 1) {
                return true;
            }
            $this->saveUseCoupon([
                'order_id'       => $this->oid,
                'coupon_id'      => 0,
                'user_id'        => $userid,
                'original_amout' => $this->onlinePayPrice,
                'discount_amout' => $this->onlinePayPrice - $res['result']['amount'],
                'create_time'    => time(),
                'application'    => 'shop',
                'reid'           => $receiveId
            ]);
            $this->onlinePayPrice = $res['result']['amount'];
            //请求获取支付url
            return true;
        } else {
            // 无需支付
            return false;
        }
    }

    public function getPayUrl() {
        global $_W;

        return [
            'tid'       =>  $this->oid, // 订单编号
            'opid'    =>  $_W['openid'],// 用户openid
            'fee'       =>  $this->onlinePayPrice, // 订单交易金额
            'title'     =>  '个人用品',// 订单商品名称
            'uniacid'   =>  $_W['uniacid'], // 公众号所属id
            'returnUrl'=>''//回调地址
        ];
    }
}

if ($_W['isajax']) {
    $data = $_GPC['data'];
    if (empty($data['list'])) {
        show_json(1, '请选择商品');
    }
    if (empty($data['buss'])) {
        show_json(1, '支付失败');
    }
    $order = new order($data);
    $order->countPrice();
    $err = $order->subscribe($_W['uniacid'], $_W['openid']);
    if (!is_null($err)) {
        show_json(1, $err);
    }
    if ($order->isPay($_W['fans']['unionid'], $data['isCoupon'])) {
        $host = $_SERVER['HTTP_ORIGIN'];
        //获取支付url
        if ($data['type']=='wechat') {
            $url = $order->getPayUrl();
            $url['url']=$host.'/addons/sz_yi/payment/tgwechat/Wechat.php';
            $url['returnUrl'] = $this->createMobileUrl('order/easy_deliver_paysuccess');
            $url['returnUrl'] = $url['returnUrl'] . "&oid=" . $order->oid;
            show_json(2,$url);
        }else{
            $url['payParam'] = $order->getPayUrl();
            $url['ajaxReqUrl']=$host.'/addons/sz_yi/payment/tgwechat/Alipay.php';
            $url['locaUrl']=$host.'/addons/sz_yi/payment/tgwechat/Alipays.php';
            $url['payParam']['returnUrl'] = $this->createMobileUrl('order/easy_deliver_paysuccess');
            $url['payParam']['returnUrl'] = $url['payParam']['returnUrl'] . "&oid=" . $order->oid;
            show_json(3,$url);
        }

    } else {
        // 返回订购成功页面
        $url = $this->createMobileUrl('order/easy_deliver_paysuccess');
        $url = $url . "&oid=" . $order->oid;
        show_json(0, $url);
    }
}







