<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

class Pay_EweiShopV2Page extends mobilePage
{

    function main()
    {
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$title='订单支付';
		if(!empty($_GPC['orderid']))
		{
			$orderInfo = pdo_fetch("SELECT * FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn='".$_GPC['orderid']."' and (a.pay_status=0 or a.pay_status=2)");
			if (!$orderInfo){
				message('没有待支付订单或已变更');
			}
			$dikou = ($orderInfo['total'] - $orderInfo['pay_account']);
			$dikou = $dikou<=0?0.00:$dikou;
			//$dikou = $dikou?$dikou.'.00':0.00;
			$star = date("Y-m-d H:i:s",$orderInfo['starttime']);
			$end = date("Y-m-d H:i:s",$orderInfo['endtime']);
		}
		
		include $this->template('parking/payment');
    }


	//2018-04-13  前往支付：选择支付方式
	function payMoney()
	{
		load()->func('communication');
		global $_W;
		global $_GPC;
		$title='支付';
		if(!empty($_GPC['orderid']))
		{
//			$order = pdo_fetchall("select * from ".tablename('foll_order')." where user_id='".$_W['openid']."' and (pay_status=0 or pay_status=2)")[0];
			$order = pdo_fetchall("select * from ".tablename('foll_order')." where ordersn='".$_GPC['orderid']."' and (pay_status=0 or pay_status=2)")[0];
			if(!$order){
				message('没有未结订单');
			}
		}
		include $this->template('parking/payMoney');
	}

	
	//支付确认；2018-04-13
	function payCheck()
	{
		/**
		 * 开发步骤：
		 * 1、支付类型，：选主动支付、免密支付；
		 * 2、有支付类型：需要生成新的订单号；
		 * 授权
		 */
		load()->func('communication');
		global $_W;
		global $_GPC;
		
		if(!$_W['isajax']){
			show_json(0,['msg'=>'请求类型不正确']);
		}

        //			$payData = pdo_fetch("SELECT {$field} FROM ".tablename('foll_order')." WHERE ordersn = :ordersn AND (pay_status = :pay_status OR pay_status = :paystatus) ORDER BY id LIMIT 1", array(':ordersn' => $_GPC['orderid'], ':pay_status' => 0,':paystatus' => 2));
        $payType = ['alipay','wechat','UnionPay'];
        $field = "id,ordersn,pay_type";
        $find    = array(':ordersn' => $_GPC['orderid'], ':pay_status' => 0,':paystatus' => 2);
        $payData = pdo_fetch("SELECT {$field} FROM ".tablename('foll_order')." WHERE ordersn = :ordersn AND (pay_status = :pay_status OR pay_status = :paystatus)",$find);

        /*$find    = array(':ordersn' => $_GPC['orderid'], ':pay_status' => 1);
        $payData = pdo_fetch("SELECT {$field} FROM ".tablename('foll_order')." WHERE ordersn = :ordersn AND (pay_status = :pay_status)",$find);*/

        $prkId   = pdo_get("parking_order",['ordersn'=>$payData['ordersn']],['id']);
        if(empty($payData)) {
            show_json(0,['msg'=>'无订单需要支付']);
            //show_json(0,['msg'=>'该订单已支付']);
        }
        //当前表中订单编号；  旧订单号；
        $oldOrdersn = $payData['ordersn'];
        /**
         * 支付类型与订单表中的不相等，可取原来订单号，
         * 如果相等，需要重新生成订单号				 *
         */
        //当前选中支付类型是否：主动支付，并且表中的支付字段不属于主动支付； pay_type = wechat;
        if(in_array($_GPC['payType'],$payType) && $_GPC['payType'] != $payData['pay_type']) {

            //foll_order 订单表的ID； 更新支付类型
            $resup = pdo_update('foll_order', array('pay_type'=>$_GPC['payType']),array('id'=>$payData['id']));
            //请求数据
            $postdata = [
                'token' => $_GPC['payType'],//支付类型如：wechat,alipay,...
                'ordersn' => $oldOrdersn,//新订单编号
            ];

            //聚合支付请求URL
            $payurl = 'http://shop.gogo198.cn/payment/wechat/Tgpay.php';
            //http请求  更新与插入成功后，请求支付链接；
            /*if(empty($resup)) {
                $json['msg'] = 'update error';
                show_json(0,$json);
            }*/

            $res = $this->ihttp_post($payurl, $postdata);

            $res = json_decode($res);

            if(!empty($res) && ($res->msg =='success')) {

                $json['payUrl'] = $res->payurl;
                $json['pay_info'] = isset($res->pay_info) ? $res->pay_info : '';
                $json['msg']    = $res->msg;

            }else {
                $json['payUrl'] = '';
                $json['msg']    = $res->info?$res->info:'支付失败，请稍后重试！';
            }

            show_json(1,$json);

        } else {

            //支付类型与原来的相等，需重新生成订单号；
            $millisecond = round(explode(" ", microtime())[0]*1000);
            $orderId = 'G99198'  . date('Ymd', time()) .str_pad($millisecond,3,'0',STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111,999);
            $receive = array(
                'ordersn'=>$orderId,
                'pay_type' => $_GPC['payType'],//支付类型
            );
            //foll_order 订单表的ID；
            $resup = pdo_update('foll_order', $receive,array('id'=>$payData['id']));
            pdo_update('parking_order', ['ordersn'=>$orderId],array('id'=>$prkId['id']));

            /*if(empty($resup)) { 2019-01-13
                $json['msg'] = 'update error';
                show_json(0,$json);
            }*/
            /**
             * 根据旧的订单编号出所有 parking_order 表的数据，重新写入表中
             */
            /*$field = [
                'CarNo','number','starttime','endtime','moncard',
                'duration','status','charge_type','upOrderId','charge_status',
                'time_period','span_status'
            ];
            //获取数据； 根据旧订单号查询   parking_order
            $getData = pdo_get('parking_order',array('ordersn'=>$oldOrdersn),$field);
            //2018-04-10  	新的订单号
            $getData['ordersn'] = $receive['ordersn'];
            //查询出的数据，已最新的订单编号插入进数据库中；
            $insert = pdo_insert('parking_order',$getData);*/

            //请求数据
            $postdata = [
                'token' => $receive['pay_type'],//支付类型如：wechat,alipay,...
                'ordersn' => $receive['ordersn'],//新订单编号
                'orderId'=> $oldOrdersn,//旧订单编号
            ];

            //聚合支付请求URL
            $payurl = 'http://shop.gogo198.cn/payment/wechat/Tgpay.php';
            //http请求  更新与插入成功后，请求支付链接；
            //					if(!empty($resup) && !empty($insert))

            $res = $this->ihttp_post($payurl, $postdata);
            //解析JSON
            $res = json_decode($res);
            if(!empty($res) && ($res->msg =='success')) {

                $json['payUrl'] = $res->payurl;
                $json['pay_info'] = isset($res->pay_info) ? $res->pay_info : '';
                $json['msg']    = $res->msg;

            }else {

                $json['payUrl'] = '';
                $json['msg']    = $res->msg ? $res->msg : '支付失败，请稍后重试！';
            }
            show_json(1,$json);
        }

	}


	//聚合支付请求扣费
	function ajaxPost()
	{
		load()->func('communication');
		global $_W;
		global $_GPC;

		if($_W['isajax']) {

//			$signs = pdo_get('parking_authorize', array('openid' => $_W['openid']), array('auth_status', 'CarNo'));
//			if(is_array($signs) && $signs['auth_status'] == '1'){
//				$carNo = $signs['CarNo'];//车牌号
//			}else {
//				$carNo = '';//车牌号
//			}
//			$returnUrl ='http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
            $millisecond = round(explode(" ", microtime())[0]*1000);
            $orderId = 'G99198'  . date('Ymd', time()) .str_pad($millisecond,3,'0',STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111,999);
			$receive = array(
				'ordersn'=>$orderId,
				'pay_type' => $_GPC['payType'],//支付类型
			);
			//foll_order 订单表的ID；
			$resup = pdo_update('foll_order', $receive,array("openid"=>$_W['openid'],'id'=>$_GPC['oid']));
			
			/**
			 * 根据旧的订单编号出所有 parking_order 表的数据，重新写入表中
			 */
			$field = [
				'CarNo','number','starttime','endtime','moncard',
				'duration','status','charge_type','upOrderId','charge_status',
				'time_period','span_status'
			];
			//获取数据；
			$getData = pdo_get('parking_order',array('ordersn'=>$_GPC['order']),$field);
			//把表中的数据重新插回表中
//			$resup1 = pdo_update('parking_order', $receive,array("openid"=>$_W['openid'],'id'=>$_GPC['oid']));
			//2018-04-10
			$getData['ordersn'] = $receive['ordersn'];
			//查询出的数据，已最新的订单编号插入进数据库中；
			$insert = pdo_insert('parking_order',$getData);
			
			//查询订单编号，支付类型
//			$order = pdo_get('parking_order', array('id' => $orderid), array('ordersn','pay_type'));

//			$order = pdo_get('parking_order', array('openid' => $_W['Openid'],'pay_status'=>0), array('ordersn','pay_type'));

//			$order=pdo_fetchall("select * from ".tablename("parking_order")." WHERE openid = '".$_W['openid']."' AND pay_status = 0 OR pay_status = 2 LIMIT 1");

			//请求数据
			$postdata = [
				'token' => $receive['pay_type'],//支付类型如：wechat,alipay,...
				'ordersn' => $receive['ordersn'],//新订单编号
				'orderId'=>$_GPC['order'],//旧订单编号
			];

			//聚合支付请求URL
			$payurl = 'http://shop.gogo198.cn/payment/wechat/Tgpay.php';
			//http请求  更新与插入成功后，请求支付链接；
			if(!empty($resup) && !empty($insert)) {
				$res = $this->ihttp_post($payurl, $postdata);
				//解析JSON
				$res = json_decode($res);

				if(!empty($res) && ($res->msg =='success')) {

					$json['payUrl'] = $res->payurl;
                    $json['pay_info'] = isset($res->pay_info) ? $res->pay_info : '';
					$json['msg'] = $res->msg;

				}else {
					$json['payUrl'] = '';
					$json['msg'] = $res->msg;
				}

				echo json_encode($json);
				exit();
			}
//			$json['msg'] = 'error';

			$json['msg'] = $_GPC['oid'];

			echo json_encode($json);
		}

	}

	//停车免密扣费
	function praks() {

		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		date_default_timezone_set('Asia/Shanghai');

		$signs = pdo_get('parking_authorize', array('openid' => $_W['openid']), array('auth_status', 'CarNo','auth_type'));
		//反序列化查询授权类型  a:1:{i:0;s:11:"Credit_Card";}  a:1:{s:2:"wg";s:11:"Credit_Card";}
		$payType = unserialize($signs['auth_type']);

		$returnUrl ='http://shop.gogo198.cn/app/index.php?i='.$_W['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.pay';
        $millisecond = round(explode(" ", microtime())[0]*1000);
        $orderId = 'G99198'  . date('Ymd', time()) .str_pad($millisecond,3,'0',STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111,999);
		$receive = array(
			'Openid' => $_W['openid'],//用户openid
			'uniacid' => $_W['uniacid'],//公众号id
			'ordersn'=>$orderId,
			'number' => '1008',//车位编号
			'CarNo' => $signs['CarNo'],//车牌编号  粤YGB098  测试：粤A88S92
			'starttime' => time(),//进场时间
			'endtime' =>time()+60,//出场时间
			'total' =>2,//优惠前金额
			'PayAmount' =>1,//优惠后金额
			'body' =>'停车服务',//消费项目
			'pay_type' => 'Parks',//支付类型
			'paytime' =>'',//支付时间
			'pay_status' =>'0',//支付状态：0》未支付，1》已支付，2》支付失败；
			'create_time' =>time(),//订单创建时间
			'returnUrl' => $returnUrl,//成功前端同步地址；
		);
		//写入订单数据表中  parking_order
		$orders = pdo_insert('parking_order', $receive);
		if (!empty($orders)) {
	    	$orderid = pdo_insertid();
		}else {
			echo 'sqlError';
		}
		//查询订单编号，支付类型
		$order = pdo_get('parking_order', array('id' => $orderid), array('uniacid','ordersn','pay_type','openid','PayAmount','body'));

		if($signs['auth_status'] != 1 || $payType['wg'] != 'Credit_Card' ) {

			$sendArr = array(
				'touser' => $order['openid'],//接收消息的用户
				'payMoney' =>$order['PayAmount'],//交易金额
				'uniacid' =>$order['uniacid'],//公众号ID
				'body' => $order['body'],//商品描述
				'paytime' => date('Y-m-d h:i:s',time()),//消费时间
				'first' => '抱歉，您的停车服务费扣费失败！',
				'remark' =>'请点击详情，继续完成支付！',
				'Reurl' => $returnUrl,
			);
			//停车扣收失败！
			$send = sendMessagess($sendArr);
//			echo $send;

		} else {
			//请求数据
			$postdata = [
				'token' => $order['pay_type'],//停车支付  Parks
				'ordersn' => $order['ordersn'],//
			];
			//无感支付请求地址
			$payurl = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
			//http请求
			$res = $this->ihttp_post($payurl, $postdata);
			//解析JSON
//			echo "<pre>";
			$resd = json_decode($res,TRUE);
//			print_r($resd);
			if(($resd['Message']['Plain']['Result']['ResultCode'] == '07') && ($resd['Message']['Plain']['Result']['ResultMsg'] == '用户不存在')) {
				$sendArr = array(
					'touser' => $order['openid'],//接收消息的用户
					'payMoney' =>$order['PayAmount'],//交易金额
					'uniacid' =>$order['uniacid'],//公众号ID
					'body' => $order['body'],//商品描述
					'paytime' => date('Y-m-d h:i:s',time()),//消费时间
					'first' => '抱歉，您的停车服务费扣费失败！',
					'remark' =>'请点击详情，继续完成支付！',
					'Reurl' => $returnUrl,
				);
				//停车扣收失败！
				$send = sendMessagess($sendArr);
//				echo $send;
			}
		}
	}



	//测试公众号支付
	function pays() {
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if(isset($_GPC['codeUrl'])) {
			
			$codeurl = $_GPC['codeUrl'];
			$payUrl = m('qrcode')->createQrcode($codeurl);//创建二维码图片
		}
		
		include $this->template('parking/pays');
	}

	//拼接字串
	function toreceive($arrs){
		krsort($arrs);
		$str = '';
		foreach($arrs as $key=>$val){
			$str .= $key . '=' . $val . '&';
		}
		$str = trim($str,'&');
		return $str;
	}
	function ihttp_post($url,$post_data) {

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}
}
?>
