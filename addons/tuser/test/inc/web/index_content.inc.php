<?php

global $_W, $_GPC;
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';

$type = $_GPC['type'] ? $_GPC['type'] : 1;
       
$pageTypes = array(1=>'首页',2=>'积分列表页');

$index_info = pdo_fetch($index_info_sql);
$banner_num = 6;
$adv_num = 2;
$id = $index_info['id'];
if (!$index_info) {
    $data = array(
        'uniacid' => $_W['uniacid'],
        'type' => $type,
        'create_at' => time(),
    );
    $id = pdo_insert('wxz_shoppingmall_index', $data);
} else {
    for ($i = 1; $i <= $banner_num; $i++) {
        if ($index_info["banner{$i}"]) {

        }
    }

    for ($i = 1; $i <= $adv_num; $i++) {
        if ($index_info["adv{$i}"]) {
            $index_info["adv{$i}_arr"] = explode(',', $index_info["adv{$i}"]);
        }
    }
}

load()->web('tpl');

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'banner1' => $_GPC['banner1'] ? $_GPC['banner1'] . ',' . $_GPC['banner1_url'] : '',
        'banner2' => $_GPC['banner2'] ? $_GPC['banner2'] . ',' . $_GPC['banner2_url'] : '',
        'banner3' => $_GPC['banner3'] ? $_GPC['banner3'] . ',' . $_GPC['banner3_url'] : '',
        'banner4' => $_GPC['banner4'] ? $_GPC['banner4'] . ',' . $_GPC['banner4_url'] : '',
        'banner5' => $_GPC['banner5'] ? $_GPC['banner5'] . ',' . $_GPC['banner5_url'] : '',
        'banner6' => $_GPC['banner6'] ? $_GPC['banner6'] . ',' . $_GPC['banner6_url'] : '',
        'adv1' => $_GPC['adv1'] ? $_GPC['adv1'] . ',' . $_GPC['adv1_url'] : '',
        'adv2' => $_GPC['adv2'] ? $_GPC['adv2'] . ',' . $_GPC['adv2_url'] : '',
        'shop_ids' => $_GPC['shop_ids'],
        'shop_activity_ids' => $_GPC['shop_activity_ids'],
        'update_at' => time(),
    );

    if (pdo_update('wxz_shoppingmall_index', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('index_content'));
    } else {
        message('更新失败', $this->createWebUrl('index_content'));
    }
}

include $this->template('web/index_content');
?>
