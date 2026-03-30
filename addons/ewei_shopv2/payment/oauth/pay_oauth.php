<?php
//	header('Content-Type:text/html;charset=utf-8');
error_reporting(0);
define('IN_MOBILE', true);
require dirname(__FILE__) . '/../../../../framework/bootstrap.inc.php';
require IA_ROOT . '/addons/ewei_shopv2/defines.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/functions.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/plugin_model.php';
require IA_ROOT . '/addons/ewei_shopv2/core/inc/com_model.php';
global $_W;
global $_GPC;

/**$_GPC
    [payMoney] => 331
    [type] => tgwechat
    [http] => ok
    [oauth] => yes
 * 开发步骤：
 * 1.判断用户表中否存在GOGO公众的openid，
 * 1.1：有直接拿表中的GOGO公众号openid；
 * 1.2：没有，跳转授权链接
 * 1.2.1：获取到用户openid，存入ewei_shop_member newOpenid字段中
 * 1.2.2：跳转至tgpay.tgpay中执行跳转；请求支付URL；
 */
 
if(!empty($_GPC['oauth'])&& $_GPC['oauth'] == 'yes' ) {
	
		$payMoney = $_GPC['payMoney']?$_GPC['payMoney']:0.00;//交易金额
		$title = $_GPC['title']?$_GPC['title']:'福满城桂城店消费券';
		
		$post_data = array(
     		"title" => $title,
    		"uniacid" => "14",
    		'fee' => $payMoney,
    		"type" => $_GPC['type'],
    		"http" => "ok",
     	);
     	
     	$str = serialize($post_data);
		//数据写进缓存
		cache_write('params', $str);
     	
		$app_id = 'wx76d541cc3e471aeb';
		
		switch($_GPC['type']) {
			
			case 'wechat'://微信支付；
				$notifyUrl = "http://shop.gogo198.cn/addons/ewei_shopv2/payment/oauth/pay_notify.php";
				
				$redirect_uri = urlencode($notifyUrl);
				
				$getOpenidurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state={$payMoney}#wechat_redirect";
				
				header("Location:".$getOpenidurl);
				
			break;
			
			case 'alipay'://支付支付
			
				$package = [
					'fee'=>$payMoney,//订单金额
					'tid'=>'RN'.date('Y-m-dHis',time()).randS(),//订单编号
					'title'=>'福满城桂城店消费券',//订单描述
					'createtime'=>time(),//创建时间
					'pay_type'=>'alipay',
				];
				
				$config = [
					'mchid'=>'101540254006',//商户号
					'key'=>'f8ee27742a68418da52de4fca59b999e',//秘钥
				];
				//插入数据到表中；
				$res = pdo_insert('pay_money',$package);		
				if(!empty($res)) {
					
					$pay_alipay = m('common')->pay_alipay($package, $config);			
					if($pay_alipay['status'] == '100' && $pay_alipay['message'] == '获取二维码成功') {
										
						if(isset($pay_alipay['codeUrl'])) {
							$codeurl = $pay_alipay['codeUrl'];
						}
	
					}
					header("Location:".$codeurl);
				}
			break;
		}

}else{
	echo 'No Datas';
}


function randS()
{
	$ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
	$list=explode(",",$ychar);
	
	for($i=0;$i<6;$i++){
		
		$randnum=rand(0,35); // 10+26;
		
		$authnum.=$list[$randnum];
	}
	
	return $authnum;
}



 /* 微信授权相关接口
 */
class Wchat
{
    private $app_id = 'wx76d541cc3e471aeb';
    private $app_secret = '3e3d16ccb63672a059d387e43ec67c95';
    private $state='yes';
   /**
     * 获取微信授权链接
     * 
     * @param string $redirect_uri 跳转地址
     * @param mixed $state 参数
     */
    public function get_authorize_url($redirect_uri = '', $state = '')
    {
        $redirect_uri = urlencode($redirect_uri);
//		snsapi_base   snsapi_userinfo
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state={$state}#wechat_redirect";
    }
     /**
     * 获取微信openid
     */
    public function getOpenid($turl)
    {
        if (!isset($_GET['code'])){
            //触发微信返回code码
            
            $url=$this->get_authorize_url($turl, $this->state);
            
//          Header("Location: $url");
//			header("Access-Control-Allow-Origin:$url");
			header("Location:$url");
//			return $turl;
            exit();
        } else {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $access_info = $this->get_access_token($code);
            return $access_info;
        }
        
    }
    /**
     * 获取授权token网页授权
     * 
     * @param string $code 通过get_authorize_url获取到的code
	 * 
     */
    public function get_access_token($code = '')
    {
      $appid=$this->app_id;
      $appsecret=$this->app_secret;
      
        $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$appsecret."&code=".$code."&grant_type=authorization_code";
        //echo $token_url;
        $token_data = $this->http($token_url);
       // var_dump( $token_data);
        if($token_data[0] == 200)
        {
            $ar=json_decode($token_data[1], TRUE);
            return $ar;
        }
        
        return $token_data[1];
    }
    
    
    public function http($url, $method='', $postfields = null, $headers = array(), $debug = false)
    {
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
 
        switch ($method) {
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                    $this->postdata = $postfields;
                }
                break;
        }
        curl_setopt($ci, CURLOPT_URL, $url);
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
 
        $response = curl_exec($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
 
        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
 
            echo '=====info=====' . "\r\n";
            print_r(curl_getinfo($ci));
 
            echo '=====$response=====' . "\r\n";
            print_r($response);
        }
        curl_close($ci);
        return array($http_code, $response);
    }
	
	public function http_post($url,$post_data) {
	    $curl = curl_init();
		//设置抓取的url
		curl_setopt($curl, CURLOPT_URL, $url);
		 //设置头文件的信息作为数据流输出
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent'=>'Mozilla/5.0 (Linux; U; Android 4.0.2; en-us; Galaxy Nexus Build/ICL53F) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30'));
 		//设置获取的信息以文件流的形式返回，而不是直接输出。
 		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 		//设置post方式提交
 		curl_setopt($curl, CURLOPT_POST, 1);
 		//设置post数据
 		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
 		//执行命令
		$data = curl_exec($curl);
 		//关闭URL请求
 		curl_close($curl);
 		//显示获得的数据
 		print_r($data);
	}
 
}

/**
 * 
 * {{first.DATA}}
	停车时长：{{keyword1.DATA}}
	月卡时长：{{keyword2.DATA}}
	实计时长：{{keyword3.DATA}}
	 应付金额：{{keyword4.DATA}}
	 优惠折扣：{{keyword5.DATA}}
	 余额支付：{{keyword6.DATA}}
	 实付金额：{{keyword7.DATA}}
	{{remark.DATA}}欢迎您再次使用智能无感路内停车服务！
 * n7aQkN93Y-CeUBM491OMfzabqvLAWYmhG7awrIyyVNY

 */
?>