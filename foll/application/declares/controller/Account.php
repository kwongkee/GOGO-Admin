<?php
namespace app\declares\controller;
use think\Controller;
use Util\data\Sysdb;
//use think\Db;
use think\Session;
use think\Sms;
use think\Request;
use think\Loader;

class Account extends Controller
{
    public function login()
	{
		if(Session::has('admin')) {
            Redirects('home/index');
        }
        
		return $this->fetch();
	}

	public function LoginChecked(Request $request)
    {
		$this->db = new Sysdb;
    	$username = input("post.username");//手机号码
    	$yzm = input('post.yzm');//验证码
    	if(strlen($username) != "11" || $username=='') 
    	{
    		return json(['code'=>0,'msg'=>'长度或不能为空']);
    	}
    	if(!preg_match("/^1[34578]{1}\d{9}$/",$username)){
    		return json(['code'=>0,'msg'=>'手机格式错误']);
    	}
    	
        if($yzm!==Session::get('yzm')||empty($yzm))
        {
        	return json(['code'=>0,'msg'=>'验证码错误']);
        }

        $user = $this->db->table('foll_business_admin')->where(['user_mobile'=>$username])->item();
        if(!empty($user)){
        	if($user['user_status']=='0') {
        		//0是禁用,1是正常
				return json(['code'=>0,'msg'=>'已被禁用']);
        	}
        	
        	if($user['status'] > 0 ){//0超级管理员,1管理员
				return json(['code'=>0,'msg'=>'账号禁用,请联系管理员!']);
        	}
            session('admin',$user);
			return json(['code'=>1,'msg'=>'登录成功']);
        }
		return json(['code'=>0,'msg'=>'用户不存在']);
    }
    
    /*
     * 发送短信验证码阿里云大鱼
     */
    public function sendCode(Request $request)
    {
    	$this->db = new Sysdb;
		$username = $request->get("username");
		$username = trim($username);
		$user 	  = $this->db->table('foll_business_admin')->where(['user_mobile'=>$username])->item();
		if(empty($user)) {
			return json(['code'=>0,'msg'=>'该用户不存在!']);
		} else {
			if(verifCode($username)){//验证用户名(手机)
//				$code =mt_rand(11,99).mt_rand(11,99).mt_rand(11,99);
				$code='123456';
				$config=[
			        'SingnName'=> 'Gogo购购网',
			        'code'     => $code,
			        'product'  =>'Gogo海关申报系统',
			        'tel'      => $username,
			        'TemplateCode'=>'SMS_35030091'
			    ];
//				sendSms($config);
				Session::set("yzm",$code);
	        	return json(['code'=>1,'msg'=>'发送成功']);
			}else{
				return json(array('code'=>0,'msg'=>'手机格式错误'));
			}
		}
    }
    
   //退出登录
   public function logout()
   {
   		session('admin',null);
   		//Session::set('admin',null);
   		return json(['code'=>1,'msg'=>'退出成功']);
   }
   
   	//管理员登录
	/*public function dologin()
	{
		$username = trim(input('post.username'));
		$pwd = trim(input('post.pwd'));
		$verifycode = trim(input('post.verifycode'));
		if($username == ''){
			exit(json_encode(['code'=>1,'msg'=>'用户名不能为空']));
		}
		if($pwd == ''){
			exit(json_encode(['code'=>1,'msg'=>'密码不能为空']));
		}
		if($verifycode == ''){
			exit(json_encode(['code'=>1,'msg'=>'验证码不能为空']));
		}
		
		//验证码验证
		if(!captcha_check($verifycode)){
			exit(json_encode(['code'=>1,'msg'=>'验证码错误']));
		}
		
		//验证用户
		$this->db = new Sysdb;
		$admin = $this->db->table('admin')->where(['username'=>$username])->item();
		if(!$admin){
			exit(json_encode(['code'=>1,'msg'=>'用户不存在']));
		}
		// 验证密码
		if(md5($admin['username'].$pwd) != $admin['password']){
			exit(json_encode(['code'=>1,'msg'=>'密码错误']));
		}
		if($admin['status'] == 1){
			exit(json_encode(['code'=>1,'msg'=>'用户已被禁用']));
		}
		// 设置用户session
		session('admin',$admin);
		exit(json_encode(['code'=>0,'msg'=>'登录成功']));
	}*/
	
	
	
	/**
	 * 2018-07-16
	 * @author 赵金如
	 * 生成昨日账单，并发送至商户邮箱
	 */
	public function Generatingbill()
	{
		$this->db = new Sysdb;
		echo date('Y-m-d H:i:s',time());
		/**
		 * 步骤：获取商户信息，生成对账日期，循环添加数据；
		 * 循环商户数据发送电子邮箱
		 */
		//商户信息
		$getUserinfo = $this->db->table('foll_business_admin')->where(['order_prix'=>['neq','']])->lists();
		
		//生成对账时间
		$startDate = date("Y-m-d 00:00:00",strtotime("-1 day"));//昨天开始时间
		$endDate   = date("Y-m-d H:i:s",(strtotime($startDate)+86399));//昨日结束时间
		
		//定义对账类型   商品备案  支付订单，实名验证
		$type = ['goods','pay','auth','orders'];
		$count = count($type);
		$getCount = count($getUserinfo);
		$updatas = [];
		$updata  = [];
		for($n=0;$n<$count;$n++) {//外层3次  对账次数
			for($i=0;$i<$getCount;$i++) {//内层4次  商户数据
				$updata[$i]['uid'] 	      = $getUserinfo[$i]['id'];//用户ID
				$updata[$i]['user_name']  = $getUserinfo[$i]['user_name'];//商户名称
				$updata[$i]['user_email'] = $getUserinfo[$i]['user_email'];//商户邮箱
				$updata[$i]['startDate']  = $startDate;//昨日开始时间
				$updata[$i]['endDate'] 	  = $endDate;//昨日结束时间
				$updata[$i]['status'] 	  = 3;//对账状态：1、确认对账，2、有误账单，3、未对账
				$updata[$i]['c_time'] 	  = date('Y-m-d H:i:s',time());//创建时间
				$updata[$i]['type'] 	  = $type[$n];//对账类型
				$updata[$i]['accountDay'] = date("Y-m-d",strtotime("-1 day"));//对账日  昨天
//				$updata[$i]['accountDay'] = date("Y-m-d",$days);//对账日  昨天
			}
			array_push($updatas,$updata);//末尾添加数据
		}
		
		//echo '<pre>';
		//循环生成数据
		foreach($updatas as $key=>$val) {
			foreach($val as $k=>$v) {
				//写入对账表内
				$this->db->table('foll_goodconfirm')->insert($v);
				//print_r($v);
			}
		}
		
		//循环发送邮件  2020-01-02
		/*foreach($getUserinfo as $key=>$val){
			$this->sendEmail('true',$val['user_email']);
			//echo '发送电子邮件: '.$val['user_email'].'<br>';
		}*/
		
		//$this->sendEmail('true','805929498@qq.com');
		
		echo '生成成功';
		echo '<hr>';
		echo date('Y-m-d H:i:s',time());
	}
	
	
	//发送电子邮件给商户
	protected function sendEmail($path,$email,$subject = '您有海关商户对账信息，请及时登录查看') {
		$name    = '系统管理员';
		$content = "提示：您有海关商户对账信息，请及时登录查看！,您可请登录后台<a href='http://shop.gogo198.cn/foll/public/?s=account/login'>点击前往登录后台</a>";
    	if($path == 'true'){//没有数据发送
    		$status  = send_mail($email,$name,$subject,$content);
    	} else {
    		$status  = send_mail($email,$name,$subject,$content,['0'=>$path]);
    	}
		if($status) {
			return true;
		} else {
			return false;
		}
	}
}
?>