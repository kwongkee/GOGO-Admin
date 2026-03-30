<?php

namespace app\declares\controller;

use app\declares\controller;
use think\Request;
use think\Loader;


class DataMonitor extends BaseAdmin
{
    public function index()
    {
        $dataCountLogic = Loader::model('DataCount','logic');
        $list = Loader::model('OrderElec','model')->fetchBatchList(Session('admin.id'));
        if (empty($list)){
            $list[0]['batch_num']='00000000';
        }
        $numInfo = $dataCountLogic->getCount($list[0]['batch_num']);
        $numInfo['batch_list'] = $list;
        return view('datamonitor/index',$numInfo);
    }
    
    public function getCount(Request $request)
    {
        $dataCountLogic = Loader::model('DataCount','logic');
        $numInfo = $dataCountLogic->getCount($request->get('num'));
        return json($numInfo);
    }
    
    
    /**
     * 导出错误提交
     */
    public function exproErrorSub(Request $request)
    {
        try{
            $result = Loader::model('ErrorOrderExp','logic')->expErrorOrder($request->get('num'),Session('admin.id'));
        }catch (\Exception $exception){
            $this->error('异常错误',Url("datamonitor/index"));
        }
        $this->success($result,Url("datamonitor/index"));
    }
    
    
    /**
     * 手机版监控
     */
    public function mobileIndex(Request $request)
    {
    
    }
    
}