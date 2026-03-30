<?php

header('Location: ' . $this->createMobileUrl('award_list'));
exit;

//兑换奖品
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$award_id = $_GPC['id'];

$award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_award') . " WHERE id={$award_id} AND uniacid={$_W['uniacid']}";
$award_info = pdo_fetch($award_info_sql);
if (!$award_info) {
    message("奖品不存在", $this->createMobileUrl('award_list_credit'));
}

//未兑换
if ($award_info['num'] <= 0) {
    message("奖品已发完", $this->createMobileUrl('award_list_credit'));
}

if ($user['left_credit'] < $award_info['credit']) {
    message("积分不足", $this->createMobileUrl('award_list_credit'));
}

$insert_exchange_data = array(
    'uniacid' => $_W['uniacid'],
    'fans_id' => $user['uid'],
    'award_credit_id' => $award_id,
    'type' => 2,
    'event_desc' => "兑换积分商品-{$award_info['credit']}积分",
    'event_type' => 1,
    'num' => $award_info['credit'],
    'create_at' => time(),
);

pdo_insert('wxz_shoppingmall_credit_log', $insert_exchange_data);

//更新用户积分
$update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set left_credit=left_credit-{$award_info['credit']},use_credit=use_credit+{$award_info['credit']} where uid={$user['uid']}"; //消耗积分
$ret = pdo_query($update_mem);

if ($ret) {
    //更新库存
    $update_award = "UPDATE  " . tablename('wxz_shoppingmall_award') . " set num=num-1,cashed=cashed+1 where id={$award_info['id']}"; //消耗积分
    $ret2 = pdo_query($update_award);
}

if ($ret2) {
    message("兑换成功", $this->createMobileUrl('award_list_credit'));
} else {
    message("兑换失败", $this->createMobileUrl('award_list_credit'));
}
?>
