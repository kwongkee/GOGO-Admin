<?php
// Ä£¿éLTDÌá¹©
if (!defined('IN_IA')) {
	exit('Access Denied');
}

define('IS_API', true);
require IA_ROOT . '/addons/sz_yi/version.php';
require IA_ROOT . '/addons/sz_yi/defines.php';
require SZ_YI_INC . 'functions.php';
require SZ_YI_INC . 'processor.php';
require SZ_YI_INC . 'plugin/plugin_model.php';
class Sz_yiModuleProcessor extends Processor
{
	public function respond()
	{
		return parent::respond();
	}
}

?>
