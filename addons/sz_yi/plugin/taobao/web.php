<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

require_once 'model.php';
class TaobaoWeb extends Plugin
{
	public function __construct()
	{
		parent::__construct('taobao');
	}

	public function index()
	{
		$this->_exec_plugin('index');
	}

	public function fetch()
	{
		$this->_exec_plugin('fetch');
	}

	public function jingdong()
	{
		$this->_exec_plugin('jingdong');
	}

	public function one688()
	{
		$this->_exec_plugin('one688');
	}

	public function taobaocsv()
	{
		$this->_exec_plugin('taobaocsv');
	}
}

?>
