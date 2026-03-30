<?php


namespace app\superadmin\controller;

use app\superadmin\controller;
use think\Request;
use think\Loader;
use think\Db;

//use think\Controller;

class Home extends LoginAuth
{
    
    public function index ()
    {
        return view('home/pool_list');
    }
    
    public function getLogic ( $name )
    {
        return Loader::model($name, 'logic');
    }
    
    public function pool_list ( Request $request )
    {
        $logic = $this->getLogic('LotteryPool');
        $list  = $logic->getAllPlan();
        return view('home/pool_list', ['page' => $list[0], 'list' => $list[1]]);
    }
    
    
    public function setLotteryRound ( Request $request )
    {
        return view('home/set_round', ['id' => $request->get('mid')]);
    }
    
    public function saveLotteryRound ( Request $request )
    {
        $logic  = $this->getLogic('LotteryPool');
        $result = $logic->saveRoundData($request->post());
        if ( $result != null ) {
            return json(['code' => -1, 'msg' => $result]);
        }
        return json(['code' => 0, 'msg' => '完成']);
    }
    
    public function roundMenagem ( Request $request )
    {
        $list = $this->getLogic('LotteryPool')->getRoundManage();
        $type = ['系统摇号', '人工摇号'];
        return view('home/round_menage', ['list' => $list[1], 'page' => $list[0], 'type' => $type]);
    }
    
    /**
     * 中签管理
     * @param Request $request
     */
    public function lotteryMenagem ( Request $request )
    {
        $logic = $this->getLogic('LotteryPool');
        $list  = $logic->getAllLotteryPlan();
        return view('home/lottery', ['page' => $list[0], 'list' => $list[1], 'type' => ['系统摇号', '人工摇号']]);
    }
    
    public function upload ( Request $request )
    {
        $file = $request->file('file');
        if ( !$file ) {
            return json(['code' => -1, 'msg' => '错误']);
        }
        $fileObj  = $file->move(ROOT_PATH . 'public/uploads/xls');
        $fileName = $fileObj->getSaveName();
        $logic    = $this->getLogic('LotteryPool');
        list($result, $errno) = $logic->fetchFormFileAndSave($fileName, $request->get('mid'));
        @unlink(ROOT_PATH . 'public/uploads/xls/' . $fileName);
        return json(['code' => $errno, 'msg' => $result]);
    }
    
    public function lotteryDetail ( Request $request )
    {
        return view('home/lottery_detail', ['title' => '详情']);
    }
    
    public function confirmWin ( Request $request )
    {
        $result = $this->getLogic('LotteryPool')->confirmWin($request->get('mid'));
        if ( $result ) {
            $this->error($result, Url('superadmin/lottery_menagem'));
        }
        $this->success('完成', Url('superadmin/lottery_menagem'));
    }
    
    
    public function sayMenage ( Request $request )
    {
        
        $logic = $this->getLogic('LotteryPool');
        if ( $request->isGET() ) {
            $list = $logic->fetchAllSay();
            return view('home/say_list', ['page' => $list->render(), 'list' => $list->toArray()]);
        }
        if ( $request->isPOST() ) {
            return $logic->saveReply($request->post('msg'));
        }
    }
    
}