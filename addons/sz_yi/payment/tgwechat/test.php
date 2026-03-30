<?php
	/*header('Content-Type:text/html;charset=utf-8');
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
	global $_GPC;*/

	//  http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/test.php

	/*$postdata['tid'] 	= 'GG20180817104814244868';
    $postdata['fee'] 	= '31.50';
    $postdata['title']  = '加拿大Christie';
    $postdata['token'] 	= 'wechat';
    
    //钜铭
    /*$postdata['openid']  = 'ov3-btyLPTGwIduBvEXdiGSnpUK4';
    $postdata['account'] = '403510118203';
    $postdata['key'] 	 = '8724d2f3f59f303866a7dacf70dfa8f7';*/
    
    //喜柏
   /* $postdata['openid']  = 'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U';
    $postdata['account'] = '101570223660';
    //$postdata['key'] 	 = '85be68fc58b1badfc7580bd988ed4f54';
    $postdata['key'] 	 = 'b4f16b4526b046c580e363fcfcd07c82';*/


    /*$postdata = [
        'tid' => 'GG20190829111323556319',
        'opid' => 'ov3-btyLPTGwIduBvEXdiGSnpUK4',
        'fee' => '1.00',
        'title' => 'Gos001[跨境直邮]schaebens奢华黄金面膜10ml',
        'acc' => '101540254006',
        'ky' => 'f8ee27742a68418da52de4fca59b999e',
        'uniacid' => 3,
        'to' => 'wechat'
    ];*/



    $postdata = [
        'tid' => 'GG20190829111323556322'.mt_rand(0,9).mt_rand(0,9),
        'opid' => 'ov3-btyLPTGwIduBvEXdiGSnpUK4',
        'fee' => '1.00',
        'title' => 'Gos001[跨境直邮]schaebens奢华黄金面膜10ml',
        'uniacid' => 3,
    ];

    $tgw = json_encode($postdata);
    $tgw = json_decode($tgw);// 请求支付；

    /*echo $tgw->tid; // 修改更新  请求跳转

    die;


    //请求地址
//    $url = 'http://shop.gogo198.cn/payment/sz_yi/Payments.php';
    /*$url = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Paymentess.php';
	//转换Json格请求
	$jsonStr = json_encode($postdata);
	//请求数据
	$res = JsonPost($url,$jsonStr);
	//打印数据
	//print_r($res);
	//ssssss
	print_r($result = json_decode($res,TRUE));

	
	function JsonPost($url,$post_data) {
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
	}*/

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script language="javascript" src="./js/jquery-1.11.1.min.js"></script>
    <title>支付宝支付</title>
</head>
<body>
    <!--form action="" id="wecpay" method="post">
        <input type="hidden" name="tid" id="tid" value="" />
        <input type="hidden" name="opid" id="opid" value="" />
        <input type="hidden" name="fee" id="fee" value="" />
        <input type="hidden" name="title" id="title" value="" />
<!--        <input type="hidden" name="acc" id="acc" value="" />-->
<!--        <input type="hidden" name="ky" id="ky" value="" />-->
        <!--input type="hidden" name="uniacid" id="uniacid" value="" />
<!--        <input type="hidden" name="to" id="to" value="" />-->
    <!--/form-->

    <form action="" method="get" id="aliwp"></form>

    <script>
        $.ajax({
            url:"http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Alipay.php",
            type:'POST',
            dataType:'json',
            data:{
                'tid':"<?php echo $tgw->tid;?>",
                'opid':"<?php echo $tgw->opid;?>",
                'title':"<?php echo $tgw->title;?>",
                'fee':"<?php echo $tgw->fee;?>",
                'uniacid':"<?php echo $tgw->uniacid;?>",
                'returnUrl':"https://shop.gogo198.cn/app/index.php?i=3&c=entry&p=easy_deliver_paysuccess&do=order&m=sz_yi&oid="+"<?php echo $tgw->tid;?>",
            },
            success:function(res) {
                if(res.code !=1) {
                    window.location.href = res.returnUrl;
                    return false;
                }
                urls = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Alipays.php';
                var inp = '<input type="hidden" name="pay_url" value="'+res.pay_url+'">';
                var ret = '<input type="hidden" name="returnUrl" value="'+res.returnUrl+'">';
                $('#aliwp').append(inp);
                $('#aliwp').append(ret);
                $('#aliwp').attr('action',urls);
                $('#aliwp').submit();
            }
        });

        // $('#wecpay').attr('action', 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Wechat.php')
        /*$('#wecpay').attr('action', 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Alipays.php')
        $('#tid').val("<?php echo $tgw->tid;?>");
        $('#opid').val("<?php echo $tgw->opid;?>");
        $('#title').val("<?php echo $tgw->title;?>");
        $('#fee').val(<?php echo $tgw->fee;?>);
        $('#uniacid').val(<?php echo $tgw->uniacid;?>)
        $('#wecpay').submit();*/
    </script>
</body>
</html>


