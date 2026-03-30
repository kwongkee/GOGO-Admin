<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class CommissionWeb extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('commission');
		$this->set = $this->getSet();
	}

	public function index()
	{
		global $_W;

		if (cv('commission.agent')) {
			header('location: ' . $this->createPluginWebUrl('commission/agent'));
			exit();
			return NULL;
		}

		if (cv('commission.level')) {
			header('location: ' . $this->createPluginWebUrl('commission/level'));
			exit();
			return NULL;
		}

		if (cv('commission.apply.view1')) {
			header('location: ' . $this->createPluginWebUrl('commission/apply', array('status' => 1)));
			exit();
			return NULL;
		}

		if (cv('commission.apply.view2')) {
			header('location: ' . $this->createPluginWebUrl('commission/apply', array('status' => 2)));
			exit();
			return NULL;
		}

		if (cv('commission.apply.view3')) {
			header('location: ' . $this->createPluginWebUrl('commission/apply', array('status' => 3)));
			exit();
			return NULL;
		}

		if (cv('commission.apply.view_1')) {
			header('location: ' . $this->createPluginWebUrl('commission/apply', array('status' => -1)));
			exit();
			return NULL;
		}

		if (cv('commission.increase')) {
			header('location: ' . $this->createPluginWebUrl('commission/increase'));
			exit();
			return NULL;
		}

		if (cv('commission.notice')) {
			header('location: ' . $this->createPluginWebUrl('commission/notice'));
			exit();
			return NULL;
		}

		if (cv('commission.cover')) {
			header('location: ' . $this->createPluginWebUrl('commission/cover'));
			exit();
			return NULL;
		}

		if (cv('commission.set')) {
			header('location: ' . $this->createPluginWebUrl('commission/set'));
			exit();
		}
	}

	public function cover()
	{
		$this->_exec_plugin('cover');
	}

	public function agent()
	{
		$this->_exec_plugin('agent');
	}

	public function level()
	{
		$this->_exec_plugin('level');
	}

	public function notice()
	{
		$this->_exec_plugin('notice');
	}

	public function increase()
	{
		$this->_exec_plugin('increase');
	}

	public function apply()
	{
		$this->_exec_plugin('apply');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}
}

?>
