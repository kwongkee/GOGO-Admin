<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}
load()->classs('curl');

date_default_timezone_set('PRC'); //默认时区
global $_W;
global $_GPC;

class  PackagePay
{
    public static $data;
    public static $goods;
    public static $totalGoodsPrice;
    public static $coupon;
    
    public static function validator($data)
    {
        if ($data['oid'] == '') {
            return '参数错误';
        }
        if (empty($data['goodsid'])) {
            return '请选择商品打包';
        }
        if (empty($data['buyName']) || empty($data['recipientName']) || empty($data['shippingName'])) {
            return '请选择申报信息';
        }
        
        if ($data['isPackage'] == 'Y') {
            if (empty($data['packageType'])) {
                return '请选择包材';
            }
        }
        self::$data = $data;
        return null;
    }
    
    
    /**
     * 获取商品 function
     *
     * @param [int] $id
     * @return array
     */
    public static function getGoodsById($id)
    {
        return pdo_fetchall('select a1.id,a1.uniacid,a1.orderid,a1.goodsid,a1.price,a1.total,a1.goodssn,a1.supplier_uid,a2.GrossWt,a2.HSCode from'.tablename('sz_yi_order_goods')."as a1 left join ".tablename('foll_goodsreglist')." as a2 on a1.goodssn=a2.EntGoodsNo where a1.id in({$id})");
    }
    
    public static function getOrderSn($id)
    {
        return pdo_get('sz_yi_order', ['id' => $id], ['ordersn']);
    }
    
    public static function getReceiveCoupon($unionid, $uniacid)
    {
        // load()->classs('curl');
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/getReceiveList', json_encode([
            'uniacid' => $uniacid,
            'unionid' => $unionid,
            'status'  => 0,
            'time'    => time(),
            'app'     => 'directmail',
        ]));
        return $curl->response;
    }
    
    /**
     * 标记优惠卷使用
     * @param $data
     */
    public static function saveUseCoupon($data)
    {
        // load()->classs('curl');
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/saveUseCoupon', json_encode($data));
        
    }
    
    /**
     * 验证打包数 function
     *
     * @return void
     */
    public static function verifPackNum($goods)
    {
        $er = false;
        foreach (self::$data['goodsid'] as $key => $val) {
            if ($val > $goods[$key]['total']) {
                $er = true;
                break;
            }
        }
        return $er;
    }
    
    
    /**
     * 验证保费 function
     *
     * @return void
     */
    public static function verifSafeFee()
    {
        if (self::$data['safeFree'] > 100) {
            return true;
        }
        return false;
    }
    
    
    /**
     * 验证年额度 function
     *
     * @return bool
     */
    public static function verifYearAmount()
    {
        //获取年额度配置
        $edu        = pdo_fetch('select yearMoney from '.tablename('customs_riskconfig').' limit 1');
        $firstday   = date('Y', time()).'-01-01 00:00:00';
        $lastFirday = date('Y-m-01', strtotime('2019-12'));
        $lastday    = date('Y-m-d', strtotime("$lastFirday +1 month -1 day")).' 23:59:00';
        $firstday   = strtotime($firstday);
        $lastday    = strtotime($lastday);
        //获取用户
        $user       = pdo_get('member_family', ['id' => self::$data['recipientName']]);
        $totalPrice = pdo_fetchall('select sum(`OrderGoodTotal`)as total_price from '.tablename('customs_elec_order_detail').' where OrderDocId="'.$user['idcard'].'"'.' and create_at>='.$firstday.' and create_at<='.$lastday);
        if ($totalPrice['total_price'] >= $edu['yearMoney']) {
            return true;
        }
        return false;
        //获取年订单总额
    }
    
    /**
     * 验证商品是否可以一起打包
     * @return bool
     */
    public static function isThatTheTtemCanBePackagedTogether()
    {
        $gid = '';
        foreach (self::$goods as $val) {
            $gid .= $val['goodsid'].',';
        }
        $gid         = rtrim($gid, ',');
        $sql         = "select a1.title,a2.HSCode from ".tablename('sz_yi_goods')." as a1 left join ".tablename('foll_goodsreglist')." as a2 on a1.goodssn=a2.EntGoodsNo where a1.id in({$gid})";
        $hsCode      = pdo_fetchall($sql);
        $lojinHsCode = '';
        $goodsName   = [];
        $message     = '';
        foreach ($hsCode as $value) {
            $lojinHsCode                 .= $value['HSCode'].',';
            $goodsName[$value['HSCode']] = $value['title'];
        }
        $lojinHsCode = rtrim($lojinHsCode, ',');
        if ($lojinHsCode == "") {
            return $message;
        }
        $sql          = "select * from ".tablename('sz_yi_dismantling_conditions')." where hs_code in({$lojinHsCode})";
        $isSplitOrder = pdo_fetchall($sql);
        
        foreach ($isSplitOrder as $value) {
            $message .= $goodsName[$value['hs_code']].',';
        }
        $message = rtrim($message, ',');
        if (empty($message)) {
            return false;
        }
        return $message;
    }
    
    /**
     * 计算费用
     */
    public static function countGoodsCost($goods)
    {
        foreach (self::$data['goodsid'] as $key => $val) {
            self::$totalGoodsPrice += sprintf("%.2f", $goods[$key]['price'] * $val);
        }
    }
    
    /**
     * 计算直邮费用
     */
    public static function countDirectPostage($uniacid)
    {
        global $_W;
        require_once '../addons/sz_yi/core/model/CountDirectPostage.php';
        $countDirectModel         = new CountDirectPostage(self::$data['shippingName'], self::$data['recipientName'],
            $uniacid);
        $countDirectModel->coupon = self::$coupon;
        return $countDirectModel->countServiceFee(self::$goods, $_W['fans']['unionid'], $uniacid);
        // $countCrossborderTransportationCostsPrice = $model->countCrossborderTransportationCosts(self::$goods);//跨境费用
        // $countCalculateCourierCostsPrice = $model->countCalculateCourierCosts(self::$goods);//国内快递费用
        // $countPackMaterialPrice = $model->countPackMaterial(self::$data['isPackage'], self::$data['packageType']);//包材费用
        // $countExpressInsuredPrice = $model->countExpressInsuredPrice(self::$data['safeFree'], self::$totalGoodsPrice);//快递保费
        // $countCustomsClearanceFeesPrice = $model->countCustomsClearanceFees(self::$goods, 1);//清关费用
        // $countTaxPayablePrice = $model->CountTaxPayable(1);//应缴税费
        // return [
        //     'crossTransCosts' => $countCrossborderTransportationCostsPrice,
        //     'calculateCourierCosts' => $countCalculateCourierCostsPrice,
        //     'packMaterial' => $countPackMaterialPrice,
        //     'expressInsured' => $countExpressInsuredPrice,
        //     'customsClearanceFees' => $countCustomsClearanceFeesPrice,
        //     'TaxPayable' => $countTaxPayablePrice
        // ];
    }
    
    /**
     * 生成服务费订单
     * @param $orderId
     * @param $list
     * @return string
     */
    public static function saveServiceFeeOrder($orderId, $list)
    {
        global $_W;
        $totalPrice  = 0;
        $totalPrice1 = 0;
        $oid         = self::_generateOrderSn();
        // $type = ['crossTransCosts' => '跨境运输', 'calculateCourierCosts' => '国内快递', 'packMaterial' => '包材费用', 'expressInsured' => '快递保费', 'customsClearanceFees' => '清关费用', 'TaxPayable' => '应缴税费'];
        foreach ($list as $value) {
            $totalPrice  += $value['originPrice'];
            $totalPrice1 += $value['actuallyPaid'];
            pdo_insert('customs_directpostage_orderdetail', [
                'ordersn'    => $oid,
                'fee_type'   => $value['type'],
                'fee_price'  => $value['originPrice'],
                'fee_price1' => $value['actuallyPaid'],
                'payer'      => $value['payer'],
            ]);
            self::saveUseCoupon([
                'order_id'       => $oid,
                'coupon_id'      => 0,
                'user_id'        => $_W['fans']['unionid'],
                'original_amout' => $value['originPrice'],
                'discount_amout' => $value['originPrice'] - $value['actuallyPaid'],
                'create_time'    => time(),
                'application'    => 'directmail',
                'reid'           => $value['cid'],
            ]);
        }
        
        pdo_insert('customs_directpostage_order', [
            'oid'          => $orderId,
            'ordersn'      => $oid,
            'pay_ordersn'  => $oid."1",
            'pay_ordersn1' => '',
            'total_price'  => $totalPrice,
            'total_price1' => $totalPrice1,
            'status'       => 0,
            'create_time'  => time(),
        ]);
        return $oid;
    }
    
    
    /**
     * 生成打包订单
     * @return mixed
     */
    public static function savePackOrder()
    {
        global $_W;
        $oid = 'GC'.self::_generateOrderSn();
        pdo_insert('sz_yi_order', [
            'uniacid'                  => $_W['uniacid'],
            'openid'                   => $_W['openid'],
            'ordersn'                  => $oid,
            'price'                    => self::$totalGoodsPrice,
            'goodsprice'               => self::$totalGoodsPrice,
            'status'                   => 1,
            'paytype'                  => 2,
            'createtime'               => time(),
            'paytime'                  => time(),
            'oldprice'                 => self::$totalGoodsPrice,
            'isvirtual'                => 1,
            'realprice'                => self::$totalGoodsPrice,
            'ordersn_general'          => $oid,
            'supplier_uid'             => self::$data['shippingName'],
            'expresscom'               => 0,
            'expresssn'                => 0,
            'address_send'             => 0,
            'deductyunbimoney'         => 0.00,
            'deductyunbi'              => 0.00,
            'offline_pay_price'        => self::$totalGoodsPrice,
            'logistics_status'         => '已打包,待直邮',
            'logistics_time'           => time(),
            'logistics_order_type'     => 20,
            'logistics_order_declinfo' => json_encode([
                'recipientId' => self::$data['recipientName'],
                'orderDocId' => self::$data['buyName'],
                'shipperId'   => self::$data['shippingName'],
            ]),
        ]);
        return pdo_insertid();
    }
    
    
    public static function saveOrderGoods($oid)
    {
        global $_W;
        global $_GPC;
        foreach (self::$goods as $value) {
            pdo_insert('sz_yi_order_goods', [
                'uniacid'               => $_W['uniacid'],
                'orderid'               => $oid,
                'goodsid'               => $value['goodsid'],
                'price'                 => $value['price'],
                'total'                 => $_GPC['goodsid'][$value['id']],
                'createtime'            => time(),
                'realprice'             => $value['price'],
                'goodssn'               => $value['goodssn'],
                'oldprice'              => $value['price'],
                'supplier_uid'          => $value['supplier_uid'],
                'supplier_apply_status' => 0,
                'channel_apply_status'  => 0,
                'ischannelpay'          => 0,
                'declaration_mid'       => 0,
                'rankingstatus'         => 0,
                'openid'                => $_W['openid'],
            ]);//保存订单商品
        }
        
    }
    
    /**
     * 判断走cc还是bc类型
     * @return int|string
     */
    public static function isCCOrBC()
    {
        $isRate = 0;
        foreach (self::$goods as $item) {
            $tariff                         = pdo_get('customs_hscode_tariffschedule', ['hscode' => $item['HSCode']],
                ['consumption_tax_rate', 'vat_rate']);
            $tariff['consumption_tax_rate'] = ($tariff['consumption_tax_rate'] == "" || $tariff['consumption_tax_rate'] == '-') ? 0 : $tariff['consumption_tax_rate'];
            $tariff['vat_rate']             = ($tariff['vat_rate'] == "" || $tariff['vat_rate'] == '-') ? 0 : $tariff['vat_rate'];
            $comprehensiveTaxRate           = (($tariff['consumption_tax_rate'] + $tariff['vat_rate']) / (1 - $tariff['consumption_tax_rate'])) * 0.7;//[（消费税率 + 增值税率）/（1-消费税率）]×70%
            $comprehensiveTaxRate           = sprintf("%.2f", $comprehensiveTaxRate);//跨境综合税率
            $comprehensiveRate              = $item['price'] * $comprehensiveTaxRate;//跨境综合税=零售价格 x 跨境综合税率
            $hsCodeTwo                      = substr($item['HSCode'], 0, 2);
            $hsCodeFour                     = substr($item['HSCode'], 0, 4);
            $hsCodeSix                      = substr($item['HSCode'], 0, 6);
            $rateRes                        = pdo_fetchall("select * from ".tablename('customs_hscode_rate')." where code='{$hsCodeTwo}' or code='{$hsCodeFour}' or code='{$hsCodeSix}'");
            foreach ($rateRes as $v) {
                $isRate += sprintf("%.2f", $comprehensiveRate * ($v['rate'] / 100));
            }
        }
        return $isRate;
    }
    
    private static function _generateOrderSn()
    {
        $id = hexdec(uniqid());
        if ($id % 2 == 0) {
            $id = $id + 1;
        }
        return $id.mt_rand(1111, 9999);
    }
}

if (!$_W['isajax']) {
    show_json(1, '请求类型错误');
}

$err = PackagePay::validator($_GPC);
if ($err != null) {
    show_json(1, $err);
}
$gid = '';
foreach ($_GPC['goodsid'] as $key => $value) {
    $gid .= $key.',';
}
$gid = rtrim($gid, ',');
//获取商品
PackagePay::$goods = PackagePay::getGoodsById($gid);
$shopOrder         = PackagePay::getOrderSn(PackagePay::$goods[0]['orderid']);
$coupon            = json_decode(PackagePay::getReceiveCoupon($_W['fans']['unionid'], $_W['uniacid']), true);
if (!empty($coupon) && $coupon['status'] == 0) {
    PackagePay::$coupon = $coupon['result'];
}
$goods = [];
foreach (PackagePay::$goods as $val) {
    $goods[$val['id']] = $val;
}
//验证商品打包数是否超出订购数
if (PackagePay::verifPackNum($goods)) {
    show_json(1, '商品项打包数超出订购数');
}
//验证收件人年额度
if (PackagePay::verifYearAmount()) {
    show_json(1, '收件人年额度已满');
}
//验证保价是否超过总值
PackagePay::countGoodsCost($goods);
if (PackagePay::verifSafeFee()) {
    show_json(1, '保费高于100');
}
//拆单，不能与某些关键商品一起打包
if ($err = PackagePay::isThatTheTtemCanBePackagedTogether()) {
    show_json(1, '包含不可同包裹物品'.$err);
}
$clearType = 'BC';
pdo_begin();
try {
    $feeList = PackagePay::countDirectPostage($_W['uniacid']);
    $orderId = PackagePay::savePackOrder();
    PackagePay::saveOrderGoods($orderId);
    $orderId = PackagePay::saveServiceFeeOrder($orderId, $feeList);
    if (PackagePay::isCCOrBC() <= 50) {
        $clearType = 'CC';
    }
    pdo_insert('customs_packing_list', [
        'pack_ordersn' => $orderId, 'shop_ordersn' => $shopOrder['ordersn'], 'clear_type' => $clearType,
        'create_time'  => time(),
    ]);
    pdo_commit();
} catch (\Exception $e) {
    pdo_rollback();
    show_json(1, $e->getMessage());
}

$url = $this->createMobileUrl('order/easy_deliver_directpostage_detail')."&oid=".$orderId;
show_json(0, ['type' => $clearType, 'url' => $url]);