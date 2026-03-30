<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
class Login_EweiShopV2Page extends MobileLoginPage
{
	//主页显示
	public function main()
	{
		global $_W;
		global $_GPC;
		
//		echo '<pre>';print_r($_W['fans']);
//		print_r($_W['fans']['tag']['avatar']);
//		die;
		$openid = $_W['openid'];//用户Openid
		$uniacid = $_W['uniacid'];//当前公众号ID

		include $this->template('housing/login');
	}
	
	//验证身份
	public function Receive(){
		global $_W;
		global $_GPC;
		if(!empty($_GPC)){
			$user = '';
			if ($_GPC['username'] != '') {

				$user = pdo_get('Insurance',array('name'=>$_GPC['username']),'*');
				if(empty($user)) {
					echo "<script> alert('您输入的姓名不存在！请重新输入'); </script>";
					echo "<script> window.history.go(-1); </script>";
				}else{
					echo "<script> alert('恭喜您！验证通过'); </script>";
				}
				
			} else if($_GPC['cardID'] != '') {				
				$user = pdo_get('Insurance',array('num'=>$_GPC['cardID']),'*');
				if(empty($user)) {
					echo "<script> alert('您输入的身份证号码不存在！请重新输入'); </script>";
					echo "<script> window.history.go(-1); </script>";
				}else{
					echo "<script> alert('恭喜您！验证通过'); </script>";
				}
				
			} else if($_GPC['mobile'] != '') {
				$user = pdo_get('Insurance',array('mobile'=>$_GPC['mobile']),'*');
				if(empty($user)) {
					echo "<script> alert('您输入的手机号码不存在！请重新输入'); </script>";
					echo "<script> window.history.go(-1); </script>";
				}else{
					echo "<script> alert('恭喜您！验证通过'); </script>";
				}
			}
			
//			print_r($user);
			include $this->template('housing/user');
			
		}else{
			echo "未接收到数据";
		}
	}

	//点击我很随缘
	public function Verifi(){
		global $_W;
		global $_GPC;
		if ($_GPC['username'] != '') {
			$userinfo = pdo_get('Insurance',array('name'=>$_GPC['username']),'sex');
								
			$pipei = pdo_getall('Insurance',array('sex' => $userinfo['sex']),'*');
			
			$count = count($pipei);
			$index = rand(1, $count);
			if(!empty($pipei) && $pipei[$index]['name'] != ''){
				
				$info = array(
					'sex' => $pipei[$index]['sex'],
					'name' => $pipei[$index]['name'],
					'id' => $pipei[$index]['id'],
				);
				
				echo json_encode($info);
			}	
		}
	}
	//点击确认匹配
	public function pipei(){
		global $_W;
		global $_GPC;
		if($_W['ispost']){
//			echo "恭喜！您与".$_GPC['pipeiname'].'住宿匹配成功！';
			$users = $_GPC['pipeiname'];
			print_r($_GPC);
		}
		
		include $this->template('housing/success');
	}
	
	public function Yaoqiu(){
		
	}

}



?>