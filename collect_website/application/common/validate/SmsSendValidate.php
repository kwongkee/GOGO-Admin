<?php

namespace app\common\validate;

class SmsSendValidate extends BaseValidate
{
    protected $rule = [
        'mobile_phone'      => 'require|isMobile',      // 手机号码
        'type'              => 'require|in:1,2,3,4',    // 验证类型：1、登录验证，2、注册验证，3、修改手机号码，4、重置密码
    ];
}