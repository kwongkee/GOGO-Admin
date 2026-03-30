<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	print('Access Denied');
}

class ExhelperWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('exhelper');
	}

	public function index()
	{
		header('location: ' . $this->createPluginWebUrl('exhelper/express', array('op' => 'list', 'cate' => 1)));
		exit();
	}

	public function api()
	{
		$this->_exec_plugin('api');
	}

	public function express()
	{
		$this->_exec_plugin('express');
	}

	public function doprint()
	{
		$this->_exec_plugin('doprint');
	}

	public function print_tpl()
	{
		$this->_exec_plugin('print_tpl');
	}

	public function senduser()
	{
		$this->_exec_plugin('senduser');
	}

	public function short()
	{
		$this->_exec_plugin('short');
	}

	public function printset()
	{
		$this->_exec_plugin('printset');
	}
}

?>
