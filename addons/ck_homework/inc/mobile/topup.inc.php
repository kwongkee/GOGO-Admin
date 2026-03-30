<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
load()->func('tpl');

require "common.php";
require "public.php";

$urltk = $this->createMobileUrl('topup');
$payurl = $this->createMobileUrl('pay');
if($user_show['type'] == '1'){
	$returnurl = $this->createMobileUrl('t_index');
}elseif($user_show['type'] == '2'){
	$returnurl = $this->createMobileUrl('jz_index');
}else{
	$returnurl = $this->createMobileUrl('m_index');
}


//编辑
	
include template_app('topup');