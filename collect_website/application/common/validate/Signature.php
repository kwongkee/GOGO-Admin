<?php

namespace app\common\validate;

class Signature extends BaseValidate
{
    protected $rule = [
        'member_tid'        => 'isPositiveInteger',     // 会员索引
        'sign_platform_type'=> 'require|in:1,2',        // 签名请求平台类型(（1-小程序商城，2-手机网页商城）
        'sign_equipment_sn' => 'require|length:1,50',   // 签名请求设备号
        'sign_request_time' => 'require|integer',       // 签名请求时间
        'sign_token'        => 'require|length:32'      // 签名TOKEN
    ];
}