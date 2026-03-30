<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}
class Index_EweiShopV2Page extends Page{
	public $name='';
	public $documents='';
	public $date='';
	public $mobile='';
	public $sex='';
	public $type='';
	public $info= array();
	public function main() {
		echo 'asdfb';die;
		global $_W;
		global $_GPC;
		if($_W['isajax']){
		$this->name=$_GPC['name'];
		$this->documents=$_GPC['documents'];
		$this->date=substr($_GPC['documents'],6,8);
		$this->mobile=$_GPC['mobile'];
		$this->sex=$_GPC['sex'];
		if(!$this->verifyIsset()){
			echo json_encode($this->info);
			exit();
		}else{
			$this->verify();
			}
		}else{
			include $this->template('Insurance/index');
		}
	}
	public function verify(){
		if(!$this->VerifyMobile()){
			echo json_encode($this->info);
			exit();
		}
		if(!$this->validateIdCard()){
			$this->info['type']='1';
			$this->info['error']='身份证号码格式不对';
			echo json_encode($this->info);
			exit();
		}
		if($this->save()){
			$this->info['type']=0;
			$this->info['sourrce']='成功受理,稍后通知';
			echo json_encode($this->info);
		}else{
			echo json_encode($this->info);
		}

	}

public function verifyIsset(){
	$data = pdo_fetch("SELECT * FROM ".tablename('Insurance')."WHERE num=:num",array(':num'=>$this->documents));
	// var_dump($data);
	if(empty($data)){
		return true;
	}else{
		$this->info['type']='1';
		$this->info['error']='已投保过';
		return false;
	}
}
	public function save(){
		global $_W;
		global $_GPC;
		$data=array(
		'name'=>$this->name,
		'type'=>'1',
		'num'=>$this->documents,
		'age'=>$this->date,
		'sex'=>$this->sex,
		'mobile'=>$this->mobile,
		'createtime'=>time(),	
		'uniacid'=>$_W['uniacid'],
		'openid'=>$_W['openid']
		);
		$bol=pdo_insert('Insurance',$data);
		if(empty($bol)){
			$this->info['type']='1';
			$this->info['error']='注册失败';
			return false;
		}
		return true;
	}
	//验证手机
	public function VerifyMobile(){
		if(preg_match('/^0?(13|14|15|17|18)[0-9]{9}$/', $this->mobile)){
			return true;
		}else{
			$this->info['type']='1';
			$this->info['error']='手机号码不对';
			return false;
		}
	}

// // 验证身份证
	protected function validateIdCard(){
    if (!preg_match('/^\d{17}[0-9xX]$/', $this->documents)) { //基本格式校验
    	$this->info['type']='1';
		$this->info['error']='身份证号码格式不对';

        return false;
    }
    $base = substr($this->documents, 0, 17);
 
    $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
    $tokens = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
    $checkSum = 0;
    for ($i=0; $i<17; $i++) {
        $checkSum += intval(substr($base, $i, 1)) * $factor[$i];
    }
    $mod = $checkSum % 11;
    $token = $tokens[$mod];
 
    $lastChar = strtoupper(substr($this->documents, 17, 1));
 
    return ($lastChar === $token); //最后一位校验位校验
	}
}
