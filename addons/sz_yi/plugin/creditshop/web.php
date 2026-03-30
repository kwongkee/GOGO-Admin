<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class CreditshopWeb extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('creditshop');
		$this->set = $this->getSet();
	}

	public function index()
	{
		global $_W;

		if (cv('creditshop.cover')) {
			header('location: ' . $this->createPluginWebUrl('creditshop/cover'));
			exit();
			return NULL;
		}

		if (cv('creditshop.goods')) {
			header('location: ' . $this->createPluginWebUrl('creditshop/goods'));
			exit();
			return NULL;
		}

		if (cv('creditshop.category')) {
			header('location: ' . $this->createPluginWebUrl('creditshop/category'));
			exit();
			return NULL;
		}

		if (cv('creditshop.adv')) {
			header('location: ' . $this->createPluginWebUrl('creditshop/adv'));
			exit();
			return NULL;
		}

		if (cv('creditshop.log.view0')) {
			header('location: ' . $this->createPluginWebUrl('creditshop/log', array('type' => 0)));
			exit();
			return NULL;
		}

		if (cv('creditshop.log.view1')) {
			header('location: ' . $this->createPluginWebUrl('creditshop/log', array('type' => 1)));
			exit();
			return NULL;
		}

		if (cv('creditshop.notice')) {
			header('location: ' . $this->createPluginWebUrl('creditshop/notice'));
			exit();
			return NULL;
		}

		if (cv('creditshop.set')) {
			header('location: ' . $this->createPluginWebUrl('creditshop/set'));
			exit();
		}
	}

	public function cover()
	{
		$this->_exec_plugin('cover');
	}

	public function category()
	{
		$this->_exec_plugin('category');
	}

	public function goods()
	{
		$this->_exec_plugin('goods');
	}

	public function adv()
	{
		$this->_exec_plugin('adv');
	}

	public function log()
	{
		$this->_exec_plugin('log');
	}

	public function notice()
	{
		$this->_exec_plugin('notice');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}
}

?>
