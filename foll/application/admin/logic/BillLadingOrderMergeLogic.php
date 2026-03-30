<?php


namespace app\admin\logic;

use app\admin\model\DeclBolModel;
use app\admin\model\CustomsBatchModel;
use app\admin\model\CustomsElecOrderDetailModel;
use app\admin\model\CustomsElecOrderHeadModel;
use app\admin\model\FollGoodsRegListModel;
use app\admin\model\GoodsModel;
use app\admin\logic\GoodsLogic;
use think\Db;

class BillLadingOrderMergeLogic
{
    public function handleMerge($data)
    {
        $this->verifFeild($data);
        $tmp = explode(',', $data['batchList']);
        $num = '';
        foreach ($tmp as $key => $value) {
            $num .= '"'.$value.'",';
        }
        $num              = trim($num, ',');
        $bnum             = $this->_generateBatchNum($data['newBill']);//新批次编号
        $this->ladingInfo = $this->_getBillLadingInfoByBillNum($data['billId']);
        $this->batchInfo  = $this->_getBatchListInfoByBatchNum($num);
        $order            = $this->_getOrderInfoByBatchNum($num);
        if (empty($order)) {
            throw new \RuntimeException('暂无订单数据');
        }
        $this->order = $this->_parseNewOrder($order);
        $goodsLogic  = new GoodsLogic();
        Db::startTrans();
        try {
            foreach ($this->order as $wayNo => $value) {
                if (count($value) === 2) {
                    //需要合并
                    $send        = [];
                    $goods       = [];
                    $lastOrderSn = [];
                    foreach ($value as $item) {
                        $item->goodsNo = json_decode($item->goodsNo, true);
                        foreach ($item->goodsNo as $good) {
                            $goodsName                       = FollGoodsRegListModel::where('EntGoodsNo',
                                $good['goodNo'])->field('ShelfGName')->find();
                            $goods[$goodsName->ShelfGName][] = $good;
                        }
                        $send[$item->senderName][] = $item->senderName;
                        $lastOrderSn[]             = $item->EntOrderNo;
                    }
                    $orderGoods     = [];
                    $xuhao          = 1;
                    $weight         = 0;//毛重
                    $grossWeight    = 0;//净重
                    $orderGoodTotal = 0;
                    foreach ($goods as $gname => $good) {
                        $totalGoodsInfo = GoodsModel::where('goods_name', $gname)->find();
                        $num            = 0;
                        foreach ($good as $item) {
                            $num += $item['num'];
                        }
                        $weight                += sprintf("%.2f", $totalGoodsInfo->netwt * $num);
                        $grossWeight           += sprintf("%.2f", $totalGoodsInfo->grosswt * $num);
                        $orderGoodTotal        += sprintf("%.2f", $totalGoodsInfo->price * $num);
                        $totalGoodsInfo->price = explode('-', $totalGoodsInfo->price)[0];
                        $orderGoods[]          = [
                            'WaybillNo' =>$value[0]->WaybillNo,
                            'goodNo'      => $totalGoodsInfo->goodssn,
                            'price'       => $totalGoodsInfo->price,
                            'prices'      => sprintf("%.2f", $totalGoodsInfo->price * $num),
                            'grossWeight' => sprintf("%.2f", $totalGoodsInfo->netwt * $num),
                            'weight'      => sprintf("%.2f", $totalGoodsInfo->grosswt * $num),
                            'priced'      => $totalGoodsInfo->price,
                            'pricesd'     => sprintf("%.2f", $totalGoodsInfo->price * $num),
                            'xuhao'       => $xuhao,
                            'num'         =>$num
                        ];
                        if(empty($goodsLogic->isExistsShopTableGoods($totalGoodsInfo->goodssn))){
                            $cateId                = $goodsLogic->addCategory(3, $totalGoodsInfo->class);
                            $goodsId               = $goodsLogic->addShopTableFormGoodsStock($cateId, $totalGoodsInfo);
                            $goodsLogic->addGoodsParam(3, $goodsId, $totalGoodsInfo->toArray());
                        }
                        if (empty($goodsLogic->isExistsRegTableGoods($totalGoodsInfo->goodssn))){
                            $goodsLogic->addRegGoodsTableFormStock($totalGoodsInfo->toArray());
                        }
                        ++$xuhao;
                    }
                    $this->_saveMergeOrder($data, $lastOrderSn, $orderGoods, $orderGoodTotal, $value, $send, $weight, $grossWeight, $bnum);
                } else {
                    //不需要合并
                    $this->_saveNotMergeOrderData($data, $value, $bnum);
                }
            }
            $bid = $this->_generateNewBolInfo($data);
            $this->_generateNewBatchInfo($data, $bid, $bnum);
            Db::commit();
        }catch (\Exception $exception){
            Db::rollback();
            throw new \Exception($exception->getMessage());
        }
    }
    
    /**
     * @param $data
     * @throws \RuntimeException
     */
    protected function verifFeild($data)
    {
        if (empty($data['billId'])) {
            throw new \RuntimeException('请填写提单编号');
        }
        if (empty($data['batchList']) || count(explode(',', $data['batchList'])) < 2) {
            throw new \RuntimeException('请选择批次进行合并');
        }
    }
    
    /**
     * 获取提单表信息
     * @param $num
     * @return mixed
     */
    protected function _getBillLadingInfoByBillNum($num)
    {
        return DeclBolModel::where('bill_num', $num)->find();
    }
    
    /**
     * 获取批次表信息
     * @param $nums
     * @return mixed
     */
    protected function _getBatchListInfoByBatchNum($nums)
    {
        
        
        return CustomsBatchModel::where("batch_num in({$nums})")->select();
    }
    
    /**
     * 获取订单信息
     * @param $nums
     * @return mixed
     */
    protected function _getOrderInfoByBatchNum($nums)
    {
        return CustomsElecOrderDetailModel::where("batch_num in({$nums})")->select();
    }
    
    /**
     * @param $data
     * @return int
     */
    protected function _generateNewBolInfo($data)
    {
        return DeclBolModel::insertGetId([
            'user_id'          => 4,
            'eb_ent_name'      => empty($data['ebent_name']) ? $this->ladingInfo->eb_ent_name : $data['ebent_name'],
            'ebp_code'         => empty($data['ebent_no']) ? $this->ladingInfo->ebp_code : $data['ebent_no'],
            'waybill_name'     => $this->ladingInfo->waybill_name,
            'ebp_name'         => empty($data['ebp_ent_name']) ? $this->ladingInfo->ebp_name : $data['ebp_ent_name'],
            'bill_num'         => $data['newBill'],
            'bill_votes'       => $this->ladingInfo->bill_votes,
            'bill_time'        => $this->ladingInfo->bill_time,
            'order_time'       => $this->ladingInfo->order_time,
            'customs_codes'    => $this->ladingInfo->customs_codes,
            'ciq_codes'        => $this->ladingInfo->ciq_codes,
            'decl_sort'        => $this->ladingInfo->decl_sort,
            'decl_type'        => $this->ladingInfo->decl_type,
            'func_code'        => $this->ladingInfo->func_code,
            'action_type'      => $this->ladingInfo->action_type,
            'create_time'      => date('Y-m-d H:i:s'),
            'Importdate'       => $this->ladingInfo->Importdate,
            'Proposdate'       => $this->ladingInfo->Proposdate,
            'Parcelnumber'     => $this->ladingInfo->Parcelnumber,
            'Transport'        => $this->ladingInfo->Transport,
            'Transportmode'    => $this->ladingInfo->Transportmode,
            'Transportnumber'  => $this->ladingInfo->Transportnumber,
            'Flightnumber'     => $this->ladingInfo->Flightnumber,
            'Manifestnumber'   => $this->ladingInfo->Manifestnumber,
            'Inoutport'        => $this->ladingInfo->Inoutport,
            'Dealmode'         => $this->ladingInfo->Dealmode,
            'Storedaddr'       => $this->ladingInfo->Storedaddr,
            'Inplace'          => $this->ladingInfo->Inplace,
            'Shipment'         => $this->ladingInfo->Shipment,
            'Courier'          => $this->ladingInfo->Courier,
            'plat_code'        => $this->ladingInfo->plat_code,
            'plat_cus_code'    => $this->ladingInfo->plat_cus_code,
            'eb_ent_code'      => empty($data['ebp_ent_no']) ? $this->ladingInfo->eb_ent_code : $data['ebp_ent_no'],
            'company_cus_code' => $this->ladingInfo->company_cus_code,
            'waybill_code'     => $this->ladingInfo->waybill_code,
            'prefix'           => $this->ladingInfo->prefix,
            'Outspecies'       => $this->ladingInfo->Outspecies,
            'bol_type'         => $this->ladingInfo->bol_type,
            'main_id'          => $this->ladingInfo->main_id,
            'status'           => 1,
            'last_bill_no'     =>$data['billId']
        ]);
    }
    
    /**
     * @param $data
     * @param  int  $bid
     */
    protected function _generateNewBatchInfo($data, $bid, $bnum)
    {
        CustomsBatchModel::insert([
            'uid'          => 4,
            'bill_id'      => $bid,
            'bill_num'     => $data['newBill'],
            'batch_num'    => $bnum,
            'status'       => 1,
            'succ_num'     => count($this->order),
            'err_num'      => 0,
            'type'         => 'E',
            'desc'         => '待申报',
            'create_time'  => date('Y-m-d H:i:s'),
            'Timerub'      => time(),
            'check_status' => 3,
            'err_goods'    => 2,
        ]);
    }
    
    /**
     * @param $order
     * @return array
     */
    protected function _parseNewOrder(&$order)
    {
        $newOrder = [];
        foreach ($order as $value) {
            $newOrder[$value->WaybillNo][] = $value;
        }
        return $newOrder;
    }
    
    /**
     * @param $billId
     * @return string
     */
    protected function _generateBatchNum($billId)
    {
        return 'E'.str_pad($billId, 14, 'X', STR_PAD_RIGHT).'01';
    }
    
    /**
     * @param $data
     * @param $value
     * @param  string  $bnum
     */
    protected function _saveNotMergeOrderData($data, $value, $bnum)
    {
        $entOrderNo = generateOrderSn('KK');
//        $hid        = $this->_saveOrderHeader($data, $entOrderNo, [$value[0]->EntOrderNo]);
        CustomsElecOrderDetailModel::insert([
            'head_id'                => 0,
            'EntOrderNo'             => $entOrderNo,
            'goodsNo'                => $value[0]->goodsNo,
            'GoodsName'              => $value[0]->GoodsName,
            'OrderGoodTotal'         => $value[0]->OrderGoodTotal,
            'OrderGoodTotalCurr'     => $value[0]->OrderGoodTotalCurr,
            'Freight'                => $value[0]->Freight,
            'Tax'                    => $value[0]->Tax,
            'OtherPayment'           => $value[0]->OtherPayment,
            'OtherPayNotes'          => $value[0]->OtherPayNotes,
            'OtherCharges'           => $value[0]->OtherCharges,
            'ActualAmountPaid'       => $value[0]->ActualAmountPaid,
            'RecipientName'          => $value[0]->RecipientName,
            'RecipientAddr'          => $value[0]->RecipientAddr,
            'RecipientTel'           => $value[0]->RecipientTel,
            'RecipientCountry'       => $value[0]->RecipientCountry,
            'RecipientProvincesCode' => $value[0]->RecipientProvincesCode,
            'OrderDocAcount'         => $value[0]->OrderDocAcount,
            'OrderDocName'           => $value[0]->OrderDocName,
            'OrderDocType'           => $value[0]->OrderDocType,
            'OrderDocId'             => $value[0]->OrderDocId,
            'OrderDocTel'            => $value[0]->OrderDocTel,
            'OrderDate'              => $value[0]->OrderDate,
            'BatchNumbers'           => $value[0]->BatchNumbers,
            'InvoiceType'            => $value[0]->InvoiceType,
            'InvoiceNo'              => $value[0]->InvoiceNo,
            'InvoiceTitle'           => $value[0]->InvoiceTitle,
            'InvoiceIdentifyID'      => $value[0]->InvoiceIdentifyID,
            'InvoiceDesc'            => $value[0]->InvoiceDesc,
            'InvoiceAmount'          => $value[0]->InvoiceAmount,
            'InvoiceDate'            => $value[0]->InvoiceDate,
            'Notes'                  => $value[0]->Notes,
            'EHSEntNo'               => $value[0]->EHSEntNo,
            'EHSEntName'             => $value[0]->EHSEntName,
            'WaybillNo'              => $value[0]->WaybillNo,
            'PayEntNo'               => $value[0]->PayEntNo,
            'PayEntName'             => $value[0]->PayEntName,
            'PayNo'                  => $value[0]->PayNo,
            'Qty'                    => $value[0]->Qty,
            'insuredFree'            => $value[0]->insuredFree,
            'senderName'             => $value[0]->senderName,
            'senderTel'              => $value[0]->senderTel,
            'senderAddr'             => $value[0]->senderAddr,
            'senderCountry'          => $value[0]->senderCountry,
            'senderProvincesCode'    => $value[0]->senderProvincesCode,
            'weight'                 => $value[0]->weight,
            'grossWeight'            => $value[0]->grossWeight,
            'packageType'            => $value[0]->packageType,
            'unit'                   => $value[0]->unit,
            'GoodsStyle'             => $value[0]->GoodsStyle,
            'Province'               => $value[0]->Province,
            'city'                   => $value[0]->city,
            'county'                 => $value[0]->county,
            'BarCode'                => $value[0]->BarCode,
            'create_at'              => time(),
            'OriginCountry'          => $value[0]->OriginCountry,
            'Price'                  => $value[0]->Price,
            'Pay_time'               => $value[0]->Pay_time,
            'batch_num'              => $bnum,
            'supplier_id'            => $value[0]->supplier_id,
            'realPrice'              => $value[0]->realPrice,
            'detail'                 => $value[0]->detail,
            'lastOrderSn'=>json_encode([$value[0]->EntOrderNo])
        ]);
    }
    
    /**
     * @param $data
     * @param $ordersn
     * @return int
     */
    protected function _saveOrderHeader($data, $ordersn, $lastOrderSn)
    {
        $headInfo = CustomsElecOrderHeadModel::where('EntOrderNo', $lastOrderSn[0])->find();
        $headInfo->Head = json_decode($headInfo->Head, true);
        $headInfo->orderHead = json_decode($headInfo->orderHead, true);
        $headInfo->Head['MessageID']= 'KJ881111_'.$ordersn.'_'.date('YmdHis').mt_rand(11111, 99999);
        $headInfo->orderHead['EBEntNo']= empty($data['ebent_no']) ? $headInfo->orderHead['EBEntNo'] : $data['ebent_no'];
        $headInfo->orderHead['EBEntName']= empty($data['ebent_name']) ? $headInfo->orderHead['EBEntName'] : $data['ebent_name'];
        $headInfo->orderHead['EBPEntNo']= empty($data['ebp_ent_no']) ? $headInfo->orderHead['EBPEntNo'] : $data['ebp_ent_no'];
        $headInfo->orderHead['EBPEntName']= empty($data['ebp_ent_name']) ? $headInfo->orderHead['EBPEntName'] : $data['ebp_ent_name'];
        $headInfo->orderHead['InternetDomainName'] = empty($data['internet_domain_name']) ? $headInfo->orderHead['InternetDomainName'] : $data['internet_domain_name'];
        return CustomsElecOrderHeadModel::insertGetId([
            'EntOrderNo'      => $ordersn,
            'Head'            => json_encode($headInfo->Head),
            'orderHead'       => json_encode($headInfo->orderHead),
            'orderWaybillRel' => $headInfo->orderWaybillRel,
            'orderPaymentRel' => $headInfo->orderPaymentRel,
            'edi'             => $headInfo->edi,
            'api_key'         => $headInfo->api_key,
            'lastOrderSn'     => json_encode($lastOrderSn),
        ]);
    }
    
    /**
     *
     * 保存合并订单
     * @param $data
     * @param  array  $lastOrderSn  上级订单编号列表
     * @param  array  $orderGoods  订单商品
     * @param $orderGoodTotal 总额
     * @param $value 订单列表
     * @param  array  $send  发件人
     * @param $weight 毛重
     * @param $grossWeight 净重
     * @param  string  $bnum  批次
     */
    protected function _saveMergeOrder($data, $lastOrderSn, $orderGoods, $orderGoodTotal, $value, $send, $weight, $grossWeight, $bnum) {
        $entOrderNo = generateOrderSn('KK');
//        $hid        = $this->_saveOrderHeader($data, $entOrderNo, $lastOrderSn);
        CustomsElecOrderDetailModel::insert([
            'head_id'                => 0,
            'EntOrderNo'             => $entOrderNo,
            'goodsNo'                => json_encode($orderGoods),
            'GoodsName'              => '日用品',
            'OrderGoodTotal'         => $orderGoodTotal,
            'OrderGoodTotalCurr'     => $value[0]->OrderGoodTotalCurr,
            'Freight'                => $value[0]->Freight,
            'Tax'                    => $value[0]->Tax,
            'OtherPayment'           => $value[0]->OtherPayment,
            'OtherPayNotes'          => $value[0]->OtherPayNotes,
            'OtherCharges'           => $value[0]->OtherCharges,
            'ActualAmountPaid'       => $orderGoodTotal,
            'RecipientName'          => $value[0]->RecipientName,
            'RecipientAddr'          => $value[0]->RecipientAddr,
            'RecipientTel'           => $value[0]->RecipientTel,
            'RecipientCountry'       => $value[0]->RecipientCountry,
            'RecipientProvincesCode' => $value[0]->RecipientProvincesCode,
            'OrderDocAcount'         => $value[0]->OrderDocAcount,
            'OrderDocName'           => $value[0]->OrderDocName,
            'OrderDocType'           => $value[0]->OrderDocType,
            'OrderDocId'             => $value[0]->OrderDocId,
            'OrderDocTel'            => $value[0]->OrderDocTel,
            'OrderDate'              => $value[0]->OrderDate,
            'BatchNumbers'           => $value[0]->BatchNumbers,
            'InvoiceType'            => $value[0]->InvoiceType,
            'InvoiceNo'              => $value[0]->InvoiceNo,
            'InvoiceTitle'           => $value[0]->InvoiceTitle,
            'InvoiceIdentifyID'      => $value[0]->InvoiceIdentifyID,
            'InvoiceDesc'            => $value[0]->InvoiceDesc,
            'InvoiceAmount'          => $value[0]->InvoiceAmount,
            'InvoiceDate'            => $value[0]->InvoiceDate,
            'Notes'                  => $value[0]->Notes,
            'EHSEntNo'               => $value[0]->EHSEntNo,
            'EHSEntName'             => $value[0]->EHSEntName,
            'WaybillNo'              => $value[0]->WaybillNo,
            'PayEntNo'               => $value[0]->PayEntNo,
            'PayEntName'             => $value[0]->PayEntName,
            'PayNo'                  => $value[0]->PayNo,
            'Qty'                    => $value[0]->Qty,
            'insuredFree'            => $value[0]->insuredFree,
            'senderName'             => count($send) >= 2 ? '' : $value[0]->senderName,
            'senderTel'              => count($send) >= 2 ? '' : $value[0]->senderTel,
            'senderAddr'             => count($send) >= 2 ? '' : $value[0]->senderAddr,
            'senderCountry'          => count($send) >= 2 ? '' : $value[0]->senderCountry,
            'senderProvincesCode'    => count($send) >= 2 ? '' : $value[0]->senderProvincesCode,
            'weight'                 => $weight,
            'grossWeight'            => $grossWeight,
            'packageType'            => $value[0]->packageType,
            'unit'                   => $value[0]->unit,
            'GoodsStyle'             => $value[0]->GoodsStyle,
            'Province'               => $value[0]->Province,
            'city'                   => $value[0]->city,
            'county'                 => $value[0]->county,
            'BarCode'                => $value[0]->BarCode,
            'create_at'              => time(),
            'OriginCountry'          => $value[0]->OriginCountry,
            'Price'                  => $value[0]->Price,
            'Pay_time'               => $value[0]->Pay_time,
            'batch_num'              => $bnum,
            'supplier_id'            => $value[0]->supplier_id,
            'realPrice'              => $value[0]->realPrice,
            'detail'                 => $value[0]->detail,
            'lastOrderSn'=>json_encode($lastOrderSn)
        ]);
    }
}