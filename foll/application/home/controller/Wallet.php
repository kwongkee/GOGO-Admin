<?php
namespace app\home\controller;
use think\Controller;
use think\Request;
use think\Db;
//use think\db\Query;

class Wallet extends Controller{
	//钱包列表
	public function index()
	{

		$page = is_numeric(input('page'))?input('page'):'1';
		//当前页码-1 * 每页显示条数；
		$offet = 2;//每页显示多少条；
		//偏移量公式：（当前页码-1）* 每页显示多少条；
//		$offset = ($page-1)*$offet;//0 2
        
        $start = input('start');//开始时间
        $end = input('end');//结束时间
        $uname = input('uname');//商家ID
        
        if($start!='' && $end!='') {//开始，结束日期不为空；
        	 $config = [
	        	'query'=>['s'=>'wallet/wallet&start='.$start.'&end='.$end],//额外参数
	        	'var_page'=>'page',//分页变量
	        ];
	        
	        $starts = strtotime($start);//开始时间
        	$ends = strtotime($end)+86399;//结束时间
	        
	        $count = Db('foll_rebate_water')->where('create_time','>=',$starts)->where('create_time','<=',$ends)->where('uniacid',14)->count();//查询多条数据
        	$res = Db('foll_rebate_water')->where('create_time','>=',$starts)->where('create_time','<=',$ends)->where('uniacid',14)->paginate($offet,$count,$config);
       
        }else if($uname != '') {//用户ID不为空
        	 $config = [//分页配置
	        	'query'=>['s'=>'wallet/wallet'],//额外参数
	        	'var_page'=>'page',//分页变量
	        ];
        	$count = Db('foll_rebate_water')->where('uniacid',14)->where('business_id',$uname)->count();//查询多条数据
        	$res = Db('foll_rebate_water')->where('uniacid',14)->where('business_id',$uname)->paginate($offet,$count,$config);
        
        }else {//没有查询条件
        	 $config = [//分页配置
	        	'query'=>['s'=>'wallet/wallet'],//额外参数
	        	'var_page'=>'page',//分页变量
	        ];
        	$count = Db('foll_rebate_water')->where('uniacid',14)->count();//查询多条数据
        	$res = Db('foll_rebate_water')->where('uniacid',14)->order('id desc')->paginate($offet,$count,$config);
        }
		
		// 获取分页显示
		$pages = $res->render();
		$arrs = [
			'results'=>$res->toArray(),
			'count'=>$count,
			'page'=>$pages,
		];
		//模板赋值
		$this->assign($arrs);
		//渲染模板；
		return $this->fetch('wallet/walletlist');
	}
	
	//钱包编辑
	public function edit(){
		return $this->fetch('wallet/edit');
	}
	
	//添加数据  测试用
	public function add(){
		
		$inserArr = [
			'user_id'=>4,
			'business_id'=>15,
			'old'=>date('YmdHis',time()).rand(1,999),
			'uniacid'=>14,
			'goods'=>'路内智能停车',
			'goods_price'=>15,
			'rebate_money'=>2,
			'status'=>1,
			'body'=>'备注信息详情',
			'create_time'=>time(),
		];
		
		$res = Db('foll_rebate_water')->insert($inserArr);
		if(!empty($res)){
			$this->success('添加成功'.$res);
		}else {
			$this->error('添加失败');
		}
//		return $this->fetch('wallet/edit');
	}
	
	//密码修改
	public function password(){
		return $this->fetch('wallet/password');
	}
	
	//消费列表
	public function consumption()
	{
		return $this->fetc('wallet/wallet_list');
	}
	
}

?>