<?php


if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

class Auth_EweiShopV2Page extends MobilePage
{
    public function __construct ()
    {
        parent::__construct();
        load()->func("common");
        isUserReg();
    }

    function main()
    {
        global $_W;
        global $_GPC;
        load()->classs("head");
        $title = '扣费授权';
        $show=null;
        $tel = pdo_get("parking_authorize",array("openid"=>$_W['openid']));
        $announcement = Head::announcement($_W['uniacid']);//公告
        $carousel = Head::carousel($_W['uniacid'],1);//广告
        if(!empty($tel)) {
            $tel['auth_type']=unserialize($tel['auth_type']);
        }
        if($_W['fans']['follow']!=1) {
            $show="showPopup";
        }
        
	    include $this->template('parking/auth');
    }
    
    
	/**
	 * http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth
	 * http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth.Surrender
	 * 解约操作；信用卡无感支付；
	 */
	function SignSurrender()
	{
		load()->func('diysend');
		$curlpost = new Curl;//实例化
		global $_W;
		global $_GPC;

		$custId = pdo_get("parking_authorize",array("openid"=>$_W['openid'],'auth_status'=>1),array("id","CustId"));
		if(!empty($custId)) {
			$data = [
				'token' =>'Surrender',
				'CustId' =>$custId['CustId'],//用户签约银行卡唯一标识。
			];
			
			//解约请求地址
			$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
			$result = $curlpost->post($url,$data);
			$json = json_decode($result->response,TRUE);
			//解绑成功！
			if($json['Message']['Plain']['Result']['ResultCode'] == '00')
			{
				//解约成功  更新签约状态； 2018-04-11
				//$up = pdo_update('parking_authorize',array('auth_status'=>0),array('openid'=>$_W['openid']));
				$updata = [ // 2018-04-11
					'auth_status'	=>0,
					//'auth_type'=>serialize(['wg'=>' ']),
					'auth_type'		=>'',
					'credit_accout'	=>'',
					'CarNo'		  	=> '',
				];
				//更新表数据
				$up = pdo_update('parking_authorize',$updata,array('id'=>$custId['id']));
				
				$Surr = 'success';
				$msg = $json['Message']['Plain']['Result']['ResultMsg'];
				
			}else {//解绑失败；
				$Surr = 'error';
				$msg = $json['Message']['Plain']['Result']['ResultMsg'];
			}
		} else { //不符合条件，没有授权免密
			$Surr = 'error';
		}

		include $this->template('parking/Surrender');
	}
	
	/**
	 * 查询无感信用卡优惠券;
	 * Coupon
	 */
	public function Coupon()
	{
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$userInfo = pdo_get("parking_authorize",array("openid"=>$_W['openid'],'auth_status'=>1),array("mobile","auth_type"));
		$auth['auth'] = unserialize($userInfo['auth_type']);//解压序列化；
		if(!empty($userInfo) && in_array('FCreditCard',$auth['auth'])) {
			$data = [
				'token' =>'Coupon',
				'mobile' =>$userInfo['mobile'],//用户签约使用的手机号码；
			];
			$curlpost = new Curl;//实例化
			$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';//优惠券请求地址
			$result = $curlpost->post($url,$data);
			$json = json_decode($result->response,TRUE);
			if($json['Message']['Plain']['Result']['ResultCode'] == '00') {
//				$up = pdo_update('parking_authorize',$updata,array('CustId'=>$custId['CustId']));
				$Surr = 'success';
				$msg = $json['Message']['Plain']['Result']['ResultMsg'];
			}else {//解绑失败；
				$Surr = 'error';
				$msg = $json['Message']['Plain']['Result']['ResultMsg'];
			}
		} else {
			show_json(0,'您没有签约信用卡免密扣费');
		}
	}
	
	/**
	 * 2018-04-18
	 * 顺德农商解约
	 */
	public function FagroSurrender()
	{
		load()->func('diysend');
		global $_W;
		global $_GPC;

		$custId = pdo_get("parking_authorize",array("openid"=>$_W['openid'],'auth_status'=>1),array("id","CustId","credit_accout"));
		if(!empty($custId)) {
			
			$data = [
				'Token' =>'Surrender',
				'Phone' =>$custId['CustId'],//用户签约银行卡唯一标识。
				'CardNo'=>$custId['credit_accout'],//注册签约会员银行卡号
			];
			$curlpost = new Curl;//实例化
			$url = 'http://shop.gogo198.cn/payment/agro/Fagro.php';//解约请求地址
			$result = $curlpost->post($url,$data);
			$json = json_decode($result->response,TRUE);
			//解绑成功！
			if($json['Result'] == '00000') {
				
				//解约成功  更新签约状态； 2018-04-18
				$updata = [ // 2018-04-18
					'auth_status'	=> 0,
					'auth_type'		=> '',
					//'credit_accout'	=>'',
					'CarNo'		   	=> '',
				];
				//解约
				
				//更新数据表；
				$up = pdo_update('parking_authorize',$updata,array('id'=>$custId['id']));
                $this->postCredit($custId['credit_accout']);
                
				$Surr = 'success';
				$msg = $json['Message'];
				
			}else {//解绑失败；
				
				$Surr = 'error';
				$msg = $json['Message'];
			}
		} else { //不符合条件，没有授权免密
			
			$Surr = 'error';
		}

		include $this->template('parking/Surrender');
	}
	
	
	public function postCredit($credit) {
		$postData = [
			'credit_accout'=>$credit
		];
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/pullParkingCardBindStatusApi",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => json_encode($postData),
		  CURLOPT_HTTPHEADER => array(
		    "Cache-Control: no-cache",
		    "Content-Type: application/json",
		  ),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
	}
	
	/**
	 * 2018-06-13
	 * 点击去解约现在用户解约信息
	 */
	public function SurrenList()
	{
		global $_W;
		global $_GPC;
		/**
		 * 接收上传参数或url数据;
		 * 通过用户openid 查询用户授权信息
		
		*/
		$flag = true;
		$title = '我的授权信息';
		
		$userinfo = pdo_get("parking_authorize",array("openid"=>$_W['openid'],'auth_status'=>1),['mobile','name','credit_accout','auth_status','auth_type','CarNo']);
		if($userinfo) {
			
			
			$authType = unserialize($userinfo['auth_type']);
			$t = '';
			foreach($authType as $k=>$v) {
				if($v === 'FCreditCard') {
					$t = '无感免密支付';
				} else if($v === 'Fwechat') {
					$t = '微信免密支付';
				} else if($v === 'FAgro'){
					$t = '农商免密支付';
				}
				$surrent = $v;
			}
			
			$userinfo['auth_type'] = $t;
			$userinfo['credit_accout'] = $userinfo['credit_accout']?$userinfo['credit_accout']:'无';
			$userinfo['auth_status']	= $userinfo['auth_status']?'已授权':'未授权';
			$userinfo['CarNo'] = $userinfo['CarNo']?$userinfo['CarNo']:'无';
		} else {
			
			$flag = false;
		}
		
		include $this->template('parking/SurrenList');
	}
	
	/**
	 * 微信免密解约
	 */
	public function wechat()
	{
		global $_W;
		global $_GPC;
		
		$title = '微信免密解约流程';
		include $this->template('parking/wechat');
	}
	
	/**
	 * 微信免密解约操作
	 */
	public function wechatSurr()
	{
		global $_W;
		global $_GPC;
		
		$custId = pdo_get("parking_authorize",array("openid"=>$_W['openid'],'auth_status'=>1));
		//查询签约类型
		$type = unserialize($custId['auth_type']);
		
		if(!empty($custId) && $type['wx'] == 'Fwechat') {
			
			$updata = [ // 2018-06-14
				'auth_status'	=> 0,
				'auth_type'		=> '',
				'credit_accout'	=> '',
				'CarNo'			=> '',
			];
			
			//更新表数据
			$up = pdo_update('parking_authorize',$updata,array('openid'=>$_W['openid']));
			if($up){
				$Surr = 'success';
				$msg  = '停车平台解约成功，请前往微信APP操作!';
			} else {
				$Surr = 'error';
				$msg  = '停车平台解约失败，您无需操作!';
			}
			
		} else {
			echo "<script>alert('您没有签约微信免密信息，请查询APP为准！')</script>";
			header('Location:'.mobileUrl('parking/auth'));
			exit();
		}
		
		include $this->template('parking/Surrender');
	}
	
	
/*授权扣费改版*/
	function testauthmain()
    {
        global $_W;
        global $_GPC;
        load()->classs("head");
        $title = '扣费授权';
        $show=null;
        $tel = pdo_get("parking_authorize",array("openid"=>$_W['openid']));
        $announcement = Head::announcement($_W['uniacid']);//公告
        $carousel = Head::carousel($_W['uniacid'],1);//广告
        if(!empty($tel)) {
            $tel['auth_type']=unserialize($tel['auth_type']);
        }
        if($_W['fans']['follow']!=1) {
            $show="showPopup";
        }
        
	    include $this->template('parking/auth_gai');
    }
    
    /**
	 * 微信免密解约-改版
	 */
	public function testwechat()
	{
		global $_W;
		global $_GPC;
		
		$title = '微信免密解约流程';
		include $this->template('parking/wechat_gai');
	}
	
}
