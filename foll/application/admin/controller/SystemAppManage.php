<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;
/**
 *
 * 各应用地址管理
 * Class SystemAppManage
 * @package app\admin\Controller
 */
class SystemAppManage extends Auth
{
    public function index()
    {
        $res = Db::name('total_system_menu')->paginate(10, true, ['query' => ['s' => 'SystemAppManage/index'], 'var_page' => 'page', 'type' => 'Layui', 'newstyle' => true]);
        $page = $res->render();
        $data = $res->toArray()['data'];
        $status = ['启用','关闭'];
        return view('SystemAppManage/index',['title'=>'各系统地址管理','page'=>$page,'data'=>$data,'status'=>$status]);
    }


    public function add(Request $request)
    {
        if ($request->isGET()){
            return view('SystemAppManage/add');
        }else{
            $data = $request->post();
            $domain = explode(".",$data['url']);
            $data['domain_prefix'] = $domain[0];
            Db::startTrans();
            try{

                Db::name('total_system_menu')->insert($data);
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
                return json(['code'=>-1,'message'=>$e->getMessage()]);
            }
            return json(['code'=>0,'message'=>'成功']);
        }
    }

    public function edit(Request $request)
    {
        if ($request->isGET()){
            if (!is_numeric($request->get('id'))){
                return view('');
            }
            $data = Db::name('total_system_menu')->where('id',$request->get('id'))->find();
            return view('SystemAppManage/edit',['data'=>$data]);
        }else{
            $data = $request->post();
            $id = $data['id'];
            $domain = explode(".",$data['url']);
            $data['domain_prefix'] = $domain[0];
            unset($data['id']);
            if (!is_numeric($id)){
                return json(['code'=>-1,'message'=>'参数错误']);
            }
            Db::startTrans();
            try{
                Db::name('total_system_menu')->where('id',$id)->update($data);
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
                return json(['code'=>-1,'message'=>$e->getMessage()]);
            }
            return json(['code'=>0,'message'=>'更新成功']);
        }
    }


    public function del(Request $request)
    {
        if (!is_numeric($request->get('id'))){
            return json(['code'=>-1,'message'=>'参数错误']);
        }
        Db::name('total_system_menu')->where('id',$request->get('id'))->delete();
        return json(['code'=>0,'message'=>'操作成功']);
    }

}