<?php

if (!defined('IN_IA')) 
{
	exit('Access Denied');
}
ini_set('display','on');
class Reg_EweiShopV2Page extends MobilePage
{
	public function main(){
		$title='注册';
		include $this->template('parking/reg');
	}
	public function code(){
		global $_W;
		global $_GPC;
		$set = m('common')->getSysset(array('shop', 'wap'));
		$sms_id = $set['wap'][$temp];
		$key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $_GPC['mobile'];
		$code = random(5, true);
		$ret = com('sms')->send($_GPC['mobile'], $sms_id, array('名称' =>$_W['uniaccount']['name'],'验证码' => $code));
		if ($ret['status']) {
			$_SESSION[$key] = $code;
			$_SESSION['verifycodesendtime'] = time();
			show_json(1, '短信发送成功');
		}
		show_json(0, $ret['message']);
	}

	public function verify_reg(){
		global $_W;
		global $_GPC;
		if($_W['isajax']){
			if(empty($_GPC['mobile'])||strlen($_GPC['mobile'])!=11){
				show_json(0,'请输入手机号');
				return false;
			}
			if(empty($_GPC['yzm'])){
				show_json(0,'请输入验证码');
				return false;
			}
			$key = '__ewei_shopv2_member_verifycodesession_' . $_W['uniacid'] . '_' . $mobile;
			if (!(isset($_SESSION[$key])) || ($_SESSION[$key] !== $verifycode) || !(isset($_SESSION['verifycodesendtime'])) || (($_SESSION['verifycodesendtime'] + 600) < time())) 
			{
				show_json(0, '验证码错误或已过期!');
			}
			$member = pdo_fetch('select uid from ' . tablename('mc_mapping_fans') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
			$members = pdo_fetch('select mobile from ' . tablename('mc_members') . ' where uid=:uid and uniacid=:uniacid limit 1', array(':uid' =>$member['uid'], ':uniacid' => $_W['uniacid']));
			if(isset($members['mobile'])){
				pdo_update('mc_members', array('mobile'=>$_GPC['mobile']), array('uid' => $member['uid']));
				pdo_insert('parking_authorize', array('uid'=>$member['uid'],'openid'=>$_W['openid'],'mobile'=>$_GPC['mobile'],'createtime'=>time()));
				show_json(1,'注册成功');
				return true;
			}else{
			$salt = random(16);
				$nid=pdo_insert('mc_members', array('uniacid'=>$_W['uniacid'],'mobile'=>$_GPC['mobile'],'salt'=>$salt,'groupid'=>$_W['uniacid'],'createtime'=>time()));
				if (!empty($result)) {
				$uid = pdo_insertid();
				pdo_insert('parking_authorize', array('uid'=>$uid,'openid'=>$_W['openid'],'mobile'=>$_GPC['mobile'],'createtime'=>time()));
				show_json(1,'注册成功');
				return true;
				}	// show_json(0,'')
			}
			// var_dump($members);
		}
	}
}