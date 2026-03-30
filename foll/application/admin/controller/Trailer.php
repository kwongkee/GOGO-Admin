<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use think\Response;
use Excel5;
use PHPExcel_IOFactory;

class Trailer extends Auth{
    #税率设置
    public function tax_set(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $res = Db::name('customs_tax_set')->where(['id'=>1])->update(['tax'=>trim($dat['tax'])]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功!']);
            }
        }else{
            $tax = Db::name('customs_tax_set')->where(['id'=>1])->find();
            return view('trailer/tax_set',compact('tax'));
        }
    }

    #订单列表
    public function order_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();
            $list = Db::name('customs_freight_order')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                if($v['status']==1){
                    $list[$k]['status'] = '已生成待确认';
                }elseif($v['status']==2){
                    $list[$k]['status'] = '已确认已修订';
                }elseif($v['status']==3){
                    $list[$k]['status'] = '已确认冻结';
                }
                $list[$k]['url'] = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=freight&op=sureOrderList&m=sz_yi&id='.$v['id'];
                $list[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $list[$k]['manage'] = '<button type="button" onclick="edit('."'查看','".Url('admin/trailer/edit_lists')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs" style="margin-right: 10px;">查看</button>';
            }
            $total = Db::name('customs_freight_order')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view('trailer/order_list');
        }
    }

    #查看订单详情
    public function edit_lists(Request $request){
        $dat = input();

        if(isset($dat['id'])){
            $id = intval($dat['id']);
        }else{
            $id = 0;
        }

        if ( request()->isPost() || request()->isAjax()) {

            $ins_data = [
                'fPort'=>trim($dat['fPort']),
                'sPort'=>trim($dat['sPort']),
                'lading_no'=>trim($dat['lading_no']),
                'ship_name'=>trim($dat['ship_name']),
                'voyage'=>trim($dat['voyage']),
                'destination_port'=>trim($dat['destination_port']),

                'factory_address'=>trim($dat['factory_address']),
                'factory_contacter'=>trim($dat['factory_contacter']),
                'factory_mobile'=>trim($dat['factory_mobile']),
                'is_penalty'=>intval($dat['is_penalty']),
                'approach_idea'=>intval($dat['is_penalty'])==2?trim($dat['approach_idea']):'',
                'is_baoshui'=>intval($dat['is_baoshui']),
                'is_beian'=>intval($dat['is_baoshui'])==2?intval($dat['is_beian']):'',
                'data_service'=>intval($dat['data_service']),
                'data_service3'=>intval($dat['data_service'])==3?intval($dat['data_service3']):'',

                'making_date'=>strtotime($dat['making_date']),
                'estimate_weight'=>trim($dat['estimate_weight']),
                'box_type'=>trim($dat['box_type']),
                'box_num'=>trim($dat['box_num']),
                'making_requrest'=>trim($dat['making_requrest']),

                'is_wait'=>intval($dat['is_wait']),
                'is_wait2'=>intval($dat['is_wait'])==2?intval($dat['is_wait2']):'',
                'end_date'=>strtotime($dat['end_date']),
                'is_entrust'=>intval($dat['is_entrust']),

                'currency'=>$dat['currency'],
                'price'=>trim($dat['price']),
                'exchange_rate'=>$dat['currency']==142?0:trim($dat['exchange_rate']),
                'is_tax'=>intval($dat['is_tax']),
                'tax_type'=>intval($dat['is_tax'])==2?intval($dat['tax_type']):0,
                'tax_num'=>intval($dat['is_tax'])==2?trim($dat['tax_num']):0,
                'invoicing_tax'=>trim($dat['invoicing_tax']),
                'real_price'=>trim($dat['real_price']),
                'payer_name'=>trim($dat['payer_name']),

                'status'=>1,
                'pay_term'=>trim($dat['pay_term']),
                'pay_fee'=>trim($dat['pay_fee']),
                'createtime'=>time(),
            ];
//            'qrcode'=>'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=sureOrderList&m=sz_yi&id=',
            $res = Db::name('customs_freight_order')->insert($ins_data);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功!']);
            }
        }else{
//            $tax = Db::name('customs_tax_set')->where(['id'=>1])->find();
            $order = ['id'=>0,'fPort'=>'','sPort'=>'','lading_no'=>'','ship_name'=>'','voyage'=>'','destination_port'=>'','factory_address'=>'','factory_contacter'=>'','factory_mobile'=>'','is_penalty'=>'','approach_idea'=>'','is_baoshui'=>'','is_beian'=>'','data_service'=>'','data_service3'=>'','making_date'=>'','estimate_weight'=>'','box_type'=>'','box_num'=>'','making_requrest'=>'','is_wait'=>'','is_wait2'=>'','end_date'=>'','is_entrust'=>'','currency'=>'','price'=>'','exchange_rate'=>'','is_tax'=>'','tax_type'=>'','tax_num'=>'','payer_name'=>'','invoicing_tax'=>'','pay_term'=>'','pay_fee'=>'','real_price'=>''];
            $sPort = '';
            if($id>0){
                $order = Db::name('customs_freight_order')->where(['id'=>$id])->find();
                $sPort = Db::name('customs_freight_port_name')->where(['code'=>$order['sPort']])->find();
            }
            $fPort = Db::name('customs_freight_port_name')->where('pid',0)->select();
            $currency = Db::name('currency')->select();

            return view('trailer/edit_lists',compact('order','fPort','sPort','currency'));
        }
    }

    //获取子港口sport
    public function get_sport(Request $request){
        $dat = input();
        $fport = Db::name('customs_freight_port_name')->where(['code'=>trim($dat['val'])])->find();
        $sport = Db::name('customs_freight_port_name')->where(['pid'=>$fport['id']])->select();
        return json(['code'=>0,'data'=>$sport]);
    }
}