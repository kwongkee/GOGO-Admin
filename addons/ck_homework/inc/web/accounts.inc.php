<?php

defined('IN_IA') or exit('Access Denied');

global $_W, $_GPC;

load()->func('tpl');
$op = $_GPC['op'];

$urlt = $this->createWebUrl('accounts');

$newtimes = time();

//删除
if($op == 'delete'){
	$id = intval($_GPC['id']);
	if($id){
		pdo_delete('onljob_accounts', array('id' => $id,'weid' => $_W['uniacid']));
		message('删除成功', $urlt, 'success');
	}else{
		message('删除失败', $urlt, 'error');
	}
}

//批量处理
$optype_arr = array('1'=>'删除');
$optype_json = json_encode($optype_arr);
if (checksubmit('listsubmit')){
	$optype = !empty($_GPC['optype'])?$_GPC['optype']:'';
	if($_GPC['ids'] && is_array($_GPC['ids']) && $optype) {
		$ids = $_GPC['ids'];
		$ids_count = count($ids);
		switch ($optype) {
			case '1'://删除
				for($i=0;$i<$ids_count;$i++){
					pdo_delete('onljob_accounts', array('id' => $ids[$i],'weid' => $_W['uniacid']));
				}
				break;
		}
		message('批量处理成功', $urlt, 'success');
	}else{
		message('批量处理失败', $urlt, 'error');
	}
}

//列表--------------------
$pindex = max(1, intval($_GPC['page']));
$psize = 12;

$where = '';

$accounttype = !empty($_GPC['accounttype'])?$_GPC['accounttype']:0;
$accounttype_arr = array('0'=>'不限','1'=>'支出','2'=>'收入');
$accounttype_selected[$accounttype] = 'selected="selected"';
if (!empty($accounttype)) {
	$where .= " AND a.accounttype = '".$accounttype."'";
}

$moneytype = !empty($_GPC['moneytype'])?$_GPC['moneytype']:0;
$moneytype_arr = array('0'=>'不限','zsd'=>'购买知识点','topup'=>'充值余额','vip'=>'续费/购买VIP','qjl'=>'上传题目','class'=>'购买加入班级');
$moneytype_selected[$moneytype] = 'selected="selected"';
if (!empty($moneytype)) {
	$where .= " AND a.moneytype = '".$moneytype."'";
}


//$days_ago = 60*60*24*29;
$day_last_sec = 60*60*24-1;
if(empty($days_ago)){
	//当月第一天
	$starttimes_str = !empty($_GPC['starttimes'])?$_GPC['starttimes']:date('Y-m', $newtimes);
	$starttimes = strtotime($starttimes_str);
}else{
	//当天的n天前
	$starttimes_str = !empty($_GPC['starttimes'])?$_GPC['starttimes']:date('Y-m-d', $newtimes);
	$starttimes = !empty($_GPC['starttimes'])?strtotime($starttimes_str):strtotime($starttimes_str)-$days_ago;
}

$endtimes_str = !empty($_GPC['endtimes'])?$_GPC['endtimes']:date('Y-m-d', $newtimes);
$endtimes = strtotime($endtimes_str)+$day_last_sec;

$check_times = !empty($_GPC['starttimes'])?1:0;  //!empty($_GPC['check_times'])?$_GPC['check_times']:0;

if(!empty($check_times)){
	$checked = 'checked="checked"';
	$where .= " AND a.dateline >= $starttimes AND a.dateline <= $endtimes";
	$name_times = $starttimes_str==$endtimes_str?$starttimes_str:$starttimes_str.'至'.$endtimes_str;
}


$list = pdo_fetchall("SELECT a.*,b.name FROM " . tablename('onljob_accounts') . " AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where} ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
$total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_accounts') . " AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where}");
$pager = pagination($total, $pindex, $psize);

include $this->template('accounts');
?>