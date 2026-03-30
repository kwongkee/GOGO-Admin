<?php

namespace app\index\controller;

use app\index\controller;
use think\Request;
use think\Session;
use think\Db;

class Holiday extends CommonController
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function Holiday_index()
    {
        return view("holiday/holiday_index");
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function Holiday_add(Request $request)
    {
        try{
            Db::name("parking_holiday_schedule")->insert([
                'holiday_type'  =>3,
                'pirce'         =>$request->post("money"),
                'uniacid'       =>Session::get('UserResutlt')['uniacid']
            ]);
            $this->success("添加成功",Url('index/Holiday_index'));
        }catch (Exception $e){
            $this->error("添加失败",Url('index/Holiday_index'));
        }
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
