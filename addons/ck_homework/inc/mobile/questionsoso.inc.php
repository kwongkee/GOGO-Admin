<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";

$where = '';

$last = empty($_GPC['last'])?0:intval($_GPC['last']);
$amount = empty($_GPC['amount'])?1:intval($_GPC['amount']);
$typeid = empty($_GPC['typeid'])?0:intval($_GPC['typeid']);
$parentid = empty($_GPC['parentid'])?0:intval($_GPC['parentid']);

if (!empty($typeid)) {     $where .= " AND type = '{$typeid}'";}
if (!empty($parentid)) {     $where .= " AND parentid = '{$parentid}'";}

//编码
$list = pdo_fetchall("SELECT id,titlename FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' {$where} ORDER BY id DESC limit $last,$amount");
foreach($list as $key=>$value){
	$return[$key] = array($this->createMobileUrl('knowledge', array('op'=>'showq','parentid'=>$value['parentid'],'id'=>$value['id'])), html_entity_decode($value['titlename']));
}

if($return){
	echo json_encode(array('msg'=>'ok','info'=>$return));
	exit();
}else{
	echo json_encode(array('msg'=>'error','info'=>$return));
	exit();
}

?>