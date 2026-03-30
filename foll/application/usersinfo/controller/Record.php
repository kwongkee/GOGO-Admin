<?php
namespace app\usersinfo\controller;
use app\usersinfo\controller\BaseAdmin;
use think\Controller;

class Record extends BaseAdmin
{
	public function index()
	{
		$userinfo = $this->_admin;
		//$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		//所有人都能看到   只有管理员能改
		$follinfo = $this->db->table('foll_cross_border')->order('id asc')->item();
		if(isset($follinfo['platforms'])) {
			$info = json_decode($follinfo['platforms'],true);
			$this->assign('info',$info);
		}
		$this->assign('role',$userinfo['role']);
		return $this->fetch();
	}
	
	//电商平台信息保存
	public function platformsave()
	{
		$userinfo = $this->_admin;//用户数据
		$info = $_POST['domain'];//表单数据
		foreach($info as $key=>$val) 
		{
			if(trim($val) == '')
			{
				exit(json_encode(['code'=>0,'msg'=>'表单信息不能为空,请检查!']));
			}
			$newInfo[$key] = trim($val);
		}
		
		$platforms['uid']       =  $userinfo['id'];
		$platforms['platforms'] = json_encode($newInfo);
		
		if($userinfo['role'] == 1) {
			//查询表中是否存在该用户数据
			$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
			if(!$follinfo){//如果没有该用户信息就写入
				$res = $this->db->table('foll_cross_border')->insert($platforms);
			} else {
				$res = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->update($platforms);
			}
			
			if(!$res){
				exit(json_encode(['code'=>0,'msg'=>'保存失败']));
			}
			exit(json_encode(['code'=>1,'msg'=>'保存成功']));
			
		} else {
			exit(json_encode(['code'=>0,'msg'=>'您不是管理员']));
		}
	}
	
	
	//主体信息
	public function subjects()
	{
		$userinfo = $this->_admin;
		$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		if(isset($follinfo['subject'])){
			$info = json_decode($follinfo['subject'],true);
			$this->assign('info',$info);
		}
		return $this->fetch();
	}
	
	//主体信息保存
	public function subjectsSave()
	{
		$userinfo = $this->_admin;//用户数据
		$info = $_POST['domain'];//表单数据
		foreach($info as $key=>$val) 
		{
			if(trim($val) == '')
			{
				exit(json_encode(['code'=>0,'msg'=>'表单信息不能为空,请检查!']));
			}
			$newInfo[$key] = trim($val);
		}
		//2018-05-21  需修改
		$newInfo['cpnum']   = '91440605782023871F';			 //电商企业编号
		$newInfo['cpname']  = '佛山市钜铭商务资讯服务有限公司';//电商企业名称
		$newInfo['cpshop']  = '粤B2-20120011';				 //电商平台企业编号
		$newInfo['electronicBusinessPlatfor'] = '购购网';	 //电商平台名称
		//2018-05-21
		
		$subject['uid']     = $userinfo['id'];
		$subject['subject'] = json_encode($newInfo);
		
		//查询表中是否存在该用户数据
		$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		if(!$follinfo){//如果没有该用户信息就写入
			$res = $this->db->table('foll_cross_border')->insert($subject);
		} else {
			$res = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->update($subject);
		}
		
		if(!$res){
			exit(json_encode(['code'=>0,'msg'=>'保存失败']));
		}
		exit(json_encode(['code'=>1,'msg'=>'保存成功']));
	}
	
	
	
	//报文信息  支付企业信息
	public function messages()
	{
		$userinfo = $this->_admin;
		//$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		$follinfo = $this->db->table('foll_cross_border')->item();
		if(isset($follinfo['message'])){
			$info = json_decode($follinfo['message'],true);
			$this->assign('info',$info);
		}
		$this->assign('role',$userinfo['role']);
		return $this->fetch();
	}
	
	
	//报文信息保存   支付企业保存
	public function messagesSave()
	{
		$userinfo = $this->_admin;//用户数据
		$info = $_POST['domain'];//表单数据
		foreach($info as $key=>$val) 
		{
			$newInfo[$key] = trim($val);
			if(trim($val) == '')
			{
				exit(json_encode(['code'=>0,'msg'=>'表单信息不能为空,请检查!']));
			}
		}
		
		//$subject['uid'] = $userinfo['id'];
		$subject['message'] = json_encode($newInfo);
		if($userinfo['role'] == 1){
			//查询表中是否存在该用户数据
			$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
			if(!$follinfo){//如果没有该用户信息就写入
				$res = $this->db->table('foll_cross_border')->insert($subject);
			} else {
				$res = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->update($subject);
			}
			
			if(!$res){
				exit(json_encode(['code'=>0,'msg'=>'保存失败']));
			}
			exit(json_encode(['code'=>1,'msg'=>'保存成功']));
		} else {
			exit(json_encode(['code'=>0,'msg'=>'您不是管理员']));
		}		
	}
	
	//报关信息
	public function customs()
	{
		$userinfo = $this->_admin;
		$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		if(isset($follinfo['customs'])){
			$info = json_decode($follinfo['customs'],true);
			$this->assign('info',$info);
		}
		return $this->fetch();
	}
	
	//报关信息保存
	public function customsSave()
	{
		$userinfo = $this->_admin;//用户数据
		$info = $_POST['domain'];//表单数据
		foreach($info as $key=>$val) 
		{
			if(trim($val) == '')
			{
				exit(json_encode(['code'=>0,'msg'=>'表单信息不能为空,请检查!']));
			}
			$newInfo[$key] = trim($val);
		}
				
		$subject['uid']     = $userinfo['id'];
		$subject['customs'] = json_encode($newInfo);
		
		//查询表中是否存在该用户数据
		$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		if(!$follinfo){//如果没有该用户信息就写入
			$res = $this->db->table('foll_cross_border')->insert($subject);
		} else {
			$res = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->update($subject);
		}
		
		if(!$res){
			exit(json_encode(['code'=>0,'msg'=>'保存失败']));
		}
		exit(json_encode(['code'=>1,'msg'=>'保存成功']));
	}
	
	
	//检验检疫
	public function inspection()
	{
		$userinfo = $this->_admin;
		$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		if(isset($follinfo['inspection'])){
			$info = json_decode($follinfo['inspection'],true);
			$this->assign('info',$info);
		}
		return $this->fetch();
	}
	//检验检疫保存
	public function inspectionSave()
	{
		$userinfo = $this->_admin;//用户数据
		$info = $_POST['domain'];//表单数据
		foreach($info as $key=>$val) 
		{
			if(trim($val) == '')
			{
				exit(json_encode(['code'=>0,'msg'=>'表单信息不能为空,请检查!']));
			}
			$newInfo[$key] = trim($val);
		}
				
		$subject['uid']     = $userinfo['id'];
		$subject['inspection'] = json_encode($newInfo);
		
		//查询表中是否存在该用户数据
		$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		if(!$follinfo){//如果没有该用户信息就写入
			$res = $this->db->table('foll_cross_border')->insert($subject);
		} else {
			$res = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->update($subject);
		}
		
		if(!$res){
			exit(json_encode(['code'=>0,'msg'=>'保存失败']));
		}
		exit(json_encode(['code'=>1,'msg'=>'保存成功']));
	}
	
	//支付配置
	public function payConfig()
	{
		$userinfo = $this->_admin;
		$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		if(isset($follinfo['payconfig'])){
			$info = json_decode($follinfo['payconfig'],true);
			$this->assign('info',$info);
		}
		return $this->fetch();
	}
	//支付配置保存
	public function payConfigSave()
	{
				$userinfo = $this->_admin;//用户数据
		$info = $_POST['domain'];//表单数据
		foreach($info as $key=>$val) 
		{
			if(trim($val) == '')
			{
				exit(json_encode(['code'=>0,'msg'=>'表单信息不能为空,请检查!']));
			}
			$newInfo[$key] = trim($val);
		}
				
		$subject['uid']     = $userinfo['id'];
		$subject['payconfig'] = json_encode($newInfo);
		
		//查询表中是否存在该用户数据
		$follinfo = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->item();
		if(!$follinfo){//如果没有该用户信息就写入
			$res = $this->db->table('foll_cross_border')->insert($subject);
		} else {
			$res = $this->db->table('foll_cross_border')->where(['uid'=>$userinfo['id']])->update($subject);
		}
		
		if(!$res){
			exit(json_encode(['code'=>0,'msg'=>'保存失败']));
		}
		exit(json_encode(['code'=>1,'msg'=>'保存成功']));
	}
}

?>