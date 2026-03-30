<?php

namespace app\api_v3\model;

use think\Model;
use think\Db;

/**
 * 申报系统用户管理
 * Class DeclMenu
 * @package app\admin\model
 */
class DeclUserModel extends Model {
    /**
     * 获取所有商家
     * @return mixed
     */
    public function fetchAllCheckUser(){
        return Db::name('decl_user')
            ->alias('a')
            ->join('decl_user_role b',"a.id=b.user_id",'left')
            ->field(['a.*','b.role_id'])
            ->order('id','desc')
            ->paginate(10,true,['query'=> ['s'=>'admin/customssystem/declUserManage'], 'var_page'=>'page']);
    }
    
    /**
     * 获取申报系统商户
     * @param $page
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fetchMerchant($page,$limit)
    {
        return Db::name('decl_user')
            ->alias('a')
            ->join('decl_user_role b',"a.id=b.user_id",'left')
            ->field(['a.*','b.role_id'])
            ->order('id','desc')
            ->limit($page,$limit)
            ->select();
    }
    
    /**
     * 获取申报商户数量
     * @return int|string
     */
    public function countMerchant(){
        return Db::name('decl_user')->count();
    }

    /**
     * 查找商户账号信息
     * @param $id
     * @return mixed
     */
    public function findUserInfoById($id){
        return Db::name('decl_user')->where('id',$id)->find();
    }
    /**
     * 更新商户账户状态
     * @param $id
     * @param $status
     */
    public function userStatus($id,$status){
        Db::name('decl_user')->where('id',$id)->update(['user_status'=>$status]);
    }


    /**
     * 审核不通过后删除
     * @param $id
     */
    public function userDel($id){
        Db::name('decl_user')->where('id',$id)->delete();
    }


    /**
     * 关联角色
     * @param $data
     */
    public function userLinkRole($data){
        Db::name('decl_user_role')->insert($data);
    }

    //变更角色
    public function updateRole($user_id,$role_id)
    {
        Db::name('decl_user_role')->where('user_id',$user_id)->update(['role_id'=>$role_id]);
    }

    public function addOrderCustomsDiscount($id,$num)
    {
        Db::name('decl_user')->where('id',$id)->update(['sbDis'=>$num]);
    }

    /**
     * 获取用户名称
     * @param $id
     * @return mixed
     */
    public function getUserNameById($id)
    {
        return Db::name('decl_user')->where('id',$id)->field('user_name')->find();
    }
}
