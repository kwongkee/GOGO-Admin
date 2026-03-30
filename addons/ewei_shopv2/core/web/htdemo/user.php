<?php
/**
 * 用户管理控制器
 */
 global $_W;
 global $_GPC;
defined('IN_IA') or exit('Access Denied');
//uni_user_permission_check('mc_member');
$dos = array('display', 'post','del', 'add','register','sms');
$do = in_array($do, $dos) ? $do : 'display';
load()->model('mc');
if($do == 'display') {
	
}else if($do == 'register') {
	
	template('htdemo/register');
}
else if($do == 'post') {
	global $_W;
	global $_GPC;
	echo $_W['uniacid'];die;
	
	// $set = m('common')->getSysset(array('shop', 'wap'));
	$sms_id=$this->GetSmsid($_W['uniacid']);
		// $sms_id = $set['wap'][$temp];
		$key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
		$code = random(5, true);
		$ret = com('sms')->send($_GPC['mobile'],$sms_id['id'], array('名称' =>$_W['uniaccount']['name'],'验证码' => $code));
		if ($ret['status']) {
			$_SESSION[$key] = $code;
			$_SESSION['verifycodesendtime'] = time();
			show_json(1, '短信发送成功');
		}
		show_json(0, $ret['message']);
	
	public function GetSmsid($cid){
		return pdo_get('ewei_shop_sms',array('uniacid'=>$cid),array('id'));
	}
	echo '<pre>';
	print_r($_GPC);
	
//	template('htdemo/register');
}else if($do == 'sms') {
	global $_W;
	global $_GPC;
	// $set = m('common')->getSysset(array('shop', 'wap'));
	$sms_id=$this->GetSmsid($_W['uniacid']);
	// $sms_id = $set['wap'][$temp];
	$key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
	$code = random(5, true);
	$ret = com('sms')->send($_GPC['mobile'],$sms_id['id'], array('名称' =>$_W['uniaccount']['name'],'验证码' => $code));
	if ($ret['status']) {
		$_SESSION[$key] = $code;
		$_SESSION['verifycodesendtime'] = time();
		show_json(1, '短信发送成功');
	}
	show_json(0, $ret['message']);
}
?>