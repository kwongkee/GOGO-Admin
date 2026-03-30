<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use think\Session;

class AdvertingManagement extends model
{
	public function get_order(){
		return Db::table('ims_foll_advertising_order')
		->alias('a')
		->join('ims_foll_advertising b','a.adv_id = b.id')
		->join('ims_foll_business_admin c','a.business_id = c.id')
		->field('a.*,b.adv_name,c.user_name')
		->select();
	}
	
	public function order_one($id){
		return Db::table('ims_foll_advertising_order')
		->alias('a')
		->join('ims_foll_advertising b','a.adv_id = b.id')
		->field('a.*,b.way,b.type,b.image')
		->where(['a.id'=>$id])
		->find();
	}
	
	public function order_ones($id){
		return Db::table('ims_foll_advertising_order')
		->alias('a')
		->join('ims_foll_advertising b','a.adv_id = b.id')
		->join('ims_foll_advertising_content c','a.id = c.order_id')
		->field('a.*,b.way,b.type,b.image,c.content,c.status,c.url,c.min_times')
		->where(['a.id'=>$id])
		->find();
	}
	
	public function get_invoice(){
		return Db::table('ims_foll_advertising_invoice')
		->alias('a')
		->join('ims_foll_advertising_order b','a.order_id = b.id')
		->join('ims_foll_business_admin c','b.business_id = c.id')
		->field('a.*,b.ordersn,c.user_name')
		->select();
	}
	
	public function upload_adv($data){
		$add=Db::table('ims_foll_advertising_content')->insert($data);
		if($add){
			return true;
		}else{
			return false;
		}
	}
}
?>