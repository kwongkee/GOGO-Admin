<?php
namespace app\usersinfo\controller;
use app\usersinfo\controller\BaseAdmin;
use think\Controller;
//use Util\data\Sysdb;

/**
 * 角色管理
 */
class Roles extends BaseAdmin
{
	// 角色列表；
	public function index()
	{
		$data['roles'] = $this->db->table('foll_business_groups')->lists();
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	// 角色添加
	public function add() 
	{
		$gid = (int)input('get.gid');
		$role = $this->db->table('foll_business_groups')->where(['gid'=>$gid])->item();
		$role && $role['rights'] && $role['rights'] = json_decode($role['rights']);
		$this->assign('role',$role);

		$menus_list = $this->db->table('foll_business_menu')->where(['status'=>0])->cates('id');
		$menus = $this->gettreeitems($menus_list);
		$results = [];
		foreach ($menus as $key => $value) {
			// $_data = isset($value['children'])?$value['children']:$value;
			$value['children'] = isset($value['children'])?$this->formatMenus($value['children']):false;
			$results[] = $value;
		}
		$this->assign('menus',$results);
		return $this->fetch();
	}
	
	private function gettreeitems($items) {
		$tree = [];
		foreach ($items as $key => $item) {
			if(isset($items[$item['parent_id']])){
				$items[$item['parent_id']]['children'][] = &$items[$item['id']];
			} else {
				$tree[] = &$items[$item['id']];
			}
		}
		return $tree;
	}
	
	private function formatMenus($items,&$res = []){
		foreach ($items as $key => $item) {
			if(!isset($item['children'])){
				$res[] = $item;
			} else {
				$tem = $item['children'];
				unset($item['children']);
				$res[] = $item;
				$this->formatMenus($tem,$res);
			}
		}
		return $res;
	}
	
	public function save() {
		$gid = (int)input('post.gid');

		$data['title'] = trim(input('post.title'));
		$menus = input('post.menu/a');
		if(!$data['title']) {
			exit(json_encode(['code'=>1,'msg'=>'角色名称不能为空']));
		}

		$menus && $data['rights'] = json_encode(array_keys($menus));
		if($gid){
			$this->db->table('foll_business_groups')->where(['gid'=>$gid])->update($data);
		} else {
			$this->db->table('foll_business_groups')->insert($data);
		}
		exit(json_encode(['code'=>0,'msg'=>'保存成功']));
	}
	
	// 删除
	public function deletes(){
		$gid = (int)input('gid');
		$this->db->table('foll_business_groups')->where(['gid'=>$gid])->delete();
		exit(json_encode(['code'=>0,'msg'=>'删除成功']));
	}
	
}
?>