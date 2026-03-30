<?php
namespace app\declares\controller;
use think\Controller;
use Util\data\Sysdb;
/**
 * 管理员管理
 */
class Admins extends BaseAdmin
{
	//管理员列表
	public function index()
	{
		$admins = $this->_admin;//Sisson数据
		$data['role'] = $admins['role'];
		$data['lists'] = $this->db->table('foll_business_admin')->lists();
		$data['groups'] = $this->db->table('foll_business_groups')->cates('gid');
		$this->assign('data',$data);
		return $this->fetch();
	}
	// 添加管理员
	public function add() {
		$id = (int)input('get.id');
		// 添加管理员
		$data['item'] = $this->db->table('foll_business_admin')->where(['id'=>$id])->item();
		$settle = $this->db->table('foll_business_settlement')->where(['uid'=>$id])->item();
		$data['items'] = $settle;
		$data['cserMoney'] 	   = $settle['cserMothod'] == 'Percentage' ? ($settle['cserMoney']*100) : $settle['cserMoney'];
		//$data['pserMoney'] 	   = $settle['pserMothod'] == 'Percentage' ? ($settle['pserMoney']*100) : $settle['pserMoney'];//平台应付(服务费用)
		$data['authCopyMoney'] 	   = ($settle['authCopyMoney']);
		//$data['authPtaiMoney'] 	   = ($settle['authPtaiMoney']*100);//平台应收(验核费用)
		$data['payCopyMoney'] 	   = ($settle['payCopyMoney']*100);
		//$data['payPtaiMoney'] 	   = ($settle['payPtaiMoney']*100);//平台应收(交易费用)
		// 加载角色
		$data['groups'] = $this->db->table('foll_business_groups')->cates('gid');
		$this->assign('data',$data);
		return $this->fetch();
	}
	
	// 保存管理员
	public function save() {

		$id 				= (int)input('post.id');//用户ID
		$data['user_mobile']= trim(input('post.user_mobile'));//用户名
		$data['role'] 		= (int)input('post.gid');//权限
		$data['user_name']  = trim(input('post.user_name'));//真实姓名
		$data['user_email'] = trim(input('post.user_email'));//电子邮箱
		$data['status'] 	= (int)(input('post.status'));//状态
        $data['uniacid'] 	= (int)(input('post.uniacid'));//状态
		/**
		 * 数据存为用户结算费用标准表
		 * foll_business_settlement  商户结算表
		 */
		$Fee['busType'] 	  = trim(input('post.busType'));//业务类型
		$Fee['setMothod'] 	  = trim(input('post.setMothod'));//结算方式：
		$Fee['banMoney'] 	  = trim(input('post.banMoney'));//备案结算金额
		$Fee['cserMothod'] 	  = $cser = trim(input('post.cserMothod'));//企业应付（结算方式)
		$Fee['cserMoney'] 	  = $cser == 'Percentage' ? (trim(input('post.cserMoney'))/100) : trim(input('post.cserMoney'));//企业服务结算（金额）
		$Fee['pserMothod'] 	  = $pser = trim(input('post.pserMothod'));//平台应付(结算方式)
		//$Fee['pserMoney'] 	  = $pser == 'Percentage' ? (trim(input('post.pserMoney'))/100) : trim(input('post.pserMoney'));;//平台服务结算金额
		$Fee['authCopyMoney'] = (trim(input('post.authCopyMoney')));//企业应收(验核费用)
		//$Fee['authPtaiMoney'] = (trim(input('post.authPtaiMoney'))/100);//平台应收(验核费用)
		$Fee['payCopyMoney']  = (trim(input('post.payCopyMoney'))/100);//企业应收(交易费用)
		//$Fee['payPtaiMoney']  = (trim(input('post.payPtaiMoney'))/100);//平台应收(交易费用)

		if(!$data['user_mobile']){
			exit(json_encode(['code'=>1,'msg'=>'手机号码不能为空']));
		}
		if(!$data['role']){
			exit(json_encode(['code'=>1,'msg'=>'角色不能为空']));
		}

		if(!$data['user_name']){
			exit(json_encode(['code'=>1,'msg'=>'姓名不能为空']));
		}

		$res = true;
		if($id == 0) {
			$item = $this->db->table('foll_business_admin')->where(['user_mobile'=>$data['user_mobile']])->item();
			if($item){
				exit(json_encode(['code'=>1,'msg'=>'该用户已存在']));
			}

			$data['order_prix']	= strtoupper(input('post.order_prix'));
			if(!empty($this->isCommpanyId($data['order_prix']))){
				return json(['code'=>1,'msg'=>'企业自编号重复']);
			}
			$data['create_time'] = date("Y-m-d H:i:s",time());//添加时间
			// 保存用户到数据库；
			$resid = $this->db->table('foll_business_admin')->insert($data);
			//添加结算数据
			$Fee['uid'] 	  = $resid;
			$Fee['add_time']  = date('Y-m-d H:i:s',time());
			$this->db->table('foll_business_settlement')->insert($Fee);
			
		} else {
			
			$data['order_prix']	= strtoupper(input('post.order_prix'));
			$this->db->table('foll_business_admin')->where(['id'=>$id])->update($data);
			
			$items = $this->db->table('foll_business_settlement')->where(['uid'=>$id])->item();
			if(!$items){
				//添加结算数据
				$Fee['uid'] 	  = $id;
				$Fee['add_time']  = date('Y-m-d H:i:s',time());
				$this->db->table('foll_business_settlement')->insert($Fee);
			} else {
				//修改结算数据
				$Fee['update_time']  = date('Y-m-d H:i:s',time());
				$this->db->table('foll_business_settlement')->where(['uid'=>$id])->update($Fee);
			}		
		}
		if(!$res){
			exit(json_encode(['code'=>1,'msg'=>'保存失败']));
		}
		exit(json_encode(['code'=>0,'msg'=>'保存成功']));
	}


	public function isCommpanyId($cid){
		return $this->db->table('foll_business_admin')->where(['order_prix'=>$cid])->item();
	}

	// 删除管理员
	public function delete(){
		$id = (int)input('post.id');
		$res = $this->db->table('foll_business_admin')->where(['id'=>$id])->delete();
		  $this->db->table('foll_business_settlement')->where(['uid'=>$id])->delete();
		if(!$res){
			exit(json_encode(['code'=>1,'msg'=>'删除失败']));
		}

		exit(json_encode(['code'=>0,'msg'=>'删除成功']));
	}
	
	
	//管理平台配置  收费标准
	public function Charge()
	{
		$settlement = $this->db->table('foll_admin_settlement')->item();
		$settlement['pserMoney'] 		= $settlement['pserMothod'] == 'Percentage' ? ($settlement['pserMoney']*100) : $settlement['pserMoney']; 
		$settlement['authPtaiMoney']	= ($settlement['authPtaiMoney']*100);
		$settlement['payPtaiMoney']		= ($settlement['payPtaiMoney']*100);
		$this->assign('data',$settlement);
		return view('Charge');
		
		//$uid  = $this->_admin['id'];
		//echo $uid;
	}
	
	//保存管理设置  收费标准
	public function Chargesave()
	{
		
		$uid  = $this->_admin['id'];
		$info = $_POST['datas'];
		if($info['banMoney'] == '') {
			return json(['code'=>1,'msg'=>'备案金额不能为空!']);
		}
		
		if($info['pserMoney'] == '') {
			return json(['code'=>1,'msg'=>'服务费用金额不能为空!']);
		}
		
		if($info['authPtaiMoney'] == '') {
			return json(['code'=>1,'msg'=>'验核费用金额不能为空!']);
		}
		
		if($info['payPtaiMoney'] == '') {
			return json(['code'=>1,'msg'=>'交易费用金额不能为空!']);
		}
		
		$info['pserMoney'] 		= $info['pserMothod'] == 'Percentage' ? sprintf("%.2f",($info['pserMoney']/100)) : sprintf("%.2f",$info['pserMoney']); 
		$info['authPtaiMoney']	= sprintf("%.2f",($info['authPtaiMoney']/100));
		$info['payPtaiMoney']	= sprintf("%.2f",($info['payPtaiMoney']/100));
		
		if(isset($info['uid'])){//有数据就更新
			$info['u_time'] = date('Y-m-d H:i:s',time());
			$resid = $this->db->table('foll_admin_settlement')->where(['uid'=>$info['uid']])->update($info);
			
		} else {//没有数据就插入
			$info['uid']			= $uid;
			$info['user_mobile']	= $this->_admin['user_mobile'];
			$info['user_name']		= $this->_admin['user_name'];
			$info['user_email']		= $this->_admin['user_email'];
			$info['c_time'] = date('Y-m-d H:i:s',time());
			
			$resid = $this->db->table('foll_admin_settlement')->insert($info);
		}
		
		return json(['code'=>0,'msg'=>'保存成功!']);
	}
}
?>
