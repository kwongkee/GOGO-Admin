<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";
require "public.php";
require "t_common.php";
require 'weixin.class.php';
$op = trim($_GPC['op']);
$lid = empty($_GPC['lid'])?0:intval($_GPC['lid']);
$uid = intval($_GPC['uid']);
$urlt = $this->createMobileUrl('t_problem');
//获取公众号配置信息
$srdm = pdo_get('account_wechats', array('uniacid' => $_W['uniacid']));
$appid = $srdm['key'];
$appsecret = $srdm['secret'];
$access_token_odl = $srdm['token'];
$mb_config = $config;

//添加
if(checksubmit('add_submit')){
	$uid = intval($_GPC['uid']);
	$urlt .= '&op=show&lid='.$lid.'uid='.$uid;
	$data = array(
		'weid' => $_W['uniacid'],
		'uid' => $_W['member']['uid'],
		'lid' => $lid,
		'content' => trim($_GPC['content']),
		'addtime' => time()
	);
	   
	$resultp = pdo_insert('onljob_message_content', $data, true);
	if (!empty($resultp)) {
		pdo_update('onljob_message', array('showt' => 1,'addtime' => time()), array('lid' => $lid,'weid' => $_W['uniacid']));
		//发送模板消息---------------------
		if(!empty($mb_config['mb_open']) && !empty($appid) && !empty($appsecret)){		
			$access_token = moban($appid,$appsecret,$access_token_odl,$uniacid);
			//获取openid
			$user_openid = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'],'uid' =>$uid,'follow' => 1));	
			$first = "您好，您收到一条回复消息，请点击查看！";
			$url = $_W['siteroot'] . "app/index.php?i=".$_GPC['i']."&c=".$_GPC['c']."&m=".$_GPC['m']."&do=m_problem&op=show&lid=$lid&lsuid={$_W['member']['uid']}";
			$template = array(
				'touser'=> trim($user_openid['openid']),
				'template_id'=> trim($mb_config['mbid4']), 
				'url'=> $url,
				'topcolor'=>"#FF0000",
				'data'=>array(
				'first'=>array('value'=>urlencode($first),'color'=>"#00008B"),    
				'keyword1'=>array('value'=>urlencode("老师回复"),'color'=>"#00008B"),    //咨询名称keyword1     
				'keyword2'=>array('value'=>urlencode($_GPC['content']),'color'=>'#00008B'),        //回复内容keyword2   
				'remark'=>array('value'=>urlencode("点击进入交流页面。"),'color'=>'#00008B'),
				)
			);		
			$data = urldecode(json_encode($template));
			$send_result = send_template_message($data,$access_token);		
		}
		//---------------------	
		header("Location: ".$urlt."");
		exit(); 
	}else{
		message_app('发布失败！', array($urlt), 'error');
	}

}

//获取留言列表----------
if($op == 'show'){
	
	$srdb = pdo_get('onljob_message', array('lsuid' => $_W['member']['uid'],'lid' => $lid,'weid' => $_W['uniacid']));
	if (empty($srdb)) {
		message_app('抱歉！参数错误！禁止提问！', '', 'error');
	}
	
	pdo_update('onljob_message', array('showls' => 0), array('lid' => $srdb['lid'],'weid' => $_W['uniacid']));
	
	//列表
	$list_content = pdo_fetchall("SELECT a.*,b.name,b.headimg FROM ".tablename('onljob_message_content')." AS a LEFT JOIN ".tablename('onljob_user')." AS b ON a.uid = b.uid WHERE a.weid = '{$_W['uniacid']}' and a.lid = '{$lid}' ORDER BY a.addtime ASC");
	
	include template_app('t_problem_show');
	exit();
}

//列表---------------------
$list = pdo_fetchall("SELECT * FROM ".tablename('onljob_message')." WHERE weid = '{$_W['uniacid']}' and lsuid = '{$_W['member']['uid']}' ORDER BY addtime DESC");

include template_app('t_problem');
?>