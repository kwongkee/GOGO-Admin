<?php
// 模块LTD提供
global $_W;
global $_GPC;
$openid = m('user')->getOpenid();
$op = (empty($_GPC['op']) ? 'display' : trim($_GPC['op']));
$id = intval($_GPC['id']);
$coupon = pdo_fetch('select * from ' . tablename('sz_yi_coupon') . ' where id=:id and uniacid=:uniacid  limit 1', array(':id' => $id, ':uniacid' => $_W['uniacid']));

if (empty($coupon)) {
	if ($_W['isajax']) {
		show_json(-1, '未找到优惠券');
	}

	header('location: ' . $this->createPluginMobileUrl('coupon'));
	exit();
}

$coupon = $this->model->setCoupon($coupon, time());
$set = $this->model->getSet();

if ($op == 'display') {
	$this->model->setShare();
	$credit = m('member')->getCredit($openid, 'credit1');
	include $this->template('detail');
	return 1;
}

if (($op == 'pay') && $_W['ispost']) {
	if (empty($coupon['gettype'])) {
		show_json(-1, '无法' . $coupon['gettypestr']);
	}

	if ($coupon['total'] != -1) {
		if ($coupon['total'] <= 0) {
			show_json(-1, '优惠券数量不足');
		}
	}

	if (!$coupon['canget']) {
		show_json(-1, '您已超出' . $coupon['gettypestr'] . '次数限制');
	}

	if (0 < $coupon['credit']) {
		$credit = m('member')->getCredit($openid, 'credit1');

		if ($credit < intval($coupon['credit'])) {
			show_json(-1, '您的积分不足，无法' . $coupon['gettypestr'] . '!');
		}
	}

	$needpay = false;

	if (0 < $coupon['money']) {
		pdo_delete('sz_yi_coupon_log', array('couponid' => $id, 'openid' => $openid, 'status' => 0, 'paystatus' => 0));
		$needpay = true;
		$lastlog = pdo_fetch('select * from ' . tablename('sz_yi_coupon_log') . ' where couponid=:couponid and openid=:openid  and status=0 and paystatus=1 and uniacid=:uniacid limit 1', array(':couponid' => $id, ':openid' => $openid, ':uniacid' => $_W['uniacid']));

		if (!empty($lastlog)) {
			show_json(1, array('logid' => $lastlog['id']));
		}
	}
	else {
		pdo_delete('sz_yi_coupon_log', array('couponid' => $id, 'openid' => $openid, 'status' => 0));
	}

	$log = array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'logno' => m('common')->createNO('coupon_log', 'logno', 'CC'), 'couponid' => $id, 'status' => 0, 'paystatus' => 0 < $coupon['money'] ? 0 : -1, 'creditstatus' => 0 < $coupon['credit'] ? 0 : -1, 'createtime' => time(), 'getfrom' => 1);
	pdo_insert('sz_yi_coupon_log', $log);
	$logid = pdo_insertid();

	if ($needpay) {
		$useweixin = true;

		if (!empty($coupon['usecredit2'])) {
			$money = m('member')->getCredit($openid, 'credit2');

			if ($coupon['money'] <= $money) {
				$useweixin = false;
			}
		}

		pdo_update('sz_yi_coupon_log', array('paytype' => $useweixin ? 1 : 0), array('id' => $logid));

		if ($useweixin) {
			$set = m('common')->getSysset();

			if (!is_weixin()) {
				show_json(-1, '非微信环境!');
			}

			if (empty($set['pay']['weixin'])) {
				show_json(-1, '未开启微信支付!');
			}

			$wechat = array('success' => false);
			$params = array();
			$params['tid'] = $log['logno'];
			$params['user'] = $openid;
			$params['fee'] = $coupon['money'];
			$params['title'] = $set['shop']['name'] . '优惠券领取单号:' . $log['logno'];
			load()->model('payment');
			$setting = uni_setting($_W['uniacid'], array('payment'));

			if (is_array($setting['payment'])) {
				$options = $setting['payment']['wechat'];
				$options['appid'] = $_W['account']['key'];
				$options['secret'] = $_W['account']['secret'];
				$wechat = m('common')->wechat_build($params, $options, 4);
				$wechat['success'] = false;

				if (!is_error($wechat)) {
					$wechat['success'] = true;
				}
				else {
					show_json(0, $wechat['message']);
				}
			}

			if (!$wechat['success']) {
				show_json(0, '微信支付参数错误!');
			}

			show_json(1, array('logid' => $logid, 'wechat' => $wechat));
		}
	}

	show_json(1, array('logid' => $logid));
	return 1;
}

if (($op == 'payresult') && $_W['ispost']) {
	$logid = intval($_GPC['logid']);
	$logno = pdo_fetchcolumn('select logno from ' . tablename('sz_yi_coupon_log') . ' where id=:id and uniacid=:uniacid limit 1', array(':id' => $logid, ':uniacid' => $_W['uniacid']));
	$result = $this->model->payResult($logno);

	if (is_error($result)) {
		show_json($result['errno'], $result['message']);
	}

	show_json(1, array('url' => $result['url'], 'coupontype' => $coupon['coupontype']));
}

?>
