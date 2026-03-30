<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "t_common.php";

//待批改作业
$total_dgword = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_fen')." AS a LEFT JOIN ".tablename('onljob_work')." AS b ON a.wid = b.wid WHERE a.weid = '{$_W['uniacid']}' and b.uid = '{$_W['member']['uid']}' and a.state = '0'");

//待批改习题
$total_dgxt = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_practice_fen')." AS a LEFT JOIN ".tablename('onljob_work')." AS b ON a.wid = b.wid WHERE a.weid = '{$_W['uniacid']}' and b.uid = '{$_W['member']['uid']}' and a.state = '0' and a.type = '0'");

//出题数量
$total_q = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}'");

include template_app('t_index');
?>