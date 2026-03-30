<?php

global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];
$filters['nickname'] = $_GPC['nickname'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

$start = ($pindex - 1) * $psize;
$condition = "`uniacid`={$_W['uniacid']} AND room_no!='' AND user_type=1 AND member_type=1 AND `ischeck`=0";

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_fans') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql);

$list = pdo_fetchall($sql);
$pager = pagination($total, $pindex, $psize);

include $this->template('web/owner_check_list');
?>
