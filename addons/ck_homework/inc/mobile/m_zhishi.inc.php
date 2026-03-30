<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "m_common.php";


//幻灯片
$list_slide = pdo_fetchall("SELECT urlt,titlename,imgurl FROM ".tablename('onljob_slide')."  WHERE weid = '{$_W['uniacid']}' and status = '1' ORDER BY sort ASC,id DESC");

//导航
$list_menu = pdo_fetchall("SELECT id,turl,titlename,icon_img,lx_type FROM ".tablename('onljob_menu')."  WHERE weid = '{$_W['uniacid']}' and showt = '1' ORDER BY listorder ASC,id DESC");

//公告列表
$list_notice = pdo_fetchall("SELECT id,titlename FROM ".tablename('onljob_notice')."  WHERE weid = '{$_W['uniacid']}' ORDER BY listorder ASC,id DESC");

//热门知识点
$tj_knowledge = pdo_fetchall("SELECT id,titlename,readnum,imgurl,listorder FROM ".tablename('onljob_knowledge')."  WHERE weid = '{$_W['uniacid']}' and state = '1' and tj = '1' ORDER BY listorder ASC,id DESC LIMIT 0,6");
foreach($tj_knowledge as $key=>$value){
	$tj_knowledge[$key]['ztotal'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge_son')."  WHERE weid = '{$_W['uniacid']}' and parentid = '{$value['id']}'");
}

//最新知识点
$new_knowledge = pdo_fetchall("SELECT id,titlename,readnum,imgurl,listorder FROM ".tablename('onljob_knowledge')."  WHERE weid = '{$_W['uniacid']}' and state = '1' ORDER BY id DESC LIMIT 0,6");
foreach($new_knowledge as $key=>$value){
	$new_knowledge[$key]['ztotal'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_knowledge_son')."  WHERE weid = '{$_W['uniacid']}' and parentid = '{$value['id']}'");
}

//作文赏析
$list_zwsxx = pdo_fetchall("SELECT * FROM ".tablename('onljob_news')." WHERE weid = '{$_W['uniacid']}' and type = 'zwsx' {$where} ORDER BY listorder ASC,id DESC LIMIT 0,6");

//语文作文赏析
$list_zwsx_yw = pdo_fetchall("SELECT * FROM ".tablename('onljob_news')." WHERE weid = '{$_W['uniacid']}' and type = 'zwsx' and craid = '2' ORDER BY listorder ASC,id DESC LIMIT 0,6");


include template_app('m_zhishi');
?>