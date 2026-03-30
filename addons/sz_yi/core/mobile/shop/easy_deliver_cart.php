<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

class getShopCart
{
    public function get()
    {
        global $_W;
        global $_GPC;

        // $unionid = pdo_get('sz_yi_member', ['openid' => $_W['openid']], ['unionid']);
        // $userList = pdo_fetchAll("select openid from " . tablename('sz_yi_member') . " where unionid='" . $unionid['unionid'] . "'");
        // if (empty($userList)){
        //     return array();
        // }
        $openidList = "'".$_W['openid']."'";
        // foreach ($userList as $value) {
        //     $openidList .= "'" . $value['openid'] . "'" . ',';
        // }
        $sql = "SELECT a1.id,a1.total,a1.marketprice,a2.title,a2.thumb,a2.directType,a2.packingType,a2.paymentType,a3.value,a4.username,a4.uid
                FROM
                    " . tablename('sz_yi_member_cart') . " AS a1
                LEFT JOIN
                    " . tablename('sz_yi_goods') . " AS a2 ON a1.goodsid = a2.id
                LEFT JOIN
                    " . tablename('sz_yi_goods_param') . " AS a3 ON a2.id = a3.goodsid
                LEFT JOIN
                    " . tablename('sz_yi_perm_user') . " AS a4 ON a2.supplier_uid = a4.uid
                WHERE a1.openid IN (" . rtrim($openidList, ',') . ") AND a3.title = '型号规格' and a1.deleted=0";

        $goods = pdo_fetchall($sql);
        $newGoods = [];
        foreach ($goods as $good) {
            $newGoods['total']+=sprintf("%.2f",$good['total']*$good['marketprice']);
            $good['marketprice']= sprintf("%.2f",$good['total']*$good['marketprice']);
            if (isset($newGoods[$good['uid']])) {
                array_push($newGoods[$good['uid']]['goods_list'],$good );
            } else {
                $newGoods[$good['uid']] = [
                    'uid' => $good['uid'],
                    'name' => $good['username'],
                    'goods_list' => [$good]
                ];
            }

        }
        return $newGoods;
    }

    function get_receive_coupon($unionid, $uniacid)
    {
        load()->classs('curl');
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/getReceiveList', json_encode([
            'uniacid' => $uniacid,
            'unionid' => $unionid,
            'status' => 0,
            'time' => time(),
            'app' => 'shop'
        ]));
        return $curl->response;
    }

    /**
     * 计算使用优惠卷后金额
     * @param $unionid
     * @param $receiveId
     * @param $amount
     * @return mixed
     */
    function use_coupon($unionid, $receiveId, $amount)
    {
        load()->classs('curl');
        $curl = new Curl();
        $curl->setHeader('Content-type', 'application/json;charset=utf-8');
        $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/deductibleDiscountAmount', json_encode(['unionid' => $unionid, 'receive_id' => $receiveId, 'amount' => $amount]));
        return $curl->response;
    }
}


if ($_W['isajax']){
    $cart = new getShopCart();
    $res = $cart->use_coupon($_W['fans']['unionid'], $_GPC['receive_id'], $_GPC['amount']);
    $res = json_decode($res, true);
    if (empty($res) || $res['status'] == 1) {
        show_json(1, '使用失败');
    } else {
        show_json(0, $res['result']['amount']);
    }
}else{
    $title='商品订购';
    $cart = new getShopCart();
    $list = $cart->get();
    $totalPrice =$list['total'];
    unset($list['total']);
    $coupon_list =$cart->get_receive_coupon($_W['fans']['unionid'], $_W['uniacid']);
    $coupon_list = json_decode($coupon_list,true);
    $coupon_str = [];
    if (!empty($coupon_list) && $coupon_list['status'] == 0) {
        foreach ($coupon_list['result'] as $value) {
            $coupon_str[] = '<span style="margin-left: 12px;" data-cid="' . $value['id'] . '">' . $value['coupon_name'] . '<span style=" margin-left: 167px;">' . $value['coupon_money'] . '</span></span>';
        }
        $coupon_str[] = '<span  data-cid="" style="margin-left:41%;color:red;">取消</span>';
    }

    $coupon_str = json_encode($coupon_str);

    include $this->template('shop/easy_deliver_cart');
}
