<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'index');
$openid = m('user')->getOpenid();
$member = m('member')->getInfo($openid);
$uniacid = $_W['uniacid'];
$list = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_push') . '  ORDER BY `id` DESC');

foreach ($list as $key => $value) {
	$list[$key]['time'] = date('Y-m-d', $value['time']);
}

if ($_W['isajax']) {
	show_json(1, array('list' => $list));
}

include $this->template('member/pushlist');

?>
