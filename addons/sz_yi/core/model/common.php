<?php
// 模块LTD提供
class Sz_DYi_Common
{


	// 检测当前登陆用户的读写权限  2019-10-09
	public function permission(){
		global  $_GPC;
        $forward = './index.php?c=account&a=display';
        message("您当前账号禁止使用！", $forward);
        
        // 调用方式 $perm = m('common')->permission();
	}

	public function dataMove()
	{
		$dbprefix = 'ewei_shop';
		$_obf_DTgCBTMJFRkqNjQWDwgPJTwnOB8HORE_ = 'sz_yi';
		$result = pdo_fetchall('SHOW TABLES LIKE \'%' . $_obf_DTgCBTMJFRkqNjQWDwgPJTwnOB8HORE_ . '%\'');

		if (!$result) {
			return false;
		}

		foreach ($result as $tables) {
			foreach ($tables as $tablename) {
				$sql = 'drop table `' . $tablename . '`';
				pdo_query($sql);
			}
		}

		$result = pdo_fetchall('SHOW TABLES LIKE \'%' . $dbprefix . '%\'');

		if (!$result) {
			return false;
		}

		foreach ($result as $tables) {
			foreach ($tables as $tablename) {
				$sql = 'rename table `' . $tablename . '` to `' . str_replace($dbprefix, $_obf_DTgCBTMJFRkqNjQWDwgPJTwnOB8HORE_, $tablename) . '`';
				pdo_query($sql);
			}
		}

		if (!pdo_fieldexists('sz_yi_member', 'regtype')) {
			pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `regtype` tinyint(3) DEFAULT \'1\';');
		}

		if (!pdo_fieldexists('sz_yi_member', 'isbindmobile')) {
			pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isbindmobile` tinyint(3) DEFAULT \'0\';');
		}

		if (!pdo_fieldexists('sz_yi_member', 'isjumpbind')) {
			pdo_query('ALTER TABLE ' . tablename('sz_yi_member') . ' ADD    `isjumpbind` tinyint(3) DEFAULT \'0\';');
		}

		if (!pdo_fieldexists('sz_yi_member', 'pwd')) {
			pdo_query('ALTER TABLE  ' . tablename('sz_yi_member') . ' CHANGE  `pwd`  `pwd` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;');
		}

		if (!pdo_fieldexists('sz_yi_goods', 'cates')) {
			pdo_query('ALTER TABLE ' . tablename('sz_yi_goods') . ' ADD     `cates` text;');
		}
	}

	public function getSetData($uniacid = 0)
	{
		global $_W;

		if (empty($uniacid)) {
			$uniacid = $_W['uniacid'];
		}

		$set = m('cache')->getArray('sysset', $uniacid);

		if (empty($set)) {
			$set = pdo_fetch('select * from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));

			if (empty($set)) {
				$set = array();
			}

			m('cache')->set('sysset', $set, $uniacid);
		}

		return $set;
	}

	public function getSysset($key = '', $uniacid = 0)
	{
		global $_W;
		global $_GPC;
		$set = $this->getSetData($uniacid);
		$allset = unserialize($set['sets']);
		$retsets = array();

		if (!empty($key)) {
			if (is_array($key)) {
				foreach ($key as $k) {
					$retsets[$k] = isset($allset[$k]) ? $allset[$k] : array();
				}
			}
			else {
				$retsets = (isset($allset[$key]) ? $allset[$key] : array());
			}

			return $retsets;
		}

		return $allset;
	}
	/***	 * @params 支付订单信息	 * @alipay 支付配置信息	 * @type   支付类型	 * @openid 用户openid	 */
	public function alipay_build($params, $alipay = array(), $type = 0, $openid = '')
	{
		global $_W;
		$tid = $params['tid'];
		$set = array();
		$set['partner'] = $alipay['partner'];
		$set['seller_id'] = $alipay['account'];

		if (!isMobile()) {
			$set['seller_id'] = $alipay['partner'];
			$set['service'] = 'create_direct_pay_by_user';
		}
		else {
			$set['service'] = 'alipay.wap.create.direct.pay.by.user';
		}

		$set['_input_charset'] = 'utf-8';
		$set['sign_type'] = 'MD5';

		if (empty($type)) {
			$set['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/notify.php';
			$set['return_url'] = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=order&p=pay&op=return&openid=' . $openid;
		}
		else {
			$set['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/alipay/notify.php';
			$set['return_url'] = $_W['siteroot'] . 'app/index.php?i=' . $_W['uniacid'] . '&c=entry&m=sz_yi&do=member&p=recharge&op=return&openid=' . $openid;
		}

		$set['out_trade_no'] = $tid;
		$set['subject'] = $params['title'];
		$set['total_fee'] = $params['fee'];
		$set['payment_type'] = 1;
		$set['body'] = $_W['uniacid'] . ':' . $type;
		$prepares = array();

		foreach ($set as $key => $value) {
			if (($key != 'sign') && ($key != 'sign_type')) {
				$prepares[] = $key . '=' . $value;
			}
		}

		sort($prepares);
		$string = implode($prepares, '&');
		$string .= $alipay['secret'];
		$set['sign'] = md5($string);
		return array('url' => ALIPAY_GATEWAY . '?' . http_build_query($set, '', '&'));
	}


	//通莞微信公众号支付   101540254006    f8ee27742a68418da52de4fca59b999e  GOGO
	//喜柏：	 101570223660  b4f16b4526b046c580e363fcfcd07c82
	public function tgwechats($params, $config){

		$package = array();
		$package['account'] 	= $config['account'];//商户号
		$package['appId']	    = 'wx76d541cc3e471aeb';
		$package['payMoney'] 	= $params['fee'];//交易金额
		$package['lowOrderId']  = $params['tid'];//订单号
		$package['body'] 		= $params['title'];//商品描述
		$package['notifyUrl'] 	= 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/notify.php';
		$package['openId'] 	  	= $params['openid'];//用户ID
		$package['isMinipg']	= '2';
		//转换key=value&key=value;
		$str = $this->tostrings($package);
		//拼接加密字串
		$str .= '&key=' . $config['key'];
		//MD5加密字串
		//$sign = md5($str);
		//返回加密字串转换成大写字母
		$package['sign'] = strtoupper(md5($str));
		//数据包转换成json格式
		$data =  json_encode($package);
		//数据请求地址，post形式传输
		$url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
		//数据请求地址，post形式传输
		$response = $this->ihttp_posts($url,$data);
		//解析json数据
		$response = json_decode($response,TRUE);
		//直接返回支付URL地址
		return $response;
	}
	
	
	//喜柏：	 101570223660  b4f16b4526b046c580e363fcfcd07c82
	public function tgwechats1($params, $config){

		$package = array();
		$package['account'] 	= $config['account'];//商户号
		$package['payMoney'] 	= $params['fee'];//交易金额
		$package['lowOrderId']  = $params['tid'];//订单号
		$package['body'] 		= $params['title'];//商品描述
		$package['notifyUrl'] = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/notify.php';
		$package['openId'] 	  = $params['openid'];//用户ID
		//转换key=value&key=value;
		$str = $this->tostrings($package);
		//拼接加密字串
		$str .= '&key=' . $config['key'];
		//MD5加密字串
		//$sign = md5($str);
		//返回加密字串转换成大写字母
		$package['sign'] = strtoupper(md5($str));
		//数据包转换成json格式
		$data =  json_encode($package);
		//数据请求地址，post形式传输
		$url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
		//数据请求地址，post形式传输
		$response = $this->ihttp_posts($url,$data);
		//解析json数据
		$response = json_decode($response,TRUE);
		//直接返回支付URL地址
		return $response;
	}
	
	/**
	 * 通莞微信公众号支付。 
	 * @params 订单支付信息
	 * @config  配置信息
	 * 2018-08-17
	 */
	public function tgwechat($url,$post_data) {
		$json = json_encode($post_data);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($json)));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	//判断是否敏感商品
	public function is_sensitive($hscode_info){
		$sensitive_arr = ['01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','32','33','34','68','69','70','93'];

		if(in_array($hscode_info['two'],$sensitive_arr)){
			return 1;//是敏感
		}else{
			return -1;//不是敏感
		}
	}

	//php爬取归类通，使用代理ip
	public function getGuiLeiTong($keywords){
		//第一种：
//		$ch = curl_init();
//		curl_setopt($ch, CURLOPT_URL, 'https://hsciq.com/HSCN/Search?keywords='.$keywords);
//		//设置超时
//		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//		// 如果是请求https时，要打开下面两个ssl安全校验
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//表示string输出，0为直接输出；
//		curl_setopt($ch,CURLOPT_HEADER,1);
//		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type:application/json;","Accept:application/json"));
//		$html_source = curl_exec($ch);
//		curl_close($ch);

		//第二种:
		$ctx = stream_context_create(array(
				'http' => array(
					'method' => 'GET',
					'timeout' => 300,//访问时间超过3600秒出错
					'proxy' => 'tps632.kdlapi.com:15818',
					'request_fulluri' => True,
				)
			)
		);
		$html_source = file_get_contents('https://hsciq.com/HSCN/Search?keywords='.$keywords,false,$ctx);
//		$html_source = file_get_contents('https://hsciq.com/HSCN/Search?keywords='.$keywords);
		$parament = "/<a.*?href=\"\/HSCN\/Code\/.*?\" target=\"_blank\">(\d*?)<\/a>/s";
		preg_match_all($parament,$html_source,$matchs);
		foreach($matchs[1] as $k=>$v){
			if(!empty($v)){
				pdo_insert('customs_hscode_gui_lei_tong',['hscode'=>$v,'keyword'=>$keywords]);
			}
		}
		return $matchs[1][0];//返回第一个

		//第三种：
//		$page_url = 'https://hsciq.com/HSCN/Search?keywords='.$keywords;
//		$ch = curl_init();
//		$tunnelhost = "tps632.kdlapi.com";
//		$tunnelport = "15818";
//		$proxy = $tunnelhost.":".$tunnelport;
//
//		//隧道用户名密码
//		$username   = "t15102282001142";
//		$password   = "vzpo77tz";
//		curl_setopt($ch, CURLOPT_URL, $page_url);
//		//发送post请求
//		$requestData["post"] = "send post request";
//		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
//		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
//		//设置代理
//		curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
//		curl_setopt($ch, CURLOPT_PROXY, $proxy);
//		//设置代理用户名密码
//		curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
//		curl_setopt($ch, CURLOPT_PROXYUSERPWD, "{$username}:{$password}");
//		//自定义header
//		$headers = array();
//		$headers["user-agent"] = 'User-Agent: Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0);';
//		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//		//自定义cookie
//		curl_setopt($ch, CURLOPT_COOKIE,'');
//		curl_setopt($ch, CURLOPT_ENCODING, 'gzip'); //使用gzip压缩传输数据让访问更快
//		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
//		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//		curl_setopt($ch, CURLOPT_HEADER, true);
//		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//		$html_source = curl_exec($ch);
////		$info = curl_getinfo($ch);
//		curl_close($ch);
		//hsciq
//		$parament = "/<a class=\"\" href=\"\/HSCN\/Code\/.*?\" target=\"_blank\">(.*?)<\/a>/s";
//		preg_match_all($parament,$html_source,$matchs);
//		foreach($matchs[1] as $k=>$v){
//			if(!empty($v)){
//				pdo_insert('customs_hscode_gui_lei_tong',['hscode'=>$v,'keyword'=>$keywords]);
//			}
//		}
//		return $matchs[1][0];

		//归类通
//		$parament = "/<table.*id=\"zngl_result\">(.*?)<\/table>/s";
//		preg_match_all($parament,$html_source,$matchs);
//
//		$parament2 = "/<a.*class=\"zngl_hscode\".*>(.*?)<\/a>/";
//		preg_match_all($parament2,$matchs[0][0],$matchs2);
//
//		//将爬到的信息，保存到数据表
//		foreach($matchs2[1] as $k=>$v){
//			if($v!='查阅' && !empty($v)){
//				pdo_insert('customs_hscode_gui_lei_tong',['hscode'=>$v,'keyword'=>$keywords]);
//			}
//		}
//		return $matchs2[1][0];
	}

	//获取计量单位
	public function getUnit($code_name){
		$res = pdo_fetchcolumn('select code_value from '.tablename('unit').' where code_name=:code_name',[':code_name'=>$code_name]);
		return $res;
	}

	//同品同价同重,取平均值，统一调整
	public function tongPinPriceWeight($arr,$config,$method,$key,$secret,$openid){
		foreach($arr as $k2=>$v2){
			$goods = $this->onebound_data_handle($v2['itemName'],$config,$method,$key,$secret);//返回按“商品名称”搜索的结果
			if(!empty($goods)){
				$count = count($goods);
				$insert_data = '';
				$true_goods = '';
				if($count>3){
					//获取最高价，中间价，最低价
					$price = array_column($goods,'price');
					array_multisort($price,SORT_DESC,$goods);
					$max_price = $goods[0]['price'];//最高价
					$min_price = $goods[$count-1]['price'];//最低价
					$avg_price = ($max_price+$min_price)/2;//中间价 32.6
					//找出与中间价相临近的价钱
					$min_value = 0;//记录最小值
					foreach($goods as $k3=>$v3){
						$value = abs($avg_price-$v3['price']);//27.3 11.4  25.58 27.3
						if(empty($min_value)){
							$min_value=$value;
							$true_goods = $v3;
						}elseif($min_value>=$value){
							$min_value=$value;
							$true_goods = $v3;
						}
					}
					$platform = '';
					if($true_goods['type']==2){
						//1688
						$platform = '1688';
					}elseif($true_goods['type']==1){
						//淘宝
						$platform = 'taobao';
					}

					//商品详情为空则查询万邦接口
					if(empty($true_goods['goods_desc'])){
						$res = $this->onebound_itemGet($platform,$key,$secret,$true_goods['num_iid'],$method);
						$insert_data = [
							'brand'=>$res['item']['brand'],//品牌
							'desc'=>$res['item']['desc'],//描述
							'item_imgs'=>$res['item']['item_imgs'],//商品图片
							'item_weight'=>$res['item']['item_weight'],//商品重量
							'post_fee'=>$res['item']['post_fee'],//邮费
							'ems_fee'=>$res['item']['ems_fee'],//EMS费用
							'express_fee'=>$res['item']['express_fee'],//EMS费用
							'sellUnit'=>$res['item']['sellUnit'],//出售单位
							'unit'=>$res['item']['unit'],//单位
							'desc_img'=>$res['item']['desc_img'],//详情图片列表
						];
						if(!empty($res['item']['skus']['sku'])){
							//查询同一商品价钱的规格（随机）
							foreach($res['item']['skus']['sku'] as $k3=>$v3){
								if($v3['price']==$true_goods['price']){
									array_push($insert_data,['sku'=>$v3]);
									break;
								}
							}
						}
						pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
					}
				}
				elseif($count<3 && $count>1){
					//获取最高价
					$price = array_column($goods,'price');
					array_multisort($price,SORT_DESC,$goods);
					$max_price = $goods[0]['price'];//最高价

					foreach($goods as $k3=>$v3){
						if($max_price==$v3['price']){
							$true_goods = $v3;
						}
					}
					$platform = '';
					if($true_goods['type']==2){
						//1688
						$platform = '1688';
					}elseif($true_goods['type']==1){
						//淘宝
						$platform = 'taobao';
					}

					//商品详情为空则查询万邦接口
					if(empty($true_goods['goods_desc'])){
						$res = $this->onebound_itemGet($platform,$key,$secret,$true_goods['num_iid'],$method);
						$insert_data = [
							'brand'=>$res['item']['brand'],//品牌
							'desc'=>$res['item']['desc'],//描述
							'item_imgs'=>$res['item']['item_imgs'],//商品图片
							'item_weight'=>$res['item']['item_weight'],//商品重量
							'post_fee'=>$res['item']['post_fee'],//邮费
							'ems_fee'=>$res['item']['ems_fee'],//EMS费用
							'express_fee'=>$res['item']['express_fee'],//EMS费用
							'sellUnit'=>$res['item']['sellUnit'],//出售单位
							'unit'=>$res['item']['unit'],//单位
							'desc_img'=>$res['item']['desc_img'],//详情图片列表
						];
						if(!empty($res['item']['skus']['sku'])){
							//查询同一商品价钱的规格（随机）
							foreach($res['item']['skus']['sku'] as $k3=>$v3){
								if($v3['price']==$true_goods['price']){
									array_push($insert_data,['sku'=>$v3]);
									break;
								}
							}
						}
						pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
					}
				}
				elseif($count==1){
					//获取当前价格
					$true_goods = $goods[0];
					$platform = '';
					if($true_goods['type']==2){
						//1688
						$platform = '1688';
					}elseif($true_goods['type']==1){
						//淘宝
						$platform = 'taobao';
					}

					//商品详情为空则查询万邦接口
					if(empty($true_goods['goods_desc'])){
						$res = $this->onebound_itemGet($platform,$key,$secret,$true_goods['num_iid'],$method);
						$insert_data = [
							'brand'=>$res['item']['brand'],//品牌
							'desc'=>$res['item']['desc'],//描述
							'item_imgs'=>$res['item']['item_imgs'],//商品图片
							'item_weight'=>$res['item']['item_weight'],//商品重量
							'post_fee'=>$res['item']['post_fee'],//邮费
							'ems_fee'=>$res['item']['ems_fee'],//EMS费用
							'express_fee'=>$res['item']['express_fee'],//EMS费用
							'sellUnit'=>$res['item']['sellUnit'],//出售单位
							'unit'=>$res['item']['unit'],//单位
							'desc_img'=>$res['item']['desc_img'],//详情图片列表
						];
						if(!empty($res['item']['skus']['sku'])){
							//查询同一商品价钱的规格（随机）
							foreach($res['item']['skus']['sku'] as $k3=>$v3){
								if($v3['price']==$true_goods['price']){
									array_push($insert_data,['sku'=>$v3]);
									break;
								}
							}
						}
						pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
					}
				}

				$is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$v2['id']]);
				$onebound_total_goods=[];
				if(isset($true_goods['id'])) {
					$onebound_total_goods = pdo_fetch('select * from ' . tablename('onebound_total_goods') . ' where id=:id', [':id' => $true_goods['id']]);
					$onebound_total_goods['goods_desc'] = json_decode($onebound_total_goods['goods_desc'],true);
				}

				if($is_have['id']>0){
					$res = pdo_update('customs_goods_pre_fill_log',[
						'price'=>$onebound_total_goods['price'],
						'totalPrice'=>$v2['qty']*$onebound_total_goods['price'],//申报数量*单价
						'charge'=>$v2['qty']*$onebound_total_goods['price'],
						'onebound_gid'=>$true_goods['id'],
					],['good_id'=>$v2['id']]);
				}
				elseif(isset($true_goods['id'])){
					$res = pdo_insert('customs_goods_pre_fill_log',[
						'pre_batch_num'=>$v2['pre_batch_num'],
						'openid'=>$openid,
						'type'=>1,
						'itemNo'=>$v2['itemNo'],
						'itemName'=>$v2['itemName'],
						'gcode'=>$v2['gcode'],
						'currency'=>$v2['currency'],
						'qty'=>$v2['qty'],
						'qty1'=>$v2['qty1'],
						'gmodel'=>$v2['gmodel'],
						'price'=>$onebound_total_goods['price'],
						'totalPrice'=>$v2['qty']*$onebound_total_goods['price'],//申报数量*单价
						'charge'=>$v2['qty']*$onebound_total_goods['price'],
						'chargeDate'=>$v2['chargeDate'],
						'logisticsNo'=>$v2['logisticsNo'],
						'freight'=>$v2['freight'],
						'insuredFee'=>$v2['insuredFee'],
						'barCode'=>$v2['barCode'],
						'grossWeight'=>$v2['grossWeight'],
						'netWeight'=>$v2['netWeight'],
						'packNo'=>$v2['packNo'],
						'goodsInfo'=>$v2['goodsInfo'],
//						$this->getUnit($onebound_total_goods['goods_desc']['sellUnit'])
						'unit'=>!empty($v2['unit'])?$v2['unit']:'',
						'unit1'=>!empty($v2['unit1'])?$v2['unit1']:'',
						'good_id'=>$v2['id'],
						'onebound_gid'=>$true_goods['id'],
						'is_fill'=>1
					]);
				}
			}
		}
	}

	//异品异价异重,随机取平台名，逐一调整
	public function yiPinPriceWeight($arr,$config,$method,$key,$secret,$openid){
		foreach($arr as $k2=>$v2){
			$goods = $this->onebound_data_handle($v2['itemName'],$config,$method,$key,$secret);//返回按“商品名称”搜索的结果
			if(!empty($goods)){
				$count = count($goods)-1;
				$insert_data = '';
				$true_goods = '';

				//获取当前价格
				$true_goods = $goods[rand(0,$count)];
				$platform = '';
				if($true_goods['type']==2){
					//1688
					$platform = '1688';
				}
				elseif($true_goods['type']==1){
					//淘宝
					$platform = 'taobao';
				}
				//商品详情为空则查询万邦接口
				if(empty($true_goods['goods_desc'])){
					$res = $this->onebound_itemGet($platform,$key,$secret,$true_goods['num_iid'],$method);
					$insert_data = [
						'brand'=>$res['item']['brand'],//品牌
						'desc'=>$res['item']['desc'],//描述
						'item_imgs'=>$res['item']['item_imgs'],//商品图片
						'item_weight'=>$res['item']['item_weight'],//商品重量
						'post_fee'=>$res['item']['post_fee'],//邮费
						'ems_fee'=>$res['item']['ems_fee'],//EMS费用
						'express_fee'=>$res['item']['express_fee'],//EMS费用
						'sellUnit'=>$res['item']['sellUnit'],//出售单位
						'unit'=>$res['item']['unit'],//单位
						'desc_img'=>$res['item']['desc_img'],//详情图片列表
					];
					if(!empty($res['item']['skus']['sku'])){
						//查询同一商品价钱的规格（随机）
						foreach($res['item']['skus']['sku'] as $k3=>$v3){
							if($v3['price']==$true_goods['price']){
								array_push($insert_data,['sku'=>$v3]);
								break;
							}
						}
					}
					pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
				}

				$is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$v2['id']]);
				$onebound_total_goods=[];
				if(isset($true_goods['id'])) {
					$onebound_total_goods = pdo_fetch('select * from ' . tablename('onebound_total_goods') . ' where id=:id', [':id' => $true_goods['id']]);
					$onebound_total_goods['goods_desc'] = json_decode($onebound_total_goods['goods_desc'],true);
				}

				if($is_have['id']>0){
					$res = pdo_update('customs_goods_pre_fill_log',[
						'price'=>$onebound_total_goods['price'],
						'totalPrice'=>$v2['qty']*$onebound_total_goods['price'],//申报数量*单价
						'charge'=>$v2['qty']*$onebound_total_goods['price'],
						'onebound_gid'=>$true_goods['id'],
					],['good_id'=>$v2['id']]);
				}
				elseif(isset($true_goods['id'])){
					$res = pdo_insert('customs_goods_pre_fill_log',[
						'pre_batch_num'=>$v2['pre_batch_num'],
						'openid'=>$openid,
						'type'=>3,
						'itemNo'=>$v2['itemNo'],
						'itemName'=>$v2['itemName'],
						'gcode'=>$v2['gcode'],
						'currency'=>$v2['currency'],
						'qty'=>$v2['qty'],
						'qty1'=>$v2['qty1'],
						'gmodel'=>$v2['gmodel'],
						'price'=>$onebound_total_goods['price'],
						'totalPrice'=>$v2['qty']*$onebound_total_goods['price'],//申报数量*单价
						'charge'=>$v2['qty']*$onebound_total_goods['price'],
						'chargeDate'=>$v2['chargeDate'],
						'logisticsNo'=>$v2['logisticsNo'],
						'freight'=>$v2['freight'],
						'insuredFee'=>$v2['insuredFee'],
						'barCode'=>$v2['barCode'],
						'grossWeight'=>$v2['grossWeight'],
						'netWeight'=>$v2['netWeight'],
						'packNo'=>$v2['packNo'],
						'goodsInfo'=>$v2['goodsInfo'],
//						$this->getUnit($onebound_total_goods['goods_desc']['sellUnit'])
						'unit'=>!empty($v2['unit'])?$v2['unit']:'',
						'unit1'=>!empty($v2['unit1'])?$v2['unit1']:'',
						'good_id'=>$v2['id'],
						'onebound_gid'=>$true_goods['id'],
						'is_fill'=>1
					]);
				}
			}
		}
	}

	//同品异重-比对每个商品毛重相差有无超过1倍，并获取平台名，逐一调整,废弃
	public function eachWeight($num_count,$num,$bc_arr,$info,$openid){
	    if(count($bc_arr)<2){
	        return 0;
	    }
	    foreach($bc_arr as $k2=>$v2){
            if($k2!=0){
                $grw = abs(floatval($bc_arr[$num]['grossWeight'])-floatval($v2['grossWeight']));//两者倍差
                $min = min(floatval($bc_arr[$num]['grossWeight']),floatval($v2['grossWeight']));//两者取最小值
//				floatval($bc_arr[$num]['grossWeight'])
                if($grw>$min){
                    //取平台名，逐一调整
					$goods = $this->onebound_data_handle($info['gname'],$info['config'],$info['method'],$info['key'],$info['secret']);
					$count = count($goods);
					$insert_data = '';
					$true_goods = '';
					if($count>3){
						//获取最高价，中间价，最低价
						$price = array_column($goods,'price');
						array_multisort($price,SORT_DESC,$goods);
						$max_price = $goods[0]['price'];//最高价
						$min_price = $goods[$count-1]['price'];//最低价
						$avg_price = ($max_price+$min_price)/2;//中间价 32.6
						//找出与中间价相临近的价钱
						$min_value = 0;//记录最小值
						foreach($goods as $k3=>$v3){
							$value = abs($avg_price-$v3['price']);//27.3 11.4  25.58 27.3
							if(empty($min_value)){
								$min_value=$value;
								$true_goods = $v3;
							}elseif($min_value>=$value){
								$min_value=$value;
								$true_goods = $v3;
							}
						}
						$platform = '';
						if($true_goods['type']==2){
							//1688
							$platform = '1688';
						}elseif($true_goods['type']==1){
							//淘宝
							$platform = 'taobao';
						}

						//商品详情为空则查询万邦接口
						if(empty($true_goods['goods_desc'])){
							$res = $this->onebound_itemGet($platform,$info['key'],$info['secret'],$true_goods['num_iid'],$info['method']);

							$insert_data = [
								'brand'=>$res['item']['brand'],//品牌
								'desc'=>$res['item']['desc'],//描述
								'item_imgs'=>$res['item']['item_imgs'],//商品图片
								'item_weight'=>$res['item']['item_weight'],//商品重量
								'post_fee'=>$res['item']['post_fee'],//邮费
								'ems_fee'=>$res['item']['ems_fee'],//EMS费用
								'express_fee'=>$res['item']['express_fee'],//EMS费用
								'sellUnit'=>$res['item']['sellUnit'],//出售单位
								'unit'=>$res['item']['unit'],//单位
								'desc_img'=>$res['item']['desc_img'],//详情图片列表
							];
							if(!empty($res['item']['skus']['sku'])){
								//查询同一商品价钱的规格（随机）
								foreach($res['item']['skus']['sku'] as $k3=>$v3){
									if($v3['price']==$true_goods['price']){
										array_push($insert_data,['sku'=>$v3]);
										break;
									}
								}
							}

							pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
						}
					}
					elseif($count<3 && $count>1){
						//获取最高价
						$price = array_column($goods,'price');
						array_multisort($price,SORT_DESC,$goods);
						$max_price = $goods[0]['price'];//最高价

						foreach($goods as $k3=>$v3){
							if($max_price==$v3['price']){
								$true_goods = $v3;
							}
						}
						$platform = '';
						if($true_goods['type']==2){
							//1688
							$platform = '1688';
						}elseif($true_goods['type']==1){
							//淘宝
							$platform = 'taobao';
						}

						//商品详情为空则查询万邦接口
						if(empty($true_goods['goods_desc'])){
							$res = $this->onebound_itemGet($platform,$info['key'],$info['secret'],$true_goods['num_iid'],$info['method']);
							$insert_data = [
								'brand'=>$res['item']['brand'],//品牌
								'desc'=>$res['item']['desc'],//描述
								'item_imgs'=>$res['item']['item_imgs'],//商品图片
								'item_weight'=>$res['item']['item_weight'],//商品重量
								'post_fee'=>$res['item']['post_fee'],//邮费
								'ems_fee'=>$res['item']['ems_fee'],//EMS费用
								'express_fee'=>$res['item']['express_fee'],//EMS费用
								'sellUnit'=>$res['item']['sellUnit'],//出售单位
								'unit'=>$res['item']['unit'],//单位
								'desc_img'=>$res['item']['desc_img'],//详情图片列表
							];
							if(!empty($res['item']['skus']['sku'])){
								//查询同一商品价钱的规格（随机）
								foreach($res['item']['skus']['sku'] as $k3=>$v3){
									if($v3['price']==$true_goods['price']){
										array_push($insert_data,['sku'=>$v3]);
										break;
									}
								}
							}
							pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
						}
					}
					elseif($count==1){
						//获取当前价格
						$true_goods = $goods[0];
						$platform = '';
						if($true_goods['type']==2){
							//1688
							$platform = '1688';
						}elseif($true_goods['type']==1){
							//淘宝
							$platform = 'taobao';
						}

						//商品详情为空则查询万邦接口
						if(empty($true_goods['goods_desc'])){
							$res = $this->onebound_itemGet($platform,$info['key'],$info['secret'],$true_goods['num_iid'],$info['method']);
							$insert_data = [
								'brand'=>$res['item']['brand'],//品牌
								'desc'=>$res['item']['desc'],//描述
								'item_imgs'=>$res['item']['item_imgs'],//商品图片
								'item_weight'=>$res['item']['item_weight'],//商品重量
								'post_fee'=>$res['item']['post_fee'],//邮费
								'ems_fee'=>$res['item']['ems_fee'],//EMS费用
								'express_fee'=>$res['item']['express_fee'],//EMS费用
								'sellUnit'=>$res['item']['sellUnit'],//出售单位
								'unit'=>$res['item']['unit'],//单位
								'desc_img'=>$res['item']['desc_img'],//详情图片列表
							];
							if(!empty($res['item']['skus']['sku'])){
								//查询同一商品价钱的规格（随机）
								foreach($res['item']['skus']['sku'] as $k3=>$v3){
									if($v3['price']==$true_goods['price']){
										array_push($insert_data,['sku'=>$v3]);
										break;
									}
								}
							}
							pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
						}
					}

					$update_arr = [$bc_arr[$num],$v2];

					foreach($update_arr as $k3=>$v3){
						$is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$v3['id']]);
						if(isset($true_goods['id'])) {
							$onebound_total_goods = pdo_fetch('select * from ' . tablename('onebound_total_goods') . ' where id=:id', [':id' => $true_goods['id']]);
							$onebound_total_goods['goods_desc'] = json_decode($onebound_total_goods['goods_desc'],true);
						}

						if($is_have['id']>0){
							pdo_update('customs_goods_pre_fill_log',[
								'currency'=>$v3['currency'],
								'logisticsNo'=>$v3['logisticsNo'],
								'price'=>$v3['price']>0?$v3['price']:$onebound_total_goods['price'],
								'totalPrice'=>$v3['totalPrice']>0?$v3['totalPrice']:($v3['qty']*$onebound_total_goods['price']),//申报数量*单价
								'charge'=>$v3['charge']>0?$v3['charge']:($v3['qty']*$onebound_total_goods['price']),
								'chargeDate'=>$v3['chargeDate'],
								'freight'=>$v3['freight'],
								'insuredFee'=>$v3['insuredFee'],
								'barCode'=>$v3['barCode'],
								'unit'=>!empty($v3['unit'])?$v3['unit']:$this->getUnit($onebound_total_goods['goods_desc']['sellUnit']),
								'unit1'=>!empty($v3['unit1'])?$v3['unit1']:$this->getUnit($onebound_total_goods['goods_desc']['sellUnit']),
								'onebound_gid'=>$true_goods['id'],
							],['good_id'=>$v3['id']]);
						}
						elseif(isset($true_goods['id'])){
							pdo_insert('customs_goods_pre_fill_log',[
								'openid'=>$openid,
								'logisticsNo'=>$v3['logisticsNo'],
								'currency'=>$v3['currency'],
								'price'=>$v3['price']>0?$v3['price']:$onebound_total_goods['price'],
								'totalPrice'=>$v3['totalPrice']>0?$v3['totalPrice']:($v3['qty']*$onebound_total_goods['price']),//申报数量*单价
								'charge'=>$v3['charge']>0?$v3['charge']:($v3['qty']*$onebound_total_goods['price']),
								'chargeDate'=>$v3['chargeDate'],
								'freight'=>$v3['freight'],
								'insuredFee'=>$v3['insuredFee'],
								'barCode'=>$v3['barCode'],
								'unit'=>!empty($v3['unit'])?$v3['unit']:$this->getUnit($onebound_total_goods['goods_desc']['sellUnit']),
								'unit1'=>!empty($v3['unit1'])?$v3['unit1']:$this->getUnit($onebound_total_goods['goods_desc']['sellUnit']),
								'good_id'=>$v3['id'],
								'onebound_gid'=>$true_goods['id'],
							]);
						}
					}
                }else{
					//同品异价
					$goods = $this->onebound_data_handle($info['gname'],$info['config'],$info['method'],$info['key'],$info['secret']);
					$count = count($goods);
					$insert_data = '';
					$true_goods = '';
					if($count>3){
						//获取最高价，中间价，最低价
						$price = array_column($goods,'price');
						array_multisort($price,SORT_DESC,$goods);
						$max_price = $goods[0]['price'];//最高价
						$min_price = $goods[$count-1]['price'];//最低价
						$avg_price = ($max_price+$min_price)/2;//中间价 32.6
						//找出与中间价相临近的价钱
						$min_value = 0;//记录最小值
						foreach($goods as $k3=>$v3){
							$value = abs($avg_price-$v3['price']);//27.3 11.4  25.58 27.3
							if(empty($min_value)){
								$min_value=$value;
								$true_goods = $v3;
							}elseif($min_value>=$value){
								$min_value=$value;
								$true_goods = $v3;
							}
						}
						$platform = '';
						if($true_goods['type']==2){
							//1688
							$platform = '1688';
						}elseif($true_goods['type']==1){
							//淘宝
							$platform = 'taobao';
						}

						//商品详情为空则查询万邦接口
						if(empty($true_goods['goods_desc'])){
							$res = $this->onebound_itemGet($platform,$info['key'],$info['secret'],$true_goods['num_iid'],$info['method']);

							$insert_data = [
								'brand'=>$res['item']['brand'],//品牌
								'desc'=>$res['item']['desc'],//描述
								'item_imgs'=>$res['item']['item_imgs'],//商品图片
								'item_weight'=>$res['item']['item_weight'],//商品重量
								'post_fee'=>$res['item']['post_fee'],//邮费
								'ems_fee'=>$res['item']['ems_fee'],//EMS费用
								'express_fee'=>$res['item']['express_fee'],//EMS费用
								'sellUnit'=>$res['item']['sellUnit'],//出售单位
								'unit'=>$res['item']['unit'],//单位
								'desc_img'=>$res['item']['desc_img'],//详情图片列表
							];
							if(!empty($res['item']['skus']['sku'])){
								//查询同一商品价钱的规格（随机）
								foreach($res['item']['skus']['sku'] as $k3=>$v3){
									if($v3['price']==$true_goods['price']){
										array_push($insert_data,['sku'=>$v3]);
										break;
									}
								}
							}

							pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
						}
					}
					elseif($count<3 && $count>1){
						//获取最高价
						$price = array_column($goods,'price');
						array_multisort($price,SORT_DESC,$goods);
						$max_price = $goods[0]['price'];//最高价

						foreach($goods as $k3=>$v3){
							if($max_price==$v3['price']){
								$true_goods = $v3;
							}
						}
						$platform = '';
						if($true_goods['type']==2){
							//1688
							$platform = '1688';
						}elseif($true_goods['type']==1){
							//淘宝
							$platform = 'taobao';
						}

						//商品详情为空则查询万邦接口
						if(empty($true_goods['goods_desc'])){
							$res = $this->onebound_itemGet($platform,$info['key'],$info['secret'],$true_goods['num_iid'],$info['method']);
							$insert_data = [
								'brand'=>$res['item']['brand'],//品牌
								'desc'=>$res['item']['desc'],//描述
								'item_imgs'=>$res['item']['item_imgs'],//商品图片
								'item_weight'=>$res['item']['item_weight'],//商品重量
								'post_fee'=>$res['item']['post_fee'],//邮费
								'ems_fee'=>$res['item']['ems_fee'],//EMS费用
								'express_fee'=>$res['item']['express_fee'],//EMS费用
								'sellUnit'=>$res['item']['sellUnit'],//出售单位
								'unit'=>$res['item']['unit'],//单位
								'desc_img'=>$res['item']['desc_img'],//详情图片列表
							];
							if(!empty($res['item']['skus']['sku'])){
								//查询同一商品价钱的规格（随机）
								foreach($res['item']['skus']['sku'] as $k3=>$v3){
									if($v3['price']==$true_goods['price']){
										array_push($insert_data,['sku'=>$v3]);
										break;
									}
								}
							}
							pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
						}
					}
					elseif($count==1){
						//获取当前价格
						$true_goods = $goods[0];
						$platform = '';
						if($true_goods['type']==2){
							//1688
							$platform = '1688';
						}elseif($true_goods['type']==1){
							//淘宝
							$platform = 'taobao';
						}

						//商品详情为空则查询万邦接口
						if(empty($true_goods['goods_desc'])){
							$res = $this->onebound_itemGet($platform,$info['key'],$info['secret'],$true_goods['num_iid'],$info['method']);
							$insert_data = [
								'brand'=>$res['item']['brand'],//品牌
								'desc'=>$res['item']['desc'],//描述
								'item_imgs'=>$res['item']['item_imgs'],//商品图片
								'item_weight'=>$res['item']['item_weight'],//商品重量
								'post_fee'=>$res['item']['post_fee'],//邮费
								'ems_fee'=>$res['item']['ems_fee'],//EMS费用
								'express_fee'=>$res['item']['express_fee'],//EMS费用
								'sellUnit'=>$res['item']['sellUnit'],//出售单位
								'unit'=>$res['item']['unit'],//单位
								'desc_img'=>$res['item']['desc_img'],//详情图片列表
							];
							if(!empty($res['item']['skus']['sku'])){
								//查询同一商品价钱的规格（随机）
								foreach($res['item']['skus']['sku'] as $k3=>$v3){
									if($v3['price']==$true_goods['price']){
										array_push($insert_data,['sku'=>$v3]);
										break;
									}
								}
							}
							pdo_update('onebound_total_goods',['goods_desc'=>json_encode($insert_data,true)],['id'=>$true_goods['id']]);
						}
					}

					$update_arr = [$bc_arr[$num],$v2];
					foreach($update_arr as $k3=>$v3){
						$is_have = pdo_fetch('select id from '.tablename('customs_goods_pre_fill_log').' where good_id=:gid',[':gid'=>$v3['id']]);
						if(isset($true_goods['id'])) {
							$onebound_total_goods = pdo_fetch('select * from ' . tablename('onebound_total_goods') . ' where id=:id', [':id' => $true_goods['id']]);
							$onebound_total_goods['goods_desc'] = json_decode($onebound_total_goods['goods_desc'],true);
						}

						if($is_have['id']>0){
							pdo_update('customs_goods_pre_fill_log',[
								'currency'=>$v3['currency'],
								'logisticsNo'=>$v3['logisticsNo'],
								'price'=>$v3['price']>0?$v3['price']:$onebound_total_goods['price'],
								'totalPrice'=>$v3['totalPrice']>0?$v3['totalPrice']:($v3['qty']*$onebound_total_goods['price']),//申报数量*单价
								'charge'=>$v3['charge']>0?$v3['charge']:($v3['qty']*$onebound_total_goods['price']),
								'chargeDate'=>$v3['chargeDate'],
								'freight'=>$v3['freight'],
								'insuredFee'=>$v3['insuredFee'],
								'barCode'=>$v3['barCode'],
								'unit'=>!empty($v3['unit'])?$v3['unit']:$this->getUnit($onebound_total_goods['goods_desc']['sellUnit']),
								'unit1'=>!empty($v3['unit1'])?$v3['unit1']:$this->getUnit($onebound_total_goods['goods_desc']['sellUnit']),
								'onebound_gid'=>$true_goods['id'],
							],['good_id'=>$v3['id']]);
						}
						elseif(isset($true_goods['id'])){
							pdo_insert('customs_goods_pre_fill_log',[
								'openid'=>$openid,
								'logisticsNo'=>$v3['logisticsNo'],
								'currency'=>$v3['currency'],
								'price'=>$v3['price']>0?$v3['price']:$onebound_total_goods['price'],
								'totalPrice'=>$v3['totalPrice']>0?$v3['totalPrice']:($v3['qty']*$onebound_total_goods['price']),//申报数量*单价
								'charge'=>$v3['charge']>0?$v3['charge']:($v3['qty']*$onebound_total_goods['price']),
								'chargeDate'=>$v3['chargeDate'],
								'freight'=>$v3['freight'],
								'insuredFee'=>$v3['insuredFee'],
								'barCode'=>$v3['barCode'],
								'unit'=>!empty($v3['unit'])?$v3['unit']:$this->getUnit($onebound_total_goods['goods_desc']['sellUnit']),
								'unit1'=>!empty($v3['unit1'])?$v3['unit1']:$this->getUnit($onebound_total_goods['goods_desc']['sellUnit']),
								'good_id'=>$v3['id'],
								'onebound_gid'=>$true_goods['id'],
							]);
						}
					}
				}
            }
            
            if($k2==$num_count){
                // $num+=1;
                unset($bc_arr[0]);
                $bc_arr = array_values($bc_arr);
                $num_count = count($bc_arr)-1;
                $this->eachWeight($num_count,$num,$bc_arr,$info,$openid);
            }
        }
	}

	/**
	 * 总值调整（按比率）
	 * @param $openid
	 * @param $total_price 清单总值
	 * @param $config 后台配置信息
	 * @param $method 1-调升，2-调降
	 * @param $userTotalMoney 用户定义调整总值
	 * @param $orderList 清单商品id
	 * @param $amp1 比率
	 * @param int $typ 0-外部调用，1-循环时调用
	 * @param array $system_goods_price 循环时携带的系统调值限值
	 * @param int $mode 模式：1-完成该轮商品再判断客户端总值，2-完成该商品即刻判断有无超过客户端总值
	 * @return array
	 */
	public function grossAdjustRatio($openid,$total_price,$config,$method,$userTotalMoney,$orderList,$amp1,$typ=0,$system_goods_price=[],$mode=1){
		//按系统商品单价每个百分比调整商品单价
		$user_total_price = 0;//商户按1.88%调整后总价
		$user_goods_price = [];//商户按1.88%调整后每个商品的价格
		if($typ==0){
			//外部调用
			$times = 1;
			$system_goods_price = [];//系统按2%调整后每个商品的价格
			foreach($orderList as $k=>$v){
				$fill_good_price = pdo_fetch('select a.id,b.itemName,a.price,b.qty from '.tablename('customs_goods_pre_fill_log').' a left join '.tablename('customs_goods_pre_log').' b on b.id=a.good_id where a.good_id=:gid',[':gid'=>$v['id']]);
				//1、用户商品相乘
				$ratio=0;
				if($method==2) {
					$ratio = (1 - ($amp1 / 100));
				}elseif($method==1){
					$ratio = (1+($amp1/100));
				}
				$user_goodsPrice = $fill_good_price['price']*$ratio;//1*1.0188=1.0188
				array_push($user_goods_price,['id'=>$fill_good_price['id'],'itemName'=>$fill_good_price['itemName'],'price'=>$user_goodsPrice,'qty'=>$fill_good_price['qty'],'pre_batch_num'=>$v['pre_batch_num'],'times'=>$times]);

				//2、系统相乘
				$ratio = 0;
				if($method==2){
					$ratio = (1-($config['goodsValue_up']/100));
				}elseif($method==1){
					$ratio = (1+($config['goodsValue_up']/100));
				}
				$system_goodsPrice = $fill_good_price['price']*$ratio;//1*1.02=1.02
				array_push($system_goods_price,['id'=>$fill_good_price['id'],'itemName'=>$fill_good_price['itemName'],'price'=>$system_goodsPrice]);

				//商品每次计算完后，比率对比自定义和系统的，然后记录临时表
				if($user_goodsPrice>=$system_goodsPrice){
					$notice = 0;
					$condition = 0;
					if($method==1) {
						$notice=1;
						$condition=2;
					}else{
						$notice=0;
						$condition=1;
					}
					array_merge($user_goods_price[$k], ['notice' => $notice]);
					//用户值>系统值
					pdo_insert('declare_value_adjust_tmp', [
						'openid' => $openid,
						'pre_batch_num' => $v['pre_batch_num'],
						'typ'=>$method,
						'good_id' => $fill_good_price['id'],
						'ori_price' => $fill_good_price['price'],//清单商品补缺后价格,调前单价
						'calc_price' => $user_goodsPrice,//用户计算后价格,调后单价
						'sys_price' => $system_goodsPrice,//系统配置价格*0.02
						'times' => $times,
						'condition' => $condition
					]);
				}else{
					$notice = 0;
					$condition = 0;
					if($method==1) {
						$notice = 0;
						$condition = 1;
					}else{
						//调降时若小于系统值就标记不能调整了
						$notice = 1;
						$condition = 2;
					}
					array_merge($user_goods_price[$k], ['notice' => $notice]);
					//用户值<系统值
					pdo_insert('declare_value_adjust_tmp', [
						'openid' => $openid,
						'pre_batch_num' => $v['pre_batch_num'],
						'typ'=>$method,
						'good_id' => $fill_good_price['id'],
						'ori_price' => $fill_good_price['price'],//清单商品补缺后价格,调前单价
						'calc_price' => $user_goodsPrice,//用户计算后价格,调后单价
						'sys_price' => $system_goodsPrice,//系统配置价格*0.02
						'times' => $times,
						'condition' => $condition
					]);
				}
				$user_total_price += ($fill_good_price['qty'] * $user_goodsPrice);//客户调整总值幅度第一轮
				if($mode==2 && $method==1){
					if($user_total_price<$userTotalMoney){
						continue;
					}else{
						return [1,'您好！系统已经依据你设定的总值及幅度完成货物申报价值的调整，请确认及导出调值后的申报清单。'];
					}
				}
			}
		}
		else{
			//循环时调用
			foreach($orderList as $k=>$v){
				//先查询临时表该商品有无标记超过停止
				$condition = pdo_fetchcolumn('select `condition` from '.tablename('declare_value_adjust_tmp').' where good_id=:gid and pre_batch_num=:batch_num order by id desc',[':gid'=>$v['id'],':batch_num'=>$v['pre_batch_num']]);
				if($condition==1){
					//还小于系统限值
					$times = $v['times']+1;
					//1、用户商品相乘
					$ratio=0;
					if($method==2) {
						$ratio = (1 - ($amp1 / 100));
					}elseif($method==1){
						$ratio = (1+($amp1/100));
					}
					$user_goodsPrice = $v['price']*$ratio;//1*1.0188=1.0188,1.0188*1.0188=1.037953
					array_push($user_goods_price,['id'=>$v['id'],'itemName'=>$v['itemName'],'price'=>$user_goodsPrice,'qty'=>$v['qty'],'pre_batch_num'=>$v['pre_batch_num'],'times'=>$times]);
					//商品每次计算完后，比率对比自定义和系统的，然后记录临时表
					if($user_goodsPrice>=$system_goods_price[$k]['price']){
						//用户值>系统值
				// 		pdo_delete('declare_value_adjust_tmp',['openid'=>$openid,'pre_batch_num'=>$v['pre_batch_num'],'good_id'=>$v['id'],'times'=>$times-1]);
						$notice = 0;
						$condition = 0;
						if($method==1){
							$notice = 1;
							$condition = 2;
						}else{
							$notice = 0;
							$condition = 1;
						}
						array_merge($user_goods_price[$k],['notice'=>$notice]);
						pdo_insert('declare_value_adjust_tmp',[
							'openid'=>$openid,
							'pre_batch_num'=>$v['pre_batch_num'],
							'typ'=>$method,
							'good_id'=>$v['id'],
							'ori_price'=>$v['price'],//上一轮价格,调前单价
							'calc_price'=>$user_goodsPrice,//用户计算后价格,调后单价
							'sys_price'=>$system_goods_price[$k]['price'],//系统配置价格*0.02
							'times'=>$times,
							'condition'=>$condition
						]);
					}else{
						//用户值<系统值
						$notice = 0;
						$condition = 0;
						if($method==1) {
							$notice = 0;
							$condition = 1;
						}else{
							//调降时若小于系统值就标记不能调整了
							$notice = 1;
							$condition = 2;
						}
						array_merge($user_goods_price[$k], ['notice' => $notice]);
						pdo_insert('declare_value_adjust_tmp', [
							'openid' => $openid,
							'pre_batch_num' => $v['pre_batch_num'],
							'typ'=>$method,
							'good_id' => $v['id'],
							'ori_price' => $v['price'],//上一轮价格,调前单价
							'calc_price' => $user_goodsPrice,//用户计算后价格,调后单价
							'sys_price' => $system_goods_price[$k]['price'],//系统配置价格*0.02
							'times' => $times,
							'condition' => $condition
						]);
					}
				}
				else{
					//大于或小于系统总值，停止标记，并提示客户
					$user_goodsPrice = $v['price'];//上一轮商品价格
					array_push($user_goods_price,['id'=>$v['id'],'itemName'=>$v['itemName'],'price'=>$user_goodsPrice,'qty'=>$v['qty'],'pre_batch_num'=>$v['pre_batch_num'],'times'=>$v['times'],'notice'=>1]);
				}
				$user_total_price += ($v['qty'] * $user_goodsPrice);//客户调整总值幅度第n轮
				if($mode==2 && $method==1){
				    if($user_total_price<$userTotalMoney){
				        continue;
				    }else{
				        return [1,'您好！系统已经依据你设定的总值及幅度完成货物申报价值的调整，请确认及导出调值后的申报清单。'];
				    }
				}
			}
		}

		//判断计算后的所有商品总值是否超过客户配置总值
		if($method==1){
			if($user_total_price<$userTotalMoney){
				//总和小于客户端设定的调整总值
				if($this->in_array_r2(0,$user_goods_price,'notice')){
					//继续循环
					$res = $this->grossAdjustRatio($openid,$total_price,$config,$method,$userTotalMoney,$user_goods_price,$amp1,1,$system_goods_price);
					return $res;
				}else{
					//通知客户调整失败
					return [-1,'您好！基于您设定有调整总值与幅度，系统无法实现有关货物申报价值的调整，请重新设定总值或调值幅度。'];
				}

			}elseif($user_total_price>=$userTotalMoney){
				//总和大于或等于客户端设定的调整总值
				return [1,'您好！系统已经依据你设定的总值及幅度完成货物申报价值的调整，请确认及导出调值后的申报清单。'];
			}
		}
		else{
			if($user_total_price>$userTotalMoney){
				//总和大于客户端设定的调整总值
				if($this->in_array_r2(0,$user_goods_price,'notice')){
					//继续循环
					$res = $this->grossAdjustRatio($openid,$total_price,$config,$method,$userTotalMoney,$user_goods_price,$amp1,1,$system_goods_price);
					return $res;
				}else{
					//通知客户调整失败
					return [-1,'您好！基于您设定有调整总值与幅度，系统无法实现有关货物申报价值的调整，请重新设定总值或调值幅度。'];
				}

			}elseif($user_total_price<=$userTotalMoney){
				//总和小于或等于客户端设定的调整总值
				if($this->in_array_r2(0,$user_goods_price,'notice')){
					//继续循环
					$res = $this->grossAdjustRatio($openid,$total_price,$config,$method,$userTotalMoney,$user_goods_price,$amp1,1,$system_goods_price);
					return $res;
				}else {
					return [1, '您好！系统已经依据你设定的总值及幅度完成货物申报价值的调整，请确认及导出调值后的申报清单。'];
				}
			}
		}
	}

	/**
	 * 总值调整（按金额）
	 * @param $openid
	 * @param $total_price 清单总值
	 * @param $config 后台配置信息
	 * @param $method 1-调升，2-调降
	 * @param $userTotalMoney 用户定义调整总值
	 * @param $orderList 清单商品id
	 * @param $amp1 比率
	 * @param int $typ 0-外部调用，1-循环时调用
	 * @param array $system_goods_price 循环时携带的系统调值限值
	 * @param int $mode 模式：1-完成该轮商品再判断客户端总值，2-完成该商品即刻判断有无超过客户端总值
	 * @return array
	 */
	public function grossAdjustMoney($openid,$total_price,$config,$method,$userTotalMoney,$orderList,$amp1,$typ=0,$system_goods_price=[],$mode=1){
		//按系统商品单价每个±2元调整商品单价
		$user_total_price = 0;//商户按±2元调整后总价
		$user_goods_price = [];//商户按±2元调整后每个商品的价格
		if($typ==0){
			//外部调用
			$times = 1;
			$system_goods_price = [];//系统按2%调整后每个商品的价格
			foreach($orderList as $k=>$v){
				$fill_good_price = pdo_fetch('select a.id,b.itemName,a.price,b.qty from '.tablename('customs_goods_pre_fill_log').' a left join '.tablename('customs_goods_pre_log').' b on b.id=a.good_id where a.good_id=:gid',[':gid'=>$v['id']]);

				//1、用户商品相加减
				$calcMoney=0;
				if($method==2) {
					$calcMoney = $fill_good_price['price']-$amp1;
				}elseif($method==1){
					$calcMoney = $fill_good_price['price']+$amp1;
				}
				$user_goodsPrice = $calcMoney;//1+2=3
				array_push($user_goods_price,['id'=>$fill_good_price['id'],'itemName'=>$fill_good_price['itemName'],'price'=>$user_goodsPrice,'qty'=>$fill_good_price['qty'],'pre_batch_num'=>$v['pre_batch_num'],'times'=>$times]);

				//2、系统相乘
				$ratio = 0;
				if($method==2){
					$ratio = (1-($config['goodsValue_up']/100));
				}elseif($method==1){
					$ratio = (1+($config['goodsValue_up']/100));
				}
				$system_goodsPrice = $fill_good_price['price']*$ratio;//1*1.02=1.02
				array_push($system_goods_price,['id'=>$fill_good_price['id'],'itemName'=>$fill_good_price['itemName'],'price'=>$system_goodsPrice]);

				//商品每次计算完后，比率对比自定义和系统的，然后记录临时表
				if($user_goodsPrice>=$system_goodsPrice){
					$notice = 0;
					$condition = 0;
					if($method==1) {
						$notice = 1;
						$condition = 2;
					}else{
						$notice = 0;
						$condition = 1;
					}
					array_merge($user_goods_price[$k], ['notice' => $notice]);
					//用户值>系统值
					pdo_insert('declare_value_adjust_tmp', [
						'openid' => $openid,
						'pre_batch_num' => $v['pre_batch_num'],
						'typ'=>$method,
						'good_id' => $fill_good_price['id'],
						'ori_price' => $fill_good_price['price'],//清单商品补缺后价格,调前单价
						'calc_price' => $user_goodsPrice,//用户计算后价格,调后单价
						'sys_price' => $system_goodsPrice,//系统配置价格*0.02
						'times' => $times,
						'condition' => $condition
					]);
				}else{
					$notice = 0;
					$condition = 0;
					if($method==1) {
						$notice = 0;
						$condition = 1;
					}else{
						$notice = 1;
						$condition = 2;
					}
					array_merge($user_goods_price[$k], ['notice' => $notice]);
					//用户值<系统值
					pdo_insert('declare_value_adjust_tmp', [
						'openid' => $openid,
						'pre_batch_num' => $v['pre_batch_num'],
						'typ'=>$method,
						'good_id' => $fill_good_price['id'],
						'ori_price' => $fill_good_price['price'],//清单商品补缺后价格,调前单价
						'calc_price' => $user_goodsPrice,//用户计算后价格,调后单价
						'sys_price' => $system_goodsPrice,//系统配置价格*0.02
						'times' => $times,
						'condition' => $condition
					]);
				}
				$user_total_price += ($fill_good_price['qty'] * $user_goodsPrice);//客户调整总值幅度第一轮
				if($mode==2 &&$method==1){
				    if($user_total_price<$userTotalMoney){
				        continue;
				    }else{
				        return [1,'您好！系统已经依据你设定的总值及幅度完成货物申报价值的调整，请确认及导出调值后的申报清单。'];
				    }
				}
			}
		}
		else{
			//循环时调用
			foreach($orderList as $k=>$v){
				//先查询临时表该商品有无标记超过停止
				$condition = pdo_fetchcolumn('select `condition` from '.tablename('declare_value_adjust_tmp').' where good_id=:gid and pre_batch_num=:batch_num order by id desc',[':gid'=>$v['id'],':batch_num'=>$v['pre_batch_num']]);
				if($condition==1){
					//还小于系统限值
					$times = $v['times']+1;
					//1、用户商品相加减
					$user_goodsPrice=0;
					if($method==2) {
						$user_goodsPrice = $v['price']-$amp1;
					}elseif($method==1){
						$user_goodsPrice = $v['price']+$amp1;
					}
					array_push($user_goods_price,['id'=>$v['id'],'itemName'=>$v['itemName'],'price'=>$user_goodsPrice,'qty'=>$v['qty'],'pre_batch_num'=>$v['pre_batch_num'],'times'=>$times]);
//					abs($user_goodsPrice - $system_goods_price[$k]['price']) < 0.000001
					if(floatval($user_goodsPrice)>=floatval($system_goods_price[$k]['price'])){
						//用户值>系统值
						$notice = 0;
						$condition = 0;
						if($method==1) {
							$notice = 1;
							$condition = 2;
						}else{
							$notice = 0;
							$condition = 1;
						}
						array_merge($user_goods_price[$k], ['notice' => $notice]);
						pdo_insert('declare_value_adjust_tmp', [
							'openid' => $openid,
							'pre_batch_num' => $v['pre_batch_num'],
							'typ'=>$method,
							'good_id' => $v['id'],
							'ori_price' => $v['price'],//上一轮价格,调前单价
							'calc_price' => $user_goodsPrice,//用户计算后价格,调后单价
							'sys_price' => $system_goods_price[$k]['price'],//系统配置价格*0.02
							'times' => $times,
							'condition' => $condition
						]);
					}else{
						if($method==1) {
							$notice = 0;
							$condition = 1;
						}else{
							$notice = 1;
							$condition = 2;
						}
						array_merge($user_goods_price[$k], ['notice' => $notice]);
						//用户值<系统值
						pdo_insert('declare_value_adjust_tmp', [
							'openid' => $openid,
							'pre_batch_num' => $v['pre_batch_num'],
							'typ'=>$method,
							'good_id' => $v['id'],
							'ori_price' => $v['price'],//上一轮价格,调前单价
							'calc_price' => $user_goodsPrice,//用户计算后价格,调后单价
							'sys_price' => $system_goods_price[$k]['price'],//系统配置价格*0.02
							'times' => $times,
							'condition' => $condition
						]);
					}
				}else{
					//大于系统总值，停止标记，并提示客户
					$user_goodsPrice = $v['price'];//上一轮商品价格
					array_push($user_goods_price,['id'=>$v['id'],'itemName'=>$v['itemName'],'price'=>$user_goodsPrice,'qty'=>$v['qty'],'pre_batch_num'=>$v['pre_batch_num'],'times'=>$v['times'],'notice'=>1]);
				}
				$user_total_price += ($v['qty'] * $user_goodsPrice);//客户调整总值幅度第n轮
				if($mode==2 && $method==1){
				    if($user_total_price<$userTotalMoney){
				        continue;
				    }else{
				        return [1,'您好！系统已经依据你设定的总值及幅度完成货物申报价值的调整，请确认及导出调值后的申报清单。'];
				    }
				}
			}
		}

		//判断计算后的所有商品总值是否超过客户配置总值
		if($method==1){
			if($user_total_price<$userTotalMoney){
				//总和小于客户端设定的调整总值
				if($this->in_array_r2(0,$user_goods_price,'notice')){
					//继续循环
					$res = $this->grossAdjustMoney($openid,$total_price,$config,$method,$userTotalMoney,$user_goods_price,$amp1,1,$system_goods_price);
					return $res;
				}else{
					//通知客户调整失败
					return [-1,'您好！基于您设定有调整总值与幅度，系统无法实现有关货物申报价值的调整，请重新设定总值或调值幅度。'];
				}

			}elseif($user_total_price>=$userTotalMoney){
				//总和大于或等于客户端设定的调整总值
				return [1,'您好！系统已经依据你设定的总值及幅度完成货物申报价值的调整，请确认及导出调值后的申报清单。'];
			}
		}
		else{
			if($user_total_price>$userTotalMoney){
				//总和小于客户端设定的调整总值
				if($this->in_array_r2(0,$user_goods_price,'notice')){
					//继续循环
					$res = $this->grossAdjustMoney($openid,$total_price,$config,$method,$userTotalMoney,$user_goods_price,$amp1,1,$system_goods_price);
					return $res;
				}else{
					//通知客户调整失败
					return [-1,'您好！基于您设定有调整总值与幅度，系统无法实现有关货物申报价值的调整，请重新设定总值或调值幅度。'];
				}

			}elseif($user_total_price<=$userTotalMoney){
				//总和小于或等于客户端设定的调整总值
				if($this->in_array_r2(0,$user_goods_price,'notice')){
					//继续循环
					$res = $this->grossAdjustMoney($openid,$total_price,$config,$method,$userTotalMoney,$user_goods_price,$amp1,1,$system_goods_price);
					return $res;
				}else {
					return [1, '您好！系统已经依据你设定的总值及幅度完成货物申报价值的调整，请确认及导出调值后的申报清单。'];
				}
			}
		}
	}

	/**
	 * 万邦请求
	 * @param $url
	 * @param $post_data
	 * @return bool|string
	 */
	public function onebound_post($url,$post_data,$method){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($curl, CURLOPT_FAILONERROR, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_ENCODING, "gzip");
		$res = curl_exec($curl);
		curl_close($curl);
		return json_decode($res,true);
	}

	/**
	 * 万邦-按关键字搜索淘宝/1688的商品
	 */
	public function onebound_itemSearch($platform,$key,$secret,$gname,$page=1,$method){
		$url = "https://api-gw.onebound.cn/".$platform."/item_search/?key=".$key."&secret=".$secret."&q=".$gname."&start_price=0&end_price=0&page=".$page."&cat=0&discount_only=&sort=&page_size=&seller_info=&nick=&ppath=&imgid=&filter=";
		$res = $this->onebound_post($url,'',$method);
		return $res;
	}

	/**
	 * 万邦-按商品id搜索淘宝/1688的商品详情
	 */
	public function onebound_itemGet($platform,$key,$secret,$num_iid,$method){
		$url = "https://api-gw.onebound.cn/".$platform."/item_get/?key=".$key."&secret=".$secret."&num_iid=".$num_iid;
		$res = $this->onebound_post($url,'',$method);
		return $res;
	}

	/**
	 * 万邦接口-商品数据筛查
	 * @param $list,$config,$pagecount
	 */
	public function onebound_goods_sort($list,$config,$pagecount,$type,$gname){
		$goods = [];
		if(!empty($list)) {
			foreach ($list as $k2 => &$v2) {
				if ($v2['sales'] >= $config['sold']) {
					//找到的数据插入数据表
					$is_have = pdo_fetchcolumn('select id from ' . tablename('onebound_total_goods') . ' where title=:title', [':title' => $v2['title']]);
					if (empty($is_have)) {
						pdo_insert('onebound_total_goods', [
							'type' => $type,
							'title' => $v2['title'],
							'pic_url' => $v2['pic_url'],
							'promotion_price' => $v2['promotion_price'],
							'price' => $v2['price'],
							'sales' => $v2['sales'],
							'num_iid' => $v2['num_iid'],
							'seller_nick' => $v2['seller_nick'],
							'seller_id' => $type == 1 ? $v2['seller_id'] : '',//taobao
							'tag_percent' => $type == 2 ? $v2['tag_percent'] : '',//1688
							'area' => $type == 2 ? $v2['area'] : '',//1688
							'detail_url' => $v2['detail_url'],
							'keywords' => $gname
						]);
						$v2['id'] = pdo_insertid();
						$v2['type'] = $type;
						$v2['goods_desc'] = '';
					}
					array_push($goods, $v2);//符合条件的数据
				}
			}
		}
		return $goods;
	}

	/**
	 * 数据表和1688都没有数据的话则查询淘宝
	 */
	public function onebound_taobao_search($key,$secret,$gname,$page=1,$method){
		$res = $this->onebound_itemSearch('taobao',$key,$secret,$gname,$page,$method);
		return $res;
	}

	/**
	 * 万邦接口-处理数据
	 * @param $gname,$config,$method,$key,$secret
	 */
	public function onebound_data_handle($gname,$config,$method,$key,$secret,$page=1){
		//先查找数据库
// 		$gname1 = mb_substr($gname,-3,3,'utf-8');
// 		$gname2 = mb_substr($gname,-2,2,'utf-8');
// 		$res = pdo_fetchall('select * from '.tablename('onebound_total_goods').' where ( title like "%'.$gname.'%" or title like "%'.$gname1.'%" or title like "%'.$gname2.'%" ) and sales>='.$config['sold']);
		$res = pdo_fetchall('select * from '.tablename('onebound_total_goods').' where keywords="'.$gname.'" and sales>='.$config['sold']);
		$count = count($res);

		$pagecount = 1;
		if(empty($res)){
			//先查找1688
			$res = $this->onebound_itemSearch('1688',$key,$secret,$gname,$page,$method);
			$count = $res['items']['real_total_results'];//总数
			$pagecount = $res['items']['pagecount'];//总页数
			$res = $res['items']['item'];//商品列表
		}
        
		//根据页数查找
		if(empty($count)){
			//去搜索淘宝
//			$res = $this->onebound_taobao_search($key,$secret,$gname,$page,$method);
//			$count = $res['items']['real_total_results'];//总数
//			$pagecount = $res['items']['pagecount'];//总页数
//			$res = $res['items']['item'];
//			$goods = $this->onebound_goods_sort($res,$config,$pagecount,1,$gname);
			$goods = [];
		}else{
			//数据表数据或1688接口数据
			if(empty($res)){
				$goods = [];
			}else {
				$goods = $this->onebound_goods_sort($res, $config, $pagecount, 2, $gname);
			}
//			if(empty($goods)){
//				//1688没有合适的数据,然后去查询taobao
//				$res = $this->onebound_taobao_search($key,$secret,$gname,$page,$method);
//				$count = $res['items']['real_total_results'];//总数
//				$pagecount = $res['items']['pagecount'];//总页数
//				$res = $res['items']['item'];
//				if(empty($res)){
//					$goods = [];
//				}else{
//					$goods = $this->onebound_goods_sort($res,$config,$pagecount,1,$gname);
//				}
//			}
		}

		return $goods;
	}

	/**
	 * 查找二维数组中是否有相同数据
	 * @param $needle 字符串
	 * @param $haystack 二维数组
	 */
	function in_array_r($needle, $haystack, $strict = false) {
		foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
				return true;
			}
		}

		return false;
	}
	
	function in_array_r2($needle, $haystack,$column='') {
		foreach ($haystack as $item) {
			if (($item[$column] == $needle)) {
				return true;
			}
		}

		return false;
	}

	public function ihttp_post($url,$post_data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}

	/** 
	 * 通莞支付宝扫码支付。
	 * @params 订单支付信息
	 * @config  配置信息
	 * 2017-11-20
	 */
	public function Tgalipay_scode($params, $config) {
	
		global $_W;
		$wOpt = array();
		$package = array();
		$package['account'] = $config['mchid'];
		$package['payMoney'] = $params['fee'];
		$package['lowOrderId'] = $params['tid'];
		$package['body'] = $params['title'];
		if($params['typ']=='custompayment'){
		    $package['notifyUrl'] = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgalipay/customnotify.php';  
		}elseif($params['typ']=='onlinepayment'){
			$package['notifyUrl'] = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgalipay/onlinenotify.php';
		}elseif($params['typ']=='gatherpayment'){
			#集运收款
			$package['notifyUrl'] = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgalipay/gathernotify.php';
		}elseif($params['typ']=='gatherbalancepayment'){
			#集运收款
			$package['notifyUrl'] = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgalipay/gatherbalancenotify.php';
		}else{
		    $package['notifyUrl'] = 'http://shop.gogo198.cn/addons/sz_yi/payment/tgalipay/notify.php';    
		}
		
//		$package['notifyUrl'] = $params['notifyUrl'];
		$package['payType'] = '1';
		//转换key=value&key=value;   
		$str = $this->tostrings($package);
		//拼接加密字串
		$str .= '&key=' . $config['key'];
		//MD5加密字串
		$sign = md5($str);
		//返回加密字串转换成大写字母
		$package['sign'] = strtoupper($sign);
		//数据包转换成json格式
		$data =  json_encode($package);
		//数据请求地址，post形式传输
		$url = 'https://ipay.833006.net/tgPosp/services/payApi/unifiedorder';
//		$url = 'http://tgjf.833006.biz/tgPosp/services/payApi/unifiedorder';//测试地址
		//数据请求地址，post形式传输
		$response = $this->ihttp_posts($url,$data);
		//解析json数据
		$response = json_decode($response,TRUE);
		//返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
//		return $response->codeUrl;//返回支付URL
//		return $response;//{message: "下游订单号重复", status: 101}
		return $response;

	}

	/**
	 * @数据请求提交POST json
	 * @$url:请求地址
	 * @post_data:请求数据
	 */
	public function ihttp_posts($url,$post_data){
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
	
	/**
 * 字符串拼接
 * @arrs :数组数据
 */
public function tostring($arrs) {
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
	 * 字符串拼接
	 * @arrs :数组数据
	 */
	public function tostrings($arrs){
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



	//微信支付
	public function wechat_build($params, $wechat, $type = 0)
	{
		global $_W;
		load()->func('communication');
		if (empty($wechat['version']) && !empty($wechat['signkey'])) {
			$wechat['version'] = 1;
		}

		$wOpt = array();

		if ($wechat['version'] == 1) {
			$wOpt['appId'] = $wechat['appid'];
			$wOpt['timeStamp'] = TIMESTAMP . '';
			$wOpt['nonceStr'] = random(8) . '';
			$package = array();
			$package['bank_type'] = 'WX';
			$package['body'] = urlencode($params['title']);
			$package['attach'] = $_W['uniacid'] . ':' . $type;
			$package['partner'] = $wechat['partner'];
			$package['device_info'] = 'sz_yi';
			$package['out_trade_no'] = $params['tid'];
			$package['total_fee'] = $params['fee'] * 100;
			$package['fee_type'] = '1';
			$package['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/wechat/notify.php';
			$package['spbill_create_ip'] = CLIENT_IP;
			$package['input_charset'] = 'UTF-8';
			ksort($package);
			$string1 = '';

			foreach ($package as $key => $v) {
				if (empty($v)) {
					continue;
				}

				$string1 .= $key . '=' . $v . '&';
			}

			$string1 .= 'key=' . $wechat['key'];
			$sign = strtoupper(md5($string1));
			$string2 = '';

			foreach ($package as $key => $v) {
				$v = urlencode($v);
				$string2 .= $key . '=' . $v . '&';
			}

			$string2 .= 'sign=' . $sign;
			$wOpt['package'] = $string2;
			$string = '';
			$keys = array('appId', 'timeStamp', 'nonceStr', 'package', 'appKey');
			sort($keys);

			foreach ($keys as $key) {
				$v = $wOpt[$key];

				if ($key == 'appKey') {
					$v = $wechat['signkey'];
				}

				$key = strtolower($key);
				$string .= $key . '=' . $v . '&';
			}

			$string = rtrim($string, '&');
			$wOpt['signType'] = 'SHA1';
			$wOpt['paySign'] = sha1($string);
			return $wOpt;
		}

		$package = array();
		$package['appid'] = $wechat['appid'];
		$package['mch_id'] = $wechat['mchid'];
		$package['nonce_str'] = random(8) . '';
		$package['body'] = $params['title'];
		$package['device_info'] = 'sz_yi';
		$package['attach'] = $_W['uniacid'] . ':' . $type;
		$package['out_trade_no'] = $params['tid'];
		$package['total_fee'] = $params['fee'] * 100;
		$package['spbill_create_ip'] = CLIENT_IP;
		$package['notify_url'] = $_W['siteroot'] . 'addons/sz_yi/payment/wechat/notify.php';
		$package['trade_type'] = $params['trade_type'] == 'NATIVE' ? 'NATIVE' : 'JSAPI';
		$package['openid'] = $_W['fans']['from_user'];
		ksort($package, SORT_STRING);
		$string1 = '';

		foreach ($package as $key => $v) {
			if (empty($v)) {
				continue;
			}

			$string1 .= $key . '=' . $v . '&';
		}

		//$string1 .= 'key=' . $wechat['signkey'];
		$string1 .= 'key=' . $wechat['apikey'];
		$package['sign'] = strtoupper(md5($string1));

		// 2019-12-25  更新表请求数据
        pdo_update('core_paylog',['reqReq'=>json_encode($package)],['tid'=>$params['tid']]);

        $dat = array2xml($package);
		$response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);

		if (is_error($response)) {
			return $response;
		}

		$xml = @simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);

		if (strval($xml->return_code) == 'FAIL') {
			return error(-1, strval($xml->return_msg));
		}

		if (strval($xml->result_code) == 'FAIL') {
			return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
		}

		$prepayid = $xml->prepay_id;
		$wOpt['appId'] = $wechat['appid'];
		$wOpt['timeStamp'] = TIMESTAMP . '';
		$wOpt['nonceStr'] = random(8) . '';
		$wOpt['package'] = 'prepay_id=' . $prepayid;
		$wOpt['signType'] = 'MD5';

		if ($params['trade_type'] == 'NATIVE') {
			$code_url = (array) $xml->code_url;
			$wOpt['code_url'] = $code_url[0];
		}

		ksort($wOpt, SORT_STRING);
        $string = '';
		foreach ($wOpt as $key => $v) {
			$string .= $key . '=' . $v . '&';
		}

		//$string .= 'key=' . $wechat['signkey'];
		$string .= 'key=' . $wechat['apikey'];
		$wOpt['paySign'] = strtoupper(md5($string));
		return $wOpt;
	}

	public function getAccount()
	{
		global $_W;
		load()->model('account');

		if (!empty($_W['acid'])) {
			return WeAccount::create($_W['acid']);
		}

		$acid = pdo_fetchcolumn('SELECT acid FROM ' . tablename('account_wechats') . ' WHERE `uniacid`=:uniacid LIMIT 1', array(':uniacid' => $_W['uniacid']));
		return WeAccount::create($acid);
	}

	public function shareAddress()
	{
		global $_W;
		global $_GPC;
		$appid = $_W['account']['key'];
		$secret = $_W['account']['secret'];
		load()->func('communication');
		$url = $_W['siteroot'] . 'app/index.php?' . $_SERVER['QUERY_STRING'];

		if (empty($_GPC['code'])) {
			$oauth2_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appid . '&redirect_uri=' . urlencode($url) . '&response_type=code&scope=snsapi_base&state=123#wechat_redirect';
			header('location: ' . $oauth2_url);
			exit();
		}

		$code = $_GPC['code'];
		$token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $appid . '&secret=' . $secret . '&code=' . $code . '&grant_type=authorization_code';
		$resp = ihttp_get($token_url);
		$token = @json_decode($resp['content'], true);
		if (empty($token) || !is_array($token) || empty($token['access_token']) || empty($token['openid'])) {
			return false;
		}

		$package = array('appid' => $appid, 'url' => $url, 'timestamp' => time() . '', 'noncestr' => random(8, true) . '', 'accesstoken' => $token['access_token']);
		ksort($package, SORT_STRING);
		$addrSigns = array();

		foreach ($package as $k => $v) {
			$addrSigns[] = $k . '=' . $v;
		}

		$string = implode('&', $addrSigns);
		$addrSign = strtolower(sha1(trim($string)));
		$data = array('appId' => $appid, 'scope' => 'jsapi_address', 'signType' => 'sha1', 'addrSign' => $addrSign, 'timeStamp' => $package['timestamp'], 'nonceStr' => $package['noncestr']);
		return $data;
	}

	public function createNO($table, $field, $prefix='GG')
	{
		$billno = date('YmdHis') . random(6, true);

		while (1) {
			$count = pdo_fetchcolumn('select count(*) from ' . tablename('sz_yi_' . $table) . ' where ' . $field . '=:billno limit 1', array(':billno' => $billno));

			if ($count <= 0) {
				break;
			}

			$billno = date('YmdHis') . random(6, true);
		}

		return $prefix . $billno;
	}

	public function html_images($detail = '')
	{
		$detail = htmlspecialchars_decode($detail);
		preg_match_all('/<img.*?src=[\\\'| "](.*?(?:[\\.gif|\\.jpg|\\.png|\\.jpeg]?))[\\\'|"].*?[\\/]?>/', $detail, $imgs);
		$images = array();

		if (isset($imgs[1])) {
			foreach ($imgs[1] as $img) {
				$im = array('old' => $img, 'new' => save_media($img));
				$images[] = $im;
			}
		}

		foreach ($images as $img) {
			$detail = str_replace($img['old'], $img['new'], $detail);
		}

		return $detail;
	}

	public function getSec($uniacid = 0)
	{
		global $_W;

		if (empty($uniacid)) {
			$uniacid = $_W['uniacid'];
		}

		$set = pdo_fetch('select sec from ' . tablename('sz_yi_sysset') . ' where uniacid=:uniacid limit 1', array(':uniacid' => $uniacid));

		if (empty($set)) {
			$set = array();
		}

		return $set;
	}

	public function paylog($log = '')
	{
		global $_W;
		$_obf_DQwmGRMGLQoRDCciPUABBTATGBM_IwE_ = m('cache')->getString('paylog', 'global');

		if (!empty($_obf_DQwmGRMGLQoRDCciPUABBTATGBM_IwE_)) {
			$path = IA_ROOT . '/addons/sz_yi/data/paylog/' . $_W['uniacid'] . '/' . date('Ymd');

			if (!is_dir($path)) {
				load()->func('file');
				@mkdirs($path, '0777');
			}

			$file = $path . '/' . date('H') . '.log';
			file_put_contents($file, $log, FILE_APPEND);
		}
	}

	public function checkClose()
	{
		if (strexists($_SERVER['REQUEST_URI'], '/web/')) {
			return NULL;
		}

		$shop = $this->getSysset('shop');

		if (!empty($shop['close'])) {
			if (!empty($shop['closeurl'])) {
				header('location: ' . $shop['closeurl']);
				exit();
			}

			exit("<!DOCTYPE html>\n                    <html>\n                        <head>\n                            <meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'>\n                            <title>抱歉，商城暂时关闭</title><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1, user-scalable=0'><link rel='stylesheet' type='text/css' href='https://res.wx.qq.com/connect/zh_CN/htmledition/style/wap_err1a9853.css'>\n                        </head>\n                        <body>\n                        <style type='text/css'>\n                        body { background:#fbfbf2; color:#333;}\n                        img { display:block; width:100%;}\n                        .header {\n                        width:100%; padding:10px 0;text-align:center;font-weight:bold;}\n                        </style>\n                        <div class='page_msg'>\n                        \n                        <div class='inner'><span class='msg_icon_wrp'><i class='icon80_smile'></i></span>" . $shop['closedetail'] . "</div></div>\n                        </body>\n                    </html>");
		}
	}

	public function mylink()
	{
		global $_W;
		$mylink['designer'] = p('designer');
		$mylink['categorys'] = pdo_fetchall('SELECT * FROM ' . tablename('sz_yi_article_category') . ' WHERE uniacid=:uniacid ', array(':uniacid' => $_W['uniacid']));

		if ($mylink['designer']) {
			$mylink['diypages'] = pdo_fetchall('SELECT id,pagetype,setdefault,pagename FROM ' . tablename('sz_yi_designer') . ' WHERE uniacid=:uniacid order by setdefault desc  ', array(':uniacid' => $_W['uniacid']));
		}

		$mylink['article_sys'] = pdo_fetch('SELECT * FROM ' . tablename('sz_yi_article_sys') . ' WHERE uniacid=:uniacid limit 1 ', array(':uniacid' => $_W['uniacid']));
		$mylink['article_sys']['article_area'] = json_decode($mylink['article_sys']['article_area'], true);
		$mylink['area_count'] = sizeof($mylink['article_sys']['article_area']);

		if ($mylink['area_count'] == 0) {
			$mylink['article_sys']['article_area'][0]['province'] = '';
			$mylink['article_sys']['article_area'][0]['city'] = '';
			$mylink['area_count'] = 1;
		}

		$mylink['goodcates'] = pdo_fetchall('SELECT id,name,parentid FROM ' . tablename('sz_yi_category') . ' WHERE enabled=:enabled and uniacid= :uniacid  ', array(':uniacid' => $_W['uniacid'], ':enabled' => '1'));
		return $mylink;
	}

	public function wechat_native_build($params, $wechat, $type = 0)
	{
		global $_W;
		load()->func('communication');
		$package = array();
		$package['appid'] = $wechat['appid'];
		$package['mch_id'] = $wechat['mchid'];
		$package['nonce_str'] = random(8) . '';
		$package['body'] = $params['title'];
		$package['device_info'] = isset($params['device_info']) ? 'sz_yi:' . $params['device_info'] : 'sz_yi';
		$package['attach'] = (isset($params['uniacid']) ? $params['uniacid'] : $_W['uniacid']) . ':' . $type;
		$package['out_trade_no'] = $params['tid'];
		$package['total_fee'] = $params['fee'] * 100;
		$package['spbill_create_ip'] = CLIENT_IP;
		$package['product_id'] = $params['goods_id'];

		if (!empty($params['goods_tag'])) {
			$package['goods_tag'] = $params['goods_tag'];
		}

		$package['time_start'] = date('YmdHis', TIMESTAMP);
		$package['time_expire'] = date('YmdHis', TIMESTAMP + 3600);
		$package['notify_url'] = empty($params['notify_url']) ? $_W['siteroot'] . 'addons/sz_yi/payment/wechat/notify.php' : $params['notify_url'];
		$package['trade_type'] = 'NATIVE';
		ksort($package, SORT_STRING);
		$string1 = '';

		foreach ($package as $key => $v) {
			if (empty($v)) {
				continue;
			}

			$string1 .= $key . '=' . $v . '&';
		}

		$string1 .= 'key=' . $wechat['apikey'];
		$package['sign'] = strtoupper(md5($string1));
		$dat = array2xml($package);
		$response = ihttp_request('https://api.mch.weixin.qq.com/pay/unifiedorder', $dat);

		if (is_error($response)) {
			return $response;
		}

		$xml = simplexml_load_string($response['content'], 'SimpleXMLElement', LIBXML_NOCDATA);

		if (strval($xml->return_code) == 'FAIL') {
			return error(-1, strval($xml->return_msg));
		}

		if (strval($xml->result_code) == 'FAIL') {
			return error(-1, strval($xml->err_code) . ': ' . strval($xml->err_code_des));
		}

		libxml_disable_entity_loader(true);
		$result = json_decode(json_encode($xml), true);
		return $result;
	}

	/**
	 * @param $url 二维码打开链接
	 * @param $folder 文件夹
	 */
	public function generate_qrcode($url,$folder){
		//链接生成二维码
		$errorCorrectionLevel = 'L';//错误等级，忽略
		$matrixPointSize = 4;
		require_once IA_ROOT.'/addons/sz_yi/phpqrcode.php';
		$path = $folder; //储存的地方
		if (!is_dir(IA_ROOT.$path)) {
			load()->func('file');
			mkdirs(IA_ROOT.$path); //创建文件夹
		}
		$infourl = $url;
		$filename =  $path.time().'.png'; //图片文件
		QRcode::png($infourl, IA_ROOT.$filename, $errorCorrectionLevel, $matrixPointSize, 2); //生成图片
		$qrcode = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn',IA_ROOT.$filename);
		return $qrcode;
	}


	//审批系统判断用户角色是否有权限
	public function check_approval_role($role_id,$company_id,$now_func=0){
		if(empty($now_func)){
			return 0;
		}
		$role = pdo_fetch('select * from '.tablename('approval_role').' where id=:role_id and decl_id=:decl_id',[':role_id'=>$role_id,':decl_id'=>$company_id]);
		$role['authList'] = json_decode($role['authList'],true);
		if(empty($role['authList'])){
			#还没有配置权限，则按禁读禁写
			return 3;
		}
		foreach($role['authList'] as $k=>$v){
			if($now_func==$v['authId']){
				return $v['authType'];
			}
		}
	}
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
