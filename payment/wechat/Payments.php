<?php

require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
//加载发送消息函数  diysend
load()->func('diysend');
global $_W;
global $_GPC;


// 接收数据

if ($_W['ispost']) {

    $inputs  = file_get_contents("php://input");
    $inputsd = json_decode($inputs,true);
    if(empty($inputsd)) {
        exit(json_encode(['code'=>0,'msg'=>'参数不能为空，请检查！']));
    }

    if(!isset($inputsd['token'])) {
        exit(json_encode(['code'=>0,'msg'=>'请填写token参数！']));
    }

    // token = wxScode、Alipays
    $token = ['wxScode','Alipays','Refund','LoadBills'];// token
    if(!in_array($inputsd['token'],$token)) {
        exit(json_encode(['code'=>0,'msg'=>'您的token参数有误，请检查！']));
    }

    // 分流数据
    switch($inputsd['token'])
    {
        case 'wxScode':

            $inData = ['payMoney','ordersn','body','returnUrl','openid','token'];
            foreach($inputsd as $k=>$v) {
                if(!in_array($k,$inData)) {
                    exit(json_encode(['code'=>0,'msg'=>'您的参数有误，请检查！']));
                }
            }
            // 请求支付
            $response = wxScode($inputsd);
            if($response['status'] == '100') {
                exit(json_encode(['code'=>1,'msg'=>$response['message'],'pay_url'=>$response['pay_url'],'pay_info'=>$response['pay_info']]));
            } else {
                exit(json_encode(['code'=>0,'msg'=>$response['message'],'pay_url'=>'']));
            }
            break;

        case 'Alipays':

            $inData = ['payMoney','ordersn','body','returnUrl','openid','token'];
            foreach($inputsd as $k=>$v) {
                if(!in_array($k,$inData)) {
                    exit(json_encode(['code'=>0,'msg'=>'您的参数有误，请检查！']));
                }
            }

            $response = Alipays($inputsd);
            if($response['status'] == '100') {
                exit(json_encode(['code'=>1,'msg'=>$response['message'],'pay_url'=>$response['pay_url'],'pay_info'=>$response['pay_info']]));
            } else {
                exit(json_encode(['code'=>0,'msg'=>$response['message'],'pay_url'=>'']));
            }
            break;

        case "Refund":// 退款操作

            $inData = ['upOrderId','refundMoney','token'];
            foreach($inputsd as $k=>$v) {
                if(!in_array($k,$inData)) {
                    exit(json_encode(['code'=>0,'msg'=>'您的参数有误，请检查！']));
                }
            }
            // 退款请求
            $response = Refund($inputsd);
            if($response['status'] == 100 ) {
                $Refu['pStatus'] = 4;
                pdo_update('door_orders',$Refu,array('upOrderId' => $response['upOrderId']));
                exit(json_encode(['code'=>1,'msg'=>$response['message']]));

            } else {
                exit(json_encode(['code'=>0,'msg'=>$response['message']]));
            }
            break;

            // 对账文件下载
        case "LoadBills":
            $response = LoadBills();
            if($response) {// 下载成功
                exit(json_encode(['code'=>1,'msg'=>'download ok']));
            } else {
                exit(json_encode(['code'=>0,'msg'=>$response]));
            }
            break;
    }

} else {

    return json_encode(['code'=>0,'msg'=>'请使用POST方法请求']);
}


// 微信支付
function wxScode($inputs)
{
    $account = '101540254006';
    $key     = 'f8ee27742a68418da52de4fca59b999e';

    $doors = [];// 门禁支付订单表
    $package = array();
    $package['account']    = $account;
    $doors['payMoney']  = $package['payMoney']   = number_format($inputs['payMoney'],2);
    $doors['ordersn']   = $package['lowOrderId'] = $inputs['ordersn'];
    $doors['body']      = $package['body'] 	     = $inputs['body'];
    // foll/application/payments/payments.php @ Notifys
    $doors['notifyUrl'] = $package['notifyUrl']  = 'http://shop.gogo198.cn/foll/public/index.php?s=Payments/Notifys';//后台回调地址
    $doors['returnUrl'] = $package['returnUrl']  = $inputs['returnUrl'];
    $doors['openid']    = $package['openId'] 	 = $inputs['openid'];
    $doors['otime']     = time();// 订单生成时间
    $doors['pStatus']   = 2;// 支付状态

    pdo_insert('door_orders',$doors);

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
    $url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
    //数据请求地址，post形式传输
    $response = ihttp_posts($url,$data);
    // json数据
    $response = json_decode($response,true);
    return $response;
}


// 支付宝支付
function Alipays($inputs)
{
    $account = '101540254006';
    $key     = 'f8ee27742a68418da52de4fca59b999e';

    $package = array();
    $package['account'] 	=   $account;
    $package['payMoney'] 	= 	number_format($inputs['payMoney'],2);
    $package['lowOrderId']  = 	$inputs['ordersn'];
    $package['body'] 		= 	$inputs['body'];
    $package['notifyUrl']	=   'http://shop.gogo198.cn/foll/public/index.php?s=Payments/Notifys';
    $package['payType'] 	= 	'1';//
    $package['returnUrl']   =   $inputs['returnUrl'] ? $inputs['returnUrl'] : '';
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
    $url = 'https://ipay.833006.net/tgPosp/services/payApi/unifiedorder';
    //数据请求地址，post形式传输
    $response = ihttp_posts($url,$data);
    //解析json数据
    $response = json_decode($response,TRUE);
    //返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
    return $response;

}


// 订单退款
function Refund($params)
{
    $account = '101540254006';
    $key     = 'f8ee27742a68418da52de4fca59b999e';
    $package = array();
    $package['account']     = $account;
    $package['upOrderId']   = $params['upOrderId'];
    $package['refundMoney'] = number_format($params['refundMoney'],2);
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
    $url = 'https://ipay.833006.net/tgPosp/services/payApi/refund';
    //$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/refund'; //测试地址
    //数据请求地址，post形式传输
    $response = ihttp_posts($url,$data);
    //解析json数据
    $response = json_decode($response,TRUE);

    file_put_contents('./log/Refunde.txt', print_r($response,TRUE),FILE_APPEND);
    //直接返回支付URL地址
    //return $response->pay_url;
    //返回数组
    return $response;
}


function LoadBills($times = '')
{
    try {

        $appId	=	'tgkj22493580';
        $key	=	'cef9ea4f0ed2cf9352ed6c23d7734345';
        //昨天通莞数据
        $time  =  !empty($times)? date('Ymd',$times):date('Ymd',strtotime('-1 day'));
        //数据组装
        $data   = [
            'api' 		=>	"statement/trade",
            'appId' 	=>	$appId,//tgkj22493580
            'fromDate'	=>	$time,
            'toDate'	=>	$time,
            'merId'		=>	"617112200019682",//"617112400019774"
            'method'	=>	"checkTradeDetail",
        ];

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
        $url = "http://erp.yltg.com.cn/erpApi/?".$strs;
        $responses = ihttp_gets($url);
        //解析json数据
        $response = json_decode($responses, true);
        //文件路径
        $path = '../../crontab/gogo/tg'.$time.'.txt';
        //$path = "/www/web/default/crontab/gogo/tg{$time}.txt";
        if(!file_exists($path)) {
            $dataStr = '';
            if(!empty($response['trade'])) {
                foreach($response['trade'] as $key=>$val) {
                    //支付成功或支付失败的数据   订单状态：0支付成功,1支付失败,2已撤销,4待支付，5已转入退款，6已转入部分退款,
                    if(($val['state'] == 0) || ($val['state'] == 5) || ($val['state'] == 6) ) {//支付成功数据
                        //$datastr .= $val['pay_time'].','.$val['pay_money'].','.$val['fcp_id'].','.$val['order_id']."\r\n";
                        //订单创建时间，支付通道编号，支付状态，支付时间，支付金额，上游订单号，下游订单号
                        //$dataStr .= $val['create_time'].','.$val['channel_id'].','.$val['state'].','.$val['pay_time'].','.($val['pay_money']/100).','.$val['order_id'].','.$val['low_order_id'].','.($val['refund_money']/100)."\r\n";
                        $dataStr .= $val['state'].','.$val['pay_time'].','.number_format(($val['pay_money']/100),2).','.$val['order_id'].','.$val['low_order_id'].','.number_format(($val['refund_money']/100),2)."\r\n";
                    }
                }
            }

            //写入文件
            file_put_contents($path,print_r($dataStr,TRUE),FILE_APPEND);

            return true;

        } else {

            return false;
            //echo json_encode(['code'=>1,'msg'=>'download ok!']);die;
        }
    }catch (\Exception $e) {
        return $e->getMessage();
    }
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

/**
 * @数据请求提交POST json
 * @$url:请求地址
 * @post_data:请求数据
 */
function ihttp_posts($url,$post_data) {
    //初始化
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0); // 跳过证书检查
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);  // 从证书中检查SSL加密算法是否存在
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
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