<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "t_common.php";

//编码
$urltk = $this->createMobileUrl('t_knowledgebc');
$urlt = $this->createMobileUrl('knowledgesoso');

//知识点分类
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' ORDER BY listorder ASC, cid DESC", array(), 'cid');
if (!empty($category)) {
	$children = '';
	foreach ($category as $cid => $cate) {
		if (!empty($cate['pid'])) {
			$children[$cate['pid']][$cate['cid']] = array($cate['cid'], $cate['name']);
		}
	}
}

//保存
if(checksubmit('add_submit')){
	
	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $_W['member']['uid'],
		'parentid' => intval($_GPC['parentid']),
		'type' => intval($_GPC['type']),
		'content' => $_GPC['content'],
		'addtime' => time()
	);
	   
	$result = pdo_insert('onljob_knowledge_message', $data);
	if (!empty($result)) {
		message_app('保存成功！', array($urltk), 'success');
	}else{
		message_app('保存失败！', array($urltk), 'error');
	}

}

include template_app('t_knowledgebc');
?>