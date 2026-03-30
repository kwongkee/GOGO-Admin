<?php
	namespace app\order\controller;
//	use think\Controller;
	use app\common\controller\Base;
	use think\Session;
	use think\Request;
//	use app\order\model\FollOrder;
	use think\Db;
	
	class Order extends Base
	{
		//信息统计页
		public function index()
		{
			//判断用户是否登录
//			$this->logined();
			
			//订单列表链接
			return $this->view->fetch('index');
		}
		
		/**
		 * 查询条件：
		 * 商家ID、类型（支付宝...)、查询日期大于当天凌晨，
		 * 大于对账表中的ID，分页；
		 */
		public function order(Request $request)
		{
			//判断用户是否登录
//			$this->logined();
			//获取URL连接  urlInfo
			$getData = $request->param();			
			//商家信息存于Session 中
			$userId = Session::get('user_id');
			$orId = 0;
			
			//通过商户ID，查询该商户最后一条对账记录  返回一维数组；
			$accounts = Db('foll_accounts')->where('busineId',$userId)->where('type',$getData['type'])->order('create_time')->find();
			if(!empty($accounts)) {
				$orId = substr($accounts['allId'],-1,1);//取最后一个对账的ID；
				
				//利用对账单的ID加一，获取对账日期全天的时间；
				$id = $orId+1;//获取未对账的最后第一条数据；
				$ones = Db('foll_order')->where('id',$id)->field('create_time')->find();
				$star = strtotime(date('Ymd',$ones['create_time']));//获取表中数据的开始时间  0000
			}
			
//			1523289600  当天凌晨时间
//			1523289599  昨日23:59时间
			
			$times = date('Ymd',time());//当前时间；
			$times = strtotime($times);//当前时间转为时间搓；
			//组装查询条件;
			$where = [
				'a.business_id'=>$userId,
				'a.id' => ['>',$orId?$orId:0],
				'a.create_time'=>['>',$times],//只查询上一天的时间；
			];			
			switch($getData['type']){
				case 'free'://微信账单，免费订单；
					$title = '免费订单';
					$where1 = ['a.pay_account'=>0];
				break;
				case 'bill'://微信账单，挂账订单;
					$title = '挂账订单';//未支付订单
					$where1 = ['a.pay_status'=>0];
				break;
				case 'advance'://微信账单，预付费订单;
					$title = '预付费订单';
					$where1 = ['b.charge_type'=>0];
				break;
				case 'wechat'://微信账单，聚合支付；微信账单
					$title = '微信账单';
					$where1 = ['a.pay_type'=>'wechat','a.pay_status'=>1];
				break;
				case 'alipay'://微信账单，聚合支付；支付宝账单
					$title = '支付宝账单';
					$where1 = ['a.pay_type'=>'alipay','a.pay_status'=>1];
				break;
				case 'unionpay'://微信账单，聚合支付；银联账单
					$title = '银联账单';
					$where1 = ['a.pay_type'=>'unionpay','a.pay_status'=>1];
				break;
				case 'Fwechat'://微信账单，免费支付；微信账单
					$title = '微信免密支付账单';
					$where1 = ['a.pay_type'=>'Fwechat','a.pay_status'=>1];
				break;
				case 'Falipay'://微信账单，免费支付；支付宝账单
					$title = '支付宝免密支付账单';
					$where1 = ['a.pay_type'=>'Falipay','a.pay_status'=>1];
				break;
				case 'FCreditCard'://微信账单，免费支付；信用卡账单
					$title = '信用卡免密支付账单';
					$where1 = ['a.pay_type'=>'FCreditCard','a.pay_status'=>1];
				break;
				case 'FAgro'://微信账单，免费支付；顺德农商订单
					$title = '顺德农商免密支付账单';
					$where1 = ['a.pay_type'=>'FAgro','a.pay_status'=>1];
				break;
			}
			$where = array_merge($where,$where1);
			$data = Db('foll_order')->alias('a')
					->join('parking_order b','a.ordersn = b.ordersn')
					->where($where)->field('a.id,a.ordersn,a.pay_account,b.number,b.starttime,b.endtime')->limit(10)->select();
			//订单列表链接
			$this->assign([
				'data'=>$data,
				'title'=>$title,
				'empty'=>'<h2 class="text-center">暂无数据</h2>',
			]);
			return $this->view->fetch('order');
		}
		
		//获取用户订单列表
		public function orderList(Request $request)
		{		
			//检查用户是否登录
			$this->logined();
			/**
			 * 开发步骤：
			 * 1、获取分页查询信息  page = 1
			 * 2、获取商家信息（配置）
			 * 3、根据商家与分页信息查询总数据
			 * 4、根据商家与分页信息查询数据，返回前端加载；
			 * 获取商家信息（）
			 */
			//请求类型是否为ajax
			if(!Request::instance()->isAjax()){
				return ['status'=>0,'请求数据类型不正确'];
			}
			
			//获取用户提交的数据  page = 1
			$page = $request->param('page')?$request->param('page'):1;
			$count = 6;//每页显示6
			$page = ($page-1)*$count;
			
			$type = $request->param('type');//获取查询数据的类型；
			//商家信息存于Session 中
			$userId = Session::get('user_id');
			//组装查询条件;
			$where = ['a.business_id'=>$userId];
			switch($getData['type']){
				case 'free'://微信账单，免费订单；
					$title = '免费订单';
					$where1 = ['a.pay_account'=>0];
				break;
				case 'bill'://微信账单，挂账订单;
					$title = '挂账订单';//未支付订单
					$where1 = ['a.pay_status'=>0];
				break;
				case 'advance'://微信账单，预付费订单;
					$title = '预付费订单';
					$where1 = ['b.charge_type'=>0];
				break;
				case 'wechat'://微信账单，聚合支付；微信账单
					$title = '微信账单';
					$where1 = ['a.pay_type'=>'wechat','a.pay_status'=>1];
				break;
				case 'alipay'://微信账单，聚合支付；支付宝账单
					$title = '支付宝账单';
					$where1 = ['a.pay_type'=>'alipay','a.pay_status'=>1];
				break;
				case 'unionpay'://微信账单，聚合支付；银联账单
					$title = '银联账单';
					$where1 = ['a.pay_type'=>'unionpay','a.pay_status'=>1];
				break;
				case 'Fwechat'://微信账单，免费支付；微信账单
					$title = '微信免密支付账单';
					$where1 = ['a.pay_type'=>'Fwechat','a.pay_status'=>1];
				break;
				case 'Falipay'://微信账单，免费支付；支付宝账单
					$title = '支付宝免密支付账单';
					$where1 = ['a.pay_type'=>'Falipay','a.pay_status'=>1];
				break;
				case 'FCreditCard'://微信账单，免费支付；信用卡账单
					$title = '信用卡免密支付账单';
					$where1 = ['a.pay_type'=>'FCreditCard','a.pay_status'=>1];
				break;
				case 'FAgro'://微信账单，免费支付；顺德农商订单
					$title = '顺德农商免密支付账单';
					$where1 = ['a.pay_type'=>'FAgro','a.pay_status'=>1];
				break;
			}
			$where = array_merge($where,$where1);
			//查询数据时，根据商家的ID  查询该商家所有的信息  查询：foll_order 和 pariking_order 
			$data = Db('foll_order')->alias('a')
					->join('parking_order b','a.ordersn = b.ordersn')
					->where($where)->field('a.id,a.ordersn,a.pay_account,b.number,b.starttime,b.endtime')->limit($page,$count)->select();
			if(!empty($data)){
				return ['status'=>1,'data'=>$data];	
			} else {
				return ['status'=>0,'data'=>''];	
			}
		}
		
		
		//用户登录才能进行对账
		public function login()
		{
			return $this->view->fetch('login');
		}
		
		//用户退出登录
		public function logout()
		{
			Session::clear();
			$url = "http://shop.gogo198.cn/foll/public/index.php?s=order/login";
			$this->success('退出成功',$url);
		}
		
		//提交用户登录
		public function isLogin()
		{
			$data = input();//接收表单数据
			$validate = [
				'username|用户名'=>'require|alpha|min:3',
				'password|密码'=>'require|min:5|max:15|alphaNum',
			];
			$valid = $this->validate($data,$validate);
			if($valid !== true) {
				return ['status'=>0,'msg'=>$valid];
			}
			/**
			 * 查询商户表数据；ims_foll_verified_business
			 */
//			$res = Db::table('');
			if($data['password'] == 'abc123' && $data['username'] == 'ZJR'){
				Session::set('user_id','14');
				Session::set('user_name','ThinkPhp');
				return ['status'=>1,'msg'=>'成功，欢迎您回来'];
			}else {
				return ['status'=>0,'msg'=>'用户名或密码不正确，请检查'];
			}
			
		}
		
	}
?>