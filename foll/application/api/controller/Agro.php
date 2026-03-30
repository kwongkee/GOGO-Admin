<?php

/*
 * 农商卡二次接口
 */

namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Log;
use think\Response;
use think\Db;

class Agro extends Controller
{
    protected static $requestData;
    protected $data;
    public function __construct ( Request $request)
    {
        parent::__construct($request);
        self::$requestData['user']=$request->header('user');
        self::$requestData['pwd'] =$request->header('pwd');
        self::$requestData['data']=$request->post('data');
        @file_put_contents('../runtime/log/out/'.date('Ymd',time()).'.txt','停车卡拍卡：'.date('Y-m-d H:i:s',time()).'---'.json_encode($request->post())."\n",FILE_APPEND);
    
    }

    public function acceptAgroInfo(Request $request,Response $response)
    {
        $packResult = validationPacket(self::$requestData);
        if(!$packResult['error']){
            $response->header("Content-Type","application/json;charset=utf-8")
                ->header("Content-Length",strlen($packResult['errorMsg']))
                ->data($packResult['errorMsg'])->send();
            exit();
        }
        if(empty($packResult['errorMsg']['cardNo'])){
            return json(['statusCode'=>1003,'msg'=>'卡号为空','data'=>'']);
        }
        if(empty($packResult['errorMsg']['parkCode'])){
           return json(['statusCode'=>1003,'msg'=>'泊位编号不存在','data'=>'']);
        }
        return $this->inserNewOrder($packResult['errorMsg']);
        
      
    }

    protected function isOrder($openid){
        return  Db::name("foll_order")->alias("a1")
            ->join("parking_order a2","a2.ordersn=a1.ordersn")
            ->where("a1.user_id",$openid)
            ->where('a1.pay_status=0 or a1.pay_status=2')
            ->field(['a1.ordersn','a2.devs_ordersn'])
            ->order('a1.id','desc')
            ->find();
//        return Db::name('foll_order')->where('user_id',$openid)->where('pay_status=0 or pay_status=2')->field('id')->order('id','desc')->find()['id'];
    }
    
    
    protected function isPeriod($openid){
       return  Db::name("foll_order")->alias("a1")
            ->join("parking_order a2","a2.ordersn=a1.ordersn")
            ->where(["a1.user_id"=>$openid,"a2.charge_status"=>0])
            ->field(['a1.ordersn','a2.devs_ordersn'])
            ->find();
    }
    /*
     * 插入新订单
     */
    protected function inserNewOrder($parm){
        try{
            $millisecond = round(explode(" ", microtime())[0]*1000);
            $order_id = 'G99198'  . date('Ymd', time()) .str_pad($millisecond,3,'0',STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111,999);
            $addrRes = $this->getAddRes($parm['parkCode']);
            $userRes = $this->getUserRes($parm['cardNo']);
            
            if(empty($userRes)){
                return json(['statusCode'=>1004,'msg'=>'未注册','data'=>'']);
            }
            
            if ($userRes['auth_status']!=1){
                return json(['statusCode'=>1005,'msg'=>'未签约','data'=>'']);
            }
    

            $isOrder = $this->isOrder($userRes['openid']);
            if(!empty($isOrder)){
                sendWechatMsgTemplate($userRes,$addrRes,date('Y-m-d H:i:s',$parm['stime']),'您好,你已存在一笔未离场帐单,停车失败!');
                return json(['statusCode'=>1006,'msg'=>'已有未结订单','data'=>$isOrder['devs_ordersn']]);
            }
            
            $isOrder = $this->isPeriod($userRes['openid']);

            if (!empty($isOrder)){
                sendWechatMsgTemplate($userRes,$addrRes,date('Y-m-d H:i:s',$parm['stime']),'您好,你已存在一笔未离场帐单,停车失败!');
                return json(['statusCode'=>1006,'msg'=>'已有未结订单','data'=>$isOrder['devs_ordersn']]);
            }

            $isMonthCard = Db::name('parking_month_pay')->where(['user_id'=>$userRes['openid'],'pay_status'=>1,'status'=>'A'])->find();
            if(empty($isMonthCard)){
                $isMonthCard = Db::name('card_member')->where(['openid'=>$userRes['openid'],'status'=>'Y'])->find();
            }
            $devOrderId = date('YmdHi') . substr(implode(null, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
            $follOrder = [
                'ordersn'       =>$order_id, //订单编号
                'user_id'       => $userRes['openid'],//用户openid
                'business_id'   => $userRes['uniacid'],
                'uniacid'       => $userRes['uniacid'],//公众号id
                'application'   => "parking",
                'goods_name'    => "路内停车",
                'goods_price'   => 0.00,
                'body'          => '停车服务',//消费项目
                'create_time'   => time(),//订单创建时间
                'nickname'      =>$userRes['name'],
                'address'       =>$addrRes['Province'].$addrRes['City'].$addrRes['Area'].$addrRes['Town'].$addrRes['Committee'].$addrRes['Road'].$addrRes['Road_num'].'号'
            ];
            $parkOrder  = [
                'ordersn'    => $order_id,
                'CarNo'      =>$userRes['CarNo'],
                'number'     =>$addrRes['park_code'],
                'starttime'  =>$parm['stime'],
                'moncard'    =>empty($isMonthCard)?0:1,
                'status'     =>'已停车',
                'charge_type'=>1,
                'charge_status'=>1,
                'OthSeq'     =>date('YmdHis', time()) . rand(111111, 999999),
                'devs_ordersn'=>$devOrderId
            ];
            Db::name("foll_order")->insert($follOrder);
            Db::name("parking_order")->insert($parkOrder);
            sendWechatMsgTemplate($userRes,$addrRes,date('Y-m-d H:i:s',$parm['stime']));
            unset($userRes,$parm,$addrRes);
            return json(['statusCode'=>1001,'msg'=>'完成','data'=>$devOrderId]);
        }catch (Exception $exception){
            Log::write($exception->getCode().":".$exception->getMessage());
            return json(['statusCode'=>1003,'msg'=>'系统异常','data'=>'']);
        }
    }

  

    protected function getAddRes($code){
        $pid = Db::name("parking_space")->where("numbers",$code)->field(["pid","park_code"])->find();
        $dataResult = Db::name("parking_position")->where("id",$pid['pid'])->find();
        $dataResult['park_code']=$pid['park_code'];
        return $dataResult;
    }
    protected function getUserRes($card){
        return Db::name("parking_authorize")->where("credit_accout",$card)->find();
    }
}
