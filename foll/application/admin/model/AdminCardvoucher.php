<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use think\Session;
use think\Loader;	

class AdminCardvoucher extends model
{
	public $config = [
        'type' =>'Layui',
        'query'=>['s'=>'AdminCardvoucher/Check'],
        'var_page'=>'page',
        'newstyle'=>true
    ];
	public function getCardvoucherList($total,$limit)
	{

		return Db::name("foll_coupon") ->alias('a')
            ->join('foll_seller_member b','a.busin_id=b.id')
            ->where('a.check_status',0)
            ->field(['a.*','b.busin_name'])
            ->paginate($limit,$total,$this->config);
	}

	public function getTableCount($table)
    {
        return Db::name($table)->count('id');
    }

    /**
     * 通过审核并设置收取发行费用标准
     * @param $where
     * @param $param
     * @throws \Exception
     */
    public function passOrReject($param){
        Db::startTrans();
        try{
            Db::name("foll_coupon_issuancefee")->insert($param);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage());
        }

    }

    /**
     * 更新审核状态
     * @param $id
     * @param $status
     * @throws \Exception
     */
    public function updateCheckStatus($id,$status){
        Db::startTrans();
        try{
            Db::name('foll_coupon')->where('id',$id)->update(['check_status'=>$status]);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 查找商家id
     * @param $id
     * @return mixed
     */
    public function getBusinIdById($id){
        return Db::name('foll_coupon')->where('id',$id)->field('busin_id')->find();
    }

}
