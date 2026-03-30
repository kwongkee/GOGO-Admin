<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use think\Session;

class Admincharacterlist extends model{
	private $offset =10;
	
	public function getResult(){
		return Db::table('ims_foll_account_authrole')->alias('a')->join('ims_foll_account_rwaccess b','a.id = b.role_id')->select();
	}
	
   public function addAdminCharacter($data){
   		$read=null;
        $write=null;
        if($data['authType']==1){
            $read=1;
            $write=0;
        }else{
            $read=1;
            $write=1;
        }
        try{
            $role_id = Db::table("ims_foll_account_authrole")->insertGetId([
                'name'  =>$data['name'],
                'status'=>$data['roleStatus'],
                'remark'=>$data['remark'],
                'create_time'=>time(),
                'update_time'=>time(),
            ]);
            Db::table("ims_foll_account_rwaccess")->insert([
                'role_id' =>$role_id,
                'read'    =>$read,
                'write'   =>$write
            ]);
            return ['status'=>true,"code"=>100,'msg'=>'添加成功'];
        }catch (Exception $e){
            return ['status'=>false,'code'=>101,'msg'=>$e->getMessage()];
        }
    }
   
    public function getBusinessRoleSingData($id){
        $data = Db::table("ims_foll_account_authrole")->where("id",$id)->find();
        $Rw   =Db::table("ims_foll_account_rwaccess")->where("role_id",$id)->find();
        $data['read']=$Rw['read'];
        $data['write']=$Rw['write'];
        unset($Rw);
        return $data;
    }
    
    public function updateSingBusinesRoleData($request){
        $read=null;
        $write=null;
        if($request->post('authType')==1){
            $read=1;
            $write=0;
        }else{
            $read=1;
            $write=1;
        }
        try{
            Db::table("ims_foll_account_authrole")
                ->where("id",$request->post("id"))
                ->update([
                    'name' =>$request->post("name"),
                    'status' =>$request->post("roleStatus"),
                    'remark' =>$request->post("remark"),
                    'update_time' =>time()
                ]);
            Db::table("ims_foll_account_rwaccess")->where("role_id",$request->post("id"))->update(['read'=>$read,"write"=>$write]);
            return true;
        }catch (Exception $e){
            return false;
        }
    }

    public function deleteBusinesRoleData($id){
        Db::table('ims_foll_account_authrole')->delete($id);
        Db::table('ims_foll_account_rwaccess')->where('role_id',$id)->delete();
    }
}
?>