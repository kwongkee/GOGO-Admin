<?php
namespace app\usersinfo\controller;
use think\Controller;
use Util\data\Sysdb;
use think\Session;
use think\Request;
use think\Loader;
use think\log;

/**
 * 接收消息控制器
 */
class Notify extends Controller
{
	public $pkey;
	public $admin;//用户数据
	public function __construct()
	{
		parent::__construct();
		//实例化数据库
		$this->db = new Sysdb;
		$this->admin = session('admin');//登录数据信息
//		$this->pkey = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100d8559af15dfe44bf2144ded0933a988cd80da94a2710e2a171d3da2ae731d757ce4e36815f7cecafba37f73898c7f7607117035ce2af171229347c31bd76124abe127cb1729da9fa97c84e5f3ee5b06973bf22e1cb1ff544060f96a3191faaadf4935aaa55660b697f4472d8eeca26c3055221dc99cb7e0bf506a5bc7100ec673ca155e6c596a42e28fde3775cde8de2d1edc15c045a6b59a40643d06e4c1fe3620f281c87daac09005ec5d410b6dedae0437beb9f13a11b4bddc2cb466db6fe7f9ec3d134266229deb958d9ef0f46271d505fb67ed83f83987d0ecd3ca0fd6774b4222cbc5d66e58f896a3bd1419e91b32655d2e0ff264697a9de3ca23885ef0203010001';
//		$this->pkey = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100cc26d29f5f95612b8557bc3db217c2d67022595943f2648006b76863d6770b0ee5e627cf279c17df75e188f5f270092d376fed846c470cb05e10fa7688180afbf807f5165cfae82a732ff5a3f63e4b43292720ef16039af2be7c2ebfc8f49c40f835f3da50d8934011d42497c9684134e52f9396c60e038bed33cb8734c58114f825e4a51e533871b7a514afae20af045a0a53099418ec6a7a8c3a4ea0d858ba1f5b2efa9a05c9539661941f6ab76f171c2febb0b68b6fb26bd5da885c5e857f700605b4dfd8525924ba58e99560aa7403e77c5e1e82a6ddd38b18eb92369abe6747d7efcb2177cda5093f77400f647aebf529f45ae81d228adbbcd3793c4b9f0203010001';
		//正式账号
//		$this->pkey = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100cc26d29f5f95612b8557bc3db217c2d67022595943f2648006b76863d6770b0ee5e627cf279c17df75e188f5f270092d376fed846c470cb05e10fa7688180afbf807f5165cfae82a732ff5a3f63e4b43292720ef16039af2be7c2ebfc8f49c40f835f3da50d8934011d42497c9684134e52f9396c60e038bed33cb8734c58114f825e4a51e533871b7a514afae20af045a0a53099418ec6a7a8c3a4ea0d858ba1f5b2efa9a05c9539661941f6ab76f171c2febb0b68b6fb26bd5da885c5e857f700605b4dfd8525924ba58e99560aa7403e77c5e1e82a6ddd38b18eb92369abe6747d7efcb2177cda5093f77400f647aebf529f45ae81d228adbbcd3793c4b9f0203010001';
		
		$this->partnerId = '10000002473';
		$this->pkey 	 = '30820122300d06092a864886f70d01010105000382010f003082010a0282010100ad710bc122ce76cad43df144049fba2cfee73f8625ef1505a22bceec20072792b52efb12e372dad11e1406afd52c5fad80dbbfbb0c6db60eb4a93b48f70be09344ef2e1ecfcaddb0aa87bd8e7c3d2e73a5db783a8728c3c4fb78e709ce31ca11aa571a09f371002a01fa94ffa1213b2ef16b74204c26cedf15e86747a0abb5bcf6b7a445d0f99d548d553f81efe4c0a9eec853b343ecdb11283f392a6025edd8d1355c791fbc667b22c04e7fb2c719fdef2da27cf3d7e424eda84d552cfc9f566c414aee471010edda4914280e614aa2a36fc11b15cfdc44c346de9fc235fbe647cbc0509b08fd281a94e660db06a8881bac0bb5d341d697cd40bc00ee1011130203010001';
		
	}
	
	
	//人民币收银台  后台回调地址 
	public function noticeUrl()
	{
		$this->db = new Sysdb;
		$postInfo = $_POST;
		file_put_contents('./paylog/notify/noticeUrl.txt', print_r($postInfo,TRUE),FILE_APPEND);
		$signStr = $this->Splicing($postInfo);
		//数据加密
		$signMsg = $signStr."&pkey=".$this->pkey;
		$signMsg = md5($signMsg);
		
		if($signMsg == $postInfo['signMsg'] && $postInfo['resultCode'] == '1004') 
		{
			$ordersn = $postInfo['orderId'];//订单编号
			//查询数据
			$res = $this->db->table('foll_payment_nativepay')->where(['orderId'=>$ordersn])->item();
			if(!empty($res) && $res['payStatus'] == '1004'){
				return 200;
				exit;
			} else {
//				try{
					//开启事务 startTrans
//					$this->db->startTrans();
						$update = [
							'payStatus'=>$postInfo['resultCode'],//结果状态码
							'resultMsg'=>$postInfo['resultMsg'],//支付结果
							'pay_time'=>$postInfo['completeTime'],//支付完成时间
							'dealId'=>$postInfo['dealId'],//上级支付流水号
						];
						$res = $this->db->table('foll_payment_nativepay')->where(['orderId'=>$ordersn])->update($update);
						//事务提交 commit()
//					$this->db->commit();
//				}catch(\Exception $e){
//					//事务回滚
//					$this->db->rollback();
//				}
				return 200;
			}
			
		} else {
			echo 'error';
		}
	}
	
	//人民币收银台   商户通知地址 前端
	public function returnUrl() 
	{
		$this->db = new Sysdb;
		$postInfo = $_POST;
		file_put_contents('./paylog/notify/returnUrl.txt', print_r($postInfo,TRUE),FILE_APPEND);
		$signStr = $this->Splicing($postInfo);
		//数据加密
		$signMsg = $signStr."&pkey=".$this->pkey;
		$signMsg = md5($signMsg);
	
		if( $signMsg == $postInfo['signMsg'] && $postInfo['resultCode'] == '1004') {
			echo "<h2 style='color:green;'>".$postInfo['resultMsg']."</h2>";
			return 200;
		} else {
			echo "<h2 style='color:red;'>交易失败</h2>";
		}
	}
	
	
	//报关回调通知地址   支付明细后台回调
	public function DetailNotify()
	{
		file_put_contents("./paylog/notify/DetailNotify.txt", print_r($_POST,true)."\r\n",FILE_APPEND);
		$info = $_POST;
		if(is_array($info) && ($info['resultCode'] == '0000') && ($info['resultMsg'] == '请求处理成功')) {
			
			$update = [
				//报关结果2：报关成功3：报关失败
				'chkMark'			=>$info['chkMark'],
				'completeTime'		=>$info['completeTime'],
				'resultCode'		=>$info['resultCode'],
				'resultMsg'			=>$info['resultMsg'],
				'payTransactionNo'	=>$info['payTransactionNo'],
				'partnerId'			=>$info['partnerId'],
			];
			//更新数据
			$this->db->table('foll_detail_list')->where(['ordersn'=>$info['orderId']])->update($update);
			
		}
		return 200;
	}
	
	
	//文件校验结果通知地址   支付明细前端回调
	public function fileNotifyUrl()
	{
//		echo '文件校验结果通知地址';
//		file_put_contents("./paylog/notify/fileNotifyUrl.txt", $_POST."\r\n",FILE_APPEND);
		file_put_contents("./paylog/notify/fileNotifyUrl.txt", print_r($_POST,true)."\r\n",FILE_APPEND);
		return 200;
	}
	
	
	
	/**
	 * 帮付宝  报关文件提交回调  2018-06-05
	 * 
	 */
	public function submitNotify()
	{
		$this->db = new Sysdb;
		$postInfo = $_POST;
		file_put_contents("./paylog/notify/submitNotifys.txt",json_encode($_POST)."\r\n",FILE_APPEND);
		
		if(empty($postInfo)) {
			return false;
		}
		
		$update = [
			'payTransactionNo'	=>	$postInfo['payTransactionNo'],//支付交易号
			'chkMark'			=>	$postInfo['chkMark'],//报关结果
			'failInfo'			=>	$postInfo['failInfo'],//报关失败信息
			'acquiringTime' 	=>	$postInfo['acquiringTime'],//收单时间
			'completeTime'		=>	$postInfo['completeTime'],//处理完成时间
			'resultCode'		=>	$postInfo['resultCode'],//处理结果码
			'resultMsg'			=>	$postInfo['resultMsg']//处理结果描述
		];
		
		$orderid = $postInfo['orderId'];//订单编号
		//回执订单表
		$ins = $this->db->table('foll_payment_old')->insert($postInfo);
		
		if(($postInfo['resultCode'] == '0000') && ($postInfo['resultMsg'] == '请求处理成功') && ($postInfo['chkMark'] == '2'))
		{
			//查询数据
			$res = $this->db->table('foll_payment_order')->where(['orderId'=>$orderid])->item();
			if(!empty($res) && $res['resultCode'] == '0000') {
				
//				return true;
				exit();
			} else {//支付成功
				
				try{
					//开启事务 startTrans
					$this->db->startTranss();
						
						$Rupdate = $this->db->table('foll_payment_order')->where(['orderId'=>$orderid])->update($update);
//						if($Rupdate)
//						{
							$url = 'http://shop.gogo198.cn/foll/public/?s=ordercustoms/sendElcOrder';
							$this->postJson($url,$postInfo);
							//更新成功发送数据给阿新
//						}
						
					//事务提交 commits()
					$this->db->commits();
				}catch(Exception $e){
//					//事务回滚
					$this->db->rollbacks();
				}
//				return true;
			}
			
		} else {//支付失败
			
			try{
				//开启事务 startTrans
				$this->db->startTranss();
					
					$Rupdate = $this->db->table('foll_payment_order')->where(['orderId'=>$orderid])->update($update);
					
					//if($Rupdate)
					//{
						$url = 'http://shop.gogo198.cn/foll/public/?s=ordercustoms/sendElcOrder';
						$this->postJson($url,$postInfo);
						//更新成功发送数据给阿新
					//}
					
				//事务提交 commits()
				$this->db->commits();
			}catch(Exception $e){
				//事务回滚
				$this->db->rollbacks();
			}
//			return false;
		}
		
	}
	
	
	//数据拼接
	public function Splicing($val)
	{
		$str = '';
		ksort($val);
		if(is_array($val))
		{
			foreach($val as $k=>$v)
			{
				if(trim($v) !== '' && $k !== 'signMsg')
				{
					$str.= $k.'='.$v.'&';
				}
			}
		}
		return substr($str,0,-1);
	}
	
	//curl post 请求
	public function submitPost($url,$param)
	{
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
			$result = curl_exec($ch);
			curl_close($ch);
//			return $result;//返回结果
			echo $result;
		}catch(Exception $e){
			file_put_contents('./paylog/MyError.txt', print_r($e,TRUE),FILE_APPEND);
		}
	}
	
	//post 请求
    public function doPost($url,$post_data)
    {
    	$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        // 执行后不直接打印出来
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        // 设置请求方式为post
        curl_setopt($ch,CURLOPT_POST,true);
        // post的变量
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    
    //提交Json数据 2018-06-08
	public function postJson($url,$dataArr = null)
	{
		$data_string = json_encode($dataArr);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		    'Content-Type:application/json;charset=utf-8',
		    'Content-Length:'.strlen($data_string))
		);
		
		$result = curl_exec($ch);
		curl_close($curl);
		return $result;
	}
}
