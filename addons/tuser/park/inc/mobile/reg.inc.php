<?php

/**
 * 用户注册
 */
global $_W, $_GPC;
include dirname(__FILE__) . '/permission.php';
$modulePublic = '../addons/' . $_GPC['m'] . '/static/';
if ($user['mobile'] && !$user['birthday']) {
    header('Location: ' . $this->createMobileUrl('reg2'));
    exit;
}
if ($user['mobile']) {
    header('Location: ' . $this->createMobileUrl('index'));
    exit;
}

require_once WXZ_SHOPPINGMALL . '/source/Page.class.php';
$pageContents = Page::getPage(array(7));

include $this->template('reg');
?>

