<?php

namespace app\common\validate;

class SmsCheckValidate extends BaseValidate
{
    protected $rule = [
        'mobile_phone'      => 'require|isMobile',          // 手机号码
        'sms_tid'           => 'require|isPositiveInteger',
        'sms_type'          => 'require|in:1,2,3,4',        // 验证类型：1、登录验证，2、注册验证，3、修改手机号码，4、重置密码
        'sms_captcha'       => 'require|length:6',          // 验证码
    ];
}