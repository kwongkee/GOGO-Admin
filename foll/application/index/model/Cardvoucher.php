<?php
namespace app\index\model;
use think\Db;
use think\Model;
use think\Session;
use think\Loader;

class Cardvoucher extends model{
	
	public function getBusiness()
	{
		 return Db::table("ims_account_wechats")->field(['uniacid','name'])->select();
	}
	
	public function	CardvouhcerAdd($data)
	{
		Db::table("ims_foll_coupon")->insert($data);
	}
	
	public function getUniacid($id){
		return Db::table("ims_foll_business_admin")->where(['id'=>$id])->field(['uniacid'])->find();
	}
	
	public function getCouponCard($card)
	{
		return $code = implode('',$card);
	}
	
	public function getList($where,$page,$limit)
	{
		return Db::name("foll_coupon")->where($where)->order('id','desc')->limit($page,$limit)->select();
	}

    public function get_total($tableName,$where,$field)
    {
        return Db::name($tableName)->where($where)->count($field);
    }
}