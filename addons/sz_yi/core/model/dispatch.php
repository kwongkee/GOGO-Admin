<?php
// 模块LTD提供
class Sz_DYi_Dispatch
{
	public function getDispatchPrice($dephp_0, $dephp_1, $dephp_2 = -1)
	{
		if (empty($dephp_1)) {
			return 0;
		}

		$_obf_DSg_EjMUKD4lHCcoKFshEBdbIhs4CxE_ = 0;

		if ($dephp_2 == -1) {
			$dephp_2 = $dephp_1['calculatetype'];
		}

		if ($dephp_2 == 1) {
			if ($dephp_0 <= $dephp_1['firstnum']) {
				$_obf_DSg_EjMUKD4lHCcoKFshEBdbIhs4CxE_ = floatval($dephp_1['firstnumprice']);
			}
			else {
				$_obf_DSg_EjMUKD4lHCcoKFshEBdbIhs4CxE_ = floatval($dephp_1['firstnumprice']);
				$_obf_DR4RJyctDwUUHT4iLCQ_KxYyJDwBOCI_ = $dephp_0 - floatval($dephp_1['firstnum']);
				$_obf_DQQdLwYdMB03FCgMGC4NPxALDD4xDwE_ = (floatval($dephp_1['secondnum']) <= 0 ? 1 : floatval($dephp_1['secondnum']));
				$_obf_DTcJHzMmBhAbNCEeLhgvOBMbXCoiJAE_ = 0;

				if (($_obf_DR4RJyctDwUUHT4iLCQ_KxYyJDwBOCI_ % $_obf_DQQdLwYdMB03FCgMGC4NPxALDD4xDwE_) == 0) {
					$_obf_DTcJHzMmBhAbNCEeLhgvOBMbXCoiJAE_ = ($_obf_DR4RJyctDwUUHT4iLCQ_KxYyJDwBOCI_ / $_obf_DQQdLwYdMB03FCgMGC4NPxALDD4xDwE_) * floatval($dephp_1['secondnumprice']);
				}
				else {
					$_obf_DTcJHzMmBhAbNCEeLhgvOBMbXCoiJAE_ = ((int) ($_obf_DR4RJyctDwUUHT4iLCQ_KxYyJDwBOCI_ / $_obf_DQQdLwYdMB03FCgMGC4NPxALDD4xDwE_) + 1) * floatval($dephp_1['secondnumprice']);
				}

				$_obf_DSg_EjMUKD4lHCcoKFshEBdbIhs4CxE_ += $_obf_DTcJHzMmBhAbNCEeLhgvOBMbXCoiJAE_;
			}
		}
		else if ($dephp_0 <= $dephp_1['firstweight']) {
			$_obf_DSg_EjMUKD4lHCcoKFshEBdbIhs4CxE_ = floatval($dephp_1['firstprice']);
		}
		else {
			$_obf_DSg_EjMUKD4lHCcoKFshEBdbIhs4CxE_ = floatval($dephp_1['firstprice']);
			$_obf_DR4RJyctDwUUHT4iLCQ_KxYyJDwBOCI_ = $dephp_0 - floatval($dephp_1['firstweight']);
			$_obf_DQQdLwYdMB03FCgMGC4NPxALDD4xDwE_ = (floatval($dephp_1['secondweight']) <= 0 ? 1 : floatval($dephp_1['secondweight']));
			$_obf_DTcJHzMmBhAbNCEeLhgvOBMbXCoiJAE_ = 0;

			if (($_obf_DR4RJyctDwUUHT4iLCQ_KxYyJDwBOCI_ % $_obf_DQQdLwYdMB03FCgMGC4NPxALDD4xDwE_) == 0) {
				$_obf_DTcJHzMmBhAbNCEeLhgvOBMbXCoiJAE_ = ($_obf_DR4RJyctDwUUHT4iLCQ_KxYyJDwBOCI_ / $_obf_DQQdLwYdMB03FCgMGC4NPxALDD4xDwE_) * floatval($dephp_1['secondprice']);
			}
			else {
				$_obf_DTcJHzMmBhAbNCEeLhgvOBMbXCoiJAE_ = ((int) ($_obf_DR4RJyctDwUUHT4iLCQ_KxYyJDwBOCI_ / $_obf_DQQdLwYdMB03FCgMGC4NPxALDD4xDwE_) + 1) * floatval($dephp_1['secondprice']);
			}

			$_obf_DSg_EjMUKD4lHCcoKFshEBdbIhs4CxE_ += $_obf_DTcJHzMmBhAbNCEeLhgvOBMbXCoiJAE_;
		}

		return $_obf_DSg_EjMUKD4lHCcoKFshEBdbIhs4CxE_;
	}

	public function getCityDispatchPrice($dephp_7, $dephp_8, $dephp_0, $dephp_1)
	{
		if (is_array($dephp_7) && (0 < count($dephp_7))) {
			foreach ($dephp_7 as $_obf_DQEYLy4oFQsCMxYVPxY0KBgXBzA5FwE_) {
				$_obf_DTEpLg4WDBwyGTg0FDQ3HxwcKQUTOTI_ = explode(';', $_obf_DQEYLy4oFQsCMxYVPxY0KBgXBzA5FwE_['citys']);
				if (in_array($dephp_8, $_obf_DTEpLg4WDBwyGTg0FDQ3HxwcKQUTOTI_) && !empty($_obf_DTEpLg4WDBwyGTg0FDQ3HxwcKQUTOTI_)) {
					return $this->getDispatchPrice($dephp_0, $_obf_DQEYLy4oFQsCMxYVPxY0KBgXBzA5FwE_, $dephp_1['calculatetype']);
				}
			}
		}

		return $this->getDispatchPrice($dephp_0, $dephp_1);
	}

	public function payResult($params)
	{
		global $_W;
		$fee = $params['fee'];
		$uniacid = $_W['uniacid'];
		$data = array('status' => $params['result'] == 'success' ? 1 : 0);
		$ordersn = $params['tid'];
		$_obf_DR82HBYYExwbJgwxJVwxFBANKSI2MAE_ = pdo_fetchall('select * from ' . tablename('sz_yi_order') . ' where ordersn_general=:ordersn_general and uniacid=:uniacid', array(':ordersn_general' => $ordersn, ':uniacid' => $uniacid));

		if (1 < count($_obf_DR82HBYYExwbJgwxJVwxFBANKSI2MAE_)) {
			$order = array();
			$order['ordersn'] = $ordersn;
			$orderid = array();

			foreach ($_obf_DR82HBYYExwbJgwxJVwxFBANKSI2MAE_ as $key => $val) {
				$order['price'] += $val['price'];
				$order['deductcredit2'] += $val['deductcredit2'];
				$order['ordersn2'] += $val['ordersn2'];
				$orderid[] = $val['id'];
			}

			$order['dispatchtype'] = $val['dispatchtype'];
			$order['addressid'] = $val['addressid'];
			$order['isvirtual'] = $val['isvirtual'];
			$order['carrier'] = $val['carrier'];
			$order['status'] = $val['status'];
			$order['virtual'] = $val['virtual'];
			$order['couponid'] = $val['couponid'];
		}
		else {
			$order = $_obf_DR82HBYYExwbJgwxJVwxFBANKSI2MAE_[0];
			$orderid = $order['id'];
			$verify_set = m('common')->getSetData();
			$allset = iunserializer($verify_set['plugins']);
			if (($order['isverify'] == 1) && ($allset['verify']['sendcode'] == 1)) {
				$carriers = unserialize($order['carrier']);
				$mobile = $carriers['carrier_mobile'];
				$type = 'verify';
				$order_goods = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order_goods') . ' WHERE orderid=:id and uniacid=:uniacid', array(':id' => $orderid, ':uniacid' => $_W['uniacid']));
				$goodstitle = pdo_fetchcolumn('SELECT title FROM ' . tablename('sz_yi_goods') . ' WHERE id=:id and uniacid=:uniacid', array(':id' => $order_goods['goodsid'], ':uniacid' => $_W['uniacid']));
				$store = pdo_fetch(' SELECT * FROM ' . tablename('sz_yi_store') . ' WHERE id=' . $order['storeid']);
				$issendsms = $this->sendSms($mobile, $order['verifycode'], 'reg', $type, $carriers['carrier_realname'], $goodstitle, $order_goods['total'], $store['tel']);
			}
		}

		$log = pdo_fetch('select * from ' . tablename('core_paylog') . ' where `uniacid`=:uniacid and fee=:fee and `module`=:module and `tid`=:tid limit 1', array(':uniacid' => $_W['uniacid'], ':module' => 'sz_yi', ':fee' => $fee, ':tid' => $ordersn));

		if (empty($log)) {
			show_json(-1, '订单金额错误, 请重试!');
			exit();
		}

		if ($params['from'] == 'return') {
			$address = false;

			if (empty($order['dispatchtype'])) {
				$address = pdo_fetch('select realname,mobile,address from ' . tablename('sz_yi_member_address') . ' where id=:id limit 1', array(':id' => $order['addressid']));
			}

			$carrier = false;
			if (($order['dispatchtype'] == 1) || ($order['isvirtual'] == 1)) {
				$carrier = unserialize($order['carrier']);
			}

			if ($params['type'] == 'cash') {
				return array('result' => 'success', 'order' => $order, 'address' => $address, 'carrier' => $carrier);
			}

			if (is_array($orderid)) {
				$orderids = implode(',', $orderid);
				$order_update = 'id in (' . $orderids . ')';
				$_obf_DQY_EAIrJjkCHxMvPg8oOTQfKDgEFAE_ = 'o.id in (' . $orderids . ')';
				$goods_where = 'og.orderid in (' . $orderids . ')';
			}
			else {
				$order_update = 'id = ' . $orderid;
				$_obf_DQY_EAIrJjkCHxMvPg8oOTQfKDgEFAE_ = 'o.id = ' . $orderid;
				$goods_where = 'og.orderid = ' . $orderid;
			}

			if ($order['status'] == 0) {
				$pv = p('virtual');
				if (!empty($order['virtual']) && $pv) {
					$pv->pay($order);
				}
				else {
					if (p('channel')) {
						if ($params['ischannelpay'] == 1) {
							pdo_query('update ' . tablename('sz_yi_order') . ' set status=3, paytime=' . time() . ', finishtime=' . time() . ', pay_ordersn=ordersn_general, ordersn_general=ordersn where ' . $order_update . ' and uniacid=\'' . $uniacid . '\' ');
						}
						else {
							pdo_query('update ' . tablename('sz_yi_order') . ' set status=1, paytime=' . time() . ', pay_ordersn=ordersn_general, ordersn_general=ordersn where ' . $order_update . ' and uniacid=\'' . $uniacid . '\' ');
						}
					}
					else {
						pdo_query('update ' . tablename('sz_yi_order') . ' set status=1, paytime=' . time() . ', pay_ordersn=ordersn_general, ordersn_general=ordersn where ' . $order_update . ' and uniacid=\'' . $uniacid . '\' ');
					}

					if (0 < $order['deductcredit2']) {
						$shopset = m('common')->getSysset('shop');
						m('member')->setCredit($order['openid'], 'credit2', 0 - $order['deductcredit2'], array(0, $shopset['name'] . '余额抵扣: ' . $order['deductcredit2'] . ' 订单号: ' . $order['ordersn']));
					}

					if (is_array($orderid)) {
						foreach ($_obf_DR82HBYYExwbJgwxJVwxFBANKSI2MAE_ as $k => $v) {
							$this->setStocksAndCredits($v['id'], 1);
							if (p('coupon') && !empty($v['couponid'])) {
								p('coupon')->backConsumeCoupon($v['id']);
							}

							m('notice')->sendOrderMessage($v['id']);

							if (p('commission')) {
								p('commission')->checkOrderPay($v['id']);
							}
						}
					}
					else {
						$this->setStocksAndCredits($orderid, 1);
						if (p('coupon') && !empty($order['couponid'])) {
							p('coupon')->backConsumeCoupon($orderid);
						}

						m('notice')->sendOrderMessage($orderid);

						if (p('commission')) {
							p('commission')->checkOrderPay($orderid);
						}
					}
				}
			}

			if (p('hotel')) {
				$set = set_medias(m('common')->getSysset('shop'), array('logo', 'img'));
				$print_order = $order;
				$ordergoods = pdo_fetchall('select * from ' . tablename('sz_yi_order_goods') . ' where uniacid=' . $_W['uniacid'] . ' and orderid=' . $orderid);

				foreach ($ordergoods as $key => $value) {
					$ordergoods[$key]['goodstitle'] = pdo_fetchcolumn('select title from ' . tablename('sz_yi_goods') . ' where uniacid=' . $_W['uniacid'] . ' and id=' . $value['goodsid']);
					$ordergoods[$key]['totalmoney'] = number_format($ordergoods[$key]['price'] * $value['total'], 2);
					$ordergoods[$key]['print_id'] = pdo_fetchcolumn('select print_id from ' . tablename('sz_yi_goods') . ' where uniacid=' . $_W['uniacid'] . ' and id=' . $value['goodsid']);
					$ordergoods[$key]['type'] = pdo_fetchcolumn('select type from ' . tablename('sz_yi_goods') . ' where uniacid=' . $_W['uniacid'] . ' and id=' . $value['goodsid']);
				}

				$print_order['goods'] = $ordergoods;
				$print_id = $print_order['goods'][0]['print_id'];
				$goodtype = $print_order['goods'][0]['type'];

				if ($print_id != '') {
					$print_detail = pdo_fetch('select * from ' . tablename('sz_yi_print_list') . ' where uniacid=' . $_W['uniacid'] . ' and id=' . $print_id);
					if (!empty($print_detail) && ($print_detail['status'] == '0')) {
						$member_code = $print_detail['member_code'];
						$device_no = $print_detail['print_no'];
						$key = $print_detail['key'];
						include IA_ROOT . '/addons/sz_yi/core/model/print.php';

						if ($goodtype == '99') {
							$sql2 = 'SELECT * FROM ' . tablename('sz_yi_order_room') . ' WHERE `orderid` = :orderid';
							$params2 = array(':orderid' => $orderid);
							$price_list = pdo_fetchall($sql2, $params2);
							$msgNo = testSendFreeMessage($print_order, $member_code, $device_no, $key, $set, $price_list);
						}
						else {
							if ($goodtype == '1') {
								$msgNo = testSendFreeMessageshop($print_order, $member_code, $device_no, $key, $set);
							}
						}
					}
				}
			}

			$orderdetail = pdo_fetch('select o.dispatchprice,o.ordersn,o.price,og.optionname as optiontitle,og.optionid,og.total from ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_order_goods') . 'og on og.orderid = o.id where ' . $_obf_DQY_EAIrJjkCHxMvPg8oOTQfKDgEFAE_ . ' and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid']));
			$sql = 'SELECT og.goodsid,og.total,g.title,g.thumb,og.price,og.optionname as optiontitle,og.optionid FROM ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on og.goodsid = g.id ' . ' where ' . $goods_where . ' order by og.id asc';
			$orderdetail['goods1'] = set_medias(pdo_fetchall($sql), 'thumb');
			$_obf_DSELDycVLjgrHAQFHBIlERgtHTYHFSI_ = p('love');

			if ($_obf_DSELDycVLjgrHAQFHBIlERgtHTYHFSI_) {
				$_obf_DSELDycVLjgrHAQFHBIlERgtHTYHFSI_->checkOrder($goods_where, $order['openid'], 0);
			}

			$orderdetail['goodscount'] = count($orderdetail['goods1']);
			return array('result' => 'success', 'order' => $order, 'address' => $address, 'carrier' => $carrier, 'virtual' => $order['virtual'], 'goods' => $orderdetail, 'verifycode' => $issendsms);
		}
	}

	public function sendSms($mobile, $code, $templateType = 'reg', $type = 'check', $name, $title, $total, $tel)
	{
		$set = m('common')->getSysset();

		if ($set['sms']['type'] == 1) {
			return send_sms($set['sms']['account'], $set['sms']['password'], $mobile, $code, $type, $name, $title, $total, $tel);
		}
		if ($set['sms']['type'] == 3) { //20170911 han
			return send_sms_aliyun($mobile, $code, $templateType);
		}
		return send_sms_alidayu($mobile, $code, $templateType);
	}

	public function setStocksAndCredits($orderid = '', $type = 0)
	{
		global $_W;
		$order = pdo_fetch('select id,ordersn,price,openid,dispatchtype,addressid,carrier,status from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $orderid));
		$cond = '';

		if (p('channel')) {
			$cond = ',og.channel_id,og.ischannelpay';
		}

		$goods = pdo_fetchall('select og.id,og.goodsid' . $cond . ',og.total,g.totalcnf,og.realprice, g.credit,og.optionid,g.total as goodstotal,og.optionid,g.sales,g.salesreal from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.orderid=:orderid and og.uniacid=:uniacid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));
		$credits = 0;

		foreach ($goods as $g) {
			$stocktype = 0;

			if ($type == 0) {
				if ($g['totalcnf'] == 0) {
					$stocktype = -1;
				}
			}
			else if ($type == 1) {
				if ($g['totalcnf'] == 1) {
					$stocktype = -1;
				}
			}
			else {
				if ($type == 2) {
					if (1 <= $order['status']) {
						if ($g['totalcnf'] == 1) {
							$stocktype = 1;
						}
					}
					else {
						if ($g['totalcnf'] == 0) {
							$stocktype = 1;
						}
					}
				}
			}

			if (!empty($stocktype)) {
				if (!empty($g['optionid'])) {
					if (p('channel')) {
						if (!empty($g['channel_id'])) {
							$my_info = p('channel')->getInfo($order['openid'], $g['goodsid'], $g['optionid'], $g['total']);

							if (!empty($my_info['up_level']['stock'])) {
								$stock = -1;

								if ($stocktype == 1) {
									$stock = $my_info['up_level']['stock']['stock_total'] + $g['total'];
								}
								else {
									if ($stocktype == -1) {
										$stock = $my_info['up_level']['stock']['stock_total'] - $g['total'];
									}
								}

								if ($stock != -1) {
									pdo_update('sz_yi_channel_stock', array('stock_total' => $stock), array('uniacid' => $_W['uniacid'], 'goodsid' => $g['goodsid'], 'openid' => $my_info['up_level']['openid'], 'optionid' => $g['optionid']));
									$channel = true;
								}

								$goods_price = pdo_fetchcolumn('SELECT marketprice FROM ' . tablename('sz_yi_goods') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND id=' . $g['goodsid']);
								$up_mem = m('member')->getInfo($order['openid']);
								$log_data = array('goodsid' => $g['goodsid'], 'optionid' => $g['optionid'], 'order_goodsid' => $g['id'], 'uniacid' => $_W['uniacid'], 'every_turn' => $g['total'], 'goods_price' => $goods_price, 'surplus_stock' => $stock, 'mid' => $up_mem['id'], 'paytime' => time());

								if (!empty($my_info['up_level'])) {
									$log_data['openid'] = $my_info['up_level']['openid'];
								}

								if (!empty($g['ischannelpay'])) {
									$log_data['every_turn_price'] = ($goods_price * $my_info['my_level']['purchase_discount']) / 100;
									$log_data['every_turn_discount'] = $my_info['my_level']['purchase_discount'];
									$log_data['type'] = 2;
									pdo_insert('sz_yi_channel_stock_log', $log_data);
								}
								else {
									$log_data['every_turn_price'] = $goods_price;
									$log_data['every_turn_discount'] = 0;
									$log_data['type'] = 3;
									pdo_insert('sz_yi_channel_stock_log', $log_data);
								}
							}
						}
					}

					if (empty($channel)) {
						$option = m('goods')->getOption($g['goodsid'], $g['optionid']);
						if (!empty($option) && ($option['stock'] != -1)) {
							$stock = -1;

							if ($stocktype == 1) {
								$stock = $option['stock'] + $g['total'];
							}
							else {
								if ($stocktype == -1) {
									$stock = $option['stock'] - $g['total'];
									($stock <= 0) && ($stock = 0);
								}
							}

							if ($stock != -1) {
								pdo_update('sz_yi_goods_option', array('stock' => $stock), array('uniacid' => $_W['uniacid'], 'goodsid' => $g['goodsid'], 'id' => $g['optionid']));
							}
						}
					}
				}

				if (p('channel')) {
					if (empty($channel)) {
						if (!empty($g['channel_id'])) {
							$my_info = p('channel')->getInfo($order['openid'], $g['goodsid'], 0, $g['total']);

							if (!empty($my_info['up_level']['stock'])) {
								$totalstock = -1;

								if ($stocktype == 1) {
									$totalstock = $my_info['up_level']['stock']['stock_total'] + $g['total'];
								}
								else {
									if ($stocktype == -1) {
										$totalstock = $my_info['up_level']['stock']['stock_total'] - $g['total'];
									}
								}

								if ($totalstock != -1) {
									pdo_update('sz_yi_channel_stock', array('stock_total' => $totalstock), array('uniacid' => $_W['uniacid'], 'goodsid' => $g['goodsid'], 'openid' => $my_info['up_level']['openid']));
									$channels = true;
								}

								$goods_price = pdo_fetchcolumn('SELECT marketprice FROM ' . tablename('sz_yi_goods') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND id=' . $g['goodsid']);
								$up_mem = m('member')->getInfo($order['openid']);
								$log_data = array('goodsid' => $g['goodsid'], 'order_goodsid' => $g['id'], 'uniacid' => $_W['uniacid'], 'every_turn' => $g['total'], 'goods_price' => $goods_price, 'surplus_stock' => $totalstock, 'mid' => $up_mem['id'], 'paytime' => time());

								if (!empty($my_info['up_level'])) {
									$log_data['openid'] = $my_info['up_level']['openid'];
								}

								if (!empty($g['ischannelpay'])) {
									$log_data['every_turn_price'] = ($goods_price * $my_info['my_level']['purchase_discount']) / 100;
									$log_data['every_turn_discount'] = $my_info['my_level']['purchase_discount'];
									$log_data['type'] = 2;
									pdo_insert('sz_yi_channel_stock_log', $log_data);
								}
								else {
									$log_data['every_turn_price'] = $goods_price;
									$log_data['every_turn_discount'] = 0;
									$log_data['type'] = 3;
									pdo_insert('sz_yi_channel_stock_log', $log_data);
								}
							}
						}
					}
				}

				if (empty($channels)) {
					if (!empty($g['goodstotal']) && ($g['goodstotal'] != -1)) {
						$totalstock = -1;

						if ($stocktype == 1) {
							$totalstock = $g['goodstotal'] + $g['total'];
						}
						else {
							if ($stocktype == -1) {
								$totalstock = $g['goodstotal'] - $g['total'];
								($totalstock <= 0) && ($totalstock = 0);
							}
						}

						if ($totalstock != -1) {
							pdo_update('sz_yi_goods', array('total' => $totalstock), array('uniacid' => $_W['uniacid'], 'id' => $g['goodsid']));
						}
					}
				}
			}

			$gcredit = trim($g['credit']);

			if (!empty($gcredit)) {
				if (strexists($gcredit, '%')) {
					$credits += intval((floatval(str_replace('%', '', $gcredit)) / 100) * $g['realprice']);
				}
				else {
					$credits += intval($g['credit']) * $g['total'];
				}
			}

			if ($type == 0) {
				pdo_update('sz_yi_goods', array('sales' => $g['sales'] + $g['total']), array('uniacid' => $_W['uniacid'], 'id' => $g['goodsid']));
			}
			else {
				if ($type == 1) {
					if (1 <= $order['status']) {
						$salesreal = pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on o.id = og.orderid ' . ' where og.goodsid=:goodsid and o.status>=1 and o.uniacid=:uniacid limit 1', array(':goodsid' => $g['goodsid'], ':uniacid' => $_W['uniacid']));
						pdo_update('sz_yi_goods', array('salesreal' => $salesreal), array('id' => $g['goodsid']));
					}
				}
			}
		}

		if (0 < $credits) {
			$shopset = m('common')->getSysset('shop');

			if ($type == 1) {
				m('member')->setCredit($order['openid'], 'credit1', $credits, array(0, $shopset['name'] . '购物积分 订单号: ' . $order['ordersn']));
				return NULL;
			}

			if ($type == 2) {
				if (1 <= $order['status']) {
					m('member')->setCredit($order['openid'], 'credit1', 0 - $credits, array(0, $shopset['name'] . '购物取消订单扣除积分 订单号: ' . $order['ordersn']));
				}
			}
		}
	}

	public function getDefaultDispatch($supplier_uid = 0)
	{
		global $_W;
		$sql = 'select * from ' . tablename('sz_yi_dispatch') . ' where isdefault=1 and uniacid=:uniacid and enabled=1 and supplier_uid=:supplier_uid Limit 1';
		$prem = array(':supplier_uid' => $supplier_uid, ':uniacid' => $_W['uniacid']);
		$_obf_DQguISQsFTMsDTM7PhctMRcoWwE4EDI_ = pdo_fetch($sql, $prem);
		return $_obf_DQguISQsFTMsDTM7PhctMRcoWwE4EDI_;
	}

	public function getNewDispatch($supplier_uid = 0)
	{
		global $_W;
		$sql = 'select * from ' . tablename('sz_yi_dispatch') . ' where uniacid=:uniacid and enabled=1 and supplier_uid=:supplier_uid order by id desc Limit 1';
		$prem = array(':supplier_uid' => $supplier_uid, ':uniacid' => $_W['uniacid']);
		$_obf_DQEvJh1ACj0KKFw4PiQVJi4jGxYePzI_ = pdo_fetch($sql, $prem);
		return $_obf_DQEvJh1ACj0KKFw4PiQVJi4jGxYePzI_;
	}

	public function getOneDispatch($dispatch_id, $supplier_uid = 0)
	{
		global $_W;
		$sql = 'select * from ' . tablename('sz_yi_dispatch') . ' where id=:id and uniacid=:uniacid and enabled=1 and supplier_uid=:supplier_uid Limit 1';
		$prem = array(':supplier_uid' => $supplier_uid, ':id' => $dispatch_id, ':uniacid' => $_W['uniacid']);
		$_obf_DT0VWxAICkAxDy0OFz0VIQ4SBQk_LTI_ = pdo_fetch($sql, $prem);
		return $_obf_DT0VWxAICkAxDy0OFz0VIQ4SBQk_LTI_;
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
