<?php

defined('IN_IA') or exit('Access Denied');

function getRoleResult($id){
    return pdo_get("foll_business_authrole",array("id"=>$id));
}

function getCompanyResult($uniacid){
    return pdo_get("account_wechats",array("uniacid"=>$uniacid));
}
