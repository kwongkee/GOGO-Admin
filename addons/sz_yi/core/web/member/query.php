<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$kwd = trim($_GPC['keyword']);
$params = array();
$params[':uniacid'] = $_W['uniacid'];
$condition = ' and uniacid=:uniacid';
$op = $operation = ($_GPC['op'] ? $_GPC['op'] : 'query');

if ($op == 'query') {
	if (!empty($kwd)) {
		$condition .= ' AND ( `nickname` LIKE :keyword or `realname` LIKE :keyword or `mobile` LIKE :keyword )';
		$params[':keyword'] = '%' . $kwd . '%';
	}

	$ds = pdo_fetchall('SELECT id,avatar,nickname,openid,realname,mobile FROM ' . tablename('sz_yi_member') . ' WHERE 1 ' . $condition . ' order by createtime desc', $params);
	include $this->template('web/member/query');
	return 1;
}

if ($op == 'delbindmobile') {
	pdo_update('sz_yi_member', array('isbindmobile' => 0), array('uniacid' => $_W['uniacid']));
	message('清除手机绑定记录成功', $this->createWebUrl('sysset', array('op' => 'member')), 'success');
}

?>
