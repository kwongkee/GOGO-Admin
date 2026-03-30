<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class PermWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('perm');
	}

	public function index()
	{
		if (cv('perm.role')) {
			header('location: ' . $this->createPluginWebUrl('perm/role'));
			exit();
			return NULL;
		}

		if (cv('perm.user')) {
			header('location: ' . $this->createPluginWebUrl('perm/user'));
			exit();
			return NULL;
		}

		if (cv('perm.log')) {
			header('location: ' . $this->createPluginWebUrl('perm/log'));
			exit();
			return NULL;
		}

		if (cv('perm.set')) {
			header('location: ' . $this->createPluginWebUrl('perm/set'));
			exit();
		}
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}

	public function role()
	{
		$this->_exec_plugin('role');
	}

	public function user()
	{
		$this->_exec_plugin('user');
	}

	public function log()
	{
		$this->_exec_plugin('log');
	}

	public function plugins()
	{
		$this->_exec_plugin('plugins');
	}

	public function setting()
	{
		$this->_exec_plugin('setting');
	}
}

?>
