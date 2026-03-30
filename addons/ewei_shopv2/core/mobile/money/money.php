<?php
/**
 *	代收款工具
 */
 if (!(defined('IN_IA'))) {
 	exit('Access Denied');
 }
class Money_EweiShopV2Page extends mobilePage
{

    function main()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		//获取对应商家编号：
		$mcid = isset($_GPC['mcid'])?$_GPC['mcid']:1;
		$mcinfo = pdo_get('pay_mcinfo',array('id' => $mcid),array());//获取对应商家的信息；
		
		
    	$title = '商家收银台';
    	$money = 0.50;//本次可获得返利    	
    	$getm = pdo_get('pay_getmoney',array('openid' => $_W['openid']),array('money'));//总返利
    	
		//GOGO公众号链接;
		//http://shop.gogo198.cn/app/index.php?i=3&c=entry&m=ewei_shopv2&do=mobile&r=money.money
		
		include $this->template('money/new');
    }
    
    //获取消费返利；
    function getMoney()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['getM']) && $_GPC['getM'] == 'yes' ) {
			
			$ms['state'] = 0;
			$ms['msg'] = '获取成功,可立即使用！';
			
			$where = [
				'phone'=>$_GPC['phone'],
			];
			
			$where1 = [
				'openid'=>$_W['openid'],
				'phone'=>$_GPC['phone'],
			];
			//获取钱包的数据；
			$getm = pdo_get('pay_getmoney',$where,array());
			if(!empty($getm)) {//如果存在该手机用户
				$updata = [
					'money' => ($getm['money'] + $_GPC['money']), 
					'updatetime'=>time(),
				];
				//
				$res = pdo_update('pay_getMoney',$updata,$where1);
				if(!$res){
					$ms['state'] = 1;
					$ms['msg'] = '该手机用户已存在！';
				}
				
			}else {//如果数据等于空就插入数据
				
				$inserData = [
					'openid'=>$_W['openid'],
					'phone'=>$_GPC['phone'],
					'money' => $_GPC['money'],
					'createtime'=>time(),
				];
				
				$res = pdo_insert('pay_getmoney',$inserData);
				if(empty($res)){
					$ms['state'] = 1;
					$ms['msg'] = '获取失败，请重新获取';
				}
			}
		}
		echo json_encode($ms);
    }
    
    //微信支付；
    function wechat()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;

		$package = [
			'fee'=>$_GPC['payMoney'],//订单金额
			'tid'=>'RN'.date('YmdHis',time()).$this->randS(16),//订单编号
			'title'=>$_GPC['title'],//订单描述
			'openid'=>$_W['openid'],
			'createtime'=>time(),//创建时间
			'pay_type'=>'wechat',
		];

		$config = [
			'mchid'=>'101540254006',//商户号
			'key'=>'f8ee27742a68418da52de4fca59b999e',//秘钥
		];
		
		//支付流水表ims_pay_Mold
		$mold = [
			'openid' => $_W['openid'],
			'mcid' => $_GPC['mcid'],//商户ID
			'shop_name'=>$_GPC['title'],//商品名称
			'ordersn' => 'SN'.date('YmdHis',time()).time().$this->randS(9),//流水号
			'tid' => $package['tid'],//订单编号
			'status'=> '0',//订单状态：0未支付，1：支付成功，2支付失败；
			'c_time' => time(),//订单创建时间;
			'pay_type' => 'wechat',
		];
		$res = pdo_insert('pay_Mold',$mold);

		/**
		 *	ims_pay_money 收款表；
		 */
		
		$res = pdo_insert('pay_money',$package);
		
		if(!empty($res)) {
			
			$uid = pdo_insertid();
			//请求支付微信支付
			$tgwechat_public = m('common')->pay_wechat($package, $config);
							
			if($tgwechat_public['status'] == '100' && $tgwechat_public['message'] == '获取成功') {	
												
				if(isset($tgwechat_public['pay_url'])) {
					
					$data = ['statu'=>'success','payurl'=>$tgwechat_public['pay_url'],];
					echo json_encode($data);
				}
				
			}else {
				
				$data = ['statu'=>'error','payurl'=>$tgwechat_public['message']];
				echo json_encode($data);
			}
		}
		
    }
    
    //支付宝支付；
    function alipay()
    {
    	load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$package = [
			'fee'=>$_GPC['payMoney'],//订单金额
			'tid'=>'RN'.date('YmdHis',time()).$this->randS(16),//订单编号
			'title'=>$_GPC['title'],//订单描述
			'openid'=>$_W['openid'],
			'createtime'=>time(),//创建时间
			'pay_type'=>'alipay',
		];
		
		$config = [
			'mchid'=>'101540254006',//商户号
			'key'=>'f8ee27742a68418da52de4fca59b999e',//秘钥
		];
		
		//支付流水表ims_pay_Mold
		$mold = [
			'openid' => $_W['openid'],
			'mcid' => $_GPC['mcid'],//商户ID
			'shop_name'=> $_GPC['title'],//商品名称
			'ordersn' => 'SN'.date('YmdHis',time()).time().$this->randS(9),//流水号
			'tid' => $package['tid'],//订单编号
			'status'=> '0',//订单状态：0未支付，1：支付成功，2支付失败；
			'c_time' => time(),//订单创建时间；		
			'pay_type' => 'alipay',	
		];
		$res = pdo_insert('pay_Mold',$mold);

		//插入数据到表中；
		$res = pdo_insert('pay_money',$package);		
		if(!empty($res)) {
			
			$pay_alipay = m('common')->pay_alipay($package, $config);
			if($pay_alipay['status'] == '100' && $pay_alipay['message'] == '获取二维码成功') {
								
				if(isset($pay_alipay['codeUrl'])) {
					
					$data = ['statu'=>'success','payurl'=>$pay_alipay['codeUrl'],];
					echo json_encode($data);
				}

			}else{
				$data = ['statu'=>'error','payurl'=>'',];
				echo json_encode($data);
			}
		}
    }
    
    //9位随机数
    function randS($n = 1)
	{
		$ychar="0,1,2,3,4,5,6,7,8,9,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z";
		$list=explode(",",$ychar);
		
		for($i=0;$i<$n;$i++){
			
			$randnum=rand(0,35); // 10+26;
			
			$authnum.=$list[$randnum];
		}
		
		return $authnum;
	}
    
    
}


?>