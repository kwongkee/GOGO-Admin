<?php

namespace app\admin\controller;
use app\admin\controller\Auth;
use think\Request;
use think\Loader;

class Advertising extends Auth
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $Ader = Loader::model("Advertising",'logic');
        $data = $Ader->allAder();
        $page = $data->render();
        return view("advertising/index",['data'=>$data->toArray(),'page'=>$page]);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $Ader = Loader::model("Advertising",'logic');
        if($Ader->updateAndInserNewData($request)){
            return ['code'=>001,'msg'=>'审核成功'];
        }
        return ['code'=>000,'msg'=>'审核失败'];
    }

    /*
     * 广告数据统计
     */

    public function advertisingStatistics(Request $request)
    {
        $data=Loader::model("Advertising",'logic')->sumCps();
        dump($data);
        return view("advertising/advertisingStatistics");
    }
}
