<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$shopset = m('common')->getSysset('shop');
$limitsum = 10;
$sql = 'SELECT * FROM ' . tablename('sz_yi_member') . ' WHERE uniacid = :uniacid ORDER BY credit1 DESC LIMIT ' . $limitsum;
$params = array(':uniacid' => $_W['uniacid']);
$list = pdo_fetchall($sql, $params);
include $this->template('member/phb');

?>
