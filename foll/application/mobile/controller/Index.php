<?php
namespace app\mobile\controller;
use app\mobile\controller;
use think\Request;
use think\Db;
use think\Log;
class Index extends Auth
{
    public function index()
    {
        $sql="select tb1.*,tb2.number,tb2.starttime,tb2.endtime,tb2.charge_type,tb4.Area,tb4.Road,tb4.Road_num from `ims_foll_order` as tb1
                left join `ims_parking_order` as tb2 on tb2.ordersn=tb1.ordersn 
                left join `ims_parking_space` as tb3 on tb3.numbers=tb2.number
                left join `ims_parking_position` as tb4 on tb4.id=tb3.pid
                where tb1.application='parking' and (tb1.pay_status=0 or tb2.charge_status=0) order by id desc limit 0,10";
        $orderData=Db::query($sql);
        return view("index",['list'=>$orderData]);
    }

    public function send(Request $request)
    {
            if(!empty($request->param("num"))){
//                echo json(['msg'=>$request->param("num")]);die();
                $num=explode(",",trim($request->param('num'),','));
                if(is_array($num)){
                    foreach ($num as $key=>$value){
                        $stims= time();
                        $sing=md5("test".$value.$stims.date("Ymd",time()));
                        $str=["sigin"=>$sing,"parkCode"=>$value,"etime"=>$stims];
//                        $str='{"sigin":"'.$sing.'","parkCode":"'.$value.'","etime":'.$stims.'}';
                        Log::save($this->httpRequest($str));
                        sleep(1);
                    }
                    return json(['errorCode'=>200,'msg'=>'已发送']);
                }

            }
            return json(['errorCode'=>460,'msg'=>'请选择']);

    }

    public function parking_info(Request $request)
    {
        $nums=$request->param("pid");
        $sql="select tb1.starttime,tb1.endtime,tb1.status,tb1.charge_type,tb3.*,tb4.phone,tb4.user from ims_parking_order  tb1 
                left join ims_parking_space tb2 on tb1.number=tb2.numbers 
                left join ims_parking_position tb3 on tb2.pid=tb3.id 
                left join ims_parking_admin_user tb4 on tb2.admin_id=tb4.id  where tb1.ordersn='".$request->param("did")."'";
        $data=Db::query($sql);
        $data[0]['nums']=$nums;
        return view("index/parking_info",["data"=>$data]);
    }

    public function httpRequest($body='')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_URL, "http://shop.gogo198.cn/foll/public/?s=api/departure");
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-type'=>"application/json"]);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        $result=curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}