<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

if (!class_exists('CashierModel')) {
	class CashierModel extends PluginModel
	{
		public function payResult($params)
		{
			global $_W;
			$fee = $params['fee'];
			$data = array('status' => $params['result'] == 'success' ? 1 : 0);
			$ordersn = $params['tid'];
			$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where  (ordersn=:ordersn or pay_ordersn=:ordersn or ordersn_general=:ordersn) and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':ordersn' => $ordersn));
			$store = pdo_fetch('select s.* from ' . tablename('sz_yi_cashier_order') . ' o inner join ' . tablename('sz_yi_cashier_store') . ' s on o.cashier_store_id = s.id where o.order_id=:orderid and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':orderid' => $order['id']));
			$log = pdo_fetch('select * from ' . tablename('core_paylog') . ' where `uniacid`=:uniacid and fee=:fee and `module`=:module and `tid`=:tid limit 1', array(':uniacid' => $_W['uniacid'], ':module' => 'sz_yi', ':fee' => $fee, ':tid' => $params['tid']));

			if (empty($log)) {
				show_json(-1, '订单金额错误, 请重试!');
				exit();
			}

			$orderid = $order['id'];

			if ($params['from'] == 'return') {
				if (($order['status'] == 0) || ($order['status'] == 1)) {
					pdo_update('sz_yi_order', array('status' => 3, 'paytime' => time(), 'finishtime' => time()), array('id' => $orderid));

					if (0 < $order['deductcredit2']) {
						$shopset = m('common')->getSysset('shop');
						m('member')->setCredit($order['openid'], 'credit2', 0 - $order['deductcredit2'], array(0, $shopset['name'] . '余额抵扣: ' . $order['deductcredit2'] . ' 订单号: ' . $order['ordersn']));
					}

					$this->setCredits($orderid);

					if ($params['type'] != 'wechat') {
						m('notice')->sendOrderMessage($orderid);
					}

					if (p('commission')) {
						$this->calculateCommission($order['id']);
					}

					if ($store['condition'] <= $order['price']) {
						$this->setCoupon($orderid);
					}
				}
			}

			if (p('return') && ($store['isreturn'] == 1)) {
				p('return')->cumulative_order_amount($orderid);
			}

			if (p('commission')) {
				p('commission')->upgradeLevelByOrder($orderid);
			}
		}

		public function setCredits($orderid, $forStatistics = false)
		{
			global $_W;
			$order = pdo_fetch('select id,ordersn,openid,price from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $orderid));
			$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_order') . ' o inner join ' . tablename('sz_yi_cashier_store') . ' s on o.cashier_store_id = s.id where o.order_id=:orderid and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));
			$credits = 0;

			if (0 < $store['credit1']) {
				$credits += ($order['price'] * $store['credit1']) / 100;
			}

			if (0 < $credits) {
				if ($forStatistics) {
					return $credits;
				}

				m('member')->setCredit($order['openid'], 'credit1', $credits, array(0, '收银台奖励积分 订单号: ' . $order['ordersn']));
			}
		}

		public function setCredits2($orderid, $forStatistics = false)
		{
			global $_W;
			$order = pdo_fetch('select id,ordersn,openid,price from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $orderid));
			$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_order') . ' o inner join ' . tablename('sz_yi_cashier_store') . ' s on o.cashier_store_id = s.id where o.order_id=:orderid and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));
			$credits = 0;

			if (0 < $store['creditpack']) {
				$credits += ($order['price'] * $store['creditpack']) / 100;
			}

			if (0 < $credits) {
				if ($forStatistics) {
					return $credits;
				}

				m('member')->setCredit($order['openid'], 'credit2', $credits, array(0, '收银台奖励余额 订单号: ' . $order['ordersn']));
			}
		}

		public function setCoupon($orderid)
		{
			global $_W;
			$pcoupon = p('coupon');

			if (!$pcoupon) {
				return NULL;
			}

			$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $orderid));
			$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_order') . ' o inner join ' . tablename('sz_yi_cashier_store') . ' s on o.cashier_store_id = s.id where o.order_id=:orderid and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));
			$couponid = $store['coupon_id'];

			if (!$couponid) {
				return NULL;
			}

			$coupon = $pcoupon->getCoupon($couponid);

			if (empty($coupon)) {
				return NULL;
			}

			$logData = array('uniacid' => $_W['uniacid'], 'openid' => $order['openid'], 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $couponid, 'status' => 1, 'paystatus' => -1, 'creditstatus' => -1, 'createtime' => time(), 'getfrom' => 0);
			pdo_insert('sz_yi_coupon_log', $logData);
			$data = array('uniacid' => $_W['uniacid'], 'openid' => $order['openid'], 'couponid' => $couponid, 'gettype' => 0, 'gettime' => time());
			pdo_insert('sz_yi_coupon_data', $data);
		}

		public function calculateCommission($orderid, $forStatistics = false)
		{
			global $_W;
			$_obf_DTsLMTsnBgMiDjMNNAsCKiULCBwXKDI_ = p('commission');

			if (!$_obf_DTsLMTsnBgMiDjMNNAsCKiULCBwXKDI_) {
				return NULL;
			}

			$set = $_obf_DTsLMTsnBgMiDjMNNAsCKiULCBwXKDI_->getSet();
			$order = pdo_fetch('select * from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $orderid));
			$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_order') . ' o inner join ' . tablename('sz_yi_cashier_store') . ' s on o.cashier_store_id = s.id where o.order_id=:orderid and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));

			if (0 < $set['level']) {
				$realprice = $order['price'];
				$commissions = array();
				$commissions['commission1'] = array('default' => 0);
				$commissions['commission2'] = array('default' => 0);
				$commissions['commission3'] = array('default' => 0);
				if ((1 <= $set['level']) && (0 < $store['commission1_rate'])) {
					$commissions['commission1'] = array('default' => round(($store['commission1_rate'] * $realprice) / 100, 2));
				}

				if ((2 <= $set['level']) && (0 < $store['commission2_rate'])) {
					$commissions['commission2'] = array('default' => round(($store['commission2_rate'] * $realprice) / 100, 2));
				}

				if ((3 <= $set['level']) && (0 < $store['commission3_rate'])) {
					$commissions['commission3'] = array('default' => round(($store['commission3_rate'] * $realprice) / 100, 2));
				}

				$levels = array('level1' => 0, 'level2' => 0, 'level3' => 0);

				if (!empty($order['agentid'])) {
					$user = m('member')->getMember($order['agentid']);
					$_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_ = array();
					if (($user['isagent'] == 1) && ($user['status'] == 1)) {
						$levels['level1'] = round($commissions['commission1']['default'], 2);
						$_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_['commission1'] = iserializer($commissions['commission1']);

						if (!empty($user['agentid'])) {
							$puser = m('member')->getMember($user['agentid']);
							$levels['level2'] = round($commissions['commission2']['default'], 2);
							$_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_['commission1'] = iserializer($commissions['commission1']);
							$_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_['commission2'] = iserializer($commissions['commission2']);

							if (!empty($puser['agentid'])) {
								$levels['level3'] = round($commissions['commission3']['default'], 2);
								$_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_['commission1'] = iserializer($commissions['commission1']);
								$_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_['commission2'] = iserializer($commissions['commission2']);
								$_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_['commission3'] = iserializer($commissions['commission3']);
							}
						}
					}

					$_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_['commissions'] = iserializer($levels);

					if ($forStatistics) {
						$total = 0;

						foreach ($levels as $level => $commission) {
							$total += $commission;
						}

						return NULL;
					}

					pdo_update('sz_yi_order_goods', $_obf_DTkKBjZcCggfGwNbKggPJxVcJi8zNRE_, array('orderid' => $orderid));
				}
			}
		}

		public function calculateBonus($orderid)
		{
			global $_W;
			$set = p('bonus')->getSet();
			$levels = p('bonus')->getLevels();
			$time = time();
			$order = pdo_fetch('select openid, address from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $orderid));
			$openid = $order['openid'];
			$address = unserialize($order['address']);
			$goods = pdo_fetchall('select id,realprice,price,goodsid,total,optionname,optionid,bonusmoney from ' . tablename('sz_yi_order_goods') . ' where orderid=:orderid and uniacid=:uniacid', array(':orderid' => $orderid, ':uniacid' => $_W['uniacid']));
			$member = m('member')->getInfo($openid);
			$levels = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_bonus_level') . ' WHERE uniacid = \'' . $_W['uniacid'] . '\' ORDER BY level asc');
			$isdistinction = (empty($set['isdistinction']) ? 0 : 1);

			foreach ($goods as $cinfo) {
				$cinfo['productprice'] = $cinfo['realprice'];
				$cinfo['marketprice'] = $cinfo['realprice'];
				$cinfo['costprice'] = $cinfo['realprice'];
				$price_all = p('bonus')->calculate_method($cinfo);
				if (!empty($cinfo['bonusmoney']) && (0 < $price_all)) {
					if (empty($set['selfbuy'])) {
						$_obf_DQYvBxoUORcvMzcYBCk5EQQvKzMBIzI_ = $member['agentid'];
					}
					else {
						$_obf_DQYvBxoUORcvMzcYBCk5EQQvKzMBIzI_ = $member['id'];
					}

					if (!empty($_obf_DQYvBxoUORcvMzcYBCk5EQQvKzMBIzI_) && !empty($set['start'])) {
						$parentAgents = p('bonus')->getParentAgents($_obf_DQYvBxoUORcvMzcYBCk5EQQvKzMBIzI_, $isdistinction);
						$_obf_DRM5JFspEyk1HhgaGxU9EjwYJTg_PxE_ = 0;

						foreach ($levels as $key => $level) {
							$levelid = $level['id'];

							if (array_key_exists($levelid, $parentAgents)) {
								if (0 < $level['agent_money']) {
									$setmoney = $level['agent_money'] / 100;
								}
								else {
									continue;
								}

								$_obf_DSMVChkDHjYxJT9cFxs9CAI1IhcLKSI_ = round($price_all * $setmoney, 2);

								if ($isdistinction == 0) {
									$bonus_money = $_obf_DSMVChkDHjYxJT9cFxs9CAI1IhcLKSI_ - $_obf_DRM5JFspEyk1HhgaGxU9EjwYJTg_PxE_;
									$_obf_DRM5JFspEyk1HhgaGxU9EjwYJTg_PxE_ = $_obf_DSMVChkDHjYxJT9cFxs9CAI1IhcLKSI_;
								}
								else {
									$bonus_money = $_obf_DSMVChkDHjYxJT9cFxs9CAI1IhcLKSI_;
								}

								if ($bonus_money <= 0) {
									continue;
								}

								$data = array('uniacid' => $_W['uniacid'], 'ordergoodid' => $cinfo['goodsid'], 'orderid' => $orderid, 'total' => $cinfo['total'], 'optionname' => $cinfo['optionname'], 'mid' => $parentAgents[$levelid], 'levelid' => $levelid, 'money' => $bonus_money, 'createtime' => $time);
								pdo_insert('sz_yi_bonus_goods', $data);
							}
						}
					}

					$_obf_DT8VNFsyNxgUKx8nLCoGCBwVOUALLAE_ = 0;

					if (!empty($set['area_start'])) {
						$bonus_commission3 = floatval($set['bonus_commission3']);

						if (!empty($bonus_commission3)) {
							$_obf_DTg2BDwODSIHHBYRFgwsCzMNFhwjPCI_ = pdo_fetchall('select id, bonus_area_commission from ' . tablename('sz_yi_member') . ' where bonus_province=\'' . $address['province'] . '\' and bonus_city=\'' . $address['city'] . '\' and bonus_district=\'' . $address['area'] . '\' and bonus_area=3 and uniacid=' . $_W['uniacid']);

							if (!empty($_obf_DTg2BDwODSIHHBYRFgwsCzMNFhwjPCI_)) {
								foreach ($_obf_DTg2BDwODSIHHBYRFgwsCzMNFhwjPCI_ as $key => $agent_district) {
									if (0 < $agent_district['bonus_area_commission']) {
										$_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ = round(($price_all * $agent_district['bonus_area_commission']) / 100, 2);
									}
									else {
										$_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ = round(($price_all * $set['bonus_commission3']) / 100, 2);
									}

									if (empty($set['isdistinction_area'])) {
										$bonus_area_money = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ - $_obf_DT8VNFsyNxgUKx8nLCoGCBwVOUALLAE_;
										$_obf_DT8VNFsyNxgUKx8nLCoGCBwVOUALLAE_ = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_;
									}
									else {
										$bonus_area_money = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_;
									}

									if (0 < $bonus_area_money) {
										$data = array('uniacid' => $_W['uniacid'], 'ordergoodid' => $cinfo['goodsid'], 'orderid' => $orderid, 'total' => $cinfo['total'], 'optionname' => $cinfo['optionname'], 'mid' => $agent_district['id'], 'bonus_area' => 3, 'money' => $bonus_area_money, 'createtime' => $time);
									}

									pdo_insert('sz_yi_bonus_goods', $data);
									if (empty($set['isdistinction_area']) || empty($set['isdistinction_area_all'])) {
										break;
									}
								}
							}
						}

						$bonus_commission2 = floatval($set['bonus_commission2']);

						if (!empty($bonus_commission2)) {
							$_obf_DRg9IxMpCgouNjVbJChAHRMJMjUyHgE_ = pdo_fetchall('select id, bonus_area_commission from ' . tablename('sz_yi_member') . ' where bonus_province=\'' . $address['province'] . '\' and bonus_city=\'' . $address['city'] . '\' and bonus_area=2 and uniacid=' . $_W['uniacid']);

							if (!empty($_obf_DRg9IxMpCgouNjVbJChAHRMJMjUyHgE_)) {
								foreach ($_obf_DRg9IxMpCgouNjVbJChAHRMJMjUyHgE_ as $key => $agent_city) {
									if (0 < $agent_city['bonus_area_commission']) {
										$_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ = round(($price_all * $agent_city['bonus_area_commission']) / 100, 2);
									}
									else {
										$_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ = round(($price_all * $set['bonus_commission2']) / 100, 2);
									}

									if (empty($set['isdistinction_area'])) {
										$bonus_area_money = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ - $_obf_DT8VNFsyNxgUKx8nLCoGCBwVOUALLAE_;
										$_obf_DT8VNFsyNxgUKx8nLCoGCBwVOUALLAE_ = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_;
									}
									else {
										$bonus_area_money = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_;
									}

									if (0 < $bonus_area_money) {
										$data = array('uniacid' => $_W['uniacid'], 'ordergoodid' => $cinfo['goodsid'], 'orderid' => $orderid, 'total' => $cinfo['total'], 'optionname' => $cinfo['optionname'], 'mid' => $agent_city['id'], 'bonus_area' => 2, 'money' => $bonus_area_money, 'createtime' => $time);
										pdo_insert('sz_yi_bonus_goods', $data);
									}

									if (empty($set['isdistinction_area']) || empty($set['isdistinction_area_all'])) {
										break;
									}
								}
							}
						}

						$bonus_commission1 = floatval($set['bonus_commission1']);

						if (!empty($bonus_commission1)) {
							$_obf_DSkWBwwIDAofCgwnCww2MxclNxoLARE_ = pdo_fetchall('select id, bonus_area_commission from ' . tablename('sz_yi_member') . ' where bonus_province=\'' . $address['province'] . '\' and bonus_area=1 and uniacid=' . $_W['uniacid']);

							if (!empty($_obf_DSkWBwwIDAofCgwnCww2MxclNxoLARE_)) {
								foreach ($_obf_DSkWBwwIDAofCgwnCww2MxclNxoLARE_ as $key => $agent_province) {
									if (0 < $agent_province['bonus_area_commission']) {
										$_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ = round(($price_all * $agent_province['bonus_area_commission']) / 100, 2);
									}
									else {
										$_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ = round(($price_all * $set['bonus_commission1']) / 100, 2);
									}

									if (empty($set['isdistinction_area'])) {
										$bonus_area_money = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_ - $_obf_DT8VNFsyNxgUKx8nLCoGCBwVOUALLAE_;
										$_obf_DT8VNFsyNxgUKx8nLCoGCBwVOUALLAE_ = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_;
									}
									else {
										$bonus_area_money = $_obf_DQMaJSIDKAQ_JxEMJhUqHQ4xHS9AGAE_;
									}

									if (0 < $bonus_area_money) {
										$data = array('uniacid' => $_W['uniacid'], 'ordergoodid' => $cinfo['goodsid'], 'orderid' => $orderid, 'total' => $cinfo['total'], 'optionname' => $cinfo['optionname'], 'mid' => $agent_province['id'], 'bonus_area' => 1, 'money' => $bonus_area_money, 'createtime' => $time);
										pdo_insert('sz_yi_bonus_goods', $data);
									}

									if (empty($set['isdistinction_area']) || empty($set['isdistinction_area_all'])) {
										break;
									}
								}
							}
						}
					}
				}
			}
		}

		public function redpack($openid, $orderid, $desc = '', $act_name = '', $remark = '')
		{
			global $_W;
			$order = pdo_fetch('select id,ordersn,openid,price from ' . tablename('sz_yi_order') . ' where id=:id limit 1', array(':id' => $orderid));
			$member = m('member')->getMember($openid);
			$store = pdo_fetch('select * from ' . tablename('sz_yi_cashier_order') . ' o inner join ' . tablename('sz_yi_cashier_store') . ' s on o.cashier_store_id = s.id where o.order_id=:orderid and o.uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));

			if ($order['price'] < $store['redpack_min']) {
				return NULL;
			}

			$money = ($order['price'] * $store['redpack']) / 100;
			if (($money < 1) || (200 < $money)) {
				$credit2 = $money;
				m('member')->setCredit($openid, 'credit2', $credit2, array(0, '收银台红包奖励(超过红包最大金额限制,直接加到余额) 订单号: ' . $order['ordersn']));
				return NULL;
			}

			if (empty($openid)) {
				return error(-1, 'openid不能为空');
			}

			$member = m('member')->getInfo($openid);

			if (empty($member)) {
				return error(-1, '未找到用户');
			}

			load()->model('payment');
			$setting = uni_setting($_W['uniacid'], array('payment'));

			if (!is_array($setting['payment'])) {
				return error(1, '没有设定支付参数');
			}

			$pay = m('common')->getSysset('pay');
			$wechat = $setting['payment']['wechat'];
			$sql = 'SELECT `key`,`secret`,`name` FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid limit 1';
			$row = pdo_fetch($sql, array(':uniacid' => $_W['uniacid']));
			$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
			$noncestr = '';
			$i = 0;

			while ($i < 32) {
				$noncestr .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
				++$i;
			}

			$post = array('wxappid' => $row['key'], 'mch_id' => $wechat['mchid'], 'mch_billno' => $mchId . date('YmdHis') . rand(1000, 9999), 'client_ip' => gethostbyname($_SERVER['HTTP_HOST']), 're_openid' => $openid, 'total_amount' => $money * 100, 'total_num' => 1, 'send_name' => $row['name'], 'wishing' => empty($desc) ? '微信红包奖励' : $desc, 'act_name' => empty($act_name) ? '红包奖励' : $act_name, 'remark' => empty($remark) ? '红包奖励' : $remark, 'nonce_str' => $noncestr);
			ksort($post);
			$params = array();

			foreach ($post as $key => $val) {
				if (($key != 'sign') && ($val != NULL) && ($val != 'null')) {
					$params[] = $key . '=' . $val;
				}
			}

			$_obf_DQM8DxU0KRoHGDAHLwckXAVcDDsQNyI_ = implode('&', $params);
			$stringSignTemp = $_obf_DQM8DxU0KRoHGDAHLwckXAVcDDsQNyI_ . '&key=' . $wechat['apikey'];
			$post['sign'] = strtoupper(md5($stringSignTemp));
			$postXml = array2xml($post);
			$sec = m('common')->getSec();
			$certs = iunserializer($sec['sec']);

			if (is_array($certs)) {
				if (empty($certs['cert']) || empty($certs['key']) || empty($certs['root'])) {
					message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
				}

				$certfile = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
				file_put_contents($certfile, $certs['cert']);
				$keyfile = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
				file_put_contents($keyfile, $certs['key']);
				$_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_ = IA_ROOT . '/addons/sz_yi/cert/' . random(128);
				file_put_contents($_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_, $certs['root']);
				$extras = array('CURLOPT_SSLCERT' => $certfile, 'CURLOPT_SSLKEY' => $keyfile, 'CURLOPT_CAINFO' => $_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_);
			}
			else {
				message('未上传完整的微信支付证书，请到【系统设置】->【支付方式】中上传!', '', 'error');
			}

			load()->func('communication');
			$resp = ihttp_request($url, $postXml, $extras);
			@unlink($certfile);
			@unlink($keyfile);
			@unlink($_obf_DSkqPBgwDh5AGgkkCxUUHQgTEyYULwE_);

			if (is_error($resp)) {
				return error(-2, $resp['message']);
			}

			if (empty($resp['content'])) {
				return error(-2, '网络错误');
			}

			$arr = json_decode(json_encode((array) simplexml_load_string($resp['content'])), true);
			$xml = '<?xml version="1.0" encoding="utf-8"?>' . $resp['content'];
			$dom = new DOMDocument();

			if ($dom->loadXML($xml)) {
				$xpath = new DOMXPath($dom);
				$code = $xpath->evaluate('string(//xml/return_code)');
				$ret = $xpath->evaluate('string(//xml/result_code)');
				if ((strtolower($code) == 'success') && (strtolower($ret) == 'success')) {
					return true;
				}

				if ($xpath->evaluate('string(//xml/return_msg)') == $xpath->evaluate('string(//xml/err_code_des)')) {
					$error = $xpath->evaluate('string(//xml/return_msg)');
				}
				else {
					$error = $xpath->evaluate('string(//xml/return_msg)') . '<br/>' . $xpath->evaluate('string(//xml/err_code_des)');
				}

				if (!empty($orderid)) {
					$sql = 'SELECT `ordersn` FROM ' . tablename('sz_yi_order') . ' WHERE `id`=:orderid limit 1';
					$row = pdo_fetch($sql, array(':orderid' => $orderid));
					$msg = array(
						'keyword1' => array('value' => '收银台收款发送红包失败', 'color' => '#73a68d'),
						'keyword2' => array('value' => '【订单编号】' . $row['ordersn'], 'color' => '#73a68d'),
						'remark'   => array('value' => '收银台收款红包发送失败！失败原因：' . $error)
						);
					pdo_update('sz_yi_order', array('redstatus' => $error), array('id' => $orderid));
					m('message')->sendCustomNotice($openid, $msg);
				}

				return error(-2, $error);
			}

			return error(-1, '未知错误');
		}
	}
}

?>
