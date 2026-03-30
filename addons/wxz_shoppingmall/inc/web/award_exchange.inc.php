<?php

global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];
$filters['nickname'] = $_GPC['nickname'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

$start = ($pindex - 1) * $psize;
$condition = '`uniacid`=:uniacid';
if ($_GPC['event_type']) {
    $condition .= " AND event_type={$_GPC['event_type']}";
}
if ($_GPC['id']) {
    $condition .= " AND (award_credit_id={$_GPC['id']} or award_coupon_id={$_GPC['id']})";
}

$pars = array();
$pars[':uniacid'] = $_W['uniacid'];

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql, $pars);

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition} ORDER BY `id` DESC limit $start , $psize";
$list = pdo_fetchall($sql, $pars);
$pager = pagination($total, $pindex, $psize);

foreach ($list as $i => $_row) {
    $fans_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_fans') . " WHERE uid={$_row['fans_id']}";
    $fans_info = pdo_fetch($fans_info_sql);
    switch ($_row['event_type']) {
        case 1:
            $award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_award') . " WHERE id={$_row['award_credit_id']}";
            break;
        case 2:
            $award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_coupon') . " WHERE id={$_row['award_coupon_id']}";
            break;
    }

    $award_info = pdo_fetch($award_info_sql);
    $list[$i]['fans_info'] = $fans_info;
    $list[$i]['award_info'] = $award_info;
}

$eventTypes = array(
    1 => '实物',
    2 => '优惠券'
);

include $this->template('web/award_exchange');
?>
