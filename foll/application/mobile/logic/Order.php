<?php

namespace app\mobile\logic;

use think\Model;

class Order extends Model
{
    protected $table = 'ims_parking_order';
    public function OrderList()
    {
        return $list = Order::all(function ($query){
            $query->where("pay_status",0)->limit(20);
        });
    }

}