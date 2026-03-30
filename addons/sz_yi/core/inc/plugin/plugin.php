<?php
// 模块LTD提供
if (!defined('IN_IA')) {
	exit('Access Denied');
}

class Plugin extends Core
{
	public $pluginname;
	public $model;

	public function __construct($name = '')
	{
		parent::__construct();
		$this->modulename = 'sz_yi';
		$this->pluginname = $name;
		$this->loadModel();

		if (strexists($_SERVER['REQUEST_URI'], '/web/')) {
			cpa($this->pluginname);
		}
		else {
			if (strexists($_SERVER['REQUEST_URI'], '/app/')) {
				$this->setFooter();
			}
		}

		$this->module['title'] = pdo_fetchcolumn('select title from ' . tablename('modules') . ' where name=\'sz_yi\' limit 1');
	}

	private function loadModel()
	{
		$modelfile = IA_ROOT . '/addons/' . $this->modulename . '/plugin/' . $this->pluginname . '/model.php';

		if (is_file($modelfile)) {
			$classname = ucfirst($this->pluginname) . 'Model';
			require $modelfile;
			$this->model = new $classname($this->pluginname);
		}
	}

	public function getSet()
	{
		return $this->model->getSet();
	}

	public function updateSet($data = array())
	{
		$this->model->updateSet($data);
	}

	public function template($filename, $type = TEMPLATE_INCLUDEPATH)
	{
		global $_W;
		$tmplateType = (isMobile() ? 'mobile' : 'pc');

		if (strstr($_SERVER['REQUEST_URI'], 'app')) {
			if (!isMobile()) {
				if (($this->yzShopSet['ispc'] == 0) || ($this->pluginname == 'designer')) {
					$tmplateType = 'mobile';
				}
			}
		}

		$set = m('common')->getSysset('shop');
		$defineDir = IA_ROOT . '/addons/sz_yi/';

		if (defined('IN_SYS')) {
			$source = IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . '/template/' . $filename . '.html';
			$compile = IA_ROOT . '/data/tpl/web/' . $_W['template'] . '/sz_yi/plugin/' . $this->pluginname . '/' . $filename . '.tpl.php';

			if (!is_file($source)) {
				$source = IA_ROOT . '/addons/sz_yi/template/' . $filename . '.html';
				$compile = IA_ROOT . '/data/tpl/web/' . $_W['template'] . '/sz_yi/' . $filename . '.tpl.php';
			}

			if (!is_file($source)) {
				$source = IA_ROOT . '/web/themes/' . $_W['template'] . '/' . $filename . '.html';
				$compile = IA_ROOT . '/data/tpl/web/' . $_W['template'] . '/' . $filename . '.tpl.php';
			}

			if (!is_file($source)) {
				$source = IA_ROOT . '/web/themes/default/' . $filename . '.html';
				$compile = IA_ROOT . '/data/tpl/web/default/' . $filename . '.tpl.php';
			}
		}
		else {
			if (is_app()) {
				$template = m('cache')->getString('app_template_shop');
			}
			else {
				if (!isMobile() && $set['ispc']) {
					$template = m('cache')->getString('template_shop_pc');
				}
				else {
					$template = m('cache')->getString('template_shop');
				}
			}

			if (empty($template)) {
				$template = 'default';
			}

			if (!is_dir(IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . '/template/' . $tmplateType . '/' . $template)) {
				$template = 'default';
			}

			$compile = IA_ROOT . '/data/app/sz_yi/plugin/' . $this->pluginname . '/' . $template . '/' . $tmplateType . '/' . $filename . '.tpl.php';
			$source = $defineDir . '/plugin/' . $this->pluginname . '/template/' . $tmplateType . '/' . $template . '/' . $filename . '.html';

			if (!is_file($source)) {
				$source = $defineDir . '/plugin/' . $this->pluginname . '/template/' . $tmplateType . '/default/' . $filename . '.html';
				$compile = IA_ROOT . '/data/app/sz_yi/plugin/' . $this->pluginname . '/default/' . $tmplateType . '/' . $filename . '.tpl.php';
			}

			if (!is_file($source)) {
				$source = $defineDir . '/template/' . $tmplateType . '/' . $template . '/' . $filename . '.html';
				$compile = IA_ROOT . '/data/app/sz_yi/' . $template . '/' . $tmplateType . '/' . $filename . '.tpl.php';
			}

			if (!is_file($source)) {
				$source = $defineDir . '/template/' . $tmplateType . '/default/' . $filename . '.html';
				$compile = IA_ROOT . '/data/app/sz_yi/default/' . $tmplateType . '/' . $filename . '.tpl.php';
			}

			if (!is_file($source)) {
				$source = $defineDir . '/template/' . $tmplateType . '/default_drug/' . $filename . '.html';
				$compile = IA_ROOT . '/data/app/sz_yi/' . $tmplateType . '/' . $filename . '.tpl.php';
			}

			if (!is_file($source)) {
				$names = explode('/', $filename);
				$pluginname = $names[0];
				$ptemplate = m('cache')->getString('template_' . $pluginname);

				if (empty($ptemplate)) {
					$ptemplate = 'default';
				}

				if (!is_dir(IA_ROOT . '/addons/sz_yi/plugin/' . $pluginname . '/template/' . $tmplateType . '/' . $ptemplate)) {
					$ptemplate = 'default';
				}

				$pfilename = $names[1];
				$source = IA_ROOT . '/addons/sz_yi/plugin/' . $pluginname . '/template/' . $tmplateType . '/' . $ptemplate . '/' . $pfilename . '.html';
			}
		}

		if (!is_file($source)) {
			exit('Error: template source \'' . $filename . '\' is not exist!');
		}

		if (DEVELOPMENT || !is_file($compile) || (filemtime($compile) < filemtime($source))) {
			shop_template_compile($source, $compile, true);
		}

		return $compile;
	}

	public function _exec_plugin($do, $web = true)
	{
		global $_GPC;

		if ($web) {
			$file = IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . '/core/web/' . $do . '.php';
		}
		else {
			$file = IA_ROOT . '/addons/sz_yi/plugin/' . $this->pluginname . '/core/mobile/' . $do . '.php';
		}

		if (!is_file($file)) {
			message('未找到控制器文件 : ' . $file);
		}

		include $file;
		exit();
	}
}

?>
