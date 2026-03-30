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

class BonusMobile extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('bonus');
		global $_GPC;
		$this->set = $this->getSet();
		$openid = m('user')->getOpenid();
		$isbonus = $this->model->isLevel($openid);
		if (($isbonus == false) && ($_GPC['method'] != 'register')) {
			if (($_GPC['method'] == 'agent_info') || ($_GPC['method'] == 'agency')) {
				return NULL;
			}

			redirect($this->createPluginMobileUrl('bonus/register'));
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

	public function order_area()
	{
		$this->_exec_plugin('order_area', false);
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

	public function log()
	{
		$this->_exec_plugin('log', false);
	}

	public function agent_info()
	{
		$this->_exec_plugin('agent_info', false);
	}

	public function agency()
	{
		$this->_exec_plugin('agency', false);
	}
}

?>
