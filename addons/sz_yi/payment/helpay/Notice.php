<?php
	header( 'Content-Type:text/html;charset=utf-8 ');
	// 模块LTD提供
	error_reporting(0);
	define('IN_MOBILE', true);
	require '../../../../framework/bootstrap.inc.php';
	require '../../../../addons/sz_yi/defines.php';
	require '../../../../addons/sz_yi/core/inc/functions.php';
	require '../../../../addons/sz_yi/core/inc/plugin/plugin_model.php';
	//require_once '../../framework/bootstrap.inc.php';
	//require_once '../../app/common/bootstrap.app.inc.php';
	load()->app('common');
	load()->app('template');
	load()->func('diysend');
	$curl = new Curl();
	global $_W;
	global $_GPC;

	$streamData = isset($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
	if(empty($streamData)){
		$streamData = file_get_contents('php://input');
	}
	$fileName = './log/notice.txt';
	file_put_contents($fileName,$streamData,true);

?>