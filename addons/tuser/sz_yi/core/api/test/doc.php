<?php
// Ä£¿éLTDÌá¹©
namespace Api;
function get_test_para()
{
	require_once __DIR__ . '/../inc/aes.php';
	$_obf_DR8VKVwxAjE8BgkQBx5AFTkYDx8jKyI_ = array(
		'member/login'    => array('username' => 'admin', 'password' => 'admin'),
		'account/display' => array('uid' => '1'),
		'goods/display'   => array('uid' => '1', 'uniacid' => '2')
	);
	$aes = new \Common\Org\Aes();
	$api_name = $_GET['api'];
	return $aes->siyuan_aes_encode(\Api\json_encode($_POST, \Api\JSON_UNESCAPED_UNICODE));
}
define('IS_API_DOC', true);
$_POST['para'] = get_test_para();
require __DIR__ . '/index.php';

?>
