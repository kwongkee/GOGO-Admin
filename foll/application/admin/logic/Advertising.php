<?php

namespace app\admin\logic;
use http\Env\Request;
use think\Model;
use think\Db;
class Advertising extends Model
{
    protected $table = "ims_foll_advertising";
    protected $config = [
        'query'=>['s'=>'admin/adver'],
        'var_page'=>'page'
    ];
    protected $offset = 8;




    public function allAder()
    {
        $total = Advertising::count('id');
        return Advertising::table("ims_foll_advertising adver,ims_foll_verified_business user")
            ->where("user.uid=adver.business_id")
            ->where("adver.status",10)
            ->field("user.company_name,adver.*")
            ->order("id desc")
            ->paginate($this->offset,$total,$this->config);
    }



    public function updateAndInserNewData($request)
    {
        $charge =[
            'money' =>  $request->post("charges"),
            'status'=>  20
        ];
        $Result = [
            'gid'      => $request->post("id"),
            'multiple' => $request->post("multiple"),
            'rate'     => $request->post("rate")
        ];
        if(empty($charge)&&!empty($multiple)&&empty($rate)){
           return false;
        }
        try{
            Advertising::where("id",$request->post("id"))->update($charge);
            Db::execute('insert into ims_foll_advertisingclick_data (gid, multiple,rate) values (:gid, :multiple,:rate)',$Result);
            unset($request);
            return true;
        }catch (Exception $e){
            return false;
        }
    }




    public function sumCps()
    {
//        $totalClicks = totalClicks();
//        $appSource = appSource();
//        $totalBusinessClicks = totalBusinessClicks();
        return;
    }
}