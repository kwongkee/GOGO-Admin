<?php
// Ä£¿éLTDÌá¹©
namespace api;

class AutoLoader
{
	public function __construct()
	{
		spl_autoload_register(array($this, 'spl_autoload_register'));
	}

	public function spl_autoload_register($full_class_name)
	{
		$namespace = substr($full_class_name, 0, strrpos($full_class_name, '\\'));

		if (empty($namespace)) {
			return false;
		}

		$dir = self::_mapNamespaceToDir($namespace);
		$class_name = $this->_getClassName($full_class_name);
		require __API_ROOT__ . '/../' . $dir . '/' . $class_name . '.php';
	}

	private function _getClassName($full_class_name)
	{
		$array = explode('\\', $full_class_name);
		$name = array_pop($array);
		return $name;
	}

	private function _mapNamespaceToDir($namespace)
	{
		$dir = '';

		switch ($namespace) {
		case 'Util':
			$dir = __API_ROOT__ . '/../inc/';
			break;

		case 'LeanCloud':
			$dir = __API_ROOT__ . '/../inc/plugin/vendor/';
			break;

		default:
			break;
		}

		$dir .= str_replace('\\', '/', $namespace);
		return $dir;
	}
}

final class Dispatcher
{
	private $api_name_arr;
	private $api_name;

	public function __construct($api_name)
	{
		$this->api_name = $api_name;
		$this->api_name_arr = explode('/', $api_name);
	}

	public function getControllerPatch()
	{
		$_obf_DVsmCSEyCAI_CBQUCzUqLw09IjEXHjI_ = $this->getControllerGroupName();
		$controller_name = $this->getControllerName();
		return __API_ROOT__ . '/controller/' . $_obf_DVsmCSEyCAI_CBQUCzUqLw09IjEXHjI_ . '/' . $controller_name . '.php';
	}

	public function getControllerGroupName()
	{
		$_obf_DVsmCSEyCAI_CBQUCzUqLw09IjEXHjI_ = $this->api_name_arr[0];
		return $_obf_DVsmCSEyCAI_CBQUCzUqLw09IjEXHjI_;
	}

	public function getControllerName()
	{
		$controller_name = $this->api_name_arr[1];
		return $controller_name;
	}

	public function getMethodName()
	{
		$method_name = (isset($this->api_name_arr[2]) ? $this->api_name_arr[2] : 'index');
		return $method_name;
	}
}

final class Run
{
	const CONTROLLER_NAME_SPACE = '\\api\\controller\\';

	private $dispatch;

	public function __construct()
	{
		$this->dispatch = new Dispatcher($_GET['api']);
		new AutoLoader();
		$this->run();
	}

	public function run()
	{
		require $this->dispatch->getControllerPatch();
		$_obf_DQoiLDgUBzMkKBUXFD4ODDYOFzc3MTI_ = $this->_getControllerFullName();
		$method_name = $this->dispatch->getMethodName();
		$controller_obj = new $_obf_DQoiLDgUBzMkKBUXFD4ODDYOFzc3MTI_();
		$controller_obj->$method_name();
	}

	private function _getControllerFullName()
	{
		$_obf_DVsmCSEyCAI_CBQUCzUqLw09IjEXHjI_ = $this->dispatch->getControllerGroupName();
		$controller_name = $this->dispatch->getControllerName();
		$_obf_DQoiLDgUBzMkKBUXFD4ODDYOFzc3MTI_ = $this::CONTROLLER_NAME_SPACE . $_obf_DVsmCSEyCAI_CBQUCzUqLw09IjEXHjI_ . '\\' . $controller_name;
		return $_obf_DQoiLDgUBzMkKBUXFD4ODDYOFzc3MTI_;
	}
}

define('IN_SYS', true);
define('__API_ROOT__', __DIR__);
define('__BASE_ROOT__', __DIR__ . '/../../../..');
require_once __BASE_ROOT__ . '/framework/bootstrap.inc.php';
require_once __BASE_ROOT__ . '/addons/sz_yi/defines.php';
require_once __BASE_ROOT__ . '/addons/sz_yi/core/inc/functions.php';
require_once __BASE_ROOT__ . '/addons/sz_yi/core/inc/plugin/plugin_model.php';
require_once __BASE_ROOT__ . '/addons/sz_yi/core/inc/aes.php';
require_once __BASE_ROOT__ . '/addons/sz_yi/core/inc/core.php';
$_GET['api'] = ltrim($_GET['api'], '/');
require_once __API_ROOT__ . '/controller/YZ.class.php';
new Run();

?>
