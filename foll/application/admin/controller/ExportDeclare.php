<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;
use app\admin\model\CustomsExportOrderHead;
use app\admin\model\CustomsExportPaymentSlipHead;
use app\admin\model\CustomsExportLogisticsWaybillHead;
use app\admin\model\CustomsZXExportLogisticsWaybillHead;
use app\admin\model\CustomsExportDeclarationlistHead;
use app\admin\model\CustomsExportInventoryTotalscoreHead;
use app\admin\model\DeclUserModel;
use app\admin\model\CustomsExportDepartureticketHead;
use app\admin\model\CustomsExportCollectApplyHead;
use app\admin\model\CustomsPortplatforminfo;
use app\admin\model\CustomsxportDeclHead;
use app\admin\model\CustomsExportDeclarationlistCancel;

class ExportDeclare extends Auth
{


    public function getLading(Request $request)
    {
        $model = $this->getObjecType($request->get('type'));
        if (empty($request->get('search'))) {
            //$limit = $request->get('limit');
            $limit = $request->get('offset').','.$request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = $model->count();
            $data = $model->order(trim($request->get('sort')), trim($request->get('order')))->limit($limit)->select();
        } else {
            $total = 1;
            $data = $model->where('tracking_num', trim($request->get('search')))->select();
        }
        $user = new DeclUserModel();
        $appType = [1 => '新增', 2 => '变更', 3 => '删除'];
        foreach ($data as &$item) {
            $item['app_type'] = $appType[$item['app_type']];
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            $item['user_name'] = $user->getUserNameById($item->uid)['user_name'];
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    public function zxgetLading(Request $request)
    {
        $model = $this->zxgetObjecType($request->get('type'));
        if (empty($request->get('search'))) {
            //$limit = $request->get('limit');
            $limit = $request->get('offset').','.$request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = $model->count();
            $data = $model->order(trim($request->get('sort')), trim($request->get('order')))->limit($limit)->select();
        } else {
            $total = 1;
            $data = $model->where('tracking_num', trim($request->get('search')))->select();
        }
        $user = new DeclUserModel();
        $appType = [1 => '新增', 2 => '变更', 3 => '删除'];
        foreach ($data as &$item) {
            $item['app_type'] = $appType[$item['app_type']];
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            $item['user_name'] = $user->getUserNameById($item->uid)['user_name'];
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    /**
     * @param $type
     * @return CustomsExportDeclarationlistHead|CustomsExportInventoryTotalscoreHead|CustomsExportLogisticsWaybillHead|CustomsExportOrderHead|CustomsExportPaymentSlipHead
     */
    protected function getObjecType($type)
    {
        switch ($type) {
            case 'CEB303Message':
                //电子订单数据
                return new CustomsExportOrderHead();
            case 'CEB403Message':
                //收款单数据
                return new CustomsExportPaymentSlipHead();
            case 'CEB505Message':
                //物流运单数据
                return new CustomsExportLogisticsWaybillHead();
            case 'CEB603Message':
                return new CustomsExportDeclarationlistHead();
            case 'CEB607Message':
                return new CustomsExportInventoryTotalscoreHead();
            case 'CEB509Message':
                return new CustomsExportDepartureticketHead();
            case 'CEB701Message':
                return new CustomsExportCollectApplyHead();
            case 'CEB605Message':
                return new CustomsExportDeclarationlistCancel();
        }
    }

    protected function zxgetObjecType($type)
    {
        switch ($type) {
            case 'CEB303Message':
                //电子订单数据
                return new CustomsExportOrderHead();
            case 'CEB403Message':
                //收款单数据
                return new CustomsExportPaymentSlipHead();
            case 'CEB505Message':
                //物流运单数据
                return new CustomsZXExportLogisticsWaybillHead();
            case 'CEB603Message':
                return new CustomsExportDeclarationlistHead();
            case 'CEB607Message':
                return new CustomsExportInventoryTotalscoreHead();
            case 'CEB509Message':
                return new CustomsExportDepartureticketHead();
            case 'CEB701Message':
                return new CustomsExportCollectApplyHead();
            case 'CEB605Message':
                return new CustomsExportDeclarationlistCancel();
        }
    }

    /**
     * 出口电子订单
     * @return mixed
     */
    public function orderExportLadingView()
    {
        return view('export_declare/order_export_lading_list');
    }

    /**
     * 出口电子订单批次列表
     * @return mixed
     */
    public function orderExportBatchView(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/order_export_batch_list', compact('trackingNum'));
    }


    /**
     * 新增订单申报主体
     * @param Request $request
     * @return mixed
     */
    public function addOrderBody(Request $request)
    {
        if($request->isGET()){
            $id=$request->get('id');
            if(!is_numeric($id)){
                return json(['code'=>-1,'msg'=>'参数错误']);
            }
            $cop=CustomsPortplatforminfo::field(['id','area_code','decl_name'])->order('id','desc')->select();
            return view('export_declare/add_order_body',compact('id','cop'));
        }
        $data=$request->post();
        if (!is_numeric($data['id'])){
            return json(['code'=>-1,'msg'=>'参数错误']);
        }
        $oriOrderHeder=CustomsExportOrderHead::where('id',$data['id'])->find();
        $newData=[
            'sid'=>$data['sid']==""?$oriOrderHeder['sid']:$data['sid'],
            'tracking_num'=>$oriOrderHeder['tracking_num'].'-'.mt_rand(11,99),
            'app_type'=>$oriOrderHeder['app_type'],
            'app_time'=>$oriOrderHeder['app_time'],
            'app_status'=>$oriOrderHeder['app_status'],
            'order_type'=>$oriOrderHeder['order_type'],
            'ebp_code'=>$data['ebp_code']==""?$oriOrderHeder['ebp_code']:$data['ebp_code'],
            'ebp_name'=>$data['ebp_name']==""?$oriOrderHeder['ebp_name']:$data['ebp_name'],
            'ebc_code'=>$data['ebc_code']==""?$oriOrderHeder['ebc_code']:$data['ebc_code'],
            'ebc_name'=>$data['ebc_name']==""?$oriOrderHeder['ebc_name']:$data['ebc_name'],
            'parend_id'=>$oriOrderHeder['id'],
            'create_at'=>time(),
        ];
        CustomsExportOrderHead::insert($newData);
        return json(['code'=>0,'msg'=>"新增成功"]);
    }

    /**
     * 新增订单提单合并
     * @param Request $request
     * @return mixed
     */
    public function newOrderMerge(Request $request)
    {
        if($request->isGET()){
            $cop=CustomsPortplatforminfo::field(['id','area_code','decl_name'])->order('id','desc')->select();
            $trackingInfo=CustomsExportOrderHead::field(['id','tracking_num'])->order('id','desc')->select();
            return view('export_declare/new_order_merge',compact('cop','trackingInfo'));
        }
    }

    /**
     * 出口支付单
     * @return mixed
     */
    public function paymentSlipExportLadingView()
    {
        return view('export_declare/payment_slip_export_lading_list');
    }

    /**
     * 出口支付单批次列表
     * @return mixed
     */
    public function paymentSlipExportBatchView(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/payment_slip_export_batch_list', compact('trackingNum'));
    }

    /**
     * 物流运单数据出口提单
     * @return mixed
     */
    public function logisticsWaybillExportLadingView()
    {
        return view('export_declare/logistics_waybill_export_lading_list');
    }

    //中新物流
    public function zxlogisticsWaybillExportLadingView()
    {
        return view('export_declare/zx_logistics_waybill_export_lading_list');
    }

    /**
     * 物流运单数据出口批次
     * @return mixed
     */
    public function logisticsWaybillExportBatchView(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/logistics_waybill_export_batch_list', compact('trackingNum'));
    }

    public function logisticsWaybillExportBillnoView(Request $request)
    {
        $batchNum = $request->get('batch_num');
        return view('export_declare/logistics_waybill_export_billno_list', compact('batchNum'));
    }

    public function getLogisticsNo(Request $request)
    {
        $batch_num = $request->get('batch_num');
        $limit = $request->post('limit');
        $page = $request->post('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
        $total = Db::name('customs_export_logistics_waybill_list')->where('batch_num',$batch_num)->count();
        $data = Db::name('customs_export_logistics_waybill_list')->where('batch_num',$batch_num)->order(trim($request->post('sort')), trim($request->post('order')))->limit($page, $limit)->select();
        foreach ($data as &$item) {
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    public function zxlogisticsWaybillExportBatchView(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/zx_logistics_waybill_export_batch_list', compact('trackingNum'));
    }

    /**
     * 出口清单申报
     * @return mixed
     */
    public function inventoryExportLadingView()
    {
        return view('export_declare/inventory_export_lading_list');
    }

    /**
     * 出口清单撤销申报
     * @return mixed
     */
    public function inventoryExportCancelLadingView()
    {
        return view('export_declare/inventory_export_cancel_lading_list');
    }

    /**
     * 出口清单申报批次
     * @param Request $request
     * @return mixed
     */
    public function inventoryExportBatchView(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/inventory_export_batch_list', compact('trackingNum'));
    }

    /**
     * 出口清单申报批次
     * @param Request $request
     * @return mixed
     */
    public function inventoryExportCancelBatchView(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/inventory_export_cancel_batch_list', compact('trackingNum'));
    }

    /**
     * 出口清单总分单(总运单)
     * @return mixed
     */
    public function inventoryTotalScoreExportLading()
    {
        return view('export_declare/inventory_total_score_export_lading');
    }

    /**
     * 出口清单总分单(总运单)批次
     * @param Request $request
     * @return mixed
     */
    public function inventoryTotalScoreExportBatch(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/inventory_total_score_export_batch', compact('trackingNum'));
    }


    /**
     * 出口物流离境单
     * @return mixed
     */
    public function logisticsDepartureListLading()
    {
        return view('export_declare/logistics_departure_list_lading');
    }

    /**
     * 出口物流离境单批次
     * @param Request $request
     * @return mixed
     */
    public function logisticsDepartureListBatch(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/logistics_departure_list_batch', compact('trackingNum'));
    }

    /**
     * 出口汇总申请
     * @return mixed
     */
    public function collectApplyLading()
    {
        return view('export_declare/collect_apply_lading');
    }

    /**
     * 出口汇总批次
     * @param Request $request
     * @return mixed
     */
    public function collectApplyBatch(Request $request)
    {
        $trackingNum = $request->get('tracking_num');
        return view('export_declare/collect_apply_batch',compact('trackingNum'));
    }


    /**
     * 报关单列表
     * @param Request $request
     * @return mixed
     */
    public function customsDeclarationList(Request $request)
    {
        // admin/export/customsDeclarationList
        if($request->isGET()){
            return view('export_declare/customs_declaration_list');
        }
        $model = new CustomsxportDeclHead();
        if (empty($request->post('search'))) {
            $limit = $request->post('limit');
            $page = $request->post('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = $model->count();
            $data = $model->order(trim($request->post('sort')), trim($request->post('order')))->limit($page, $limit)->select();
        } else {
            $total = 1;
            $data = $model->where('tracking_num', trim($request->post('search')))->select();
        }
        $user = new DeclUserModel();
        $appType = ['一般报关单','转关提前报关单'];
        foreach ($data as &$item) {
            $item['decl_trn_rel']=$appType[$item['decl_trn_rel']];
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            $item['user_name'] = $user->getUserNameById($item->uid)['user_name'];
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    /**
     * 报关单批次列表
     * @param Request $request
     * @return mixed
     */
    public function customsDeclarationBatchList(Request $request)
    {
        $trackingNum=$request->get('tracking_num');
        return view('export_declare/customs_declaration_batch_list',compact('trackingNum'));
    }

}