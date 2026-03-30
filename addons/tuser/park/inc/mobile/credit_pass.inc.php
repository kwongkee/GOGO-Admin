<?php

//积分转赠
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';
if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}
include $this->template('credit_pass');
?>
