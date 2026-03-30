<?php
/**
 *	代收款工具
 */
 if (!(defined('IN_IA'))) {
 	exit('Access Denied');
 }
class Business_EweiShopV2Page extends mobilePage
{
	
	protected $redis;
    function main()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		include $this->template('money/busin/main');
	}
	
	/**
	 * 商户认证确认
	 */
	function UserCheck()
	{
		global $_W;
		global $_GPC;
		
		if($_W['isajax'])
		{
			$busin = pdo_get('foll_user',array('tel'=>$_GPC['phone'],'username'=>$_GPC['userName'],'role'=>3),array('id','openid'));
			if(!empty($busin['id'])){
				//如果用户openid为空 就更新该用户的openid
				if(empty($busin['openid'])) {
					pdo_update('foll_user',array('openid'=>$_W['openid']),array('id'=>$busin['id']));	
				}
				show_json(1,'商户认证成功');
			}else {
				show_json(0,'您输入的信息不是商户的，请重新输入');	
			}
		}
	}
	
	
	
	/**
	 * 用户登录
	 */
	function login()
	{
		global $_W;
		global $_GPC;
		
		include $this->template('money/busin/login');
	}
	
	
	//登录判断
	function loginOk()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if($_W['isajax'] && !empty($_GPC['yzm'])) {
			
			//判断验证码  如果验证码不相等
			if($_GPC['yzm'] == ''){
				show_json(0,'请输入验证码');
			}else if($_COOKIE['yzm'] != $_GPC['yzm']) {
				show_json(0,'验证码错误，请重新输入');
			}
			
			/**
			 * 步骤：
			 * 1、查询表中是否存在该用户 并且该用户已验证；foll_verfied_saleman  isCheck = 1
			 * 2、存在，把用户信息保存到cookie中，并保存7天有效期；
			 */
			//查询条件，拓展员电话，openid,角色：商户；

			$where = ['tel'=>$_GPC['phone'],'openid'=>$_W['openid'],'role'=>3];
			//查询用户表  是否存在该用户的信息
			$selData = pdo_get('foll_user',$where,array('id'));
			//如果存在该用户；
			if(!empty($selData['id'])) {
				//查询条件：用户id,等于商户id;验证状态：1 
				$where1 = ['uid'=>$selData['id'],'isCheck'=>1];
				//查询 foll_verified_salesman   拓展员
				$sale = pdo_get('foll_verified_business',$where1,array('id'));
				if(!empty($sale['id'])) {
					//设置cookie
					$expire = time() + 604800; // 设置7*24小时的有效期
//					$expire = time() + 60; // 设置60秒的有效期
					$sets = md5($selData['id'].'+'.$expire);
					//设置cookie
					$setVal = base64_encode($sets);//数据转换base64
					setcookie($_W['openid'],$setVal,$expire);
					//设置缓存；
					cache_write($_W['openid'],$sets);
					
					show_json(1,'登录成功');
				}else {
					show_json(0,'商户尚未验证');
				}
				
			}else {
				show_json(0,'商户不存在或未认证');
			}

		}else{
			show_json(0,'请填写验证码');
		}
	}
	
	/**
	 * 发送验证码
	 */
	function SendYzm()
	{
		global $_W;
		global $_GPC;
		
		/**
		 * 开发步骤：
		 * 1、判断是否ajax提交
		 * 2、表中是否存在该手机号码；
		 * 3、发送验证码；
		 */
		if($_W['isajax'] && !empty($_GPC['phone']) ) {
			
			//获取foll_user表中是否存在该手机号码;3 商户
			$phones = pdo_get('foll_user',array('tel'=>$_GPC['phone'],'role'=>3),array('username'));
			if(!empty($phones['username'])) {
				
				$sms_id = $this->GetSemid($_W['uniacid']);
				$key = $_GPC['phone'];
				
				//随机产生5位数验证码；
				$vcode = random(5,true);
				
				//发送验证码；
				$ret = com('sms')->send($_GPC['phone'],$sms_id['id'], array('名称' =>$_W['uniaccount']['name'],'验证码' => $vcode));
				if ($ret['status']) {
					//验证码存储在cookid中，并保存60秒
					setcookie('yzm',$vcode,time()+60);
					
					show_json(1,'验证获取成功');
					
				}else{
					show_json(0,'验证码获取失败');
				}
			}else {
				show_json(0,'该号码不是商家的手机号码');
			}
		}
		
	}
	// 获取短信发送配置
	function GetSemid($uid)
	{
		return pdo_get('ewei_shop_sms',array('uniacid'=>$uid),array('id'));
	}
	
	
	
	
	/**
	 * 合作管理
	 */
	function cooperation()
	{
		global $_W;
		global $_GPC;
		
//		$UserInfo = pdo_get('foll_user',array('openid'=>$_W['openid'],'role'=>3),array('username'));
		$UserInfo['username'] = 'Json';
		include $this->template('money/busin/cooperation');
	}
	
	/**
	 * 企业结算账号
	 */
	function JieSuan()
	{
		global $_W;
		global $_GPC;
		
		if($_W['isajax']) {
			
			$userInfo = pdo_get('foll_user',array('openid'=>$_W['openid'],'role'=>3),array('id'));
			if(!empty($userInfo['id'])){
				
				/**
				 * 1、查询商户表中是否已添加；
				 */
				
				$check = pdo_get('foll_verified_business',array('uid'=>$userInfo['id'],'isCheck'=>1),array('account'));
				if(!empty($check) && $check['account']!= '0'){//商户已添加  收款账号不能修改
					show_json(0,'您已添加银行账号，请勿重复添加');
				}else {//没有添加账号  可添加
					$upData = [
						'account_type'=> $_GPC['AccType'] == 'qiye'?1:2,//账号类型
						'account' =>$_GPC['CardNumber'],//银行账号
						'bank'=> $_GPC['brank'],//所属银行
					];
					//更新数据；
					pdo_update('foll_verified_business',$upData,array('uid'=>$userInfo['id']));
					show_json(1,'添加成功');
				}
				
			}else {
				show_json(0,'用户不存在或尚未认证!');
			}
		}
	}
	
	
		/**
	 * 企业结算账号
	 */
	function JieSuans()
	{
		global $_W;
		global $_GPC;
		
		if($_W['isajax']) {
			
			$userInfo = pdo_get('foll_user',array('openid'=>$_W['openid'],'role'=>3),array('id'));
			if(!empty($userInfo['id'])){
				
				/**
				 * 1、查询商户表中是否已添加；
				 */
				
				$check = pdo_get('foll_verified_business',array('uid'=>$userInfo['id'],'isCheck'=>1),array('account'));
				if(!empty($check) && $check['account']!= '0'){//商户已添加  收款账号不能修改
					show_json(0,'您已添加银行账号，请勿重复添加');
				}else {//没有添加账号  可添加
					$upData = [
						'account_type'=> $_GPC['AccTypes'] == 'qiye'?1:2,//账号类型
						'account' =>$_GPC['CardNumbers'],//银行账号
						'bank'=> $_GPC['branks'],//所属银行
					];
					//更新数据；
					pdo_update('foll_verified_business',$upData,array('uid'=>$userInfo['id']));
					show_json(1,'添加成功');
				}
				
			}else {
				show_json(0,'用户不存在或尚未认证!');
			}
		}
	}
	
	
	
	/**
	 * 业务管理
	 */
	function mana()
	{
		load()->classs('sessions');
		global $_W;
		global $_GPC;
		
		session_start();
		
//		sessions::set('test','我是测试数据',10);
		
		echo sessions::get('test');
		
		echo '<br>';
			
		echo sessions::get('test');
		
//		echo date('Y-m-d H:i:s',time());
//		echo $start = date('H:i',time());//预约时间
//		echo '<br>';
//		echo date('Y-m-d H:i:s','1521171946');//预约设置

//		echo $end = date('H:i','1521171946');//预约设置   1521166546
//		echo $start = date('H:i','1521166546');//预约设置
		
		//判断当前提交的预约时间 是否在设置的时间表中  例如：当前时间必须大于11：20  并且 小于16:00
//		if($start > '10:10' && $start < '15:00') {
//			echo '当前时间段可预约';
//		}else {
//			echo '当前不可预约';
//		}
		
//		cache_write('manas','数值参数',15);
		
//		file_put_contents('./log/wecaht.txt', print_r($_W,TRUE));
		
		include $this->template('money/busin/mana');
	}
	
	/**
	 * 预约计算
	 * 开发步骤：
	 * 1、GPC 获取用户提交的预约时间
	 * 2、判断 当前预约时间是否  大于开始时间并且小于结束时间；计算预约时间
	 * 3、计算当前是否为：工作日，节假日；调用API，计算当天是否为节假日：判断缓存中是否有该值
	 * 用当天的日期作为该值的键；用查出的数据作为该键的值；第二次查询可以调用缓存中的参数；
	 */
	
	/**
	 * 预约设置
	 */
	function YyueSet()
	{
		global $_W;
		global $_GPC;
		
		
		$timeArr = [
			'strtotime' =>strtotime($_GPC['Stime']),
			'info' =>$_GPC,
		];
		
		show_json(0,$timeArr);
	}
	
	
	/**
	 * 判断是否为工作日
	 * @param $TimeRub； 接收的日期  提交预约
	 * @return bool;
	 */
	function isWorkDay()
	{
		load()->classs('sessions');
		session_start();
		global $_W;
		global $_GPC;
		if(is_null($this->redis))$this->createRedisConnect();
		/**
		 * 开发步骤：
		 * 开发思路：把查询出来的数据保存到数据表中；
		 * 1、以当天的日期作为键值；
		 * 2、查询出当天的数据信息，通过ID加减  删除上一条信息
		 * 
		 */
		
//		if(isset($TimeRub)) {//时间搓
			
			$key = date('Ymd',$TimeRub);
//			if($redis->get("kkkk")){
//			$this->redis->set("kkkk","test");
//				
//			}

			$this->redis->pexpire("kkkk",3);
			echo $this->redis->get("kkkk");
			//存在session 中
//			if(isset(sessions::get($key))){
//				new Redis();
//			}else {//不存在；
//				
//				
//				$expire = 2359-date('Hi',$TimeRub);
//				$expire = $expire * 
//				sessions::set($key,'我是测试数据',$expire);
//			}
//			
			
			
			
			
			//1、查询数据库对应的键是否存在值
//			$dateVal = pdo_get('isworkday',array('checkDate'=>$date));
//			if(!empty($dateVal['id'])) {//存在该键值；
//				//ID；1、2、3、4、5  删除上一条数据
//				$num = ($dateVal['id']-1);//0,1,2,3,4,
//				if($num != 0) {//如果不等于0 代表数据大于等于参数
//					pdo_delete('isworkday',array('id'=>$num));
//				}
//				//取出json 数据；
//				$json = json_decode($dateVal['val']);
//				if($json->type == 1){//工作日
//					return '工作日';
//				}else if($json->type == 2){//周末
//					return '周末';
//				}else {//3  节假日
//					return '节假日';
//				}
//				
//			}else {//不存在该键值  查询数据
//				$res = $this->RequersWorkDay($date);//以时间为键； 返回json值
//				//把数据存入数据库中；
//				$addArr = [
//					'checkDate'=>$date,//键值
//					'val' => $res,//json 数据
//				];
//				$insert = pdo_insert('isworkday',$addArr);

//		}
	}
	
	protected function createRedisConnect()
	{
			$this->redis=new Redis();
			$this->redis->connect("127.0.0.1",6379);
	}
	/**
	 * 请求节假日
	 * @param $day 请求时间：例 20180319
	 */
	function RequersWorkDay($day = '') {
		
		header("Content-Type:text/html;charset=UTF-8");
		date_default_timezone_set("PRC");
		$showapi_appid = '52908';  //替换此值,在官网的"我的应用"中找到相关值
		$showapi_secret = 'f4dbb4a2d3a84cda8555c81237db42c9';  //替换此值,在官网的"我的应用"中找到相关值
		$paramArr = array(
		'showapi_appid'=> $showapi_appid,
//			'day'=> "20171001"
			'day'=> $day,
		//添加其他参数
		);
		
		$param = $this->createParam($paramArr,$showapi_secret);
		$url = 'http://route.showapi.com/894-2?'.$param;
//		echo "请求的url:".$url."\r\n";
		$result = file_get_contents($url);
//		echo "返回的json数据:\r\n";
//		print $result;
//		$result = json_decode($result);
//		echo "\r\n取出showapi_res_code的值:";
//		print_r($result->showapi_res_code);
//		echo "\r\n";
		return $result;//返回数据；
		
	}
	
	//创建参数(包括签名的处理)
	function createParam($paramArr,$showapi_secret) {
		$paraStr = "";
		$signStr = "";
		ksort($paramArr);
		foreach ($paramArr as $key => $val) {
			if ($key != '' && $val != '') {
				$signStr .= $key.$val;
				$paraStr .= $key.'='.urlencode($val).'&';
			}
		}
		$signStr .= $showapi_secret;//排好序的参数加上secret,进行md5
		$sign = strtolower(md5($signStr));
		$paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
//		echo "排好序的参数:".$signStr."\r\n";
		return $paraStr;
	}
	
	
	
	/**
	 * 结算管理
	 */
	function Settlement()
	{
		global $_W;
		global $_GPC;
		
		include $this->template('money/busin/Settlement');
	}
	
	
	/**
	 * 接收前端请求
	 * <script src="http://shop.gogo198.cn/addons/ewei_shopv2/static/msg/css/jquery.min.js"></script>
	 * 
	 * http://shop.gogo198.cn/addons/ewei_shopv2/static/msg/css/bill.css
	 * http://shop.gogo198.cn/addons/ewei_shopv2/static/msg/css/flickerplate.css
	 *  $.ajax({
            type:'POST',
            url :$('#Urls').val(),
            async : false,
            dataType:'json',
            data: $('#form1').serialize(),
            success : function(json) {
              console.dir(json);
            }
        });
	 * 
	 */
	
	/**
	 * 2018-03-20
	 * 页面端请求数据
	 * 显示商家信息；
	 * 
	 */
	function Requert() {
		global $_W;
		global $_GPC;
		/**
		 * 开发步骤：
		 * 1、获取当前公众号ID
		 * 2、根据公众号ID查询商家
		 * 3、把商家新显示到模板中；
		 */
		
		$info = [
//			'i'=>$_W['uniacid'],
//			'ops'=>$_W['openid'],
			'one'=>' ',
			'two'=>'伦教停车',
		];
		
		show_json(1,$info);
	}
	
}

?>