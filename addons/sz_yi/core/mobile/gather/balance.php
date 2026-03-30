<?php
/**
  集运余额充值
 **/
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$shopset = m('common')->getSysset('shop');
$set = m('common')->getSysset(array('pay'));
$openid = $_W['openid'];
$time = TIMESTAMP;
$data = $_GPC;

if($op=='display'){
    #1、判断是否其他充值
    $balance_order = pdo_fetch('select * from '.tablename('centralize_balance_order').' where id=:id',[':id'=>$data['id']]);
    $status='';
    if($balance_order['status']==0){
        $status='未支付';
    }elseif($balance_order['status']==1){
        $status='已支付';
    }
    $user = pdo_fetch('select * from '.tablename('website_user').' where id=:uid',[':uid'=>intval($balance_order['uid'])]);
    if(empty($user['openid'])){
        pdo_update('website_user',['openid'=>$openid],['id'=>intval($balance_order['uid'])]);
    }
    $balance_list = pdo_fetch('select * from '.tablename('centralize_balance_list').' where id=:id',[':id'=>$balance_order['balance_id']]);

    include $this->template('gather/balance');
}
elseif($op=='pay'){
    //获取支付信息
    //支付通道
    $member = pdo_fetch('select * from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>trim($_W['openid'])]);

    $member['openid'] = $openid;
    $uniacid = $_W['uniacid'];
    $orderid = intval($_GPC['orderid']);
    $user = pdo_fetch('select * from '.tablename('website_user').' where openid=:openid',[':openid'=>$openid]);
    $ordersn_general = pdo_fetchcolumn('select ordersn from ' . tablename('centralize_balance_order') . ' where id=:id and uid=:uid limit 1', array(':id' => $orderid,':uid'=>$user['id']));
    if(empty($ordersn_general)){
        //其他人进来查看
        show_json(1,'');
    }
    $order = pdo_fetch('select * from ' . tablename('centralize_balance_order') . ' where ordersn=:ordersn', array(':ordersn' => $ordersn_general));


    if (empty($orderid)) {
        show_json(0, '参数错误!');
    }

    if (empty($order)) {
        show_json(0, '订单未找到!');
    }

    if ($order['status'] == -1) {
        show_json(-1, '订单已关闭, 无法付款!');
    }
    else {
        if (1 == $order['status'] && $order['status'] == 3) {
//			show_json(-1, '订单已付款, 无需重复支付!');
        }
    }

    $log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'sz_yi', ':tid' => $ordersn_general));

    if (!empty($log) && ($log['status'] != '0')) {
        show_json(-1, '订单已支付, 无需重复支付!');
    }

    if (!empty($log) && ($log['status'] == '0')) {
        pdo_delete('core_paylog', array('plid' => $log['plid']));
        $log = NULL;
    }

    if (empty($log)) {
        $log = array('uniacid' => $uniacid, 'openid' => $member['openid'], 'module' => 'sz_yi', 'tid' => $ordersn_general, 'fee' => $order['service_price'], 'status' => 0);
        pdo_insert('core_paylog', $log);
        $plid = pdo_insertid();
    }
    $set = m('common')->getSysset(array('shop', 'pay'));

    //2017-11-16 通莞微信支付
    $tgwechat = array('success' => false);
    $tgalipay = array('success' => false);

    if (($set['pay']['tgpaystatus']) && !empty($set['pay']['tgpay'])) {
        $tgwechat['success'] = true;
        $tgalipay['success'] = true;
    }
    load()->model('payment');
    $setting = uni_setting($_W['uniacid'], array('payment'));
    $wechat = array('success' => false, 'qrcode' => false);
    $jie = $set['pay']['weixin_jie'];

    if (is_weixin()) {
        if (isset($set['pay']) && ($set['pay']['weixin'] == 1) && ($jie != 1)) {
            if (is_array($setting['payment']['wechat']) && $setting['payment']['wechat']['switch']) {
                $wechat['success'] = true;
                $wechat['weixin'] = true;
                $wechat['weixin_jie'] = false;
            }
        }
    }

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
        if ((isset($set['pay']) && ($set['pay']['weixin_jie'] == 1) && !$wechat['success']) || ($jie == 1)) {
            $wechat['success'] = true;
            $wechat['weixin_jie'] = true;
            $wechat['weixin'] = false;
        }
    }

    $wechat['jie'] = $jie;
    if (!isMobile() && isset($set['pay']) && ($set['pay']['weixin'] == 1)) {
        if (isset($set['pay']) && ($set['pay']['weixin'] == 1)) {
            if (is_array($setting['payment']['wechat']) && $setting['payment']['wechat']['switch']) {
                $wechat['qrcode'] = true;
            }
        }
    }

    $returnurl = '';
    show_json(1, array('order' => $order, 'set' => $set, 'wechat' => $wechat, 'tgwechat' => $tgwechat, 'tgalipay' => $tgalipay, 'isweixin' => is_weixin(), 'returnurl' => $returnurl));
}
elseif($op=='tgpay'){
    //发起支付
    $type = trim($_GPC['type']);
    if(!in_array($type, array('weixin', 'alipay', 'app_alipay', 'app_weixin', 'unionpay', 'yunpay', 'yeepay', 'paypal', 'yeepay_wy','tgwechat','tgalipay','helpay'))) {
        show_json(0, '未找到支付方式');
    }
    $set = m('common')->getSysset(array('shop', 'pay'));
    $order = pdo_fetch('select * from '.tablename('centralize_balance_order').' where id=:id',array(':id'=>intval($_GPC['orderid'])));
    $fee = pdo_fetch('select * from '.tablename('centralize_balance_list').' where id=:id',[':id'=>$order['balance_id']]);
    if(empty($order)){
        show_json(0,'参数错误');
    }

    if($type == 'tgwechat'){
        if (empty($set['pay']['tgpay'])) {
            show_json(0, '未开启通莞微信支付!');
        }

        $tgwechat = array('success' => false);
        $params = array();
        $params['tid'] = $order['ordersn'];
        $params['openid']  = $openid;

        $params['fee']   = $fee['recharge_money'];
        $params['title'] = '余额充值';
        load()->model('payment');

        if (is_array($set['pay']['tgpay'])) {
            $payment = 	$set['pay']['tgpay'];
            if (is_weixin()) {

                if ($set['pay']['tgpaystatus']) {

                    $params['account']  = $options['account'] = $payment['mchid'];
                    $params['key'] 		= $options['key'] 	  = $payment['key'];
                    $params['token']	= 'wechat';

                    $tgwechat['tid']	= $params['tid'];
                    $tgwechat['openid']	= $params['openid'];
                    $tgwechat['fee']	= $params['fee'];
                    $tgwechat['title']	= $params['title'];
                    $tgwechat['account']= $params['account'];
                    $tgwechat['key']	= $params['key'];
                    $tgwechat['token']	= $params['token'];
                    $tgwechat['uniacid']= $_W['uniacid'];
                    $tgwechat['success'] = true;

                    if(empty($tgwechat)){
                        show_json(0, '数据为空！');
                    }

                }else {
                    $tgwechat['success'] = FALSE;
                }
            }

            if (!$tgwechat['success']) {
                show_json(0, '微信支付参数错误!');
            }
            //2022-01-03 记录支付总金额和逾期金额
            pdo_update('centralize_balance_order',['type'=>1],['id'=>$order['id']]);
            show_json(1, array('tgwechat' => $tgwechat));
        }
    }
    elseif($type == 'tgalipay'){
        $param_title = $shopset['name'] . '订单:' . $order['ordersn'];

        $tgalipay = array('success' => false);
        $params = array();
        $params['tid'] = $order['ordersn'];
        $params['user'] = $openid;

        $params['fee'] = $fee['recharge_money'];
        $params['title'] = '余额充值';
        $params['typ'] = 'gatherbalancepayment';
        load()->func('communication');
        //记录支付总金额和逾期金额
        pdo_update('centralize_balance_order',['paytype'=>2],['id'=>$order['id']]);

        if (!empty($set['pay']['tgpay']) && ($set['pay']['tgpaystatus'] == 1)) {
            $options = $set['pay']['tgpay'];
            //获取该公众号配置
            $config = array(
                'mchid'=>$options['mchid'],
                'key'=>$options['key'],
            );

            $tgalipayRes = m('common')->Tgalipay_scode($params, $config);
            //"下游订单号重复"
            if ($tgalipayRes['status'] == '100') {
                header("Location:".$tgalipayRes['codeUrl']);
                exit('正在跳转支付宝支付...');
            }else if ($tgalipayRes['status'] == '101') {

                $params['tid'] = 'G' . date('YmdHis', time());//重新生成订单
                //更新订单数据
                $result = pdo_update('centralize_balance_order', ['ordersn' => $params['tid']], array('id' => $order['id']));
                pdo_update('core_paylog',['tid'=>$params['tid']],array('tid'=>$order['ordersn']));
                if (!empty($result)) {
                    $tgalipayRes = m('common')->Tgalipay_scode($params, $config);
                    if ($tgalipayRes['status'] == '100') {
                        header("Location:".$tgalipayRes['codeUrl']);
                        exit('正在跳转支付宝支付...');
                    }else {
                        $tgalipay['success'] = FALSE;
                        $tgalipay['message'] = $tgalipayRes['message'];

                        exit($tgalipayRes['message']);
                    }
                }
            }
        }
    }
}