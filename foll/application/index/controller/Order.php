<?php

namespace app\index\controller;

use app\index\controller;
use think\Request;
use think\Loader;
use think\Db;

class Order extends CommonController
{
    
    public $orderModel = null;
    
    public function __construct ()
    {
        $this->orderModel = model('Order', 'logic');
    }
    
    /**
     * 订单列表
     */
    public function index ()
    {
        return view('order/index');
    }
    
    
    /**
     * 订单查找
     * @param Request $request
     * @return mixed
     */
    public function SearchOrder ( Request $request )
    {
        return Loader::model('Order', 'logic')->fetchOrder($request->get());
    }
    
    
    /**
     * 发送查看代码
     */
    public function SendAuthCode ()
    {
        return Loader::model('Order', 'logic')->SendAuthCode();
    }
    
    /**
     * 订单用户信息
     */
    public function OrderUserInfo ( Request $request )
    {
        $code = Session('orderAuthCode');
        
        if ( $code != trim($request->get('code')) ) {
            return json(['code' => -1, 'msg' => '授权码错误!']);
        }
        
        if ( empty($request->get('uid')) ) {
            return json(['code' => -1, 'msg' => '用户id不能空!']);
        }
        
        return Loader::model('Order', 'logic')->FetchOrderUserInfo($request->get('uid'));
    }
    
    
    /**
     * 导出订单信息
     * @param Request $request
     */
    public function exproOrder ( Request $request )
    {
        try {
            $this->orderModel->exproOrder($request->get());
        } catch (\Exception $exception) {
            $this->success($exception->getMessage(), Url('index/order_index'));
        }
    }
    
    /**
     * 查看用户授权信息
     */
    //    public function OrderUserAuthPay(Request $request)
    //    {
    //        if (Session('orderAuthCode')!=trim($request->get('code'))){
    //            return json(['code'=>-1,'msg'=>'授权码错误!']);
    //        }
    //        if (empty($request->get('uid'))){
    //            return json(['code'=>-1,'msg'=>'用户id不能空!']);
    //        }
    //    }
    
    /**试运营订单处理
     * @param Request $request
     * @return mixed
     */
    public function trailOrderHandle ( Request $request )
    {
        return view('order/trail_order');
    }
    
    /**
     * 删除试运营订单
     * @param Request $request
     */
    /*
    public function delTrailOrder(Request $request){
   
       $data = json_decode($request->post('data'),true);
       if (empty($data)){
           return json(['code'=>-1,'msg'=>'数据为空！']);
       }
       if (empty($request->post('code'))){
           return json(['code'=>-1,'msg'=>'验证码错误！']);
       }
       if ($request->post('code')!=Session("orderCode")){
           return json(['code'=>-1,'msg'=>'验证码错误！']);
       }
       
       try{
           $this->orderModel->delTrailOrder($data);
       }catch (\Exception $e){
           return json(['code'=>-1,'msg'=>$e->getMessage()]);
       }
    
        return json(['code'=>0,'msg'=>'处理完成']);
    }
    */
    
    /**
     * 更改入场时间
     * @param Request $request
     */
    public function changeParkingStartTime ( Request $request )
    {
        
        $data = json_decode($request->post('data'), true);
        if ( empty($data) ) {
            return json(['code' => -1, 'msg' => '数据为空！']);
        }
        
        if ( empty($request->post('code')) ) {
            return json(['code' => -1, 'msg' => '验证码错误！']);
        }
        if ( $request->post('code') != Session("orderCode") ) {
            return json(['code' => -1, 'msg' => '验证码错误！']);
        }
        
        try {
            $this->orderModel->changeParkingStartTime($data);
        } catch (\Exception $exception) {
            return json(['code' => -1, 'msg' => $exception->getMessage()]);
        }
        return json(['code' => 0, 'msg' => '处理完成']);
    }

    /**
     * 处理订单时验证码
     * @param Request $request
     * @return mixed
     */
    public function sendCode ( Request $request )
    {
//        $adminUserTel = Db::name('foll_business_admin')->where(['uniacid' => Session('UserResutlt.uniacid'), 'role' => 2])->field('user_mobile')->find();
//        $adminUserTel = Session('UserResutlt.user_mobile');
//        $code   = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
        $code = '123456';
        $code_t = '800800';
//        $config = ['SingnName' => '变更验证', 'code' => $code, 'product' => 'Gogo运营后台管理', 'tel' => $adminUserTel, //            'tel'       =>'13229784981',
//            'TemplateCode' => 'SMS_35030086'];
//        sendSms($config);
        Session("orderCode", null);
        Session("orderCode", $code);

        Session("orderCode_t", null);
        Session("orderCode_t", $code_t);
        return json(['status' => true, 'code' => 10000, 'msg' => '发送成功']);
    }
    
    /**
     * 删除订单
     * @param Request $request
     * @return mixed
     */
    public function delOrder ( Request $request )
    {
        
        
        $data = json_decode($request->post('data'), true);
        if ( empty($data) ) {
            return json(['code' => -1, 'msg' => '请选择数据']);
        }
        
        if ( empty($request->post('code')) ) {
            return json(['code' => -1, 'msg' => '验证码错误']);
        }
        
        if ( $request->post('code') != Session('orderCode') ) {
            return json(['code' => -1, 'msg' => '验证码错误']);
        }
        
        try {
            $this->orderModel->delOrder($data);
        } catch (\Exception $exception) {
            return json(['code' => -1, 'msg' => $exception->getMessage()]);
        }
        return json(['code' => 0, 'msg' => '完成']);
    }


    /**
     * 修改订单金额以及车牌号
     * @param Request $request
     * @return mixed
     */
    public function modifyOrder(Request $request){
        if ($request->isGet()){
            return view('order/order_modify',['orderId'=>$request->get('order_id')]);
        }
        if ($request->isPost()){
            try{
                $this->orderModel->modifyOrder($request->post());
            }catch (\Exception $exception){
                return json(['code'=>-1,'msg'=>$exception->getMessage()]);
            }
            return json(['code'=>0,'msg'=>'更新成功']);
        }
    }

}