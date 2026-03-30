<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Db;
use app\admin\model\DeclUserModel;
use app\admin\model\CustomsExportBeforehandDetailedlist;
use app\admin\model\CustomsExportBeforehandDeclarationlist;
use app\admin\model\CustomsExportBeforehandTransferlist;

/**
 * 出口还原申报（订单和清单）
 * Class Reduction
 * @package app\admin\controller
 */
class Reduction extends Auth
{
    //还原订单列表
    public function orderlist(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = Db::name('reduction_orderlist')->count();
            $data =  Db::name('reduction_orderlist')->order(trim($request->get('sort')), trim($request->get('order')))->limit($page, $limit)->select();
            foreach($data as $k=>$v){
                $data[$k]['beginDate'] = date('Y-m-d H:i:s',$v['beginDate']);
                $data[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page, 'rows' => $data]);
        }else{
            return view('reduction/orderlist');
        }
    }
    //生成订单列表
    public function generate_orderlist(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax())
        {
            $dat['ordersn'] = trim($dat['ordersn']);
            $dat['ebp_name'] = trim($dat['ebp_name']);
            $dat['ebc_name'] = trim($dat['ebc_name']);
            $dat['beginDate'] = strtotime(trim($dat['beginDate']));
            $dat['createtime'] = time();

            $res = Db::name('reduction_orderlist')->insert($dat);
            if($res){
                return json(['code'=>1,'msg'=>'还原成功！']);
            }
            return json(['code'=>-1,'msg'=>'系统错误！']);
        }else{
            $ebc = Db::name('reduction_ebc_body')->order('createtime','desc')->select();
            $date = date('Y-m-d H:i:s',time());
            return view('reduction/generate_orderlist',compact('date','ebc'));
        }
    }

    //还原清单列表
    public function declareorderlist(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = Db::name('reduction_declareorderlist')->count();
            $data =  Db::name('reduction_declareorderlist')->order(trim($request->get('sort')), trim($request->get('order')))->limit($page, $limit)->select();
            foreach($data as $k=>$v){
                $data[$k]['beginDate'] = date('Y-m-d H:i:s',$v['beginDate']);
                $data[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page, 'rows' => $data]);
        }else{
            return view('reduction/declareorderlist');
        }
    }
    //还原清单列表
    public function generate_declareorderlist(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax())
        {
            $dat['beginDate'] = strtotime(trim($dat['beginDate']));
            $dat['createtime'] = time();

            $res = Db::name('reduction_declareorderlist')->insert($dat);
            if($res){
                return json(['code'=>1,'msg'=>'还原成功！']);
            }
            return json(['code'=>-1,'msg'=>'系统错误！']);
        }else{
            $ebc = Db::name('reduction_ebc_body')->order('createtime','desc')->select();
            $date = date('Y-m-d H:i:s',time());
            return view('reduction/generate_declareorderlist',compact('ebc','date'));
        }
    }

    //添加申报信息，卡号、电商企业
    public function add_body(request $request){
        $dat = input();
        if(request()->isPost() || request()->isAjax()){
            $dat['createtime'] = time();
            $ishave = Db::name('reduction_ebc_body')->where('ebc_name',$dat['ebc_name'])->where('ebc_number',$dat['ebc_number'])->find();
            if($ishave){
                return json(['code'=>-1,'msg'=>'该电商企业已添加！']);
            }
            $res = Db::name('reduction_ebc_body')->insert($dat);
            if($res){
                return json(['code'=>1,'msg'=>'添加成功！']);
            }
        }else{
            return view('reduction/add_body');
        }
    }

    //订单批次编号关联
    public function connect_order(Request $request){
        $dat = input();
        if(!empty($dat['batch_num'])){
            $list = Db::name('customs_export_order_list')
            ->alias('a')
            ->join('customs_export_order_head b',"a.hid=b.id",'left')
            ->field(['a.ordersn','b.ebc_name','b.ebp_name'])
            ->where(['a.batch_num' => trim($dat['batch_num'])])
            ->find();

            if(empty($list['ordersn'])){
                return json(['code'=>-1,'msg'=>'查找不到有关数据！']);
            }
            return json(['code'=>1,'list'=>$list,'msg'=>'关联成功！']);
        }
    }

    //清单批次编号关联
    public function connect_declareorder(Request $request){
        $dat = input();
        if(!empty($dat['batch_num'])){
            $list = Db::name('customs_export_declarationlist_list')
                ->alias('a')
                ->join('customs_export_declarationlist_head b',"a.hid=b.id",'left')
                ->join('customs_portplatforminfo c',"c.id=b.sid",'left')
                ->field(['a.ordersn','a.logistics_no','b.owner_name','b.traf_mode','b.voyage_no','b.tracking_num','b.total_package_no','b.ebc_name','b.ebp_name','b.statistics_flag','a.cop_no','c.vpn_account'])
                ->where(['a.batch_num' => trim($dat['batch_num'])])
                ->find();
            $list['traf_mode'] = Db::name('transport')->where('code_value',$list['traf_mode'])->field('code_name')->find()['code_name'];
            if(empty($list['ordersn'])){
                return json(['code'=>-1,'msg'=>'查找不到有关数据！']);
            }
            return json(['code'=>1,'list'=>$list,'msg'=>'关联成功！']);
        }
    }

    //订单单一窗口
    public function single_window(Request $request){
        $dat = input();
        if(request()->isPost() || request()->isAjax()){

        }else{
            $list = Db::name('reduction_orderlist')->where('id',$dat['id'])->find();

            switch($list['appStatusId']){
                case 0:
                    $list['appStatusId'] = '全部';
                    break;
                case 1:
                    $list['appStatusId'] = '暂存';
                    break;
                case 100:
                    $list['appStatusId'] = '海关退单';
                    break;
                case 120:
                    $list['appStatusId'] = '海关入库';
                    break;
                case 2:
                    $list['appStatusId'] = '申报';
                    break;
                case 3:
                    $list['appStatusId'] = '发送海关成功';
                    break;
                case 4:
                    $list['appStatusId'] = '发送海关失败';
                    break;
                default:
                    $list['appStatusId']='';
                    break;
            }

            switch($list['orderTypeId']){
                case '0':
                    $list['orderTypeId']='全部';
                    break;
                case 'B':
                    $list['orderTypeId']='B2B出口订单';
                    break;
                case 'E':
                    $list['orderTypeId']='B2C出口订单';
                    break;
                case 'W':
                    $list['orderTypeId']='海外仓订仓单';
                    break;
                default:
                    $list['orderTypeId']='';
                    break;
            }
            $endDate = $list['beginDate']+604800;
            $list['beginDate'] = date('Y-m-d H:i:s',$list['beginDate']);
            $list['endDate'] = date('Y-m-d H:i:s',$endDate);


            //申报主体
            $list['ebc_body'] = Db::name('reduction_ebc_body')->where('id',$list['ebc_body_id'])->find();
            return view('reduction/order_single_window',compact('list'));
        }
    }

    //清单-单一窗口
    public function declare_single_window(Request $request){
        $dat = input();
        if(request()->isPost() || request()->isAjax()){

        }else{
            $list = Db::name('reduction_declareorderlist')->where('id',$dat['id'])->find();

//            switch($list['appStatusId']){
//                case 0:
//                    $list['appStatusId'] = '全部';
//                    break;
//                case 1:
//                    $list['appStatusId'] = '暂存';
//                    break;
//                case 100:
//                    $list['appStatusId'] = '海关退单';
//                    break;
//                case 120:
//                    $list['appStatusId'] = '海关入库';
//                    break;
//                case 2:
//                    $list['appStatusId'] = '申报';
//                    break;
//                case 3:
//                    $list['appStatusId'] = '发送海关成功';
//                    break;
//                case 4:
//                    $list['appStatusId'] = '发送海关失败';
//                    break;
//                default:
//                    $list['appStatusId']='';
//                    break;
//            }
//
//            switch($list['orderTypeId']){
//                case '0':
//                    $list['orderTypeId']='全部';
//                    break;
//                case 'B':
//                    $list['orderTypeId']='B2B出口订单';
//                    break;
//                case 'E':
//                    $list['orderTypeId']='B2C出口订单';
//                    break;
//                case 'W':
//                    $list['orderTypeId']='海外仓订仓单';
//                    break;
//                default:
//                    $list['orderTypeId']='';
//                    break;
//            }
            $endDate = $list['beginDate']+604800;
            $list['beginDate'] = date('Y-m-d H:i:s',$list['beginDate']);
            $list['endDate'] = date('Y-m-d H:i:s',$endDate);


            //申报主体
            $list['ebc_body'] = Db::name('reduction_ebc_body')->where('id',$list['ebc_body_id'])->find();
            return view('reduction/declareorder_single_window',compact('list'));
        }
    }
}