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


    function auth(){
        global $_W;
        global $_GPC;
        $UserAuth=pdo_get("parking_authorize",array("openid"=>'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',"auth_status"=>1));
        if(!empty($UserAuth)){
            $UserAuth['auth_type']=unserialize($UserAuth['auth_type']);
        }
        return $UserAuth;
    }

	/*$ordersn = 'G99198101570223660201804278917';
	$filed = 'a.uniacid,a.pay_account,a.total,a.body,a.user_id,b.starttime,b.endtime,b.duration ';
	$find = array(':ordersn' => $ordersn, ':pay_status' => 0,':paystatus' => 2);
	$OrdData = pdo_fetch("SELECT ".$filed." FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn AND a.pay_status = :pay_status OR a.pay_status = :paystatus LIMIT 1",$find);

	echo '<pre>';
	print_r($OrdData);*/

	/*$key = 'APP_ID';//缓存设置；
	if(!cache_load($key)) {
		$config = [
			'mchid'=>'12314131312',
			'openkey'=>'abddwer12312'
		];
		cache_write($key, $config);
	} else {
		$res = cache_load($key);
		echo '<pre>';
		print_r($res);

		cache_delete($key);
	}*/

//	$Result['Result'] = 00000;
//	$Result['Message'] = '成功完成';
//
//	if($Result['Result'] == '00000' && $Result['Message'] == '成功完成'){
//		echo 'yes';
//	} else {
//		echo 'no';
//	}
//
//	die;


/*
 *  模拟订单写入；foll_order,parking_order,
 *
 *
 */


/**
 *  未绑定车牌，提示绑定车牌；
 *  已绑定车牌的，提示授权
 */

/*echo '<a href="https://payapp.weixin.qq.com/vehicle/plat/indextemplate?appid=wxdeeda2690614a91f&sub_appid=wx01af4897eca4527e&mch_id=1228108702&sub_mch_id=1499711372&nonce_str=gVYFawqGjkB3NopEbJuFBLFJFSsBUgz7&plate_number=粤X12356&sign_type=HMAC-SHA256&trade_scene=PARKING&sub_openid=oR-IB0t4Yc9zmV-K-_5NRB-u5k4U&sign=6FDB003E57E11714A8F66E6C4D02E66AAA4586DB9C981CC77C8D882014786CCB#wechat_redirect">点击跳转了</a>';

die;*/



    $CarNo = trim($_POST['CarNo']);
    $Openid = trim($_POST['Openid']);
    //echo $CarNo.'<>'.$Openid;

    echo '<h2>用户信息：'.$CarNo.'<>'.$Openid.'</h2>';

    $status = '1101';
    if($_POST['Token'] == 'OK') {

        $CarNo = trim($_POST['CarNo']);
        $Openid = trim($_POST['Openid']);

        $data = [
            'Token' => 'CheckCarNoSign',
            'inType' => 'PARKING',
            'CarNo'  => $CarNo,
            'openid' => $Openid,
        ];

        $url = 'http://shop.gogo198.cn/payment/Frx/testFrx.php';
        //$url = 'http://shop.gogo198.cn/payment/Frx/upload.php';
        //下载对账文件
        //$url = 'http://shop.gogo198.cn/payment/Frx/upload.php';
        $result = $curlpost->post($url, $data);
        //echo '<pre>';
        //print_r($result->response);
        $json = json_decode($result->response, TRUE);
        //$path = $json['msg']['path'];
        //file_put_contents('./log/MytestRefunds.txt', print_r($json,TRUE),FILE_APPEND);
        //print_r($json);

        $status = isset($json['userState']) ? $json['userState'] : '1101';

          print_r($json);
        // NORMAL：正常用户、PAUSED：已暂停车主服务、OVERDUE: 已开通但欠费、UNAUTHORIZED: 未开通

    }


    $type=[];
    $type['NORMAL'] = '正常用户';
    $type['PAUSED'] = '已暂停车主服务';
    $type['OVERDUE'] = '已开通但欠费';
    $type['UNAUTHORIZED'] = '未开通';
    $type['1101'] = '等待查询';

    $userState = $type[$status];

    //
    // // 查询签约状态；
    // $data=[
    //     'Token' =>'CheckCarNoSign',
    //     'inType'=>'PARKING',
    //     'CarNo' =>'粤E31018',
    //     'openid'=>'oR-IB0g3w57me3fDRAT2nSZG08VY',
    // ];
    //
    // $url = 'http://shop.gogo198.cn/payment/Frx/testFrx.php';
    // // 提交查询
    // $result = $curlpost->post($url,$data);
    //
    // $res = json_decode($result->response,true);
    //
    // echo '<pre>';
    // print_r($res);
    // die;


	/*if(isset($_GET['type'])) {

		/*switch($_GET['type']) {
			case '1':
				//车辆入场；
				$data = [
					'Token'  =>'inPark', //停车类型；
					'inType' =>'PARKING',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
					'orderSn'=>'G99198101570223660201808075601',//订单编号；
				];
			break;

			case '2':
				//扣费；
				$data = [
					'Token'  =>'Fee', //停车类型；
					'inType' =>'PARKING',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
					'orderSn'=>'G99198101570223660201808075601',//订单编号；
				];
			break;

			case '3':
				//订单查询
				$data = [
					'Token' =>'Query', //停车类型；
					'inType'=>'streamNo',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
					'orderSn'=>'G99198101570223660201806223940',//订单编号；
				];
			break;

			case '4':
				//对账单下载；
				$data = [
					'Token'   =>  'loadBill', //停车类型；
					'loaDate' => '1540796377',//查询日期；
					'uniacid' => 14,//公众号ID
				];
			break;

			case '5':
				//测试数据
				$data = [
					'Token' =>'Test', //停车类型；
					'inType'=>'PARKING SPACE',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
					'orderSn'=>'G99198101570223660201804285166',//订单编号；
				];
			break;

			case '6':
				//测试数据	Aesdecrypt
				$data = [
					'Token' =>'Aesdecrypt', //停车类型；
					//'inType'=>'PARKING SPACE',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
					//'orderSn'=>'G99198101570223660201804278917',//订单编号；
				];
			break;

			case '7':
				//车辆入场；
				$data = [
					'Token' =>'inPark', //停车类型；
					'inType'=>'PARKING SPACE',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
					'orderSn'=>'G99198101570223660201806074988',//订单编号；
				];
			break;

			case '8':
				//扣费；
				$data = [
					'Token' =>'Fee', //停车类型；
					'inType'=>'PARKING SPACE',//场景ID； 停车场：PARKING   停车位：PARKING SPACE
					'orderSn'=>'G99198101570223660201806074988',//订单编号；
				];
			break;
			case '9':
				//退款
				$data = [
					'Token' =>'Refund', //退款类型
					'RefundMoney'=>4.5,
					'orderSn'=>'G99198101570223660201808215567',//订单编号；
				];
			break;

			case '10':
				$data=[
					'Token' =>'CheckCarNoSign',
					'inType'=>'PARKING',
					'CarNo' =>'粤YGB998',
					'openid'=>'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',
				];
			break;
		}

        $data=[
            'Token' =>'CheckCarNoSign',
            'inType'=>'PARKING',
            'CarNo' =>'粤X12355',
            'openid'=>'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',
        ];

		$url = 'http://shop.gogo198.cn/payment/Frx/Frx.php';
		//$url = 'http://shop.gogo198.cn/payment/Frx/upload.php';
		//下载对账文件
		//$url = 'http://shop.gogo198.cn/payment/Frx/upload.php';
		$result = $curlpost->post($url,$data);
		echo '<pre>';
		//print_r($result->response);
		$json = json_decode($result->response,TRUE);
		//$path = $json['msg']['path'];
		//file_put_contents('./log/MytestRefunds.txt', print_r($json,TRUE),FILE_APPEND);
		print_r($json);
		/*if($path){
			header('Location:'.$path);
		}
		//echo 'END';

	} else {
		echo 'place type';
	}*/

	/*appid		=	wxdeeda2690614a91f&
	sub_appid	=	wx01af4897eca4527e&
	mch_id		=	1228108702&
	sub_mch_id	=	1499711372&
	nonce_str	=	dZ37yi6rRLSImW9SMFhOiXn9r2ZiK8xv&
	plate_number=	粤YGB粤YGB998&
	sign_type	=	HMAC-SHA256&
	trade_scene	=	PARKING&
	sub_openid	=	oR-IB0t4Yc9zmV-K-_5NRB-u5k4U&
	sign		=	7A7FE67F4C550FE589BA6CBEC25363FC7302FD00801CC7FF931B7DF2610C6BE8
	#wechat_redirect*/



/**
 * 支付成功回调信息；
 * amt=1&	退款金额（以分为单位，没有小数点）
 * ordernum=439457294502068224&	原订单号
 * requesttype=1&	1支付5退款6撤销
 * sign=6fdd789c4022177bde4a5c697ec0f69b&	签名字符串
 * streamNo=G99198101570223660201804278914&	开发者流水号，确认同一门店内唯一
 * trade_time=20180427160623&	交易时间
 * tradestate=1	订单状态: 1支付成功7退款完成
 *
 * amt=1&ordernum=439457294502068224&requesttype=1&sign=6fdd789c4022177bde4a5c697ec0f69b&streamNo=G99198101570223660201804278914&trade_time=20180427160623&tradestate=1
 * parse_str() 可以解析；
 */

//	$str = "amt=1&ordernum=439457294502068224&requesttype=1&sign=6fdd789c4022177bde4a5c697ec0f69b&streamNo=G99198101570223660201804278914&trade_time=20180427160623&tradestate=1";
//	parse_str($str,$data);
//	echo '<pre>';
//	print_r($data);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>车主签约查询</title>
    <style>
        #box{
            width: 50%;
            margin: 15px auto;
        }
        #box input{
            width: 200px;
            height: 26px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

    <div id="box">
        <form action="http://shop.gogo198.cn/payment/Frx/mytest.php" method="POST">
            <label for="">车牌号</label>
            <input type="text" name="CarNo" value="粤E31018" placeholder="请输入车牌号">
            <br>
            <label for="">用户ID</label>
            <input type="text" name="Openid" value="oR-IB0g3w57me3fDRAT2nSZG08VY" placeholder="请输入用户Openid">
            <input type="hidden" name="Token" value="OK">
            <br>
            <button type="submit" value="">查 询</button>
            <button type="reset" value="">重 置</button>
            <p>
                <label style="color: red;" for="">NORMAL：正常用户、PAUSED：已暂停车主服务、OVERDUE: 已开通但欠费、UNAUTHORIZED: 未开通</label>
                <br>
                状态：<?php echo $userState;?>
            </p>
        </form>
    </div>

</body>
</html>
