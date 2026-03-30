<?php
	/**
	 * 基础控制器
	 * 必须继承自：think\Controller.php
	 */
	namespace app\common\controller;	
	use think\Controller;
	use think\Session;
	use think\Request;
//	use think\Db;
	
	class Base extends Controller
	{
		/**
		 * 初始化方法
		 * 创建常量：公共方法
		 * 在所有的方法之前被调用；
		 */
//		public function __construct()
//		{
//			//显示分类导航
////			$this->showNav();
//			
//			//检测网站是否关闭
////			$this->is_open();
//			
//			//获取右侧数据
////			$this->getHotArt();
//
//			//检查用户登录
//			parent::__construct();
//			$this->logined();
//		}
//		
		//防止重复登录
		protected function isLogin()
		{
			if(Session::has('user_id'))
			{
				$this->error('客观,您已经登录啦~~','index/index');
			}
		}
		
		//检查是否未登录：放在需要登录操作的方法的最前面，例如发布文章
		protected function logined()
		{
			if(!Session::has('user_id'))
			{
				$url = "http://shop.gogo198.cn/foll/public/index.php?s=order/login";
				$this->error('客观,您是不是忘记登录啦~~',$url);
			}
		}
		
		//显示分类导航
//		protected function showNav()
//		{
//			//1.查询分类表获取到所有的分类信息
//			$cateList = ArtCate::all(function($query){
//				$query->where('status','1')
//					->order('sort','asc');
//			});
//			//将分类信息赋给模板  nav.html
//			$this->view->assign('cateList',$cateList);
//		}
	}
?>