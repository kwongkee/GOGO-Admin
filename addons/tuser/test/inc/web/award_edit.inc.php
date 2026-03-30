<?php

global $_W, $_GPC;

$id = $_GPC['id'];
$award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_award') . " WHERE id={$id}";
$award_info = pdo_fetch($award_info_sql);
if (!$award_info) {
    message('奖品不存在', $this->createWebUrl('award_manage'));
}
load()->web('tpl');
$_GPC['total_num'] = $_GPC['total_num'] > 0 ? intval($_GPC['total_num']) : 0;
$_GPC['credit'] = $_GPC['credit'] > 0 ? intval($_GPC['credit']) : 0;
if (checksubmit()) {
    //字段验证, 并获得正确的数据$dat
    $data = array(
        'name' => $_GPC['name'],
        'img' => $_GPC['img'],
        'desc' => $_GPC['desc'],
        'total_num' => $_GPC['total_num'],
        'credit' => $_GPC['credit'],
        'max_exchange_num' => $_GPC['max_exchange_num'],
        'password' => $_GPC['password'],
        'expiry_date_start' => $_GPC['expiry_date_start'],
        'expiry_date_end' => $_GPC['expiry_date_end'],
    );

    if ($_GPC['total_num'] < $award_info['cashed']) {
        message('总数量不能小于已兑换数量', $this->createWebUrl('award_edit', array('id' => $id)));
    }

    //加库存
    if ($_GPC['total_num'] > $award_info['total_num']) {
        $data['num'] = $award_info['num'] + ($_GPC['total_num'] - $award_info['total_num']);
    } else {
        //减库存
        $data['num'] = $award_info['num'] - ($award_info['total_num'] - $_GPC['total_num']);
    }

    if (pdo_update('wxz_shoppingmall_award', $data, array('id' => $id))) {
        message('更新成功', $this->createWebUrl('award_manage'));
    } else {
        message('更新失败', $this->createWebUrl('award_add'));
    }
}

include $this->template('web/award_edit');
?>
