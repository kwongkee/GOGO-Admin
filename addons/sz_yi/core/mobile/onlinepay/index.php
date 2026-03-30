<?php

// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$op = !empty($_GPC['op']) ? trim($_GPC['op']) : 'index';
$openid = $_W['openid'];
$data = $_GPC;
$user = pdo_fetch('select * from '.tablename('website_user').' where openid=:openid',[':openid'=>$_W['openid']]);

if ($op == 'index') {
    //方块选择
    $uid = base64_decode($_GPC['uid']);

    if(empty($user['openid'])){
        pdo_update('website_user',['openid'=>$_W['openid']],['id'=>$uid]);
    }
    include $this->template('onlinepay/index');
}
elseif($op == 'status1'){
    #已通知待确认
    if(isset($data['pa'])){
        if($data['pa']==1){
            $limit = intval($data['limit']);
            $page = (intval($data['page'])-1) * $limit;
            $offset = ' limit '.$page.','.$limit;
            $count = pdo_fetchcolumn('select count(id) from '.tablename('website_collect').' where pay_userid=:user_id and status=1',[':user_id'=>$user['id']]);
            $list = pdo_fetchall('select * from '.tablename('website_collect').' where pay_userid=:user_id and status=1 order by id desc '.$offset,[':user_id'=>$user['id']]);
            $status = [1=>'已发起已通知',2=>'已确认未付款',3=>'已付款已提交',4=>'已收款已确认'];
            foreach($list as $k=>$v){
                $list[$k]['statusname'] = $status[$v['status']];
                $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
        }elseif($data['pa']==2){
            $res = pdo_update('website_collect',['status'=>2],['id'=>$data['id']]);
            if($res){
                show_json(0,['msg'=>'操作成功']);
            }
        }
    }
    include $this->template('onlinepay/status1');
}
elseif($op == 'status2'){
    #已确认待付款
    if(isset($data['pa'])){
        if($data['pa']==1){
            $limit = intval($data['limit']);
            $page = (intval($data['page'])-1) * $limit;
            $offset = ' limit '.$page.','.$limit;
            $count = pdo_fetchcolumn('select count(id) from '.tablename('website_collect').' where pay_userid=:user_id and status=2',[':user_id'=>$user['id']]);
            $list = pdo_fetchall('select * from '.tablename('website_collect').' where pay_userid=:user_id and status=2 order by id desc '.$offset,[':user_id'=>$user['id']]);
            $status = [1=>'已发起已通知',2=>'已确认未付款',3=>'已付款已提交',4=>'已收款已确认'];
            foreach($list as $k=>$v){
                $list[$k]['statusname'] = $status[$v['status']];
                $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
        }
    }
    include $this->template('onlinepay/status2');
}
elseif($op == 'status3'){
    #已付款待确认
    include $this->template('onlinepay/status3');
}
elseif($op == 'status3_1'){
    #已付款待确认-已提交未确认
    if(isset($data['pa'])){
        if($data['pa']==1){
            $limit = intval($data['limit']);
            $page = (intval($data['page'])-1) * $limit;
            $offset = ' limit '.$page.','.$limit;
            $count = pdo_fetchcolumn('select count(id) from '.tablename('website_collect').' where pay_userid=:user_id and status=3',[':user_id'=>$user['id']]);
            $list = pdo_fetchall('select * from '.tablename('website_collect').' where pay_userid=:user_id and status=3 order by id desc '.$offset,[':user_id'=>$user['id']]);
            $status = [1=>'已发起已通知',2=>'已确认未付款',3=>'已付款已提交',4=>'已收款已确认'];
            foreach($list as $k=>$v){
                $list[$k]['statusname'] = $status[$v['status']];
                $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
        }
    }
    include $this->template('onlinepay/status3_1');
}
elseif($op == 'status3_2'){
    #已付款待确认-已提交已确认
    if(isset($data['pa'])){
        if($data['pa']==1){
            $limit = intval($data['limit']);
            $page = (intval($data['page'])-1) * $limit;
            $offset = ' limit '.$page.','.$limit;
            $count = pdo_fetchcolumn('select count(id) from '.tablename('website_collect').' where pay_userid=:user_id and status=4',[':user_id'=>$user['id']]);
            $list = pdo_fetchall('select * from '.tablename('website_collect').' where pay_userid=:user_id and status=4 order by id desc '.$offset,[':user_id'=>$user['id']]);
            $status = [1=>'收款人确认款项未到账',2=>'收款人确认到账未足额',3=>'收款人确认到账已足额'];
            foreach($list as $k=>$v){
                $list[$k]['statusname'] = $status[$v['status2']];
                $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
        }
    }
    include $this->template('onlinepay/status3_2');
}
elseif($op=='order_detail'){
    $orderid = intval($data['oid']);
    $order = pdo_fetch('select * from '.tablename('website_collect').' where id=:orderid and pay_userid=:uid',[':orderid'=>$orderid,':uid'=>$user['id']]);
    if($order['order_type']==1){
        #订单
        $order['order_detail'] = pdo_fetch('select * from '.tablename('website_order').' where ordersn_flow=:ordersn',[':ordersn'=>$order['ordersn']]);
    }elseif($order['order_type']==2){
        #账单
        $order['order_detail'] = pdo_fetch('select * from '.tablename('website_bill').' where ordersn_flow=:ordersn',[':ordersn'=>$order['ordersn']]);
        $order['order_detail']['fee_content'] = json_decode($order['order_detail']['fee_content'],true);
    }

    if($order['type']==1){
        #自有账号
        $order['account_info'] = pdo_fetch('select * from '.tablename('website_account').' where id=:acid',[':acid'=>$order['account_id']]);
        if($order['type2']==1){
            $order['account_info']['name'] = $order['account_info']['bank_name'];
            $order['account_info']['bank_name'] = pdo_fetchcolumn('select bank_name from '.tablename('bank_list').' where id=:id',[':id'=>$order['account_info']['bank_id']]);
        }
    }elseif($order['type']==2){
        #购购银行账号
        if($order['type3']==1){
            $order['account_info'] = pdo_fetch('select * from '.tablename('onshore_account').' where id=:acid',[':acid'=>$order['account_id']]);
        }
    }

    if(!empty($order['payinfo'])){
        $order['payinfo'] = json_decode($order['payinfo'],true);
    }
    $status = [1=>'收款人已发起已通知',2=>'付款人已确认未付款',3=>'付款人已付款已提交',4=>'收款人已收款已确认'];
    $order['statusname'] = $status[$order['status']];

    if($order['status']==4){
        $status2 = [1=>'收款人确认款项未到账',2=>'收款人确认到账未足额',3=>'收款人确认到账已足额'];
        $order['statusname2'] = $status2[$order['status2']];
    }

    include $this->template('onlinepay/order_detail');
}
elseif($op=='sure_pay'){
    $orderid = intval($data['oid']);
    $order = pdo_fetch('select * from '.tablename('website_collect').' where id=:orderid and pay_userid=:uid',[':orderid'=>$orderid,':uid'=>$user['id']]);
    if(empty($data['pic_file'])){
        show_json(-1,['msg'=>'请上传付款记录']);
    }
    $datas = [
        'totalmoney'=>trim($data['payinfo']['totalmoney']),
        'transfer_date'=>isset($data['payinfo']['transfer_date'])?trim($data['payinfo']['transfer_date']):'',
        'pay_platform'=>isset($data['payinfo']['pay_platform'])?trim($data['payinfo']['pay_platform']):'',
        'pic_file'=>$data['pic_file'],
    ];

    $status2 = 0;
    if($order['totalmoney']<$datas['totalmoney']){
        $status2 = 2;
    }else{
        $status2 = 3;
    }

    $res = pdo_update('website_collect',['payinfo'=>json_encode($datas,true),'status'=>3,'status2'=>$status2],['id'=>$orderid,'pay_userid'=>$user['id']]);
    if($res){
        show_json(0,['msg'=>'提交成功']);
    }
}
elseif($op=='pay'){
    //支付通道
    $member = m('member')->getMember(trim($_W['openid']));
    $member['openid'] = $_W['openid'];
    $uniacid = $_W['uniacid'];
    $orderid = intval($_GPC['orderid']);
    $ordersn_general = pdo_fetchcolumn('select ordersn_flow from ' . tablename('website_collect') . ' where id=:id limit 1', array(':id' => $orderid));
    if(empty($ordersn_general)){
        $ordersn_general = pdo_fetchcolumn('select ordersn_flow from ' . tablename('website_collect') . ' where id=:id limit 1', array(':id' => $orderid));
    }
    if(empty($ordersn_general)){
        //其他人进来查看
        show_json(1,'');
    }
    $order = pdo_fetch('select * from ' . tablename('website_collect') . ' where ordersn_flow=:ordersn', array(':ordersn' => $ordersn_general));

    if(empty($order)){
        $order = pdo_fetch('select * from ' . tablename('website_collect') . ' where ordersn_flow=:ordersn', array(':ordersn' => $ordersn_general));
    }

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
        if (1 <= $order['status']) {
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
        $log = array('uniacid' => $uniacid, 'openid' => $member['openid'], 'module' => 'sz_yi', 'tid' => $ordersn_general, 'fee' => $order['totalmoney'], 'status' => 0);
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
    $type = trim($_GPC['type']);
    if(!in_array($type, array('weixin', 'alipay', 'app_alipay', 'app_weixin', 'unionpay', 'yunpay', 'yeepay', 'paypal', 'yeepay_wy','tgwechat','tgalipay','helpay'))) {
        show_json(0, '未找到支付方式');
    }
    $set = m('common')->getSysset(array('shop', 'pay'));
    $order = pdo_fetch('select * from '.tablename('website_collect').' where id=:id',array(':id'=>intval($_GPC['orderid'])));
    if(empty($order)){
        $member = m('member')->getMember(trim($_GPC['openid']));
        $order = pdo_fetch('select * from '.tablename('website_collect').' where id=:id',array(':id'=>intval($_GPC['orderid'])));
    }
    if(empty($order)){
        show_json(0,'参数错误');
    }

    $name = '';
    if($order['order_type']==1){
        $name = '订单信息服务';
    }elseif($order['order_type']==2){
        $name = '账单信息服务';
    }

    if($type == 'tgwechat'){
        if (empty($set['pay']['tgpay'])) {
            show_json(0, '未开启通莞微信支付!');
        }

        $tgwechat = array('success' => false);
        $params = array();
        $params['tid'] = $order['ordersn_flow'];
        $params['openid']  = $_W['openid'];

        $params['fee']   = $order['totalmoney'];
        $params['title'] = $name;
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
//            pdo_query('update '.tablename('customs_collection').' set pay_type=1,overdue_money='.$_GPC['overdue_money'].',total_money='.$params['fee'].' where id=:id and uniacid=3',array(':id'=>$order['id']));
            show_json(1, array('tgwechat' => $tgwechat));
        }

    }
    elseif($type == 'tgalipay'){
        $shopset = m('common')->getSysset('shop');
        $param_title = $shopset['name'] . '订单:' . $order['ordersn_flow'];

        $tgalipay = array('success' => false);
        $params = array();
        $params['tid'] = $order['ordersn_flow'];
        $params['user'] = $_W['openid'];
        $params['fee'] = $order['totalmoney'];
        $params['title'] = $name;
        $params['typ'] = 'onlinepayment';
        load()->func('communication');
        //记录支付总金额和逾期金额
//        pdo_query('update '.tablename('customs_collection').' set pay_type=2,overdue_money='.$_GPC['overdue_money'].',total_money='.$params['fee'].' where id=:id and uniacid=3',array(':id'=>$order['id']));

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

                $params['tid'] = 'GP' . date('YmdH', time()) . str_pad(mt_rand(1, 999999), 6, '0',
                        STR_PAD_LEFT) . substr(microtime(), 2, 6);//重新生成订单
                //更新订单数据
                $result = pdo_update('website_collect', ['ordersn_flow' => $params['tid']], array('id' => $order['id']));
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