<?php

global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 1000;

$start = ($pindex - 1) * $psize;
$condition = "`uniacid`=:uniacid AND `activity_id`={$_GPC['acid']}";

$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_activity_sign') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql, $pars);
$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_activity_sign') . " WHERE {$condition} ORDER BY `id` desc limit $start , $psize";
$list = pdo_fetchall($sql, $pars);

$pager = pagination($total, $pindex, $psize);

include $this->template('web/shop_activity_apply_list');
?>
