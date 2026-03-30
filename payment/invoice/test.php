<?php
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
$curlpost = new Curl;//实例化
define('IN_IA',TRUE);
define('IN_MOBILE',TRUE);
define('URL', 'http://shop.gogo198.cn/payment/invoice/invoice.php');

	/**
	 * 更新发票配置
	 */
	$data = [
		'token' =>'UpConfig',
		'uniacid' => '14',
	];
	$result = $curlpost->post(URL,$data);
	$json = json_decode($result->response,TRUE);
	echo '<pre>';
	print_r($json);
	echo '更新成功！';
	
	/**
	 *发票订单查询；
	 */
	/*$data = [
		'token' =>'QueryEli',
		'uniacid' => '14',
	];
	$url = 'http://shop.gogo198.cn/payment/invoice/invoice.php';
	$result = $curlpost->post($url,$data);
//	$json = json_decode($result->response,TRUE);
	echo '<pre>';
	print_r($result->response);*/
	
	
	/*$sendArr = [
		'token' =>'SendinVeli',
		'uniacid' => '14',
	    
	    'FPQQLSH'=>'OGGP'.date('YmdHis',time()).rand(1,99),//发票流水号		s
	    'DDH'=>'GGP'.date('YmdHis',time()).rand(1,99),//订单编号			s
	    
	    'XMMC'=>'路内智能停车服务',//项目名称	s
		'DW'=>'个',//单位  个		n
		'XMSL'=>'2.00',//项目数量	s	订单部分
		'XMDJ'=>'2.00',//项目单价	s	订单部分	
		'GGXH'=>'WX10010',//规格型号  填写车位编码	n		配置部分
		/**
		 * 购货方信息	订单部分
		 */
		/*'GHF_NSRSBH'=>'440002999999441',//购货方识别号	可空	91440604MA4W42D54L（找不到）n
		'GHF_DZ'=>'佛山市南海区桂城天佑三路美邦公寓',//		n
		'GHF_GDDH'=>'0757-86329922',//		n
		'GHF_SJ'=>'13044221462',//购货方手机 用于接收发票短信链接；	n
		'GHF_YHZH'=>'广州银山支行  3602051719200476881',//		n
	    'GHF_MC'=>'赵子龙',//购货方名称		s
		'GHFQYLX'=>'01',//购货方企业类型：01企业，02机关事业单位03：个人,04:其他；
		
//		'YHZCBS'=>'0',//优惠政策：0不使用，1使用	n
		
//		'HSBZ'=>'1',//含税标志		s	配置部分
//		'SL'=>'0.17',//税率		s	配置部分		
		
//	    'SPBM'=>'3070500000000000000',//商品分类编码	s  3070500000000000000（居民日常服务）		1070101010200000000（车用汽油）
		 /**
		 * 销货方信息	配置部分
		 */
//	    'KP_NSRSBH'=>'440002999999441',//开票方纳税人识别号	s
//	    'KP_NSRMC'=>'佛山市成柏电子科技有限公司',//开票方名称		s
//	    'DKBZ'=>'1',//代开标志:1、自开(0),2、代开(1)			s
//	    'XHF_NSRSBH'=>'440002999999441',//销货方识别号		440002999999441		s
//	    'XHF_MC'=>'佛山市成柏电子科技有限公司',//销货方名称								s
//	    'XHF_DZ'=>'佛山市顺德区伦教街道办事处常教社区居民委员会尚成路1号唯美嘉园6座103号铺',//销货方地址		s
//	    'XHF_DH'=>'0757-86329911',//销货方电话				s
//	    'XHF_YHZH'=>'中国工商银行广州银山支行  3602051719200476881',//销货方银行账号		s
//	    'KPR'=>'成柏电子',//开票员
//	    'SKR'=>'喜柏停车',//收款人
//	    'FHR'=>'喜柏停车',//复核人
	/*];
	
	$result = $curlpost->post(URL,$sendArr);
	$json = json_decode($result->response,TRUE);
	echo '<pre>';	
	print_r($result->response);*/
	
	
	
	/*$sendArr = [
		'token'=>'getFunc',
		'uniacid' =>'14',
	];
	$result = $curlpost->post(URL,$sendArr);
//	$json = json_decode($result->response,TRUE);
	echo '<pre>';
	
	print_r($result->response);*/
	
	
	
	
	/**
	 * ims_user_invoices:用户发票订单表
	 * ims_invoices_ord:发票订单流水表；
	 * ims_invoice_config:企业信息配置表；
	 */
	
?>