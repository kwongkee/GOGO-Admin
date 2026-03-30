<?php

global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];
$filters['nickname'] = $_GPC['nickname'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

$start = ($pindex - 1) * $psize;
$condition = '`uniacid`=:uniacid AND isdel=0 AND type=1';
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_msg') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql, $pars);

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_msg') . " WHERE {$condition} ORDER BY `id` DESC limit $start , $psize";
$list = pdo_fetchall($sql, $pars);
$pager = pagination($total, $pindex, $psize);

include $this->template('web/user_msg_list');
?>
