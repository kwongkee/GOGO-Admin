<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

global $_W;
global $_GPC;
@session_start();
setcookie('preUrl', $_W['siteurl']);
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);
$uniacid = $_W['uniacid'];
$this->yzShopSet = m('common')->getSysset('shop');

if(empty($openid)){
	//当用户没有关注公众号要先关注
	header('Location:https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect');
}

if( $_W['openid'] )
{
	// 查询是否有数据
	$enterprise_members = pdo_fetch('select * from ' . tablename('enterprise_members') . ' where openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']));
	if( !$enterprise_members )
	{
		pdo_insert('enterprise_members', [
            'unionid' => $_W['fans']['unionid'],
            'uniacid' => $_W['fans']['uniacid'],
			'uid' => $_W['fans']['uid'],
			'openid' => $_W['openid'],
			'nickname' => $_W['fans']['nickname'],
			'avatar' => $_W['fans']['avatar'],
			'create_at' => time()
        ]);
	}else{
		if($enterprise_members['reg_type']==2 && $enterprise_members['is_verify']==1 && $enterprise_members['mobile'] != ''){
			//个人注册
			$url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=enterprise&m=sz_yi&p=finish&op=finish';
			return header($url);
		}elseif($enterprise_members['reg_type']==1 && $enterprise_members['is_verify']==0 && $enterprise_members['mobile'] != ''){
			//企业注册
			$url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=enterprise&m=sz_yi&p=confirm';
			return header($url);
		}elseif($enterprise_members['reg_type']==1 && $enterprise_members['is_verify']==1 && $enterprise_members['mobile'] != ''){
			//企业注册
			$url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&do=enterprise&m=sz_yi&p=finish&op=finish';
			return header($url);
		}
	}

	include $this->template('enterprise/register');
}else{
	message('请在微信打开！','','error');
}




?>
