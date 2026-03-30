<?php

/**
 * 
 * 短信测试
 * 
 */
global $_W, $_GPC;
echo '123123';die;
$_W['module_setting'] = $this->module['config'];
include dirname(__FILE__) . '/permission.php';

$modulePublic = '../addons/' . $_GPC['m'] . '/static/';
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
require_once WXZ_SHOPPINGMALL . '/source/Page.class.php';
$pageContents = Page::getPage(array(5, 6));
$levels = Fans::getLevels();
include $this->template('index');
?>
