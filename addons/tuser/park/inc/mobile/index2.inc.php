<?php

/**
 * 
 * 短信测试
 * 
 */
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';

$index_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_index') . " WHERE uniacid={$_W['uniacid']} AND type=1 limit 1";
$index_info = pdo_fetch($index_info_sql);

$banner_num = 6;
$adv_num = 2;

for ($i = 1; $i <= $banner_num; $i++) {
    if ($index_info["banner{$i}"]) {
        $index_info["banners"][] = explode(',', $index_info["banner{$i}"]);
    } else {
        unset($index_info["banner{$i}"]);
    }
}

for ($i = 1; $i <= $adv_num; $i++) {
    if ($index_info["adv{$i}"]) {
        $index_info["advs"][] = explode(',', $index_info["adv{$i}"]);
    } else {
        unset($index_info["adv{$i}"]);
    }
}

if ($index_info['shop_activity_ids']) {
    $condition = "id in({$index_info['shop_activity_ids']})";
    $sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop_activity') . " WHERE {$condition}";
    $index_info['shop_activitys'] = pdo_fetchall($sql);
}

if ($index_info['shop_ids']) {
    $condition = "id in({$index_info['shop_ids']})";
    $sql = "SELECT * FROM " . tablename('wxz_shoppingmall_shop') . " WHERE {$condition} order by substring_index('{$index_info['shop_ids']}',id,1)";
    $index_info['shops'] = pdo_fetchall($sql);
}

//页面配置
require_once WXZ_SHOPPINGMALL . '/source/Page.class.php';
$pageContents = Page::getPage(array(1, 2, 3, 4, 9));
$pageContents[3]['desc'] = json_decode($pageContents[3]['desc'], true);
$pageContents[9]['desc'] = json_decode($pageContents[9]['desc'], true);

include $this->template('index2');
?>
