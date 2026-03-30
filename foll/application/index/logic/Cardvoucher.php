<?php

namespace app\index\logic;
use think\Model;
use think\Loader;
class Cardvoucher extends Model
{
    public function checkField($data)
    {
        $validate = Loader::validate('Coupon');
        if(!$validate->check($data)){
            throw new \Exception($validate->getError());
        }
        return $this;
    }

    public function inserCouponData($data)
    {
        try{
            $data['uniacid'] = Session('UserResutlt')['uniacid'];
            $data['c_code']  = $data['c_type'].mt_rand(11111,99999).mt_rand(11111,99999);
            $data['create_time'] = time();
            $data['s_time'] = strtotime($data['s_time']);
            $data['e_time'] = strtotime($data['e_time']);
            $coupon = Loader::model('Cardvoucher','model');
            $coupon->CardvouhcerAdd($data);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}