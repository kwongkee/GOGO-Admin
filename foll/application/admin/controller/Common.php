<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use app\admin\model\CustomsExportBillofladingBatch;
use app\admin\model\CustomsZXExportBillofladingBatch;
use think\Db;


class Common extends Auth
{
    /**
     * 获取出口申报类型批次信息
     * @param Request $request
     * @return mixed
     */
    public function getExportBatchInfoList(Request $request)
    {
        $type=$request->get('btype');
        if (empty($request->get('search'))) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = CustomsExportBillofladingBatch::where(['tracking_num'=>$request->get('tracking_num'),'type'=>$type])->count();
            $data = CustomsExportBillofladingBatch::where(['tracking_num'=>$request->get('tracking_num'),'type'=>$type])->order(trim($request->get('sort')), trim($request->get('order')))->limit($page, $limit)->select();
        } else {
            $total = 1;
            $data = CustomsExportBillofladingBatch::where(['batch_num'=>trim($request->get('search')),'type'=>$type])->select();
        }
        $status = [0 => '待验证', 1 => '待提交', 3 => '待申报', 4 => '已申报', 5 => '申请撤回', 6 => '已撤回'];
        foreach ($data as &$item) {
            $item['batch_status'] = $status[$item['batch_status']];
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
            //20210910
            $order_head = Db::name('customs_export_order_head')->where('tracking_num',$item['tracking_num'])->field(['sid'])->find();
            $platform = Db::name('customs_portplatforminfo')->where('id',$order_head['sid'])->field(['isFtp'])->find();
            if($platform['isFtp']==2){
                $item['is_need_xml'] = 1;
                if($item['type']=='CEB303Message'){
                    $order_list = Db::name('customs_export_order_list')->where('batch_num',$item['batch_num'])->field(['order_action_message'])->find();
                    $item['xml_url'] = $order_list['order_action_message'];
                }else if($item['type']=='CEB403Message'){
                    $order_list = Db::name('customs_export_payement_slip_list')->where('batch_num',$item['batch_num'])->field(['action_message'])->find();
                    $item['xml_url'] = $order_list['action_message'];
                }else if($item['type']=='CEB603Message'){
                    $order_list = Db::name('customs_export_declarationlist_list')->where('batch_num',$item['batch_num'])->field(['action_message'])->find();
                    $item['xml_url'] = $order_list['action_message'];
                }else if($item['type']=='CEB605Message'){
                    $order_list = Db::name('customs_export_declarationlist_cancel')->where('batch_num',$item['batch_num'])->field(['action_message'])->find();
                    $item['xml_url'] = $order_list['action_message'];
                }else if($item['type']=='CEB607Message'){
                    $order_list = Db::name('customs_export_inventory_totalscore')->where('batch_num',$item['batch_num'])->field(['action_message'])->find();
                    $item['xml_url'] = $order_list['action_message'];
                }else if($item['type']=='DecMessage'){
                    $order_list = Db::name('customs_export_order_list')->where('batch_num',$item['batch_num'])->field(['order_action_message'])->find();
                    $item['xml_url'] = $order_list['order_action_message'];
                }

//                $item['xml_url'] = str_replace('/www/wwwroot/default/gogo/','http://decl.gogo198.cn/',$order_list['order_action_message']);

            }else{
                $item['is_need_xml'] = 0;
            }
        }

        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    //下载xml
    public function downloadxml(Request $request){
        print_r($request->get('url'));die;
    }

    //中新物流
    public function zxgetExportBatchInfoList(Request $request)
    {
        $type=$request->get('btype');
        if (empty($request->get('search'))) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = CustomsZXExportBillofladingBatch::where(['tracking_num'=>$request->get('tracking_num'),'type'=>$type])->count();
            $data = CustomsZXExportBillofladingBatch::where(['tracking_num'=>$request->get('tracking_num'),'type'=>$type])->order(trim($request->get('sort')), trim($request->get('order')))->limit($page, $limit)->select();
        } else {
            $total = 1;
            $data = CustomsZXExportBillofladingBatch::where(['batch_num'=>trim($request->get('search')),'type'=>$type])->select();
        }
        $status = [0 => '待验证', 1 => '待提交', 3 => '待申报', 4 => '已申报', 5 => '申请撤回', 6 => '已撤回'];
        foreach ($data as &$item) {
            $item['batch_status'] = $status[$item['batch_status']];
            $item['create_at'] = date('Y-m-d H:i:s', $item['create_at']);
        }
        return json(['message' => "", 'status' => 0, 'total' => $total, 'rows' => $data]);
    }

    /**
     * 出口申报批次申请撤回
     * @param Request $request
     * @return mixed
     */
    public function exportBatchApplyRecall(Request $request)
    {
        if (!is_numeric($request->get('id'))) {
            return json(['code' => -1, 'msg' => '非法请求']);
        }
        CustomsExportBillofladingBatch::where('id', $request->get('id'))->update(['batch_status' => 6]);
        return json(['code' => 0, 'msg' => '完成']);
    }
}