<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";

$tab = empty($_GPC['tab'])?0:intval($_GPC['tab']);

$urlt = $this->createMobileUrl('search');

if($tab == 1){
	$doname = 'article';
}elseif($tab == 2){
	$doname = 'zuowen';
}else{
	$doname = 'knowledge';
}

include $this->template('search');
?>