<?php

namespace app\admin\controller;

use think\Cache;
use think\Request;
use app\admin\logic\UserOrderHistoryExportLogic;
use think\Controller;


class UserOrderHistoryExport extends Controller
{
    
    public $logic;
    
    public function __construct(UserOrderHistoryExportLogic $userOrderHistoryExportLogic)
    {
        parent::__construct();
        $this->logic = $userOrderHistoryExportLogic;
    }
    
    public function index()
    {
        $province = $this->logic->getProvince();
        $this->assign(['title' => '单证导出', 'province' => $province]);
        return $this->fetch();
    }
    
    public function getCity(Request $request)
    {
        return json(['code' => 0, 'msg' => '', 'data' => $this->logic->getCity($request->get('pid'))]);
    }
    
    public function getArea(Request $request)
    {
        return json(['code' => 0, 'msg' => '', 'data' => $this->logic->getArea($request->get('pid'))]);
    }
    
    
    public function getDataList(Request $request)
    {
        try {
            $data = $this->logic->getData($request->post());
        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage().$e->getLine()]);
        }
        return json(['code' => 0, 'msg' => '完成', 'count' => $data['count'], 'data' => $data['data']]);
    }
    
    public function export(Request $request)
    {
        if (empty($request->post('key'))) {
            $key='h:1'.date('Ymd');
            $param=$request->post();
            $param['key']=$key;
            $param = json_encode($param);
            $shell="cd /www/web/default/foll &&/www/wdlinux/php/bin/php think PackUserInfo '{$param}'";
            shell_exec($shell);
            return json(['code' => 0, 'message' => '请等待', 'data' => $key])->header('Access-Control-Allow-Origin:*');
        } else {
            $val=Cache::get($request->post('key'));
            if (empty($val)){
                return json(['code'=>1,'message'=>'暂无']);
            }
            Cache::rm($request->get('key'));
            return json(['code'=>0,'message'=>'完成','data'=>'http://shop.gogo198.cn/foll/public/uploads/excel/'.date('Ymd').'/'.$val])->header('Access-Control-Allow-Origin:*');
        }
    }
}