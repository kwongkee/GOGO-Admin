<?php
// 模块LTD提供
// 点击商城上的“立即购买” 跳转到这里confirm.php
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');//display 
$openid = m('user')->getOpenid();//用户openid
$member = m('member')->getMember($openid);//获取sz_yi会员表中的全部字段。
$shopset = m('common')->getSysset('shop');
$uniacid = $_W['uniacid'];//当前公众号ID
$fromcart = 0;
$trade = m('common')->getSysset('trade');
$verifyset = m('common')->getSetData();//获取当前支付信息（id,uniacid,sets,plugins,sec)
$allset = iunserializer($verifyset['plugins']);//plugins
$store_total = false;
if (isset($allset['verify']) && ($allset['verify']['store_total'] == 1)) {
	$store_total = true;
}

if (!empty($trade['shareaddress']) && is_weixin()) {
	if (!$_W['isajax']) {
		$shareAddress = m('common')->shareAddress();

		if (empty($shareAddress)) {
			exit();
		}
	}
}

$pv = p('virtual');//返回VirtualModel Object模块对象
$hascouponplugin = false;
$plugc = p('coupon');

if ($plugc) {
	$hascouponplugin = true;
}

$goodid = ($_GPC['id'] ? intval($_GPC['id']) : 0);//订单自增ID
$cartid = ($_GPC['cartids'] ? $_GPC['cartids'] : 0);//余额0
$diyform_plugin = p('diyform');
$order_formInfo = false;

if ($diyform_plugin) {
	$diyform_set = $diyform_plugin->getSet();
	
	if (!empty($diyform_set['order_diyform_open'])) {
		$orderdiyformid = intval($diyform_set['order_diyform']);

		if (!empty($orderdiyformid)) {
			$order_formInfo = $diyform_plugin->getDiyformInfo($orderdiyformid);
			$fields = $order_formInfo['fields'];
			$f_data = $diyform_plugin->getLastOrderData($orderdiyformid, $member);
		}
	}
}
//sz_yi_store  公司地址配置信息。
$carrier_list = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_store') . ' WHERE uniacid=:uniacid AND status=1 AND myself_support=1', array(':uniacid' => $_W['uniacid']));
//创建订单
if (($operation == 'display') || ($operation == 'create')) {
	$id = ($operation == 'create' ? intval($_GPC['order'][0]['id']) : intval($_GPC['id']));//订单自增ID
	$show = 1;

	if ($diyform_plugin) {
		if (!empty($id)) {
			$sql = 'SELECT id as goodsid,type,diyformtype,diyformid,diymode FROM ' . tablename('sz_yi_goods') . ' WHERE id=:id AND uniacid=:uniacid  limit 1';
			$goods_data = pdo_fetch($sql, array(':uniacid' => $uniacid, ':id' => $id));
			$diyformtype = $goods_data['diyformtype'];
			$diyformid = $goods_data['diyformid'];
			$diymode = $goods_data['diymode'];
			if (!empty($diyformtype) && !empty($diyformid)) {
				$formInfo = $diyform_plugin->getDiyformInfo($diyformid);
				$goods_data_id = ($operation == 'create' ? intval($_GPC['order'][0]['gdid']) : intval($_GPC['gdid']));
			}
		}
	}
}

$ischannelpick = $_GPC['ischannelpick'];

if ($operation == 'date') {
	global $_GPC;
	global $_W;
	$id = intval($_GPC['id']);
	if ($search_array && !empty($search_array['bdate']) && !empty($search_array['day'])) {
		$bdate = $search_array['bdate'];
		$day = $search_array['day'];
	}
	else {
		$bdate = date('Y-m-d');
		$day = 1;
	}

	load()->func('tpl');
	include $this->template('order/date');
	exit();
}
else {
	if ($operation == 'ajaxData') {
		global $_GPC;
		global $_W;
		$id = intval($_GPC['id']);

		switch ($_GPC['ac']) {
		case 'time':
			$bdate = $_GPC['bdate'];
			$day = $_GPC['day'];
			if (!empty($bdate) && !empty($day)) {
				$btime = strtotime($bdate);
				$etime = $btime + ($day * 86400);
				$data['btime'] = $btime;
				$data['etime'] = $etime;
				$data['bdate'] = $bdate;
				$data['edate'] = date('Y-m-d', $etime);
				$data['day'] = $day;
				$_SESSION['data'] = $data;
				$url = $this->createMobileUrl('order', array('p' => 'confirm', 'id' => $id));
//				exit(json_encode(array('result' => 1, 'url' => $url)));
			}

			break;
		}
	}
}

$yunbi_plugin = p('yunbi');

if ($yunbi_plugin) {
	$yunbiset = $yunbi_plugin->getSet();
}

if ($_W['isajax']) {
	
	$ischannelpick = intval($_GPC['ischannelpick']);
	$isyunbipay = intval($_GPC['isyunbipay']);

	if ($operation == 'display') {
		$id = intval($_GPC['id']);
		$optionid = intval($_GPC['optionid']);
		$total = intval($_GPC['total']);//total = 1
		$ischannelpay = intval($_GPC['ischannelpay']);
		$ids = '';

		if ($total < 1) {
			$total = 1;
		}

		$buytotal = $total;
		$isverify = false;
		$isvirtual = false;
		$changenum = false;
		$goods = array();

		if (empty($id)) {
			$condition = '';
			$cartids = $_GPC['cartids'];

			if (!empty($cartids)) {
				$condition = ' and c.id in (' . $cartids . ')';
			}

			$suppliers = pdo_fetchall('SELECT distinct g.supplier_uid FROM ' . tablename('sz_yi_member_cart') . ' c ' . ' left join ' . tablename('sz_yi_goods') . ' g on c.goodsid = g.id ' . ' left join ' . tablename('sz_yi_goods_option') . ' o on c.optionid = o.id ' . ' where c.openid=:openid and  c.deleted=0 and c.uniacid=:uniacid ' . $condition . ' order by g.supplier_uid asc', array(':uniacid' => $uniacid, ':openid' => $openid), 'supplier_uid');
			$sql = 'SELECT c.goodsid,c.total,g.maxbuy,g.type,g.issendfree,g.isnodiscount,g.weight,o.weight as optionweight,g.title,g.thumb,ifnull(o.marketprice, g.marketprice) as marketprice,o.title as optiontitle,c.optionid,g.storeids,g.isverify,g.isverifysend,g.dispatchsend, g.deduct,g.deduct2,g.virtual,o.virtual as optionvirtual,discounts,discounts2,discounttype,discountway,g.supplier_uid,g.dispatchprice,g.dispatchtype,g.dispatchid, g.yunbi_deduct FROM ' . tablename('sz_yi_member_cart') . ' c ' . ' left join ' . tablename('sz_yi_goods') . ' g on c.goodsid = g.id ' . ' left join ' . tablename('sz_yi_goods_option') . ' o on c.optionid = o.id ' . ' where c.openid=:openid and  c.deleted=0 and c.uniacid=:uniacid ' . $condition . ' order by g.supplier_uid asc';
			$goods = pdo_fetchall($sql, array(':uniacid' => $uniacid, ':openid' => $openid));

			if (empty($goods)) {
				show_json(-1, array('url' => $this->createMobileUrl('shop/cart')));
			}
			else {
				foreach ($goods as $k => $v) {
					if (!empty($v['optionvirtual'])) {
						$goods[$k]['virtual'] = $v['optionvirtual'];
					}

					if (!empty($v['optionweight'])) {
						$goods[$k]['weight'] = $v['optionweight'];
					}
				}
			}

			$fromcart = 1;
		}
		else {
			if (p('hotel')) {
				$sql = 'SELECT id as goodsid,type,title,weight,deposit,issendfree,isnodiscount, thumb,marketprice,storeids,isverify,isverifysend,dispatchsend,deduct,virtual,maxbuy,usermaxbuy,discounts,discounts2,discounttype,discountway,total as stock, deduct2, ednum, edmoney, edareas, diyformtype, diyformid, diymode, dispatchtype, dispatchid, dispatchprice, supplier_uid, yunbi_deduct FROM ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid  limit 1';
			}
			else {
				$sql = 'SELECT id as goodsid,type,title,weight,issendfree,isnodiscount, thumb,marketprice,storeids,isverify,isverifysend,dispatchsend,deduct,virtual,maxbuy,usermaxbuy,discounts,discounts2,discounttype,discountway,total as stock, deduct2, ednum, edmoney, edareas, diyformtype, diyformid, diymode, dispatchtype, dispatchid, dispatchprice, supplier_uid, yunbi_deduct FROM ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid  limit 1';
			}

			$data = pdo_fetch($sql, array(':uniacid' => $uniacid, ':id' => $id));
			$suppliers = array(
				$data['supplier_uid'] => array('supplier_uid' => $data['supplier_uid'])
				);
			$data['total'] = $total;
			$data['optionid'] = $optionid;

			if (!empty($optionid)) {
				$option = pdo_fetch('select id,title,marketprice,goodssn,productsn,virtual,stock,weight from ' . tablename('sz_yi_goods_option') . ' WHERE id=:id AND goodsid=:goodsid AND uniacid=:uniacid  limit 1', array(':uniacid' => $uniacid, ':goodsid' => $id, ':id' => $optionid));

				if (!empty($option)) {
					$data['optionid'] = $optionid;
					$data['optiontitle'] = $option['title'];

					if (p('supplier')) {
						if ($option['marketprice'] != 0) {
							$data['marketprice'] = $option['marketprice'];
						}
					}
					else {
						$data['marketprice'] = $option['marketprice'];
					}

					$data['virtual'] = $option['virtual'];
					$data['stock'] = $option['stock'];

					if (!empty($option['weight'])) {
						$data['weight'] = $option['weight'];
					}
				}
			}

			$changenum = true;
			$totalmaxbuy = $data['stock'];

			if (0 < $data['maxbuy']) {
				if ($totalmaxbuy != -1) {
					if ($data['maxbuy'] < $totalmaxbuy) {
						$totalmaxbuy = $data['maxbuy'];
					}
				}
				else {
					$totalmaxbuy = $data['maxbuy'];
				}
			}

			if (0 < $data['usermaxbuy']) {
				$order_goodscount = pdo_fetchcolumn('select ifnull(sum(og.total),0)  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on og.orderid=o.id ' . ' WHERE og.goodsid=:goodsid AND  o.status>=1 AND o.openid=:openid  AND og.uniacid=:uniacid ', array(':goodsid' => $data['goodsid'], ':uniacid' => $uniacid, ':openid' => $openid));
				$last = $data['usermaxbuy'] - $order_goodscount;

				if ($last <= 0) {
					$last = 0;
				}

				if ($totalmaxbuy != -1) {
					if ($last < $totalmaxbuy) {
						$totalmaxbuy = $last;
					}
				}
				else {
					$totalmaxbuy = $last;
				}
			}

			$data['totalmaxbuy'] = $totalmaxbuy;

			if (p('hotel')) {
				if ($data['type'] == '99') {
					$btime = $_SESSION['data']['btime'];
					$bdate = $_SESSION['data']['bdate'];
					$days = intval($_SESSION['data']['day']);
					$etime = $_SESSION['data']['etime'];
					$edate = $_SESSION['data']['edate'];
					$date_array = array();
					$date_array[0]['date'] = $bdate;
					$date_array[0]['day'] = date('j', $btime);
					$date_array[0]['time'] = $btime;
					$date_array[0]['month'] = date('m', $btime);

					if (1 < $days) {
						$i = 1;

						while ($i < $days) {
							$date_array[$i]['time'] = $date_array[$i - 1]['time'] + 86400;
							$date_array[$i]['date'] = date('Y-m-d', $date_array[$i]['time']);
							$date_array[$i]['day'] = date('j', $date_array[$i]['time']);
							$date_array[$i]['month'] = date('m', $date_array[$i]['time']);
							++$i;
						}
					}

					$sql2 = 'SELECT * FROM ' . tablename('sz_yi_hotel_room') . ' WHERE `goodsid` = :goodsid';
					$params2 = array(':goodsid' => $id);
					$room = pdo_fetch($sql2, $params2);
					$sql = 'SELECT `id`, `roomdate`, `num`, `status` FROM ' . tablename('sz_yi_hotel_room_price') . " WHERE `roomid` = :roomid\n                    AND `roomdate` >= :btime AND `roomdate` < :etime AND `status` = :status";
					$params = array(':roomid' => $room['id'], ':btime' => $btime, ':etime' => $etime, ':status' => '1');
					$room_date_list = pdo_fetchall($sql, $params);
					$flag = intval($room_date_list);
					$list = array();
					$max_room = 5;
					$is_order = 1;

					if ($flag == 1) {
						$i = 0;

						while ($i < $days) {
							$k = $date_array[$i]['time'];

							foreach ($room_date_list as $p_key => $p_value) {
								if ($p_value['roomdate'] == $k) {
									$room_num = $p_value['num'];

									if (empty($room_num)) {
										$is_order = 0;
										$max_room = 0;
										$list['num'] = 0;
										$list['date'] = $date_array[$i]['date'];
									}
									else {
										if ((0 < $room_num) && ($room_num < $max_room)) {
											$max_room = $room_num;
											$list['num'] = $room_num;
											$list['date'] = $date_array[$i]['date'];
										}
										else {
											$list['num'] = $max_room;
											$list['date'] = $date_array[$i]['date'];
										}
									}

									break;
								}
							}

							++$i;
						}
					}

					$data['totalmaxbuy'] = $list['num'];
				}
			}

			$goods[] = $data;
		}

		$goods = set_medias($goods, 'thumb');

		foreach ($goods as &$g) {
			if ($g['isverify'] == 2) {
				$isverify = true;
			}

			if ($g['isverifysend'] == 1) {
				$isverifysend = true;
			}

			if ($g['dispatchsend'] == 1) {
				$dispatchsend = true;
			}

			if (!empty($g['virtual']) || ($g['type'] == 2)) {
				$isvirtual = true;
			}

			if (p('channel')) {
				if (($ischannelpay == 1) && empty($ischannelpick)) {
					$isvirtual = true;
				}
			}

			if (p('yunbi')) {
				if (!empty($isyunbipay) && !empty($yunbiset['isdeduct'])) {
					$g['marketprice'] -= $g['yunbi_deduct'];
				}
			}
		}

		foreach ($suppliers as $key => $val) {
			$order_all[$val['supplier_uid']]['weight'] = 0;
			$order_all[$val['supplier_uid']]['total'] = 0;
			$order_all[$val['supplier_uid']]['goodsprice'] = 0;
			$order_all[$val['supplier_uid']]['realprice'] = 0;
			$order_all[$val['supplier_uid']]['deductprice'] = 0;
			$order_all[$val['supplier_uid']]['yunbideductprice'] = 0;
			$order_all[$val['supplier_uid']]['discountprice'] = 0;
			$order_all[$val['supplier_uid']]['deductprice2'] = 0;
			$order_all[$val['supplier_uid']]['dispatch_price'] = 0;
			$order_all[$val['supplier_uid']]['storeids'] = array();
			$order_all[$val['supplier_uid']]['dispatch_array'] = array();
			$order_all[$val['supplier_uid']]['supplier_uid'] = $val['supplier_uid'];

			if ($val['supplier_uid'] == 0) {
				$order_all[$val['supplier_uid']]['supplier_name'] = $shopset['name'];
			}
			else {
				$supplier_names = pdo_fetch('select username, brandname from ' . tablename('sz_yi_perm_user') . ' where uid=' . $val['supplier_uid'] . ' and uniacid=' . $_W['uniacid']);

				if (!empty($supplier_names)) {
					$order_all[$val['supplier_uid']]['supplier_name'] = $supplier_names['brandname'] ? $supplier_names['brandname'] : '';
				}
				else {
					$order_all[$val['supplier_uid']]['supplier_name'] = '';
				}
			}
		}

		$member = m('member')->getMember($openid);
		$level = m('member')->getLevel($openid);
		$stores = array();
		$stores_send = array();
		$address = false;
		$carrier = false;
		$carrier_list = array();
		$dispatch_list = false;
		$carrier_list = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where  uniacid=:uniacid and status=1 AND myself_support=1 ', array(':uniacid' => $_W['uniacid']));

		if (!empty($carrier_list)) {
			$carrier = $carrier_list[0];
		}

		if (p('channel')) {
			$my_info = p('channel')->getInfo($openid);
		}

		foreach ($goods as &$g) {
			if (empty($g['total']) || (intval($g['total']) == '-1')) {
				$g['total'] = 1;
			}

			if (p('channel')) {
				if ($ischannelpay == 1) {
					$g['marketprice'] = ($g['marketprice'] * $my_info['my_level']['purchase_discount']) / 100;
				}
			}

			$gprice = $g['marketprice'] * $g['total'];
			$discounts = json_decode($g['discounts'], true);
			$discountway = $g['discountway'];
			$discounttype = $g['discounttype'];

			if ($discountway == 1) {
				if ($g['discounttype'] == 1) {
					$level = m('member')->getLevel($openid);
					$discounts = json_decode($g['discounts'], true);

					if (is_array($discounts)) {
						if (!empty($level['id'])) {
							if ((0 < floatval($discounts['level' . $level['id']])) && (floatval($discounts['level' . $level['id']]) < 10)) {
								$level['discount'] = floatval($discounts['level' . $level['id']]);
							}
							else {
								if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < 10)) {
									$level['discount'] = floatval($level['discount']);
								}
								else {
									$level['discount'] = 0;
								}
							}
						}
						else {
							if ((0 < floatval($discounts['default'])) && (floatval($discounts['default']) < 10)) {
								$level['discount'] = floatval($discounts['default']);
							}
							else {
								if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < 10)) {
									$level['discount'] = floatval($level['discount']);
								}
								else {
									$level['discount'] = 0;
								}
							}
						}
					}
				}
				else {
					$level = p('commission')->getLevel($openid);
					$discounts = json_decode($g['discounts2'], true);
					$level['discount'] = 0;
					if (($member['isagent'] == 1) && ($member['status'] == 1)) {
						if (is_array($discounts)) {
							if (!empty($level['id'])) {
								if ((0 < floatval($discounts['level' . $level['id']])) && (floatval($discounts['level' . $level['id']]) < 10)) {
									$level['discount'] = floatval($discounts['level' . $level['id']]);
								}
							}
							else {
								if ((0 < floatval($discounts['default'])) && (floatval($discounts['default']) < 10)) {
									$level['discount'] = floatval($discounts['default']);
								}
							}
						}
					}
				}

				if (p('channel') && ($ischannelpay == 1)) {
					$level['discount'] = 10;
				}

				if (empty($g['isnodiscount']) && (0 < floatval($level['discount'])) && (floatval($level['discount']) < 10)) {
					$price = round((floatval($level['discount']) / 10) * $gprice, 2);
					$order_all[$g['supplier_uid']]['discountprice'] += $gprice - $price;
				}
				else {
					$price = $gprice;
				}
			}
			else {
				if ($g['discounttype'] == 1) {
					$level = m('member')->getLevel($openid);
					$level['discount'] = 0;
					$discounts = json_decode($g['discounts'], true);

					if (is_array($discounts)) {
						if (!empty($level['id'])) {
							if (floatval($discounts['level' . $level['id']]) < $g['marketprice']) {
								$level['discount'] = floatval($discounts['level' . $level['id']]);
							}
							else {
								if (floatval($level['discount']) < $g['marketprice']) {
									$level['discount'] = floatval($level['discount']);
								}
							}
						}
						else {
							if ((0 < floatval($discounts['default'])) && (floatval($discounts['default']) < $g['marketprice'])) {
								$level['discount'] = floatval($discounts['default']);
							}
							else {
								if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < $g['marketprice'])) {
									$level['discount'] = floatval($level['discount']);
								}
							}
						}
					}
				}
				else {
					$level = p('commission')->getLevel($openid);
					$discounts = json_decode($g['discounts2'], true);
					$level['discount'] = 0;
					if (($member['isagent'] == 1) && ($member['status'] == 1)) {
						if (is_array($discounts)) {
							if (!empty($level['id'])) {
								if (floatval($discounts['level' . $level['id']]) < $g['marketprice']) {
									$level['discount'] = floatval($discounts['level' . $level['id']]);
								}
							}
							else {
								if (floatval($discounts['default']) < $g['marketprice']) {
									$level['discount'] = floatval($discounts['default']);
								}
							}
						}
					}
				}

				if (empty($g['isnodiscount']) && (floatval($level['discount']) < $g['marketprice'])) {
					$price = round(floatval($gprice - ($level['discount'] * $g['total'])), 2);
					$order_all[$g['supplier_uid']]['discountprice'] += $gprice - $price;
				}
				else {
					$price = $gprice;
				}

				if (p('channel') && ($ischannelpay == 1)) {
					$price = $gprice;
				}
			}

			$g['discount'] = $level['discount'];
			$g['ggprice'] = $price;
			$order_all[$g['supplier_uid']]['realprice'] += $price;
			$order_all[$g['supplier_uid']]['goodsprice'] += $gprice;
			if (p('hotel') && ($data['type'] == '99')) {
				$sql2 = 'SELECT * FROM ' . tablename('sz_yi_hotel_room') . ' WHERE `goodsid` = :goodsid';
				$params2 = array(':goodsid' => $id);
				$room = pdo_fetch($sql2, $params2);
				$pricefield = 'oprice';
				$r_sql = 'SELECT `roomdate`, `num`, `oprice`, `status`, ' . $pricefield . ' AS `m_price` FROM ' . tablename('sz_yi_hotel_room_price') . ' WHERE `roomid` = :roomid AND `roomdate` >= :btime AND ' . ' `roomdate` < :etime';
				$params = array(':roomid' => $room['id'], ':btime' => $btime, ':etime' => $etime);
				$price_list = pdo_fetchall($r_sql, $params);
				$this_price = $old_price = ($pricefield == 'cprice' ? $room['oprice'] * $member_p[$_W['member']['groupid']] : $room['roomprice']);

				if ($this_price == 0) {
					$this_price = $old_price = $room['oprice'];
				}

				$totalprice = $old_price * $days;

				if ($price_list) {
					$check_date = array();

					foreach ($price_list as $k => $v) {
						$price_list[$k]['time'] = date('Y-m-d', $v['roomdate']);
						$new_price = ($pricefield == 'mprice' ? $this_price : $v['m_price']);
						$roomdate = $v['roomdate'];
						if (($v['status'] == 0) || ($v['num'] == 0)) {
							$has = 0;
						}
						else {
							if ($new_price && $roomdate) {
								if (!in_array($roomdate, $check_date)) {
									$check_date[] = $roomdate;

									if ($old_price != $new_price) {
										$totalprice = ($totalprice - $old_price) + $new_price;
									}
								}
							}
						}
					}

					$goodsprice = round($totalprice);
				}
				else {
					$goodsprice = round($goods[0]['marketprice']) * $days;
				}

				$order_all[$g['supplier_uid']]['realprice'] = $goodsprice;
				$order_all[$g['supplier_uid']]['goodsprice'] = $goodsprice;
				$price = $goodsprice;
			}

			$order_all[$g['supplier_uid']]['total'] += $g['total'];
			$order_all[$g['supplier_uid']]['deductprice'] += $g['deduct'] * $g['total'];

			if ($g['yunbi_deduct']) {
				$order_all[$g['supplier_uid']]['yunbideductprice'] += $g['yunbi_deduct'] * $g['total'];
			}
			else {
				$order_all[$g['supplier_uid']]['yunbideductprice'] += $g['yunbi_deduct'];
			}

			if ($g['deduct2'] == 0) {
				$order_all[$g['supplier_uid']]['deductprice2'] += $price;
			}
			else {
				if (0 < $g['deduct2']) {
					if ($price < $g['deduct2']) {
						$order_all[$g['supplier_uid']]['deductprice2'] += $price;
					}
					else {
						$order_all[$g['supplier_uid']]['deductprice2'] += $g['deduct2'];
					}
				}
			}

			$order_all[$g['supplier_uid']]['goods'][] = $g;
		}

		unset($g);

		if ($isverify) {
			$storeids = array();

			foreach ($goods as $g) {
				if (!empty($g['storeids'])) {
					$order_all[$g['supplier_uid']]['storeids'] = array_merge(explode(',', $g['storeids']), $order_all[$g['supplier_uid']]['storeids']);
				}
			}

			foreach ($suppliers as $key => $val) {
				if (empty($order_all[$val['supplier_uid']]['storeids'])) {
					$order_all[$val['supplier_uid']]['stores'] = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where  uniacid=:uniacid and status=1 and myself_support=1', array(':uniacid' => $_W['uniacid']));
				}
				else {
					$order_all[$val['supplier_uid']]['stores'] = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where id in (' . implode(',', $order_all[$val['supplier_uid']]['storeids']) . ') and uniacid=:uniacid and status=1 and myself_support=1', array(':uniacid' => $_W['uniacid']));
				}

				if (empty($order_all[$val['supplier_uid']]['storeids'])) {
					$order_all[$val['supplier_uid']]['stores_send'] = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where  uniacid=:uniacid and status=1 ', array(':uniacid' => $_W['uniacid']));
				}
				else {
					$order_all[$val['supplier_uid']]['stores_send'] = pdo_fetchall('select * from ' . tablename('sz_yi_store') . ' where id in (' . implode(',', $order_all[$val['supplier_uid']]['storeids']) . ') and uniacid=:uniacid and status=1 ', array(':uniacid' => $_W['uniacid']));
				}

				$stores = $order_all[$val['supplier_uid']]['stores'];
				$stores_send = $order_all[$val['supplier_uid']]['stores_send'];
			}

			if ($trade['is_street'] == '1') {
				$address = pdo_fetch('select id,realname,mobile,address,province,city,area,street from ' . tablename('sz_yi_member_address') . ' where openid=:openid and deleted=0 and isdefault=1  and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':openid' => $openid));
			}
			else {
				$address = pdo_fetch('select id,realname,mobile,address,province,city,area from ' . tablename('sz_yi_member_address') . ' where openid=:openid and deleted=0 and isdefault=1  and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':openid' => $openid));
			}
		}
		else if ($trade['is_street'] == '1') {
			$address = pdo_fetch('select id,realname,mobile,address,province,city,area,street from ' . tablename('sz_yi_member_address') . ' where openid=:openid and deleted=0 and isdefault=1  and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':openid' => $openid));
		}
		else {
			$address = pdo_fetch('select id,realname,mobile,address,province,city,area from ' . tablename('sz_yi_member_address') . ' where openid=:openid and deleted=0 and isdefault=1  and uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':openid' => $openid));
		}

		$isDispath = true;
		if ($isverify && !$isverifysend && !$dispatchsend) {
			$isDispath = false;
		}

		if (!$isvirtual && $isDispath) {
			foreach ($goods as $g) {
				$sendfree = false;

				if (!empty($g['issendfree'])) {
					$sendfree = true;
				}
				else {
					$gareas = explode(';', $g['edareas']);
					if (($g['ednum'] <= $g['total']) && (0 < $g['ednum'])) {
						if (empty($gareas)) {
							$sendfree = true;
						}
						else if (!empty($address)) {
							if (!in_array($address['city'], $gareas)) {
								$sendfree = true;
							}
						}
						else if (!empty($member['city'])) {
							if (!in_array($member['city'], $gareas)) {
								$sendfree = true;
							}
						}
						else {
							$sendfree = true;
						}
					}

					if ((floatval($g['edmoney']) <= $g['ggprice']) && (0 < floatval($g['edmoney']))) {
						if (empty($gareas)) {
							$sendfree = true;
						}
						else if (!empty($address)) {
							if (!in_array($address['city'], $gareas)) {
								$sendfree = true;
							}
						}
						else if (!empty($member['city'])) {
							if (!in_array($member['city'], $gareas)) {
								$sendfree = true;
							}
						}
						else {
							$sendfree = true;
						}
					}
				}

				if (!$sendfree) {
					if ($g['dispatchtype'] == 1) {
						if (0 < $g['dispatchprice']) {
							if (!isset($order_all[$g['supplier_uid']]['minDispathPrice'])) {
								$order_all[$g['supplier_uid']]['minDispathPrice'] = $g['dispatchprice'];
							}

							$order_all[$g['supplier_uid']]['dispatch_price'] = $g['dispatchprice'] < $order_all[$g['supplier_uid']]['minDispathPrice'] ? $g['dispatchprice'] : $order_all[$g['supplier_uid']]['minDispathPrice'];
						}
					}
					else {
						if ($g['dispatchtype'] == 0) {
							if (empty($g['dispatchid'])) {
								$order_all[$g['supplier_uid']]['dispatch_data'] = m('order')->getDefaultDispatch($g['supplier_uid']);
							}
							else {
								$order_all[$g['supplier_uid']]['dispatch_data'] = m('order')->getOneDispatch($g['dispatchid'], $g['supplier_uid']);
							}

							if (empty($order_all[$g['supplier_uid']]['dispatch_data'])) {
								$order_all[$g['supplier_uid']]['dispatch_data'] = m('order')->getNewDispatch($g['supplier_uid']);
							}

							if (!empty($order_all[$g['supplier_uid']]['dispatch_data'])) {
								if ($order_all[$g['supplier_uid']]['dispatch_data']['calculatetype'] == 1) {
									$order_all[$g['supplier_uid']]['param'] = $g['total'];
								}
								else {
									$order_all[$g['supplier_uid']]['param'] = $g['weight'] * $g['total'];
								}

								$dkey = $order_all[$g['supplier_uid']]['dispatch_data']['id'];

								if (array_key_exists($dkey, $order_all[$g['supplier_uid']]['dispatch_array'])) {
									$order_all[$g['supplier_uid']]['dispatch_array'][$dkey]['param'] += $order_all[$g['supplier_uid']]['param'];
								}
								else {
									$order_all[$g['supplier_uid']]['dispatch_array'][$dkey]['data'] = $order_all[$g['supplier_uid']]['dispatch_data'];
									$order_all[$g['supplier_uid']]['dispatch_array'][$dkey]['param'] = $order_all[$g['supplier_uid']]['param'];
								}
							}
						}
					}
				}
			}

			foreach ($suppliers as $key => $val) {
				if (!empty($order_all[$val['supplier_uid']]['dispatch_array'])) {
					foreach ($order_all[$val['supplier_uid']]['dispatch_array'] as $k => $v) {
						$order_all[$val['supplier_uid']]['dispatch_data'] = $order_all[$val['supplier_uid']]['dispatch_array'][$k]['data'];
						$param = $order_all[$val['supplier_uid']]['dispatch_array'][$k]['param'];
						$areas = unserialize($order_all[$val['supplier_uid']]['dispatch_data']['areas']);

						if (!empty($address)) {
							$order_all[$val['supplier_uid']]['dispatch_price'] += m('order')->getCityDispatchPrice($areas, $address['city'], $param, $order_all[$val['supplier_uid']]['dispatch_data'], $val['supplier_uid']);
						}
						else if (!empty($member['city'])) {
							$order_all[$val['supplier_uid']]['dispatch_price'] += m('order')->getCityDispatchPrice($areas, $member['city'], $param, $order_all[$val['supplier_uid']]['dispatch_data'], $val['supplier_uid']);
						}
						else {
							$order_all[$val['supplier_uid']]['dispatch_price'] += m('order')->getDispatchPrice($param, $order_all[$val['supplier_uid']]['dispatch_data'], -1, $val['supplier_uid']);
						}
					}
				}
			}
		}

		$sale_plugin = p('sale');
		$saleset = false;

		if ($sale_plugin) {
			$saleset = $sale_plugin->getSet();
			$saleset['enoughs'] = $sale_plugin->getEnoughs();
		}

		$realprice_total = 0;

		foreach ($suppliers as $key => $val) {
			if ($saleset) {
				if (!empty($saleset['enoughfree'])) {
					if (floatval($saleset['enoughorder']) <= 0) {
						$order_all[$val['supplier_uid']]['dispatch_price'] = 0;
					}
					else {
						if (floatval($saleset['enoughorder']) <= $order_all[$val['supplier_uid']]['realprice']) {
							if (empty($saleset['enoughareas'])) {
								$order_all[$val['supplier_uid']]['dispatch_price'] = 0;
							}
							else {
								$areas = explode(';', $saleset['enoughareas']);

								if (!empty($address)) {
									if (!in_array($address['city'], $areas)) {
										$order_all[$val['supplier_uid']]['dispatch_price'] = 0;
									}
								}
							}
						}
					}
				}

				if (p('hotel') && ($data['type'] == '99')) {
					$order_all[$val['supplier_uid']]['dispatch_price'] = 0;
				}

				$order_all[$val['supplier_uid']]['saleset'] = $saleset;
				if (p('channel') && ($ischannelpay == 1)) {
					$saleset = array();
				}

				if (!empty($saleset['enoughs'])) {
					$tmp_money = 0;

					foreach ($saleset['enoughs'] as $e) {
						if ((floatval($e['enough']) <= $order_all[$val['supplier_uid']]['realprice']) && (0 < floatval($e['money']))) {
							if ($tmp_money < $e['enough']) {
								$tmp_money = $e['enough'];
								$order_all[$val['supplier_uid']]['saleset']['showenough'] = true;
								$order_all[$val['supplier_uid']]['saleset']['enoughmoney'] = $e['enough'];
								$order_all[$val['supplier_uid']]['saleset']['enoughdeduct'] = number_format($e['money'], 2);
								$final_money = $e['money'];
								$saleset['enoughmoney'] = $e['enough'];
								$saleset['enoughdeduct'] = number_format($e['money'], 2);
							}
						}
					}

					$order_all[$val['supplier_uid']]['realprice'] -= floatval($final_money);
				}

				if (empty($saleset['dispatchnodeduct'])) {
					$order_all[$val['supplier_uid']]['deductprice2'] += $order_all[$val['supplier_uid']]['dispatch_price'];
				}
			}

			$order_all[$val['supplier_uid']]['hascoupon'] = false;

			if ($hascouponplugin) {
				$order_all[$val['supplier_uid']]['couponcount'] = $plugc->consumeCouponCount($openid, $order_all[$val['supplier_uid']]['goodsprice'], $val['supplier_uid'], 0, 0, $goodid, $cartid);
				$order_all[$val['supplier_uid']]['hascoupon'] = 0 < $order_all[$val['supplier_uid']]['couponcount'];
			}

			$order_all[$val['supplier_uid']]['realprice'] += $order_all[$val['supplier_uid']]['dispatch_price'];
			$realprice_total += $order_all[$val['supplier_uid']]['realprice'];
			$order_all[$val['supplier_uid']]['deductcredit'] = 0;
			$order_all[$val['supplier_uid']]['deductmoney'] = 0;
			$order_all[$val['supplier_uid']]['deductcredit2'] = 0;

			if ($sale_plugin) {
				$credit = m('member')->getCredit($openid, 'credit1');

				if (!empty($saleset['creditdeduct'])) {
					$pcredit = intval($saleset['credit']);
					$pmoney = round(floatval($saleset['money']), 2);
					if ((0 < $pcredit) && (0 < $pmoney)) {
						if (($credit % $pcredit) == 0) {
							$order_all[$val['supplier_uid']]['deductmoney'] = round(intval($credit / $pcredit) * $pmoney, 2);
						}
						else {
							$order_all[$val['supplier_uid']]['deductmoney'] = round((intval($credit / $pcredit) + 1) * $pmoney, 2);
						}
					}

					if ($order_all[$val['supplier_uid']]['deductprice'] < $order_all[$val['supplier_uid']]['deductmoney']) {
						$order_all[$val['supplier_uid']]['deductmoney'] = $order_all[$val['supplier_uid']]['deductprice'];
					}

					if ($order_all[$val['supplier_uid']]['realprice'] < $order_all[$val['supplier_uid']]['deductmoney']) {
						$order_all[$val['supplier_uid']]['deductmoney'] = $order_all[$val['supplier_uid']]['realprice'];
					}

					$order_all[$val['supplier_uid']]['deductcredit'] = ($order_all[$val['supplier_uid']]['deductmoney'] / $pmoney) * $pcredit;
				}

				if (!empty($saleset['moneydeduct'])) {
					$order_all[$val['supplier_uid']]['deductcredit2'] = m('member')->getCredit($openid, 'credit2');

					if ($order_all[$val['supplier_uid']]['realprice'] < $order_all[$val['supplier_uid']]['deductcredit2']) {
						$order_all[$val['supplier_uid']]['deductcredit2'] = $order_all[$val['supplier_uid']]['realprice'];
					}

					if ($order_all[$val['supplier_uid']]['deductprice2'] < $order_all[$val['supplier_uid']]['deductcredit2']) {
						$order_all[$val['supplier_uid']]['deductcredit2'] = $order_all[$val['supplier_uid']]['deductprice2'];
					}
				}
			}

			$order_all[$val['supplier_uid']]['deductyunbi'] = 0;
			$order_all[$val['supplier_uid']]['deductyunbimoney'] = 0;
			if ($yunbi_plugin && $yunbiset['isdeduct']) {
				$virtual_currency = $member['virtual_currency'];
				$ycredit = 1;
				$ymoney = round(floatval($yunbiset['money']), 2);
				if ((0 < $ycredit) && (0 < $ymoney)) {
					if (($virtual_currency % $ycredit) == 0) {
						$order_all[$val['supplier_uid']]['deductyunbimoney'] = round(intval($virtual_currency / $ycredit) * $ymoney, 2);
					}
					else {
						$order_all[$val['supplier_uid']]['deductyunbimoney'] = round((intval($virtual_currency / $ycredit) + 1) * $ymoney, 2);
					}
				}

				if ($order_all[$val['supplier_uid']]['yunbideductprice'] < $order_all[$val['supplier_uid']]['deductyunbimoney']) {
					$order_all[$val['supplier_uid']]['deductyunbimoney'] = $order_all[$val['supplier_uid']]['yunbideductprice'];
				}

				if ($order_all[$val['supplier_uid']]['realprice'] < $order_all[$val['supplier_uid']]['deductyunbimoney']) {
					$order_all[$val['supplier_uid']]['deductyunbimoney'] = $order_all[$val['supplier_uid']]['realprice'];
				}

				$order_all[$val['supplier_uid']]['deductyunbi'] = ($order_all[$val['supplier_uid']]['deductyunbimoney'] / $ymoney) * $ycredit;
			}

			$order_all[$val['supplier_uid']]['goodsprice'] = number_format($order_all[$val['supplier_uid']]['goodsprice'], 2);
			$order_all[$val['supplier_uid']]['totalprice'] = number_format($order_all[$val['supplier_uid']]['totalprice'], 2);
			if (p('channel') && ($ischannelpay == 1)) {
				$order_all[$val['supplier_uid']]['discountprice'] = 0;
			}

			$order_all[$val['supplier_uid']]['discountprice'] = number_format($order_all[$val['supplier_uid']]['discountprice'], 2);
			$order_all[$val['supplier_uid']]['realprice'] = number_format($order_all[$val['supplier_uid']]['realprice'], 2);
			$order_all[$val['supplier_uid']]['dispatch_price'] = number_format($order_all[$val['supplier_uid']]['dispatch_price'], 2);
		}

		$supplierids = implode(',', array_keys($suppliers));

		if (p('hotel')) {
			if ($data['type'] == '99') {
				$sql2 = 'SELECT * FROM ' . tablename('sz_yi_hotel_room') . ' WHERE `goodsid` = :goodsid';
				$params2 = array(':goodsid' => $id);
				$room = pdo_fetch($sql2, $params2);
				$pricefield = 'oprice';
				$r_sql = 'SELECT `roomdate`, `num`, `oprice`, `status`, ' . $pricefield . ' AS `m_price` FROM ' . tablename('sz_yi_hotel_room_price') . ' WHERE `roomid` = :roomid AND `roomdate` >= :btime AND ' . ' `roomdate` < :etime';
				$btime = $_SESSION['data']['btime'];
				$etime = $_SESSION['data']['etime'];
				$params = array(':roomid' => $room['id'], ':btime' => $btime, ':etime' => $etime);
				$price_list = pdo_fetchall($r_sql, $params);
				$this_price = $old_price = ($pricefield == 'cprice' ? $room['oprice'] * $member_p[$_W['member']['groupid']] : $room['roomprice']);

				if ($this_price == 0) {
					$this_price = $old_price = $room['oprice'];
				}

				$totalprice = $old_price * $days;

				if ($price_list) {
					$check_date = array();

					foreach ($price_list as $k => $v) {
						$price_list[$k]['time'] = date('Y-m-d', $v['roomdate']);
						$new_price = ($pricefield == 'mprice' ? $this_price : $v['m_price']);
						$roomdate = $v['roomdate'];
						if (($v['status'] == 0) || ($v['num'] == 0)) {
							$has = 0;
						}
						else {
							if ($new_price && $roomdate) {
								if (!in_array($roomdate, $check_date)) {
									$check_date[] = $roomdate;

									if ($old_price != $new_price) {
										$totalprice = ($totalprice - $old_price) + $new_price;
									}
								}
							}
						}
					}

					$goodsprice = round($totalprice);
				}
				else {
					$goodsprice = round($goods[0]['marketprice']) * $days;
				}

				$realprice = $goodsprice + $goods[0]['deposit'];
				$deposit = $goods[0]['deposit'];
				$order_all[$g['supplier_uid']]['realprice'] = $goodsprice;
				$order_all[$g['supplier_uid']]['goodsprice'] = $goodsprice;
			}
		}
		show_json(1, array('member' => $member, 'deductmoney' => $deductmoney, 'deductcredit2' => $deductcredit2, 'saleset' => $saleset, 'goods' => $goods, 'has' => $has, 'weight' => $weight / $buytotal, 'set' => $shopset, 'fromcart' => $fromcart, 'haslevel' => !empty($level) && (0 < $level['discount']) && ($level['discount'] < 10), 'total' => $total, 'totalprice' => number_format($totalprice, 2), 'goodsprice' => number_format($goodsprice, 2), 'discountprice' => number_format($discountprice, 2), 'discount' => $level['discount'], 'realprice_total' => number_format($realprice_total, 2), 'address' => $address, 'carrier' => $stores[0], 'carrier_list' => $stores, 'carrier_send' => $stores_send[0], 'carrier_list_send' => $stores_send, 'dispatch_list' => $dispatch_list, 'isverify' => $isverify, 'isverifysend' => $isverifysend, 'dispatchsend' => $dispatchsend, 'stores' => $stores, 'stores_send' => $stores_send, 'isvirtual' => $isvirtual, 'changenum' => $changenum, 'order_all' => $order_all, 'supplierids' => $supplierids, 'deposit' => number_format($deposit, 2), 'price_list' => $price_list, 'realprice' => number_format($realprice, 2), 'type' => $goods[0]['type']));
	}
	else if ($operation == 'getdispatchprice') {
		$isverify = false;
		$isvirtual = false;
		$isverifysend = false;
		$deductprice = 0;
		$deductprice2 = 0;
		$deductcredit2 = 0;
		$dispatch_array = array();
		$totalprice = floatval($_GPC['totalprice']);
		$dflag = $_GPC['dflag'];
		$hascoupon = false;
		$couponcount = 0;
		$pc = p('coupon');
		$supplier_uid = $_GPC['supplier_uid'];
		$coupon_carrierid = intval($_GPC['carrierid']);
		$goodid = ($_GPC['id'] ? intval($_GPC['id']) : 0);
		$cartids = ($_GPC['cartids'] ? $_GPC['cartids'] : 0);
		$storeid = intval($_GPC['carrierid']);
		$addressid = intval($_GPC['addressid']);
		$address = pdo_fetch('select id,realname,mobile,address,province,city,area,street from ' . tablename('sz_yi_member_address') . ' WHERE  id=:id AND openid=:openid AND uniacid=:uniacid limit 1', array(':uniacid' => $uniacid, ':openid' => $openid, ':id' => $addressid));
		$member = m('member')->getMember($openid);
		$level = m('member')->getLevel($openid);
		$weight = $_GPC['weight'];
		$dispatch_price = 0;
		$deductenough_money = 0;
		$deductenough_enough = 0;
		$sale_plugin = p('sale');
		$saleset = false;

		if ($sale_plugin) {
			$saleset = $sale_plugin->getSet();
			$saleset['enoughs'] = $sale_plugin->getEnoughs();
		}

		if (empty($g['isnodiscount']) && (0 < floatval($level['discount'])) && (floatval($level['discount']) < 10)) {
			$totalprice = round((floatval($level['discount']) / 10) * $totalprice, 2);
		}

		if ($pc) {
			$pset = $pc->getSet();

			if (empty($pset['closemember'])) {
				$couponcount = $pc->consumeCouponCount($openid, $totalprice, $supplier_uid, 0, 0, $goodsid, $cartids, $coupon_carrierid);
				$hascoupon = 0 < $couponcount;
			}
		}

		if ($sale_plugin) {
			if ($saleset) {
				foreach ($saleset['enoughs'] as $e) {
					if ((floatval($e['enough']) <= $totalprice) && (0 < floatval($e['money'])) && ($deductenough_enough <= floatval($e['enough']))) {
						$deductenough_money = floatval($e['money']);
						$deductenough_enough = floatval($e['enough']);
					}
				}

				if (!empty($saleset['enoughfree'])) {
					if (floatval($saleset['enoughorder']) <= 0) {
						show_json(1, array('price' => 0, 'hascoupon' => $hascoupon, 'couponcount' => $couponcount, 'deductenough_money' => $deductenough_money, 'deductenough_enough' => $deductenough_enough));
					}
				}

				if (!empty($saleset['enoughfree']) && (floatval($saleset['enoughorder']) <= $totalprice)) {
					if (!empty($saleset['enoughareas'])) {
						$areas = explode(';', $saleset['enoughareas']);

						if (!in_array($address['city'], $areas)) {
							show_json(1, array('price' => 0, 'hascoupon' => $hascoupon, 'couponcount' => $couponcount, 'deductenough_money' => $deductenough_money, 'deductenough_enough' => $deductenough_enough));
						}
					}
					else {
						show_json(1, array('price' => 0, 'hascoupon' => $hascoupon, 'couponcount' => $couponcoun, 'deductenough_money' => $deductenough_money, 'deductenough_enough' => $deductenough_enough));
					}
				}
			}
		}

		$goods = trim($_GPC['goods']);

		if (!empty($goods)) {
			$weight = 0;
			$allgoods = array();
			$goodsarr = explode('|', $goods);

			foreach ($goodsarr as &$g) {
				if (empty($g)) {
					continue;
				}

				$goodsinfo = explode(',', $g);
				$goodsid = (!empty($goodsinfo[0]) ? intval($goodsinfo[0]) : '');
				$optionid = (!empty($goodsinfo[1]) ? intval($goodsinfo[1]) : 0);
				$goodstotal = (!empty($goodsinfo[2]) ? intval($goodsinfo[2]) : '1');

				if ($goodstotal < 1) {
					$goodstotal = 1;
				}

				if (empty($goodsid)) {
					show_json(1, array('price' => 0));
				}

				$sql = 'SELECT id as goodsid,title,type, weight,total,issendfree,isnodiscount, thumb,marketprice,cash,isverify,goodssn,productsn,sales,istime,timestart,timeend,usermaxbuy,maxbuy,unit,buylevels,buygroups,deleted,status,deduct,virtual,discounts,deduct2,ednum,edmoney,edareas,diyformid,diyformtype,diymode,dispatchtype,dispatchid,dispatchprice,yunbi_deduct FROM ' . tablename('sz_yi_goods') . ' WHERE id=:id AND uniacid=:uniacid  limit 1';
				$data = pdo_fetch($sql, array(':uniacid' => $uniacid, ':id' => $goodsid));

				if (empty($data)) {
					show_json(1, array('price' => 0));
				}

				$data['stock'] = $data['total'];
				$data['total'] = $goodstotal;

				if (!empty($optionid)) {
					$option = pdo_fetch('select id,title,marketprice,goodssn,productsn,stock,virtual,weight from ' . tablename('sz_yi_goods_option') . ' WHERE id=:id AND goodsid=:goodsid AND uniacid=:uniacid  limit 1', array(':uniacid' => $uniacid, ':goodsid' => $goodsid, ':id' => $optionid));

					if (!empty($option)) {
						$data['optionid'] = $optionid;
						$data['optiontitle'] = $option['title'];
						$data['marketprice'] = $option['marketprice'];

						if (!empty($option['weight'])) {
							$data['weight'] = $option['weight'];
						}
					}
				}

				$discounts = json_decode($data['discounts'], true);

				if (is_array($discounts)) {
					if (!empty($level['id'])) {
						if ((0 < $discounts['level' . $level['id']]) && ($discounts['level' . $level['id']] < 10)) {
							$level['discount'] = $discounts['level' . $level['id']];
						}
						else {
							if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < 10)) {
								$level['discount'] = floatval($level['discount']);
							}
							else {
								$level['discount'] = 0;
							}
						}
					}
					else {
						if ((0 < $discounts['default']) && ($discounts['default'] < 10)) {
							$level['discount'] = $discounts['default'];
						}
						else {
							if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < 10)) {
								$level['discount'] = floatval($level['discount']);
							}
							else {
								$level['discount'] = 0;
							}
						}
					}
				}

				$gprice = $data['marketprice'] * $goodstotal;
				$ggprice = 0;
				if (empty($data['isnodiscount']) && (0 < $level['discount']) && ($level['discount'] < 10)) {
					$dprice = round(($gprice * $level['discount']) / 10, 2);
					$discountprice += $gprice - $dprice;
					$ggprice = $dprice;
				}
				else {
					$ggprice = $gprice;
				}

				$data['ggprice'] = $ggprice;
				$allgoods[] = $data;
			}

			unset($g);

			foreach ($allgoods as $g) {
				if ($g['isverify'] == 2) {
					$isverify = true;
				}

				if (!empty($g['virtual']) || ($g['type'] == 2)) {
					$isvirtual = true;
				}

				if ($g['isverifysend'] == 1) {
					$isverifysend = true;
				}

				$deductprice += $g['deduct'] * $g['total'];

				if ($data['yunbi_deduct']) {
					$yunbideductprice += $g['yunbi_deduct'] * $g['total'];
				}
				else {
					$yunbideductprice += $g['yunbi_deduct'];
				}

				if ($g['deduct2'] == 0) {
					$deductprice2 += $g['ggprice'];
				}
				else {
					if (0 < $g['deduct2']) {
						if ($g['ggprice'] < $g['deduct2']) {
							$deductprice2 += $g['ggprice'];
						}
						else {
							$deductprice2 += $g['deduct2'];
						}
					}
				}

				if (p('channel')) {
					if (($ischannelpay == 1) && empty($ischannelpick)) {
						$isvirtual = true;
					}
				}
			}

			$isDispath = true;
			if ($isverify && !$isverifysend) {
				$isDispath = false;
			}

			if ($isverify && $isDispath) {
				show_json(1, array('price' => 0, 'hascoupon' => $hascoupon, 'couponcount' => $couponcount));
			}

			if (!empty($allgoods)) {
				foreach ($allgoods as $g) {
					$sendfree = false;

					if (!empty($g['issendfree'])) {
						$sendfree = true;
					}

					if (($g['type'] == 2) || ($g['type'] == 3)) {
						$sendfree = true;
					}
					else {
						$gareas = explode(';', $g['edareas']);
						if (($g['ednum'] <= $g['total']) && (0 < $g['ednum'])) {
							if (empty($gareas)) {
								$sendfree = true;
							}
							else if (!empty($address)) {
								if (!in_array($address['city'], $gareas)) {
									$sendfree = true;
								}
							}
							else if (!empty($member['city'])) {
								if (!in_array($member['city'], $gareas)) {
									$sendfree = true;
								}
							}
							else {
								$sendfree = true;
							}
						}

						if ((floatval($g['edmoney']) <= $g['ggprice']) && (0 < floatval($g['edmoney']))) {
							if (empty($gareas)) {
								$sendfree = true;
							}
							else if (!empty($address)) {
								if (!in_array($address['city'], $gareas)) {
									$sendfree = true;
								}
							}
							else if (!empty($member['city'])) {
								if (!in_array($member['city'], $gareas)) {
									$sendfree = true;
								}
							}
							else {
								$sendfree = true;
							}
						}
					}

					if (!$sendfree) {
						if ($g['dispatchtype'] == 1) {
							if (0 < $g['dispatchprice']) {
								if (!isset($minDispathPrice)) {
									$minDispathPrice = $g['dispatchprice'];
								}

								$dispatch_price = ($g['dispatchprice'] < $minDispathPrice ? $g['dispatchprice'] : $minDispathPrice);
							}
						}
						else {
							if ($g['dispatchtype'] == 0) {
								if (empty($g['dispatchid'])) {
									$dispatch_data = m('order')->getDefaultDispatch($supplier_uid);
								}
								else {
									$dispatch_data = m('order')->getOneDispatch($g['dispatchid'], $supplier_uid);
								}

								if (empty($dispatch_data)) {
									$dispatch_data = m('order')->getNewDispatch($supplier_uid);
								}

								if (!empty($dispatch_data)) {
									$areas = unserialize($dispatch_data['areas']);

									if ($dispatch_data['calculatetype'] == 1) {
										$param = $g['total'];
									}
									else {
										$param = $g['weight'] * $g['total'];
									}

									$dkey = $dispatch_data['id'];

									if (array_key_exists($dkey, $dispatch_array)) {
										$dispatch_array[$dkey]['param'] += $param;
									}
									else {
										$dispatch_array[$dkey]['data'] = $dispatch_data;
										$dispatch_array[$dkey]['param'] = $param;
									}
								}
							}
						}
					}
				}

				if (!empty($dispatch_array)) {
					foreach ($dispatch_array as $k => $v) {
						$dispatch_data = $dispatch_array[$k]['data'];
						$param = $dispatch_array[$k]['param'];
						$areas = unserialize($dispatch_data['areas']);

						if (!empty($address)) {
							$dispatch_price += m('order')->getCityDispatchPrice($areas, $address['city'], $param, $dispatch_data, $supplier_uid);
						}
						else if (!empty($member['city'])) {
							$dispatch_price += m('order')->getCityDispatchPrice($areas, $member['city'], $param, $dispatch_data, $supplier_uid);
						}
						else {
							$dispatch_price += m('order')->getDispatchPrice($param, $dispatch_data, -1, $supplier_uid);
						}
					}
				}
			}

			if ($dflag != 'true') {
				if (empty($saleset['dispatchnodeduct'])) {
					$deductprice2 += $dispatch_price;
				}
			}

			$deductcredit = 0;
			$deductmoney = 0;

			if ($sale_plugin) {
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

					if ($deductprice < $deductmoney) {
						$deductmoney = $deductprice;
					}

					if ($totalprice < $deductmoney) {
						$deductmoney = $totalprice;
					}

					$deductcredit = ($deductmoney / $pmoney) * $pcredit;
				}

				if (!empty($saleset['moneydeduct'])) {
					$deductcredit2 = m('member')->getCredit($openid, 'credit2');

					if ($totalprice < $deductcredit2) {
						$deductcredit2 = $totalprice;
					}

					if ($deductprice2 < $deductcredit2) {
						$deductcredit2 = $deductprice2;
					}
				}
			}

			$deductyunbi = 0;
			$deductyunbimoney = 0;
			if ($yunbi_plugin && $yunbiset['isdeduct']) {
				$virtual_currency = $member['virtual_currency'];
				$ycredit = 1;
				$ymoney = round(floatval($yunbiset['money']), 2);
				if ((0 < $ycredit) && (0 < $ymoney)) {
					if (($virtual_currency % $ycredit) == 0) {
						$deductyunbimoney = round(intval($virtual_currency / $ycredit) * $ymoney, 2);
					}
					else {
						$deductyunbimoney = round((intval($virtual_currency / $ycredit) + 1) * $ymoney, 2);
					}
				}

				if ($yunbideductprice < $deductyunbimoney) {
					$deductyunbimoney = $yunbideductprice;
				}

				if ($totalprice < $deductyunbimoney) {
					$deductyunbimoney = $totalprice;
				}

				$deductyunbi = ($deductyunbimoney / $ymoney) * $ycredit;
			}
		}

		show_json(1, array('price' => $dispatch_price, 'hascoupon' => $hascoupon, 'couponcount' => $couponcount, 'deductenough_money' => $deductenough_money, 'deductenough_enough' => $deductenough_enough, 'deductcredit2' => $deductcredit2, 'deductcredit' => $deductcredit, 'deductmoney' => $deductmoney, 'deductyunbi' => $deductyunbi, 'deductyunbimoney' => $deductyunbimoney, 'supplier_uid' => $supplier_uid));
	}
	else {
		if (($operation == 'create') && $_W['ispost']) {//如当前的方法是create并且是post方式传递
			
			$ischannelpay = intval($_GPC['ischannelpay']);
			$ischannelpick = intval($_GPC['ischannelpick']);
			$isyunbipay = intval($_GPC['isyunbipay']);
			$order_data = $_GPC['order'];
			
			if (p('hotel')) {
				if ($_GPC['type'] == '99') {
					$order_data[] = $_GPC;
				}
			}

			$ordersn_general = m('common')->createNO('order', 'ordersn', 'SH');
			$member = m('member')->getMember($openid);
			$level = m('member')->getLevel($openid);
			$can_buy = array();
			$can_buy = m('order')->isSupportDelivery($order_data);

			if ($can_buy['status'] == -1) {
				show_json(-2, '您的订单中，商品标题为 ‘' . $can_buy['title'] . '’ 的商品不支持配送核销，请更换配送方式或者剔除此商品！');
			}
			else {
				if ($can_buy['status'] == -2) {
					show_json(-2, '您的订单中，商品标题为 ‘' . $can_buy['title'] . '’ 的商品不支持快递配送，请更换配送方式或者剔除此商品！');
				}
			}

			$yunbiprice = 0;

			foreach ($order_data as $key => $order_row) {
				unset($minDispathPrice);
				$dispatchtype = intval($order_row['dispatchtype']);
				$addressid = intval($order_row['addressid']);
				$address = false;
				if (!empty($addressid) && (($dispatchtype == 0) || ($dispatchtype == 2))) {
					$address = pdo_fetch('select id,realname,mobile,address,province,city,area,street from ' . tablename('sz_yi_member_address') . ' where id=:id and openid=:openid and uniacid=:uniacid   limit 1', array(':uniacid' => $uniacid, ':openid' => $openid, ':id' => $addressid));

					if (empty($address)) {
						show_json(0, '未找到地址');
					}
				}

				$carrierid = intval($order_row['carrierid']);
				$goods = $order_row['goods'];

				if (empty($goods)) {
					show_json(0, '未找到任何商品');
				}

				$allgoods = array();
				$totalprice = 0;
				$goodsprice = 0;
				$redpriceall = 0;
				$weight = 0;
				$discountprice = 0;
				$goodsarr = explode('|', $goods);
				$cash = 1;
				$deductprice = 0;
				$deductprice2 = 0;
				$virtualsales = 0;
				$dispatch_price = 0;
				$dispatch_array = array();
				$sale_plugin = p('sale');
				$saleset = false;

				if ($sale_plugin) {
					$saleset = $sale_plugin->getSet();
					$saleset['enoughs'] = $sale_plugin->getEnoughs();
				}

				$isvirtual = false;
				$isverify = false;
				$isverifysend = false;

				foreach ($goodsarr as $g) {
					if (empty($g)) {
						continue;
					}

					$goodsinfo = explode(',', $g);
					$goodsid = (!empty($goodsinfo[0]) ? intval($goodsinfo[0]) : '');
					$optionid = (!empty($goodsinfo[1]) ? intval($goodsinfo[1]) : 0);
					$goodstotal = (!empty($goodsinfo[2]) ? intval($goodsinfo[2]) : '1');

					if ($goodstotal < 1) {
						$goodstotal = 1;
					}

					if ($store_total) {
						$storegoodstotal = pdo_fetchcolumn('SELECT total FROM ' . tablename('sz_yi_store_goods') . ' WHERE goodsid=:goodsid and uniacid=:uniacid and storeid=:storeid and optionid=:optionid', array(':goodsid' => $goodsid, ':uniacid' => $uniacid, ':storeid' => $carrierid, ':optionid' => $optionid));
						if (($storegoodstotal < $goodstotal) && !empty($carrierid)) {
							show_json(-2, '抱歉，此门店库存不足！');
						}
					}

					if (empty($goodsid)) {
						show_json(0, '参数错误，请刷新重试');
					}

					$channel_condtion = '';
					$yunbi_condtion = '';

					if (p('channel')) {
						$channel_condtion = 'isopenchannel,';
					}

					if (p('yunbi')) {
						$yunbi_condtion = 'isforceyunbi,yunbi_deduct,';
					}

					$sql = 'SELECT id as goodsid,costprice,' . $channel_condtion . 'supplier_uid,title,type, weight,total,issendfree,isnodiscount, thumb,marketprice,cash,isverify,goodssn,productsn,sales,istime,timestart,timeend,usermaxbuy,maxbuy,unit,buylevels,buygroups,deleted,status,deduct,virtual,discounts,discounts2,discountway,discounttype,deduct2,ednum,edmoney,edareas,diyformtype,diyformid,diymode,dispatchtype,dispatchid,dispatchprice,redprice, yunbi_deduct,bonusmoney FROM ' . tablename('sz_yi_goods') . ' where id=:id and uniacid=:uniacid  limit 1';
					$data = pdo_fetch($sql, array(':uniacid' => $uniacid, ':id' => $goodsid));

					if (p('channel')) {
						if ($ischannelpay == 1) {
							if (empty($data['isopenchannel'])) {
								show_json(-1, $data['title'] . '<br/> 不支持采购!请前往购物车移除该商品！');
							}
						}
					}

					if (empty($data['status']) || !empty($data['deleted'])) {
						show_json(-1, $data['title'] . '<br/> 已下架!');
					}

					$virtualid = $data['virtual'];
					$data['stock'] = $data['total'];
					$data['total'] = $goodstotal;

					if ($data['cash'] != 2) {
						$cash = 0;
					}

					$unit = (empty($data['unit']) ? '件' : $data['unit']);

					if (0 < $data['maxbuy']) {
						if ($data['maxbuy'] < $goodstotal) {
							show_json(-1, $data['title'] . '<br/> 一次限购 ' . $data['maxbuy'] . $unit . '!');
						}
					}

					if (0 < $data['usermaxbuy']) {
						$order_goodscount = pdo_fetchcolumn('select ifnull(sum(og.total),0)  from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_order') . ' o on og.orderid=o.id ' . ' where og.goodsid=:goodsid and  o.status>=1 and o.openid=:openid  and og.uniacid=:uniacid ', array(':goodsid' => $data['goodsid'], ':uniacid' => $uniacid, ':openid' => $openid));

						if ($data['usermaxbuy'] <= $order_goodscount) {
							show_json(-1, $data['title'] . '<br/> 最多限购 ' . $data['usermaxbuy'] . $unit . '!');
						}
					}

					if ($data['istime'] == 1) {
						if (time() < $data['timestart']) {
							show_json(-1, $data['title'] . '<br/> 限购时间未到!');
						}

						if ($data['timeend'] < time()) {
							show_json(-1, $data['title'] . '<br/> 限购时间已过!');
						}
					}

					$levelid = intval($member['level']);
					$groupid = intval($member['groupid']);

					if ($data['buylevels'] != '') {
						$buylevels = explode(',', $data['buylevels']);

						if (!in_array($levelid, $buylevels)) {
							show_json(-1, '您的会员等级无法购买<br/>' . $data['title'] . '!');
						}
					}

					if ($data['buygroups'] != '') {
						$buygroups = explode(',', $data['buygroups']);

						if (!in_array($groupid, $buygroups)) {
							show_json(-1, '您所在会员组无法购买<br/>' . $data['title'] . '!');
						}
					}

					if (!empty($optionid)) {
						$option = pdo_fetch('select * from ' . tablename('sz_yi_goods_option') . ' where id=:id and goodsid=:goodsid and uniacid=:uniacid  limit 1', array(':uniacid' => $uniacid, ':goodsid' => $goodsid, ':id' => $optionid));
						if (p('channel') && !empty($ischannelpick)) {
							$my_option_stock = p('channel')->getMyOptionStock($openid, $goodsid, $optionid);
							$option['stock'] = $my_option_stock;
						}

						if (!empty($option)) {
							if ($option['stock'] != -1) {
								if (empty($option['stock'])) {
									show_json(-1, $data['title'] . '<br/>' . $option['title'] . ' 库存不足!');
								}
							}

							$data['optionid'] = $optionid;
							$data['optiontitle'] = $option['title'];
							$data['marketprice'] = $option['marketprice'];

							if (!empty($option['costprice'])) {
								$data['costprice'] = $option['costprice'];
							}

							$virtualid = $option['virtual'];

							if (!empty($option['goodssn'])) {
								$data['goodssn'] = $option['goodssn'];
							}

							if (!empty($option['productsn'])) {
								$data['productsn'] = $option['productsn'];
							}

							if (!empty($option['weight'])) {
								$data['weight'] = $option['weight'];
							}

							if (!empty($option['redprice'])) {
								$data['redprice'] = $option['redprice'];
							}
						}
					}
					else {
						if (p('channel') && !empty($ischannelpick)) {
							$channel_stock = p('channel')->getMyOptionStock($openid, $data['goodsid'], 0);
							$data['stock'] = $channel_stock;
						}

						if ($data['stock'] != -1) {
							if (empty($data['stock'])) {
								show_json(-1, $data['title'] . '<br/>库存不足!');
							}
						}
					}

					$data['diyformdataid'] = 0;
					$data['diyformdata'] = iserializer(array());
					$data['diyformfields'] = iserializer(array());

					if ($order_row['fromcart'] == 1) {
						if ($diyform_plugin) {
							$cartdata = pdo_fetch('select id,diyformdataid,diyformfields,diyformdata from ' . tablename('sz_yi_member_cart') . ' ' . ' where goodsid=:goodsid and optionid=:optionid and openid=:openid and deleted=0 order by id desc limit 1', array(':goodsid' => $data['goodsid'], ':optionid' => $data['optionid'], ':openid' => $openid));

							if (!empty($cartdata)) {
								$data['diyformdataid'] = $cartdata['diyformdataid'];
								$data['diyformdata'] = $cartdata['diyformdata'];
								$data['diyformfields'] = $cartdata['diyformfields'];
							}
						}
					}
					else {
						if (!empty($diyformtype) && !empty($data['diyformid'])) {
							$temp_data = $diyform_plugin->getOneDiyformTemp($goods_data_id, 0);
							$data['diyformfields'] = $temp_data['diyformfields'];
							$data['diyformdata'] = $temp_data['diyformdata'];
							$data['declaration_mid'] = $temp_data['declaration_mid'];
							$data['diyformid'] = $formInfo['id'];
						}
					}

					if (strpos($data['redprice'], '%') === false) {
						if (strpos($data['redprice'], '-') === false) {
							$redprice = $data['redprice'];
						}
						else {
							$rprice = explode('-', $data['redprice']);

							if (200 < $rprice[1]) {
								$redprice = rand($rprice[0] * 100, 200 * 100) / 100;
							}
							else if ($rprice[0] < 0) {
								$redprice = rand(0, $rprice[1] * 100) / 100;
							}
							else {
								$redprice = rand($rprice[0] * 100, $rprice[1] * 100) / 100;
							}
						}
					}
					else {
						$rprice = explode('%', $data['redprice']);
						$redprice = ($rprice[0] * $data['marketprice']) / 100;
					}

					$redprice = $redprice * $goodstotal;
					$redpriceall += $redprice;

					if (p('channel')) {
						$my_info = p('channel')->getInfo($openid);

						if ($ischannelpay == 1) {
							$data['marketprice'] = ($data['marketprice'] * $my_info['my_level']['purchase_discount']) / 100;
						}
					}

					$gprice = $data['marketprice'] * $goodstotal;
					$goodsprice += $gprice;
					$ggprice = 0;
					if (p('hotel') && ($_GPC['type'] == '99')) {
						$gprice = $_GPC['goodsprice'];
					}

					if ($data['discountway'] == 1) {
						if ($data['discounttype'] == 1) {
							$discounts = json_decode($data['discounts'], true);
							$level = m('member')->getLevel($openid);

							if (is_array($discounts)) {
								if (!empty($level['id'])) {
									if ((0 < floatval($discounts['level' . $level['id']])) && (floatval($discounts['level' . $level['id']]) < 10)) {
										$level['discount'] = floatval($discounts['level' . $level['id']]);
									}
									else {
										if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < 10)) {
											$level['discount'] = floatval($level['discount']);
										}
										else {
											$level['discount'] = 0;
										}
									}
								}
								else {
									if ((0 < floatval($discounts['default'])) && (floatval($discounts['default']) < 10)) {
										$level['discount'] = floatval($discounts['default']);
									}
									else {
										if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < 10)) {
											$level['discount'] = floatval($level['discount']);
										}
										else {
											$level['discount'] = 0;
										}
									}
								}

								if (p('channel') && ($ischannelpay == 1)) {
									$level['discount'] = 10;
								}
							}
						}
						else {
							$discounts = json_decode($data['discounts2'], true);
							$level = p('commission')->getLevel($openid);
							$level['discount'] = 0;
							if (($member['isagent'] == 1) && ($member['status'] == 1)) {
								if (is_array($discounts)) {
									if (!empty($level['id'])) {
										if ((0 < floatval($discounts['level' . $level['id']])) && (floatval($discounts['level' . $level['id']]) < 10)) {
											$level['discount'] = floatval($discounts['level' . $level['id']]);
										}
									}
									else {
										if ((0 < floatval($discounts['default'])) && (floatval($discounts['default']) < 10)) {
											$level['discount'] = floatval($discounts['default']);
										}
									}
								}
							}
						}

						if (p('channel') && ($ischannelpay == 1)) {
							$level['discount'] = 10;
						}

						if (empty($data['isnodiscount']) && (0 < $level['discount']) && ($level['discount'] < 10)) {
							$dprice = round(($gprice * $level['discount']) / 10, 2);
							$discountprice += $gprice - $dprice;
							$ggprice = $dprice;
						}
						else {
							$ggprice = $gprice;
						}
					}
					else {
						if ($data['discounttype'] == 1) {
							$discounts = json_decode($data['discounts'], true);
							$level = m('member')->getLevel($openid);
							$level['discount'] = 0;

							if (is_array($discounts)) {
								if (!empty($level['id'])) {
									if ((0 < floatval($discounts['level' . $level['id']])) && (floatval($discounts['level' . $level['id']]) < $data['marketprice'])) {
										$level['discount'] = floatval($discounts['level' . $level['id']]);
									}
									else {
										if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < $data['marketprice'])) {
											$level['discount'] = floatval($level['discount']);
										}
										else {
											$level['discount'] = 0;
										}
									}
								}
								else {
									if ((0 < floatval($discounts['default'])) && (floatval($discounts['default']) < $data['marketprice'])) {
										$level['discount'] = floatval($discounts['default']);
									}
									else {
										if ((0 < floatval($level['discount'])) && (floatval($level['discount']) < $data['marketprice'])) {
											$level['discount'] = floatval($level['discount']);
										}
										else {
											$level['discount'] = 0;
										}
									}
								}
							}
						}
						else {
							$discounts = json_decode($data['discounts2'], true);
							$level = p('commission')->getLevel($openid);
							$level['discount'] = 0;
							if (($member['isagent'] == 1) && ($member['status'] == 1)) {
								if (is_array($discounts)) {
									if (!empty($level['id'])) {
										if (floatval($discounts['level' . $level['id']]) < $data['marketprice']) {
											$level['discount'] = floatval($discounts['level' . $level['id']]);
										}
									}
									else {
										if (floatval($discounts['default']) < $data['marketprice']) {
											$level['discount'] = floatval($discounts['default']);
										}
									}
								}
							}
						}

						if (empty($data['isnodiscount']) && ($level['discount'] < $data['marketprice'])) {
							$dprice = round($gprice - ($level['discount'] * $goodstotal), 2);
							$discountprice += $gprice - $dprice;
							$ggprice = $dprice;
						}
						else {
							$ggprice = $gprice;
						}

						if (p('channel') && ($ischannelpay == 1)) {
							$ggprice = $gprice;
						}
					}

					$data['realprice'] = $ggprice;
					$totalprice += $ggprice;
					$dispatchsend = false;

					if ($dispatchtype == '2') {
						$dispatchtype = '0';
						$dispatchsend = true;
					}

					if (($data['isverify'] == 2) && !$dispatchsend) {
						$isverify = true;
					}

					if (empty($dispatchtype) && $isverify) {
						$isverifysend = true;
					}

					if (!empty($data['virtual']) || ($data['type'] == 2)) {
						$isvirtual = true;
					}

					if (p('channel')) {
						if (($ischannelpay == 1) && empty($ischannelpick)) {
							$isvirtual = true;
						}
					}

					$deductprice += $data['deduct'] * $data['total'];

					if ($data['yunbi_deduct']) {
						$yunbiprice += $data['yunbi_deduct'] * $data['total'];
						$yunbideductprice = $data['yunbi_deduct'] * $data['total'];
					}

					$deductyunbi = 0;
					$deductyunbimoney = 0;
					if ($yunbi_plugin && $yunbiset['isdeduct']) {
						if (empty($isyunbipay)) {
							if (isset($_GPC['order']) && !empty($_GPC['order'][0]['yunbi'])) {
								$virtual_currency = $member['virtual_currency'];
								$ycredit = 1;
								$ymoney = round(floatval($yunbiset['money']), 2);
								if ((0 < $ycredit) && (0 < $ymoney)) {
									if (($virtual_currency % $ycredit) == 0) {
										$deductyunbimoney = round(intval($virtual_currency / $ycredit) * $ymoney * $data['total'], 2);
									}
									else {
										$deductyunbimoney = round((intval($virtual_currency / $ycredit) + 1) * $ymoney * $data['total'], 2);
									}
								}

								if ($yunbideductprice < $deductyunbimoney) {
									$deductyunbimoney = $yunbideductprice;
								}

								if ($totalprice < $deductyunbimoney) {
									$deductyunbimoney = $totalprice;
								}

								$deductyunbi = round(($deductyunbimoney / $ymoney) * $ycredit, 2);
							}
						}
						else {
							$virtual_currency = $member['virtual_currency'];
							$ycredit = 1;
							$ymoney = round(floatval($yunbiset['money']), 2);
							if ((0 < $ycredit) && (0 < $ymoney)) {
								if (($virtual_currency % $ycredit) == 0) {
									$deductyunbimoney = round(intval($virtual_currency / $ycredit) * $ymoney * $data['total'], 2);
								}
								else {
									$deductyunbimoney = round((intval($virtual_currency / $ycredit) + 1) * $ymoney * $data['total'], 2);
								}
							}

							if ($yunbideductprice < $deductyunbimoney) {
								$deductyunbimoney = $yunbideductprice;
							}

							if ($totalprice < $deductyunbimoney) {
								$deductyunbimoney = $totalprice;
							}

							$deductyunbi = round(($deductyunbimoney / $ymoney) * $ycredit, 2);
						}

						$totalprice -= $deductyunbimoney;
					}

					$virtualsales += $data['sales'];

					if ($data['deduct2'] == 0) {
						$deductprice2 += $ggprice;
					}
					else {
						if (0 < $data['deduct2']) {
							if ($ggprice < $data['deduct2']) {
								$deductprice2 += $ggprice;
							}
							else {
								$deductprice2 += $data['deduct2'];
							}
						}
					}

					$allgoods[] = $data;
				}

				if (empty($allgoods)) {
					show_json(0, '未找到任何商品');
				}

				$deductenough = 0;
				$tmp_money = 0;
				if (p('channel') && ($ischannelpay == 1)) {
					$saleset = array();
				}

				if ($saleset) {
					foreach ($saleset['enoughs'] as $e) {
						if ((floatval($e['enough']) <= $totalprice) && (0 < floatval($e['money']))) {
							if ($tmp_money < $e['enough']) {
								$tmp_money = $e['enough'];
								$deductenough = floatval($e['money']);

								if ($totalprice < $deductenough) {
									$deductenough = $totalprice;
								}
							}
						}
					}
				}

				$isDispath = true;
				if ($isverify && !$isverifysend && !$dispatchsend) {
					$isDispath = false;
				}

				if (!$isvirtual && $isDispath && ($dispatchtype == 0)) {
					$isAllSameDispath = true;

					foreach ($allgoods as $g) {
						$g['ggprice'] = $g['realprice'];
						$sendfree = false;

						if (!empty($g['issendfree'])) {
							$sendfree = true;
						}
						else {
							$gareas = explode(';', $g['edareas']);
							if (($g['ednum'] <= $g['total']) && (0 < $g['ednum'])) {
								if (empty($gareas)) {
									$sendfree = true;
								}
								else if (!empty($address)) {
									if (!in_array($address['city'], $gareas)) {
										$sendfree = true;
									}
								}
								else if (!empty($member['city'])) {
									if (!in_array($member['city'], $gareas)) {
										$sendfree = true;
									}
								}
								else {
									$sendfree = true;
								}
							}

							if ((floatval($g['edmoney']) <= $g['ggprice']) && (0 < floatval($g['edmoney']))) {
								if (empty($gareas)) {
									$sendfree = true;
								}
								else if (!empty($address)) {
									if (!in_array($address['city'], $gareas)) {
										$sendfree = true;
									}
								}
								else if (!empty($member['city'])) {
									if (!in_array($member['city'], $gareas)) {
										$sendfree = true;
									}
								}
								else {
									$sendfree = true;
								}
							}
						}

						if (!$sendfree) {
							if ($g['dispatchtype'] == 1) {
								if (0 < $g['dispatchprice']) {
									if (!isset($minDispathPrice)) {
										$minDispathPrice = $g['dispatchprice'];
									}

									$dispatch_price = ($g['dispatchprice'] < $minDispathPrice ? $g['dispatchprice'] : $minDispathPrice);
								}
							}
							else {
								if ($g['dispatchtype'] == 0) {
									if (empty($g['dispatchid'])) {
										$dispatch_data = m('order')->getDefaultDispatch($g['supplier_uid']);
									}
									else {
										$dispatch_data = m('order')->getOneDispatch($g['dispatchid'], $g['supplier_uid']);
									}

									if (empty($dispatch_data)) {
										$dispatch_data = m('order')->getNewDispatch($g['supplier_uid']);
									}

									if (!empty($dispatch_data)) {
										$areas = unserialize($dispatch_data['areas']);

										if ($dispatch_data['calculatetype'] == 1) {
											$param = $g['total'];
										}
										else {
											$param = $g['weight'] * $g['total'];
										}

										$dkey = $dispatch_data['id'];

										if (array_key_exists($dkey, $dispatch_array)) {
											$dispatch_array[$dkey]['param'] += $param;
										}
										else {
											$dispatch_array[$dkey]['data'] = $dispatch_data;
											$dispatch_array[$dkey]['param'] = $param;
										}
									}
								}
							}
						}
					}

					if (!empty($dispatch_array)) {
						foreach ($dispatch_array as $k => $v) {
							$dispatch_data = $dispatch_array[$k]['data'];
							$param = $dispatch_array[$k]['param'];
							$areas = unserialize($dispatch_data['areas']);

							if (!empty($address)) {
								$dispatch_price += m('order')->getCityDispatchPrice($areas, $address['city'], $param, $dispatch_data, $order_row['supplier_uid']);
							}
							else if (!empty($member['city'])) {
								$dispatch_price += m('order')->getCityDispatchPrice($areas, $member['city'], $param, $dispatch_data, $order_row['supplier_uid']);
							}
							else {
								$dispatch_price += m('order')->getDispatchPrice($param, $dispatch_data, -1, $order_row['supplier_uid']);
							}
						}
					}
				}

				if ($saleset) {
					if (!empty($saleset['enoughfree'])) {
						if (floatval($saleset['enoughorder']) <= 0) {
							$dispatch_price = 0;
						}
						else {
							if (floatval($saleset['enoughorder']) <= $totalprice) {
								if (empty($saleset['enoughareas'])) {
									$dispatch_price = 0;
								}
								else {
									$areas = explode(';', $saleset['enoughareas']);

									if (!empty($address)) {
										if (!in_array($address['city'], $areas)) {
											$dispatch_price = 0;
										}
									}
								}
							}
						}
					}
				}

				$couponprice = 0;
				$couponid = intval($order_row['couponid']);

				if ($plugc) {
					$coupon = $plugc->getCouponByDataID($couponid);

					if (!empty($coupon)) {
						if (($coupon['enough'] <= $totalprice) && empty($coupon['used'])) {
							if ($coupon['backtype'] == 0) {
								if (0 < $coupon['deduct']) {
									$couponprice = $coupon['deduct'];
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

				$totalprice -= $deductenough;
				$totalprice += $dispatch_price;
				if ($saleset && empty($saleset['dispatchnodeduct'])) {
					$deductprice2 += $dispatch_price;
				}

				$deductcredit = 0;
				$deductmoney = 0;
				$deductcredit2 = 0;

				if ($sale_plugin) {
					if (isset($_GPC['order']) && !empty($_GPC['order'][0]['deduct'])) {
						$credit = m('member')->getCredit($openid, 'credit1');
						$saleset = $sale_plugin->getSet();

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

							if ($deductprice < $deductmoney) {
								$deductmoney = $deductprice;
							}

							if ($totalprice < $deductmoney) {
								$deductmoney = $totalprice;
							}

							$deductcredit = round(($deductmoney / $pmoney) * $pcredit, 2);
						}
					}

					$totalprice -= $deductmoney;

					if (!empty($order_row['deduct2'])) {
						$deductcredit2 = m('member')->getCredit($openid, 'credit2');

						if ($totalprice < $deductcredit2) {
							$deductcredit2 = $totalprice;
						}

						if ($deductprice2 < $deductcredit2) {
							$deductcredit2 = $deductprice2;
						}
					}

					$totalprice -= $deductcredit2;
				}

				$ordersn = m('common')->createNO('order', 'ordersn', 'SH');
				$verifycode = '';

				if ($isverify) {
					$verifycode = random(8, true);

					while (1) {
						$count = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_order') . ' where verifycode=:verifycode and uniacid=:uniacid limit 1', array(':verifycode' => $verifycode, ':uniacid' => $_W['uniacid']));

						if ($count <= 0) {
							break;
						}

						$verifycode = random(8, true);
					}
				}

				$carrier = $_GPC['order'][0]['carrier'];
				$carriers = (is_array($carrier) ? iserializer($carrier) : iserializer(array()));

				if ($totalprice <= 0) {
					$totalprice = 0;
				}

				if (200 < $redpriceall) {
					$redpriceall = 200;
				}

				if (p('hotel')) {
					if ($_GPC['type'] == '99') {
						$btime = $_SESSION['data']['btime'];
						$days = intval($_SESSION['data']['day']);
						$etime = $_SESSION['data']['etime'];
						$sql2 = 'SELECT * FROM ' . tablename('sz_yi_hotel_room') . ' WHERE `goodsid` = :goodsid';
						$params2 = array(':goodsid' => $_GPC['id']);
						$room = pdo_fetch($sql2, $params2);

						if ($discountprice != '0') {
							$totalprice = $_GPC['totalprice'] - $discountprice;
						}
						else {
							$totalprice = $_GPC['totalprice'];
						}

						$goodsprice = $_GPC['goodsprice'];
					}
				}
				
				//数组数据，包含公众号ID，用户openid,订单号等信息。
				$order = array('supplier_uid' => $order_row['supplier_uid'], 'uniacid' => $uniacid, 'openid' => $openid, 'ordersn' => $ordersn, 'ordersn_general' => $ordersn_general, 'price' => $totalprice, 'cash' => $cash, 'discountprice' => $discountprice, 'deductprice' => $deductmoney, 'deductcredit' => $deductcredit, 'deductyunbimoney' => $yunbiprice, 'deductyunbi' => $deductyunbi, 'deductcredit2' => $deductcredit2, 'deductenough' => $deductenough, 'status' => 0, 'paytype' => 0, 'transid' => '', 'remark' => $order_row['remark'], 'addressid' => empty($dispatchtype) ? $addressid : 0, 'goodsprice' => $goodsprice, 'dispatchprice' => $dispatch_price, 'dispatchtype' => $dispatchtype, 'dispatchid' => $dispatchid, 'storeid' => $carrierid, 'carrier' => $carriers, 'createtime' => time(), 'isverify' => $isverify ? 1 : 0, 'verifycode' => $verifycode, 'virtual' => $virtualid, 'isvirtual' => $isvirtual ? 1 : 0, 'oldprice' => $totalprice, 'olddispatchprice' => $dispatch_price, 'couponid' => $couponid, 'couponprice' => $couponprice, 'redprice' => $redpriceall);

				if (p('channel')) {
					if (!empty($ischannelpick)) {
						$order['ischannelself'] = 1;
						$order['status'] = 1;
					}
				}

				if (p('hotel')) {
					if ($_GPC['type'] == '99') {
						$order['order_type'] = '3';
						$order['addressid'] = '9999999';
						$order['checkname'] = $_GPC['realname'];
						$order['realmobile'] = $_GPC['realmobile'];
						$order['realsex'] = $_GPC['realsex'];
						$order['invoice'] = $_GPC['invoice'];
						$order['invoiceval'] = $_GPC['invoiceval'];
						$order['invoicetext'] = $_GPC['invoicetext'];
						$order['num'] = $_GPC['goodscount'];
						$order['btime'] = $btime;
						$order['etime'] = $etime;
						$order['depositprice'] = $_GPC['depositprice'];
						$order['depositpricetype'] = $_GPC['depositpricetype'];
						$order['roomid'] = $room['id'];
						$order['days'] = $days;
						$order['dispatchprice'] = 0;
						$order['olddispatchprice'] = 0;
						$order['deductcredit2'] = $_GPC['deductcredit2'];
						$order['deductcredit'] = $_GPC['deductcredit'];
						$order['deductprice'] = $_GPC['deductcredit'];
					}
				}

				if ($diyform_plugin) {
					if (is_array($order_row['diydata']) && !empty($order_formInfo)) {
						$diyform_data = $diyform_plugin->getInsertData($fields, $order_row['diydata']);
						$idata = $diyform_data['data'];
						$order['diyformfields'] = iserializer($fields);
						$order['diyformdata'] = $idata;
						$order['diyformid'] = $order_formInfo['id'];
					}
				}

				if (!empty($address)) {
					$order['address'] = iserializer($address);
				}
				
				
				
				/**-----插入订单sz_yi_order表中 */
				pdo_insert('sz_yi_order', $order);
				$orderid = pdo_insertid();//返回数据的自增ID；创建order

				if (p('hotel')) {
					if ($_GPC['type'] == '99') {
						$r_sql = 'SELECT * FROM ' . tablename('sz_yi_hotel_room_price') . ' WHERE `roomid` = :roomid AND `roomdate` >= :btime AND ' . ' `roomdate` < :etime';
						$params = array(':roomid' => $room['id'], ':btime' => $btime, ':etime' => $etime);
						$price_list = pdo_fetchall($r_sql, $params);

						if ($price_list != '') {
							foreach ($price_list as $key => $value) {
								$order_room = array('orderid' => $orderid, 'roomid' => $room['id'], 'roomdate' => $value['roomdate'], 'thisdate' => $value['thisdate'], 'oprice' => $value['oprice'], 'cprice' => $value['cprice'], 'mprice' => $value['mprice']);
								pdo_insert('sz_yi_order_room', $order_room);
							}
						}

						$sql2 = 'SELECT * FROM ' . tablename('sz_yi_hotel_room') . ' WHERE `goodsid` = :goodsid';
						$params2 = array(':goodsid' => $allgoods[0]['goodsid']);
						$room = pdo_fetch($sql2, $params2);
						$starttime = $btime;
						$i = 0;

						while ($i < $days) {
							$sql = 'SELECT * FROM ' . tablename('sz_yi_hotel_room_price') . ' WHERE  roomid = :roomid AND roomdate = :roomdate';
							$day = pdo_fetch($sql, array(':roomid' => $room['id'], ':roomdate' => $btime));
							pdo_update('sz_yi_hotel_room_price', array('num' => $day['num'] - $_GPC['goodscount']), array('id' => $day['id']));
							$btime += 86400;
							++$i;
						}
					}
				}

				if (is_array($carrier)) {
					$up = array('realname' => $carrier['carrier_realname'], 'membermobile' => $carrier['carrier_mobile']);
					$up_mc = array('realname' => $carrier['carrier_realname'], 'mobile' => $carrier['carrier_mobile']);
					pdo_update('sz_yi_member', $up, array('id' => $member['id'], 'uniacid' => $_W['uniacid']));

					if (!empty($member['uid'])) {
						pdo_update('mc_members', $up_mc, array('uid' => $member['uid'], 'uniacid' => $_W['uniacid']));
					}
				}

				if ($order_row['fromcart'] == 1) {
					$cartids = $order_row['cartids'];

					if (!empty($cartids)) {
						pdo_query('update ' . tablename('sz_yi_member_cart') . ' set deleted=1 where id in (' . $cartids . ') and openid=:openid and goodsid=:goodsid and optionid=:optionid and uniacid=:uniacid ', array(':uniacid' => $uniacid, ':openid' => $openid, ':goodsid' => $data['goodsid'], ':optionid' => $data['optionid']));
					}
					else {
						pdo_query('update ' . tablename('sz_yi_member_cart') . ' set deleted=1 where openid=:openid and goodsid=:goodsid and optionid=:optionid and uniacid=:uniacid ', array(':uniacid' => $uniacid, ':openid' => $openid, ':goodsid' => $data['goodsid'], ':optionid' => $data['optionid']));
					}
				}

				$supplier_or_merchant_price = 0;
				$supplier_or_merchant_basis = 0;

				foreach ($allgoods as $goods) {
					$order_goods = array('uniacid' => $uniacid, 'orderid' => $orderid, 'goodsid' => $goods['goodsid'], 'price' => $goods['marketprice'] * $goods['total'], 'total' => $goods['total'], 'optionid' => $goods['optionid'], 'createtime' => time(), 'optionname' => $goods['optiontitle'], 'goodssn' => $goods['goodssn'], 'productsn' => $goods['productsn'], 'realprice' => $goods['realprice'], 'oldprice' => $goods['realprice'], 'openid' => $openid, 'goods_op_cost_price' => $goods['costprice']);
					if (p('supplier') || p('merchant')) {
						$supplier_or_merchant_price += $goods['costprice'] * $goods['total'];
						$supplier_or_merchant_basis += $goods['bonusmoney'] * $goods['total'];
					}

					if (p('hotel') && ($_GPC['type'] == '99')) {
						$order_goods['price'] = $goodsprice;
						$order_goods['realprice'] = $goodsprice - $discountprice;
						$order_goods['oldprice'] = $goodsprice - $discountprice;
					}

					if ($diyform_plugin) {
						$order_goods['diyformid'] = $goods['diyformid'];
						$order_goods['diyformdata'] = $goods['diyformdata'];
						$order_goods['declaration_mid'] = $goods['declaration_mid'];
						$order_goods['diyformfields'] = $goods['diyformfields'];
					}

					if (p('supplier')) {
						$order_goods['supplier_uid'] = $goods['supplier_uid'];
					}

					if (p('channel')) {
						$my_info = p('channel')->getInfo($openid, $goods['goodsid'], $goods['optionid'], $goods['total']);
						if (($ischannelpay == 1) && empty($ischannelpick)) {
							$every_turn_price = $goods['marketprice'] / $my_info['my_level']['purchase_discount'] / 100;
							$channel_cond = '';

							if (!empty($goods['optionid'])) {
								$channel_cond = ' AND optionid=' . $goods['optionid'];
							}

							$ischannelstock = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_stock') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND openid=\'' . $openid . '\' AND goodsid=' . $goods['goodsid'] . ' ' . $channel_cond);

							if (empty($ischannelstock)) {
								pdo_insert('sz_yi_channel_stock', array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'goodsid' => $goods['goodsid'], 'optionid' => $goods['optionid'], 'stock_total' => $goods['total']));
							}
							else {
								$stock_total = $ischannelstock['stock_total'] + $goods['total'];
								pdo_update('sz_yi_channel_stock', array('stock_total' => $stock_total), array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'optionid' => $goods['optionid'], 'goodsid' => $goods['goodsid']));
							}

							$op_where = '';

							if (!empty($goods['optionid'])) {
								$op_where = ' AND optionid=' . $goods['optionid'];
							}

							$surplus_stock = pdo_fetchcolumn('SELECT stock_total FROM ' . tablename('sz_yi_channel_stock') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND openid=\'' . $openid . '\' AND goodsid=' . $goods['goodsid'] . ' ' . $op_where);
							$up_mem = m('member')->getInfo($my_info['up_level']['openid']);
							$stock_log = array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'goodsid' => $goods['goodsid'], 'optionid' => $goods['optionid'], 'every_turn' => $goods['total'], 'every_turn_price' => $goods['marketprice'], 'every_turn_discount' => $my_info['my_level']['purchase_discount'], 'goods_price' => $every_turn_price, 'paytime' => time(), 'type' => 1, 'surplus_stock' => $surplus_stock, 'mid' => $up_mem['id']);
							pdo_insert('sz_yi_channel_stock_log', $stock_log);
							$order_goods['ischannelpay'] = $ischannelpay;
						}

						$order_goods['channel_id'] = 0;

						if (!empty($my_info['up_level'])) {
							$up_member = m('member')->getInfo($my_info['up_level']['openid']);
							$order_goods['channel_id'] = $up_member['id'];
						}
					}

					pdo_insert('sz_yi_order_goods', $order_goods);

					if (p('channel')) {
						if (!empty($order_goods['channel_id']) && empty($order_goods['ischannelpay'])) {
							$order_goods_id = pdo_insertid();
							$profit = ((($order_goods['price'] - (($order_goods['price'] * $my_info['up_level']['purchase_discount']) / 100)) * $my_info['up_level']['profit_sharing']) / 100) + (($order_goods['price'] * $my_info['up_level']['purchase_discount']) / 100);
							$profit_data = array('uniacid' => $_W['uniacid'], 'order_goods_id' => $order_goods_id, 'goods_price' => $order_goods['price'], 'discount' => $my_info['up_level']['purchase_discount'], 'profit_ratio' => $my_info['up_level']['profit_sharing'], 'profit' => $profit);
							pdo_insert('sz_yi_channel_order_goods_profit', $profit_data);
						}
					}
				}

				if (p('supplier')) {
					$supplier_set = p('supplier')->getSet();
					$supplier_order = array('uniacid' => $_W['uniacid'], 'orderid' => $orderid);

					if (empty($supplier_set['isopenbonus'])) {
						$supplier_order['money'] = $supplier_or_merchant_price + $dispatch_price;
						$supplier_order['isopenbonus'] = 0;
					}
					else {
						$supplier_order['money'] = $supplier_or_merchant_basis + $dispatch_price;
						$supplier_order['isopenbonus'] = 1;
					}

					pdo_insert('sz_yi_supplier_order', $supplier_order);
				}

				if (p('merchant')) {
					$merchant_set = p('merchant')->getSet();
					$merchant_order = array('uniacid' => $_W['uniacid'], 'orderid' => $orderid);

					if (empty($merchant_set['isopenbonus'])) {
						$merchant_order['money'] = $totalprice;
						$merchant_order['isopenbonus'] = 0;
					}
					else {
						$merchant_order['money'] = $supplier_or_merchant_basis;
						$merchant_order['isopenbonus'] = 1;
					}

					pdo_insert('sz_yi_merchant_order', $merchant_order);
				}

				$store_info = pdo_fetch(' SELECT * FROM ' . tablename('sz_yi_store') . ' WHERE id=:id and uniacid=:uniacid ', array(':id' => $carrierid, ':uniacid' => $_W['uniacid']));
				$order_goods_store = pdo_fetchall(' SELECT * FROM ' . tablename('sz_yi_order_goods') . ' WHERE orderid=:id and uniacid=:uniacid', array(':uniacid' => $_W['uniacid'], ':id' => $orderid));
				$goods_realprice = 0;

				foreach ($order_goods_store as $val) {
					$goods_store = pdo_fetch(' SELECT * FROM ' . tablename('sz_yi_goods') . ' WHERE uniacid=:uniacid and id=:id ', array(':uniacid' => $_W['uniacid'], ':id' => $val['goodsid']));
					if (empty($goods_store['balance_with_store']) || ($goods_store['balance_with_store'] == '0')) {
						$goods_realprice += ($val['price'] * (100 - $goods_store['goods_balance'])) / 100;
					}
					else if (!empty($store_info['balance'])) {
						$goods_realprice += ($val['price'] * (100 - $store_info['balance'])) / 100;
					}
					else {
						$goods_realprice += $val['price'];
					}
				}

				$realprice = $goods_realprice - ((($goodsprice - $totalprice) * (100 - $store_info['balance'])) / 100);
				pdo_update('sz_yi_order', array('realprice' => $realprice), array('id' => $orderid, 'uniacid' => $_W['uniacid']));

				if (p('hotel')) {
					$set = set_medias(m('common')->getSysset('shop'), array('logo', 'img'));
					$print_order = $order;
					$ordergoods = pdo_fetchall('select * from ' . tablename('sz_yi_order_goods') . ' where uniacid=' . $_W['uniacid'] . ' and orderid=' . $orderid);

					foreach ($ordergoods as $key => $value) {
						$ordergoods[$key]['price'] = pdo_fetchcolumn('select marketprice from ' . tablename('sz_yi_goods') . ' where uniacid=' . $_W['uniacid'] . ' and id=' . $value['goodsid']);
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
						if (!empty($print_detail) && ($print_detail['status'] == '1')) {
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

				if (0 < $deductcredit) {
					$shop = m('common')->getSysset('shop');
					m('member')->setCredit($openid, 'credit1', 0 - $deductcredit, array('0', $shop['name'] . '购物积分抵扣 消费积分: ' . $deductcredit . ' 抵扣金额: ' . $deductmoney . ' 订单号: ' . $ordersn));
				}

				if (0 < $deductyunbi) {
					$shop = m('common')->getSysset('shop');
					p('yunbi')->setVirtualCurrency($openid, 0 - $deductyunbi);
					$data_log = array('id' => $member['id'], 'openid' => $openid, 'credittype' => 'virtual_currency', 'money' => $deductyunbi, 'remark' => '购物' . $yunbiset['yunbi_title'] . '抵扣 消费' . $yunbiset['yunbi_title'] . ': ' . $deductyunbi . ' 抵扣金额: ' . $deductyunbimoney . ' 订单号: ' . $ordersn);
					p('yunbi')->addYunbiLog($uniacid, $data_log, '3');
				}

				if (p('channel') && !empty($ischannelpick)) {
					p('channel')->deductChannelStock($orderid);
				}
				else if (empty($virtualid)) {
					m('order')->setStocksANDCredits($orderid, 0);
				}
				else {
					if (isset($allgoods[0])) {
						$vgoods = $allgoods[0];
						pdo_update('sz_yi_goods', array('sales' => $vgoods['sales'] + $vgoods['total']), array('id' => $vgoods['goodsid']));
					}
				}

				$plugincoupon = p('coupon');

				if ($plugincoupon) {
					$plugincoupon->useConsumeCoupon($orderid);
				}

				m('notice')->sendOrderMessage($orderid);

				if (p('channel')) {
					if (empty($ischannelpay)) {
						$pluginc = p('commission');

						if ($pluginc) {
							$pluginc->checkOrderConfirm($orderid);
						}
					}
				}
				else {
					$pluginc = p('commission');

					if ($pluginc) {
						$pluginc->checkOrderConfirm($orderid);
					}
				}
			}




			//点击确认订单后返回的json数据
			show_json(1, array('orderid' => $orderid, 'ischannelpay' => $ischannelpay, 'ischannelpick' => $ischannelpick));
			
			
			
			
		}
		else {
			if ($operation == 'date') {
				global $_GPC;
				global $_W;
				$id = $_GPC['id'];
				if ($search_array && !empty($search_array['bdate']) && !empty($search_array['day'])) {
					$bdate = $search_array['bdate'];
					$day = $search_array['day'];
				}
				else {
					$bdate = date('Y-m-d');
					$day = 1;
				}

				load()->func('tpl');
				include $this->template('order/date');
			}
		}
	}
}

if (p('hotel') && ($goods_data['type'] == '99')) {
	if (empty($_SESSION['data'])) {
		$btime = strtotime(date('Y-m-d'));
		$day = 1;
		$etime = $btime + ($day * 86400);
		$arr['btime'] = $btime;
		$arr['etime'] = $etime;
		$arr['bdate'] = date('Y-m-d');
		$arr['edate'] = date('Y-m-d', $etime);
		$arr['day'] = $day;
		$_SESSION['data'] = $arr;
	}

	include $this->template('order/confirm_hotel');
	return 1;
}
include $this->template('order/confirm');

?>
