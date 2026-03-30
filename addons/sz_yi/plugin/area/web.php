<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require_once 'model.php';
class AreaWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('area');
	}

	public function index()
	{
		$this->_exec_plugin('index');
	}

	public function statistics()
	{
		$this->_exec_plugin('statistics');
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}
}

?>
