<?php

// 模块LTD提供

if (!defined('IN_IA')) {
    exit('Access Denied');
}
load()->classs('curl');

/**
 * 打包
 * Class Packet
 */
class Packet
{


    private $goods;
    private $totalGoodsPrice;
    private $coupon;

    public function validator()
    {
        global $_GPC;
        if (empty($_GPC['goods_list'])) throw new Exception('请选择商品打包');
        if (empty($_GPC['buy']) || empty($_GPC['recipient']) || empty($_GPC['merchant'])) throw new \Exception('请选择申报信息');
        if ($_GPC['isMaterial'] == 'Y') {
            if (empty($_GPC['material'])) {
                throw new \Exception('请选择包材');
            }
        }
    }

    public function goodsPackingOperation()
    {
        global $_GPC;
        global $_W;
        $gid = '';
        foreach ($_GPC['goods_list'] as $key => $value) {
            $gid .= $key . ',';
        }
        $this->goods = $this->_getGoodsById(rtrim($gid, ','));
        $shopOrder = $this->_getOrderSnById($this->goods);
        $coupon = json_decode($this->get_receive_coupon($_W['fans']['unionid'],$_W['uniacid']),true);
        if ($coupon['status']==0){
            $this->coupon=$coupon['result'];
        }
        $newGoodsData = [];
        foreach ($this->goods as $val) {
            $newGoodsData[$val['id']] = $val;
        }
        $this->_countGoodsPrice($newGoodsData);
        $this->_isPackageOverOrderNum($newGoodsData);
        $this->_verifYearAmount();
        $this->_isSafeFee();
        $this->_isThatTheTtemCanBePackagedTogether();
        $fee = $this->_getServiceFee();
        pdo_begin();
        try {
            $orderId = $this->_savePackOrder();
            $this->_saveOrderGoods($orderId);
            $orderId = $this->_saveServiceFeeOrder($orderId, $fee);
            $type = 'BC';
            if ($this->isCCOrBC()<=50){
                $type='CC';
            }
            foreach ($shopOrder as $value){
                pdo_insert('customs_packing_list',[
                    'pack_ordersn'=>$orderId,
                    'shop_ordersn'=>$value['ordersn'],
                    'clear_type'=>$type,
                    'create_time'=>time()
                ]);
            }
            pdo_commit();
        } catch (\Exception $exception) {
            pdo_rollback();
            throw new \Exception($exception->getMessage());
        }

        return ['ordersn'=>$orderId,'type'=>$type];
    }


    private function _getGoodsById($id)
    {
        return pdo_fetchall('select a1.id,a1.uniacid,a1.orderid,a1.goodsid,a1.price,a1.total,a1.goodssn,a1.supplier_uid,a2.GrossWt,a2.HSCode from' . tablename('sz_yi_order_goods') . "as a1 left join " . tablename('foll_goodsreglist') . " as a2 on a1.goodssn=a2.EntGoodsNo where a1.id in({$id})");
    }

    private function _getOrderSnById($goods)
    {
        $oid = [];
        $oidStr = '';
        foreach ($goods as $val){
            $oid[$val['orderid']] = $val['orderid'];
        }
        foreach ($oid as $val){
            $oidStr.=$val.',';
        }
        return pdo_fetchall('select ordersn from '.tablename('sz_yi_order').' where id in('.trim($oidStr,',').')');
    }

    private function _countGoodsPrice($goodsList)
    {
        global $_GPC;
        foreach ($_GPC['goods_list'] as $key => $val) {
            $this->totalGoodsPrice += sprintf("%.2f", $goodsList[$key]['price'] * $val);
        }
    }

    private function _isPackageOverOrderNum($goodsList)
    {
        global $_GPC;
        foreach ($_GPC['goods_list'] as $key => $val) {
            if ($val > $goodsList[$key]['total']) {
                throw new \Exception('商品项打包数超出订购数');
            }

        }
    }


    /**
     * 验证年额度
     * @throws Exception
     */
    private function _verifYearAmount()
    {
        global $_GPC;
        //获取年额度配置
        $edu = pdo_fetch('select yearMoney from ' . tablename('customs_riskconfig') . ' limit 1');
        $firstday = date('Y', time()) . '-01-01 00:00:00';
        $lastFirday = date('Y-m-01', strtotime('2019-12'));
        $lastday = date('Y-m-d', strtotime("$lastFirday +1 month -1 day")) . ' 23:59:00';
        $firstday = strtotime($firstday);
        $lastday = strtotime($lastday);
        //获取用户
        $user = pdo_get('member_family', ['id' => $_GPC['recipient']]);
        $totalPrice = pdo_fetchall('select sum(`OrderGoodTotal`)as total_price from ' . tablename('customs_elec_order_detail') . ' where OrderDocId="' . $user['idcard'] . '"' . ' and create_at>=' . $firstday . ' and create_at<=' . $lastday);
        if ($totalPrice['total_price'] >= $edu['yearMoney']) {
            throw new \Exception('收件人年额度已满');
        }
        //获取年订单总额
    }

    private function _isSafeFee()
    {
        global $_GPC;
        if ($_GPC['safeFree'] > 100) {
            throw new \Exception('保费高于100');
        }
    }

    private function _isThatTheTtemCanBePackagedTogether()
    {
        $gid = '';
        foreach ($this->goods as $val) {
            $gid .= $val['goodsid'] . ',';
        }
        $gid = rtrim($gid, ',');
        $sql = "select a1.title,a2.HSCode from " . tablename('sz_yi_goods') . " as a1 left join " . tablename('foll_goodsreglist') . " as a2 on a1.goodssn=a2.EntGoodsNo where a1.id in({$gid})";
        $hsCode = pdo_fetchall($sql);
        $lojinHsCode = '';
        $goodsName = [];
        foreach ($hsCode as $value) {
            $lojinHsCode .= $value['HSCode'] . ',';
            $goodsName[$value['HSCode']] = $value['title'];
        }
        $lojinHsCode = trim($lojinHsCode, ',');
        if ($lojinHsCode == "") {
            return false;
        }
        $sql = "select * from " . tablename('sz_yi_dismantling_conditions') . " where hs_code in({$lojinHsCode})";
        $isSplitOrder = pdo_fetchall($sql);
        $message = '';
        foreach ($isSplitOrder as $value) {
            $message .= $goodsName[$value['hs_code']] . ',';
        }
        $message = rtrim($message, ',');
        if (!empty($message)) {
            throw new \Exception('包含不可同包裹物品' . $message);
        }
    }



    private function _getServiceFee()
    {
        global $_GPC;
        global $_W;
        require_once '../addons/sz_yi/core/model/CountDirectPostage.php';
        $countDirectModel = new CountDirectPostage($_GPC['merchant'], $_GPC['recipient'], $_W['uniacid']);
        $countDirectModel->coupon=$this->coupon;
        return $countDirectModel->countServiceFee($this->goods,$_W['fans']['unionid'],$_W['uniacid']);
        // $countCrossborderTransportationCostsPrice = $model->countCrossborderTransportationCosts($this->goods);//跨境费用
        // $countCalculateCourierCostsPrice = $model->countCalculateCourierCosts($this->goods);//国内快递费用
        // $countPackMaterialPrice = $model->countPackMaterial($_GPC['isMaterial'], $_GPC['material']);//包材费用
        // $countExpressInsuredPrice = $model->countExpressInsuredPrice($_GPC['safeFree'], $this->totalGoodsPrice);//快递保费
        // $countCustomsClearanceFeesPrice = $model->countCustomsClearanceFees($this->goods, 1);//清关费用
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
    public function _saveServiceFeeOrder($orderId, $list)
    {
        global $_W;
        $totalPrice = 0;
        $totalPrice1 = 0;
        $type = ['crossTransCosts' => '跨境运输', 'calculateCourierCosts' => '国内快递', 'packMaterial' => '包材费用', 'expressInsured' => '快递保费', 'customsClearanceFees' => '清关费用', 'TaxPayable' => '应缴税费'];
        $oid = $this->_generateOrderSn();
        foreach ($list as $value) {
            $totalPrice += $value['originPrice'];
            $totalPrice1 += $value['actuallyPaid'];
            if ($value['cid']!=0){
                pdo_insert('customs_directpostage_orderdetail', [
                    'ordersn' => $oid,
                    'fee_type' => $value['type'],
                    'fee_price' => $value['originPrice'],
                    'fee_price1' => $value['actuallyPaid'],
                    'payer' => $value['payer']
                ]);
                $this->saveUseCoupon([
                    'order_id'       => $oid,
                    'coupon_id'      => 0,
                    'user_id'        => $_W['fans']['unionid'],
                    'original_amout' => $value['originPrice'],
                    'discount_amout' => $value['originPrice']-$value['actuallyPaid'],
                    'create_time'    => time(),
                    'application'    => 'directmail',
                    'reid'           => $value['cid']
                ]);
            }

        }
        pdo_insert('customs_directpostage_order', [
            'oid' => $orderId,
            'ordersn' => $oid,
            'pay_ordersn' => $oid . "1",
            'pay_ordersn1' => '',
            'total_price' => $totalPrice,
            'total_price1' => $totalPrice1,
            'status' => 0,
            'create_time' => time(),
        ]);
        return $oid;
    }


    /**
     * 生成打包订单
     * @return mixed
     */
    private function _savePackOrder()
    {
        global $_W;
        global $_GPC;
        $oid = 'GC' . $this->_generateOrderSn();
        pdo_insert('sz_yi_order', [
            'uniacid' => $_W['uniacid'],
            'openid' => $_W['openid'],
            'ordersn' => $oid,
            'price' => $this->totalGoodsPrice,
            'goodsprice' => $this->totalGoodsPrice,
            'status' => 1,
            'paytype' => 2,
            'createtime' => time(),
            'paytime' => time(),
            'oldprice' => $this->totalGoodsPrice,
            'isvirtual' => 1,
            'realprice' => $this->totalGoodsPrice,
            'ordersn_general' => $oid,
            'supplier_uid' => $_GPC['merchant'],
            'expresscom' => 0,
            'expresssn' => 0,
            'address_send' => 0,
            'deductyunbimoney' => 0.00,
            'deductyunbi' => 0.00,
            'offline_pay_price' => $this->totalGoodsPrice,
            'logistics_status' => '已打包,待直邮',
            'logistics_time' => time(),
            'logistics_order_type' => 20,
            'logistics_order_declinfo'=>json_encode(['recipientId'=>$_GPC['recipient'],'orderDocId'=>$_GPC['buy'],'shipperId'=>$_GPC['merchant']])
        ]);
        return pdo_insertid();
    }


    private function _saveOrderGoods($oid)
    {
        global $_W;
        global $_GPC;
        foreach ($this->goods as $value) {
            pdo_insert('sz_yi_order_goods', [
                'uniacid' => $_W['uniacid'],
                'orderid' => $oid,
                'goodsid' => $value['goodsid'],
                'price' => $value['price'],
                'total' => $_GPC['goods_list'][$value['id']],
                'createtime' => time(),
                'realprice' => $value['price'],
                'goodssn' => $value['goodssn'],
                'oldprice' => $value['price'],
                'supplier_uid' => $value['supplier_uid'],
                'supplier_apply_status' => 0,
                'channel_apply_status' => 0,
                'ischannelpay' => 0,
                'declaration_mid' => 0,
                'rankingstatus' => 0,
                'openid' => $_W['openid']
            ]);//保存订单商品
        }

    }

    /**
     * 判断走cc还是bc类型
     * @return int|string
     */
    public function isCCOrBC()
    {
        $isRate = 0;
        foreach ($this->goods as $item){
            $tariff=pdo_get('customs_hscode_tariffschedule',['hscode'=>$item['HSCode']],['consumption_tax_rate','vat_rate']);
            $tariff['consumption_tax_rate'] = ($tariff['consumption_tax_rate'] == "" || $tariff['consumption_tax_rate'] == '-') ? 0 : $tariff['consumption_tax_rate'];
            $tariff['vat_rate'] = ($tariff['vat_rate'] == "" || $tariff['vat_rate'] == '-') ? 0 : $tariff['vat_rate'];
            $comprehensiveTaxRate = (($tariff['consumption_tax_rate'] + $tariff['vat_rate']) / (1 - $tariff['consumption_tax_rate'])) * 0.7;//[（消费税率 + 增值税率）/（1-消费税率）]×70%
            $comprehensiveTaxRate = sprintf("%.2f", $comprehensiveTaxRate);//跨境综合税率
            $comprehensiveRate = $item['price'] * $comprehensiveTaxRate;//跨境综合税=零售价格 x 跨境综合税率
            $hsCodeTwo = substr($item['HSCode'], 0, 2);
            $hsCodeFour = substr($item['HSCode'], 0, 4);
            $hsCodeSix = substr($item['HSCode'], 0, 6);
            $rateRes = pdo_fetchall("select * from ".tablename('customs_hscode_rate')." where code='{$hsCodeTwo}' or code='{$hsCodeFour}' or code='{$hsCodeSix}'");
            foreach ($rateRes as $v){
                $isRate += sprintf("%.2f",$comprehensiveRate*($v['rate']/100));
            }
        }
        return $isRate;
    }

    public function get_receive_coupon($unionid, $uniacid)
    {
        // load()->classs('curl');
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/getReceiveList', json_encode([
            'uniacid' => $uniacid,
            'unionid' => $unionid,
            'status' => 0,
            'time' => time(),
            'app' => 'directmail'
        ]));
        return $curl->response;
    }



    /**
     * 标记优惠卷使用
     * @param $data
     */
    public function saveUseCoupon($data)
    {
        // load()->classs('curl');
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/saveUseCoupon', json_encode($data));

    }

    private function _generateOrderSn()
    {
        $id = hexdec(uniqid());
        if ($id % 2 == 0)
            $id = $id + 1;
        return $id . mt_rand(1111, 9999);
    }
}
