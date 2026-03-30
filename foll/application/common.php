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

//	use phpmailer\phpmailer\PHPMailer;
//	include "../vendor/phpmailer/phpmailer/src/PHPMailer";
// 应用公共文件
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Util\data\Sysdb;
use think\Db;

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

function sendErrorTempls($temp) {
    $templdate=[
        'touser'	 =>$temp['openid'],
        'template_id'=>'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8',
        'url'		 =>$temp['url']?$temp['url']:'',
        'data'=>[
            'first'=>[
                'value'=>$temp['title'],//'',
                'color'=>'#f00000',
            ],
            'keyword1'=>[
                'value'=>$temp['projects'],
                'color'=>'#436EEE',
            ],
            'keyword2'=>[
                'value'=>$temp['status_text'],
                'color'=>'#436EEE',
            ],
            'keyword3'=>[
                'value'=>$temp['time'],
                'color'=>'#436EEE',
            ],
            'remark'=>[
                'value'=>$temp['remark'],
                'color'=>'#808080'
            ],
        ]
    ];
    
    $postUrl = 'http://shop.gogo198.cn/foll/public/?s=api/wechat/template';
    $tmp = ['template'=>serialize($templdate),'uniacid'=>3];
    $res = postJson($postUrl,$tmp,true);
    return $res;
}

// 代理商审核
function sendAgentsTempls($temp) {
    $templdate=[
        'touser'	 =>$temp['openid'],
        'template_id'=>'tKp53WIg8puJ_u3jIyZasBvVonPEtUeNlXKnmi7r9UM',
        'url'		 =>$temp['url']?$temp['url']:'',
        'data'=>[
            'first'=>[
                'value'=>$temp['first'],//'',
                'color'=>'#f00000',
            ],
            'keyword1'=>[
                'value'=>$temp['keyword1'],
                'color'=>'#436EEE',
            ],
            'keyword2'=>[
                'value'=>$temp['keyword2'],
                'color'=>'#436EEE',
            ],
            'keyword3'=>[
                'value'=>$temp['keyword3'],
                'color'=>'#436EEE',
            ],
            'remark'=>[
                'value'=>$temp['remark'],
                'color'=>'#808080'
            ],
        ]
    ];
    
    $postUrl = 'http://shop.gogo198.cn/foll/public/?s=api/wechat/template';
    $tmp = ['template'=>serialize($templdate),'uniacid'=>3];
    $res = postJson($postUrl,$tmp,true);
    return $res;
}

function postJson($url,$data = null,$json=false)
	{
		$curl = curl_init();
		curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
		curl_setopt($curl,CURLOPT_HEADER,0); //头文件信息做数据流输出
		curl_setopt($curl,CURLOPT_URL,$url);
		if(!empty($data)) {

			if($json && is_array($data)){
				$data = json_encode($data);
			}

			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);

			if($json) {//发送JSON数据；

				curl_setopt($curl,CURLOPT_HEADER,0);
				curl_setopt($curl,CURLOPT_HTTPHEADER,array(
					//'Content-Type:text/html;charset=utf-8',
					'Content-Type:application/json;charset=utf-8',
					'Content-Length:'.strlen($data)
				));
			}
		}
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		$res = curl_exec($curl);
		$errorno = curl_errno($curl);
		if($errorno) {//错误
			return ['errorno'=>false,'errmsg'=>$errorno];
		}
		curl_close($curl);
		return json_decode($res,true);
	}

function getExchangeRate($type)
{
    return Db::name('currency')->where('code_value',$type)->value('rate');
}

// 发送微信通知
function sendWechatMsg($data)
{
	$url = 'http://shop.gogo198.cn/api/sendwechattemplatenotice.php';
    $client = new \GuzzleHttp\Client();
    try {
        //正常请求
        $promise = $client->request('post', $url, ["headers" => ['Content-Type' => 'application/json'],'body'=>$data]);
    } catch (GuzzleHttpExceptionClientException $exception) {
        //捕获异常 输出错误
        return $this->error($exception->getMessage());
    }
}
    
// 获取会员信息
function getUserInfo($uid)
{
    $user = Db::name('decl_user')->where('id',$uid)->find();
    return $user;
}

// 记录资金变动 // 1-冻结 2-解冻 3-支出 4-收入
function createMoneyLog($uid,$user_money,$change_money,$money_type,$message,$type,$change_id,$change_type)
    {
        if( $type == 3 )
        {
            $new_money = $user_money;
            $change_money = 0;
        }else{
            $new_money = floatval($change_money) + floatval($user_money);
        }
        
        // 插入记录
        DB::name('decl_user_money_log')->insert([
            'uid' => $uid,
            'old_money' => $user_money,
            'change_money' => floatval($change_money),
            'new_money' => $new_money,
            'money_type' => $money_type,
            'message' => $message,
            'type' => $type,
            'change_id' => $change_id,
            'change_type' => $change_type,
            'create_at' => time(),
        ]);
        // 更新金额
        updageUserMoney($uid,$new_money,$money_type,$type);
    }

// 更新用户金额
function updageUserMoney($uid,$money,$money_type,$type)
{
    $user_money = getUserMoney($uid,'all');
    if($type != 3)
    {
        switch ($money_type) {
            case 'CNY':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['cny'=>$money]);
            break;
            case 'USD':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['usd'=>$money]);
            break;
            case 'HKD':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['hkd'=>$money]);
            break;
            case 'CHF':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['chf'=>$money]);
            break;
            case 'DKK':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['dkk'=>$money]);
            break;
            case 'EUR':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['eur'=>$money]);
            break;
            case 'GBP':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['gbp'=>$money]);
            break;
            case 'JPY':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['jpy'=>$money]);
            break;
            case 'AUD':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['aud'=>$money]);
            break;
            case 'CAD':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['cad'=>$money]);
            break;
            case 'NOK':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['nok'=>$money]);
            break;
            case 'SEK':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['sek'=>$money]);
            break;
            case 'SGD':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['sgd'=>$money]);
            break;
            case 'NZD':
                Db::name('decl_user_money')->where('id',$user_money['id'])->update(['nzd'=>$money]);
            break;
        }
    }
    
}

//更新用户可提现余额
function upgradeUserCanUseMoney($uid,$money,$withdraw_money,$money_type,$exchange_type=0){
    $user_money = getUserCanUseMoney($uid,'all',$exchange_type);
    $change_money = floatval($money) + floatval($withdraw_money);
    switch ($money_type) {
        case 'CNY':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['cny'=>$change_money]);
            break;
        case 'USD':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['usd'=>$change_money]);
            break;
        case 'HKD':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['hkd'=>$change_money]);
            break;
        case 'CHF':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['chf'=>$change_money]);
            break;
        case 'DKK':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['dkk'=>$change_money]);
            break;
        case 'EUR':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['eur'=>$change_money]);
            break;
        case 'GBP':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['gbp'=>$change_money]);
            break;
        case 'JPY':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['jpy'=>$change_money]);
            break;
        case 'AUD':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['aud'=>$change_money]);
            break;
        case 'CAD':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['cad'=>$change_money]);
            break;
        case 'NOK':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['nok'=>$change_money]);
            break;
        case 'SEK':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['sek'=>$change_money]);
            break;
        case 'SGD':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['sgd'=>$change_money]);
            break;
        case 'NZD':
            Db::name('decl_user_money_can_use')->where('id',$user_money['id'])->update(['nzd'=>$change_money]);
            break;
    }
}

// 查询商户账户
function getUserMoney($uid,$money_type='all')
    {
        $user_money = Db::name('decl_user_money')->where('uid',$uid)->find();
        switch ($money_type) {
            case 'all':
                return $user_money;
            break;
            case 'CNY':
                return $user_money['cny'];
            break;
            case 'USD':
                return $user_money['usd'];
            break;
            case 'HKD':
                return $user_money['hkd'];
            break;
            case 'CHF':
                return $user_money['chf'];
            break;
            case 'DKK':
                return $user_money['dkk'];
            break;
            case 'EUR':
                return $user_money['eur'];
            break;
            case 'GBP':
                return $user_money['gbp'];
            break;
            case 'JPY':
                return $user_money['jpy'];
            break;
            case 'AUD':
                return $user_money['aud'];
            break;
            case 'CAD':
                return $user_money['cad'];
            break;
            case 'NOK':
                return $user_money['nok'];
            break;
            case 'SEK':
                return $user_money['sek'];
            break;
            case 'SGD':
                return $user_money['sgd'];
            break;
            case 'NZD':
                return $user_money['nzd'];
            break;
        }
    }

//获取用户可提现余额 $exchange_type:1换汇后结汇至国内，2换汇后转账至境外
function getUserCanUseMoney($uid,$money_type='all',$exchange_type=0){
    $user_money = Db::name('decl_user_money_can_use')->where(['uid'=>$uid,'exchange_type'=>$exchange_type])->find();
    switch ($money_type) {
        case 'all':
            return $user_money;
            break;
        case 'CNY':
            return $user_money['cny'];
            break;
        case 'USD':
            return $user_money['usd'];
            break;
        case 'HKD':
            return $user_money['hkd'];
            break;
        case 'CHF':
            return $user_money['chf'];
            break;
        case 'DKK':
            return $user_money['dkk'];
            break;
        case 'EUR':
            return $user_money['eur'];
            break;
        case 'GBP':
            return $user_money['gbp'];
            break;
        case 'JPY':
            return $user_money['jpy'];
            break;
        case 'AUD':
            return $user_money['aud'];
            break;
        case 'CAD':
            return $user_money['cad'];
            break;
        case 'NOK':
            return $user_money['nok'];
            break;
        case 'SEK':
            return $user_money['sek'];
            break;
        case 'SGD':
            return $user_money['sgd'];
            break;
        case 'NZD':
            return $user_money['nzd'];
            break;
    }
}

//小程序用户操作记录
function SaveWechatUserLogs($openid,$message,$type,$ordersn,$data_id,$decl_user_id)
{
  $data['openid'] = $openid;
  $data['message'] = $message;
  $data['create_time'] = time();
  $data['type'] = $type;
  $data['ordersn'] = $ordersn;
  $data['data_id'] = $data_id;
  $data['decl_user_id'] = $decl_user_id;
  Db::name('smallwechat_user_logs')->insert($data);
}

function Redirects($param)
{
//    $_SERVER['SERVER_NAME']
   $url="https://admin.gogo198.cn/foll/public/?s=".$param;
    header('Location: ' . $url);
   exit();
}

function Url($param)
{
    //    $_SERVER['SERVER_NAME']
    return "https://admin.gogo198.cn/foll/public/?s=".$param;
}

function verifCode($tel)
{
    if(preg_match("/^1[34578]\d{9}$/",$tel)){
        return true;
    }
    return false;
}


// 获取agents 角色组数据
function getRole($gid)
{
    $db = new SysDb();
    $res = $db->table('customs_agents_group')->where(['gid'=>$gid])->item();
    if(!$res) {
        return $gid;
    }
    return $res['title'];
}


// 获取agents 代理商名称
function getAgents($gid)
{
    $db = new SysDb();
    $res = $db->table('customs_agents_admin')->where(['id'=>$gid])->item();
    if(!$res) {
        return $gid;
    }
    return $res['uname'];
}

// 获取decl_user
function getDecls($gid)
{
    $db = new SysDb();
    $res = $db->table('decl_user')->where(['id'=>$gid])->field('user_name')->item();
    if(!$res) {
        return $gid;
    }
    return $res['user_name'];
}


// 获取账单类型
function billType($type)
{
    switch ($type){
        case 'day':
            return '日账单';
            break;
        case 'month':
            return '月账单';
            break;
        default:
            return $type;
            break;
    }
}


/**
 * 系统邮件发送函数
 * @param string $tomail 接收邮件者邮箱
 * @param string $name 接收邮件者名称
 * @param string $subject 邮件主题
 * @param string $body 邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 * @author static7 <static7@qq.com>
 */
 function send_mail($tomail, $name, $subject = '', $body = '', $attachment = null) {
    $mail = new PHPMailer();           //实例化PHPMailer对象
//  $mail = $this->PHPMailer
//	$mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';          	 		//设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                    		// 设定使用SMTP服务
    $mail->SMTPDebug = 0;               		// SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
    $mail->SMTPAuth = true;             		// 启用 SMTP 验证功能
    //$mail->SMTPSecure = 'ssl';          		// 使用安全协议
    $mail->SMTPSecure = 'tls';          		// 使用安全协议
    $mail->Host = "smtp.qq.com"; 				// SMTP 服务器
    //$mail->Port = 465;                 	    // SMTP服务器的端口号
    $mail->Port = 587;                 			// SMTP服务器的端口号
    $mail->Username = "805929498@qq.com";    	// SMTP服务器用户名    805929498@qq.com
    $mail->Password = "zjpbqdibcdmobgac";//"txrosoelfjiybcej";     	// SMTP服务器密码     auelorsctusbbfgh
     /**
      * txrosoelfjiybcej(新)   dbflwnifoxmobedd(旧)
      */
    $mail->SetFrom('805929498@qq.com', '系统管理员');
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



/**
 * 发送登录验证码
 * @param $config
 * @return mixed
 */
 function sendSms($config){
     vendor('alidayu.top.TopClient');
     vendor('alidayu.top.ResultSet');
     vendor('alidayu.top.RequestCheckUtil');
     vendor('alidayu.top.TopLogger');
     vendor('alidayu.top.request.AlibabaAliqinFcSmsNumSendRequest');
     $client = new \TopClient;
     $client->appkey = config("appkey");
     $client->secretKey = config("appSecret");
     $req = new \AlibabaAliqinFcSmsNumSendRequest;
     $req->setSmsType("normal");
     $req->setSmsFreeSignName($config['SingnName']);
     $req->setSmsParam(json_encode(['code'=>$config['code'],'product'=>$config['product']]));
     $req->setRecNum($config['tel']);//参数为用户的手机号码
     $req->setSmsTemplateCode($config['TemplateCode']);
     $resp = $client->execute($req);
     return $resp;
 }


/**
 * 发送审核短信
 * @param $config
 * @return mixed
 */
function sendReg($config){
    vendor('alidayu.top.TopClient');
    vendor('alidayu.top.ResultSet');
    vendor('alidayu.top.RequestCheckUtil');
    vendor('alidayu.top.TopLogger');
    vendor('alidayu.top.request.AlibabaAliqinFcSmsNumSendRequest');
    $client = new \TopClient;
    $client->appkey = config("appkey");
    $client->secretKey = config("appSecret");
    $req = new \AlibabaAliqinFcSmsNumSendRequest;
    $req->setSmsType("normal");
    $req->setSmsFreeSignName($config['SingnName']);
    $req->setSmsParam(json_encode(['submittime'=>$config['submittime'],'status'=>$config['status']]));
    $req->setRecNum($config['tel']);//参数为用户的手机号码
    $req->setSmsTemplateCode($config['TemplateCode']);
    $resp = $client->execute($req);
    return $resp;
}


/**
 * 发送验证码新版
 * @param $config
 * @return mixed
 */
function newSendSms($config){
    vendor('alidayu.top.TopClient');
    vendor('alidayu.top.ResultSet');
    vendor('alidayu.top.RequestCheckUtil');
    vendor('alidayu.top.TopLogger');
    vendor('alidayu.top.request.AlibabaAliqinFcSmsNumSendRequest');
    $client = new \TopClient;
    $client->appkey = config("appkey");
    $client->secretKey = config("appSecret");
    $req = new \AlibabaAliqinFcSmsNumSendRequest;
    $req->setSmsType("normal");
    $req->setSmsFreeSignName($config['SingnName']);
    $req->setSmsParam(json_encode($config['parm']));
    $req->setRecNum($config['tel']);//参数为用户的手机号码
    $req->setSmsTemplateCode($config['TemplateCode']);
    $resp = $client->execute($req);
    return $resp;
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

function reply_json(){

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



function httpRequest($url,$data,$head=[])
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

//发送支付完成给设备
function sendPayInfoDev($ordersn) {
    $postData = [
        'ordersn'=>$ordersn,
        'type'=>'wp'
    ];
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/pullOnlinePayStatusApi",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($postData),
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
}

function reqWx($data)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "http://shop.gogo198.cn/foll/public/?s=api/wechat/template",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            "Cache-Control: no-cache",
            "Content-Type: application/json",
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
       return $err;
    } else {
        return $response;
    }
}

/*
userData 用户数据
n 抽取人数
*/
function  lottery($UserData=array(),$n=null)
{
    $res=array('yes'=>array(),'no'=>array());//存放中签跟未中签的用户数组
    $UserRes=$UserData;
    unset($UserData);
    $MaxNum=count($UserRes);
    if(empty($UserRes)){return false;}
    shuffle($UserRes);
    try{
        for($i=0;$i<=$n-1;$i++){
            $randNum=mt_rand(0,$MaxNum-1);
            if(empty($UserRes[$randNum])){
                continue;
            }
            array_push($res['yes'],$UserRes[$randNum]);
            unset($UserRes[$randNum]);
        }
        $res['no']=$UserRes;
        return $res;
    }catch(Exception $e){
        return false;
    }
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


// 获取支付企业名称
function getPayName($pay_id)
{
    return Db::connect('shop')->table('customs_pay_list')->where('id',$pay_id)->value('payEntName');
}




// 获取快递企业   2019-08-20
function getExpress($expId) {
    return Db::name('customs_express')->where('id',$expId)->value('expName');
}

// publicId  获取公众号名称
function publicId($expId) {
    return Db::name('account_wechats')->where('uniacid',$expId)->value('name');
}

//merchatId     获取商户名称
function merchatId($expId) {
    return Db::name('sz_yi_perm_user')->where('uid',$expId)->value('username');
}

//expPargId     获取运单配置
function expPargId($expId) {
    return Db::name('customs_transportbill')->where('id',$expId)->value('expNames');
}

//packgeId     获取包材名称
function packgeId($expId) {
    return Db::name('customs_packaging')->where('id',$expId)->value('packgeName');
}

// 获取物流企业
function logistics($expId) {
    return Db::name('customs_logistics')->where('id',$expId)->value('routeName');
}

// 获取买家
function getBuyerFormUser()
{
    // 获取企业买家
    $enterprise_buyer_data = array();
    $enterprise_buyer = Db::name('decl_user_enterprise_buyer')->where('is_delete',0)->where('uid','<>',0)->select();
    foreach ($enterprise_buyer as $k => $v) {
        $enterprise_buyer_data[$k]['id'] = $v['id'];
        $enterprise_buyer_data[$k]['platform'] = $v['platform']==1?'平台买家':'其他买家';
        $enterprise_buyer_data[$k]['type'] = '企业买家';
        $enterprise_buyer_data[$k]['country'] = Db::name('country_code')->where('code_value',$v['country_code'])->field('code_name')->find()['code_name'];
        $enterprise_buyer_data[$k]['name'] = $v['company_name'];
        $enterprise_buyer_data[$k]['email'] = $v['company_email'];
        $enterprise_buyer_data[$k]['tel'] = $v['company_tel'];
        $enterprise_buyer_data[$k]['address'] = $v['company_address'];
        if($v['status']==0){
            $enterprise_buyer_data[$k]['status'] = '待审核';    
        }else if($v['status']==1){
            $enterprise_buyer_data[$k]['status'] = '已审核';    
        }else if($v['status']==2){
            $enterprise_buyer_data[$k]['status'] = '审核不通过';    
        }
        $enterprise_buyer_data[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
        $decl_user = Db::name('decl_user')->where('id',$v['uid'])->field(['user_name','user_tel'])->find();
        $enterprise_buyer_data[$k]['user_name'] = $decl_user['user_name'];
        $enterprise_buyer_data[$k]['user_tel'] = $decl_user['user_tel'];
        $enterprise_buyer_data[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".',1)" class="btn btn-primary btn-xs">审核</button>';
        unset($decl_user);
    }
    // 获取个人买家
    $personal_buyer_data = array();
    $personal_buyer = Db::name('decl_user_personal_buyer')->where('is_delete',0)->where('uid','<>',0)->select();
    foreach ($personal_buyer as $k => $v) {
        $personal_buyer_data[$k]['id'] = $v['id'];
        $personal_buyer_data[$k]['platform'] = $v['platform']==1?'平台买家':'其他买家';
        $personal_buyer_data[$k]['type'] = '个人买家';
        $personal_buyer_data[$k]['country'] = Db::name('country_code')->where('code_value',$v['country_code'])->field('code_name')->find()['code_name'];
        $personal_buyer_data[$k]['name'] = $v['last_name'].' '.$v['first_name'];
        $personal_buyer_data[$k]['email'] = $v['email'];
        $personal_buyer_data[$k]['tel'] = $v['tel'];
        $personal_buyer_data[$k]['address'] = $v['address'];
        if($v['status']==0){
            $personal_buyer_data[$k]['status'] = '待审核';    
        }else if($v['status']==1){
            $personal_buyer_data[$k]['status'] = '已审核';    
        }else if($v['status']==2){
            $personal_buyer_data[$k]['status'] = '审核不通过';    
        }
        $personal_buyer_data[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);  
        $decl_user = Db::name('decl_user')->where('id',$v['uid'])->field(['user_name','user_tel'])->find();
        $personal_buyer_data[$k]['user_name'] = $decl_user['user_name'];
        $personal_buyer_data[$k]['user_tel'] = $decl_user['user_tel'];
        $personal_buyer_data[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".',2)" class="btn btn-primary btn-xs">审核</button>';
        unset($decl_user);
    }
    $data = array_merge($enterprise_buyer_data,$personal_buyer_data);
    $last_names = array_column($data,'create_at');
    array_multisort($last_names,SORT_DESC,$data);
    return $data;
}

// 获取卖家
function getSellerFormUser()
{
    // 获取企业卖家
    $enterprise_buyer_data = array();
    $enterprise_buyer = Db::name('decl_user_enterprise_seller')->where('is_delete',0)->where('uid','<>',0)->select();
    foreach ($enterprise_buyer as $k => $v) {
        $enterprise_buyer_data[$k]['id'] = $v['id'];
        $enterprise_buyer_data[$k]['platform'] = $v['platform']==1?'平台卖家':'其他卖家';
        $enterprise_buyer_data[$k]['type'] = '企业卖家';
        $enterprise_buyer_data[$k]['country'] = Db::name('country_code')->where('code_value',$v['country_code'])->field('code_name')->find()['code_name'];
        $enterprise_buyer_data[$k]['name'] = $v['company_name'];
        $enterprise_buyer_data[$k]['email'] = $v['company_email'];
        $enterprise_buyer_data[$k]['tel'] = $v['company_tel'];
        $enterprise_buyer_data[$k]['address'] = $v['company_address'];
        if($v['status']==0){
            $enterprise_buyer_data[$k]['status'] = '待审核';    
        }else if($v['status']==1){
            $enterprise_buyer_data[$k]['status'] = '已审核';    
        }else if($v['status']==2){
            $enterprise_buyer_data[$k]['status'] = '审核不通过';    
        }
        $enterprise_buyer_data[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
        $decl_user = Db::name('decl_user')->where('id',$v['uid'])->field(['user_name','user_tel'])->find();
        $enterprise_buyer_data[$k]['user_name'] = $decl_user['user_name'];
        $enterprise_buyer_data[$k]['user_tel'] = $decl_user['user_tel'];
        $enterprise_buyer_data[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".',3)" class="btn btn-primary btn-xs">审核</button>';
        unset($decl_user);
    }
    // 获取个人卖家
    $personal_buyer_data = array();
    $personal_buyer = Db::name('decl_user_personal_seller')->where('is_delete',0)->where('uid','<>',0)->select();
    foreach ($personal_buyer as $k => $v) {
        $personal_buyer_data[$k]['id'] = $v['id'];
        $personal_buyer_data[$k]['platform'] = $v['platform']==1?'平台卖家':'其他卖家';
        $personal_buyer_data[$k]['type'] = '个人卖家';
        $personal_buyer_data[$k]['country'] = Db::name('country_code')->where('code_value',$v['country_code'])->field('code_name')->find()['code_name'];
        $personal_buyer_data[$k]['name'] = $v['last_name'].' '.$v['first_name'];
        $personal_buyer_data[$k]['email'] = $v['email'];
        $personal_buyer_data[$k]['tel'] = $v['tel'];
        $personal_buyer_data[$k]['address'] = $v['address'];
        if($v['status']==0){
            $personal_buyer_data[$k]['status'] = '待审核';    
        }else if($v['status']==1){
            $personal_buyer_data[$k]['status'] = '已审核';    
        }else if($v['status']==2){
            $personal_buyer_data[$k]['status'] = '审核不通过';    
        }
        $personal_buyer_data[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);  
        $decl_user = Db::name('decl_user')->where('id',$v['uid'])->field(['user_name','user_tel'])->find();
        $personal_buyer_data[$k]['user_name'] = $decl_user['user_name'];
        $personal_buyer_data[$k]['user_tel'] = $decl_user['user_tel'];
        $personal_buyer_data[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".',4)" class="btn btn-primary btn-xs">审核</button>';
        unset($decl_user);
    }
    $data = array_merge($enterprise_buyer_data,$personal_buyer_data);
    $last_names = array_column($data,'create_at');
    array_multisort($last_names,SORT_DESC,$data);
    return $data;

}
