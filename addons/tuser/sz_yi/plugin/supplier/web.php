<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class SupplierWeb extends Plugin
{
	protected $set;

	public function __construct()
	{
		parent::__construct('supplier');
		$this->set = $this->getSet();
	}

	public function index()
	{
		global $_W;

		if (cv('supplier')) {
			header('location: ' . $this->createPluginWebUrl('supplier/supplier'));
			exit();
			return NULL;
		}

		if (cv('supplier')) {
			header('location: ' . $this->createPluginWebUrl('supplier/supplier_apply'));
			exit();
			return NULL;
		}

		if (cv('supplier')) {
			header('location: ' . $this->createPluginWebUrl('supplier/supplier_finish'));
			exit();
		}
	}

	public function upgrade()
	{
		$this->_exec_plugin('upgrade');
	}

	public function supplier()
	{
		$this->_exec_plugin('supplier');
	}

	public function supplier_apply()
	{
		$this->_exec_plugin('supplier_apply');
	}

	public function supplier_finish()
	{
		$this->_exec_plugin('supplier_finish');
	}

	public function supplier_for()
	{
		$this->_exec_plugin('supplier_for');
	}

	public function supplier_list()
	{
		$this->_exec_plugin('supplier_list');
	}

	public function supplier_add()
	{
		$this->_exec_plugin('supplier_add');
	}

	public function notice()
	{
		$this->_exec_plugin('notice');
	}

	public function set()
	{
		$this->_exec_plugin('set');
	}

	public function supplier_for_resu()
	{
		$this->_exec_plugin('supplier_for_resu');
	}

	public function supplier_detail()
	{
		$this->_exec_plugin('supplier_detail');
	}
}

?>
