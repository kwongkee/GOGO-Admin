<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use think\Session;
use think\Paginator;

class AdminAdverting extends model{
	public function get_wechats(){//获取公众号的名称和ID
		return Db::table('ims_account_wechats')->field('name,uniacid')->select();
	}
	
	//获取展位
	public function get_data(){
		return Db::table('ims_foll_advertising')
		->alias('a')
		->join('ims_account_wechats b','a.uniacid = b.uniacid')
		->join('ims_sz_yi_designer c','a.board = c.id')
		->field('a.*,b.name,c.pagename')
		->order('id asc')
		->select();

	}
	
	//查询展位总数
	public function get_counts(){
		return Db::table('ims_foll_advertising')->count();
	}

	//新增
	public function saves($data){
		$add=Db::table('ims_foll_advertising')->insert($data);
//		echo db('ims_foll_advertising')->fetchSql()->insert($data);
//		die;
		if($add){
			return true;
		}else{
			return false;
		}
	}
	
	//获取单条展位数据
	public function adverting_one($id){
		return Db::table('ims_foll_advertising')
		->alias('a')
		->join('ims_account_wechats b','a.uniacid = b.uniacid')
		->join('ims_sz_yi_designer c','a.board = c.id')
		->field('a.*,b.name,c.pagename')
		->where('a.id ='.$id)
		->find();
	}
	
	//查询单条
	public function get_one($id){
		return Db::table('ims_wechat_attachment')->where(['id'=>$id])->find();
	}
	//获取对应展位ID所属板块列
	public function get_board($id){
		return Db::table('ims_sz_yi_designer')
		->where('uniacid = '.$id)
		->select();
	}
	
	public function GetMod($uniacid){
		return Db::table('ims_sz_yi_designer')->field('id,pagename')->where(['uniacid'=>$uniacid])->select();
	}
	
	//编辑
	public function update_data($request){
		$update = Db::table('ims_foll_advertising')
		->where('id',$request->post('id'))
		->update(['uniacid'=>$request->post('uniacid'),
			'adv_name'=>$request->post('adv_name'),
			'type'=>$request->post('type'),
			'way'=>$request->post('way'),
			'image'=>$request->post('image'),
			'board'=>$request->post('board'),
			'money'=>$request->post('money'),
			'weighted'=>$request->post('weighted'),
			's_time'=>strtotime($request->post('s_time')),//有效时间,开始时间
			'e_time'=>strtotime($request->post('e_time')),//有效时间,结束时间
			'position'=>$request->post('position'),
			'condition'=>$request->post('condition'),
		]);
		
		if($update){
			return true;
		}else{
			return false;
		}
	}
	
	public function deleteData($id){
		if(Db::table('ims_foll_advertising')->where('id',$id)->delete()){
			return true;
		}else{
			return false;
		}
	}
}
?>