<?php

namespace app\admin\controller;

use app\admin\controller\Auth;
use think\Request;
use think\Loader;

class Order extends Auth
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index(Request $request)
    {
        $order=Loader::model("Order","logic");
        $data=$order->totalOrderData($request);
        unset($order);
        $page=$data->render();
        return view("order/index",[
            'data'=>$data->toArray(),
                'page'=>$page]
        );
    }



    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        $orderInfo=Loader::model('order','logic');
        $data=$orderInfo->orderInfo($id);
        return view("order/order_info",['data'=>$data[0]->toArray()]);
    }


}
