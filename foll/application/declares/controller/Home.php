<?php
namespace app\declares\controller;
use think\Controller;
use Util\data\Sysdb;

class Home extends BaseAdmin
{
	public function index()
	{
		
//		print_r($this->_admin);
		//用户 $this->_admin
		$roles = $this->_admin['role'];
		$menus = false;
		//foll_business_groups  角色组
		$role = $this->db->table('foll_business_groups')->where(['gid'=>$this->_admin['role']])->item();
		if($role){
			// $role['rights'] = (isset($role['rights']) && $role['rights']) ? json_decode($role['rights'],true): [];
			$role['rights'] = (isset($role['rights']) && $role['rights']) ? json_decode($role['rights'],true) : [];
		}
		
		//foll_business_menu  菜单组
		if($role['rights']) {
			$where = 'id in('.implode(',',$role['rights']).') and ishidden=0 and status=0';
			$menus = $this->db->table('foll_business_menu')->where($where)->cates('id');
			$menus && $menus = $this->gettreeitems($menus);
		}
//		$site = $this->db->table('sites')->where(['names'=>'site'])->item();
//		$site && $site['values'] = json_decode($site['values'],true);
//		$this->assign('site',$site);
		$this->assign('title','跨境电商业务管理系统');
		$this->assign('role',$role);
		$this->assign('roles',$roles);
		$this->assign('menus',$menus);
		return $this->fetch();
	}
	
	private function gettreeitems($items)
	{
		$tree = array();
		foreach($items as $item)
		{
			if(isset($items[$item['parent_id']])) {
				
				$items[$item['parent_id']]['children'][] = &$items[$item['id']];
				
			} else {
				$tree[] = &$items[$item['id']];
			}
		}
		return $tree;
	}
	
	//欢迎页面
	public function welcome()
	{
		$this->assign('title','欢迎使用跨境电商业务管理系统');
		return $this->fetch();
	}
}
?>