<?php

namespace app\api\logic;

use think\Model;


class Agro extends Model
{
    public function getAgroUserOrder($data)
    {
        $cryData = $this->cryResData($data['data']);
        return json(['code'=>'1001','msg'=>'已存在订单']);
    }
    
    
    protected function cryResData($data)
    {
    
    }
}