<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller;
use think\Loader;
use think\Session;
use think\Db;

class AdminAdverting extends Auth
{
	public function index(Request $request){
		$AdminAdvertingModel=Loader::model('AdminAdverting','model');
		$Type=['type'=>[0=>'',1=>'图片',2=>'图文',3=>'音频',4=>'视频']];
		
		$config = [
			'path'=>'',
			'query'=>['s'=>'admin/adminAdverting'],//其他参数
            'var_page'=>'page'
        ];
        $where='1 = 1';
        $number = $request->post('number');
        $uniacid = $request->post('uniacid');
        $condition = $request->post('condition');
        if($number!=''){
        	$where.=' AND a.number = '.$number;
        }
        
        if($uniacid!=''){
        	$where.=' AND a.uniacid = '.$uniacid;
        }
        if($condition!=''){
        	$where.=' AND a.condition = '.$condition;
        }
        $total=Db::table("ims_foll_advertising")->alias('a')->where($where)->count();
		$adver_result = Db::table('ims_foll_advertising')
		->alias('a')
		->join('ims_account_wechats b','a.uniacid = b.uniacid')
		->join('ims_sz_yi_designer c','a.board = c.id')
		->field('a.*,b.name,c.pagename')
		->where($where)
		->order('id asc')
		->paginate(10,$total,$config);
		return view("adminadverting/list",[
			'title'=>'展位管理',
			'Type'=>$Type,
			'wechats'=>$AdminAdvertingModel->get_wechats(),
			'page'=>$adver_result->render(),
			'data'=>$adver_result->toArray()['data'],
		]);
	}
	
	public function AdminAdvertingAdd(Request $request){
		$AdminAdvertingModel=Loader::model('AdminAdverting','model');
		return view('adminadverting/add',
		['title'=>'展位添加',
		'wechats'=>$AdminAdvertingModel->get_wechats(),
		]);
	}

	public function AdminAdvertingSaves(Request $request){
		$AdminAdvertingModel=Loader::model('AdminAdverting','model');
		$data=array(
			'adv_name'=>$request->post('adv_name'),
			'uniacid'=>$request->post('uniacid'),
			'number'=>$request->post('uniacid').$request->post('board').($AdminAdvertingModel->get_counts()+1),
			'type'=>$request->post('type'),//1图片,2图文,3音频,4视频 默认1
			'way'=>$request->post('way'),//1单播,2轮播
			'position'=>$request->post('position'),//位置
			'board'=>$request->post('board'),//板块
			'image'=>$request->post('image'),
			'money'=>$request->post('money'),
			'weighted'=>$request->post('weighted'),
			'condition'=>$request->post('condition'),
			's_time'=>strtotime($request->post('s_time')),//有效时间,开始时间
			'e_time'=>strtotime($request->post('e_time')),//有效时间,结束时间
			'status'=>'10',//10未审核,20已审核
			'expiration'=>'0',//广告:0:未过期,1:已过期',
			'create_time'=>time(),
		);
		$add=$AdminAdvertingModel->saves($data);
		if($add){
			$this->success('添加成功', Url("admin/AdminAdverting"));
		}else{
			$this->error('添加失败');
		}
	}
	
	public function AdminAdvertingEdit(Request $request){
		$AdminAdvertingModel=Loader::model('AdminAdverting','model');
		$id = $request ->get('id');
		$data=$AdminAdvertingModel->adverting_one($id);
		return view('adminadverting/edit',
		['title'=>'展位编辑',
		'wechats'=>$AdminAdvertingModel->get_wechats(),
		'data'=>$data,
		'board'=>$AdminAdvertingModel->get_board($data['uniacid']),
		]);
	}
	
	//编辑
	public function AdminAdvertingEdits(Request $request){
		$AdminAdvertingModel=Loader::model('AdminAdverting','model');
//		$data=array(
//			'uniacid'=>$request->post['uniacid'],
//			'type'=>$request->post['type'],
//			'image'=>$request->post['image'],
//			'board'=>$request->post['board'],
//			'money'=>$request->post['money'],
//			'position'=>$request->post['position'],
//		);
        if($AdminAdvertingModel->update_data($request)){
            $this->success('更新成功', Url("admin/adminAdverting"));
        }else{
        	$this->error("更新失败");
        }

	}
	
	public function AdminAdvertingCheck(Request $request){
		$obj = $request->post('obj');
		if(empty($obj)){
			return ['status'=>false,"code"=>100,'msg'=>'不能为空1'];
		}
	}
	
	//获取公众号对应的板块
	public function AdminAdvertingGetMod(Request $request){
		$AdminAdvertingModel=Loader::model('AdminAdverting','model');
		$uniacid = $request->post('uniacid');
		echo json_encode($AdminAdvertingModel->GetMod($uniacid));
		
	}
	
	//删除
	public function AdminAdvertingDel(Request $request){
		$AdminAdvertingModel=Loader::model('AdminAdverting','model');
		$id= $request->get('id');
		$deleteData=$AdminAdvertingModel->deleteData($id);
		if($deleteData){
			$this->success('删除成功',Url('admin/adminAdverting'));
		}else{
			$this->error('删除失败');
		}
	}
	
	
	//视频素材
	public function videoAdd(){
		$AdminAdvertingModel=Loader::model('AdminAdverting','model');
//		print_r($AdminAdvertingModel->get_one('629'));die;
		return view('adminadverting/video',
		['title'=>'视频素材',
		'data'=>$AdminAdvertingModel->get_one('629'),
		]);
	}
}
?>