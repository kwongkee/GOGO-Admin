<?php

/**
 * 添加自定义分享
 */
global $_W, $_GPC;

load()->web('tpl');

if (checksubmit()) {
    if (!$_GPC['type']) {
        message('标签不能为空', $this->createWebUrl("share_setting_add"));
    }
    $info_sql = "SELECT id FROM " . tablename('wxz_shoppingmall_share') . " WHERE uniacid={$_W['uniacid']} AND type='{$_GPC['type']}'";
    $info = pdo_fetch($info_sql);

    if ($info) {
        message('自定义分享标签已存在', $this->createWebUrl("share_setting_edit", array("id" => $info["id"])));
    }

    //字段验证, 并获得正确的数据$dat
    $data = array(
        'title' => $_GPC['title'],
        'type' => $_GPC['type'],
        'uniacid' => $_W['uniacid'],
        'img' => $_GPC['img'],
        'link' => $_GPC['link'],
        'desc' => $_GPC['desc'],
        'create_at' => time(),
    );
    if (pdo_insert('wxz_shoppingmall_share', $data)) {
        message('添加成功', $this->createWebUrl('share_setting_list'));
    } else {
        message('添加失败', $this->createWebUrl('share_setting_add'));
    }
}

include $this->template('web/share_setting_add');
?>
