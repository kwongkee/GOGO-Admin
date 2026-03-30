<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');

if ($operation == 'days') {
	$year = intval($_GPC['year']);
	$month = intval($_GPC['month']);
	exit(get_last_day($year, $month));
}

?>
