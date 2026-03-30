<?php

namespace app\admin\model;

use think\Model;
use think\Db;

/**
 * 申报系统角色管理
 * Class DeclMenu
 * @package app\admin\model
 */
class DeclRoleModel extends Model {


    public function sTrans(){
        Db::startTrans();
    }

    public function transCommit(){
        Db::commit();
    }

    public function roll(){
        Db::rollback();
    }


    /**
     * 保存添加角色
     * @param $roleData
     * @return int
     * @throws \Exception
     */
    public function RoleStorage($roleData){
        return Db::name('decl_role')->insertGetId($roleData);
    }


    /**
     * 角色菜单关联
     * @param $data
     * @return void
     */
    public function roleLinkNode($data){
        Db::name('decl_node')->insertAll($data);
    }

    /**
     * 获取所有总管理角色信息
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function fetchRole(){
        $count = Db::name('decl_role')->where('parent_id',0)->count();
        return Db::name('decl_role')
            ->where('parent_id',0)
            ->paginate(10,$count, [
                'query' => ['s' => 'admin/customssystem/declRoleInfo'],//['query'=> ['s'=>'admin/customssystem/declRoleInfo'], 'var_page'=>'page'],
                'var_page' => 'page',
                'type' => 'Layui',
                'newstyle' => true
            ]);
    }

    /**
     * 更新角色状态
     * @param $id
     * @param $status
     * @throws \Exception
     */
    public function roleStatus($id,$status){
        $this->sTrans();
        try{
            Db::name('decl_role')->where('id',$id)->update(['status'=>$status]);
            $this->transCommit();
        }catch (\Exception $e){
            $this->roll();
            throw new \Exception('更新失败');
        }
    }


    /**
     * 查看角色是否关联用户
     * @param $rid
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function isRoleUse($rid){
        return Db::name('decl_user_role')->where('role_id',$rid)->find();
    }

    /**
     * 删除角色
     * @param $id
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function roleDel($id){
        Db::name('decl_role')->where('id',$id)->delete();
        Db::name('decl_node')->where('role_id',$id)->delete();
    }

    /**
     * 查找单条角色信息
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function findRoleInfoByID($id){
        return Db::name('decl_role')->where('id',$id)->find();
    }


    /**
     * 更新角色信息
     * @param $id
     * @param $parm
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function roleRep($id,$parm){
        Db::name('decl_role')->where('id',$id)->update($parm);
        Db::name('decl_node')->where('role_id',$id)->delete();
    }

    /**
     * 获取所有父级角色
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function fetchAllRole(){
        return Db::name('decl_role')->where('parent_id',0)->select();
    }
}
