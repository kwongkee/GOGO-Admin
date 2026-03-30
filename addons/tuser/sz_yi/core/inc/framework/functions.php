<?php
// 模块LTD提供
function C($name = NULL, $value = NULL, $default = NULL)
{
	static $_config = array();

	if (empty($name)) {
		return $_config;
	}

	if (is_string($name)) {
		if (!strpos($name, '.')) {
			$name = strtoupper($name);

			if (is_null($value)) {
				return isset($_config[$name]) ? $_config[$name] : $default;
			}

			$_config[$name] = $value;
			return NULL;
		}

		$name = explode('.', $name);
		$name[0] = strtoupper($name[0]);

		if (is_null($value)) {
			return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : $default;
		}

		$_config[$name[0]][$name[1]] = $value;
		return NULL;
	}

	if (is_array($name)) {
		$_config = array_merge($_config, array_change_key_case($name, CASE_UPPER));
		return NULL;
	}
}

function load_config($file, $parse = CONF_PARSE)
{
	$ext = pathinfo($file, PATHINFO_EXTENSION);

	switch ($ext) {
	case 'php':
		return include $file;
	case 'ini':
		return parse_ini_file($file);
	case 'yaml':
		return yaml_parse_file($file);
	case 'xml':
		return (array) simplexml_load_file($file);
	case 'json':
		return json_decode(file_get_contents($file), true);
	default:
		if (function_exists($parse)) {
			return $parse($file);
		}

		E(L('_NOT_SUPPORT_') . ':' . $ext);
	}
}

function E($msg, $code = 0)
{
	throw new \Think\Exception($msg, $code);
}

function L($name = NULL, $value = NULL)
{
	static $_lang = array();

	if (empty($name)) {
		return $_lang;
	}

	if (is_string($name)) {
		$name = strtoupper($name);

		if (is_null($value)) {
			return isset($_lang[$name]) ? $_lang[$name] : $name;
		}

		if (is_array($value)) {
			$replace = array_keys($value);

			foreach ($replace as &$v) {
				$v = '{$' . $v . '}';
			}

			return str_replace($replace, $value, isset($_lang[$name]) ? $_lang[$name] : $name);
		}

		$_lang[$name] = $value;
		return NULL;
	}

	if (is_array($name)) {
		$_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
	}
}

function D($name = '', $layer = '')
{
	if (empty($name)) {
		return new \Think\Model();
	}

	static $_model = array();
	$layer = $layer ?: c('DEFAULT_M_LAYER');

	if (isset($_model[$name . $layer])) {
		return $_model[$name . $layer];
	}

	$class = 'app\\api' . '\\model\\' . $name;

	if (class_exists($class)) {
		$model = new $class(basename($name));
	}
	else if (false === strpos($name, '/')) {
		echo 2;

		if (!c('APP_USE_NAMESPACE')) {
			import('Common/' . $layer . '/' . $class);
		}
		else {
			$class = '\\Common\\' . $layer . '\\' . $name . $layer;
		}

		dump($class);
		$model = (class_exists($class) ? new $class($name) : new \Think\Model($name));
	}
	else {
		\Think\Log::record('D方法实例化没找到模型类' . $class, \Think\Log::NOTICE);
		$model = new \Think\Model(basename($name));
	}

	$_model[$name . $layer] = $model;
	return $model;
}

function parse_res_name($name, $layer, $level = 1)
{
	if (strpos($name, '://')) {
		list($extend, $name) = explode('://', $name);
	}
	else {
		$extend = '';
	}

	if (strpos($name, '/') && ($level <= substr_count($name, '/'))) {
		list($module, $name) = explode('/', $name, 2);
	}
	else {
		$module = (defined('MODULE_NAME') ? MODULE_NAME : '');
	}

	$array = explode('/', $name);

	if (!c('APP_USE_NAMESPACE')) {
		$class = parse_name($name, 1);
		import($module . '/' . $layer . '/' . $class . $layer);
	}
	else {
		$class = $module . '\\' . $layer;

		foreach ($array as $name) {
			$class .= '\\' . parse_name($name, 1);
		}

		if ($extend) {
			$class = $extend . '\\' . $class;
		}
	}

	return $class . $layer;
}

function parse_name($name, $type = 0)
{
	if ($type) {
		return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function($match) {
			return strtoupper($match[1]);
		}, $name));
	}

	return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
}

function N($key, $step = 0, $save = false)
{
	static $_num = array();

	if (!isset($_num[$key])) {
		$_num[$key] = false !== $save ? S('N_' . $key) : 0;
	}

	if (empty($step)) {
		return $_num[$key];
	}

	$_num[$key] = $_num[$key] + (int) $step;

	if (false !== $save) {
		S('N_' . $key, $_num[$key], $save);
	}
}

function G($start, $end = '', $dec = 4)
{
	static $_info = array();
	static $_mem = array();

	if (is_float($end)) {
		$_info[$start] = $end;
		return NULL;
	}

	if (!empty($end)) {
		if (!isset($_info[$end])) {
			$_info[$end] = microtime(true);
		}

		if (MEMORY_LIMIT_ON && ($dec == 'm')) {
			if (!isset($_mem[$end])) {
				$_mem[$end] = memory_get_usage();
			}

			return number_format(($_mem[$end] - $_mem[$start]) / 1024);
		}

		return number_format($_info[$end] - $_info[$start], $dec);
	}

	$_info[$start] = microtime(true);

	if (MEMORY_LIMIT_ON) {
		$_mem[$start] = memory_get_usage();
	}
}

function trace($value = '[think]', $label = '', $level = 'DEBUG', $record = false)
{
	return \Think\Think::trace($value, $label, $level, $record);
}


?>
