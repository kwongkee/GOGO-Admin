<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$uniacid = $_W['uniacid'];
require_once '../addons/sz_yi/plugin/pingpp/init.php';
$input_data = json_decode(file_get_contents('php://input'), true);

do {
	if (!isset($input_data['id'])) {
		$res['status'] = 500;
		$res['msg'] = 'Internal Server Error';
		echo '事件ID为空';
		break;
	}

	if (!isset($input_data['type'])) {
		header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
		exit('fail');
	}

	switch ($input_data['type']) {
	case 'charge.succeeded':
		$pay_info = $input_data['data']['object'];

		if ($pay_info['paid'] == 1) {
			$order_data['pay_time'] = $pay_info['time_paid'];
			$order_data['pay_id'] = $pay_info['id'];
			$order_data['order_id'] = $pay_info['order_no'];

			if ($pay_info['channel'] == 'wx') {
				$pay_type = 'wechat';
				$pay_type_num = 27;
			}
			else {
				if ($pay_info['channel'] == 'alipay') {
					$pay_type = 'alipay';
					$pay_type_num = 28;
				}
			}

			if (substr($pay_info['order_no'], 0, 2) == 'RC') {
				$log = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member_log') . ' WHERE `logno`=:logno and `uniacid`=:uniacid limit 1', array(':uniacid' => $uniacid, ':logno' => $pay_info['order_no']));
				if (!empty($log) && empty($log['status'])) {
					pdo_update('sz_yi_member_log', array('status' => 1, 'rechargetype' => $pay_type), array('id' => $log['id']));
					m('member')->setCredit($log['openid'], 'credit2', $log['money']);
					m('member')->setRechargeCredit($log['openid'], $log['money']);

					if (p('sale')) {
						p('sale')->setRechargeActivity($log);
					}

					m('notice')->sendMemberLogMessage($log['id']);
				}
			}
			else {
				$order_info = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order') . ' WHERE uniacid=:uniacid AND ordersn=:ordersn', array(':uniacid' => $uniacid, ':ordersn' => $pay_info['order_no']));

				if (empty($order_info)) {
					$order_info = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order') . ' WHERE uniacid=:uniacid AND ordersn_general=:ordersn_general', array(':uniacid' => $uniacid, ':ordersn_general' => $pay_info['order_no']));
				}

				pdo_query('update ' . tablename('sz_yi_order') . ' set paytype=' . $pay_type_num . ', trade_no="' . $pay_info['id'] . '" where ordersn_general=:ordersn_general and uniacid=:uniacid ', array(':uniacid' => $uniacid, ':ordersn_general' => $order_info['ordersn_general']));
				$log = pdo_fetch('SELECT * FROM ' . tablename('core_paylog') . ' WHERE `uniacid`=:uniacid AND `module`=:module AND `tid`=:tid limit 1', array(':uniacid' => $uniacid, ':module' => 'sz_yi', ':tid' => $order_info['ordersn_general']));

				if ($log['status'] != 1) {
					$record = array();
					$record['status'] = '1';
					$record['type'] = 'alipay';
					pdo_update('core_paylog', $record, array('plid' => $log['plid']));
					$ret = array();
					$ret['result'] = 'success';
					$ret['type'] = $pay_type;
					$ret['from'] = 'return';
					$ret['tid'] = $log['tid'];
					$ret['user'] = $log['openid'];
					$ret['fee'] = $log['fee'];
					$ret['weid'] = $log['weid'];
					$ret['uniacid'] = $log['uniacid'];
					$this->payResult($ret);
					m('notice')->sendOrderMessage($order_info['id']);
					echo '成功';
					$res['status'] = 200;
					$res['msg'] = 'ok';
				}
			}
		}

		break;

	case 'refund.succeeded':
		$refund = $input_data['data']['object'];
		if (($refund['succeed'] == true) && ($refund['status'] == 'succeeded')) {
			$order_info = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order') . ' WHERE uniacid=:uniacid AND ordersn=:ordersn', array(':uniacid' => $uniacid, ':ordersn' => $refund['charge_order_no']));

			if (empty($order_info)) {
				$order_info = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order') . ' WHERE uniacid=:uniacid AND ordersn_general=:ordersn_general', array(':uniacid' => $uniacid, ':ordersn_general' => $refund['charge_order_no']));
			}

			if ($order_info['paytype'] == 28) {
				$shopset = m('common')->getSysset('shop');
				$goods = pdo_fetchall('SELECT g.id,g.credit, o.total,o.realprice FROM ' . tablename('sz_yi_order_goods') . ' o left join ' . tablename('sz_yi_goods') . ' g on o.goodsid=g.id ' . ' WHERE o.orderid=:orderid and o.uniacid=:uniacid', array(':orderid' => $order_info['id'], ':uniacid' => $uniacid));
				$credits = 0;

				foreach ($goods as $g) {
					$gcredit = trim($g['credit']);

					if (!empty($gcredit)) {
						if (strexists($gcredit, '%')) {
							$credits += intval((floatval(str_replace('%', '', $gcredit)) / 100) * $g['realprice']);
						}
						else {
							$credits += intval($g['credit']) * $g['total'];
						}
					}
				}

				if (0 < $credits) {
					m('member')->setCredit($order_info['openid'], 'credit1', 0 - $credits, array(0, $shopset['name'] . '退款扣除积分: ' . $credits . ' 订单号: ' . $order_info['ordersn']));
				}

				if (0 < $order_info['deductcredit']) {
					m('member')->setCredit($order_info['openid'], 'credit1', $order_info['deductcredit'], array('0', $shopset['name'] . '购物返还抵扣积分 积分: ' . $order_info['deductcredit'] . ' 抵扣金额: ' . $order_info['deductprice'] . ' 订单号: ' . $order_info['ordersn']));
				}

				if (0 < $order_info['deductyunbimoney']) {
					p('yunbi')->setVirtualCurrency($order_info['openid'], $order_info['deductyunbi']);
					$data_log = array('id' => '', 'openid' => $order_info['openid'], 'credittype' => 'virtual_currency', 'money' => $order_info['deductyunbi'], 'remark' => '购物返还抵扣' . $yunbiset['yunbi_title'] . ' ' . $yunbiset['yunbi_title'] . ': ' . $order_info['deductyunbi'] . ' 抵扣金额: ' . $order_info['deductyunbimoney'] . ' 订单号: ' . $order_info['ordersn']);
					p('yunbi')->addYunbiLog($_W['uniacid'], $data_log, '4');
				}

				if (0 < $order_info['deductcredit2']) {
					m('member')->setCredit($order_info['openid'], 'credit2', $order_info['deductcredit2'], array('0', $shopset['name'] . '购物返还抵扣余额 积分: ' . $order_info['deductcredit2'] . ' 订单号: ' . $order_info['ordersn']));
				}

				$data['reply'] = '';
				$data['status'] = 1;
				$data['refundtype'] = 3;
				$data['price'] = $refund['amount'] * 0.01;
				$data['refundtime'] = time();
				pdo_update('sz_yi_order_refund', $data, array('id' => $order_info['refundid']));
				m('notice')->sendOrderMessage($item['id'], true);
				pdo_update('sz_yi_order', array('refundstate' => 0, 'status' => -1, 'refundtime' => time()), array('id' => $order_info['id'], 'uniacid' => $uniacid));
			}
		}

		header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
		break;

	default:
		header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
		break;
	}
} while (0);

header('HTTP/1.1 ' . $res['status'] . ' ' . $res['msg']);

?>
