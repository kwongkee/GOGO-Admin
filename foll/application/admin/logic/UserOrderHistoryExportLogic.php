<?php


namespace app\admin\logic;

use app\admin\model\CustomsPositionModel;
use app\admin\model\CustomsElecOrderDetailModel;
use app\admin\model\CustomsHscodeTariffScheduleModel;
use app\admin\model\FollGoodsRegListModel;
use app\admin\model\SzYiOrderGoodsModel;
use app\admin\model\SzYiOrderModel;


class UserOrderHistoryExportLogic
{
    
    
    /**
     * 获取省
     * @return mixed
     */
    public function getProvince()
    {
        return $this->_getPosition(['pid' => 0]);
    }
    
    /**
     * 获取城市
     * @param $pid
     * @return mixed
     */
    public function getCity($pid)
    {
        return $this->_getPosition(['pid' => $pid]);
    }
    
    /**
     * 获取区
     * @param $pid
     * @return mixed
     */
    public function getArea($pid)
    {
        return $this->_getPosition(['pid' => $pid]);
    }
    
    
    /**
     * 获取地址表
     * @param $where
     * @return mixed
     */
    protected function _getPosition($where)
    {
        return CustomsPositionModel::where($where)->select();
    }
    
    protected function _firstPositionById($id)
    {
        return CustomsPositionModel::where('id', $id)->field('name')->find();
    }
    
    protected function _getHsCode($s, $e)
    {
        return CustomsHscodeTariffScheduleModel::where("hscode like '{$s}%' or hscode like '{$e}%'")
            ->field('hscode')
            ->select();
    }
    
    /**
     * @param $param
     * @return array
     * @throws \Exception
     */
    public function getData($param)
    {
        $data  = isset($param['data']) ? $param['data'] : null;
        $limit = $param['limit'];
        $page  = ($param['page'] - 1) * $limit;
        $page  = $page === 0 ? 1 : $page;
        $list  = [];
        $wh    = '';
        if (!empty($data)) {
            if (!empty($data['province'])) {
                $provice = $this->_firstPositionById($data['province'])->name;
                $wh      = "Province='{$provice}'";
            }
            if (!empty($data['city'])) {
                $city = $this->_firstPositionById($data['city'])->name;
                $wh   .= $wh === '' ? "city='{$city}'" : " and city='{$city}'";
            }
            if (!empty($data['area'])) {
                $area = $this->_firstPositionById($data['area'])->name;
                $wh   .= $wh === '' ? "county='{$area}'" : " and county='{$area}'";
            }
            if (!empty($data['idcardfix'])) {
                $wh .= $wh === '' ? "`OrderDocId` LIKE '{$data['idcardfix']}%'" : " and `OrderDocId` LIKE '{$data['idcardfix']}%'";
                if (!empty($data['birth'])) {
                    $data['birth']    = explode('/', $data['birth']);
                    $data['birth'][0] = date('Ymd', strtotime($data['birth'][0]));
                    $data['birth'][1] = date('Ymd', strtotime($data['birth'][1]));
                    $wh               .= ' and (substring(`OrderDocId`, 7, 8) >= '.$data['birth'][0].' and substring(`OrderDocId`, 7, 8)  <= '.$data['birth'][1].')';
                }
            }
            $goodsWhere = '';
            if (!empty($data['hscode'])) {
                $data['hscode'] = explode('-', trim($data['hscode']));
                if (strlen($data['hscode'][0]) != strlen($data['hscode'][1])) {
                    throw new \Exception('税号范围请保持相同长度');
                }
                // $hsCodeList=$this->_getHsCode(trim($data['hscode'][0]),trim($data['hscode'][1]));
                // $hsCodeStr ='';
                // foreach ($hsCodeList as $code){
                //     $hsCodeStr.=$code->hscode.',';
                // }
                // $hsCodeStr=trim($hsCodeStr,',');
                // $goodsWhere = "HSCode in ({$hsCodeStr}) ";
                $data['hscode'][0] = trim($data['hscode'][0]);
                $data['hscode'][1] = trim($data['hscode'][1]);
                $goodsWhere        = "hscode like '{$data['hscode'][0]}%' or hscode like '{$data['hscode'][1]}%'";
            }
            if (!empty($data['brand'])) {
                $goodsWhere .= $goodsWhere === '' ? "Brand='{$data['brand']}'" : ' and Brand="'.$data['brand'].'"';
            }
            if ($goodsWhere !== '') {
                $goodsSn   = FollGoodsRegListModel::where($goodsWhere)->field('EntGoodsNo')->select();
                $goodSnStr = '';
                foreach ($goodsSn as $sn) {
                    $goodSnStr .= "'".$sn->EntGoodsNo."'".',';
                }
                $goodSnStr  = trim($goodSnStr, ',');
                $goodsWhere = $goodSnStr === '' ? '' : "goodssn in({$goodSnStr})";
            }
            if (!empty(trim($data['buyNum']))) {
                $data['buyNum'] = explode('-', trim($data['buyNum']));
                $goodsWhere  .= $goodsWhere === '' ? "total>={$data['buyNum'][0]} and total<={$data['buyNum'][1]}" : " and (total>={$data['buyNum'][0]} and total<={$data['buyNum'][1]})";
            }
            if ($goodsWhere!==''){
                $orderId = SzYiOrderGoodsModel::where($goodsWhere)->field('orderid')->select();
                $oids    = '';
                foreach ($orderId as $oid) {
                    $oids .= $oid->orderid.',';
                }
                $oids   = trim($oids, ',');
                $osn    = SzYiOrderModel::where("id in({$oids})")->field('ordersn')->select();
                $osnStr = '';
                foreach ($osn as $sn) {
                    $osnStr .= "'".$sn->ordersn."'".',';
                }
                $osnStr = trim($osnStr, ',');
                $wh     = $wh === '' ? "EntOrderNo in({$osnStr})" : $wh." and EntOrderNo in({$osnStr})";
            }
         
        }
        if ($wh !== '') {
            $sql   = "select `OrderDocId`,`RecipientAddr`,`RecipientTel` from ims_customs_elec_order_detail where {$wh}  limit {$page},{$limit} ";
            $sql2  = "select count(*) as total from ims_customs_elec_order_detail where {$wh}";
            $count = CustomsElecOrderDetailModel::query($sql2)[0]['total'];
            $ret   = CustomsElecOrderDetailModel::query($sql);
        } else {
            $count = CustomsElecOrderDetailModel::count();
            $ret   = CustomsElecOrderDetailModel::limit($page, $limit)->field([
                'OrderDocId', 'RecipientAddr', 'RecipientTel',
            ])->select();
        }
        foreach ($ret as &$item) {
            $sexNum = (int) substr($item['OrderDocId'], -2, 1) % 2;
            if ((isset($data['sex']) && $data['sex'] !== '') && !($sexNum === (int) $data['sex'])) {
                continue;
            }
            $list[] = [
                'idcard'        => $item['OrderDocId'],
                'sex'           => $sexNum === 1 ? '男' : '女',
                'shipping_addr' => $item['RecipientAddr'],
                'shipping_tel'  => $item['RecipientTel'],
            ];
        }
        return ['count' => $count, 'data' => $list];
    }
    
}