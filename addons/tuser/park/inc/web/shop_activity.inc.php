<?php

/**
 * 商铺活动管理
 */
global $_W, $_GPC;
$filters = array();
$filters['uniacid'] = $_W['uniacid'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

$start = ($pindex - 1) * $psize;
$condition = '`uniacid`=:uniacid AND isdel=0';
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_shop_activity') . " WHERE {$condition} and isdel=0";
$total = pdo_fetchcolumn($sql, $pars);

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop_activity') . " WHERE {$condition} ORDER BY `id` DESC limit $start , $psize";
$list = pdo_fetchall($sql, $pars);

$pager = pagination($total, $pindex, $psize);

foreach ($list as $k => $row) {
    $shop_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE id={$row['shop_id']}";
    $list[$k]['shop'] = pdo_fetch($shop_info_sql);
}

include $this->template('web/shop_activity_list');
?>
