<?php

namespace app\common\validate;

class TIDMustBePositiveInt extends BaseValidate
{
    protected $rule = [
        'tid' => 'require|isPositiveInteger'
    ];
}