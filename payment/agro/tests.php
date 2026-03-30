<?php
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
$curlpost = new Curl;//实例化
define('IN_IA',TRUE);
define('IN_MOBILE',TRUE);
global $_W;
global $_GPC;
	
	
	$str = 12.0011;
	echo (float)$str;
	echo '<hr>';
	echo round($str,2);
	echo '<hr>';
	echo round($str,2)*100;
	die;
	
	
//	$file = file_get_contents('./log/text.txt');
//	print_r($file);
	
//	$content = htmlspecialchars_decode($file);
//	
//	echo '<pre>';
//	print_r($content);
//	echo "<br>";
//	
//	print_r(json_decode($content,true)[0]);
//	echo 'success';
//	die;
	
	$strs = '';
	$sat[] = '[{"PlaceNum":"001401","PoleNo":"������·40","InTime":"2018-05-09 10:55:32.000","CardNo":null,"PayEndTime":null,"AlarmTime":"2018-05-09 11:06:32.000","AlarmType":1}]';
//	echo $strs = substr($strs,1,-1);
//	$js = json_decode($strs,true);
	$str = str_replace('[',"'",$sat[0]);
	$str = str_replace(']',"'",$str);
	echo '<pre>';
	print_r($str);
	die;
	
	
	$str = '{"PlaceNum":"001401","PoleNo":"������·40","InTime":"2018-05-09 10:55:32.000","CardNo":null,"PayEndTime":null,"AlarmTime":"2018-05-09 11:06:32.000","AlarmType":1}';
	$js = json_decode($str,true);
	echo '<pre>';
	print_r($js);
	die;

	//用户解约
//	$data = [
//		'Token' =>'Surrender',//'Surrender',FeeDeduction、Query
//		'Phone'=>'13044221462',
//		'CardNo'=>'6223228802600499',
//	];
	
	//扣费
//	$data = [
//		'Token' =>'FeeDeduction',//'Surrender',FeeDeduction、Query
//		'Phone'=>'13044221462',//用户唯一编号；
//		'CardNo'=>'6223228802600499',//银行卡号
//		'OrderSn'=>'G99198201804171338206171897368',//订单编号
//	];
	
	
	//查询
//	$data = [
//		'Token' =>'Query',//'Surrender',FeeDeduction、Query
//		'OldSeq'=>'20180419123732902762',//发起方流水；
//		'OldDate'=>time(),//发起方日期  时间搓；
//	];

	
	//单笔扣费冲销(只能冲当天)
//	$data = [
//		'Token' =>'WriteOff',//单笔扣费冲销（只能冲当天）
//		'PlatDate'=>'20180619',//原银行日期	 8
//		'BkOldSeq'=>'2072002',//原银行流水	 12
//	];
	
	
	//单笔扣费对账文件获取
//	$data = [
//		'Token' =>'Reconciliation',//单笔扣费对账文件获取
//		'OldDate'=>1523808000,//实际交易日期(不能是当天)	 8  传入时间搓；
//	];
//	
	$url = 'http://shop.gogo198.cn/payment/agro/Fagro.php';
	$result = $curlpost->post($url,$data);
	echo '<pre>';
	$json = json_decode($result->response,TRUE);
	print_r($json);



//	$file = file_get_contents('./log/Result.txt');
//	$res = Analysis($file);
//	echo '<pre>';
//	print_r($res);


//	$order_id='G99198' . '101570223660' . date('Ymd',time()) . mt_rand(1111,9999);
//	echo $order_id;
//	echo '<br>';
//	echo strlen($order_id);


//	$str = '将字符串转换为字符长度';
//	echo strlen($str);
//	echo '<br>';
//	
//	$res = getBaoWen($str,8);
//	print_r($res);

	/**
	 * 2018-04-18
	 * 计算报头+报文长度
	 */
	function getBaoWen($str,$len)
	{
		$str = strlen($str);//将字符串转换为字符长度；
		$strLen = strlen($str);//计算长度,ksdfj
		$lens = $len;
		$left = '';//需拼接变量；
		$ji = ($lens - $strLen);//8-3=5  
		if($strLen == $lens)//如果计算出来的字符串长度与需要的长度一致，直接返回； 
		{
			return $str;
		} else if( $strLen < ($lens+1))
		{
			for( $i=1; $i<=$ji; $i++ )
			{
				$left .= '0';
			}
			return $left.$str;
		}
	}
	
	

	/**
	 * 解析返回的数据；
	 * 0=8、1=6、2=8、3=12、4=8、5=20、6=6、7=100
	 */
	function Analysis($str)
	{
		$i = 0;//截取字符串开始位置；
		$start = 0;//开始截取的位置；
		$lenght = 8;//截取的长度；
		$arrayData = [];//截取的字符串保存到数组中；
		for($i ;$i<=7;$i++) {
			switch($i)
			{
				case 0:
					$lenght = 8;
					$arrayData['__GDTA_ITEMDATA_LENGTH'] = substr($str,$start,$lenght);
				break;
				case 1:
					$start = $lenght;
					$lenght = 6;
					$arrayData['__GDTA_SVCNAME'] = substr($str,$start,$lenght);
				break;
				case 2:
					$start += $lenght;
					$lenght = 8;
					$arrayData['BkPlatDate'] = substr($str,$start,$lenght);
				break;
				case 3:
					$start += $lenght;
					$lenght = 12;
					$arrayData['BkSeqNo'] = ltrim(substr($str,$start,$lenght),' ');
				break;
				case 4:
					$start += $lenght;
					$lenght = 8;
					$arrayData['BkOthDate'] = substr($str,$start,$lenght);
				break;
				case 5:
					$start += $lenght;
					$lenght = 20;
					$arrayData['BkOthSeq'] = substr($str,$start,$lenght);
				break;
				case 6:
					$start += $lenght;
					$lenght = 6;
					$arrayData['__ERR_RET'] = substr($str,$start,$lenght);
				break;
				case 7:
					$start += $lenght;
					$lenght =100;
					$arrayData['__ERR_MSG'] = trim(substr($str,$start,$lenght),' ');
				break;
			}
		}
		return $arrayData;
	}
	
	//获取字符串长度；
//	function getStrlen($res)
//	{
//		return strlen($res);
//	}
	
	function charsetToGB($mixed,$Unicode) 
	{
	    if (is_array($mixed)) {
	        foreach ($mixed as $k => $v) {
	            if (is_array($v)) {
	                $mixed[$k] = charsetToGBK($v);
	            } else {
	                $encode = mb_detect_encoding($v, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
	                if ($encode == 'UTF-8') {
	                    $mixed[$k] = iconv('UTF-8', $Unicode, $v);
	                }
	            }
	        }
	    } else {
	        $encode = mb_detect_encoding($mixed, array('ASCII', 'UTF-8', 'GB2312', 'GBK', 'BIG5'));
	        if ($encode == 'UTF-8') {
	            $mixed = iconv('UTF-8', $Unicode, $mixed);
	        }
	    }
	    return $mixed;
	}
	
	//查询当前字符串的长度，拼接后返回新的字符串
	function getStrlen($strs)
	{	
		$str = charsetToGB($strs,'GB2312');
		$strlen = strlen($str);
		if($strlen < 99) 
		{
			$leng = 98 - $strlen;
			for($i=1 ; $i <= $leng ;$i++)
			{
				$str += ' ';
			}
		}
		return $str;
	}
	
?>