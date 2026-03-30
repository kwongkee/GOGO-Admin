<?php
namespace app\usersinfo\controller;
use think\Controller;
use Util\data\Sysdb;
//use think\Db;
use think\Session;
use think\Sms;
use think\Request;
use think\Loader;

class Account extends Controller
{
	public function __construct(){
		parent::__construct();
		$this->db = new Sysdb;
	}
	
	//登录跳转，没有登录就跳转登录！
    public function login()
	{
		if(Session::has('plats')) {
            Redirects('usersinfo/home');
      	}
    	
     	$plat = $this->db->table('platform')->lists();
        $data = [
        	'title'=>'用户数据信息查询系统',
        ];
        $this->assign('plat',$plat);
        $this->assign('order',$data);
		return $this->fetch();
	}
	
	//登录验证
	public function LoginChecked(Request $request)
    {
		$this->db = new Sysdb;
    	$mobile = trim(input("post.username"));//手机号码
    	$yzm = trim(input('post.yzm'));//验证码
    	$platform = trim(input("post.platform"));//平台名称
    	
    	if(strlen($mobile) != "11" || $mobile=='') 
    	{
    		return json(['code'=>0,'msg'=>'长度或不能为空']);
    	}
    	
    	if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
    		return json(['code'=>0,'msg'=>'手机格式错误']);
    	}
    	
        if($yzm!==Session::get('yzm')||empty($yzm))
        {
        	return json(['code'=>0,'msg'=>'验证码错误']);
        }
        
        switch($platform){
			//停车平台
			case 'parking':
				$user 	  = $this->db->table('foll_business_admin')->where(['user_mobile'=>$mobile,'order_prix'=>''])->item();
			break;
			//电商平台
			case 'Crossborder':
				$user 	  = $this->db->table('foll_business_admin')->where(['user_mobile'=>$mobile,'order_prix'=>['neq','']])->item();
			break;
		}
		$user['platform'] = $platform;
		
        //$user = $this->db->table('foll_business_admin')->where(['user_mobile'=>$username])->item();
        if(!empty($user)){
        	if($user['user_status']=='0') {
        		//0是禁用,1是正常
				return json(['code'=>0,'msg'=>'已被禁用']);
        	}
        	
        	if($user['status'] > 0 ){//0超级管理员,1管理员
				return json(['code'=>0,'msg'=>'账号禁用,请联系管理员!']);
        	}
            session('plats',$user);
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
		$mobile   = trim($username);
		//平台标识
		$platform = $request->get("platform");
		$platform = trim($platform);
		switch($platform){
			//停车平台
			case 'parking':
				$user 	  = $this->db->table('foll_business_admin')->where(['user_mobile'=>$mobile,'order_prix'=>''])->item();
			break;
			//电商平台
			case 'Crossborder':
				$user 	  = $this->db->table('foll_business_admin')->where(['user_mobile'=>$mobile,'order_prix'=>['neq','']])->item();
			break;
		}
		if(empty($user)) {
			return json(['code'=>0,'msg'=>'该平台不存在该用户！请检查！']);
		} else {
			return $this->send($mobile);
		}
    }
    
    public function send($mobile){
    	if(verifCode($mobile)){//验证用户名(手机)
				//$code =mt_rand(11,99).mt_rand(11,99).mt_rand(11,99);
				$code='123456';
				$config=[
			        'SingnName'=> 'Gogo购购网',
			        'code'     => $code,
			        'product'  =>'Gogo海关申报系统',
			        'tel'      => $mobile,
			        'TemplateCode'=>'SMS_35030091'
			    ];
				//sendSms($config);
				Session::set("yzm",$code);
	        	return json(['code'=>1,'msg'=>'发送成功']);
			}else{
				return json(array('code'=>0,'msg'=>'手机格式错误'));
			}
    }
    
   //退出登录
   public function logout()
   {
   		session('plats',null);
   		//Session::set('admin',null);
   		return json(['code'=>1,'msg'=>'退出成功']);
   }
   
   	
	
}
?>