<?php
namespace app\index\validate;
use think\Validate;

class Coupon extends Validate
{
    protected $rule = [
        'c_name'            =>  'require',
        'get_max'           =>  'require|number',
        's_time'            =>  'require',
        'e_time'            =>  'require',
        'c_type'            =>  'require|number',
        'enough'            =>  'require|number',
        'c_amount'          =>  'require|number',
        'c_total'           =>  'require|number',
        'use_type'          =>  'require|number'
    ];
}