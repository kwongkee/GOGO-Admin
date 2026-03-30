<?php

defined('IN_IA') or exit('Access Denied');

global $_W;
global $_GPC;

//获取openid
$openid = m('user')->getOpenid();
$popenid = m('user')->islogin();
$openid = ($openid ? $openid : $popenid);
$member = m('member')->getMember($openid);

include $this->template('member/travel_express/index');