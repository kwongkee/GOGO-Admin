<?php
// Ä£¿éLTDÌá¹©
namespace Api;
function get_test_para()
{
	require_once __DIR__ . '/../inc/aes.php';
	$_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_ = \Api\file_get_contents(__DIR__ . '/test/para.json');
	$_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_ = \Api\json_decode($_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_, \Api\true);
	$aes = new Aes('hrbin-yunzs-2016', '');
	$api_name = \Api\explode('/', $_GET['api']);
	$group_name = \Api\array_shift($api_name);
	$method_name = \Api\array_shift($api_name);
	$para = $_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_[$group_name]['method'][$method_name]['para'];
	return $aes->siyuan_aes_encode(\Api\json_encode($para));
}
define('IS_TEST', true);

if (!function_exists('dump')) {
	function dump($var, $echo = true, $label = NULL, $strict = true)
	{
		if (!defined('IS_TEST')) {
			return NULL;
		}

		$label = ($label === null ? '' : rtrim($label) . ' ');

		if (!$strict) {
			if (ini_get('html_errors')) {
				$output = print_r($var, true);
				$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
			}
			else {
				$output = $label . print_r($var, true);
			}
		}
		else {
			ob_start();
			var_dump($var);
			$output = ob_get_clean();

			if (!extension_loaded('xdebug')) {
				$output = preg_replace('/\\]\\=\\>\\n(\\s+)/m', '] => ', $output);
				$output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
			}
		}

		if ($echo) {
			echo $output;
			return null;
		}

		return $output;
	}
}

$_POST['para'] = get_test_para();
require __DIR__ . '/index.php';

?>
