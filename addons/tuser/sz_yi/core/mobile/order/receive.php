<?php
// 模块LTD提供
global $_W;
global $_GPC;
$sets = pdo_fetchall('select uniacid from ' . tablename('sz_yi_sysset'));

foreach ($sets as $set) {
	$_W['uniacid'] = $set['uniacid'];

	if (empty($_W['uniacid'])) {
		continue;
	}

	$trade = m('common')->getSysset('trade', $_W['uniacid']);
	$days = intval($trade['receive']);

	if ($days <= 0) {
		continue;
	}

	$daytimes = 86400 * $days;
	$p = p('commission');
	$pcoupon = p('coupon');
	$orders = pdo_fetchall('select id,couponid from ' . tablename('sz_yi_order') . ' where uniacid=' . $_W['uniacid'] . ' and status=2 and sendtime + ' . $daytimes . ' <=unix_timestamp() ', array(), 'id');

	if (!empty($orders)) {
		$orderkeys = array_keys($orders);
		$orderids = implode(',', $orderkeys);

		if (!empty($orderids)) {
			pdo_query('update ' . tablename('sz_yi_order') . ' set status=3,finishtime=' . time() . ' where id in (' . $orderids . ')');

			foreach ($orders as $orderid => $o) {
				m('notice')->sendOrderMessage($orderid);

				if ($pcoupon) {
					if (!empty($o['couponid'])) {
						$pcoupon->backConsumeCoupon($o['id']);
					}
				}

				if ($p) {
					$p->checkOrderFinish($orderid);
				}
			}
		}
	}
}

$pbonus = p('bonus');

if (!empty($pbonus)) {
	load()->func('file');
	$tmpdir = IA_ROOT . '/addons/sz_yi/tmp/bonus';
	$file = $tmpdir . '/filelock.txt';

	if (!is_dir($tmpdir)) {
		mkdirs($tmpdir);
	}

	if (!file_exists($file)) {
		foreach ($sets as $set) {
			$_W['uniacid'] = $set['uniacid'];

			if (empty($_W['uniacid'])) {
				continue;
			}

			$daytime = strtotime(date('Y-m-d 00:00:00'));
			$isbonus = false;
			$bonus_set = $pbonus->getSet();

			if (empty($bonus_set['sendmethod'])) {
				continue;
			}

			if ($bonus_set['sendmonth'] == 1) {
				$daytime = strtotime(date('Y-m-1 00:00:00'));
			}

			$bonus_data = pdo_fetch('select * from ' . tablename('sz_yi_bonus') . ' where ctime>' . $daytime . ' and isglobal=0 and uniacid=' . $_W['uniacid'] . ' and bonus_area=0  order by id desc');
			$bonus_data_area = pdo_fetch('select * from ' . tablename('sz_yi_bonus') . ' where ctime>' . $daytime . ' and isglobal=0 and uniacid=' . $_W['uniacid'] . ' and bonus_area!=0  order by id desc');
			$bonus_data_isglobal = pdo_fetch('select * from ' . tablename('sz_yi_bonus') . ' where ctime>' . $daytime . ' and isglobal=1 and uniacid=' . $_W['uniacid'] . '  order by id desc');

			if (!empty($bonus_set['start'])) {
				if (empty($bonus_data)) {
					$pbonus->autosend();
				}

				if (empty($bonus_data_area)) {
					$pbonus->autosendarea();
				}

				if (empty($bonus_data_isglobal)) {
					$pbonus->autosendall();
				}
			}
		}

		@unlink($file);
	}
}

if (p('love')) {
	$time = time();
	$rechanges = pdo_fetchall('select * from ' . tablename('sz_yi_member_aging_rechange') . ' where sendpaytime <=' . $time . ' and status=0');
	$set = m('common')->getSysset('shop');

	foreach ($rechanges as $key => $value) {
		$logno = m('common')->createNO('member_log', 'logno', 'RC');
		$_W['uniacid'] = $value['uniacid'];
		$moneyall = pdo_fetchcolumn('select sum(money) from ' . tablename('sz_yi_member_log') . ' where aging_id=' . $value['id'] . ' and uniacid=' . $_W['uniacid']);
		$remain = $value['num'] - $moneyall;
		$edit_rechange = array();

		if ($value['qtotal'] < $remain) {
			$sendmney = $value['qtotal'];
		}
		else {
			$sendmney = $remain;
			$edit_rechange['status'] = 1;
		}

		if ($sendmney <= 0) {
			continue;
		}

		if ($sendmonth == 0) {
			$edit_rechange['sendpaytime'] = mktime($value['sendtime'], 0, 0, date('m'), date('d') + 1, date('Y'));
		}
		else {
			$edit_rechange['sendpaytime'] = mktime($value['sendtime'], 0, 0, date('m') + 1, 1, date('Y'));
		}

		$edit_rechange['phase'] = $value['phase'] + 1;
		pdo_update('sz_yi_member_aging_rechange', $edit_rechange, array('id' => $value['id']));
		$data = array('openid' => $value['openid'], 'logno' => $logno, 'uniacid' => $_W['uniacid'], 'type' => '0', 'createtime' => $time, 'status' => '1', 'title' => $set['name'] . '会员分期充值', 'money' => $sendmney, 'rechargetype' => 'system', 'aging_id' => $value['id'], 'paymethod' => $value['paymethod']);
		pdo_insert('sz_yi_member_log', $data);

		if ($value['paymethod'] == 0) {
			m('member')->setCredit($value['openid'], 'credit2', $sendmney, array(0, '分期充值余额'));
			$logid = pdo_insertid();
			m('notice')->sendMemberLogMessage($logid);
		}
		else {
			m('member')->setCredit($value['openid'], 'credit1', $sendmney, array(0, '分期充值积分'));
			$msg = array(
				'first'    => array('value' => '后台会员分期充值积分！', 'color' => '#4a5077'),
				'keyword1' => array('title' => '分期积分充值', 'value' => '后台会员分期充值积分:' . $sendmney . '积分!', 'color' => '#4a5077'),
				'remark'   => array('value' => "\r\n我们已为您充值积分，请您登录个人中心查看。", 'color' => '#4a5077')
				);
			$detailurl = $this->createMobileUrl('member');
			m('message')->sendCustomNotice($value['openid'], $msg, $detailurl);
		}
	}
}

echo 'ok...';

?>
