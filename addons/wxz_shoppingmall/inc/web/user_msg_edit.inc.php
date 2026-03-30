<?php

global $_W, $_GPC;

$id = $_GPC['id'];
$info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_msg') . " WHERE id={$id}";
$info = pdo_fetch($info_sql);

if (!$info) {
    message('消息不存在', $this->createWebUrl('user_msg_list'));
}

load()->web('tpl');

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'title' => $_GPC['title'],
        'desc' => $_GPC['desc'],
    );

    if (pdo_update('wxz_shoppingmall_msg', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('user_msg_list'));
    } else {
        message('更新失败', $this->createWebUrl('user_msg_edit', array('id' => $id)));
    }
}

include $this->template('web/user_msg_edit');
?>
