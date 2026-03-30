<?php
// 模块LTD提供

ini_set('display_errors', 'On');
//define('IN_MOBILE', true);
//error_reporting(30719 ^ 8);
//global $_W;
//global $_GPC;

require '../../../../framework/bootstrap.inc.php';
require '../../../../addons/sz_yi/defines.php';
require '../../../../addons/sz_yi/core/inc/functions.php';
require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';

//载入日志函数
//获取文件流
$input = file_get_contents('php://input');
//写入日志
file_put_contents('./log/onlinenotify.log', $input."\r\n",FILE_APPEND);
//将接受到的Json数据转换成数组格式。
$data = json_decode($input, true);
//echo $_W['siteroot'] . 'addons/sz_yi/payment/tgwechat/notify.log';
if (!empty($data)) {

    $order = pdo_fetch('select * from ' . tablename('website_collect') . ' where ordersn_flow=:ordersn limit 1', array(':ordersn' => $data['lowOrderId']));
    if(empty($order)){
        $answer['finished'] = 'FAIL';
        echo json_encode($answer);die;
    }
    $data['uniacid'] = $_W['uniacid'];//订单所属公众号

    $setting = uni_setting($_W['uniacid'], array('payment'));

    $answer = array(
        'lowOrderId'=> $data['lowOrderId'],//下游系统流水号，必须唯一
        'merchantId'=> $data['merchantId'],//商户进件账号
        'upOrderId'=>  $data['upOrderId'],//上游流水号
    );

    if ($data['state'] == '0' && $data['orderDesc'] == '支付成功') {
        //是否接收到回调  SUCCESS表示成功
        //付款成功修改订单表中sz_yi_order数据  状态：status = 1
        if ($order['status'] == 2) {
            m('common')->paylog($data);
            m('common')->paylog('status');
            load()->func('communication');

            $zf_type='';
            if($data['channelId']=='WX'){
                $zf_type = '微信';
            }elseif($data['channelId']=='ZFB'){
                $zf_type = '支付宝';
            }

            $payinfo = [
                'totalmoney'=>trim($data['payMoney']),
                'transfer_date'=>$data['payTime'],
                'pay_platform'=>$zf_type,
                'pic_file'=>'',
                'ordersn_general'=>$data['upOrderId'],
            ];

            pdo_update('website_collect', array(
                'status' => '3',
                'status2' => '3',
                'payinfo'=>json_encode($payinfo,true),
            ), array('id' => $order['id']));

            $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid  limit 1';
            $params = array();
            $params[':tid'] = $data['lowOrderId'];
            $params[':module'] = 'sz_yi';
            //查找core_paylog中的数据
            $log = pdo_fetch($sql, $params);
            $record = array();
            $record['status'] = '1';
            pdo_update('core_paylog', $record, array('plid' => $log['plid']));

            $name = '';
            if($order['order_type']==1){
                $name = '订单信息服务';
            }elseif($order['order_type']==2){
                $name = '账单信息服务';
            }
//            $order['total_money'] = $order['trade_price']+$order['overdue_money'];
            //step1:发送消息给发起付款的人
//            $post = json_encode([
//                'call'=>'collectionNotice',
//                'first'=>'您有一笔收款信息',
//                'keyword1'=>'ceshi',
//                'keyword2'=>'CNY '.$order['totalmoney'],
//                'keyword3'=>$zf_type,
//                'keyword4'=>date('Y-m-d H:i:s',time()),
//                'keyword5'=>$order['ordersn_flow'],
//                'remark' =>'感谢您的使用',
//                'openid' =>$_W['openid'],
//                'uniacid'=>$_W['uniacid'],
//                'temp_id'=>'WcvDClChgUbLfWHu5jQw5TEilYU36VdNDH514KZ-f4w',
//                'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id'].'&isadmin=1'
//            ]);
//            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

            //step2:成功支付后，发送消息给本人
//            $type = '';
//            if($order['trade_type']==1){
//                $type='商品';
//            }elseif($order['trade_type']==2){
//                $type='项目';
//            }elseif($order['trade_type']==3){
//                $type='多项服务';
//            }
//            if(is_numeric($order['openid'])){
//                $order['openid'] = pdo_fetchcolumn('select openid from '.tablename('sz_yi_member').' where mobile=:mob',[':mob'=>$order['openid']]);
//            }
//            $post2 = json_encode([
//                'call'=>'collectionNotice',
//                'first'=>'您好，您已经完成新订单的处理，点击查看详情，如有疑问，敬请联系客服075786329911，感谢您的支持！',
//                'keyword1'=>$order['ordersn'],
//                'keyword2'=>$type,
//                'keyword3'=>'CNY '.$order['total_money'],
//                'keyword4'=>$zf_type,
//                'keyword5'=>date('Y-m-d H:i:s',time()),
//                'remark' =>'感谢您的使用',
//                'openid' =>$order['openid'],
//                'uniacid'=>$order['uniacid'],
//                'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
//                'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id']
//            ]);
//            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

            //step3:成功支付后，发送消息给管理员（老板）
//            $post3 = json_encode([
//                'call'=>'collectionNotice',
//                'first'=>'您好，有［'.$type.'］订单状态已变更为［订单已付］，点击查看详情！',
//                'keyword1'=>$order['ordersn'],
//                'keyword2'=>$type,
//                'keyword3'=>'CNY '.$order['total_money'],
//                'keyword4'=>$zf_type,
//                'keyword5'=>date('Y-m-d H:i:s',time()),
//                'remark' =>'',
//                'openid' =>'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',
//                'uniacid'=>$order['uniacid'],
//                'temp_id'=>'tHWxOL4Kc3v6uZinHT3Zo661I8o6EbAg46XKUP0FnnY',
//                'url'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$order['id'].'&isadmin=1'
//            ]);
//            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post3);
        }
        $answer['finished'] = 'SUCCESS';

    } else {
        $answer['finished'] = 'FAIL';
    }
//	$str = tostring($answer);

    ksort($answer, SORT_STRING);
    $str = '';
    foreach ($answer as $key => $v ) {
        if (empty($v)) {
            continue;
        }
        $str .= $key . '=' . $v . '&';
    }

//	$str = $str .'&key=5f61d7f65b184d19a1e006bc9bfb6b2f';
    $str .= 'key='.$setting['payment']['tgpay']['key'];
    //数据加密
    $answer['sign'] = strtoupper(md5($str));

    //将数据转换成json数据返回
    echo json_encode($answer);

    $get = $data;
}else {
    $get = $_GET;
}

//$_W['uniacid'] = $_W['weid'] = intval($strs[0]);
$_W['uniacid'] = $_W['weid'] = $get['uniacid'];

//$type = intval($strs[1]);
$type = 0;

$total_fee = $get['payMoney'];

if ($type == 0) {
    $paylog = "\n-------------------------------------------------\n";
    $paylog .= 'orderno: ' . $get['lowOrderId'] . "\n";
    $paylog .= "paytype: alipay\n";
    $paylog .= 'data: ' . json_encode($_POST) . "\n";
    m('common')->paylog($paylog);
}

$set = m('common')->getSysset(array('shop', 'pay'));

$setting = uni_setting($_W['uniacid'], array('payment'));
if (is_array($set['payment'])) {

    $wechat = $set['payment']['tgpay'];

    if (!empty($wechat)) {

        m('common')->paylog('setting: ok');

        if (($data['state'] == '0') && ($data['orderDesc'] == '支付成功')) {

            m('common')->paylog('sign: ok');

            if (empty($type)) {

                $tid = $get['lowOrderId'];

                // if (strexists($tid, 'GJ')) {
                // 	$tids = explode('GJ', $tid);
                // 	$tid = $tids[0];
                // }

                $sql = 'SELECT * FROM ' . tablename('core_paylog') . ' WHERE `module`=:module AND `tid`=:tid  limit 1';

                $params = array();

                $params[':tid'] = $tid;

                $params[':module'] = 'sz_yi';
                //查找core_paylog中的数据
                $log = pdo_fetch($sql, $params);

                m('common')->paylog('log: ' . (empty($log) ? '' : json_encode($log)) . '');

                if (!empty($log) && ($log['status'] == '0') && (bccomp($log['fee'], $total_fee, 2) == 0)) {

                    m('common')->paylog('corelog: ok');

                    $site = WeUtility::createModuleSite($log['module']);

                    if (!is_error($site)) {

                        $method = 'payResult';

                        if (method_exists($site, $method)) {
                            $ret = array();
                            // 			$ret['weid'] = $log['weid'];
                            $ret['uniacid'] = $log['uniacid'];
                            $ret['result'] = 'success';
//							$ret['type'] = $log['type'];
                            $ret['type'] = 'wechat';//2017-11-17
                            $ret['from'] = 'return';
                            $ret['tid'] = $log['tid'];
                            $ret['user'] = $log['openid'];
                            $ret['fee'] = $log['fee'];
                            $ret['tag'] = $log['tag'];
                            $result = $site->$method($ret);

                            m('common')->paylog('payResult: ' . json_encode($result) . ".\n");

                            if (is_array($result) && ($result['result'] == 'success')) {

                                $log['tag'] = iunserializer($log['tag']);
                                $log['tag']['transaction_id'] = $get['transaction_id'];
                                $record = array();
                                $record['status'] = '1';
//								$record['tag'] = iserializer($log['tag']);

                                pdo_update('core_paylog', $record, array('plid' => $log['plid']));

                                // pdo_update('sz_yi_order',$record, array('ordersn_general' => $tid, 'uniacid' => $log['uniacid']));
                                exit();
                            }
                        }
                    }
                }
            } else if ($type == 1) {
                $logno = trim($get['lowOrderId']);

                if (empty($logno)) {
                    exit();
                }

                $log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `uniacid`=:uniacid and `logno`=:logno limit 1', array(':uniacid' => $_W['uniacid'], ':logno' => $logno));
                if (!empty($log) && empty($log['status']) && ($log['fee'] == $total_fee) && ($log['openid'] == $get['openid'])) {
                    pdo_update('sz_yi_member_log', array('status' => 1, 'rechargetype' => 'wechat'), array('id' => $log['id']));
                    m('member')->setCredit($log['openid'], 'credit2', $log['money'], array(0, '商城会员充值:credit2:' . $log['money']));
                    m('member')->setRechargeCredit($log['openid'], $log['money']);

                    if (p('sale')) {
                        p('sale')->setRechargeActivity($log);
                    }

                    if (!empty($log['couponid'])) {
                        $pc = p('coupon');

                        if ($pc) {
                            $pc->useRechargeCoupon($log);
                        }
                    }
                    m('notice')->sendMemberLogMessage($log['id']);
                }
            }
        }
    }
}
?>