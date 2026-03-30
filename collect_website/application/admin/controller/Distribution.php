<?php
namespace app\admin\controller;

//use think\Controller;
use app\admin\controller;
use think\Request;
use think\Db;

class Distribution extends Auth
{
    public function index(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('centralize_user')->where('agent_status','>',0)->where('agentid',0)->count();
            $rows = Db::name('centralize_user')->where('agent_status','>',0)->where('agentid',0)->order($order)->limit($limit)->select();
            foreach($rows as $k=>$v){
                if(!empty($v['agent_time'])){
                    $rows[$k]['agent_time'] = date('Y-m-d H:i',$v['agent_time']);
                }
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                if($v['agent_status']==1){
                    $rows[$k]['agent_status'] = '申请中';
                }elseif($v['agent_status']==2){
                    $rows[$k]['agent_status'] = '申请通过';
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    public function add_distr(Request $request){
        $dat = input();

        if ($request->isAjax()) {
            $ishave = Db::name('centralize_distribution_send_list')->where('email',trim($dat['email']))->whereOr('mobile',trim($dat['mobile']))->find();
            if($ishave['id']){
                return json(['code'=>-1,'msg'=>'该手机号或邮箱号已发送过，请勿重复发送']);
            }
            $link = trim($dat['link']);

            if($dat['method']==2){
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>trim($dat['mobile']),
                    'content'=>'您好！请点击链接登录后，进入会员中心->我的分销->申请分销。链接：'.$link.'【GOGO】',
                ];
                httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }elseif($dat['method']==1){
                cklein_mailAli(trim($dat['email']), '尊敬的用户', '正在向您下发分销申请', '您好！请点击链接登录后，进入会员中心->我的分销->申请分销。链接：'.$link);
            }

            $res = Db::name('centralize_distribution_send_list')->insert([
                'method'=>$dat['method'],
                'email'=>$dat['method']==1?trim($dat['email']):'',
                'mobile'=>$dat['method']==2?trim($dat['mobile']):'',
                'link'=>$link,
                'createtime'=>time(),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'发送成功']);
            }
        }else{
            return view('');
        }
    }

    public function distr_commission(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isAjax()){
            $commission_rules = ['type'=>$dat['type']];
            if($dat['type']==1){
                $commission_rules['trade_amount'] = $dat['trade_amount1'];
            }elseif($dat['type']==2){
                $commission_rules['trade_amount'] = $dat['trade_amount2'];
            }elseif($dat['type']==3){
                $commission_rules['trade_amount'] = $dat['trade_amount3'];
            }

            $res = Db::name('centralize_user')->where(['id'=>$id])->update(['commission_rules'=>json_encode($commission_rules,true)]);
            if($res){
                return json(['code'=>0,'msg'=>'配置成功']);
            }
        }else{
            if($id>0){
                $data = Db::name('centralize_user')->where(['id'=>$id])->find();
                if(empty($data['commission_rules'])){
                    $data = ['commission_rules'=>['type'=>'','trade_amount'=>'']];
                }else{
                    $data['commission_rules'] = json_decode($data['commission_rules'],true);
                }
//                {"type":2,"trade_amount":0,"commission_amount":15}
            }
            return view('',compact('id','data'));
        }
    }

    public function distr_restrict(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isAjax()){
            $distr_restrict = ['levels'=>intval($dat['levels']),'area'=>trim($dat['area']),'category'=>intval($dat['category'])];
            $res = Db::name('centralize_user')->where(['id'=>$id])->update(['distr_restrict'=>json_encode($distr_restrict,true)]);
            if($res){
                return json(['code'=>0,'msg'=>'配置成功']);
            }
        }else{
            if($id>0){
                $data = Db::name('centralize_user')->where(['id'=>$id])->find();
                if(empty($data['distr_restrict'])){
                    $data = ['distr_restrict'=>['levels'=>'','area'=>'','category'=>'']];
                }else{
                    $data['distr_restrict'] = json_decode($data['distr_restrict'],true);
                }
            }
            return view('',compact('id','data'));
        }
    }

    public function recruit_method(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isAjax()){
            $recruit_rules = ['type'=>intval($dat['type']),'link'=>trim($dat['link']),'qrcode'=>isset($dat['qrcode'][0])?$dat['qrcode'][0]:''];
            $res = Db::name('centralize_user')->where(['id'=>$id])->update(['recruit_rules'=>json_encode($recruit_rules,true)]);
            if($res){
                return json(['code'=>0,'msg'=>'配置成功']);
            }
        }else{
            if($id>0){
                $data = Db::name('centralize_user')->where(['id'=>$id])->find();
                if(empty($data['recruit_rules'])){
                    $data = ['recruit_rules'=>['type'=>'','link'=>'','qrcode'=>'']];
                }else{
                    $data['recruit_rules'] = json_decode($data['recruit_rules'],true);
                }
            }
            return view('',compact('id','data'));
        }
    }

    public function del_distr(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::name('centralize_user')->where(['id'=>$id])->update([
            'agent_status'=>-1
        ]);
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #审核经销商
    public function apply_distr(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $agent_status = 0;
        $remark = '';
        $agent_time = '';

        if(intval($dat['agent_status'])==2){
            $agent_status = intval($dat['agent_status']);
            $agent_time = time();
        }elseif(intval($dat['agent_status'])==-1){
            $agent_status = intval($dat['agent_status']);
            $remark = trim($dat['agent_remark']);
        }

        $res = Db::name('centralize_user')->where(['id'=>$id])->update([
            'agent_status'=>$agent_status,
            'agent_remark'=>$remark,
            'agent_time'=>$agent_time
        ]);
        if($res){
            return json(['code'=>0,'msg'=>'操作成功']);
        }
    }
    
    //订单管理
    public function order_manage(Request $request){
        $dat = input();
        if($request->isAjax()){
            
        }else{
            return view('');
        }
    }
    
    //结算管理
    public function settlement_manage(Request $request){
        $dat = input();
        
        if($request->isAjax()){
            
        }else{
            return view('');
        }
    }
    
    #企业信息
    public function add_elist(Request $request)
    {
        $dat = input();
        if (request()->isPost() || request()->isAjax()) {
            $res = Db::name('customs_enterprise_info')->insert([
                'enterprise_name'=>trim($dat['enterprise_name']),
                'legal_name'=>trim($dat['legal_name']),
                'orgNo'=>trim($dat['orgNo']),
            ]);
            
            return json(['code'=>0,'msg'=>'操作成功!']);
        } else {
            return view('');
        }
    }
    
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
                $list[$k]['manage'] = '<button type="button" onclick="edit(' . "'查看','" . Url('admin/distribution/edit_lists') . "'" . ',' . "'" . $v['id'] . "'" . ')" class="btn btn-primary btn-xs" style="margin-right: 10px;">查看</button>';
            }
            $total = Db::name('customs_crossorder_detail')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        } else {
            return view('');
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

            return view('',compact('unit','order','currency','fPort','sPort'));
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
                // $list[$k]['manage'] = '<button type="button" onclick="edit(' . "'查看','" . Url('admin/distribution/edit_olists') . "'" . ',' . "'" . $v['id'] . "'" . ')" class="btn btn-primary btn-xs" style="margin-right: 10px;">查看</button>';
            }
            $total = Db::name('customs_crossorder_list')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        } else {
            return view('');
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

            return view('',compact('unit','order','currency','fPort','sPort','enterprise'));
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
            return view('');
        }
    }
    
    #付款管理
    public function settlement_statics(Request $request){
        $dat = input();
        if($request->isAjax()){
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');
            $count = Db::name('centralize_manage_payment')->count();
            $rows = DB::name('centralize_manage_payment')
                ->order($order)
                ->limit($limit)
                ->select();
            $rows = objectToArrays($rows);
    
            foreach ($rows as &$item) {
                $item['currency'] = Db::name('currency')->where('code_value',$item['currency'])->find()['code_name'];
                $item['member_name'] = Db::table('centralize_user')->where('id',$item['member_id'])->find()['name'];
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }
            
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }
    
    #新增付款账号
    public function save_settlement(Request $request){
        $dat = input();
        $id = 0;
        if(isset($data['id'])){
            $id = intval($data['id']);
        }
         if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_manage_bank_account')->where(['id'=>$id])->update([
                    'account_type'=>intval($data['account_type']),
                    'currency'=>trim($data['currency']),
                    'name'=>trim($data['name']),
                    'desc'=>trim($data['desc']),
                    'bank_id'=>trim($data['bank_id']),
                    'bank_branch'=>trim($data['bank_branch']),
                    'account'=>trim($data['account']),
                    'bank_type'=>intval($data['bank_type']),
                    'type'=>intval($data['type']),
                ]);
            }else{
                $res = Db::name('centralize_manage_bank_account')->insert([
                    'uid'=>0,
                    'account_type'=>intval($data['account_type']),
                    'currency'=>trim($data['currency']),
                    'name'=>trim($data['name']),
                    'desc'=>trim($data['desc']),
                    'bank_id'=>trim($data['bank_id']),
                    'bank_branch'=>trim($data['bank_branch']),
                    'account'=>trim($data['account']),
                    'bank_type'=>intval($data['bank_type']),
                    'type'=>intval($data['type']),
                    'createtime'=>time(),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
        }else{
            $info = ['name'=>'','desc'=>'','account_type'=>'','currency'=>'','bank_id'=>'','bank_branch'=>'','account'=>'','bank_type'=>''];
            if($id>0){
                $info = Db::name('centralize_manage_bank_account')->where(['id'=>$id,'uid'=>0])->find();
            }
            $currency = Db::name('currency')->select();
            $bank = Db::name('bank_list')->select();
        
            return view('',compact('data','id','info','currency','bank'));
        }
    }
    
    public function save_payinfo(Request $request){
        $dat = input();
        
        if($request->isAjax()){
            $ordersn = 'GP' . date('YmdH', time()) . str_pad(mt_rand(1, 999999), 6, '0',
                    STR_PAD_LEFT) . substr(microtime(), 2, 6);

            $time = time();
            $datas = [
                'uid'=>0,
                'member_id'=>intval($data['customer_id']),
                'ordersn'=>$ordersn,
                'currency'=>$data['currency'],
                'price'=>trim($data['price']),
                'payer_name'=>trim($data['payer_name']),
                'payer_tel'=>trim($data['payer_tel']),
                'payment_account'=>intval($data['payment_account']),
                'desc'=>trim($data['desc']),
                'createtime'=>$time
            ];
            $res = Db::table('centralize_manage_payment')->insertGetId($datas);
            if($res){
                return json(['code'=>0,'msg'=>'发起付款成功!']);
            }
        }else{
            #付款银行
            $bank_list = Db::name('centralize_manage_bank_account')->where(['uid'=>0,'type'=>2])->select();
            
            foreach($bank_list as $k=>$v){
                $bank_list[$k]['bank_id'] = Db::name('bank_list')->where(['id'=>$v['bank_id']])->field(['bank_name'])->find()['bank_name'];
            }
            
            #币种
            $currency = Db::name('currency')->select();
            
            $customers_list = Db::name('centralize_user')->where(['agent_status'=>2,'agentid'=>0])->select();
            
            return view('',compact('bank_list','customers_list','currency'));
        }
    }
}