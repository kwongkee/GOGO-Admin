<?php

namespace app\common\validate;

use app\common\model\comm\Platform;
use app\lib\exception\param_exception\ParameterException;
use think\Request;
use think\Validate;

class BaseValidate extends Validate
{
    /**
     * 检查参数是否符合验证规则
     * @throws
     */
    public function goCheck()
    {
        // 必须设置content-type:application/json
        $params = Request::instance()->param();
        $params['token'] = Request::instance()->header('token');

        if (!$this->check($params))
        {
            // $this->error有一个问题，并不是一定返回数组，需要判断
            $errorMsg = is_array($this->error) ? implode(';', $this->error) : $this->error;

            $exception = new ParameterException(['msg' => $errorMsg]);
            throw $exception;
        }
        return true;
    }

    public function getParameters()
    {
        $params = Request::instance()->param();
        $result = array();

        foreach ($this->rule as $key=>$value)
        {
            if (array_key_exists($key, $params))
            {
                $result[$key] = $params[$key];
            }
        }
        return $result;
    }

    /**
     * 默认
     */
     protected function isDefault($value, $rule='', $data=[], $field='')
     {

           return true;

     }

    /**
     * 判断是否为正整数
     */
    protected function isPositiveInteger($value, $rule='', $data=[], $field='')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0)
        {
            return true;
        }
        return $field . ' must be positive integer';
    }
    /**
     * 判断是否为空
     */
    protected function isNotEmpty($value, $rule='', $data=[], $field='')
    {
        if (!empty($value))
        {
            return true;
        }
        return $field . ' not allowed to be empty';
    }
    /**
     * 判断是否为手机号码
     */
    protected function isMobile($value, $rule='', $data=[], $field='')
    {
        if (empty($rule))
        {
            $rule = '^1(3|4|5|6|7|8|9)[0-9]\d{8}$^';
        }
        $result = preg_match($rule, $value);
        if ($result)
        {
            return true;
        }
        return $field . ' is not a phone number';
    }
    /**
     * 判断是否为数组（可空）
     */
    protected function isArray($value, $rule='', $data=[], $field='')
    {
        if (is_array($value))
        {
            return true;
        }
        return $field . ' is not a array';
    }
    /**
     * 判断是否为平台类型
     */
    protected function isPlatformType($value, $rule='', $data=[], $field='')
    {
        if (is_numeric($value) && is_int($value + 0))
        {
            $value = intval($value);
            if ($value == Platform::PLATFORM_TYPE_WEB_MANAGE ||
                $value == Platform::PLATFORM_TYPE_WEB_SHOP ||
                $value == Platform::PLATFORM_TYPE_WAP_SHOP ||
                $value == Platform::PLATFORM_TYPE_XCX_SHOP)
            {
                return true;
            }
        }
        return $field . ' 不是平台类型数据';
    }
}
