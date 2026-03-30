<?php

global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

$start = ($pindex - 1) * $psize;
$condition = '`uniacid`=:uniacid AND `type`=:type';
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];
$pars[':type'] = 2;

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql, $pars);
$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition} ORDER BY `send_time` desc limit $start , $psize";
$list = pdo_fetchall($sql, $pars);
foreach ($list as $i => $_row) {
    $fans_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_fans') . " WHERE uid={$_row['fans_id']}";
    $fans_info = pdo_fetch($fans_info_sql);
    $list[$i]['fans_info'] = $fans_info;
}
$pager = pagination($total, $pindex, $psize);

include $this->template('web/credit_use_list');
?>
