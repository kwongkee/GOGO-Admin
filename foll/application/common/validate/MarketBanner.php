<?php

namespace app\common\validate;

class MarketBanner extends BaseValidate
{
    protected function checkType($value, $rule='', $data=[], $field='')
    {
        if ($value=='product' || $value=='web_url')
        {
            return true;
        }
        return $field . ' value is error. (product)';
    }

    protected function checkPosition($value, $rule='', $data=[], $field='')
    {
        if ($value=='main_banner')
        {
            return true;
        }
        return $field . ' value is error. (main_banner)';
    }
}