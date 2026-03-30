<?php

/*
升级为会员
*/
global $_GPC,$_W;

$openid = sunshine_huayueModuleSite::$openid;

if(!$openid)
{
	echoJson(array('res'=>'101','msg'=>'error'));
}

// 检测是否是会员
$vip = new VipComponent($openid);
if($vip->isVip())
{
	echoJson(array('res'=>'101','msg'=>'您目前已经是会员身份'));
}

// 如果不是会员直接更新为会员
$credit = $this->settings['vip_credit_month_1'];
if(!$credit)
{
	echoJson(array('res'=>'101','msg'=>'消费积分错误，积分不存在'));
}

// 获取用户的总积分，判断是否已足够消费积分
load()->model('mc');
$credit_now =  mc_credit_fetch($_W['member']['uid'],array('credit1'));
if($credit_now < $credit) {
	echoJson(array('res'=>'101','msg'=>'积分不足，积分剩余'.$credit_now));	
}

pdo_begin();

$data = array();
$data['uniacid'] = $_W['uniacid'];
$data['openid'] = $openid;
$data['sid'] = 0;
$data['credit'] = $credit;
$data['type'] = '月会员';
$data['add_date'] = date('Y-m-d');
$data['add_time'] = date('Y-m-d H:i:s');

$res = pdo_insert('sunshine_huayue_credit',$data);
if(!$res)
{
	pdo_rollback();
	echoJson(array('res'=>'101','msg'=>'记录失败'));
}
// 操作会员积分
$update_log = array();
$update_log[] = 'sunshine_huayue';
$update_log[] = '开通月会员';

load()->model('mc');
// start transaction
$result = mc_credit_update($_W['member']['uid'], 'credit1', -$credit,$update_log);

if($result === true)
{
	$r = $vip -> updateToVip();
	if($r)
	{
		pdo_commit();
		echoJson(array('res'=>'100','msg'=>'消费积分成功'));	
	}
	else 
	{
		pdo_rollback();
		echoJson(array('res'=>'101','msg'=>'更新会员身份失败'));
	}
}
else
{
	pdo_rollback();
	echoJson(array('res'=>'101','msg'=>'积分余额不足'));
}

