<?php

/**
 * 
 * 短信测试
 * 
 */
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}
$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

$sql = "SELECT * FROM " . tablename('wxz_shoppingmall_fans') . "  ORDER BY `credit` DESC limit 10";
$list = pdo_fetchall($sql, $pars);

//获取我的排名
$sql = "SELECT (
SELECT COUNT(*) FROM " . tablename('wxz_shoppingmall_fans') . " WHERE k.credit<=credit OR (k.credit=credit)) AS rank
FROM " . tablename('wxz_shoppingmall_fans') . " k
WHERE uid={$user['uid']};";
$myrand = pdo_fetch($sql);

include $this->template('credit_rank');
?>
