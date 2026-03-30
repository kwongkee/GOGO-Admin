<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;

require "common.php";

$urltk = $this->createMobileUrl('notice');

$op = $_GPC['op'];

if($op == 'show'){

	//显示
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_notice', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('参数错误！访问失败！', '', 'error');
	}
	
	//内容
	$message = html_entity_decode($srdb['message']);
	
	$srdb['hot'] = $srdb['hot'] + 1;
	
	//存入浏览次数
	pdo_query("UPDATE ".tablename('onljob_notice')." SET hot=hot+1 WHERE weid = '{$_W['uniacid']}' and id = '".$id."'");
	
	include template_app('notice');

}
	