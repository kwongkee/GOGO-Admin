<?php
// ģ��LTD�ṩ
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
@session_start();
$cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];
setcookie($cookieid, '', time() - 1);
$_COOKIE[$cookieid] = '';
$url = $this->createMobileUrl('shop');
redirect($url);

?>
