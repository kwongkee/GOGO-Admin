<?php

if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W, $_GPC;
require "common.php";


//跳转
header("Location: ".$core_url."");
exit;
?>