<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";

$id = intval($_GPC['id']);
$srdb = pdo_get('onljob_menu', array('weid' => $_W['uniacid'],'id' => $id));
if (empty($srdb)) {
	message_app('参数错误！访问失败！', '', 'error');
}

include $this->template('page_show');
?>