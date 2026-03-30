<?php

namespace app\index\model;
use think\Db;
use think\Model;
use think\Session;

class Advertising extends Model
{
	//获取所有展位信息
	public function Get_Data()
	{
		return Db::table('ims_foll_advertising')->select();
	}
	
	//获取单条展位信息
	public function adv_one($id){
		return Db::table('ims_foll_advertising')->where('id',$id)->find();
	}
	
	//添加订单
	public function add_data($data){
		$add=Db::table('ims_foll_advertising_order')->insert($data);
		if($add){
			return true;
		}else{
			return false;
		}
	}
	
	//所有已支付完成的订单,完成才可开发票,且该订单未开过发票
	public function all_pay_orders($order_ids){
		$admin_data=Session::get('UserResutlt');
		return Db::table('ims_foll_advertising_order')
		->where(['id','in',$order_ids])
		->select();
	}
	
	public function add_invoice($data){
		$add=Db::table('ims_foll_advertising_invoice')->insert($data);
		if($add){
			return true;
		}else{
			return false;
		}
	}
	
	public function get_invoice(){
		$admin_data=Session::get('UserResutlt');
		return Db::table('ims_foll_advertising_invoice')
		->alias('a')
		->join('ims_foll_advertising_order b','a.order_id = b.id')
		->field('a.*,b.ordersn')
		->where(['a.business_id'=>$admin_data['id']])
		->select();
	}
	
	
	
}
?>