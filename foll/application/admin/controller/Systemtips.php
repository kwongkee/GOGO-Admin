<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;

// 提示设置
class Systemtips extends Auth
{
    public function list()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('decl_user_systemtips')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                $list[$k]['type'] = $v['type'] == 1 ? '提示' : '指引';
                $list[$k]['is_force'] = $v['is_force'] == 1 ? '否' : '是';
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" onclick="editInfo('."'编辑','".Url('admin/systemTips/edit')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">编辑</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="delInfo('."'".$v['id']."'".')" class="btn btn-danger btn-xs">删除</button>';
            }
            $total = Db::name('decl_user_systemtips')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    public function gettableinfo()
    {
        $data = input();
        $c = $data['controller_name'];
        $a = $data['function_name'];

        if( $c == 'buyer' && $a == 'personal_add' ) {
            $table = 'ims_decl_user_personal_buyer';
        }else if($c == 'buyer' && $a == 'enterprise_add') {
            $table = 'ims_decl_user_enterprise_buyer';
        }else if($c == 'payment' && $a == 'account_add') {
            $table = 'ims_decl_user_account';
        }else if($c == 'trade' && $a == 'platform_add') {
            $table = 'ims_decl_user_trade_platform';
        }else if($c == 'trade' && $a == 'other_add') {
            $table = 'ims_decl_user_trade_other';
        }else if($c == 'payment' && $a == 'entry_add') {
            $table = 'ims_decl_user_entry';
        }else if($c == 'payment' && $a == 'exchange_add') {
            $table = 'ims_decl_user_exchange';
        }else if($c == 'payment' && $a == 'withdraw_add') {
            $table = 'ims_decl_user_withdraw';
        }else if($c == 'payment' && $a == 'transfer_add') {
            $table = 'ims_decl_user_transfer';
        }else if($c == 'electronport' && $a == 'add') {
            $table = 'ims_customs_portplatforminfo';
        }else if($c == 'onlineexport' && $a == 'ecommerce_add') {
            $table = 'ims_customs_export_order_head';
        }else if($c == 'onlineexport' && $a == 'payment_add') {
            $table = 'ims_customs_export_payment_slip_head';
        }else if($c == 'onlineexport' && $a == 'logistics_add') {
            $table = 'ims_customs_export_logistics_waybill_head';
        }else if($c == 'onlineexport' && $a == 'declare_add') {
            $table = 'ims_customs_export_declarationlist_head';
        }else if($c == 'onlineexport' && $a == 'logistics_add_e') {
            $table = 'ims_customs_export_inventory_totalscore_head';
        }else if($c == 'onlineexport' && $a == 'logistics_add_s') {
            $table = 'ims_customs_export_departureticket_head';
        }else if($c == 'onlineexport' && $a == 'declare_add_t') {
            $table = 'ims_customs_export_collect_apply_head';
        }else if($c == 'commrecord' && $a == 'add') {
            $table = 'ims_foll_goodsreghead';
        }else if($c == 'withhold' && $a == 'index') {
            $table = 'ims_decl_bol';
        }else if($c == 'logistic' && $a == 'index') {
            $table = 'ims_customs_logistics_lading';
        }else if($c == 'elist' && $a == 'index') {
            $table = 'ims_cutoms_elist_lading';
        }else if($c == 'ccgoodsdecl' && $a == 'add_bol') {
            $table = 'ims_customs_ccgoods_bol';
        }else if( ($c == 'mainbody' || $c == 'main') && ($a == 'addMainBody' || $a == 'add') ) {
            $table = 'ims_decl_main_body';
        }else if( ($c == 'CommonShipperConfig' || $c == 'commonshipperconfig') && $a == 'index' ) {
            $table = 'ims_customs_common_shipperinfo';
        }else if($c == 'goods' && $a == 'shelf_index') {
            return [
                ['field' => 'distCode', 'comment' => '关区代码']
            ];
        }else if($c == 'index' && $a == 'save_line' ) {
            $table = 'ims_centralize_line_country';
        }else{
            return json(["length" => 0, "message" => "匹配数据表失败"]);
        }
        
        return getTableField($table);
    }

    public function add()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $data['create_at'] = time();
            if( Db::name('decl_user_systemtips')->insert($data) )
            {
                return json(["status" => 1, "message" => "新增成功"]);
            }else{
                return json(["status" => 0, "message" => "新增失败"]);
            }
        }else{
            return view();
        }
    }

    public function edit()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            if( Db::name('decl_user_systemtips')->update($data) )
            {
                return json(["status" => 1, "message" => "修改成功"]);
            }else{
                return json(["status" => 0, "message" => "修改失败"]);
            }
        }else{
            $data = Db::name('decl_user_systemtips')->where('id',input('id'))->find();
            $this->assign('data', $data);
            return view();
        }
    }

    public function del()
    {
        $id = input('id');
        if( Db::name('decl_user_systemtips')->where('id',$id)->delete() )
        {
            return json(["status" => 1, "message" => "删除成功"]);
        }else{
            return json(["status" => 0, "message" => "删除失败"]);
        }
    }
}