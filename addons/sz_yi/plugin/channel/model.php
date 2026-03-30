<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

if (!class_exists('ChannelModel')) {
	class ChannelModel extends PluginModel
	{
		public function getSet()
		{
			$set = parent::getSet();
			return $set;
		}

		public function getMyOptionStock($openid, $goodsid, $optionid)
		{
			global $_W;
			$cond = '';

			if (!empty($optionid)) {
				$cond = ' AND optionid=' . $optionid;
			}

			$stock = pdo_fetchcolumn('SELECT stock_total FROM ' . tablename('sz_yi_channel_stock') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND openid=\'' . $openid . '\' AND goodsid=' . $goodsid . $cond);
			return $stock;
		}

		public function getMyOptionStockLog($openid, $goodsid, $optionid)
		{
			global $_W;
			$cond = '';

			if (!empty($optionid)) {
				$cond = ' AND sl.optionid=' . $optionid;
			}

			$stock_log = pdo_fetchall('SELECT g.thumb,g.title,sl.*,m.nickname,m.avatar FROM ' . tablename('sz_yi_channel_stock_log') . 'sl left join  ' . tablename('sz_yi_member') . ' m ON sl.mid = m.id LEFT JOIN ' . tablename('sz_yi_goods') . ' g on sl.goodsid = g.id WHERE sl.uniacid=' . $_W['uniacid'] . ' AND sl.openid=\'' . $openid . '\' AND sl.goodsid=' . $goodsid . $cond);
			return $stock_log;
		}

		public function getSuperiorStock($openid, $goodsid, $optionid)
		{
			global $_W;
			$_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_ = $this->getAgentOpenids($openid);

			if (!empty($_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_)) {
				$stock = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_stock') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND openid in (' . $_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_ . ') AND goodsid=' . $goodsid . ' AND optionid=' . $optionid . '  ORDER BY stock_total DESC');
				return $stock;
			}
		}

		public function getAgentOpenids($openid)
		{
			global $_W;
			$_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_ = array();
			$member = m('member')->getInfo($openid);

			if (empty($member['agentid'])) {
				return $_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_;
			}

			$agent = m('member')->getMember($member['agentid']);
			$_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_[] = '\'' . $agent['openid'] . '\'';

			if (empty($agent['agentid'])) {
				$_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_ = implode(',', $_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_);
				return $_obf_DQtAOAoCKDsNHDgkMSQKLR0wFgUaHTI_;
			}

			$this->getAgentOpenids($agent['openid']);
		}

		public function getInfo($openid, $goodsid = '', $optionid = '', $total = '')
		{
			global $_W;
			$channel_info = array();
			$member = m('member')->getInfo($openid);

			if (empty($member)) {
				return NULL;
			}

			$_obf_DSQIAjAnAhcIAis5XA0WJiYELCsWFQE_ = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_channel_merchant') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND openid=\'' . $openid . '\'');
			$set = $this->getSet();

			if (!empty($_obf_DSQIAjAnAhcIAis5XA0WJiYELCsWFQE_)) {
				$lower_openids = array();

				foreach ($_obf_DSQIAjAnAhcIAis5XA0WJiYELCsWFQE_ as $value) {
					$lower_openids[] = '\'' . $value['lower_openid'] . '\'';
				}

				$lower_openids = implode(',', $lower_openids);
				$lower_order_money = pdo_fetchcolumn('SELECT sum(o.goodsprice) FROM ' . tablename('sz_yi_order') . ' o LEFT JOIN ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id WHERE o.uniacid=:uniacid AND o.status>=3 AND o.iscmas=0 AND og.ischannelpay=1 AND o.openid in (' . $lower_openids . ')', array(':uniacid' => $_W['uniacid']));
				$lower_order_money = number_format(($lower_order_money * $set['setprofitproportion']) / 100, 2);
			}
			else {
				$lower_openids = 0;
				$lower_order_money = 0;
			}

			$channel_info['channel']['lower_openids'] = $lower_openids;
			$channel_info['channel']['lower_order_money'] = $lower_order_money;
			$channel_info['channel']['dispatchprice'] = pdo_fetchcolumn('SELECT ifnull(sum(dispatchprice),0) FROM ' . tablename('sz_yi_order') . ' WHERE uniacid=:uniacid AND status>=3 AND iscmas=0 AND ischannelself=1 AND openid=:openid', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
			$channel_info['channel']['order_total_price'] = number_format(pdo_fetchcolumn('SELECT ifnull(sum(price),0) FROM ' . tablename('sz_yi_order_goods') . ' WHERE uniacid=:uniacid AND channel_id=:channel_id', array(':uniacid' => $_W['uniacid'], ':channel_id' => $member['id'])), 2);
			$channel_info['channel']['ordercount'] = pdo_fetchcolumn('SELECT count(o.id) FROM ' . tablename('sz_yi_order_goods') . ' og left join ' . tablename('sz_yi_order') . ' o on (o.id=og.orderid) WHERE og.channel_id=:channel_id AND o.userdeleted=0 AND o.deleted=0 AND o.uniacid=:uniacid ', array(':channel_id' => $member['id'], ':uniacid' => $_W['uniacid']));
			$channel_info['channel']['commission_total'] = number_format(pdo_fetchcolumn('SELECT sum(apply_money) FROM ' . tablename('sz_yi_channel_apply') . ' WHERE uniacid=:uniacid AND openid=:openid', array(':uniacid' => $_W['uniacid'], ':openid' => $openid)), 2);
			$channel_info['channel']['commission_pay_total'] = number_format(pdo_fetchcolumn('SELECT sum(apply_money) FROM ' . tablename('sz_yi_channel_apply') . ' WHERE uniacid=:uniacid AND openid=:openid AND status = 3', array(':uniacid' => $_W['uniacid'], ':openid' => $openid)), 2);
			$_obf_DTURQBwGDw8KGT0cERADJyQrHjMrGDI_ = pdo_fetchcolumn('SELECT ifnull(sum(ogp.profit),0) FROM ' . tablename('sz_yi_channel_order_goods_profit') . ' ogp LEFT JOIN ' . tablename('sz_yi_order_goods') . ' og ON og.id=ogp.order_goods_id LEFT JOIN ' . tablename('sz_yi_order') . ' o ON (o.id=og.orderid) WHERE o.uniacid=:uniacid AND og.channel_id=:channel_id AND o.status=3 AND og.channel_apply_status=0 AND og.ischannelpay=0 ', array(':uniacid' => $_W['uniacid'], ':channel_id' => $member['id']));
			$_obf_DVsdExwoAi0eLhkzLyoKFj0JKjI4PBE_ = pdo_fetchcolumn('SELECT sum(o.goodsprice) FROM ' . tablename('sz_yi_order_goods') . ' og left join ' . tablename('sz_yi_order') . ' o on (o.id=og.orderid) WHERE o.uniacid=:uniacid AND og.channel_id=:channel_id AND o.status=3 AND og.channel_apply_status=0 AND og.ischannelpay=1 ', array(':uniacid' => $_W['uniacid'], ':channel_id' => $member['id']));
			$channel_info['channel']['commission_ok'] = $_obf_DTURQBwGDw8KGT0cERADJyQrHjMrGDI_ + $_obf_DVsdExwoAi0eLhkzLyoKFj0JKjI4PBE_;
			$channel_info['channel']['mychannels'] = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_member') . ' WHERE uniacid=:uniacid AND channel_level<>0 AND agentid=:agentid', array(':uniacid' => $_W['uniacid'], ':agentid' => $member['id']));
			$level = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid AND id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $member['channel_level']));

			if (!empty($level)) {
				if (!empty($goodsid)) {
					$up_level = $this->getUpChannel($openid, $goodsid, $optionid = '', $total);

					if (!empty($optionid)) {
						$up_level = $this->getUpChannel($openid, $goodsid, $optionid, $total);
					}
				}
				else {
					$up_level = $this->getUpChannel($openid);
				}

				$channel_info['my_level'] = $level;
				$channel_info['up_level'] = $up_level;
				return $channel_info;
			}

			$channel_info['my_level'] = array();
			$up_level = $this->getUpChannel($openid);
			$channel_info['up_level'] = $up_level;
			return $channel_info;
		}

		public function getUpChannel($openid, $goodsid = '', $optionid = '', $total = '')
		{
			global $_W;

			if (empty($total)) {
				$total = 0;
			}

			$member = m('member')->getInfo($openid);

			if (empty($member['channel_level'])) {
				$member['level_num'] = -1;
			}
			else {
				$member['level_num'] = pdo_fetchcolumn('SELECT level_num FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid AND id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $member['channel_level']));
			}

			if (empty($member['agentid'])) {
				return NULL;
			}

			$up_channel = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member') . ' WHERE uniacid=:uniacid AND id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $member['agentid']));

			if (!empty($up_channel['channel_level'])) {
				$up_level = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid AND id=:id', array(':uniacid' => $_W['uniacid'], ':id' => $up_channel['channel_level']));

				if (!empty($goodsid)) {
					$condtion = ' AND goodsid=' . $goodsid;

					if (!empty($optionid)) {
						$condtion = ' AND goodsid=' . $goodsid . ' AND optionid=' . $optionid . ' AND stock_total>=' . $total;
					}

					$_obf_DTIuEDYJAkAVMAQNKj8hDjwlOwo5OSI_ = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_stock') . ' WHERE uniacid=:uniacid AND openid=:openid ' . $condtion . ' AND stock_total>=:stock_total', array(':uniacid' => $_W['uniacid'], ':openid' => $up_channel['openid'], ':stock_total' => $total));
				}
				else {
					$_obf_DTIuEDYJAkAVMAQNKj8hDjwlOwo5OSI_ = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_stock') . ' WHERE uniacid=:uniacid AND openid=:openid AND stock_total>0', array(':uniacid' => $_W['uniacid'], ':openid' => $up_channel['openid']));
				}

				if (($member['level_num'] < $up_level['level_num']) && !empty($_obf_DTIuEDYJAkAVMAQNKj8hDjwlOwo5OSI_)) {
					$up_level['openid'] = $up_channel['openid'];
					$up_level['stock'] = $_obf_DTIuEDYJAkAVMAQNKj8hDjwlOwo5OSI_;
					return $up_level;
				}

				$this->getUpChannel($up_channel['openid']);
			}
		}

		public function getChannelNum($openid)
		{
			global $_W;
			$set = $this->getSet();
			$member = m('member')->getInfo($openid);
			$_obf_DRoJHQgOMCgwLQY5HDkdNQEyLCU1FTI_ = $this->getLevel($openid);

			if (empty($member['agentid'])) {
				return NULL;
			}

			$up_channel = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_member') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND id=' . $member['agentid']);

			if (empty($up_channel['channel_level'])) {
				return NULL;
			}

			$_obf_DT4CNScoESMuIlwXQAIzIRsyC0AaGgE_ = $this->getLevel($up_channel['openid']);

			if ($_obf_DT4CNScoESMuIlwXQAIzIRsyC0AaGgE_['level_num'] <= $_obf_DRoJHQgOMCgwLQY5HDkdNQEyLCU1FTI_['level_num']) {
				pdo_insert('sz_yi_channel_merchant', array('uniacid' => $_W['uniacid'], 'openid' => $up_channel['openid'], 'lower_openid' => $openid, 'commission' => $set['setprofitproportion']));
				return NULL;
			}

			$this->getChannelNum($up_channel['openid']);
		}

		public function getLevel($openid)
		{
			global $_W;

			if (empty($openid)) {
				return false;
			}

			$member = m('member')->getMember($openid);

			if (empty($member['channel_level'])) {
				return false;
			}

			$level = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid AND id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $member['channel_level']));
			return $level;
		}

		public function upgradeLevelByAgent($openid)
		{
			global $_W;

			if (empty($openid)) {
				return false;
			}

			$set = $this->getSet();
			$member = m('member')->getMember($openid);

			if (empty($member)) {
				return NULL;
			}

			$_obf_DSI7LT4OMBwpAwE5GCkQGzIkKlwDJAE_ = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('sz_yi_member') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND agentid=' . $member['id'] . ' AND ischannel=1 AND channel_level>0');

			if ($set['become'] == 1) {
				$my_level = $this->getLevel($openid);
				$_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_ = $my_level['level_num'] + 1;
				$channel_level = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND level_num=' . $_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_);

				if ($channel_level['team_count'] <= $_obf_DSI7LT4OMBwpAwE5GCkQGzIkKlwDJAE_) {
					pdo_update('sz_yi_member', array('channel_level' => $channel_level['id']), array('uniacid' => $_W['uniacid'], 'id' => $member['id']));
					$this->getChannelNum($member['openid']);
				}
			}
		}

		public function deductChannelStock($orderid)
		{
			global $_W;
			$openid = pdo_fetchcolumn('SELECT openid FROM ' . tablename('sz_yi_order') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND id=' . $orderid);
			$my_info = $this->getInfo($openid);
			$order_goods = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_order_goods') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND orderid=' . $orderid);

			foreach ($order_goods as $og) {
				$channel_cond = ' WHERE uniacid=' . $_W['uniacid'] . ' AND goodsid=' . $og['goodsid'] . ' AND openid=\'' . $openid . '\'';

				if (!empty($og['optionid'])) {
					$channel_cond .= ' AND optionid=' . $og['optionid'];
				}

				$channel_stock = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_stock') . $channel_cond);
				$data = array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'goodsid' => $og['goodsid']);

				if (empty($my_info['up_channel'])) {
					$mid = 0;
				}
				else {
					$up_mem = m('member')->getInfo($my_info['up_channel']['openid']);
					$mid = $up_mem['id'];
				}

				$log_data = array('openid' => $openid, 'goodsid' => $og['goodsid'], 'order_goodsid' => $og['id'], 'every_turn' => $og['total'], 'uniacid' => $_W['uniacid'], 'type' => 4, 'paytime' => time(), 'mid' => $mid);

				if (!empty($channel_stock)) {
					$stock_total = $channel_stock['stock_total'] - $og['total'];

					if (!empty($og['optionid'])) {
						$data['optionid'] = $og['optionid'];
						$log_data['optionid'] = $og['optionid'];
					}

					$log_data['surplus_stock'] = $stock_total;
					pdo_update('sz_yi_channel_stock', array('stock_total' => $stock_total), $data);
					pdo_insert('sz_yi_channel_stock_log', $log_data);
				}
			}
		}

		public function checkOrderFinishOrPay($orderid = '')
		{
			global $_W;
			global $_GPC;

			if (empty($orderid)) {
				return NULL;
			}

			$set = $this->getSet();

			if (empty($set['become'])) {
				return NULL;
			}

			$order = pdo_fetch('SELECT id,openid,ordersn,goodsprice,agentid,paytime,finishtime FROM ' . tablename('sz_yi_order') . ' WHERE id=:id AND status>=1 AND uniacid=:uniacid limit 1', array(':id' => $orderid, ':uniacid' => $_W['uniacid']));

			if (empty($order)) {
				return NULL;
			}

			$openid = $order['openid'];
			$member = m('member')->getMember($openid);

			if (empty($member)) {
				return NULL;
			}

			$order_goods = pdo_fetchall('select g.id,g.title,og.total,og.price,og.realprice, og.optionname as optiontitle,g.noticeopenid,g.noticetype,og.commission1,og.channel_id,og.ischannelpay from ' . tablename('sz_yi_order_goods') . ' og ' . ' left join ' . tablename('sz_yi_goods') . ' g on g.id=og.goodsid ' . ' where og.uniacid=:uniacid and og.orderid=:orderid ', array(':uniacid' => $_W['uniacid'], ':orderid' => $orderid));

			foreach ($order_goods as $og) {
				$goods = '';
				$pricetotal = 0;
				$goods .= '' . $og['title'] . '( ';

				if (!empty($og['optiontitle'])) {
					$goods .= ' 规格: ' . $og['optiontitle'];
				}

				@$goods .= ' 单价: ' . ($og['realprice'] / $og['total']) . ' 数量: ' . $og['total'] . ' 总价: ' . $og['realprice'] . '); ';
				$pricetotal += $og['realprice'];
				$level = $this->getLevel($openid);
				$message = array('nickname' => $member['nickname'], 'ordersn' => $order['ordersn'], 'price' => $pricetotal, 'goods' => $goods, 'level_name' => $level['level_name']);

				if (!empty($og['ischannelpay'])) {
					if (!empty($og['channel_id'])) {
						$_obf_DS0OKCsNISMtGTU8BiE5IhUtCwcGGiI_ = pdo_fetchcolumn('SELECT openid FROM ' . tablename('sz_yi_member') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND id=' . $og['channel_id']);
						$this->sendMessage($_obf_DS0OKCsNISMtGTU8BiE5IhUtCwcGGiI_, $message, TM_LOWERCHANNEL_ORDER);
					}
					else {
						$this->sendMessage($openid, $message, TM_CHANNELPURCHASE_ORDER);
					}
				}
				else {
					if (!empty($og['channel_id'])) {
						$up_member = pdo_fetch('SELECT openid,nickname FROM ' . tablename('sz_yi_member') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND id=' . $og['channel_id']);
						$message['up_nickname'] = $up_member['nickname'];
						$this->sendMessage($up_member['openid'], $message, TM_CHANNELRETAIL_ORDER);
					}
				}
			}
		}

		public function sendMessage($openid = '', $data = array(), $message_type = '')
		{
			global $_W;
			global $_GPC;
			$set = $this->getSet();
			$member = m('member')->getInfo($openid);
			$tm = $set['tm'];
			if (($message_type == TM_CHANNEL_APPLY) && !empty($tm['channel_apply'])) {
				$message = $tm['channel_apply'];
				$message = str_replace('[昵称]', $member['nickname'], $message);
				$message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
				$message = str_replace('[金额]', $data['commission'], $message);
				$message = str_replace('[提现方式]', $data['type'], $message);
				$msg = array(
					'keyword1' => array('value' => !empty($tm['channel_applytitle']) ? $tm['channel_applytitle'] : '渠道商提现申请提交通知', 'color' => '#73a68d'),
					'keyword2' => array('value' => $message, 'color' => '#73a68d')
					);
			}
			else {
				if (($message_type == TM_CHANNEL_APPLY_FINISH) && !empty($tm['channel_check'])) {
					$message = $tm['channel_check'];
					$message = str_replace('[昵称]', $member['nickname'], $message);
					$message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
					$message = str_replace('[金额]', $data['commission'], $message);
					$message = str_replace('[提现方式]', $data['type'], $message);
					$msg = array(
						'keyword1' => array('value' => !empty($tm['channel_checktitle']) ? $tm['channel_checktitle'] : '渠道商提现申请审核完成通知', 'color' => '#73a68d'),
						'keyword2' => array('value' => $message, 'color' => '#73a68d')
						);
				}
				else {
					if (($message_type == TM_CHANNEL_BECOME) && !empty($tm['channel_become'])) {
						$message = $tm['channel_become'];
						$message = str_replace('[昵称]', $member['nickname'], $message);
						$message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
						$msg = array(
							'keyword1' => array('value' => !empty($tm['channel_becometitle']) ? $tm['channel_becometitle'] : '成为渠道商通知', 'color' => '#73a68d'),
							'keyword2' => array('value' => $message, 'color' => '#73a68d')
							);
					}
					else {
						if (($message_type == TM_CHANNEL_UPGRADE) && !empty($tm['channel_upgrade'])) {
							$message = $tm['channel_upgrade'];
							$message = str_replace('[昵称]', $member['nickname'], $message);
							$message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
							$message = str_replace('[旧等级]', $data['oldlevelname'], $message);
							$message = str_replace('[旧等级采购折扣]', $data['old_purchase_discount'], $message);
							$message = str_replace('[新等级]', $data['newlevelname'], $message);
							$message = str_replace('[新等级采购折扣]', $data['new_purchase_discount'], $message);
							$msg = array(
								'keyword1' => array('value' => !empty($tm['channel_upgradetitle']) ? $tm['channel_upgradetitle'] : '渠道商等级升级通知', 'color' => '#73a68d'),
								'keyword2' => array('value' => $message, 'color' => '#73a68d')
								);
						}
						else {
							if (($message_type == TM_LOWERCHANNEL_ORDER) && !empty($tm['channel_lowerpurchase'])) {
								$message = $tm['channel_lowerpurchase'];
								$message = str_replace('[昵称]', $data['nickname'], $message);
								$message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
								$message = str_replace('[订单号]', $data['ordersn'], $message);
								$message = str_replace('[渠道等级]', $data['level_name'], $message);
								$message = str_replace('[商品]', $data['goods'], $message);
								$msg = array(
									'keyword1' => array('value' => !empty($tm['channel_upgradetitle']) ? $tm['channel_upgradetitle'] : '下级渠道商采购通知', 'color' => '#73a68d'),
									'keyword2' => array('value' => $message, 'color' => '#73a68d')
									);
							}
							else {
								if (($message_type == TM_CHANNELPURCHASE_ORDER) && !empty($tm['channel_purchase'])) {
									$message = $tm['channel_purchase'];
									$message = str_replace('[昵称]', $data['nickname'], $message);
									$message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
									$message = str_replace('[订单号]', $data['ordersn'], $message);
									$message = str_replace('[渠道等级]', $data['level_name'], $message);
									$message = str_replace('[商品]', $data['goods'], $message);
									$msg = array(
										'keyword1' => array('value' => !empty($tm['channel_upgradetitle']) ? $tm['channel_upgradetitle'] : '渠道商采购通知', 'color' => '#73a68d'),
										'keyword2' => array('value' => $message, 'color' => '#73a68d')
										);
								}
								else {
									if (($message_type == TM_CHANNELRETAIL_ORDER) && !empty($tm['channel_retail'])) {
										$message = $tm['channel_retail'];
										$message = str_replace('[上级昵称]', $data['up_nickname'], $message);
										$message = str_replace('[昵称]', $data['nickname'], $message);
										$message = str_replace('[时间]', date('Y-m-d H:i:s', time()), $message);
										$message = str_replace('[订单号]', $data['ordersn'], $message);
										$message = str_replace('[渠道等级]', $data['level_name'], $message);
										$message = str_replace('[商品]', $data['goods'], $message);
										$msg = array(
											'keyword1' => array('value' => !empty($tm['channel_retailtitle']) ? $tm['channel_retailtitle'] : '渠道商零售订单通知', 'color' => '#73a68d'),
											'keyword2' => array('value' => $message, 'color' => '#73a68d')
											);
									}
								}
							}
						}
					}
				}
			}

			m('message')->sendCustomNotice($openid, $msg);
		}

		public function becomeChannelByAgent($openid)
		{
			global $_W;

			if (empty($openid)) {
				return false;
			}

			$set = $this->getSet();
			$member = m('member')->getMember($openid);

			if (empty($member)) {
				return NULL;
			}

			$_obf_DSI7LT4OMBwpAwE5GCkQGzIkKlwDJAE_ = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('sz_yi_member') . ' WHERE uniacid=:uniacid AND agentid=:id', array(':uniacid' => $_W['uniacid'], ':id' => $member['id']));
			if (!empty($set['default_level']) && !empty($set['become_condition_team'])) {
				if ($set['become_condition_team'] <= $_obf_DSI7LT4OMBwpAwE5GCkQGzIkKlwDJAE_) {
					$_obf_DRwmECsrMyYxFz4BBx0bKRkJAwcUMAE_ = pdo_fetchcolumn('SELECT id FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $set['default_level']));

					if (!$_obf_DRwmECsrMyYxFz4BBx0bKRkJAwcUMAE_) {
						$_obf_DRwmECsrMyYxFz4BBx0bKRkJAwcUMAE_ = pdo_fetchcolumn('SELECT id FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid ORDER BY level_num ASC LIMIT 1', array(':uniacid' => $_W['uniacid']));
					}

					pdo_update('sz_yi_member', array('ischannel' => 1, 'channel_level' => $_obf_DRwmECsrMyYxFz4BBx0bKRkJAwcUMAE_), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
					$this->getChannelNum($openid);
					$this->sendMessage($openid, array('nickname' => $member['nickname']), TM_CHANNEL_BECOME);
					return NULL;
				}

				return NULL;
			}
		}

		public function becomeChannelByOrder($openid, $orderid)
		{
			global $_W;
			if (empty($openid) || empty($orderid)) {
				return false;
			}

			$set = $this->getSet();
			$member = m('member')->getMember($openid);

			if (empty($member)) {
				return NULL;
			}

			if ($set['become_condition_order'] == 0) {
				$condtion .= ' AND o.status >= 1 AND o.status < 3';
			}
			else {
				if ($set['become_condition_order'] == 1) {
					$condtion .= ' AND o.status >= 3';
				}
			}

			$orderinfo = pdo_fetch('SELECT sum(og.realprice) AS ordermoney,count(distinct og.orderid) AS ordercount FROM ' . tablename('sz_yi_order') . ' o ' . ' LEFT JOIN  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' WHERE o.openid=:openid ' . $condtion . ' AND o.uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
			$_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_ = pdo_fetchcolumn('SELECT id FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $set['default_level']));

			if (!$_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_) {
				$_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_ = pdo_fetchcolumn('SELECT id FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid ORDER BY level_num ASC LIMIT 1', array(':uniacid' => $_W['uniacid']));
			}

			if ($set['become_condition'] == 3) {
				if ($set['become_condition_money'] <= $orderinfo['ordermoney']) {
					pdo_update('sz_yi_member', array('ischannel' => 1, 'channel_level' => $_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
					$this->getChannelNum($openid);
					$this->sendMessage($openid, array('nickname' => $member['nickname']), TM_CHANNEL_BECOME);
					return NULL;
				}
			}
			else if ($set['become_condition'] == 4) {
				if ($set['become_condition_count'] <= $orderinfo['ordercount']) {
					pdo_update('sz_yi_member', array('ischannel' => 1, 'channel_level' => $_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
					$this->getChannelNum($openid);
					$this->sendMessage($openid, array('nickname' => $member['nickname']), TM_CHANNEL_BECOME);
					return NULL;
				}
			}
			else {
				return NULL;
			}
		}

		public function becomeChannelByGood($openid, $orderid)
		{
			global $_W;
			$set = $this->getSet();
			if (empty($openid) || empty($orderid)) {
				return false;
			}

			$member = m('member')->getMember($openid);

			if (empty($member)) {
				return NULL;
			}

			if ($set['become_condition_order'] == 0) {
				$condtion .= ' AND o.status >= 1 AND o.status < 3';
			}
			else {
				if ($set['become_condition_order'] == 1) {
					$condtion .= ' AND o.status >= 3';
				}
			}

			$goods = pdo_fetch('SELECT og.goodsid FROM ' . tablename('sz_yi_order_goods') . '  og LEFT JOIN ' . tablename('sz_yi_order') . '  o  ON og.orderid = o.id WHERE o.id=:orderid AND o.openid = :openid AND og.uniacid=:uniacid AND og.goodsid = :goodsid' . $condtion, array(':orderid' => $orderid, ':uniacid' => $_W['uniacid'], ':openid' => $openid, ':goodsid' => $set['become_condition_goodsid']));
			$_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_ = pdo_fetchcolumn('SELECT id FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid AND id = :id', array(':uniacid' => $_W['uniacid'], ':id' => $set['default_level']));

			if (!$_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_) {
				$_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_ = pdo_fetchcolumn('SELECT id FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid ORDER BY level_num ASC LIMIT 1', array(':uniacid' => $_W['uniacid']));
			}

			if ($set['become_condition'] == 5) {
				if ($goods) {
					pdo_update('sz_yi_member', array('ischannel' => 1, 'channel_level' => $_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
					$this->getChannelNum($openid);
					$this->sendMessage($openid, array('nickname' => $member['nickname']), TM_CHANNEL_BECOME);
					return NULL;
				}
			}
			else {
				return NULL;
			}
		}

		public function upgradelevelByGood($openid, $orderid)
		{
			global $_W;
			$set = $this->getSet();
			if (empty($openid) || empty($orderid)) {
				return false;
			}

			$member = m('member')->getMember($openid);

			if (empty($member)) {
				return NULL;
			}

			if ($set['become_order'] == 0) {
				$condtion .= ' AND o.status >= 1 AND o.status < 3';
			}
			else {
				if ($set['become_order'] == 1) {
					$condtion .= ' AND o.status >= 3';
				}
			}

			$my_level = $this->getLevel($openid);
			$up_level = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=:uniacid AND level_num > :level_num ORDER BY level_num ASC LIMIT 1', array(':uniacid' => $_W['uniacid'], ':level_num' => $my_level['level_num']));
			if ($up_level && $up_level['goods_id'] && ($up_level['become'] == 1)) {
				$goods = pdo_fetch('SELECT og.goodsid FROM ' . tablename('sz_yi_order_goods') . '  og LEFT JOIN ' . tablename('sz_yi_order') . '  o  ON og.orderid = o.id WHERE o.id=:orderid AND o.openid = :openid AND og.uniacid=:uniacid AND og.goodsid = :goodsid' . $condtion . ' LIMIT 1', array(':orderid' => $orderid, ':uniacid' => $_W['uniacid'], ':openid' => $openid, ':goodsid' => $up_level['goods_id']));

				if ($goods) {
					pdo_update('sz_yi_member', array('ischannel' => 1, 'channel_level' => $up_level['id']), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
					$this->getChannelNum($openid);
					$this->sendMessage($openid, array('nickname' => $member['nickname'], 'oldlevelname' => $my_level['level_name'], 'old_purchase_discount' => $my_level['purchase_discount'], 'newlevelname' => $up_level['level_name'], 'new_purchase_discount' => $up_level['purchase_discount']), TM_CHANNEL_UPGRADE);
					return NULL;
				}
			}
			else {
				return NULL;
			}
		}

		public function upgradelevelByOther($openid, $orderid = '')
		{
			global $_W;
			$set = $this->getSet();

			if (empty($openid)) {
				return false;
			}

			$member = m('member')->getMember($openid);

			if (empty($member)) {
				return NULL;
			}

			if ($set['become_order'] == 0) {
				$condtion .= ' AND o.status >= 1 AND o.status < 3';
			}
			else {
				if ($set['become_order'] == 1) {
					$condtion .= ' AND o.status >= 3';
				}
			}

			$my_level = $this->getLevel($openid);
			$_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_ = $my_level['level_num'] + 1;
			$channel_level = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_channel_level') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND level_num=' . $_obf_DSIqDS4fHyQZQBBAGx8BFg0xKhcQMhE_);

			if (empty($channel_level)) {
				return NULL;
			}

			$status = array();

			if ($set['become'] == 2) {
				if (in_array(1, $set['become_other'])) {
					$_obf_DSI7LT4OMBwpAwE5GCkQGzIkKlwDJAE_ = pdo_fetchcolumn('SELECT count(*) FROM ' . tablename('sz_yi_member') . ' WHERE uniacid=' . $_W['uniacid'] . ' AND agentid=' . $member['id'] . ' AND ischannel=1 AND channel_level>0');

					if ($channel_level['team_count'] <= $_obf_DSI7LT4OMBwpAwE5GCkQGzIkKlwDJAE_) {
						$status[1]['status'] = 1;
					}
					else {
						$status[1]['status'] = 0;
					}
				}

				if (in_array(2, $set['become_other'])) {
					$orderinfo = pdo_fetch('SELECT sum(og.realprice) AS ordermoney,count(distinct og.orderid) AS ordercount FROM ' . tablename('sz_yi_order') . ' o ' . ' LEFT JOIN  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' WHERE o.openid=:openid ' . $condtion . ' AND o.uniacid=:uniacid AND og.ischannelpay=1 limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
					$ordermoney = $orderinfo['ordermoney'];

					if ($channel_level['order_money'] <= $ordermoney) {
						$status[2]['status'] = 1;
					}
					else {
						$status[2]['status'] = 0;
					}
				}

				if (in_array(3, $set['become_other'])) {
					$orderinfo = pdo_fetch('SELECT sum(og.realprice) AS ordermoney,count(distinct og.orderid) AS ordercount FROM ' . tablename('sz_yi_order') . ' o ' . ' LEFT JOIN  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' WHERE o.openid=:openid ' . $condtion . ' AND o.uniacid=:uniacid AND og.ischannelpay=1 limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $openid));
					$ordercount = $orderinfo['ordercount'];

					if ($channel_level['order_count'] <= $ordercount) {
						$status[3]['status'] = 1;
					}
					else {
						$status[3]['status'] = 0;
					}
				}

				$_obf_DS0_MhA9JQEvLhodFiEKIRQ4Nx8UHDI_ = 0;

				foreach ($status as $row) {
					if ($row['status'] == 1) {
						$_obf_DS0_MhA9JQEvLhodFiEKIRQ4Nx8UHDI_ = 1;
					}
					else {
						$_obf_DS0_MhA9JQEvLhodFiEKIRQ4Nx8UHDI_ = 0;
						break;
					}
				}

				if ($_obf_DS0_MhA9JQEvLhodFiEKIRQ4Nx8UHDI_ == 1) {
					pdo_update('sz_yi_member', array('channel_level' => $channel_level['id']), array('uniacid' => $_W['uniacid'], 'openid' => $openid));
					$this->getChannelNum($openid);
					$this->sendMessage($openid, array('nickname' => $member['nickname'], 'oldlevelname' => $my_level['level_name'], 'old_purchase_discount' => $my_level['purchase_discount'], 'newlevelname' => $channel_level['level_name'], 'new_purchase_discount' => $channel_level['purchase_discount']), TM_CHANNEL_UPGRADE);
					return NULL;
				}

				return NULL;
			}
		}
	}
}

?>
