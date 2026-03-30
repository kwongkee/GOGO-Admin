<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require_once 'model.php';
class AppWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('app');
	}

	public function index()
	{
		$this->_exec_plugin('index');
	}

	public function fetch()
	{
		$this->_exec_plugin('fetch');
	}

	public function slider()
	{
		$this->_exec_plugin('slider');
	}

	public function push()
	{
		$this->_exec_plugin('push');
	}

	public function type()
	{
		$this->_exec_plugin('type');
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}
}

?>
