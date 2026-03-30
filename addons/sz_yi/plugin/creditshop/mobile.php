<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class CreditshopMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('creditshop');
		$this->set = $this->getSet();
	}

	public function index()
	{
		$this->_exec_plugin('index', false);
	}

	public function lists()
	{
		$this->_exec_plugin('lists', false);
	}

	public function detail()
	{
		$this->_exec_plugin('detail', false);
	}

	public function log()
	{
		$this->_exec_plugin('log', false);
	}

	public function creditlog()
	{
		$this->_exec_plugin('creditlog', false);
	}

	public function exchange()
	{
		$this->_exec_plugin('exchange', false);
	}
}

?>
