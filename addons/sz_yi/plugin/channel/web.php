<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class ChannelWeb extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('channel');
		$this->set = $this->getSet();
	}

	public function index()
	{
		global $_W;
		header('location: ' . $this->createPluginWebUrl('channel/manage'));
		exit();
	}

	public function level()
	{
		$this->_exec_plugin('level');
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}

	public function manage()
	{
		$this->_exec_plugin('manage');
	}

	public function apply()
	{
		$this->_exec_plugin('apply');
	}

	public function withdraw()
	{
		$this->_exec_plugin('withdraw');
	}

	public function notice()
	{
		$this->_exec_plugin('notice');
	}

	public function inventory()
	{
		$this->_exec_plugin('inventory');
	}
}

?>
