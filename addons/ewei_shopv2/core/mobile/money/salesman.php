<?php
/**
 *	拓展员管理页面；
 */
 if (!(defined('IN_IA'))) {
 	exit('Access Denied');
 }
 
class Salesman_EweiShopV2Page extends mobilePage
{
	/**
	 * 拓展员主页；
	 */
    function main()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$title = '用户认证';
		$title1 = '实名验证';
		/**
		 * 主页；赵金如
		 */
		
//		include $this->template('money/sales/main');
		include $this->template('money/sales/salesman');
	}
	
	
	/**
	 * 检测商户手机号码
	 */
	function CheckPhone()
	{
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['Uphone']) && $_W['isajax']){
			$phone = pdo_get('foll_user',array('tel'=>$_GPC['Uphone'],'role'=>3),array('id'));
			if(!empty($phone['id'])){
				show_json(1,'存在该商户');
			}else{
				show_json(0,'不存在该商户,请重新输入');
			}
		}
	}
	
	/**
	 * 拓展员 认证
	 */
	function authenti()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$title = '用户认证';
		$title1 = '实名验证';
		
		include $this->template('money/sales/salesman');
	}
	
	//点击下一步验证数据是否与数据库中的匹配
	function nexit()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		/*
		 *开发步骤：
		 * 1、判断是否ajax 提交
		 * 2、组装查询数组
		 * 3、查询表中是否存在该扩展员信息
		 * 4、查询结果：有返回success， 赵金如
		 */
		if($_W['isajax'] && !empty($_GPC['phone'])) {//是否有ajax提交；
			
			$selectArr = [
				'username'=> trim($_GPC['userName'],' '),
				'tel'=> trim($_GPC['phone'],' '),
				'role' => 2,//'0 超级管理,1 管理员,2 拓展员,3 商户',
			];
			//查询表中是否存在该用户拓展员；
			$res = pdo_get('foll_user',$selectArr,array('id','username'));
			if(!empty($res)) {//存在
				//更新表中openid字段；
				pdo_update('foll_user',array('openid'=>$_W['openid']),array('id'=>$res['id']));
				
				$reArr = 'success';
				show_json(1,$res);
				
			}else{//不存在
				
				$reArr = 'error';
				show_json(0,$reArr);
			}
			
		}else{
			
			$reArr = 'error';
			show_json(0,$reArr);
		}
		
	}
	
	//实名验证
	function nexitPay()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;	
		
		/*
		 * 开发步骤：点击前往支付验证费用！
		 * 1、获取该用户的用户名；接收用户填写的身份证号码，判断该用户是否存在；
		 * 2、把该用户的身份证号码更新到foll_user 表中；
		 * 3、更新成功返回；费用支付；
		 */
		
		if($_W['isajax'] && !empty($_GPC['CardId']) ) {//是否有ajax提交
			
			
			//查询后台用户表；条件openid,role = 2   id = 4
			$user = pdo_get('foll_user',array('openid'=>$_W['openid'],'role'=>2),array('id'));
			if(!empty($user)) {//如果用户存在
				
				//获取拓展员验证信息 状态;
				$salesman = pdo_get('foll_verified_salesman',array('uid'=>$user['id'],'isCheck'=>1),array('isCheck'));
				if(!empty($salesman) && $salesman['isCheck'] == 1) {//拓展员已验证，无需重复验证；
					show_json(0,'您已验证通过，请勿重复验证');
				}else { //否则没有数据，插入新的数据； 
					
					/**
					 * 身份验证步骤
					 * 1、查询表中是否存在身份证这个参数；
					 * 2、存在：通过，不存在更新：下一步；
					 */
					
					$uIds = pdo_get('foll_verified_salesman',array('uid'=>$user['id']),array('idcard'));
					if(!empty($uIds['idcard'])){//存在
						$reArr = '成功,请支付验证费用1111';//成功标志；
						show_json(1,$reArr);
					}else {//不存在
						
						$inserArr = [
							'idcard' => $_GPC['CardId'],//身份证号码	 赵金如
							'uName' =>$_GPC['uName'],
						];
						
						//更新拓展员的身份证
						$res = pdo_update('foll_verified_salesman',$inserArr,array('uid'=>$user['id']));
						if(!empty($res))//更新成功；
						{
							$reArr = '成功,请支付验证费用';//成功标志；
							show_json(1,$reArr);
							
						}else{
							
							$reArr = '身份证认证失败';//失败标志；
							show_json(0,$reArr);
							
						}
					}
				}
				
			}else{
				show_json(0,'您还不是业务员，请联系管理员；');
			}
		
		}else {//没有接收到表单数据；
					
			$reArr = '请填写完整数据';//失败标志；
			show_json(0,$reArr);
		}
		
	}
	
	//微信支付
	function wechat()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		/**
		 * 调用步骤;
		 * 1、判断是否AJAX提交
		 * 2、组装支付数据
		 * 3、支付配置
		 * 4、数据写入表内，
		 * 5、请求数据
		 * 6、返回数据返回前台唤起支付；
		 * 
		 * 提交拓展员验证：
		 * 1、查询该拓展员的身份证号，姓名、id;
		 * 2、把该信息写入拓展员验证交易流水表中；
		 */
		if( $_W['isajax'] && (!empty($_GPC['payMoney']))) {
			//查找foll_user表，拓展员id，拓展员姓名;
			$tzy = pdo_get('foll_user',array('openid'=>$_W['openid']),array('id','username','uniacid'));
			if(!empty($tzy)) {//如果有数据；存在该拓展员；
				//查找拓展员验证信息；foll_verified_salesman,条件：uid = 拓展员id，并且验证状态为2,查询出该拓展员的身份证号码
				$tzyInfo = pdo_get('foll_verified_salesman',array('uid'=>$tzy['id'],'isCheck'=>2),array('idcard'));
				if(!empty($tzyInfo)) {//拓展员没有验证
					
					/**
					 * 组装支付数据;
					 * 支付请求数据
					 */
					$params = [
//						'fee' => 0.01,//交易金额  
						'fee' => $_GPC['payMoney'],//交易金额  
						'tid'=>'VF'.date('YmdHis',time()).time().$this->randS(6),//订单编号
						'title' =>'身份实名验证',//商品名称
						'returnUrl' => 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&m=ewei_shopv2&do=mobile&r=goods',//前端成功返回界面；
						'notifyUrl' => 'http://shop.gogo198.cn/addons/ewei_shopv2/payment/salesman/VfNotify.php',//后台处理
						'openid'=> $_W['openid'],//验证用户openid
						'uid' =>$tzy['id'],//拓展员所属id
						'username' => $tzy['username'],//拓展员姓名
						'idcard' =>$tzyInfo['idcard'],//拓展员身份证号码;
						'payType'=> 'wechat',//支付类型；
						'uniacid' =>$tzy['uniacid'],//当前公众号id
					];
					/**
					 * 将数据插入表中  foll_vfold 支付订单表；
					 * 1、插入成功
					 * 2、请求支付，返回支付状态；
					 * 3、支付成功：返回支付URL  赵金如
					 */
					$insert = pdo_insert('foll_vfold',$params);
					if(!empty($insert)) {
						//获取公共函数中的微信支付功能
						$varlifi = m('common')->Varlifi_wechat($params);
						//判断支付是否获取成功
						if($varlifi['status'] == '100' && $varlifi['message'] == '获取成功') {
							
							if(isset($varlifi['pay_url'])) {//判断是否存在支付Url；
								$reData = [
									'status' => 'success',
									'payUrl' => $varlifi['pay_url'],
								];
								show_json(1,$reData);//返回支付URL
							}
							
						}else{
							show_json(0,'支付参数错误,请重新请求');
						}
						
					}else {
						show_json(0,'请求创建失败,请重新提交');
					}
					
				}else {//拓展员已验证
					show_json(0,'您已验证，请勿重复验证！');
				}
				
			}else {//没有拓展员信息
				show_json(0,'拓展员信息不存在，请联系管理员');
			}
	
		}
	}
	
	
	//支付宝支付
	function alipay()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		/**
		 * 调用步骤;
		 * 1、判断是否AJAX提交
		 * 2、组装支付数据
		 * 3、支付配置
		 * 4、数据写入表内，
		 * 5、请求数据
		 * 6、返回数据返回前台唤起支付；
		 */
		if( $_W['isajax'] && (!empty($_GPC['payMoney']))) {
			
			//查找foll_user表，拓展员id，拓展员姓名;
			$tzy = pdo_get('foll_user',array('openid'=>$_W['openid']),array('id','username','uniacid'));
			if(!empty($tzy)) {//如果有数据；存在该拓展员；
				//查找拓展员验证信息；foll_verified_salesman,条件：uid = 拓展员id，并且验证状态为2,查询出该拓展员的身份证号码
				$tzyInfo = pdo_get('foll_verified_salesman',array('uid'=>$tzy['id'],'isCheck'=>2),array('idcard'));
				if(!empty($tzyInfo)) {//拓展员没有验证
					
					/**
					 * 组装支付数据;
					 * 支付请求数据
					 */
					$params = [
//						'fee' => 0.01,//交易金额  
						'fee' => $_GPC['payMoney'],//交易金额  
						'tid'=>'VF'.date('YmdHis',time()).time().$this->randS(6),//订单编号
						'title' =>'身份实名验证',//商品名称
						'returnUrl' => 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&m=ewei_shopv2&do=mobile&r=goods',//前端成功返回界面；
						'notifyUrl' => 'http://shop.gogo198.cn/addons/ewei_shopv2/payment/salesman/VfNotify.php',//后台处理
						'openid'=> $_W['openid'],//验证用户openid
						'uid' =>$tzy['id'],//拓展员所属id
						'username' => $tzy['username'],//拓展员姓名
						'idcard' =>$tzyInfo['idcard'],//拓展员身份证号码;
						'payType'=> 'alipay',//支付类型；
						'uniacid' =>$tzy['uniacid'],//当前公众号id
					];
					/**
					 * 将数据插入表中  foll_vfold 支付订单表；
					 * 1、插入成功
					 * 2、请求支付，返回支付状态；
					 * 3、支付成功：返回支付URL  赵金如
					 */
					$insert = pdo_insert('foll_vfold',$params);
					if(!empty($insert)) {
						//获取公共函数中的微信支付功能
						$varlifi = m('common')->Varlifi_alipay($params);
						//判断支付是否获取成功
						if($varlifi['status'] == '100' && $varlifi['message'] == '获取二维码成功') {
							
							if(isset($varlifi['codeUrl'])) {//判断是否存在支付Url；
								$reData = [
									'status' => 'success',
									'payUrl' => $varlifi['codeUrl'],
								];
								show_json(1,$reData);//返回支付URL
							}
							
						}else{
							show_json(0,'支付参数错误,请重新请求');
						}
						
					}else {
						show_json(0,'请求创建失败,请重新提交');
					}
					
				}else {//拓展员已验证
					show_json(0,'您已验证，请勿重复验证！');
				}
				
			}else {//没有拓展员信息
				show_json(0,'拓展员信息不存在，请联系管理员');
			}
		}
		
	}
	
	//实名验证返回页面用户确认；ReurlIdcard
	function ReurlIdcard()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		//如果状态不等于空
		if(!empty($_GPC['key'])) {
			
			$key = base64_decode($_GPC['key']);
			$busInfo = json_decode(cache_load($key),true);
			
			$user = pdo_get('foll_verified_salesman',array('uid'=>$busInfo['uid']),array('isCheck'));
			if(!empty($user)){
				$info = [
					'isCheck' => $user['isCheck'],//验证状态：1验证，2未验证
					'uid' => $busInfo['uid'],//验证用户ID
					'uName' => $busInfo['uName'],//验证名称
					'code' => $busInfo['code']==1000?'验证成功':'验证失败',//验证状态
					'msg' => $busInfo['msg'],//验证有效性
					'sex' => $busInfo['sex'],//性别
					'brithday' => $busInfo['brithday'],//生日
					'area' => $busInfo['area'],//所属地区
					'key' =>$_GPC['key'],//缓存键
				];
			}
			
		}
		
		include $this->template('money/sales/idcard');
	}
	
	//核实确认身份信息
	function ReurlIdcardCheck()
	{
		global $_W;
		global $_GPC;
		//如果有ajax提交
		if($_W['isajax'] && !empty($_GPC['uid'])) {
			/**
			 * 如果用户验证状态：isCheck == 2 允许确认审核，否则已审核不能修改；
			 */
			$user = pdo_get('foll_verified_salesman',array('uid'=>$_GPC['uid']),array('isCheck'));	
			if(!empty($user) && $user['isCheck'] == 2) {
				
				$upData = [
					'code' => $_GPC['code'],//验证状态
					'msg' => $_GPC['msg'],//验证有效性
					'uName' => $_GPC['uName'],//用户名称
					'sex' => $_GPC['sex'],//性别
					'brithday' => $_GPC['brithday'],//生日
					'area' => $_GPC['area'],//所属地区
					'isCheck'=> 1
				];
				
				$res = pdo_update('foll_verified_salesman', $upData, array('uid' => $_GPC['uid']));
				if(!empty($res)){
					
					$key = base64_decode($_GPC['keys']);
					//清除缓存
					cache_delete($key);
					
					show_json(1,'核实确认成功');
					
				}else {
					show_json(0,'核实确认失败');
				}
			}else {
				show_json(0,'您的信息已核实，请勿重复核实!');
			}
		}
	}
	
	
	
	//9位随机数
    function randS($n = 1)
	{
		$ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
		$list=explode(",",$ychar);
		
		for($i=0;$i<$n;$i++){
			
			$randnum=rand(0,35); // 10+26;
			
			$authnum.=$list[$randnum];
		}
		
		return $authnum;
	}
	
	
	
	/**
	 * 拓展员登录  页面展示；
	 */
	function login()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$title = '用户登录';
		
		include $this->template('money/sales/login');
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
			//查询条件，拓展员电话，openid,角色：拓展员；

			$where = ['tel'=>$_GPC['phone'],'openid'=>$_W['openid'],'role'=>2];
			//查询用户表  是否存在该用户的信息
			$selData = pdo_get('foll_user',$where,array('id'));
			//如果存在该用户；
			if(!empty($selData)) {
				//查询条件：用户id,等于商户id;验证状态：1 
				$where1 = ['uid'=>$selData['id'],'isCheck'=>1];
				//查询 foll_verified_salesman   拓展员
				$sale = pdo_get('foll_verified_salesman',$where1,array('id'));
				if(!empty($sale)) {
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
					show_json(0,'用户尚未验证');
				}
				
			}else {
				show_json(0,'用户不存在');
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
			
			//获取foll_user表中是否存在该手机号码;
			$phones = pdo_get('foll_user',array('tel'=>$_GPC['phone'],'role'=>2),array('username'));
			if(!empty($phones)) {
				
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
				show_json(0,'该号码不是拓展员的手机号码');
			}
		}
		
	}
	// 获取短信发送配置
	function GetSemid($uid)
	{
		return pdo_get('ewei_shop_sms',array('uniacid'=>$uid),array('id'));
	}
	
	
	
	
	/*
	 * 业务管理 == 商家添加
	 */
	function business()
	{
		global $_W;
		global $_GPC;
		//获取cookie中的UserLogin值，进行base64编码
		
		$values = $_W['openid'];
		//对COOKID的值进行解码；解码后的数据不为空，并且存在缓存中
		$key = base64_decode($_COOKIE[$values]);
		//key是否存在，并且存在缓存中
		if(!empty($key)||!empty(cache_load($key))) {//如果用户未登录，提示用户登录，跳转登录；
			//拓展员信息  查询拓展员的ID
			$salesid = pdo_get('foll_user',array('openid'=>$_W['openid']),array('id'));
			//查询所有为验证的商户信息；
			$sql = "SELECT id,company_name FROM ims_foll_verified_business WHERE isCheck = 2 and uid in(SELECT id FROM ims_foll_user WHERE pid={$salesid['id']})";
			
			//查询所属拓展员的商家
			if(!empty($salesid)){
				$busInfo = pdo_fetchall($sql);//获取所有商家信息；
			}
			include $this->template("money/sales/business");
			exit();
//			message('请登录',mobileUrl('money/salesman/login'));	
		}
		//跳转登录；
		header('Location:'.mobileUrl('money/salesman/login'));
	}
	
	//商家添加页面
	function businessAdd()
	{
		global $_W;
		global $_GPC;
		
		//开发步骤；
		/**
		 * 1.查询该拓展员的信息，判断商家信息是否存在；
		 * 2.获取拓展员的ID 自增ID作为父级id
		 * 3.添加商家进入后台ims_foll_user 用户表中
		 * 4.查询商户表中是否存在信息（存在，不存在）
		 * 5.插入数据到商户表中；（等待商户验证 交费用；）
		 * 
		 */
		if($_W['isajax'] && !empty($_GPC['codeOrg'])) {
			//查询用户表中是否存在该拓展员
			$user = pdo_get('foll_user',array('openid'=>$_W['openid']),array('id'));
			if(!empty($user)) {//如果存在该拓展员
				
				$saWhere = [//查询条件
					'company_name'=> $_GPC['CopyName'],//企业名称
					'person_name'=> $_GPC['uname'],//企业法人
					'person_tel'=> $_GPC['phone'],//法人手机
					'credit_code'=> $_GPC['codeOrg'],//企业信用代码
				];
				//查询 商户表中是否存在该商户信息；
				$saLesMan = pdo_get('foll_verified_business',$saWhere,array('id'));
				if(!empty($saLesMan)) {//存在该商户
					show_json(0,'该商户已存在请勿重复添加');
//					show_json(0,$saLesMan);
				
				}else {//不存在该商户
					
					/**
					 * 1、查询该号码是否存在  foll_user  电话号码是否存在
					 * 2、判断输入的信用代码是否存在  foll_verified_business 是否存在
					 */
					$phone = pdo_get('foll_user',array('tel'=>$_GPC['phone']),array('id'));
					if(!empty($phone)){
						show_json(0,'手机号码已存在');
						exit;
					}
					
					$busi = pdo_get('foll_verified_business',array('credit_code'=>$_GPC['codeOrg']),array('id'));
					if(!empty($busi)){
						show_json(0,'企业统一信用号已存在');
						exit;
					}
					
					
					/**
					 * 先把商家的数据添加到foll_user表中，再添加到foll_verified_business表中；
					 */
					$inserUser = [
						'uniacid' => $_W['uniacid'],//公众号所属ID
						'tel' => $_GPC['phone'],//商户电话；
						'username'=> $_GPC['uname'],//商户名称；
						'role'=> 3,//用户权限商户3
						'pid'=> $user['id'],//父级
						'create_time'=> time(),//创建时间；
					];
					//数据插入foll_user表中
					$insU = pdo_insert('foll_user',$inserUser);
					$uid = pdo_insertid();//自增ID
					
					$inserBusiness = [
						'uid'=> $uid,//自己ID
						'company_name'=>$_GPC['CopyName'],//企业名称
						'person_name'=>$_GPC['uname'],//企业法人
						'person_tel'=>$_GPC['phone'],//法人手机
						'credit_code'=>$_GPC['codeOrg'],//企业信用代码
						'account_type'=>1,//商户类型：1，企业，2：法人
						'account'=>'0',//银行账号
						'bank'=>'所属银行',//所属银行
						'pay_status' => 2,//2未支付；
						'create_time'=>time(),//创建时间
					];
					
					//数据插入foll_verified_business表中
					$insB = pdo_insert('foll_verified_business',$inserBusiness);
					if(!empty($insU) && !empty($insB)){
						show_json(1,'添加成功');	
					}else{
						show_json(0,'添加失败');
					}
				}
				
			}else {//不存在该业务员;
				
				show_json(0,'您不是业务员，请联系管理员！');
			}
			
		}else{
			show_json(0,'数据不能为空！');
		}
	}
	
	/**
	 * 2018-03-08
	 * 微网功能
	 */
	function JiaoYi()
	{
		global $_W;
		global $_GPC;
		if(!empty($_GPC['Uphone']) && $_W['isajax']){
			//判断该手机号是否存在 foll_user 表中 并且是商家
			$phone = pdo_get('foll_user',array('tel'=>$_GPC['Uphone'],'role'=>3),array('id'));
			if(!empty($phone['id'])){
				$inserData = [
					'uid' => $phone['id'],//商户ID
					'url'=>$_GPC['gurl'],//官网地址
				];
				//查询该商家是否已添加官网地址  存该商户信息
				$isUrl = pdo_get('foll_website',array('uid'=>$phone['id']),array('id'));
				if(!empty($isUrl)){//存在					
					show_json(0,'您的微网地址已添加，请勿重复添加');
				}else {//不存在URL
					$inser = pdo_insert('foll_website',$inserData);
					show_json(1,'微网地址添加成功');
				}				
			}else {
				show_json(0,'该手机号不属于商家 ，请重新输入');
			}
			
		}else {
			show_json(0,'您提交的数据不完整');
		}
	}
	
	
	//商家验证 接收用户UID；工商未验证用户
	function busiYz()
	{
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['UID'])) {
			$uid  = $_GPC['UID'];			
			include $this->template('money/sales/busiYz');
		}
		
	}
	
	//工商费用支付;微信支付
	function Yzwechat()
	{
		global $_W;
		global $_GPC;
		
		if($_W['isajax'] && !empty($_GPC['uid']) ) {
			/**
			 * 开发步骤：
			 * 1、查询该商家是否验证 uid  isCheck  1 2:未验证
			 * 2、将验证信息写入订单内，foll_vfold 
			 * 3、请求支付链接url，
			 */
			$busUser = pdo_get('foll_verified_business',array('id'=>$_GPC['uid'],'isCheck'=>2));
			
			if(!empty($busUser)) {
				/**
					 * 组装支付数据;
					 * 支付请求数据
					 */
					$params = [
//						'fee' => 0.01,//交易金额  
						'fee' => $_GPC['payMoney'],//交易金额  
						'tid'=>'VF'.date('YmdHis',time()).time().$this->randS(6),//订单编号
						'title' =>'工商实名验证',//商品名称
						'returnUrl' => 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&m=ewei_shopv2&do=mobile&r=goods',//前端成功返回界面；
						'notifyUrl' => 'http://shop.gogo198.cn/addons/ewei_shopv2/payment/salesman/BusNotify.php',//后台处理
						'openid'=> $_W['openid'],//验证用户openid
						'uid' => $_GPC['uid'],//商家所属ID
						'username' => $busUser['company_name'],//企业名称
						'idcard' => $busUser['credit_code'],//企业统一信用号
						'payType'=> 'wechat',//支付类型；
						'uniacid' =>$_W['uniacid'],//当前公众号id
					];
					/**
					 * 将数据插入表中  foll_vfold 支付订单表；
					 * 1、插入成功
					 * 2、请求支付，返回支付状态；
					 * 3、支付成功：返回支付URL  赵金如
					 */
					$insert = pdo_insert('foll_vfold',$params);
					if(!empty($insert)) {
						//获取公共函数中的微信支付功能
						$varlifi = m('common')->Varlifi_wechat($params);
						//判断支付是否获取成功
						if($varlifi['status'] == '100' && $varlifi['message'] == '获取成功') {
							
							if(isset($varlifi['pay_url'])) {//判断是否存在支付Url；
								$reData = [
									'status' => 'success',
									'payUrl' => $varlifi['pay_url'],
								];
								show_json(1,$reData);//返回支付URL
							}
							
						}else{
							show_json(0,'支付参数错误,请重新请求');
						}
						
					}else {
						show_json(0,'请求创建失败,请重新提交');
					}
			}else {
				show_json(0,'该商户已验证，请勿重复验证');
			}
			
			
		}else {
			show_json(0,'无数据...');
		}
		
	}
	
	
	//工商费用支付;支付宝支付
	function Yzalipay()
	{
		global $_W;
		global $_GPC;
		
		if($_W['isajax'] && !empty($_GPC['uid']) ) {
			/**
			 * 开发步骤：
			 * 1、查询该商家是否验证 uid  isCheck  1 2:未验证
			 * 2、将验证信息写入订单内，foll_vfold 
			 * 3、请求支付链接url，
			 */
			$busUser = pdo_get('foll_verified_business',array('id'=>$_GPC['uid'],'isCheck'=>2));
			
			if(!empty($busUser)) {
				/**
					 * 组装支付数据;
					 * 支付请求数据
					 */
					$params = [
//						'fee' => 0.01,//交易金额  
						'fee' => $_GPC['payMoney'],//交易金额  
						'tid'=>'VF'.date('YmdHis',time()).time().$this->randS(6),//订单编号
						'title' =>'工商实名验证',//商品名称
						'returnUrl' => 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&m=ewei_shopv2&do=mobile&r=goods',//前端成功返回界面；
						'notifyUrl' => 'http://shop.gogo198.cn/addons/ewei_shopv2/payment/salesman/BusNotify.php',//后台处理
						'openid'=> $_W['openid'],//验证用户openid
						'uid' => $_GPC['uid'],//商家所属ID
						'username' => $busUser['company_name'],//企业名称
						'idcard' => $busUser['credit_code'],//企业统一信用号
						'payType'=> 'alipay',//支付类型；
						'uniacid' =>$_W['uniacid'],//当前公众号id
					];
					/**
					 * 将数据插入表中  foll_vfold 支付订单表；
					 * 1、插入成功
					 * 2、请求支付，返回支付状态；
					 * 3、支付成功：返回支付URL  赵金如
					 */
					$insert = pdo_insert('foll_vfold',$params);
					if(!empty($insert)) {
						//获取公共函数中的微信支付功能
						$varlifi = m('common')->Varlifi_alipay($params);
						//判断支付是否获取成功
						if($varlifi['status'] == '100' && $varlifi['message'] == '获取二维码成功') {
							
							if(isset($varlifi['codeUrl'])) {//判断是否存在支付Url；
								$reData = [
									'status' => 'success',
									'payUrl' => $varlifi['codeUrl'],
								];
								show_json(1,$reData);//返回支付URL
							}
							
						}else{
							show_json(0,'支付参数错误,请重新请求');
						}
						
					}else {
						show_json(0,'请求创建失败,请重新提交');
					}
			}else {
				show_json(0,'该商户已验证，请勿重复验证');
			}
			
			
		}else {
			show_json(0,'无数据...');
		}
		
	}
	
	
	//确认商户信息核实；
	function ReurlBusInfo()
	{
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['key'])) {
			
			$key = $_GPC['key'];
			$keys = base64_decode($_GPC['key']);
			$busInfo = json_decode(cache_load($keys),true);//读取缓存
		}
		
		include $this->template('money/sales/ReurlBusInfo');
	}
	
	//确认商户信息核实； 数据
	function ReurlBusInfoCheck()
	{
		global $_W;
		global $_GPC;
		
		//如果有ajax提交
		if($_W['isajax'] && !empty($_GPC['uid'])) {
			
			/**
			 * 如果用户验证状态：isCheck == 2 允许确认审核，否则已审核不能修改；
			 */
			$user = pdo_get('foll_verified_business',array('id'=>$_GPC['uid']),array('isCheck'));
			if(!empty($user) && $user['isCheck'] == 2) {
				
				
				//查询当前验证是信息；企业名称，法人，信用号是否与填写的一致；
				$check = pdo_get('foll_verified_business',array('credit_code'=>$_GPC['creditCode'],'company_name'=>$_GPC['orgName'],'person_name'=>$_GPC['personName']),array('id'));
				if(!empty($check)) {//数据一致
					
					$upData = [						
						'company_name' => $_GPC['orgName'],//企业名称
						'person_name' => $_GPC['personName'],//法人名称
						'credit_code' => $_GPC['creditCode'],//统一信用号
						'registerNum' => $_GPC['registerNum'],//工商注册号
						'establishDate' => $_GPC['establishDate'],//注册时间
						'isCheck'=> 1
					];
					
					$res = pdo_update('foll_verified_business', $upData, array('id' => $_GPC['uid']));
					if(!empty($res)) {//更新成功
						
						$key = base64_decode($_GPC['keys']);
						//清除缓存
						cache_delete($key);
						
						show_json(1,'核实确认成功');
						
					}else {//更新失败
						show_json(0,'核实确认失败');
					}
					
					
				} else {//数据不一致
					show_json(0,'验证的数据与填写的数据不一致');
				}
				
			}else {
				show_json(0,'您的信息已核实，请勿重复核实!');
			}
		}
	}
	
	
	/**
	 * 企业信息
	 */
	function qiye()
	{
		global $_W;
		global $_GPC;
		
		include $this->template('money/business/qiye');
	}
	
	
	
	/*
	 * 结算管理
	 */
	function Settlement()
	{
		global $_W;
		global $_GPC;
		$values = $_W['openid'];
		//对COOKID的值进行解码；解码后的数据不为空，并且存在缓存中
		$key = base64_decode($_COOKIE[$values]);
		//key是否存在，并且存在缓存中
//		if(!empty($key)||!empty(cache_load($key))) {//如果用户未登录，提示用户登录，跳转登录；
//			message('请登录',mobileUrl('money/salesman/login'));	
			include $this->template("money/sales/settlement");
			exit;
//		}
//		header('Location:'.mobileUrl('money/salesman/login'));
		
	}
	
	/**
	 * 2018-03-07
	 * 账单
	 */
	
	function bill()
	{
		global $_W;
		global $_GPC;
		
		include $this->template('money/sales/bill');
	}
	
	/**
	 * 账单确认 没问题
	 */
	function CheckOk()
	{
		global $_W;
		global $_GPC;
		
		show_json(0,$_GPC);
	}
	
	/**
	 * 账单确认 有问题
	 */
	function CheckNo()
	{
		global $_W;
		global $_GPC;
		
		show_json(0,$_GPC);
	}
	
}
?>