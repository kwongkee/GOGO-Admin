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
 *
 * Class Reduction
 * @package app\admin\controller
 */
class Predeclare extends Auth
{
    //新增提单
    public function addlading(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {

        }
    }

    public function createSelfOrdersn()
    {
        $millisecond = round(explode(" ", microtime())[0] * 1000);
        $ordersn = 'GG198' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);
        return $ordersn;
    }

    /**
     * 生成批次名称.
     */
    private function piciName($pici)
    {

        $info = Db::name('customs_export_billoflading_batch')->where(array('type'=>'CEB303Message','tracking_num'=>$pici))->order('id', 'desc')->find(['batch_num']);
        // 第一次
        $snum = sprintf('%02d', 01);

        // 输入的批次总单号小于15位补X
        if (strlen($pici) < 15) {
            $l = (15 - strlen($pici));
            for ($i = 0; $i < $l; ++$i) {
                $pici .= 'X';
            }
        }

        // 二次提交
        if (!empty($info)) {
            $sn = $info;
            $num = substr($sn->batch_num, -2, 2);
            $snum = sprintf('%02d', ($num + 1));
            $p = 'E'.$pici.$snum;

            return ['pici' => $p, 'num' => $snum];
        }
        // time 订单时间
        return ['pici' => 'E'.$pici.'01', 'num' => $snum];
    }

    //海关guid
    private function getGuidOnlyValue(){
        $a4 = uniqid().rand(10,99);
        $a4 = $this->insertToStr($a4,4,'-');
        $a4 = $a4.$this->GetRandStr(1);

        $a1 = $this->GetRandStr(8);
        $a2 = $this->GetRandStr(4);
        $a3 = $this->GetRandStr(4);
        $val = $a1.'-'.$a2.'-'.$a3.'-'.$a4;
        return strtoupper($val);
    }

    /**
     * 指定位置插入字符串
     * @param $str  原字符串
     * @param $i    插入位置
     * @param $substr 插入字符串
     * @return string 处理后的字符串
     */
    private function insertToStr($str, $i, $substr){
        $startstr="";
        for($j=0; $j<$i; $j++){
            $startstr .= $str[$j];
        }

        //指定插入位置后的字符串
        $laststr="";
        for ($j=$i; $j<strlen($str); $j++){
            $laststr .= $str[$j];
        }

        //将插入位置前，要插入的，插入位置后三个字符串拼接起来
        $str = $startstr . $substr . $laststr;

        //返回结果
        return $str;
    }

    /**
     * 获得指定位数随机数
     * @param $length  指定位数
     * @return string  处理后的字符串
     */
    private function GetRandStr($length){
        $str='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len=strlen($str)-1;
        $randstr='';
        for($i=0;$i<$length;$i++){
            $num=mt_rand(0,$len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }

    //获取headid
    private function getHeadId($tracking_num)
    {
        $hid = Db::name('customs_export_order_head')->where('tracking_num', $tracking_num)->value('id');
        return $hid;
    }

    //生成订单号
    private function createOrderSn()
    {
        $millisecond = round(explode(" ", microtime())[0] * 1000);
        $ordersn = 'EP' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);
        return $ordersn;
    }
    
    public function getBatchStatus($status) {
        switch ($status) {
            case '0':
                $statusText = '待验证';
                break;
            case '1':
                $statusText = '待提交';
                break;
            case '3':
                $statusText = '待申报';
                break;
            case '4':
                $statusText = '已申报';
                break;
            case '5':
                $statusText = '申请撤回';
                break;
            case '6':
                $statusText = '已撤回';
                break;    
        }
        return $statusText;
    }

    //电子订单
    public function ecommerce(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $where =['is_predeclare'=>1];

            $count = Db::name('customs_export_order_head')->where($where)
                ->count();
            $rows = Db::name('customs_export_order_head')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['app_type'] = '新增';
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('predeclare/declare/ecommerce',compact('list'));
        }
    }

    //电子订单-关联预申报编号
    public function ecommerce_connect(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            //1、查询该预报编号有无配置口岸信息
            $log = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b',' b.openid=a.openid','left')
                ->where('a.pre_batch_num',trim($dat['pre_batch_num']))
                ->field(['a.*','b.fanid as uid','b.nickname'])
                ->find();
            if(empty($log['sid'])){
                return json(['status'=>0,'msg'=>'请先配置用户的口岸信息']);
            }

            //2、新增电子订单报头
            $order_head = Db::name('customs_export_order_head')->insert([
                'uid' => $log['uid'],
                'sid' => $log['sid'],
                'tracking_num' => $log['pre_batch_num'],
                'app_type' => 1,
                'app_time' => date('YmdHis',time()),
                'app_status' => 2,
                'order_type' => 'E',//电商平台的订单类型 I-进口商品订单；E-B2C出口商品订单；B-B2B出口商品订单；W-海外仓出口商品订单
                'ebp_type' => 1,//平台境外/境内类型
                'ebc_code' => $log['ebcCode'],
                'ebp_code' => $log['ebpCode'],
                'ebp_name' => $log['ebpName'],
                'ebc_name' => $log['ebcName'],
                'self_ordersn' => $this->createSelfOrdersn(),
                'create_at' => time(),
                'is_predeclare'=>1
            ]);
//             $order_head=true;
            if($order_head){
                //3、生成用户
                $user = Db::name('sz_yi_member')->where('openid',$log['openid'])->find();
                if(empty($user['id'])){
                    $insert_id = Db::name('sz_yi_member')->insertGetId(['openid'=>$log['openid'],'uniacid'=>3,'realname'=>$log['nickname'],'pwd'=>md5('888888'),'createtime'=>time(),'nickname'=>$log['nickname']]);
                    $user['id'] = $insert_id;
                }

                //4-1、1个运单等于1个订单,查找所有订(运)单笔数并去重
                $batch_total_num = Db::name('customs_pre_declare')
                    ->alias('a')
                    ->join('customs_goods_pre_log b','b.pre_batch_num=a.pre_batch_num','right')
                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                    ->distinct(true)
                    ->field('b.logisticsNo')
                    ->select();
                //4-2、生成批次
                $batch_num = $this->piciName( $log['pre_batch_num'] )['pici'];
                Db::name('customs_export_billoflading_batch')->insert([
                    'uid' => $log['uid'],
                    'tracking_num' => $log['pre_batch_num'],
                    'batch_num' => $batch_num,
                    'type' => "CEB303Message",
                    'batch_status' => 3,
                    'total_num' => count($batch_total_num),
                    'success_num' => 0,
                    'error_num' => 0,
                    'create_at' => time()
                ]);

                //5、生成订单
                //先查询风险表关联哪个
                $connect_glist = Db::name('customs_declare_grisk_head')->where('pre_batch_num',$log['pre_batch_num'])->field(['connect_glist'])->find();

                foreach($batch_total_num as $k=>$v){
                    if($connect_glist['connect_glist']==4){
                        //归类
                        $goods_info = Db::name('customs_declare_risk_list')->where('pre_batch_num',$log['pre_batch_num'])->where('status',0)->where('logisticsNo',$v['logisticsNo'])->field(['connect_glist','unit','unit1','gcode','itemName','update_itemName','itemNo'])->select();
                        $glist2 = $goods_info[0]['connect_glist'];
                        if($glist2==3){
                            //调值
                            foreach($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_adjust_log')
                                        ->alias('a')
                                        ->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')
                                        ->where('a.pre_batch_num',$log['pre_batch_num'])
                                        ->where('a.logisticsNo',$v['logisticsNo'])
                                        ->where('b.itemNo',$vv['itemNo'])
                                        ->field(['a.price','a.totalPrice','a.charge','b.goodsInfo','b.currency','b.qty','b.freight'])
                                        ->find();
                                if(!empty($info2['currency'])){
                                    $goods_info[$kk]['price'] = $info2['price'];
                                    $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                    $goods_info[$kk]['charge'] = $info2['charge'];
                                    $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                    $goods_info[$kk]['currency'] = $info2['currency'];
                                    $goods_info[$kk]['qty'] = $info2['qty'];
                                    $goods_info[$kk]['freight'] = $info2['freight'];
                                }
                            }
                        }elseif($glist2==2){
                            //补缺
                            foreach ($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_fill_log')->where('pre_batch_num',$log['pre_batch_num'])->where('logisticsNo',$v['logisticsNo'])->where('itemNo',$vv['itemNo'])->field(['price','totalPrice','charge','goodsInfo','currency','qty','freight'])->find();
                                if(!empty($info2['currency'])) {
                                    $goods_info[$kk]['price'] = $info2['price'];
                                    $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                    $goods_info[$kk]['charge'] = $info2['charge'];
                                    $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                    $goods_info[$kk]['currency'] = $info2['currency'];
                                    $goods_info[$kk]['qty'] = $info2['qty'];
                                    $goods_info[$kk]['freight'] = $info2['freight'];
                                }
                            }
                        }elseif($glist2==1){
                            //原始
                            foreach ($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_log')->where('pre_batch_num',$log['pre_batch_num'])->where('logisticsNo',$v['logisticsNo'])->where('itemNo',$vv['itemNo'])->field(['price','totalPrice','charge','goodsInfo','currency','qty','freight'])->find();
                                if(!empty($info2['currency'])) {
                                    $goods_info[$kk]['price'] = $info2['price'];
                                    $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                    $goods_info[$kk]['charge'] = $info2['charge'];
                                    $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                    $goods_info[$kk]['currency'] = $info2['currency'];
                                    $goods_info[$kk]['qty'] = $info2['qty'];
                                    $goods_info[$kk]['freight'] = $info2['freight'];
                                }
                            }
                        }

                    }
                    elseif($connect_glist['connect_glist']==3){
                        //调值
                        $goods_info = Db::name('customs_goods_pre_adjust_log')
                                    ->alias('a')
                                    ->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')
                                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                                    ->where('a.logisticsNo',$v['logisticsNo'])
                                    ->where('a.status',0)
                                    ->field(['a.price','a.totalPrice','a.charge','b.itemNo','b.itemName','b.update_itemName','b.goodsInfo','b.unit','b.currency','b.qty','b.freight'])
                                    ->select();
                    }
                    elseif($connect_glist['connect_glist']==2){
                        //补缺
                        $goods_info = Db::name('customs_goods_pre_fill_log')
                                    ->where('pre_batch_num',$log['pre_batch_num'])
                                    ->where('logisticsNo',$v['logisticsNo'])
                                    ->where('status',0)
                                    ->select();
                    }
                    elseif($connect_glist['connect_glist']==1){
                        //原始
                        $goods_info = Db::name('customs_goods_pre_log')
                                    ->where('pre_batch_num',$log['pre_batch_num'])
                                    ->where('logisticsNo',$v['logisticsNo'])
                                    ->where('status',0)
                                    ->select();
                    }                
                    $order_price=0;
                    $freight=0;
                    foreach($goods_info as $k2=>$v2){
                        $order_price+=$v2['totalPrice'];
                        $freight+=$v2['freight'];
                    }

                    $list = array();
                    $list['uid'] = $log['uid'];
                    $list['hid'] = $this->getHeadId($log['pre_batch_num']);
                    $list['batch_num'] = $batch_num;
                    $list['guid'] = $this->getGuidOnlyValue();
                    $list['ordersn'] =  $this->createOrderSn();//如果不填写商城订单号的话，就会生成一个新的
                    $list['order_price'] = $order_price;
                    $list['freight'] = $freight;
                    $list['currency'] = 142;
                    $list['order_status'] = 0;
                    $list['order_action_message'] = '待申报【'.$list['guid'].'】';
                    $list['order_action_time'] = date('Y-m-d H:i:s',time());
                    $list['logistics_no'] = $v['logisticsNo'];
                    $list['note'] = 'test';
                    $list['create_at'] = time();
                    $list['shop_mid'] = $user['id'];
    
    
                    $order_id = Db::name('customs_export_order_list')->insertGetId($list);
                    
                    //6、生成订单商品表
                    foreach($goods_info as $k2=>$v2){
                        $itemName = $v2['itemName'];
                        if(!empty($v2['update_itemName'])){
                            $itemName = $v2['update_itemName'];
                        }
                        $goods = array();
                        $goods['order_id'] = $order_id;
                        $goods['gnum'] = $k2 * 1 + 1;
                        $goods['item_no'] = $v2['itemNo'];
                        $goods['item_name'] = $itemName;
                        $goods['item_describe'] = $v2['goodsInfo'];
                        $goods['bar_code'] = "无";
                        $goods['goods_unit'] = $v2['unit'];
                        $goods['currency'] = $v2['currency'];
                        $goods['goods_qty'] = $v2['qty'];
                        $goods['goods_price'] = $v2['price'];
                        $goods['goods_total_price'] = $v2['totalPrice'];
    
                        Db::name('customs_export_order_goods')->insert($goods);
                    }
                }
                return json(['msg'=>'关联成功','status'=>1]);
            }else{
                return json(['msg'=>'关联失败','status'=>1]);
            }

        }else{
            return view('predeclare/declare/ecommerce_connect',compact('list'));
        }
    }

    //电子订单-申报列表
    public function ecommerce_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $tracking_num = $dat['tracking_num'];
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $user = Db::name('customs_pre_declare')
                    ->alias('a')
                    ->join('mc_mapping_fans b','b.openid=a.openid','left')
                    ->where('a.pre_batch_num',$tracking_num)
                    ->field(['b.nickname','b.fanid'])
                    ->find();

            $where =['uid'=>$user['fanid'],'type'=>'CEB303Message','tracking_num'=>$tracking_num];
    
            $count = Db::name('customs_export_billoflading_batch')->where($where)->count();
            $rows = Db::name('customs_export_billoflading_batch')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['ordersn'] = Db::name('customs_export_order_list')->where(array('batch_num'=>$item['batch_num']))->value('ordersn');
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['batch_status'] = $this->getBatchStatus($item['batch_status']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $tracking_num = $dat['tracking_num'];
            return view('predeclare/declare/ecommerce_list',compact('tracking_num'));
        }
    }

    //收款单
    public function payment(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $where =['is_predeclare'=>1];

            $count = Db::name('customs_export_payment_slip_head')->where($where)
                ->count();
            $rows = Db::name('customs_export_payment_slip_head')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['app_type'] = '新增';
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);

        }else{
            return view('predeclare/declare/payment');
        }
    }

    //收款单-关联预申报编号
    public function payment_connect(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            //1、查询该预报编号有无配置口岸信息
            $log = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b',' b.openid=a.openid','left')
                ->where('a.pre_batch_num',trim($dat['pre_batch_num']))
                ->field(['a.*','b.fanid as uid','b.nickname'])
                ->find();
            if(empty($log['sid'])){
                return json(['status'=>0,'msg'=>'请先配置用户的口岸信息']);
            }

            //2、新增收款单报头
            $HeadId = Db::name('customs_export_payment_slip_head')->insertGetId([
                'uid' => $log['uid'],
                'sid' => $log['sid'],
                'tracking_num' => $log['pre_batch_num'],
                'app_type' => 1,
                'app_time' => date('YmdHis',time()),
                'app_status' => 2,
                'ebc_code' => $log['ebcCode'],
                'ebp_code' => $log['ebpCode'],
                'ebp_name' => $log['ebpName'],
                'ebc_name' => $log['ebcName'],
                'pay_code' => $log['payCode'],
                'pay_name' => $log['payName'],
                'self_ordersn' => $log['pre_batch_num'],
                'is_predeclare' => 1,
                'create_at' => time()
            ]);
            if (!empty($HeadId)) {
                //3、生成用户
                $user = Db::name('sz_yi_member')->where('openid',$log['openid'])->find();
                if(empty($user['id'])){
                    $insert_id = Db::name('sz_yi_member')->insertGetId(['openid'=>$log['openid'],'uniacid'=>3,'realname'=>$log['nickname'],'pwd'=>md5('888888'),'createtime'=>time(),'nickname'=>$log['nickname']]);
                    $user['id'] = $insert_id;
                }

                //4-1、1个运单等于1个订单,查找所有订(运)单笔数并去重
                $batch_total_num = Db::name('customs_pre_declare')
                    ->alias('a')
                    ->join('customs_goods_pre_log b','b.pre_batch_num=a.pre_batch_num','right')
                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                    ->distinct(true)
                    ->field('b.logisticsNo')
                    ->select();

                //4-2、生成批次
                $batch_num = $this->piciName( $log['pre_batch_num'] )['pici'];
                $batch_id = Db::name('customs_export_billoflading_batch')->insertGetId([
                    'uid' => $log['uid'],
                    'tracking_num' => $log['pre_batch_num'],
                    'batch_num' => $batch_num,
                    'type' => "CEB403Message",
                    'batch_status' => 3,
                    'total_num' => count($batch_total_num),
                    'success_num' => 0,
                    'error_num' => 0,
                    'create_at' => time()
                ]);

                foreach($batch_total_num as $k=>$v){
                    //5、获取电子订单的订单编号
                    $order_info = Db::name('customs_export_order_list')->where('batch_num',$batch_num)->where('logistics_no',$v['logisticsNo'])->where('uid',$log['uid'])->field(['ordersn','order_price'])->find();

                    //6、生成支付清单列表
                    $list = array();
                    $list['uid'] = $log['uid'];
                    $list['hid'] = $HeadId;
                    $list['batch_num'] = $batch_num;
                    $list['guid'] = $this->getGuidOnlyValue();
                    $list['ordersn'] = $order_info['ordersn'];
                    $list['pay_no'] = '';
                    $list['receiving_amount'] = $order_info['order_price'];
                    $list['currency'] = 142;
                    $list['accounting_date'] = date('YmdHis',strtotime($log['ie_date'])-604800);//到账时间
                    $list['note'] = 'test';
                    $list['status'] = 0;
                    $list['action_time'] = date('Y-m-d H:i:s',time());
                    $list['action_message'] = '待申报【'.$list['guid'].'】';
                    $list['create_at'] = time();

                    $list_id = Db::name('customs_export_payement_slip_list')->insertGetId($list);
                }
                return json(['msg'=>'关联成功','status'=>1]);
            }else{
                return json(['msg'=>'关联失败','status'=>1]);
            }
        }else{
            return view('predeclare/declare/payment_connect',compact('list'));
        }
    }

    //收款单-申报列表
    public function payment_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $tracking_num = $dat['tracking_num'];
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $user = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b','b.openid=a.openid','left')
                ->where('a.pre_batch_num',$tracking_num)
                ->field(['b.nickname','b.fanid'])
                ->find();
            $where =['uid'=>$user['fanid'],'type'=>'CEB403Message','tracking_num'=>$tracking_num];

            $count = Db::name('customs_export_billoflading_batch')->where($where)->count();
            $rows = Db::name('customs_export_billoflading_batch')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();
            foreach ($rows as &$item) {
                $item['ordersn'] = Db::name('customs_export_payement_slip_list')->where(array('batch_num'=>$item['batch_num']))->value('ordersn');
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['batch_status'] = $this->getBatchStatus($item['batch_status']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $tracking_num = $dat['tracking_num'];
            return view('predeclare/declare/ecommerce_list',compact('tracking_num'));
        }
    }

    //物流运单
    public function logistics(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $where =['is_predeclare'=>1];

            $count = Db::name('customs_export_logistics_waybill_head')->where($where)
                ->count();
            $rows = Db::name('customs_export_logistics_waybill_head')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['app_type'] = '<span style="color:#1E9FFF;">新增</span>';
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);

        }else{
            return view('predeclare/declare/logistics');
        }
    }

    //物流运单-关联预申报编号
    public function logistics_connect(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            //1、查询该预报编号有无配置口岸信息
            $log = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b',' b.openid=a.openid','left')
                ->where('a.pre_batch_num',trim($dat['pre_batch_num']))
                ->field(['a.*','b.fanid as uid','b.nickname'])
                ->find();
            if(empty($log['sid'])){
                return json(['status'=>0,'msg'=>'请先配置用户的口岸信息']);
            }

            //2、新增物流运单报头
            $HeadId = Db::name('customs_export_logistics_waybill_head')->insertGetId([
                'uid' => $log['uid'],
                'sid' => $log['sid'],
                'tracking_num' => $log['pre_batch_num'],
                'app_type' => 1,
                'app_time' => date('YmdHis',time()),
                'app_status' => 2,
                'logistics_code' => $log['logisticsCode'],
                'logistics_name' => $log['logisticsName'],
                'ebc_code' => $log['ebcCode'],
                'ebc_name' => $log['ebcName'],
                'ebc_tele_phone' => $log['ebc_tele_phone'],//电商企业电话
                'self_ordersn' => $log['pre_batch_num'],
                'rerecorded_ordersn' => '',
                'is_predeclare' => 1,
                'create_at' => time()
            ]);
            if(!empty($HeadId)){
                //3、生成用户
                $user = Db::name('sz_yi_member')->where('openid',$log['openid'])->find();
                if(empty($user['id'])){
                    $insert_id = Db::name('sz_yi_member')->insertGetId(['openid'=>$log['openid'],'uniacid'=>3,'realname'=>$log['nickname'],'pwd'=>md5('888888'),'createtime'=>time(),'nickname'=>$log['nickname']]);
                    $user['id'] = $insert_id;
                }

                //4-1、1个运单等于1个订单,查找所有订(运)单笔数并去重
                $batch_total_num = Db::name('customs_pre_declare')
                    ->alias('a')
                    ->join('customs_goods_pre_log b','b.pre_batch_num=a.pre_batch_num','right')
                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                    ->distinct(true)
                    ->field('b.logisticsNo')
                    ->select();

                //4-2、生成批次
                $batch_num = $this->piciName( $log['pre_batch_num'] )['pici'];
                $batch_id = Db::name('customs_export_billoflading_batch')->insertGetId([
                    'uid' => $log['uid'],
                    'tracking_num' => $log['pre_batch_num'],
                    'batch_num' => $batch_num,
                    'type' => "CEB505Message",
                    'batch_status' => 3,
                    'total_num' => count($batch_total_num),
                    'success_num' => 0,
                    'error_num' => 0,
                    'create_at' => time()
                ]);
                
                //先查询风险表关联哪个
                $connect_glist = Db::name('customs_declare_grisk_head')->where('pre_batch_num',$log['pre_batch_num'])->field(['connect_glist'])->find();
                foreach($batch_total_num as $k=>$v){
                    //5、获取该预报编号下的商品信息
                    if($connect_glist['connect_glist']==4){
                        //归类
                        $goods_info = Db::name('customs_declare_risk_list')->where('pre_batch_num',$log['pre_batch_num'])->where('status',0)->where('logisticsNo',$v['logisticsNo'])->field(['connect_glist','unit','unit1','gcode','itemName','update_itemName','itemNo'])->select();
                        $glist2 = $goods_info[0]['connect_glist'];
                        if($glist2==3){
                            //调值
                            foreach($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_adjust_log')
                                        ->alias('a')
                                        ->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')
                                        ->where('a.pre_batch_num',$log['pre_batch_num'])
                                        ->where('b.itemNo',$vv['itemNo'])
                                        ->where('b.logisticsNo',$v['logisticsNo'])
                                        ->field(['a.price','a.totalPrice','a.charge','b.goodsInfo','b.currency','b.qty','b.freight','b.insuredFee','b.packNo','b.grossWeight'])
                                        ->find();
                                $goods_info[$kk]['price'] = $info2['price'];
                                $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                $goods_info[$kk]['charge'] = $info2['charge'];
                                $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                $goods_info[$kk]['currency'] = $info2['currency'];
                                $goods_info[$kk]['qty'] = $info2['qty'];
                                $goods_info[$kk]['freight'] = $info2['freight'];
                                $goods_info[$kk]['insuredFee'] = $info2['insuredFee'];
                                $goods_info[$kk]['packNo'] = $info2['packNo'];
                                $goods_info[$kk]['grossWeight'] = $info2['grossWeight'];
                            }
                        }elseif($glist2==2){
                            //补缺
                            foreach ($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_fill_log')->where('pre_batch_num',$log['pre_batch_num'])->where('logisticsNo',$v['logisticsNo'])->where('itemNo',$vv['itemNo'])->field(['price','totalPrice','charge','goodsInfo','currency','qty','freight','insuredFee','packNo','grossWeight'])->find();
                                $goods_info[$kk]['price'] = $info2['price'];
                                $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                $goods_info[$kk]['charge'] = $info2['charge'];
                                $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                $goods_info[$kk]['currency'] = $info2['currency'];
                                $goods_info[$kk]['qty'] = $info2['qty'];
                                $goods_info[$kk]['freight'] = $info2['freight'];
                                $goods_info[$kk]['insuredFee'] = $info2['insuredFee'];
                                $goods_info[$kk]['packNo'] = $info2['packNo'];
                                $goods_info[$kk]['grossWeight'] = $info2['grossWeight'];
                            }
                        }elseif($glist2==1){
                            //原始
                            foreach ($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_log')->where('pre_batch_num',$log['pre_batch_num'])->where('logisticsNo',$v['logisticsNo'])->where('itemNo',$vv['itemNo'])->field(['price','totalPrice','charge','goodsInfo','currency','qty','freight','insuredFee','packNo','grossWeight'])->find();
                                $goods_info[$kk]['price'] = $info2['price'];
                                $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                $goods_info[$kk]['charge'] = $info2['charge'];
                                $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                $goods_info[$kk]['currency'] = $info2['currency'];
                                $goods_info[$kk]['qty'] = $info2['qty'];
                                $goods_info[$kk]['freight'] = $info2['freight'];
                                $goods_info[$kk]['insuredFee'] = $info2['insuredFee'];
                                $goods_info[$kk]['packNo'] = $info2['packNo'];
                                $goods_info[$kk]['grossWeight'] = $info2['grossWeight'];
                            }
                        }
                    }
                    elseif($connect_glist['connect_glist']==3){
                        //调值
                        $goods_info = Db::name('customs_goods_pre_adjust_log')
                                    ->alias('a')
                                    ->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')
                                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                                    ->where('a.logisticsNo',$v['logisticsNo'])
                                    ->where('a.status',0)
                                    ->field(['a.price','a.totalPrice','a.charge','b.itemNo','b.itemName','b.update_itemName','b.goodsInfo','b.unit','b.currency','b.qty','b.freight','b.insuredFee','b.packNo','b.grossWeight'])
                                    ->select();
                    }
                    elseif($connect_glist['connect_glist']==2){
                        //补缺
                        $goods_info = Db::name('customs_goods_pre_fill_log')
                                    ->where('pre_batch_num',$log['pre_batch_num'])
                                    ->where('logisticsNo',$v['logisticsNo'])
                                    ->where('status',0)
                                    ->select();
                    }
                    elseif($connect_glist['connect_glist']==1){
                        //原始
                        $goods_info = Db::name('customs_goods_pre_log')
                                    ->where('pre_batch_num',$log['pre_batch_num'])
                                    ->where('logisticsNo',$v['logisticsNo'])
                                    ->where('status',0)
                                    ->select();
                    }
                    
                    $freight = 0;
                    $insured_fee = 0;
                    $gross_weight = 0;
                    $pack_no = 0;
                    $goodsInfo = '';
                    $currency = 142;
                    foreach($goods_info as $k2=>$v2){
                        $freight += $v2['freight'];
                        $insured_fee += $v2['insuredFee'];
                        $gross_weight += $v2['grossWeight'];
                        $pack_no += $v2['packNo'];
                        $goodsInfo .= $v2['goodsInfo'].',';
                        $currency = $v2['currency'];//订单币种
                    }

                    //6、生成物流清单列表
                    $list = array();
                    $list['uid'] = $log['uid'];
                    $list['hid'] = $HeadId;
                    $list['batch_num'] = $batch_num;
                    $list['guid'] = $this->getGuidOnlyValue();
                    $list['logistics_no'] = $v['logisticsNo'];
                    $list['freight'] = $freight;
                    $list['insured_fee'] = $insured_fee;
                    $list['currency'] = $currency;
                    $list['gross_weight'] = $gross_weight;
                    $list['pack_no'] = $pack_no;
                    $list['goods_info'] = rtrim($goodsInfo,',');
                    $list['status'] = 0;
                    $list['action_time'] = date('Y-m-d H:i:s',time());
                    $list['action_message'] = '待申报【'.$list['guid'].'】';
                    $list['note'] = 'test';
                    $list['create_at'] = time();

                    $list_id = Db::name('customs_export_logistics_waybill_list')->insertGetId($list);
                }
                return json(['msg'=>'关联成功','status'=>1]);
            }else{
                return json(['msg'=>'关联失败','status'=>1]);
            }
        }else{
            return view('predeclare/declare/logistics_connect');
        }
    }

    //物流运单-申报列表
    public function logistics_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $tracking_num = $dat['tracking_num'];
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $user = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b','b.openid=a.openid','left')
                ->where('a.pre_batch_num',$tracking_num)
                ->field(['b.nickname','b.fanid'])
                ->find();
            $where =['uid'=>$user['fanid'],'type'=>'CEB505Message','tracking_num'=>$tracking_num];

            $count = Db::name('customs_export_billoflading_batch')->where($where)->count();
            $rows = Db::name('customs_export_billoflading_batch')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['batch_status'] = $this->getBatchStatus($item['batch_status']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $tracking_num = $dat['tracking_num'];
            return view('predeclare/declare/logistics_list',compact('tracking_num'));
        }
    }

    //申报清单
    public function declares(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $where =['is_predeclare'=>1];

            $count = Db::name('customs_export_declarationlist_head')->where($where)
                ->count();
            $rows = Db::name('customs_export_declarationlist_head')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['statistics_flag']="简化申报";
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['app_type'] = '<span style="color:#1E9FFF;">新增</span>';
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('predeclare/declare/declares');
        }
    }

    //生成企业唯一编号
    private function createGogoOrderSn()
    {
        $millisecond = round(explode(" ", microtime())[0] * 1000);
        $ordersn = 'GG' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);
        return $ordersn;
    }

    private function getHeadDataInvtno($tracking_num)
    {
        $invt_no = Db::name('customs_export_declarationlist_head')->where('tracking_num', $tracking_num)->field(['invt_no'])->find();
        return $invt_no['invt_no'];
    }

    public function getNineCode($length)
    {
        $str='0123456789';
        $len=strlen($str)-1;
        $randstr='';
        for($i=0;$i<$length;$i++){
            $num=mt_rand(0,$len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }

    //申报清单-关联预申报编号
    public function declares_connect(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            //1、查询该预报编号有无配置口岸信息
            $log = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b', ' b.openid=a.openid', 'left')
                ->where('a.pre_batch_num', trim($dat['pre_batch_num']))
                ->field(['a.*', 'b.fanid as uid', 'b.nickname'])
                ->find();
            if (empty($log['sid'])) {
                return json(['status' => 0, 'msg' => '请先配置用户的口岸信息']);
            }

            //2、新增清单申报报头
            $HeadId = Db::name('customs_export_declarationlist_head')->insertGetId([
                'uid' => $log['uid'],
                'sid' => $log['sid'],
                'tracking_num' => $log['pre_batch_num'],
                'app_type' => 1,
                'app_time' => date('YmdHis',time()),
                'app_status' => 2,
                'customs_code' => explode(':',$log['customs_code'])[0],
                'ebp_code' => $log['ebpCode'],
                'ebp_name' => $log['ebpName'],
                'logistics_code' => $log['logisticsCode'],
                'logistics_name' => $log['logisticsName'],
                'ie_flag' => 'E',
                'port_code' => explode(':',$log['port_code'])[0],
                'ie_date' => date('Ymd',strtotime($log['ie_date'])),
                'statistics_flag' => 'A',//A-简化申报;B-汇总申报；若采用A简化申报，则要求商品不涉许可证、不涉出口关税、不涉及出口退税。同时商品编码前4位为税则表填写
                'statistics_type' => 'date',
                'agent_code' => $log['ebcCode'],//申报企业
                'agent_name' => $log['ebcName'],//申报企业
                'ebc_code' => $log['ebcCode'],
                'ebc_name' => $log['ebcName'],
                'owner_code' => $log['ebcCode'],//生产企业
                'owner_name' => $log['ebcName'],//生产企业
                'trade_mode' => '9610',//贸易方式
                'voyage_no' => str_replace(',','',$log['voyage_no']),
                'traf_mode' => $log['transport_code'],
                'country' => $log['country_code'],//运抵国
                'pod' => $log['pod'],//港口
                'wrap_type' => 2,//包装种类代码
//                'invt_no' => explode(':',$log['customs_code'])[0].date('Y').'E'.$this->getNineCode(9),
                'invt_no' => '',
                'iac_code' => $log['iac_code'],
                'iac_name' => $log['iac_name'],
                'ems_no' => $log['ems_no'],
                'traf_name' => $log['traf_name'],
                'total_package_no' => '',//总包号
                'loct_no' => $log['loctNo'],
                'license_no' => '',//许可证号
                'self_ordersn' => $log['pre_batch_num'],
                'is_predeclare' => 1,
                'create_at' => time()
            ]);
            if(!empty($HeadId)){
                //3、生成用户
                $user = Db::name('sz_yi_member')->where('openid',$log['openid'])->find();
                if(empty($user['id'])){
                    $insert_id = Db::name('sz_yi_member')->insertGetId(['openid'=>$log['openid'],'uniacid'=>3,'realname'=>$log['nickname'],'pwd'=>md5('888888'),'createtime'=>time(),'nickname'=>$log['nickname']]);
                    $user['id'] = $insert_id;
                }

                //4-1、1个运单等于1个订单,查找所有订(运)单笔数并去重
                $batch_total_num = Db::name('customs_pre_declare')
                    ->alias('a')
                    ->join('customs_goods_pre_log b','b.pre_batch_num=a.pre_batch_num','right')
                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                    ->distinct(true)
                    ->field('b.logisticsNo')
                    ->select();

                //4-2、生成批次
                $batch_num = $this->piciName( $log['pre_batch_num'] )['pici'];
                $batch_id = Db::name('customs_export_billoflading_batch')->insertGetId([
                    'uid' => $log['uid'],
                    'tracking_num' => $log['pre_batch_num'],
                    'batch_num' => $batch_num,
                    'type' => "CEB603Message",
                    'batch_status' => 3,
                    'total_num' => count($batch_total_num),
                    'success_num' => 0,
                    'error_num' => 0,
                    'create_at' => time()
                ]);

                //先查询风险表关联哪个
                $connect_glist = Db::name('customs_declare_grisk_head')->where('pre_batch_num',$log['pre_batch_num'])->field(['connect_glist'])->find();
                foreach($batch_total_num as $k=>$v){
                    //5、获取该物流运单下的电子订单信息
                    $ordersn='';
                    $order_info = Db::name('customs_export_order_list')->where('batch_num',$batch_num)->where('uid',$log['uid'])->where('logistics_no',$v['logisticsNo'])->find();
                    $ordersn = $order_info['ordersn'];
                    $freight = $order_info['freight'];

                    //5-1、获取该预申报下的商品保费
                    // $goods_info = Db::name('customs_goods_pre_log')->where('pre_batch_num',$log['pre_batch_num'])->where('logisticsNo',$v['logisticsNo'])->select();
                    if($connect_glist['connect_glist']==4){
                        //归类
                        $goods_info = Db::name('customs_declare_risk_list')->where('pre_batch_num',$log['pre_batch_num'])->where('status',0)->where('logisticsNo',$v['logisticsNo'])->field(['connect_glist','unit','unit1','gcode','itemName','update_itemName','itemNo'])->select();
                        $glist2 = $goods_info[0]['connect_glist'];
                        if($glist2==3){
                            //调值
                            foreach($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_adjust_log')
                                        ->alias('a')
                                        ->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')
                                        ->where('a.pre_batch_num',$log['pre_batch_num'])
                                        ->where('a.logisticsNo',$v['logisticsNo'])
                                        ->where('b.itemNo',$vv['itemNo'])
                                        ->field(['a.price','a.totalPrice','a.charge','b.goodsInfo','b.currency','b.qty','b.freight','b.insuredFee','b.qty1','b.grossWeight','netWeight','packNo','gmodel'])
                                        ->find();
                                $goods_info[$kk]['price'] = $info2['price'];
                                $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                $goods_info[$kk]['charge'] = $info2['charge'];
                                $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                $goods_info[$kk]['currency'] = $info2['currency'];
                                $goods_info[$kk]['qty'] = $info2['qty'];
                                $goods_info[$kk]['freight'] = $info2['freight'];
                                $goods_info[$kk]['insuredFee'] = $info2['insuredFee'];
                                $goods_info[$kk]['qty1'] = $info2['qty1'];
                                $goods_info[$kk]['grossWeight'] = $info2['grossWeight'];
                                $goods_info[$kk]['netWeight'] = $info2['netWeight'];
                                $goods_info[$kk]['packNo'] = $info2['packNo'];
                                $goods_info[$kk]['gmodel'] = $info2['gmodel'];
                            }
                        }
                        elseif($glist2==2){
                            //补缺
                            foreach ($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_fill_log')->where('pre_batch_num',$log['pre_batch_num'])->where('logisticsNo',$v['logisticsNo'])->where('itemNo',$vv['itemNo'])->field(['price','totalPrice','charge','goodsInfo','currency','qty','freight','insuredFee','qty1','grossWeight','netWeight','packNo','gmodel'])->find();
                                $goods_info[$kk]['price'] = $info2['price'];
                                $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                $goods_info[$kk]['charge'] = $info2['charge'];
                                $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                $goods_info[$kk]['currency'] = $info2['currency'];
                                $goods_info[$kk]['qty'] = $info2['qty'];
                                $goods_info[$kk]['freight'] = $info2['freight'];
                                $goods_info[$kk]['insuredFee'] = $info2['insuredFee'];
                                $goods_info[$kk]['qty1'] = $info2['qty1'];
                                $goods_info[$kk]['grossWeight'] = $info2['grossWeight'];
                                $goods_info[$kk]['netWeight'] = $info2['netWeight'];
                                $goods_info[$kk]['packNo'] = $info2['packNo'];
                                $goods_info[$kk]['gmodel'] = $info2['gmodel'];
                            }
                        }
                        elseif($glist2==1){
                            //原始
                            foreach ($goods_info as $kk=>$vv){
                                $info2 = Db::name('customs_goods_pre_log')->where('pre_batch_num',$log['pre_batch_num'])->where('logisticsNo',$v['logisticsNo'])->where('itemNo',$vv['itemNo'])->field(['price','totalPrice','charge','goodsInfo','currency','qty','freight','insuredFee','qty1','grossWeight','netWeight','packNo','gmodel'])->find();
                                $goods_info[$kk]['price'] = $info2['price'];
                                $goods_info[$kk]['totalPrice'] = $info2['totalPrice'];
                                $goods_info[$kk]['charge'] = $info2['charge'];
                                $goods_info[$kk]['goodsInfo'] = $info2['goodsInfo'];
                                $goods_info[$kk]['currency'] = $info2['currency'];
                                $goods_info[$kk]['qty'] = $info2['qty'];
                                $goods_info[$kk]['freight'] = $info2['freight'];
                                $goods_info[$kk]['insuredFee'] = $info2['insuredFee'];
                                $goods_info[$kk]['qty1'] = $info2['qty1'];
                                $goods_info[$kk]['grossWeight'] = $info2['grossWeight'];
                                $goods_info[$kk]['netWeight'] = $info2['netWeight'];
                                $goods_info[$kk]['packNo'] = $info2['packNo'];
                                $goods_info[$kk]['gmodel'] = $info2['gmodel'];
                            }
                        }
                    }
                    elseif($connect_glist['connect_glist']==3){
                        //调值
                        $goods_info = Db::name('customs_goods_pre_adjust_log')
                                    ->alias('a')
                                    ->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')
                                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                                    ->where('a.logisticsNo',$v['logisticsNo'])
                                    ->where('a.status',0)
                                    ->field(['a.price','a.totalPrice','a.charge','b.itemNo','b.itemName','b.update_itemName','b.goodsInfo','b.unit','b.currency','b.qty','b.freight','b,insuredFee','b.unit1','b.qty1','b.grossWeight','b.netWeight','b.packNo','b.gcode','b.gmodel'])
                                    ->select();
                    }
                    elseif($connect_glist['connect_glist']==2){
                        //补缺
                        $goods_info = Db::name('customs_goods_pre_fill_log')
                                    ->where('pre_batch_num',$log['pre_batch_num'])
                                    ->where('logisticsNo',$v['logisticsNo'])
                                    ->where('status',0)
                                    ->select();
                    }
                    elseif($connect_glist['connect_glist']==1){
                        //原始
                        $goods_info = Db::name('customs_goods_pre_log')
                                    ->where('pre_batch_num',$log['pre_batch_num'])
                                    ->where('logisticsNo',$v['logisticsNo'])
                                    ->where('status',0)
                                    ->select();
                    }
                    $insured_fee = 0;
                    $pack_no = 0;
                    $gross_weight=0;
                    $net_weight=0;//净重
                    $currency = '';
                    foreach($goods_info as $k2=>$v2){
                        $insured_fee += $v2['insuredFee'];
                        $pack_no += $v2['packNo'];
                        $gross_weight += $v2['grossWeight'];
                        $net_weight += $v2['netWeight'];
                        $currency=$v2['currency'];
                    }

                    //6、生成清单列表信息
                    $list = array();
                    $list['uid'] = $log['uid'];
                    $list['hid'] = $HeadId;
                    $list['batch_num'] = $batch_num;
                    $list['guid'] = $this->getGuidOnlyValue();
                    $list['ordersn'] = $ordersn;
                    $list['logistics_no'] = $v['logisticsNo'];
                    $list['cop_no'] = $this->createGogoOrderSn();
                    $list['pre_no'] = '';
                    $list['invt_no'] = '';
//                    $list['invt_no'] = $this->getHeadDataInvtno($batch_num);//海关清单编号
                    $list['freight'] = $freight;
                    $list['fcurrency'] = $currency;
                    $list['fflag'] = 3;
                    $list['insured_fee'] = $insured_fee;
                    $list['icurrency'] = $currency;
                    $list['iflag'] = 3;
                    $list['pack_no'] = $pack_no;
                    $list['gross_weight'] = $gross_weight;
                    $list['net_weight'] = $net_weight;
                    $list['status'] = 0;
                    $list['action_time'] = date('Y-m-d H:i:s',time());
                    $list['action_message'] = '待申报【'.$list['guid'].'】';
                    $list['create_at'] = time();

                    $order_id = Db::name('customs_export_declarationlist_list')->insertGetId($list);

                    //7、生成清单列表商品信息
                    foreach($goods_info as $k2=>$v2) {
                        $itemName = $v2['itemName'];
                        if(!empty($v2['update_itemName'])){
                            $itemName = $v2['update_itemName'];
                        }
                        $goods = array();
                        $goods['oid'] = $order_id;
                        $goods['gnum'] = $k2 * 1 + 1;
                        $goods['item_no'] = $v2['itemNo'];
                        $goods['item_record_no'] = $log['ems_no'];//账册备案编号
                        $goods['item_name'] = $itemName;
                        $goods['gcode'] = $v2['gcode'];//商品海关编码 *
                        $goods['gname'] = $itemName;
                        $goods['gmodel'] = $v2['gmodel'];//规格型号 *
                        $goods['bar_code'] = '无';
                        $goods['country'] = $log['country_code'];//目的国
                        $goods['currency'] = $v2['currency'];
                        $goods['qty'] = $v2['qty'];
                        $goods['qty1'] = $v2['qty1'];//法定数量 *
                        $goods['qty2'] = "";
                        $goods['unit'] = $v2['unit'];
                        $goods['unit1'] = $v2['unit1'];//法定计量单位 *
                        $goods['unit2'] = "";
                        $goods['price'] = $v2['price'];
                        $goods['total_price'] = $v2['totalPrice'];

                        Db::name('customs_export_declarationlist_goods')->insert($goods);
                    }
                }
                return json(['msg'=>'关联成功','status'=>1]);
            }else{
                return json(['msg'=>'关联失败','status'=>1]);
            }
        }else{
            return view('predeclare/declare/declares_connect');
        }
    }

    //申报清单-申报列表
    public function declares_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $tracking_num = $dat['tracking_num'];
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $user = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b','b.openid=a.openid','left')
                ->where('a.pre_batch_num',$tracking_num)
                ->field(['b.nickname','b.fanid'])
                ->find();
            $where =['uid'=>$user['fanid'],'type'=>'CEB603Message','tracking_num'=>$tracking_num];

            $count = Db::name('customs_export_billoflading_batch')->where($where)->count();
            $rows = Db::name('customs_export_billoflading_batch')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['batch_status'] = $this->getBatchStatus($item['batch_status']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $tracking_num = $dat['tracking_num'];
            return view('predeclare/declare/declares_list',compact('tracking_num'));
        }
    }

    //清单总分单
    public function logistics_e(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $where =['is_predeclare'=>1];

            $count = Db::name('customs_export_inventory_totalscore_head')->where($where)
                ->count();
            $rows = Db::name('customs_export_inventory_totalscore_head')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['app_type'] = '<span style="color:#1E9FFF;">新增</span>';
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('predeclare/declare/logistics_e');
        }
    }

    //清单总分单-关联预申报
    public function logistics_e_connect(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            //1、查询该预报编号有无配置口岸信息
            $log = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b', ' b.openid=a.openid', 'left')
                ->where('a.pre_batch_num', trim($dat['pre_batch_num']))
                ->field(['a.*', 'b.fanid as uid', 'b.nickname'])
                ->find();
            if (empty($log['sid'])) {
                return json(['status' => 0, 'msg' => '请先配置用户的口岸信息']);
            }

            //查找该预申报下的毛重（累加）
            $gross_weight = Db::name('customs_goods_pre_log')->where('pre_batch_num',$log['pre_batch_num'])->sum('grossWeight');

            //2、生成清单总分单报文头
            $HeadId = Db::name('customs_export_inventory_totalscore_head')->insertGetId([
                'uid' => $log['uid'],
                'sid' => $log['sid'],
                'tracking_num' => $log['pre_batch_num'],
                'app_type' => 1,
                'app_time' => date('YmdHis',time()),
                'app_status' => 2,
                'customs_code' => explode(':',$log['customs_code'])[0],
                'agent_code' => $log['ebcCode'],
                'agent_name' => $log['ebcName'],
                'traf_mode' => $log['transport_code'],
                'traf_name' => $log['traf_name'],
                'voyage_no' => str_replace(',','',$log['voyage_no']),
                'gross_weight' => $gross_weight,
                'logistics_code' => $log['logisticsCode'],
                'logistics_name' => $log['logisticsName'],
                'self_ordersn' => $log['pre_batch_num'],
                'loct_no' => $log['loctNo'],
                'domestic_traf_no' => '',//境内运输工具编号
                'is_predeclare' => 1,
                'create_at' => time()
            ]);

            if(!empty($HeadId)){
                //3、生成用户
                $user = Db::name('sz_yi_member')->where('openid',$log['openid'])->find();
                if(empty($user['id'])){
                    $insert_id = Db::name('sz_yi_member')->insertGetId(['openid'=>$log['openid'],'uniacid'=>3,'realname'=>$log['nickname'],'pwd'=>md5('888888'),'createtime'=>time(),'nickname'=>$log['nickname']]);
                    $user['id'] = $insert_id;
                }

                //4-1、1个运单等于1个订单,查找所有订(运)单笔数并去重
                $batch_total_num = Db::name('customs_pre_declare')
                    ->alias('a')
                    ->join('customs_goods_pre_log b','b.pre_batch_num=a.pre_batch_num','right')
                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                    ->distinct(true)
                    ->field('b.logisticsNo')
                    ->select();

                //4-2、生成批次
                $batch_num = $this->piciName( $log['pre_batch_num'] )['pici'];
                $batch_id = Db::name('customs_export_billoflading_batch')->insertGetId([
                    'uid' => $log['uid'],
                    'tracking_num' => $log['pre_batch_num'],
                    'batch_num' => $batch_num,
                    'type' => "CEB607Message",
                    'batch_status' => 3,
                    'total_num' => count($batch_total_num),
                    'success_num' => 0,
                    'error_num' => 0,
                    'create_at' => time()
                ]);

                foreach($batch_total_num as $k=>$v){
                    //5、生成总分单数据头
                    $list = array();
                    $list['uid'] = $log['uid'];
                    $list['hid'] = $HeadId;
                    $list['batch_num'] = $batch_num;
                    $list['guid'] = $this->getGuidOnlyValue();
                    $list['cop_no'] = $this->createGogoOrderSn();
                    $list['pre_no'] = '';
                    $list['logistics_no'] = $v['logisticsNo'];
                    $list['invt_no'] = '';//出口清单编号
                    $list['total_package_no'] = '';//总包号
                    $list['status'] = 0;
                    $list['action_time'] = date('Y-m-d H:i:s',time());
                    $list['action_message'] = '待申报【'.$list['guid'].'】';
                    $list['create_at'] = time();

                    $list_id = Db::name('customs_export_inventory_totalscore')->insertGetId($list);
                }

                return json(['msg'=>'关联成功','status'=>1]);
            }else{
                return json(['msg'=>'关联失败','status'=>1]);
            }

        }else{
            return view('predeclare/declare/logistics_e_connect');
        }
    }

    //清单总分单-申报列表
    public function logistics_e_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $tracking_num = $dat['tracking_num'];
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $user = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b','b.openid=a.openid','left')
                ->where('a.pre_batch_num',$tracking_num)
                ->field(['b.nickname','b.fanid'])
                ->find();
            $where =['uid'=>$user['fanid'],'type'=>'CEB607Message','tracking_num'=>$tracking_num];

            $count = Db::name('customs_export_billoflading_batch')->where($where)->count();
            $rows = Db::name('customs_export_billoflading_batch')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['batch_status'] = $this->getBatchStatus($item['batch_status']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $tracking_num = $dat['tracking_num'];
            return view('predeclare/declare/logistics_e_list',compact('tracking_num'));
        }
    }

    //物流离境单
    public function logistics_s(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $where =['is_predeclare'=>1];

            $count = Db::name('customs_export_departureticket_head')->where($where)
                ->count();
            $rows = Db::name('customs_export_departureticket_head')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('predeclare/declare/losigtics_s');
        }
    }

    //物流离境单-关联预申报
    public function logistics_s_connect(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            //1、查询该预报编号有无配置口岸信息
            $log = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b', ' b.openid=a.openid', 'left')
                ->where('a.pre_batch_num', trim($dat['pre_batch_num']))
                ->field(['a.*', 'b.fanid as uid', 'b.nickname'])
                ->find();
            if (empty($log['sid'])) {
                return json(['status' => 0, 'msg' => '请先配置用户的口岸信息']);
            }

            //2、生成离境单报文头
            $HeadId = Db::name('customs_export_departureticket_head')->insert([
                'uid' => $log['uid'],
                'sid' => $log['sid'],
                'tracking_num' => $log['pre_batch_num'],
                'app_type' => 1,
                'app_time' => date('YmdHis',time()),
                'app_status' => 2,
                'customs_code' => explode(':',$log['customs_code'])[0],
                'logistics_code' => $log['logisticsCode'],
                'logistics_name' => $log['logisticsName'],
                'traf_mode' => $log['transport_code'],
                'traf_name' => $log['traf_name'],
                'voyage_no' => str_replace(',','',$log['voyage_no']),
                'leave_time' => date('YmdHis',strtotime($log['ie_date'])),//离境时间 *
                'self_ordersn' => $log['pre_batch_num'],
                'rerecorded_ordersn' => '',
                'is_predeclare' => 1,
                'create_at' => time()
            ]);
            if(!empty($HeadId)){
                //3、生成用户
                $user = Db::name('sz_yi_member')->where('openid',$log['openid'])->find();
                if(empty($user['id'])){
                    $insert_id = Db::name('sz_yi_member')->insertGetId(['openid'=>$log['openid'],'uniacid'=>3,'realname'=>$log['nickname'],'pwd'=>md5('888888'),'createtime'=>time(),'nickname'=>$log['nickname']]);
                    $user['id'] = $insert_id;
                }

                //4-1、1个运单等于1个订单,查找所有订(运)单笔数并去重
                $batch_total_num = Db::name('customs_pre_declare')
                    ->alias('a')
                    ->join('customs_goods_pre_log b','b.pre_batch_num=a.pre_batch_num','right')
                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                    ->distinct(true)
                    ->field('b.logisticsNo')
                    ->select();

                //4-2、生成批次
                $batch_num = $this->piciName( $log['pre_batch_num'] )['pici'];
                $batch_id = Db::name('customs_export_billoflading_batch')->insertGetId([
                    'uid' => $log['uid'],
                    'tracking_num' => $log['pre_batch_num'],
                    'batch_num' => $batch_num,
                    'type' => "CEB509Message",
                    'batch_status' => 3,
                    'total_num' => count($batch_total_num),
                    'success_num' => 0,
                    'error_num' => 0,
                    'create_at' => time()
                ]);

                foreach($batch_total_num as $k=>$v){
                    $list = array();
                    $list['uid'] = $log['uid'];
                    $list['hid'] = $HeadId;
                    $list['batch_num'] = $batch_num;
                    $list['guid'] = $this->getGuidOnlyValue();
                    $list['cop_no'] = $this->createGogoOrderSn();//企业唯一编号
                    $list['pre_no'] = '';
                    $list['logistics_no'] = $v['logisticsNo'];
                    $list['status'] = 0;
                    $list['action_time'] = date('Y-m-d H:i:s',time());
                    $list['action_message'] = '待申报【'.$list['guid'].'】';
                    $list['create_at'] = time();

                    $list_id = Db::name('customs_export_departureticket')->insertGetId($list);
                }
                return json(['msg'=>'关联成功','status'=>1]);
            }else{
                return json(['msg'=>'关联失败','status'=>1]);
            }
        }else{
            return view('predeclare/declare/logistics_s_connect');
        }
    }

    //物流离境单-申报列表
    public function logistics_s_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $tracking_num = $dat['tracking_num'];
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $user = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b','b.openid=a.openid','left')
                ->where('a.pre_batch_num',$tracking_num)
                ->field(['b.nickname','b.fanid'])
                ->find();
            $where =['uid'=>$user['fanid'],'type'=>'CEB509Message','tracking_num'=>$tracking_num];

            $count = Db::name('customs_export_billoflading_batch')->where($where)->count();
            $rows = Db::name('customs_export_billoflading_batch')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['batch_status'] = $this->getBatchStatus($item['batch_status']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $tracking_num = $dat['tracking_num'];
            return view('predeclare/declare/logistics_s_list',compact('tracking_num'));
        }
    }

    //汇总申请单
    public function declare_t(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $where =['is_predeclare'=>1];

            $count = Db::name('customs_export_collect_apply_head')->where($where)
                ->count();
            $rows = Db::name('customs_export_collect_apply_head')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                if($item['summary_flag'] == 1)
                {
                    $item['summary_flag'] = '按收发货人单一汇总';
                }else{
                    $item['summary_flag'] = '按收发货人和生产销售单位汇总';
                }
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('predeclare/declare/declare_t');
        }
    }

    //汇总申请单-关联预申报(按运单)
    public function declare_t_connect(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            //1、查询该预报编号有无配置口岸信息
            $log = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b', ' b.openid=a.openid', 'left')
                ->where('a.pre_batch_num', trim($dat['pre_batch_num']))
                ->field(['a.*', 'b.fanid as uid', 'b.nickname'])
                ->find();
            if (empty($log['sid'])) {
                return json(['status' => 0, 'msg' => '请先配置用户的口岸信息']);
            }

            //2、生成汇总单报文头
            $HeadId = Db::name('customs_export_collect_apply_head')->insertGetId([
                'uid' => $log['uid'],
                'sid' => $log['sid'],
                'tracking_num' => $log['pre_batch_num'],
                'app_type' => 1,//企业报送类型。1-新增 2-变更 3-删除。
                'app_time' => date('YmdHis',time()),
                'app_status' => 2,
                'customs_code' => explode(':',$log['customs_code'])[0],
                'agent_code' => $log['ebpCode'],//申报单位-电商平台
                'agent_name' => $log['ebpName'],
                'ebc_code' => $log['ebcCode'],
                'ebc_name' => $log['ebcName'],
                'decl_agent_code' => $log['ebcCode'],//报关企业
                'decl_agent_name' => $log['ebcCode'],
                'summary_flag' => 1,//1:按收发货人单一汇总,2:按收发货人和生产销售单位汇总
                'item_name_flag' => 1,//填1,按清单原始商品名相同汇总，不填则按商品综合分类名汇总
                'self_ordersn' => $this->createSelfOrdersn(),
                'create_at' => time(),
                'is_predeclare' => 1
            ]);
            if(!empty($HeadId)){

                //3、1个运单等于1个订单,查找所有订(运)单笔数并去重
                $batch_total_num = Db::name('customs_pre_declare')
                    ->alias('a')
                    ->join('customs_goods_pre_log b','b.pre_batch_num=a.pre_batch_num','right')
                    ->where('a.pre_batch_num',$log['pre_batch_num'])
                    ->distinct(true)
                    ->field('b.logisticsNo')
                    ->select();

                //4、生成批次
                $batch_num = $this->piciName( $log['pre_batch_num'] )['pici'];
                $batch_id = Db::name('customs_export_billoflading_batch')->insert([
                    'uid' => $log['uid'],
                    'tracking_num' => $log['pre_batch_num'],
                    'batch_num' => $batch_num,
                    'type' => "CEB701Message",
                    'batch_status' => 3,
                    'total_num' => count($batch_total_num),
                    'success_num' => 0,
                    'error_num' => 0,
                    'create_at' => time()
                ]);

                foreach($batch_total_num as $k=>$v){
                    $list['pre_no'] = '';
                    $list['uid'] = $log['uid'];
                    $list['hid'] = $HeadId;
                    $list['batch_num'] = $batch_num;
                    $list['guid'] = $this->getGuidOnlyValue();
                    $list['cop_no'] = $this->createGogoOrderSn();
                    $list['status'] = 0;
                    $list['action_time'] = date('Y-m-d H:i:s',time());
                    $list['action_message'] = '待申报【'.$list['guid'].'】';
                    $list['type'] = 2;//1按日期，2按运单
                    $list['create_at'] = time();

                    Db::name('customs_export_collect_apply')->insert($list);
                }
                return json(['msg'=>'关联成功','status'=>1]);
            }else{
                return json(['msg'=>'关联失败','status'=>1]);
            }
        }else{
            return view('predeclare/declare/declare_t_connect');
        }
    }

    //汇总申请单-申报列表
    public function declare_t_list(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $tracking_num = $dat['tracking_num'];
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $user = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b','b.openid=a.openid','left')
                ->where('a.pre_batch_num',$tracking_num)
                ->field(['b.nickname','b.fanid'])
                ->find();
            $where =['uid'=>$user['fanid'],'type'=>'CEB701Message','tracking_num'=>$tracking_num];

            $count = Db::name('customs_export_billoflading_batch')->where($where)->count();
            $rows = Db::name('customs_export_billoflading_batch')->where($where)
                ->limit($page,$limit)
                ->order('id', 'desc')
                ->select();

            foreach ($rows as &$item) {
                $cop = Db::name('customs_export_collect_apply')->where(array('batch_num'=>$item['batch_num']))->field(['cop_no'])->find();
                $item['cop_no']  = $cop['cop_no'];
                $item['create_at']=date('Y-m-d H:i:s', $item['create_at']);
                $item['batch_status'] = $this->getBatchStatus($item['batch_status']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $tracking_num = $dat['tracking_num'];
            return view('predeclare/declare/declare_t_list',compact('tracking_num'));
        }
    }

    //预提列表
    public function predeclarelist(Request $request){
//        echo base64_decode('e622b753414a41c4nGYrjIjjVC0Sltn9M6_DFKD7wKmpYgJo3rtByPqvfNzhG0p7fPOemuV9Odav6BwhAcwI2D4y641s_b0nClKvkrcoJL-oLsIOk7rrAHkjYENG8y0IraD1R2cVF6rxBxN_tZkMEVyDneYf91sDRJOxiyJSQC9S_iDBL9H1yN8zYKVfzetY91SiglhShSwJLQ~~');die;
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('offset') == 0 ? 0 : ($request->get('offset') - 1) * $limit;
            $total = Db::name('customs_pre_declare')->count();
            $data = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b','b.openid=a.openid','left')
                ->where('a.withhold_status','>',1)
                ->order(trim($request->get('sort')), trim($request->get('order')))
                ->limit($page, $limit)
                ->field(['a.*','b.nickname'])
                ->select();
            foreach($data as $k=>$v){
                $data[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $total,'page'=>$page, 'rows' => $data]);
        }else{
            return view('predeclare/predeclarelist',compact('list'));
        }
    }

    //口岸信息
    public function getportinfo(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            if(empty($dat['sid']) || empty($dat['id'])){
                return json(['msg' => "参数错误", 'status' => 0]);
            }
            $res = Db::name('customs_pre_declare')->where('id',$dat['id'])->update(['sid'=>$dat['sid']]);
            if($res){
                return json(['msg' => "修改口岸信息成功", 'status' => 1]);
            }
        }else{
            $id = $dat['id'];//预申报ID
            $data = Db::name('customs_pre_declare')
                ->alias('a')
                ->join('mc_mapping_fans b','b.openid=a.openid','left')
                ->join('customs_portplatforminfo c','c.uid=b.fanid','left')
                ->where('a.id',$id)
                ->field(['c.*','a.sid as pre_sid'])
                ->select();
            return view('predeclare/getportinfo',compact('data','id'));
        }
    }

    //下发消息
    public function send_news(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            if(empty($dat['id'])){
                return json(['status'=>-1,'msg'=>'参数错误']);
            }
            //获取openid
            $info = Db::name('customs_pre_declare')->where('id',$dat['id'])->field(['openid'])->find();
            sendWechatMsg(json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好！请根据本次预报填写口岸信息，详情点击进入查看!',
                'keyword1' => '口岸信息',
                'keyword2' => '口岸信息',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '根据本次预报填写口岸信息',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=declare&m=sz_yi&p=port_platform&op=add_port',
                'openid' => $info['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]));

            return json(['status'=>1,'msg'=>'已通知客户新增口岸信息！']);

        }
    }

    //提单信息
    public function lading_save(Request $request){
        $dat = input();
        if ( request()->isPost() || request()->isAjax()) {
            if (empty($dat['id'])) {
                return json(['status' => -1, 'msg' => '参数错误']);
            }

            //更改预申报清单列表
            $data = [];
            foreach($dat['value'] as $k=>$v){
                $data[$v['name']]=trim($v['value']);
            }

            $res = Db::name('customs_pre_declare')->where('id', $dat['id'])->update($data);

            if($res){
                return json(['msg' => "修改提单信息成功", 'status' => 1]);
            }
        }else{
            $id = $dat['id'];
            $data = Db::name('customs_pre_declare')->where('id', $id)->find();
            $loctcode = Db::name('loctcode')->select();
            $customs_codes = Db::name('customs_codes')->select();
            $port_code = Db::name('port_code')->select();
            $country_code = Db::name('country_code')->select();
            $transport = Db::name('transport')->select();
            $data['transport_file'] = json_decode($data['transport_file'],true);
            return view('predeclare/batch_view/lading_save',compact('data','id','loctcode','customs_codes','port_code','country_code','transport'));
        }
    }
    
    //商品列表
    public function goods_list(Request $request){
        $dat = input();
        $id = $dat['id'];
        //找预申报编号
        $decl = Db::name('customs_pre_declare')->where('id', $id)->find();
        
        if ( request()->isPost() || request()->isAjax()) {
            if (empty($dat['id'])) {
                return json(['status' => -1, 'msg' => '参数错误']);
            }
            $limit = $dat['limit'];
            $page = $dat['offset'] - 1;
            $search = '';
            if(isset($dat['search'])){
                $search = trim($dat['search']);
            }

            if($page==-1){$page=0;}
            
//            if ($page != 0) {
//                $page = $limit * $page;
//            }
            
            //找出风险对应的商品表
            $connect_glist = Db::name('customs_declare_grisk_head')->where('pre_batch_num',$decl['pre_batch_num'])->field(['connect_glist'])->find();
            $goods = [];
            if($connect_glist['connect_glist']==4){
                //归类
                $where['pre_batch_num']  = $decl['pre_batch_num'];
                if($search)
                {
                    $where['gcode'] = ['like','%'.$search.'%'];
                }
                $goods = Db::name('customs_declare_risk_list')->where($where)->order('id','desc')->limit($page,$limit)->field(['pre_batch_num','id','itemNo','itemName','update_itemName','gcode','connect_glist'])->select();
                $count = Db::name('customs_declare_risk_list')->where($where)->count();
                $glist2 = $goods[0]['connect_glist'];
                if($glist2==3){
                    //调值
                    foreach ($goods as $k=>$v){
                        $tz = Db::name('customs_goods_pre_adjust_log')->alias('a')->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')->where('b.itemNo',$v['itemNo'])->where('a.pre_batch_num',$decl['pre_batch_num'])->field(['b.currency','a.price','a.totalPrice','b.qty'])->find();
                        $goods[$k]['currency']=$tz['currency'];
                        $goods[$k]['price']=$tz['price'];
                        $goods[$k]['totalPrice']=$tz['totalPrice'];
                        $goods[$k]['qty']=$tz['qty'];
                    }
                }elseif($glist2==2){
                    //补缺
                    foreach ($goods as $k=>$v){
                        $tz = Db::name('customs_goods_pre_fill_log')->where('itemNo',$v['itemNo'])->where('pre_batch_num',$decl['pre_batch_num'])->field(['currency','price','totalPrice','qty'])->find();
                        $goods[$k]['currency']=$tz['currency'];
                        $goods[$k]['price']=$tz['price'];
                        $goods[$k]['totalPrice']=$tz['totalPrice'];
                        $goods[$k]['qty']=$tz['qty'];
                    }
                }elseif($glist2==1){
                    //原始
                    foreach ($goods as $k=>$v){
                        $tz = Db::name('customs_goods_pre_log')->where('itemNo',$v['itemNo'])->where('pre_batch_num',$decl['pre_batch_num'])->field(['currency','price','totalPrice','qty'])->find();
                        $goods[$k]['currency']=$tz['currency'];
                        $goods[$k]['price']=$tz['price'];
                        $goods[$k]['totalPrice']=$tz['totalPrice'];
                        $goods[$k]['qty']=$tz['qty'];
                    }
                }
            }
            elseif($connect_glist['connect_glist']==3){
                //调值
                $where['a.pre_batch_num']  = $decl['pre_batch_num'];
                if($search)
                {
                    $where['b.gcode'] = ['like','%'.$search.'%'];
                }
                $goods = Db::name('customs_goods_pre_adjust_log')
                        ->alias('a')
                        ->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')
                        ->where($where)
                        ->limit($page,$limit)
                        ->field(['a.pre_batch_num','a.id','b.itemNo','b.itemName','b.update_itemName','b.gcode','b.currency','a.price','a.totalPrice','b.qty'])
                        ->select();
                $count = Db::name('customs_goods_pre_adjust_log')->alias('a')->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')->where($where)->count();
            }
            elseif($connect_glist['connect_glist']==2){
                //补缺
                $where['pre_batch_num']  = $decl['pre_batch_num'];
                if($search)
                {
                    $where['gcode'] = ['like','%'.$search.'%'];
                }
                $goods = Db::name('customs_goods_pre_fill_log')->where($where)->order('id','desc')->limit($page,$limit)->field(['id','itemNo','itemName','update_itemName','gcode','currency','price','totalPrice','qty'])->select();
                $count = Db::name('customs_goods_pre_fill_log')->where($where)->count();
            }
            elseif($connect_glist['connect_glist']==1){
                //原始
                $where['pre_batch_num']  = $decl['pre_batch_num'];
                if($search)
                {
                    $where['gcode'] = ['like','%'.$search.'%'];
                }
                $goods = Db::name('customs_goods_pre_log')->where($where)->order('id','desc')->limit($page,$limit)->field(['id','itemNo','itemName','update_itemName','gcode','currency','price','totalPrice','qty'])->select();
                $count = Db::name('customs_goods_pre_log')->where($where)->count();
            }
            
            foreach ($goods as $k=>$v){
                $goods[$k]['gname']=$v['itemName'];
                if(!empty($v['update_itemName'])){
                    $goods[$k]['gname']=$v['update_itemName'];
                }
            }
             return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $goods]);
            // return json(['code'=>0,'count'=>$count,'data'=>$goods,'page'=>$page]);
        }
    
        return view('predeclare/batch_view/goods_list',compact('id','decl'));
    }

    //商品详情
    public function good_detail(Request $request){
        $dat = input();
        $id = $dat['id'];//商品id
        $batch_num = $dat['pre_batch_num'];

        if ( request()->isPost() || request()->isAjax()) {
            //编辑
            //1、先查询风险列表采取的是哪一个列表
            $connect_glist = Db::name('customs_declare_grisk_head')->where('pre_batch_num',$batch_num)->field(['connect_glist'])->find();
            $res = '';
            if($connect_glist['connect_glist']==4){
                //归类
                $dat['update_itemName'] = $dat['gname'];
                //1、查询归类关联列表
                $glist2 = Db::name('customs_declare_risk_list')->where(['pre_batch_num'=>$batch_num,'id'=>$id])->field(['connect_glist'])->find();
                //2、修改归类信息
                Db::name('customs_declare_risk_list')->where(['pre_batch_num'=>$batch_num,'id'=>$id])->update(['update_itemName'=>trim($dat['update_itemName']),'gcode'=>trim($dat['gcode']),'unit'=>trim($dat['unit']),'unit1'=>trim($dat['unit1'])]);
                unset($dat['id']);unset($dat['gname']);unset($dat['update_itemName']);unset($dat['gcode']);unset($dat['unit']);unset($dat['unit1']);unset($dat['pre_batch_num']);
                if($glist2['connect_glist']==3){
                    //调值
                    //先查找补缺itemNo下的fill_id
                    $fill_info = Db::name('customs_goods_pre_fill_log')->where(['pre_batch_num'=>$batch_num,'itemNo'=>$dat['itemNo']])->field(['id'])->find();
                    Db::name('customs_goods_pre_adjust_log')->where(['pre_batch_num'=>$batch_num,'fill_id'=>$fill_info['id']])->update(['price'=>trim($dat['price']),'totalPrice'=>trim($dat['totalPrice']),'charge'=>trim($dat['charge'])]);
                    unset($dat['price']);unset($dat['totalPrice']);unset($dat['charge']);
                    //调值-补缺
                    $res = Db::name('customs_goods_pre_fill_log')->where(['pre_batch_num'=>$batch_num,'itemNo'=>$dat['itemNo']])->update($dat);
                }elseif($glist2['connect_glist']==2){
                    //补缺
                    $res = Db::name('customs_goods_pre_fill_log')->where(['pre_batch_num'=>$batch_num,'itemNo'=>$dat['itemNo']])->update($dat);
                }elseif($glist2['connect_glist']==1){
                    //原始
                    $res = Db::name('customs_goods_pre_log')->where(['pre_batch_num'=>$batch_num,'itemNo'=>$dat['itemNo']])->update($dat);
                }
            }elseif($connect_glist['connect_glist']==3){
                //调值（单价，总价，收款）
                $dat['update_itemName'] = $dat['gname'];
                Db::name('customs_goods_pre_adjust_log')->where(['pre_batch_num'=>$batch_num,'id'=>$id])->update(['price'=>trim($dat['price']),'totalPrice'=>trim($dat['totalPrice']),'charge'=>trim($dat['charge'])]);
                unset($dat['id']);unset($dat['gname']);unset($dat['price']);unset($dat['totalPrice']);unset($dat['charge']);unset($dat['pre_batch_num']);
                //调值-补缺
                $res = Db::name('customs_goods_pre_fill_log')->where(['pre_batch_num'=>$batch_num,'itemNo'=>$dat['itemNo']])->update($dat);
            }elseif($connect_glist['connect_glist']==2){
                //补缺
                $dat['update_itemName'] = $dat['gname'];
                unset($dat['id']);unset($dat['gname']);unset($dat['pre_batch_num']);
                $res = Db::name('customs_goods_pre_fill_log')->where(['pre_batch_num'=>$batch_num,'id'=>$id])->update($dat);
            }elseif($connect_glist['connect_glist']==1){
                //原始
                $dat['update_itemName'] = $dat['gname'];
                unset($dat['id']);unset($dat['gname']);unset($dat['pre_batch_num']);
                $res = Db::name('customs_goods_pre_log')->where(['pre_batch_num'=>$batch_num,'id'=>$id])->update($dat);
            }
            if($res){
                return json(['code' => 1, 'msg' => '修改成功']);
            }
            print_r($dat);die;
        }else{
            //找出风险对应的商品表
            $connect_glist = Db::name('customs_declare_grisk_head')->where('pre_batch_num',$batch_num)->field(['connect_glist'])->find();
            $goods = [];
            if($connect_glist['connect_glist']==4){
                //归类
                $where['pre_batch_num']  = $batch_num;
                $where['id']  = $id;

                $goods = Db::name('customs_declare_risk_list')->where($where)->find();
                $glist2 = $goods['connect_glist'];
                if($glist2==3){
                    //调值
                    $tz = Db::name('customs_goods_pre_adjust_log')->alias('a')->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')->where('b.itemNo',$goods['itemNo'])->where('a.pre_batch_num',$batch_num)->field(['b.currency','a.price','a.totalPrice','b.qty','a.charge','b.qty1','b.gmodel','b.chargeDate','b.logisticsNo','b.freight','b.insuredFee','b.barCode','b.grossWeight','b.netWeight','b.packNo','b.goodsInfo'])->find();
                }elseif($glist2==2){
                    //补缺
                    $tz = Db::name('customs_goods_pre_fill_log')->where('itemNo',$goods['itemNo'])->where('pre_batch_num',$batch_num)->field(['currency','price','totalPrice','qty','charge','qty1','gmodel','chargeDate','logisticsNo','freight','insuredFee','barCode','grossWeight','netWeight','packNo','goodsInfo'])->find();
                }elseif($glist2==1){
                    //原始
                    $tz = Db::name('customs_goods_pre_log')->where('itemNo',$goods['itemNo'])->where('pre_batch_num',$batch_num)->field(['currency','price','totalPrice','qty','charge','qty1','gmodel','chargeDate','logisticsNo','freight','insuredFee','barCode','grossWeight','netWeight','packNo','goodsInfo'])->find();
                }
                $goods['currency']=$tz['currency'];
                $goods['price']=$tz['price'];
                $goods['totalPrice']=$tz['totalPrice'];
                $goods['qty']=$tz['qty'];
                $goods['charge']=$tz['charge'];
                $goods['qty1']=$tz['qty1'];
                $goods['gmodel']=$tz['gmodel'];
                $goods['chargeDate']=$tz['chargeDate'];
                $goods['logisticsNo']=$tz['logisticsNo'];
                $goods['freight']=$tz['freight'];
                $goods['insuredFee']=$tz['insuredFee'];
                $goods['barCode']=$tz['barCode'];
                $goods['grossWeight']=$tz['grossWeight'];
                $goods['netWeight']=$tz['netWeight'];
                $goods['packNo']=$tz['packNo'];
                $goods['goodsInfo']=$tz['goodsInfo'];
            }
            elseif($connect_glist['connect_glist']==3){
                //调值
                $where['a.pre_batch_num']  = $batch_num;
                $where['a.id']  = $id;

                $goods = Db::name('customs_goods_pre_adjust_log')
                    ->alias('a')
                    ->join('customs_goods_pre_fill_log b','b.id=a.fill_id','left')
                    ->where($where)
                    ->field(['a.id','b.itemNo','b.itemName','b.update_itemName','b.gcode','b.currency','a.price','a.totalPrice','b.qty','a.charge','b.qty1','b.gmodel','b.chargeDate','b.logisticsNo','b.freight','b.insuredFee','b.barCode','b.grossWeight','b.netWeight','b.packNo','b.goodsInfo'])
                    ->find();
            }
            elseif($connect_glist['connect_glist']==2){
                //补缺
                $where['pre_batch_num']  = $batch_num;
                $where['id']  = $id;
                $goods = Db::name('customs_goods_pre_fill_log')->where($where)->field(['id','itemNo','itemName','update_itemName','gcode','currency','price','totalPrice','qty','charge','qty1','gmodel','chargeDate','logisticsNo','freight','insuredFee','barCode','grossWeight','netWeight','packNo','goodsInfo'])->find();
            }
            elseif($connect_glist['connect_glist']==1){
                //原始
                $where['pre_batch_num']  = $batch_num;
                $where['id']  = $id;
                $goods = Db::name('customs_goods_pre_log')->where($where)->field(['id','itemNo','itemName','update_itemName','gcode','currency','price','totalPrice','qty','charge','qty1','gmodel','chargeDate','logisticsNo','freight','insuredFee','barCode','grossWeight','netWeight','packNo','goodsInfo'])->select();
            }
            $goods['gname']=$goods['itemName'];
            if(!empty($goods['update_itemName'])){
                $goods['gname']=$goods['update_itemName'];
            }
            $currency = Db::name('currency')->select();
            $unit = Db::name('unit')->select();
            return view('predeclare/batch_view/good_detail',compact('id','batch_num','goods','currency','unit'));
        }
    }

    //风险商品
    public function goods_risk_list(Request $request){
        $dat = input();
        $batch_num = $dat['pre_batch_num'];

        if ( request()->isPost() || request()->isAjax()) {
            $limit = $dat['limit'];
            $page = $dat['offset'] - 1;
            $search = '';
            if(isset($dat['search'])){
                $search = trim($dat['search']);

            }
            if($page==-1){$page=0;}
//            if ($page != 0) {
//                $page = $limit * $page;
//            }
            $where['pre_batch_num']  = $batch_num;
            if($search)
            {
                $where['gcode'] = ['like','%'.$search.'%'];
                $goods = Db::name('customs_declare_grisk_list')->where($where)->limit($page,$limit)->order('id','desc')->select();
                $count = Db::name('customs_declare_grisk_list')->where($where)->count();
            }else{
                $goods = Db::name('customs_declare_grisk_list')->where($where)->whereOr('is_cert',1)->whereOr('is_check',1)->limit($page,$limit)->order('id','desc')->select();
                $count = Db::name('customs_declare_grisk_list')->where($where)->whereOr('is_cert',1)->whereOr('is_check',1)->count();
            }
            //查找风险商品
            foreach($goods as $k=>$v){
                if(!empty($v['file'])){
                    $goods[$k]['file'] = json_decode($v['file'],true);
                }
                if(!empty($v['file2'])) {
                    $goods[$k]['file2'] = json_decode($v['file2'], true);
                }
            }

            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $goods]);
        }else{

        }
        return view('predeclare/batch_view/goods_risk_list',compact('batch_num'));
    }

    //审核
    public function audit_accrual(Request $request){
        $dat = input();
        $batch_num = $dat['pre_batch_num'];
        $id = $dat['id'];

        if ( request()->isPost() || request()->isAjax()) {
            $data = Db::name('customs_pre_declare')->where('pre_batch_num',$batch_num)->find();
            if($dat['withhold_status']==2){
                return json(['msg' => "无需重复操作", 'status' => 1]);
            }
            //1、记录
            Db::name('customs_pre_declare')->where('pre_batch_num',$batch_num)->update(['withhold_status'=>$dat['withhold_status'],'check_remark'=>$dat['check_remark']]);
            $msg = '';
            if($dat['withhold_status']==3){
                $msg = '本次预提已被退回';
                sendWechatMsg(json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好！'.$msg.'，详情点击进入查看!',
                    'keyword1' => '预提编号['.$data['pre_batch_num'].']',
                    'keyword2' => $msg,
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => '退回原因：'.$dat['check_remark'],
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=declare&m=sz_yi&p=accrual&op=display',
                    'openid' => $data['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]));
            }else if($dat['withhold_status']==4){
                $msg = '本次预提已申报';
                sendWechatMsg(json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好！'.$msg.'，详情点击进入查看!',
                    'keyword1' => '预提编号['.$data['pre_batch_num'].']',
                    'keyword2' => $msg,
                    'keyword3' => date('Y-m-d H:i:s',time()),
                    'remark' => $msg,
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=declare&m=sz_yi&p=accrual&op=display',
                    'openid' => $data['openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]));
            }

            return json(['status'=>1,'msg'=>'修改审核状态成功，已通知客户！']);
        }else{
            //查找pre_declare
            $data = Db::name('customs_pre_declare')->where('pre_batch_num',$batch_num)->find();
        }
        return view('predeclare/batch_view/audit_accrual',compact('batch_num','id','data'));
    }

    public function declare_status(Request $request){
        $dat = input();
        $batch_num = $dat['pre_batch_num'];
        $id = $dat['id'];

        if ( request()->isPost() || request()->isAjax()) {
            $data = Db::name('customs_pre_declare')->where('pre_batch_num',$batch_num)->find();
            //1、记录
            Db::name('customs_pre_declare')->where('pre_batch_num',$batch_num)->update(['declare_status'=>$dat['declare_status']]);
            $msg = '';
            if($dat['declare_status']==1){
                $msg='已申报未运抵';
            }else if($dat['declare_status']==2){
                $msg='已运抵未通关';
            }else if($dat['declare_status']==3){
                $msg='已通关未离境';
            }else if($dat['declare_status']==4){
                $msg='已离境已结关';
            }
            sendWechatMsg(json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好！您本次预报状态为['.$msg.']，详情点击进入查看!',
                'keyword1' => '预提编号['.$batch_num.']',
                'keyword2' => $msg,
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=declare&m=sz_yi&p=declare_list&op=display',
                'openid' => $data['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]));

            return json(['status'=>1,'msg'=>'修改申报状态成功，已通知客户！']);
        }else{
            //查找pre_declare
            $data = Db::name('customs_pre_declare')->where('pre_batch_num',$batch_num)->find();
        }
        return view('predeclare/batch_view/declare_status',compact('batch_num','id','data'));
    }
}