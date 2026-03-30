<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'index');
$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
$set = unserialize($setdata['sets']);
$shop_name = $set['shop']['name'];

if ($operation == 'success') {
	include $this->template('shop/success');
	return 1;
}

if ($operation == 'fail') {
	include $this->template('shop/fail');
}

?>
