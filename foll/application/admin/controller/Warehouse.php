<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use think\Response;
use Excel5;
use PHPExcel_IOFactory;

class Warehouse extends Auth
{
    #订单列表
    public function order_list(Request $request)
    {
        $dat = input();
        if (request()->isPost() || request()->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $map = array();
            $list = Db::name('customs_warehouse_order')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                if($v['type']==1){
                    $list[$k]['type_name'] = '仓库存储';
                }else if($v['type']==2){
                    $list[$k]['type_name'] = '仓库操作';
                }else if($v['type']==3){
                    $list[$k]['type_name'] = '仓储物流';
                }

                if ($v['status'] == 1) {
                    $list[$k]['status'] = '已生成待确认';
                } elseif ($v['status'] == 2) {
                    $list[$k]['status'] = '已确认已修订';
                } elseif ($v['status'] == 3) {
                    $list[$k]['status'] = '已确认冻结';
                }
                $list[$k]['url'] = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=warehouse&op=sureOrderList&m=sz_yi&id=' . $v['id'];
                $list[$k]['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                $list[$k]['manage'] = '<button type="button" onclick="edit(' . "'查看','" . Url('admin/warehouse/edit_lists') . "'" . ',' . "'" . $v['id'] . "'" . ')" class="btn btn-primary btn-xs" style="margin-right: 10px;">查看</button>';
            }
            $total = Db::name('customs_warehouse_order')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        } else {
            return view('warehouse/order_list');
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
            $content = json_encode([
                'event_date'=>$dat['event_date'],
                'event_type'=>$dat['event_type'],
                'event_name'=>$dat['event_name'],
                'event_unit'=>$dat['event_unit'],
                'event_currency'=>$dat['event_currency'],
                'event_price'=>$dat['event_price'],
                'event_num'=>$dat['event_num'],
                'event_totalprice'=>$dat['event_totalprice'],
                'event_remark'=>$dat['event_remark'],
            ],true);
            $res = Db::name('customs_warehouse_order')->insert([
                'type'=>intval($dat['type']),
                'payer_name'=>trim($dat['payer_name']),
                'pay_term'=>trim($dat['pay_term']),
                'pay_fee'=>trim($dat['pay_fee']),
                'content'=>$content,
                'status'=>1,
                'createtime'=>time(),

                'currency_backup'=>$dat['currency_backup'],
                'currency'=>$dat['currency'],
                'price'=>trim($dat['price']),
                'exchange_rate'=>$dat['currency_backup']==142?0:trim($dat['exchange_rate']),
                'is_tax'=>intval($dat['is_tax']),
                'tax_type'=>intval($dat['is_tax'])==2?intval($dat['tax_type']):0,
                'tax_num'=>intval($dat['is_tax'])==2?trim($dat['tax_num']):0,
                'invoicing_tax'=>trim($dat['invoicing_tax']),
                'real_price'=>trim($dat['real_price']),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功!']);
            }
        }else{
            $unit = Db::name('unit')->select();
            $currency = Db::name('currency')->select();
//            $tax = Db::name('customs_tax_set')->where(['id'=>1])->find();

            $order = ['id'=>0,'type'=>'','content'=>'','currency_backup'=>'','currency'=>'','price'=>'','exchange_rate'=>'','is_tax'=>'','tax_type'=>'','tax_num'=>'','payer_name'=>'','invoicing_tax'=>'','pay_term'=>'','pay_fee'=>'','real_price'=>''];
            if($id>0){
                $order = Db::name('customs_warehouse_order')->where(['id'=>$id])->find();
                $order['content'] = json_decode($order['content'],true);
            }

            return view('warehouse/edit_lists',compact('unit','order','currency'));
        }
    }
}