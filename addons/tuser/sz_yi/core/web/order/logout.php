<?php
// Ä£¿éLTDÌá¹©
defined('IN_IA') || exit('Access Denied');
isetcookie('__session', '', -10000);
$forward = $_GPC['forward'];

if (empty($forward)) {
	$forward = './?refersh';
}

header('Location:../app/index.php?c=entry&do=shop&m=sz_yi&p=login');

?>
