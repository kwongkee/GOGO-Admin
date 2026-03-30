<?php
namespace app\admin\controller;

use think\Db;
use think\Request;
use think\Response;
use Excel5;
use PHPExcel_IOFactory;

class Crossorder extends Auth
{
    #订单详情
    public function orderdetail_list(Request $request){
        $dat = input();
        if (request()->isPost() || request()->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $map = array();
            $list = Db::name('customs_crossorder_detail')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                if($v['type']==1){
                    $list[$k]['type'] = '拖车';
                }elseif($v['type']==2){
                    $list[$k]['type'] = '仓储';
                }
                if($v['type2']==1){
                    $list[$k]['type2'] = '运费';
                }elseif($v['type2']==2){
                    $list[$k]['type2'] = '超重费';
                }elseif($v['type2']==3){
                    $list[$k]['type2'] = '报关费';
                }elseif($v['type2']==7){
                    $list[$k]['type2'] = '港杂费';
                }elseif($v['type2']==4){
                    $list[$k]['type2'] = '仓库存储';
                }elseif($v['type2']==5){
                    $list[$k]['type2'] = '仓库操作';
                }elseif($v['type2']==6){
                    $list[$k]['type2'] = '仓储物流';
                }
                $list[$k]['url'] = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=crossborder&op=order_detail&m=sz_yi&id=' . $v['id'];
                $list[$k]['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                $list[$k]['manage'] = '<button type="button" onclick="edit(' . "'查看','" . Url('admin/crossorder/edit_lists') . "'" . ',' . "'" . $v['id'] . "'" . ')" class="btn btn-primary btn-xs" style="margin-right: 10px;">查看</button>';
            }
            $total = Db::name('customs_crossorder_detail')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        } else {
            return view('crossorder/orderdetail_list');
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
            if(isset($dat['company_file'])){
                $dat['content']['declare_file'] = $dat['company_file'];
            }
            $content = json_encode($dat['content'],true);
            $res = Db::name('customs_crossorder_detail')->insert([
                'type'=>intval($dat['type']),
                'type2'=>intval($dat['type2']),
                'content'=>$content,
                'status'=>1,
                'createtime'=>time()
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功!']);
            }
        }else{
            $unit = Db::name('unit')->select();
            $currency = Db::name('currency')->select();
            $fPort = Db::name('customs_freight_port_name')->where('pid',0)->select();
            $sPort = '';
            
            $order = ['id'=>0,'type'=>'','type2'=>'','content'=>['lading_no'=>'','fPort'=>'','sPort'=>'','lading_no'=>'','ship_name'=>'','voyage'=>'','destination_port'=>'','factory_address'=>'','factory_contacter'=>'','factory_mobile'=>'','is_penalty'=>'','approach_idea'=>'','is_baoshui'=>'','is_beian'=>'','data_service'=>'','data_service3'=>'','making_date'=>'','estimate_weight'=>'','box_type'=>'','box_num'=>'','making_requrest'=>'','is_wait'=>'','is_wait2'=>'','end_date'=>'','is_entrust'=>'','weight_currency'=>'','weight_money'=>'','price'=>'','exchange_rate'=>'','is_tax'=>'','tax_type'=>'','tax_num'=>'','payer_name'=>'','grosswt'=>'','netwt'=>'','money'=>'','currency'=>'','baoguan_file'=>'','event_date'=>'','event_type'=>'','event_name'=>'','event_unit'=>'','event_price'=>'','event_currency'=>'','event_num'=>'','event_totalprice'=>'','event_remark'=>'','freight_currency'=>'','freight_money'=>'','declare_currency'=>'','declare_money'=>'','incidental_currency'=>'','incidental_money'=>'']];
            if($id>0){
                $order = Db::name('customs_crossorder_detail')->where(['id'=>$id])->find();
                $order['content'] = json_decode($order['content'],true);
                if(!empty($order['content']['sPort'])){
                    $sPort = Db::name('customs_freight_port_name')->where(['code'=>$order['content']['sPort']])->find();
                }
            }

            return view('crossorder/edit_lists',compact('unit','order','currency','fPort','sPort'));
        }
    }

    #订单列表
    public function order_list(Request $request){
        $dat = input();
        if (request()->isPost() || request()->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $map = array();
            $list = Db::name('customs_crossorder_list')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                $list[$k]['url'] = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=crossborder&op=order_list&m=sz_yi&id=' . $v['id'];
                $list[$k]['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                $list[$k]['manage'] = '<button type="button" onclick="edit(' . "'查看','" . Url('admin/crossorder/edit_olists') . "'" . ',' . "'" . $v['id'] . "'" . ')" class="btn btn-primary btn-xs" style="margin-right: 10px;">查看</button>';
            }
            $total = Db::name('customs_crossorder_list')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        } else {
            return view('crossorder/order_list');
        }
    }

    #主订单详情
    public function edit_olists(Request $request){
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
                'event_url'=>$dat['event_url'],
            ],true);
            if($id>0){
                $res = Db::name('customs_crossorder_list')->where(['id'=>$id])->update([
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
                    'company_id'=>intval($dat['company_id'])
                ]);
            }else{
                $res = Db::name('customs_crossorder_list')->insert([
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
                    'company_id'=>intval($dat['company_id'])
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功!']);
            }
        }else{
            $unit = Db::name('unit')->select();
            $currency = Db::name('currency')->select();
            $enterprise = Db::name('customs_enterprise_info')->select();

            $order = ['id'=>0,'type'=>'','content'=>'','currency_backup'=>'','currency'=>'','price'=>'','exchange_rate'=>'','is_tax'=>'','tax_type'=>'','tax_num'=>'','payer_name'=>'','invoicing_tax'=>'','pay_term'=>'','pay_fee'=>'','real_price'=>'','company_id'=>''];
            if($id>0){
                $order = Db::name('customs_crossorder_list')->where(['id'=>$id])->find();
                $order['content'] = json_decode($order['content'],true);
            }

            return view('crossorder/edit_olists',compact('unit','order','currency','fPort','sPort','enterprise'));
        }
    }

    #下放通知
    public function notice(Request $request){
        $dat = input();
        if (request()->isPost() || request()->isAjax()) {
            $mobile = trim($dat['mobile']);
            $content = trim($dat['content']);
            $post_data = [
                'spid'=>'254560',
                'password'=>'J6Dtc4HO',
                'ac'=>'1069254560',
                'mobiles'=>$mobile,
                'content'=>$content.' 【GOGO】',
            ];
            $post_data = json_encode($post_data,true);
            $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));// 必须声明请求头);
            $res = json_decode($res,true);
            if($res['code']==0){
                $res = Db::name('customs_notice')->insert([
                    'mobile'=>$mobile,
                    'content'=>$content,
                    'is_send'=>1,
                    'createtime'=>time()
                ]);
                if($res){
                    return json(["code" => 0, "msg" => "发送成功"]);
                }
            }else{
                Db::name('customs_notice')->insert([
                    'mobile'=>$mobile,
                    'content'=>$content,
                    'is_send'=>2,
                    'remark'=>json_encode($res,true),
                    'createtime'=>time()
                ]);
                return json(["code" => -1, "msg" => "发送失败"]);
            }

        }else{
            return view('crossorder/notice');
        }
    }
}