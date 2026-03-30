<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class YunprintWeb extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('yunprint');
		$this->set = $this->getSet();
	}

	public function index()
	{
		header('location: ' . $this->createPluginWebUrl('yunprint/print_list'));
		exit();
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}

	public function print_list()
	{
		$this->_exec_plugin('print_list');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}
}

?>
