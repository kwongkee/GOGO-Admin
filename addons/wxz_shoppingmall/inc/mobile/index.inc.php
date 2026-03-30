<?php

/**
 * 
 * 短信测试
 * 
 */

global $_W, $_GPC;
$_W['module_setting'] = $this->module['config'];


include dirname(__FILE__) . '/permission.php';
// echo '<pre>';
//print_r(dirname(__FILE__));die;
// $_GPC['M'] = wxz_shoppingmall;
$modulePublic = '../addons/' . $_GPC['m'] . '/static/';
// WXZ_SHOPPINGMALL = /home/wwwroot/default/addons/wxz_shoppingmall
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
require_once WXZ_SHOPPINGMALL . '/source/Page.class.php';
$pageContents = Page::getPage(array(5, 6));
$levels = Fans::getLevels();
include $this->template('index');
?>
