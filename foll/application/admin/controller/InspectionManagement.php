<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;

class InspectionManagement extends Auth
{

    protected $logic;

    public function __construct()
    {
        $this->logic = model('MerchantBillCumulativeLogic', 'logic');
    }

    /**
     * 提单查验率列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function billCheckRate(Request $request)
    {
        $title    = '提单查验率信息';
        $total    = Db::name('customs_check_value')->count();
        $billList = Db::name('customs_check_value')->paginate(10, $total,
            ['query' => ['s' => 'admin/billrate/check_list'], 'var_page' => 'page']);
        $page     = $billList->render();
        $bills    = $billList->toArray()['data'];
        foreach ($bills as &$val){
            $val['check_datafile'] = explode("|",$val['check_datafile']);
        }
        unset($billList);
        return view('inspection/bill_checkrate', compact('title', 'page', 'bills'));
    }

    /**
     * 提单累积率
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function billGrandTotal(Request $request)
    {
        $title = '累计';
        list($list, $page) = $this->logic->getCompanyList($request);
        return view('inspection/bill_check_pile', compact('list', 'page', 'title'));
    }


    /**
     * 提单查验率峰值设置
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function billCheckWarningValueList(Request $request)
    {
        $title = '峰值设置';
        $total = Db::name('decl_user')->where('parent_id', 0)->count();
        $list  = Db::name('decl_user')
            ->alias('a')
            ->join('customs_check_warning_value b', 'a.id=b.uid', 'left')
            ->where('a.parent_id', 0)
            ->field(['a.user_name','a.company_name','a.id as busin_id','a.user_status','b.*'])
            ->paginate(10, $total, ['query' => ['s' => 'admin/billrate/set_warningvalue'], 'var_page' => 'page']);
        $page  = $list->render();
        $list  = $list->toArray()['data'];
        return view('inspection/warningvalue', compact('title', 'page', 'list'));
    }

    /**
     * 设置查验峰值
     * @param Request $request
     * @return mixed
     */
    public function setBillCheckWarningValue(Request $request)
    {
        try{
            $this->logic->setBillRatePeak($request->post());
        }catch (\Exception $e){
            return json(['code'=>-1,'message'=>$e->getMessage()]);
        }
        return json(['code'=>0,'message'=>'已设置']);
    }


    /**
     * 禁用用户
     * @param Request $request
     * @return mixed
     */
    public function disableUser(Request $request)
    {
        if (!$request->has('uid')&&!is_numeric($request->get('uid'))){
            return json(['code'=>-1,'message'=>'参数错误']);
        }
        if (!$request->has('type')&&$request->get('type')==''){
            return json(['code'=>-1,'message'=>'参数错误']);
        }
        switch ($request->get('type')){
            case 'lock':
                Db::name('decl_user')->where('id',$request->get('uid'))->update(['user_status'=>1]);
                Db::name('decl_user')->where('parent_id',$request->get('uid'))->update(['user_status'=>1]);
                break;
            case 'open':
                Db::name('decl_user')->where('id',$request->get('uid'))->update(['user_status'=>0]);
                Db::name('decl_user')->where('parent_id',$request->get('uid'))->update(['user_status'=>0]);
                break;
            default:
                break;
        }
        return json(['code'=>0,'message'=>'操作成功']);
    }

    /**
     * 控制上传查验文件
     */
    public function uploadBillCheckFileCondition(Request $request)
    {
        if ($request->isGET()){
           $list = $this->logic->getUserLists();
            $page = $list->render();
            $data = $list->toArray()['data'];
            $title = '管理上传查验文件';
            foreach ($data as &$val){
                $val['isSelect'] = [];
                if (empty($val['file_type'])){
                    continue;
                }
                $val['file_type'] = json_decode($val['file_type'],true);
                 foreach ($val['file_type']  as $key=> $value){
                     array_push($val['isSelect'],$value['value']);
                 }
            }
            return view('inspection/user_list',compact('page','data','title'));
        }
        if ($request->isPOST()){
            try{
                $this->logic->uploadBillCheckFileConditions($request->post());
            }catch (\Exception $e){
                return json(['code'=>-1,'message'=>$e->getMessage()]);
            }
            return json(['code'=>0,'message'=>'操作成功']);
        }
    }
}
