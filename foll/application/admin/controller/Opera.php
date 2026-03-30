<?php
namespace app\admin\controller;
use app\admin\controller;
use think\Request;
use Util\data\Sysdb;
use think\Session;
use think\Validate;
use think\Loader;
class Opera extends Auth
{
	
	/**
	 * 运营审核
	 */
	public function reviewed()
	{
		$this->db = new Sysdb;
		$userInfo = Session('myUser');
		
		$config = [
            'type' =>'Layui',
            'query'=>['s'=>'Opera/reviewed'],
            'var_page'=>'page',
            'newstyle'=>true
        ];
        
		if($userInfo['role'] == 1){
			$opera = $this->db->table('parking_operate')->where(['status'=>1])->pages(5,$config);
		} else {
			$opera = $this->db->table('parking_operate')->where(['uniacid'=>$userInfo['id'],'status'=>1])->pages(5,$config);
		}
		
		$this->assign('order',$opera);
		
		return view("opera/reviewed",[
            'title'=>'运营审核列表',
        ]);
	}
	
	/**
	 * 运营折扣列表；
	 */
	public function check(Request $request)
	{
		$this->db = new Sysdb;
		$userInfo = Session('myUser');
		
		$id      = $request->post('id');//作为修改条件
		$token   = $request->post('token');//设置通过或拒绝  ok OR no
		$status  = 3;//修改状态
		if($token == 'ok') {
			$status = 2;
		}
		
		$data = [
			'AuditUser'  =>$userInfo['username'],//审核人
			'update_time'=>time(),//审核日期
			'status'	 => $status,
		];
		
		$up = $this->db->table('parking_operate')->where(['id'=>$id])->update($data);
		if($up) {
			
			$this->sendEmail();
			
			echo json_encode(['code'=>0,'msg'=>'设置成功']);
		} else {
			echo json_encode(['code'=>1,'msg'=>'设置失败']);
		}
		
	}
	
	//运营总列表
	public function lists()
    {
    	$this->db = new Sysdb;
		$userInfo = Session('myUser');
		
		$config = [
            'type' =>'Layui',
            'query'=>['s'=>'Opera/lists'],
            'var_page'=>'page',
            'newstyle'=>true
        ];
        
		if($userInfo['role'] == 1){
			$opera = $this->db->table('parking_operate')->where(['status'=>['neq',1]])->pages(5,$config);
		} else {
			$opera = $this->db->table('parking_operate')->where(['uniacid'=>$userInfo['id'],'status'=>['neq',1]])->pages(5,$config);
		}
		
		$this->assign('order',$opera);
    	
    	return view("opera/lists",[
            'title'=>'试运营列表',
        ]);
    }
    
    public function sendEmail($toemail = '805929498@qq.com')
    {
    	//$toemail = input('email');//kali20@126.com  805929498@qq.com
		$name    = '系统管理员';
		$subject = '商户试运营设置';
		$content = "您的运营设置已通过,请登录后台查看：<a href='http://shop.gogo198.cn/foll/public/?s=index/index'>点击前往登录</a>";
    	$status  = send_mail($toemail,$name,$subject,$content);
    }
	
}
?>