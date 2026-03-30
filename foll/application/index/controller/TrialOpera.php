<?php
namespace app\index\controller;
use app\index\controller;
//use think\Db;
use Util\data\Sysdb;
use think\Request;
use think\Session;
use think\Validate;
use think\Loader;
use extend\lib\phpqrcode;
class TrialOpera extends CommonController
{
	/**
	 * 试运行界面
	 */
	public function index()
	{
		
		return view('trialopera/index',[
			'title'=>'试运营设置'
		]);
	}
	/**
	 * 保存设置
	 */
	public function save(Request $request)
	{
		//获取用户登录信息
		$info = session('UserResutlt');
		$this->db = new Sysdb;
		
		//需添加数据数组
		$data['user']		= $info['user_name'];
		$data['uniacid']	= $info['uniacid'];
		$data['mobile']		= $info['user_mobile'];
		$data['title'] 		= $request->post('title');
		$data['discount'] 	= $request->post('discount');
		$data['startDate'] 	= strtotime($request->post('startDate'));
		$data['endDate'] 	= strtotime($request->post('endDate'));
		$data['status']		= '1';//状态： 1、提交申请，2、审核通过，3、审核不通过！
		$data['create_time'] = time();//创建日期
		/**
		 * 开发步骤：
		 * 判断该用户添加的表内是否存在已添加的  下一个优惠必须是上一个时间结束的开始位置；
		 * 填写成功发送短信或微信通知管理员；
		 * 不通过需要通知管理修改；
		 * 新增表：parking_operate  运营表
		 */
		//查询该用户是否添加有数据，就就把里面的数据拿出来循环判断当前添加的日期是否小于最后一个添加的
		$where = [
			//'user'		=> $info['user_name'],//用户名
			'uniacid'	=> $info['uniacid'],//用户名
			'status'	=> 2,
			//'mobile'	=> $info['user_mobile']//用户名
		];
		$flag = false;
		$msg  = 'success';
		$judge = $this->db->table("parking_operate")->where($where)->order('id desc')->lists();
		if(!empty($judge)) {//如果数据不为空    判断当前开始时间是否小于上一个的结束时间
			
			foreach($judge as $key=>$val) {
				//下一个开始日期，需要大于上一个的结束日期
				if($data['startDate'] < $val['endDate']) {
					$msg = '您设置的 《开始日期》 必须大于上一个阶段的 《结束日期》,请重新输入！';
					break;
				} else {//否则条件允许
					$flag = true;
				}
			}
			//允许添加数据
			if($flag) {
				$inser = $this->db->table('parking_operate')->insert($data);
				if($inser) {
					$flag = true;
				} else {
					$flag = false;
					$msg = "数据写入错误!";
				}
			}
			
		} else {//数据为空就新添加
			$inser = $this->db->table('parking_operate')->insert($data);
			if($inser) {
				$flag = true;
			} else {
				$flag = false;
				$msg = "数据写入错误!";
			}
		}
		
		//flage 判断  true
		if($flag) {
			$this->sendEmail();
			echo json_encode(['code'=>1,'msg'=>$msg]);
		} else {//false
			echo json_encode(['code'=>0,'msg'=>$msg]);
		}
	}
	
	/**
	 * 查询当前阶段名称是否重复存在
	 */
	public function checkTitle(Request $request)
	{
		//获取用户登录信息
		$info = session('UserResutlt');
		$this->db = new Sysdb;
		
		if($request->post('title') == '') {
			echo json_encode(['code'=>0,'msg'=>'标题不能为空，请填写!']);
			exit();
		}
		
		$where = [
			'user'		=> $info['user_name'],//用户名
			'uniacid'	=> $info['uniacid'],//用户名
			'mobile'	=> $info['user_mobile'],//用户名
			'title'		=> trim($request->post('title'))
		];
		
		$judge = $this->db->table("parking_operate")->where($where)->order('id desc')->item();
		if(!empty($judge)) {//存在该标题
			
			echo json_encode(['code'=>0,'msg'=>'该标题已存在，请重新输入!']);
			
		} else {
			echo json_encode(['code'=>1,'msg'=>'该标题可以使用!']);
		}
		
	}
	
	
	/**
	 * 阶段列表
	 */
	public function lists()
	{
		//获取用户登录信息
		$info = session('UserResutlt');
		$this->db = new Sysdb;
		
		$where = [
			//'user'		=> $info['user_name'],//用户名
			'uniacid'	=> $info['uniacid'],//用户名
			//'mobile'	=> $info['user_mobile']//用户名
		];
		
		$flag = false;
		$judge = $this->db->table("parking_operate")->where($where)->lists();
		if(!empty($judge)) {//存在该标题
			$flag = true;
		}
		
		return view('trialopera/lists'
		,[
			'title'=>'我的阶段',
			'data' => $judge,
			'flag' => $flag,
		]);
	}
	
	/**
	 * 编辑列表
	 */
	public function editList(Request $request)
	{
		//获取用户登录信息
		$info = session('UserResutlt');
		$this->db = new Sysdb;
		
		$where = [
			'id'  => $request->get('id'),//用户名
		];
		
		$judge = $this->db->table("parking_operate")->where($where)->item();
		if(empty($judge)){
			return '暂无数据';
		}
		return view('trialopera/editList'
		,[
			'title'=>'编辑阶段',
			'data' => $judge,
		]);
	}
	
	/**
	 * 编辑阶段
	 */
	public function edit(Request $request)
	{
		//获取用户登录信息
		$info = session('UserResutlt');
		$this->db = new Sysdb;
		
		//需添加数据数组
		$data['discount'] 	= $request->post('discount');
		$data['startDate'] 	= strtotime($request->post('startDate'));
		$data['endDate'] 	= strtotime($request->post('endDate'));
		$data['status']		= '1';//状态： 1、提交申请，2、审核通过，3、审核不通过！
		
		/**
		 * 开发步骤：
		 * 判断该用户添加的表内是否存在已添加的  下一个优惠必须是上一个时间结束的开始位置；
		 * 填写成功发送短信或微信通知管理员；
		 * 不通过需要通知管理修改；
		 * 新增表：parking_operate  运营表
		 */
		//查询该用户是否添加有数据，就就把里面的数据拿出来循环判断当前添加的日期是否小于最后一个添加的
		$id    = trim($request->post('id'));//用户名
		
		$where = [
			'user'		=> $info['user_name'],//用户名
			'uniacid'	=> $info['uniacid'],//用户名
			'mobile'	=> $info['user_mobile'],//用户名
			'id'		=> ['neq',$id],
		];
		
		$flag = false;
		$msg  = 'success';
		
		$judge = $this->db->table("parking_operate")->where($where)->order('id desc')->lists();
		if(!empty($judge)) {//如果数据不为空    判断当前开始时间是否小于上一个的结束时间
			
			foreach($judge as $key=>$val) {
				//下一个开始日期，需要大于上一个的结束日期
				if($data['startDate'] < $val['endDate']) {
					$msg = '您设置的 《开始日期》 必须大于上一个阶段的 《结束日期》,请重新输入！';
					break;
				} else {//否则条件允许
					$flag = true;
				}
			}
			//允许添加数据
			if($flag) {
				$inser = $this->db->table('parking_operate')->where(['id'=>$id])->update($data);
				if($inser) {
					$flag = true;
				} else {
					$flag = false;
					$msg = "更新错误!";
				}
			}
			
		} else {//数据为空就新添加
			$inser = $this->db->table('parking_operate')->where(['id'=>$id])->update($data);
			if($inser) {
				$flag = true;
			} else {
				$flag = false;
				$msg = "更新错误!";
			}
		}
		
		//flage 判断  true
		if($flag) {
			$this->sendEmail();
			echo json_encode(['code'=>1,'msg'=>$msg]);
		} else {//false
			echo json_encode(['code'=>0,'msg'=>$msg]);
		}
	}
	
	/**
	 * 删除编辑
	 */
	public function dels(Request $request)
	{
		//获取用户登录信息
		$info = session('UserResutlt');
		$this->db = new Sysdb;
		$where = [
			'id'  => $request->post('id'),//用户名
		];
		$info = $this->db->table('parking_operate')->where($where)->delete();
		if($info){
			echo json_encode(['code'=>0,'msg'=>'删除成功!']);
		} else {
			echo json_encode(['code'=>1,'msg'=>'删除失败!']);
		}
	}
	
	/**
	 * 发送电子邮件
	 */
    public function sendEmail()
    {
    	//$toemail = input('email');//kali20@126.com  805929498@qq.com
    	$toemail = '198@gogo198.net';
		$name    = '系统管理员';
		$subject = '商户试运营设置';
		$content = "您有新的商户运营设置,请登录后台审核：<a href='http://shop.gogo198.cn/foll/public/?s=admin/index'>点击前往登录审核</a>";
    	$status  = send_mail($toemail,$name,$subject,$content);
    }
}
?>