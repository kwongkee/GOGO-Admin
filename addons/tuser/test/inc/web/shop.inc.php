<?php

/**
 * 商铺管理
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

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_shop') . " WHERE {$condition} and isdel=0";
$total = pdo_fetchcolumn($sql, $pars);

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE {$condition} ORDER BY `order` DESC limit $start , $psize";
$list = pdo_fetchall($sql, $pars);
$pager = pagination($total, $pindex, $psize);

//获取图片域名
if ($this->module['config']['attach_url']) {
    $attach_url = $this->module['config']['attach_url'];
} else {
    $attach_url = $_W['siteroot'] . '/' . $_W['config']['upload']['attachdir'];
}

include $this->template('web/shop_list');
?>
