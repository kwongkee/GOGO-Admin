<?php
namespace app\admin\controller;

//use think\Controller;
use think\Request;
use app\admin\controller;
use think\Loader;

class Admincharacterlist extends Auth
{
//	    public function __construct()
//  {
//  	$User= \think\Loader::model('User');//实例化User model
//  	$User->check_Login();
//  }
    
    public function index(){
    	$admincharacterlistModel  =Loader::model("Admincharacterlist","model");
//  	print_r($admincharacterlistModel->getResult());
    	$statusType=['read'=>[0=>'不可读',1=>'可读'],'write'=>[0=>'不可写',1=>'可写'],'roleStatus'=>[0=>"禁用",1=>"启用"]];
    	return view("admincharacterlist/index",[
            'title'=>'超级管理用户列表',
            'data' =>$admincharacterlistModel->getResult(),
            'statusType'=>$statusType,
        ]);
    }
    
    public function AdminCharacterSave(Request $request){
    	$data=array();
        $data['name']       = $request->post("name");
        $data['authType']   = $request->post("authType");
        $data['roleStatus'] = $request->post("roleStatus");
        $data['remark']     = $request->post("beizhu");
        if(empty($data['name']))return json(['status' => false,'code' => 101,'msg' => '名称必填']);
        $role  =Loader::model("Admincharacterlist","model");
        return json($role->addAdminCharacter($data));
    }
    
    public function AdminCharacterEdit(Request $request){
    	 $updataData = Loader::model("Admincharacterlist","model");
        if($request->isGet()){
            return view("admincharacterlist/edit",[
                'title'=>'修改',
                'data'=>$updataData->getBusinessRoleSingData($request->get("id"))
            ]);
        }
        if($request->isPost()){
           if($updataData->updateSingBusinesRoleData($request)){
               $this->success('更新成功', Url("admin/adminCharacterList"));
           }
                $this->error("更新失败");
        }
    }
    
    public function AdminCharacterDel(Request $request){
    	$role = Loader::model("Admincharacterlist","model");
        $role->deleteBusinesRoleData($request->get("id"));
        $this->success('删除成功', Url("admin/adminCharacterList"));
    	
    }
}
?>