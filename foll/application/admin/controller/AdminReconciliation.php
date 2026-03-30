<?php
namespace app\admin\controller;
use app\admin\controller;
use think\Request;
use think\Loader;
use think\Validate;

class AdminReconciliation extends Auth
{
	public function Enterprise(Request $request)
    {

        return view("reconciliation/enterprise",['title'=>'银企对账']);
    }
    public function Tenant(Request $request)
    {

        return view("reconciliation/tenant",['title'=>'菜单列表']);
    }

}
?>