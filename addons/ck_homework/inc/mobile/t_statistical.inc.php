<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "t_common.php";

//班级总数
$total_bjnum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}'");

//上传题目总数
$total_qnum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}'");

//学生总人数
$total_xsnum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass_apply')." WHERE weid = '{$_W['uniacid']}' and tuid = '{$_W['member']['uid']}' and state = '1' ");

//布置作业总数
$total_zynum = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' ");

include template_app('t_statistical');
?>