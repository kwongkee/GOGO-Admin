<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "m_common.php";

//未完成的作业
$list_word = pdo_fetchall("SELECT a.* FROM ".tablename('onljob_work')." AS a LEFT JOIN ".tablename('onljob_theclass_apply')." AS b ON a.bjid = b.bjid LEFT JOIN ".tablename('onljob_user')." AS c ON a.uid = c.uid WHERE a.weid = '{$_W['uniacid']}' and a.releaset = '1' and b.uid = '{$_W['member']['uid']}' ORDER BY a.wid DESC ");
$total_workwz = 0;
if (!empty($list_word)) {
	foreach ($list_word as $k => $value) {
		$xs_fenf = pdo_get('onljob_work_fen', array('uid' => $_W['member']['uid'],'wid' => $value['wid'],'bjid' => $value['bjid'],'weid' => $_W['uniacid']));
		if (empty($xs_fenf)) {
			$total_workwz ++;
		}
	}
}
//公告列表
$list_notice = pdo_fetchall("SELECT id,titlename FROM ".tablename('onljob_notice')."  WHERE weid = '{$_W['uniacid']}' ORDER BY listorder ASC,id DESC");
//未掌握的知识点
$parentid_all = array();
$list = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and stateh = '1') GROUP BY parentid ");
foreach ($list as $cid => $cate) {
	$parentid_all[] = $cate['parentid'];
}
$total_zsdw = count($parentid_all);

//今日错题数
$newsday = date("Ymd", time());
$total_ctnews = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.stateh = '1' and FROM_UNIXTIME(a.dateline,'%Y%m%d') = '{$newsday}' and b.type < 6");

//新提问
$total_tw = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_message')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and showt = '1'");
//幻灯片
$list_slide = pdo_fetchall("SELECT urlt,titlename,imgurl FROM ".tablename('onljob_slide')."  WHERE weid = '{$_W['uniacid']}' and status = '1' ORDER BY sort ASC,id DESC");
include template_app('m_index');
?>