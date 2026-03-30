<?php

global $_W, $_GPC;

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'title' => $_GPC['title'],
        'uniacid' => $_W['uniacid'],
        'desc' => $_GPC['desc'],
        'create_at' => time(),
    );
    if (pdo_insert('wxz_shoppingmall_msg', $data)) {
        message('添加成功', $this->createWebUrl('user_msg_list'));
    } else {
        message('添加失败', $this->createWebUrl('user_msg_add'));
    }
}

load()->web('tpl');
include $this->template('web/user_msg_add');
?>
