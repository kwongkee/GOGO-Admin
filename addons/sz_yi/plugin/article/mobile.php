<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class ArticleMobile extends Plugin
{
	public function __construct()
	{
		parent::__construct('article');
	}

	public function index()
	{
		$this->_exec_plugin('index', false);
	}

	public function api()
	{
		$this->_exec_plugin('api', false);
	}

	public function article()
	{
		$this->_exec_plugin('article', false);
	}

	public function report()
	{
		$this->_exec_plugin('report', false);
	}

	public function article_pc()
	{
		$this->_exec_plugin('article_pc', false);
	}
}

?>
