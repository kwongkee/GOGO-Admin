<?php

//店面列表页面
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

//查询列表
$condition = '`uniacid`=:uniacid AND isdel=0';
if($_GPC['category']) {
   $condition .= " AND category='{$_GPC['category']}'";
}

$pars = array();
$pars[':uniacid'] = $_W['uniacid'];
$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE {$condition} ORDER BY `order` DESC";
$list = pdo_fetchall($sql, $pars);

//首页轮播图
$index_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_index') . " WHERE uniacid={$_W['uniacid']} AND type=1 limit 1";
$index_info = pdo_fetch($index_info_sql);
$banner_num = 6;
for ($i = 1; $i <= $banner_num; $i++) {
    if ($index_info["banner{$i}"]) {
        $index_info["banners"][] = explode(',', $index_info["banner{$i}"]);
    } else {
        unset($index_info["banner{$i}"]);
    }
}

include $this->template('shop_list');
?>
