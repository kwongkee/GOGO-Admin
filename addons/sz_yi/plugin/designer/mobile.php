<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class DesignerMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('designer');
	}

	public function index()
	{
		$this->_exec_plugin('index', false);
	}

	public function api()
	{
		$this->_exec_plugin('api', false);
	}

	public function date()
	{
		$this->_exec_plugin('date', false);
	}
}

?>
