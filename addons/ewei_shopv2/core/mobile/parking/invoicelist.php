<?php
/**
 *	发票开具
 */
 if (!(defined('IN_IA'))) {
 	exit('Access Denied');
 }
class InvoiceList_EweiShopV2Page extends mobilePage
{

    function main()
    {
    	$title = '发票中心';
        include $this->template('parking/invoicelist');
    }
    
	/**
	 * 商城订单
	 */
	function shop()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$title = 'GOGO商城订单'; //'invoice_iskp'=>'0'  未开票的订单；
		$shop_order = pdo_getall('ewei_shop_order', array('openid' => $_W['openid'],'uniacid'=>$_W['uniacid'],'status' => 1,'invoice_iskp'=>'0'),array('id','price','paytime'));
		include $this->template('parking/invoice_shop');
	}
	
	
	/**
	 * 没有商城未付订单
	 */
	function shop_Norder()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$title = '未付电商订单'; //'invoice_iskp'=>'0'  未开票的订单；
		$titles = '无未付电商订单'; //'invoice_iskp'=>'0'  未开票的订单；
		$body = '无商城未付订单';
		
//		$shop_order = pdo_getall('ewei_shop_order', array('openid' => $_W['openid'],'uniacid'=>$_W['uniacid'],'status' => 1,'invoice_iskp'=>'0'),array('id','price','paytime'));
		include $this->template('parking/shop_Norder');
	}
	
	
	/**
	 * 商城订单开票
	 */
	function shop_post()
	{
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;

		$curlpost = new Curl;//实例化
		
		if($_W['isajax']) {
			//查询是否填写发票抬头
			$head = pdo_get('invoice_head', array('openid' => $_W['openid'], 'uniacid' => $_W['uniacid']));
			
			if(!empty($head)) {

//				$upInvoice = pdo_query("UPDATE ims_ewei_shop_order SET invoice_iskp = 1 WHERE id in(".trim($_GPC['parkCheck'],',').")");
				//获取所有要开票的信息；
				$upInvoice = pdo_fetchall("SELECT id,ordersn,price FROM " . tablename('ewei_shop_order') . " WHERE invoice_iskp = 0 and uniacid = ". $_W['uniacid']. " and openid = '".$_W['openid']."' and status = 1 and id in(".trim($_GPC['parkCheck'],',').")");				
				if(!empty($upInvoice)) {
					
					$sendArr = [
						'uniacid'=> $_W['uniacid'],
						
						'first'=>'您好,您有一份商城开票申请！',
						'ordersn'=>$upInvoice['0']['ordersn'],
						'name'=>$head['head_name'],
						'xmmc'=>'商城开票服务',
						'c_date'=>date('Y-m-d H:i:s',time()),
						'remark'=>'您有新的开票申请，请点击详情完成开票！',
						'touser'=>'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U',
//						'touser' => 'oR-IB0h7w3lGAxFTeeVAR3LraBZI',//接收处理人Openid
						//$_GPC['parkCheck']:ewei_shop_order 表中所有开票的自增ID，$head['id']：开票人的抬头信息自增ID；
						'Reurl'=>'http://shop.gogo198.cn/app/index.php?i='.$_W['uniacid'].'&c=entry&m=ewei_shopv2&do=mobile&r=parking.invoicelist.check_invoiceShop&kpid='.$_GPC['parkCheck'].'&headid='.$head['id'],			
					];
					
					$senRes = sendInvoices($sendArr);
					if($senRes == '发送成功') {
						$res = [
							'statu'=> 'success',
							'pdfurl'=> '',
						];
						echo json_encode($res);	
					}else {
						$res = [
							'statu'=> 'error',
							'pdfurl'=> '',
						];
						echo json_encode($res);	
					}
					
				}
								
			}else {
				
				$res = [
					'statu'=> 'headNodata',
					'pdfurl'=> mobileUrl('parking/invoicelist/headers'),//跳转发票抬头
				];
				echo json_encode($res);		
			}
		}
	}
	
	//确认商城开票
	function check_invoiceShop()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['kpid']) && !empty($_GPC['headid']))
		{
			//获取抬头信息
			$head = pdo_get('invoice_head', array('id' => $_GPC['headid']));
			$headData = [
				'Type_head' => $head['Type_head'],//塔头类型
				'head_name' => $head['head_name'],//抬头名称
				'nsrsbh' => $head['nsrsbh'],//纳税人识别号
				'email' => $head['email'],//电子邮箱
				'address' => $head['addressHead'].$head['address'],//纳税人地址
				'kpids' =>$_GPC['kpid'],//开票ID
				'uniacid'=>$head['uniacid'],
				'openid'=>$head['openid'],
				'headid' =>$head['id'],
			];
			
			//获取需要开票的订单信息
			$upInvoice = pdo_fetchall("SELECT id,ordersn,price FROM " . tablename('ewei_shop_order') . " WHERE invoice_iskp = 0 and uniacid = ". $head['uniacid']. " and openid = '".$head['openid']."' and status = 1 and id in(".trim($_GPC['kpid'],',').")");
			foreach($upInvoice as $key =>$val){
				if(is_array($val)){
					$headData['price'] += $val['price'];
				}
			}

		}
		include $this->template('parking/check_invoiceShop');

	}
	
	/**
	 * 点击确认开票；
	 */
	function kp_shop()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['kpids']) && $_GPC['kpstatus']=='yes')//开票
		{
			
			$head = pdo_get('invoice_head', array('id' => $_GPC['headid']));
			if(!empty($head))
			{
				$upkp = pdo_query("UPDATE ". tablename('ewei_shop_order')." SET invoice_iskp = 1 WHERE uniacid = ". $head['uniacid']. " and openid = '".$head['openid']."' and id in(".trim($_GPC['kpids'],',').")");
				if($upkp)//状态修改成功；
				{
					/**
					 * 修改商城订单表状态成功，插入数据到invoices_ord表中  
					 */
					$insert_data = [
						'uniacid'=>$_W['uniacid'],
						'openid'=>$head['openid'],
						'DDH'=>'GGP'.date('YmdHis',time()).rand(1,time()),
						'GHF_NSRSBH'=>$head['nsrsbh'],
						'GHF_YHZH'=>$head['nsrsbh'],
						'GHF_MC'=>$head['head_name'],
						'KPLX'=>'1',
						'CZDM'=>'10',
						'XMMC'=>'电商购物订单',
						'DW'=>'件',
						'GGXH'=>'GOGO10010',
						'XMSL'=>'1.00',
						'XMDJ'=>$_GPC['money'],
						'XMJE'=>$_GPC['money'],
						'create_date'=>time(),
						'state'=>'1',
						'invoice_type'=>'mall',
						'GHF_EMAIL'=>$head['email'],
						'GHF_DZ'=>$head['addressHead'].$head['address'],//纳税人地址
						'PDF_URL'=>$_GPC['fpordersn'],
					];
					
					$result = pdo_insert('invoices_ord', $insert_data);
					if (!empty($result)) {
					    $oid = pdo_insertid();
					}
					
					$sendArr = [
						'uniacid'=> $_GPC['uniacid'],
						'touser'=>$_GPC['openid'],
						'first'=>'您好,您有一个新的发票消息！',
						'ordersn'=>$_GPC['fpordersn'],
						'c_date'=>date('Y-m-d H:i:s',time()),
						'remark'=>'您好您的发票已开具，请及时查收您的电子邮箱！',	
						//$_GPC['parkCheck']:ewei_shop_order 表中所有开票的自增ID，$head['id']：开票人的抬头信息自增ID；
						'Reurl'=>'',
					];
					
					//发送模板消息；
	//				$senRes = sendKp($sendArr);

					echo json_encode(['status'=>1,'message'=>'发送成功']);
					
				}else {//状态修改失败

					echo json_encode(['status'=>0,'message'=>'发送失败']);
				}
			}

		} else if($_GPC['kpstatus']=='no') {//拒绝开票		jjly,checkid,

			$sendArr = [
				'uniacid'=> $_GPC['uniacid'],
				'touser'=>$_GPC['openid'],
				'first'=>'您好,您有一个新的发票消息！',
				'ordersn'=>'',
				'jjly'=>$_GPC['jjly'],//拒绝理由
				'c_date'=>date('Y-m-d H:i:s',time()),
				'remark'=>'您好,您的发票开票申请已被管理员拒绝！',
				'Reurl'=>'',
			];

			echo json_encode(['status'=>0,'message'=>'已拒绝开票']);
//			echo json_encode(['status'=>0,'message'=>$_GPC]);
		}
	}

	

	/**
	 * 历史开票
	 */
	function shop_history()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$paras = [
			'openid' => $_W['openid'],
			'uniacid'=> $_W['uniacid'],
			'invoice_type'=>'mall',
		];
		
		//查询总条数
		$count = pdo_fetch('SELECT COUNT(*) as count  FROM ' . tablename('invoices_ord') . ' WHERE uniacid = :uniacid and openid = :openid and invoice_type = :invoice_type and state = 1', $paras);
		$total = $count['count'];
		$pageindex = intval($_GPC['page'])?intval($_GPC['page']):'1';
        $pagesize = 5;
        $pager = pagination($total, $pageindex, $pagesize);
		$p = ($pageindex-1) * $pagesize;

        $shop_history = pdo_fetchall("SELECT id,create_date,XMMC,XMJE,PDF_URL FROM " . tablename('invoices_ord') . " WHERE invoice_type='mall' and uniacid = ". $_W['uniacid']. " and openid = '".$_W['openid']."' and state = 1 ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);  
		$title = '电商开票历史'; //'invoice_iskp'=>'0'  未开票的订单;		
//		$history_order = pdo_getall('invoices_ord', array('openid' => $_W['openid'],'uniacid'=> $_W['uniacid'],'state' => '1'),array('create_date','XMMC','XMJE','PDF_URL'));		
		include $this->template('parking/shop_history');
	}
	
	function shop_info()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['orderid'])){
			$where = [
				'uniacid' => $_W['uniacid'],
				'openid'=>$_W['openid'],
				'invoice_type' =>'mall',
				'state' => '1',
				'id' => $_GPC['orderid'],
			];
			
			$params = [
				'create_date',
				'XMMC',
				'XMJE',
				'PDF_URL',
				'GHF_EMAIL',
				'GHF_MC',
				'GHF_NSRSBH',
			];
			
			$history_info = pdo_get('invoices_ord',$where, $params);
		}
		include $this->template('parking/shop_info');		
	}


	
	
	
	/**
	 * 未支付停车订单 2018-1-23
	 */
	function park_Norder()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;

//		$paras = [
//			':user_id' => $_W['openid'],
//			':uniacid'=> $_W['uniacid'],
//			':pay_status'=>0,
//			':application'=>'parking',//应用类型： 停车：parking，商城：shop，自助：food，预约：reservation，分时：fenshi
//		];
		//查询总条数
//		$count = pdo_fetch('SELECT COUNT(*) as count  FROM ' . tablename('parking_order') . ' WHERE uniacid = :uniacid and openid = :openid and pay_status = :pay_status', $paras);
//		$count = pdo_fetch('SELECT COUNT(*) as count  FROM ' . tablename('foll_order') . ' WHERE uniacid = :uniacid and user_id = :openid and pay_status = :pay_status and application = :application', $paras);
		
//		$count = pdo_fetch('SELECT COUNT(id) AS count FROM '.tablename('foll_order'),$paras);
		//记录总条数
//		$total = pdo_fetchcolumn("SELECT COUNT(id) as count FROM ".tablename('foll_order')." WHERE user_id = :user_id AND uniacid = :uniacid AND pay_status = :pay_status AND application = :application ",$paras);
		$total = pdo_fetch("select count(id) as count from ".tablename('foll_order')." where user_id='".$_W['openid']."'". " and uniacid=".$_W['uniacid']." and application='parking' and (pay_status=0 or pay_status=2)");
		$pageindex = intval($_GPC['page'])?intval($_GPC['page']):'1';//分页
        $pagesize = 5;//每页显示多少条
        $pager = pagination($total['count'], $pageindex, $pagesize);//加入url链接
		$p = ($pageindex-1) * $pagesize;
//      $park_order = pdo_fetchall("SELECT id,PayAmount,body,create_time FROM ".tablename('parking_order')." WHERE uniacid = ".$_W['uniacid']." and openid = '".$_W['openid']."' and pay_status = 0 ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);
        $park_order = pdo_fetchall("SELECT id,pay_account,ordersn,body,create_time FROM ".tablename('foll_order')." WHERE uniacid = ".$_W['uniacid']." and user_id = '".$_W['openid']."' and application = 'parking' and (pay_status = 0 or pay_status = 2) ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);
		$title = '路内停车未支付订单'; //'invoice_iskp'=>'0'  未开票的订单；
		include $this->template('parking/park_Norder');
	}
	
	
	
	
	
	/**
	 * 路内停车未支付订单详情
	 */
	function porder_Ninfo()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['orderid'])) {
			
			$where = [
				':id' =>$_GPC['orderid'],//订单ID
//				':uniacid' => $_W['uniacid'],
//				':user_id'=>$_W['openid'],
//				':pay_status' =>'0',
//				':application'=>'parking',
			];
			
			$user = pdo_fetch("SELECT a.uniacid,a.pay_account,a.ordersn,a.pay_type,a.pay_time,a.body,a.user_id,a.create_time,a.total,b.CarNo,b.number,b.starttime,b.endtime,b.duration FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.id = :id ",$where);
			if ($user['endtime']=='0'){
                $this->message('未离开！','','error');
            }
			$T = $this->timediffs($user['starttime'],$user['endtime']);
			
			$porder_info = array(
				'body' => $user['body'],//商品描述
				'paytime' => date('Y-m-d H:i:s',time()),//消费时间
				'touser' => $user['user_id'],//接收消息的用户
				'uniacid' =>$user['uniacid'],//公众号ID	
				'parkTime' => $T['day'].'天'.$T['hour'].'小时'.$T['min'].'分',//停车时长
				'realTime' => sprintf('%0.1f',$user['duration']/60),//实计时长
				'payableMoney' => $user['total'],//应付金额
				'deducMoney' =>($user['total']-$user['pay_account']),//抵扣金额
				'payMoney' => $user['pay_account'],//交易金额  实付金额
				
				'CarNo'=> $user['CarNo'],//车牌号
				'number'=> $user['number'],//车位编号
				'pay_type'=> $user['pay_type'],//支付方式：wecaht,alipay,park
				'paytime'=> $user['pay_time'],//支付时间
			);
			$paytitle = '订单未支付';
			$urls = mobileUrl('parking/pay');
		}
		
		
		include $this->template('parking/porder_Ninfo');
	}
	
	
	
	
	/*
	 * *****************************************************
	 */
	


	/**
	 * 停车订单  已支付订单
	 */
	function park_order()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;

		$paras = [
			':user_id' => $_W['openid'],
			':uniacid'=> $_W['uniacid'],
			':pay_status'=>'1',
			':application'=>'parking',//停车应用
		];
		//查询总条数
//		$count = pdo_fetch('SELECT COUNT(*) as count  FROM ' . tablename('parking_order') . ' WHERE uniacid = :uniacid and openid = :openid and pay_status = :pay_status', $paras);

		$count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('foll_order')." WHERE user_id = :user_id AND uniacid = :uniacid AND pay_status = :pay_status AND application =:application ",$paras);	
		
		$pageindex = intval($_GPC['page'])?intval($_GPC['page']):'1';//分页
        $pagesize = 5;//每页显示多少条
        $pager = pagination($count, $pageindex, $pagesize);//加入url链接
		$p = ($pageindex-1) * $pagesize;//分页；
//      $park_order = pdo_fetchall("SELECT id,PayAmount,body,paytime FROM ".tablename('parking_order')." WHERE uniacid = ".$_W['uniacid']." and openid = '".$_W['openid']."' and pay_status = 1 ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);
		$park_order = pdo_fetchall("SELECT id,ordersn,pay_account,body,create_time,pay_time FROM ".tablename('foll_order')." WHERE uniacid = ".$_W['uniacid']." and user_id = '".$_W['openid']."' and pay_status = 1 and application = 'parking' ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);
		
		if(empty($park_order)){
			$true = 1;
		}
		$title = '路内停车订单'; //'invoice_iskp'=>'0'  未开票的订单；
		$urls = mobileUrl('parking/invoicelist/porder_info');
		include $this->template('parking/park_order');
	}
	

	/**
	 * 路内停车订单详情
	 */
	function porder_info()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$title = '订单详情';
		
		if(!empty($_GPC['orderid'])) {
			
			$where = [
				':id' =>$_GPC['orderid'],//订单ID
//				'uniacid' => $_W['uniacid'],
				':user_id'=>$_W['openid'],
//				'pay_status' =>'1',
//				'application'=>'parking',//停车应用
			];
			$user = pdo_fetch("SELECT a.uniacid,a.pay_account,a.ordersn,a.pay_type,a.pay_time,a.body,a.user_id,a.create_time,a.total,b.CarNo,b.number,b.starttime,b.endtime,b.duration FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.id = :id AND a.user_id = :user_id ",$where);

			$T = $this->timediffs($user['starttime'],$user['endtime']);			
			$porder_info = array(
				'body' => $user['body'],//商品描述
				'paytime' => date('Y-m-d H:i:s',time()),//消费时间
				'touser' => $user['user_id'],//接收消息的用户
				'uniacid' =>$user['uniacid'],//公众号ID
						
				'parkTime' => ceil(($user['endtime']-$user['starttime'])/60),//$user['duration'],//停车时长
				'realTime' => $user['duration'],//实计时长

				'payableMoney' => $user['total'],//应付金额
				'deducMoney'   =>($user['total']-$user['pay_account']),//抵扣金额
				'payMoney'     => $user['pay_account'],//交易金额  实付金额
				
				'number'	   => $user['number'],//车位编号
				'CarNo'	   	   => $user['CarNo'],//车牌号
				'pay_type'	   => $user['pay_type'],//支付方式：wecaht,alipay,park
				'paytime'	   => $user['pay_time'],//支付时间
			);
		}

		include $this->template('parking/porder_info');
	}
	
	
	
	/**
	 * 获取时间天，小时，分钟函数；
	 */
	public function timediffs($begin_time,$end_time) 
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
	      $mins = intval($remain/60);
	      //计算秒数
	      $secs = $remain%60;
	      $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
	      return $res;
	}

	/**
	http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.invoicelist.history
	*/
	
	
	/**
	*	带参数开票  订单编号跳转
	**/
	function ParkSn()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$title = '路内停车发票开具';
		$ordersn = trim($_GPC['ordersn']);
		$verified = pdo_get("parking_verified",array("openid"=>$_W['openid']),array('idcard','uname'));
		if(empty($verified)) {
			message('您还没有实名验证！请先实名验证', mobileUrl('parking/verified'), 'error');
		}
		$where = array('uniacid'=> $_W['uniacid'],'user_id' => $_W['openid'],'ordersn'=>$ordersn,'IsWrite'=>'101','pay_account >'=>0,'pay_status' => '1','invoice_iskp'=>'0','upOrderId <>'=>'');
		//$parkNum = pdo_get('foll_order',$where,array('id'));
		$parkNum = pdo_get('foll_order',$where,['id','pay_account','body','pay_time']);
		
		if(empty($parkNum)){
			message('该笔订单不支持开票，或已退款', mobileUrl('parking/parking_orderdetails'), 'error');
		}
		
		$selectHead = mobileUrl('parking/invoicelist/select_head');
		include $this->template('parking/invoice_ParkSn');
	}
	
	
	/**
	**	检测用户是否填写发票抬头与抬头信息
	**/
	function CheckHead() {
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		$openid 	= trim($_W['openid']);
		$wh = [
			'openid'=>$openid,
			'status'=>1,
		];
		// 查询已选择的发票抬头
		$infos = pdo_get("invoice_head",$wh,array('head_name','nsrsbh','Type_head','status'));
		if(empty($infos)){
			// 没有查到数据
			$infos['urls'] = mobileUrl('parking/invoicelist/select_head');
			$status = 'error';
			$msg    = '请选择发票抬头';
		} else {
			$status = 'success';
			$msg    = '成功';
		}
		
		if($infos['Type_head'] == '01') {
			$infos['TypeHead'] = '企业抬头';
		} else {
			$infos['TypeHead'] = '个人抬头';
		}
		// 输出
		echo json_encode(['status'=>$status,'info'=>$infos,'msg'=>$msg]);
		
	}
	
	
	/**
	 * 停车订单开票
	 */
	function park()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$title = '路内停车发票开具'; //'invoice_iskp'=>'0'  未开票的订单；

        // 不进行实名验证
		/*$verified = pdo_get("parking_verified",array("openid"=>$_W['openid']),array('idcard','uname'));
		if(empty($verified)) {
			message('您还没有实名验证！请先实名验证', mobileUrl('parking/verified'), 'error');
		}*/
		
		$userid = $_W['openid'];

		// 测试用
        /*if($userid == 'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U') {
            $userid = 'oR-IB0oWm1aO9SV-XoleUxM7Ub2o';
        }*/
		
		$where = array('uniacid'=> $_W['uniacid'],'user_id' => $userid,'IsWrite'=>'101','pay_account >'=>0,'pay_status' => '1','invoice_iskp'=>'0','upOrderId <>'=>'');
		//$parkNum = pdo_get('foll_order',$where,array('id'));
		$parkNum = pdo_getall('foll_order',$where,['id']);
		
		$num = 4;
		$count = count($parkNum);
		$w     = ceil($count/$num);
		$page = isset($_GPC['page']) ? trim($_GPC['page']) : 1;
		
		$off  = ($page-1) * $num;
		
		//上一页
		$p = ($page == 1) ? 0 : ($page -1);
		//下一页
		$n = ($page == $w) ? 0 : ($page + 1);
		
		// 保存数据
		
		//$park_order = pdo_fetchall("SELECT id,pay_account,body,pay_time FROM ".tablename('foll_order')." WHERE uniacid = ".$_W['uniacid']." and user_id = '".$userid."' and application = 'parking' and IsWrite=101  and pay_account>0  and pay_status = 1 and invoice_iskp=0 and upOrderId<>' ' ORDER BY `id` DESC LIMIT " . $off . "," . $num);
		$park_order = pdo_fetchall("SELECT id,pay_account,body,pay_time FROM ".tablename('foll_order')." WHERE uniacid = ".$_W['uniacid']." and user_id = '".$userid."' and IsWrite=101 and pay_account>0  and pay_status = 1 and invoice_iskp=0 and upOrderId<>' ' ORDER BY `id` DESC LIMIT " . $off . "," . $num);
		$pageUrl    = mobileUrl('parking/invoicelist/park').'&page';

		$selectHead = mobileUrl('parking/invoicelist/select_head');
		// 文件渲染
		include $this->template('parking/invoice_park');
	}


	// 2019-06-04  一键开票
	function oneKps() {

        load()->func('communication');
        load()->func('diysend');
        global $_W;
        global $_GPC;

        // 当前用户openid
        $userid = $_W['openid'];
        // 组装sql
        $sql = "SELECT id,pay_account,body,pay_time FROM ".tablename('foll_order')." WHERE uniacid = ".$_W['uniacid']." and user_id = '".$userid."' and IsWrite=101 and pay_account>0  and pay_status = 1 and invoice_iskp=0 and upOrderId<>' ' ORDER BY `id` DESC";
        // 查询全部数据
        $park_order = pdo_fetchall($sql);
        if(empty($park_order)) {
            echo json_encode(['code'=>101,'msg'=>'您没有未开票的订单！']);
            exit;
        }

        $ids = '';
        $paymoney = 0;

        foreach($park_order as $v) {
            $ids .= $v['id'].',';
            $paymoney += $v['pay_account'];
        }

        // 获取的id
        $CheckId = trim($ids,',');
        // 时间订单金额
        $money = sprintf("%.2f",$paymoney);

        // 第三步  请求开票
        if($money <= 0) {
            echo json_encode(['code'=>102,'msg'=>'开票金额小于0元，不能开票']);
            exit;
        }

        // 已选择的抬头
        $head = pdo_get('invoice_head',['openid'=>$userid,'status'=>1]);
        if(!$head){
            //message('您没有设置发票抬头，请设置！', , 'error');
            $urls = mobileUrl('parking/invoicelist/select_head');
            echo json_encode(['code'=>103,'pdfurl'=>$urls,'msg'=>'请选择发票抬头']);
            exit;
        }

        // 组装数据
        $data['token'] 		= 'SendinVeli';
        $data['uniacid'] 	= $head['uniacid'];
        $data['openid']  	= $head['openid'];

        $data['FPQQLSH'] 	= 'OGGP'.date("YmdHis").mt_rand(01,99);//OGGP2018101617045836
        $data['DDH'] 		= 'GGP'.date("YmdHis").mt_rand(011,999);//GGP2018101617045827
        $data['invoice_type'] = 'park';
        $data['XMMC'] 		= '路内智能停车服务';
        $data['DW'] 		= '台';
        $data['XMSL'] 		= '1.00';
        $data['XMDJ'] 		= sprintf("%.2f",$money);
        $data['GGXH'] 		= '';
        $data['GHF_NSRSBH'] = $head['nsrsbh'];
        $data['GHF_DZ'] 	= $head['address'];
        $data['GHF_SJ'] 	= $head['phone_number'];
        $data['GHF_YHZH'] 	= '';
        $data['GHF_MC'] 	= $head['Type_head'] == '03'? $head['head_name'] : $head['head_name'];
        $data['GHFQYLX'] 	= $head['Type_head'];
        $data['GHF_EMAIL']  = $head['email'];
        $data['oids']		= $CheckId;

        // 切断开票
        if($_W['openid'] == 'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U') {
            echo json_encode(['code'=>104,'msg'=>'暂不支持开票']);
            exit;
        }

        $curlpost = new Curl;//实例化
        $url = 'http://shop.gogo198.cn/payment/invoice/invoice.php';
        $result = $curlpost->post($url,$data);
        $json = json_decode($result->response,TRUE);

        if($json['statu'] == 'success') {
            // 更新开票状态
            pdo_query("UPDATE ".tablename('foll_order')." SET invoice_iskp=1 WHERE id IN(".$CheckId.')');

            $pdfurl = mobileUrl('parking/invoicelist');
            // 开票成功
            echo json_encode(['code'=>100,'pdfurl'=>$pdfurl,'msg'=>'开票成功']);
            exit;

        } else {
            // 开票失败
            echo json_encode(['code'=>105,'msg'=>'开票失败,请稍后重试！']);
            exit;
        }

	    // 点击一键开票，获取该用户的所有已支付，金额大于0 并且未开票的订单；
        //'IsWrite'=>'101','pay_account >'=>0,'pay_status' => '1','invoice_iskp'=>'0','upOrderId <>'=>''
        // pod_fetchall 获取全部订单，然后循环拼接ID，计算金额
        // 调用开票功能请求

    }


    // 获取发票头信息，订单金额
    function  getOnehead() {

        load()->func('communication');
        load()->func('diysend');
        global $_W;
        global $_GPC;

        // 当前用户openid
        $userid = $_W['openid'];

        // 检测是否有填写发票抬头
        $heads = pdo_get('invoice_head',['openid'=>$userid]);
        if(empty($heads)) {
            $urls = mobileUrl('parking/invoicelist/headers');
            echo json_encode(['code'=>101,'pdfurl'=>$urls,'msg'=>'前往填写发票抬头']);
            exit;
        }


        // 已选择的抬头
        $head = pdo_get('invoice_head',['openid'=>$userid,'status'=>1]);
        if(!$head){
            //message('您没有设置发票抬头，请设置！', , 'error');
            $urls = mobileUrl('parking/invoicelist/select_head');
            echo json_encode(['code'=>102,'pdfurl'=>$urls,'msg'=>'前往选择发票抬头']);
            exit;
        }


        // 组装sql
        $sql = "SELECT id,pay_account,body,pay_time FROM ".tablename('foll_order')." WHERE uniacid = ".$_W['uniacid']." and user_id = '".$userid."' and IsWrite=101 and pay_account>0  and pay_status = 1 and invoice_iskp=0 and upOrderId<>' ' ORDER BY `id` DESC";
        // 查询全部数据
        $park_order = pdo_fetchall($sql);
        if(empty($park_order)) {
            echo json_encode(['code'=>103,'msg'=>'您没有未开票的订单！']);
            exit;
        }

        $ids = '';
        $paymoney = 0;

        foreach($park_order as $v) {
            $ids .= $v['id'].',';
            $paymoney += $v['pay_account'];
        }

        $counts = count($park_order);
        // 时间订单金额
        $money = sprintf("%.2f",$paymoney);

        // 第三步  请求开票
        if($money <= 0) {
            echo json_encode(['code'=>104,'msg'=>'开票金额小于0元，不能开票']);
            exit;
        }

        // 发票抬头，名称，姓名
        $data = [
            'copy'  =>$head['head_name'],
            'nsrsbh'=>$head['nsrsbh'],
            'moneys'=>$money,
            'counts'=>$counts,
        ];
        // 返回数据
        echo json_encode(['code'=>100,'msg'=>$data]);
        exit;

    }



	//  停车订单开票
	function park_post() {
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if($_W['isajax']) {
			
			$money 	 = $_GPC['payMoney'];
			$CheckId = $_GPC['parkCheck'];
			
			/*$str = "UPDATE ".tablename('foll_order')." SET invoice_iskp=1 WHERE id IN(".$CheckId.')';
			echo json_encode(['statu'=>'error','msg'=>'暂不支持开票','ids'=>$CheckId,'str'=>$str]);
			exit;*/
				
			if($money <= 0) {
				echo json_encode(['statu'=>'error','msg'=>'开票金额小于0元，不能开票']);
				exit;
			}
			
			if($_W['openid'] == 'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U') {
				echo json_encode(['statu'=>'error','msg'=>'暂不支持开票'.$CheckId]);
				exit;
			}
			
			$head = pdo_get('invoice_head',['openid'=>$_W['openid'],'status'=>1]);
			if(!$head){
				//message('您没有设置发票抬头，请设置！', , 'error');
				$urls = mobileUrl('parking/invoicelist/select_head');
				echo json_encode(['statu'=>'headNodata','pdfurl'=>$urls,'msg'=>'请选择发票抬头']);
				exit;
			}
			
			$data['token'] 		= 'SendinVeli';
			$data['uniacid'] 	= $head['uniacid'];
			$data['openid']  	= $head['openid'];
			
			$data['FPQQLSH'] 	= 'OGGP'.date("YmdHis").mt_rand(01,99);//OGGP2018101617045836
			$data['DDH'] 		= 'GGP'.date("YmdHis").mt_rand(011,999);//GGP2018101617045827
			$data['invoice_type'] = 'park';
			$data['XMMC'] 		= '路内智能停车服务';
			$data['DW'] 		= '台';
			$data['XMSL'] 		= '1.00';
			$data['XMDJ'] 		= sprintf("%.2f",$money);
			$data['GGXH'] 		= '';
			$data['GHF_NSRSBH'] = $head['nsrsbh'];
			$data['GHF_DZ'] 	= $head['address'];
			$data['GHF_SJ'] 	= $head['phone_number'];
			$data['GHF_YHZH'] 	= '';
			$data['GHF_MC'] 	= $head['Type_head'] == '03'? $head['head_name'] : $head['head_name'];
			$data['GHFQYLX'] 	= $head['Type_head'];
			$data['GHF_EMAIL']  = $head['email'];
			$data['oids']		= $CheckId;
			
			$curlpost = new Curl;//实例化
			$url = 'http://shop.gogo198.cn/payment/invoice/invoice.php';
			$result = $curlpost->post($url,$data);
			$json = json_decode($result->response,TRUE);
			
			if($json['statu'] == 'success') {
				
				pdo_query("UPDATE ".tablename('foll_order')." SET invoice_iskp=1 WHERE id IN(".$CheckId.')');
				
				$pdfurl = mobileUrl('parking/invoicelist');
				echo json_encode(['statu'=>'success','pdfurl'=>$pdfurl,'msg'=>'开票成功']);
				exit;
				
			} else {
				echo json_encode(['statu'=>'error','msg'=>'开票失败,请稍后重试！']);
				exit;
			}
		}
	}
	
	
	// 发票抬头
	function headers(){
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$openid   = $_W['openid'];
		// 不进行实名验证  2019-06-03
		/*$verified = pdo_get('parking_verified',['openid'=>$openid],['uname','idcard']);
		if(empty($verified)){
			message('您还没有实名验证！请先实名验证', mobileUrl('parking/verified'), 'error');
		}*/

        /**
         * 客户:oR-IB0gdjGloem0ywtLzDIbEjwCA
         *
         * 我的：oR-IB0t4Yc9zmV-K-_5NRB-u5k4U
         */

        /*if($_W['openid'] == 'oR-IB0t4Yc9zmV-K-_5NRB-u5k4U') {
            $openid = 'oR-IB0gdjGloem0ywtLzDIbEjwCA';
        }*/

        // 更新数据
        $verified = pdo_get('parking_verified',['openid'=>$openid],['uname','idcard']);
		
		if($_W['ispost']) {// 修改
			
			$head = pdo_get('invoice_head',['openid'=>$openid,'status'=>1]);
			if($head) {// 有状态为1的抬头
				
				// 03 个人  只能添加一个
				if($_GPC['head'] == '03') {
					
					$head = pdo_get('invoice_head',['openid'=>$openid,'Type_head'=>$_GPC['head']],['id']);
					if(empty($head)) {// 没有就可以添加，有了就不能添加
						
						$data = [
							'uniacid'=>$_W['uniacid'],
							'openid' =>$openid,
							'Type_head' =>$_GPC['head'],
							'head_name' =>$_GPC['head_name2'],
							'nsrsbh' 	=>$_GPC['nsrsbh1'],
							'email' 	=>$_GPC['email2'],
							'phone_number' 	=>$_GPC['phone2'],
							'status'		=>0,
						];
						$ins = pdo_insert('invoice_head',$data);
						//echo json_encode(['msg'=>'状态1,个人','ins'=>$ins,'dat'=>$data]);die;
						message('添加成功！', mobileUrl('parking/invoicelist/headers'), 'success');
					} else {
						message('只能添加一个个人发票抬头！', mobileUrl('parking/invoicelist/headers'), 'error');
					}
					
				} else {// 企业
					
					if($_GPC['nsrsbh'] == '91440606MA4WX4KK55'){
						message('不能添加91440606MA4WX4KK55统一信用号', mobileUrl('parking/invoicelist/headers'), 'error');
					}
					
					$data = [
						'uniacid'=>$_W['uniacid'],
						'openid' =>$openid,
						
						'Type_head' =>$_GPC['head'],
						'head_name' =>$_GPC['head_name'],
						'nsrsbh' 	=>$_GPC['nsrsbh'],
						'email' 	=>$_GPC['email'],
						'phone_number' 	=>$_GPC['phone'],
						'op_bank'		=>$_GPC['op_bank'],
						'bank_acc'		=>$_GPC['bank_number'],
						'address'		=>$_GPC['address'],
						'status'		=>0,
					];
					$ins = pdo_insert('invoice_head',$data);
					//echo json_encode(['msg'=>'状态1，企业','ins'=>$ins,'dat'=>$data]);die;
					message('添加成功！', mobileUrl('parking/invoicelist/headers'), 'success');
				}
				
			} else { // 状态为0
			
				// 03 个人  只能添加一个
				if($_GPC['head'] == '03') {
					
					$head = pdo_get('invoice_head',['openid'=>$openid,'Type_head'=>$_GPC['head']],['id']);
					if(empty($head)) {// 没有就可以添加，有了就不能添加
						
						$data = [
							'uniacid'=>$_W['uniacid'],
							'openid' =>$openid,
							'Type_head' =>$_GPC['head'],
							'head_name' =>$_GPC['head_name2'],
							'nsrsbh' 	=>$_GPC['nsrsbh1'],
							'email' 	=>$_GPC['email2'],
							'phone_number' 	=>$_GPC['phone2'],
							'status'		=>1,
						];
						$ins = pdo_insert('invoice_head',$data);
						//echo json_encode(['msg'=>'状态0，个人','ins'=>$ins,'dat'=>$data]);die;
						message('添加成功！', mobileUrl('parking/invoicelist/headers'), 'success');
						
					} else {
						message('只能添加一个个人发票抬头！', mobileUrl('parking/invoicelist/headers'), 'error');
					}
					
				} else {// 企业
				
					if($_GPC['nsrsbh'] == '91440606MA4WX4KK55'){
						message('不能添加91440606MA4WX4KK55统一信用号', mobileUrl('parking/invoicelist/headers'), 'error');
					}
					
					$head = pdo_get('invoice_head',['openid'=>$openid,'status'=>1],['id']);
					if($head){// 有 status 为1的数据
						$data = [
							'uniacid'=>$_W['uniacid'],
							'openid' =>$openid,
							
							'Type_head' =>$_GPC['head'],
							'head_name' =>$_GPC['head_name'],
							'nsrsbh' 	=>$_GPC['nsrsbh'],
							'email' 	=>$_GPC['email'],
							'phone_number' 	=>$_GPC['phone'],
							'op_bank'		=>$_GPC['op_bank'],
							'bank_acc'		=>$_GPC['bank_number'],
							'address'		=>$_GPC['address'],
							'status'		=>0,
						];
					} else { // 没有 1
						$data = [
							'uniacid'=>$_W['uniacid'],
							'openid' =>$openid,
							
							'Type_head' =>$_GPC['head'],
							'head_name' =>$_GPC['head_name'],
							'nsrsbh' 	=>$_GPC['nsrsbh'],
							'email' 	=>$_GPC['email'],
							'phone_number' 	=>$_GPC['phone'],
							'op_bank'		=>$_GPC['op_bank'],
							'bank_acc'		=>$_GPC['bank_number'],
							'address'		=>$_GPC['address'],
							'status'		=>1,
						];
					}
					$ins = pdo_insert('invoice_head',$data);
					//echo json_encode(['msg'=>'状态0，企业','ins'=>$ins,'dat'=>$data]);die;
					message('添加成功！', mobileUrl('parking/invoicelist/headers'), 'success');
				}
			}
		}

		$urls 	  = mobileUrl('parking/invoicelist/headers');

		include $this->template('parking/invoice_heads');
	}
	
	// 选择发票抬头
	function select_head(){
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$title = '发票抬头选择';
		$head = pdo_getall('invoice_head',['openid'=>$_W['openid']]);
		
		$changeUrl = mobileUrl('parking/invoicelist/update_head');
		$updateUrl = mobileUrl('parking/invoicelist/edit_head');
		$goUrl	   = mobileUrl('parking/invoicelist/park');
		include $this->template('parking/select_head');
	}
	
	// 更新，状态，删除发票抬头
	function update_head(){
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if($_W['isajax']){
			$hid = $_GPC['hid'];// 新ID
			$oid = $_GPC['oid'];// 旧Id
			$openid = $_W['openid'];
			
			if($_GPC['method'] == 'up'){
				
				pdo_update('invoice_head',['status'=>0],['id'=>$oid]);
				
				pdo_update('invoice_head',['status'=>1],['id'=>$hid]);
				
				echo json_encode(['msg'=>'success']);die;
				
			} else if($_GPC['method'] == 'del') {
				pdo_delete('invoice_head',['id'=>$hid]);
				echo json_encode(['msg'=>'success']);die;
			}
		}
		
	}
	
	// 编辑发票抬头
	function edit_head() {
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['hid']) && (!empty($_GPC['hd']))) {
			$hid = $_GPC['hid'];
			$hd  = $_GPC['hd'];
			$openid = $_W['openid'];
			$head	= pdo_get('invoice_head',['id'=>$hid,'Type_head'=>$hd,'openid'=>$openid]);
			$urls   = mobileUrl('parking/invoicelist/edits');
			if($hd == '01'){
				include $this->template('parking/invoice_heads_company');
			} else if($hd == '03'){
				include $this->template('parking/invoice_heads_personal');
			}
		}
	}
	
	// 修改发票抬头
	function edits(){
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if($_W['ispost'] && !empty($_GPC['hid'])){
			
			$hid = $_GPC['hid'];
			$hd  = $_GPC['hd'];
			$openid = $_W['openid'];
			
			if($hd == '01'){
				$data = [
					'Type_head' =>$hd,
					'head_name' =>$_GPC['head_name'],
					'nsrsbh' 	=>$_GPC['nsrsbh'],
					'email' 	=>$_GPC['email'],
					'phone_number' 	=>$_GPC['phone'],
					'op_bank'		=>$_GPC['op_bank'],
					'bank_acc'		=>$_GPC['bank_number'],
					'address'		=>$_GPC['address'],
				];
			} else if($hd == '03'){
				$data = [
					'Type_head' =>$hd,
					'head_name' =>$_GPC['head_name'],
					'nsrsbh' 	=>$_GPC['nsrsbh'],
					'email' 	=>$_GPC['email'],
					'phone_number' 	=>$_GPC['phone'],
				];
			}
			$ins = pdo_update('invoice_head',$data,['id'=>$hid,'openid'=>$openid]);
			if(!empty($ins)){
				message('更新成功',mobileUrl('parking/invoicelist/select_head'),'success');
			} else {
				message('更新失败',mobileUrl('parking/invoicelist/select_head'),'error');
			}
			
		}
	}
	
	// 历史开票记录
	function history(){
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$title = '历史发票列表';
		
		$paras = [
			'openid' => $_W['openid'],
			'uniacid'=> $_W['uniacid'],
			'invoice_type'=>'park',
		];
		
		//查询总条数
		$count = pdo_fetch('SELECT COUNT(*) as count  FROM ' . tablename('invoices_ord') . ' WHERE uniacid = :uniacid and openid = :openid and invoice_type = :invoice_type and state = 1', $paras);
		$total = $count['count'];
		$pageindex 	= intval($_GPC['page'])?intval($_GPC['page']):'1';
        $pagesize 	= 5;
        $pager 		= pagination($total, $pageindex, $pagesize);
		$p = ($pageindex-1) * $pagesize;
        $history_order = pdo_fetchall("SELECT id,create_date,XMMC,XMJE,PDF_URL FROM " . tablename('invoices_ord') . " WHERE invoice_type='park' and uniacid = ". $_W['uniacid']. " and openid = '".$_W['openid']."' and state = 1 ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);  
		if(empty($history_order)) {
			message('您没有开票记录，请先开票',mobileUrl('parking/invoicelist/park'),'error');
		}
		include $this->template('parking/invoice_history');
	}
	
	// 发票详情
	function history_info(){
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$title = '发票详情';
		
		if($_GPC['orderid']) {
			
			$id 	= $_GPC['orderid'];
			$openid = $_W['openid'];
			$history_info = pdo_get('invoices_ord',['id'=>$id,'openid'=>$openid]);
			if(empty($history_info)) {
				message('没有该订单数据，请检查!',mobileUrl('parking/invoicelist/history'),'error');
			}
		}
		
		include $this->template('parking/history_info');
	}
	


/******************改版****************/	
	
		/**
	 * 停车订单  已支付订单-改版
	 */
	function testpark_order()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$paras = [
			':user_id' => $_W['openid'],
			':uniacid'=> $_W['uniacid'],
			':pay_status'=>'1',
			':application'=>'parking',//停车应用
		];
		//查询总条数
//		$count = pdo_fetch('SELECT COUNT(*) as count  FROM ' . tablename('parking_order') . ' WHERE uniacid = :uniacid and openid = :openid and pay_status = :pay_status', $paras);

		$count = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename('foll_order')." WHERE user_id = :user_id AND uniacid = :uniacid AND pay_status = :pay_status AND application =:application ",$paras);	
		
		$pageindex = intval($_GPC['page'])?intval($_GPC['page']):'1';//分页
        $pagesize = 5;//每页显示多少条
        $pager = pagination($count, $pageindex, $pagesize);//加入url链接
		$p = ($pageindex-1) * $pagesize;//分页；
//      $park_order = pdo_fetchall("SELECT id,PayAmount,body,paytime FROM ".tablename('parking_order')." WHERE uniacid = ".$_W['uniacid']." and openid = '".$_W['openid']."' and pay_status = 1 ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);
		$park_order = pdo_fetchall("SELECT id,pay_account,body,create_time,pay_time FROM ".tablename('foll_order')." WHERE uniacid = ".$_W['uniacid']." and user_id = '".$_W['openid']."' and pay_status = 1 and application = 'parking' ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);  
		
		if(empty($park_order)){
			$true = 1;
		}
		$title = '路内停车订单'; //'invoice_iskp'=>'0'  未开票的订单；
		$urls = mobileUrl('parking/invoicelist/porder_info_gai');
		include $this->template('parking/park_order_gai');
	}
	
	
	
	/**
	 * 路内停车订单详情-改版
	 */
	function testporder_info()
	{
		
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		$title = '订单详情';
		
		if(!empty($_GPC['orderid'])) {
			
			$where = [
				':id' =>$_GPC['orderid'],//订单ID
//				'uniacid' => $_W['uniacid'],
				':user_id'=>$_W['openid'],
//				'pay_status' =>'1',
//				'application'=>'parking',//停车应用
			];
			$user = pdo_fetch("SELECT a.uniacid,a.pay_account,a.ordersn,a.pay_type,a.pay_time,a.body,a.user_id,a.create_time,a.total,b.number,b.starttime,b.endtime,b.duration FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.id = :id AND a.user_id = :user_id ",$where);

			$T = $this->timediffs($user['starttime'],$user['endtime']);			
			$porder_info = array(
				'body' => $user['body'],//商品描述
				'paytime' => date('Y-m-d H:i:s',time()),//消费时间
				'touser' => $user['user_id'],//接收消息的用户
				'uniacid' =>$user['uniacid'],//公众号ID
						
				'parkTime' => ceil(($user['endtime']-$user['starttime'])/60),//$user['duration'],//停车时长
				'realTime' => $user['duration'],//实计时长

				'payableMoney' => $user['total'],//应付金额
				'deducMoney'   =>($user['total']-$user['pay_account']),//抵扣金额
				'payMoney'     => $user['pay_account'],//交易金额  实付金额
				
				'number'	   => $user['number'],//车位编号
				'pay_type'	   => $user['pay_type'],//支付方式：wecaht,alipay,park
				'paytime'	   => $user['pay_time'],//支付时间
			);
		}

		include $this->template('parking/porder_info_gai');
	}
	
	
		/**
	 * 未支付停车订单 2018-1-23-改版
	 */
	function testpark_Norder()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;

//		$paras = [
//			':user_id' => $_W['openid'],
//			':uniacid'=> $_W['uniacid'],
//			':pay_status'=>0,
//			':application'=>'parking',//应用类型： 停车：parking，商城：shop，自助：food，预约：reservation，分时：fenshi
//		];
		//查询总条数
//		$count = pdo_fetch('SELECT COUNT(*) as count  FROM ' . tablename('parking_order') . ' WHERE uniacid = :uniacid and openid = :openid and pay_status = :pay_status', $paras);
//		$count = pdo_fetch('SELECT COUNT(*) as count  FROM ' . tablename('foll_order') . ' WHERE uniacid = :uniacid and user_id = :openid and pay_status = :pay_status and application = :application', $paras);
		
//		$count = pdo_fetch('SELECT COUNT(id) AS count FROM '.tablename('foll_order'),$paras);
		//记录总条数
//		$total = pdo_fetchcolumn("SELECT COUNT(id) as count FROM ".tablename('foll_order')." WHERE user_id = :user_id AND uniacid = :uniacid AND pay_status = :pay_status AND application = :application ",$paras);
		$total = pdo_fetch("select count(id) as count from ".tablename('foll_order')." where user_id='".$_W['openid']."'". " and uniacid=".$_W['uniacid']." and application='parking' and (pay_status=0 or pay_status=2)");
		$pageindex = intval($_GPC['page'])?intval($_GPC['page']):'1';//分页
        $pagesize = 5;//每页显示多少条
        $pager = pagination($total['count'], $pageindex, $pagesize);//加入url链接
		$p = ($pageindex-1) * $pagesize;
//      $park_order = pdo_fetchall("SELECT id,PayAmount,body,create_time FROM ".tablename('parking_order')." WHERE uniacid = ".$_W['uniacid']." and openid = '".$_W['openid']."' and pay_status = 0 ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);
        $park_order = pdo_fetchall("SELECT id,pay_account,body,create_time FROM ".tablename('foll_order')." WHERE uniacid = ".$_W['uniacid']." and user_id = '".$_W['openid']."' and application = 'parking' and (pay_status = 0 or pay_status = 2) ORDER BY `id` DESC LIMIT " . $p . "," . $pagesize);
		$title = '路内停车未支付订单'; //'invoice_iskp'=>'0'  未开票的订单；
		include $this->template('parking/park_Norder_gai');
	}
	
	
	
	
	
		/**
	 * 路内停车未支付订单详情-改版
	 */
	function testporder_Ninfo()
	{
		load()->func('communication');
		load()->func('diysend');
		global $_W;
		global $_GPC;
		
		if(!empty($_GPC['orderid'])) {
			
			$where = [
				':id' =>$_GPC['orderid'],//订单ID
//				':uniacid' => $_W['uniacid'],
//				':user_id'=>$_W['openid'],
//				':pay_status' =>'0',
//				':application'=>'parking',
			];
			
			$user = pdo_fetch("SELECT a.uniacid,a.pay_account,a.ordersn,a.pay_type,a.pay_time,a.body,a.user_id,a.create_time,a.total,b.number,b.starttime,b.endtime,b.duration FROM ".tablename('foll_order')." a LEFT JOIN ".tablename('parking_order')." b ON a.ordersn = b.ordersn WHERE a.id = :id ",$where);
			if ($user['endtime']=='0'){
                $this->message('未离开！','','error');
            }
			$T = $this->timediffs($user['starttime'],$user['endtime']);
			
			$porder_info = array(
				'body' => $user['body'],//商品描述
				'paytime' => date('Y-m-d H:i:s',time()),//消费时间
				'touser' => $user['user_id'],//接收消息的用户
				'uniacid' =>$user['uniacid'],//公众号ID	
				'parkTime' => $T['day'].'天'.$T['hour'].'小时'.$T['min'].'分',//停车时长
				'realTime' => sprintf('%0.1f',$user['duration']/60),//实计时长
				'payableMoney' => $user['total'],//应付金额
				'deducMoney' =>($user['total']-$user['pay_account']),//抵扣金额
				'payMoney' => $user['pay_account'],//交易金额  实付金额
				
				'number'=> $user['number'],//车位编号
				'pay_type'=> $user['pay_type'],//支付方式：wecaht,alipay,park
				'paytime'=> $user['pay_time'],//支付时间
			);
			$paytitle = '订单未支付';
			$urls = mobileUrl('parking/pay');
		}
		
		
		include $this->template('parking/porder_Ninfo_gai');
	}
	
	
}