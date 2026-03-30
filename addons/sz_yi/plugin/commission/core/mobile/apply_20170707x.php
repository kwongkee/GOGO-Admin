<?php
// 模块LTD提供
global $_W;
global $_GPC;
$openid = m('user')->getOpenid();
$member = $this->model->getInfo($openid, array('ok'));
$settingalipay = m('common')->getSysset(array('shop', 'pay'));

if ($_W['isajax']) {
	$level = $this->set['level'];
	$closewithdrawcheck = $this->set['closewithdrawcheck'];
	$creditnocheck = $this->set['creditnocheck'];
	$member = $this->model->getInfo($openid, array('ok'));
	$time = time();
	$day_times = intval($this->set['settledays']) * 3600 * 24;
	$commission_ok = $member['commission_ok'];

	if ($commission_ok <= 0) {
		show_json('0', '提现金额不能为0！');
	}

	$cansettle = floatval($this->set['withdraw']) <= $commission_ok;
	$member['commission_ok'] = number_format($commission_ok, 2);
	$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');

	if ($_W['ispost']) {
		$orderids = array();

		if (1 <= $level) {
			$level1_orders = pdo_fetchall('select distinct o.id from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=3  and og.status1=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ') and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid'], ':agentid' => $member['id']));

			if (p('hotel')) {
				$level1_orders = pdo_fetchall('select distinct o.id from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid=:agentid and o.status>=3  and o.status<>4 and o.status<>5 and  o.status<>6 and og.status1=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ') and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid'], ':agentid' => $member['id']));
			}

			foreach ($level1_orders as $o) {
				if (empty($o['id'])) {
					continue;
				}

				$orderids[] = array('orderid' => $o['id'], 'level' => 1);
			}
		}

		if (2 <= $level) {
			if (0 < $member['level1']) {
				$level2_orders = pdo_fetchall('select distinct o.id from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($member['level1_agentids'])) . ')  and o.status>=3  and og.status2=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ') and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid']));

				if (p('hotel')) {
					$level2_orders = pdo_fetchall('select distinct o.id from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($member['level1_agentids'])) . ')  and o.status>=3 and o.status<>4 and o.status<>5 and  o.status<>6  and og.status2=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ') and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid']));
				}

				foreach ($level2_orders as $o) {
					if (empty($o['id'])) {
						continue;
					}

					$orderids[] = array('orderid' => $o['id'], 'level' => 2);
				}
			}
		}

		if (3 <= $level) {
			if (0 < $member['level2']) {
				$level3_orders = pdo_fetchall('select distinct o.id from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($member['level2_agentids'])) . ')  and o.status>=3  and  og.status3=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ')   and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid']));

				if (p('hotel')) {
					$level3_orders = pdo_fetchall('select distinct o.id from ' . tablename('sz_yi_order') . ' o ' . ' left join  ' . tablename('sz_yi_order_goods') . ' og on og.orderid=o.id ' . ' where o.agentid in( ' . implode(',', array_keys($member['level2_agentids'])) . ')  and o.status>=3  and o.status<>4 and o.status<>5 and  o.status<>6 and  og.status3=0 and og.nocommission=0 and (' . $time . ' - o.finishtime > ' . $day_times . ')   and o.uniacid=:uniacid  group by o.id', array(':uniacid' => $_W['uniacid']));
				}

				foreach ($level3_orders as $o) {
					if (empty($o['id'])) {
						continue;
					}

					$orderids[] = array('orderid' => $o['id'], 'level' => 3);
				}
			}
		}

		$settingalipay = m('common')->getSysset(array('shop', 'pay'));

		if ($_GPC['type'] == '2') {
			if (($settingalipay['pay']['weixin'] != '1') || ($settingalipay['pay']['weixin_withdrawals'] != '1')) {
				show_json('0', '商家未开启微信支付或微信红包提现功能！!');
			}
		}

		if ($_GPC['type'] == '3') {
			if (($settingalipay['pay']['alipay'] != '1') || ($settingalipay['pay']['alipay_withdrawals'] != '1')) {
				show_json('0', '商家未开启支付宝支付或支付宝提现功能！!');
			}
		}

		$time = time();

		foreach ($orderids as $o) {
			pdo_update('sz_yi_order_goods', array('status' . $o['level'] => 1, 'applytime' . $o['level'] => $time), array('orderid' => $o['orderid'], 'uniacid' => $_W['uniacid']));
		}

		$applyno = m('common')->createNO('commission_apply', 'applyno', 'CA');
		$apply = array('uniacid' => $_W['uniacid'], 'applyno' => $applyno, 'orderids' => iserializer($orderids), 'mid' => $member['id'], 'commission' => $commission_ok, 'type' => intval($_GPC['type']), 'status' => 1, 'applytime' => $time);
		if (($_GPC['alipay'] != '') && ($_GPC['alipayname'] != '')) {
			$apply = array('uniacid' => $_W['uniacid'], 'applyno' => $applyno, 'orderids' => iserializer($orderids), 'mid' => $member['id'], 'commission' => $commission_ok, 'type' => intval($_GPC['type']), 'alipay' => $_GPC['alipay'], 'alipayname' => $_GPC['alipayname'], 'status' => 1, 'applytime' => $time);
		}
		else {
			$apply = array('uniacid' => $_W['uniacid'], 'applyno' => $applyno, 'orderids' => iserializer($orderids), 'mid' => $member['id'], 'commission' => $commission_ok, 'type' => intval($_GPC['type']), 'status' => 1, 'applytime' => $time);
		}

		if (0 < $member['credit20']) {
			$credit20 = 0 - $member['credit20'];
			m('member')->setCredit($openid, 'credit20', $credit20);
			$apply['credit20'] = $member['credit20'];
		}

		pdo_insert('sz_yi_commission_apply', $apply);
		$id = pdo_insertid();

		if ($creditnocheck == 1) {
			if (($commission_ok <= $closewithdrawcheck) || ($apply['type'] == 0)) {
				$time = time();
				$pay = $commission_ok;
				if (($apply['type'] == 1) || ($apply['type'] == 2)) {
					$pay *= 100;
				}

				if ($apply['type'] == 2) {
					if (($pay <= 20000) && (1 <= $pay)) {
						$result = m('finance')->sendredpack($openid, $pay, 0, $desc = '佣金提现', $act_name = '佣金提现', $remark = '佣金提现金额以红包形式发送');
					}
					else {
						message('红包提现金额限制1-200元！', '', 'error');
					}
				}
				else {
					$result = m('finance')->pay($openid, $apply['type'], $pay, $apply['applyno']);
				}

				if (is_error($result)) {
					if (strexists($result['message'], '系统繁忙')) {
						$updateno['applyno'] = $apply['applyno'] = m('common')->createNO('commission_apply', 'applyno', 'CA');
						pdo_update('sz_yi_commission_apply', $updateno, array('id' => $apply['id']));
						$result = m('finance')->pay($openid, $apply['type'], $pay, $apply['applyno']);

						if (is_error($result)) {
							message($result['message'], '', 'error');
						}
					}

					message($result['message'], '', 'error');
				}

				pdo_update('sz_yi_commission_apply', array('status' => 3, 'paytime' => $time, 'checktime' => $time, 'commission_pay' => $commission_ok), array('id' => $id, 'uniacid' => $_W['uniacid']));
				$log = array('uniacid' => $_W['uniacid'], 'applyid' => $id, 'mid' => $member['id'], 'commission' => $commission_ok, 'commission_pay' => $commission_ok, 'createtime' => $time);
				pdo_insert('sz_yi_commission_log', $log);
				$this->model->sendMessage($openid, array('commission' => $commission_ok, 'type' => $apply['type'] == 0 ? '余额' : '微信'), TM_COMMISSION_PAY);
				$this->model->upgradeLevelByCommissionOK($openid);
				plog('commission.apply.pay', '佣金打款 ID: ' . $id . ' 申请编号: ' . $apply['applyno'] . ' 总佣金: ' . $commission_ok . ' 审核通过佣金: ' . $commission_ok . ' ');
				show_json(1, '已自动打款!');
			}
			else {
				$returnurl = urlencode($this->createMobileUrl('member/withdraw'));
				$infourl = $this->createMobileUrl('member/info', array('returnurl' => $returnurl));
				$this->model->sendMessage($openid, array('commission' => $commission_ok, 'type' => $apply['type'] == 0 ? '余额' : '微信'), TM_COMMISSION_APPLY);
				show_json(1, '已提交,请等待审核!');
			}
		}
		else {
			$returnurl = urlencode($this->createMobileUrl('member/withdraw'));
			$infourl = $this->createMobileUrl('member/info', array('returnurl' => $returnurl));
			$this->model->sendMessage($openid, array('commission' => $commission_ok, 'type' => $apply['type'] == 0 ? '余额' : '微信'), TM_COMMISSION_APPLY);
			show_json(1, '已提交,请等待审核!');
		}
	}

	$returnurl = urlencode($this->createPluginMobileUrl('commission/apply'));
	$infourl = $this->createMobileUrl('member/info', array('returnurl' => $returnurl));
	show_json(1, array('commission_ok' => $member['commission_ok'], 'cansettle' => $cansettle, 'member' => $member, 'set' => $this->set, 'infourl' => $infourl, 'noinfo' => empty($member['realname'])));
}

include $this->template('apply');

?>
