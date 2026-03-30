<?php

//小票上传记录
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

$psize = 20;

$start = ($pindex - 1) * $psize;
$condition = "`uniacid`={$_W['uniacid']} AND fans_id={$user['uid']} AND event_type=5";

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_credit_log') . " WHERE {$condition} ORDER BY `create_at` desc limit $psize";
$list = pdo_fetchall($sql, $pars);

include $this->template('user_ticket');
?>
