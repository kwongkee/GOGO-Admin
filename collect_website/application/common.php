<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use think\Db;
use GuzzleHttp\Client;
use think\Log;
// 应用公共文件

/**
 * @param $url
 * @param $data
 * @param array $head
 * @param int $method 0=get,1=post
 * @return bool|string
 */
function httpRequest($url,$data,$head=[],$method=0)
{
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    if($method == 1){
        curl_setopt($ch, CURLOPT_POST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }
    if(!empty($head)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        curl_setopt($ch, CURLOPT_HEADER, 0);//返回response头部信息
    }
    $output=curl_exec($ch);
    curl_close($ch);
    return $output;
}
function httpRequest_wx($url, $data = null) {
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        CURLOPT_ENCODING => 'gzip, deflate', // 处理压缩响应
    ]);

    if (!empty($data)) {
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ]
        ]);
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // 如果请求失败，返回空字符串而不是错误信息（避免编码问题）
    if ($httpCode !== 200) {
        return "";
    }

    return $response;
}

function wechat_httpRequest($post){
    $ch2=curl_init();
    curl_setopt($ch2,CURLOPT_URL,'https://shop.gogo198.cn/api/sendwechattemplatenotice.php');
    curl_setopt($ch2,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch2,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch2,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch2,CURLOPT_POST,1);
    curl_setopt($ch2,CURLOPT_POSTFIELDS,$post);
    curl_setopt($ch2, CURLOPT_HTTPHEADER,[]);
    $output=curl_exec($ch2);
    curl_close($ch2);

    return $output;
}

//星赋创达（BuckyDrop）的接口请求
function httpRequest3($url,$data)
{
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output=curl_exec($ch);
    curl_close($ch);

//    Log::write(json_encode($output,true));
//    Log::info(json_encode($output,true));
    return $output;
}

//聚梦短信通知
function httpRequest2($url,$data,$head=[])
{
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$head);
    $output=curl_exec($ch);
    curl_close($ch);
    return $output;
}

//聚梦状态获取

function http_get($url,$headers){
//    $serverurl = "http://api.pfcexpress.com:81/";
//    $apikey = "aeae3d3c-bcaa-4442-8849-ec61bbf8def4125730";
//    $headers=array('Authorization: '.'Bearer '.$apikey,'Content-type: application/json');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $json = curl_exec($ch);
    curl_close($ch);
    $result=json_decode($json, true);

    return $result;
}

function dd($data){
    print_r($data);die;
}

function Url($param)
{
    //    $_SERVER['SERVER_NAME']
    return "https://admin.gogo198.cn/collect_website/public/?s=".$param;
}

// 发送微信通知
function sendWechatMsg($data)
{
    $url = 'https://shop.gogo198.cn/api/sendwechattemplatenotice.php';

//    $client = new \GuzzleHttp\Client();
    try {
        //正常请求
//        $promise = $client->request('post', $url, ["headers" => ['Content-Type' => 'application/json'],'body'=>$data]);
        return httpRequest($url,$data,['Content-Type' => 'application/json'],1);
    } catch (\Exception $exception) {
        //捕获异常 输出错误
        return $this->error($exception->getMessage());
    }
}

function Redirects($param)
{
//    $_SERVER['SERVER_NAME']
    $url="https://admin.gogo198.cn/collect_website/public/?s=".$param;
    header('Location: ' . $url);
    exit();
}

//验证手机格式
function verifCode($tel)
{
    if(preg_match("/^1[34578]\d{9}$/",$tel)){
        return true;
    }
    return false;
}

/**
 * 发送阿里云邮箱  2020-03-24  发送停车对账单使用阿里云企业邮箱发送；
 * @param string $tomail 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @author static7 <static7@qq.com>
 */
function send_mailAli($tomail, $name, $subject = '', $body = '', $attachment = null) {
    $mail = new PHPMailer();           //实例化PHPMailer对象
//  $mail = $this->PHPMailer
//	$mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';          	 		//设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    		// 设定使用SMTP服务
    $mail->SMTPDebug = 0;               		// SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             		// 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          		// 使用安全协议
//    $mail->SMTPSecure = 'tls';          		// 使用安全协议
    $mail->Host = "smtp.qiye.aliyun.com"; 				// SMTP 服务器
    $mail->Port = 465;                 	    // SMTP服务器的端口号
//    $mail->Port = 25;                 			// SMTP服务器的端口号
    $mail->Username = "mail@gogo198.net";    	// SMTP服务器用户名    805929498@qq.com
    $mail->Password = "Pp86329911";//"txrosoelfjiybcej";     	// SMTP服务器密码     auelorsctusbbfgh
    /**
     * txrosoelfjiybcej(新)   dbflwnifoxmobedd(旧)
     */
    $mail->SetFrom('mail@gogo198.net', 'mail@gogo198.net');
    $replyEmail = '';                   		//留空则为发件人EMAIL
    $replyName = '';                    		//回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($tomail, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}


function cklein_mailAli($tomail, $name, $subject = '', $body = '', $attachment = null) {
    $dir = $_SERVER['DOCUMENT_ROOT'].'/collect_website/vendor/phpmailer/phpmailer/src';
    require_once($dir."/PHPMailer.php");
    require_once($dir."/Exception.php");
    require_once($dir."/SMTP.php");
    $mail = new PHPMailer();           //实例化PHPMailer对象
//  $mail = $this->PHPMailer
//	$mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';          	 		//设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    		// 设定使用SMTP服务
    $mail->SMTPDebug = 0;               		// SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             		// 启用 SMTP 验证功能
    $mail->SMTPSecure = 'ssl';          		// 使用安全协议
//    $mail->SMTPSecure = 'tls';          		// 使用安全协议
    $mail->Host = "smtp.qiye.aliyun.com"; 				// SMTP 服务器
    $mail->Port = 465;                 	    // SMTP服务器的端口号
//    $mail->Port = 25;                 			// SMTP服务器的端口号
    $mail->Username = "go@gogo198.net";    	// SMTP服务器用户名    805929498@qq.com  cklein@gogo198.net
    $mail->Password = "@Pp86329911";//"txrosoelfjiybcej";     	// SMTP服务器密码     auelorsctusbbfgh  Lishiqi1993
    //$mail->ConfirmReadingTo = '597831209@qq.com';  //询问是否发送回执
    /**
     * txrosoelfjiybcej(新)   dbflwnifoxmobedd(旧)
     */
    $mail->SetFrom('go@gogo198.net', 'go@gogo198.net');
    $replyEmail = '';                   		//留空则为发件人EMAIL
    $replyName = '';                    		//回复名称（留空则为发件人名称）
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($tomail, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}

function show_msg($msg){
    echo '<!DOCTYPE html>
	<html>
	<head lang="en">
	    <meta charset="UTF-8">
	    <title></title>
	    <script src="js/jquery-1.8.3.min.js"></script>
	    <style>
	        *{padding: 0; margin: 0}
	        .box{
	            position: fixed;
	            width: 100%;
	            height: 100%;
	            background: rgba(0,0,0,0.2);
	            display: none;
	        }
	        .box1{
	            width: 500px;
	            height: 500px;
	            position: fixed;left: 50%; top: 25%;
	            margin-left: -250px;
	            border: 1px solid #000000;
	        }
	    </style>
	    <script>

	    </script>
	</head>
	<body>
	    <div class="box">
	        <div class="box1">
	            <a href="javascript:;" onclick="jQuery(".box").hide()" class="close">关闭</a>
	    </div>
	</div>
	<a href="javascript:;" onclick="jQuery(".box").show()" class="show">'.$msg.'</a>
	</body>
	</html>  ';
//	echo '<script>alert("'.$msg.'")</script>';
}

function isMobiles()
{
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    }
    if (isset ($_SERVER['HTTP_VIA']))
    {
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        }
    }
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        }
    }
    return false;
}

/*
 * 获取wxtoken
 */
function RequestAccessToken ( $uniacid)
{
    $uniacid = $uniacid?$uniacid:14;
    $key = 'accesstoken:'.$uniacid;

    $coreCache = Db::name('core_cache')->where('key',$key)->find();

    if(!empty($coreCache)){
        $coreCache = unserialize($coreCache['value']);
        if($coreCache['expire']>time()){
            return $coreCache['token'];
        }
    }

    $account = Db::name("account_wechats")->where('uniacid', $uniacid)->find();
    $ASSESS_TOKEN = file_get_contents('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $account['key'] . '&secret=' . $account['secret']);
    $ASSESS_TOKEN = json_decode($ASSESS_TOKEN, true);
    $value = serialize([
        'token'=>$ASSESS_TOKEN['access_token'],
        'expire'=>(time()+$ASSESS_TOKEN['expires_in'])-200
    ]);
    if(empty($coreCache)){
        Db::name('core_cache')->insert(['key'=>$key,'value'=>$value]);
    }else{
        Db::name('core_cache')->where('key',$key)->update(['value'=>$value]);
    }

    return $ASSESS_TOKEN['access_token'];

}

//随机数
function randomkeys($length) {
    $returnStr='';
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
    for($i = 0; $i < $length; $i ++) {
        $returnStr .= $pattern {mt_rand ( 0, 61 )}; //生成php随机数
    }
    return $returnStr;
}

//生成订单编号
function generateOrderSn($fix) {
    @date_default_timezone_set('PRC');
    //订购日期
    $order_id_main = date('YmdHis') . rand(10000000,99999999);
    //订单号码主体长度
    $order_id_len = strlen($order_id_main);
    $order_id_sum = 0;
    for($i=0; $i<$order_id_len; $i++){
        $order_id_sum += (int)(substr($order_id_main,$i,1));
    }
    //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
    $order_id = $fix.$order_id_main . str_pad((100 - $order_id_sum % 100) % 100,2,'0',STR_PAD_LEFT);
    return $order_id;
}

//生成二维码
function generate_code($name,$url,$folder){
    //链接生成二维码
    $errorCorrectionLevel = 'L';//错误等级，忽略
    $matrixPointSize = 4;
    require_once $_SERVER['DOCUMENT_ROOT'].'/collect_website/extend/lib/phpqrcode.php';
    $path = $folder; //储存的地方
    if (!is_dir($path)) {
        mkdirs($path); //创建文件夹
    }
    $infourl = $url;
    $filename =  $path.$name.'.png'; //图片文件
    QRcode::png($infourl, $filename, $errorCorrectionLevel, $matrixPointSize, 2); //生成图片

//    $filename = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn',$filename);
    $logo = 'http://shop.gogo198.cn/collect_website/public/logo.png';//准备好的logo图片
    $QR = $filename;//已经生成的原始二维码图
    if ($logo !== FALSE) {

        $QR = imagecreatefromstring(file_get_contents($QR));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);//二维码图片宽度
        $QR_height = imagesy($QR);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 5; //logo图片在二维码图片中宽度大小
        $scale = $logo_width/$logo_qr_width;
        $logo_qr_height = $logo_height/$scale; //logo图片在二维码图片中高度大小
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
            $logo_qr_height, $logo_width, $logo_height);
    }

    imagepng($QR,$filename); // 保存最终生成的二维码到本地

    //直接输出图片到浏览器
    Header("Content-type: image/png");

    $qrcode = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn',$filename);
    return $qrcode;
}

#集运流程链接
function get_link($function_id){
    $link = '';
    if($function_id==17){
        $link = '/?s=gather/package_forecast&process1=16&process2=17&process3=17';
    }elseif($function_id==18){
        $link = '/?s=gather/package_manage&manage=1&process1=16&process2=18&process3=18';
    }elseif($function_id==20){
        $link = '/?s=gather/package_forecasts&process1=19&process2=20&process3=20';
    }elseif($function_id==21){
        $link = '/?s=gather/package_manage&manage=2&process1=19&process2=21&process3=21';
    }elseif($function_id==34){
        $link = '/?s=gather/package_claim&process1=22&process2=34&process3=34';
    }elseif($function_id==23){
        $link = '/?s=gather/package_manage&manage=3&process1=22&process2=23&process3=23';
    }elseif($function_id==24){
        $link = '/?s=gather/package_manage&manage=4&process1=22&process2=24&process3=24';
    }elseif($function_id==28){
        $link = '/?s=gather/package_merge_split&process1=25&process2=26&process3=28';
    }elseif($function_id==29){
        $link = '/?s=gather/package_merge_split&process1=25&process2=26&process3=29';
    }elseif($function_id==30){
        $link = '/?s=gather/package_merge_split&process1=25&process2=26&process3=30';
    }elseif($function_id==27){
        $link = '/?s=gather/package_manage&manage=5&process1=25&process2=27&process3=27';
    }elseif($function_id==32){
        $link = '/?s=gather/add_waybill&process1=31&process2=32&process3=32';
    }elseif($function_id==33){
        $link = '/?s=gather/package_manage&manage=6&process1=31&process2=33&process3=33';
    }


    return $link;
}

// 获取表字段和注释
function getTableField($table)
{
    $res = Db::query("show full fields from ".$table);

    $field=[];
    foreach($res as $key=>$vo){
        if($vo['Field'] != 'id'){
            $field[] = [
                'field' => $vo['Field'],
                'comment' => $vo['Comment']
            ];
        }
    }

    if($table == 'ims_cutoms_elist_lading')
    {
        $elist = [
            ['field' => 'discharge_place', 'comment' => '货物存放地' ],
            ['field' => 'out_date', 'comment' => '出仓/进境日期' ],
            ['field' => 'contact_tel', 'comment' => '联系电话' ],
            ['field' => 'ebent_no', 'comment' => '电商企业编号' ],
            ['field' => 'ebent_name', 'comment' => '电商企业名称' ],
            ['field' => 'internet_domain_name', 'comment' => '电商平台域名' ],
            ['field' => 'apply_sea_port', 'comment' => '申报口岸' ],
            ['field' => 'trade_mode', 'comment' => '贸易方式' ],
            ['field' => 'elist_type', 'comment' => '清单类型' ],
            ['field' => 'comp_access_no', 'comment' => '报关企业代码' ],
            ['field' => 'comp_access_name', 'comment' => '报关企业名称' ],
            ['field' => 'assure_code', 'comment' => '担保企业编号' ],
            ['field' => 'ie_port', 'comment' => '进出口岸代码' ],
            ['field' => 'svp_code', 'comment' => '监管场所' ],
            ['field' => 'ie_date', 'comment' => '进出口日期' ],
            ['field' => 'trans_mode', 'comment' => '成交方式' ],
            ['field' => 'wrap_type', 'comment' => '外包装种类代码' ],
            ['field' => 'trans_type', 'comment' => '运输工具类型' ],
            ['field' => 'trans_code', 'comment' => '运输方式代码' ],
            ['field' => 'trans_no', 'comment' => '运输工具编号' ],
            ['field' => 'destination_country', 'comment' => '起运国/运抵国' ],
            ['field' => 'destination_port', 'comment' => '起运港/抵运港' ],
            ['field' => 'edest_date', 'comment' => '拟到达时间或出发时间' ],
            ['field' => 'ebp_ent_name', 'comment' => '电商平台企业名称' ],
            ['field' => 'ebp_ent_no', 'comment' => '电商平台企业编号' ],
            ['field' => 'ems_no', 'comment' => '账册号' ],
        ];
        $field = array_merge($field,$elist);
    }
    return $field;
}

//1.获取各大州下的国地
function get_country($no_need=0){
    $list2 = Db::name('centralize_diycountry_content')->where(['pid'=>9])->select();
    $list = [];
    foreach($list2 as $k=>$v){
        $list[$k]['name'] = $v['param1'];
        $list[$k]['value'] = $v['id'];
        if($no_need>0){
            $list[$k]['children'] = Db::name('centralize_diycountry_content')->where(['state_id'=>$v['id']])->whereRaw('state_id='.$v['id'].' and id<>'.$no_need)->select();
        }else{
            $list[$k]['children'] = Db::name('centralize_diycountry_content')->where(['state_id'=>$v['id']])->select();    
        }
    
        foreach($list[$k]['children'] as $k2=>$v2){
            $list[$k]['children'][$k2]['name'] = $v2['param2'];
            $list[$k]['children'][$k2]['value'] = $v2['id'];
            $list[$k]['children'][$k2]['children'] = [];
        }
    }
    return $list;
//    return json_encode($list,true);
}

//1.1、获取各国地下的行政区域
function get_country_area($country){
//    $country = Db::name('centralize_adminstrative_area')->where(['country_id'=>$country_id,'pid'=>0])->select();//省
//    if($country!=''){
//        foreach($country as $k3=>$v3){
//            $country[$k3]['name'] = $v3['code_name'];
//            $country[$k3]['value'] = 'area_'.$v3['id'];
//            $country[$k3]['children'] = Db::name('centralize_adminstrative_area')->where(['pid'=>$v3['id']])->select();//市
//            if($country[$k3]['children']!=''){
//                foreach($country[$k3]['children'] as $k4=>$v4){
//                    $country[$k3]['children'][$k4]['name'] = $v4['code_name'];
//                    $country[$k3]['children'][$k4]['value'] = 'area_'.$v4['id'];
//                    $country[$k3]['children'][$k4]['children'] = Db::name('centralize_adminstrative_area')->where(['pid'=>$v4['id']])->select();//区
//                    if($country[$k3]['children'][$k4]['children']!=''){
//                        foreach($country[$k3]['children'][$k4]['children'] as $k5=>$v5){
//                            $country[$k3]['children'][$k4]['children'][$k5]['name'] = $v5['code_name'];
//                            $country[$k3]['children'][$k4]['children'][$k5]['value'] = 'area_'.$v5['id'];
//                            $country[$k3]['children'][$k4]['children'][$k5]['children'] = Db::name('centralize_adminstrative_area')->where(['pid'=>$v5['id']])->select();//镇
//                            if($country[$k3]['children'][$k4]['children'][$k5]['children']!=''){
//                                foreach($country[$k3]['children'][$k4]['children'][$k5]['children'] as $k6=>$v6){
//                                    $country[$k3]['children'][$k4]['children'][$k5]['children'][$k6]['name'] = $v5['code_name'];
//                                    $country[$k3]['children'][$k4]['children'][$k5]['children'][$k6]['value'] = 'area_'.$v5['id'];
//                                    $country[$k3]['children'][$k4]['children'][$k5]['children'][$k6]['children'] = Db::name('centralize_adminstrative_area')->where(['pid'=>$v6['id']])->select();//街
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }
//    }
    foreach($country as $k=>$v){
        if($v['name']=='亚洲'){
            foreach($v['children'] as $k2=>$v2){
                if($v2['id']==162){
                    $country[$k]['children'][$k2]['children'] = Db::name('centralize_adminstrative_area')->where(['country_id'=>$v2['id'],'pid'=>0])->select();//省
                    if($country[$k]['children'][$k2]['children']!=''){
                        foreach($country[$k]['children'][$k2]['children'] as $k3=>$v3){
                            $country[$k]['children'][$k2]['children'][$k3]['name'] = $v3['code_name'];
                            $country[$k]['children'][$k2]['children'][$k3]['value'] = 'area_'.$v3['id'];
                            $country[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('centralize_adminstrative_area')->where(['pid'=>$v3['id']])->select();//市
                            if($country[$k]['children'][$k2]['children'][$k3]['children']!=''){
                                foreach($country[$k]['children'][$k2]['children'][$k3]['children'] as $k4=>$v4){
                                    $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['name'] = $v4['code_name'];
                                    $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['value'] = 'area_'.$v4['id'];
                                    $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'] = Db::name('centralize_adminstrative_area')->where(['pid'=>$v4['id']])->select();//区
                                    if($country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children']!=''){
                                        foreach($country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'] as $k5=>$v5){
                                            $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'][$k5]['name'] = $v5['code_name'];
                                            $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'][$k5]['value'] = 'area_'.$v5['id'];
                                            $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'][$k5]['children'] = Db::name('centralize_adminstrative_area')->where(['pid'=>$v5['id']])->select();//镇
                                            if($country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'][$k5]['children']!=''){
                                                foreach($country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'][$k5]['children'] as $k6=>$v6){
                                                    $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'][$k5]['children'][$k6]['name'] = $v5['code_name'];
                                                    $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'][$k5]['children'][$k6]['value'] = 'area_'.$v5['id'];
                                                    $country[$k]['children'][$k2]['children'][$k3]['children'][$k4]['children'][$k5]['children'][$k6]['children'] = Db::name('centralize_adminstrative_area')->where(['pid'=>$v6['id']])->select();//街
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return json_encode($country,true);
}

//1.2、获取各国地下的线路
function get_country_line($country){
    foreach($country as $k=>$v) {
        foreach ($v['children'] as $k2 => $v2) {
            $country[$k]['children'][$k2]['children'] = get_line_channel($v2['value'],$v2['id']);
        }
    }
    return json_encode($country,true);
}

//1.2、线路下的渠道
function get_line_channel($value,$country){
    $menu = Db::name('centralize_line_channel')->field('id,name')->select();//渠道名称
    foreach($menu as $k=>$v){
        $new_value = 'channel_'.$v['id'];
        $menu[$k]['name'] = $v['name'];
        $menu[$k]['value'] = $value.$new_value;
        $menu[$k]['children'] = get_this_line($v['id'],$country);//该国该渠道的所有线路
    }
    return $menu;
}

//1.2、获取该渠道下的线路
function get_this_line($id,$country){
//    $cmenu = Db::name('centralize_line_list')
//        ->alias('a')
//        ->join('centralize_line_country b','b.pid=a.id')
//        ->where(['b.country_code'=>$country,'a.channel_id'=>$id])
//        ->field('a.id,a.name')
//        ->group('a.id')
//        ->select();
    $cmenu = Db::name('centralize_lines')->where(['end_country'=>$country,'channel_id'=>$id])->select();
    if(empty($cmenu)){
        $cmenu[0]['name'] = '--暂无可选线路--';
        $cmenu[0]['value'] = '-1';
        $cmenu[0]['disabled'] = true;
    }else{
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = $v['name'];
            $cmenu[$k]['value'] = 'area_'.$v['id'];
        }
    }
    return $cmenu;
}

//2、获取货物类别
function get_product(){
    $menu = Db::name('centralize_gvalue_list')->where(['pid'=>1])->select();
    foreach($menu as $k=>$v){
        $menu[$k]['name'] = $v['name'];
        $menu[$k]['value'] = 'p'.$v['id'];
//        $menu[$k]['children'] = [];
        $menu[$k]['children'] = get_productDown($v['id'],$v['ids']);
    }
    return json_encode($menu,true);
}

//2.1、获取下级类别
function get_productDown($id,$ids){
    $cmenu = Db::name('centralize_gvalue_product')->whereRaw('id in ('.$ids.')')->field('id,name,pid')->select();
    foreach($cmenu as $k=>$v){
        $cmenu[$k]['name'] = $v['name'];
        $cmenu[$k]['value'] = $id.'_'.$v['id'];
        $cmenu[$k]['top_id'] = $id;
        $cmenu[$k]['children'] = [];
//        $cmenu[$k]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v2['id']])->field('id,name,country,channel,desc,keywords,pid')->select();
//        foreach($cmenu[$k]['children'] as $k2=>$v2){
//            $cmenu[$k]['children'][$k2]['name'] = $v2['name'];
//            $cmenu[$k]['children'][$k2]['value'] = $v2['id'];
//            $cmenu[$k]['children'][$k2]['top_id'] = $id;
//            $cmenu[$k]['children'][$k2]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v2['id']])->field('id,name,country,channel,desc,keywords,pid')->select();
//            foreach($cmenu[$k]['children'][$k2]['children'] as $k3=>$v3){
//                $cmenu[$k]['children'][$k2]['children'][$k3]['name'] = $v3['name'];
//                $cmenu[$k]['children'][$k2]['children'][$k3]['value'] = $v3['id'];
//                $cmenu[$k]['children'][$k2]['children'][$k3]['top_id'] = $id;
//                $cmenu[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v3['id']])->field('id,name,country,channel,desc,keywords,pid')->select();
//                foreach($cmenu[$k]['children'][$k2]['children'][$k3]['children'] as $k4=>$v4){
//                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['name'] = $v4['name'];
//                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['value'] = $v4['id'];
//                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['top_id'] = $id;
//                }
//            }
//        }
    }
    return $cmenu;
}

//1、获取中国的发货国地，获取省
function get_province($country_id=162){
    $data = Db::name('centralize_adminstrative_area')->where(['country_id'=>$country_id,'pid'=>0])->select();
    return json_encode($data,true);
}

//1.1、获取省下的市
function get_city($province_id=0){
    $data = Db::name('centralize_adminstrative_area')->where(['pid'=>$province_id])->select();
    return json_encode($data,true);
}

function get_category($config){
    $catearr = [];
    if(empty(session('catearr'))){
        $catearr2 = Db::connect($config)->name('category')->where(['parent_id'=>0])->select();
        foreach($catearr2 as $k=>$v){
            $catearr[$k]['value'] = $v['cat_id'];
            $catearr[$k]['name'] = $v['cat_name'];
            $catearr2[$k]['children'] = Db::connect($config)->name('category')->where(['parent_id'=>$v['cat_id']])->select();
            foreach($catearr2[$k]['children'] as $k2=>$v2){
                $catearr[$k]['children'][$k2]['value'] = $v2['cat_id'];
                $catearr[$k]['children'][$k2]['name'] = $v2['cat_name'];
                $catearr2[$k]['children'][$k2]['children'] = Db::connect($config)->name('category')->where(['parent_id'=>$v2['cat_id']])->select();
                foreach($catearr2[$k]['children'][$k2]['children'] as $k3=>$v3){
                    $catearr[$k]['children'][$k2]['children'][$k3]['value'] = $v3['cat_id'];
                    $catearr[$k]['children'][$k2]['children'][$k3]['name'] = $v3['cat_name'];
                    $catearr[$k]['children'][$k2]['children'][$k3]['children'] = [];
//                    $catearr2[$k]['children'][$k2]['children'][$k3]['children'] = Db::connect($config)->name('category')->where(['parent_id'=>$v3['cat_id']])->select();
                }
            }
        }
        session('catearr',$catearr);
    }else{
        $catearr = session('catearr');
    }


    return $catearr;
}

//#==================================================
//通知用户
function notice_user($user_id,$msg=[]){
    $user = Db::name('website_user')->where(['id'=>$user_id])->find();

    if(!empty($user['openid'])){
        #公众号通知
        $res = sendWechatMsg(json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>$msg['first'],
            'keyword1' => $msg['keyword1'],
            'keyword2' => $msg['keyword2'],
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '',
            'url' => $msg['url'],
            'openid' => $user['openid'],#'ov3-bt5vIxepEjWc51zRQNQbFSaQ'
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ],true));
//        dd($res);
    }elseif(!empty($user['sns_openid'])){
        #小程序通知

    }elseif(!empty($user['email'])){
        #邮箱通知
        httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'消息通知','content'=>$msg['msg']]);
    }elseif(!empty($user['phone'])){
        #手机通知
        $post_data = [
            'spid'=>'254560',
            'password'=>'J6Dtc4HO',
            'ac'=>'1069254560',
            'mobiles'=>$user['phone'],
            'content'=>$msg['msg'].'【GOGO】',
        ];
        $post_data = json_encode($post_data,true);
        httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
    }

    return 1;
}

#通知他人
function notice_people($data){
    $notice_type = $data['notice_type'];

    if($notice_type==1){
        #邮箱
        httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$data['number'],'title'=>$data['title'],'content'=>$data['msg']]);
    }
    elseif($notice_type==2){
        #短信
        $post_data = [
            'spid'=>'254560',
            'password'=>'J6Dtc4HO',
            'ac'=>'1069254560',
            'mobiles'=>$data['number'],
            'content'=>$data['msg'].'【GOGO】',
        ];
        $post_data = json_encode($post_data,true);
        httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
    }

    return 1;
}

//常用通知
function send_msg($user=[],$msg=[]){
    if(!empty($user['email'])){
        #邮箱通知
        httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'消息通知','content'=>$msg['msg']]);
    }elseif(!empty($user['phone'])){
        #手机通知
        $post_data = [
            'spid'=>'254560',
            'password'=>'J6Dtc4HO',
            'ac'=>'1069254560',
            'mobiles'=>$user['phone'],
            'content'=>$msg['msg'].'【GOGO】',
        ];
        $post_data = json_encode($post_data,true);
        httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
    }
}
//#==================================================

function get_statusname($status){
    if($status==-2){
        return '待确认';
    }elseif($status==-3){
        return '申请取消订购';
    }elseif($status==-4){
        return '已取消';
    }elseif($status==-5){
        return '申请退货';
    }elseif($status==-6){
        return '已退货';
    }elseif($status==-7){
        return '申请换货';
    }elseif($status==-8){
        return '已换货';
    }elseif($status==-9){
        return '有货（无修改）';
    }elseif($status==-10){
        return '有货（有修改）';
    }elseif($status==-11){
        return '无货';
    }elseif($status==-12){
        return '拒绝订购';
    }elseif($status==-13){
        return '请求支付中';
    }elseif($status==-14){
        return '允许支付';
    }elseif($status==0){
        return '待付款';
    }elseif($status==1){
        return '待采购';
    }elseif($status==2){
        return '已发货';
    }elseif($status==3){
        return '待验货';
    }elseif($status==4){
        return '待入库';
    }elseif($status==5){
        return '待集货';
    }elseif($status==6){
        return '待转运';
    }elseif($status==7){
        return '待签收';
    }elseif($status==8){
        return '待评价';
    }elseif($status==9){
        return '已完成';
    }
}

//微信小程序、公众号、邮箱、手机通知
function common_notice($data,$msg){
    if(!empty($data['sns_openid'])){
        #小程序
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
        $res = file_get_contents($url);
        $result = json_decode($res, true);

        $post2 = json_encode([
            'template_id'=>'GRa2BGkGrqU8g7IgMAVh6vx2iDD08uJSdK316TINQ7s',
            'page'=>$msg['page'],
            'touser' =>$data['sns_openid'],
            'data'=>['thing1'=>['value'=>$msg['taskname']],'phrase2'=>['value'=>$msg['opera']],'time4'=>['value'=>date('Y年m月d日 H:i')]],
            'miniprogram_state'=>'formal',//developer为开发版；trial为体验版；formal为正式版
            'lang'=>'zh_CN',
        ]);
        $resu = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
    }elseif(!empty($data['openid'])){
        #微信
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'find' =>$msg['msg']."请打开查看！",
            'keyword1' => $msg['msg']."请打开查看！",
            'keyword2' => $msg['opera'],
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => $msg['url'],
            'openid' => $data['openid'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);

        httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
    }elseif(!empty($data['email'])){
        $title = $msg['msg']."请打开查看！";
        $post_data = json_encode(['email'=>$data['email'],'title'=>$title,'content'=>$msg['url']],true);
        $res = httpRequest('https://admin.gogo198.cn/collect_website/public/?s=api/sendemail/index',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
    }elseif(!empty($data['phone'])){
        $post_data = [
            'spid'=>'254560',
            'password'=>'J6Dtc4HO',
            'ac'=>'1069254560',
            'mobiles'=>$data['phone'],
            'content'=>$msg['msg'].'请打开链接（'.$msg['url'].'）查看！【GOGO】',
        ];
        $post_data = json_encode($post_data,true);
        httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
    }
}

//更新订单商品库存（代发仓库不减库存）
function update_goods_inventory($orderid,$goods_list){
    //{"goods_info":[{"good_id":57023,"otherfee_content":null,"otherfee_currency":null,"otherfee_total":null,"reduction_content":null,"reduction_money":null,"prefe_gift":null,"prefe_reduction":null,"gift_money":null,"noinclude_content":null,"noinclude_money":null,"potential_content":null,"potential_money":null,"file":null,"services":"[{\"service_id\":2},{\"service_id\":12},{\"service_id\":13}]","sku_info":[{"sku_id":1652906,"goods_num":2,"price":"17.60","currency":"5","cart_id":159}]}],"warehouse_id":16,"delivery_method":1,"gather_method":0,"line_id":0,"address_id":19}
    $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];
    if(!empty($goods_list['goods_info'])){
        foreach($goods_list['goods_info'] as $k=>$v){
            $goods_merchant = Db::connect($config)->name('goods_merchant')->where(['shelf_id'=>$v['good_id']])->find();
            $goods = Db::connect($config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();
            
            if(1>2){
                foreach($v['sku_info'] as $k2=>$v2){
                    #1、修改规格的库存
                    $sku_info = Db::connect($config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                    $sku_info['sku_prices'] = json_decode($sku_info['sku_prices'],true);
                    $sku_info['sku_prices']['goods_number'] = $sku_info['sku_prices']['goods_number'] - $v2['goods_number'];
                    Db::connect($config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->update([
                        'goods_number'=>$sku_info['sku_prices']['goods_number'],
                        'sku_prices'=>json_encode($sku_info['sku_prices'],true)
                    ]);
                    $merchant_goods_sku_info = Db::connect($config)->name('goods_sku_merchant')->where(['goods_id'=>$goods_merchant['id'],'spec_ids'=>$sku_info['spec_ids'],'spec_vids'=>$sku_info['spec_vids'],'goods_sn'=>$sku_info['goods_sn'],'goods_barcode'=>$sku_info['goods_barcode'],'goods_stockcode'=>$sku_info['goods_stockcode']])->find();
                    $merchant_goods_sku_info['sku_prices'] = json_decode($sku_info['sku_prices'],true);
                    $merchant_goods_sku_info['sku_prices']['goods_number'] = $merchant_goods_sku_info['sku_prices']['goods_number'] - $v2['goods_number'];
                    Db::connect($config)->name('goods_sku_merchant')->where(['goods_id'=>$goods_merchant['id'],'spec_ids'=>$sku_info['spec_ids'],'spec_vids'=>$sku_info['spec_vids'],'goods_sn'=>$sku_info['goods_sn'],'goods_barcode'=>$sku_info['goods_barcode'],'goods_stockcode'=>$sku_info['goods_stockcode']])->update([
                        'goods_number'=>$sku_info['sku_prices']['goods_number'],
                        'sku_prices'=>json_encode($merchant_goods_sku_info['sku_prices'],true)
                    ]);
                }
                
                #2、修改商品的库存
                $all_goods_sku = Db::connect($config)->name('goods_sku')->where(['goods_id'=>$v['good_id']])->field('goods_number')->select();
                $all_num = 0;
                foreach($all_goods_sku as $k2=>$v2){
                    $all_num += $v2['goods_number'];
                }
                Db::connect($config)->name('goods')->where(['goods_id'=>$v['good_id']])->update(['goods_number'=>$all_num]);
                Db::connect($config)->name('goods_merchant')->where(['shelf_id'=>$v['good_id']])->update(['goods_number'=>$all_num]);
                
                #3、同步更新在商品现有库存表
                foreach($v['sku_info'] as $k2=>$v2) {
                    $goods_sku_info = Db::connect($config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                    $merchant_goods_sku_info = Db::connect($config)->name('goods_sku_merchant')->where(['goods_id'=>$goods_merchant['id'],'spec_ids'=>$goods_sku_info['spec_ids'],'spec_vids'=>$goods_sku_info['spec_vids'],'goods_sn'=>$goods_sku_info['goods_sn'],'goods_barcode'=>$goods_sku_info['goods_barcode'],'goods_stockcode'=>$goods_sku_info['goods_stockcode']])->find();
    
                    Db::name('website_warehouse_goodsnum')->where(['company_id' => $goods['shop_id'], 'warehouse_id' => $goods_merchant['wid'], 'goods_id' => $goods_merchant['id'], 'sku_id' => $merchant_goods_sku_info['sku_id']])->update(['num' => $goods_sku_info['goods_number']]);
                }
            }
            
            foreach($v['sku_info'] as $k2=>$v2){
                if($goods['goods_type']==0){
                    #单品
                    $origin_num = Db::name('website_warehouse_goodsnum')->where(['warehouse_id'=>$goods['wid'],'goods_id'=>$goods_merchant['id'],'sku_id'=>$v2['sku_id']])->value('num');
                    Db::name('website_warehouse_goodsnum')->where(['warehouse_id'=>$goods['wid'],'goods_id'=>$goods_merchant['id'],'sku_id'=>$v2['sku_id']])->update(['num'=>$origin_num - $v2['goods_number']]);
                }
                elseif($goods['goods_type']==1){
                    #组合
                    $origin_num = Db::name('website_warehouse_combo_goodsnum')->where(['warehouse_id'=>$goods['wid'],'goods_id'=>$goods_merchant['id']])->value('sum_num');
                    Db::name('website_warehouse_combo_goodsnum')->where(['warehouse_id'=>$goods['wid'],'goods_id'=>$goods_merchant['id']])->update(['sum_num'=>$origin_num - $v2['goods_number']]);
                }
            }
        }
    }
}

/**
 * 返回并去除二维数组指定列名的重复值
 * @param $array1
 * @param $array2
 * @param $column_name
 */
function remove_duplicate_values($array1,$array2,$column_name){
    $array = array_merge($array1,$array2);
    if($column_name=='name'){
        $callback = function($item) {
            return $item['name'];
        };
    }
    elseif($column_name=='lx_name'){
        $callback = function($item) {
            return $item['lx_name'];
        };
    }
    elseif($column_name=='cf_name'){
        $callback = function($item) {
            return $item['cf_name'];
        };
    }
    elseif($column_name=='yb_name'){
        $callback = function($item) {
            return $item['yb_name'];
        };
    }
    elseif($column_name=='lb_name'){
        $callback = function($item) {
            return $item['lb_name'];
        };
    }

    $unique_names = array_map($callback, $array);
    $unique_names = array_unique($unique_names);
    $list = [];
    foreach ($unique_names as $name) {
        $list[] = [$column_name => $name];
    }
    return $list;
}

function order_status($status){
    if($status==-1){
        return '处方待申请';
    }elseif($status==-2){
        return '待确认';
    }elseif($status==-3){
        return '申请取消订购';
    }elseif($status==-4){
        return '已取消';
    }elseif($status==-5){
        return '申请退货';
    }elseif($status==-6){
        return '已退货';
    }elseif($status==-7){
        return '申请换货';
    }elseif($status==-8){
        return '已换货';
    }elseif($status==-13){
        return '待付处理';
    }elseif($status==-14){
        return '待付处理通知';
    }elseif($status==-15){
        return '退款处理';
    }elseif($status==0){
        return '待付款';
    }elseif($status==1){
        return '已付，待采购';
    }elseif($status==2){
        return '已发货';
    }elseif($status==3){
        return '待验货';
    }elseif($status==4){
        return '待入库';
    }elseif($status==5){
        return '待集货';
    }elseif($status==6){
        return '待转运';
    }elseif($status==7){
        return '待签收';
    }elseif($status==8){
        return '待评价';
    }elseif($status==9){
        return '已完成';
    }
}

function platform_log($request){
    #日志记录
    $time = time();
    $content = '访客@@';

    if(session('myUser')!=''){
        $content = '用户【'.session('myUser')['username'].'】@@';
    }

    // 获取协议类型
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    // 获取主机名(包括域名和端口)
    $host = $_SERVER['HTTP_HOST'];
    // 获取资源路径
    $uri = $_SERVER['REQUEST_URI'];
    // 组合完整的URL
    $url = $protocol . '://' . $host . $uri;

    $userAgent = '';
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
    } else {
        // 处理未定义的情况，例如设置默认值或记录错误
        $userAgent = '未知';
    }

    $content .= $_SERVER['REMOTE_ADDR'].'@@'.$userAgent.'@@'.date('Y-m-d H:i:s',$time).'@@'.$url;

    Db::name('system_log')->insert([
        'type'=>5,
        'ip'=>$_SERVER['REMOTE_ADDR'],
        'content'=>$content,
        'createtime'=>$time
    ]);
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }
}

//生成订单编号===与商城生成订单一致
function get_ordersn($type){
    $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    $year = date('Y');
    $month = date('m');
    $days = date("t", mktime(0, 0, 0, $month, 1, $year));
    $ordersn = '';

    $starttime = strtotime($year.'-'.$month.'-1 00:00:00');
    $endtime = strtotime($year.'-'.$month.'-'.$days.' 23:59:59');

    if($type==1){
        #选购单编号(今年今月第N个选购单)
        $ordersn = $year.$month.'A';
        $times = Db::connect($config)->name('cart')->whereRaw('created_at>='.$starttime.' and created_at<='.$endtime)->count();
        $ordersn = $ordersn.str_pad($times+1,'4','0',STR_PAD_LEFT);
    }
    elseif($type==2){
        #订购单编号(今年今月第N个订购单)
        $ordersn = $year.$month.'B';
        $times = Db::name('website_order_list')->whereRaw('createtime>='.$starttime.' and createtime<='.$endtime)->count();
        $ordersn = $ordersn.str_pad($times+1,'4','0',STR_PAD_LEFT);
    }
    elseif($type==3){
        #商品订单编号（今年今月今日今时第N个支付单）
        $date = date('d');
        $hour = date('H');
        $ordersn = $year.$month.'G'.$date.$hour;
        $starttime = strtotime($year.'-'.$month.'-'.$date.' '.$hour.':00:00');
        $endtime = strtotime($year.'-'.$month.'-'.$date.' '.$hour.':59:59');
        $times = Db::name('website_order_list')->whereRaw('createtime>='.$starttime.' and createtime<='.$endtime)->count();
        $ordersn = $ordersn.str_pad($times+1,'4','0',STR_PAD_LEFT);
    }

    return $ordersn;
}

#立即同步文本信息至“云端服务”/“本地服务”
function now_sync_to_local($data){
    $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    if($data['sync_type']==2){
        # 系统重新对数据进行重头开始同步：把is_add_dataset=1改为0
        restart_sync($data['type']);
    }

    $send_num=0;#发送数量
    $success_num=0;#成功数量
    $fail_num=0;#失败数量

    #获取同步储存方向
    $store_direction = Db::name('train_setting')->where(['type'=>1])->find();

    if($data['type']==0){
        #商品
        $unified_send = "";#统一发送
        $goods = Db::connect($config)->name('goods')->where(['is_add_dataset'=>0])->limit($data['sync_num'])->field('goods_id,other_platform,goods_name,shop_id,cat_id')->select();
        foreach($goods as $k2=>$v2) {
            #平台名称
            $platform = '';
            if (!empty($v2['other_platform'])) {
                $platform = $v2['other_platform'];
            } else {
                $platform = '淘中国';
            }

            #分类名称
            $category_name = '';
            if(!empty($v2['cat_id'])){
                $category_name = Db::connect($config)->name('category')->where(['cat_id'=>$v2['cat_id']])->field('cat_name')->find()['cat_name'];
            }
            $goods_sku = Db::connect($config)->name('goods_sku')->where(['goods_id' => $v2['goods_id']])->field('sku_id,sku_prices,spec_names')->select();

            $goods_link = 'https://www.gogo198.cn/goods-' . $v2['goods_id'] . '.html';

            if($store_direction['direction_type']==2) {
                $unified_send = "{
                    \"goods_name\":\"{$v2['goods_name']}\",
                    \"shop_name\":\"{$platform}\",
                    \"shop_id\":\"{$v2['shop_id']}\",
                    \"category_name\":\"{$category_name}\",
                    \"goods_url\":\"{$goods_link}\",
                    \"database\":\"lrw\",
                    \"table\":\"goods\",
                    \"all_skus\":[
                ";
            }
            foreach ($goods_sku as $k3 => $v3) {
                $sku_prices = json_decode($v3['sku_prices'], true);
                $currency = Db::name('centralize_currency')->where(['id' => $sku_prices['currency'][0]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                $sku = '';
                if (empty($v3['spec_names'])) {
                    $sku = '无';
                } else {
                    $sku = $v3['spec_names'];
                }


                if($store_direction['direction_type']==1){
                    #本地储存
                    $result = send_data_tcp('sync_info@:@product@@@' . json_encode(['goods_id' => $v2['goods_id'], 'shop_id' => $v2['shop_id'], 'sku_id' => $v3['sku_id'], 'goods_name' => $v2['goods_name'], 'category_name'=>$category_name, 'goods_platform' => $platform, 'options_name' => $sku, 'goods_currency' => $currency, 'goods_price' => $sku_prices['price'][0], 'goods_inventory' => $sku_prices['goods_number'], 'goods_link' => 'https://www.gogo198.cn/goods-' . $v2['goods_id'] . '.html'], true));

                    #记录同步数量
                    $send_num +=1;
                    if($result=='True' || $result==True){
                        $success_num += 1;
                    }else{
                        $fail_num += 1;
                    }
                }
                elseif($store_direction['direction_type']==2){
                    #云端储存
                    $uploadDir = "/www/wwwroot/gogo/collect_website/public/storage_info/";
                    $fileName = "text_input_" . time() . ".txt";
                    // 获取文本内容
                    $textContent = '商品ID：'.$v2['goods_id'].'，商家ID：'.$v2['shop_id'].'规格ID：'.$v3['sku_id'].'，商品名称：'.$v2['goods_name'].'，商品分类名称：'.$category_name.'，商品平台名称：'.$platform.'，规格名称：'.$sku.'，商品币种：'.$currency.'，商品价格：'.$sku_prices['price'][0].'，商品库存：'.$sku_prices['goods_number'].'，商品链接：https://www.gogo198.cn/goods-' . $v2['goods_id'] . '.html';


//                    // 保存文本到临时文件
//                    $targetPath = $uploadDir . $fileName;
//                    file_put_contents($targetPath, $textContent);
//                    $command = "python3 /opt/huggingface_project/vectorize.py " . escapeshellarg($textContent);
                    $unified_send .= "{
                        \"sku_id\":\"{$v3['sku_id']}\",
                        \"sku_name\":\"{$sku}\",
                        \"currency\":\"{$currency}\",
                        \"price\":\"{$sku_prices['price'][0]}\",
                        \"goods_number\":\"{$sku_prices['goods_number']}\"
                    },";

//                    $command = "python3 /opt/huggingface_project/vectorize.py vectorize_structured product {$v2['goods_id']} '" ."{
//                        \"goods_name\":\"{$v2['goods_name']}\",
//                        \"shop_name\":\"{$platform}\",
//                        \"shop_id\":\"{$v2["shop_id"]}\",
//                        \"category_name\":\"{$category_name}\",
//                        \"sku_id\":\"{$v3["sku_id"]}\",
//                        \"sku_name\":\"{$sku}\",
//                        \"currency\":\"{$currency}\",
//                        \"price\":\"{$sku_prices['price'][0]}\",
//                        \"goods_number\":\"{$sku_prices['goods_number']}\",
//                        \"goods_url\":\"{$goods_link}\",
//                        \"database\":\"lrw\",
//                        \"data_table\":\"goods\",
//                    }" . "'" ;
//
//                    $result = shell_exec($command);
//
//                    echo "处理结果: " . $result;
//                    #记录同步数量
//                    $send_num +=1;
//                    $result = explode('@@',$result)[0];
//                    if($result=='True' || $result==True){
//                        $success_num += 1;
//                    }else{
//                        $fail_num += 1;
//                    }
                }
            }

            if($store_direction['direction_type']==2) {
                $unified_send = rtrim($unified_send,',');
                $unified_send .= "]}";

                $command = "python3 /opt/huggingface_project/vectorize.py vectorize_structured product {$v2['goods_id']} '{$unified_send}'";

                $result = shell_exec($command);
                echo "处理结果: " . $result;
                #记录同步数量
                $send_num +=1;
                $result = explode('@@',$result)[0];
                if($result=='True' || $result==True){
                    $success_num += 1;
                }else{
                    $fail_num += 1;
                }
            }
            Db::connect($config)->name('goods')->where(['goods_id' => $v2['goods_id']])->update(['is_add_dataset' => 1]);
        }
    }
    elseif($data['type']==1){
        #订单
        $order = Db::name('website_order_list')->where(['is_add_dataset'=>0])->limit($data['sync_num'])->select();
        foreach($order as $kk=>$vv){
            #订单信息
            $order[$kk]['status_name'] = order_status($vv['status']);
            $order[$kk]['createtime'] = date('Y-m-d H:i:s',$vv['createtime']);
            $currency = Db::name('centralize_currency')->where(['id'=>$vv['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
            $order[$kk]['content'] = json_decode($vv['content'],true);
            $order_info = '订单信息：（订单编号：'.$order[$kk]['ordersn'].'，订单币种：'.$currency.'，订单金额：'.$order[$kk]['true_money'].'，实付金额：'.$order[$kk]['final_money'].'，预付费用：'.$order[$kk]['prepaid_money'].'，剩余费用：'.$order[$kk]['remain_money'].'，订单状态：'.$order[$kk]['status_name'].'，创建时间：'.$order[$kk]['createtime'].'）。';

            #订单信息同步
            $order_arr = ['id' => $vv['id'], 'ordersn' => $vv['ordersn'], 'currency' => $currency, 'order_money' => $vv['true_money'], 'final_money' => $vv['final_money'], 'prepaid_money' => $vv['prepaid_money'], 'remain_money' => $vv['remain_money'], 'status_name' => $order[$kk]['status_name'], 'createtime' => $order[$kk]['createtime'], 'user_id' => $vv['user_id'], 'pay_id' => $vv['pay_id']];

            $buy_goods_info = '订单商品信息：（';
            foreach($order[$kk]['content']['goods_info'] as $k2=>$v2){
                #订单商品信息
                $goods = Db::connect($config)->name('goods')->where(['goods_id'=>$v2['good_id']])->find();
                $buy_goods_info .= '第'.($k2+1).'个：（商品名称：'.$goods['goods_name'].')，';
                foreach($v2['sku_info'] as $k3=>$v3){
                    $sku_info = Db::connect($config)->name('goods_sku')->where(['sku_id'=>$v3['sku_id']])->find();
                    $currency = Db::name('centralize_currency')->where(['id'=>$v3['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                    $spec_name = '无具体规格名称';
                    if(!empty($sku_info['spec_names'])){
                        $spec_name = $sku_info['spec_names'];
                    }
                    $buy_goods_info .= '(商品规格'.($k3+1).'：“'.$spec_name.'”，规格价格：'.$currency.$v3['price'].'，购买数量：'.$v3['goods_num'].'），';

                    #订单商品信息同步
                    $order_arr['goods_id'] = $v2['good_id'];
                    $order_arr['sku_id'] = $v3['sku_id'];
                    $order_arr['sku_name'] = $spec_name;
                    $order_arr['sku_currency'] = $currency;
                    $order_arr['sku_price'] = $v3['price'];
                    $order_arr['buy_num'] = $v3['goods_num'];
                }
                $buy_goods_info = rtrim($buy_goods_info,'，');
                $buy_goods_info .= '。';
            }
            $buy_goods_info .= '）。';
            $order_info = $order_info . $buy_goods_info;

            #买家信息
            $user = Db::name('website_user')->where(['id'=>$order[$kk]['user_id']])->find();
            $user_name = '';
            if(!empty($user['realname'])){
                $user_name = $user['realname'];
            }else{
                $user_name = $user['nickname'];
            }
            $order_info .= '买家信息：（买家名称：'.$user_name.'，买家手机号：'.$user['area_code'].' '.$user['phone'].'，买家邮箱号：'.$user['email'].'）。';

            #支付单信息
            $pay_order_info = '支付单信息：暂无支付信息。';
            if($vv['pay_id']>0){
                $pay_order = Db::name('customs_collection')->where(['id'=>$vv['pay_id']])->find();
                $pay_overdue_date = '无';
                if(!empty($pay_order['overdue'])){
                    $pay_overdue_date = date('Y-m-d H:i:s',$pay_order['overdue']);
                }
                $status_name = '';
                if($pay_order['status']==0){
                    $status_name = '待付款';
                }
                elseif($pay_order['status']==1){
                    $status_name = '已付款';
                }
                $pay_type = '';
                if($pay_order['pay_type']==1){
                    $pay_type = '微信';
                }
                elseif($pay_order['pay_type']==2){
                    $pay_type = '支付宝';
                }
                elseif($pay_order['pay_type']==3){
                    $pay_type = '其他支付方式';
                }
                $paytime = '';
                if(!empty($pay_order['paytime'])){
                    $paytime = date('Y-m-d H:i:s',$pay_order['paytime']);
                }

                #支付单信息同步
                $order_arr['pay_ordersn'] = $pay_order['ordersn'];
                $order_arr['trade_price'] = $pay_order['trade_price'];
                $order_arr['payer_name'] = $pay_order['payer_name'];
                $order_arr['payer_tel'] = $pay_order['payer_tel'];
                $order_arr['pay_term'] = $pay_order['pay_term'];
                $order_arr['pay_fee'] = $pay_order['pay_fee'];
                $order_arr['overdue_money'] = $pay_order['overdue_money'];
                $order_arr['pay_overdue_date'] = $pay_overdue_date;
                $order_arr['total_money'] = $pay_order['total_money'];
                $order_arr['paystatus_name'] = $status_name;
                $order_arr['pay_type'] = $pay_type;
                $order_arr['paytime'] = $paytime;
                $order_arr['pay_createtime'] = date('Y-m-d H:i:s', $pay_order['createtime']);
            }

            $result = send_data_tcp('sync_info@:@order@@@' . json_encode($order_arr, true));

            #记录同步数量
            $send_num +=1;
            if($result=='True' || $result==True){
                $success_num += 1;
            }else{
                $fail_num += 1;
            }

            Db::name('website_order_list')->where(['id'=>$vv['id']])->update(['is_add_dataset'=>1]);
        }
    }
    elseif($data['type']==2){
        #物流
        $lines = Db::name('centralize_lines')->whereRaw('is_add_dataset=0 and id>=21')->limit($data['sync_num'])->select();
        foreach($lines as $k=>$v){
            $lines[$k]['start_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$v['start_country']])->field('param2')->find()['param2'];
            $lines[$k]['transport_name'] = Db::name('centralize_lines_transport_method')->where(['id'=>$v['transport_id']])->field('name')->find()['name'];
            $lines[$k]['channel_name'] = Db::name('centralize_line_channel')->where(['id'=>$v['channel_id']])->find()['name'];
            $lines[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            #联运详情==
            $lines[$k]['transport_union_info'] = '';
            if($lines[$k]['transport_name']=='联运'){
                $lines[$k]['transport_union_info'] = '联运详情：'.$lines['transport_union'].'，';
            } elseif($lines[$k]['transport_name']=='海运'){
                $lines[$k]['transport_union_info'] = '船期截单：'.$lines['shipping_date'].'，';
            }
            #船期截单==
            $lines[$k]['week'] = json_decode($v['week'],true);
            $lines[$k]['shipping_date'] = '';
            if($lines[$k]['transport_id']==2){
                if($lines[$k]['week']['have_week']==0){
                    $lines[$k]['shipping_date'] = '船期信息：无船期';
                }
                elseif($lines[$k]['week']['have_week']==1){
                    $lines[$k]['shipping_date'] = '船期信息：有船期，周'.$lines[$k]['week']['start_week'].'截单，'.$lines[$k]['week']['this_week'].'周'.$lines[$k]['week']['end_week'].'开船；';
                }
            }
            #尾程派送
            $lines_delivery = Db::name('centralize_lines_delivery')->where(['id'=>$lines[$k]['delivery_id']])->find();
            $lines[$k]['deliver_info'] = $lines_delivery['name'].'-'.$lines_delivery['remark'];

            #线路内容==
            $lines[$k]['content'] = json_decode($lines[$k]['content'],true);

            foreach($lines[$k]['content']['procategory'] as $k2=>$v2){
                #货物类别名称
                $lines[$k]['content']['procategory'][$k2] = Db::name('centralize_gvalue_list')->where(['id'=>$v2])->field('name')->find()['name'];
                #适用货物属性名称
                $chicategory = explode(',',$lines[$k]['content']['chicategory'][$k2]);
                $chicategory_name = '';
                foreach($chicategory as $k3=>$v3){
                    $this_chicategory_name = Db::name('centralize_gvalue_product')->where(['id'=>$v3])->field('name')->find()['name'];
                    $chicategory_name .= ','.$this_chicategory_name;
                }
                $lines[$k]['content']['chicategory'][$k2] = rtrim(ltrim($chicategory_name,','),',');
                #计费标准====
                #最低消费
                if($lines[$k]['content']['mini_cost'][$k2]==1){
                    $lines[$k]['content']['mini_cost'][$k2] = '无最低';
                }
                elseif($lines[$k]['content']['mini_cost'][$k2]==2){
                    $lines[$k]['content']['mini_cost'][$k2] = '有最低';
                    $lines[$k]['content']['minicost_unit'][$k2] = Db::name('unit')->where(['code_value'=>$lines[$k]['content']['minicost_unit'][$k2]])->field('code_name')->find()['code_name'];
                }

                #计费区间
                foreach($lines[$k]['content']['qj1'][$k2] as $k3=>$v3){
                    #计费区间--数值/以上
                    if($lines[$k]['content']['qj2_method'][$k2][$k3]==1){
                        $lines[$k]['content']['qj2_method'][$k2][$k3]='数值';
                    }
                    elseif($lines[$k]['content']['qj2_method'][$k2][$k3]==2){
                        $lines[$k]['content']['qj2_method'][$k2][$k3]='以上';
                    }

                    #计费单位
                    $lines[$k]['content']['unit'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines[$k]['content']['unit'][$k2][$k3]])->field('code_name')->find()['code_name'];

                    #计费方式
                    foreach($lines[$k]['content']['jf_method'][$k2][$k3] as $k4=>$v4){
                        if($lines[$k]['content']['jf_method'][$k2][$k3][$k4]==1){
                            $lines[$k]['content']['jf_method'][$k2][$k3][$k4] = '首续计费';
                            #计费币种
//                            if(isset($lines[$k]['content']['currency'][$k2][$k3][$k4][0])){
//                                $lines[$k]['content']['currency'][$k2][$k3][$k4][0] = Db::name('centralize_currency')->where(['id'=>$lines[$k]['content']['currency'][$k2][$k3][$k4][0]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
//                            }else{
//                                $lines[$k]['content']['currency'][$k2][$k3][$k4] = Db::name('centralize_currency')->where(['id'=>$lines[$k]['content']['currency'][$k2][$k3][$k4]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
//                            }


                            if(!empty($lines[$k]['content']['currency'][$k2][$k3][$k4])){
                                $lines[$k]['content']['currency'][$k2][$k3][$k4] = 'CNY';
                            }
                        }
                        elseif($lines[$k]['content']['jf_method'][$k2][$k3][$k4]==2){
                            $lines[$k]['content']['jf_method'][$k2][$k3][$k4] = '按量计费';
                            #计费币种
//                            if(count($lines[$k]['content']['currency'][$k2][$k3][$k4])>0){
//                                foreach($lines[$k]['content']['currency'][$k2][$k3][$k4] as $k5=>$v5){
//                                    $lines[$k]['content']['currency'][$k2][$k3][$k4][$k5] = Db::name('centralize_currency')->where(['id'=>$lines[$k]['content']['currency'][$k2][$k3][$k4][$k5]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
//                                }
//                            }
//                            else{
//                                $lines[$k]['content']['currency'][$k2][$k3][$k4] = Db::name('centralize_currency')->where(['id'=>$lines[$k]['content']['currency'][$k2][$k3][$k4]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
//                            }

                            if(!empty($lines[$k]['content']['currency'][$k2][$k3][$k4])){
                                $lines[$k]['content']['currency'][$k2][$k3][$k4] = 'CNY';
                            }
                        }
                        elseif($lines[$k]['content']['jf_method'][$k2][$k3][$k4]==3){
                            $lines[$k]['content']['jf_method'][$k2][$k3][$k4] = '分段计费';
                            #计费币种
                            foreach($lines[$k]['content']['currency'][$k2][$k3][$k4] as $k5=>$v5){
                                $lines[$k]['content']['currency'][$k2][$k3][$k4][$k5] = Db::name('centralize_currency')->where(['id'=>$lines[$k]['content']['currency'][$k2][$k3][$k4][$k5]])->field('currency_symbol_standard')->find()['currency_symbol_standard'];

                                if(!empty($lines[$k]['content']['currency'][$k2][$k3][$k4][$k5])){
                                    $lines[$k]['content']['currency'][$k2][$k3][$k4][$k5] = 'CNY';
                                }
                            }

                            #分段计费方式
                            foreach($lines[$k]['content']['fenduan_method'][$k2][$k3][$k4] as $k5=>$v5){
                                if($v5==1){
                                    $lines[$k]['content']['fenduan_method'][$k2][$k3][$k4][$k5] = '数值';
                                }
                                elseif($v5==2){
                                    $lines[$k]['content']['fenduan_method'][$k2][$k3][$k4][$k5] = '以上';
                                }
                            }
                        }
                    }
                }

                #体积算法====
                #分泡
                if(!empty($lines[$k]['content']['fenpao'][$k2])){
                    $lines[$k]['content']['fenpao'][$k2] = Db::name('centralize_lines_strict')->where(['id'=>$lines[$k]['content']['fenpao'][$k2]])->field('name')->find()['name'];
                }
                #超限条款====
                #超重限制名称
                if(!empty($lines[$k]['content']['overweight'][$k2])){
                    $lines[$k]['content']['overweight'][$k2] = Db::name('centralize_lines_strict')->where(['id'=>$lines[$k]['content']['overweight'][$k2]])->field('name')->find()['name'];
                }
                #超长限制====
                #超长限制名称
                if(!empty($lines[$k]['content']['overlong'][$k2])){
                    $lines[$k]['content']['overlong'][$k2] = Db::name('centralize_lines_strict')->where(['id'=>$lines[$k]['content']['overlong'][$k2]])->field('name')->find()['name'];
                }
                #其他限制====
                #其他限制名称
                if(!empty($lines[$k]['content']['overother'][$k2])){
                    $lines[$k]['content']['overother'][$k2] = Db::name('centralize_lines_strict')->where(['id'=>$lines[$k]['content']['overother'][$k2]])->field('name')->find()['name'];
                }
                #清关说明====
                if(!empty($lines[$k]['content']['clearance'][$k2])){
                    $lines[$k]['content']['clearance'][$k2] = Db::name('centralize_lines_clearance')->where(['id'=>$lines[$k]['content']['clearance'][$k2]])->field('name')->find()['name'];
                }
                #配送说明====
                #配送到门=
                #可送区域-按行政区
                $kesong_area_all = '';
                if(!empty($lines[$k]['content']['kesong_area1'][$k2])){
                    $kesong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['kesong_area1'][$k2]])->field('code_name')->find()['code_name'];
                    $kesong_area_all .= $kesong_area;
                }
                if(!empty($lines[$k]['content']['kesong_area2'][$k2])){
                    $kesong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['kesong_area2'][$k2]])->field('code_name')->find()['code_name'];
                    $kesong_area_all .= ' '.$kesong_area;
                }
                if(!empty($lines[$k]['content']['kesong_area3'][$k2])){
                    $kesong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['kesong_area3'][$k2]])->field('code_name')->find()['code_name'];
                    $kesong_area_all .= ' '.$kesong_area;
                }
                $lines[$k]['content']['kesong_area_all'][$k2] = $kesong_area_all;#可送区域整合
                #不送区域=
                #不送区域-按行政区
                $busong_area_all = '';
                if(!empty($lines[$k]['content']['busong_area1'][$k2])){
                    $busong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['busong_area1'][$k2]])->field('code_name')->find()['code_name'];
                    $busong_area_all .= $busong_area;
                }
                if(!empty($lines[$k]['content']['busong_area2'][$k2])){
                    $busong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['busong_area2'][$k2]])->field('code_name')->find()['code_name'];
                    $busong_area_all .= ' '.$busong_area;
                }
                if(!empty($lines[$k]['content']['busong_area3'][$k2])){
                    $busong_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['busong_area3'][$k2]])->field('code_name')->find()['code_name'];
                    $busong_area_all .= ' '.$busong_area;
                }
                $lines[$k]['content']['busong_area_all'][$k2] = $busong_area_all;#不可送区域整合
                #定点自提=
                #定点地址区域
                $dingdian_area_all = '';
                if(!empty($lines[$k]['content']['dingdian_area1'][$k2])){
                    $dingdian_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['dingdian_area1'][$k2]])->field('code_name')->find()['code_name'];
                    $dingdian_area_all .= $dingdian_area;
                }
                if(!empty($lines[$k]['content']['dingdian_area2'][$k2])){
                    $dingdian_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['dingdian_area2'][$k2]])->field('code_name')->find()['code_name'];
                    $dingdian_area_all .= $dingdian_area;
                }
                if(!empty($lines[$k]['content']['dingdian_area3'][$k2])){
                    $dingdian_area = Db::name('centralize_adminstrative_area')->where(['country_id' => $lines[$k]['end_country'], 'id' => $lines[$k]['content']['dingdian_area3'][$k2]])->field('code_name')->find()['code_name'];
                    $dingdian_area_all .= $dingdian_area;
                }
                $lines[$k]['content']['dingdian_area_all'][$k2] = $dingdian_area_all;#定点区域整合
                #税费说明====
                #已含税费=
                foreach($lines[$k]['content']['shuifei_name'][$k2] as $k3=>$v3){
                    if(!empty($lines[$k]['content']['shuifei_unit'][$k2][$k3])){
                        #税费计量单位（名称）
                        $lines[$k]['content']['shuifei_unit'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines[$k]['content']['shuifei_unit'][$k2][$k3]])->field('code_name')->find()['code_name'];
                    }
                    if(!empty($lines[$k]['content']['shuifei_currency'][$k2][$k3])){
                        #税费计量币种（名称）
                        $lines[$k]['content']['shuifei_currency'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines[$k]['content']['shuifei_currency'][$k2][$k3]])->field('code_name')->find()['code_name'];
                    }
                }
                #未含税费=
                foreach($lines[$k]['content']['noshuifei_name'][$k2] as $k3=>$v3){
                    if(!empty($lines[$k]['content']['noshuifei_unit'][$k2][$k3])){
                        #税费计量单位（名称）
                        $lines[$k]['content']['noshuifei_unit'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines[$k]['content']['noshuifei_unit'][$k2][$k3]])->field('code_name')->find()['code_name'];
                    }
                    if(!empty($lines[$k]['content']['noshuifei_currency'][$k2][$k3])){
                        #税费计量币种（名称）
                        $lines[$k]['content']['noshuifei_currency'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines[$k]['content']['noshuifei_currency'][$k2][$k3]])->field('code_name')->find()['code_name'];
                    }
                }
                #潜在税费=
                foreach($lines[$k]['content']['maybeshuifei_name'][$k2] as $k3=>$v3){
                    if(!empty($lines[$k]['content']['maybeshuifei_unit'][$k2][$k3])){
                        #税费计量单位（名称）
                        $lines[$k]['content']['maybeshuifei_unit'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines[$k]['content']['maybeshuifei_unit'][$k2][$k3]])->field('code_name')->find()['code_name'];
                    }
                    if(!empty($lines[$k]['content']['maybeshuifei_currency'][$k2][$k3])){
                        #税费计量币种（名称）
                        $lines[$k]['content']['maybeshuifei_currency'][$k2][$k3] = Db::name('unit')->where(['code_value'=>$lines[$k]['content']['maybeshuifei_currency'][$k2][$k3]])->field('code_name')->find()['code_name'];
                    }
                }
                #参考时效====
                #节点名称
                if(!empty($lines[$k]['content']['shixiao_type'][$k2])){
                    $lines[$k]['content']['shixiao_type'][$k2] = Db::name('centralize_lines_referentime')->where(['id'=>$lines[$k]['content']['shixiao_type'][$k2]])->field('name')->find()['name'];
                }
                #物流查询====
                #物流商户
                if(!empty($lines[$k]['content']['logistics'][$k2])){
                    $lines[$k]['content']['logistics'][$k2] = Db::name('centralize_diycountry_content')->where(['pid'=>6,'id'=>$lines[$k]['content']['logistics'][$k2]])->field('param3')->find()['param3'];
                }
                #申报要求====
                #其他说明====
            }
            $lines[$k]['end_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$lines[$k]['end_country']])->find()['param2'];

            #线路信息同步
            $lines_arr = ['id'=>$v['id'],'shop_id'=>$v['company_id'],'name'=>$v['name'],'code'=>$v['code'],'channel_name'=>$lines[$k]['channel_name'],'transport_name'=>$lines[$k]['transport_name'],'transport_union'=>$lines[$k]['transport_union_info'],'shipping_date'=>$lines[$k]['shipping_date'],'deliver_info'=>$lines[$k]['deliver_info'],'start_country'=>$lines[$k]['start_country'],'end_country'=>$lines[$k]['end_country'],'createtime'=>$lines[$k]['createtime']];

            foreach($lines[$k]['content']['procategory'] as $k2=>$v2){
                $lines_content = log_train_dataset_logistics($k2,$lines[$k]['content'],$lines[$k]);

                #线路货物类别信息同步
                foreach($lines_content as $k3=>$v3){
                    $lines_arr['procategory'] = $v3['procategory'];
                    $lines_arr['chicategory'] = $v3['chicategory'];
                    $lines_arr['mini_cost'] = $v3['mini_cost'];
                    $lines_arr['mini_num'] = $v3['mini_num'];
                    $lines_arr['mini_cost'] = $v3['mini_cost'];
                    $lines_arr['minicost_unit'] = $v3['minicost_unit'];
                    $lines_arr['rate'] = $v3['rate'];
                    $lines_arr['fenpao'] = $v3['fenpao'];
                    $lines_arr['overweight'] = $v3['overweight'];
                    $lines_arr['overlong'] = $v3['overlong'];
                    $lines_arr['overother'] = $v3['overother'];
                    $lines_arr['clearance'] = $v3['clearance'];
                    $lines_arr['kesong_area_info'] = $v3['kesong_area_info'];
                    $lines_arr['busong_area_info'] = $v3['busong_area_info'];
                    $lines_arr['peisong_remark'] = $v3['peisong_remark'];
                    $lines_arr['dingdian_info'] = $v3['dingdian_info'];
                    $lines_arr['busong_info'] = $v3['busong_remark'];
                    $lines_arr['shuifei_info'] = $v3['shuifei_info'];
                    $lines_arr['noshuifei_info'] = $v3['noshuifei_info'];
                    $lines_arr['maybeshuifei_info'] = $v3['maybeshuifei_info'];
                    $lines_arr['shixiao_info'] = $v3['shixiao_info'];
                    $lines_arr['logistics_info'] = $v3['logistics_info'];
                    $lines_arr['declare_info'] = $v3['declare_info'];
                    $lines_arr['other_remark'] = $v3['other_remark'];
                    $lines_arr['billing_range'] = $v3['billing_range'];

                    $result = send_data_tcp('sync_info@:@logistics@@@' . json_encode($lines_arr, true));

                    #记录同步数量
                    $send_num +=1;
                    if($result=='True' || $result==True){
                        $success_num += 1;
                    }else{
                        $fail_num += 1;
                    }
                }
            }

            Db::name('centralize_lines')->where(['id'=>$v['id']])->update(['is_add_dataset'=>1]);
        }
    }
    elseif($data['type']==3){
        #会员
        $member = Db::name('website_user')->where(['is_add_dataset'=>0])->limit($data['sync_num'])->select();
        foreach($member as $k2=>$v2) {
            $result = send_data_tcp('sync_info@:@member@@@' . json_encode($v2, true));

            #记录同步数量
            $send_num +=1;
            if($result=='True' || $result==True){
                $success_num += 1;
            }else{
                $fail_num += 1;
            }

            Db::name('website_user')->where(['id' => $v2['id']])->update(['is_add_dataset' => 1]);
        }
    }

    log_sync($data,$send_num,$success_num,$fail_num);
    return 1;
}

#立即同步文档信息至本地电脑
function now_sync_file_to_local($data,$is_user=0){
    #获取同步储存方向
    $store_direction = Db::name('train_setting')->where(['type'=>1])->find();

    if($store_direction['direction_type']==1){
        #本地服务
        $arr = [];
        foreach($data['file_path'] as $v){
            array_push($arr,['product_id'=>$data['knowledge_id'],'seller_id'=>$data['cid'],'file_path'=>'https://rte.gogo198.cn'.$v]);
        }

        $result = send_data_tcp('sync_info@:@files@@@' . json_encode($arr, true));
        if($result=='True' || $result==True){
            return 1;
        }
        else{
            return -1;
        }
    }
    elseif($store_direction['direction_type']==2){
        #云端服务
        #Array ( [id] => 3 [cid] => 19 [type] => 2 [knowledge_id] => 0 [file_path] => Array ( [0] => /uploads/knowledge_files/20250723/6880aa97634ae.docx ) [status] => 1 [is_add_dataset] => 0 [createtime] => 1753262776 )

//        foreach($data['file_path'] as $k=>$v){
//            httpRequest('https://rte.gogo198.cn/upload.php',['is_backend_operation'=>1,'file_path'=>$v,'is_user'=>$is_user],[],1);
//        }

        $post_data = ['file_path'=>$data['file_path'],'is_user'=>$is_user,'knowledge_id'=>$data['id'],'cid'=>0];
        $result = httpRequest('https://rte.gogo198.cn/upload.php',$post_data,[],1);

        $response = json_decode($result, true);
        if ($response && $response['success']) {
            $success_count = $response['data']['success_count'];
            Db::name('experience_knowledge_list')->where(['id'=>$data['id']])->update(['remark'=>"批量文件处理完成: 成功 {$success_count}/{$response['data']['total_files']} 个文件"]);
//            error_log();
        }else{
            Db::name('experience_knowledge_list')->where(['id'=>$data['id']])->update(['remark'=>json_encode($response,true)]);
        }

        return $success_count > 0 ? 1 : -1;

//        return 1;
    }
}

#立即同步缓存热门商品至本地电脑
function now_sync_cache_product_to_local($data){
    $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    #查询需缓存的商品
    $goods_ids = explode(',',$data['goods_id']);
    $arr = [];
    foreach($goods_ids as $k=>$v){
        $this_goods = Db::connect($config)->name('goods')->where(['goods_id'=>$v])->field('goods_id,goods_name,goods_price')->find();
        array_push($arr,['product_id'=>$v,'seller_id'=>$data['cid'],'goods_name'=>$this_goods['goods_name'],'goods_link'=>'https://www.gogo198.cn/goods-'.$v.'.html']);
    }

    $result = send_data_tcp('sync_info@:@hot_products@@@' . json_encode($arr, true));
    if($result=='True' || $result==True){
        return 1;
    }
    else{
        return -1;
    }
}

#立即同步缓存用户行为至本地电脑
function sync_userbehavior_to_local($data){

    foreach($data as $k=>$v){
        $result = send_data_tcp('sync_info@:@user_preferences@@@' . json_encode(['redis_key'=>$v['redis_key'],'event'=>$v['event']], true));
        if($result=='True' || $result==True){
//            return 1;
            Db::name('user_behavior_record')->where(['is_add_dataset'=>0])->update(['is_add_dataset'=>1]);
        }
        else{
//            return -1;
            Db::name('user_behavior_record')->where(['is_add_dataset'=>0])->update(['is_add_dataset'=>-1]);
        }
    }
}

#数据同步日志
function log_sync($data,$send_num,$success_num,$fail_num){
    Db::name('sync_logs')->insert(['pid'=>$data['id'],'send_num'=>$send_num,'success_num'=>$success_num,'fail_num'=>$fail_num,'createtime'=>time()]);
}

# 线路详细内容解析
function log_train_dataset_logistics($k2,$content=[],$lines=[]){
    $line_procategory_arr = [];
    foreach($lines['content']['procategory'] as $k=>$v){
        #货物类别、适用货物====
        $line_procategory_arr[$k]['procategory'] = $v;
        $line_procategory_arr[$k]['chicategory'] = $lines['content']['chicategory'][$k];
        #计费标准====
        #最低消费=
        $line_procategory_arr[$k]['mini_cost'] = '';
        $line_procategory_arr[$k]['mini_num'] = '';
        $line_procategory_arr[$k]['minicost_unit'] = '';
        if($lines['content']['mini_cost'][$k]=='无最低'){
            $line_procategory_arr[$k]['mini_cost'] = $lines['content']['mini_cost'][$k];
        }
        elseif($lines['content']['mini_cost'][$k]=='有最低'){
            $minicost_unit = Db::name('unit')->where(['code_value'=>$lines['content']['minicost_unit'][$k]])->field('code_name')->find()['code_name'];

            $line_procategory_arr[$k]['mini_num'] = $lines['content']['mini_num'][$k];
            $line_procategory_arr[$k]['minicost_unit'] = $minicost_unit;
        }
        #体积算法
        $line_procategory_arr[$k]['rate'] = '';
        if(!empty($lines['content']['rate'][$k])) {
            $line_procategory_arr[$k]['rate'] = $lines['content']['rate'][$k];
        }
        #分泡方式
        $line_procategory_arr[$k]['fenpao'] = '';
        if(!empty($lines['content']['fenpao'][$k])){
            $line_procategory_arr[$k]['fenpao'] = $lines['content']['fenpao'][$k];
        }
        #超限条款
        $line_procategory_arr[$k]['overweight'] = '';
        if(!empty($lines['content']['overweight'][$k])){
            $line_procategory_arr[$k]['overweight'] = '【'.$lines['content']['overweight'][$k].'】'.$lines['content']['overweight_remark'][$k];
        }
        #超长限制
        $line_procategory_arr[$k]['overlong'] = '';
        if(!empty($lines['content']['overlong'][$k])){
            $line_procategory_arr[$k]['overlong'] = '【'.$lines['content']['overlong'][$k].'】'.$lines['content']['overlong_remark'][$k];
        }
        #其他限制
        $line_procategory_arr[$k]['overother'] = '';
        if(!empty($lines['content']['overother'][$k])){
            $line_procategory_arr[$k]['overother'] = '【'.$lines['content']['overother'][$k].'】'.$lines['content']['overother_remark'][$k];
        }
        #清关说明
        $line_procategory_arr[$k]['clearance'] = '';
        if(!empty($lines['content']['clearance'][$k])){
            $line_procategory_arr[$k]['clearance'] = $lines['content']['clearance'][$k];
        }
        #配送说明-配送到门-可送区域
        $line_procategory_arr[$k]['kesong_area_info'] = '';
        if(!empty($lines['content']['kesong_area_all'][$k])){
            $line_procategory_arr[$k]['kesong_area_info'] = '可送区域：'.$lines['content']['kesong_area_all'][$k];
            if(!empty($lines['content']['kesong_post'][$k])){
                $line_procategory_arr[$k]['kesong_area_info'] .= '，可送邮编：（'.$lines['content']['kesong_post'][$k].'）';
            }
            if(!empty($lines['content']['diy_kesong'][$k])){
                $line_procategory_arr[$k]['kesong_area_info'] .= '，可送详细区域：“'.$lines['content']['diy_kesong'][$k].'”';
            }
        }
        #配送说明-配送到门-不送区域
        $line_procategory_arr[$k]['busong_area_info'] = '';
        if(!empty($lines['content']['busong_area_all'][$k])){
            $line_procategory_arr[$k]['busong_area_info'] = '不送区域：'.$lines['content']['busong_area_all'][$k];
            if(!empty($lines['content']['busong_post'][$k])){
                $line_procategory_arr[$k]['busong_area_info'] .= '，不送邮编：“'.$lines['content']['busong_post'][$k].'”';
            }
            if(!empty($lines['content']['diy_busong'][$k])){
                $line_procategory_arr[$k]['busong_area_info'] .= '，不送详细区域：'.$lines['content']['diy_busong'][$k];
            }
        }
        #配送说明-配送到门-备注说明
        $line_procategory_arr[$k]['peisong_remark'] = '';
        if(!empty($lines['content']['peisong_remark'][$k])) {
            $line_procategory_arr[$k]['peisong_remark'] = $lines['content']['peisong_remark'][$k];
        }
        #配送说明-定点自提
        $line_procategory_arr[$k]['dingdian_info'] = '';
        if(!empty($lines['content']['dingdian_name'][$k])){
            $line_procategory_arr[$k]['dingdian_info'] = '自提定点名称：'.$lines['content']['dingdian_name'][$k];
            if(!empty($lines['content']['dingdian_area_all'][$k])){
                $line_procategory_arr[$k]['dingdian_info'] .= '，自提定点地址：'.$lines['content']['dingdian_area_all'][$k].$lines['content']['dingdian_address'][$k];
            }
        }
        #配送说明-不予配送
        $line_procategory_arr[$k]['busong_remark'] = '';
        if(!empty($lines['content']['busong_remark'][$k])){
            $line_procategory_arr[$k]['busong_remark'] = $lines['content']['busong_remark'][$k];
        }
        #税费说明-已含税费
        $line_procategory_arr[$k]['shuifei_info'] = '';
        foreach($lines['content']['shuifei_name'][$k] as $k2=>$v2){
            if(!empty($lines['content']['shuifei_name'][$k][$k2])){
                $line_procategory_arr[$k]['shuifei_info'] .= '第'.($k2+1).'点：（税费名称：'.$lines['content']['shuifei_name'][$k][$k2].'，摘要说明：'.$lines['content']['shuifei_intro'][$k][$k2].'，税费价格：'.$lines['content']['shuifei_currency'][$k][$k2].$lines['content']['shuifei_price'][$k][$k2].'/'.$lines['content']['shuifei_unit'][$k][$k2].'，税费说明：'.$lines['content']['shuifei_remark'][$k][$k2].'）；';
            }
        }

        #税费说明-未含税费
        $line_procategory_arr[$k]['noshuifei_info'] = '';
        foreach($lines['content']['noshuifei_name'][$k] as $k2=>$v2){
            if(!empty($lines['content']['noshuifei_name'][$k][$k2])){
                $line_procategory_arr[$k]['noshuifei_info'] .= '第'.($k2+1).'点：（税费名称：'.$lines['content']['noshuifei_name'][$k][$k2].'，摘要说明：'.$lines['content']['noshuifei_intro'][$k][$k2].'，税费价格：'.$lines['content']['noshuifei_currency'][$k][$k2].$lines['content']['noshuifei_price'][$k][$k2].'/'.$lines['content']['noshuifei_unit'][$k][$k2].'，税费说明：'.$lines['content']['noshuifei_remark'][$k][$k2].'）；';
            }
        }

        #税费说明-潜在税费
        $line_procategory_arr[$k]['maybeshuifei_info'] = '';
        foreach($lines['content']['maybeshuifei_name'][$k] as $k2=>$v2){
            if(!empty($lines['content']['maybeshuifei_name'][$k][$k2])){
                $line_procategory_arr[$k]['maybeshuifei_info'] .= '第'.($k2+1).'点：（税费名称：'.$lines['content']['maybeshuifei_name'][$k][$k2].'，摘要说明：'.$lines['content']['maybeshuifei_intro'][$k][$k2].'，税费价格：'.$lines['content']['maybeshuifei_currency'][$k][$k2].$lines['content']['maybeshuifei_price'][$k][$k2].'/'.$lines['content']['maybeshuifei_unit'][$k][$k2].'，税费说明：'.$lines['content']['maybeshuifei_remark'][$k][$k2].'）；';
            }
        }
        #参考时效
        $line_procategory_arr[$k]['shixiao_info'] = '';
        if(!empty($lines['content']['shixiao_type'][$k])){
            $day_type = '';
            if($lines['content']['shixiao_daytype'][$k]==1){
                $day_type = '工作天';
            }
            elseif($lines['content']['shixiao_daytype'][$k]==2){
                $day_type = '自然日';
            }
            else{
                $day_type = $lines['content']['shixiao_daytype'][$k];
            }
            $line_procategory_arr[$k]['shixiao_info'] = '参考时效：'.$lines['content']['shixiao_type'][$k].$lines['content']['shixiao_num'][$k].$day_type;
        }
        #物流查询
        $line_procategory_arr[$k]['logistics_info'] = '';
        if(!empty($lines['content']['logistics'][$k])){
            $line_procategory_arr[$k]['logistics_info'] = '物流商户：'.$lines['content']['logistics'][$k].'，物流查询网址：'.$lines['content']['logistics_website'][$k];
        }
        #申报要求-品名要求
        $line_procategory_arr[$k]['declare_info'] = '';
        if(!empty($lines['content']['nameReq'][$k])){
            $line_procategory_arr[$k]['declare_info'] .= '品名要求：（'.$lines['content']['nameReq'][$k].'）';
        }
        #申报要求-价值要求
        if(!empty($lines['content']['valueReq'][$k])){
            $line_procategory_arr[$k]['declare_info'] .= '，价值要求：（'.$lines['content']['valueReq'][$k].'）';
        }
        #申报要求-其他要求
        if(!empty($lines['content']['valueReq'][$k])){
            $line_procategory_arr[$k]['declare_info'] .= '，其他要求：（'.$lines['content']['otherReq'][$k].'）';
        }
        #其他说明
        $line_procategory_arr[$k]['other_remark'] = '';
        if(!empty(trim($lines['content']['other_remark'][$k]))){
            $line_procategory_arr[$k]['other_remark'] = trim($lines['content']['other_remark'][$k]);
        }
        #计费区间=
        $line_procategory_arr[$k]['billing_range'] = '';
        foreach($lines['content']['qj1'][$k] as $k2=>$v2){
            $line_procategory_arr[$k]['billing_range'] .= '计费区间：自'.$v2.$lines['content']['unit'][$k][$k2].'至';
            if($lines['content']['qj2_method'][$k][$k2]=='数值'){
                $line_procategory_arr[$k]['billing_range'] .= $lines['content']['qj2'][$k][$k2].$lines['content']['unit'][$k][$k2].'，';
            }
            elseif($lines['content']['qj2_method'][$k][$k2]=='以上'){
                $line_procategory_arr[$k]['billing_range'] .= $lines['content']['qj2_method'][$k][$k2].'，';
            }
            #计费进阶
            $line_procategory_arr[$k]['billing_range'] .= '计费进阶：'.$lines['content']['jinjie'][$k][$k2].$lines['content']['unit'][$k][$k2].'，';

            #计费方式
            foreach($lines['content']['jf_method'][$k][$k2] as $k3=>$v3){
                $line_procategory_arr[$k]['billing_range'] .= '计费方式：（（'.$v3.'）';
                if($v3=='首续计费'){
                    $line_procategory_arr[$k]['billing_range'] .= '首重：'.$lines['content']['shouzhong'][$k][$k2][$k3].$lines['content']['unit'][$k][$k2].'/'.$lines['content']['currency'][$k][$k2][$k3].$lines['content']['shouzhong_money'][$k][$k2][$k3].'，';
                    $line_procategory_arr[$k]['billing_range'] .= '续重：'.$lines['content']['xuzhong'][$k][$k2][$k3].$lines['content']['unit'][$k][$k2].'/'.$lines['content']['currency'][$k][$k2][$k3].$lines['content']['xuzhong_money'][$k][$k2][$k3].'）。';
                }
                elseif($v3=='按量计费'){
                    $line_procategory_arr[$k]['billing_range'] .= $lines['content']['anliang'][$k][$k2][$k3].$lines['content']['unit'][$k][$k2].'/'.$lines['content']['currency'][$k][$k2][$k3].$lines['content']['anliang_money'][$k][$k2][$k3].'）。';
                }
                elseif($v3=='分段计费'){
                    foreach($lines['content']['fenduan_num1'][$k][$k2][$k3] as $k4=>$v4){
                        $line_procategory_arr[$k]['billing_range'] .= $v4.$lines['content']['unit'][$k][$k2].' 至 ';
                        if($lines['content']['fenduan_method'][$k][$k2][$k3][$k4]=='数值'){
                            $line_procategory_arr[$k]['billing_range'] .= $lines['content']['fenduan_num2'][$k][$k2][$k3][$k4].$lines['content']['unit'][$k][$k2].' '.$lines['content']['currency'][$k][$k2][$k3][$k4].$lines['content']['fenduan_money'][$k][$k2][$k3][$k4].'）。';
                        }
                        elseif($lines['content']['fenduan_method'][$k][$k2][$k3][$k4]=='以上'){
                            $line_procategory_arr[$k]['billing_range'] .= '以上 '.$lines['content']['currency'][$k][$k2][$k3][$k4].$lines['content']['fenduan_money'][$k][$k2][$k3][$k4].'）。';
                        }
                    }
                }
            }
        }
    }

    return $line_procategory_arr;
}

#系统重新对数据进行重头开始同步：把is_add_dataset=1改为0
function restart_sync($type){
    $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    if($type==0){
        #商品
        Db::connect($config)->name('goods')->where(['is_add_dataset'=>1])->update(['is_add_dataset'=>0]);
    }
    elseif($type==1){
        #订单
        Db::name('website_order_list')->where(['is_add_dataset'=>1])->update(['is_add_dataset'=>0]);
    }
    elseif($type==2){
        #物流
        Db::name('centralize_lines')->where(['is_add_dataset'=>1])->update(['is_add_dataset'=>0]);
    }
    elseif($type==3){
        #会员
        Db::name('website_user')->where(['is_add_dataset'=>1])->update(['is_add_dataset'=>0]);
    }
}

//向本地电脑通过frp的tcp协议发起请求
function send_data_tcp($data, $address = '127.0.0.1', $port = 7001) {
    // 创建 TCP 套接字
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if ($socket === false) {
        Log::info("创建套接字失败: " . socket_strerror(socket_last_error()) . PHP_EOL);
//        echo "创建套接字失败: " . socket_strerror(socket_last_error()) . PHP_EOL;
        return;
    }

    // 连接到服务器
    $result = socket_connect($socket, $address, $port);
    if ($result === false) {
        Log::info("连接失败: " . socket_strerror(socket_last_error($socket)) . PHP_EOL);
//        echo "连接失败: " . socket_strerror(socket_last_error($socket)) . PHP_EOL;
        socket_close($socket);
        return;
    }

    // 发送数据
    $bytes = socket_write($socket, $data, strlen($data));
    if ($bytes === false) {
        Log::info("连接失败: " . socket_strerror(socket_last_error($socket)) . PHP_EOL);
//        echo "发送失败: " . socket_strerror(socket_last_error($socket)) . PHP_EOL;
        socket_close($socket);
        return;
    }

    // 接收响应
    $response = socket_read($socket, 1024);
    if ($response === false) {
        Log::info("连接失败: " . socket_strerror(socket_last_error($socket)) . PHP_EOL);
//        echo "接收失败: " . socket_strerror(socket_last_error($socket)) . PHP_EOL;
    } else {
        Log::info("Server response: " . trim($response) . PHP_EOL);
        return trim($response);
//        echo "Server response: " . trim($response) . PHP_EOL;
    }

    // 关闭连接
    socket_close($socket);
}

//开始训练（tcp）（废弃）
function start_lora($id){
    $package = Db::name('train_dataset_package')->where(['id'=>$id])->find();
    Db::name('train_dataset_package')->where(['id'=>$id])->update(['traintime'=>time()]);#记录开始训练时间

    $dataset_ids = explode(',',$package['dataset_ids']);
    $dataset_arr = [];
    foreach($dataset_ids as $k=>$v){
        $dataset = Db::name('train_dataset')->where(['id'=>$v])->find();
        array_push($dataset_arr,['instruction'=>$dataset['question'],'input'=>$dataset['answer'],'output'=>$dataset['real_text']]);
    }

    // 通过FRP的tcp网络协议
    $result = send_data_tcp('lora_webhook:'.$id.'@@@'.json_encode($dataset_arr,true));

    return $result;
}

#=====================================================================================更新指定数据库下的数据表START

//同步指定数据库下的数据表数据和结构到指定数据库下的数据表
function sync_data_to_database($sync_id){
    #当前需要同步的信息
    $sync_info = Db::name('ai_syncdata')->where(['id'=>$sync_id])->find();

    #本次需要同步的数据表与相应数据库
    $table_arr = [];

    $sync_info['tables'] = explode(',',$sync_info['tables']);
    foreach($sync_info['tables'] as $v){
        $table = Db::name('ai_sync_datatable')->where(['id'=>$v])->find();
        array_push($table_arr,[
            'id'=>$table['id'],
            'tablename'=>$table['realname'],
            'database'=>$table['database']
        ]);
    }

    // 记录开始时间
    $startTime = microtime(true);

    try {
        // 同步所有配置的表
        foreach ($table_arr as $k => $v){
            $target_table = $v['tablename'];
            if($v['database']=='lrw'){
                $target_table = 'ims_'.$v['tablename'];
            }
            syncTable($v['database'], $v['tablename'], $target_table, $sync_id);
        }

        $timeUsed = round(microtime(true) - $startTime, 2);

        $msg = "同步完成！耗时 {$timeUsed} 秒";
        sync_log($sync_id,$msg);

        return [
            'code' => 1,
            'msg'  => $msg,
//            'data' => Cache::get('sync_log', [])
        ];
    } catch (\Exception $e) {
        sync_log($sync_id,'同步失败: ' . $e->getMessage());
        return [
            'code' => 0,
            'msg'  => '同步失败: ' . $e->getMessage()
        ];
    }
}

//同步单个表
function syncTable($sourceDb, $sourceTable, $targetTable, $sync_id)
{
    #业务数据库
    $shop_config =  [
        //数据库类型
        'type' => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'shop',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix' => '',
    ];

    #商城商品信息库
    $lrw_config = [
        //数据库类型
        'type' => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix' => '',
    ];

    #AI信息库
    $ai_config = [
        //数据库类型
        'type' => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'ai',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix' => '',
    ];

    $sourceDb2 = [];
    if($sourceDb == 'shop'){
        $sourceDb2 = &$shop_config;
    }
    elseif($sourceDb == 'lrw'){
        $sourceDb2 = &$lrw_config;
    }

    // 获取数据库连接
    $dbSource = Db::connect($sourceDb2);
    $dbTarget = Db::connect($ai_config);

    // 记录日志
    sync_log($sync_id,"开始同步 {$sourceDb}.{$sourceTable} => DB_AI.{$targetTable}");

    // 1. 同步表结构
    syncTableStructure($dbSource, $dbTarget, $sourceTable, $targetTable, $sync_id);

    // 2. 同步数据
    syncTableData($dbSource, $dbTarget, $sourceTable, $targetTable, $sync_id);

    sync_log($sync_id,"完成同步 {$sourceDb}.{$sourceTable}");
}

//同步表结构
function syncTableStructure($dbSource, $dbTarget, $sourceTable, $targetTable, $sync_id)
{
    // 获取源表字段信息
    $sourceFields = $dbSource->getTableFields($sourceTable);
    $sourceFields = array_flip($sourceFields);

    // 获取目标表字段信息
    $targetFields = getTableFieldsDirectly($dbTarget, $targetTable);
    $targetFields = array_flip($targetFields);

    // 查找新增字段
    $newFields = array_diff_key($sourceFields, $targetFields);

    if (empty($newFields)) {
        sync_log($sync_id,"表结构无变化");
        return;
    }

    // 获取源表字段详细信息
    $sourceFieldInfo = $dbSource->getFields($sourceTable);

    // 构建ALTER语句
    $alterSql = "ALTER TABLE `{$targetTable}` ";
    $addColumns = [];

    foreach ($newFields as $field => $index) {
        $field = strtolower($field);
        $info = $sourceFieldInfo[$field];

        $columnSql = "ADD COLUMN `{$field}` {$info['type']}";

        if ($info['notnull']) {
            $columnSql .= " NOT NULL";
        }

        if ($info['default'] !== null) {
            $default = is_string($info['default']) ? "'{$info['default']}'" : $info['default'];
            $columnSql .= " DEFAULT {$default}";
        }

        if (!empty($info['comment'])) {
            $columnSql .= " COMMENT '{$info['comment']}'";
        }

        $addColumns[] = $columnSql;
    }

    $alterSql .= implode(", ", $addColumns);

    // 执行表结构变更
    $dbTarget->execute($alterSql);

    if (method_exists($dbTarget, 'clearTableInfo')) {
        $dbTarget->clearTableInfo($targetTable); // ThinkPHP 内置方法
    }

    sync_log($sync_id,"新增字段: " . implode(', ', array_keys($newFields)));
}

//同步表数据
function syncTableData($dbSource, $dbTarget, $sourceTable, $targetTable, $sync_id)
{
    // 获取源表和目标表的主键
    $sourcePk = getPrimaryKey($dbSource, $sourceTable);
    $targetPk = getPrimaryKey($dbTarget, $targetTable);

    // 获取主键字段（以源表为主）
    $pk = $sourcePk;

    // 获取最后一次同步时间
    $lastSyncKey = "last_sync_{$sourceTable}";
    $lastSyncTime = session($lastSyncKey, 0);

    sync_log($sync_id,"上次同步时间: " . ($lastSyncTime ? date('Y-m-d H:i:s', $lastSyncTime) : '从未同步'));

    // 查询需要同步的数据（新增或更新）
    $query = $dbSource->name($sourceTable);

    // 获取目标表最新字段列表（清除缓存后）
    $targetFields = getTableFieldsDirectly($dbTarget, $targetTable);
    $targetFields = array_flip($targetFields);

    // 分批处理
    $page = 1;
    $pageSize = 1000;
    $totalSynced = 0;
    $maxUpdateTime = $lastSyncTime;

    do {
        // 获取当前批次数据
        $dataList = $query->page($page, $pageSize)->select();

        if (empty($dataList)) break;

        // 处理每一条数据
        foreach ($dataList as $data) {
            // 获取实际主键字段
            $pkValue = null;
            if ($pk && isset($data[$pk])) {
                $pkValue = $data[$pk];
            }

            // 过滤目标表不存在的字段（关键修复）
            $filteredData = array_intersect_key($data, $targetFields);

            try {
                if ($pkValue) {
                    $exists = $dbTarget->name($targetTable)->where($pk, $pkValue)->find();

                    if ($exists) {
                        $dbTarget->name($targetTable)->where($pk, $data[$pk])->update($filteredData);
                    } else {
                        $dbTarget->name($targetTable)->insert($filteredData);
                    }
                } else {
                    // 没有主键时直接插入
                    if (isset($data['id'])) unset($data['id']);
                    $dbTarget->name($targetTable)->insert($data);
                }
            } catch (\Exception $e) {
                sync_log($sync_id,"数据同步错误: " . $e->getMessage() . " | 数据: " . json_encode($data));
            }
        }

        $batchCount = count($dataList);
        $totalSynced += $batchCount;
        sync_log($sync_id,"已同步批次 #{$page}: {$batchCount} 条记录");

        $page++;
    } while (count($dataList) === $pageSize);

    // 更新最后同步时间
    if ($maxUpdateTime > $lastSyncTime) {
        session($lastSyncKey, $maxUpdateTime);
    }

    sync_log($sync_id,"数据同步完成: 共 {$totalSynced} 条记录");
}

//获取表的主键
function getPrimaryKey($db, $table) {
    try {
        $pk = $db->getPk($table);
        if ($pk && is_string($pk)) return $pk;

        // 手动查询表结构获取主键
        $fields = $db->getFields($table);
        foreach ($fields as $field => $info) {
            if ($info['primary']) {
                return $field;
            }
        }
    } catch (\Exception $e) {
        // 错误处理
    }

    // 最后尝试常见主键名称
    $commonKeys = ['id', $table.'_id', 'uid', 'userid','order_id','goods_id','sku_id'];
    foreach ($commonKeys as $key) {
        if (isset($fields[$key])) return $key;
    }

    return null; // 明确返回null表示未找到
}

//记录同步日志
function sync_log($sync_id,$message)
{
    $log = session('sync_log', []);
    $log[] = date('[Y-m-d H:i:s]') . ' ' . $message;
    session('sync_log', $log);

    Db::name('ai_syncdata_log')->insert([
        'sync_id'=>$sync_id,
        'event'=>$message
    ]);
}

// 新增辅助函数
function getTableFieldsDirectly($db, $table) {
    $result = $db->query("SHOW COLUMNS FROM `{$table}`");
    return array_column($result, 'Field');
}

#=====================================================================================更新指定数据库下的数据表END

#=====================================================================================创建商品小程序主题海报图START
// 创建圆角图片函数
function create_rounded_image($image, $radius) {
    $width = imagesx($image);
    $height = imagesy($image);

    // 创建新图像
    $rounded = imagecreatetruecolor($width, $height);
    imagealphablending($rounded, false);
    $transparent = imagecolorallocatealpha($rounded, 0, 0, 0, 127);
    imagefill($rounded, 0, 0, $transparent);
    imagesavealpha($rounded, true);

    // 创建圆角遮罩
    $mask = imagecreatetruecolor($width, $height);
    $black = imagecolorallocate($mask, 0, 0, 0);
    $white = imagecolorallocate($mask, 255, 255, 255);
    imagefill($mask, 0, 0, $black);

    // 绘制圆角矩形
    imagefilledrectangle($mask, $radius, 0, $width - $radius - 1, $height - 1, $white);
    imagefilledrectangle($mask, 0, $radius, $width - 1, $height - $radius - 1, $white);
    imagefilledellipse($mask, $radius, $radius, $radius * 2, $radius * 2, $white);
    imagefilledellipse($mask, $width - $radius - 1, $radius, $radius * 2, $radius * 2, $white);
    imagefilledellipse($mask, $radius, $height - $radius - 1, $radius * 2, $radius * 2, $white);
    imagefilledellipse($mask, $width - $radius - 1, $height - $radius - 1, $radius * 2, $radius * 2, $white);

    // 应用遮罩
    for($x = 0; $x < $width; $x++) {
        for($y = 0; $y < $height; $y++) {
            $mask_pixel = imagecolorat($mask, $x, $y);
            if($mask_pixel == 0) { // 如果是黑色（遮罩外部）
                imagesetpixel($rounded, $x, $y, $transparent);
            } else {
                $source_pixel = imagecolorat($image, $x, $y);
                imagesetpixel($rounded, $x, $y, $source_pixel);
            }
        }
    }

    imagedestroy($mask);
    return $rounded;
}

// 添加圆形图片创建函数
function create_circular_image($image, $radius) {
    $diameter = $radius * 2;

    // 创建新图像
    $circular = imagecreatetruecolor($diameter, $diameter);
    imagealphablending($circular, false);
    $transparent = imagecolorallocatealpha($circular, 0, 0, 0, 127);
    imagefill($circular, 0, 0, $transparent);
    imagesavealpha($circular, true);

    // 创建圆形遮罩
    $mask = imagecreatetruecolor($diameter, $diameter);
    $black = imagecolorallocate($mask, 0, 0, 0);
    $white = imagecolorallocate($mask, 255, 255, 255);
    imagefill($mask, 0, 0, $black);

    // 绘制圆形
    imagefilledellipse($mask, $radius, $radius, $diameter, $diameter, $white);

    // 调整原图尺寸以匹配圆形
    $resized_image = imagecreatetruecolor($diameter, $diameter);
    imagecopyresampled($resized_image, $image, 0, 0, 0, 0, $diameter, $diameter, imagesx($image), imagesy($image));

    // 应用圆形遮罩
    for($x = 0; $x < $diameter; $x++) {
        for($y = 0; $y < $diameter; $y++) {
            $mask_pixel = imagecolorat($mask, $x, $y);
            if($mask_pixel == 0) { // 如果是黑色（遮罩外部）
                imagesetpixel($circular, $x, $y, $transparent);
            } else {
                $source_pixel = imagecolorat($resized_image, $x, $y);
                imagesetpixel($circular, $x, $y, $source_pixel);
            }
        }
    }

    imagedestroy($mask);
    imagedestroy($resized_image);
    return $circular;
}

if (!function_exists('mb_str_split')) {
    /**
     * 兼容 PHP < 7.4 的 mb_str_split
     * @param string $str
     * @param int $split_length
     * @param string $encoding
     * @return array
     */
    function mb_str_split($str, $split_length = 1, $encoding = 'UTF-8') {
        if ($split_length < 1) {
            throw new InvalidArgumentException('Split length must be >= 1');
        }

        $result = [];
        $len = mb_strlen($str, $encoding);

        for ($i = 0; $i < $len; $i += $split_length) {
            $result[] = mb_substr($str, $i, $split_length, $encoding);
        }

        return $result;
    }
}

#获取商品小程序码
function get_miniprogram($data){
    $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    $time = time();
    $goods_id = &$data['goods_id'];

    #1、获取商品名称、商品主图、币种和最低价
    $goods = Db::connect($config)->name('goods_merchant')->where(['id'=>$goods_id])->field('goods_image,goods_name,goods_currency,cid as shop_id')->find();
    $goods_currency = Db::name('centralize_currency')->where(['id'=>$goods['goods_currency']])->field('currency_symbol_standard')->find();
    $goods_sku = Db::connect($config)->name('goods_sku_merchant')->where(['goods_id'=>$goods_id])->select();
    $low_price = 0;// 最低价
    foreach($goods_sku as $k=>$v){
        $goods_sku[$k]['sku_prices'] = json_decode($v['sku_prices'],true);
        foreach($goods_sku[$k]['sku_prices']['price'] as $k2=>$v2){
            if(empty($low_price)){
                $low_price = $v2;
            }else{
                if($low_price>$v2){
                    $low_price = $v2;
                }
            }
        }
    }
    $true_low_price = $goods_currency['currency_symbol_standard'].' '.$low_price;//最低价和币种

    #2、获取店铺logo和名称
    $shop_name = 'Gogo淘中国';
    $shop_logo = 'https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/679357cc06e93.png';
    if($goods['shop_id']>0){
        $shop_name = Db::name('website_user_company')->where(['id'=>$goods['shop_id']])->field('company')->find()['company'];
        $shop_logo = Db::name('website_basic')->where(['company_id'=>$goods['shop_id'],'company_type'=>0])->value('logo');
        if(empty($shop_logo)){
            $shop_logo = Db::name('website_basic')->where(['company_id'=>$goods['shop_id'],'company_type'=>1])->value('logo');
        }
        $shop_logo = 'https://dtc.gogo198.net'.$shop_logo;
    }

    #3、随机颜色
    $color = Db::name('centralize_diycountry_content')->where(['pid'=>12])->orderRaw('RAND()')->field('param1,param2,param3')->find();

    #4、开始制作海报图
    header("Content-type: text/html; charset=utf-8");

    #4.1、 创建图像
    $height = 1700; //图像高度
    $width = 1000; //图像宽度
    $im = imagecreatetruecolor($width,$height); //创建一个真彩色的图像

    $random_color = imagecolorallocate($im, $color['param1'], $color['param2'], $color['param3']);
    $font_color1 = imagecolorallocate($im, 255, 255, 255);//白
    $font_color2 = imagecolorallocate($im, 206, 0, 2);//红
    $font_color3 = imagecolorallocate($im, 0, 0, 0);//黑
    $font_color4 = imagecolorallocate($im, 106, 106, 106);//灰
    $font_color5 = imagecolorallocate($im, 71, 1, 2);//红
    $shadow_color = imagecolorallocatealpha($im, 0, 0, 0, 60); // 阴影颜色（半透明黑）

    //保存海报图路径
    $path = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/images/goods_share/';
    if(!file_exists($path)) {
        mkdir($path,0777,true);
    }

    // 保存路径
    $savePath = $path.'goods_'.$goods_id.'_'.$data['uid'].'_'.$data['campaign_id'].'.png';
    // 准备字体
    $font = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/font/msyh.ttf';

    #4.2、将背景设置为白色
    imagefill($im, 0, 0, $random_color);

    #4.3、商品图片准备
    $goods['goods_image'] = 'https://dtc.gogo198.net'.$goods['goods_image'];
//    if(strpos($goods['goods_image'],'https:') === false){
//        $goods['goods_image'] = 'https:'.$goods['goods_image'];
//    }

    // 方法1：使用 file_get_contents（需要 allow_url_fopen=On）
    $imageData = @file_get_contents($goods['goods_image']);
    if ($imageData === false) {
        return ['code' => -1, 'msg' => '无法获取商品图片'];
    }

    $true_goods_image = @imagecreatefromstring($imageData);
    if ($true_goods_image === false) {
        return ['code' => -1, 'msg' => '无效的图片数据'];
    }

    // 获取图像尺寸
    $width = imagesx($true_goods_image);
    $height = imagesy($true_goods_image);

    // 组合商品图片到画布
    imagecopyresampled($im, $true_goods_image, 70, 70, 0, 0, 860, 860, $width, $height);

    imagedestroy($true_goods_image);

    //imagecreatefrompng问题隐藏
//    $goods_image = getimagesize($goods['goods_image']);
//    //判断png或jpg
//    $judge_format = explode('.',$goods['goods_image'])[3];
//    $true_goods_image = '';
//    if($judge_format=='jpg' || $judge_format=='jpeg'){
//        $true_goods_image = imagecreatefromjpeg($goods['goods_image']);
//    }elseif($judge_format=='png'){
//        $true_goods_image = imagecreatefrompng($goods['goods_image']);
//    }
//
//    //组合商品图片到画布
//    imagecopyresampled($im, $true_goods_image, 70, 70, 0, 0, 860, 860, $goods_image[0], $goods_image[1]);

    #4.4、店铺logo和名称展示位置
    //店铺logo展示
    $logo_image = getimagesize($shop_logo);
    //判断png或jpg
    $judge_format = explode('.',$shop_logo)[3];
    $true_logo_image = '';
    if($judge_format=='jpg' || $judge_format=='jpeg'){
        $true_logo_image = imagecreatefromjpeg($shop_logo);
    }elseif($judge_format=='png'){
        $true_logo_image = imagecreatefrompng($shop_logo);
    }
    imagecopyresampled($im, $true_logo_image, 120, 850, 0, 0, 50, 50, $logo_image[0], $logo_image[1]);
    imagettftext($im, 20, 0, 190, 885, $font_color3, $font, $shop_name);


    #4.5、商品名称位置
    // 将字符串分割为单个字符数组，然后每19个一组
    $characters = mb_str_split($goods['goods_name'], 1, 'UTF-8');
    $result = array_chunk($characters, 19);
    // 将每组字符重新组合成字符串
    $result = array_map(function($chunk) {
        return implode('', $chunk);
    }, $result);
    if(count($result)==1){
        //商品名称只有一行
        imagettftext($im, 30, 0, 120+1, 1040, $font_color1, $font, $result[0]);   // 右
        imagettftext($im, 30, 0, 120-1, 1040, $font_color1, $font, $result[0]);   // 左
        imagettftext($im, 30, 0, 120, 1040+1, $font_color1, $font, $result[0]);   // 下
        imagettftext($im, 30, 0, 120, 1040-1, $font_color1, $font, $result[0]);   // 上
        imagettftext($im, 30, 0, 120, 1040, $font_color1, $font, $result[0]);     // 中心
    }
    elseif(count($result)==2){
        //商品名称只有一行
        imagettftext($im, 30, 0, 120+1, 1040, $font_color1, $font, $result[0]);   // 右
        imagettftext($im, 30, 0, 120-1, 1040, $font_color1, $font, $result[0]);   // 左
        imagettftext($im, 30, 0, 120, 1040+1, $font_color1, $font, $result[0]);   // 下
        imagettftext($im, 30, 0, 120, 1040-1, $font_color1, $font, $result[0]);   // 上
        imagettftext($im, 30, 0, 120, 1040, $font_color1, $font, $result[0]);     // 中心

        imagettftext($im, 30, 0, 120+1, 1100, $font_color1, $font, $result[1]);   // 右
        imagettftext($im, 30, 0, 120-1, 1100, $font_color1, $font, $result[1]);   // 左
        imagettftext($im, 30, 0, 120, 1100+1, $font_color1, $font, $result[1]);   // 下
        imagettftext($im, 30, 0, 120, 1100-1, $font_color1, $font, $result[1]);   // 上
        imagettftext($im, 30, 0, 120, 1100, $font_color1, $font, $result[1]);     // 中心
    }
    elseif(count($result)>=3){
        //商品名称只有三行
        imagettftext($im, 30, 0, 120+1, 1040, $font_color1, $font, $result[0]);   // 右
        imagettftext($im, 30, 0, 120-1, 1040, $font_color1, $font, $result[0]);   // 左
        imagettftext($im, 30, 0, 120, 1040+1, $font_color1, $font, $result[0]);   // 下
        imagettftext($im, 30, 0, 120, 1040-1, $font_color1, $font, $result[0]);   // 上
        imagettftext($im, 30, 0, 120, 1040, $font_color1, $font, $result[0]);     // 中心

        imagettftext($im, 30, 0, 120+1, 1100, $font_color1, $font, $result[1] . '...');   // 右
        imagettftext($im, 30, 0, 120-1, 1100, $font_color1, $font, $result[1] . '...');   // 左
        imagettftext($im, 30, 0, 120, 1100+1, $font_color1, $font, $result[1] . '...');   // 下
        imagettftext($im, 30, 0, 120, 1100-1, $font_color1, $font, $result[1] . '...');   // 上
        imagettftext($im, 30, 0, 120, 1100, $font_color1, $font, $result[1] . '...');     // 中心
    }

    #4.6、商品分享词
    $characters = mb_str_split($goods['goods_name'], 1, 'UTF-8');
    $result2 = array_chunk($characters, 24);
    // 将每组字符重新组合成字符串
    $result2 = array_map(function($chunk) {
        return implode('', $chunk);
    }, $result2);
    if(count($result)==1){
        #商品名称一行
        if(count($result2)==1){
            imagettftext($im, 20, 0, 120, 1140, $font_color1, $font, $result2[0]);
        }
        elseif(count($result2)==2){
            imagettftext($im, 20, 0, 120, 1140, $font_color1, $font, $result2[0]);
            imagettftext($im, 20, 0, 120, 1200, $font_color1, $font, $result2[1]);
        }
        elseif(count($result2)==3){
            imagettftext($im, 20, 0, 120, 1140, $font_color1, $font, $result2[0]);
            imagettftext($im, 20, 0, 120, 1200, $font_color1, $font, $result2[1] . '...');
        }
    }
    elseif(count($result)>=2){
        #商品名称两行
        if(count($result2)==1){
            imagettftext($im, 24, 0, 120, 1200, $font_color1, $font, $result2[0]);
        }
        elseif(count($result2)==2){
            imagettftext($im, 24, 0, 120, 1200, $font_color1, $font, $result2[0]);
            imagettftext($im, 24, 0, 120, 1260, $font_color1, $font, $result2[1]);
        }
        elseif(count($result2)==3){
            imagettftext($im, 24, 0, 120, 1200, $font_color1, $font, $result2[0]);
            imagettftext($im, 24, 0, 120, 1260, $font_color1, $font, $result2[1] . '...');
        }
    }

    #4.7、商品价格
    $low_price_num = strlen(explode('.',$low_price)[0]);

    if($low_price_num==1){
        //9
        if(count($result)==1){
            imagettftext($im, 30, 0, 690+1, 1200, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 690-1, 1200, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 690, 1200+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 690, 1200-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 690, 1200, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 710+1, 1200, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 710-1, 1200, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 710, 1200+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 710, 1200-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 710, 1200, $font_color1, $font, $true_low_price);     // 中心
        }
        elseif(count($result)>=2){
            imagettftext($im, 30, 0, 690+1, 1260, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 690-1, 1260, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 690, 1260+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 690, 1260-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 690, 1260, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 710+1, 1260, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 710-1, 1260, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 710, 1260+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 710, 1260-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 710, 1260, $font_color1, $font, $true_low_price);     // 中心
        }
    }
    elseif($low_price_num==2){
        //99
        if(count($result)==1){
            imagettftext($im, 30, 0, 670+1, 1200, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 670-1, 1200, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 670, 1200+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 670, 1200-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 670, 1200, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 690+1, 1200, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 690-1, 1200, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 690, 1200+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 690, 1200-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 690, 1200, $font_color1, $font, $true_low_price);     // 中心
        }
        elseif(count($result)>=2){
            imagettftext($im, 30, 0, 670+1, 1260, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 670-1, 1260, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 670, 1260+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 670, 1260-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 670, 1260, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 690+1, 1260, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 690-1, 1260, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 690, 1260+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 690, 1260-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 690, 1260, $font_color1, $font, $true_low_price);     // 中心
        }
    }
    elseif($low_price_num==3){
        //999
        if(count($result)==1){
            imagettftext($im, 30, 0, 650+1, 1200, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 650-1, 1200, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 650, 1200+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 650, 1200-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 650, 1200, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 670+1, 1200, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 670-1, 1200, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 670, 1200+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 670, 1200-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 670, 1200, $font_color1, $font, $true_low_price);     // 中心
        }
        elseif(count($result)>=2){
            imagettftext($im, 30, 0, 650+1, 1260, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 650-1, 1260, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 650, 1260+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 650, 1260-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 650, 1260, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 670+1, 1260, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 670-1, 1260, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 670, 1260+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 670, 1260-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 670, 1260, $font_color1, $font, $true_low_price);     // 中心
        }
    }
    elseif($low_price_num==4){
        //9999
        if(count($result)==1){
            imagettftext($im, 30, 0, 630+1, 1200, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 630-1, 1200, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 630, 1200+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 630, 1200-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 630, 1200, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 650+1, 1200, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 650-1, 1200, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 650, 1200+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 650, 1200-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 650, 1200, $font_color1, $font, $true_low_price);     // 中心
        }
        elseif(count($result)>=2){
            imagettftext($im, 30, 0, 630+1, 1260, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 630-1, 1260, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 630, 1260+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 630, 1260-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 630, 1260, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 650+1, 1260, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 650-1, 1260, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 650, 1260+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 650, 1260-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 650, 1260, $font_color1, $font, $true_low_price);     // 中心
        }
    }
    elseif($low_price_num>=5){
        //9999
        if(count($result)==1){
            imagettftext($im, 30, 0, 610+1, 1200, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 610-1, 1200, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 610, 1200+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 610, 1200-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 610, 1200, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 630+1, 1200, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 630-1, 1200, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 630, 1200+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 630, 1200-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 630, 1200, $font_color1, $font, $true_low_price);     // 中心
        }
        elseif(count($result)>=2){
            imagettftext($im, 30, 0, 610+1, 1260, $font_color1, $font, "|");   // “|” 字符
            imagettftext($im, 30, 0, 610-1, 1260, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 610, 1260+1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 610, 1260-1, $font_color1, $font, "|");
            imagettftext($im, 30, 0, 610, 1260, $font_color1, $font, "|");

            imagettftext($im, 30, 0, 630+1, 1260, $font_color1, $font, $true_low_price);   // 右
            imagettftext($im, 30, 0, 630-1, 1260, $font_color1, $font, $true_low_price);   // 左
            imagettftext($im, 30, 0, 630, 1260+1, $font_color1, $font, $true_low_price);   // 下
            imagettftext($im, 30, 0, 630, 1260-1, $font_color1, $font, $true_low_price);   // 上
            imagettftext($im, 30, 0, 630, 1260, $font_color1, $font, $true_low_price);     // 中心
        }
    }

    #4.8、小程序二维码
    $t = time();
    $mini_code = $_SERVER['DOCUMENT_ROOT'].'/collect_website/public/images/goods_miniprogram/wxmini_to_shop_img_'.$goods_id.'_'.$data['uid'].'_'.$data['campaign_id'].'.jpg';// 小程序码
    $true_mini_code = 'https://shop.gogo198.cn/collect_website/public/images/goods_miniprogram/wxmini_to_shop_img_'.$goods_id.'_'.$data['uid'].'_'.$data['campaign_id'].'.jpg';
    if (!file_exists($mini_code)) {
        // 获取小程序码
        $res = get_miniprogram_code($goods_id,$data['uid'],$data['campaign_id'],"pages/orderfood_detail/index");
        if($res['code'] == 0){
            $mini_code = $res['img'];
        }else{
            return json(['code'=>-1,'msg'=>$res['msg']]);
        }
        sleep(1);
    }

    $mini_image = getimagesize($true_mini_code);
    //判断png或jpg
    $judge_format = explode('.',$true_mini_code)[3];
    $true_mini_image = '';
    if($judge_format=='jpg' || $judge_format=='jpeg'){
        $true_mini_image = imagecreatefromjpeg($true_mini_code);
    }elseif($judge_format=='png'){
        $true_mini_image = imagecreatefrompng($true_mini_code);
    }
    // 创建圆角商品图片
    $rounded_mini_image = create_rounded_image($true_mini_image, 210);
    imagecopyresampled($im, $rounded_mini_image, 400, 1400, 0, 0, 200, 200, $mini_image[0], $mini_image[1]);

    header("cotent-type:image/png"); //输出图像的MIME类型
    imagepng($im,$savePath); //输出一个png图像数据

    return ['code'=>0,'msg'=>'生成推广图片成功','img'=>'https://shop.gogo198.cn/collect_website/public/images/goods_share/goods_'.$goods_id.'_'.$data['uid'].'_'.$data['campaign_id'].'.png'];
}

# 获取小程序码
function get_miniprogram_code($goods_id,$uid,$campaign_id,$page="pages/agreement/index"){
    $time = time();

    #获取accesstoken
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
    $res = file_get_contents($url);
    $result = json_decode($res, true);

    if(!isset($result['access_token'])){
        $error_msg = isset($result['errmsg']) ? $result['errmsg'] : '未知错误';
        // 确保错误消息是UTF-8编码
        $error_msg = mb_convert_encoding($error_msg, 'UTF-8', 'auto');
        return ['code' => -1, 'msg' => '获取access_token失败: ' . $error_msg];
    }

    $access_token = $result['access_token'];

    #获取微信小程序码
    $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
    $datas = array(
        "page" => $page,
        "scene" => "gid=" . $goods_id . "&cid=".$campaign_id."&uid=". $uid,
        "check_path" => true,
        "env_version" => 'release',//release develop trial体验
        'width' => 430,
    );
    // 使用改进的httpRequest函数
    $img = httpRequest_wx($url, json_encode($datas));

    // 首先检查是否是JSON错误响应
    if (substr($img, 0, 1) === '{') {
        $error_result = json_decode($img, true);
        if (isset($error_result['errcode'])) {
            $error_msg = isset($error_result['errmsg']) ? $error_result['errmsg'] : '未知错误';
            // 确保错误消息是UTF-8编码
            $error_msg = mb_convert_encoding($error_msg, 'UTF-8', 'auto');
            return ['code' => -1, 'msg' => '微信API错误: ' . $error_msg . ' (错误码: ' . $error_result['errcode'] . ')'];
        }
    }

    // 检查返回的是否是有效的图片数据
    if (empty($img) || strlen($img) < 100) {
        return ['code' => -1, 'msg' => '小程序码生成失败，返回数据异常，数据长度: ' . strlen($img)];
    }

    $savepath = $_SERVER['DOCUMENT_ROOT'] . '/collect_website/public/images/goods_miniprogram/wxmini_to_shop_img_'.$goods_id.'_'.$uid.'_'.$campaign_id.'.jpg';

    // 确保目录存在
    $dir = dirname($savepath);
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }

    // 保存文件
    if (file_put_contents($savepath, $img) === false) {
        return ['code' => -1, 'msg' => '保存小程序码文件失败'];
    }

    // 验证保存的文件
    if (!file_exists($savepath) || filesize($savepath) == 0) {
        return ['code' => -1, 'msg' => '小程序码文件保存失败'];
    }

    return ['code' => 0, 'img' => 'https://shop.gogo198.cn/collect_website/public/images/goods_miniprogram/wxmini_to_shop_img_'.$goods_id.'_'.$uid.'_'.$campaign_id.'.jpg'];
}
#=====================================================================================创建商品小程序主题海报图END

//通过FRP的tcp协议同步信息到本地电脑
function sync_info($id,$method=''){
    if($method=='member'){
        #会员信息同步
        $member = Db::name('website_user')->where(['id'=>$id])->find();
        // 通过FRP的tcp网络协议
        $result = send_data_tcp('sync_info@:@member@@@'.json_encode($member,true));
        return ['msg'=>$result];
    }
    elseif($method=='package'){
        #数据包同步
        $package = DB::name('train_dataset_package')->where(['id'=>$id])->find();
//        $result = send_data_tcp('sync_info@:@package@@@'.json_encode($package,true));
        $result = send_data_tcp('sync_info@:@package@@@'.$id);
        return ['msg'=>$result];
    }
    elseif($method=='dataset'){
        //数据条同步
        $dataset = DB::name('train_dataset')->where(['id'=>$id])->find();
        $result = send_data_tcp('sync_info@:@dataset@@@'.json_encode($dataset,true));
        return ['msg'=>$result];
    }
}

function start_rag($id){
    $result = send_data_tcp('query@:@'.$id.'@@@');
    return ['msg'=>$result];
}

#智能客服提交问题ID给通义千问检测翻译语种和调起翻译
function translate_word($question_id,$is_convert_customer=0,$translate_lang='',$en_name='Chinese'){
    // 配置API Key和模型
    $apiKey = 'sk-c9c7f92a563d4f8683419b43acb3b50b'; // 替换为你的DashScope API Key
    $translateModel = 'qwen-mt-turbo'; // 使用模型

    // 调用PHP的语种检测包
    $detector = new LanguageDetection\Language();

    // 步骤1：买家母语咨询文本内容
    $userInput = '';
    if($is_convert_customer==0){
        #智能客服问答表
        $question_info = Db::name('train_qa_history')->where(['id'=>$question_id])->find();
        $userInput = $question_info['text'];#原文
    }
    elseif($is_convert_customer==1){
        #转人工后的表
        $question_info = Db::name('website_chatlist')->where(['id'=>$question_id])->find();
        $userInput = json_decode($question_info['content'],true);#获取原文
    }

//    $userInput = 'Who are you, where are you from, and why are you here?';
    $results = $detector->detect($userInput); // 获取语种判断结果
    $languages = $results->close(); // 获取概率数组
    arsort($languages);           // 按分值降序排序
    $detectedLang = key($languages); // 取第一个键名

    $detectedLang = explode('-',$detectedLang)[0];
    if($detectedLang!=$translate_lang && !empty($translate_lang) && $translate_lang!='auto'){
        $detectedLang = $translate_lang;#以免检测语种结果不正确，最终采用用户选择的语种
    }

    // 判断得到阿里云需要的语种名称
    if($detectedLang=='zh'){
        $detectedLang = 'Chinese';
    }elseif($detectedLang=='en'){
        $detectedLang = 'English';
    }elseif($detectedLang=='ja'){
        $detectedLang = 'Japanese';#日语
    }elseif($detectedLang=='ko'){
        $detectedLang = 'Korean';#韩语
    }elseif($detectedLang=='fr'){
        $detectedLang = 'French';#法语
    }elseif($detectedLang=='de'){
        $detectedLang = 'German';#德语
    }elseif($detectedLang=='es'){
        $detectedLang = 'Spanish';#西班牙语
    }elseif($detectedLang=='ru'){
        $detectedLang = 'Russian';#俄语
    }elseif($detectedLang=='ar'){
        $detectedLang = 'Arabic';#阿拉伯语
    }elseif($detectedLang=='pt'){
        $detectedLang = 'Portuguese';#巴西葡萄牙语
    }elseif($detectedLang=='it'){
        $detectedLang = 'Italian';#意大利语
    }elseif($detectedLang=='vi'){
        $detectedLang = 'Vietnamese';#越南语
    }elseif($detectedLang=='th'){
        $detectedLang = 'Thai';#泰国语
    }elseif($detectedLang=='tr'){
        $detectedLang = 'Turkish';#土耳其语
    }elseif($detectedLang=='hi'){
        $detectedLang = 'Hindi';#印地语
    }elseif($detectedLang=='be'){
        $detectedLang = 'Bengali';#孟加拉语
    }elseif($detectedLang=='ur'){
        $detectedLang = 'Urdu';#乌尔都语
    }elseif($detectedLang=='id'){
        $detectedLang = 'Indonesian';#印尼语
    }elseif($detectedLang=='nl'){
        $detectedLang = 'Dutch';#荷兰语
    }elseif($detectedLang=='km'){
        $detectedLang = 'Khmer';#高棉语
    }elseif($detectedLang=='none'){
        $detectedLang = 'Cebuano';#宿务语不支持，阿里云支持
    }elseif($detectedLang=='tl'){
        $detectedLang = 'Filipino';#菲律宾语
    }elseif($detectedLang=='cs'){
        $detectedLang = 'Czech';#捷克语
    }elseif($detectedLang=='pl'){
        $detectedLang = 'Polish';#波兰语
    }elseif($detectedLang=='fa'){
        $detectedLang = 'Persian';#波斯语
    }elseif($detectedLang=='he'){
        $detectedLang = 'Hebrew';#希伯来语
    }else{
        $detectedLang = 'Chinese';
    }

    #最终确认用户发送的是什么语种
    $userLanguage = $detectedLang;

//    $finalAnswer = ''; #大模型最终回答
    if($userLanguage=='Chinese'){
        #本是中文，无需翻译
        if($is_convert_customer==0){
            Db::name('train_qa_history')->where(['id'=>$question_id])->update(['origin_text'=>$userInput,'language'=>$userLanguage]);
        }
        elseif($is_convert_customer==1){
            Db::name('website_chatlist')->where(['id'=>$question_id])->update(['origin_content'=>json_encode($userInput,true),'language'=>$userLanguage]);
        }
        return 1;
    }
    else{
        #外语，需要翻译成中文（因为AI只接受中文）
        $userInput = translateText($userInput, $userLanguage, $en_name, $translateModel, $apiKey);
        if($userInput == -1){
            return $userInput;
        }
        #记录中文内容到翻译后的字段
        if($is_convert_customer==0){
            Db::name('train_qa_history')->where(['id'=>$question_id])->update(['origin_text'=>$userInput,'language'=>$userLanguage]);
        }
        elseif($is_convert_customer==1){
            Db::name('website_chatlist')->where(['id'=>$question_id])->update(['origin_content'=>json_encode($userInput,true),'language'=>$userLanguage]);
        }

        return 1;
    }

    #返回1给智能客服应用，并在那个PHP控制台脚本使用队列服务
    return 1;
}

#大模型返回中文回答给通义千问翻译成用户的母语
function translate_answer($answer_id,$is_convert_customer=0,$translate_back_language=''){
    // 配置API Key和模型
    $apiKey = 'sk-c9c7f92a563d4f8683419b43acb3b50b'; // 替换为你的DashScope API Key
    $translateModel = 'qwen-mt-turbo'; // 使用模型

    if($is_convert_customer==0){
        #查询上级问题的母语
        $info = Db::name('train_qa_history')
            ->alias('a')
            ->join('train_qa_history b','b.id=a.pid')
            ->where(['a.id'=>$answer_id])
            ->field('b.language,a.origin_text')
            ->find();

        #翻译成用户的母语
        $userInput = translateText($info['origin_text'], 'Chinese', $info['language'], $translateModel, $apiKey);
        Db::name('train_qa_history')->where(['id'=>$answer_id])->update(['text'=>$userInput]);

        return 1;
    }
    elseif($is_convert_customer==1){
        #转人工后的表
        $info = Db::name('website_chatlist')->where(['id'=>$answer_id])->find();

        if('Chinese' == $translate_back_language){
            #是中文就不用给阿里翻译了
            Db::name('website_chatlist')->where(['id'=>$answer_id])->update(['content'=>$info['origin_content']]);

            return 1;
        }else{
            #不是中文，需翻译成用户提问时的语言。
            $userInput = translateText($info['origin_content'], 'Chinese', $translate_back_language, $translateModel, $apiKey);
            Db::name('website_chatlist')->where(['id'=>$answer_id])->update(['content'=>json_encode($userInput,true)]);

            return 1;
        }
    }

}

//调用通义千问翻译API
function translateText($text, $sourceLang, $targetLang, $model, $apiKey) {
    $url = 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions';
    $headers = [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ];
    $data = [
        'model' => $model,
        'messages' => [["role"=>"user","content"=>$text]],
//        'messages' => [["role"=>"user","content"=>truncateText($text, 6000)]],
        'translation_options' => [
            'source_lang' => $sourceLang,#目前文本所属的语种
            'target_lang' => $targetLang,#需要翻译成的用户提问时的语言
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if(isset($result['error'])){
        # 翻译错误返回
        return -1;
    }
    else{
        return isset($result['choices'][0]['message']['content']) ? $result['choices'][0]['message']['content'] : $text; // 默认返回原文
    }

//    Array 失败示例
//    (
//        [error] => Array
//        (
//            [code] => invalid_parameter_error
//            [param] =>
//            [message] => 暂时不支持当前设置的语种！
//            [type] => invalid_request_error
//        )
//
//        [id] => chatcmpl-2cad9ec3-37e9-927c-bfda-05db419c0ba5
//        [request_id] => 2cad9ec3-37e9-927c-bfda-05db419c0ba5
//    )

//    Array  成功示例
//    (
//        [choices] => Array
//        (
//            [0] => Array
//            (
//                [message] => Array
//                (
//                    [content] => 你是谁，你从哪里来，你为什么在这里？
//                    [role] => assistant
//                )
//
//                [finish_reason] => stop
//                [index] => 0
//                [logprobs] =>
//            )
//        )
//
//        [object] => chat.completion
//        [usage] => Array
//        (
//            [prompt_tokens] => 37
//            [completion_tokens] => 13
//            [total_tokens] => 50
//        )
//        [created] => 1747021042
//        [system_fingerprint] =>
//        [model] => qwen-mt-turbo
//        [id] => chatcmpl-debf1b6f-3848-9f82-948c-d6cdb7f741f8
//    )
}

//输入文本截断（避免超长输入）（废弃）
function truncateText($text, $maxLength = 6000) {
    if (mb_strlen($text, 'UTF-8') > $maxLength) {
        return mb_substr($text, 0, $maxLength, 'UTF-8');
    }
    return $text;
}

//开始训练(webhook版本http)
function start_lora_backup($id){
    $package = Db::name('train_dataset_package')->where(['id'=>$id])->find();
    Db::name('train_dataset_package')->where(['id'=>$id])->update(['traintime'=>time()]);#记录开始训练时间

    $dataset_ids = explode(',',$package['dataset_ids']);
    $dataset_arr = [];
    foreach($dataset_ids as $k=>$v){
        $dataset = Db::name('train_dataset')->where(['id'=>$v])->find();
        array_push($dataset_arr,['instruction'=>$dataset['question'],'input'=>$dataset['answer'],'output'=>$dataset['real_text']]);
    }

    #开始把数据推到本地电脑===================start
    // ngrok 生成的公网地址
    $webhookUrl = 'https://ed09-14-212-103-145.ngrok-free.app/lora_webhook';
    // 1. 构造请求数据
    $payload = [
        'message' => $dataset_arr,
        'package_id'=>$id
    ];
    // 2. 转换为 JSON（保留中文不转义，模拟 Python 的 json=payload）
    $jsonData = json_encode($payload, true);

    // 3. 初始化 cURL（模拟 Python 的 requests.post）
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $webhookUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ],
        CURLOPT_RETURNTRANSFER => true, // 不直接输出响应，返回字符串
        CURLOPT_SSL_VERIFYPEER => false, // 本地测试时禁用 SSL 验证（生产环境需启用）
        CURLOPT_TIMEOUT => 10, // 超时时间（秒）
        CURLOPT_CONNECTTIMEOUT => 5 // 连接超时时间（秒）
    ]);

    $result = '';
    // 4. 执行请求并获取响应
    try {
        $responseBody = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // 5. 检查响应状态（模拟 Python 的 response.raise_for_status()）
        if ($httpStatusCode >= 200) {
            Log::info("Webhook 请求失败，状态码：{$httpStatusCode}，响应：{$responseBody}");
//                throw new Exception("Webhook 请求失败，状态码：{$httpStatusCode}，响应：{$responseBody}");
            return json(['code'=>-1,'msg'=>'本地电脑webhook发生错误！']);
        }

        // 6. 记录推送日志（模拟 Python 的 logger.info）
        $currentTime = date("Y年m月d日H时i分s秒"); // PHP 日期格式与 Python 对应
        $numRecords = count($dataset); // 获取数据条数
        $logMessage = "在{$currentTime}，推送了{$numRecords}条数据\n";

        // 写入日志文件（可替换为实际日志系统）
        Log::info('webhook推送成功：'.$logMessage);
        $result = ['code'=>0,'msg'=>'Webhook 推送成功，响应：' . $responseBody];

//            Db::name('train_dataset_package')->where(['id'=>$id])->update(['status'=>1]);

    } catch (Exception $e) {
        // 处理错误（包括 cURL 错误和状态码错误）
        $error = curl_errno($ch) ? curl_error($ch) : $e->getMessage();

        Log::info('webhook推送失败：'.$error);
        $result = ['code'=>-1,'msg'=>'cURL Error: ' . $error];
    }

    // 关闭 cURL 句柄
    curl_close($ch);

    return $result;
}

//智能客服开始检索
function start_rag_backup($id){
    #开始把数据推到本地电脑===================start
    // ngrok 生成的公网地址
    $webhookUrl = 'https://ed09-14-212-103-145.ngrok-free.app/query';
    // 1. 构造请求数据
    $payload = [
        'question_id'=>$id
    ];
    // 2. 转换为 JSON（保留中文不转义，模拟 Python 的 json=payload）
    $jsonData = json_encode($payload, true);

    // 3. 初始化 cURL（模拟 Python 的 requests.post）
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $webhookUrl,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ],
        CURLOPT_RETURNTRANSFER => true, // 不直接输出响应，返回字符串
        CURLOPT_SSL_VERIFYPEER => false, // 本地测试时禁用 SSL 验证（生产环境需启用）
        CURLOPT_TIMEOUT => 10, // 超时时间（秒）
        CURLOPT_CONNECTTIMEOUT => 5 // 连接超时时间（秒）
    ]);

    $result = '';
    // 4. 执行请求并获取响应
    try {
        $responseBody = curl_exec($ch);
        $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ddate = date('Y-m-d H:i:s');
        // 5. 检查响应状态（模拟 Python 的 response.raise_for_status()）
        if ($httpStatusCode >= 200) {
            Log::info($ddate." Webhook 请求失败，状态码：{$httpStatusCode}，响应：{$responseBody}");
//                throw new Exception("Webhook 请求失败，状态码：{$httpStatusCode}，响应：{$responseBody}");
            return json(['code'=>-1,'msg'=>'本地电脑webhook发生错误！']);
        }

        // 6. 记录推送日志（模拟 Python 的 logger.info）
        $currentTime = date("Y年m月d日H时i分s秒"); // PHP 日期格式与 Python 对应
        $logMessage = "智能客服，在{$currentTime}，推送了1条问题数据\n";

        // 写入日志文件（可替换为实际日志系统）
        Log::info($ddate.' webhook推送成功：'.$logMessage);
        $result = ['code'=>0,'msg'=>'Webhook 推送成功，响应：' . $responseBody];

//            Db::name('train_dataset_package')->where(['id'=>$id])->update(['status'=>1]);

    } catch (Exception $e) {
        // 处理错误（包括 cURL 错误和状态码错误）
        $error = curl_errno($ch) ? curl_error($ch) : $e->getMessage();

        Log::info($ddate.' webhook推送失败：'.$error);
        $result = ['code'=>-1,'msg'=>'cURL Error: ' . $error];
    }

    // 关闭 cURL 句柄
    curl_close($ch);

    return $result;
}
