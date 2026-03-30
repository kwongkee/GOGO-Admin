<?php
require_once IA_ROOT."/framework/class/weixin.account.class.php";
require_once IA_ROOT."/framework/class/curl.class.php";
load()->func('pdo');
//load()->func('cache.memcache');

// 发送消息方法
function sendMessagess($sendArr){
	$template_id = 'trXaMaikj3VTVCmx4l9urCvridhOrD_95q2Z1NG3ae0';//支付结果通知
	$postdata = array(
			'first'=>array(
					'value'=> $sendArr['first'],
					'color'=>'#173177'),

			'keyword1'=>array(
					'value'=> $sendArr['body'],
					'color'=>'#436EEE'),

			'keyword2'=>array(
					'value'=>'￥'.$sendArr['payMoney'].'元',
					'color'=>'#173177'),

			'keyword3'=>array(
					'value'=> $sendArr['paytime'],
					'color'=>'#173177'),

			'remark'=>array(
					'value'=>$sendArr['remark'],
					'color'=>'#808080'));

//	$sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
//	$account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
	
	//数据写入缓存
//	$account = senCache($sendArr['uniacid']);
	
	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	$abs = new WeiXinAccount($account);
	
	if(!empty($sendArr['Reurl'])){
		
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $sendArr['Reurl'], $topcolor = '#FF683F');
		
	}else{
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata);
	}	
	if (is_error($status)) {
		return '发送失败,原因为'.$status['message'];
	}else {
		return '发送成功';
	}
}


// 发送订单支付失败消息
function sendMsgError($sendArr){
	$template_id = 'trXaMaikj3VTVCmx4l9urCvridhOrD_95q2Z1NG3ae0';//支付结果通知
	$postdata = array(
			'first'=>array(
					'value'=> $sendArr['first'],
					'color'=>'#173177'),

			'keyword1'=>array(
					'value'=> $sendArr['body'],
					'color'=>'#436EEE'),

			'keyword2'=>array(
					'value'=>'￥'.$sendArr['payMoney'].'元',
					'color'=>'#173177'),

			'keyword3'=>array(
					'value'=> $sendArr['paytime'],
					'color'=>'#173177'),

			'remark'=>array(
					'value'=>$sendArr['remark'],
					'color'=>'#808080'));

//	$sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
//	$account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
	
	//数据写入缓存
//	$account = senCache($sendArr['uniacid']);
	
	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	$abs = new WeiXinAccount($account);
	
	if(!empty($sendArr['Reurl'])){
		
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $sendArr['Reurl'], $topcolor = '#FF683F');
		
	}else{
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata);
	}	
	if (is_error($status)) {
		return '发送失败,原因为'.$status['message'];
	}else {
		return '发送成功';
	}
}


// 发送订单成功消息
function sendMsgSuccess($sendArr) {
	$template_id = 'n7aQkN93Y-CeUBM491OMfzabqvLAWYmhG7awrIyyVNY';//支付成功结果通知
	$postdata = array(
		'first'=>array(
				'value'=> $sendArr['first'],
				'color'=>'#173177'),

		'keyword1'=>array(
				'value'=> $sendArr['parkTime'],//停车时长 12小时22分
				'color'=>'#436EEE'),

		'keyword2'=>array(
				'value'=>$sendArr['realTime'].' 分钟',//实计时长：10小时20分钟
				'color'=>'#173177'),

		'keyword3'=>array(
				'value'=> '￥'.$sendArr['payableMoney'].'元',//应付金额：$12元
				'color'=>'#173177'),
				
		'keyword4'=>array(
				'value'=> '-￥'.$sendArr['deducMoney'].'元',//抵扣金额：-￥2元
				'color'=>'#173177'),
		'keyword5'=>array(
				'value'=> '￥'.$sendArr['payMoney'].'元',//实付金额：￥10元
				'color'=>'#173177'),

		'remark'=>array(
				'value'=>$sendArr['remark'],
				'color'=>'#808080')
	);
	
	//设置数据缓存； 2018-06-01
//	$cacheArr['sendMsg'] = $receive['lowOrderId'];
	/*$account = null;
	//数据键名
	$key = 'sendMsg'.$sendArr['uniacid'];
	//不为空，取缓存数据；判断数据一致则直接退出
	if(!empty($cache = cache_load($key)))
	{
		$account = $cache;
		
	} else {
		
		$sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
		$account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
		
		//写入缓存
		cache_write($key,$account);
	}*/
	
	//数据写入缓存
//	$account = senCache($sendArr['uniacid']);
	
	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	$abs = new WeiXinAccount($account);
	
	if(!empty($sendArr['Reurl'])){
		
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $sendArr['Reurl'], $topcolor = '#FF683F');
		
	}else{
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata);
	}	
	if (is_error($status)) {
		return '发送失败,原因为'.$status['message'];
	}else {
		return 'success';
	}
}




// 发送发票方法
function sendInvoices($sendArr){
	$template_id = 'T5Btdmjeu_TeLf9ATzFNFI_tfNI6Ud0KjwiYY3Fttv8';//支付结果通知
	$postdata = array(
		'first'=>array(
				'value'=> $sendArr['first'],
				'color'=>'#173177'),
		'keyword1'=>array(
				'value'=> $sendArr['ordersn'],//调动单号
				'color'=>'#436EEE'),
		'keyword2'=>array(
				'value'=> $sendArr['name'],//申请姓名
				'color'=>'#436EEE'),
		'keyword3'=>array(
				'value'=> $sendArr['xmmc'],//项目名称
				'color'=>'#436EEE'),
				
		'keyword4'=>array(
				'value'=> $sendArr['c_date'],//申请时间
				'color'=>'#436EEE'),
				
		'remark'=>array(
				'value'=>$sendArr['remark'],
				'color'=>'#808080')
	);

//	$sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
//	$account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
	//数据写入缓存
//	$account = senCache($sendArr['uniacid']);

	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	$abs = new WeiXinAccount($account);
	
	if(!empty($sendArr['Reurl'])){
		
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $sendArr['Reurl'], $topcolor = '#FF683F');
		
	}else{
		
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata);
	}	
	if (is_error($status)) {
		
		return '发送失败,原因为'.$status['message'];
		
	}else {
		return '发送成功';
	}
}




/**
 * 收款支付成功通知；2018-01-30
 */
function sendPay_m($sendArr){
	$template_id = 'Fh3uY4ohrWSwKUVtTB71IWgR-7KAcKIAljkeELkyAxs';//模板ID
	$postdata = array(
		'first'=>array(
				'value'=> $sendArr['first'],
				'color'=>'#173177'),
		'keyword1'=>array(
				'value'=> $sendArr['money'],//交易金额
				'color'=>'#436EEE'),
		'keyword2'=>array(
				'value'=> $sendArr['shop_name'],//商品名称
				'color'=>'#436EEE'),
		'keyword3'=>array(
				'value'=> $sendArr['ordersn'],//支付订单号
				'color'=>'#436EEE'),
		'keyword4'=>array(
				'value'=> $sendArr['time'],//交易时间
				'color'=>'#436EEE'),
		'keyword5'=>array(
				'value'=> $sendArr['feet'],//本次返利
				'color'=>'#436EEE'),
		'remark'=>array(
				'value'=>$sendArr['remark'],
				'color'=>'#808080')
	);
//
//	$sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
//	$account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
	//数据写入缓存
//	$account = senCache($sendArr['uniacid']);

	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	$abs = new WeiXinAccount($account);
	
	if(!empty($sendArr['Reurl'])){
		
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $sendArr['Reurl'], $topcolor = '#FF683F');
		
	}else{
		
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata);
	}	
	if (is_error($status)) {
		
		return '发送失败,原因为'.$status['message'];
		
	}else {
		return '发送成功';
	}
}


/**
 * 2018-3-2
 * 实名验证，或工商验证模板消息
 */

function sendVFmsg($sendArr)
{
	$postdata = array(
		'first' => array(
			'value' => $sendArr['first'],
			'color' => ''
		),
		
		'keyword1' => array(
			'value' => $sendArr['fee'].'元',//验证费
			'color' => ''
		),
		
		'keyword2' => array(
			'value' => $sendArr['xmmc'],//项目名称
			'color' => ''
		),
		
		'keyword3' => array(
			'value' => $sendArr['time'],//验证时间
			'color' => ''
		),
		
		'remark' => array(
			'value' => $sendArr['remark'],//详细
			'color' => ''
		),
	);
	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	//数据写入缓存
//	$account = senCache($sendArr['uniacid']);
	//实例化微信类
	$abs = new WeiXinAccount($account);
	if(!empty($sendArr['Reurl'])){
		$status = $abs->sendTplNotice($sendArr['touser'],$sendArr['template_id'],$postdata,$sendArr['Reurl'],$topcolor = '#FF683F');
	}else { 
		$status = $abs->sendTplNotice($sendArr['touser'],$sendArr['template_id'],$postdata);
	}
	
	if(is_error($status)){
		return '发送失败，原因'.$status['message'];
	}else {
		return '发送成功';
	}	
}




/**
 * 2018-3-5   
 * sendPkmsg()
 * 停车确认模板消息
 * template_id ： 模板消息
 * touser ： 接收信息用户openid
 * Reurl ： 详情链接地址
 * uniacid : 所属公众号id
 * address ： 车位地址
 * first : 通知结果；
 * code : 车位编码
 * time ： 停入时间
 * remark ：详细引导
 */
function sendPkmsg($sendArr)
{
	$postdata = array(
		'first' => array(
			'value' => $sendArr['first'],
			'color' => ''
		),
		
		'keyword1' => array(
			'value' => $sendArr['address'],//车位地址
			'color' => ''
		),
		
		'keyword2' => array(
			'value' => $sendArr['code'],//车位编码
			'color' => ''
		),
		
		'keyword3' => array(
			'value' => $sendArr['time'],//停入时间
			'color' => ''
		),
		
		'remark' => array(
			'value' => $sendArr['remark'],//详细
			'color' => ''
		),
	);
	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	//数据写入缓存
//	$account = senCache($sendArr['uniacid']);
	//实例化微信类
	$abs = new WeiXinAccount($account);
	if(!empty($sendArr['Reurl'])){
		$status = $abs->sendTplNotice($sendArr['touser'],$sendArr['template_id'],$postdata,$sendArr['Reurl'],$topcolor = '#FF683F');
	}else { 
		$status = $abs->sendTplNotice($sendArr['touser'],$sendArr['template_id'],$postdata);
	}
	
	if(is_error($status)){
		return '发送失败，原因'.$status['message'];
	}else {
		return 'success';
	}	
}



/**
 * 2018-3-13  
 * sendSing()
 * 授权签约模板
 * template_id ： 模板消息
 * touser ： 接收信息用户openid
 * Reurl ： 详情链接地址
 * uniacid : 所属公众号id
 * first : 通知结果；
 * keyword1 ： 扣费方案
 * keyword2 : 扣费账号
 * keyword3 ： 扣费方式
 * remark ：详细引导
 */
function sendSing($sendArr)
{
	$postdata = array(
		'first' => array(
			'value' => $sendArr['first'],
			'color' => ''
		),
		
		'keyword1' => array(
			'value' => $sendArr['keyword1'],//扣费方案
			'color' => ''
		),
		
		'keyword2' => array(
			'value' => $sendArr['keyword2'],//扣费账号
			'color' => ''
		),
		
		'keyword3' => array(
			'value' => $sendArr['keyword3'],//扣费方式
			'color' => ''
		),
		
		'remark' => array(
			'value' => $sendArr['remark'],//详细
			'color' => ''
		),
	);
	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	//数据写入缓存
//	$account = senCache($sendArr['uniacid']);
	//实例化微信类
	$abs = new WeiXinAccount($account);
	if(!empty($sendArr['Reurl'])){
		$status = $abs->sendTplNotice($sendArr['touser'],$sendArr['template_id'],$postdata,$sendArr['Reurl'],$topcolor = '#FF683F');
	}else { 
		$status = $abs->sendTplNotice($sendArr['touser'],$sendArr['template_id'],$postdata);
	}
	
	if(is_error($status)){
		return '发送失败，原因'.$status['message'];
	}else {
		return '发送成功';
	}	
}



/**
 * 时间计算函数：
 * @begin_time;开始时间
 * @end_time;结束时间
 * 返回数组[day,hour,min,sec]
 */
function timediff($begin_time,$end_time)
{
      if($begin_time < $end_time){
         $starttime = $begin_time;
         $endtime = $end_time;
      }else{
         $starttime = $end_time;
         $endtime = $begin_time;
      }
      //计算天数
      $timediff = $endtime-$starttime;
      $days = intval($timediff/86400);
      //计算小时数
      $remain = $timediff%86400;
      $hours = intval($remain/3600);
      //计算分钟数
      $remain = $remain%3600;
      $mins = intval($remain/60)+1;
      //计算秒数
      $secs = $remain%60;
      $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
      return $res;
}




function senCache($senKey)
{
	$account = null;
	//数据键名
	$key = 'sendMsg'.$senKey;
	/**
	 * a:2:{
	 * 	s:5:"token";
	 *  s:157:"11_1h-vLWfkClIgIgwApBrbemLTqinRnCmCoKZwJbbAi05NAGpWDXeMycdl35u3MbNLM-00IGuE8ZEGtgEtK2BXxX0Kc7zlfbKSiLxbLVx0NsyZJlm1n2TwKDVpKMRA1bergKt8oF7SlsAt_c7fFNDdAFAVDT";
	 * s:6:"expire";
	 * i:1530695689;}
	 */
	//不为空，取缓存数据；判断数据一致则直接退出
	if(!empty($cache = cache_load($key)))
	{
		$account = $cache;
		
	} else {
		
		$sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
		$account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
		
		//写入缓存
		cache_write($key,$account);
	}
	
	return $account;
}



// 发送消息方法
function sendObtainMsg($sendArr=null){
    $template_id = 'Sik-JhN3Uo7WaLNUF_RvUk5cQJvdS7vLSn_vyhvfnXk';//支付结果通知
    $postdata = [
        'first'=>[
            'value'=> '您好！已成功获取月卡资格！',
            'color'=>'#173177'],
        
        'keyword1'=>array(
            'value'=> date('Y-m-d H:i:s',time()),
            'color'=>'#436EEE'),
        
        'keyword2'=>array(
            'value'=>$sendArr['time'],
            'color'=>'#173177'),
        
        'remark'=>array(
            'value'=>'点击可查看月卡',
            'color'=>'#808080')
    ];
    
    //	$sql = 'SELECT * FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid';
    //	$account = pdo_fetch($sql, array(':uniacid' => $sendArr['uniacid']));
    
    //数据写入缓存
    //	$account = senCache($sendArr['uniacid']);
    
    //查询公众号信息
    $account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
    $abs = new WeiXinAccount($account);
    
    if(!empty($sendArr['url'])){
        
        $status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $sendArr['url'], $topcolor = '#FF683F');
        
    }else{
        $status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata);
    }
    if (is_error($status)) {
        return '发送失败,原因为'.$status['message'];
    }else {
        return '发送成功';
    }
}


/**
 * 发送退款消息
 */
function sendMsgRefund($sendArr){
	$template_id = 'z6ZvP_Zj8Pw1QwIm6aSuD6ibQzdLc66pAU48f4De7Gw';//支付成功结果通知
	$postdata = array(
		'first'=>array(
				'value'=> $sendArr['first'],
				'color'=>'#173177'),

		'keyword1'=>array(
				'value'=> $sendArr['RefundType'],//退款通道
				'color'=>'#436EEE'),

		'keyword2'=>array(
				'value'=>'￥'.$sendArr['RefundMoney'].'元',//退款金额
				'color'=>'#173177'),

		'keyword3'=>array(
				'value'=> $sendArr['RefundDate'],//退款提交日期
				'color'=>'#173177'),

		'remark'=>array(
				'value'=>$sendArr['remark'],
				'color'=>'#808080')
	);
	
	//查询公众号信息
	$account = pdo_get('account_wechats',array('uniacid'=>$sendArr['uniacid']));
	$abs = new WeiXinAccount($account);
	
	if(!empty($sendArr['Reurl'])){
		
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata, $sendArr['Reurl'], $topcolor = '#FF683F');
		
	}else{
		$status = $abs->sendTplNotice($sendArr['touser'], $template_id, $postdata);
	}	
	if (is_error($status)) {
		return '发送失败,原因为'.$status['message'];
	}else {
		return 'success';
	}
}


//发送支付回调后的模板
/**
 * 模板请求API
 * 2018-09-12
 * update  2018-09-22
 */
function sendSuccesTempl($temp) {
	$templdate=[
		'touser'	 =>$temp['touser'],
		'template_id'=>'671nviCnAMjycHKkjzeUg3NqnM0HwIBnt8bKnDEjf8g',
		'url'		 =>$temp['Reurl']?$temp['Reurl']:'',
		'data'=>[
			'first'=>[
				'value'=>$temp['first'],//'',
				'color'=>'#173177',
			],
			'keyword1'=>[
				'value'=>$temp['parkTime'].' 分钟',//停车时长
				'color'=>'#436EEE',
			],
			'keyword2'=>[
				'value'=>$temp['realTime'].' 分钟',
				'color'=>'#436EEE',
			],
			'keyword3'=>[
				'value'=>'￥'.$temp['payableMoney'].'元',//应付金额
				'color'=>'#436EEE',
			],
			'keyword4'=>[
				'value'=>'￥'.$temp['payMoney'].'元',
				'color'=>'#436EEE',
			],
			'remark'=>[
				'value'=>$temp['remark'],
				'color'=>'#808080'
			],
		]
	];
	
	$postUrl = 'http://shop.gogo198.cn/foll/public/?s=api/wechat/template';
	$tmp = ['template'=>serialize($templdate),'uniacid'=>$temp['uniacid']];
	$res = postJson($postUrl,$tmp,true);
	return $res;
}


//旧文件备份   2018-09-22
function sendSuccesTemplbase($temp) {
	$templdate=[
		'touser'	 =>$temp['touser'],
		'template_id'=>'n7aQkN93Y-CeUBM491OMfzabqvLAWYmhG7awrIyyVNY',
		'url'		 =>$temp['Reurl']?$temp['Reurl']:'',
		'data'=>[
			'first'=>[
				'value'=>$temp['first'],//'',
				'color'=>'#173177',
			],
			'keyword1'=>[
				'value'=>$temp['parkTime'].' 分钟',//停车时长
				'color'=>'#436EEE',
			],
			'keyword2'=>[
				'value'=>$temp['realTime'].' 分钟',
				'color'=>'#436EEE',
			],
			'keyword3'=>[
				'value'=>'￥'.$temp['payableMoney'].'元',//应付金额
				'color'=>'#436EEE',
			],
			'keyword4'=>[
				'value'=>'-￥'.$temp['deducMoney'].'元',//抵扣金额
				'color'=>'#436EEE',
			],
			'keyword5'=>[
				'value'=>'￥'.$temp['payMoney'].'元',
				'color'=>'#436EEE',
			],
			'remark'=>[
				'value'=>$temp['remark'],
				'color'=>'#808080'
			],
		]
	];
	
	$postUrl = 'http://shop.gogo198.cn/foll/public/?s=api/wechat/template';
	$tmp = ['template'=>serialize($templdate),'uniacid'=>$temp['uniacid']];
	$res = postJson($postUrl,$tmp,true);
	return $res;
}

/**
 * 发送失败模板
 * 2018-09-12
 */
function sendErrorTempl($temp) {
	$templdate=[
		'touser'	 =>$temp['touser'],
		'template_id'=>'trXaMaikj3VTVCmx4l9urCvridhOrD_95q2Z1NG3ae0',
		'url'		 =>$temp['Reurl']?$temp['Reurl']:'',
		'data'=>[
			'first'=>[
				'value'=>$temp['first'],//
				'color'=>'#173177',
			],
			'keyword1'=>[
				'value'=>$temp['body'],//
				'color'=>'#436EEE',
			],
			'keyword2'=>[
				'value'=>'￥'.$temp['payMoney'].'元',
				'color'=>'#436EEE',
			],
			'keyword3'=>[
				'value'=>$temp['paytime'],//支付时间
				'color'=>'#436EEE',
			],
			'remark'=>[
				'value'=>$temp['remark'],
				'color'=>'#808080'
			],
		]
	];
	
	$postUrl = 'http://shop.gogo198.cn/foll/public/?s=api/wechat/template';
	$tmp	 = ['template'=>serialize($templdate),'uniacid'=>$temp['uniacid']];
	$res	 = postJson($postUrl,$tmp,true);
	return $res;
}

/**
 *	发送Json请求 
 */
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



//function sendChargeLottery($str){
//
//}


/*
{{first.DATA}}
消费项目：{{keyword1.DATA}}
消费金额：{{keyword2.DATA}}
消费时间：{{keyword4.DATA}}
{{remark.DATA}}

您好，您的车费扣收失败。
消费项目：停车费用
消费金额：9元
消费时间：2017年11月22日 14:38
请点击以下订单，继续完成支付*/

/**
 * 
 * 成功：您好，您的停车服务费扣费成功！
 * 失败：抱歉，您的停车服务费扣费失败！
 * 
 * 消费项目：停车服务费
 * 消费时间：201711270328至201711280328
 * 
 * 
 * 
 *  您好，您的订单支付成功！
	交易金额：9.00元
	商品名称：天河科技桂城店消费券
	交易订单：RN20180130ADJKKL
	交易时间:2018年01月30日 14:38:33
	本次返利：0.50元
	更多返利消费以及查看返利钱包，请点击详情
	
	
	//实名验证模板消息
	您好,您的实名验证已通过
	验证费：3.00元
	验证名称：实名认证
	验证时间：2018年3月2日 14:33:19
	查看更详细资料，请点击详情；
 * 
 * 
 * 
 * 2018-03-13
 * 无感签约
 * 
 * 授权签约结果通知
 * 您好，您的停车扣费授权已经成功！
 * 扣费方案：银联无感支付
 * 扣费账号：信用卡号
 * 扣费方式：授权免密代扣
 * 您可点击详情，查询或变更您的授权
 * 
 * 
 */

?>