<?php


namespace app\admin\controller;

use think\Log;
use think\Request;
use app\admin\model\DeclBolModel;
use app\admin\model\CustomsBatchModel;
use app\admin\logic\BillLadingOrderMergeLogic;

class BillLadingOrderMerge extends Auth
{
    public $logic;
    
    public function __construct()
    {
        parent::__construct();
        $this->logic = new BillLadingOrderMergeLogic();
    }
    
    public function newBillLading(Request $request)
    {
        $billList = DeclBolModel::order('id', 'desc')->field('bill_num')->select();
        $this->assign(['billList' => $billList, 'title' => '新增提单']);
        return $this->fetch();
    }
    
    public function getBatchListByBillNum(Request $request)
    {
        if (!$request->get('billNum')) {
            return json(['code' => 1, 'msg' => '提单编号为空']);
        }
        $data = CustomsBatchModel::where(['bill_num'=>$request->get('billNum'),'status'=>1])->order('id', 'desc')->field('batch_num')->select();
        return json(['code' => 0, 'msg' => '完成', 'data' => $data]);
    }
    
    public function merge(Request $request)
    {
        try {
            $this->logic->handleMerge($request->post());
        } catch (\Exception $exception) {
            return json(['code' => 1, 'msg' => $exception->getMessage().$exception->getLine()]);
        }
        return json(['code' => 0, 'msg' => '完成']);
    }
}