<?php
namespace app\admin\controller;
use app\admin\controller;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use think\Request;
use think\Loader;

class BusinessAdminRole extends Auth
{
    public function businessAdminCharacterList(Request $request)
    {
        $statusType=['read'=>[0=>'不可读',1=>'可读'],'write'=>[0=>'不可写',1=>'可写'],'roleStatus'=>[0=>"禁用",1=>"启用"]];
        $roleList = Loader::model("Role","model");
        return view("businessadmin/business_admin_characterlist",['title'=>'角色列表','statusType'=>$statusType,'data'=>$roleList->getBusinessRole()]);
    }

    public function businessAdminCharacterAdd()
    {
        return view("businessadmin/business_admin_characteradd");
    }

    public function businessAdminCharacterSave(Request $request)
    {
        $data       =array();
        $data['name']       = $request->post("name");
        $data['authType']   = $request->post("authType");
        $data['roleStatus'] = $request->post("roleStatus");
        $data['remark']     = $request->post("beizhu");
        if(empty($data['name']))return json(['status' => false,'code' => 101,'msg' => '名称必填']);
        $role  =Loader::model("Role","model");
        return json($role->addBusinessRole($data));
    }

    public function businessAdminCharacterDel(Request $request)
    {
        $role = Loader::model("Role","model");
        $role->deleteBusinesRoleData($request->get("id"));
        $this->success('删除成功', Url("admin/businessAdminCharacterList"));
    }

    public function  businessAdminCharacterUpdate(Request $request)
    {
        $updataData = Loader::model("Role","model");
        if($request->isGet()){
            return view("businessadmin/business_admin_characterupdate",[
                'title'=>'修改',
                'data'=>$updataData->getBusinessRoleSingData($request->get("id"))
            ]);
        }
        if($request->isPost()){
           if($updataData->updateSingBusinesRoleData($request)){
               $this->success('更新成功', Url("admin/businessAdminCharacterList"));
           }
                $this->error("更新失败");
        }
    }
}