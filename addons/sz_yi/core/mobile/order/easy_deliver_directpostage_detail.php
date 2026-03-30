<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W;
global $_GPC;

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
        'app' => 'directmail'
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
function deductibleDiscountAmount($unionid, $receiveId, $amount)
{
    load()->classs('curl');
    $curl = new Curl();
    $curl->setHeader('Content-type', 'application/json;charset=utf-8');
    $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/deductibleDiscountAmount', json_encode(['unionid' => $unionid, 'receive_id' => $receiveId, 'amount' => $amount]));
    return $curl->response;
}

/**
 * 标记优惠卷使用
 * @param $receiveId
 */
function use_coupon($data)
{
    load()->classs('curl');
    $curl = new Curl();
    $curl->setHeader('Content-type', 'application/json;charset=utf-8');
    $curl->post('http://shop.gogo198.cn/foll/public/?s=api_v2/Coupon/saveUseCoupon', json_encode($data));

}

function sendmsg()
{
  global $_W;
  //发送提醒
  $url = '';
  $member = m('member')->getMember($_W['openid']);
  if($member)
  {
    $postdata = array(
        'first' => array(
            'value' => '你好,提交打包成功！',
            'color' => '#ff510'
        ),
        'keyword1' => array(
            'value' => $member['nickname'],
            'color' => '#ff510'
        ),
        'keyword2' => array(
            'value' => date('Y-m-d H:i:s',time()),
            'color' => '#ff510'
        ),
        'remark' => array(
            'value' => '系统通知',
            'color' => '#ff510'
        ),
    );

    load()->classs( 'weixin.account' );
    $account=WeAccount::create();
    $account->sendTplNotice($_W['openid'], '8vqI7z2VqXks8H9uTcl8tkR2v9wYi-tBQZjOrrQOuwI', $postdata, $url, $topcolor = '#ff510');
  }
}
// function generateOrderSn()
// {
//     $id = hexdec(uniqid());
//     if ($id % 2 == 0)
//         $id = $id + 1;
//     return $id . mt_rand(1111, 9999);
// }

if (!$_W['isajax']) {
    $title = '直邮费用';
    $coupon_list = get_receive_coupon($_W['fans']['unionid'], $_W['uniacid']);
    $coupon_list = json_decode($coupon_list, true);
    $order = pdo_get('customs_directpostage_order', ['ordersn' => $_GPC['oid']]);
    if ($order['status']==1){
        $hurl=$this->createMobileUrl('order/easy_deliver_directmailpaysuccess');
        $hurl=$hurl."&oid=".$order['ordersn'];
        header("Location:".$hurl);
        exit();
    }
    $orderDetail = pdo_getall('customs_directpostage_orderdetail', ['ordersn' => $_GPC['oid']]);
    $osn = pdo_get('sz_yi_order', ['id' => $order['oid']], ['ordersn']);
    $coupon_str = [];
    if (!empty($coupon_list) && $coupon_list['status'] == 0) {
        foreach ($coupon_list['result'] as $value) {
            $coupon_str[] = '<span style="margin-left: 12px;" data-cid="' . $value['id'] . '">' . $value['coupon_name'] . '<span style=" margin-left: 167px;">' . $value['coupon_money'] . '</span></span>';
        }
        $coupon_str[] = '<span  data-cid="" style="margin-left:41%;color:red;">取消</span>';
    }

    $coupon_str = json_encode($coupon_str);
    include $this->template('order/easy_deliver_directpostage_detail');
} else {

    if ($_GPC['a'] == 'coupon_deduction') {
        $res = deductibleDiscountAmount($_W['fans']['unionid'], $_GPC['receive_id'], $_GPC['Amount']);
        $res = json_decode($res, true);
        if (empty($res) || $res['status'] == 1) {
            show_json(1, '使用失败');
        } else {
            show_json(0, ['youhui' => ($_GPC['Amount'] - $res['result']['amount']), 'total' => $res['result']['amount']]);
        }
    } else {
        if ($_GPC['oid'] == "") {
            show_json(1, '参数错误');
        }
        $orderRes = pdo_get('customs_directpostage_order', ['ordersn' => $_GPC['oid']]);
        if ($orderRes['status']==1){
            sendmsg();
            show_json(0, $this->createMobileUrl('order/easy_deliver_directmailpaysuccess') . '&oid=' . $orderRes['ordersn']);

        }
        if ($orderRes['total_price1'] == 0) {
            pdo_update('customs_directpostage_order', ['status' => 1, 'pay_time' => time()], ['id' => $orderRes['id']]);
            sendmsg();
            show_json(0, $this->createMobileUrl('order/easy_deliver_directmailpaysuccess') . '&oid=' . $orderRes['ordersn']);
        }
        if ($_GPC['isCoupon']!=''&&is_numeric($_GPC['isCoupon'])){
            $res = deductibleDiscountAmount($_W['fans']['unionid'], $_GPC['isCoupon'], $_GPC['Amount']);
            $res = json_decode($res, true);
            if (!empty($res) &&$res['status'] != 1) {
                $orderRes['total_price1']=$orderRes['total_price1']-$res['result']['amount'];
                use_coupon([
                    'order_id'       => $orderRes['ordersn'],
                    'coupon_id'      => 0,
                    'user_id'        => $_W['fans']['unionid'],
                    'original_amout' => $this->onlinePayPrice,
                    'discount_amout' => $this->onlinePayPrice - $res['result']['amount'],
                    'create_time'    => time(),
                    'application'    => 'directmail',
                    'reid'           => $_GPC['isCoupon']
                ]);
            }
            if ($orderRes['total_price1'] == 0) {
                pdo_update('customs_directpostage_order', ['status' => 1, 'pay_time' => time()], ['id' => $orderRes['id']]);
                sendmsg();
                show_json(0, $this->createMobileUrl('order/easy_deliver_directmailpaysuccess') . '&oid=' . $orderRes['ordersn']);
            }
        }
        $pay_ordersn =generateOrderSn(). "1";
        pdo_update('customs_directpostage_order',['pay_ordersn'=>$pay_ordersn],['id'=>$orderRes['id']]);
        $returnUrl = $this->createMobileUrl('order/easy_deliver_directpostage_detail') . '&oid=' . $orderRes['ordersn'];
        $url=[
            'url'=>'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Wechat.php',
            'tid'       =>  $pay_ordersn, // 订单编号
            'opid'    =>  $_W['openid'],// 用户openid
            'fee'       =>  $orderRes['total_price1'], // 订单交易金额
            'title'     =>  '服务费用',// 订单商品名称
            'uniacid'   =>  $_W['uniacid'], // 公众号所属id
            'returnUrl'=>$returnUrl,//回调地址
            'notifyUrl'=>'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/ServiceNotify.php'
        ];
        show_json(2, $url);
    }

}
