<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require_once 'model.php';
class VirtualWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('virtual');
	}

	public function index()
	{
		if (cv('virtual.temp')) {
			header('location: ' . $this->createPluginWebUrl('virtual/temp'));
			exit();
			return NULL;
		}

		if (cv('virtual.category')) {
			header('location: ' . $this->createPluginWebUrl('virtual/category'));
			exit();
		}
	}

	public function temp()
	{
		$this->_exec_plugin('temp');
	}

	public function data()
	{
		$this->_exec_plugin('data');
	}

	public function category()
	{
		$this->_exec_plugin('category');
	}

	public function import()
	{
		$this->_exec_plugin('import');
	}

	public function export()
	{
		$this->_exec_plugin('export');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}
}

?>
