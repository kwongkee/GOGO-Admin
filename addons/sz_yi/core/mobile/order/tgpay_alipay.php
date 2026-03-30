<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();

if (empty($openid)) {
	$openid = $_GPC['openid'];
}

$member = m('member')->getMember($openid);

$uniacid = $_W['uniacid'];
$orderid = intval($_GPC['orderid']);
$logid = intval($_GPC['logid']);

$shopset = m('common')->getSysset('shop');
$set = m('common')->getSysset(array('pay'));
/**
 * $set['pay']['tgpaystatus'] == 1
 * $set['pay']['tgpay'] mchid,key
 */


if (!empty($orderid)) {
	
	$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
	
	if (empty($order)) {
		show_json(0, '订单未找到!');
	}
	
	// han 20170915
	if ($order['status'] == -1) {
		show_json(-1, '订单已关闭, 无法付款!');
	} else {
		if ( (1 <= $order['status']) || ($order['ordersn_general'] == $order['ordersn']) ) {
			show_json(-1, '订单已付款, 无需重复支付!');
		}
	}
	
	// end
	$order_price = pdo_fetchcolumn('select sum(price) from ' . tablename('sz_yi_order') . ' where ordersn_general=:ordersn_general and uniacid=:uniacid and openid=:openid limit 1', array(':ordersn_general' => $order['ordersn_general'], ':uniacid' => $uniacid, ':openid' => $openid));
	
	$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'sz_yi', ':tid' => $order['ordersn_general']));

	//xj 20170810 异常订单
	if (empty($log)) {
		show_json(0, '异常订单!');
	}
	//xj 20170810 异常订单
	
	if (!empty($log) && ($log['status'] != '0')) {
		show_json(0, '订单已支付, 无需重复支付!');
	}

	$param_title = $shopset['name'] . '订单:' . $order['ordersn_general'];
	$tgalipay = array('success' => false);
	$params = array();
	$params['tid'] = $order['ordersn_general'];
	$params['user'] = $openid;
	$params['fee'] = $order_price;
	$params['title'] = $param_title;
	load()->func('communication');
	
	//2017-12-18 系统支付配置
//	load()->model('payment');
//	$setting = uni_setting($_W['uniacid'], array('payment'));
//	$set['pay']['tgpaystatus'] == 1
//	$set['pay']['tgpay'] mchid,key
	if (!empty($set['pay']['tgpay']) && ($set['pay']['tgpaystatus'] == 1)) {
		
//		$options = $setting['payment']['tgpay'];
		$options = $set['pay']['tgpay'];
		//获取该公众号配置
		$config = array(
			'mchid'=>$options['mchid'],
			'key'=>$options['key'],
		);
		//请求数据返回支付URL
//		$tgalipay['url'] = m('common')->Tgalipay_scode($params, $config);
		$tgalipayRes = m('common')->Tgalipay_scode($params, $config);
		if ($tgalipayRes['status'] == '100') {
			
//			$tgalipay['url'] = $tgalipayRes['codeUrl'];
//			$tgalipay['success'] = true;
			
			header("Location:".$tgalipayRes['codeUrl']);
			exit('正在跳转支付宝支付...');
			
		}else if ($tgalipayRes['status'] == '101') {
			
			$ordersn = m('common')->createNO('order', 'ordersn', 'GG');//生成订单编号
			$order_data = ['ordersn' => $ordersn,];//更新数据库中的订单编号
			//更新订单数据
			$result = pdo_update('sz_yi_order', $order_data, array('id' => $order['id']));
			if (!empty($result)) {
				$params['tid'] = $ordersn;
				$tgalipayRes = m('common')->Tgalipay_scode($params, $config);
				if ($tgalipayRes['status'] == '100') {

//					$tgalipay['url'] = $tgalipayRes['codeUrl'];
//					$tgalipay['message'] = $tgalipayRes['message'];
//					$tgalipay['success'] = true;
					
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
	
} else {
	
	if (!empty($logid)) {
		$log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `id`=:id and `uniacid`=:uniacid limit 1', array(':uniacid' => $uniacid, ':id' => $logid));

		if (empty($log)) {
			show_json(0, '充值出错!');
		}

		if (!empty($log['status'])) {
			show_json(0, '已经充值成功,无需重复支付!');
		}

		$tgalipay = array('success' => false);
		$params = array();
		$params['tid'] = $log['logno'];
		$params['user'] = $log['openid'];
		$params['fee'] = $log['money'];
		$params['title'] = $log['title'];
		load()->func('communication');
		
		if (!empty($set['pay']['tgpay']) && ($set['pay']['tgpaystatus'] == 1)) {
		
			$options = $set['pay']['tgpay'];
			//获取该公众号配置
			$config = array(
				'mchid'=>$options['mchid'],
				'key'=>$options['key'],
			);
			//请求数据返回支付URL

			$tgalipayRes = m('common')->Tgalipay_scode($params, $config);
			if ($tgalipayRes['status'] == '100') {
				
				$tgalipay['url'] = $tgalipayRes['codeUrl'];
				$tgalipay['success'] = true;
				
				header("Location:".$tgalipayRes['codeUrl']);
				exit('正在跳转支付宝支付...');
				
			}else if ($tgalipayRes['status'] == '101') {
			
				$ordersn = m('common')->createNO('order', 'ordersn', 'SH');//生成订单编号
				$order_data = ['ordersn' => $ordersn,];//更新数据库中的订单编号
				//更新订单数据
				$result = pdo_update('sz_yi_order', $order_data, array('id' => $order['id']));
				if (!empty($result)) {
					$params['tid'] = $ordersn;
					$tgalipayRes = m('common')->Tgalipay_scode($params, $config);
					if ($tgalipayRes['status'] == '100') {
	
						$tgalipay['url'] = $tgalipayRes['codeUrl'];
						$tgalipay['message'] = $tgalipayRes['message'];
						$tgalipay['success'] = true;
						
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
		
		/*load()->model('payment');
		$setting = uni_setting($_W['uniacid'], array('payment'));
		if (is_array($setting['payment'])) {
			$options = $setting['payment']['tgpay'];
			//获取该公众号配置
			$config = array(
				'mchid'=>$options['mchid'],
				'key'=>$options['key'],
			);
			$tgalipay['url'] = m('common')->Tgalipay_scode($params, $config);

			if (!empty($tgalipay['url'])) {
				$tgalipay['success'] = true;
			}
		}*/
		
	}
}

//if($tgalipay['success']) {
//	header('location:'.$tgalipay['url']);
//}else{
//	echo $tgalipay['message'];
//}

//show_json(1, array('tgalipay' => $tgalipay));

//include $this->template('order/tgpay_alipay');

?>