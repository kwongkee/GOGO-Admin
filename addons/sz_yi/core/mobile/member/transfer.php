<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
load()->func('file');
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$member = m('member')->getMember($openid);

if (!empty($_GPC['yunbi'])) {
	$member['basis_money'] = $member['virtual_currency'];
}
else {
	$member['basis_money'] = $member['credit2'];
}

$uniacid = $_W['uniacid'];
$trade = m('common')->getSysset('trade');

if (p('yunbi')) {
	$yunbi_set = p('yunbi')->getSet();

	if (!empty($yunbi_set['yunbi_title'])) {
		$title = $yunbi_set['yunbi_title'];
	}
	else {
		$title = '云币';
	}
}

if ($operation == 'assigns') {
	if ($_W['isajax']) {
		$assigns_id = intval($_GPC['assigns_id']);
		$assigns = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid and id = :assigns_id ', array(':uniacid' => $uniacid, ':assigns_id' => $assigns_id));

		if ($assigns) {
			if ($assigns['id'] == $member['id']) {
				show_json(0, '受让人不可以是您自己！');
				exit();
			}

			show_json(1, $assigns);
		}
		else {
			show_json(-1);
		}
	}
}
else {
	if ($operation == 'submit') {
		if ($_W['isajax']) {
			$tmpdir = IA_ROOT . '/addons/sz_yi/tmp/member';
			$file = $tmpdir . '/' . $member['openid'] . '.txt';

			if (!is_dir($tmpdir)) {
				mkdirs($tmpdir);
			}

			if (!file_exists($file)) {
				touch($file);
				$money = floatval($_GPC['money']);

				if (empty($_GPC['yunbi'])) {
					if (($money <= 0) || ($member['credit2'] < $money)) {
						@unlink($file);
						show_json(0, '转让金额不正确');
					}
				}
				else {
					if (($money <= 0) || ($member['virtual_currency'] < $money)) {
						@unlink($file);
						show_json(0, '转让' . $title . '不正确');
					}

					if (($money < $yunbi_set['bot_limit']) || (($money % $yunbi_set['bot_fold']) != 0)) {
						@unlink($file);
						show_json(0, '转让' . $yunbi_set['yunbi_title'] . '小于' . $yunbi_set['bot_limit'] . '或不是' . $yunbi_set['bot_fold'] . '的倍数');
					}
				}

				$assigns_id = intval($_GPC['assigns']);
				$assigns = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where uniacid=:uniacid and id = :assigns_id ', array(':uniacid' => $uniacid, ':assigns_id' => $assigns_id));

				if ($assigns) {
					if (empty($_GPC['yunbi'])) {
						//xj 修改 所有操作放在发送通知前，以免微信通信失败导致 转账失败，一个扣一个未加
						$mc_assigns = m('member')->getMember($assigns['openid']);
						//转账人
						m('member')->setCredit($member['openid'], 'credit2', 0 - $money, array(0, '向' . $assigns['nickname'] . '转赠玫瑰花' . $money . '朵！'));

						//被转账人
						m('member')->setCredit($assigns['openid'], 'credit2', $money, array(0, '会员玫瑰花转赠所得：' . $money . ' 朵'));

						//插入转账记录 s
						$member_data = array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'tosell_id' => $member['id'], 'assigns_id' => $assigns_id, 'createtime' => time(), 'status' => 1, 'money' => $money, 'tosell_current_credit' => $member['credit2'] - $money, 'assigns_current_credit' => $assigns['credit2'] + $money);
						pdo_insert('sz_yi_member_transfer_log', $member_data);
						//插入转账记录 s

						//转账人通知
						$messages = array(
							'keyword1' => array('value' => '转赠通知', 'color' => '#73a68d'),

							'keyword2' => array('value' => '你向' . $assigns['nickname'] . '转赠玫瑰花' . $money . '朵！', 'color' => '#73a68d')

						);
						m('message')->sendCustomNotice($member['openid'], $messages);
						//转账人通知 e

						//被转账人通知
						$messages = array(
							'keyword1' => array('value' => '转赠通知', 'color' => '#73a68d'),

							'keyword2' => array('value' => $member['nickname'] . '向你转赠玫瑰花' . $money . '朵！', 'color' => '#73a68d')

						);
						m('message')->sendCustomNotice($assigns['openid'], $messages);
						//被转账人通知 e
						//xj 修改 所有操作放在发送通知前，以免微信通信失败导致 转账失败，一个扣一个未加
						@unlink($file);
						show_json(1);
					}
					else {
						$moneys = 0 - $money;
						p('yunbi')->setVirtualCurrency($openid, $moneys);
						$data = array('uniacid' => $uniacid, 'id' => $member['id'], 'openid' => $openid, 'credittype' => '', 'money' => $money, 'remark' => '转让' . $title . ':' . $moneys);
						p('yunbi')->addYunbiLog($_W['uniacid'], $data, '8');
						$messages = array(
							'keyword1' => array('value' => '转赠通知', 'color' => '#73a68d'),
							'keyword2' => array('value' => '你向' . $assigns['nickname'] . '转赠' . $title . $money, 'color' => '#73a68d')
							);
						m('message')->sendCustomNotice($member['openid'], $messages);
						p('yunbi')->setVirtualCurrency($assigns['openid'], $money);
						$data = array('uniacid' => $uniacid, 'id' => $assigns['id'], 'openid' => $assigns['openid'], 'credittype' => '', 'money' => $money, 'remark' => '获得转让' . $title . ':' . $money);
						p('yunbi')->addYunbiLog($_W['uniacid'], $data, '9');
						$messages = array(
							'keyword1' => array('value' => '转赠通知', 'color' => '#73a68d'),
							'keyword2' => array('value' => '您获得' . $member['nickname'] . '向您转赠' . $title . $money, 'color' => '#73a68d')
							);
						m('message')->sendCustomNotice($assigns['openid'], $messages);
						@unlink($file);
						show_json(1);
					}
				}
				else {
					@unlink($file);
					show_json(0, '受让人不存在！');
				}
			}
		}
	}
}

include $this->template('member/transfer');

?>
