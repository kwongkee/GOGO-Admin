<?PHP

//$sms = new sms_t();
//$sms_res = $sms->send_code('13083056971');//发送短信验证码

/**
 * 短信发送类
 */
class sms_t {

    private $appKey = '23563183'; //必填参数。APP证书
    private $appSecret = '37d120df9994a14d61e398ef383e4e63';
    private $sms_tpl = 'SMS_33485827';
    private $sms_sign = '签名测试';
    private $verify = ''; //短信验证码

    public function __construct($setting) {
        global $_W, $_GPC;
        $this->appKey = $setting['sms_appkey'];
        $this->appSecret = $setting['sms_appSecret'];
        $this->sms_tpl = $setting['sms_tpl'];
        $this->sms_sign = $setting['sms_sign'];
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
     * 发送验证码
     * @param type $mobile
     * @param type $content
     * @param type $stime
     * @param type $extno
     */
    public function send_code($mobile) {
        include WXZ_SHOPPINGMALL . "/func/taobaosms/TopSdk.php";
        $c = new TopClient();
        $this->verify = $this->get_rand_code();
        $c->appkey = $this->appKey;
        $c->secretKey = $this->appSecret;
        $req = new AlibabaAliqinFcSmsNumSendRequest();
        $req->setExtend("123456");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($this->sms_sign);
        $req->setSmsParam("{\"verify_code\":\"{$this->verify}\"}");
        $req->setRecNum($mobile);
        $req->setSmsTemplateCode($this->sms_tpl);
        $resp = $c->execute($req);
        $retArr = $this->xmlToArr($resp);
        return true;
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

    /**
     * Convert a SimpleXML object into an array (last resort).
     * @param object $xml
     * @param bool   $root    Should we append the root node into the array
     * @return array|string
     */
    public function xmlToArr($xml, $root = true) {

        if (!$xml->children()) {
            return (string) $xml;
        }
        $array = array();
        foreach ($xml->children() as $element => $node) {
            $totalElement = count($xml->{$element});
            if (!isset($array[$element])) {
                $array[$element] = "";
            }
            // Has attributes
            if ($attributes = $node->attributes()) {
                $data = array('attributes' => array(), 'value' => (count($node) > 0) ? $this->xmlToArr($node, false) : (string) $node);
                foreach ($attributes as $attr => $value) {
                    $data['attributes'][$attr] = (string) $value;
                }
                if ($totalElement > 1) {
                    $array[$element][] = $data;
                } else {
                    $array[$element] = $data;
                }
                // Just a value
            } else {
                if ($totalElement > 1) {
                    $array[$element][] = $this->xmlToArr($node, false);
                } else {
                    $array[$element] = $this->xmlToArr($node, false);
                }
            }
        }
        if ($root) {
            return array($xml->getName() => $array);
        } else {
            return $array;
        }
    }

}

//$sms = new sms();
//$ret = $sms->send_code(13083056971);
?>