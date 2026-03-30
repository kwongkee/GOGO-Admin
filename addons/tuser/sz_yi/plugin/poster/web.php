<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require_once 'model.php';
class PosterWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('poster');
	}

	public function index()
	{
		$this->_exec_plugin('index');
	}

	public function manage()
	{
		$this->_exec_plugin('manage');
	}

	public function log()
	{
		$this->_exec_plugin('log');
	}

	public function scan()
	{
		$this->_exec_plugin('scan');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}
}

?>
