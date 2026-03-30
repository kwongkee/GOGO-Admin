<?php
/**
    集运结算操作订单
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
    #1、查找支付订单
    $order = pdo_fetch('select * from '.tablename('centralize_order_fee_log').' where id=:id',[':id'=>intval($data['id'])]);
    $order['service_statusname'] = pdo_fetchcolumn('select status_name from '.tablename('centralize_parcel_operation_status').' where status_id=:status_id',[':status_id'=>$order['service_status']]);
    $order['order_statusname'] = pdo_fetchcolumn('select status_name from '.tablename('centralize_parcel_status').' where status_id=:status_id',[':status_id'=>$order['order_status']]);
    $user = pdo_fetch('select * from '.tablename('website_user').' where id=:uid',[':uid'=>intval($data['uid'])]);
    if(empty($user['openid']) && !empty($data['uid'])){
        pdo_update('website_user',['openid'=>$openid],['id'=>intval($data['uid'])]);
    }
    elseif(empty($data['uid'])){
        $user = pdo_fetch('select * from '.tablename('website_user').' where id=:uid',[':uid'=>intval($order['user_id'])]);
    }

    #2、判断是否待付款的订单
    $pay_status=1;#已付款
    if($order['order_status']==1 || $order['order_status']==4 || $order['order_status']==7 || $order['order_status']==12 || $order['order_status']==17 || $order['order_status']==22 || $order['order_status']==27 || $order['order_status']==32 || $order['order_status']==36 || $order['order_status']==40 || $order['order_status']==44 || $order['order_status']==48 || $order['order_status']==52 || $order['order_status']==56 || $order['order_status']==67 ){
        $pay_status=0;
    }
//    $a = json_decode('{"upOrderId":"91706224569486020608","payoffType":null,"payTime":"2023-09-25 16:30:21","openid":"ov3-bt5vIxepEjWc51zRQNQbFSaQ","sign":"EE50805259D922AF402F525FD3E36E4F","settlementChannel":"038","lowOrderId":"G20230922153317","payMoney":"0.01","merchantId":"617112400019774","state":"0","orderDesc":"支付成功","account":"101540254006","channelId":"WX"}',true);
//    $aa = '';
//    foreach($a as $k=>$v){
//        $aa .= $k.'='.$v.'&';
//    }
//    dd($order);
    include $this->template('gather/pay');
}
elseif($op=='balancepay'){
    #余额支付
    $user = pdo_fetch('select * from '.tablename('website_user').' where openid=:openid',[':openid'=>$openid]);
    $order = pdo_fetch('select * from '.tablename('centralize_order_fee_log').' where id=:id',[':id'=>intval($data['orderid'])]);
    if($user['balance']<$order['service_price']){
        show_json(-1,['msg'=>'支付失败，您的余额不足']);
    }else{
        $after_balance = floatval($user['balance']) - floatval($order['service_price']);
        $time = time();
        #插入余额使用表
        pdo_insert('centralize_balance_use_log',[
            'uid'=>$user['id'],
            'orderid'=>$order['id'],
            'balance'=>$order['service_price'],
            'after_balance'=>$after_balance,
            'createtime'=>$time
        ]);
        #更改用户余额
        pdo_update('website_user',['balance'=>$after_balance],['id'=>$user['id']]);
        #更改订单状态和包裹状态
        pdo_update('centralize_order_fee_log',['order_status'=>intval($order['order_status'])+1,'paytime'=>$time],['id'=>$order['id']]);
        $parcel = pdo_fetch('select * from '.tablename('centralize_parcel_order_package').' where express_no=:express_no',[':express_no'=>$order['express_no']]);
        pdo_update('centralize_parcel_order_package',['status2'=>intval($order['order_status'])+1],['id'=>$parcel['id']]);

//        $notice = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
        $servicers = pdo_fetchall('select * from '.tablename('centralize_system_servicer').' where status=1');
        foreach($servicers as $k=>$v) {
            $muser = pdo_fetch('select * from ' . tablename('website_user') . ' where id=:id', [':id' => $v['uid']]);
            if(!empty($muser['openid'])){
                //step1:发送消息给发起付款的人
                $post = json_encode([
                    'call'=>'collectionNotice',
                    'first'=>'您有一笔收款信息',
                    'keyword1'=>$user['realname'],
                    'keyword2'=>'CNY '.$order['service_price'],
                    'keyword3'=>'余额支付',
                    'keyword4'=>date('Y-m-d H:i:s',time()),
                    'keyword5'=>$order['ordersn'],
                    'remark' =>'感谢您的使用',
                    'openid' =>$muser['openid'],
                    'uniacid'=>3,
                    'temp_id'=>'WcvDClChgUbLfWHu5jQw5TEilYU36VdNDH514KZ-f4w',
                    'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=gather&p=pay&m=sz_yi&id='.$order['id'].'&isadmin=1'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }
        }

        //step2:成功支付后，发送消息给本人
        $type = pdo_fetchcolumn('select status_name from '.tablename('centralize_parcel_operation_status').' where status_id=:status_id',[':status_id'=>$order['service_status']]);;
        $post2 = json_encode([
            'call'=>'collectionNotice',
            'first'=>'您好，您已经完成订单的处理，点击查看详情，如有疑问，敬请联系客服075786329911，感谢您的支持！',
            'keyword1'=>$order['ordersn'],
            'keyword2'=>$type,
            'keyword3'=>'CNY '.$order['service_price'],
            'keyword4'=>'余额支付',
            'keyword5'=>date('Y-m-d H:i:s',time()),
            'remark' =>'感谢您的使用',
            'openid' =>$user['openid'],
            'uniacid'=>3,
            'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
            'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=gather&p=pay&m=sz_yi&id='.$order['id']
        ]);
        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

        //发送工单信息
        $taskid = pdo_fetch('select id from '.tablename('centralize_task').' where order_id=:orderid and package_id=:package_id order by id desc',[':orderid'=>$order['orderid'],':package_id'=>$order['good_id']]);
        if(empty($taskid)){
            $taskid = pdo_fetch('select id from '.tablename('centralize_task').' where order_id=:orderid order by id desc',[':orderid'=>$order['orderid']]);
        }
        $task_name='确认结算订单';
        pdo_insert('centralize_workorder',[
            'user_id'=>$user['id'],
            'pid'=>$taskid['id'],
            'type'=>2,
            'workorder_number'=>'MC'.date('ymdHis',time()).'01',
            'event_name'=>$task_name,
            'status'=>0,
            'createtime'=>time()
        ]);

        show_json(0,['msg'=>'支付成功']);
    }
}
elseif($op=='delaypay'){
    #赊账条件
    if($data['pa']==1){
        #补充个人信息
        if(!empty($data['phone'])){
            if(strlen($data['phone']) != 11){
                show_json(-1,['msg'=>'请输入正确的手机号']);
            }
            pdo_update('website_user',['phone'=>trim($data['phone'])],['openid'=>$openid]);
        }
        if(!empty($data['email'])){
            if(!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+.[a-zA-Z]{2,}$/',trim($data['phone']))){
                show_json(-1,['msg'=>'请输入正确的邮箱号']);
            }
            pdo_update('website_user',['email'=>trim($data['email'])],['openid'=>$openid]);
        }
        if(!empty($data['realname'])){
            pdo_update('website_user',['realname'=>trim($data['realname'])],['openid'=>$openid]);
        }
        if(!empty($data['idcard'])){
            if(!preg_match('/^([1-9]\d{5})(19\d{2}|20\d{2})(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i',trim($data['idcard']))){
                show_json(-1,['msg'=>'请输入正确的身份证号']);
            }
            pdo_update('website_user',['idcard'=>trim($data['idcard'])],['openid'=>$openid]);
        }
        show_json(0,['msg'=>'提交联系信息成功']);
    }elseif ($data['pa']==2){
        #实名认证接口

    }elseif($data['pa']==3){
        /**
        赊账条件判断
        -- 总后台（GOGO）接收申请，并可”确认“或”拒绝“赊账：
        --- 确认赊账，默认"N天"后，系统每天消息、短信、电邮”催款“
        --- 系统发送消息第三天起，状态变为”已逾期“
        --- 拒绝赊账，默认订单生成，且系当天起每天消息、短信、电邮”催款“
        -- 系统每天汇总”正在赊账“、”已经逾期“的订单清单，并每天消息GOGO
        1. 有完善联系（手机、邮箱、关注公众号）
        2. 必须实名认证
        3. 必须已有历史订单
         */
        $user = pdo_fetch('select * from '.tablename('website_user').' where openid=:openid',[':openid'=>$openid]);
        if(empty($user['email']) || empty($user['phone']) || empty($user['realname']) || empty($user['idcard'])){
            show_json(-1,['msg'=>'请先完善联系信息']);
        }
        if(empty($user['is_verify'])){
            show_json(-2,['msg'=>'请先进行实名认证']);
        }
        $history = pdo_fetch('select id from '.tablename('centralize_order_fee_log').' where user_id=:uid and status=3 and service_price>0',[':uid'=>$user['id']]);
        if(empty($history)){
            show_json(-3,['msg'=>'暂无历史订单，申请赊账失败。']);
        }
        #确定赊账，并生成赊账记录
        $res = pdo_update('centralize_order_fee_log',['check_status'=>1],['id'=>$data['orderid']]);
        if($res){
//            $notice = pdo_fetch('select * from '.tablename('centralize_system_notice').' where uid=0');
            $servicers = pdo_fetchall('select * from '.tablename('centralize_system_servicer').' where status=1');
            foreach($servicers as $k=>$v) {
                $muser = pdo_fetch('select * from ' . tablename('website_user') . ' where id=:id', [':id' => $v['uid']]);
                if (!empty($muser['openid'])) {
                    //step1:发送消息给发起付款的人
                    $post = json_encode([
                        'call' => 'collectionNotice',
                        'first' => '您有一笔赊账申请信息',
                        'keyword1' => $user['realname'],
                        'keyword2' => 'CNY ' . $order['service_price'],
                        'keyword3' => '赊账支付',
                        'keyword4' => date('Y-m-d H:i:s', time()),
                        'keyword5' => $order['ordersn'],
                        'remark' => '请登录总后台审批',
                        'openid' => $muser['openid'],
                        'uniacid' => 3,
                        'temp_id' => 'WcvDClChgUbLfWHu5jQw5TEilYU36VdNDH514KZ-f4w',
                        'url' => 'https://gadmin.gogo198.cn'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }
            }
        }
        show_json(0,['msg'=>'已提交赊账申请']);
    }
}
elseif($op=='pay'){
    //获取支付信息
    //支付通道
    $member = pdo_fetch('select * from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>trim($_W['openid'])]);
    $member['openid'] = $openid;
    $uniacid = $_W['uniacid'];
    $orderid = intval($_GPC['orderid']);
    $user = pdo_fetch('select * from '.tablename('website_user').' where openid=:openid',[':openid'=>$openid]);
    $ordersn_general = pdo_fetchcolumn('select ordersn from ' . tablename('centralize_order_fee_log') . ' where id=:id and user_id=:uid limit 1', array(':id' => $orderid,':uid'=>$user['id']));
    if(empty($ordersn_general)){
        #2024/08/19，测试临时加上
        $ordersn_general = pdo_fetchcolumn('select ordersn from ' . tablename('centralize_order_fee_log') . ' where id=:id and user_id=:uid limit 1', array(':id' => $orderid,':uid'=>17));
    }

    if(empty($ordersn_general)){
        //其他人进来查看
        show_json(1,'');
    }
    $order = pdo_fetch('select * from ' . tablename('centralize_order_fee_log') . ' where ordersn=:ordersn', array(':ordersn' => $ordersn_general));


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
    $order = pdo_fetch('select * from '.tablename('centralize_order_fee_log').' where id=:id',array(':id'=>intval($_GPC['orderid'])));

    if(empty($order)){
        show_json(0,'参数错误');
    }
    $name = pdo_fetchcolumn('select status_name from '.tablename('centralize_parcel_operation_status').' where status_id=:service_status',[':service_status'=>$order['service_status']]);

    if($type == 'tgwechat'){
        if (empty($set['pay']['tgpay'])) {
            show_json(0, '未开启通莞微信支付!');
        }

        $tgwechat = array('success' => false);
        $params = array();
        $params['tid'] = $order['ordersn'];
        $params['openid']  = $openid;

        $params['fee']   = $order['service_price'];
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
            pdo_update('centralize_order_fee_log',['paytype'=>2],['id'=>$order['id']]);
//            pdo_query('update '.tablename('customs_collection').' set pay_type=1,overdue_money='.$_GPC['overdue_money'].',total_money='.$params['fee'].' where id=:id and uniacid=3',array(':id'=>$order['id']));
            show_json(1, array('tgwechat' => $tgwechat));
        }

    }
    elseif($type == 'tgalipay'){
        $param_title = $shopset['name'] . '订单:' . $order['ordersn'];

        $tgalipay = array('success' => false);
        $params = array();
        $params['tid'] = $order['ordersn'];
        $params['user'] = $openid;

        $params['fee'] = $order['service_price'];
        $params['title'] = $name;
        $params['typ'] = 'gatherpayment';
        load()->func('communication');
        //记录支付总金额和逾期金额
        pdo_update('centralize_order_fee_log',['paytype'=>3],['id'=>$order['id']]);
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

                $params['tid'] = 'G' . date('YmdHis', time());//重新生成订单
                //更新订单数据
                $result = pdo_update('centralize_order_fee_log', ['ordersn' => $params['tid']], array('id' => $order['id']));
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