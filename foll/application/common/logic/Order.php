<?php

namespace app\index\logic;

use think\Model;

class Order extends Model
{
    protected $table = 'ims_parking_order';
    public function OrderList()
    {
        dump('2');
    }
}