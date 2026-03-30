<?php
namespace app\declares\controller;
use think\Controller;
use Util\data\Sysdb;

class BaseAdmin extends Controller
{
	public function __construct() {
		parent::__construct();
		$this->_admin = session('admin');
		// 未登录的用户不允许访问
		if(!$this->_admin){
			//header('Location:/admins.php/admins/Account/login');
			Redirects('account/login');
		}
		$this->admin = session('admin');//登录数据信息
		$this->assign('admin',$this->_admin);
		$this->db = new Sysdb;
		// 判断用户是否有权限
	}
}
?>