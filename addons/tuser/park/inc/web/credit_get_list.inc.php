<?php

global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

require_once WXZ_SHOPPINGMALL . '/source/CreditLog.class.php';

$eventTypes = CreditLog::$eventTypes;

$start = ($pindex - 1) * $psize;
$condition = "`uniacid`={$_W['uniacid']}";

if ($_GPC['mobile']) {
    require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
    $mobileFans = new Fans();
    $fans = $mobileFans->getByMobile($_GPC['mobile']);
    if ($fans) {
        $condition .= " AND fans_id={$fans['uid']}";
    } else {
        $condition .= " AND 1!=1";
    }
}

if ($_GPC['event_type']) {
    $condition .= " AND event_type={$_GPC['event_type']}";
}


$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition}";

$total = pdo_fetchcolumn($sql);
$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition} ORDER BY `create_at` desc limit $start , $psize";
$list = pdo_fetchall($sql, $pars);

foreach ($list as $i => $_row) {
    $fans_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_fans') . " WHERE uid={$_row['fans_id']}";
    $fans_info = pdo_fetch($fans_info_sql);
    $list[$i]['fans_info'] = $fans_info;

    if ($_row['pass_fans_id']) {
        $fans_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_fans') . " WHERE uid={$_row['pass_fans_id']}";
        $fans_info = pdo_fetch($fans_info_sql);
        $list[$i]['pass_fans_info'] = $fans_info;
    }
}

$pager = pagination($total, $pindex, $psize);
include $this->template('web/credit_get_list');
?>