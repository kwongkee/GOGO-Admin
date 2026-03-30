<?php

namespace app\admin\controller;

use app\admin\controller;
use mysql_xdevapi\Exception;
use think\Request;
use think\Loader;

/**
 * 海关抽取查验订单管理
 * Class CheckOrderManagement
 * @package app\admin\controller
 */
class CheckOrderManagement extends Auth
{
    public $logic;

    public function __construct()
    {
        parent::__construct();
        $this->logic = model('CheckOrderManagementLogic', 'logic');
    }

    public function index(Request $request)
    {
        $title = '抽查列表';
        $list  = $this->logic->getBillList();
        $page  = $list->render();
        $list  = $list->toArray()['data'];
        return view('CheckOrderManagement/index', compact('list', 'title','page'));
    }

    public function orderList(Request $request)
    {
        if (!input('?bill_num')){
            return '';
        }
        $list  = $this->logic->getCheckOrderInfoFromBillNum(input('bill_num'));
        $title = '已查验订单列表';
        $status=['待提交','已提交','提交失败'];
        return view('CheckOrderManagement/order_list',compact('list','title','status'));
    }

    /**
     * 导出查验原始支付信息
     * @param Request $request tp
     * @return excel
     */
    public function exportRawPayInfo(Request $request)
    {
        if (!input('?billnum')){
            return '参数错误';
        }
        try{
            $this->logic->exportRawPayInfoLogic(input('billnum'));
        }catch (Exception $e){
            throw new Exception($e->getMessage());
        }
    }
}
