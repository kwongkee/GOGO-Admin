<?php
// Ä£¿éLTDÌá¹©
namespace Think;

class Think
{
	static public function start()
	{
		spl_autoload_register('Think\\Think::autoload');
		C(load_config(LIB_PATH . '/config/convention.php'));
		C(load_config(LIB_PATH . '/config/config.php'));
	}

	static public function autoload($class)
	{
		if (false !== strpos($class, '\\')) {
			$name = strstr($class, '\\', true);
			if (in_array($name, array('Think')) || is_dir(LIB_PATH . $name)) {
				$path = LIB_PATH;
			}
			else {
				$namespace = C('AUTOLOAD_NAMESPACE');
				$path = (isset($namespace[$name]) ? dirname($namespace[$name]) . '/' : APP_PATH);
			}

			$filename = $path . str_replace('\\', '/', $class) . EXT;

			if (is_file($filename)) {
				if (IS_WIN && (false === strpos(str_replace('/', '\\', realpath($filename)), $class . EXT))) {
					return NULL;
				}

				include $filename;
			}
		}

		return false;
	}

	static public function trace($value = '[think]', $label = '', $level = 'DEBUG', $record = false)
	{
		static $_trace = array();

		if ('[think]' === $value) {
			return $_trace;
		}

		$info = ($label ? $label . ':' : '') . print_r($value, true);
		$level = strtoupper($level);
		if ((defined('IS_AJAX') && IS_AJAX) || !C('SHOW_PAGE_TRACE') || $record) {
			Log::record($info, $level, $record);
			return NULL;
		}

		if (!isset($_trace[$level]) || (C('TRACE_MAX_RECORD') < count($_trace[$level]))) {
			$_trace[$level] = array();
		}

		$_trace[$level][] = $info;
	}
}

require_once __CORE_PATH__ . '/inc/framework/functions.php';
define('LIB_PATH', __CORE_PATH__ . '/inc/framework/');
define('EXT', '.php');

?>
