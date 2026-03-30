<?php
/**
 * [WeEngine System] Copyright (c) 2014 lotodo.com
 * WeEngine is NOT a free software, it under the license terms, visited http://www.lotodo.com/ for more details.
 */

define('IN_GW', true);

if(in_array($action, array('profile', 'device', 'callback', 'appstore', 'sms'))) {
	$do = $action;
	$action = 'redirect';
}
if($action == 'touch') {
	exit('success');
}
