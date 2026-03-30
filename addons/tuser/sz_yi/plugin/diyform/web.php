<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require_once 'model.php';
class DiyformWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('diyform');
	}

	public function index()
	{
		if (cv('diyform.temp')) {
			header('location: ' . $this->createPluginWebUrl('diyform/temp'));
			exit();
			return NULL;
		}

		if (cv('diyform.category')) {
			header('location: ' . $this->createPluginWebUrl('diyform/category'));
			exit();
			return NULL;
		}

		if (cv('diyform.set')) {
			header('location: ' . $this->createPluginWebUrl('diyform/set'));
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
