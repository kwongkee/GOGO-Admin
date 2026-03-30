<?php

namespace app\admin\model;

use think\Model;
use think\Db;
class Menu extends Model{

    public function getMenuAll()
    {
        return Db::table("ims_foll_business_menu")
            ->select();
    }
    public function addMenu($data){
    	return Db::table("ims_foll_business_menu")->insert($data);
    }
    //递归树型分类
    public function getMenuTreeList($data){
    $tree = array();  

	foreach($data as $category){  
	    $tree[$category['id']] = $category;  
	    $tree[$category['id']]['children'] = array();  
	}  

	foreach($tree as $key=>$item){  
	    if($item['parent_id'] != 0){  
	        $tree[$item['parent_id']]['children'][] = &$tree[$key];//注意：此处必须传引用否则结果不对  
//	        if($tree[$key]['children'] == null){  
//	            unset($tree[$key]['children']); //如果children为空，则删除该children元素（可选）  
//	        }  
	    }  
	}  

	foreach($tree as $key=>$category){  
	    if($category['parent_id'] != 0){  
	        unset($tree[$key]);  
	    }  
	}  
  	unset($data);
	return $tree;
	}
	//get获取当前ID的内容
	public function getMenuId($id){
		return Db::table("ims_foll_business_menu")->where("id",$id)->find();
	}
	//编辑菜单
	public function MenuUpdate($id,$data){
		return Db::table("ims_foll_business_menu")->where("id",$id)->update($data);
	}
	//添加菜单
	public function MenuAdd($data){
		return Db::table("ims_foll_business_menu")->insert($data);
	}
	//删除菜单
	public function MenuDelete($id){
		return Db::table("ims_foll_business_menu")->where("id",$id)->delete();
	}
}