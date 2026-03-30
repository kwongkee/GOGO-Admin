<?php

global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];
$filters['nickname'] = $_GPC['nickname'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
$_W['module_setting'] = $this->module['config'];
$levels = Fans::getLevels();

$start = ($pindex - 1) * $psize;
$condition = "`uniacid` = {$_W['uniacid']}";

if ($_GPC['mobile']) {
    $condition .= " AND mobile='{$_GPC['mobile']}'";
    $url .="&mobile={$_GPC['mobile']}";
}

//结束时间
if ($_GPC['rg_time_end']) {
    $reg_time_end = strtotime($_GPC['rg_time_end'].' 23:59:59');
    $conditionRegTime .= " AND reg_time<='{$reg_time_end}'";
}

$condition .= $conditionRegTime;

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_fans') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql);

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_fans') . " WHERE {$condition} ORDER BY `credit` DESC limit $start , $psize";

$list = pdo_fetchall($sql);
$pager = pagination($total, $pindex, $psize);

/* * ************************** */
//统计

//完善资料
$condition = "`uniacid`={$_W['uniacid']} AND mobile!='' AND birthday!='0000-00-00'";
$condition .= $conditionRegTime;
$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_fans') . " WHERE {$condition}";
$reg2Num = pdo_fetchcolumn($sql);
//登记车牌
$condition = "`uniacid`={$_W['uniacid']} AND mobile!='' AND plate_number!=''";
$condition .= $conditionRegTime;
$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_fans') . " WHERE {$condition}";
$reg2PlateNum = pdo_fetchcolumn($sql);

//会员数
$condition = "`uniacid`={$_W['uniacid']} AND mobile!=''";
$condition .= $conditionRegTime;
$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_fans') . " WHERE {$condition}";
$userNum = pdo_fetchcolumn($sql);
include $this->template('web/fans_list');
?>
