<?php
// Ä£¿éLTDÌá¹©
namespace admin\api\model;

class member
{
	public function __construct()
	{
	}

	public function getCount($para)
	{
		$count = pdo_fetch('select count(*) from ' . tablename('sz_yi_member') . ' where  createtime>:createtime and uniacid=:uniacid limit 1', array(':uniacid' => $para['uniacid'], ':createtime' => $para['createtime']));
		return current($count);
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
