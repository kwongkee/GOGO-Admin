<?php
namespace app\payments\controller;
use think\Controller;
use Util\data\Sysdb;
use Util\data\Redis;
use think\Session;
use think\Sms;
use think\Request;
use think\Loader;
use Util\data\TgPay;
// 支付API
class PaymentsApi extends Controller
{
	private $redis;
	private $rds;
	public function __construct() {
		$config = [
			'host'	=> '127.0.0.1',
			'port'	=> '6379',
			'auth'	=> '123456',
		];
		$attr = [
			//连接超时时间，redis配置文件中默认为300秒
			'timeout'=>300,
			//选择数据库
			'db_id'=>8,
		];
		
		//实例化 Redis 缓存类
		//$this->redis = Redis::getINstance($config,$attr);
		//$this->rds = $this->redis->getRedis();
		//实例化数据库
		$this->db = new Sysdb;
	}
	
	// 支付入口
	public function pay() {
		// 接收文件流
		$inputs = file_get_contents("php://input");
		$data   = json_decode($inputs,true);
		if(empty($data)) {
			return json(['status'=>'101','msg'=>'请求数据不能为空','info'=>[]]);
			exit;
		}
		// 参数配置 如果传入的参数不在这个范围内，提示参数错误
		$dataConfig = ['uniacId','payMoney','orderSn','openId','payType','returnUrl','notifyUrl','body'];
		foreach($data as $key=>$v){
			if(!in_array($key,$dataConfig)){
				return json(['status'=>'101','msg'=>'参数错误','info'=>[]]);
				break;
			}
		}
		// 数据验证
		if(empty($data['uniacId'])){
			return json(['status'=>'101','msg'=>'配置参数不能为空!','info'=>[]]);
		}
		
		if(empty($data['payMoney'])){
			return json(['status'=>'101','msg'=>'交易金额不能为空!','info'=>[]]);
		}
		
		if(empty($data['orderSn'])){
			return json(['status'=>'101','msg'=>'订单编号不能为空!','info'=>[]]);
		}
		
		if(strlen($data['orderSn']) > 33){
			return json(['status'=>'101','msg'=>'订单编号长度不能大于32位!','info'=>[]]);
		}
		
		if(empty($data['payType'])){
			return json(['status'=>'101','msg'=>'支付类型不能为空!','info'=>[]]);
		}
		
		if(empty($data['body'])){
			return json(['status'=>'101','msg'=>'商品描述不能为空!','info'=>[]]);
		}
		
		if(empty($data['notifyUrl'])){
			return json(['status'=>'101','msg'=>'后台回调不能为空!','info'=>[]]);
		}
		//    聚合微信支付 支付宝支付  微信扫码    支付宝扫码
		$payType = ['tgWx','tgAlipay','tgWxScan','tgAliScan'];
		if(!in_array($data['payType'],$payType)){
			return json(['status'=>'101','msg'=>'支付类型错误!','info'=>[]]);
		}
		
		$config = $this->db->table('payments_config')->where(['uniacId'=>$data['uniacId']])->item();
		if(empty($config)){
			return json(['status'=>'101','msg'=>'支付配置为空，请填写!','info'=>[]]);
		}
		
		// 分流执行
		switch($data['payType']) {
			case 'tgWxScan':
			case 'tgAlipay':
			case 'tgAliScan':
			case 'tgWx':
				$tgWx = json_decode($config['TgPay'],true);
				if(empty($tgWx)){
					return json(['status'=>'101','msg'=>'支付通道配置为空!','info'=>[]]);
				}
				// 聚合支付实例化 单例模式
				$tgWxs = TgPay::instance();
				$payConfig = [
					'account' =>$tgWx['mach_id'],
					'key'	  =>$tgWx['mach_pwd'],
					'appId'	  =>$tgWx['appId'],
				];
			break;
			case 'WxPay':
				
			break;
			case 'UnPay':
				
			break;
			case 'SdPay':
				
			break;	
		}



		
		// 组装数据
		// 唯一订单号
		$orderId = 'GOGO'.date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
		// uniacid ordersn orderid uporderid paymoney openid paytype returnurl notifyurl paystatus refstatus paytime createtime
		$InsData['uniacid']  = $data['uniacId'];
		$InsData['ordersn']  = $data['orderSn'];
		$InsData['orderid']  = $orderId;
		$InsData['paymoney'] = $data['payMoney'];
		$InsData['openid']   = (isset($data['openId']) ? $data['openId']:' ');
		$InsData['paytype']  = $data['payType'];
		$InsData['returnurl']= (isset($data['returnUrl']) ? $data['returnUrl']:' ');
		$InsData['notifyurl']= $data['notifyUrl'];
		$InsData['body']	 = $data['body'];
		$InsData['createtime'] = date('Y-m-d H:i:s',time());
		$InsData['c_time']	   = time();
		$this->db->table('payments_ordersn')->insert($InsData);
		// 把数据写入库中，
		$InsData['config'] = $payConfig;
		// 分流执行
		switch($data['payType']) {
			case 'tgWx':
				$res = $tgWxs->tgWx($InsData);
				return json(['status'=>'100','msg'=>'成功','info'=>$res]);
			break;
			
			case 'tgWxScan':// 微信扫码
				$res = $tgWxs->tgWxScan($InsData);
                if($res['status'] == '100') {
					$rds = ['codeUrl'=>$res['codeUrl'],'lowOrderId'=>$res['lowOrderId']];
					return json(['status'=>$res['status'],'msg'=>$res['message'],'info'=>$rds]);
				}
				return json(['status'=>$res['status'],'msg'=>$res['message'],'info'=>$res]);
			break;
			
			case 'tgAlipay':// 支付
				$res = $tgWxs->tgAlipay($InsData);
				return json(['status'=>'100','msg'=>'成功','info'=>$res]);
			break;
			
			case 'tgAliScan': // 支付扫码
				$res = $tgWxs->tgAliScan($InsData);
				if($res['status'] == '100') {
					$rds = ['codeUrl'=>$res['codeUrl'],'lowOrderId'=>$res['lowOrderId']];
					return json(['status'=>$res['status'],'msg'=>$res['message'],'info'=>$rds]);
				}
				return json(['status'=>$res['status'],'msg'=>$res['message'],'info'=>$res]);
			break;
			
			case 'tgWx':
				
			break;
			case 'tgWx':
				
			break;
			case 'tgWx':
				
			break;
			case 'tgWx':
				
			break;			
		}
	}
	
	
	
	// 退款接口
	public function Refund(){
		// 实例化
		$tgWxs = TgPay::instance();
		
		// 接收文件流
		$inputs = file_get_contents("php://input");
		$data   = json_decode($inputs,true);
		if(empty($data)) {
			return json(['status'=>'101','msg'=>'请求数据不能为空','info'=>[]]);
			exit;
		}
		
		$config = $this->db->table('payments_config')->where(['uniacId'=>$data['uniacId']])->item();
		if(empty($config)){
			return json(['status'=>'101','msg'=>'支付配置为空，请填写!','info'=>[]]);
		}
		
		$tgWx = json_decode($config['TgPay'],true);
		if(empty($tgWx)){
			return json(['status'=>'101','msg'=>'支付通道配置为空!','info'=>[]]);
		}
		
		$payConfig = [
			'account' =>$tgWx['mach_id'],
			'key'	  =>$tgWx['mach_pwd']
		];
		$InsData['uniacid']     = $data['uniacId'];
		$InsData['upOrderId']   = $data['upOrderId'];
		$InsData['refundMoney'] = $data['refundMoney'];
		$InsData['config']		= $payConfig;
		$res = $tgWxs->Refund($InsData);
		print_r($res);
	}
	
	
	
	
	// 队列
	private function QEuery(){
		
	}
}
?>