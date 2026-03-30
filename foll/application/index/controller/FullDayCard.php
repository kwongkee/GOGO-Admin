<?php

namespace app\index\controller;

use app\index\controller;
use think\Loader;
use think\Request;
use app\index\logic;

class FullDayCard extends CommonController
{
    
    
    /**
     * 获取server
     * @param $name
     * @return object
     */
    protected function getLogic ( $name )
    {
        return Loader::model($name, 'logic');
    }
    
    /**
     * 获取model模型
     * @param $name
     * @return object
     */
    protected function getModel ( $name )
    {
        return Loader::model($name, 'model');
    }
    
    
    /**
     * 发行列表
     * @param Request $request
     * @return view
     */
    
    public function monthIndex ( Request $request )
    {
        
        $list = $this->getLogic('FullDayCard')->getAll($this->getModel('MonthCard'));
        return view('month/month_index', $list);
    }
    
    /**
     * 添加发行
     * @param Request $request
     * @return view or add result
     */
    public function addMonthCard ( Request $request )
    {
        if ( $request->isPOST() ) {
            $result = $this->getLogic('FullDayCard')->saveMonth($request->post(), $this->getModel('MonthCard'));
            return json(['code' => $result['code'], 'message' => $result['msg']]);
        }
        $adr = $this->getLogic('FullDayCard')->getAddr();
        return view('month/month_add', ['addr' => $adr]);
    }
    
    
    /**
     * @param Request $request
     * 删除发行月卡
     */
    public function monthDel ( Request $request )
    {
        $res = $request->get();
        if ( !isset($res['id']) && empty($res['id']) ) {
            $this->error('错误', Url('month_card/index'));
        }
        $logic = $this->getLogic('FullDayCard');
        $logic->delMonthFromTable($res['id']);
        $this->success('完成', Url('month_card/index'));
        
    }
    
    
    public function monthStatus ( Request $request )
    {
        $this->getLogic('FullDayCard')->updateMonthStatus($request->get('id'), $request->get('status'));
        $this->success('完成', Url('month_card/index'));
    }
    
    /**
     * 添加需要上传的资料
     * @param Request $request
     * @return view
     */
    public function addUploadCer ( Request $request )
    {
        $logic = $this->getLogic('FullDayCard');
        if ( $request->isPOST() ) {
            if ( $result = $logic->saveUploadCer($request->post('name')) ) {
                return json(['code' => -1, 'message' => $result]);
            }
            return json(['code' => 0, 'message' => '完成']);
        }
        $list = $logic->getCer();
        return view('month/add_cer', ['list' => $list]);
    }
    
    public function delCer ( Request $request )
    {
        $res = $request->get();
        if ( !isset($res['id']) && empty($res['id']) ) {
            $this->error('错误', Url('month_card/add_cer'));
        }
        $this->getLogic('FullDayCard')->delTableCer($res['id']);
        $this->success('完成', Url('month_card/add_cer'));
    }
    
    /**受理
     * @param Request $request
     * @return mixed
     */
    public function monthApplyAccept ( Request $request )
    {
        $logic     = $this->getLogic('FullDayCard');
        $checkList = $logic->getApplyList($this->getModel('MonthCard'));
        return view('month/apply_list', ['page' => $checkList[0], 'list' => $checkList[1], 'num' => $checkList[2],]);
    }
    
    
    /**更新受理状态
     * @param Request $request
     * @return json
     */
    public function monthApplyCheck ( Request $request )
    {
        $result = $this->getLogic('FullDayCard')->updateApplyStatus($request->post('id'), $request->post('status'), $request->post('msg'));
        if ( !$result[0] ) {
            return json(['code' => -1, 'msg' => $result[1]]);
        }
        return json(['code' => 0, 'msg' => '完成']);
    }
    
    
    /**申请审核列表
     * @param Request $request
     * @return mixed
     */
    public function monthReview ( Request $request )
    {
        $logic      = $this->getLogic('FullDayCard');
        $reviewList = $logic->getReviewPlanList($this->getModel('MonthCard'));
        return view('month/review_list', ['page' => $reviewList[0], 'list' => $reviewList[1]]);
    }
    
    
    /**
     * 月卡审核详情，待定
     * @param Request $request
     */
    public function monthReviewDetail ( Request $request )
    {
        dump('21');
    }
    
    
    /**处理月卡审核结果
     * @return json
     * @param Request $request
     */
    public function isMonthReview ( Request $request )
    {
        $timer = time();
        $fullDayCardServer = $this->getLogic('FullDayCard');
        $m     = $fullDayCardServer->getMonthInfo($request->get('id'));
        if ( $timer < $m['accept_review'] && $timer > $m['accept_review2'] ) {
            return json(['code' => -1, 'msg' => '不在审核期']);
        }
        if ( $rel = $fullDayCardServer->updateStatusAndSendMsg($request->get()) ) {
            return json(['code' => -1, 'msg' => $rel]);
        }
        return json(['code' => 0, 'msg' => '完成']);
    }
    
    public function getAddr ( Request $request )
    {
        $list = $this->getLogic('FullDayCard')->getAddrPath($request->get('pid'));
        return json(['code' => 0, 'msg' => $list]);
    }
    
    
    public function userMonthApplyList (Request $request)
    {
        $list    = $this->getLogic('FullDayCard')->getUserApplyInfo($request->get());
        $aStatus = ['未受理', '已受理', '拒绝'];
        $rStatus = ['未审核', '已审核', '不通过'];
        $isWin   = ['<span style="color: red">未</span>', '<span style="color: lawngreen">中</span>'];
        return view('month/user_apply_list', ['page' => $list[0], 'list' => $list[1], 'aStatus' => $aStatus, 'rStatus' => $rStatus, 'isWin' => $isWin]);
    }
    
    
    /**
     * 申请月卡前置公告
     */
    public function addAplPub ( Request $request )
    {
        $fullDayCardServer = $this->getLogic('FullDayCard');
        if ( $request->isGET() ) {
            $source = $fullDayCardServer->getPublicity();
            return view('month/apply_publicity', ['text' => $source]);
        }
        
        if ( $request->isPOST() ) {
            $fullDayCardServer->savePublicity($request->post('context'));
            return json(['code' => 0, 'msg' => '完成']);
        }
        
        
    }
    
    
    /**处理上传中签结果
     * @param Request $request
     * @return mixed
     */
    public function uploadWinResl ( Request $request )
    {
        if ( $request->isGET() ) {
            return view('month/upload');
        }
        if ( $request->isPOST() ) {
            $file = $request->file('file');
            if ( !$file ) {
                return json(['code' => -1, 'msg' => '错误']);
            }
            $fileObj  = $file->move(ROOT_PATH . 'public/uploads/xls');
            $fileName = $fileObj->getSaveName();
            $fullDayCardServer    = $this->getLogic('FullDayCard');
            list($result, $errno) = $fullDayCardServer->fetchFormFileAndSave($fileName);
            @unlink(ROOT_PATH . 'public/uploads/xls/' . $fileName);
            return json(['code' => $errno, 'msg' => $result]);
        }
    }
    
    /**中签结果列表
     * @param Request $request
     * @return int
     */
    public function win_list ( Request $request )
    {
        $fullDayCardServer = $this->getLogic('FullDayCard');
        $list = $fullDayCardServer->getAllWinPlan($this->getModel('MonthCard'));
        return view('month/win_list',['page'=>$list[0],'list'=>$list[1]]);
    }
    
    
    /**
     * 方案中签详情
     * @param Request $request
     */
    public function win_detail(Request $request)
    {
        $list = $this->getLogic('FullDayCard')->winDetail($request->get('mid'));
        return view('month/win_detail',['page'=>$list[0],'list'=>$list[1]]);
    }
    
    
    /**
     * 月卡逾期缴费管理
     * @param Request $request
     * @return mixed
     */
    public function payOutManage(Request $request)
    {
        $list =  $this->getLogic('FullDayCard')->getPayOutList();
        return view('month/payout_manage',['page'=>$list[0],'list'=>$list[1]]);
    }
    
    /**
     * 月卡逾期缴费详情
     * @param Request $request
     */
    public function payOutManageInfo(Request $request)
    {
    	$list =  $this->getLogic('FullDayCard')->getPayOutInfo($request->get('mid'));
        return view('month/payout_manage_info',['page'=>$list->render(),'list'=>$list->toArray()]);
    }
    
    
    /**
     * 延迟月卡支付时间
     * @param Request $request
     * @return mixed
     */
    public function payOutDelayTime(Request $request)
    {
        try{
            $result = $this->getLogic('FullDayCard')->addDelayPayTime($request->post());
            return $result;
        }catch (\Exception $exception){
            return json(['code'=>-1,'msg'=>$exception->getMessage()]);
        }
       
    }
    
    
    /**返回候补列表
     * @param Request $request
     * @return mixed
     */
    public function subteList(Request $request)
    {
        return $this->getLogic('FullDayCard')->fetchWaiting($request->get('id'));
    }
    
    
    /**
     * 更新候补状态，候补转正
     */
    public function  updateAlternateStu( Request $request )
    {
        return $this->getLogic('FullDayCard')->updateAlternateStu($request->get());
    }
    
    
    /**
     * 导出用户月卡申请信息
     * @param Request $request
     */
    public function exprotUserApply(Request $request)
    {
        try{
            $this->getLogic('FullDayCard')->exprotUserApply();
        }catch (\Exception $e){
            $this->error('系统异常',Url('month_card/user_month_applylist'));
        }
    }
    
    
    /**月卡注销管理
     * @param Request $request
     * @return mixed
     */
    public function monthCancelList(Request $request)
    {
        $list = $this->getLogic('FullDayCard')->fetchMonthCancel();
        $isPay = ['未付款','已付款','付款失败'];
        return view('month/cancel_list',['page'=>$list->render(),'list'=>$list->toArray(),'isPay'=>$isPay]);
    }
    
    /**
     * 月卡注销已退金额
     * @param Request $request
     */
    public function monthCancelHandle(Request $request)
    {
       return $this->getLogic('FullDayCard')->monthCancelHandle($request->post());
    }
    
    
    /**
     * 月卡支付管理
     */
    
    public function monthPay(Request $request)
    {
        $isPay = ['未支付','已支付','支付失败'];
        $list = $this->getLogic('FullDayCard')->monthPay($request->get());
        return view('month/month_pay',['page'=>$list[0],'list'=>$list[1],'isPay'=>$isPay,'mid'=>$request->get('mid')]);
    }
    
    
    /**
     * 导出月卡支付信息
     */
    public function exproPayInfo(Request $request){
        $this->getLogic('FullDayCard')->exproPayInfo($request->get('mid'));
    }
    
}