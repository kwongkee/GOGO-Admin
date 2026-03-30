<?php

global $_W, $_GPC;

$id = $_GPC['id'];
$info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_share') . " WHERE id={$id}";
$info = pdo_fetch($info_sql);

if (!$info) {
    message('记录不存在', $this->createWebUrl('share_setting_list'));
}

load()->web('tpl');

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'title' => $_GPC['title'],
        'img' => $_GPC['img'],
        'link' => $_GPC['link'],
        'desc' => $_GPC['desc'],
    );

    if (pdo_update('wxz_shoppingmall_share', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('share_setting_list'));
    } else {
        message('更新失败', $this->createWebUrl("share_setting_edit", array("id" => $info["id"])));
    }
}
//获取所有商铺
$sql = "SELECT id,name FROM " . tablename('wxz_shoppingmall_shop') . " WHERE isdel=0";
$shops = pdo_fetchall($sql, $pars);
include $this->template('web/share_setting_edit');
?>
