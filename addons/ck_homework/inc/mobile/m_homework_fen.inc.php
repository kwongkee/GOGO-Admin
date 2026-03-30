<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "m_common.php";
require 'weixin.class.php';
load()->func('tpl');


$wid = empty($_GPC['wid'])?0:intval($_GPC['wid']);
$bjid = empty($_GPC['bjid'])?0:intval($_GPC['bjid']);

$urlt = $this->createMobileUrl('m_homework_fen') . '&wid=' . $wid . '&bjid=' . $bjid;
$dourl = $this->createMobileUrl('m_homework_fen') . '&wid=' . $wid . '&bjid=' . $bjid;

//获取公众号配置信息
$srdm = pdo_get('account_wechats', array('uniacid' => $_W['uniacid']));
$appid = $srdm['key'];
$appsecret = $srdm['secret'];
$access_token_odl = $srdm['token'];
$mb_config = $config;

//附件上传文件地址
$url_attachment = $_W['siteroot'] . 'app/index.php?i='.$_GPC['i'].'&c='.$_GPC['c'].'&do=attachment&m='.$_GPC['m'];

//开始考试时间
$starttimes = empty($_GPC['starttimes'])?0:intval($_GPC['starttimes']);
if($starttimes > 0){
	$kstimes = $starttimes;
	$urlt .= '&starttimes = '.$starttimes;
}else{
	$kstimes = $newstmes;
	$urlt .= '&starttimes = '.$newstimed;
}

//获取作业信息
$rsdb = pdo_get('onljob_work', array('weid' => $_W['uniacid'],'wid' => $wid,'bjid' => $bjid));
if (empty($rsdb)) {
	message_app('不存在或是已经被删除', '', 'error');
}

//判断是否已到期
if($rsdb['endtimes'] < $newstmes){
	message_app('抱歉！该作业提交日期已到期，不能再提交！', '', 'error');
}

//验证做题人编辑信息
$uer_class = pdo_get('onljob_theclass_apply', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'bjid' => $rsdb['bjid'],'state' => 1)); 
if (empty($uer_class)) {
	message_app('抱歉！您不是该班级的学生不能做该作业！', '', 'error');
}

//判断是否已经提交作业 
$work_fen = pdo_get('onljob_work_fen', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'wid' => $wid,'bjid' => $bjid));
if (!empty($work_fen)) {
	message_app('抱歉！您已经提交了作业，不能再次提交！', '', 'error');
}
	  

//默认
$page = empty($_GPC['page'])?1:intval($_GPC['page']);

$datishow = 0;
if(!empty($_GPC['aqid'])){
	$datishow = 1;
}
if(!empty($_GPC['answerarr'])){
	$datishow = 1;
}

//交卷-----------------------------
if($_GPC['actiond'] == 'dosubmit'){
	$q_total = intval($_GPC['q_total']);  //题目总数量
	$stratimes = $_GPC['startimes'];      //开始时间
	
	//答题存入---------
	if($datishow == 1 && !empty($_GPC['q_id'])){
	
		$rsdb_que = pdo_get('onljob_questions', array('weid' => $_W['uniacid'],'id' => $_GPC['q_id']), array('type','zqanswer'));
		if($rsdb_que['type'] == 2){
			$answer = substr($_GPC['aqid'], 0, -1);
			$answer = implode('、', explode(',',$answer));
		}elseif($rsdb_que['type'] > 4){
			$answer = implode(',', $_GPC['answerarr']);
		}else{
			$answer = $_GPC['aqid'];
		}
		
		$data = array(
			'zqanswer' => trim($rsdb_que['zqanswer']),
			'answer' => $answer,
			'numberbh' => intval($_GPC['numberbh']),
			'dateline' => time(),
            'media_file'=>$_GPC['baseUrl']
		);
		
		//判断非作文题
		if($rsdb_que['type'] < 5){
			if(trim($rsdb_que['zqanswer']) == trim($answer)){
				$data['stateh'] = 0;
			}else{
				$data['stateh'] = 1;
			}
		}
		
		$work_answer = pdo_get('onljob_work_answer', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'wid' => $wid,'bjid' => $bjid,'qid' => $_GPC['q_id']));
		if($work_answer){
			pdo_update('onljob_work_answer', $data, array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'wid' => $wid,'bjid' => $bjid,'qid' => $_GPC['q_id']));
		}else{
	
			$data['weid'] = $_W['uniacid'];
			$data['uid'] = $_W['member']['uid'];
			$data['qid'] =  intval($_GPC['q_id']);
			$data['wid'] =  intval($wid);
			$data['bjid'] =  intval($bjid);
		    $data['media_file']=$_GPC['baseUrl'];
			pdo_insert('onljob_work_answer', $data, true);
		}
		
	}
	//-----------------
	
	//获取已做题数
	$q_total_yz = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." WHERE  weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and wid = '{$wid}' and bjid = '{$bjid}' and fid = '' ");
	if($q_total_yz < $q_total){
		message_app('抱歉！要做完全部的题目才能交卷！', '', 'error');
	}
	
	//存入作业信息
	$datap = array(
		'weid' => $_W['uniacid'],
		'uid' => $_W['member']['uid'],
		'wid' => intval($wid),
		'bjid' => intval($bjid),
		'stratimes' => $stratimes,
		'dateline' => time(),
        // 'media_file'=>$_GPC['baseUrl']
	);
	
	//需要批改的题目数
	$xpg_total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_answer')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b ON a.qid = b.id WHERE  a.weid = '{$_W['uniacid']}' and a.uid = '{$_W['member']['uid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' and a.fid = '' and b.type > 4 ");
	if($xpg_total < 1){
		$datap['state'] = 1;
	}

	$result = pdo_insert('onljob_work_fen', $datap, true);
	if (!empty($result)) {
		$fid = pdo_insertid();
		pdo_update('onljob_work_answer', array('fid' => $fid), array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'wid' => $wid,'bjid' => $bjid,'fid' => ''));
		
		$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$rowpy['uid'],'follow' => 1));	
		
		//发送模板消息---------------------
	if(!empty($mb_config['mb_open']) && !empty($appid) && !empty($appsecret)){		
		$access_token = moban($appid,$appsecret,$access_token_odl,$uniacid);
		//负责班级老师UID
		$class_list = pdo_get('onljob_theclass_apply', array('weid' => $_W['uniacid'],'bjid' =>$bjid));
		//作业名称
		$work_name = pdo_get('onljob_work', array('weid' => $_W['uniacid'],'wid' =>$wid));
		//获取openid
		if (!empty($class_list)){
			$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$class_list['tuid'],'follow' => 1));	
			$first = "您好，您有一名学生完成了作业，请点击查看！";
			$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=".$_GPC['c']."&m=".$_GPC['m']."&do=t_homework";
			$template = array(
				'touser'=> trim($user_openid['openid']),
				'template_id'=> trim($mb_config['mbid2']), 
				'url'=> $url,
				'topcolor'=>"#FF0000",
				'data'=>array(
				'first'=>array('value'=>urlencode($first),'color'=>"#00008B"),    
				'keyword1'=>array('value'=>urlencode($user_show['name']),'color'=>"#00008B"),    //课程名称keyword1     
				'keyword2'=>array('value'=>urlencode("《".$work_name['titlename']."》"."作业已经完成，记得对作业进行批改哦！"),'color'=>'#00008B'),        //作业名称keyword2  
				'remark'=>array('value'=>urlencode("点击进入我的班级作业。"),'color'=>'#00008B'),
				)
			);		
			$data = urldecode(json_encode($template));
			$send_result = send_template_message($data,$access_token);	
			
		}	
	}
	//---------------------
		
		//跳转
		$result_url = $this->createMobileUrl('m_homework_result', array('fid' => $fid,'wid' => $wid,'bjid' => $bjid));
		message_app('答题完毕，您的客观题部分可以直接查看解析和答案！您的非客观题部分请等候老师批改。', array($result_url), 'success', array('查看答题结果'));
	}else{
		message_app('抱歉！您交卷失败！', '', 'error');
	}

}
//-----------------------

//答题存入--------------------------------
if($datishow == 1 && !empty($_GPC['q_id'])){
	
	$numberbh = $page - 1;
	$rsdb_que = pdo_get('onljob_questions', array('weid' => $_W['uniacid'],'id' => $_GPC['q_id']));
	if($rsdb_que['type'] == 2){
		$answer = substr($_GPC['aqid'], 0, -1);
		$answer = implode('、', explode(',',$answer));
	}elseif($rsdb_que['type'] > 4){
		//$answer = implode(',', $_GPC['answerarr']);
        $answer = $_GPC['aqid'];
	}else{
		$answer = $_GPC['aqid'];
	}
	
	$data = array(
		'zqanswer' => trim($rsdb_que['zqanswer']),
		'answer' => $answer,
		'numberbh' => intval($numberbh),
		'dateline' => time(),
        'media_file'=>$_GPC['baseUrl']
	);
	
	//判断非作文题
	if($rsdb_que['type'] < 5){
		if(trim($rsdb_que['zqanswer']) == trim($answer)){
			$data['stateh'] = 0;
		}else{
			$data['stateh'] = 1;
		}
	}
	
	$work_answer = pdo_get('onljob_work_answer', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'wid' => $wid,'bjid' => $bjid,'qid' => $_GPC['q_id']));
	if($work_answer){
		pdo_update('onljob_work_answer', $data, array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'wid' => $wid,'bjid' => $bjid,'qid' => $_GPC['q_id']));
	}else{

		$data['weid'] = $_W['uniacid'];
		$data['uid'] = $_W['member']['uid'];
		$data['qid'] =  intval($_GPC['q_id']);
		$data['wid'] =  intval($wid);
		$data['bjid'] =  intval($bjid);
        $data['media_file']=$_GPC['baseUrl'];
	  
		pdo_insert('onljob_work_answer', $data, true);
	}
	
}
//---------------------------------------

//列表-------------------------------------/
$total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('onljob_work_questions')." where weid = '{$_W['uniacid']}' and wid = '{$wid}' and bjid = '{$bjid}' ");

//题目列表-------------------
$listdb = array();
$query = pdo_fetchall("SELECT b.* FROM ".tablename('onljob_work_questions')." AS a LEFT JOIN ".tablename('onljob_questions')." AS b on a.qid = b.id  where a.weid = '{$_W['uniacid']}' and a.wid = '{$wid}' and a.bjid = '{$bjid}' ORDER BY b.type ASC LIMIT " . ($page - 1) . ', 1');
foreach ($query as $bb=>$value) {
	$work_answer = pdo_get('onljob_work_answer', array('weid' => $_W['uniacid'],'uid' => $_W['member']['uid'],'wid' => $wid,'bjid' => $bjid,'qid' => $value['id']), array('answer'));
	if($value['type']==2){
		$value['hdanswer'] = explode('、', $work_answer['answer']);
	}elseif($value['type'] > 4){
		$value['hdanswer'] = explode(',', $work_answer['answer']);
	}else{
		$value['hdanswer'] = $work_answer['answer'];
	}
	$listdb[] = $value;
}

//已答得题情况
$aqid_all = array();
$query = pdo_fetchall("SELECT numberbh FROM ".tablename('onljob_work_answer')." WHERE  weid = '{$_W['uniacid']}' and uid = '{$_W['member']['uid']}' and wid = '{$wid}' and bjid = '{$bjid}' ORDER BY qid ASC ");
foreach ($query as $bb=>$value) {
	$aqid_all[] = $value['numberbh'];
}

include template_app('m_homework_fen');
?>