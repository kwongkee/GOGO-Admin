<?php
// 模块LTD提供
namespace Api;
function get_test_para()
{
	$_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_ = \Api\file_get_contents(__DIR__ . '/para.json');
	$_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_ = \Api\json_decode($_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_, \Api\true);

	if (!\Api\is_array($_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_)) {
		\Api\dump('para.json格式错误');
		return array();
	}

	$api_name = \Api\explode('/', $_GET['api']);
	$group_name = \Api\array_shift($api_name);
	$method_name = \Api\array_shift($api_name);

	if (!isset($_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_[$group_name]['method'][$method_name]['para'])) {
		\Api\dump('参数为空');
		return array();
	}

	$para = $_obf_DTUePzkSHAIIAgUOJxIZFgYCMzAeXAE_[$group_name]['method'][$method_name]['para'];
	return $para;
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

$_POST = get_test_para();
require __DIR__ . '/../../../index.php';

?>
