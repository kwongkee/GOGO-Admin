<?PHP

/**
 * 短信发送类
 */
class sms {

    private $api_url = 'http://sms.1xinxi.cn/asmx/smsservice.aspx?';
    private $sign = '安徽信达会';
    private $username = '13856958326'; //必填参数。用户账号
    private $password = '6E77D4D9096F79757FA691F32E0F'; //必填参数。（web平台：基本资料中的接口密码）
    private $verify = ''; //短信验证码

    /**
     * 发送验证码
     * @param type $mobile
     */

    public function send_code($mobile) {
        $this->verify = $this->get_rand_code();
        $content = '短信验证码为：' . $this->verify . '，请勿将验证码提供给他人。';
        return $this->send($mobile, $content);
    }

    public function set_sign($sign) {
        $this->sign = $sign;
    }

    /**
     * 获取短信验证码
     * @return type
     */
    public function get_send_code() {
        return $this->verify;
    }

    /**
     * 发送短信
     * @param type $mobile
     * @param type $content
     * @param type $stime
     * @param type $extno
     */
    public function send($mobile, $content, $stime = '', $extno = '') {
        $argv = array(
            'name' => $this->username,
            'pwd' => $this->password,
            'content' => $content, //必填参数。发送内容（1-500 个汉字）UTF-8编码
            'mobile' => $mobile, //必填参数。手机号码。多个以英文逗号隔开
            'stime' => $stime, //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
            'sign' => $this->sign, //必填参数。用户签名。
            'type' => 'pt', //必填参数。固定值 pt
            'extno' => $extno    //可选参数，扩展码，用户定义扩展码，只能为数字
        );
        $params = $this->param2url($argv);
        $url = "http://sms.1xinxi.cn/asmx/smsservice.aspx?" . $params; //提交的url地址
        $ret = substr(file_get_contents($url), 0, 1);  //获取信息发送后的状态
        if ($ret == '0') {
            return true;
        }
        return false;
    }

    public function param2url($argv) {
        $flag = 0;
        $params = '';
        foreach ($argv as $key => $value) {
            if ($flag != 0) {
                $params .= "&";
                $flag = 1;
            }
            $params.= $key . "=";
            $params.= urlencode($value); // urlencode($value); 
            $flag = 1;
        }
        return $params;
    }

    /**
     * 获取随机验证码
     * @return type
     */
    public function get_rand_code() {
        return rand(123456, 999999); //获取随机验证码		
    }

}

//$sms = new sms();
//$ret = $sms->send_code(13083056971);
?>