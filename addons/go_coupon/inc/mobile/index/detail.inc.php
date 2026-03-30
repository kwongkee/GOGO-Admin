<?php

defined('IN_IA') or exit('Access Denied');

require_once IA_ROOT . "/framework/class/curl.class.php";

global $_GPC, $_W;

if ($_GPC['cid']==""&&!is_numeric($_GPC['cid'])){
    message('参数错误','','error');
}

$title ='详情';
include $this->template("index/detail");