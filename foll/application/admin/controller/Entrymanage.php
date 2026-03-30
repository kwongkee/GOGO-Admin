<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;

// 入账管理
class Entrymanage extends Auth
{
    public function noklist()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            //排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');
            
            $list = Db::name('decl_user_entry')->where('status','in',[0,-1,3,4])->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                switch ($v['status']) {
                    case -1 :
                        $list[$k]['status'] = '申请已撤回';
                    break;
                    case 0 :
                        $list[$k]['status'] = '申请已提交，还在审核中';
                    break;
                    case 1 :
                        $list[$k]['status'] = '审核已通过，款项已入账';
                    break;
                    case 2 :
                        $list[$k]['status'] = '审核不通过，敬请再提交';
                    break;
                    case 3 :
                        $list[$k]['status'] = '审核初通过，正待收款中';
                    break;
                    case 4 :
                        $list[$k]['status'] = '款项已到账，正在审查中';
                    break;
                }
                $list[$k]['user_name'] = DB::name('decl_user')->where('id',$v['uid'])->value('user_name');
                $list[$k]['order_type'] = $v['order_type'] == 1 ? '平台交易' : '其他交易';
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" onclick="checkInfo('."'审核','".Url('admin/entryManage/noklist_check')."'".','."'".$v['id']."'".')" class="btn btn-primary btn-xs">审核</button>';
                $list[$k]['manage'] .= '<button type="button" onclick="delInfo('."'".$v['id']."'".')" class="btn btn-danger btn-xs">删除</button>';
            }
            $total = Db::name('decl_user_entry')->where('status',0)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    public function check_pdf()
    {
        $data = input();
        if( $data['trade_id'] == '' )
        {
            return json(['code' => 1, 'msg' => '请先到客户端生成该项单证！']);
        }
        $pdf = Db::name('decl_user_trade_pdf')->where(['trade_id'=> $data['trade_id'], 'trade_type'=> $data['trade_type'], 'type'=> $data['type'],'is_change'=>1])->find();

        if($pdf)
        {
            switch ($data['type'])
            {
                case 'pi_invoice' :
                    $text = 'PI发票';
                    break;
                case 'ci_invoice' :
                    $text = 'CI发票';
                    break;
                case 'po_contract' :
                    $text = 'PO合同';
                    break;
                case 'ocean_manifest' :
                    $text = '海运舱单';
                    break;
                case 'highway_manifest' :
                    $text = '公路舱单';
                    break;
            }
            return json(['code' => 1, 'msg' => '该'.$text.'文件已经生成，请勿重复生成！']);
        }else{
            return json(['code' => 0, 'msg' => '可生成！']);
        }
    }

    public function upload_seal_file(Request $request)
    {
        set_time_limit(0);
        $file = request()->file('file');
        $filePath = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'seal' . DS . input('folder');
        try {
            $info = $file->move($filePath);
            $filename = $info->getFilename();
            $files = 'foll/public/uploads/seal' . DS . input('folder') . DS . $info->getSaveName();
        }catch (\Exception $e){
            return json(['code' => 0, 'msg' => '图章上传失败！']);
        }

        return json(['code' => 1, 'msg' => '图章上传成功！' ,'file_path' => $files, 'filename' => $filename]);
    }

    public function upload_file(Request $request)
    {
        set_time_limit(0);
        $file = request()->file('file');
        $filePath = ROOT_PATH . 'public' . DS . 'uploads' . DS . 'default_pdf';
        try {
            $info = $file->move($filePath);
            $filename = $info->getFilename();
            $files = '/public/uploads/default_pdf' . DS . $info->getSaveName();
        }catch (\Exception $e){
            return json(['code' => 0, 'msg' => '文件上传失败！']);
        }

        return json(['code' => 1, 'msg' => '文件上传成功！' ,'file_path' => $files, 'filename' => $filename]);
    }

    public function save_pdf()
    {
        $data = input();
        $data['is_change'] = 1;
        $data['create_at'] = time();
        try {
            DB::name('decl_user_trade_pdf')->insert($data);
        }catch (\Exception $e){
            return json(['code' => 0, 'msg' => '生成pdf失败！']);
        }
        return json(['code' => 1, 'msg' => '生成pdf成功！']);
    }

    public function change_buyer()
    {
        if ( request()->isPost() || request()->isAjax())
        {

        }else{
            $type = input('type');
            $trade_id = input('entry_id');
            $entry_order = Db::name('decl_user_entry')->where('id',$trade_id)->find();
            $trade_platform = Db::name('decl_user_trade_platform')->where('ordersn',$entry_order['relation_ordersn'])->find();
            // 公用买家
            $default_buyer = DB::name('default_buyer')->where('id',1)->find();
            $order = Db::name('decl_user_trade_order')->where('trade_id',$trade_platform['id'])->find();
            
            //修改个人买家,2021\07\28
//            $shop_order = Db::name('sz_yi_order')->where('trade_id',$order['trade_id'])->field(['address'])->find();
//            if($shop_order['address']){
//                $buyer_info = unserialize($shop_order['address']);
//                if($buyer_info['buyer_type']==1){
//                    $default_buyer = Db::name('decl_user_enterprise_buyer')->where('id',$buyer_info['buyer_id'])->find();
//                }else{
//                    $default_buyer = Db::name('decl_user_personal_buyer')->where('id',$buyer_info['buyer_id'])->find();
//                    $default_buyer['company_name'] = $default_buyer['first_name'].$default_buyer['last_name'];
//                    $default_buyer['company_tel'] = $default_buyer['tel'];
//                    $default_buyer['company_address'] = $default_buyer['address'];
//                }
//                $default_buyer['company_ename']='';
//            }
//            print_r($default_buyer);die;
            $order_usd_rate = getExchangeRate(502);
            if($order['currency'] != 142)
            {
                // 不是人民币
                $ordder_rate = getExchangeRate($order['currency']);
                if( $ordder_rate != '0.00' )
                {
                    $order['order_amount_cny'] = sprintf('%.2f', $order['receiving_amount'] * $ordder_rate);
                    if($order['currency'] == 502)
                    {
                        $order['order_amount_usd'] = sprintf('%.2f', $order['receiving_amount']);
                    }else{
                        $order['order_amount_usd'] = sprintf('%.2f', $order['order_amount_cny'] / $order_usd_rate);
                    }
                }else{
                    $order['order_amount_cny'] = '0.00';
                    $order['order_amount_usd'] = '0.00';
                }
            }else{
                $order['order_amount_cny'] = sprintf('%.2f', $order['receiving_amount'] );
                $order['order_amount_usd'] = sprintf('%.2f', $order['receiving_amount'] / $order_usd_rate);
            }

            $order_goods = Db::name('decl_user_trade_order_goods')->where('trade_id',$trade_platform['id'])->select();
            foreach ($order_goods as $k => $v) {
                // 汇率兑人民币
                if($v['currency'] != 142)
                {
                    $default_rate = getExchangeRate($v['currency']);
                    if( $default_rate != '0.00' )
                    {
                        $order_goods[$k]['goods_price_cny'] = sprintf('%.2f', $v['goods_price'] * $default_rate);
                        $order_goods[$k]['goods_total_price_cny'] = sprintf('%.2f', $v['goods_total_price'] * $default_rate);
                    }else{
                        $order_goods[$k]['goods_price_cny'] = "0.00";
                    }
                }else{
                    $order_goods[$k]['goods_price_cny'] = sprintf('%.2f', $v['goods_price'] );
                    $order_goods[$k]['goods_total_price_cny'] = sprintf('%.2f', $v['goods_total_price'] );
                }
                $order_goods[$k]['unit_name'] = Db::name('unit')->where('code_value',$v['goods_unit'])->value('code_name');
                $order_goods[$k]['country_name'] = Db::name('country_code')->where('code_value',$v['country'])->value('code_name');
            }
            switch ($type) {
                case 'pi':
                    $doc_data = DB::name('decl_user_trade_pdf')->where(['type'=>'pi_invoice','trade_type'=>1,'trade_id'=>$trade_platform['id'],'is_change'=>0])->find();
                    if($doc_data)
                    {
                        $doc_data['account'] = Db::name('offshore_account')->where('id',$doc_data['collect_account'])->find();
                    }
                    return view('entrymanage/change_pi', ['default_buyer' => $default_buyer, 'doc_data' => $doc_data, 'order' => $order, 'order_goods' => $order_goods]);
                break;
                case 'ci':
                    $doc_data = DB::name('decl_user_trade_pdf')->where(['type'=>'ci_invoice','trade_type'=>1,'trade_id'=>$trade_platform['id'],'is_change'=>0])->find();
                    if($doc_data)
                    {
                        $doc_data['account'] = Db::name('offshore_account')->where('id',$doc_data['collect_account'])->find();
                    }
                    return view('entrymanage/change_ci', ['default_buyer' => $default_buyer, 'doc_data' => $doc_data,'order' => $order, 'order_goods' => $order_goods]);
                break;
                case 'po':
                    $doc_data = DB::name('decl_user_trade_pdf')->where(['type'=>'po_contract','trade_type'=>1,'trade_id'=>$trade_platform['id'],'is_change'=>0])->find();
                    if($doc_data)
                    {
                        $doc_data['account'] = Db::name('offshore_account')->where('id',$doc_data['collect_account'])->find();
                    }
                    return view('entrymanage/change_po', ['default_buyer' => $default_buyer, 'doc_data' => $doc_data,'order' => $order, 'order_goods' => $order_goods]);
                break;
                //2021/08/03,新增海运舱单
                case 'ocean_manifest':
                    $doc_data = DB::name('decl_user_trade_pdf')->where(['type'=>'ocean_manifest','trade_type'=>1,'trade_id'=>$trade_platform['id'],'is_change'=>0])->find();
                    //运单信息
                    $order['info'] = DB::name('sz_yi_order o')
                                    ->join('sz_yi_member m','m.openid=o.openid','left')
                                    ->join('sz_yi_member_address ma','ma.id=o.addressid','left')
                                    ->join('users','users.uid=o.supplier_uid','left')
                                    ->join('customs_export_declarationlist_list dl','dl.ordersn=o.ordersn','left')
                                    ->join('customs_export_declarationlist_head dh','dh.id=dl.hid','left')
                                    ->join('country_code cc','cc.code_value=dh.country','left')
                                    ->join('customs_export_logistics_waybill_list lwl','lwl.logistics_no=dl.logistics_no','left')
                                    ->where('o.ordersn',$order['ordersn'])
                                    ->field(['m.realname','ma.province','ma.city','ma.area','ma.address','users.username','o.id as orderid','dl.pack_no','dl.logistics_no','dh.voyage_no','dh.pod','cc.code_name','dh.loct_no','dh.traf_name','dh.license_no','lwl.create_at'])->find();
                    if(empty($order['info'])){
                        exit('商户还没生成海运舱单！');
                    }
                    //商品信息
                    $order['goods_info2'] = DB::name('sz_yi_order')
                                            ->alias('o')
                                            ->join('sz_yi_order_goods og','o.id = og.orderid','left')
                                            ->join('sz_yi_goods g','g.id = og.goodsid','left')
                                            ->join('customs_export_declarationlist_list dl','dl.ordersn = o.ordersn','left')
                                            ->join('customs_export_declarationlist_goods dg','dg.oid = dl.id','left')
                                            ->where('o.ordersn',$order['ordersn'])
                                            ->field(['g.id','g.title as gname','og.total','dl.gross_weight','dl.net_weight','dg.gmodel','g.width','g.length','g.height'])
                                            ->select();
                    $total = 0;
                    foreach($order['goods_info2'] as $item=>$val){
                        $total +=$val['total'];
                    }
                    $order['info']['total'] = $total;
                    $order['info']['create_at'] = date('Y-m-d',$order['info']['create_at']);
                    
                    // 获取公用买家
                    $common_buyer = $this->common_buyer();
                    // 获取公用卖家
                    $common_seller = $this->common_seller();
                    // print_r($common_buyer);die;
                    return view('entrymanage/change_ocean_manifest', ['default_buyer' => $default_buyer, 'doc_data' => $doc_data,'order' => $order, 'order_goods' => $order_goods,'common_buyer'=>$common_buyer,'common_seller'=>$common_seller]);
                break;
                //2021/08/06,新增公路舱单
                case 'highway_manifest':
                    $doc_data = DB::name('decl_user_trade_pdf')->where(['type'=>'highway_manifest','trade_type'=>1,'trade_id'=>$trade_platform['id'],'is_change'=>0])->find();

                    $shopOrder = Db::name('sz_yi_order o')
                                ->join('sz_yi_member m','m.openid=o.openid')
                                ->join('sz_yi_member_address ma','ma.id=o.addressid')
                                ->join('users','users.uid=o.supplier_uid')
                                ->join('decl_user du','du.supplier=users.uid')
                                ->join('customs_export_declarationlist_list dl','dl.ordersn=o.ordersn')
                                ->join('customs_export_declarationlist_head dh','dh.id=dl.hid')
                                ->join('country_code cc','cc.code_value=dh.country')
                                ->join('port_code pc','pc.code_value=dh.pod')
                                ->join('customs_export_logistics_waybill_list lwl','lwl.logistics_no=dl.logistics_no')
                                ->where('o.ordersn',$order['ordersn'])
                                ->field(['dh.ie_date','pc.code_name','m.realname','m.mobile','ma.province','ma.city','ma.area','ma.address','du.user_name','du.address as decl_address','du.company_name','du.user_tel','dh.trade_mode','dh.port_code','cc.code_name as country'])->find();
                    if(empty($shopOrder)){
                        exit('商户还没生成公路舱单！');
                    }
                    //查询装货地点
                    $shopOrder['port_code'] = Db::name('customs_codes')
                        ->where('AreaCode','like','%'.$shopOrder['port_code'].'%')->field('AreaCode')->find()['AreaCode'];
                        
                    $shopOrder['port_code'] = explode(':',$shopOrder['port_code'])[1];
                    $shopOrder['ie_date'] = date('Y年m月d日',strtotime($shopOrder['ie_date']));
                    $shopOrder['ginfo'] = Db::name('sz_yi_order o')
                                        ->join('sz_yi_order_goods og','og.orderid=o.id')
                                        ->join('sz_yi_goods g','g.id=og.goodsid')
                                        ->join('customs_export_declarationlist_list dl','dl.ordersn=o.ordersn')
                                        ->join('customs_export_declarationlist_goods dg','dg.oid=dl.id')
                                        ->where('o.ordersn',$order['ordersn'])
                                        ->field(['g.title as gname','g.goodssn','og.total','g.marketprice','dl.gross_weight','dl.net_weight','dg.gmodel','g.width','g.length','g.height'])->select();

                    foreach($shopOrder['ginfo'] as $k => &$v){
                        $v['total_gross_weight'] = $v['total']*$v['gross_weight'];//总重量
                        $v['total_net_weight'] = $v['total']*$v['net_weight'];//总净重
                        $v['total_price'] = $v['total']*$v['marketprice'];
                    }
                    $total = 0;
                    $total_weight = 0;
                    foreach($shopOrder['ginfo'] as $item=>$val){
                        $total +=$val['total'];
                        $total_weight +=$val['total_gross_weight'];
                    }
                    $shopOrder['total'] = $total;
                    $shopOrder['total_weight'] = $total_weight;
                    unset($total);
                    unset($total_weight);
        
                    //创建司机纸编号
                    $shopOrder['driversn'] = $this->createDriverSn('617436824',0);
                    // $highway_sn = $this->createSn($trade_info->ordersn);
                    
                    // 获取公用买家
                    $common_buyer = $this->common_buyer();
                    // 获取公用卖家
                    $common_seller = $this->common_seller();

                    return view('entrymanage/change_highway_manifest', ['default_buyer' => $default_buyer, 'doc_data' => $doc_data,'shopOrder' => $shopOrder, 'order_goods' => $order_goods,'common_buyer'=>$common_buyer,'common_seller'=>$common_seller]);
                break;
            }
        }
    }
    
    /**司机纸编号生成
     * @param $EnterpriseOrganizationCode 企业组织机构代码
     * @param int $InOrOut 进口1，出口0
     * return String $driversn
     */
    public function createDriverSn($EnterpriseOrganizationCode,$InOrOut=0){
        $driversn = $EnterpriseOrganizationCode.$InOrOut.$this->GetRandStr(3);
        return $driversn;
    }

    /**
     * 获得指定位数随机数
     * @param $length  指定位数
     * @return string  处理后的字符串
     */
    function GetRandStr($length){
        $str='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len=strlen($str)-1;
        $randstr='';
        for($i=0;$i<$length;$i++){
            $num=mt_rand(0,$len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }

    public function oklist()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            //排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');
            
            $list = Db::name('decl_user_entry')->where('status',1)->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                switch ($v['status']) {
                    case -1 :
                        $list[$k]['status'] = '申请已撤回';
                    break;
                    case 0 :
                        $list[$k]['status'] = '申请已提交，还在审核中';
                    break;
                    case 1 :
                        $list[$k]['status'] = '审核已通过，款项已入账';
                    break;
                    case 2 :
                        $list[$k]['status'] = '审核不通过，敬请再提交';
                    break;
                    case 3 :
                        $list[$k]['status'] = '审核初通过，正待收款中';
                    break;
                    case 4 :
                        $list[$k]['status'] = '款项已到账，正在审查中';
                    break;
                }
                $list[$k]['user_name'] = DB::name('decl_user')->where('id',$v['uid'])->value('user_name');
                $list[$k]['order_type'] = $v['order_type'] == 1 ? '平台交易' : '其他交易';
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                
            }
            $total = Db::name('decl_user_entry')->where('status',1)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view();
        }
    }

    public function noklist_check()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $status_text = $data['status_text'];
            unset($data['status_text']);
            Db::startTrans();
            try{
                $info = Db::name('decl_user_entry')->where('id',$data['id'])->find();
                if($info['status'] == 1)
                {
                    return json(['code'=>0, 'message'=>'该订单已经完成,不能再操作']);
                }else{
                    if($info['status'] == $data['status'])
                    {
                        return json(['code'=>0, 'message'=>'该订单已经当前状态']);
                    }
                }
                // 获取币种余额
                $user_money = getUserMoney($info['uid'],$info['currency']);

                // 入账处理
                if($data['status'] == 1)
                {
                    // 入账
//                    createMoneyLog($info['uid'],$user_money,$data['entryed_money'],$info['currency'],'离岸收款入账',4,$info['id'],3);
                    //由于出口后台已插入数据，在这里只需要修改log表和修改user_money表币种总金额
//                    $new_money = floatval($user_money)+floatval($data['entryed_money']);
                    $new_money = floatval($user_money)+floatval($data['real_entry_money']);
                    Db::name('decl_user_money_log')
                        ->where(['uid'=>$info['uid'],'change_type'=>3,'status'=>0,'change_id'=>$info['id']])
                        ->update([
                            'change_money'=>floatval($data['real_entry_money']),
                            'new_money'=>$new_money,
                            'message'=>'离岸收款已入账',
                            'status'=>1
                    ]);
                    // 更新金额
                    updageUserMoney($info['uid'],$new_money,$info['currency'],4);

                    //2021.09.01-根据结汇类型新增用户可用金额(1换汇后结汇至国内，2换汇后转账至境外)
                    $exchange_type = 0;
                    if($info['money_use']=='结汇提现'){
                        $exchange_type = 1;
                    }else if($info['money_use']=='离岸转账'){
                        $exchange_type = 2;
                    }
                    $isHaveInfo = Db::name('decl_user_money_can_use')->where(['uid'=>$info['uid'],'exchange_type'=>$exchange_type])->find();
                    $update_array=[];
                    //根据换汇币种进行修改
                    if($info['currency']=='CNY'){
                        $update_array = array_merge($update_array,['cny'=>floatval($isHaveInfo['cny'])+$new_money]);
                    }else if($info['currency']=='HKD'){
                        $update_array = array_merge($update_array,['hkd'=>floatval($isHaveInfo['hkd'])+$new_money]);
                    }else if($info['currency']=='EUR'){
                        $update_array = array_merge($update_array,['eur'=>floatval($isHaveInfo['eur'])+$new_money]);
                    }else if($info['currency']=='USD'){
                        $update_array = array_merge($update_array,['usd'=>floatval($isHaveInfo['usd'])+$new_money]);
                    }

                    if($isHaveInfo['id']){
                        Db::name('decl_user_money_can_use')->where(['uid'=>$info['uid'],'exchange_type'=>$exchange_type])->update($update_array);
                    }else{
                        $new_array = array_merge($update_array,['uid'=>$info['uid'],'exchange_type'=>$exchange_type]);
                        Db::name('decl_user_money_can_use')->insert($new_array);
                    }
                    //
                }
                if($data['status'] == 4)
                {
                    // 实际入账金额
                    $data['real_entry_money'] = $data['entryed_money'] - $data['entry_cost'];
                }
                if($data['status'] == 2)
                {
                    if($info['order_type'] == 1)
                    {
                        $orders = Db::name('decl_user_trade_platform')->where('ordersn',$info['relation_ordersn'])->find();
                        if($orders)
                        {
                            Db::name('decl_user_trade_platform')->where('id',$orders['id'])->update(['is_use'=>0]);
                        }
                    }else{
                        $orders = Db::name('decl_user_trade_other')->where('ordersn',$info['relation_ordersn'])->find();
                        if($orders)
                        {
                            Db::name('decl_user_trade_other')->where('id',$orders['id'])->update(['is_use'=>0]);
                        }
                    }                    
                }
                Db::name('decl_user_entry')->update($data);

                // 微信通知
                $user = getUserInfo($info['uid']);
                if($user['openid'] != '')
                {
//                    sendErrorTempls([
//                        'title' =>'您的离岸收款订单已审核',
//                        'projects' => '离岸收款',
//                        'status_text' => $status_text,
//                        'time' => date('Y-m-d H:i:s',time()),
//                        'remark' => '订单号:'.$info['ordersn'],
//                        'url' => '',
//                        'openid' => $user['openid']
//                    ]);
                }
                Db::commit();
            }catch (\Exception $e){
                Db::rollback();
                // var_dump($e);
                return json(['code'=>0, 'message'=>$e->getMessage()]);
            }

            return json(['code'=>1, 'message'=>'审核成功！']);

        }else{
            $rate = "6.70";
            $entry_data = Db::name('decl_user_entry')->where('id',input('id'))->find();
            if($entry_data['order_type'] == 1)
            {
                $entry_data['order_type'] = '平台交易';
                $trade = Db::name('decl_user_trade_platform')->where(['uid'=>$entry_data['uid'], 'ordersn'=>$entry_data['relation_ordersn']])->find();
                // 商城商品上架信息
                $shop_good = Db::name('sz_yi_goods')->where('id','in',[$trade['good_ids']])->select();
            }else{
                $entry_data['order_type'] = '其他交易';
                $trade = Db::name('decl_user_trade_other')->where(['uid'=>$entry_data['uid'], 'ordersn'=>$entry_data['relation_ordersn']])->find();
                $shop_good = [];
            }

            if($entry_data['is_merchant_account']==1){
                $entry_data['merchant_account'] = Db::name('decl_user_account')->where('id',$entry_data['offshore_account_id'])->find();
            }

            if($entry_data['red_pack_type'] == 1)
            {
                $entry_data['red_pack_type'] = '金额抵扣';
            }else{
                $entry_data['red_pack_type'] = '金额抵扣';
            }
            // var_dump($trade);
            if($trade)
            {
                $result = array();
                $order = Db::name('decl_user_trade_order')->where('trade_id',$trade['id'])->find();
                $order_goods = Db::name('decl_user_trade_order_goods')->where('trade_id',$trade['id'])->select();

                // 买家
                if($trade['buyer_type'] == 1)
                {
                    $buyer = Db::name('decl_user_enterprise_buyer')->where('id',$trade['buyer_id'])->find();
                    $result['buyer'] = $buyer['company_name'];
                    $result['buyer_country'] = Db::name('country_code')->where('code_value',$buyer['country_code'])->value('code_name');
                    $buyer_e = $buyer['company_email'];
                }else{
                    $buyer = Db::name('decl_user_personal_buyer')->where('id',$trade['buyer_id'])->find();
                    $result['buyer'] = $buyer['last_name'].' '.$buyer['first_name'];
                    $result['buyer_country'] = Db::name('country_code')->where('code_value',$buyer['country_code'])->value('code_name');
                    $buyer_e = $buyer['email'];
                }

                // 获取公用买家,2021/07/27
                $common_buyer = $this->common_buyer();
                // 获取公用卖家,2021/07/27
                $common_seller = $this->common_seller();

                // echo $trade['id'];
                // 商城订单id
                $shop_order = Db::name('sz_yi_order')->field('id,openid')->where('trade_id',$trade['id'])->find();

                if(empty($shop_order)){
                    //如果为空，证明是先申报订单再关联平台交易，2021/07/27
                    $shop_order = Db::name('sz_yi_order')->field('id,openid')->where('ordersn',$order['ordersn'])->find();
                }
                // 更新商城订单openid
                if( $shop_order['openid'] == '' )
                {
                    Db::name('sz_yi_order')->update( array('id'=>$shop_order['id'], 'openid'=> $buyer_e) );
                }else{
                    //不为空则将出口选择的openid更改为原买家的tel,2021/07/29
                    $original_member = Db::name('sz_yi_member')->where('openid',$shop_order['openid'])->field('mobile')->find();
                    $buyer_e = $original_member['mobile'];
                }
                
                // 转账附言
                if( $entry_data['is_pay_ps'] == '方便附言' && $entry_data['is_buyer_name'] == '可写买家姓名')
                {
                    if( $entry_data['association'] == '付款人与买家是关联关系' )
                    {
                        $pay_postscript = 'As linked company make this payment on behalf of "Buyer Name"、"CI/PI/PO number"、"product category"';
                    }else{
                        $pay_postscript = 'As close friend make this payment on behalf of "Buyer Name"、"CI/PI/PO number"、"product category';
                    }
                }else{
                    $pay_postscript = '';
                }

                // 发票合同单证
                $trade_pdf = array();
//                print_r($trade);die;
                $trade_pdf['ci'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'ci_invoice','is_change'=> 0])->value('file_path');
                $trade_pdf['pi'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'pi_invoice','is_change'=> 0])->value('file_path');
                $trade_pdf['po'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'po_contract','is_change'=> 0])->value('file_path');

                // 变更后
                $trade_pdf_c = array();
                $trade_pdf_c['ci'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'ci_invoice','is_change'=> 1])->value('file_path');
                $trade_pdf_c['pi'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'pi_invoice','is_change'=>1] )->value('file_path');
                $trade_pdf_c['po'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'po_contract','is_change'=> 1])->value('file_path');

            }

            //找出商户配置的离岸收款手续费
            $offshore_entry_info = Db::name('decl_user')->where('id',$trade['uid'])->field('offshore_entry_info')->find();
            $offshore_entry_info = json_decode($offshore_entry_info['offshore_entry_info'],true);
            if($offshore_entry_info['entry_type']==1){
                $offshore_entry_info['currency'] = Db::name('currency')->where('code_value',$offshore_entry_info['currency'])->field('code_name')->find()['code_name'];
                //计算手续费
                $entry_data['entry_cost'] = $offshore_entry_info['money'];
            }else if($offshore_entry_info['entry_type']==2){
                //计算手续费
                $entry_data['entry_cost'] = sprintf('%.2f',$entry_data['entry_money'] * $offshore_entry_info['trade_rate']);
                if($entry_data['entry_cost']<=$offshore_entry_info['trade_low_money']){
                    $entry_data['entry_cost'] = $offshore_entry_info['trade_low_money'];
                }
            }
            $this->assign('offshore_entry_info', $offshore_entry_info);
            $this->assign('entry_data', $entry_data);
            $this->assign('result', $result);
            $this->assign('shop_good', $shop_good);
            $this->assign('pay_postscript', $pay_postscript);
            $this->assign('trade', $trade);
            $this->assign('trade_pdf', $trade_pdf);
            $this->assign('trade_pdf_c', $trade_pdf_c);
            $this->assign('shop_order', $shop_order);
            $this->assign('buyer_e', $buyer_e);
            $this->assign('common_buyer', $common_buyer);
            $this->assign('common_seller', $common_seller);

            return view();
        }
    }

    //2021/07/27添加查询公用买家卖家
    public function common_buyer(){
        $enterprise_buyer_data = array();
        $enterprise_buyer = Db::name('decl_user_enterprise_buyer')->where(['uid'=>0, 'is_delete'=>0])->select();
        $enterprise_buyer = json_decode(json_encode($enterprise_buyer), true);
        foreach ($enterprise_buyer as $k => $v) {
            $enterprise_buyer_data[$k]['id'] = $v['id'];
            $enterprise_buyer_data[$k]['platform'] = $v['platform'];
            $enterprise_buyer_data[$k]['type'] = $v['type'];
            $enterprise_buyer_data[$k]['name'] = $v['company_name'];
            $enterprise_buyer_data[$k]['email'] = $v['company_email'];
            $enterprise_buyer_data[$k]['tel'] = $v['company_tel'];
            $enterprise_buyer_data[$k]['address'] = $v['company_address'];
        }
        // 获取公用个人卖家
        $personal_buyer_data = array();
        $personal_buyer = Db::name('decl_user_personal_buyer')->where(['uid'=>0, 'is_delete'=>0])->select();
        $personal_buyer = json_decode(json_encode($personal_buyer), true);
        foreach ($personal_buyer as $k => $v) {
            $personal_buyer_data[$k]['id'] = $v['id'];
            $personal_buyer_data[$k]['platform'] = $v['platform'];
            $personal_buyer_data[$k]['type'] = $v['type'];
            $personal_buyer_data[$k]['name'] = $v['first_name'].$v['last_name'];
            $personal_buyer_data[$k]['email'] = $v['email'];
            $personal_buyer_data[$k]['tel'] = $v['tel'];
            $personal_buyer_data[$k]['address'] = $v['address'];
        }
        return array_merge($personal_buyer_data,$enterprise_buyer_data);
    }
    
    public function common_seller(){
        $enterprise_seller_data = array();
        $enterprise_seller = Db::name('decl_user_enterprise_seller')->where(['uid'=>0, 'is_delete'=>0])->select();
        $enterprise_seller = json_decode(json_encode($enterprise_seller), true);
        foreach ($enterprise_seller as $k => $v) {
            $enterprise_seller_data[$k]['id'] = $v['id'];
            $enterprise_seller_data[$k]['platform'] = $v['platform'];
            $enterprise_seller_data[$k]['type'] = $v['type'];
            $enterprise_seller_data[$k]['name'] = $v['company_name'];
            $enterprise_seller_data[$k]['email'] = $v['company_email'];
            $enterprise_seller_data[$k]['tel'] = $v['company_tel'];
        }
        // 获取公用个人卖家
        $personal_seller_data = array();
        $personal_seller = Db::name('decl_user_personal_seller')->where(['uid'=>0, 'is_delete'=>0])->select();
        $personal_seller = json_decode(json_encode($personal_seller), true);
        foreach ($personal_seller as $k => $v) {
            $personal_seller_data[$k]['id'] = $v['id'];
            $personal_seller_data[$k]['platform'] = $v['platform'];
            $personal_seller_data[$k]['type'] = $v['type'];
            $personal_seller_data[$k]['name'] = $v['last_name'].' '.$v['first_name'];
            $personal_seller_data[$k]['email'] = $v['email'];
            $personal_seller_data[$k]['tel'] = $v['tel'];
        }
        return array_merge($enterprise_seller_data,$personal_seller_data);
    }
    
    public function change_buyer_info(){
        if ( request()->isPost() || request()->isAjax()){
            $data = explode(',',input()['buyer']);
            $buyer_id = $data[0];
            $buyer_type = $data[2];//1是企业买家，2是个人买家
            $orderid = input()['orderid'];
            $ser_data = [];
            
            if($buyer_type==1){
                $res = Db::name('decl_user_enterprise_buyer')->where(['uid'=>0, 'is_delete'=>0,'type'=>$buyer_type,'id'=>$buyer_id])->find();
                $ser_data = ['id'=>$res['id'],'realname'=>$res['company_name'],'mobile'=>$res['company_tel'],'address'=>$res['company_address'],'province'=>'','city'=>'','area'=>'','street'=>'','buyer_id'=>$buyer_id,'buyer_type'=>$buyer_type,'email'=>$res['company_email']];
            }else{
                $res = Db::name('decl_user_personal_buyer')->where(['uid'=>0, 'is_delete'=>0,'type'=>$buyer_type,'id'=>$buyer_id])->find();
                $ser_data = ['id'=>$res['id'],'realname'=>$res['first_name'].$res['last_name'],'mobile'=>$res['tel'],'address'=>$res['address'],'province'=>'','city'=>'','area'=>'','street'=>'','buyer_id'=>$buyer_id,'buyer_type'=>$buyer_type,'email'=>$res['email']];
            }
            // print_r($res);die;
            $result = Db::name('sz_yi_order')->where('id',$orderid)->update(['address'=>serialize($ser_data)]);
            if($result){
                return json(['message'=>'更改买家信息成功','code'=>1]);
            }
        }
    }

    public function change_seller_info(){
        if ( request()->isPost() || request()->isAjax()){
            $data = explode(',',input()['buyer']);
            $buyer_id = $data[0];
            $buyer_type = $data[2];//1是企业卖家，2是个人卖家
            $orderid = input()['orderid'];
            $ser_data = [];

            if($buyer_type==1){
                $res = Db::name('decl_user_enterprise_seller')->where(['uid'=>0, 'is_delete'=>0,'type'=>$buyer_type,'id'=>$buyer_id])->find();
            }else{
                $res = Db::name('decl_user_personal_seller')->where(['uid'=>0, 'is_delete'=>0,'type'=>$buyer_type,'id'=>$buyer_id])->find();
            }
            // print_r($res);die;
            $result = Db::name('sz_yi_order')->where('id',$orderid)->update(['seller_id'=>$res['id'],'seller_type'=>$buyer_type]);
            if($result){
                return json(['message'=>'更改卖家信息成功','code'=>1]);
            }
        }
    }

    //单证下载,2021/08/02
    public function document_download(){
        $req = input();

        $entry_data = Db::name('decl_user_entry')->where('id',input('entry_data'))->find();
        if($entry_data['order_type'] == 1)
        {
            $entry_data['order_type'] = '平台交易';
            $trade = Db::name('decl_user_trade_platform')->where(['uid'=>$entry_data['uid'], 'ordersn'=>$entry_data['relation_ordersn']])->find();
        }else{
            $entry_data['order_type'] = '其他交易';
            $trade = Db::name('decl_user_trade_other')->where(['uid'=>$entry_data['uid'], 'ordersn'=>$entry_data['relation_ordersn']])->find();
        }

        if($trade){
            // 发票合同单证
            $trade_pdf = array();
            $trade_pdf['ci'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'ci_invoice','is_change'=> 0])->value('file_path');
            $trade_pdf['pi'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'pi_invoice','is_change'=> 0])->value('file_path');
            $trade_pdf['po'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'po_contract','is_change'=> 0])->value('file_path');
            $trade_pdf['ocean_manifest'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'ocean_manifest','is_change'=> 0])->value('file_path');
            $trade_pdf['highway_manifest'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'highway_manifest','is_change'=> 0])->value('file_path');

            // 变更后
            $trade_pdf_c = array();
            $trade_pdf_c['ci'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'ci_invoice','is_change'=> 1])->value('file_path');
            $trade_pdf_c['pi'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'pi_invoice','is_change'=>1] )->value('file_path');
            $trade_pdf_c['po'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'po_contract','is_change'=> 1])->value('file_path');
            $trade_pdf_c['ocean_manifest'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'ocean_manifest','is_change'=> 1])->value('file_path');
        }

        $this->assign('entry_data', $entry_data);
        $this->assign('trade', $trade);
        $this->assign('trade_pdf', $trade_pdf);
        $this->assign('trade_pdf_c', $trade_pdf_c);
        return view();
    }
    //买家变更
    public function buyer_change(){
        $req = input();

        $entry_data = Db::name('decl_user_entry')->where('id',input('entry_data'))->find();
        if($entry_data['order_type'] == 1)
        {
            $entry_data['order_type'] = '平台交易';
            $trade = Db::name('decl_user_trade_platform')->where(['uid'=>$entry_data['uid'], 'ordersn'=>$entry_data['relation_ordersn']])->find();
        }else{
            $entry_data['order_type'] = '其他交易';
            $trade = Db::name('decl_user_trade_other')->where(['uid'=>$entry_data['uid'], 'ordersn'=>$entry_data['relation_ordersn']])->find();
        }

        if($trade){
            // 发票合同单证
            $trade_pdf = array();
            $trade_pdf['ci'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'ci_invoice','is_change'=> 0])->value('file_path');
            $trade_pdf['pi'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'pi_invoice','is_change'=> 0])->value('file_path');
            $trade_pdf['po'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'po_contract','is_change'=> 0])->value('file_path');

            // 变更后
            $trade_pdf_c = array();
            $trade_pdf_c['ci'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'ci_invoice','is_change'=> 1])->value('file_path');
            $trade_pdf_c['pi'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'pi_invoice','is_change'=>1] )->value('file_path');
            $trade_pdf_c['po'] = Db::name('decl_user_trade_pdf')->where(['trade_id'=>$trade['id'], 'type'=>'po_contract','is_change'=> 1])->value('file_path');
        }

        $this->assign('entry_data', $entry_data);
        $this->assign('trade', $trade);
        $this->assign('trade_pdf', $trade_pdf);
        $this->assign('trade_pdf_c', $trade_pdf_c);

        return view();
    }

    public function phpMaxMin($arr = [],$keys = ''){
        $max['key'] = '';
        $max['value'] = '';
        $min['key'] = '';
        $min['value'] = '';

        foreach ($arr as $key => $val){

            if($max['key'] === ''){

                $max['key'] = $key;
                $max['value'] = $val[$keys];

            }

            if((int)$max['value'] < $val[$keys]){

                $max['key'] = $key;
                $max['value'] = $val[$keys];

            }

            if($min['key'] === ''){

                $min['key'] = $key;
                $min['value'] = $val[$keys];

            }

            if((int)$min['value'] > $val[$keys]){

                $min['key'] = $key;
                $min['value'] = $val[$keys];
            }

        }
        $array['max'] = $max;
        $array['min'] = $min;
        return $array;

    }
}