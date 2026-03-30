<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;


class Entrustdecl extends Auth
{

    // 结汇提现管理
    public function withdrawal_list()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('decl_user_withdraw')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                switch ($v['status']) {
                    case 0 :
                        $list[$k]['status'] = '提现已申请，正在审核中';
                    break;
                    case 1 :
                        $list[$k]['status'] = '提现已确认，操作已完成';
                    break;
                    case 2 :
                        $list[$k]['status'] = '审核不通过，拒绝申请';
                    break;
                    case 3 :
                        $list[$k]['status'] = '提现已审核，正在提现中';
                    break;
                    case 4 :
                        $list[$k]['status'] = '提现已完成，正在确认中';
                    break;
                    case 5 :
                        $list[$k]['status'] = '审核不通过，退回申请中';
                    break;
                    case 6 :
                        $list[$k]['status'] = '撤回申请';
                    break;
                }
                $list[$k]['finish_time'] = date('Y-m-d H:i:s',$v['finish_time']);
                $list[$k]['user_name'] = DB::name('decl_user')->where('id',$v['uid'])->value('user_name');
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $account = DB::name('decl_user_account')->where('id',$v['account_id'])->find();
                // $list[$k]['withdraw_account'] = $account['account_name'].':'.$account['bank_name'].':'.$account['account'];
                $list[$k]['withdraw_account'] = $account['account_name'];
                $list[$k]['manage'] = '<button type="button" onclick="seeInfo('."'提现审核','".Url('admin/entrustDecl/withdraw_info')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">提现审核</button>';
                
            }
            $total = Db::name('decl_user_withdraw')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    // 外币归集列表
    public function entry_list()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('customs_export_pool_order')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" onclick="seeInfo('."'入账审核','".Url('admin/entrustDecl/entry_info')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">入账审核</button>';
                
            }
            $total = Db::name('customs_export_pool_order')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    // 币种兑换列表
    public function exchange_list()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('decl_user_exchange')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                switch ($v['status']) {
                    case 0 :
                        $list[$k]['status'] = '审核中';
                    break;
                    case 1 :
                        $list[$k]['status'] = '已通过';
                    break;
                    case 2 :
                        $list[$k]['status'] = '不通过';
                    break;
                }
                
                $list[$k]['user_name'] = DB::name('decl_user')->where('id',$v['uid'])->value('user_name');
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" onclick="seeInfo('."'兑换审核','".Url('admin/entrustDecl/exchange_info')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">兑换审核</button>';
                
            }
            $total = Db::name('decl_user_exchange')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    // 兑换审核
    public function exchange_info()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();

            // 更新状态
            $data_info = Db::name('decl_user_exchange')->where('id',$data['id'])->find();
            $data['finish_time'] = time();

            Db::name('decl_user_exchange')->where('id',$data['id'])->update($data);
            $user_cny_money = getUserMoney($data_info['uid'],'CNY');
            $user_money = getUserMoney($data_info['uid'],$data_info['money_type']);

            if( $data['status'] == 1 )
            {
                // 更新金额
                // 1-待卖出余额(冻结) 2-解冻 3-支出 4-离岸收款(收入)
                createMoneyLog($data_info['uid'],$user_cny_money,$data['true_money'],'CNY','换汇收入',4,$data_info['id'],1);
                createMoneyLog($data_info['uid'],$user_money,$data_info['money'],$data_info['money_type'],'换汇支出',3,$data_info['id'],1);

                //2021.09.01-根据结汇类型新增用户可用金额(1换汇后结汇至国内，2换汇后转账至境外)
                $update_array=[];
                $isHaveInfo = Db::name('decl_user_money_can_use')->where(['uid'=>$data_info['uid'],'exchange_type'=>1])->find();
                //根据换汇币种进行修改
                if($data_info['buy_currency']=='CNY'){
                    $update_array = array_merge($update_array,['cny'=>floatval($isHaveInfo['cny'])+floatval($data['true_money'])]);
                }
                Db::name('decl_user_money_can_use')->where(['uid'=>$data_info['uid'],'exchange_type'=>1])->update($update_array);

                //换汇的币种须将待结汇金额变为0
                $money_type = strtolower($data_info['money_type']);
                if($isHaveInfo[$money_type]>0){
                    if($money_type=='usd'){
                        Db::name('decl_user_money_can_use')->where(['uid'=>$data_info['uid'],'exchange_type'=>1])->update(['usd'=>0]);
                    }
                }
                //
            }else{
                createMoneyLog($data_info['uid'],$user_money,-$data_info['money'],$data_info['money_type'],'换汇解冻',2,$data_info['id'],1);
            }

            // 微信通知
            $status = $data['status'] == 1 ? "通过" : "不通过";
            $user = getUserInfo($data_info['uid']);
            if($user['openid'] != '')
            {
//                sendErrorTempls([
//                    'title' =>'您的离岸换汇订单已审核',
//                    'projects' => '离岸换汇',
//                    'status_text' => $status,
//                    'time' => date('Y-m-d H:i:s',time()),
//                    'remark' => '订单号:'.$data_info['ordersn'],
//                    'url' => '',
//                    'openid' => $user['openid']
//                ]);
            }
            return json(["status" => 0, "message" => "审核成功"]);
        }else{
            
            $data = DB::name('decl_user_exchange')->where('id',input('id'))->find();
            $user_money = getUserMoney($data['uid'],'all');

            //获取商户配置的离岸换汇手续费
            $user_rule = Db::name('decl_user')->where('id',$data['uid'])->field('offshore_exchange_info')->find();
            $user_rule = json_decode($user_rule['offshore_exchange_info'],true);

            //获取交易费用
            if($user_rule['entry_type']==5){
                $user_rule['currency'] = Db::name('currency')->where('code_value',$user_rule['currency'])->field('code_name')->find()['code_name'];
                //计算手续费
                $trade_fee = $user_rule['money'];
            }else{
                //计算手续费
                $trade_fee = sprintf('%.2f',$data['money'] * $user_rule['trade_rate']);
                if($trade_fee<=$user_rule['trade_low_money']){
                    $trade_fee = $user_rule['trade_low_money'];
                }
            }

            $this->assign('user_rule', $user_rule);
            $this->assign('trade_fee', $trade_fee);
            $this->assign('data', $data);
            $this->assign('user_money', $user_money);
            return view();
        }
    }

    // 提现申请
    public function withdraw_list()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('decl_user_withdraw')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                switch ($v['status']) {
                    case 0 :
                        $list[$k]['status'] = '审核中';
                    break;
                    case 1 :
                        $list[$k]['status'] = '已通过';
                    break;
                    case 2 :
                        $list[$k]['status'] = '不通过';
                    break;
                }
                $list[$k]['finish_time'] = date('Y-m-d H:i:s',$v['finish_time']);
                $list[$k]['user_name'] = DB::name('decl_user')->where('id',$v['uid'])->value('user_name');
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $account = DB::name('decl_user_account')->where('id',$v['account_id'])->find();
                $list[$k]['withdraw_account'] = $account['account_name'].':'.$account['bank_name'].':'.$account['account'];
                $list[$k]['manage'] = '<button type="button" onclick="seeInfo('."'提现审核','".Url('admin/entrustDecl/withdraw_info')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">提现审核</button>';
                
            }
            $total = Db::name('decl_user_withdraw')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    // 上传文件
    public function withdraw_upload(Request $request)
    {
        $path = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'withdraw';
        $file = request()->file('file');
        if( $file )
        {
            $info = $file->rule('uniqid')->move($path);
            if( $info )
            {
                return json(["code" => 1, "message" => "上传成功", "path" => '/foll/public/uploads/withdraw/'.$info->getSaveName() ]);
            }else{
                return json(["code" => 0, "message" => "上传失败", "path" => "" ]);
            }
            
        }else{
            return json(["code" => 0, "message" => "请先上传图片！"]);
        }
        
    }

    // 提现审核
    public function withdraw_info()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $status_text = $data['status_texts'];
            unset($data['status_texts']);
            // 更新状态
            $data_info = Db::name('decl_user_withdraw')->where('id',$data['id'])->find();
            if($data_info['status'] == 1)
            {
                return json(["code" => 0, "message" => "该订单已完成！"]);
            }
            $data['finish_time'] = time();
            Db::name('decl_user_withdraw')->where('id',$data['id'])->update($data);
            //$user_cny_money = getUserMoney($data_info['uid'],'CNY');
            $user_money = getUserMoney($data_info['uid'],$data_info['money_type']);
            $user_money_can_use = getUserCanUseMoney($data_info['uid'],$data_info['money_type'],1);
            if( $data['status'] == 1 )
            {
                // 更新金额
                // 1-冻结 2-解冻 3-支出 4-收入
                // createMoneyLog($data_info['uid'],$user_cny_money,$data['true_money'],'CNY','换汇收入',4,$data_info['id']);
                createMoneyLog($data_info['uid'],$user_money,$data_info['withdraw_money'],$data_info['money_type'],'提现支出',3,$data_info['id'],2);
            }

            if( $data['status'] == 2 )
            {
                createMoneyLog($data_info['uid'],$user_money,-$data_info['withdraw_money'],$data_info['money_type'],'提现解冻',2,$data_info['id'],2);
                //不通过时可提现余额加回来
                upgradeUserCanUseMoney($data_info['uid'],$user_money_can_use,-$data_info['withdraw_money'],$data_info['money_type'],1);
            }

            $user = getUserInfo($data_info['uid']);
            if($user['openid'] != '')
            {
//                sendErrorTempls([
//                    'title' =>'您的结汇提现订单已审核',
//                    'projects' => '结汇提现',
//                    'status_text' => $status_text,
//                    'time' => date('Y-m-d H:i:s',time()),
//                    'remark' => '订单号:'.$data_info['ordersn'],
//                    'url' => '',
//                    'openid' => $user['openid']
//                ]);
            }

            return json(["code" => 1, "message" => "审核成功"]);
        }else{
            $data = DB::name('decl_user_withdraw')->where('id',input('id'))->find();
            if($data['logistics_status']=='未发货'){
                if($data['buyer_type']=='新买家'){
                    $data['communicate_log_file'] = explode(',',$data['communicate_log_file']);
                    $data['no_deliver_contract'] = explode(',',$data['no_deliver_contract']);
                    $data['no_deliver_invoice'] = explode(',',$data['no_deliver_invoice']);
                }elseif($data['buyer_type']=='老买家'){
                    $data['logistics_log_file'] = explode(',',$data['logistics_log_file']);
                    $data['no_deliver_contract'] = explode(',',$data['no_deliver_contract']);
                    $data['no_deliver_invoice'] = explode(',',$data['no_deliver_invoice']);
                }
            }elseif($data['logistics_status']=='已发货'){
                if($data['delivery_type']=='直接发货'){
                    $data['waybill_file'] = explode(',',$data['waybill_file']);
                    $data['waylading_file'] = explode(',',$data['waylading_file']);
                    $data['expressbill_file'] = explode(',',$data['expressbill_file']);
                    $data['bookinglist_file'] = explode(',',$data['bookinglist_file']);
                    $data['manifest_file'] = explode(',',$data['manifest_file']);
                    $data['declare_file'] = explode(',',$data['declare_file']);
                    $data['deliver_contract'] = explode(',',$data['deliver_contract']);
                    $data['deliver_invoice'] = explode(',',$data['deliver_invoice']);
                }elseif($data['delivery_type']=='货代转运'){
                    $data['freight_file'] = explode(',',$data['freight_file']);
                    $data['clearance_file'] = explode(',',$data['clearance_file']);
                    $data['deliver_contract'] = explode(',',$data['deliver_contract']);
                    $data['deliver_invoice'] = explode(',',$data['deliver_invoice']);
                }elseif($data['delivery_type']=='国内交货'){
                    $data['re_delivery_file'] = explode(',',$data['re_delivery_file']);
                    $data['delivery_note_file'] = explode(',',$data['delivery_note_file']);
                    $data['packinglist_file'] = explode(',',$data['packinglist_file']);
                    $data['express_file'] = explode(',',$data['express_file']);
                    $data['deliver_contract'] = explode(',',$data['deliver_contract']);
                    $data['deliver_invoice'] = explode(',',$data['deliver_invoice']);
                }

                if($data['consignor_platform']=='不是'){
                    $data['transport_contract'] = explode(',',$data['transport_contract']);
                }
                if($data['consignee_buyer']=='不是'){
                    $data['receiving_file'] = explode(',',$data['receiving_file']);
                }
                if($data['payer_buyer']=='不是'){
                    if($data['buyer_type2']=='买家与付款人同属于集团或企业'){
                        $data['relationship_file'] = explode(',',$data['relationship_file']);
                    }elseif($data['buyer_type2']=='买家是中介，付款人为终端买家'){
                        if($data['is_consignee']=='不是'){
                            $data['is_consignee_file'] = explode(',',$data['is_consignee_file']);
                        }
                    }
                }
            }

            switch ($data['status']) {
                case 0 :
                    $data['status_text'] = '提现已申请，正在审核中';
                break;
                case 1 :
                    $data['status_text'] = '提现已确认，操作已完成';
                break;
                case 2 :
                    $data['status_text'] = '审核不通过，拒绝申请';
                break;
                case 3 :
                    $data['status_text'] = '提现已审核，正在提现中';
                break;
                case 4 :
                    $data['status_text'] = '提现已完成，正在确认中';
                break;
                case 5 :
                    $data['status_text'] = '审核不通过，退回申请中';
                break;
                case 6 :
                    $data['status_text'] = '撤回申请';
                break;
            }

            if($data['red_pack_type'] == 1)
            {
                $data['red_pack_type'] = '金额抵扣';
            }else{
                $data['red_pack_type'] = '金额抵扣';
            }

            if(!empty($data['diy_file'])) {
                $data['diy_file'] = explode(',', $data['diy_file']);
            }

            // 提现账户
            $withdraw_account = DB::name('decl_user_account')->where(['id'=>$data['account_id']])->find();
            $user_money = getUserMoney($data['uid'],'all');

            //结汇机构
            $foreign_exchange = Db::name('foreign_exchange_manage')->select();

            //找出商户配置的在岸结汇手续费
            $onshore_exchange_info = Db::name('decl_user')->where('id',$data['uid'])->field('onshore_exchange_info')->find();
            $onshore_exchange_info = json_decode($onshore_exchange_info['onshore_exchange_info'],true);
            if($onshore_exchange_info['entry_type']==7){
                $onshore_exchange_info['currency'] = Db::name('currency')->where('code_value',$onshore_exchange_info['currency'])->field('code_name')->find()['code_name'];
                //计算手续费
                $data['withdrawal_fee'] = $onshore_exchange_info['money'];
            }else if($onshore_exchange_info['entry_type']==8){
                //计算手续费
                $data['withdrawal_fee'] = sprintf('%.2f',abs($data['withdraw_money']) * $onshore_exchange_info['trade_rate']);
                $data['withdrawal_fee_rate'] = $onshore_exchange_info['trade_rate'];
                if($data['withdrawal_fee']<=$onshore_exchange_info['trade_low_money']){
                    $data['withdrawal_fee'] = $onshore_exchange_info['trade_low_money'];
                }
            }

            $this->assign('onshore_exchange_info', $onshore_exchange_info);
            $this->assign('foreign_exchange', $foreign_exchange);
            $this->assign('data', $data);
            $this->assign('user_money', $user_money);
            $this->assign('withdraw_account', $withdraw_account);
            return view();
        }
    }

    // 离岸转账
    public function transfer_list()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('decl_user_transfer')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                switch ($v['status']) {
                    case 0 :
                        $list[$k]['status'] = '审核中';
                    break;
                    case 1 :
                        $list[$k]['status'] = '已通过';
                    break;
                    case 2 :
                        $list[$k]['status'] = '不通过';
                    break;
                }
                $list[$k]['finish_time'] = date('Y-m-d H:i:s',$v['finish_time']);
                $list[$k]['user_name'] = DB::name('decl_user')->where('id',$v['uid'])->value('user_name');
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                if($v['transfer_type']==2){
                    $account = DB::name('decl_user_account')->where('id',$v['account_id'])->find();
                    $list[$k]['transfer_account'] = $account['account_name'].':'.$account['bank_name'].':'.$account['account'];
                }elseif($v['transfer_type']==1){
                    $account = DB::name('onshore_account')->where('id',$v['account_id'])->find();
                    $list[$k]['transfer_account'] = $account['name'].':'.$account['bank_name'].':'.$account['bank_account'];
                }
                $list[$k]['manage'] = '<button type="button" onclick="seeInfo('."'转账审核','".Url('admin/entrustDecl/transfer_info')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">转账审核</button>';
                
            }
            $total = Db::name('decl_user_transfer')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    public function transfer_info()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            // 更新状态
            $data_info = Db::name('decl_user_transfer')->where('id',$data['id'])->find();
            if($data_info['status'] == 1)
            {
                return json(["code" => 0, "message" => "该订单已完成！"]);
            }
            $data['finish_time'] = time();
            $settlement_institution = $data['settlement_institution'];
            unset($data['settlement_institution']);
            Db::name('decl_user_transfer')->where('id',$data['id'])->update($data);
            //$user_cny_money = getUserMoney($data_info['uid'],'CNY');
            $user_money = getUserMoney($data_info['uid'],$data_info['money_type']);
            $user_money_can_use = getUserCanUseMoney($data_info['uid'],$data_info['money_type'],$data_info['transfer_type']);

            if( $data['status'] == 1 )
            {
                // 更新金额
                // 1-冻结 2-解冻 3-支出 4-收入
                if($data_info['transfer_type']==1){
                    upgradeUserCanUseMoney($data_info['uid'],$user_money_can_use,$data['true_money'],$data['true_currency'],$data_info['transfer_type']);
                    createMoneyLog($data_info['uid'],$user_money,$data['true_money'],$data['true_currency'],'离岸转账成功，等待结汇',2,$data_info['id'],4);
                }elseif($data_info['transfer_type']==2){
                    createMoneyLog($data_info['uid'],$user_money,$data_info['transfer_money'],$data_info['money_type'],'离岸转账支出',3,$data_info['id'],4);
                }

                //审核通过时，如果该账户是kvb账户，通知kvb
                $account = Db::name('decl_user_account')->where('id',$data_info['account_id'])->field('open_other')->find();
//                if($settlement_institution=='KVB'){
//                    //kvb管辖账户
//                    $kvb_account = Db::name('kvb_client_bank_account')->where('decl_user_account_id',$data_info['account_id'])->find();
//                    //将转账信息发送至kvb
//                    httpRequest('http://decl.gogo198.cn/api/kvb/withdrawal',array('ccy'=>$data_info['money_type'],'amount'=>$data_info['transfer_money'],'account_id'=>$kvb_account['account_id']));
//                }
            }else{
                createMoneyLog($data_info['uid'],$user_money,-$data_info['transfer_money'],$data_info['money_type'],'离岸转账解冻',2,$data_info['id'],4);
                //不通过时可转账余额加回来
                upgradeUserCanUseMoney($data_info['uid'],$user_money_can_use,-$data_info['transfer_money'],$data_info['money_type'],$data_info['transfer_type']);
            }
            
            $status_text = $data['status'] == 1 ? '通过' : '不通过';
            // 微信通知
            $user = getUserInfo($data_info['uid']);
            if($user['openid'] != '')
            {
//                sendErrorTempls([
//                    'title' =>'您的离岸转账订单已审核',
//                    'projects' => '离岸转账',
//                    'status_text' => $status_text,
//                    'time' => date('Y-m-d H:i:s',time()),
//                    'remark' => '订单号:'.$data_info['ordersn'],
//                    'url' => '',
//                    'openid' => $user['openid']
//                ]);
            }

            return json(["code" => 1, "message" => "审核成功"]);
        }else{
            $data = DB::name('decl_user_transfer')->where('id',input('id'))->find();
            // 提现账户
            if($data['transfer_type']==2){
                $transfer_account = DB::name('decl_user_account')->where(['id'=>$data['account_id']])->find();
            }elseif($data['transfer_type']==1){
                $transfer_account = DB::name('onshore_account')->where(['id'=>$data['account_id']])->find();
                $transfer_account['account_name'] = $transfer_account['name'];
                $transfer_account['account'] = $transfer_account['bank_account'];
            }

            $user_money = getUserMoney($data['uid'],'all');
            //换汇机构
            $exchange = Db::name('exchange_manage')->select();

            //找出商户配置的离岸转账手续费
            $offshore_transfer_info = Db::name('decl_user')->where('id',$data['uid'])->field('offshore_transfer_info')->find();
            $offshore_transfer_info = json_decode($offshore_transfer_info['offshore_transfer_info'],true);
            if($offshore_transfer_info['entry_type']==3){
                $offshore_transfer_info['currency'] = Db::name('currency')->where('code_value',$offshore_transfer_info['currency'])->field('code_name')->find()['code_name'];
                //计算手续费
                $data['transfer_expenses'] = $offshore_transfer_info['money'];
            }else if($offshore_transfer_info['entry_type']==4){
                //计算手续费
                $data['transfer_expenses'] = sprintf('%.2f',$data['transfer_money'] * $offshore_transfer_info['trade_rate']);
                $data['transfer_expenses_rate'] = $offshore_transfer_info['trade_rate'];//转账费率，忽略
                if($data['transfer_expenses']<=$offshore_transfer_info['trade_low_money']){
                    $data['transfer_expenses'] = $offshore_transfer_info['trade_low_money'];
                }
            }
            //            transfer_expenses
            $this->assign('exchange', $exchange);
            $this->assign('data', $data);
            $this->assign('user_money', $user_money);
            $this->assign('transfer_account', $transfer_account);
            return view();
        }
    }


    /**
     * 国内提现管理
     */
    public function domestic_withdrawal_list(){
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $list = Db::name('customs_domestic_withdrawal')->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                switch ($v['check_status']) {
                    case 0 :
                        $list[$k]['check_status'] = '提现已申请，正在审核中';
                        break;
                    case 1 :
                        $list[$k]['check_status'] = '提现已确认，操作已完成';
                        break;
                    case -1 :
                        $list[$k]['check_status'] = '审核不通过，拒绝申请';
                        break;
                }
                $list[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $list[$k]['user_name'] = DB::name('decl_user')->where('openid',$v['openid'])->value('user_name');
                $account = DB::name('customs_bank_account')->where('id',$v['account_id'])->find();
                $list[$k]['withdraw_account'] = $account['account_name'];
                $list[$k]['money_type'] = $account['money_type'];
                $list[$k]['manage'] = '<button type="button" onclick="seeInfo('."'提现审核','".Url('admin/entrustDecl/domestic_withdraw_info')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">提现审核</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="del('."'".Url('admin/extrustDecl/del_withdraw_info')."'".','.$v['id'].')" class="btn btn-danger btn-xs">删除</button>';

            }
            $total = Db::name('customs_domestic_withdrawal')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    public function domestic_withdraw_info(Request $request){
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $status_text = $data['status_texts'];
            unset($data['status_texts']);
            // 更新状态
            $data_info = Db::name('customs_domestic_withdrawal')->where('id',$data['id'])->find();
            if($data_info['check_status'] == 1)
            {
                return json(["code" => 0, "message" => "该订单已完成！"]);
            }
            $data['money_date'] = time();

            unset($data['money_type']);
            unset($data['withdrawal_expenses_currency']);
            Db::name('customs_domestic_withdrawal')->where('id',$data['id'])->update($data);

            if($data_info['openid'] != '')
            {
                $ordersn = '';
                $orderid = explode(',',$data_info['orderid']);
                $check_remark = $data['check_remark'];
                if($data['check_status']==1){$check_remark='';}
                foreach($orderid as $k=>$v){
                    if(!empty($v)){
                        $ordersn .= Db::name('customs_collection')->where('id',$v)->field('ordersn')->find()['ordersn'].',';

                        $check_status='';
                        if($data['check_status']==-1){
                            $check_status=-1;
                        }elseif($data['check_status']==1){
                            $check_status=2;
                        }
                        Db::name('customs_collection')->where('id',$v)->update(['tixian_status'=>$check_status,'tixian_remark'=>$check_remark]);
                    }
                }
                $data_info['ordersn'] = substr($ordersn,0,strlen($ordersn)-1);
//                sendErrorTempls([
//                    'title' =>'您的国内结算提现订单已审核',
//                    'projects' => '结算提现',
//                    'status_text' => $status_text,
//                    'time' => date('Y-m-d H:i:s',time()),
//                    'remark' => '实际到账：CNY '.$data['true_money'].'元'.PHP_EOL.'手续费：CNY '.$data['withdrawal_expenses'].'元'.PHP_EOL.'订单号：'.$data_info['ordersn'],
//                    'url' => '',
//                    'openid' => $data_info['openid']
//                ]);
            }

            return json(["code" => 1, "message" => "审核成功"]);
        }else{
            $data = DB::name('customs_domestic_withdrawal')->where('id',input('id'))->find();
            switch ($data['check_status']) {
                case 0 :
                    $data['status_text'] = '提现已申请，正在审核中';
                    break;
                case 1 :
                    $data['status_text'] = '提现已确认，操作已完成';
                    break;
                case -1 :
                    $data['status_text'] = '审核不通过，拒绝申请';
                    break;
            }

            if($data['orderid']){
                $data['orderid'] = explode(',',$data['orderid']);
                $order = Db::name('customs_collection')->whereIn('id',$data['orderid'])->select();
            }

            // 提现账户
            $withdraw_account = DB::name('customs_bank_account')->where(['id'=>$data['account_id']])->find();

            if(empty($data['money_date'])){
                $data['money_date'] = time();
            }

            $this->assign('data', $data);
            $this->assign('order', $order);
            $this->assign('withdraw_account', $withdraw_account);
            return view();
        }
    }

    public function del_withdraw_info(Request $request){
        $dat = input();
        if(intval($dat['id'])>0){
            $res = Db::name('customs_domestic_withdrawal')->where('id',intval($dat['id']))->delete();
            if($res){
                return json(['code'=>1,'msg'=>'删除成功！']);
            }else{
                return json(['code'=>-1,'msg'=>'系统错误！']);
            }
        }
    }

    //通过无纸化
    public function paper_customs_clearance(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax())
        {
//            $data = Db::name('customs_export_declarationlist_head')->where('tracking_num', trim($dat['lading_no']))->find();
            $data = Db::name('customs_export_declarationlist_list')
                    ->alias('a')
                    ->join('customs_export_declarationlist_head b','b.id=a.hid','left')
                    ->where('a.ordersn', trim($dat['lading_no']))->field(['b.*','a.ordersn','a.logistics_no'])->find();

            if(empty($data)){
                return json(['code'=>0,'msg'=>'找不到数据！']);
            }else{
                $data2 = [];
                $data2['logistics_no'] = $data['logistics_no'];
                $data2['sf_people'] = $data['owner_name'];
                $data2['company_name'] = $data['ebc_name'];
                $customs_codes = Db::name('customs_codes')->select();
                foreach ($customs_codes as $k => $v) {
                    $vs = explode(":",$v['AreaCode']);
                    $customs_codes[$k]['value_code'] = $vs[0];
                }
                foreach($customs_codes as $k=>$v){
                    if($v['value_code']==$data['customs_code']){
                        $data2['customs_name'] = explode(':',$v['AreaCode'])[1];
                    }
                    if($v['value_code']==$data['port_code']){
                        $data2['exit_clearance'] = explode(':',$v['AreaCode'])[1];
                        $data2['exit_clearance_code'] = explode(':',$v['AreaCode'])[0];
                    }
                }
                $data2['declare_date'] = date('Ymd',$data['create_at']);
                $data2['record_no'] = DB::name('customs_portplatforminfo')->where('id',$data['sid'])->field('electronic_port_code')->find()['electronic_port_code'];
                $data2['export_date'] = $data['ie_date'];
                $data2['transport_mode'] = DB::name('transport')->where('code_value',$data['traf_mode'])->field('code_name')->find()['code_name'];
                $data2['transport_mode_code'] = $data['traf_mode'];
                $data2['transport_name'] = $data['traf_name'].'/'.$data['voyage_no'];
                $data2['pro_sale_unit_code'] = $data['owner_code'];
                $data2['pro_sale_unit'] = $data['owner_name'];
                $data2['sup_mode'] = DB::name('tradeway')->where('code_value',$data['trade_mode'])->field('code_name')->find()['code_name'];
                $data2['sup_mode_code'] = $data['trade_mode'];
                $data2['license_key'] = !empty($data['license_no'])?$data['license_no']:'';
                $data2['arr_country'] = DB::name('country_code')->where('code_value',$data['country'])->field('code_name')->find()['code_name'];
                $data2['arr_country_code'] = Db::name('orig_country')->where('code_name',$data2['arr_country'])->field('code_value')->find()['code_value'];
                $data2['dest_port'] = DB::name('country_code')->where('code_value',$data['pod'])->field('code_name')->find()['code_name'];
                $data2['dest_port_code'] = Db::name('orig_country')->where('code_name',$data2['dest_port'])->field('code_value')->find()['code_value'];

                //合同协议号
                $data2['contract'] = Db::name('customs_export_declarationlist_head')
                    ->alias('a')
                    ->join('customs_export_declarationlist_list b','a.id=b.hid','left')
                    ->join('sz_yi_order c','b.ordersn=c.ordersn','left')
                    ->join('decl_user_trade_pdf d','c.trade_id=d.trade_id','left')
                    ->where(['d.type'=>'po_contract','d.trade_type'=>1,'b.ordersn'=>trim($dat['lading_no'])])->field(['d.pdf_sn','d.packing_type'])->find();

                //订单商品信息
                //step1:件数
                $declarationlist = Db::name('customs_export_declarationlist_list')->where('ordersn',$data['ordersn'])->find();
                $declarationlist_goods = Db::name('customs_export_declarationlist_goods') ->where('oid',$declarationlist['id'])->field(['qty'])->select();

                $piece_num=0;
                foreach($declarationlist_goods as $k=>$v){
                    $piece_num+=$v['qty'];
                }
                $data2['order_info']['piece'] = $piece_num;
                //step2:毛重、净重
                $data2['order_info']['gross_weight'] = $declarationlist['gross_weight'];
                $data2['order_info']['net_weight'] = $declarationlist['net_weight'];
                //step3:商品信息
                $all_goods = Db::name('sz_yi_order')
                    ->alias('a')
                    ->join('sz_yi_order_goods b','a.id=b.orderid','left')
                    ->join('sz_yi_goods c','b.goodsid=c.id','left')
                    ->where(['a.ordersn'=>$declarationlist['ordersn']])->field(['b.total','c.CIQGoodsNo','c.CusGoodsNo','c.marketprice'])->select();
                foreach($all_goods as $k=>$v){
                    $all_goods[$k]['goods'] = \app\admin\model\GoodsModel::where(['ciq_goodsno'=>$v['CIQGoodsNo'],'cus_goodsno'=>$v['CusGoodsNo']])->field(['goods_name','goods_style','origin_country','gunit'])->find()->toArray();
                    $all_goods[$k]['unit'] = Db::name('unit')->where('code_value',$all_goods[$k]['goods']['gunit'])->field('code_name')->find()['code_name'];
                    $all_goods[$k]['g_name'] = $all_goods[$k]['goods']['goods_name']."|".$all_goods[$k]['goods']['goods_style'].'|';
                }
                $data2['order_info']['goods'] = $all_goods;

                //step4:海关编号
                $data2['preNo'] = Db::name('reduction_declareorderlist')->where('ordNo',$declarationlist['ordersn'])->field('preNo')->find()['preNo'];

                //step5:打印时间
                $y = substr($data['ie_date'],0,4);
                $m = substr($data['ie_date'],5,2);
                $d = substr($data['ie_date'],6,2);
                $date2 = date('H:i:s',time());
                $data2['date'] = $y.'年'.$m.'月'.$d.'日  '.$date2;

                return json(['code'=>1,'msg'=>'关联成功！','dataList'=>$data2]);
            }
        }else{
            //出口关别
            $exit_clearance = DB::name('customs_codes')->select();
            foreach ($exit_clearance as $k => $v) {
                $vs = explode(":",$v['AreaCode']);
                $exit_clearance[$k]['value_code'] = $vs[0];
            }
            //运输方式
            $transport_mode = DB::name('transport')->select();
            //监管方式
            $sup_mode = DB::name('tradeway')->select();
            //征免性质
            $zm_nature = DB::name('nature_of_exemption')->select();
            //国家代码
            $arr_country = DB::name('country_code')->where('code_value','<>','000')->select();
            //指运港代码
            $dest_port = DB::name('port_code')->select();
            //包装种类
            $packing_type =  DB::name('wrap_type')->select();
            //成交方式
            $transaction_mode = DB::name('dealway')->select();
            //币制
            $currency = DB::name('currency')->select();
            //orig_country
            $orig_country = Db::name('orig_country')->select();
            //境内货源地
            $district_code = Db::name('district_code')->select();
            $this->assign('district_code',$district_code);
            $this->assign('exit_clearance',$exit_clearance);
            $this->assign('transport_mode',$transport_mode);
            $this->assign('sup_mode',$sup_mode);
            $this->assign('zm_nature',$zm_nature);
            $this->assign('arr_country',$arr_country);
            $this->assign('dest_port',$dest_port);
            $this->assign('packing_type',$packing_type);
            $this->assign('transaction_mode',$transaction_mode);
            $this->assign('currency',$currency);
            $this->assign('orig_country',$orig_country);
            return view();
        }
    }

    public function getOrigCountry(Request $request){
        return $code_value = Db::name('orig_country')->where('code_name',input('name'))->field('code_value')->find()['code_value'];
    }
    
    //水路货物运单
    public function waterway_cargo_waybill(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax())
        {
//            $data = Db::name('customs_export_declarationlist_head')->where('tracking_num', trim($dat['lading_no']))->find();
            $data = Db::name('customs_export_declarationlist_list')
                ->alias('a')
                ->join('customs_export_declarationlist_head b','b.id=a.hid','left')
                ->where('a.ordersn', trim($dat['lading_no']))->field(['b.*','a.ordersn'])->find();
            if(empty($data)){
                return json(['code'=>0,'msg'=>'找不到数据！']);
            }else{
                $list = Db::name('customs_export_declarationlist_list')->where('ordersn',$data['ordersn'])->find();
                $data2 = [];
                $data2['info']['waybill_no'] = $list['logistics_no'];
                $data2['info']['load_info'] = $data['traf_name'].'/'.$data['voyage_no'];
                //step1:收发货人
                $trade = Db::name('sz_yi_order')
                ->alias('a')
                ->join('decl_user_trade_platform b','b.id=a.trade_id','left')
                ->where('a.ordersn',$list['ordersn'])
                ->field(['b.deliver_id','b.receive_id','b.id'])
                ->find();
                
                if(!empty($trade['deliver_id'])){
                    $deliver = Db::name('decl_user_logistics_unit')->where('id',$trade['deliver_id'])->find();
                    $data2['info']['deliver'] = $deliver;
                }
                if(!empty($trade['receive_id'])){
                    $receive = Db::name('decl_user_logistics_unit')->where('id',$trade['receive_id'])->find();
                    $data2['info']['receive'] = $receive;
                }
                //step2:起运港+目的港
                $trade_pdf = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'],'type'=>'po_contract'])->find();
                $data2['departure_port'] = $trade_pdf['departure_port'];
                $data2['destination_port'] = $trade_pdf['destination_port'];
                
                //step3:商品信息
                $goods = Db::name('decl_user_trade_order')
                                ->alias('a')
                                ->join('decl_user_trade_order_goods b','b.order_id=a.id','left')
                                ->where(['a.trade_id'=>$trade['id']])
                                ->field(['b.item_name','b.goods_qty'])
                                ->select();
                 $all_goods = Db::name('sz_yi_order')
                    ->alias('a')
                    ->join('sz_yi_order_goods b','a.id=b.orderid','left')
                    ->join('sz_yi_goods c','b.goodsid=c.id','left')
                    ->where(['a.ordersn'=>$list['ordersn']])->field(['b.total','c.CIQGoodsNo','c.CusGoodsNo'])->select();
                foreach($all_goods as $k=>$v){
                    $all_goods[$k]['goods'] = \app\admin\model\GoodsModel::where(['ciq_goodsno'=>$v['CIQGoodsNo'],'cus_goodsno'=>$v['CusGoodsNo']])->field(['goods_name','goods_style','origin_country','grosswt'])->find()->toArray();
                    $all_goods[$k]['goods']['grosswt'] = sprintf('%.2f',$all_goods[$k]['goods']['grosswt']*$v['total']);
                    //包装种类
                    $all_goods[$k]['goods']['packing_type'] = $trade_pdf['packing_type']; 
                }
                $data2['goods'] = $all_goods;
                return json(['code'=>1,'msg'=>'关联成功！','dataList'=>$data2]);
            }
        }else{
            //国家代码
            $arr_country = DB::name('country_code')->where('code_value','<>','000')->select();
            //指运港代码
            $dest_port = DB::name('port_code')->select();
            //包装种类
            $packing_type =  DB::name('wrap_type')->select();
            //出口关别
            $exit_clearance = DB::name('customs_codes')->select();
            foreach ($exit_clearance as $k => $v) {
                $vs = explode(":",$v['AreaCode']);
                $exit_clearance[$k]['value_code'] = $vs[0];
            }
            //起运港
            $distinate_port = Db::name('distinate_port')->select();
            $this->assign('distinate_port',$distinate_port);
            $this->assign('exit_clearance',$exit_clearance);
            $this->assign('arr_country',$arr_country);
            $this->assign('dest_port',$dest_port);
            $this->assign('packing_type',$packing_type);
            return view();
        }
    }

    public function waterway_paper_info(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax())
        {
            $dat['createtime'] = time();
            $res = Db::name('water_paper_info')->insert($dat);
            if($res){
                return json(['code'=>1]);
            }
        }
    }

    //官网设置
    public function website_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax())
        {

        }else{
            return view();
        }
    }

    //获取官网菜单
    public function fetchMenuList(Request $request)
    {
        if ($request->isAJAX()) {
            $list = Db::name('website_navbar')->select();

            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        } else {
            return json(['code' => -1, 'msg' => '未知错误']);
        }
    }

    //创建菜单内容
    public function declMenuCreate(Request $request){
        $id = intval($request->get('id'));

        if ($request->isAJAX()) {
            $dat = input();
            $res = Db::name('website_navbar')->insert([
                'name'=>trim($dat['name']),
                'content'=>json_encode($dat['editorValue'],true),
                'pid'=>$dat['id']
            ]);
            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
        }else{
            return view('',compact('id'));
        }
    }

    //编辑菜单内容
    public function declMenuRep(Request $request){
        $id = $request->get('id');
        if ($request->isAJAX()) {
            $dat = input();
            $res = Db::name('website_navbar')->where('id',$dat['id'])->update([
                'name'=>trim($dat['name']),
                'content'=>json_encode($dat['editorValue'],true),
            ]);
            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
        }else{
            $data = Db::name('website_navbar')->where('id',$id)->find();
            $data['content'] = json_decode($data['content'],true);
            return view('',compact('data','id'));
        }
    }

    //删除菜单内容
    public function declMenuDel(Request $request){
        $id = $request->get('id');
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '错误！']);
        }
        $have_child = Db::name('website_navbar')->where('pid',$id)->find();
        if ($have_child['id']) {
            return json(['code' => -1, 'msg' => '存在子级菜单,不允许删除!']);
        }
        Db::name('website_navbar')->where('id',$id)->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }
}