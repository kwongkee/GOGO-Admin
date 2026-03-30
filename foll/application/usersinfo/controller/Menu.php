<?php
namespace app\usersinfo\controller;
use app\usersinfo\controller\BaseAdmin;
use think\Controller;
//use Util\data\Sysdb;

/**
 * 菜单管理
 */
class Menu extends BaseAdmin
{
	// 菜单列表
	public function index(){
		
		$pid = (int)input('get.pid');
		$data['lists'] = $this->db->table('foll_business_menu')->where(['parent_id'=>$pid])->lists();

		// 返回上级菜单
		$backid = 0;
		if($pid > 0) {
			$parent = $this->db->table('foll_business_menu')->where(['id'=>$pid])->item();
			$backid = $parent['parent_id'];
		}

		$this->assign('pid',$pid);
		$this->assign('backid',$backid);
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	// 保存菜单
	public function save() {
		
		$pid = (int)input('post.pid');
		$ords = input('post.ords/a');//排序
		$titles = input('post.title/a');//菜单名称
		$controllers = input('post.model/a');//控制器
		$methods = input('post.action/a');//方法
		$ishiddens = input('post.ishiddens/a');//是否隐藏
		$status = input('post.status/a');//是否禁用
		
		foreach($ords as $key=>$value) {
			// 新增
			$data['parent_id'] = $pid;
			$data['list_order'] = $value;
			$data['name'] = $titles[$key];
			$data['model'] = $controllers[$key];
			$data['action'] = $methods[$key];
			$data['ishidden'] = isset($ishiddens[$key])?1:0;
			$data['status'] = isset($status[$key])?1:0;

			if($key == 0 && $data['name']) {
				$this->db->table('foll_business_menu')->insert($data);
			}

			if($key>0){
				if($data['name']=='' && $data['model'] == '' && $data['action'] == '') {
					// 删除
					$this->db->table('foll_business_menu')->where(['id'=>$key])->delete();
				} else {//修改
					$this->db->table('foll_business_menu')->where(['id'=>$key])->update($data);
				}
			}
		}
		exit(json_encode(['code'=>0,'msg'=>'保存成功']));
	}
}
?>