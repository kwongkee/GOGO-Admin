<?php
namespace app\index\controller;
use app\index\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Validate;
use think\Loader;
use extend\lib\phpqrcode;
class Advertising extends CommonController
{
	public function index(Request $request){
		$admin_data=Session::get('UserResutlt');//商户信息
		$where="business_id = ".$admin_data['id'];
		if($request->isPost()){
			$pay_status=$request->post('pay_status');
			if(!empty($pay_status)){
				$where.=' AND `pay_status` = '.$pay_status;
			}
		}else{
			$pay_status='0';
		}
		$config = [
			'path'=>'',
            'query'=>['s'=>'advertising/index'],
            'var_page'=>'page'
        ];
        $AdvertingModel=Loader::model('Advertising','model');
        $total=Db::table("ims_foll_advertising_order")->where($where)->count();
        
		$Pay_status=['pay_status'=>[0=>'未支付',1=>'已支付',2=>'支付失败']];//支付状态
		$order_data = Db::table('ims_foll_advertising_order')
		->where($where)
		->paginate(10,$total,$config);
//		print_r($order_data->toArray());die;
		return view('advertising/list',[
		'title'=>'订单管理',
		'data'=>$order_data->toArray()['data'],
		'page'=>$order_data->render(),
		'Pay_status'=>$Pay_status,
		'pay_status'=>$pay_status,
		]);
	}
	
	//添加广告
	public function add(){
		$AdvertingModel=Loader::model('Advertising','model');
		return view('advertising/add',[
		'title'=>'广告添加',
		'advertising_data'=>$AdvertingModel->Get_Data(),
		]);
	}
	
	public function selectData(Request $request){
		$id=$request->post('adv_id');
		$AdvertingModel=Loader::model('Advertising','model');
		$Type=['type'=>[0=>'',1=>'图片',2=>'图文',3=>'音频',4=>'视频']];
		$Way=['way'=>[0=>'',1=>'单播',2=>'轮播']];
		$data=$AdvertingModel->adv_one($id);
		$data['types']=$Type['type'][$data['type']];
		if($data['type']=='1'){//如果是图片类型
			$data['ways']=$Way['way'][$data['way']];
		}
//		print_r($Type['type'][$data['type']]);die;
		echo json_encode($data);
//		$data=array('id'=>1,'type'=>'1','tpyes'=>'图片');
	}
	
	public function save(Request $request){
		$AdvertingModel=Loader::model('Advertising','model');
		$admin_data=Session::get('UserResutlt');
		
		$validate = new Validate([
            'adv_id'=>'require',		
            's_time'=>'require',		
            'e_time'=>'require',	
        ],[
            'adv_id'=>'请选择展位',
            's_time'=>'请填写开始时间',
            'e_time'=>'请填写结束时间',
        ]);
        
		$data=[
			'ordersn'=>'GG99198'.$request->post('adv_id').$admin_data['id']. date('Ymdhis',time()) . mt_rand(11111,99999),
			'business_id'=>$admin_data['id'],
			'uniacid'=>$admin_data['uniacid'],
			'adv_id'=>$request->post('adv_id'),
			'adv_condition'=>$request->post('adv_condition'),//展位是否空闲
			'adv_money'=>$request->post('adv_money'),//展位基本费用
			'pay_money'=>$request->post('adv_money'),//展位基本费用
//			'adv_type'=>$request->post('adv_type'),//展位类型
//			'adv_way'=>$request->post('adv_way'),//展位播放方式,只有当类型为1 图片才有 1为单播,2为轮播
			's_time'=>strtotime($request->post('s_time')),
			'e_time'=>strtotime($request->post('e_time')),
			'pay_status'=>'0',//支付状态 0未支付，1已支付，2支付失败
			'is_invoice'=>'0',//是否开了发票 0未开发，1已开发，
			'create_time'=>time(),
		];
		
		if(!$validate->check($data)){
            $this->error($validate->getError());
        }
        
        if($AdvertingModel->add_data($data)){
        	//修改展位表的占用时间和状态为 被占用
        	$update = Db::table('ims_foll_advertising')
        	->where('id',$request->post('adv_id'))
        	->update([
        	's_time'=>strtotime($request->post('s_time')),
        	'e_time'=>strtotime($request->post('e_time')),
        	'condition'=>'2',
        	]);
        	if($update){
        		$this->success('添加成功',Url("Advertising/index"));
        	}
        }
	}
	public function invoice_list(){
		$AdvertingModel=Loader::model('Advertising','model');
		$Status=['status'=>[0=>'已作废',1=>'申请中',2=>'开票中',3=>'已开票',4=>'已寄送',5=>'拒绝申请']];
		return view('advertising/invoice_list',[
			'title'=>'订单发票列表',
			'data'=>$AdvertingModel->get_invoice(),
			'Status'=>$Status,
		]);
	}
	
	//发票申请
	public function invoice(Request $request){
		$AdvertingModel=Loader::model('Advertising','model');
		$order_id=$request->get('o_id');
		$order_ids=trim($order_id,',');
		$order=Db::query('SELECT * FROM ims_foll_advertising_order WHERE `id` in ('.$order_ids.')');
//		print_r($order);die;
		return view('advertising/invoice',[
		'title'=>'发票申请',
		'data'=>$order,
		'order_id'=>$order_ids,
		]);
	}
	
	/*
	各种状态代表的意思如下：

	申请中：正在等待开票（只有此状态下您可以申请发票作废）
	
	开票中：您的开票申请已经受理，请您耐心等待
	
	已开票：您的发票已经开出，如果您申请的是电子发票，可以在【详细】中下载
	
	已寄送：您的普票或者增值税专票已经为您寄出，点击【详细】可以查询快递号和快递公司
	
	已作废：您已经作废的发票列表
	
	已寄送：发票已经邮寄，可在发票详情中查看邮寄进度 
				
	*/
	
	public function invoice_apply(Request $request){
		$AdvertingModel=Loader::model('Advertising','model');
		$admin_data=Session::get('UserResutlt');
		$order_id = $request->post('order_id');
		$order=Db::table('ims_foll_advertising_order')
		->where('id','exp',' IN ('.$order_id.') ')
		->select();
		$price = 0;
		foreach($order as $v){
			$price +=$v['pay_money'];
		}
//		$order=Db::table('ims_foll_advertising_order')
//		->where(['id'=>$request->post('order_id')])
//		->find();
		$data=[
			'order_id'=>$request->post('order_id'),
			'type'=>$request->post('type'),
			'title'=>$request->post('title'),
			'price'=>$price,
			'business_id'=>$admin_data['id'],
			'tax_number'=>$request->post('tax_number'),
			'consignee'=>$request->post('consignee'),
			'address'=>$request->post('address'),
			'telephone'=>$request->post('telephone'),
			'remark'=>$request->post('remark'),
			'status'=>'1',//0已作废,1申请中,2开票中,3已开票，4已寄送,5拒绝申请
			'create_time'=>time(),
		];
//		print_r($data);die;
		if($AdvertingModel->add_invoice($data)){
//			$edit_order_invoice=Db::table('ims_foll_advertising_order')
//			->where('id','exp',' IN ('.$order_id.') ')
//			->update(['is_invoice'=>1]);
//			if($edit_order_invoice){
				$this->success('申请成功',Url("Advertising/invoice_list"));
//			}else{
//				$this->error('申请失败1');
//			}
		}else{
			$this->error('申请失败');
		}
	}
	
	//支付二维码链接有效期72小时
	public function is_payurl(Request $request){
		$admin_data=Session::get('UserResutlt');
		$order_id = $request->post('order_id');
		$order=Db::table('ims_foll_advertising_order')->where(['id'=>$order_id,'pay_status'=>'0'])->find();
		if(!empty($order['payurl'])){
			//该订单已操作二维码 但未支付,修改该订单的订单号,支付订单号不可重复
			$data=['ordersn'=>'GG99198'.$order['adv_id'].$admin_data['id']. date('Ymdhis',time()) . mt_rand(11111,99999),'payurl'=>''];
			$update=Db::table('ims_foll_advertising_order')->where(['id'=>$order_id])->update($data);
			if($update){
				echo json_encode(['status'=>'1','msg'=>$data['ordersn']]);
			}else{
				echo json_encode(['status'=>'3','msg'=>'操作失败，请联系管理员']);
			}
		}else{
			echo json_encode(['status'=>'2','msg'=>'二维码不存在']);
		}
	}
	
	public function add_order(Request $request){
		
		$order_id = $request->post('order_id');
		$token = trim($request->post('pay_type'));
		$adv_order=Db::table('ims_foll_advertising_order')
		->alias('a')
		->join('ims_foll_business_admin b','a.business_id = b.id')
		->field('a.*,b.openid,b.user_name')
		->where(['a.id'=>$order_id])
		->find();
		
//		$is_order=Db::table('ims_foll_order')->where(['ordersn'=>$adv_order['ordersn']])->find();
//		if($is_order){
//			echo json_encode(['status'=>'101','msg'=>'该订单不可重复操作']);die;
//		}
		$data=[
			'ordersn'=>$adv_order['ordersn'],
			'user_id'=>$adv_order['openid'],
			'business_id'=>$adv_order['business_id'],
			'uniacid'=>'3',
			'application'=>'advertising',
			'pay_type'=>$token,
//			'pay_account'=>'0.01',
			'pay_account'=>$adv_order['adv_money'],
			'body'=>'广告服务',
			'create_time'=>time(),
		];
		
		$data1=['ordersn'=>$adv_order['ordersn']];
		
		$add=Db::table('ims_foll_order')->insert($data);
		
		$add1=Db::table('ims_parking_order')->insert($data1);
		if($add && $add1){
			$postdata = array(
				'token' 	=> $token,
				'ordersn'   => $adv_order['ordersn'],
			);
			$url = 'http://shop.gogo198.cn/payment/wechat/Tgpay.php';
			$res = $this->ihttp_post($url, $postdata);
	
			$result = json_decode($res,TRUE);
//			print_r($result);
			if($result['msg']=='success'){
				$data2=['payurl'=>$result['payurl']];
				$update=Db::table('ims_foll_advertising_order')->where(['id'=>$order_id])->update($data2);
				echo json_encode($result);die;
			}
//			echo json([$result['payurl']]);
		}else{
			echo json_encode(['status'=>'102','msg'=>'失败']);
		}
		
		
		//'Tgwechat_scode','Tgalipay_scode'
		//微信扫码跟支付宝扫码
	}
	
	public function ihttp_post($url,$post_data) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
	}

}
?>