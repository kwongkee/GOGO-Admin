<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}


class Easy_deliver_centralizedpackage extends Core
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $data = [];
        $data['merchantList'] = $this->_getMerchant($_W['openid']);
        // $data['goods'] = $this->_getGoods($_W['openid']);
        $data['recipient'] = $this->_getRecipient($_W['openid']);
        return $data;
    }

    private function _getMerchant($openid)
    {
        $bussList = pdo_fetchall('SELECT supplier_uid from ' . tablename('sz_yi_order') . ' where openid="' . $openid . '" GROUP BY supplier_uid');
        $merchant = '';
        foreach ($bussList as $value) {
            $merchant .= $value['supplier_uid'] . ',';
        }
        return pdo_fetchall('select uid,username from ' . tablename('sz_yi_perm_user') . ' where uid in(' . rtrim($merchant, ',') . ')');
    }

    private function _getGoods($sql)
    {
        // $order = pdo_getall('sz_yi_order', ['openid' => $openid], ['id', 'supplier_uid']);
        // $oid = '';
        // foreach ($order as $value) {
        //     $oid .= $value['id'] . ',';
        // }
        // $sql = 'select a1.id,a1.price,a1.total,a2.title,a2.goodssn from' . tablename('sz_yi_order_goods') . ' as a1 left join ' . tablename('sz_yi_goods') . ' as a2 on a1.goodsid=a2.id where a1.orderid in(' . rtrim($oid, ',') . ') and a2.directType=1';
        return pdo_fetchall($sql);
    }

    private function _getRecipient($openid)
    {
        $account_api = WeAccount::create();
        $fans_info = $account_api->fansQueryInfo($openid);
        $uid = pdo_get('member', ['unionid' => $fans_info['unionid']], ['id']);
        return pdo_fetchall("select id,name from " . tablename('member_family') . " where uid={$uid['id']}");//获取收件人
    }

    public function getGoods()
    {
        global $_W;
        global $_GPC;
        $params = $_GPC;
        $sql = 'select a1.id,a1.price,a1.total,a2.title,a2.goodssn from' . tablename('sz_yi_order_goods') . ' as a1 left join ' . tablename('sz_yi_goods') . ' as a2 on a1.goodsid=a2.id left join ' . tablename('sz_yi_order') . ' as a3 on a1.orderid=a3.id where a3.logistics_order_type=10 and a1.openid="' . $_W['openid'] . '"';
        if ($params['directmailType'] != "") {
            $sql .= ' and a2.directType=' . $params['directmailType'];
        }
        if ($params['merchant'] != "") {
            $sql .= ' and a1.supplier_uid in(' . $params['merchant'] . ')';
        }
        return $this->_getGoods($sql);
    }


    //获取包装材料
    public function getPackageMaterial()
    {
        global $_W;
        global $_GPC;
        $package = pdo_fetchall("select a2.* from " . tablename('customs_packagedistri') . " as a1 left join " . tablename('customs_packaging') . " as a2 on a1.packgeId=a2.id where a1.merchatId=:merchatId", [":merchatId" => $_GPC['merchantId']]);
        if (empty($package)) {
            return '该商户不提供包材';
        }
        return $package;
    }


    /**
     * 开始打包
     * @return string
     */
    public function startPacket()
    {
        require_once '../addons/sz_yi/core/model/Packet.php';
        $model = new Packet();
        try {
            $model->validator();
            $result = $model->goodsPackingOperation();
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
        return $result;
    }

    // public function getMerchant()
    // {
    //
    // }
}

global $_GPC;
global $_W;

$clas = new Easy_deliver_centralizedpackage();
if ($_GPC['a'] == 'main') {
    $title = '集中打包';
    $res = $clas->$_GPC['a'];
    include $this->template('order/easy_deliver_centralizedpackage');
    exit();
} else {
    $res = $clas->$_GPC['a'];
    if (is_string($res)) {
        show_json(1, $res);
    } else {
        if (isset($res['ordersn'])) {
            $url = $this->createMobileUrl('order/easy_deliver_directpostage_detail') . "&oid=" . $res['ordersn'];
            show_json(0, ['url' => $url,'type'=>$res['type']]);
        } else {
            show_json(0, $res);
        }
    }
}
