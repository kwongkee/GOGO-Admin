<?php
header('Content-Type:text/html;charset=utf-8');
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
define("KEYS","f8ee27742a68418da52de4fca59b999e");
define("KEYS1","b4f16b4526b046c580e363fcfcd07c82");
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
//load()->app('common');
load()->app('template');
//加载发送消息函数  diysend
load()->func('diysend');
global $_W;
global $_GPC;

//按订单号查询该订单信息发起支付
/**
 * 开发步骤：主动支付；
 * 1、表单上传token  与订单号 ordersn:
 * 例如：token = wechat,
 * ordersn:GGPgogo198201712051009
 */
//echo '获取前14位：转换时间搓';
//echo strtotime(substr('20180820153106918946',0,14));


/**
 * 银联无感对账
 */
//汇总部分
/*'7000000000000049 接入方代码
20180620		  对账日期
000001			 支付总笔数
000000000000000400	支付总金额
000000				退款总笔数
000000000000000000	退款总金额
000000000000000002	总手续费
000000000000000398	应收款金额';

//交易明细行
'00000001	序号
1			交易类型：1支付，2退款
20180620040000391256	平台订单号
G99198101570223660201806201786	下游订单号  
20180620			交易日期
000000000000000400	交易金额
00000002			交易手续费
20180620			结算日期
1000000000000007	商户号
佛山伦教路边停车位		商户名称';*/
//$times = $_GPC['times'];
// 执行获取
data_aa();

//通莞数据
function data_aa($times=null)
{
	$appId	=	'tgkj22493580';
	$key	=	'cef9ea4f0ed2cf9352ed6c23d7734345';
	//昨天通莞数据
	$time   =  !empty($times)? date('Ymd',$times):date('Ymd',strtotime('-1 day'));
	// 开始时间 2019-07-15
	$start  = $time.'000000';
    // 结束时间 2019-07-15
	$end    = $time.'235959';
	//数据组装
	$data   = array(
		'api' 		=>	"statement/trade",
      	'appId' 	=>	$appId,//tgkj22493580
      	'fromDate'	=>	$start,//$time,//20190712
      	'toDate'	=>	$end,//$time,//20190712
      	'merId'		=>	"617112200019682",//"617112400019774"
      	'method'	=>	"checkTradeDetail",
	);
	//转换key=value&key=value;
	$str = tostring($data);	
	//拼接加密字串
	$str .= '&key='.$key;
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$data['sign'] = strtoupper($sign);
	//数据请求地址，post形式传输
	$strs = splice($data);
	$url = "http://erp.yltg.com.cn/erpApi/?".$strs;//http://erp.yltg.com.cn/erpApi/?
	$responses = ihttp_gets($url);
	//file_put_contents('./log/loadBill'.$time.'.txt',$responses."\r\n");
	
	//解析json数据
	$response = json_decode($responses, true);

	//2018-10-16
    //文件路径
    //$path = '../../crontab/aq/'.$time.'.txt';
    $path = "/www/web/default/crontab/aq/{$time}.txt";
    if(!file_exists($path)) {
    	$dataStr = '';
	    if(!empty($response['trade'])) {
	    	
	    	foreach($response['trade'] as $key=>$val) {
	    		//支付成功或支付失败的数据   订单状态：0支付成功,1支付失败,2已撤销,4待支付，5已转入退款，6已转入部分退款,
	    		if(($val['state'] == 0) || ($val['state'] == 5) || ($val['state'] == 6) ) {//支付成功数据
	    		  //$datastr .= $val['pay_time'].','.$val['pay_money'].','.$val['fcp_id'].','.$val['order_id']."\r\n";
	    		  //订单创建时间，支付通道编号，支付状态，支付时间，支付金额，上游订单号，下游订单号
	    		  	//$dataStr .= $val['create_time'].','.$val['channel_id'].','.$val['state'].','.$val['pay_time'].','.($val['pay_money']/100).','.$val['order_id'].','.$val['low_order_id'].','.($val['refund_money']/100)."\r\n";
	    		  	$dataStr .= $val['state'].','.$val['pay_time'].','.($val['pay_money']/100).','.$val['order_id'].','.$val['low_order_id'].','.($val['refund_money']/100)."\r\n";	    		 	
	    		}
	    	}
	    }
	    
	    //写入文件
	    file_put_contents($path,print_r($dataStr,TRUE),FILE_APPEND);
	    echo json_encode(['code'=>1,'msg'=>'download ok']);die;
	} else {
		
		echo json_encode(['code'=>1,'msg'=>'download ok!']);die;
	}
	
}

/**
 * 通莞支付对账文件下载。
 * @params 订单支付信息
 * @config  配置信息
 */
function Tg_loadBill() {
	
	$appId	= 'tgkj22493580';
	$key	= 'cef9ea4f0ed2cf9352ed6c23d7734345';
	$time = date('Y-m-d',strtotime('-1 day'));
	
	$package = array();
	$package['api'] 	= "statement/trade";
	$package['appId'] 	= $appId;
	$package['fromDate'] = $time;
	$package['toDate']   = $time;
	$package['merId']    = "617112200019682";
	$package['method']   = "checkTradeDetail";
	
	//转换key=value&key=value;
	$str = tostring($package);	
	//拼接加密字串
	$str .= '&key=' . $key;
	//MD5加密字串
	$sign = md5($str);
	//返回加密字串转换成大写字母
	$package['sign'] = strtoupper($sign);
	//数据包转换成json格式
	$data =  json_encode($package);
	//数据请求地址，post形式传输
	$url = 'http://erp.yltg.com.cn/erpApi';
//	测试地址
//	$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/unifiedorder';
	//数据请求地址，post形式传输
	$response = ihttp_posts($url,$data);
	//解析json数据
	$response = json_decode($response,TRUE);
	//返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
	return $response;
}


/**
 * @数据请求提交POST json
 * @$url:请求地址
 * @post_data:请求数据
 */
function ihttp_posts($url,$post_data) {
	//初始化	 
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
	$result = curl_exec($ch);
	curl_close($ch);
	return $result;
}

function httpspost($url,$post_data){
	$curl = curl_init(); // 启动一个CURL会话
	curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
	curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
	curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
	curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data); // Post提交的数据包
	curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
	curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
	$tmpInfo = curl_exec($curl); // 执行操作
	if (curl_errno($curl)) {
		echo 'Errno'.curl_error($curl);//捕抓异常
	}
	curl_close($curl); // 关闭CURL会话
	return $tmpInfo; // 返回数据，json格式
}
// GET 请求
function ihttp_gets($url){
	 //初始化
     $curl = curl_init();
     //设置抓取的url
     curl_setopt($curl, CURLOPT_URL, $url);
     //设置头文件的信息作为数据流输出
     curl_setopt($curl, CURLOPT_HEADER, 0);
     //设置获取的信息以文件流的形式返回，而不是直接输出。
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
     //执行命令
     $data = curl_exec($curl);
     //关闭URL请求
     curl_close($curl);
     //返回获得的数据
     return $data;
}

/**
 * 字符串拼接
 * @arrs :数组数据
 */
function tostring($arrs) {
	ksort($arrs, SORT_STRING);
	$str = '';
	foreach ($arrs as $key => $v ) {
		if ($v=='' || $v == null) {
			continue;
		}
		$str .= $key . '=' . $v . '&';
	}
	$str = trim($str,'&');
	return $str;
}

function splice($Arrs)
{
	ksort($Arrs);
	$str = '';
	foreach($Arrs as $key=>$val){
		$str .= $key.'='.$val.'&';
	}
	return substr($str,0,-1);
}

?>