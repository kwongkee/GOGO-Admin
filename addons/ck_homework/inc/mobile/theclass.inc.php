<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W, $_GPC;
session_start();
if (!empty($_GPC['keyword'])) {
	$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
	$_SESSION['back_act'] = $http_type.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}
require "common.php";
require "public.php";
require 'weixin.class.php';
require_once (MODULE_ROOT. "/function/array_col.func.php");

$op = trim($_GPC['op']);

$urltk = $this->createMobileUrl('theclass');
//获取公众号配置信息
$srdm = pdo_get('account_wechats', array('uniacid' => $_W['uniacid']));
$appid = $srdm['key'];
$appsecret = $srdm['secret'];
$access_token_odl = $srdm['token'];
$mb_config = $config;

//科目
$category = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'xk' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//年级
$category_nj = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and pid = '0' and type = 'nj' ORDER BY listorder ASC, cid DESC", array(), 'cid');

//申请加入班级
if($op === 'add'){
	$id = empty($_GPC['id'])?0:intval($_GPC['id']);
	$srdb = pdo_get('onljob_theclass', array('id' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('参数错误！申请加入班级失败！', '', 'error');
	}
	
	//判断是否是学生
	if(empty($user_show) || $user_show['type'] > 0){
		message_app('抱歉！您不是学生不能申请加入！', '', 'error');
	}
	
	//是否已申请
	$srdb_apply = pdo_get('onljob_theclass_apply', array('bjid' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
	if (!empty($srdb_apply) && $srdb_apply['state'] != '2') {
		message_app('抱歉！您已经申请过了，不能再次申请！', '', 'error');
	}
	
	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $_W['member']['uid'],
		'bjid' => $id,
		'tuid' => $srdb['uid'],
		'dateline' => time()
	);
	  
	$result = pdo_insert('onljob_theclass_apply', $data);
	if (!empty($result)) {
		//发送加入班级模板消息---------------------
		if(!empty($mb_config['mb_open']) && !empty($appid) && !empty($appsecret)){		
			$access_token = moban($appid,$appsecret,$access_token_odl,$uniacid);
			//获取openid
			$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$srdb['uid'],'follow' => 1));	
			$first = "您好，有新学生申请加入你创建的班级啦！请点击查看！";
			$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=".$_GPC['c']."&m=".$_GPC['m']."&do=t_theclass&op=2";
			$addDateTime = date('Y-m-d H:i:s',time());
			$template = array(
				'touser'=> trim($user_openid['openid']),
				'template_id'=> trim($mb_config['mbid5']), 
				'url'=> $url,
				'topcolor'=>"#FF0000",
				'data'=>array(
				'first'=>array('value'=>urlencode($first),'color'=>"#00008B"),    
				'keyword1'=>array('value'=>urlencode($user_show['name']),'color'=>"#00008B"),    //学生姓名keyword1     
				'keyword2'=>array('value'=>urlencode($addDateTime),'color'=>'#00008B'),        //申请时间keyword2   
				'remark'=>array('value'=>urlencode("请登录平台同意或拒绝。"),'color'=>'#00008B'),
				)
			);		
			$data = urldecode(json_encode($template));
			$send_result = send_template_message($data,$access_token);		
		}
		//---------------------
		message_app('您申请加入班级已经提交给老师，请等候老师同意加入。', array($urltk), 'success');
	}else{
		message_app('抱歉！申请保存失败！', '', 'error');
	}
}
//-------------------------------

//列表-------------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;

$where = '';
$keyword = trim($_GPC['keyword']);
if (!empty($keyword)) {
    $where .= " AND numberid LIKE '%{$keyword}%' or titlename  LIKE '%{$keyword}%'";
}else{
    $where .= "";
}
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where}");

if($total){
    $list = pdo_fetchall("SELECT a.*,b.name FROM ".tablename('onljob_theclass')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' {$where} ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $xstotal = '';
    foreach ($list as $cid => $cate) {
        $xstotal[$cate['id']] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('onljob_theclass_apply') . " WHERE weid = '{$_W['uniacid']}' and bjid = '{$cate['id']}' ");
        $paid = pdo_get('onljob_pay_order',array('weid'=>$_W['uniacid'],'uid'=>$_W['member']['uid'],'type'=>'class','parentid'=>$cate['id'],'status'=>"1"));
        if($paid && $paid['status'] == "1"){
            $list[$cid]['pay_status'] = true;
        }else{
            $list[$cid]['pay_status'] = false;
        }
    }
}
$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));

$my_class = pdo_getall('onljob_theclass_apply',array('weid'=>$_W['uniacid'],'uid'=>$_W['member']['uid']),array('bjid','state'));
if($my_class){
    $my_class = array_col($my_class,'state','bjid');
}else{
    $my_class = array();
}

include template_app('theclass');
	