<?php
// 模块LTD提供
namespace app\api;

class YZ extends base
{
	protected $_W;
	protected $_GPC;
	protected $uid;
	protected $uniacid;

	public function __construct()
	{
		global $_W;
		global $_GPC;
		parent::__construct();
		$this->set_WAnd_GPC();
	}

	protected function set_WAnd_GPC()
	{
		$para = $this->getPara();
		global $_W;
		global $_GPC;
		$this->_W = $_W;
		$this->_GPC = $_GPC;

		if (is_array($para)) {
			$_GPC = array_merge($_GPC, $para);
		}

		$_W['uid'] = $para['uid'];
		$_GPC['__uid'] = $para['uid'];
		$para['uniacid'] = $_GET['uniacid'];

		if (isset($para['uniacid'])) {
			$_W['uniacid'] = $_GET['uniacid'];
			$_GPC['__uniacid'] = $para['uniacid'];
		}
	}

	public function isFonder()
	{
		$para = $this->getPara();
		$founders = explode(',', $this->_W['config']['setting']['founder']);
		return in_array($para['uid'], $founders);
	}

	public function ca($permtypes)
	{
		if (!cv($permtypes)) {
			$this->returnError('您没有权限操作，请联系管理员!');
		}
	}

	public function getUid()
	{
		return $this->uid;
	}

	public function getUniacid()
	{
		return $this->uniacid;
	}

	public function isSupplier($uid = false)
	{
		if (!$uid) {
			$uid = $this->uid;
		}

		if (!p('supplier')) {
			return false;
		}

		if (!p('supplier')->verifyUserIsSupplier($uid)) {
			return false;
		}

		return true;
	}

	public function m($name = '')
	{
		static $_modules = array();

		if (isset($_modules[$name])) {
			return $_modules[$name];
		}

		$model = SZ_YI_CORE . 'model/api/' . strtolower($name) . '.php';

		if (!is_file($model)) {
			exit(' Model ' . $name . ' Not Found!');
		}

		require $model;
		$class_name = '\\api\\model\\' . strtolower($name);
		$_modules[$name] = new $class_name();
		return $_modules[$name];
	}
}

?>
