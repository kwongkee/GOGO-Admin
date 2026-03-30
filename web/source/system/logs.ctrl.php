<?php 
/**
 * [WeEngine System] Copyright (c) 2014 lotodo.com
 * WeEngine is NOT a free software, it under the license terms, visited http://www.lotodo.com/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
$dos = array('wechat', 'system', 'database','sms','users','operation','clientUser');
$do = in_array($do, $dos) ? $do : 'wechat';

$params = array();
$where  = '';
$order = ' ORDER BY `id` DESC';
if ($_GPC['time']) {
		$starttime = strtotime($_GPC['time']['start']);
	$endtime = strtotime($_GPC['time']['end']);
	$timewhere = ' AND `createtime` >= :starttime AND `createtime` < :endtime';
	$params[':starttime'] = $starttime;
	$params[':endtime'] = $endtime + 86400;
}

if ($do == 'wechat') {
	$path = IA_ROOT . '/data/logs/';
	$files = glob($path . '*');
	if (!empty($_GPC['searchtime'])) {
		$searchtime = $_GPC['searchtime'] . '.log';
	} else {
		$searchtime = date('Ymd', time()) . '.log';
	}
	$tree = array();
	foreach ($files as $key=>$file) {
		if (!preg_match('/\/[0-9]+\.log/', $file)) {
			continue;
		}
		$pathinfo = pathinfo($file);
		array_unshift($tree, $pathinfo['filename']);
		if (strexists($file, $searchtime)) {
			$contents = file_get_contents($file);
		}
	}
}

if ($do == 'system') {
	$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
	$where .= " WHERE `type` = '1'";
	$sql = 'SELECT * FROM ' . tablename('core_performance') . " $where $timewhere $order LIMIT " . ($pindex - 1) * $psize .','. $psize;
	$list = pdo_fetchall($sql, $params);
	foreach ($list as $key=>$value) {
		$list[$key]['type'] = '系统日志';
		$list[$key]['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
	}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('core_performance'). $where . $timewhere , $params);
	$pager = pagination($total, $pindex, $psize);
}

if ($do == 'database') {
	$pindex = max(1, intval($_GPC['page']));
		$psize = 10;
	$where .= " WHERE `type` = '2'";
	$sql = 'SELECT * FROM ' . tablename('core_performance') . " $where $timewhere $order LIMIT " . ($pindex - 1) * $psize .','. $psize;
	$list = pdo_fetchall($sql, $params);
	foreach ($list as $key=>$value) {
		$list[$key]['type'] = '数据库日志';
		$list[$key]['createtime'] = date('Y-m-d H:i:s', $value['createtime']);
	}
		$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('core_performance'). $where . $timewhere , $params);
	$pager = pagination($total, $pindex, $psize);
}
if ($do == 'sms') {
    $path = IA_ROOT . '/data/logs/';
    $contents = file_get_contents($path."sms.log",FALSE,NULL);
	// if (!empty($_GPC['mobile'])) {
	// 	$timewhere .= ' AND `mobile` LIKE :mobile ';
	// 	$params[':mobile'] = "%{$_GPC['mobile']}%";
	// }
	// $pindex = max(1, intval($_GPC['page']));
	// $psize = 40;
	// $params[':uniacid'] = $_W['uniacid'];
	// $list = pdo_fetchall("SELECT * FROM". tablename('core_sendsms_log'). " WHERE uniacid = :uniacid ". $timewhere. " ORDER BY id DESC LIMIT ". ($pindex-1)*$psize . ','. $psize, $params);
	// $total = pdo_fetchcolumn("SELECT COUNT(*) FROM". tablename('core_sendsms_log'). " WHERE uniacid = :uniacid". $timewhere, $params);
	// $pager = pagination($total, $pindex, $psize);
}

if ($do=='users'){
    // if (!empty($_GPC['username'])) {
    //     $params[':username'] = "%{$_GPC['username']}%";
    // }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 30;
    $list = pdo_fetchall("SELECT * FROM". tablename('users'). " ORDER BY uid DESC LIMIT ". ($pindex-1)*$psize . ','. $psize);
    $total = pdo_fetchcolumn("SELECT COUNT(uid) FROM". tablename('users'));
    $pager = pagination($total, $pindex, $psize);
}

if ($do=='operation'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = 30;
    $list = pdo_fetchall("SELECT a1.*,a2.username FROM". tablename('sz_yi_perm_log'). " as a1 left join ".tablename('users')." as a2 on a1.uid=a2.uid  ORDER BY uid DESC LIMIT ". ($pindex-1)*$psize . ','. $psize);
    $total = pdo_fetchcolumn("SELECT COUNT(uid) FROM". tablename('sz_yi_perm_log'));
    $pager = pagination($total, $pindex, $psize);
}

if ($do=='clientUser'){
    $pindex = max(1, intval($_GPC['page']));
    $psize = 30;
    $list = pdo_fetchall("SELECT * FROM". tablename('sz_yi_member_operating_log'). " ORDER BY id DESC LIMIT ". ($pindex-1)*$psize . ','. $psize);
    $total = pdo_fetchcolumn("SELECT COUNT(uid) FROM". tablename('sz_yi_member_login_log'));
    $pager = pagination($total, $pindex, $psize);
}


template('system/logs');