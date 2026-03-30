<?php

//活动奖品列表
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$condition = '`uniacid`=:uniacid AND `fans_id` = :fans_id';
$pars = array();
$pars[':uniacid'] = $_W['uniacid'];
$pars[':fans_id'] = $user['uid'];

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_exchange') . " WHERE {$condition} ORDER BY `id` DESC";
$list = pdo_fetchall($sql, $pars);

foreach ($list as $i => $_row) {
    $award_info_sql = "SELECT * FROM " . tablename('wxz_shoppingmall_award') . " WHERE id={$_row['award_id']}";
    $award_info = pdo_fetch($award_info_sql);
    $list[$i]['award_info'] = $award_info;
}
include $this->template('my_award_list');
?>
