<?php
// 模块


if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;

if (!$_W['isfounder']) {
	message('无权访问!');
}

echo "升级~"



?>
