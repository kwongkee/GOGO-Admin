<?php

global $_W, $_GPC;

$filters = array();
$filters['uniacid'] = $_W['uniacid'];
$filters['nickname'] = $_GPC['nickname'];

$pindex = intval($_GPC['page']);
$pindex = max($pindex, 1);
$psize = 15;

$start = ($pindex - 1) * $psize;
$condition = "`uniacid`={$_W['uniacid']}";

$sql = "SELECT count(*) as num FROM " . tablename('wxz_shoppingmall_share') . " WHERE {$condition}";
$total = pdo_fetchcolumn($sql);

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_share') . " WHERE {$condition} ORDER BY `id` DESC limit $start , $psize";
$list = pdo_fetchall($sql);
$pager = pagination($total, $pindex, $psize);

//获取图片域名
if ($this->module['config']['attach_url']) {
    $attach_url = $this->module['config']['attach_url'];
} else {
    $attach_url = $_W['siteroot'] . '/' . $_W['config']['upload']['attachdir'];
}

include $this->template('web/share_setting_list');
?>
