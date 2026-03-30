<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

require "common.php";
require "public.php";

$urltk = $this->createMobileUrl('notice_show');

//显示
$id = empty($_GPC['id'])?0:intval($_GPC['id']);
$srdb = pdo_get('onljob_theclass_notice', array('id' => $id,'weid' => $_W['uniacid']));
if (empty($srdb)) {
	message_app('参数错误！访问失败！', '', 'error');
}

//内容
$infotext = html_entity_decode($srdb['infotext']);

//获取姓名
$author = pdo_get('onljob_user', array('uid' => $srdb['uid'],'weid' => $_W['uniacid']));

include template_app('notice_show');
	