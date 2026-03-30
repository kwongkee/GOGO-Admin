<?php

namespace app\admin\model;
use think\Model;
use think\Db;
class File extends Model{
    public function addEnterprise($data)
    {
        return Db::table('ims_enterprise')->insertAll($data);
//      return Db::table('ims_enterprise')->getLastSql();
    }
    public function addSummaryEnterprise($data)
    {
        return Db::table('ims_summary_enterprise')->insert($data);
    }
    //拿到所有商户ID
    public function businessID($start_time,$end_time){
    	return Db::query("select a.id,a.uniacid,b.user_email from ims_abc as a left join ims_foll_business_admin as b on a.uniacid=b.uniacid where a.pay_time>={$start_time} and a.pay_time<={$end_time} and a.pay_status = 1 and b.user_pid=0 group by a.uniacid");
    }
    //拿到昨天接口所有数据api_type
    public function api_type_id($start_time,$end_time){
    	return Db::query("select api_type from ims_enterprise where time>={$start_time} and time<={$end_time} group by api_type");
    }
    //当前商户对账 通过银企支付的
    public function businessInfo($uniacid,$start_time,$end_time){
        return Db::query("select b.upOrderId,a.pay_account,a.ordersn,a.pay_time from ims_abc as a left join ims_abcd as b on a.ordersn = b.ordersn where EXISTS (select c.flow_number,c.pay_money,c.op_order_id from ims_enterprise as c where b.upOrderId=c.flow_number) and a.uniacid = $uniacid and a.pay_time>$start_time and a.pay_time<$end_time order by a.pay_time asc");

    }
    //当前商户的所有银企支付的
    public function apiBusinessInfo($uniacid,$start_time,$end_time){
    	return Db::query("select c.flow_number,c.pay_money,c.op_order_id,c.time,c.api_type from ims_enterprise as c where EXISTS (select b.upOrderId,a.pay_account,a.ordersn,a.pay_time from ims_abc as a left join ims_abcd as b on a.ordersn = b.ordersn where b.upOrderId=c.flow_number and a.uniacid = $uniacid and a.pay_time>$start_time and a.pay_time<$end_time order by a.pay_time asc)");
    }
    //银企对账 昨天所有数据
    public function api_data($api_type){
    	return Db::query("select c.id,c.api_type,c.flow_number,c.pay_money,c.op_order_id,c.time from ims_enterprise as c where EXISTS (select a.pay_account,a.pay_time,b.upOrderId from ims_abc as a left join ims_abcd as b on a.ordersn = b.ordersn where b.upOrderId=c.flow_number) and c.api_type = $api_type order by c.time asc");
    }
    public function gogo_data($api_type){
    	return Db::query("select a.id,b.upOrderId,a.pay_account,a.ordersn,a.pay_time from ims_abc as a left join ims_abcd as b on a.ordersn = b.ordersn where EXISTS (select c.flow_number,c.pay_money,c.op_order_id from ims_enterprise as c where b.upOrderId=c.flow_number and c.api_type = $api_type) order by a.pay_time asc");
    }
    //gogo商城昨天有数据
    //拿到相同的数据api中gogo拿出相同的api数据 前提流水号相同
    public function alike_api($api_type){
//      return Db::query("select `order`,`pay`,`time` from  a  where EXISTS (select b.order,b.pay,b.time from b where a.order = b.order and a.pay = b.pay and a.time=b.time)");
        return Db::query("select c.id,c.flow_number,c.pay_money,c.op_order_id,c.time from ims_enterprise as c where EXISTS (select a.pay_account,a.pay_time,b.upOrderId from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where b.upOrderId=c.flow_number and a.pay_account = c.pay_money) and c.api_type = $api_type order by c.time asc");
    }
    //拿到相同的数据api中gogo拿出相同的gogo数据
    public function alike_gogo($api_type){
    	return Db::query("select a.id,b.upOrderId,a.pay_account,a.ordersn,a.pay_time from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where EXISTS (select c.flow_number,c.pay_money,c.op_order_id from ims_enterprise as c where b.upOrderId=c.flow_number and a.pay_account = c.pay_money and c.api_type = $api_type) order by a.pay_time asc");
    }
    //拿到不相同的数据 api中gogo拿出不相同的api数据
    public function on_alike_api($api_type){
//  	return Db::query("select `order`,`pay`,`time` from  a  where EXISTS (select b.order,b.pay,b.time from b where a.order=b.order) and NOT EXISTS (select b.order,b.pay,b.time from b where a.pay = b.pay and a.time=b.time) limit 1,10");
        return Db::query("select c.flow_number,c.pay_money,c.op_order_id,c.time from ims_enterprise as c where EXISTS (select a.pay_account,a.pay_time,b.upOrderId from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where b.upOrderId=c.flow_number) and NOT EXISTS (select a.pay_account,a.pay_time,b.upOrderId from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where a.pay_account = c.pay_money) and c.api_type = $api_type order by c.time asc");

    }
    //拿到不相同的数据 api中gogo拿出不相同的api数据
    public function on_alike_gogo($api_type){
//  	return Db::query("select `order`,`pay`,`time` from  b  where EXISTS (select a.order,a.pay,a.time from a where a.order=b.order) and  NOT EXISTS (select a.order,a.pay,a.time from a where a.pay = b.pay and a.time=b.time) limit 1,10");
        return Db::query("select b.upOrderId,a.pay_account,a.ordersn,a.pay_time from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where EXISTS (select c.flow_number,c.pay_money,c.op_order_id from ims_enterprise as c where b.upOrderId=c.flow_number) and NOT EXISTS (select c.flow_number,c.pay_money,c.op_order_id from ims_enterprise as c where a.pay_account = c.pay_money and c.api_type = $api_type) order by a.pay_time asc");

    }
        //拿到相同的数据api中gogo拿出相同的api数据 前提流水号相同 商户对账
    public function alike_api_busin($business_id){
//      return Db::query("select `order`,`pay`,`time` from  a  where EXISTS (select b.order,b.pay,b.time from b where a.order = b.order and a.pay = b.pay and a.time=b.time)");
        return Db::query("select c.id,c.flow_number,c.pay_money,c.op_order_id,c.time from ims_enterprise as c where EXISTS (select a.pay_account,a.pay_time,b.upOrderId from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where b.upOrderId=c.flow_number and a.pay_account = c.pay_money and a.business_id = $business_id) order by c.time asc");
    }
    //拿到相同的数据api中gogo拿出相同的gogo数据户  商户对账
    public function alike_gogo_busin($business_id){
    	return Db::query("select a.id,b.upOrderId,a.pay_account,a.ordersn,a.pay_time from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where EXISTS (select c.flow_number,c.pay_money,c.op_order_id from ims_enterprise as c where b.upOrderId=c.flow_number and a.pay_account = c.pay_money) and c.business_id = $business_id order by a.pay_time asc");
    }
    //拿到不相同的数据 api中gogo拿出不相同的api数据  商户对账
    public function on_alike_api_busin($business_id){
//  	return Db::query("select `order`,`pay`,`time` from  a  where EXISTS (select b.order,b.pay,b.time from b where a.order=b.order) and NOT EXISTS (select b.order,b.pay,b.time from b where a.pay = b.pay and a.time=b.time) limit 1,10");
        return Db::query("select c.flow_number,c.pay_money,c.op_order_id,c.time from ims_enterprise as c where EXISTS (select a.pay_account,a.pay_time,b.upOrderId from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where b.upOrderId=c.flow_number) and NOT EXISTS (select a.pay_account,a.pay_time,b.upOrderId from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where a.pay_account = c.pay_money and a.business_id = $business_id) order by c.time asc");

    }
    //拿到不相同的数据 api中gogo拿出不相同的api数据  商户对账
    public function on_alike_gogo_busin($business_id){
//  	return Db::query("select `order`,`pay`,`time` from  b  where EXISTS (select a.order,a.pay,a.time from a where a.order=b.order) and  NOT EXISTS (select a.order,a.pay,a.time from a where a.pay = b.pay and a.time=b.time) limit 1,10");
        return Db::query("select b.upOrderId,a.pay_account,a.ordersn,a.pay_time from ims_foll_order as a left join ims_parking_order as b on a.ordersn = b.ordersn where EXISTS (select c.flow_number,c.pay_money,c.op_order_id from ims_enterprise as c where b.upOrderId=c.flow_number) and NOT EXISTS (select c.flow_number,c.pay_money,c.op_order_id from ims_enterprise as c where a.pay_account = c.pay_money) and a.business_id = $business_id order by a.pay_time asc");

    }
}
?>