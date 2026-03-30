<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class HelperWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('helper');
	}

	public function index()
	{
		$this->_exec_plugin('index');
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}
}

?>
