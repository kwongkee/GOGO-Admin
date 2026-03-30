<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$shopset = m('common')->getSysset(array('shop'));
$hascouponplugin = false;

if (p('commission')) {
	$commission = p('commission')->getSet();
}

$plugc = p('coupon');
$member = m('member')->getMember($openid);

if ($plugc) {
	$hascouponplugin = true;
}

if ($operation == 'display') {
	$shopset = m('common')->getSysset('shop');
	$sid = $_GPC['sid'];

	if (!is_numeric($sid)) {
		redirect($this->createMobileUrl('member'));
	}

	$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_store') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $sid, ':uniacid' => $_W['uniacid']));
	$store = set_medias($store, 'thumb');

	if (!$store) {
		redirect($this->createMobileUrl('member'));
	}

	if ($commission['become_child'] == 0) {
		p('commission')->checkAgent();
	}

	if ($_W['isajax']) {
		$orig_price = $_GPC['orig_price'];
		if (!is_numeric($orig_price) || ($orig_price <= 0)) {
			redirect($this->createMobileUrl('member'));
		}
	}
	else {
		$orig_price = 0;
	}

	$store_max_credit1 = ($orig_price * $store['deduct_credit1']) / 100;
	$store_max_credit2 = ($orig_price * $store['deduct_credit2']) / 100;
	$deductcredit = 0;
	$deductmoney = 0;
	$deductcredit2 = 0;
	$sale_plugin = p('sale');

	if ($sale_plugin) {
		$saleset = $sale_plugin->getSet();
		$credit = m('member')->getCredit($openid, 'credit1');

		if (!empty($saleset['creditdeduct'])) {
			$pcredit = intval($saleset['credit']);
			$pmoney = round(floatval($saleset['money']), 2);
			if ((0 < $pcredit) && (0 < $pmoney)) {
				if (($credit % $pcredit) == 0) {
					$deductmoney = round(intval($credit / $pcredit) * $pmoney, 2);
				}
				else {
					$deductmoney = round((intval($credit / $pcredit) + 1) * $pmoney, 2);
				}
			}

			if ($store_max_credit1 < $deductmoney) {
				$deductmoney = $store_max_credit1;
			}

			$deductcredit = ($deductmoney / $pmoney) * $pcredit;
		}

		if (!empty($saleset['moneydeduct'])) {
			$deductcredit2 = m('member')->getCredit($openid, 'credit2');

			if ($store_max_credit2 < $deductcredit2) {
				$deductcredit2 = $store_max_credit2;
			}
		}
	}

	$hascoupon = false;
	$iscashier = 1;

	if ($hascouponplugin) {
		$couponcount = $plugc->consumeCouponCount($openid, $orig_price, 0, $sid, $iscashier);
		$hascoupon = 0 < $couponcount;
	}
}
else if ($operation == 'get_deduct') {
	if ($_W['isajax']) {
		$sid = $_GPC['sid'];
		$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_store') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $sid, ':uniacid' => $_W['uniacid']));
		$orig_price = $_GPC['orig_price'];
		if (!is_numeric($orig_price) || ($orig_price <= 0)) {
			redirect($this->createMobileUrl('member'));
		}

		$store_max_credit1 = ($orig_price * $store['deduct_credit1']) / 100;
		$store_max_credit2 = ($orig_price * $store['deduct_credit2']) / 100;
		$deductcredit = 0;
		$deductmoney = 0;
		$deductcredit2 = 0;
		$sale_plugin = p('sale');

		if ($sale_plugin) {
			$saleset = $sale_plugin->getSet();
			$credit = m('member')->getCredit($openid, 'credit1');

			if (!empty($saleset['creditdeduct'])) {
				$pcredit = intval($saleset['credit']);
				$pmoney = round(floatval($saleset['money']), 2);
				if ((0 < $pcredit) && (0 < $pmoney)) {
					if (($credit % $pcredit) == 0) {
						$deductmoney = round(intval($credit / $pcredit) * $pmoney, 2);
					}
					else {
						$deductmoney = round((intval($credit / $pcredit) + 1) * $pmoney, 2);
					}
				}

				if ($store_max_credit1 < $deductmoney) {
					$deductmoney = $store_max_credit1;
				}

				$deductcredit = ($deductmoney / $pmoney) * $pcredit;
			}

			if (!empty($saleset['moneydeduct'])) {
				$deductcredit2 = m('member')->getCredit($openid, 'credit2');

				if ($store_max_credit2 < $deductcredit2) {
					$deductcredit2 = $store_max_credit2;
				}
			}
		}

		$hascoupon = false;
		$iscashier = 1;

		if ($hascouponplugin) {
			$couponcount = $plugc->consumeCouponCount($openid, $orig_price, 0, $sid, $iscashier);
			$hascoupon = 0 < $couponcount;
		}

		$coupon = array('hascouponplugin' => $hascouponplugin, 'couponcount' => $couponcount, 'hascoupon' => $hascoupon);
		$deduct = array('deductcredit2' => $deductcredit2, 'deductcredit' => $deductcredit, 'deductmoney' => $deductmoney);
		show_json(1, array('couponcount' => $couponcount, 'deduct' => $deduct, 'coupon' => $coupon));
	}
}
else {
	if ($operation == 'create-order') {
		$sid = $_GPC['sid'];

		if (!is_numeric($sid)) {
			redirect($this->createMobileUrl('member'));
		}

		$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_store') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $sid, ':uniacid' => $_W['uniacid']));
		$store = set_medias($store, 'thumb');

		if (!$store) {
			redirect($this->createMobileUrl('member'));
		}

		$orig_price = $_GPC['orig_price'];
		if (!is_numeric($orig_price) || ($orig_price <= 0)) {
			redirect($this->createMobileUrl('member'));
		}

		$store_max_credit1 = ($orig_price * $store['deduct_credit1']) / 100;
		$store_max_credit2 = ($orig_price * $store['deduct_credit2']) / 100;
		$deductcredit = 0;
		$deductmoney = 0;
		$deductcredit2 = 0;
		$totalprice = $orig_price;
		$sale_plugin = p('sale');

		if ($sale_plugin) {
			if (!empty($_GPC['deduct'])) {
				$saleset = $sale_plugin->getSet();
				$credit = m('member')->getCredit($openid, 'credit1');

				if (!empty($saleset['creditdeduct'])) {
					$pcredit = intval($saleset['credit']);
					$pmoney = round(floatval($saleset['money']), 2);
					if ((0 < $pcredit) && (0 < $pmoney)) {
						if (($credit % $pcredit) == 0) {
							$deductmoney = round(intval($credit / $pcredit) * $pmoney, 2);
						}
						else {
							$deductmoney = round((intval($credit / $pcredit) + 1) * $pmoney, 2);
						}
					}

					if ($store_max_credit1 < $deductmoney) {
						$deductmoney = $store_max_credit1;
					}

					$deductcredit = round(($deductmoney / $pmoney) * $pcredit, 2);
				}
			}

			$totalprice -= $deductmoney;

			if (!empty($_GPC['deduct2'])) {
				$deductcredit2 = m('member')->getCredit($openid, 'credit2');

				if ($store_max_credit2 < $deductcredit2) {
					$deductcredit2 = $store_max_credit2;
				}
			}

			$totalprice -= $deductcredit2;
		}

		$couponid = intval($_GPC['couponid']);

		if ($plugc) {
			$coupon = $plugc->getCouponByDataID($couponid);

			if (!empty($coupon)) {
				if (($coupon['enough'] <= $totalprice) && empty($coupon['used'])) {
					if ($coupon['backtype'] == 0) {
						if (($totalprice <= $coupon['deduct']) && ($coupon['enough'] == 0)) {
							show_json(-2, '优惠券金额不能大于订单金额!');
							exit();
						}

						if (0 < $coupon['deduct']) {
							$couponprice = number_format($coupon['deduct']);
						}
					}
					else {
						if ($coupon['backtype'] == 1) {
							if (0 < $coupon['discount']) {
								$couponprice = $totalprice * (1 - ($coupon['discount'] / 10));
							}
						}
					}

					if (0 < $couponprice) {
						$totalprice -= $couponprice;
					}
				}
			}
		}

		$realtotalprice = $totalprice;
		$carrier = $_GPC['carrier'];
		$carriers = (is_array($carrier) ? iserializer($carrier) : iserializer(array()));
		$ordersn = m('common')->createNO('order', 'ordersn', 'SY');
		$user = m('member')->getMember($openid);
		$realtotalprice = ($totalprice * (100 - $store['settle_platform'])) / 100;

		if ($store['decredits'] == 1) {
			$realtotalprice = $realtotalprice - ($totalprice * ($store['creditpack'] / 100));
		}

		if (p('commission')) {
			$commission_set = p('commission')->getSet();

			if ($store['decommission'] == 1) {
				if ($commission_set['level'] == 3) {
					$fuser = m('member')->getMember($user['agentid']);
					$suser = m('member')->getMember($fuser['agentid']);

					if (empty($user['agentid'])) {
					}
					else if (empty($fuser['agentid'])) {
						$realtotalprice = $realtotalprice - ($totalprice * ($store['commission1_rate'] / 100));
					}
					else if (empty($suser['agentid'])) {
						$realtotalprice = $realtotalprice - ($totalprice * ($store['commission2_rate'] / 100)) - ($totalprice * ($store['commission1_rate'] / 100));
					}
					else {
						$realtotalprice = $realtotalprice - ($totalprice * ($store['commission3_rate'] / 100)) - ($totalprice * ($store['commission2_rate'] / 100)) - ($totalprice * ($store['commission1_rate'] / 100));
					}
				}
				else if ($commission_set['level'] == 2) {
					$fuser = m('member')->getMember($user['agentid']);
					$suser = m('member')->getMember($fuser['agentid']);

					if (empty($user['agentid'])) {
					}
					else if (empty($fuser['agentid'])) {
						$realtotalprice = $realtotalprice - ($totalprice * ($store['commission1_rate'] / 100));
					}
					else {
						if (empty($suser['agentid'])) {
							$realtotalprice = $realtotalprice - ($totalprice * ($store['commission2_rate'] / 100)) - ($totalprice * ($store['commission1_rate'] / 100));
						}
					}
				}
				else {
					if ($commission_set['level'] == 1) {
						$fuser = m('member')->getMember($user['agentid']);

						if (empty($user['agentid'])) {
						}
						else {
							if (empty($fuser['agentid'])) {
								$realtotalprice = $realtotalprice - ($totalprice * ($store['commission1_rate'] / 100));
							}
						}
					}
				}
			}

			$agentid = $user['agentid'];
		}

		if (($store['redpack_min'] <= $totalprice) && ($store['deredpack'] == 1)) {
			$realtotalprice = $realtotalprice - ($totalprice * ($store['redpack'] / 100));
		}

		$order = array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'agentid' => $agentid, 'ordersn' => $ordersn, 'price' => $totalprice, 'realprice' => $realtotalprice, 'cash' => $cash, 'discountprice' => 0, 'deductprice' => $deductmoney, 'deductcredit' => $deductcredit, 'deductcredit2' => $deductcredit2, 'deductenough' => 0, 'status' => 0, 'paytype' => 0, 'transid' => '', 'remark' => '', 'addressid' => 0, 'goodsprice' => $orig_price, 'dispatchprice' => 0, 'dispatchtype' => 0, 'dispatchid' => 0, 'storeid' => 0, 'carrier' => $carriers, 'createtime' => time(), 'isverify' => 0, 'verifycode' => '', 'virtual' => 0, 'isvirtual' => 0, 'oldprice' => 0, 'olddispatchprice' => 0, 'couponid' => $couponid, 'couponprice' => $couponprice, 'cashier' => 1, 'cashierid' => $sid, 'redprice' => $totalprice * ($store['redpack'] / 100), 'ordersn_general' => $ordersn, 'pay_ordersn' => $ordersn);

		if ($store['deredpack'] == 1) {
			$order['deredpack'] = 1;
		}

		if ($store['decommission'] == 1) {
			$order['decommission'] = 1;
		}

		if ($store['decredits'] == 1) {
			$order['decredits'] = 1;
		}

		pdo_insert('sz_yi_order', $order);
		$orderid = pdo_insertid();
		pdo_insert('sz_yi_cashier_order', array('order_id' => $orderid, 'uniacid' => $_W['uniacid'], 'cashier_store_id' => $sid));
		$order_goods = array('uniacid' => $_W['uniacid'], 'orderid' => $orderid, 'goodsid' => 0, 'price' => $orig_price, 'total' => 1, 'optionid' => 0, 'createtime' => time(), 'optionname' => '', 'goodssn' => '', 'productsn' => '', 'realprice' => $orig_price, 'oldprice' => $orig_price, 'openid' => $openid);

		if (p('bonus')) {
			$order_goods['bonusmoney'] = $store['bonus'];
		}

		pdo_insert('sz_yi_order_goods', $order_goods);
		$this->model->calculateCommission($orderid);

		if (p('bonus')) {
			$this->model->calculateBonus($orderid);
		}

		if ($store['iscontact'] == 1) {
			if (is_array($carrier)) {
				$up = array('realname' => $carrier['carrier_realname'], 'mobile' => $carrier['carrier_mobile']);
				pdo_update('sz_yi_member', $up, array('id' => $member['id'], 'uniacid' => $_W['uniacid']));

				if (!empty($member['uid'])) {
					load()->model('mc');
					mc_update($member['uid'], $up);
				}
			}
		}

		if (0 < $deductcredit) {
			$shop = m('common')->getSysset('shop');
			m('member')->setCredit($openid, 'credit1', 0 - $deductcredit, array('0', '收银台购物积分抵扣 消费积分: ' . $deductcredit . ' 抵扣金额: ' . $deductmoney . ' 订单号: ' . $ordersn));
		}

		$plugincoupon = p('coupon');

		if ($plugincoupon) {
			$plugincoupon->useConsumeCoupon($orderid);
		}

		m('notice')->sendOrderMessage($orderid);

		if ($commission['become_child'] == 1) {
			p('commission')->checkOrderConfirm($orderid);
		}

		show_json(1, array('orderid' => $orderid));
	}
}

include $this->template('cashier/order_confirm');

?>
