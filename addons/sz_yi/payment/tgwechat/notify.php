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
file_put_contents('./log/notify.log', $input."\r\n",FILE_APPEND);
//将接受到的Json数据转换成数组格式。
$data = json_decode($input, true);
//echo $_W['siteroot'] . 'addons/sz_yi/payment/tgwechat/notify.log';
if (!empty($data)) {
	
	$order = pdo_fetch('select id,uniacid,status from ' . tablename('sz_yi_order') . ' where ordersn_general=:ordersn limit 1', array(':ordersn' => $data['lowOrderId']));
	if(empty($order)){
		$answer['finished'] = 'FAIL';
		echo json_encode($answer);die;
	}
	
	$data['uniacid'] = $order['uniacid'];//订单所属公众号
	
	$setting = uni_setting($order['uniacid'], array('payment'));
	
	$answer = array(
		'lowOrderId'=> $data['lowOrderId'],//下游系统流水号，必须唯一
		'merchantId'=> $data['merchantId'],//商户进件账号
		'upOrderId'=>  $data['upOrderId'],//上游流水号
	);
	
	if ($data['state'] == '0' && $data['orderDesc'] == '支付成功') {
		//是否接收到回调  SUCCESS表示成功
		//付款成功修改订单表中sz_yi_order数据  状态：status = 1
		if ($order['status'] == 0) {
			m('common')->paylog($data);
			m('common')->paylog('status');

			pdo_update('sz_yi_order', array(
			    'status' => '1',
                'paytype'=>2,
                'ordersn_general'=>$data['upOrderId'],
            ), array('id' => $order['id']));
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

if (is_array($set['pay'])) {
	
	$wechat = $set['pay']['tgpay'];

	if (!empty($wechat)) {
		
		m('common')->paylog('setting: ok');
		
//		ksort($get);
//		$string1 = '';

//		foreach ($get as $k => $v) {
//			if (($v != '') && ($k != 'sign')) {
//				$string1 .= $k . '=' . $v . '&';
//			}
//		}
//		$wechat['key'] = $setting['payment']['tgpay']['key'];

//		$wechat['key'] = $wechat['version'] == 1 ? $wechat['key'] : $wechat['key'];
		
//		$sign = strtoupper(md5($string1 . 'key=' . $wechat['key']));

//		if ($sign == $get['sign']) { 2017-11-17
		if (($data['state'] == '0') && ($data['orderDesc'] == '支付成功')) {	

			m('common')->paylog('sign: ok');

			if (empty($type)) {

				$tid = $get['lowOrderId'];

				if (strexists($tid, 'GJ')) {
					$tids = explode('GJ', $tid);
					$tid = $tids[0];
				}

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
							$ret['weid'] = $log['weid'];
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

								if (p('cashier')) {
									
									$order = pdo_fetch('select id,cashier from ' . tablename('sz_yi_order') . ' where  (ordersn=:ordersn or pay_ordersn=:ordersn or ordersn_general=:ordersn) and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':ordersn' => $ret['tid']));

									if (!empty($order['cashier'])) {									
										$orders['status'] = '3';
									}
								}
								pdo_update('sz_yi_order',$record, array('ordersn_general' => $tid, 'uniacid' => $log['uniacid']));
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