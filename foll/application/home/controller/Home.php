<?php
	namespace app\home\controller;
	use think\Request;
	use think\Controller;
	
	class Home extends Controller{
		//http://shop.gogo198.cn/foll/public//?s=Home/home/home
		//后台首页
		public function home(){
			
//			Redirects('home/getUrls&a=1');
//			echo '<pre>';
//			print_r(get_defined_constants());

			return $this->fetch('home/index',['name'=>'thinkphp']);
		}
		
		public function getUrls(Request $request)
		{
			echo $request->get('a');
		}
		
		//登录
		public function loginIn(){
			return $this->fetch('home/login');
		}
		//登录处理
		public function loginIncheck(){
			echo '登录处理中...';
		}
		
		//退出登录
		public function loginOut(){
			
		}
	}
	
?>