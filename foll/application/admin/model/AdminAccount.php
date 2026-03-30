<?php
namespace app\admin\model;
use think\Db;
use think\Model;
use think\Session;

class AdminAccount extends model{
	private $offset =10;
	
    public function add($data)
    {
//      $userAmount=Db::table("ims_foll_user")->where("user_pid",0)->count('id');
//      if($userAmount==10){
//          return false;
//      }
        Db::table("ims_foll_user")->insert([
           'username'  =>$data['username'],
           'tel'=>$data['tel'],
           'user_status'=>$data['user_status'],
           'create_time'=>time(),
           'role'       =>$data['role'],
//         'pid' => Session::get('id'),
        ]);
        
        
        //用户角色对应
        $userId = Db::name('ims_foll_user')->getLastInsID();
        Db::table("ims_foll_account_authroleuser")->insert([
    		'role_id' =>$data['role'],
    		'user_id' =>$userId,
    	]);
    	return true;
   }
   
   
   	public function getRoleResult()
    {
        return Db::table("ims_foll_account_authrole")->where("status",1)->field(['name as role_name','id'])->select();
    }
    

    public function getAllUserResult()
    {
       return Db::table("ims_foll_user")
            ->alias("a1")
            ->join("ims_foll_account_authrole a2","a2.id=a1.role")
            ->field(["a1.*","a2.name as role_name"])
            ->limit(0,$this->offset)
            ->select();
    }
    
    
    //编辑页面数据
    public function getSingUserResult($id)
    {
        return Db::table("ims_foll_user")
            ->where("id",$id)
            ->find();
    }
    
    //编辑页面更新
    public function updateUserResult($id,$data)
    {
        Db::table("ims_foll_user")
            ->where("id",$id)
            ->update($data);
    }
    
    //判断用户权限
    public function accountPower($id){
    	
    }
}
?>