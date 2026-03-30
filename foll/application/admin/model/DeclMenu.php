<?php

namespace app\admin\model;

use think\Model;
use think\Db;

/**
 * 申报系统菜单管理
 * Class DeclMenu
 * @package app\admin\model
 */
class DeclMenu extends Model {

    /**
     * 新增菜单
     * @param $data
     * @return mixed
     */
    public function MenuStorage($data) {
        Db::startTrans();
        try {
            Db::name('decl_access')->insert($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => -1, 'msg' => '添加失败'.$e->getMessage()]);
        }
        return json(['code' => 0, 'msg' => '添加成功']);
    }

    /**
     * 更新菜单数据
     * @param $id
     * @param $data
     * @return mixed
     */
    public function MenuUpdate($data){
        $id = $data['id'];
        unset($data['id']);
        Db::startTrans();
        try {
            Db::name('decl_access')->where('id',$id)->update($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json(['code' => -1, 'msg' => '更新失败'.$e->getMessage()]);
        }
        return json(['code' => 0, 'msg' => '更新成功']);
    }

    /**
     * 删除
     * @param $id
     */
    public function menuDel($id){
        Db::name('decl_access')->where('id',$id)->delete();
    }

    /**
     * 获取菜单
     * @return mixed
     */
    public function FetchMenuList(){
        return Db::name('decl_access')->select();
    }

    /**
     * 获取单条菜单信息
     * @param $where
     * @return mixed
     */
    public function FindMenuInfoById($where){
        return Db::name('decl_access')->where($where)->find();
    }

}
