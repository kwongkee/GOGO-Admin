<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class FansWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('fans');
	}

	public function index()
	{
		global $_W;

		if (cv('fans.member')) {
			header('location: ' . $this->createPluginWebUrl('fans/member'));
			exit();
		}

		if (cv('fans.agent')) {
			header('location: ' . $this->createPluginWebUrl('fans/agent'));
			exit();
		}
	}

	public function member()
	{
		$this->_exec_plugin('member');
	}

	public function agent()
	{
		$this->_exec_plugin('agent');
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}
}

?>
