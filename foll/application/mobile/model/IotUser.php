<?php

namespace app\mobile\model;

use think\Model;
use think\Db;
class IotUser extends Model
{
    /**
     * 更新用户信息
     * @param $where
     * @param $data
     * @throws \Exception
     */
    public function updateUser($where, $data)
    {
        Db::startTrans();
        try {
            Db::name('iot_users')->where($where)->update($data);
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 保存用户信息
     * @param $data
     * @throws \Exception
     */
    public function storageUser($data)
    {
        Db::startTrans();
        try {
            Db::name('iot_users')->insert($data);
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 获取单条用户信息
     * @param $where
     * @return mixed
     */
    public function findUserInfoByWhere($where)
    {
        return Db::name('iot_users')->where($where)->find();
    }
}
