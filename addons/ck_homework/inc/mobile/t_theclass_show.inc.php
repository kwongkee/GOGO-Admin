<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";
require "t_common.php";
require 'weixin.class.php';
$op = empty($_GPC['op'])?0:intval($_GPC['op']);
$id = empty($_GPC['id'])?0:intval($_GPC['id']);
$tab = empty($_GPC['tab'])?0:intval($_GPC['tab']);
$urltk = $this->createMobileUrl('t_theclass_show').'&id='.$id;
$url_edit = $this->createMobileUrl('t_theclass_edit').'&id='.$id;
$sosourl = $this->createMobileUrl('knowledgesoso');
$datatimes = time();
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

//知识点分类
$category_zsd = pdo_fetchall("SELECT * FROM " . tablename('onljob_class') . " WHERE weid = '{$_W['uniacid']}' and type = 'zsd' ORDER BY listorder ASC, cid DESC", array(), 'cid');
if (!empty($category_zsd)) {
	$children = '';
	foreach ($category_zsd as $cid => $cate) {
		if (!empty($cate['pid'])) {
			$children[$cate['pid']][$cate['cid']] = array($cate['cid'], $cate['name']);
		}
	}
}

$nubmg_arll =array('','一','二','三','四','五','六','七','八','九','十');

//显示内容 
$srdb = pdo_get('onljob_theclass', array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
if (empty($srdb)) {
	message_app('参数错误！访问失败！', '', 'error');
}

//发布作业---------------------
if(trim($_GPC['ob']) === 'wordadd'){
	//发布第一步
	$wid = empty($_GPC['wid'])?1:intval($_GPC['wid']);
	$srdb_work = pdo_get('onljob_work', array('wid' => $wid,'bjid' => $id,'weid' => $_W['uniacid']));
	if (!empty($srdb_work)) {
		$ptypeid_arll = explode(',', $srdb_work['typeid']);
		$plevelid_arll = explode(',', $srdb_work['levelid']);
		$parentid_arll = explode(',', $srdb_work['parentid']);
		$parentid_tale = count($parentid_arll);
		if($parentid_tale > 0){
			//获取知识点
			$where = '';
			if($parentid_tale > 1){
				$where = 'and id in ('.$srdb_work['parentid'].')';
			}else{
				$where = 'and id = '.$srdb_work['parentid'];
			}
			$xy_knowledge = pdo_fetchall("SELECT id,titlename FROM ".tablename('onljob_knowledge')." WHERE weid = '{$_W['uniacid']}' {$where} ORDER BY listorder ASC,id DESC");
		}
	}
	include template_app('t_theclass_homework_add');
	exit();
}
if(trim($_GPC['ob']) === 'wordnext'){
	//发布第二步
	
	$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
	$oc = empty($_GPC['oc'])?0:intval($_GPC['oc']);
	$od = empty($_GPC['od'])?0:intval($_GPC['od']);
	$typeid = intval($_GPC['typeid']);
	
	//跳转地址
	$urltk .= '&ob=wordnext&oc='.$oc.'&wid='.$wid;
	
	$srdb_work = pdo_get('onljob_work', array('wid' => $wid,'bjid' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb_work)) {
		message_app('参数错误！无法进行下一步！', array($urltk.'&ob=wordadd&wid='.$wid), 'error');
	}
	
	$ptypeid_arll = explode(',', $srdb_work['typeid']);
	$plevelid_arll = explode(',', $srdb_work['levelid']);
	$parentid_arll = explode(',', $srdb_work['parentid']);
	$parentid_tale = count($parentid_arll);
	
	//获取题目列表
	$conditiont = '';
	if (!empty($_GPC['typeid'])) {
		$typeid = intval($_GPC['typeid']);
		$conditiont .= " AND type = '{$typeid}'";
	}else{
		$conditiont .= " AND type in (".$srdb_work['typeid'].")";
	}
	if (!empty($srdb_work['levelid'])) {
		$conditiont .= " AND level in (".$srdb_work['levelid'].")";
	}
	if (!empty($srdb_work['parentid'])) {
		$conditiont .= " AND parentid in (".$srdb_work['parentid'].")";
	}
	if($od == 1){
		$conditiont .= " AND uid = '{$_W['member']['uid']}'";
	}
	
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename('onljob_questions') . " WHERE weid = '{$_W['uniacid']}' and status = '3' $conditiont");
	$list_questions = pdo_fetchall("SELECT * FROM " . tablename('onljob_questions') . " WHERE weid = '{$_W['uniacid']}' and status = '3' $conditiont ORDER BY addtime DESC, id DESC");
	include template_app('t_theclass_homework_next'.$oc);
	exit();
}

//保存第一步
if(checksubmit('zd_submit')){
	
	//跳自动组卷
	if (empty ($_GPC['titlename'])) {
		message_app('作业名称不能为空！', '', 'error');
	}
	if (empty ($_GPC['endtimes'])) {
		message_app('完成时间不能为空！', '', 'error');
	}
	$endtimes = strtotime($_GPC['endtimes']);
	if($datatimes > $endtimes){
		message_app('完成时间不能小于当前时间！', '', 'error');
	}
	
	foreach($_GPC['parentid'] AS $key=>$value){
		if(!empty($value)){
			$parentid[] = $value;
		}
	}
	if(count($parentid) < 1){
		message_app('请选择知识点！', '', 'error');
	}
	
	$data = array(
		'bjid' => intval($id),
		'titlename' => $_GPC['titlename'],
		'endtimes' => $endtimes,
		'typeid' => implode(',', $_GPC['typeid']),
		'levelid' => implode(',', $_GPC['levelid']),
		'parentid' => implode(',', $parentid)
	);
	
	if(!empty($_GPC['wid'])){
		//修改
		pdo_update('onljob_work', $data, array('wid' => $_GPC['wid'],'weid' => $_W['uniacid']));

		$urltk .= '&ob=wordnext&wid='.$_GPC['wid'];
		header("location: $urltk");
		exit();
	}else{
		//添加
		$data['weid'] = $_W['uniacid'];
		$data['uid'] = $_W['member']['uid'];
		$data['dateline'] = time();
		$result = pdo_insert('onljob_work', $data, true);
		$wid = pdo_insertid();
		if (!empty($result) && !empty($wid)) {	
			$urltk .= '&ob=wordnext&wid='.$wid;
			header("location: $urltk");
			exit();
		}else{
			message_app('保存失败', '', 'error');
		}
	}
}
if(checksubmit('sd_submit')){
	//跳手动组卷
	if (empty ($_GPC['titlename'])) {
		message_app('作业名称不能为空！', '', 'error');
	}
	if (empty ($_GPC['endtimes'])) {
		message_app('完成时间不能为空！', '', 'error');
	}
	$endtimes = strtotime($_GPC['endtimes']);
	if($datatimes > $endtimes){
		message_app('完成时间不能小于当前时间！', '', 'error');
	}
	
	foreach($_GPC['parentid'] AS $key=>$value){
		if(!empty($value)){
			$parentid[] = $value;
		}
	}
	if(count($parentid) < 1){
		message_app('请选择知识点！', '', 'error');
	}
	
	$data = array(
		'bjid' => intval($id),
		'titlename' => $_GPC['titlename'],
		'endtimes' => $endtimes,
		'typeid' => implode(',', $_GPC['typeid']),
		'levelid' => implode(',', $_GPC['levelid']),
		'parentid' => implode(',', $parentid)
	);
	
	if(!empty($_GPC['wid'])){
		//修改
		pdo_update('onljob_work', $data, array('wid' => $_GPC['wid'],'weid' => $_W['uniacid']));

		$urltk .= '&ob=wordnext&oc=1&wid='.$_GPC['wid'];
		header("location: $urltk");
		exit();
	}else{
		//添加
		$data['weid'] = $_W['uniacid'];
		$data['uid'] = $_W['member']['uid'];
		$data['dateline'] = time();
		$result = pdo_insert('onljob_work', $data, true);
		$wid = pdo_insertid();
		if (!empty($result) && !empty($wid)) {
			$urltk .= '&ob=wordnext&oc=1&wid='.$wid;
			header("location: $urltk");
			exit();
		}else{
			message_app('保存失败', '', 'error');
		}
	}
}
//手动选题保存二
if(checksubmit('sdsace_submit')){	
	$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
	$user_work = pdo_get('onljob_work', array('weid' => $_W['uniacid'],'uid' =>$_W['member']['uid'],'wid' => $wid));
	$user_theclass = pdo_get('onljob_theclass', array('weid' => $_W['uniacid'],'uid' =>$_W['member']['uid'],'id' => $id)); 
	//print_r($user_theclass);die;
	if (empty($wid)) {
		message_app('参数错误！保存失败！', array($urltk.'&ob=wordnext&oc=1&wid='.$wid), 'error');
	}
	$ids = $_GPC['ids'];
	//所在班级学生UID
	$class_list = pdo_fetchall("SELECT * FROM ".tablename('onljob_theclass_apply')." WHERE weid = '{$_W['uniacid']}' and bjid='{$id}'");
	//print_r($class_list);die;
	$ids_count = count($ids);
	if($ids_count < 1){
		message_app('抱歉！请选择题目！', '', 'error');
	}
	foreach( $ids AS $key=>$value){
		if(!empty($value)){
			pdo_insert('onljob_work_questions', array('wid' => $wid,'qid' => $value,'bjid' => $id,'weid' => $_W['uniacid']));
		}
	}
	pdo_update('onljob_work', array('releaset' => 1), array('wid' => $wid,'weid' => $_W['uniacid']));

	$theclass= pdo_get('onljob_theclass', array('weid' => $_W['uniacid'],'id' =>$id));
	$km_name = pdo_get('onljob_class', array('weid' => $_W['uniacid'],'cid' =>$theclass['xkid']));
	
	//发送模板消息---------------------
	if(!empty($mb_config['mb_open']) && !empty($appid) && !empty($appsecret)){		
		$access_token = moban($appid,$appsecret,$access_token_odl,$uniacid);
		//获取openid
		if (!empty($class_list)) {
			foreach ($class_list as &$rowpy) {
			$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$rowpy['uid'],'follow' => 1));	
			$first = "您好，您的班级有新作业了，请点击查看！";
			$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=".$_GPC['c']."&m=".$_GPC['m']."&do=m_homework";
			$tradeDateTime = date('Y-m-d H:i:s',$user_work['endtimes']);
			$template = array(
				'touser'=> trim($user_openid['openid']),
				'template_id'=> trim($mb_config['mbid1']), 
				'url'=> $url,
				'topcolor'=>"#FF0000",
				'data'=>array(
				'first'=>array('value'=>urlencode($first),'color'=>"#00008B"),    
				'keyword1'=>array('value'=>urlencode($user_theclass['titlename']."-".$km_name['name']),'color'=>"#00008B"),    //课程名称keyword1     
				'keyword2'=>array('value'=>urlencode($user_work['titlename']),'color'=>'#00008B'),        //作业名称keyword2  
				'keyword3'=>array('value'=>urlencode($tradeDateTime),'color'=>'#00008B'),        //截止日期keyword3 
				'remark'=>array('value'=>urlencode("点击进入我的作业。"),'color'=>'#00008B'),
				)
			);		
			$data = urldecode(json_encode($template));
			$send_result = send_template_message($data,$access_token);	
			}
		}	
	}
	//---------------------
	message_app('发布成功！', array($urltk.'&op=3'), 'success', array('查看班级作业'));
	
}
//自动选题保存二
if(checksubmit('zdsace_submit')){
	$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
	$srdb_work = pdo_get('onljob_work', array('wid' => $wid,'bjid' => $id,'weid' => $_W['uniacid']));
	if (empty($srdb_work)) {
		message_app('参数错误！保存失败！', array($urltk.'&ob=wordnext&wid='.$wid), 'error');
	}
	$user_theclass = pdo_get('onljob_theclass', array('weid' => $_W['uniacid'],'uid' =>$_W['member']['uid'],'id' => $id)); 
	//print_r($user_theclass);die;
	//所在班级学生UID
	$class_list = pdo_fetchall("SELECT * FROM ".tablename('onljob_theclass_apply')." WHERE weid = '{$_W['uniacid']}' and bjid='{$id}'");
	//print_r($class_list);die;
	$ptypeid_arll = explode(',', $srdb_work['typeid']);
	$plevelid_arll = explode(',', $srdb_work['levelid']);
	$parentid_arll = explode(',', $srdb_work['parentid']);
	$parentid_tale = count($parentid_arll);
	
	$numbpl = intval($_GPC['numbpl']);
	if (empty ($numbpl)) {
		message_app('请选择数量！', '', 'error');
	}
	$rulesid = intval($_GPC['rulesid']);
	
	//获取题目列表
	$conditiont = '';
	$conditiontdt = '';
	if (!empty($srdb_work['typeid'])) {
		$conditiont .= " AND type in (".$srdb_work['typeid'].")";
		$conditiontdt .= " AND b.type in (".$srdb_work['typeid'].")";
	}
	if (!empty($srdb_work['levelid'])) {
		$conditiont .= " AND level in (".$srdb_work['levelid'].")";
		$conditiontdt .= " AND b.level in (".$srdb_work['levelid'].")";
	}

	if($rulesid == '1'){
		//随机抽取未在本班布置过的题目
		foreach( $parentid_arll AS $k=>$rowl){
			$kil[$k] = 0;
			$rs = pdo_fetchall("SELECT id FROM ".tablename('onljob_questions')."  WHERE  weid = '{$_W['uniacid']}' and parentid = '{$rowl}' $conditiont ORDER BY rand()");
			foreach ($rs as $bb => $age) {
				$work_que = pdo_get('onljob_work_questions', array('qid' => $age['id'],'bjid' => $id,'weid' => $_W['uniacid']));
				if(empty($work_que) && $numbpl > $kil[$k]){
					pdo_insert('onljob_work_questions', array('wid' => $wid,'qid' => $age['id'],'bjid' => $id,'weid' => $_W['uniacid']));
					$kil[$k]++;
				}
			}
		}
	}elseif($rulesid == '2'){
		//随机抽取本班做过的作业
		foreach( $parentid_arll AS $k=>$rowl){
			$rs = pdo_fetchall("SELECT b.id FROM ".tablename('onljob_work_questions')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b on a.qid = b.id  WHERE  a.weid = '{$_W['uniacid']}' and b.parentid = '{$rowl}' and a.bjid = '{$id}' $conditiontdt ORDER BY rand() LIMIT {$numbpl} ");
			foreach ($rs as $bb => $age) {
				pdo_insert('onljob_work_questions', array('wid' => $wid,'qid' => $age['id'],'bjid' => $id,'weid' => $_W['uniacid']));
			}
		}
	}elseif($rulesid == '3'){
		//优先选择本班错误率高的
		foreach( $parentid_arll AS $k=>$rowl){
			$rs = pdo_fetchall("SELECT b.id FROM ".tablename('onljob_work_questions')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b on a.qid = b.id  WHERE  a.weid = '{$_W['uniacid']}' and b.parentid = '{$rowl}' and a.bjid = '{$id}' $conditiontdt ORDER BY a.errornumb DESC LIMIT {$numbpl} ");
			foreach ($rs as $bb => $age) {
				pdo_insert('onljob_work_questions', array('wid' => $wid,'qid' => $age['id'],'bjid' => $id,'weid' => $_W['uniacid']));
			}
		}
	}else{
		//全部随机
		foreach( $parentid_arll AS $k=>$rowl){
			$rs = pdo_fetchall("SELECT id FROM ".tablename('onljob_questions')."  WHERE  weid = '{$_W['uniacid']}' and parentid = '{$rowl}' $conditiont ORDER BY rand() LIMIT {$numbpl} ");
			foreach ($rs as $bb => $age) {
				pdo_insert('onljob_work_questions', array('wid' => $wid,'qid' => $age['id'],'bjid' => $id,'weid' => $_W['uniacid']));
			}
		}
	}
	
	//判断是否已选题
	$total_work_q = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_questions')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$id}' and wid = '{$wid}' ");
	if($total_work_q > 0){
		pdo_update('onljob_work', array('releaset' => 1), array('wid' => $wid,'weid' => $_W['uniacid']));
		
		$theclass= pdo_get('onljob_theclass', array('weid' => $_W['uniacid'],'id' =>$id));
		$km_name = pdo_get('onljob_class', array('weid' => $_W['uniacid'],'cid' =>$theclass['xkid']));
		
		//发送模板消息---------------------
		if(!empty($mb_config['mb_open']) && !empty($appid) && !empty($appsecret)){		
			$access_token = moban($appid,$appsecret,$access_token_odl,$uniacid);
			//获取openid
			if (!empty($class_list)) {
				foreach ($class_list as &$rowpy) {
				$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$rowpy['uid'],'follow' => 1));	
				$first = "您好，您的班级有新作业了，请点击查看！";
				$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=".$_GPC['c']."&m=".$_GPC['m']."&do=m_homework";
				$tradeDateTime = date('Y-m-d H:i:s',$srdb_work['endtimes']);
				$template = array(
					'touser'=> trim($user_openid['openid']),
					'template_id'=> trim($mb_config['mbid1']), 
					'url'=> $url,
					'topcolor'=>"#FF0000",
					'data'=>array(
					'first'=>array('value'=>urlencode($first),'color'=>"#00008B"),    
					'keyword1'=>array('value'=>urlencode($user_theclass['titlename']."-".$km_name['name']),'color'=>"#00008B"),    //课程名称keyword1     
					'keyword2'=>array('value'=>urlencode($srdb_work['titlename']),'color'=>'#00008B'),        //作业名称keyword2  
					'keyword3'=>array('value'=>urlencode($tradeDateTime),'color'=>'#00008B'),        //截止日期keyword3 
					'remark'=>array('value'=>urlencode("点击进入我的作业。"),'color'=>'#00008B'),
					)
				);		
				$data = urldecode(json_encode($template));
				$send_result = send_template_message($data,$access_token);	
				}
			}	
		}
	//---------------------
		
		message_app('发布成功！', array($urltk.'&op=3'), 'success', array('查看班级作业'));
	}else{
		message_app('抱歉！设定的选题规则没有能选到题！请重新设置！', array($urltk.'&ob=wordnext&wid='.$wid), 'error');
	}
	
}
//-----------------------------

//删除通知
if(trim($_GPC['ob']) === 'delete'){
	$tzid = empty($_GPC['tzid'])?0:intval($_GPC['tzid']);
	if($tzid){
		pdo_delete('onljob_theclass_notice', array('id' => $tzid,'weid' => $_W['uniacid']));
		$urltk .= '&op='.$op;
		message_app('删除成功！', array($urltk), 'success');
	}else{
		message_app('删除失败！', '', 'error');
	}
	exit();
}

//搜索学生
if(trim($_GPC['ob']) === 'xssearch'){

	//搜索-------------------
	$where = '';
	$keyword = trim($_GPC['keyword']);
	if (!empty($keyword)) {
		$where .= " AND usernumber LIKE '%{$keyword}%'";  
		$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_user')." WHERE weid = '{$_W['uniacid']}' AND type = '0' {$where} ORDER BY id DESC");
	}
	
	include template_app('t_theclass_xssearch');
	exit();
}

//添加学生进入本班
if(trim($_GPC['ob']) === 'xsadd'){

	if (empty($_GPC['uid'])) {
		message_app('参数错误！添加失败！', '', 'error');
	}
	
	//是否已申请
	$srdb_apply = pdo_get('onljob_theclass_apply', array('bjid' => $srdb['id'],'uid' => $_GPC['uid'],'weid' => $_W['uniacid']));
	if (!empty($srdb_apply) && $srdb_apply['state'] != '2') {
		message_app('抱歉！该学生已经加入本班级了！不能重复添加！', '', 'error');
	}
	
	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $_GPC['uid'],
		'bjid' => $srdb['id'],
		'tuid' => $_W['member']['uid'],
		'state' => 1,
		'dateline' => time()
	);
	  
	$result = pdo_insert('onljob_theclass_apply', $data);
	if (!empty($result)) {
		$urltk .= '&op='.$op;
		message_app('加入成功！', array($urltk), 'success');
	}else{
		message_app('抱歉！加入失败！', '', 'error');
	}
	
}

//提交处理
if(checksubmit('save_submit')){
	
	if (empty ($_GPC['titlename'])) {
		message_app('班级名称不能为空！', '', 'error');
	}
	
	$data = array(
		'titlename' => trim($_GPC['titlename']),
		'njid' => intval($_GPC['njid']),
		'xkid' => intval($_GPC['xkid']),
		'imgurl' => trim($_GPC['imgurl'])
	);
	
	if(!empty($id)){
		pdo_update('onljob_theclass', $data, array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
		message_app('该班级信息修改成功！', array($urltk), 'success');
	}else{
		message_app('该班级信息修改失败！', array($urltk), 'error');
	}
	
}


//通知提交处理
if(checksubmit('add_submit')){
	$urltk .= '&op='.$op;
	if (empty ($_GPC['titlename'])) {
		message_app('通知标题不能为空！', '', 'error');
	}

	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $_W['member']['uid'],
		'titlename' => trim($_GPC['titlename']),
		'infotext' => stripslashes($_GPC['infotext']),
		'bjid' => intval($id),
		'dateline' => time()
	);
	
	$result = pdo_insert('onljob_theclass_notice', $data);
	if (!empty($result)) {
		message_app('成功发布一条消息！', array($urltk . '&ob=add', $urltk), 'success', array('再发一条', '返回'));
	}else{
		message_app('保存失败', array($urltk), 'error');
	}
}


//列表-------------------------
$pindex = max(1, intval($_GPC['page']));
$psize = empty($_GPC['psize'])?0:intval($_GPC['psize']);
if(!in_array($psize, array(20,50,100))) $psize = 20;

if($op == 1){

	//获取学生列表
	$title = '学生管理';
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$id}' and a.state = '1' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT a.*,b.name,b.headimg FROM ".tablename('onljob_theclass_apply')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.bjid = '{$id}' and a.state = '1' {$where} ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	}
	
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
	
	include template_app('t_theclass_students');
	exit();
	
}elseif($op == 2){

	//班级通知
	$title = '班级通知';
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_theclass_notice')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and bjid = '{$id}' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_theclass_notice')." WHERE weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and bjid = '{$id}' {$where} ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
	}
	
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
	include template_app('t_theclass_notice');
	exit();
	
}elseif($op == 3){

	//班级作业-------------
	$title = '班级作业'; 
	$where = '';
	if($tab == '1'){
		$where .= " and releaset = '1' and state = '1'";
	}elseif($tab == '2'){
		$where .= " and releaset = '1' and state = '0'";
	}else{
		$where .= " and releaset = '0' ";
	}

	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$id}' {$where}");
	if($total){
		$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_work')." WHERE weid = '{$_W['uniacid']}' and bjid = '{$id}' {$where} ORDER BY wid DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
		if (!empty($list)) {
			foreach ($list as $k => $value) {
				//选择题目数量
				$que_total[$value['wid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_questions')." WHERE weid = '{$_W['uniacid']}' and wid = '{$value['wid']}'");
				//题目错误数量
				$que_error[$value['wid']] = pdo_fetchcolumn("SELECT SUM(errornumb) FROM ".tablename('onljob_work_questions')." WHERE weid = '{$_W['uniacid']}' and wid = '{$value['wid']}'");
				//交作业学生数
				$xs_total[$value['wid']] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_fen')." WHERE weid = '{$_W['uniacid']}' and wid = '{$value['wid']}'");
			}
		}
	}
	
	$pager = pagination($total, $pindex, $psize,'', array('before' => 0, 'after' => 0));
	
	include template_app('t_theclass_homework');
	exit();
	
}else{

	//班级信息---------------------------------
	$title = '班级信息';
	//二维码地址
	$attach_dir = IA_ROOT . "/addons/".$_GPC['m']."/data/bjqrcode/" . $_W['uniacid'] . "/";
	$qrcode_url = $srdb['qrcode'];
	if(empty($srdb['qrcode'])){
		$qrcode_add = 1;
	}
	//判断是否有图片存在
	if(!file_exists($attach_dir.$srdb['qrcode'])){
		$qrcode_add = 1;
	}
	if($qrcode_add == '1'){
		//引入核心库文件
		require "phpqrcode.php";
		
		//定义纠错级别
		$errorLevel = "L";
		
		//定义生成图片宽度和高度;默认为3
		$size = "8";
		
		$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=".$_GPC['c']."&m=".$_GPC['m']."&do=theclass&keyword=".$srdb['numberid'];
		
		$attach_dir = IA_ROOT . "/addons/".$_GPC['m']."/data/bjqrcode/" . $_W['uniacid'] . "/";
        if (!is_dir($attach_dir)) {
            load()->func('file');
            mkdirs($attach_dir);
        }
		$imgurl = 'bjqrcode_'.$_W['member']['uid'].'_'.$id.'.png';
		$filename = $attach_dir.'/'.$imgurl;
		QRcode::png($url, $filename, $errorLevel, $size, 2);
		
		if (file_exists($filename)) {
			$qrcode_url = $imgurl;
			pdo_update('onljob_theclass', array('qrcode' => $qrcode_url), array('id' => $id,'uid' => $_W['member']['uid'],'weid' => $_W['uniacid']));
		}
	}
	
	include template_app('t_theclass_show');
	
}