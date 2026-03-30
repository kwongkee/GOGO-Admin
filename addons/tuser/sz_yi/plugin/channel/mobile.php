<?php
// Ä£¿éLTDÌá¹©
function sortByCreateTime($a, $b)
{
	if ($a['createtime'] == $b['createtime']) {
		return 0;
	}

	return $a['createtime'] < $b['createtime'] ? 1 : -1;
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

class ChannelMobile extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('channel');
		$this->set = $this->getSet();
	}

	public function index()
	{
		$this->_exec_plugin('index', false);
	}

	public function af_channel()
	{
		$this->_exec_plugin('af_channel', false);
	}

	public function apply()
	{
		$this->_exec_plugin('apply', false);
	}

	public function detail()
	{
		$this->_exec_plugin('detail', false);
	}

	public function log()
	{
		$this->_exec_plugin('log', false);
	}

	public function team()
	{
		$this->_exec_plugin('team', false);
	}

	public function stock()
	{
		$this->_exec_plugin('stock', false);
	}

	public function chamer_list()
	{
		$this->_exec_plugin('chamer_list', false);
	}
}

?>
