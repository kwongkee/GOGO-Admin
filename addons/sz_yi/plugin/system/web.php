<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require_once 'model.php';
class SystemWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('system');
	}

	public function index()
	{
		global $_W;

		if (cv('system.clear')) {
			header('location: ' . $this->createPluginWebUrl('system/clear'));
			exit();
			return NULL;
		}

		if (cv('system.transfer')) {
			header('location: ' . $this->createPluginWebUrl('system/transfer'));
			exit();
			return NULL;
		}

		if (cv('system.copyright')) {
			header('location: ' . $this->createPluginWebUrl('system/copyright'));
			exit();
			return NULL;
		}

		if (cv('system.backup')) {
			header('location: ' . $this->createPluginWebUrl('system/backup'));
			exit();
			return NULL;
		}

		if (cv('system.commission')) {
			header('location: ' . $this->createPluginWebUrl('system/commission'));
			exit();
			return NULL;
		}

		if (cv('system.replacedomain')) {
			header('location: ' . $this->createPluginWebUrl('system/replacedomain'));
			exit();
		}
	}

	public function clear()
	{
		$this->_exec_plugin('clear');
	}

	public function transfer()
	{
		$this->_exec_plugin('transfer');
	}

	public function copyright()
	{
		$this->_exec_plugin('copyright');
	}

	public function backup()
	{
		$this->_exec_plugin('backup');
	}

	public function commission()
	{
		$this->_exec_plugin('commission');
	}

	public function replacedomain()
	{
		$this->_exec_plugin('replacedomain');
	}
}

?>
