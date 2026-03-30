<?php

global $_W, $_GPC;

if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'name' => $_GPC['name'],
        'uniacid' => $_W['uniacid'],
        'img' => $_GPC['img'],
        'desc' => $_GPC['desc'],
        'num' => $_GPC['total_num'],
        'total_num' => $_GPC['total_num'],
        'password' => $_GPC['password'],
        'expiry_date_start' => $_GPC['expiry_date_start'],
        'expiry_date_end' => $_GPC['expiry_date_end'],
        'create_at' => time(),
    );
    if (pdo_insert('wxz_shoppingmall_award', $data)) {
        message('添加成功', $this->createWebUrl('award_manage'));
    } else {
        message('添加失败', $this->createWebUrl('award_add'));
    }
}
load()->web('tpl');
include $this->template('web/award_add');
?>
