<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";

$op = empty($_GPC['op'])?0:intval($_GPC['op']);
$bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);
$getmonth = trim($_GPC['getmonth']);

$urlt = $this->createMobileUrl('t_theclass_tongji') . '&bjid=' . $bjid;
$urltk = $this->createMobileUrl('t_theclass_tongji') . '&bjid=' . $bjid . '&op=' . $op;

//判断班级
$srdb = pdo_get('onljob_theclass', array('id' => $bjid,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
if (empty($srdb)) {
	message_app('参数错误！访问失败！', '', 'error');
}

$datatimes = time();

$month_all = array('1'=>'01','2'=>'02','3'=>'03','4'=>'04','5'=>'05','6'=>'06','7'=>'07','8'=>'08','9'=>'09','10'=>'10','11'=>'11','12'=>'12');
$month_arr = array('0'=>'12','-1'=>'11','-2'=>'10','-3'=>'09','-4'=>'08','-5'=>'07');

$year = date("Y", $datatimes);
$month = date("m", $datatimes);
$day = date("d", $datatimes);

$month_list = '';
for($i=5; $i > 0; $i--){
	$next_month = $month - $i;
	if($next_month < 1){
		$month_list[] = array($next_month, $month_arr[$next_month]);
	}else{
		$month_list[] = array($next_month, $month_all[$next_month]);
	}
}

$month_list[] = array(intval($month), $month);
if($getmonth == ''){
	$getmonth = intval($month);
}

//统计时间段----------
if($getmonth < 1){
	$year = $year - 1;
	$month = $month_arr[$getmonth];
}else{
	$month = $month_all[$getmonth];
}

$BeginDate = date('Y-m-01', strtotime($year.'-'.$month.'-01'));
$tongjidate = date('Y年m月01日', strtotime($year.'-'.$month.'-01')) . ' - ' . date('Y年m月d日', strtotime("$BeginDate +1 month -1 day"));
//-------------------

//当前月份
$news_month = $year . $month;
//相应的上个月份
$on_month_ca = $month - 1;
if($on_month_ca < 1){
	$on_year = $year - 1;
	$on_month = $month_arr[$on_month_ca];
}else{
	$on_year = $year;
	$on_month = $month_all[$on_month_ca];
}
$news_month_on = $on_year . $on_month;

//学生人数
$total_xuesheng = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass_apply')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and state = '1'");

if($op == 1){

	/*知识点统计**************
	**********/
	//当月总知识点
	$list = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}') GROUP BY parentid ");
	foreach ($list as $cid => $cate) {
		$zsd_all[] = $cate['parentid'];
	}
	$total_zsd = count($zsd_all);
	
	//当月新掌握知识点
	$list = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}' and stateh = '0') GROUP BY parentid ");
	foreach ($list as $cid => $cate) {
		$zsd_zw_all[] = $cate['parentid'];
	}
	$total_zsd_zw = count($zsd_zw_all);
	
	//上个月新掌握知识点-----------
	$list_on = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month_on}' and stateh = '0') GROUP BY parentid ");
	foreach ($list_on as $cid => $cate) {
		$zsd_zw_all_on[] = $cate['parentid'];
	}
	$total_zsd_zw_on = count($zsd_zw_all_on);
	
	/*同比上月*/
	$zsd_numbcha = $total_zsd_zw - $total_zsd_zw_on;
	if($zsd_numbcha < 0){
		$liftll_zsd = '↓';
	}else{
		$liftll_zsd = '↑';
	}
	$rate_month_zsd = $total_zsd_zw_on==0?0:$liftll_zsd . round(abs($zsd_numbcha)/$total_zsd_zw_on, 2) * 100 . '%';
	//---------------
	
	//当月未掌握知识点
	$list = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}' and stateh = '1') GROUP BY parentid ");
	foreach ($list as $cid => $cate) {
		$zsd_wzw_all[] = $cate['parentid'];
	}
	$total_zsd_wzw = count($zsd_wzw_all);
	
	//上个月新掌握知识点-----------
	$list_on1 = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month_on}' and stateh = '1') GROUP BY parentid ");
	foreach ($list_on1 as $cid => $cate) {
		$zsd_wzw_all_on[] = $cate['parentid'];
	}
	$total_zsd_wzw_on = count($zsd_wzw_all_on);
	/*同比上月*/
	$zsd_numbcha_wzw = $total_zsd_wzw - $total_zsd_wzw_on;
	if($zsd_numbcha_wzw < 0){
		$liftll_zsd_wzw = '↓';
	}else{
		$liftll_zsd_wzw = '↑';
	}
	$rate_month_zsd_wzw = $total_zsd_wzw_on==0?0:$liftll_zsd_wzw . round(abs($zsd_numbcha_wzw)/$total_zsd_wzw_on, 2) * 100 . '%';
	//---------------
	
	//累计为掌握知识点
	$list = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '0') GROUP BY parentid ");
	foreach ($list as $cid => $cate) {
		$zsd_zs_all[] = $cate['parentid'];
	}
	$total_zsd_zs = count($zsd_zs_all);
	
	//历史累计知识点
	$list = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') < '{$news_month}') GROUP BY parentid ");
	foreach ($list as $cid => $cate) {
		$zsd_zsls_all[] = $cate['parentid'];
	}
	$total_zsdls_zs = count($zsd_zsls_all);
	
	//历史未掌握知识点
	$list = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '1' and FROM_UNIXTIME(dateline,'%Y%m') < '{$news_month}') GROUP BY parentid ");
	foreach ($list as $cid => $cate) {
		$zsd_zswzw_ls_all[] = $cate['parentid'];
	}
	$total_zsd_zsls_wzw = count($zsd_zswzw_ls_all);
	
	//上个月历史未掌握知识点
	$list = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '1' and FROM_UNIXTIME(dateline,'%Y%m') < '{$news_month_on}') GROUP BY parentid ");
	foreach ($list as $cid => $cate) {
		$zsd_zswzw_ls_all_on[] = $cate['parentid'];
	}
	$total_zsd_zsls_wzw_on = count($zsd_zswzw_ls_all_on);
	
	//知识点数据分析--------------
	//历史未掌握知识点减少
	$lswzw_zsd_js = 0;
	if (!empty($total_zsd_zsls_wzw)){
        foreach ($zsd_zw_all as $k => $value) {
            if(@in_array($value, $total_zsd_zsls_wzw)){
                $lswzw_zsd_js = $lswzw_zsd_js + 1;
            }
        }
    }

	
	/******************/
	//上个月历史未掌握知识点减少
	$lswzw_zsd_js_on = 0;
	if (!empty($total_zsd_zsls_wzw_on)){
        foreach ($zsd_zw_all_on as $k => $value) {
            if(@in_array($value, $total_zsd_zsls_wzw_on)){
                $lswzw_zsd_js_on = $lswzw_zsd_js_on + 1;
            }
        }
	}

	/*同比上月*/
	$zsd_numbcha_wzwjs = $lswzw_zsd_js - $lswzw_zsd_js_on;
	if($zsd_numbcha_wzwjs < 0){
		$liftll_zsd_wzwjs = '↓';
	}else{
		$liftll_zsd_wzwjs = '↑';
	}
	$rate_month_zsd_wzwjs = $lswzw_zsd_js_on==0?0:$liftll_zsd_wzwjs . round(abs($zsd_numbcha_wzwjs)/$lswzw_zsd_js_on, 2) * 100 . '%';
	/****************/
	
	//本月未掌握率
	$errorrate_month_zsd = $total_zsd==0?0:round($total_zsd_wzw/$total_zsd, 2) * 100 . '%';
	/*未掌握率同比上月*/
	$zsd_numbcha_wzwbl = $total_zsd_wzw - $total_zsd_wzw_on;

	if($zsd_numbcha_wzwbl < 0){
		$liftll_zsd_wzwbl = '↓';
	}else{
		$liftll_zsd_wzwbl = '↑';
	}
	$errorrate_month_zsd_on = $total_zsd_wzw_on==0?0:$liftll_zsd_wzwbl . round(abs($zsd_numbcha_wzwbl)/$total_zsd_wzw_on, 2) * 100 . '%';
	
	//历史未掌握率
	$errorrate_month_zsd_ls = $total_zsdls_zs==0?0:round($total_zsd_zsls_wzw/$total_zsdls_zs, 2) * 100 . '%';
	/*历史未掌握率同比上月*/
	$zsd_numbcha_wzwls = $total_zsd_zsls_wzw - $total_zsd_zsls_wzw_on;
	if($zsd_numbcha_wzwls < 0){
		$liftll_zsd_wzwls = '↓';
	}else{
		$liftll_zsd_wzwls = '↑';
	}
	$errorrate_month_zsd_ls_on = $total_zsd_zsls_wzw_on==0?0:$liftll_zsd_wzwls . round(abs($zsd_numbcha_wzwls)/$total_zsd_zsls_wzw_on, 2) * 100 . '%';
	
	//--------------------------
	
	/*近六月学习状况走势图*/
	$xz_q = '';
	$wzw_q = '';
	$ls_q = '';
	$lswzw_q = '';
	foreach ($month_list as $k => $value) {
		
		if($value[0] < 1){
			$news_monthq = ($year - 1) . $month_arr[$value[0]];
		}else{
			$news_monthq = $year . $month_all[$value[0]];
		}
		
		//新增知识点
		$list_x = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_monthq}') GROUP BY parentid ");
		$xq_all = array();
		foreach ($list_x as $cid => $cate) {
			$xq_all[] = $cate['parentid'];
		}
		$total_xq = count($xq_all);
		$xz_q .= $total_xq . ',';
		
		//新增未掌握
		$list_w = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '1' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_monthq}') GROUP BY parentid ");
		$wq_all = array();
		foreach ($list_w as $cid => $cate) {
			$wq_all[] = $cate['parentid'];
		}
		$total_wq = count($wq_all);
		$wzw_q .= $total_wq . ',';
		
		//历史累计知识点
		$list_ls = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') < '{$news_monthq}') GROUP BY parentid ");
		$ls_all = array();
		foreach ($list_ls as $cid => $cate) {
			$ls_all[] = $cate['parentid'];
		}
		$total_ls = count($ls_all);
		$ls_q .= $total_ls . ',';
		
		//历史未掌握
		$list_lsw = pdo_fetchall("SELECT parentid FROM ".tablename('onljob_questions')." WHERE weid = '{$_W['uniacid']}' and id in (SELECT qid FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '1' and FROM_UNIXTIME(dateline,'%Y%m') < '{$news_monthq}') GROUP BY parentid ");
		$lswq_all = array();
		foreach ($list_lsw as $cid => $cate) {
			$lswq_all[] = $cate['parentid'];
		}
		$total_lswq = count($lswq_all);
		$lswzw_q .= $total_lswq . ',';
	}
	
	
	//全部会员排名
	$user_paiming = 0;
	$list_userpm = pdo_fetchall("SELECT a.uid,b.z_fen FROM ".tablename('onljob_user')." AS a LEFT JOIN (SELECT uid,sum(stateh) AS z_fen FROM ".tablename('onljob_work_answer')." where weid = '{$_W['uniacid']}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}' and stateh = '1' GROUP BY uid) AS b ON a.uid=b.uid  WHERE a.weid = '{$_W['uniacid']}' and a.type = '0' ORDER BY b.z_fen ASC,a.id DESC ");
	foreach ($list_userpm as $k => $value) {
		$k = $k + 1;
		if($value['uid'] == $_W['member']['uid']){
			$user_paiming = $k;
			break;
		}
	}
	$total_user = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_user')." WHERE weid = '{$_W['uniacid']}' and type = '0'");
	$praiming_qb = round(($total_user - $user_paiming)/$total_user, 2) * 100 . '%';
	/****************/
	
}else{

	/*错题统计**************
	**********/
	
	//当月做题总数
	$total_wa_month = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}' ");
	$total_month = $total_wa_month;
	
	//上个月做题总数
	$total_month_on = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month_on}' ");
	/*做题总数同比上月*/
	$zs_numbcha = $total_month - $total_month_on;
	if($zs_numbcha < 0){
		$liftll_zs = '↓';
	}else{
		$liftll_zs = '↑';
	}
	$rate_month_tbzn = $total_month_on==0?0:$liftll_zs . round(abs($zs_numbcha)/$total_month_on, 2) * 100 . '%';
	
	//当月错题数
	$total_wa_month_cw = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '1' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}' ");
	$total_month_cw = $total_wa_month_cw;
	
	//上个月错题数
	$total_month_cw_on = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '1' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month_on}' ");
	/*做题总数同比上月*/
	$cw_numbcha = $total_month_cw - $total_month_cw_on;
	if($cw_numbcha < 0){
		$liftll_cw = '↓';
	}else{
		$liftll_cw = '↑';
	}

	$rate_month_tbcw = $total_month_cw_on==0?0:$liftll_cw . round(abs($cw_numbcha)/$total_month_cw_on, 2) * 100 . '%';
	
	//截止当前做题数
	$total_wa = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' ");
	$total_zq = $total_wa;
	
	//当月对题数
	$total_wa_month_zq = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '0' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}' ");
	
	//题型分布-----------
	//选择题
	$total_xzt = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_month}' and b.type < 3 ");
	
	//判断题
	$total_pdt = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_month}' and b.type = '4' ");
	
	//填空题
	$total_tkt = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_month}' and b.type = '3' ");
	
	//简答题
	$total_jdt = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_month}' and b.type = '5' ");
	
	//作文题
	$total_zwt = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_month}' and b.type = '6' ");
	//------------------
	
	
	//错题数据分析--------------
	/*错误率*/
	$errorrate_month = $total_month==0?0:round($total_month_cw/$total_month, 2) * 100 . '%';
	/*错误率同比上月*/
	$cw_numbcha = $total_month_cw - $total_month_cw_on;
	if($cw_numbcha < 0){
		$liftll = '↑';
	}else{
		$liftll = '↓';
	}
	$errorrate_month_on = $total_month_cw_on==0?0:$liftll . round(abs($cw_numbcha)/$total_month_cw_on, 2) * 100 . '%';
	
	 
	//本月班级未做题
	/*作业数*/
	$total_work = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}' ");
	/*交作业数*/
	$total_work_fen = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_fen')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month}' ");
	$total_work_wzfen = $total_xuesheng * $total_work - $total_work_fen;
	
	//上个月班级未做题
	/*作业数*/
	$total_work_on = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month_on}' ");
	/*交作业数*/
	$total_work_fen_on = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_fen')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_month_on}' ");
	$total_work_wzfen_on = $total_xuesheng * $total_work_on - $total_work_fen_on;
	/*同比上月*/
	$work_wz_numbcha = $total_work_wzfen - $total_work_wzfen_on;
	if($work_wz_numbcha < 0){
		$liftll_work_wz = '↓';
	}else{
		$liftll_work_wz = '↑';
	}
	$rate_month_tbwz = $total_work_wzfen_on==0?0:$liftll_work_wz . round(abs($work_wz_numbcha)/$total_work_wzfen_on, 2) * 100 . '%';
	////////////////
	
	//班级未完成率
	$bjwwcrate_month = round($total_work_wzfen/$total_xuesheng, 2) * 100 . '%';
	
	/*同比上月*/
	$work_wzk_numbcha = $total_work_wzfen - $total_work_wzfen_on;
	if($work_wzk_numbcha < 0){
		$liftll_work_wzk = '↓';
	}else{
		$liftll_work_wzk = '↑';
	}
	$rate_month_tbwzk =$total_work_wzfen_on==0?0: $liftll_work_wzk . round(abs($work_wzk_numbcha)/$total_work_wzfen_on, 2) * 100 . '%';
	
	//------------------------
	
	
	/*近六月学习状况走势图*/
	$wrong_q = '';
	$zuoti_q = '';
	$zhuguan_q = '';
	$zhuguan_cw_q = '';
	$xuanzhe_q = '';
	$xuanzhe_cw_q = '';
	$tiankong_q = '';
	$tiankong_cw_q = '';
	foreach ($month_list as $k => $value) {
		
		if($value[0] < 1){
			$news_monthq = ($year - 1) . $month_arr[$value[0]];
		}else{
			$news_monthq = $year . $month_all[$value[0]];
		}
		
		//错题
		$total_month_cwq = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and stateh = '1' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_monthq}' ");
		$wrong_q .= $total_month_cwq . ',';
		
		//做题量
		$total_monthq = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$bjid}' and FROM_UNIXTIME(dateline,'%Y%m') = '{$news_monthq}' ");
		$zuoti_q .= $total_monthq . ',';
		
		//主观
		$total_zhuguan = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_monthq}' and b.type = '5' ");
		$zhuguan_q .= $total_zhuguan . ',';
		
		//主观错
		$total_zhuguan_cw = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and a.stateh = '1' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_monthq}' and b.type = '5' ");
		$zhuguan_cw_q .= $total_zhuguan_cw . ',';
		
		//选择
		$total_xuanzhe = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_monthq}' and b.type < '3' ");
		$xuanzhe_q .= $total_xuanzhe . ',';
		
		//选择错
		$total_xuanzhe_cw = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and a.stateh = '1' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_monthq}' and b.type < '3' ");
		$xuanzhe_cw_q .= $total_xuanzhe_cw . ',';
		
		//填空
		$total_tiankong = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_monthq}' and b.type = '3' ");
		$tiankong_q .= $total_tiankong . ',';
		
		//填空错
		$total_tiankong_cw = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$bjid}' and a.stateh = '1' and FROM_UNIXTIME(a.dateline,'%Y%m') = '{$news_monthq}' and b.type = '3' ");
		$tiankong_cw_q .= $total_tiankong_cw . ',';
	}
	/****************/
}





include template_app('t_theclass_tongji' . $op);	