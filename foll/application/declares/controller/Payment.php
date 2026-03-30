<?php
namespace app\declares\controller;
use think\Controller;
use Util\data\Sysdb;
use think\Session;
use think\Request;
use think\Loader;
use think\log;
use CURLFile;

class Payment extends BaseAdmin
{
	public $lajp_ip;
	public $lajp_port;
	public $param_type_error;
	public $socket_error;
	public $java_exception;
	public $pkey;
	public $admin;//用户数据
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->lajp_ip = '127.0.0.1';//java 端IP
		$this->lajp_port = '21230';
		$this->param_type_error = 101;
		$this->socket_error = 102;
		$this->java_exception = 104;
		$this->admin = session('admin');//登录数据信息
		$this->pkey = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100d8559af15dfe44bf2144ded0933a988cd80da94a2710e2a171d3da2ae731d757ce4e36815f7cecafba37f73898c7f7607117035ce2af171229347c31bd76124abe127cb1729da9fa97c84e5f3ee5b06973bf22e1cb1ff544060f96a3191faaadf4935aaa55660b697f4472d8eeca26c3055221dc99cb7e0bf506a5bc7100ec673ca155e6c596a42e28fde3775cde8de2d1edc15c045a6b59a40643d06e4c1fe3620f281c87daac09005ec5d410b6dedae0437beb9f13a11b4bddc2cb466db6fe7f9ec3d134266229deb958d9ef0f46271d505fb67ed83f83987d0ecd3ca0fd6774b4222cbc5d66e58f896a3bd1419e91b32655d2e0ff264697a9de3ca23885ef0203010001';
	}
	
	public function test()
	{
		$this->db = new Sysdb;
		$data = [
			'id'=>3,
			'ordersn'=>date('YmdHis',time()).mt_rand(111,999),//明细订单号
			'number'=>1,//商品数量
			'shopNames'=>'儿童奶粉',//商品名称
			'shopOrd'=>'2018kkyx',//商品编号
			'shopType'=>'KK886',//商品型号
			'shopMoney'=>300,//商品金额
			'shopCNY'=>'CNY',//订单币种
			'shopName'=>'张三',//支付人姓名
			'CardType'=>'01',//证件类型
			'Cards'=>'101101199009091234',//证件号
			'account'=>'6222888888888888'//支付账号
		];
		
		
//		$this->db->table('foll_detail_list')->transaction($data);
		
		try
		{
			//开启事务
			$this->db->startTranss();
			
				$this->db->table('foll_detail_list')->insert($data);
				echo '事务提交';
			$this->db->commits();
			
		}catch(Exception $e)
		{
			$this->db->rollbacks();
			echo '事务回滚';
		}
		
		echo '执行完成';
		
//		$param['orderId'] 	= '20180528155811801';
//		//查询开始时间
//		$param['beginTime'] = '20180528160207';
//		//查询结束时间
//		$param['endTime'] 	= '20180528161008';
//		//商户ID
//		$param['partnerId'] = '10000000050';
//		//1：支付订单、2：退款订单、3：报关订单
//		$param['type']		= '3';
//		//1：单笔、2：批量
//		$param['mode']		= '1';
//		$res = $this->connect('Query',$param);
//		echo '<pre>';
//		print_r($res);
		
	}
	
	//链接
	public function connect($token='Query',$getData='')
	{
		$inArray = [
			'exchangeRateQuery',//汇率查询
			'nativeCashierOrder',//人民币收银台支付
			'exchangeCashierOrder',//外币收银台支付
			'orderQuery',//订单查询API
			'waybillUpdate',//运单更新
			'eportOrder',//报关订单
			'feeQuery',//费率查询
			'waybillAdd',//运单新增
			'Query',	//订单查询
		];
		
		if(!in_array($token,$inArray)){
			exit(json_encode(['code'=>0,'msg'=>'请求类型错误']));
		}
		
		switch($token)
		{
			//汇率查询
			case 'exchangeRateQuery':
				$res = exchangeRateQuery($getData);
				return $res;
			break;
			
			//人民币收银台支付
			case 'nativeCashierOrder':
				$res = nativeCashierOrder($getData);
				return $res;
			break;
			
			//支付订单明细
			case 'payDetail':
				$res = $this->payDetail($getData);
				return $res;
			break;
			
			//外币收银台支付
			case 'exchangeCashierOrder':
				$res = exchangeCashierOrder($getData);
				return $res;
			break;
			
			//订单查询API
			case 'orderQuery':
				$res = orderQuery($getData);
				return $res;
			break;
			
			//运单更新
			case 'waybillUpdate':
				$res = waybillUpdate($getData);
				return $res;
			break;
			
			//报关订单
			case 'eportOrder':
				$res = eportOrder($getData);
				return $res;
			break;
			
			//费率查询
			case 'feeQuery':
				$res = feeQuery($getData);
				return $res;
			break;
			
			//运单新增
			case 'waybillAdd':
				$res = waybillAdd($getData);
				return $res;
			break;
			
			//订单查询
			case 'Query':
				$res = $this->Query($param);
				return $res;
			break;
		}		
	}
	
	//人民币收银台支付 视图  2018-05-23  ======start===============
	public function nativepay()
	{
		return $this->fetch();
	}
	
	//人民币收银台支付 处理数据2018-05-23
	public function nativePost()
	{
		$userInfo = $this->admin;//用户信息
		$order = $this->db->table('foll_payment_nativepay')->field('orderId,payStatus,isUse')->where(['uid'=>$userInfo['id']])->order(' id desc ')->item();
		//支付成功并且没有上传订单明细的；
		if($order['payStatus'] == '1004' && $order['isUse'] == 1) {
			echo json_encode(['code'=>0,'msg'=>'你有未上传的订单明细，请上传']);
			exit;
		} else {
			
			//获取支付配置
			$follinfo = $this->db->table('foll_cross_border')->field('payconfig')->where(['uid'=>$userInfo['id']])->item();
			$config = json_decode($follinfo['payconfig'],true);
			
			//接收表单数据
			$info = $_POST;
			foreach($info as $key=>$val){
				$infos[$key] = trim($val);
			}
			
			$money 	= number_format($infos['orderAmount'],2);//表单提交的金额
			$money1 = (string)($money * 100);//订单提交金额 乘以100  需要提交的金额
			
			//交易金额已分为单位：1*100 = 100 = 1元；
			$infos['orderAmount'] = $money1;//需要提交的金额
			//字串拼接
			$signStr = $this->Splicing($infos);
			//数据加密
			//$signMsg = $signStr."&pkey=".$this->pkey;
			$signMsg = $signStr."&pkey=".$config['pkey'];
			$signMsg = md5($signMsg);
			
			$infos['uid'] 		  = $userInfo['id'];
			$infos['orderAmount'] = $money;//实际金额
			$infos['create_time'] = date('Y-m-d H:i:s',time());//订单创建时间
			
			//数据写入表中foll_payment_nativepay
			$res = $this->db->table('foll_payment_nativepay')->insert($infos);
			if(!$res) {
				echo json_encode(['code'=>0,'msg'=>'签名错误']);
			}
			//返回签名
			echo json_encode(['code'=>1,'msg'=>$signMsg,'money'=>$money1]);
		}
	}
	//=============END==========================
	
	
	
	//支付明细订单上传 2018-05-25   ==========start===========
	/**
	 * $Detail  明细订单数据  二维数组
	 * $orders  支付订单数据  一维数组
	 */
	public function payDetail($Detail,$orders)
	{
		
		//用户数据参数一维数组
		$userInfo = $this->admin;
		
		/**
		 * 开发步骤：
		 * 1、获取foll_payment_nativepay表中的商户订单号；
		 * 2、查询商户配置
		 * 3、把明细文件写入txt文本
		 */

		//查询配置报关信息等  报关信息：customs  检验检疫信息：inspection  支付配置：payconfig
		$info = $this->db->table('foll_cross_border')->field('customs,inspection,payconfig')->where(['uid'=>$userInfo['id']])->item();
		if(empty($info['customs']) || empty($info['inspection']) || empty($info['payconfig'])){
			return $res = ['code'=>0,'msg'=>'请先填写报关信息,或检验检疫信息或支付配置信息!!!'];
		}
		
		$customs = json_decode($info['customs'],true);//报关信息
		$inspection = json_decode($info['inspection'],true);//检验检疫信息
		$payconfig = json_decode($info['payconfig'],true);//支付配置
		
		//订单明细部分  多条订单使用二维数组
		
		/**
		 * 生成订单明细文件
		 * 1、生成文件名称
		 * 2、写入txt文件
		 */
		//命名要求：会员号_商户订单号_时间戳
		
		$fileName = $payconfig['partnerId'].'_'.$orders['orderId'].'_'.date('YmdHis',time());
//		$fileName = $payconfig['partnerId'].'_'.$payOrdersn.'_'.date('YmdHis',time());
		//文本文件路径  文件名称
		$filePath = '/www/web/default/foll/public/paylog/detail/'.$fileName.'.txt';
		
		//订单明细写入txt文件
		$str = '';
		foreach($Detail as $key=>$val)//外层控制写入次数
		{
			if(is_array($val))
			{
				foreach($val as $k=>$v)//内层控制数据拼接
				{
					$str .= trim($v).'|';
				}
				
				$str = substr($str,0,-1);
			}
			
			//写入明细文件
			file_put_contents("./paylog/detail/{$fileName}.txt", $str."\r\n",FILE_APPEND);
			$str = '';
		}

		//报文部分
		$sendArr = [
			'version'			=>'1.0',
			//订单序列号
			'requestId'			=>date('YmdHis',time()).mt_rand(1111,9999),
			//订单提交时间
			'submitTime'		=>date('YmdHis',time()),
			//商户 ID  会员ID
			'partnerId'			=>$payconfig['partnerId'],
			//对应 3.2 提交的商户订单号
			'orderId'			=>$orders['orderId'],
			//订单币种
			'orderCurrencyCode'	=>$orders['orderCurrencyCode'],
			//订单总金额  以分为单位
//			'orderAmount'		=>($orders['orderAmount'] * 100),
			'orderAmount'		=>$orders['orderAmount'],
			//是否报关 1:需要，0:不需要
			'needDeclare'		=>'1',
			/**
			 * 报关 信息
			 */
			//电子口岸代码
			'eportCode'			=>$customs['eportCode'],
			//电商平台备案号  电商平台在电子口岸的备案号。
			'eCompanyCode'		=>$customs['eCompanyCode'],
			//电商平台备案名称  电商平台在电子口岸的名称
			'eCompanyName'		=>$customs['eCompanyName'],
			//进口类型  1：保税进口 2:直邮进口 
			'intype'			=>$customs['intype'],
			//出口类型  1:一般出口 2：保税出口
//			'outtype'=>$customs['outtype'],
			//报关回调通知地址
			'declareNotifyUrl'=>'http://shop.gogo198.cn/foll/public/?s=notify/DetailNotify',
			//文件校验结果通知地址
			'fileNotifyUrl'=>'http://shop.gogo198.cn/foll/public/?s=notify/fileNotifyUrl',
			/**
			 * 检验检疫业务参数
			 */
			//检验检疫电商企业代码
			'cbeCode'			=>$inspection['cbeCode'],
			//检验检疫支付币种
			'ciqcurrency'		=>$inspection['ciqcurrency'],
			//海关关区代码
			'customsCode'		=>$inspection['customsCode'],
			//检验检疫电商企业代码
			'ciqOrgCode'		=>$inspection['ciqOrgCode'],
			//CUS:单向海关申报 BOTH:海关、国检同时申报（广州口岸必填）
			'functionCode'		=>$inspection['functionCode'],
			//明细文件
			//命名要求：会员号_商户订单号_时间戳，如：10000000017_xxx001_20170919235959.txt
			'fileName'			=>$fileName,//明细文件名称
			//文件格式为 txt，内容见 3.3.5 节“明细文件说明”，限制大小<=5M
			//'fileObj'			=>$upfile,
			//安全信息
			'remark'			=>$orders['orderId'],//扩展字段
			'charset'			=>1,//编码方式
			'signType'			=>2//签名类型
		];
		
		
		//数据拼接
		$signStr 			= $this->Splicing($sendArr);
		//数据加密
		$signMsg 			= $signStr."&pkey=".$payconfig['pkey'];
		$signMsg 			= md5($signMsg);
		$sendArr['signMsg'] = $signMsg;
		//发送报文信息
		file_put_contents("./paylog/sendArr.txt", print_r($sendArr,true),FILE_APPEND);
		
		//测试地址
		$url = 'https://uwebgatetest.hnapay.com/webgate/payDetailUpload.htm';
		//$url = 'https://uwebgate.hnapay.com/webgate/payDetailUpload.htm';
		
		//明细文件上传  数据提交
		$res = $this->upload($url,$filePath,$fileName,$sendArr);
		
		//Url=a&b=c 转换成数组
		parse_str($res,$DataInfo);
		//写入日志  返回信息
		file_put_contents("./paylog/Detail.txt", print_r($DataInfo,true),FILE_APPEND);
		
		//如果返回结果代码0001 并且消息返回请求已受理
		if($DataInfo['resultCode'] == '0001' && $DataInfo['resultMsg'] == '请求已受理')
		{
			
			//明细提交成功，支付订单状态改为已使用  支付表
			$this->db->table('foll_payment_nativepay')->where(['orderId'=>$DataInfo['orderId']])->update(['isUse'=>2]);
			
			//更新数据
			$sendArr = [
				'tradeOrderNo'	=>$DataInfo['tradeOrderNo'],
				'resultCode'	=>$DataInfo['resultCode'],
				'resultMsg'		=>$DataInfo['resultMsg'],
				'totalCount'	=>$DataInfo['totalCount'],
			];
			//更新支付订单详细表 foll_payment_detail  明细表
//			$this->db->table('foll_payment_detail')->where(['requestId'=>$DataInfo['requestId']])->update($update);

			//数据写入ims_foll_payment_detail表内
			$this->db->table('foll_payment_detail')->insert($sendArr);
			
			return $res = ['code'=>1,'msg'=>$DataInfo];
		}
		
		return $res = ['code'=>0,'msg'=>$DataInfo];
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//测试上传订单明细地址 =====================测试用==================
	public function payDetailt()
	{
		
		//用户数据参数一维数组
		$userInfo = $this->admin;
		
		/**
		 * 开发步骤：
		 * 1、获取foll_payment_nativepay表中的商户订单号；
		 * 2、查询商户配置
		 * 3、把明细文件写入txt文本
		 */
		//查询表中的支付订单信息  获取最新条支付信息  返回订单号
		$order = $this->db->table('foll_payment_nativepay')->field('orderId,orderAmount,orderCurrencyCode')->where(['uid'=>$userInfo['id'],'isUse'=>1])->order(' id desc ')->item();
		if(!$order){
			exit(json_encode(['code'=>0,'msg'=>'您当前没有支付订单，请先支付!']));
			return false;
		}
		
		//查询配置报关信息等  报关信息：customs  检验检疫信息：inspection  支付配置：payconfig
		$info = $this->db->table('foll_cross_border')->field('customs,inspection,payconfig')->where(['uid'=>$userInfo['id']])->item();
		if(empty($info['customs']) || empty($info['inspection']) || empty($info['payconfig'])){
//			exit(json_encode(['code'=>0,'msg'=>'请先填写报关信息,或检验检疫信息或支付配置信息!!!']));
			return $res=['code'=>0,'msg'=>'请先填写报关信息,或检验检疫信息或支付配置信息!!!'];
		}
		$customs = json_decode($info['customs'],true);//报关信息
		$inspection = json_decode($info['inspection'],true);//检验检疫信息
		$payconfig = json_decode($info['payconfig'],true);//支付配置
		
		//订单明细部分  多条订单使用二维数组
		$Detail = [
			0=>[
				'ordersn'=>date('YmdHis',time()).mt_rand(111,999),//明细订单号
				'number'=>1,//商品数量
				'shopNames'=>'儿童奶粉',//商品名称
				'shopOrd'=>'2018kkyx',//商品编号
				'shopType'=>'KK886',//商品型号
				'shopMoney'=>10500,//商品金额
				'shopCNY'=>'CNY',//订单币种
				'shopName'=>'张三',//支付人姓名
				'CardType'=>'01',//证件类型
				'Cards'=>'101101199009091234',//证件号
				'account'=>'6222888888888888'//支付账号
			],
//			1=>[
//				'ordersn'=>date('YmdHis',time()).mt_rand(111,999),//明细订单号
//				'number'=>1,//商品数量
//				'shopNames'=>'儿童奶粉',//商品名称
//				'shopOrd'=>'2018kkyx',//商品编号
//				'shopType'=>'KK886',//商品型号
//				'shopMoney'=>300,//商品金额
//				'shopCNY'=>'CNY',//订单币种
//				'shopName'=>'张三',//支付人姓名
//				'CardType'=>'01',//证件类型
//				'Cards'=>'101101199009091234',//证件号
//				'account'=>'6222888888888888'//支付账号
//			],
//			2=>[
//				'ordersn'=>date('YmdHis',time()).mt_rand(111,999),//明细订单号
//				'number'=>1,//商品数量
//				'shopNames'=>'儿童奶粉',//商品名称
//				'shopOrd'=>'2018kkyx',//商品编号
//				'shopType'=>'KK886',//商品型号
//				'shopMoney'=>300,//商品金额
//				'shopCNY'=>'CNY',//订单币种
//				'shopName'=>'张三',//支付人姓名
//				'CardType'=>'01',//证件类型
//				'Cards'=>'101101199009091234',//证件号
//				'account'=>'6222888888888888'//支付账号
//			],
		];
		
		
		/**
		 * 生成订单明细文件
		 * 1、生成文件名称
		 * 2、写入txt文件
		 */
		//命名要求：会员号_商户订单号_时间戳
		$fileName = $payconfig['partnerId'].'_'.$order['orderId'].'_'.date('YmdHis',time());
		//文本文件路径  文件名称
		$filePath = '/www/web/default/foll/public/paylog/detail/'.$fileName.'.txt';
		
		//订单明细写入txt文件
		$str = '';
		foreach($Detail as $key=>$val)//外层控制写入次数
		{
			if(is_array($val))
			{
				foreach($val as $k=>$v)//内层控制数据拼接
				{
					$str .= $v.'|';
				}
				
			}
			
			$str = substr($str,0,-1);
			
			$val['orderId'] = $order['orderId'];
			
			$this->db->table('foll_detail_list')->insert($val);
			//写入明细文件
			//$date = date('Ymd');
			file_put_contents("./paylog/detail/{$fileName}.txt", $str."\r\n",FILE_APPEND);
			$str = '';
		}

		//报文部分
		$sendArr = [
			'version'			=>'1.0',
			//订单序列号
			'requestId'			=>date('YmdHis',time()).mt_rand(1111,9999),
			//订单提交时间
			'submitTime'		=>date('YmdHis',time()),
			//商户 ID  会员ID
			'partnerId'			=>$payconfig['partnerId'],
			//对应 3.2 提交的商户订单号
			'orderId'			=>$order['orderId'],
			//订单币种
			'orderCurrencyCode'	=>$order['orderCurrencyCode'],
			//订单总金额  以分为单位
			'orderAmount'		=>($order['orderAmount'] * 100),
			//是否报关 1:需要，0:不需要
			'needDeclare'		=>'1',
			/**
			 * 报关 信息
			 */
			//电子口岸代码
			'eportCode'			=>$customs['eportCode'],
			//电商平台备案号  电商平台在电子口岸的备案号。
			'eCompanyCode'		=>$customs['eCompanyCode'],
			//电商平台备案名称  电商平台在电子口岸的名称
			'eCompanyName'		=>$customs['eCompanyName'],
			//进口类型  1：保税进口 2:直邮进口 
			'intype'			=>$customs['intype'],
			//出口类型  1:一般出口 2：保税出口
//			'outtype'=>$customs['outtype'],
			//报关回调通知地址
			'declareNotifyUrl'=>'http://shop.gogo198.cn/foll/public/?s=notify/DetailNotify',
			//文件校验结果通知地址
			'fileNotifyUrl'=>'http://shop.gogo198.cn/foll/public/?s=notify/fileNotifyUrl',
			/**
			 * 检验检疫业务参数
			 */
			//检验检疫电商企业代码
			'cbeCode'			=>$inspection['cbeCode'],
			//检验检疫支付币种
			'ciqcurrency'		=>$inspection['ciqcurrency'],
			//海关关区代码
			'customsCode'		=>$inspection['customsCode'],
			//检验检疫电商企业代码
			'ciqOrgCode'		=>$inspection['ciqOrgCode'],
			//CUS:单向海关申报 BOTH:海关、国检同时申报（广州口岸必填）
			'functionCode'		=>$inspection['functionCode'],
			//明细文件
			//命名要求：会员号_商户订单号_时间戳，如：10000000017_xxx001_20170919235959.txt
			'fileName'			=>$fileName,//明细文件名称
			//文件格式为 txt，内容见 3.3.5 节“明细文件说明”，限制大小<=5M
			//'fileObj'			=>$upfile,
			//安全信息
			'remark'			=>$order['orderId'],//扩展字段
			'charset'			=>1,//编码方式
			'signType'			=>2//签名类型
		];
		
		//数据写入ims_foll_payment_detail表内
		$this->db->table('foll_payment_detail')->insert($sendArr);
		
		//数据拼接
		$signStr 			= $this->Splicing($sendArr);
		//数据加密
		$signMsg 			= $signStr."&pkey=".$payconfig['pkey'];
		$signMsg 			= md5($signMsg);
		$sendArr['signMsg'] = $signMsg;
		//发送报文信息
		file_put_contents("./paylog/sendArr.txt", print_r($sendArr,true),FILE_APPEND);
		
		//测试地址
		$url = 'https://uwebgatetest.hnapay.com/webgate/payDetailUpload.htm';
		//$url = 'https://uwebgate.hnapay.com/webgate/payDetailUpload.htm';
		
		//明细文件上传  数据提交
		$res = $this->upload($url,$filePath,$fileName,$sendArr);
		
		//Url=a&b=c 转换成数组
		parse_str($res,$DataInfo);
		//写入日志  返回信息
		file_put_contents("./paylog/Detail.txt", print_r($DataInfo,true),FILE_APPEND);
		//如果返回结果代码0001 并且消息返回请求已受理
		if($DataInfo['resultCode'] == '0001' && $DataInfo['resultMsg'] == '请求已受理')
		{
			
			$this->db->table('foll_payment_nativepay')->where(['orderId'=>$DataInfo['orderId']])->update(['isUse'=>2]);
			
			//更新数据
			$update = [
				'tradeOrderNo'=>$DataInfo['tradeOrderNo'],
				'resultCode'=>$DataInfo['resultCode'],
				'resultMsg'=>$DataInfo['resultMsg'],
				'totalCount'=>$DataInfo['totalCount'],
			];
			//更新支付订单详细表 foll_payment_detail
			$this->db->table('foll_payment_detail')->where(['requestId'=>$DataInfo['requestId']])->update($update);
			return $res=['code'=>1,'msg'=>$DataInfo];
		}

		return $res=['code'=>0,'msg'=>$DataInfo];
	}
	
	// ============END=============	
	
	
	
	
	
	
	
	
	
	public function Query($param = '')
	{
		$url = "https://uwebgatetest.hnapay.com/webgate/orderQuery.htm";
//		$url = "https://uwebgate.hnapay.com/webgate/orderQuery.htm";
		
		$queryArr = [
			'version'=>'1.0',
			//查询订单号，保证唯一
			'queryOrderId'	=>date('YmdHis',time()).mt_rand(1111,9999),
			//1：单笔、2：批量
			'mode'		=>$param['mode'],
			//1：支付订单、2：退款订单、3：报关订单
			'type'		=>$param['type'],
			//单笔查询时，商户请求时传入的订单号
			'orderId'	=>$param['orderId'],
			//查询开始时间
			'beginTime'	=>$param['beginTime'],
			//查询结束时间
			'endTime'	=>$param['endTime'],
			//商户ID
			'partnerId'	=>$param['partnerId'],
			//扩展字段
			'remark'	=>'扩展字段',
			//编码方式
			'signType'	=>'2',
			//加密方式
			'charset'	=>'1',
		];
		$strMsg = $this->Splicing($queryArr);
		$signStr = $strMsg .'&pkey='.$this->pkey;
		$queryArr['signMsg'] = md5($signStr);
		
		$res = $this->doPost($url,$queryArr);
//		parse_str($res,$resInfo);
		return $this->xmlToArray($res);
	}
	
	
	/**
	 * 以下是工具方法：==================
	 */
	//数据拼接
	public function Splicing(&$val)
	{
		$str = '';
		ksort($val);
		if(is_array($val))
		{
			foreach($val as $k=>$v)
			{
				if($v !== null && trim($v) !== '' && $k !== 'signMsg')
				{
					$str.= $k.'='.$v.'&';
				}
			}
		}
		return substr($str,0,-1);
	}
	
	//XML数据转数组
	public function xmlToArray($xml)
	{
		libxml_disable_entity_loader(true);
		$xmlstring = simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
		$val = json_decode(json_encode($xmlstring),true);
		return $val;
	}
	
	/**
	 * 将数组转换为xml
	 * @param array $data    要转换的数组
	 * @param bool $root     是否要根节点
	 * @return string         xml字符串
	 * @author Json
	 */
	public function ArrToXmls($data,$root=true)
	{
		$str = '';
		if($root) $str .= "<xml>";
		foreach($data as $key=>$val)
		{
			if(is_array($val))
			{
				$child = $this->ArrToXmls($val,false);
				$str .= "<$key>$child</$key>";
			} else {
				$str .= "<$key>[$val]</$key>";
			}
		}
		if($root) $str .= "</xml>";
		return $str;
	}
	
	//curl post 请求
	public function submitPost($url,$param)
	{
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			$result = curl_exec($ch);
			curl_close($ch);
//			return $result;//返回结果
			echo $result;
		}catch(Exception $e){
			file_put_contents('./paylog/MyError.txt', print_r($e,TRUE),FILE_APPEND);
		}
	}
	
	//post 请求
    public function doPost($url,$post_data)
    {
    	$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        // 执行后不直接打印出来
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        // 设置请求方式为post
        curl_setopt($ch,CURLOPT_POST,true);
        // post的变量
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    
    //文件上传 2018-05-28
    /**
     * $url  请求地址
     * $file  需上传文件路径与文件名称
     * $fileName  文件名称
     * $post_dat  上传参数
     */
    public function upload($url,$file,$fileName,$post_data)
    {
    	$obj = new CurlFile($file);
    	$obj->setMImeType('txt');//设置后缀
    	$obj->setPostFilename($fileName);//设置文件名
    	$post_data['fileObj'] = $obj;
    	$ch = curl_init();
    	curl_setopt($ch,CURLOPT_URL,$url);
    	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    	curl_setopt($ch,CURLOPT_POST,1);
    	curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
    	$output = curl_exec($ch);
    	curl_close($ch);
    	return $output;
    }
    
    
    //发送文件
    /** php 发送流文件
	* @param  String  $url  接收的路径
	* @param  String  $file 要发送的文件
	* @return boolean
	*/
    public function sendStreamFile($url,$file)
    {
    	if(file_exists($file))
    	{
    		$opts = [
    			'http'=>[
    				'method'=>'POST',
    				'header'=>'content-type:application/x-www-form-urlencoded',
    				'content'=>file_get_contents($file)
    			],
    		];
    		$context = stream_context_create($opts);
    		$res = fopen($url,'rb',false,$context);
    		$response = file_get_contents($url,false,$context);
    		$ret = json_decode($response,true);
//  		return $ret['success'];
			return $res;
    	} else {
    		return false;
    	}
    }
	
	//
	public function lajp_call()
	{
		//参数数量
		$args_len = func_num_args();
		//参数数组
		$arg_array = func_get_args();
		
		//参数数量不能小于1
		if($args_len < 1){
			throw new Exception("[LAJP Error] lajp_call function arguments length < 1 ",PARAM_TYPE_ERROR);
		}
		
		//第一个参数是java类,方法名称，必须是string类型
		if(!is_string($arg_array[0])){
			throw new Exception("[LAJP Error] lajp_call function's first argument must be string \"class_name::method_name\".", PARAM_TYPE_ERROR);
		}
		
		if(($socket = socket_create(AF_INET,SOCK_STREAM,0)) === false){
			throw new Exception("[LAJP Error] socket create error.", SOCKET_ERROR);
		}
		
		if(socket_connect($socket,$this->lajp_ip,$this->lajp_port) === false){
			throw new Exception("[LAJP Error] socket connect error.", SOCKET_ERROR);
		}
		
		//消息体序列化
		$request = serialize($arg_array);//数组序列化
		$req_len = strlen($request);//取序列化长度
		
		$send_len = 0;
		
		do{
			//发送
			if(($sends = socket_write($socket,$request,strlen($request))) === false){
				throw new Exception("[LAJP Error] socket write error.",SOCKET_ERROR);
			}
			$send_len += $sends;
			$request = substr($request,$sends);
		}while($send_len < $req_len);
		
		
		//接收
		$response = '';
		while(true){
			$recv = '';
			if(($recv = socket_read($socket,1400)) === false){
				throw new Exception("[LAJP Error] socket read error.", SOCKET_ERROR);
			}
			
			if($recv == ''){
				break;
			}
			
			$response .= $recv;
		}
		
		//关闭
		socket_close($socket);
		
		$rsp_stat = substr($response,0,1);	//返回类型，'S'：成功，'F':异常
		$rsp_msg = substr($response,1);		//返回信息
		
		if($rsp_stat == 'F') {
			//异常信息不用反序列化
			throw new Exception("[LAJP Error] Receive Java exception: ".$rsp_msg, JAVA_EXCEPTION);
		} else {
			if($rsp_msg != 'N')//返回非void
			{
				//反序列
				return unserialize($rsp_msg);
			}
		}
	}
	
}
?>