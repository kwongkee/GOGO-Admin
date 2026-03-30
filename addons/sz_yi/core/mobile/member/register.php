<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}
load()->func('communication');
global $_W;
global $_GPC;
$preUrl = $_COOKIE['preUrl'];
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$from = (!empty($_GPC['from']) ? $_GPC['from'] : '');
session_start();

$this->yzShopSet = m('common')->getSysset('shop');
// var_dump($this->yzShopSet['pclogo']);
if (m('user')->islogin() != false) {
	header('location: ' . $this->createMobileUrl('member'));
}

if (is_app()) {
	$setdata = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid']));
	$set = unserialize($setdata['sets']);
	$app = $set['app']['base'];
}

if ($_W['isajax']) {
	if ($_W['ispost']) {
		$mobile = (!empty($_GPC['mobile']) ? $_GPC['mobile'] : show_json(0, '手机号不能为空！'));
		$password = (!empty($_GPC['password']) ? $_GPC['password'] : show_json(0, '密码不能为空！'));
		$code = (!empty($_GPC['code']) ? $_GPC['code'] : show_json(0, '验证码不能为空！'));

		$xingming = (!empty($_GPC['xingming']) ? $_GPC['xingming'] : show_json(0, '姓名不能为空！'));
		// $idcard = (!empty($_GPC['idcard']) ? $_GPC['idcard'] : show_json(0, '身份证不能为空！'));
		// $province = (!empty($_GPC['province']) ? $_GPC['province'] : show_json(0, '省不能为空！'));
		// $city = (!empty($_GPC['city']) ? $_GPC['city'] : show_json(0, '城市不能为空！'));
		// $addr = (!empty($_GPC['addr']) ? $_GPC['addr'] : show_json(0, '地址不能为空！'));

		if (($_SESSION['codetime'] + (60 * 5)) < time()) {
			show_json(0, '验证码已过期,请重新获取');
		}

		if ($_SESSION['code'] != $code) {
			show_json(0, '验证码错误,请重新获取');
		}

		if ($_SESSION['code_mobile'] != $mobile) {
			show_json(0, '注册手机号与验证码不匹配！');
		}

		// $isPhoneReal = verif_phone_realname($xingming, $mobile);
		// if (empty($isPhoneReal)) {
		// 	show_json(0, '验证失败');
		// }
		// if ($isPhoneReal['data']['verifyCode'] != '0') {
		// 	show_json(0, '手机号实名姓名与填写不匹配！');
		// }

		// $isIDCardResult = verifIDCard(json_encode(['uname' => $xingming, 'idCard' => $idcard]));
		// $isIDCardResult = json_decode($isIDCardResult, true);
		// if (empty($isIDCardResult)) {
		// 	show_json(0, '身份验证失败');
		// }
		// if ($isIDCardResult['code'] != "01") {
		// 	show_json(0, "身份验证:" . $isIDCardResult['msg']); //身份证验证失败
		// }


        $member = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));
        if (!empty($member)) {
            if ($member['openid']!=$_W['openid']){
                pdo_delete('sz_yi_member',['openid'=>$_W['openid']]);
                pdo_update('sz_yi_member',[
                    'openid'=>$_W['openid'],
                    'pwd' => md5($password),
                    // 'realname'=>$xingming,
                    // 'id_card'=>$idcard,
                ],['id'=>$member['id']]);
                show_json(1,$preUrl);
            }else{
                show_json(0, '该手机号已被注册！');
            }
        }else{
            //为空,2022-01-27
            pdo_insert('sz_yi_member',['uniacid'=>$_W['uniacid'],'openid'=>$_W['openid'],'pwd' => md5($password),'realname'=>$xingming,'mobile'=>$mobile,'createtime'=>time()]);
        }

		$isreferraltrue = false;

		if (is_app()) {
			$isreferral = $app['accept'];
		} else {
			$isreferral = $this->yzShopSet['isreferral'];
		}

		if (($isreferral == 1) && !empty($_GPC['referral'])) {
			$referral = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where referralsn=:referralsn and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':referralsn' => $_GPC['referral']));

			if (!$referral) {
				show_json(0, '推荐码无效！');
			} else {
				$isreferraltrue = true;
			}
		}

		$openid = pdo_fetchcolumn('select openid from ' . tablename('sz_yi_member') . ' where mobile=:mobile and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));

		// if (empty($openid)) {
		// 	$member_data = array('uniacid' => $_W['uniacid'], 'uid' => 0, 'openid' => 'u' . md5($mobile), 'mobile' => $mobile, 'pwd' => md5($password), 'createtime' => time(), 'status' => 0, 'regtype' => 2);

		// 	if (is_app()) {
		// 		$member_data['bindapp'] = 1;
		// 	}

		// 	if (!is_weixin()) {
		// 		$member_data['nickname'] = $mobile;
		// 		$member_data['avatar'] = 'http://' . $_SERVER['HTTP_HOST'] . '/addons/sz_yi/template/mobile/default/static/images/photo-mr.jpg';
		// 	}
		// 	$member_data['isblack'] = 0; //注册默认黑名单
		// 	$member_data['realname'] = $xingming;
		// 	$member_data['id_card'] = $idcard;
		// 	$member_data['level'] = 1;
		// 	pdo_insert('sz_yi_member', $member_data);
		// 	pdo_insert('sz_yi_member_address', array(
		// 		'uniacid' => $member_data['uniacid'],
		// 		'openid' => $member_data['openid'],
		// 		'realname' => $member_data['realname'],
		// 		'mobile' => $member_data['mobile'],
		// 		'province' => $province,
		// 		'city' => $city,
		// 		'address' => $addr,
		// 		'isdefault' => 1
		// 	));
		// 	$openid = $member_data['openid'];
		// } else {
		// 	$member_data = array('pwd' => md5($password), 'regtype' => 1, 'isbindmobile' => 1);
		// 	pdo_update('sz_yi_member', $member_data, array('mobile' => $mobile, 'uniacid' => $_W['uniacid']));
		// }

		if ($isreferraltrue) {
			$member = pdo_fetch('select * from ' . tablename('sz_yi_member') . ' where mobile=:mobile and pwd!="" and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));

			if (!$member['agentid']) {
				$m_data = array('agentid' => $referral['id'], 'agenttime' => time(), 'status' => 1, 'isagent' => 1);

				if ($referral['id'] != 0) {
					$this->upgradeLevelByAgent($referral['id']);
				}

				pdo_update('sz_yi_member', $m_data, array('mobile' => $mobile, 'uniacid' => $_W['uniacid']));
				m('member')->responseReferral($this->yzShopSet, $referral, $member);
			}
		}

		// send_user_check_sms('13809703680', 'SMS_172013104', '商城会员审核');
		// $openid="ov3-bt8keSKg_8z9Wwi-zG1hRhwg";//被回复用户的openid
		// $info="有新的用户注册,请前往审核：".$this->createMobileUrl('member/membercheck');//回复的内容
		// $message = array(
		// 	'msgtype' => 'text',
		// 	'text' => array('content' => urlencode($info)),
		// 	'touser' =>$openid,
		// );
		// $account_api = WeAccount::create();
		// $status = $account_api->sendCustomNotice($message);//调用微擎内部的函数
		$lifeTime = 24 * 3600 * 3;
		session_set_cookie_params($lifeTime);
		// @session_start();
		// $cookieid = '__cookie_sz_yi_userid_' . $_W['uniacid'];
		// setcookie('member_mobile', $mobile);
		// //lotodo 20170116
		// $memberid = pdo_fetchcolumn('select id from ' . tablename('sz_yi_member') . ' where mobile=:mobile and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':mobile' => $mobile));
		// setcookie('member_id',$memberid);
		// setcookie($cookieid, base64_encode($openid));

		if (empty($preUrl)) {
			$preUrl = $this->createMobileUrl('member/login');
		}

		if ($from == 'app') {
			$preUrl = $this->createMobileUrl('shop/download');
		}

		show_json(1, $preUrl);
	}
}
// var_dump($_W['openid']);
include $this->template('member/register');