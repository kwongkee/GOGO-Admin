<?php

//拍小票
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';

if (!$user['mobile']) {
    header('Location: ' . $this->createMobileUrl('reg'));
    exit;
}

require_once WXZ_SHOPPINGMALL . '/source/Page.class.php';
$shopingMallTel = Page::getPage(2);

include $this->template('user_upload_ticket');
?>
