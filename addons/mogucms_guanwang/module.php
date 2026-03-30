<?php
/**
 * mogucms_shouye03模块定义
 *
 * @author 永和软件
 * @url 
 */
defined('IN_IA') or exit('Access Denied');

class Mogucms_guanwangModule extends WeModule {


	public function welcomeDisplay($menus = array()) {
		//这里来展示DIY管理界面
		global $_W,$_GPC;
		include $this->template('welcome');
	}
}