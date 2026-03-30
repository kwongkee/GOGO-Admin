<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class ReturnMobile extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('return');
		$this->set = $this->getSet();
		global $_GPC;
	}

	public function task()
	{
		$this->_exec_plugin('task', false);
	}

	public function return_queue()
	{
		$this->_exec_plugin('return_queue', false);
	}

	public function return_log()
	{
		$this->_exec_plugin('return_log', false);
	}
}

?>
