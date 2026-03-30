<?php
if (!defined('IN_IA'))
{
    exit('Access Denied');
}

class Queryuserinfo_EweiShopV2Page extends mobilePage
{
	public function login()
	{
		global $_W;
        global $_GPC;
        $title = '用户登录';
        $url = mobileUrl('parking/queryuserinfo/main');
        if(!empty($_SESSION['checkUser'])) {
        	$this->message('您已登录，请勿重复登录！',$url,'error');
        }
        
        include $this->template('parking/quserinfo/qlogin');
	}
	
	//检测用户是否登录
	public function checkLogin(){
		global $_W;
        global $_GPC;
		$url = mobileUrl('parking/queryuserinfo/login');
        if(empty($_SESSION['checkUser'])){
        	$this->message('您尚未登录，请登录操作！',$url,'error');
        }
	}
	
	public function main()
    {
        global $_W;
        global $_GPC;
        //停车订单查询url
        $parkurl = mobileUrl('parking/queryuserinfo/parkinfo');
        //支付流水查询url
        $getPayoldurl = mobileUrl('parking/queryuserinfo/getPayold');
       	//$this->parkinfo();
       	$this->selectMenu();
    }
    
    
    //订单查询菜单  2018-08-27
    public function selectMenu(){
    	global $_W;
    	global $_GPC;
    	$title  = '菜单';
    	$this->checkLogin();
    	include $this->template('parking/quserinfo/selectmenu');
    }



    public function orderManagerView(){
        global $_W;
        global $_GPC;
        $title  = '订单管理';
        $this->checkLogin();
        include $this->template('parking/quserinfo/order_manager_menu');
    }

    public function dataView(){
        global $_W;
        global $_GPC;
        $title  = '数据可视菜单';
        $this->checkLogin();
        include $this->template('parking/quserinfo/data_view');
    }
    
    //按订单号查询
    public function parkOrders(){
    	global $_W;
    	global $_GPC;
    	$title  = '按订单号查询';
    	//检测是否登录
        $this->checkLogin();
        //if(isset($_GPC['ordersn']) && $_W['ispost']) {
        if(!empty($_GPC['ordersn'])) {
        	
        	$ordersn = trim($_GPC['ordersn']);
        	//查询订单信息
        	$field = 'a.devs_ordersn,a.ordersn,a.number,a.starttime,a.endtime,a.duration,a.status,b.total,b.pay_account,b.pay_status,b.create_time,b.pay_time,b.pay_type,b.user_id';
			$find = array(':ordersn' => $ordersn);
			$parks = pdo_fetch("SELECT ".$field." FROM ".tablename('parking_order')." a LEFT JOIN ".tablename('foll_order')." b ON a.ordersn = b.ordersn WHERE a.ordersn = :ordersn  LIMIT 1",$find);
        	if(empty($parks)){
        		show_json(['code'=>0,'msg'=>'数据为空']);
        	}
        	
        	switch($parks['pay_type']){
        		case 'Fwechat':
        			$payType = '微信停车免密';
        		break;
        		case 'FAgro':
        			$payType = '农商银行免密';
        		break;
        		case 'Parks':
        			$payType = '银联无感免密';
        		break;
        		case 'wechat':
        		case 'alipay':
        			$payType = '聚合支付';
        		break;
        		default:
        			$payType = '其他';
        		break;
        	}
        	
        	$parks['pay_type']		= $payType;//支付类型
          	$parks['dedu']  		= sprintf("%.2f",($parks['total']-$parks['pay_account']));//抵扣金额
          	$parks['create_time']	= date('Y-m-d H:i:s',$parks['create_time']);
          	$parks['starttime']		= date('Y-m-d H:i:s',$parks['starttime']);
          	$parks['endtime']		= date('Y-m-d H:i:s',$parks['endtime']);
          	$parks['pay_time']		= $parks['pay_time'] > 0 ? date('Y-m-d H:i:s',$parks['pay_time']) : '没有扣费';
        	
        	//查询会员签约
        	$field1 = 'mobile,name,CertNo,CarNo,auth_status,auth_type';
        	$find1 	= array(':openid' => $parks['user_id']);
			$author = pdo_fetch("SELECT ".$field1." FROM ".tablename('parking_authorize')." WHERE openid = :openid  LIMIT 1",$find1);
        	if(empty($author)){
        		show_json(['code'=>0,'msg'=>'没有注册授权信息']);
        	}
        	//解析免密
        	switch($author['auth_type']){
        		case 'a:1:{s:2:"wg";s:11:"FCreditCard";}':
        			$auth_type = '银联无感免密';
        		break;
        		case 'a:1:{s:2:"sd";s:5:"FAgro";}':
        			$auth_type = '农商银行免密';
        		break;
        		case 'a:1:{s:2:"wx";s:7:"Fwechat";}':
        			$auth_type = '微信停车免密';
        		break;
        	}
        	//授权类型
          	$author['auth_type']  	= $auth_type;
        	
        	/**
        	 * 三表关联，
        	 * 条件车位编码；
        	 * 查询
        	 */
        	$field2 = 'a.park_code,a.pid,a.numbers,b.Road,b.id,c.name';
        	$where2 = [':park_code'=>$parks['number']];
          	//$space = pdo_fetch('SELECT '.$field2.' FROM '.tablename('parking_space').' a LEFT JOIN '.tablename('parking_position').' b LEFT JOIN '.tablename('account_wechats').' c ON a.pid = b.id ON b.uniacid=c.acid WHERE a.park_code=:park_code LIMIT 1 ',$where2);
          	//查询车位信息
          	$space  = pdo_fetch('SELECT pid,park_code,numbers FROM '.tablename('parking_space').' WHERE park_code=:park_code',$where2);
          	//查询车位路段，企业
          	$where3 = [':id'=>$space['pid']];
          	$position = pdo_fetch('SELECT a.Road,b.name FROM '.tablename('parking_position').' a LEFT JOIN '.tablename('account_wechats').' b ON a.uniacid=b.acid WHERE a.id=:id ',$where3);
        	if(empty($position)){
        		show_json(['code'=>0,'msg'=>'无企业信息']);
        	}
        }
    	
    	include $this->template('parking/quserinfo/parkorders');
    }
    
    
    //按订日期查询
    public function parkDates(){
    	global $_W;
    	global $_GPC;
    	$title  = '按日期查询';
    	//检测是否登录
        $this->checkLogin();
    	if($_W['ispost']) {
    		
    		$start = strtotime($_GPC['start']);
    		$end   = (strtotime($_GPC['end'])+86399);
    		
    		$pageSize = 8;//每页显示8条
			$pageNo   = $_GPC['pageNo']? trim($_GPC['pageNo']):1;//初始化偏移量
			//计算偏移量
			$offset = ($pageNo-1) * $pageSize;
			$pagesCount = $_GPC['pagesCount'] ? trim($_GPC['pagesCount']) : '';
    		
    		if(empty($pagesCount)){
    			//统计数据总数
	    		$count = pdo_fetch('SELECT count(id) as count FROM '.tablename('foll_order')." WHERE create_time>={$start} AND create_time<={$end}")['count'];
	    		//计算总页数
				$pagesCount   = ceil($count/$pageSize);
    		}
			
    		$order = pdo_fetchall('SELECT id,ordersn FROM '.tablename('foll_order')." WHERE create_time>={$start} AND create_time<={$end} ORDER BY id desc LIMIT {$offset},{$pageSize}");
    		if(empty($order)){
    			$url = mobileUrl('parking/queryuserinfo/parkDates');
    			$this->message('暂无该时段的数据，请重提交!',$url,'error');
    		}
    	}
    	
    	include $this->template('parking/quserinfo/parkdates');
    }
    
    //按订日期查询
    public function parkDatesd(){
    	global $_W;
    	global $_GPC;
    	$title  = '按日期查询';
    	//检测是否登录
        $this->checkLogin();
    	if($_W['isajax']) {
    		
    		//开始时间
    		$start = $_GPC['start'] ? trim($_GPC['start']) : time();
    		//结束时间
    		$end   = $_GPC['end']   ? trim($_GPC['end']) : time()+86399;
    		//每页显示8条
    		$pageSize = 8;
			$pageNo   = $_GPC['pageNo']? trim($_GPC['pageNo']):1;//初始化偏移量
			//计算偏移量
			$offset = ($pageNo-1) * $pageSize;
			$url = mobileUrl('parking/queryuserinfo/parkOrders');			
    		$order = pdo_fetchall('SELECT id,ordersn FROM '.tablename('foll_order')." WHERE create_time>={$start} AND create_time<={$end} LIMIT {$offset},{$pageSize}");
    		if(!empty($order)) {
    			//返回数据
    			exit(json_encode(['code'=>1,'result'=>$order,'str'=>$url]));
    		}
    		exit(json_encode(['code'=>0,'result'=>'no datas']));
    	}
    	
    }
    
    
    //按泊位号
    public function parksNums(){
    	global $_W;
    	global $_GPC;
    	$title  = '按泊位号查询';
    	//检测是否登录
        $this->checkLogin();
    	if($_W['ispost'] && !empty($_GPC['numbers'])) {
    		
			$numbers   = $_GPC['numbers']? trim($_GPC['numbers']):'';//初始化偏移量
			$pageSize = 8;//每页显示8条
			$pageNo   = $_GPC['pageNo']? trim($_GPC['pageNo']):1;//初始化偏移量
			//计算偏移量
			$offset = ($pageNo-1) * $pageSize;
			$pagesCount = $_GPC['pagesCount'] ? trim($_GPC['pagesCount']) : '';
    		
    		if(empty($pagesCount)){
    			//统计数据总数
	    		$count = pdo_fetch('SELECT count(id) as count FROM '.tablename('parking_order')." WHERE number={$numbers} ")['count'];
	    		//计算总页数
				$pagesCount   = ceil($count/$pageSize);
    		}
			
    		$order = pdo_fetchall('SELECT id,ordersn FROM '.tablename('parking_order')." WHERE number={$numbers} ORDER BY id desc LIMIT {$offset},{$pageSize} ");
    		if(empty($order)){
    			$url = mobileUrl('parking/queryuserinfo/parksNums');
    			$this->message('暂无该时段的数据，请重提交!',$url,'error');
    		}
    	}
    	
    	include $this->template('parking/quserinfo/parksnums');
    }
    
    //按订日期查询   ajax 请求加载数据
    public function parksNumsd(){
    	global $_W;
    	global $_GPC;
    	$title  = '按日期查询';
    	
    	if($_W['isajax']) {
    		
    		$numbers   = $_GPC['numbers']? trim($_GPC['numbers']):'';//初始化偏移量
    		//每页显示8条
    		$pageSize = 8;
			$pageNo   = $_GPC['pageNo']? trim($_GPC['pageNo']):1;//初始化偏移量
			//计算偏移量
			$offset = ($pageNo-1) * $pageSize;
			$url 	= mobileUrl('parking/queryuserinfo/parksNums');			
    		$order  = pdo_fetchall('SELECT id,ordersn FROM '.tablename('parking_order')." WHERE number={$numbers} ORDER BY id desc LIMIT {$offset},{$pageSize} ");
    		if(!empty($order)) {
    			//返回数据
    			exit(json_encode(['code'=>1,'result'=>$order,'str'=>$url]));
    		}
    		exit(json_encode(['code'=>0,'result'=>'no datas']));
    	}
    	
    }
    
    
    //按车主手机
    public function parkUsers(){
    	global $_W;
    	global $_GPC;
    	$title  = '按车主手机';
    	//检测是否登录
        $this->checkLogin();
    	if($_W['ispost'] && !empty($_GPC['phone'])) {
    		
			$phone   = $_GPC['phone']? trim($_GPC['phone']):'';//用户手机号码
			$pageSize = 8;//每页显示8条
			$pageNo   = $_GPC['pageNo']? trim($_GPC['pageNo']):1;//初始化偏移量
			//计算偏移量
			$offset = ($pageNo-1) * $pageSize;
			$pagesCount = $_GPC['pagesCount'] ? trim($_GPC['pagesCount']) : '';
			$openid = pdo_fetch('SELECT openid FROM '.tablename('parking_authorize')." WHERE mobile={$phone} LIMIT 1")['openid'];
			if(empty($openid)){
				$url = mobileUrl('parking/queryuserinfo/parkUsers');
    			$this->message('暂无该车主的信息，请重提交!',$url,'error');
			}
    		
    		if(empty($pagesCount)) {
    			//统计数据总数
	    		$count = pdo_fetch('SELECT count(id) as count FROM '.tablename('foll_order')." WHERE user_id='{$openid}'")['count'];
	    		//计算总页数
				$pagesCount   = ceil($count/$pageSize);
    		}
			
    		$order = pdo_fetchall('SELECT id,ordersn FROM '.tablename('foll_order')." WHERE user_id='{$openid}' ORDER BY id desc LIMIT {$offset},{$pageSize} ");
    		if(empty($order)){
    			$url = mobileUrl('parking/queryuserinfo/parkUsers');
    			$this->message('暂无该车主的信息，请重提交!',$url,'error');
    		}
    	}
    	
    	include $this->template('parking/quserinfo/parkusers');
    }
    
    //按订日期查询   ajax 请求加载数据
    public function parkUsersd(){
    	global $_W;
    	global $_GPC;
    	$title  = '按车主手机';
    	
    	if($_W['isajax']) {
    		
    		$openid   = $_GPC['openid']? trim($_GPC['openid']):'';//初始化偏移量
    		//每页显示8条
    		$pageSize = 8;
			$pageNo   = $_GPC['pageNo']? trim($_GPC['pageNo']):1;//初始化偏移量
			//计算偏移量
			$offset = ($pageNo-1) * $pageSize;
			$url 	= mobileUrl('parking/queryuserinfo/parkUsers');			
    		$order  = pdo_fetchall('SELECT id,ordersn FROM '.tablename('foll_order')." WHERE user_id='{$openid}' ORDER BY id desc LIMIT {$offset},{$pageSize} ");
    		if(!empty($order)) {
    			//返回数据
    			exit(json_encode(['code'=>1,'result'=>$order,'str'=>$url]));
    		}
    		exit(json_encode(['code'=>0,'result'=>'no datas']));
    	}
    	
    }
    
    
    //关联查询
    public function infoslist() {
    	global $_W;
    	global $_GPC;
    	$title = '关联数据列表';
    	//检测是否登录
        $this->checkLogin();
    	if(!empty($_GPC['token'])) {
    		$sn	   = trim($_GPC['sn']);
    		$token = trim($_GPC['token']);
    		switch($token){
    			case 'code':
    				$where=[':number'=>$sn];
    				$parking = pdo_fetchall('SELECT ordersn FROM '.tablename('parking_order').' WHERE number=:number ORDER BY id desc LIMIT 0,10',$where);
    			break;
    			case 'tel':
    				$where=[':user_id'=>$sn];
    				$parking = pdo_fetchall('SELECT ordersn FROM '.tablename('foll_order').' WHERE user_id=:user_id ORDER BY id desc LIMIT 0,10',$where);
    			break;
    			case 'old':
    				$where=[':ordersn'=>$sn];
    				$parking = pdo_fetchall('SELECT ordersn FROM '.tablename('foll_order').' WHERE ordersn=:ordersn ORDER BY id desc LIMIT 0,10',$where);
    			break;
    		}
    		
    		if(empty($parking)){
    			$url = mobileUrl('parking/queryuserinfo/parkOrders');
    			$this->message('查不到对应的数据！',$url,'error');
    		}
    	}
    	include $this->template('parking/quserinfo/infoslist');
    }
    
    
    
    
    /**
     * 停车订单查询
     */
    public function parkinfo()
    {
        global $_W;
        global $_GPC;
        $title = '停车订单查询';
        //检测是否登录
        $this->checkLogin();
        if(isset($_GPC['ordersn']) && $_W['ispost']) {
        	$ordersn = trim($_GPC['ordersn']);
        	$userid = pdo_get("foll_order",array("ordersn"=>$ordersn),array('user_id'));
        	if(empty($userid)) {
        		message('查询不到该订单对应用户数据');
        	}
        	
        	$userinfo = pdo_get("parking_authorize",array("openid"=>$userid['user_id']),array('name','mobile','auth_status','auth_type','credit_accout','CarNo'));
        	if(!$userinfo) {
        		message('查询不到该用户数据或未注册！');
        	}
        	
    		$type = '';
    		switch($userinfo['auth_type']) {
    			case 'a:1:{s:2:"wg";s:11:"FCreditCard";}':
    				$type = '信用卡免密';
    			break;
    			case 'a:1:{s:2:"sd";s:5:"FAgro";}':
    				$type = '农商行免密';
    			break;
    			case 'a:1:{s:2:"wx";s:7:"Fwechat";}':
    				$type = '微信免密';
    			break;
    			default:
    				$type = '暂无授权';
    			break;
    		}
    		
    		$userinfo['name'] 		 = $userinfo['name']!='' ? $userinfo['name']:'无';
    		$userinfo['mobile'] 	 = $userinfo['mobile']!='' ? $userinfo['mobile']:"无";
    		$userinfo['credit_accout'] = $userinfo['credit_accout']!='' ? $userinfo['credit_accout']:'无';
    		$userinfo['CarNo'] 		 = $userinfo['CarNo']!=''?$userinfo['CarNo']:'无';
    		$userinfo['auth_status'] = $userinfo['auth_status']>0 ? '已授权' : '未授权';
    		$userinfo['auth_type'] 	 = $type;
    		$userinfo['userid']		 = $userid['user_id'];
        }
        include $this->template('parking/quserinfo/quserinfo');
    }
    
    //查看停车详情
    public function checkMore(){
    	global $_W;
    	global $_GPC;
    	
    	//检测是否登录
    	$this->checkLogin();
        
    	if(!empty(trim($_GPC['uid']))){
    		
    		$userid = $_GPC['uid'];
    		$mobile = $_GPC['mo'];
    		$title = $_GPC['name'];
    		$orderInfo = pdo_fetchall("SELECT a.ordersn,a.number,a.starttime,a.endtime,b.id,a.status FROM ".tablename('parking_order')." a LEFT JOIN ".tablename('foll_order')." b ON a.ordersn = b.ordersn WHERE b.user_id='".$userid."' order by starttime desc limit 0,5;");
    		if(empty($orderInfo)){
    			$this->message('暂无数据','','error');
    		}
    	}
		include $this->template('parking/quserinfo/checkMore');
    }
    
    
    
    
    
    
    
    
    /**
     * 获取支付订单流水
     */
    public function getPayold() {
    	global $_W;
    	global $_GPC;
    	//检测是否登录
    	$this->checkLogin();
    	/**
    	 * 计算分页：
    	 * 1、获取数据总数       $num
    	 * 2、每页显示数量	 $pageSize
    	 * 3、计算总页数		 $pageCount = ceil($num/$pageSize);
    	 * 4、计算偏移量		 $offset = ($pageNo-1) * $pageSize;
    	 * 5、当前显示页码       $pageNo = isset($_GPC['page'])?$_GPC['page']:1;
    	 * 
    	 */
		$title = '订单退款';
		$pageSize = 5;//每页显示5条
		$pageNo   = 1;//初始化偏移量
		//计算偏移量
		$offset = ($pageNo-1) * $pageSize;
		//echo '<pre>';
		$where = [
			':pay_type'=>'Fwechat',//停车应用
			':pay_status'=>1,
			':IsWrite'	 => 101,
		];
		$field = "pay_account,ordersn,upOrderId,pay_time,id,goods_name";
		//微信免密支付  总数
		$fwechatNum = pdo_fetch("SELECT COUNT(id) as count FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0  AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite ", $where)['count'];
		//计算总页数
		$pagesCount1   = ceil($fwechatNum/$pageSize);
		//获取数据
		$fwechat 	   = pdo_fetchall("SELECT {$field} FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0  AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite ORDER BY pay_time desc LIMIT {$offset},{$pageSize}", $where);
		
		
		//银联无感支付  总数
		$where[':pay_type'] = 'Parks';
		$parksCount = pdo_fetch("SELECT COUNT(id) as count FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0 AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite ", $where)['count'];
		//计算总页数
		$pagesCount3   = ceil($parksCount/$pageSize);
		$parks = pdo_fetchall("SELECT {$field} FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0 AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite ORDER BY pay_time desc LIMIT {$offset},{$pageSize}", $where);
		
		
		//农商行免密   总数
		$where[':pay_type'] = 'FAgro';
		//查询当天的数据
		$where[':PlatDate'] = date('Ymd',time());
		$fagroCount = pdo_fetch("SELECT COUNT(id) as count FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0 AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite AND PlatDate = :PlatDate ", $where)['count'];
		//计算总页数
		$pagesCount2   = ceil($fagroCount/$pageSize);
		$fagro = pdo_fetchall("SELECT {$field} FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0 AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite AND PlatDate = :PlatDate ORDER BY pay_time desc LIMIT {$offset},{$pageSize}", $where);
    		
    		
		//通莞聚合支付  总数
		$juhesCount = pdo_fetch("SELECT COUNT(id) as count FROM ".tablename('foll_order')." WHERE (pay_type = 'wechat' or pay_type = 'alipay') AND pay_account > 0 AND upOrderId != ' ' AND pay_status = 1 AND  IsWrite =101 ")['count'];
		//计算总页数
		$pagesCount4   = ceil($juhesCount/$pageSize);
		$aggreg = pdo_fetchall("SELECT {$field} FROM ".tablename('foll_order')." WHERE (pay_type = 'wechat' or pay_type = 'alipay') AND pay_account > 0 AND upOrderId != ' ' AND pay_status = 1 AND  IsWrite =101 ORDER BY pay_time desc LIMIT {$offset},{$pageSize}");
		
    	include $this->template('parking/quserinfo/getPayold');
    }
    
    
    //ajax 加载数据   退款订单数据
    public function onLoading() {
    	global $_W;
    	global $_GPC;
    	
    	if($_W['isajax']) {
    		$payType 	= trim($_GPC['payType']);
    		$pageSize 	= trim($_GPC['pageSi']);
    		$pageNo 	= trim($_GPC['pageN']);
    		$pagesCount = trim($_GPC['pagesC']);
    		$offset	    = ($pageNo-1)*$pageSize;
    		$where = [
				':pay_type'=>'Fwechat',//停车应用
				':pay_status'=>1,
				':IsWrite'	 => 101,
			];
			$field = "pay_account,ordersn,upOrderId,pay_time,id,goods_name";
    		switch($payType) {
    			case 'Fwechat':
    				$res   = pdo_fetchall("SELECT {$field} FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0  AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite ORDER BY pay_time desc LIMIT {$offset},{$pageSize}", $where);
    			break;
    			
    			case 'FAgro':
    				$where[':pay_type'] = 'FAgro';
    				$res = pdo_fetchall("SELECT {$field} FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0 AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite ORDER BY pay_time desc LIMIT {$offset},{$pageSize}", $where);
    			break;
    			
    			case 'Parks':
    				$where[':pay_type'] = 'Parks';
    				$res = pdo_fetchall("SELECT {$field} FROM ".tablename('foll_order')." WHERE pay_type = :pay_type AND pay_account > 0 AND upOrderId != ' ' AND pay_status = :pay_status AND  IsWrite =:IsWrite ORDER BY pay_time desc LIMIT {$offset},{$pageSize}", $where);
    			break;
    			
    			case 'wechat':
    				$res = pdo_fetchall("SELECT {$field} FROM ".tablename('foll_order')." WHERE (pay_type = 'wechat' or pay_type = 'alipay') AND pay_account > 0 AND upOrderId != ' ' AND pay_status = 1 AND  IsWrite =101 ORDER BY pay_time desc LIMIT {$offset},{$pageSize}");
    			break;
    		}
    		
    		if(empty($res)) {
    			//$res['code'] = 0;//没有数据
    			$status   =  0;
    		} else {
    			//$res['code'] = 1;//有数据
    			$status   = 1;
    			foreach($res as $key=>$val){
    				$res[$key]['pay_time'] = date('Y-m-d H:i:s',$val['pay_time']);
    			}
    		}
			exit(json_encode(['code'=>$status,'result'=>$res]));
    	}
    	    	
    	
    }
    
    //搜索退款   按订单号，上游单号查询
    public function SearchRefund(){
    	global $_W;
    	global $_GPC;
    	$title = '按订单号,支付号查询';
    	//检测是否登录
    	$this->checkLogin();
    	if($_W['ispost']) {
    		$ordersn   =  isset($_GPC['ordersn'])?trim($_GPC['ordersn']):'';
    		$upOrders  =  isset($_GPC['upOrders'])?trim($_GPC['upOrders']):'';
    		
    		if($ordersn=='' && $upOrders=='') {
    			$url = mobileUrl('parking/queryuserinfo/SearchRefund');
    			$this->message('平台订单号或上游支付单号不能为空！',$url,'error');
    		}
    		
    		if(!empty($ordersn) && !empty($upOrders)) {
    			$payOld = pdo_fetch('SELECT id,ordersn,upOrderId,pay_account,pay_status,pay_time,IsWrite,goods_name FROM '.tablename('foll_order').' WHERE ordersn=:ordersn AND upOrderId=:upOrders LIMIT 1',[':ordersn'=>$ordersn,':upOrders'=>$upOrders]);
    		} else if(!empty($ordersn)){
    			$payOld = pdo_fetch('SELECT id,ordersn,upOrderId,pay_account,pay_status,pay_time,IsWrite,goods_name FROM '.tablename('foll_order').' WHERE ordersn=:ordersn LIMIT 1',[':ordersn'=>$ordersn]);
    		} else if(!empty($upOrders)){
    			$payOld = pdo_fetch('SELECT id,ordersn,upOrderId,pay_account,pay_status,pay_time,IsWrite,goods_name FROM '.tablename('foll_order').' WHERE upOrderId=:upOrders LIMIT 1',[':upOrders'=>$upOrders]);
    		}
    		
    		if(empty($payOld)){
    			$url = mobileUrl('parking/queryuserinfo/SearchRefund');
    			$this->message('差不到该订单的信息，请检查重新输入！',$url,'error');
    		}
    	}
    	
    	include $this->template('parking/quserinfo/searchrefund');
    }
    
    
    //退款操作
    public function Refund() {
    	global $_W;
    	global $_GPC;
    	//检测是否登录
    	$this->checkLogin();
    	if($_W['isajax']){
    		$oldMoney  = trim($_GPC['oldMoney']);
    		$remoney   = trim($_GPC['remoney']);
    		
    		if($remoney > $oldMoney) {
    			exit(json_encode(['code'=>0,'result'=>'','msg'=>'退款金额不能大于原金额！']));
    		}
    		
    		$id  = trim($_GPC['rid']);
    		$res = pdo_get('foll_order',['id'=>$id],'upOrderId,PlatDate,ordersn,pay_type,uniacid,IsWrite');
    		
    		/*exit(json_encode($res));//输出数据 请求退款
    		die;*/
    		
    		if(!empty($res) && ($res['IsWrite'] != 100)) {
    			//更新退款字段金额；
    			//pdo_update('foll_order',['RefundMoney'=>$remoney],['id'=>$id]);
    			/**
    			 * 步骤：
    			 * 1、获取当前退款通道  switch
    			 * 2、组装数据请求退款
    			 * 3、请求成功，更新数据，发送微信消息
    			 * 4、返回json 数据回前端
    			 */
    			switch($res['pay_type']) {
    				
    				case 'Fwechat'://微信免密
    					//退款
						$data = [
							//'Token' 	 => 'Test',
							'Token' =>'Refund', //退款类型
							'RefundMoney'=> $remoney,
							'orderSn'	 => $res['ordersn'],//订单编号；
						];
						$url = 'http://shop.gogo198.cn/payment/Frx/Frx.php';
						$reqs = ihttp_request($url,$data);
						//解析数据
						//$req  = json_decode($reqs['content'],true);
    				break;
    				
    				case 'FAgro'://农商行免密
    					//单笔扣费冲销(只能冲当天)
						$data = [
							'Token' =>'WriteOff',//单笔扣费冲销（只能冲当天）
							'PlatDate'=> !empty($res['PlatDate']) ? $res['PlatDate'] : date("Ymd",time()),//原银行日期	 8
							'BkOldSeq'=>$res['upOrderId'],//原银行流水	 12
						];
						$url = 'http://shop.gogo198.cn/payment/agro/Fagro.php';
						$reqs = ihttp_request($url,$data);
						//解析数据
						//$req  = json_decode($reqs['content'],true);
    				break;
    				
    				case 'Parks'://银联无感支付
    					$data = [
							'token'  => 'Refund',
							'ordersn'=>$res['ordersn'],
							'Money'	 => $remoney,//单元
						];
						$url = 'http://shop.gogo198.cn/payment/sign/Togrand.php';
						$reqs = ihttp_request($url,$data);
						//解析数据
						//$req  = json_decode($reqs['content'],true);
    				break;
    				
    				case 'wechat'://聚合支付
    				case 'alipay'://聚合支付
    					/**
						 * 退款请求
						 */
						$data = array(
							'token'      => 'refund',
							'ordersn'    => $res['ordersn'],
							'refundMoney'=> $remoney,
						);
						$url = 'http://shop.gogo198.cn/payment/wechat/refund.php';
						$reqs = ihttp_request($url,$data);
    				break;
    			}
    		}
    		
    		//解析数据   解析json数据
			$req  = json_decode($reqs['content'],true);
    		
    		if($req['codes'] >= 1) {
    			exit(json_encode(['code'=>1,'result'=>$req['msg'],'msg'=>'success','su'=>$req]));
    		}
    		exit(json_encode(['code'=>0,'result'=>$req['msg'],'msg'=>$req['msg']]));
    	}
    	exit(json_encode(['code'=>0,'result'=>' ','msg'=>'请求类型不正确']));
    }
    
    
    
    /**
	 * 发送短信验证码
	 */
	public function code ()
    {
        global $_W;
        global $_GPC;
        // $set = m('common')->getSysset(array('shop', 'wap'));
        $sms_id = $this->GetSmsid(14);
        // $sms_id = $set['wap'][$temp];
        $key  = '__ewei_shopv2_member_verifycodesession_14_' . $_GPC['mobile'];
        
        $user = pdo_get('foll_business_admin',array('user_mobile'=>$_GPC['mobile']));
        if(!$user){
        	show_json(0,'不存在该用户，请检查！');
        }
        
        $code = random(5, true);
        $ret  = com('sms')->send($_GPC['mobile'], $sms_id['id'], ['名称' => $_W['uniaccount']['name'], '验证码' => $code]);
        if ( $ret['status'] ) {
            $_SESSION[$key]                 = $code;
            $_SESSION['verifycodesendtime'] = time();
            show_json(1, '短信发送成功');
        }
        show_json(0, $ret['message']);
    }
	
    //验证登录
    public function verify_reg()
    {
        global $_W;
        global $_GPC;
        if ( $_W['ispost'] ) {
        	
            if ( empty($_GPC['mobile']) || strlen($_GPC['mobile']) != 11 ) {
                show_json(0, '请输入手机号');
            }
            if ( !preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $_GPC['mobile']) ) {
                show_json(0, '请输入正确的手机号');
            }
            $key = '__ewei_shopv2_member_verifycodesession_14_' . $_GPC['mobile'];
            if ( !($_SESSION[$key] == $_GPC['yzm']) || $_GPC === '' ) {
                show_json(0, '验证码错误');
            }
            
            $userinfo = $user = pdo_get('foll_business_admin',array('user_mobile'=>$_GPC['mobile']));
            if(!$userinfo) {
            	$url = mobileUrl('parking/queryuserinfo/login');
	        	$this->message('登录失败！',$url,'error');
            }
            
            $_SESSION['checkUser'] = $userinfo;
            $url = mobileUrl('parking/queryuserinfo/main');
            $this->message('登录成功！',$url,'success');
        }
    }
    
    protected function GetSmsid ( $cid )
    {
        return pdo_get('ewei_shop_sms', ['uniacid' => $cid], ['id']);
    }
	
}