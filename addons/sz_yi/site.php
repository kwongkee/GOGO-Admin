<?php
// 模块LTD提供
defined('IN_IA') || exit('Access Denied');
require_once IA_ROOT . '/addons/sz_yi/version.php';
require_once IA_ROOT . '/addons/sz_yi/defines.php';
require_once SZ_YI_INC . 'functions.php';
require_once SZ_YI_INC . 'core.php';
require_once SZ_YI_INC . 'plugin/plugin.php';
require_once SZ_YI_INC . 'plugin/plugin_model.php';
class Sz_yiModuleSite extends Core
{

	// 检测用户是否有操作权限 2019-10-21
	public function __construct()
    {
    	global $_GPC;
		// 解析登陆用户信息
        $session = json_decode(base64_decode($_GPC['__session']),true);
        // 判断用户权限
        if($session['sysTypes'] == 'system') {
            // 是原平台用户就直接返回
            return true;
        }
        // 获取权限
        $roleid = trim($session['roleid']);
        $role = pdo_get('merchat_role',['id'=>$roleid],['rolePermiss']);
        // 判断用户权限
        if(!empty($role) && ($role['rolePermiss'] == 'n')){
            //message('您无权限操作，请联系管理员！',referer(), 'info');
            message('您无权限操作，请联系管理员！');
        }
        // 通过
        return true;
    }

    public function doWebShop()
	{
		$this->_exec('doWebShop', 'index');
	}

	public function doWebOrder()
	{
		$this->_exec('doWebOrder', 'list');
	}

	public function doWebMember()
	{
		$this->_exec('doWebMember', 'list');
	}

	public function doWebFinance()
	{
		$this->_exec('doWebFinance', 'log');
	}

	public function doWebStatistics()
	{
		$this->_exec('doWebStatistics', 'sale');
	}

	public function doWebPlugins()
	{
		$this->_exec('doWebPlugins', 'list');
	}

	public function doWebSysset()
	{
		$this->_exec('doWebSysset', 'sysset');
	}

	public function doWebMeet()
	{
		$this->_exec('doWebMeet', 'meet');
	}

	public function doWebRest()
	{
		$this->_exec('doWebRest', 'rest');
	}

	public function doWebPlugin()
	{
		global $_W;
		global $_GPC;
		require_once SZ_YI_INC . 'plugin/plugin.php';
		$plugins = m('plugin')->getAll();
		$p = $_GPC['p'];
		$file = SZ_YI_PLUGIN . $p . '/web.php';

		if (!is_file($file)) {
			message('未找到插件 ' . $plugins[$p] . ' 入口方法');
		}

		require $file;
		$pluginClass = ucfirst($p) . 'Web';
		$plug = new $pluginClass($p);
		$method = strtolower($_GPC['method']);

		if (empty($method)) {
			$plug->index();
			exit();
		}

		if (method_exists($plug, $method)) {
			$plug->$method();
			exit();
		}

		trigger_error('Plugin Web Method ' . $method . ' not Found!');
	}

	public function doMobilePlugin()
	{
		global $_W;
		global $_GPC;
		require_once SZ_YI_INC . 'plugin/plugin.php';
		$plugins = m('plugin')->getAll();
		$p = $_GPC['p'];
		$file = SZ_YI_PLUGIN . $p . '/mobile.php';

		if (!is_file($file)) {
			message('未找到插件 ' . $plugins[$p] . ' 入口方法');
		}

		require $file;
		$pluginClass = ucfirst($p) . 'Mobile';
		$plug = new $pluginClass($p);
		$method = strtolower($_GPC['method']);

		if (empty($method)) {
			$plug->index();
			exit();
		}

		if (method_exists($plug, $method)) {
			$plug->$method();
			exit();
		}

		trigger_error('Plugin Mobile Method ' . $method . ' not Found!');
	}

	public function doMobileCart()
	{
		$this->_exec('doMobileShop', 'cart', false);
	}

	public function doMobileFavorite()
	{
		$this->_exec('doMobileShop', 'favorite', false);
	}

	public function doMobileUtil()
	{
		$this->_exec('doMobileUtil', '', false);
	}

	public function doMobileMember()
	{
		$this->_exec('doMobileMember', 'center', false);
	}

	public function doMobileShop()
	{
		global $_W;
		global $_GPC;
		$this->saveAction([
			'name'=>mt_rand(1111,99999),
			'action'=>$_COOKIE['member_mobile']==""?"游客访问":"会员访问",
			'url' =>$_W['siteurl'],
			'menu'=>$_GPC['p'],
			'ip' =>getRealIp(),
			'create_time'=>time()
		]);
		$this->_exec('doMobileShop', 'index', false);
	}

	public function doMobileOrder()
	{
		$this->_exec('doMobileOrder', 'list', false);
	}

	public function doMobileMeet()
	{
		$this->_exec('doMobileMeet', 'index', false);
	}

	public function doMobileRest()
	{
		$this->_exec('doMobileRest', 'index', false);
	}

	public function doMobileApi()
	{
		$this->_exec('doMobileApi', 'index', false);
	}

	public function payResult($params)
	{
		return m('order')->payResult($params);
	}

	public function getAuthSet()
	{
		global $_W;
		$set = pdo_fetch('select sets from ' . tablename('sz_yi_sysset') . ' order by id asc  limit 1');
		$sets = iunserializer($set['sets']);

		if (is_array($sets)) {
			return is_array($sets['auth']) ? $sets['auth'] : array();
		}

		return array();
	}

	public function doWebAuth()
	{
		$this->_exec('doWebSysset', 'auth', true);
	}

	public function doWebUpgrade()
	{
		$this->_exec('doWebSysset', 'upgrade', true);
	}

	public function doMobileWechatOrder()
	{
		$this->_execFront('doWebOrder', 'list', false);
	}

	public function saveAction($data)
	{
		pdo_insert('sz_yi_member_operating_log',$data);
	}

	public function doMobileEnterprise()
	{
		global $_W;
		global $_GPC;
		$this->_exec('doMobileEnterprise', 'index', false);
	}

	public function doMobileDistribution()
	{
		global $_W;
		global $_GPC;
		$this->_exec('doMobileDistribution', 'index', false);
	}

	public function doMobileDomestic()
	{
		$this->_exec('doMobileDomestic', 'index',false);
	}

	public function doMobileAccount()
	{
		$this->_exec('doMobileAccount', 'index',false);
	}

	public function doMobileFreight()
	{
		$this->_exec('doMobileFreight', 'index',false);
	}

	public function doMobileDeclare()
	{
		$this->_exec('doMobileDeclare', 'index',false);
	}

	public function doMobilePolicy()
	{
		$this->_exec('doMobilePolicy', 'index',false);
	}

	public function doMobileHscode()
	{
		$this->_exec('doMobileHscode', 'index',false);
	}

	public function doMobileWarehouse()
	{
		$this->_exec('doMobileWarehouse', 'index',false);
	}

	public function doMobileApproval()
	{
		$this->_exec('doMobileApproval', 'index',false);
	}

	public function doMobileFesearch()
	{
		$this->_exec('doMobileFesearch', 'index',false);
	}

	public function doMobilePfcexpress()
	{
		$this->_exec('doMobilePfcexpress', 'index',false);
	}

	public function doMobileBehalf()
	{
		$this->_exec('doMobileBehalf', 'index',false);
	}

	public function doMobileOnlinepay()
	{
		$this->_exec('doMobileOnlinepay', 'index',false);
	}
	
	public function doMobileCheckprice()
	{
		$this->_exec('doMobileCheckprice', 'index',false);
	}
	
	public function doMobileGather()
	{
		$this->_exec('doMobileGather', 'index',false);
	}

	public function doMobileCustomer()
	{
		$this->_exec('doMobileCustomer', 'index',false);
	}
}

?>
