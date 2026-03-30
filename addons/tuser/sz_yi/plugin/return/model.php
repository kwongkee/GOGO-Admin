<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

if (!class_exists('ReturnModel')) {
	class ReturnModel extends PluginModel
	{
		public function getSet()
		{
			$set = parent::getSet();
			return $set;
		}

		public function setGoodsQueue($orderid, $set = array(), $uniacid = '')
		{
			$order_goods = pdo_fetchall('SELECT og.orderid,og.goodsid,og.total,og.price,g.isreturnqueue,o.openid,m.id as mid FROM ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_member') . ' m  on o.openid = m.openid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid = o.id  left join ' . tablename('sz_yi_goods') . ' g on g.id = og.goodsid WHERE o.id = :orderid and o.uniacid = :uniacid and m.uniacid = :uniacid', array(':orderid' => $orderid, ':uniacid' => $uniacid));

			foreach ($order_goods as $good) {
				if ($good['isreturnqueue'] == 1) {
					$_obf_DTcEOSwzJz4sCBlcND44OwsiPisiMBE_ = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order_goods_queue') . ' where uniacid = ' . $uniacid . ' and goodsid = ' . $good['goodsid'] . ' order by queue desc limit 1');
					$queuemessages = '';
					$i = 1;

					while ($i <= $good['total']) {
						$_obf_DTEjEB0GPTE9FDYaNxAnHhcELzMHAwE_ = $_obf_DTcEOSwzJz4sCBlcND44OwsiPisiMBE_['queue'] + $i;
						$queuemessages .= $_obf_DTEjEB0GPTE9FDYaNxAnHhcELzMHAwE_ . '、';
						$data = array('uniacid' => $uniacid, 'openid' => $good['openid'], 'goodsid' => $good['goodsid'], 'orderid' => $good['orderid'], 'price' => $good['price'] / $good['total'], 'queue' => $_obf_DTEjEB0GPTE9FDYaNxAnHhcELzMHAwE_, 'create_time' => time());
						pdo_insert('sz_yi_order_goods_queue', $data);
						$queueid = pdo_insertid();
						$_obf_DVs9XAMaOSsSAhkFBjcHORwYMQExKBE_ = pdo_fetchcolumn('SELECT returnid FROM ' . tablename('sz_yi_order_goods_queue') . ' where uniacid = ' . $uniacid . ' and goodsid = ' . $good['goodsid'] . ' order by returnid desc limit 1');
						$return_queue = 0;

						if (!empty($_obf_DVs9XAMaOSsSAhkFBjcHORwYMQExKBE_)) {
							$return_queue = pdo_fetchcolumn('SELECT queue FROM ' . tablename('sz_yi_order_goods_queue') . ' where uniacid = ' . $uniacid . ' and goodsid = ' . $good['goodsid'] . ' and id = ' . $_obf_DVs9XAMaOSsSAhkFBjcHORwYMQExKBE_);
						}

						if ($set['queue'] <= $_obf_DTEjEB0GPTE9FDYaNxAnHhcELzMHAwE_ - $return_queue) {
							$queue = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order_goods_queue') . ' where uniacid = ' . $uniacid . ' and goodsid = ' . $good['goodsid'] . ' and status = 0 order by queue asc limit 1');
							pdo_update('sz_yi_order_goods_queue', array('returnid' => $queueid, 'status' => '1'), array('id' => $queue['id'], 'uniacid' => $uniacid));
							$this->setReturnCredit($queue['openid'], 'credit2', $queue['price'], '4');
							$_obf_DSg4MRBbFT0BPj8sPxUlJSM0ESMiXAE_ = $set['queue_price'];
							$_obf_DSg4MRBbFT0BPj8sPxUlJSM0ESMiXAE_ = str_replace('[返现金额]', $queue['price'], $_obf_DSg4MRBbFT0BPj8sPxUlJSM0ESMiXAE_);
							$messages = array(
								'keyword1' => array('value' => $set['queue_title'] ? $set['queue_title'] : '排列返现通知', 'color' => '#73a68d'),
								'keyword2' => array('value' => $_obf_DSg4MRBbFT0BPj8sPxUlJSM0ESMiXAE_ ? $_obf_DSg4MRBbFT0BPj8sPxUlJSM0ESMiXAE_ : '本次返现金额' . $queue['price'] . '元！', 'color' => '#73a68d')
								);
							$templateid = $set['templateid'];

							if (!empty($templateid)) {
								m('message')->sendTplNotice($queue['openid'], $templateid, $messages);
							}
							else {
								m('message')->sendCustomNotice($queue['openid'], $messages);
							}
						}

						++$i;
					}

					$_obf_DTwyGR8COBwREjwxJEA8AjIsPRolEjI_ = $set['queuemessages'];
					$_obf_DTwyGR8COBwREjwxJEA8AjIsPRolEjI_ = str_replace('[排列序号]', $queuemessages, $_obf_DTwyGR8COBwREjwxJEA8AjIsPRolEjI_);
					$_obf_DQYaFTEQBQQiBg0yGQMNBhwtLyMPWyI_ = array(
						'keyword1' => array('value' => $set['add_queue_title'] ? $set['add_queue_title'] : '加入排列通知', 'color' => '#73a68d'),
						'keyword2' => array('value' => $_obf_DTwyGR8COBwREjwxJEA8AjIsPRolEjI_ ? $_obf_DTwyGR8COBwREjwxJEA8AjIsPRolEjI_ : '您已加入排列，排列号为' . $queuemessages . '号！', 'color' => '#73a68d')
						);
					$templateid = $set['templateid'];

					if (!empty($templateid)) {
						m('message')->sendTplNotice($good['openid'], $templateid, $_obf_DQYaFTEQBQQiBg0yGQMNBhwtLyMPWyI_);
					}
					else {
						m('message')->sendCustomNotice($good['openid'], $_obf_DQYaFTEQBQQiBg0yGQMNBhwtLyMPWyI_);
					}
				}
			}
		}

		public function setMembeerLevel($orderid, $set = array(), $uniacid = '')
		{
			$order_goods = pdo_fetchall('SELECT og.price,og.total,g.isreturn,g.returns,g.returns2,g.returntype,o.openid,m.id as mid ,m.level, m.agentlevel FROM ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_member') . ' m  on o.openid = m.openid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid = o.id  left join ' . tablename('sz_yi_goods') . ' g on g.id = og.goodsid WHERE o.id = :orderid and o.uniacid = :uniacid and m.uniacid = :uniacid', array(':orderid' => $orderid, ':uniacid' => $uniacid));

			foreach ($order_goods as $key => $value) {
				if ($value['returntype'] == 1) {
					$discounts = json_decode($value['returns'], true);
					$level = $value['level'];
				}
				else {
					$discounts = json_decode($value['returns2'], true);
					$level = $value['agentlevel'];
				}

				if ($level == '0') {
					$money += ($discounts['default'] ? $discounts['default'] * $value['total'] : '0');
				}
				else {
					$money += ($discounts['level' . $level] ? $discounts['level' . $level] * $value['total'] : '0');
				}
			}

			if (0 < $money) {
				$this->setReturnCredit($order_goods[0]['openid'], 'credit2', $money, '1');
				$_obf_DTELN1sdBTQNFjA3ATQ3Ph0TERMGCxE_ = $set['member_price'];
				$_obf_DTELN1sdBTQNFjA3ATQ3Ph0TERMGCxE_ = str_replace('[排列序号]', $money, $_obf_DTELN1sdBTQNFjA3ATQ3Ph0TERMGCxE_);
				$_obf_DTELN1sdBTQNFjA3ATQ3Ph0TERMGCxE_ = str_replace('[订单ID]', $orderid, $_obf_DTELN1sdBTQNFjA3ATQ3Ph0TERMGCxE_);
				$msg = array(
					'keyword1' => array('value' => $set['member_title'] ? $set['member_title'] : '购物返现通知', 'color' => '#73a68d'),
					'keyword2' => array('value' => $_obf_DTELN1sdBTQNFjA3ATQ3Ph0TERMGCxE_ ? $_obf_DTELN1sdBTQNFjA3ATQ3Ph0TERMGCxE_ : '[返现金额]' . $money . '元,已存到您的余额', 'color' => '#73a68d')
					);
				$templateid = $set['templateid'];

				if (!empty($templateid)) {
					m('message')->sendTplNotice($order_goods[0]['openid'], $templateid, $msg);
					return NULL;
				}

				m('message')->sendCustomNotice($order_goods[0]['openid'], $msg);
			}
		}

		public function cumulative_order_amount($orderid)
		{
			global $_W;
			global $_GPC;
			$set = $this->getSet();
			$order = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_order') . ' WHERE id=:id and uniacid=:uniacid', array(':id' => $orderid, ':uniacid' => $_W['uniacid']));

			if ($set['islevelreturn']) {
				$this->setMembeerLevel($orderid, $set, $_W['uniacid']);
			}

			if ($set['isqueue']) {
				$this->setGoodsQueue($orderid, $set, $_W['uniacid']);
			}

			if ($set['isreturn'] == 1) {
				if (empty($orderid)) {
					return false;
				}

				if (empty($order['cashier'])) {
					$order_goods = pdo_fetchall('SELECT og.price,g.isreturn,o.openid,m.id as mid FROM ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_member') . ' m  on o.openid = m.openid left join ' . tablename('sz_yi_order_goods') . ' og on og.orderid = o.id  left join ' . tablename('sz_yi_goods') . ' g on g.id = og.goodsid WHERE o.id = :orderid and o.uniacid = :uniacid and m.uniacid = :uniacid', array(':orderid' => $orderid, ':uniacid' => $_W['uniacid']));
					$order_price = 0;
					$_obf_DQENGyMaAR8dERkRAwEEEgojPg0EIiI_ = false;

					foreach ($order_goods as $good) {
						if ($good['isreturn'] == 1) {
							$order_price += $good['price'];
							$_obf_DQENGyMaAR8dERkRAwEEEgojPg0EIiI_ = true;
						}
					}
				}
				else {
					$order_price = $order['price'];
					$order_goods = array();
					$m = m('member')->getMember($order['openid']);
					$order_goods[0]['openid'] = $order['openid'];
					$order_goods[0]['mid'] = $m['id'];
					$_obf_DQENGyMaAR8dERkRAwEEEgojPg0EIiI_ = true;
				}

				if (!$_obf_DQENGyMaAR8dERkRAwEEEgojPg0EIiI_ && empty($order['cashier'])) {
					return false;
				}

				if (empty($order_goods)) {
					return false;
				}

				if ($set['returnrule'] == 1) {
					$this->setOrderRule($order_goods, $order_price, $set, $_W['uniacid']);
					return NULL;
				}

				if ($set['returnrule'] == 2) {
					if ($set['iscumulative'] && (0 < $order['credit1'])) {
						$order_price = $order_price - $order['credit1'];
					}

					$this->setOrderMoneyRule($order_goods, $order_price, $set, $_W['uniacid']);
				}
			}
		}

		public function setOrderRule($order_goods, $order_price, $set = array(), $uniacid = '')
		{
			$data = array('mid' => $order_goods[0]['mid'], 'uniacid' => $uniacid, 'money' => $order_price, 'returnrule' => $set['returnrule'], 'create_time' => time());
			pdo_insert('sz_yi_return', $data);
			$_obf_DQ4GFy8LOT4rBgEbPRUsLTAmIhUCDQE_ = $set['order_price'];
			$_obf_DQ4GFy8LOT4rBgEbPRUsLTAmIhUCDQE_ = str_replace('[订单金额]', $order_price, $_obf_DQ4GFy8LOT4rBgEbPRUsLTAmIhUCDQE_);
			$msg = array(
				'keyword1' => array('value' => $set['add_single_title'] ? $set['add_single_title'] : '订单全返通知', 'color' => '#73a68d'),
				'keyword2' => array('value' => $_obf_DQ4GFy8LOT4rBgEbPRUsLTAmIhUCDQE_ ? $_obf_DQ4GFy8LOT4rBgEbPRUsLTAmIhUCDQE_ : '[订单返现金额]' . $order_price, 'color' => '#73a68d')
				);
			$templateid = $set['templateid'];

			if (!empty($templateid)) {
				m('message')->sendTplNotice($order_goods[0]['openid'], $templateid, $msg);
				return NULL;
			}

			m('message')->sendCustomNotice($order_goods[0]['openid'], $msg);
		}

		public function setOrderMoneyRule($order_goods, $order_price, $set = array(), $uniacid = '')
		{
			$return = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_return_money') . ' WHERE mid = :mid and uniacid = :uniacid', array(':mid' => $order_goods[0]['mid'], ':uniacid' => $uniacid));

			if (!empty($return)) {
				$returnid = $return['id'];
				$data = array('money' => $return['money'] + $order_price);
				pdo_update('sz_yi_return_money', $data, array('id' => $returnid));
			}
			else {
				$data = array('mid' => $order_goods[0]['mid'], 'uniacid' => $uniacid, 'money' => $order_price);
				pdo_insert('sz_yi_return_money', $data);
				$returnid = pdo_insertid();
			}

			$return_money = pdo_fetchcolumn('SELECT money FROM ' . tablename('sz_yi_return_money') . ' WHERE id = :id and uniacid = :uniacid', array(':id' => $returnid, ':uniacid' => $uniacid));
			$this->setmoney($set['orderprice'], $set, $uniacid);

			if ($set['orderprice'] <= $return_money) {
				$_obf_DQcEJhUjIQs_BTQ_LhIkHh4FJD4JHRE_ = $set['total_reach'];
				$_obf_DQcEJhUjIQs_BTQ_LhIkHh4FJD4JHRE_ = str_replace('[标准金额]', $set['orderprice'], $_obf_DQcEJhUjIQs_BTQ_LhIkHh4FJD4JHRE_);
				$text = ($_obf_DQcEJhUjIQs_BTQ_LhIkHh4FJD4JHRE_ ? $_obf_DQcEJhUjIQs_BTQ_LhIkHh4FJD4JHRE_ : '您的订单累计金额已经超过' . $set['orderprice'] . '元，每' . $set['orderprice'] . '元可以加入全返机制，等待全返。');
			}
			else {
				$_obf_DQYCMS4RIQY8JgEvQDsBNAwWMkA_KQE_ = $set['total_unreached'];
				$_obf_DQYCMS4RIQY8JgEvQDsBNAwWMkA_KQE_ = str_replace('[缺少金额]', $set['orderprice'] - $return_money, $_obf_DQYCMS4RIQY8JgEvQDsBNAwWMkA_KQE_);
				$_obf_DQYCMS4RIQY8JgEvQDsBNAwWMkA_KQE_ = str_replace('[标准金额]', $set['orderprice'], $_obf_DQYCMS4RIQY8JgEvQDsBNAwWMkA_KQE_);
				$text = ($_obf_DQYCMS4RIQY8JgEvQDsBNAwWMkA_KQE_ ? $_obf_DQYCMS4RIQY8JgEvQDsBNAwWMkA_KQE_ : '您的订单累计金额还差' . ($set['orderprice'] - $return_money) . '元达到' . $set['orderprice'] . '元，订单累计金额每达到' . $set['orderprice'] . '元就可以加入全返机制，等待全返。继续加油！');
			}

			$_obf_DScRNigDNjgRHR8dHAsEEzczJA0PGBE_ = $set['total_price'];
			$_obf_DScRNigDNjgRHR8dHAsEEzczJA0PGBE_ = str_replace('[累计金额]', $return_money, $_obf_DScRNigDNjgRHR8dHAsEEzczJA0PGBE_);
			$msg = array(
				'keyword1' => array('value' => $set['total_title'] ? $set['total_title'] : '订单金额累计通知', 'color' => '#73a68d'),
				'keyword2' => array('value' => $_obf_DScRNigDNjgRHR8dHAsEEzczJA0PGBE_ ? $_obf_DScRNigDNjgRHR8dHAsEEzczJA0PGBE_ : '[订单累计金额]' . $return_money, 'color' => '#73a68d'),
				'remark'   => array('value' => $text)
				);
			$templateid = $set['templateid'];

			if (!empty($templateid)) {
				m('message')->sendTplNotice($order_goods[0]['openid'], $templateid, $msg);
				return NULL;
			}

			m('message')->sendCustomNotice($order_goods[0]['openid'], $msg);
		}

		public function setOrderReturn($set = array(), $uniacid = '')
		{
			$tmpdir = IA_ROOT . '/addons/sz_yi/tmp/reutrn';
			$return_log = $tmpdir . '/return_jog.txt';
			$log_content[] = date('Y-m-d H:i:s') . '公众号ID：' . $uniacid . " 单笔订单返现开始==============\r\n";
			$_obf_DTkeHRIfLxkeFDA7FBgfBhkiWwNAMAE_ = pdo_fetchall('SELECT r.mid, m.level, m.agentlevel, m.openid FROM ' . tablename('sz_yi_return') . " r \n\t\t\t\tleft join " . tablename('sz_yi_member') . " m on (r.mid = m.id) \n\t\t\t WHERE r.uniacid = '" . $uniacid . '\' and r.returnrule = \'' . $set['returnrule'] . '\' and r.delete = \'0\'  group by r.mid');
			$level = array();

			if ($set['islevels'] == 1) {
				if ($set['islevel'] == 1) {
					$level = json_decode($set['member'], true);
				}
				else {
					if ($set['islevel'] == 2) {
						$level = json_decode($set['commission'], true);
					}
				}
			}

			$log_content[] = '单笔返现队列人：';
			$log_content[] = var_export($_obf_DTkeHRIfLxkeFDA7FBgfBhkiWwNAMAE_, true);
			$log_content[] = "\r\n";
			$current_time = time();

			foreach ($_obf_DTkeHRIfLxkeFDA7FBgfBhkiWwNAMAE_ as $key => $value) {
				$percentage = $set['percentage'];

				if ($set['islevels'] == 1) {
					if ($set['islevel'] == 1) {
						$percentage = ($level['level' . $value['level']] ? $level['level' . $value['level']] : $set['percentage']);
					}
					else {
						if ($set['islevel'] == 2) {
							$percentage = ($level['level' . $value['agentlevel']] ? $level['level' . $value['agentlevel']] : $set['percentage']);
						}
					}
				}

				if ($set['degression'] == 1) {
					$log_content[] = '递减返现';
					$log_content[] = "\r\n";
					pdo_query('update  ' . tablename('sz_yi_return') . ' set last_money = money - return_money, status=1, return_money = money, updatetime = \'' . $current_time . '\' WHERE uniacid = \'' . $uniacid . '\' and status=0 and `delete` = \'0\' and money - return_money <= 0.5  and returnrule = \'' . $set['returnrule'] . '\' and mid = \'' . $value['mid'] . '\' ');
					pdo_query('update  ' . tablename('sz_yi_return') . ' set return_money = return_money + (money - return_money) * ' . $percentage . ' / 100,last_money = (money - return_money) * ' . $percentage . ' / 100,updatetime = \'' . $current_time . '\' WHERE uniacid = \'' . $uniacid . '\' and status=0 and `delete` = \'0\' and money - return_money > 0.5 and returnrule = \'' . $set['returnrule'] . '\' and mid = \'' . $value['mid'] . '\' ');
				}
				else {
					$log_content[] = '单笔返现';
					$log_content[] = "\r\n";
					pdo_query('update  ' . tablename('sz_yi_return') . ' set last_money = money - return_money, status=1, return_money = money, updatetime = \'' . $current_time . '\' WHERE uniacid = \'' . $uniacid . '\' and status=0 and `delete` = \'0\' and (money - `return_money`) <= money * ' . $percentage . ' / 100 and returnrule = \'' . $set['returnrule'] . '\' and mid = \'' . $value['mid'] . '\' ');
					pdo_query('update  ' . tablename('sz_yi_return') . ' set return_money = return_money + money * ' . $percentage . ' / 100,last_money = money * ' . $percentage . ' / 100,updatetime = \'' . $current_time . '\' WHERE uniacid = \'' . $uniacid . '\' and status=0 and `delete` = \'0\' and (money - return_money) > money * ' . $percentage . ' / 100 and returnrule = \'' . $set['returnrule'] . '\' and mid = \'' . $value['mid'] . '\' ');
				}
			}

			$_obf_DQ4VNC8aQCUTEww3MB4BCCkWDhE3ECI_ = pdo_fetchall('SELECT sum(r.money) as money, sum(r.return_money) as return_money, sum(r.last_money) as last_money,m.openid,count(r.id) as count  FROM ' . tablename('sz_yi_return') . " r \n\t\t\t\tleft join " . tablename('sz_yi_member') . " m on (r.mid = m.id) \n\t\t\t WHERE r.uniacid = '" . $uniacid . '\' and r.updatetime = \'' . $current_time . '\' and r.returnrule = \'' . $set['returnrule'] . '\' and r.delete = \'0\'  group by r.mid');
			$log_content[] = '单笔返现内容：';
			$log_content[] = var_export($_obf_DQ4VNC8aQCUTEww3MB4BCCkWDhE3ECI_, true);
			$log_content[] = "\r\n";

			foreach ($_obf_DQ4VNC8aQCUTEww3MB4BCCkWDhE3ECI_ as $key => $value) {
				if (0 < $value['last_money']) {
					$_obf_DVspGh8mGBM0LjwQBhUfHy0vPSkxAQE_ = $value['last_money'];
					$_obf_DTcrHR0KAgMrHC0TJBsLJx8CAS8fKgE_ = $value['money'] - $value['return_money'];
					$this->setReturnCredit($value['openid'], 'credit2', $_obf_DVspGh8mGBM0LjwQBhUfHy0vPSkxAQE_, '2');
					$_obf_DTQGGDkcFRUsAR8XEVwYCAMpPlw0AzI_ = $set['single_message'];
					$_obf_DTQGGDkcFRUsAR8XEVwYCAMpPlw0AzI_ = str_replace('[返现金额]', $_obf_DVspGh8mGBM0LjwQBhUfHy0vPSkxAQE_, $_obf_DTQGGDkcFRUsAR8XEVwYCAMpPlw0AzI_);
					$_obf_DTQGGDkcFRUsAR8XEVwYCAMpPlw0AzI_ = str_replace('[剩余返现金额]', $_obf_DTcrHR0KAgMrHC0TJBsLJx8CAS8fKgE_, $_obf_DTQGGDkcFRUsAR8XEVwYCAMpPlw0AzI_);
					$messages = array(
						'keyword1' => array('value' => $set['single_return_title'] ? $set['single_return_title'] : '返现通知', 'color' => '#73a68d'),
						'keyword2' => array('value' => $_obf_DTQGGDkcFRUsAR8XEVwYCAMpPlw0AzI_ ? $_obf_DTQGGDkcFRUsAR8XEVwYCAMpPlw0AzI_ : '本次返现金额' . $_obf_DVspGh8mGBM0LjwQBhUfHy0vPSkxAQE_ . '元', 'color' => '#73a68d')
						);
					$templateid = $set['templateid'];

					if (!empty($templateid)) {
						m('message')->sendTplNotice($value['openid'], $templateid, $messages);
					}
					else {
						m('message')->sendCustomNotice($value['openid'], $messages);
					}
				}
			}

			$log_content[] = date('Y-m-d H:i:s') . '公众号ID：' . $uniacid . " 单笔订单返现完成==============\r\n\r\n\r\n\r\n";
			file_put_contents($return_log, $log_content, FILE_APPEND);
		}

		public function setOrderMoneyReturn($set = array(), $uniacid = '')
		{
			$tmpdir = IA_ROOT . '/addons/sz_yi/tmp/reutrn';
			$return_log = $tmpdir . '/return_jog.txt';
			$log_content[] = date('Y-m-d H:i:s') . '公众号ID：' . $uniacid . " 订单累计返现开始==============\r\n";
			$daytime = strtotime(date('Y-m-d 00:00:00'));
			$stattime = $daytime - 86400;
			$endtime = $daytime - 1;

			if ($set['isprofit']) {
				$sql = 'select o.id, o.price, g.marketprice, g.costprice, og.total from ' . tablename('sz_yi_order') . " o \n\t\t\t\tleft join " . tablename('sz_yi_order_goods') . " og on (o.id = og.orderid) \n\t\t\t\tleft join " . tablename('sz_yi_goods') . " g on (og.goodsid = g.id) \n\t\t\t\tleft join " . tablename('sz_yi_order_refund') . " r on r.orderid=o.id and ifnull(r.status,-1)<>-1 \n\t\t\t\twhere 1 and o.status>=3 and o.uniacid=" . $uniacid . ' and  o.finishtime >=' . $stattime . ' and o.finishtime < ' . $endtime . '  ORDER BY o.finishtime DESC,o.status DESC';
				$dayorder = pdo_fetchall($sql);

				foreach ($dayorder as $key => $value) {
					$ordermoney += $value['price'] - ($value['costprice'] * $value['total']);
				}
			}
			else {
				$sql = 'select sum(o.price) from ' . tablename('sz_yi_order') . ' o left join ' . tablename('sz_yi_order_refund') . ' r on r.orderid=o.id and ifnull(r.status,-1)<>-1 where 1 and o.status>=3 and o.uniacid=' . $uniacid . ' and  o.finishtime >=' . $stattime . ' and o.finishtime < ' . $endtime . '  ORDER BY o.finishtime DESC,o.status DESC';
				$ordermoney = pdo_fetchcolumn($sql);
			}

			$ordermoney = floatval($ordermoney);
			$log_content[] = '昨日成交金额：' . $ordermoney . "\r\n";
			$_obf_DTwVCRQEDxsaMQ8PLBUVBysWIQQXKCI_ = ($ordermoney * $set['percentage']) / 100;
			$log_content[] = '可返现金额：' . $_obf_DTwVCRQEDxsaMQ8PLBUVBysWIQQXKCI_ . "\r\n";
			$_obf_DSYkNAoUKhAiAxgYLzMGPhQfHlwwEzI_ = pdo_fetchcolumn('select count(1) from ' . tablename('sz_yi_return') . ' where uniacid = \'' . $uniacid . '\' and status = 0 and `delete` = \'0\' and returnrule = \'' . $set['returnrule'] . '\'');
			$log_content[] = '可返现队列数：' . $_obf_DSYkNAoUKhAiAxgYLzMGPhQfHlwwEzI_ . "\r\n";
			if ((0 < $_obf_DTwVCRQEDxsaMQ8PLBUVBysWIQQXKCI_) && $_obf_DSYkNAoUKhAiAxgYLzMGPhQfHlwwEzI_) {
				$_obf_DTMDDT0ZCRgeFCgyOxpbFx8QIx8YLAE_ = $_obf_DTwVCRQEDxsaMQ8PLBUVBysWIQQXKCI_ / $_obf_DSYkNAoUKhAiAxgYLzMGPhQfHlwwEzI_;
				$_obf_DTMDDT0ZCRgeFCgyOxpbFx8QIx8YLAE_ = sprintf('%.2f', $_obf_DTMDDT0ZCRgeFCgyOxpbFx8QIx8YLAE_);
				$log_content[] = '每个队列返现金额：' . $_obf_DTMDDT0ZCRgeFCgyOxpbFx8QIx8YLAE_ . "\r\n";
				$current_time = time();
				pdo_query('update  ' . tablename('sz_yi_return') . ' set last_money = money - return_money, status=1, return_money = money, updatetime = \'' . $current_time . '\' WHERE uniacid = \'' . $uniacid . '\' and status=0 and `delete` = \'0\' and (money - `return_money`) <= \'' . $_obf_DTMDDT0ZCRgeFCgyOxpbFx8QIx8YLAE_ . '\' and returnrule = \'' . $set['returnrule'] . '\' ');
				pdo_query('update  ' . tablename('sz_yi_return') . ' set return_money = return_money + \'' . $_obf_DTMDDT0ZCRgeFCgyOxpbFx8QIx8YLAE_ . '\',last_money = \'' . $_obf_DTMDDT0ZCRgeFCgyOxpbFx8QIx8YLAE_ . '\',updatetime = \'' . $current_time . '\' WHERE uniacid = \'' . $uniacid . '\' and status=0 and `delete` = \'0\' and (money - `return_money`) > \'' . $_obf_DTMDDT0ZCRgeFCgyOxpbFx8QIx8YLAE_ . '\' and returnrule = \'' . $set['returnrule'] . '\' ');
				$_obf_DQ4VNC8aQCUTEww3MB4BCCkWDhE3ECI_ = pdo_fetchall('SELECT sum(r.money) as money, sum(r.return_money) as return_money, sum(r.last_money) as last_money,m.openid,count(r.id) as count  FROM ' . tablename('sz_yi_return') . " r \n\t\t\t\t\tleft join " . tablename('sz_yi_member') . " m on (r.mid = m.id) \n\t\t\t\t WHERE r.uniacid = '" . $uniacid . '\' and r.updatetime = \'' . $current_time . '\' and r.returnrule = \'' . $set['returnrule'] . '\' and r.delete = \'0\'  group by r.mid');
				$log_content[] = 'SELECT sum(r.money) as money, sum(r.return_money) as return_money, sum(r.last_money) as last_money,m.openid,count(r.id) as count  FROM ' . tablename('sz_yi_return') . " r \n\t\t\t\t\tleft join " . tablename('sz_yi_member') . " m on (r.mid = m.id) \n\t\t\t\t WHERE r.uniacid = '" . $uniacid . '\' and r.updatetime = \'' . $current_time . '\' and r.returnrule = \'' . $set['returnrule'] . "' and r.delete = '0'  group by r.mid \r\n";
				$log_content[] = '订单累计金额返现内容：' . var_export($_obf_DQ4VNC8aQCUTEww3MB4BCCkWDhE3ECI_, true) . "\r\n";

				foreach ($_obf_DQ4VNC8aQCUTEww3MB4BCCkWDhE3ECI_ as $key => $value) {
					if (0 < $value['last_money']) {
						$_obf_DVspGh8mGBM0LjwQBhUfHy0vPSkxAQE_ = $value['last_money'];
						$_obf_DTcrHR0KAgMrHC0TJBsLJx8CAS8fKgE_ = $value['money'] - $value['return_money'];
						$this->setReturnCredit($value['openid'], 'credit2', $_obf_DVspGh8mGBM0LjwQBhUfHy0vPSkxAQE_, '3');
						$_obf_DS8OExYhGDQNQFwvCCQBBjYPOwozFzI_ = $set['total_messsage'];
						$_obf_DS8OExYhGDQNQFwvCCQBBjYPOwozFzI_ = str_replace('[返现金额]', $_obf_DVspGh8mGBM0LjwQBhUfHy0vPSkxAQE_, $_obf_DS8OExYhGDQNQFwvCCQBBjYPOwozFzI_);
						$_obf_DS8OExYhGDQNQFwvCCQBBjYPOwozFzI_ = str_replace('[剩余返现金额]', $_obf_DTcrHR0KAgMrHC0TJBsLJx8CAS8fKgE_, $_obf_DS8OExYhGDQNQFwvCCQBBjYPOwozFzI_);
						$messages = array(
							'keyword1' => array('value' => $set['total_return_title'] ? $set['total_return_title'] : '返现通知', 'color' => '#73a68d'),
							'keyword2' => array('value' => $_obf_DS8OExYhGDQNQFwvCCQBBjYPOwozFzI_ ? $_obf_DS8OExYhGDQNQFwvCCQBBjYPOwozFzI_ : '本次返现金额' . $_obf_DVspGh8mGBM0LjwQBhUfHy0vPSkxAQE_ . '元', 'color' => '#73a68d')
							);
						$templateid = $set['templateid'];

						if (!empty($templateid)) {
							m('message')->sendTplNotice($value['openid'], $templateid, $messages);
						}
						else {
							m('message')->sendCustomNotice($value['openid'], $messages);
						}
					}
				}
			}

			$log_content[] = date('Y-m-d H:i:s') . '公众号ID：' . $uniacid . " 订单累计返现完成==============\r\n\r\n";
			file_put_contents($return_log, $log_content, FILE_APPEND);
		}

		public function setmoney($orderprice, $set = array(), $uniacid = '')
		{
			$data_money = pdo_fetchall('select * from ' . tablename('sz_yi_return_money') . ' where uniacid = \'' . $uniacid . '\' and money >= \'' . $orderprice . '\' ');

			if ($data_money) {
				foreach ($data_money as $key => $value) {
					if ($orderprice <= $value['money']) {
						$data = array('uniacid' => $value['uniacid'], 'mid' => $value['mid'], 'money' => $orderprice, 'returnrule' => $set['returnrule'], 'create_time' => time());
						pdo_insert('sz_yi_return', $data);
						pdo_update('sz_yi_return_money', array('money' => $value['money'] - $orderprice), array('id' => $value['id'], 'uniacid' => $uniacid));
					}
				}

				$this->setmoney($orderprice, $set, $uniacid);
			}
		}

		public function setReturnCredit($openid = '', $credittype = 'credit1', $credits = 0, $returntype = 1, $log = array())
		{
			global $_W;
			load()->model('mc');
			$member = m('member')->getMember($openid);
			$uid = mc_openid2uid($openid);

			if (!empty($uid)) {
				$value = pdo_fetchcolumn('SELECT ' . $credittype . ' FROM ' . tablename('mc_members') . ' WHERE `uid` = :uid', array(':uid' => $uid));
				$newcredit = $credits + $value;

				if ($newcredit <= 0) {
					$newcredit = 0;
				}

				pdo_update('mc_members', array($credittype => $newcredit), array('uid' => $uid));
				if (empty($log) || !is_array($log)) {
					$log = array($uid, '未记录');
				}

				$data = array('uid' => $uid, 'credittype' => $credittype, 'uniacid' => $_W['uniacid'], 'num' => $credits, 'createtime' => TIMESTAMP, 'operator' => intval($log[0]), 'remark' => $log[1]);
				pdo_insert('mc_credits_record', $data);
			}
			else {
				$value = pdo_fetchcolumn('SELECT ' . $credittype . ' FROM ' . tablename('sz_yi_member') . ' WHERE  uniacid=:uniacid and openid=:openid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
				$newcredit = $credits + $value;

				if ($newcredit <= 0) {
					$newcredit = 0;
				}

				pdo_update('sz_yi_member', array($credittype => $newcredit), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
			}

			$data_log = array('uniacid' => $_W['uniacid'], 'mid' => $member['id'], 'openid' => $openid, 'money' => $credits, 'status' => 1, 'returntype' => $returntype, 'create_time' => time());
			pdo_insert('sz_yi_return_log', $data_log);
		}

		public function autoexec($uniacid)
		{
			global $_W;
			global $_GPC;
			$_W['uniacid'] = $uniacid;
			set_time_limit(0);
			load()->func('file');
			$tmpdir = IA_ROOT . '/addons/sz_yi/tmp/reutrn';
			$return_log = $tmpdir . '/return_log.txt';
			$log_content = array();
			$log_content[] = date('Y-m-d H:i:s') . "返现开始========================\r\n";
			$log_content[] = '当前域名：' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\r\n";
			$tmpdirs = IA_ROOT . '/addons/sz_yi/tmp/reutrn/' . date('Ymd');

			if (!is_dir($tmpdirs)) {
				mkdirs($tmpdirs);
			}

			$validation = $tmpdirs . '/' . date('Ymd') . $_W['uniacid'] . '.txt';

			if (!file_exists($validation)) {
				$set = m('plugin')->getpluginSet('return', $_W['uniacid']);

				if (!empty($set)) {
					$isexecute = false;

					if ($set['returnlaw'] == 1) {
						$log_content[] = '返现规律：按天返现，每天：' . $set['returntime'] . "返现\r\n";

						if (date('H') == $set['returntime']) {
							if (!isset($set['current_d']) || ($set['current_d'] != date('d'))) {
								$set['current_d'] = date('d');
								$this->updateSet($set);
								$isexecute = true;
							}
						}
					}
					else if ($set['returnlaw'] == 2) {
						$log_content[] = "返现规律：按月返现！\r\n";
						if (!isset($set['current_m']) || ($set['current_m'] != date('m'))) {
							$set['current_m'] = date('m');
							$this->updateSet($set);
							$isexecute = true;
						}
					}
					else {
						if ($set['returnlaw'] == 3) {
							$log_content[] = "返现规律：按周返现！\r\n";

							if (date('w') == $set['returntimezhou']) {
								if (!isset($set['current_d']) || ($set['current_d'] != date('d'))) {
									$set['current_d'] = date('d');
									$this->updateSet($set);
									$isexecute = true;
								}
							}
						}
					}

					if (($set['isreturn'] || $set['isqueue']) && $isexecute) {
						touch($validation);
						$log_content[] = "当前可以返现\r\n";

						if ($set['returnrule'] == 1) {
							$log_content[] = "返现类型：单笔订单返现\r\n";
							$this->setOrderReturn($set, $_W['uniacid']);
						}
						else {
							$log_content[] = "返现类型：订单累计金额返现\r\n";
							$this->setOrderMoneyReturn($set, $_W['uniacid']);
						}
					}
					else {
						$log_content[] = "当前不可返现\r\n";
					}
				}

				$log_content[] = '公众号ID：' . $_W['uniacid'] . "结束-----------\r\n\r\n";
			}
			else {
				$log_content[] = '公众号ID：' . $_W['uniacid'] . date('Y-m-d') . "已返现\r\n\r\n";
			}

			$log_content[] = date('Y-m-d H:i:s') . "返现任务执行完成===================\r\n \r\n \r\n";
			file_put_contents($return_log, $log_content, FILE_APPEND);
		}
	}
}

?>
