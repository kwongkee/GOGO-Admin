<?php
// Ä£¿éLTDÌá¹©
function sortByCreateTime($a, $b)
{
	if ($a['createtime'] == $b['createtime']) {
		return 0;
	}

	return $a['createtime'] < $b['createtime'] ? 1 : -1;
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

class CommissionMobile extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('commission');
		$this->set = $this->getSet();
		global $_GPC;
		if (($_GPC['method'] != 'register') && ($_GPC['method'] != 'myshop')) {
			$openid = m('user')->getOpenid();
			$member = m('member')->getMember($openid);
			if (($member['isagent'] != 1) || ($member['status'] != 1)) {
				header('location:' . $this->createPluginMobileUrl('commission/register'));
				exit();
			}
		}
	}

	public function index()
	{
		$this->_exec_plugin('index', false);
	}

	public function team()
	{
		$this->_exec_plugin('team', false);
	}

	public function customer()
	{
		$this->_exec_plugin('customer', false);
	}

	public function order()
	{
		$this->_exec_plugin('order', false);
	}

	public function withdraw()
	{
		$this->_exec_plugin('withdraw', false);
	}

	public function apply()
	{
		$this->_exec_plugin('apply', false);
	}

	public function shares()
	{
		$this->_exec_plugin('shares', false);
	}

	public function register()
	{
		$this->_exec_plugin('register', false);
	}

	public function myshop()
	{
		$this->_exec_plugin('myshop', false);
	}

	public function myshopset()
	{
		$this->_exec_plugin('myshopset', false);
	}

	public function log()
	{
		$this->_exec_plugin('log', false);
	}
}

?>
