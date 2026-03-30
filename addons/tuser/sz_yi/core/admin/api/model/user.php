<?php
// Ä£¿éLTDÌá¹©
namespace admin\api\model;

class user
{
	public function __construct()
	{
	}

	public function getInfo($para, $fields = '*')
	{
		$info = pdo_fetch('select ' . $fields . ' from ' . tablename('sz_yi_member') . ' where  openid=:openid and uniacid=:uniacid limit 1', array(':uniacid' => $para['uniacid'], ':openid' => $para['openid']));
		return $info;
	}

	public function saveProfile($uid, $profile)
	{
		$exist = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('users_profile') . ' WHERE `uid` = :uid', array(':uid' => $uid));

		if ($exist == '0') {
			$profile['uid'] = $uid;
			pdo_insert('users_profile', $profile);
			return NULL;
		}

		pdo_update('users_profile', $profile, array('uid' => $uid));
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
