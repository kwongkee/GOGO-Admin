<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class CashierWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('cashier');
	}

	public function index()
	{
		global $_W;

		if (cv('cashier')) {
			header('location: ' . $this->createPluginWebUrl('cashier/store'));
			exit();
		}
	}

	public function store()
	{
		$this->_exec_plugin('store');
	}

	public function statistics()
	{
		$this->_exec_plugin('statistics');
	}

	public function withdraw()
	{
		$this->_exec_plugin('withdraw');
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}

	public function withdraws()
	{
		$this->_exec_plugin('withdraws');
	}
}

?>
