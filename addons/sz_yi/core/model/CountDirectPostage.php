<?php
// 模块LTD提供

if (!defined('IN_IA')) {
    exit('Access Denied');
}


/**
 * 计算直邮费用
 * Class CountDirectPostage
 */
class CountDirectPostage
{

    public $user;//收件人信息
    public $merchantConf;//商户配置
    public $coupon;
    public $payer = ['1' => '平台付费', '2' => '卖家付费', '3' => '买家付费'];


    /**
     * CountDirectPostage constructor.
     * @param $merId 商户id
     * @param $recipientId 收件人id
     * @param $uniacid 公众id
     */
    public function __construct($merId, $recipientId,$uniacid)
    {
        $this->user = pdo_get('member_family', ['id' => $recipientId]);//获取收件人信息
        preg_match("/(.*?(?:省|区|自治区))(.*?市|.*?州|.*?自治州)(.*?(?:区|县))(.*)/", $this->user['address'], $match);
        $this->user['address'] = $match;
        $this->merchantConf = pdo_get('customs_charging', ['merchatId' => $merId, 'publicId' => $uniacid]);//获取计费配置
    }
    
    /**
     * 计算服务费用
     * @param $goods
     * @return array
     */
    public function countServiceFee($goods,$unionid,$uniacid){
        $this->merchantConf['merid']=json_decode($this->merchantConf['merid'],true);
        if (empty($this->merchantConf['merid'])){
            return [];
        }
        $countResult = [];
        $payer = ['11'=>'平台支付','12'=>'买家支付','13'=>'卖家支付'];
        $grossWt= 0;//商品总毛重
        $orderPrice =0;
        foreach ($goods as $good){
            $grossWt +=$good['GrossWt'];
            $originPrice+=($good['price']*$good['total']);
        }
        // $conpon = json_decode($this->get_receive_coupon($unionid,$uniacid),true);
        foreach ($this->merchantConf['merid'] as $mid=> $value){
            foreach ($value as  $item){
                $id = key($item);
                $res = pdo_get('customs_merchant_service',['id'=>$id,'m_id'=>$mid]);
                $originPrice=0;
                $actuallyPaid=0;
                $cid = 0;
                //按重计费：打包商品总毛重*收费标准，按票定额：一单固定收费标准收取，按额计费：订单费用*收费标准
                switch ($res['billing_type']){
                    case '110':
                        //按票定额
                        $originPrice = 1*$res['billing_standard'];//原价
                        $actuallyPaid = $originPrice;//优惠价
                        break;
                    case '120':
                        
                        $originPrice = (sprintf("%.2f",$grossWt))*$res['billing_standard'];
                        $originPrice = sprintf("%.2f",$originPrice);
                        $actuallyPaid = $originPrice;
                        //按重计算
                        break;
                    case '130':
                        //按额比例
                        $originPrice = (sprintf("%.2f",$orderPrice))*$res['billing_standard'];
                        $originPrice = sprintf("%.2f",$originPrice);
                        $actuallyPaid = $originPrice;
                        break;
                }
                foreach ($this->coupon as $c){
                    if ($c['service_id']==$res['id']){
                        $tmp = json_decode($this->deductibleDiscountAmount($unionid,$c['id'],$actuallyPaid),true);
                        if (!empty($tmp)&&$tmp['status']==0){
                            $cid=$c['id'];
                            $actuallyPaid= $tmp['result']['amount'];
                        }
                    }
                }
                array_push($countResult,[
                    'type'=>$res['service_name'],
                    'originPrice'=>$originPrice,
                    'actuallyPaid'=>$actuallyPaid,
                    'payer'=>$payer[$item[$id]],
                    'cid'=>$cid
                ]);
            }
        }
        return $countResult;
    }
    
    /**
     * 计算使用优惠卷后金额
     * @param $unionid
     * @param $receiveId
     * @param $amount
     * @return mixed
     */
    public function deductibleDiscountAmount($unionid, $receiveId, $amount)
    {
        load()->classs('curl');
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/deductibleDiscountAmount', json_encode(['unionid' => $unionid, 'receive_id' => $receiveId, 'amount' => $amount]));
        return $curl->response;
    }
    
    
    

    /**
     * 计算跨境运输费用
     * @return array
     */
    public function countCrossborderTransportationCosts($goods)
    {
        $originPrice = 0;//应付
        $actuallyPaid = 0;//实付
        foreach ($goods as $val) {
            $originPrice += sprintf("%.2f", ($val['GrossWt'] * $val['total']) * $val['price']);
        }
        if ($this->merchantConf['isLogisFee'] == 1) {
            if ($this->merchantConf['logisPayer'] == 3) {
                $actuallyPaid = $originPrice;
            }
        }
        return ['originPrice' => $originPrice, 'actuallyPaid' => $actuallyPaid, 'payer' => $this->payer[$this->merchantConf['logisPayer']]];
    }


    /**
     * 计算国内快递
     * @param $goods
     * @return array
     */
    public function countCalculateCourierCosts($goods)
    {
        $originPrice = 0;//应付
        $actuallyPaid = 0;//实付
        if ($this->merchantConf['isExpressFee'] == "1") {
            $kuaidi = pdo_get('customs_express', ['id' => $this->merchantConf['expressId']]); //获取快递企业信息
            $weight = 0;
            foreach ($goods as $val) {
                $weight += $val['GrossWt'];
            }
            if ($kuaidi['expName'] == '邮政' || $kuaidi['expName'] == '邮政快递包裹') {
                $areaId = pdo_fetch("select a1.id from " . tablename('customs_area') . " as a1 left join " . tablename('customs_district_association') . " as a2 on a1.id=a2.aid where a1.kuaidi='youzhengguonei' and a2.province='" . $this->user['address'][1] . "'");
                $settlementstandard = pdo_getall('customs_district_charge_standard', ['aid' => $areaId['id']]);
                foreach ($settlementstandard as $value) {
                    if ($weight >= $value['weight_start'] && $weight <= $value['weight_end']) {
                        $originPrice = $value['normal_price'];
                        break;
                    }
                }
            }
            if ($this->merchantConf['expressPayer'] == '3') {
                $actuallyPaid = $originPrice;
            }
        }

        return [
            'originPrice' => $originPrice,
            'actuallyPaid' => $actuallyPaid,
            'payer' => ($this->payer[$this->merchantConf['expressPayer']] == null ? "不计费" : $this->payer[$this->merchantConf['expressPayer']])
        ];
    }


    /**
     * 计算包材
     * @param $isPack
     * @return array
     */
    public function countPackMaterial($isPack,$pakcId)
    {
        $originPrice = 0;//应付
        $actuallyPaid = 0;//实付
        if ($isPack == 'Y') {
            $originPrice = $this->merchantConf['packageMoney']==null?0:$this->merchantConf['packageMoney'];
            // $packMaterial = $pakcId == 0 ? "" : pdo_get('customs_packaging', ['id' => $pakcId]);//获取包材信息
            if ($this->merchantConf['isPackageFee'] == 1) {
                if ($this->merchantConf['packagePayer'] == 3) {
                    $actuallyPaid = $originPrice;
                }
            }
        }

        return [
            'originPrice' => $originPrice,
            'actuallyPaid' => $actuallyPaid,
            'payer' => ($this->payer[$this->merchantConf['packagePayer']] == null ? "不计费" : $this->payer[$this->merchantConf['packagePayer']])
        ];
    }


    /**
     * 快递保费
     * @param $insureVal
     * @return array
     */
    public function countExpressInsuredPrice($insureVal,$totalGoodsPrice)
    {
        $originPrice = 0;//应付
        $actuallyPaid = 0;//实付
        if ($insureVal!=""||$insureVal!==0){
            switch ($this->merchantConf['isRatio']){
                case 1:
                    //定额
                    $originPrice = $this->merchantConf['ratioMoney'];
                    $actuallyPaid = $originPrice;
                    break;
                case 2:
                    $originPrice = sprintf("%.2f",((int)$insureVal/100)*$totalGoodsPrice);
                    $actuallyPaid = $originPrice;
                    //比例
                    break;
                default:
                    break;
            }
        }
        return [
            'originPrice' => $originPrice,
            'actuallyPaid' => $actuallyPaid,
            'payer' => '买家'
        ];

    }

    /**
     * 清关费用
     * @param $goods
     * @return array
     */
    public function countCustomsClearanceFees($goods,$n)
    {
        $originPrice = 0;//应付
        $actuallyPaid = 0;//实付

        if ($this->merchantConf['isShut']==1){
            switch ($this->merchantConf['payersFee']){
                case 1:
                    //比例
                    $originPrice = sprintf("%.2f",$n*$this->merchantConf['payFee']);
                    break;
                case 2:
                    $originPrice = $this->merchantConf['payFee'];
                    //定额
                    break;
            }
        }
        if ($this->merchantConf['isShut']==1){
            if ($this->merchantConf['payers']==3){
                $actuallyPaid = $originPrice;
            }
        }
        return [
            'originPrice' => $originPrice,
            'actuallyPaid' => $actuallyPaid,
            'payer' => ($this->payer[$this->merchantConf['payers']] == null ? "不计费" : $this->payer[$this->merchantConf['payers']])
        ];
    }

    /**
     * 计算应缴税费
     * @return array
     */
    public function CountTaxPayable($n)
    {
        $originPrice = 0;//应付
        $actuallyPaid = 0;//实付
        if ($this->merchantConf['isTax']==1){
            $originPrice = $this->merchantConf['taxCross'];
            if ($this->merchantConf['taxPerson']==1){
                $originPrice+=sprintf("%.2f",$n*$this->merchantConf['carMoney']);
            }
            if ($this->merchantConf['taxPay']==3){
                $actuallyPaid = $originPrice;
            }
        }
        return [
            'originPrice' => $originPrice,
            'actuallyPaid' => $actuallyPaid,
            'payer' => ($this->payer[$this->merchantConf['taxPay']] == null ? "不计费" : $this->payer[$this->merchantConf['taxPay']])
        ];
    }

}



