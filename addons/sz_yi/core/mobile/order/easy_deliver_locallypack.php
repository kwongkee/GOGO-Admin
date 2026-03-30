<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
// if ($_W['isajax']){
//     show_json(0);
// }

if ($_W['isajax'] && $_GPC['a'] == 'package') {
    //获取包材
    if (!is_numeric($_GPC['merid'])) {
        show_json(1, '参数错误');
    }

    $package = pdo_fetchall("select a2.* from " . tablename('customs_packagedistri') . " as a1 left join " . tablename('customs_packaging') . " as a2 on a1.packgeId=a2.id where a1.merchatId=:merchatId and a1.publicId=:publicId", [":merchatId" => $_GPC['merid'], ':publicId' => $_W['uniacid']]);
    if (empty($package)) {
        show_json(1, '该商户不提供包材');
    }
    show_json(0, $package);
} elseif ($_W['isajax'] && $_GPC['a'] == 'goods') {
    //获取商品
    if ($_GPC['type']=='directmail'){
        $oid = pdo_get('sz_yi_order', ['openid' => $_W['openid'], 'ordersn' => $_GPC['oid']], ['id', 'supplier_uid']);
        $goods = pdo_fetchall('select a1.id,a1.price,a1.total,a2.title,a2.goodssn from' . tablename('sz_yi_order_goods') . ' as a1 left join ' . tablename('sz_yi_goods') . ' as a2 on a1.goodsid=a2.id where a1.orderid=:orderid and a2.directType=:val',[':orderid'=>$oid['id'],':val'=>$_GPC['val']]);
    }
    if (empty($goods)){
        show_json(1,'暂无数据');
    }
    show_json(0,$goods);
} else if($_W['isajax'] && $_GPC['a'] == 'safe'){
    //获取保价
    $res = pdo_get('customs_charging',['merchatId'=>$_GPC['merid']],['isPremium','isRatio','ratioMoney','bili','expMoney','shopMoney']);
    if(empty($res)){
        show_json(1,'暂无数据');
    }
    show_json(0,$res);
}else {
    $title = '就地打包';
    if ($_GPC['oid']==""){
        message('参数错误');
    }
    //分组已购商家
    $bussList = pdo_fetchall('SELECT supplier_uid from ' . tablename('sz_yi_order') . ' where openid="' . $_W['openid'] . '" GROUP BY supplier_uid');
    $bussId = '';
    foreach ($bussList as $value) {
        $bussId .= $value['supplier_uid'] . ',';
    }
    $bussRes = pdo_fetchall('select uid,username from ' . tablename('sz_yi_perm_user') . ' where uid in(' . rtrim($bussId, ',') . ')');
    $oid = pdo_get('sz_yi_order', ['openid' => $_W['openid'], 'ordersn' => $_GPC['oid']], ['id', 'supplier_uid']);
    $goods = pdo_fetchall('select a1.id,a1.price,a1.total,a2.title,a2.goodssn from' . tablename('sz_yi_order_goods') . ' as a1 left join ' . tablename('sz_yi_goods') . ' as a2 on a1.goodsid=a2.id where a1.orderid=' . $oid['id'] . ' and a2.directType=1');
    $account_api = WeAccount::create();
    $fans_info = $account_api->fansQueryInfo($_W['openid']);
    $uid = pdo_get('member', ['unionid' => $fans_info['unionid']], ['id']);
    $recipient = pdo_fetchall("select id,name from " . tablename('member_family') . " where uid={$uid['id']}");//获取收件人
    include $this->template("order/easy_deliver_locallypack");
}

