<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.func.php";
$templateurl = "../addons/{$_GPC['m']}/template/mobile/";

$urltk = $this->createMobileUrl('index');

load()->model('app');

unset($_SESSION);
session_destroy();
isetcookie('logout', 1, 60);

message_app('退出登录成功！', array($urltk), 'success');


