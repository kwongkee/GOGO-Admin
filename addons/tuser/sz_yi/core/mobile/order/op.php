<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];
$shopset = m('common')->getSysset('shop');
$yunbi_plugin = p('yunbi');

if ($yunbi_plugin) {
	$yunbiset = $yunbi_plugin->getSet();
}

if ($_W['isajax']) {
	if ($operation == 'cancel') {
		$orderid = intval($_GPC['orderid']);
		$order = pdo_fetch('select id,ordersn,openid,status,deductcredit,deductprice,deductyunbi,deductyunbimoney,couponid from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
		
		if (empty($order)) {
			show_json(0, '订单未找到!');
		}

		if ($order['status'] != 0) {
			show_json(0, '订单已支付，不能取消!');
		}

		pdo_update('sz_yi_order', array('status' => -1, 'canceltime' => time()), array('id' => $order['id'], 'uniacid' => $uniacid));
		m('notice')->sendOrderMessage($orderid);

		if (0 < $order['deductprice']) {
			$shop = m('common')->getSysset('shop');
			m('member')->setCredit($order['openid'], 'credit1', $order['deductcredit'], array('0', $shop['name'] . '购物返还抵扣积分 积分: ' . $order['deductcredit'] . ' 抵扣金额: ' . $order['deductprice'] . ' 订单号: ' . $order['ordersn']));
		}

		if (0 < $order['deductyunbimoney']) {
			$shop = m('common')->getSysset('shop');
			p('yunbi')->setVirtualCurrency($order['openid'], $order['deductyunbi']);
			$data_log = array('id' => $member['id'], 'openid' => $openid, 'credittype' => 'virtual_currency', 'money' => $order['deductyunbi'], 'remark' => '购物返还抵扣' . $yunbiset['yunbi_title'] . ' ' . $yunbiset['yunbi_title'] . ': ' . $order['deductyunbi'] . ' 抵扣金额: ' . $order['deductyunbimoney'] . ' 订单号: ' . $order['ordersn']);
		}

		p('yunbi')->addYunbiLog($uniacid, $data_log, '4');
		if (p('coupon') && !empty($order['couponid'])) {
			p('coupon')->returnConsumeCoupon($orderid);
		}

		show_json(1);
	}
	else if ($operation == 'complete') {
		$orderid = intval($_GPC['orderid']);
		$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));

		if (empty($order)) {
			show_json(0, '订单未找到!');
		}

		if ($order['status'] != 2) {
			show_json(0, '订单未发货，不能确认收货!');
		}

		if ((0 < $order['refundstate']) && !empty($order['refundid'])) {
			$change_refund = array();
			$change_refund['status'] = -2;
			$change_refund['refundtime'] = time();
			pdo_update('sz_yi_order_refund', $change_refund, array('id' => $order['refundid'], 'uniacid' => $uniacid));
		}

		pdo_update('sz_yi_order', array('status' => 3, 'finishtime' => time(),'lreturnstart'=> 1, 'refundstate' => 0), array('id' => $order['id'], 'uniacid' => $uniacid));

		if ($order['paytype'] == 3) {
			$goods = pdo_fetchall('select og.id,og.total,og.realprice, g.credit from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.orderid=:orderid and og.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));
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
				$shopset = m('common')->getSysset('shop');
				m('member')->setCredit($order['openid'], 'credit1', $credits, array(0, $shopset['name'] . '购物积分 订单号: ' . $order['ordersn']));
			}
		}

		m('member')->upgradeLevel($order['openid'], $orderid);
		if (p('coupon') && !empty($order['couponid'])) {
			p('coupon')->backConsumeCoupon($orderid);
		}

		m('notice')->sendOrderMessage($orderid);

		if (p('commission')) {
			p('commission')->checkOrderFinish($orderid);
		}

		if (p('return')) {
			p('return')->cumulative_order_amount($orderid);
		}

		if (p('yunbi')) {
			p('yunbi')->GetVirtualCurrency($orderid);
		}

		if (p('beneficence')) {
			p('beneficence')->GetVirtualBeneficence($orderid);
		}

		if ((1 < $order['redprice']) && ($order['redprice'] < 200)) {
			m('finance')->sendredpack($order['openid'], $order['redprice'] * 100, $orderid, $desc = '购买商品赠送红包', $act_name = '购买商品赠送红包', $remark = '购买商品确认收货发送红包');
		}

		show_json(1);
	}
	else if ($operation == 'completehotel') {
		$orderid = intval($_GPC['orderid']);
		$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));

		if (empty($order)) {
			show_json(0, '订单未找到!');
		}

		if ($order['status'] != 2) {
			show_json(0, '订单未确认，不能确认入住!');
		}

		pdo_update('sz_yi_order', array('status' => 6), array('id' => $order['id'], 'uniacid' => $uniacid));
		show_json(1);
	}
	else if ($operation == 'delivery') {
		if ($_W['ispost']) {
			$refundid = intval($_GPC['id']);
			$orderid = intval($_GPC['orderid']);
			$order = pdo_fetch('select id,status,price,refundid,goodsprice,dispatchprice,deductprice,deductcredit2,finishtime,isverify,virtual,refundstate from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));

			if (empty($order)) {
				show_json(0, '订单未找到!');
			}

			$refund = pdo_fetch('select * from ' . tablename('sz_yi_order_refund') . ' where id=:id and uniacid=:uniacid and orderid=:orderid limit 1', array(':id' => $refundid, ':uniacid' => $uniacid, ':orderid' => $orderid));

			if (empty($refund)) {
				show_json(0, '换货申请未找到!');
			}

			$time = time();
			$refund_data = array();
			$refund_data['status'] = 1;
			$refund_data['refundtime'] = $time;
			pdo_update('sz_yi_order_refund', $refund_data, array('id' => $refundid, 'uniacid' => $uniacid));
			$order_data = array();
			$order_data['refundstate'] = 0;
			$order_data['status'] = -1;
			$order_data['refundtime'] = $time;
			pdo_update('sz_yi_order', $order_data, array('id' => $orderid, 'uniacid' => $uniacid));
			show_json(1, '成功!');
		}
	}
	else if ($operation == 'express') {
		if ($_W['ispost']) {
			$refundid = intval($_GPC['id']);
			$refunddata = $_GPC['refunddata'];
			$express = $refunddata['express'];
			$expresscom = $refunddata['expresscom'];
			$expresssn = $refunddata['expresssn'];

			if (empty($refundid)) {
				show_json(0, '参数错误!');
			}

			if (empty($expresssn)) {
				show_json(0, '请输入快递单号!');
			}

			$refund = array();
			$refund['status'] = 4;
			$refund['express'] = $express;
			$refund['expresscom'] = $expresscom;
			$refund['expresssn'] = $expresssn;
			$refund['sendtime'] = time();
			pdo_update('sz_yi_order_refund', $refund, array('id' => $refundid, 'uniacid' => $uniacid));
			show_json(1, '成功!');
		}
	}
	else if ($operation == 'refund') {
		$orderid = intval($_GPC['orderid']);
		$order = pdo_fetch('select id,status,price,refundid,goodsprice,dispatchprice,deductprice,deductcredit2,finishtime,isverify,virtual,refundstate from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));

		if (empty($order)) {
			show_json(0, '订单未找到!');
		}

		if (($order['status'] != 1) && ($order['status'] != 3)) {
			show_json(0, '订单未付款或未收货，不能申请退款!');
		}
		else {
			if ($order['status'] == 3) {
				$tradeset = m('common')->getSysset('trade');
				$refunddays = intval($tradeset['refunddays']);

				if (0 < $refunddays) {
					$days = intval((time() - $order['finishtime']) / 3600 / 24);

					if ($refunddays < $days) {
						show_json(0, '订单完成已超过 ' . $refunddays . ' 天, 无法发起退款申请!');
					}
				}
				else {
					show_json(0, '订单完成, 无法申请退款!');
				}
			}
		}

		if ($order['status'] == 1) {
			$order['refund_button'] = '退款';
		}
		else {
			$order['refund_button'] = '售后';
		}

		$order['refundprice'] = $order['price'] + $order['deductcredit2'];

		if (2 <= $order['status']) {
			$order['refundprice'] -= $order['dispatchprice'];
		}

		$refundid = $order['refundid'];

		if ($_W['ispost']) {
			if (!empty($_GPC['cancel'])) {
				$change_refund = array();
				$change_refund['status'] = -2;
				$change_refund['refundtime'] = time();
				pdo_update('sz_yi_order_refund', $change_refund, array('id' => $refundid, 'uniacid' => $uniacid));
				pdo_update('sz_yi_order', array('refundstate' => 0), array('id' => $orderid, 'uniacid' => $uniacid));
				show_json(1);
			}
			else {
				$refunddata = $_GPC['refunddata'];
				$rtype = $refunddata['rtype'];

				if ($rtype != 2) {
					$price = $refunddata['price'];

					if (empty($price)) {
						show_json(2, '退款金额不能为0元');
					}

					if ($order['refundprice'] < $price) {
						show_json(3, '退款金额不能超过' . $order['refundprice'] . '元');
					}

					$price = trim($refunddata['price']);
				}
				else {
					$price = 0;
				}

				if (empty($refunddata['images'])) {
					$imgs = '';
				}
				else {
					$imgs = iserializer($refunddata['images']);
				}

				$refund = array('uniacid' => $uniacid, 'applyprice' => $price, 'rtype' => $rtype, 'reason' => trim($refunddata['reason']), 'content' => trim($refunddata['content']), 'imgs' => $imgs);

				if ($refund['rtype'] == 2) {
					$refundstate = 2;
				}
				else {
					$refundstate = 1;
				}

				if ($order['refundstate'] == 0) {
					$refund['createtime'] = time();
					$refund['orderid'] = $orderid;
					$refund['orderprice'] = $order['refundprice'];
					$refund['refundno'] = m('common')->createNO('order_refund', 'refundno', 'SR');
					pdo_insert('sz_yi_order_refund', $refund);
					$refundid = pdo_insertid();
					pdo_update('sz_yi_order', array('refundid' => $refundid, 'refundstate' => $refundstate), array('id' => $orderid, 'uniacid' => $uniacid));
				}
				else {
					pdo_update('sz_yi_order', array('refundstate' => $refundstate), array('id' => $orderid, 'uniacid' => $uniacid));
					pdo_update('sz_yi_order_refund', $refund, array('id' => $refundid, 'uniacid' => $uniacid));
				}

				m('notice')->sendOrderMessage($orderid, true);
				show_json(1);
			}
		}

		$refund = false;
		$imgnum = 0;

		if (0 < $order['refundstate']) {
			if (!empty($refundid)) {
				$refund = pdo_fetch('select * from ' . tablename('sz_yi_order_refund') . ' where id=:id and uniacid=:uniacid and orderid=:orderid limit 1', array(':id' => $refundid, ':uniacid' => $uniacid, ':orderid' => $orderid));
				$refund['createtime'] = date('Y-m-d H:i', $refund['createtime']);

				if (!empty($refund['refundaddress'])) {
					$refund['refundaddress'] = iunserializer($refund['refundaddress']);
					$refund['address_info'] = '收件人:' . $refund['refundaddress']['name'];
					$refund['address_info'] .= ' 手机: ' . $refund['refundaddress']['mobile'];

					if (!empty($refund['refundaddress']['tel'])) {
						$refund['address_info'] .= ' 电话: ' . $refund['refundaddress']['tel'];
					}

					if (!empty($refund['refundaddress']['zipcode'])) {
						$refund['address_info'] .= ' 邮政编码: ' . $refund['refundaddress']['zipcode'];
					}

					$refund['address_info'] .= ' 退货地址: ' . $refund['refundaddress']['province'] . $refund['refundaddress']['city'] . $refund['refundaddress']['area'] . ' ' . $refund['refundaddress']['address'];
				}
			}

			if (!empty($refund['imgs'])) {
				$refund['imgs'] = iunserializer($refund['imgs']);
				$imgnum = count($refund['imgs']);
				$refund_urls = array();

				foreach ($refund['imgs'] as $k => $v) {
					$refund_urls[$k] = tomedia($v);
				}

				$refund['urls'] = $refund_urls;
			}
		}

		if (empty($refund)) {
			$show_price = $order['refundprice'];
		}
		else {
			$show_price = $refund['applyprice'];
		}

		show_json(1, array('showprice' => $show_price, 'order' => $order, 'refund' => $refund, 'imgnum' => $imgnum));
	}
	else if ($operation == 'comment') {
		$orderid = intval($_GPC['orderid']);
		$order = pdo_fetch('select id,status,iscomment from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));

		if (empty($order)) {
			show_json(0, '订单未找到!');
		}

		if (($order['status'] != 3) && ($order['status'] != 4)) {
			show_json(0, '订单未收货，不能评价!');
		}

		if (2 <= $order['iscomment']) {
			show_json(0, '您已经评价了!');
		}

		if ($_W['ispost'] && ($_GPC['from_client'] == 'post')) {
			$member = m('member')->getMember($openid);
			$comments = $_GPC['comments'];

			if (!is_array($comments)) {
				show_json(0, '数据出错，请重试!');
			}

			foreach ($comments as $c) {
				$old_c = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order_comment') . ' where uniacid=:uniacid and orderid=:orderid and goodsid=:goodsid limit 1', array(':uniacid' => $_W['uniacid'], ':goodsid' => $c['goodsid'], ':orderid' => $orderid));

				if (empty($old_c)) {
					$comment = array('uniacid' => $uniacid, 'orderid' => $orderid, 'goodsid' => $c['goodsid'], 'level' => $c['level'], 'content' => $c['content'], 'images' => is_array($c['images']) ? iserializer($c['images']) : iserializer(array()), 'openid' => $openid, 'nickname' => $member['nickname'], 'headimgurl' => $member['avatar'], 'createtime' => time());
					pdo_insert('sz_yi_order_comment', $comment);
				}
				else {
					$comment = array('append_content' => $c['content'], 'append_images' => is_array($c['images']) ? iserializer($c['images']) : iserializer(array()));
					pdo_update('sz_yi_order_comment', $comment, array('uniacid' => $_W['uniacid'], 'goodsid' => $c['goodsid'], 'orderid' => $orderid));
				}
			}

			if ($order['iscomment'] <= 0) {
				$d['iscomment'] = 1;
			}
			else {
				$d['iscomment'] = 2;
			}

			pdo_update('sz_yi_order', $d, array('id' => $orderid, 'uniacid' => $uniacid));
			show_json(1);
		}

		$goods = pdo_fetchall('select og.id,og.goodsid,og.price,g.title,g.thumb,og.total,g.credit,og.optionid,o.title as optiontitle from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' left join ' . tablename('sz_yi_goods_option') . ' o on o.id=og.optionid ' . ' where og.orderid=:orderid and og.uniacid=:uniacid ', array(':uniacid' => $uniacid, ':orderid' => $orderid));
		$goods = set_medias($goods, 'thumb');
		show_json(1, array('order' => $order, 'goods' => $goods));
	}
	else {
		if ($operation == 'delete') {
			$orderid = intval($_GPC['orderid']);
			$order = pdo_fetch('select id,status,refundstate,refundid from ' . tablename('sz_yi_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));

			if (empty($order)) {
				show_json(0, '订单未找到!');
			}

			if (($order['status'] != 3) && ($order['status'] != -1)) {
				show_json(0, '订单无交易，不能删除!');
			}

			if ((0 < $order['refundstate']) && !empty($order['refundid'])) {
				$change_refund = array();
				$change_refund['status'] = -2;
				$change_refund['refundtime'] = time();
				pdo_update('sz_yi_order_refund', $change_refund, array('id' => $order['refundid'], 'uniacid' => $uniacid));
			}

			pdo_update('sz_yi_order', array('userdeleted' => 1, 'refundstate' => 0), array('id' => $order['id'], 'uniacid' => $uniacid));
			show_json(1);
		}
	}
}

if ($operation == 'refund') {
	$tradeset = m('common')->getSysset('trade');
	include $this->template('order/refund');
	return 1;
}

if ($operation == 'comment') {
	include $this->template('order/comment');
}

?>
