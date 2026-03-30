<?php
namespace app\admin\controller;
use app\admin\controller;
use think\Request;
use think\Loader;
use think\Validate;

class BusinessAdminMenuList extends Auth
{
	public function menuList(Request $request)
    {
        $statusType=['type'=>[0=>'权限认证+菜单',1=>'只作为菜单'],'status'=>[0=>'不显示',1=>'显示']];
        $menuList = Loader::model("Menu","model");
//      $data=array(
//            'parent_id'=>27,//父级ID
//            'app'      =>"好人qq程序",//应用名称app
//            'model'    =>"index",//控制器
//            'action'   =>"aaa",//操作名称
//            'url_param' =>'id=12&d12',//url参数
//            'type'      =>0,//菜单类型  1：权限认证+菜单；0：只作为菜单
//            'status'    =>1,//状态，1显示，0不显示
//            'name'      =>"菜单名称",//菜单名称
//            'icon'      =>"images/img.ico",//菜单图标
//            'remark'    =>"备注",//备注
//            'list_order'=>255,//排序ID
//            'rule_param'=>"验证规则",//验证规则
//            'nav_id'   =>1,//nav id
//            'request' =>"请求方式（日志生成）",//请求方式（日志生成）
//            'log_rule'=>"日志规则",//日志规则
//            
//      );
//      $menuList->addMenu($data);
//      print_r($menuList->getMenuAll());
        $menu_s = $menuList->getMenuAll();
        $menu = $menuList->getMenuTreeList($menu_s);
//      dump($menu);die;
//      print_r($menu_s);
        return view("menu/business_admin_menulist",['title'=>'菜单列表','statusType'=>$statusType,'data'=>$menu]);
    }
    //编辑分类
    public function menuEdit(Request $request){
    	$menuList = Loader::model("Menu","model"); 
    	$menu_s = $menuList->getMenuAll();
        $menu = $menuList->getMenuTreeList($menu_s);
    	if($request->isGet()){
    		return view("menu/business_admin_menuedit",['title'=>'菜单编辑',
    		'menuList'=>$menuList->getMenuId($request->get("id")),'menu'=>$menu]);
    	}
    	if($request->isPost()){
    	  $date = array();
    	  $validate = new Validate([
                'app'    =>'require',
                'model'  =>'require',
                'type'   =>'require',
                'status' =>'require',
                'name'   =>'require'
            ],[
                'app'=>'应用名称必填',
                'model'=>'控制器必填',
                'type' =>'菜单类型必填',
                'status' =>'状态必填',
                'name'=>'菜单名称必填'
            ]);
    	  $data=[
    		  'app'           =>$request->post('app'),
    		  'model'         =>$request->post('model'),
    		  'action'        =>$request->post('action'),
    		  'url_param'     =>$request->post('url_param'),
    		  'type'          =>$request->post('type'),
    		  'status'        =>$request->post('status'),
    		  'name'          =>$request->post('name'),
    		  'icon'          =>$request->post('icon'),
    		  'list_order'    =>$request->post('list_order'),
    		  'rule_param'    =>$request->post('rule_param'),
    		  'nav_id'        =>$request->post('nav_id'),
    		  'request'       =>$request->post('request'),
    		  'log_rule'      =>$request->post('log_rule'),
    	  ];
    	  if(!$validate->check($data)){
                $this->error($validate->getError());
            }  	
          if($menuList->MenuUpdate($request->post("id"),$data)){
          	$this->success("编辑更新成功",Url("admin/businessAdminMenuList"));
          }	
    	}

    }
    //添加菜单
    public function menuAdd(Request $request){
    	$menuList = Loader::model("Menu","model"); 
    	$menu_s = $menuList->getMenuAll();
        $menu = $menuList->getMenuTreeList($menu_s);
        $id = $request->get('id');
    	if($request->isGet()){
    		return view("menu/business_admin_menuadd",['title'=>'菜单添加',
    		'menu'=>$menu,'id'=>$id]);
    	}
    	if($request->isPost()){
    	  $date = array();
    	  $validate = new Validate([
                'app'    =>'require',
                'model'  =>'require',
                'type'   =>'require',
                'status' =>'require',
                'name'   =>'require'
            ],[
                'app'=>'应用名称必填',
                'model'=>'控制器必填',
                'type' =>'菜单类型必填',
                'status' =>'状态必填',
                'name'=>'菜单名称必填'
            ]);
    	  $data=[
    		  'parent_id'     =>$request->post('parent_id'),
    		  'app'           =>$request->post('app'),
    		  'model'         =>$request->post('model'),
    		  'action'        =>$request->post('action'),
    		  'url_param'     =>$request->post('url_param'),
    		  'type'          =>$request->post('type'),
    		  'status'        =>$request->post('status'),
    		  'name'          =>$request->post('name'),
    		  'icon'          =>$request->post('icon'),
    		  'list_order'    =>$request->post('list_order'),
    		  'rule_param'    =>$request->post('rule_param'),
    		  'nav_id'        =>$request->post('nav_id'),
    		  'request'       =>$request->post('request'),
    		  'log_rule'      =>$request->post('log_rule'),
    	  ];
//    	  print_r($data);die;
    	  if(!$validate->check($data)){
                $this->error($validate->getError());
            }  	
          if($menuList->MenuAdd($data)){
          	$this->success("添加成功",Url("admin/businessAdminMenuList"));
          }
     }
    }
    //删除菜单
//  public function menuAdd(Request $request){
//  	$menuList = Loader::model("Menu","model");
//  	$id = $request->get('id');
//  }

}
?>