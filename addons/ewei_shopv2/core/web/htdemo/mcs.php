<?php
/**
 * [WeEngine System] Copyright (c) 2014 lotodo.com
 * WeEngine is NOT a free software, it under the license terms, visited http://www.lotodo.com/ for more details.
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_member');
$dos = array('display', 'post','del', 'add', 'index_right', 'login', 'credit_stat');
$do = in_array($do, $dos) ? $do : 'display';
load()->model('mc');
if($do == 'display') {
	
	template('htdemo/index');
	
}else if($do == 'index_right'){
	
	template('htdemo/index_right');
	
}else if($do == 'login'){
	
	template('htdemo/login');
	
}

?>