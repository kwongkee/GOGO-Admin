<?php
/**
 * 用户管理控制器
 */
defined('IN_IA') or exit('Access Denied');
uni_user_permission_check('mc_member');
$dos = array('display', 'post','del', 'add');
$do = in_array($do, $dos) ? $do : 'display';
load()->model('mc');
if($do == 'display') {
	
}
?>