<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
class Tgpay_EweiShopV2Page extends MobileLoginPage
{
	
	//集合支付
	public function tgpay() {
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;

		if(!empty($_GPC['http']) && $_GPC['http']=='ok') {
						
			$orderid = intval($_GPC['orderid']);//订单id
			
			if(isset($_GPC['openid']) && isset($_GPC['uniacid'])){
				$openid = $_GPC['openid'];
				$uniacid = $_GPC['uniacid'];
			}else{
				$openid = $_W['openid'];
				$uniacid = $_W['uniacid'];
			}
			
			
			//获取该笔订单ID，订单编号ordersn，订单交易金额；
			$order = pdo_fetch('select id,ordersn,price,status,openid from ' . tablename('ewei_shop_order') . ' where id=:id and uniacid=:uniacid and openid=:openid limit 1', array(':id' => $orderid, ':uniacid' => $uniacid, ':openid' => $openid));
			//获取商品配置，与支付状态是否开启：app_tg = 1开启，app_tg=0关闭；
			$set = m('common')->getSysset(array('shop', 'pay'));	
			//显示订单名称；
			$param_title = $set['shop']['name'] . '订单';//Gogo|跨境購订单
			
			$sec = m('common')->getSec();//获取证书
			//返序列化输出   返回商城支付配置
			$sec = iunserializer($sec['sec']);

			/**
			 * 订单信息
			 */
			$params = array();
			$params['tid'] = $order['ordersn'];
			$params['fee'] = $order['price'];
			$params['openid'] = $_GPC['newOpenid']?$_GPC['newOpenid']:$order['openid'];
			
//			$params['openid'] = 'ov3-btyLPTGwIduBvEXdiGSnpUK4';//GOGO公众号Openid
			$params['title'] = $param_title;
			
			//配置参数
			$options = array();
			$options = $sec['app_tg'];//微信支付配置
			$options['appid'] = $_W['account']['key'];//公众号ID
			$options['secret'] = $_W['account']['secret'];//公众号秘钥

//			$options['mchid'] = '13974747474';
//			$options['key'] = '5f61d7f65b184d19a1e006bc9bfb6b2f';

			/**
			 * status状态码：-1取消订单，0：待付款，1：待发货，2：待收货，3：已完成（待评价）
			 */
			if($order['status'] == '0') {
				if(($_GPC['http'] == 'ok') && ($set['pay']['app_tg'] == 1)) {
				
					switch($_GPC['type']) {
						
						case 'tgwechat'://微信公众号支付；
							$tgwechat_public = m('common')->Tgwechat_public($params, $options);					
							if($tgwechat_public['status'] == '100' && $tgwechat_public['message'] == '获取成功') {									
								if(isset($tgwechat_public['pay_url'])) {									
									$codeurl = $tgwechat_public['pay_url'];
								}								
							}else if($tgwechat_public['status'] == '101' && $tgwechat_public['message'] == '下游订单号重复') {
								//生成新订单
								$ordersn = m('common')->createNO('order', 'ordersn', 'SH');
								//更新新订单到表中
								$updata = pdo_update('ewei_shop_order', array('ordersn' => $ordersn), array('id' => $orderid));
								if(!empty($updata)) {									
									$params['tid'] = $ordersn;	
																	
									$tgwechat_public = m('common')->Tgwechat_public($params, $options);									
									if($tgwechat_public['status'] == '100' && $tgwechat_public['message'] == '获取成功') {										
										if(isset($tgwechat_public['pay_url'])) {		
											$codeurl = $tgwechat_public['pay_url'];
										}
									}
								}								
							}
							echo $codeurl;
							break;
							
						case 'tgwechat1'://微信公众号支付；GOGO公众号支付
							$tgwechat_public = m('common')->Tgwechat_public($params, $options);					
							if($tgwechat_public['status'] == '100' && $tgwechat_public['message'] == '获取成功') {									
								if(isset($tgwechat_public['pay_url'])) {									
									$codeurl = $tgwechat_public['pay_url'];//
								}								
							}else if($tgwechat_public['status'] == '101' && $tgwechat_public['message'] == '下游订单号重复') {
								//生成新订单
								$ordersn = m('common')->createNO('order', 'ordersn', 'SH');
								//更新新订单到表中
								$updata = pdo_update('ewei_shop_order', array('ordersn' => $ordersn), array('id' => $orderid));
								if(!empty($updata)) {									
									$params['tid'] = $ordersn;	
																	
									$tgwechat_public = m('common')->Tgwechat_public($params, $options);									
									if($tgwechat_public['status'] == '100' && $tgwechat_public['message'] == '获取成功') {										
										if(isset($tgwechat_public['pay_url'])) {		
											$codeurl = $tgwechat_public['pay_url'];
										}
									}
								}								
							}
//							echo $codeurl;
							header('Location:'.$codeurl);
							break;
							
						case 'tgwechath5'://微信H5支付；2017-12-14
							$tgwechat_h5 = m('common')->Tgwechat_h5($params, $options);					
							if($tgwechat_h5['status'] == '100' && $tgwechat_h5['message'] == '获取成功') {									
								if(isset($tgwechat_h5['pay_url'])) {									
									$codeurl = $tgwechat_h5['pay_url'];//		
								}								
							}else if($tgwechat_h5['status'] == '101' && $tgwechat_h5['message'] == '下游订单号重复') {
								//生成新订单
								$ordersn = m('common')->createNO('order', 'ordersn', 'SH');
								//更新新订单到表中
								$updata = pdo_update('ewei_shop_order', array('ordersn' => $ordersn), array('id' => $orderid));
								if(!empty($updata)) {									
									$params['tid'] = $ordersn;	
																	
									$tgwechat_h5 = m('common')->Tgwechat_h5($params, $options);
									if($tgwechat_h5['status'] == '100' && $tgwechat_h5['message'] == '获取成功') {
										if(isset($tgwechat_h5['pay_url'])) {
											$codeurl = $tgwechat_h5['pay_url'];
										}
									}
								}
							}
							
//							echo $codeurl;
							$st = [
//								'url' => $codeurl,
								'msg' => $tgwechat_h5['message'],
							];
							echo json_encode($st);
							break;
							
					/*case 'tgwechat':
						//微信扫码支付
						$tgwechat_scode = m('common')->tgwechat_scode($params, $options);							
						if($tgwechat_scode['status'] == '100' && $tgwechat_scode['message'] == '获取二维码成功') {									
							if(isset($tgwechat_scode['codeUrl'])) {									
								$codeurl = $tgwechat_scode['codeUrl'];//		
							}								
						}else if($tgwechat_scode['status'] == '101' && $tgwechat_scode['message'] == '下游订单号重复') {
							//生成新订单
							$ordersn = m('common')->createNO('order', 'ordersn', 'SH');
							//更新新订单到表中
							$updata = pdo_update('ewei_shop_order', array('ordersn' => $ordersn), array('id' => $orderid));
							if(!empty($updata)) {									
								$params['tid'] = $ordersn;									
								$tgwechat_scode = m('common')->tgwechat_scode($params, $options);									
								if($tgwechat_scode['status'] == '100' && $tgwechat_scode['message'] == '获取二维码成功') {										
									if(isset($tgwechat_scode['codeUrl'])) {		
										$codeurl = $tgwechat_scode['codeUrl'];
									}
								}
							}								
						}
						$codeUrl = m('qrcode')->createQrcode($codeurl);//创建二维码图片
						$code = [
							'opid' => $_W['openid'],
							'cUrl' => $codeUrl,
						];
						cache_write('Wcodeurl',$code);							
						break;*/
							
						case 'tgalipay':
							$tgalipay_scode = m('common')->tgalipay_scode($params, $options);
							if($tgalipay_scode['status'] == '100' && $tgalipay_scode['message'] == '获取二维码成功') {
									
								if(isset($tgalipay_scode['codeUrl'])) {									
									$codeurl = $tgalipay_scode['codeUrl'];//		
								}
								
							}else if($tgalipay_scode['status'] == '101' && $tgalipay_scode['message'] == '下游订单号重复') {
								//生成新订单
								$ordersn = m('common')->createNO('order', 'ordersn', 'SH');
								//更新新订单到表中
								$updata = pdo_update('ewei_shop_order', array('ordersn' => $ordersn), array('id' => $orderid));
								if(!empty($updata)) {
									
									$params['tid'] = $ordersn;
									
									$tgalipay_scode = m('common')->tgalipay_scode($params, $options);
									
									if($tgalipay_scode['status'] == '100' && $tgalipay_scode['message'] == '获取二维码成功') {
										
										if(isset($tgalipay_scode['codeUrl'])) {		
											$codeurl = $tgalipay_scode['codeUrl'];
										}
									}
								}								
							}
//							$codeUrl = m('qrcode')->createQrcode($codeurl);//创建二维码图片
							$code = [
								'opid' => $_W['openid'],
								'cUrl' => $codeurl,
							];
							cache_write('Acodeurl',$code);
							echo $codeurl;
						break;
					}
				}
			//通莞微信扫码聚合支付； 2017-12-11
			}
		}

	}
	//请求授权，静默授权
	public function getOuth() {
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$url = "http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.getOpenid";
		/**$_GPC
		    [orderid] => 331
		    [type] => tgwechat
		    [http] => ok
		    [opid] => oR-IB0t4Yc9zmV-K-_5NRB-u5k4U
		 */
		
		$newOpenid = pdo_get('ewei_shop_member',array('uniacid'=>14,'openid'=>$_GPC['opid']),array('newOpenid'));
		if(!empty($newOpenid['newOpenid'])) {
//			echo "yes";
			$url .= '&orderid='.$_GPC['orderid'].'&newOpenid='.$newOpenid['newOpenid'].'';
			header('Location:'.$url);
		}else {
			$url .= '&orderid='.$_GPC['orderid'].'&newOpenid='.$newOpenid['newOpenid'];
			
			header('Location:'.$url);
		}
	}
	
	//获取openid
	public function getOpenid(){
		global $_W;
		global $_GPC;
		print_r($_GPC);
//		echo "stringGetOpenid";
	}
	

	//微信扫码支付
	public function tgwechat() {
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		//加载缓存
		$res = cache_load('Wcodeurl');		
		
		if(($res['opid'] === $_W['openid'])) {
			$payUrl = $res['cUrl'];
		}else {
			$payUrl = '支付参数错误！';
		}
		include $this->template('order/tgwechat');
	}
	
	//支付宝扫码支付
	public function tgalipay() {

		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		//加载缓存
		$res = cache_load('Acodeurl');
				
//		if(($res['opid'] === $_W['openid'])) {
//			$payUrl = $res['cUrl'];
//		}else {
//			$payUrl = '支付参数错误！';
//		}
//		echo $payUrl;
		
//		$url = urldecode($res['cUrl']);
		$url = $res['cUrl'];
		if (!(is_weixin()))
		{
			header('location: ' . $url);
			exit();
		}
		header('location: ' . $url);
		exit();
//		include $this->template('order/tgalipay');
	}
	
}
?>