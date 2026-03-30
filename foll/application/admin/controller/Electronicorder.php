<?php
namespace app\admin\controller;

use Maatwebsite\Excel\Readers\Batch;
use think\Request;
use Util\data\Sysdb;
use think\Session;
use think\Validate;
use think\Loader;
use think\Db;
use app\admin\model\CustomsElecOrderDetailModel;
use app\admin\model\CustomsCommonShipperinfoModel;
use app\admin\model\CustomsBatchModel;

class Electronicorder extends Auth {


    public function bollist(Request $request){
        $data = null;
        $type = ['I'=>'进出口商品订单','E'=>'出口商品订单'];
        $config = ['type' =>'Layui', 'query'=>['s'=>'admin/elec/bollist'], 'var_page'=>'page', 'newstyle'=>true];
        $where = ['a.bol_type'=>'E'];
        $billNum = $request->get('billNum');
        if ($billNum!=''){
            $where['a.bill_num'] = $billNum;
        }
        $data = Db::name('decl_bol')
            ->field('a.bill_num,a.create_time,a.bill_votes,b.user_name,a.waybill_name,b.company_name,a.customs_codes')
            ->alias('a')
            ->join('decl_user b','a.user_id=b.id')
            ->where($where)
            ->order('a.id','desc')
            ->paginate(8,false,$config);

        return view('/elec/bol_list',[
            'title'=>'提单列表',
            'data'=>$data->toArray(),
            'page'=>$data->render(),
            'type'=>$type
        ]);
    }

    // 订单申报列表
    public function eleclist(Request $request) {
        $bnum = $request->get('bill_num');
        if($bnum=="") {
           $this->error('编号错误');
        }
        $opera = null;
        $this->db = new Sysdb;
        $userInfo = Session('myUser');
        //print_r($userInfo);
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/eleclist','bill_num'=>$bnum],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        if($userInfo['role'] == 1) {

            //$opera = $this->db->table('customs_batch')->where(['type'=>'E','bill_num'=>$bnum])->order('id desc')->pages(10,$config);
            // 2019-03-25
            $opera = $this->db->table('customs_batch')
                ->alias('a')
                ->join(['decl_user b','a.uid = b.id'])
                ->field('a.bill_num,a.create_time,a.batch_num,a.desc,a.status,a.uid,a.pay_id,b.user_name')
                ->where(['type'=>'E','bill_num'=>$bnum])
                ->order('a.id desc')
                ->pagesJoins(10,$config);
        }

        /*$payType = [
            'newpay'    =>  '新生支付有限公司',
            'helpay'    =>  '邦付宝支付科技有限公司',
            'quickpay'  =>  '广州商物通网络科技有限公司',
            'minpay'    =>  '敏付科技有限公司',
        ];*/

        //获取设备
        $device = Db::name('hjxssl_device')->where(1)->order('createtime','desc')->select();
        $express = Db::name('hjxssl_express')
            ->alias('a')
            ->join('customs_express_company_code b','a.code = b.code','left')
            ->where(1)
            ->order('a.createtime','desc')
            ->field(['a.code,b.name,a.id'])
            ->select();
        $this->assign('device',$device);// 改变
        $this->assign('express',$express);// 改变

        $payType = $this->payList();
        // 支付企业
        $this->assign('payType',$payType);// 改变
        $this->assign('data',$opera);// 改变
        return view("elec/index",['title'=>'电子订单预提列表']);
    }

    //2021-11-01 打印电子面单
    public function getThisBatchOrder(Request $request){
        $batch_num = $request->get('batch_num');

        //获取该批次的订单号
        $ord = Db::name('customs_elec_order_tmp')->where('batch_num',$batch_num)->field(['EntOrderNo'])->select();
        $type=1;
        if(empty($ord)){
            $ord = Db::name('customs_elec_order_detail')->where('batch_num',$batch_num)->field(['EntOrderNo'])->select();
            $type=2;
        }

        if(!empty($ord)){
            return ['code'=>1,'data'=>$ord,'type'=>$type];
        }else{
            return ['code'=>-1,'msg'=>'找不到该批次号（'.$batch_num.')下的订单'];
        }
    }

    public function printElec(Request $request){
        $dat = input();

        $batch = Db::name('customs_batch')->where(['batch_num'=>$dat['batch_num']])->field(['check_status','status','bill_num'])->find();
        if($batch['check_status']>=3 && ($batch['status']>=4 || $batch['status']<=5)){
            $dev = Db::name('hjxssl_device')->where('id',$dat['device'])->field(['device_info'])->find()['device_info'];
            $device = explode(',',$dev);
            $express = Db::name('hjxssl_express')->where('id',$dat['express'])->field(['code','temp_id'])->find();

            $orderno = [];
            foreach($dat['orderno'] as $k=>$v){
                $orderno_arr = explode('-',$v);
                if($orderno_arr[1]==2){
                    $orderno[] = Db::name('customs_elec_order_detail')->where('EntOrderNo',$orderno_arr[0])->find();
                }else{
                    $orderno[] = Db::name('customs_elec_order_tmp')->where('EntOrderNo',$orderno_arr[0])->find();
                }
            }

            $param = [
                'batch_num'=> $dat['batch_num'],
                'device'   => $device,
                'express'  => $express,
                'orderno'  => $orderno
            ];

            $res2 = $this->kuaibaoGetElecOrder($param);
            return ['code'=>1,'temp'=>$res2,'msg'=>''];
        }else{
            return ['code'=>-1,'msg'=>'该提单状态需审核通过后才可打印面单！'];
        }
    }

    //快宝接口
    public function kuaibaoGetElecOrder($param=array()){

        foreach($param['orderno'] as $k=>$v){
            //1、获取商品表名称
            $goods_name = '';
            $goodsinfo = json_decode($v['goodsNo'],true);
            foreach($goodsinfo as $kk=>$vv){
                $CusGoodsNo = Db::name('sz_yi_goods')->where('goodssn',$vv['goodNo'])->field(['CusGoodsNo'])->find()['CusGoodsNo'];

                $goods_name .= \app\admin\model\GoodsModel::where('cus_goodsNo',$CusGoodsNo)->field(['goods_name'])->find()['goods_name'].',';
            }

            $host = "https://kop.kuaidihelp.com/api";
            $headers = array();
//根据API的要求，定义相对应的Content-Type
            array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
            $appId = '110230';
            $method = 'cloud.print.waybill';
            $ts = time();
            $appKey = '4e2768717519340c44b70b4ef82065256f19c28a';

            $express_company = '';
            if($param['express']['code']=='youzhengguonei'){
                $express_company = 'post';
            }
            $bodys = [
                "app_id" => $appId,
                "method" => $method,
                "sign" => md5($appId . $method . $ts . $appKey),
                "ts" => $ts,
//            "cod_amount": 200,货到付款时使用
//            "pickup_code": "",取件码
//            "user_name": "13111111111",打印人名称
//            "weight": "1", 物品重量
                "data" => '{
                "agent_id": "9525364583374058",
                "print_type": "2",
                "template_id": '.$param['express']['temp_id'].',
                "print_data": [
                    {
                        "sequence": "1/1",
                        "cp_code": '.$express_company.',
                        "note": "",
                        "goods_name": '.substr($goods_name,0,strlen($goods_name)-1).',
                        "tid": '.$v['EntOrderNo'].',
                        "recipient": {
                            "address": {
                                "city": '.$v['RecipientCity'].',
                                "detail": '.$v['RecipientAddr'].',
                                "district": '.$v['RecipientDistrict'].',
                                "province": '.$v['RecipientProvince'].'
                            },
                            "mobile": '.$v['RecipientTel'].',
                            "name": '.$v['RecipientName'].',
                            "phone": ""
                        },
                        "sender": {
                            "address": {
                                "city": '.$v['senderCity'].',
                                "detail": '.$v['senderAddr'].',
                                "district": '.$v['senderDistrict'].',
                                "province": '.$v['senderProvince'].'
                            },
                            "mobile": '.$v['senderTel'].',
                            "name": '.$v['senderName'].',
                            "phone": ""
                        },
                        "waybill_code": '.$v['WaybillNo'].'
                    }
                ]
            }'
            ];
            $bodys = http_build_query($bodys);
            $url = $host;
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            if (1 == strpos("$".$host, "https://"))
            {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
            $result = curl_exec($curl);
            curl_close($curl);
            return $result;
        }



        //三段信息
//        "routing_info": {
//            "consolidation": {
//                "name": "温州转福鼎包(集包名)1"
//                            },
//                            "origin": {
//                "code": "610025",
//                                "name": "四川邛崃公司"
//                            },
//                            "route_code": "009 030(二三段码)456",
//                            "sortation": {
//                "name": "福鼎351(大字 一段码)123"
//                            }
//                        },
    }
    //end

    /**
     *  申报操作页面
     */
    public function declares() {
        $this->db = new Sysdb;
        // 批次号
        $batch_num = input('batch_num');
        // 商户UID
        $uid       = input('uid');

        // 支付表列表
        $payList = $this->db->table('customs_pay_list')->lists();

        //2021-11-03 查看当前批次号是否要支付
        $ispay = Db::name('customs_batch')->where('batch_num',$batch_num)->field('ver_pay,isplat')->find();

        if(empty($ispay['ver_pay'])){
            //走不需支付通道
            //step1:先查找该批次号的订单信息
            $data = Db::name('customs_elec_order_detail')->where('batch_num',$batch_num)->select();
            foreach($data as $k=>$dv){
                // 组装数据进行总署申报；
                $params = [];
                $params['orderNo']          = trim($dv['EntOrderNo']);        // 订单编号 'GC2021010816583237762399'
                $params['chkMark']          = 2;             // 报关状态1未报关，2已报关
                $params['completeTime']     = date('YmdHis',time());      // 完成时间

                //2021-10-30
                $params['isplat']           = $ispay['isplat'];//是否钜铭平台 1是 2不是
                if($params['isplat']==1){
                    $params['payTransactionNo'] = trim($dv['payNo']);  // 支付交易编号
                    $params['failInfo']         = '支付单新增申报成功';  // 支付信息：支付单新增申报成功[0EEFA9AF-14FD-4A4B-8F26-A43E22BD2B4C];
                }
                $url = 'https://decl.gogo198.cn/api/orderCustoms/sendElcOrder2';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
                $result = curl_exec($ch);
                curl_close($ch);

                if($result){
                    Db::name('customs_batch')->where(['batch_num'=>$batch_num])->update([
                        'check_status'=>3,'desc'=>'已申报'
                    ]);
                    $result = json_decode($result,true);
                    return $result['msg'];
                }
            }
        }

        $this->assign('uid',$uid);
        $this->assign('batch_num',$batch_num);
        $this->assign('payList',$payList);

        return view('elec/select',['title'=>'电子订单申报']);
    }

    // 退回操作
    public function Returns() {

        $this->db = new Sysdb;
        $batch_num = input('batch_num');
        /**
         * 根据批次更新状态；
         */
        $up = [
            'status'=>2,
            'desc'  =>'已退回',
        ];

        $this->db->table('customs_batch')->where(['batch_num'=>$batch_num])->update($up);

        return json_encode(['code'=>1,'msg'=>'退回成功']);
    }

    /**
     * @deprecated  导出物流清单
     * @param Request $request
     */
    public function exproWaybillElist(Request $request){


        $input = $request->get();
        $batch_num = $request->get('batch_num');
        if (!isset($input['type'])&&$input['type']==""){
            $this->error('导出失败');
        }

        if ($request->get('type')=="1"){
            $result = Loader::model('ElistExportExcel','logic')->orderElistExproTypeOne($request->get());
        }else if ($request->get('type')=="2"){
            $result =Loader::model('ElistExportExcel','logic')->orderElistExproTypeTwo($request->get());
        }

        if (is_object($result)){
            //$fileName = md5(date('YmdHis', time()) . mt_rand(1111, 9999));
            $fileName = $batch_num;
            ob_end_clean();//清楚缓冲避免乱码
            header('pragma:public');
            //设置表头信息
            header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$fileName.'.xls"');
            header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打
            $result->save('php://output');
        }else{
            $this->error($result);
        }
    }



    /**
     * @deprecated 后台导出购买风险
     * @param Request $request
     */
    public function exproOrderPurch(Request $request){
        $batch_num = $request->get('batch_num');
        if ($request->get('batch_num')==''){
            $this->error('导出错误');
        }

        //$fileName = md5(date('YmdHis', time()) . mt_rand(1111, 9999)).'.xlsx';
        $fileName = $batch_num. mt_rand(1111, 9999).'.xlsx';

        $result =Loader::model('ElistExportExcel','logic')->getOrderPurch($request->get('batch_num'));
        if (!is_object($result)){
            $this->error($result);
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        $result->save('php://output');
    }



    /**
     * @deprecated 导出错误申报
     * @param Request $request
     */
    public function exproOrderDeErrMsg(Request $request){
        $batch_num = $request->get('batch_num');
        if ($request->get('batch_num')==''){
            $this->error('导出错误');
        }

        //$fileName = md5(date('YmdHis', time()) . mt_rand(1111, 9999)).'.xlsx';
        $fileName = $batch_num.'.xlsx';

        $result =Loader::model('ElistExportExcel','logic')->exproOrderDeErrMsg($request->get('batch_num'));
        if (!is_object($result)){
            $this->error($result);
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        $result->save('php://output');
    }


    /**
     * @param Request $request
     * 导出税费；
     */
    public function taxExport(Request $request){
        $batch_num = $request->get('bill_num');
        if ($request->get('bill_num')==''){
            $this->error('导出错误');
        }

        //$fileName = md5(date('YmdHis', time()) . mt_rand(1111, 9999)).'.xlsx';
        $fileName = $batch_num.'_'.time().'.xlsx';

        // 获取文件
        $result =Loader::model('ElistExportExcel','logic')->getExportax($request->get('bill_num'));
        if (!is_object($result)){
            $this->error($result);
        }

        ob_end_clean();//清楚缓冲避免乱码
        header('pragma:public');
        //设置表头信息
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name='.$fileName);
        header("Content-Disposition:attachment;filename={$fileName}");//attachment新窗口打
        // 保存输出数据
        $result->save('php://output');
    }

    // 测试进度链表分页查询
    public function test()
    {
        $opera = null;
        $this->db = new Sysdb;
        $userInfo = Session('myUser');

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/schedules'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        /*if($userInfo['role'] == 1){
            $data = $this->db->table('customs_batch')->where(['type'=>'E'])->order('id desc')->pages(8,$config);
        }*/

        /*$total = Db::name('customs_batch')->where(['type'=>'E'])->count();//总条数
        $opera = Db::name('customs_batch')->alias('a')->join('decl_user b','a.uid=b.id','LEFT')->where(['a.type'=>'E'])->field(['a.*,b.user_name'])->order('a.id desc')->paginate(8,$config);
        $data = array('total'=>$total,'lists'=>$opera->items(),'pages'=>$opera->render());*/

        // 输出啊
        $ps = $this->db->table('customs_batch')
            ->alias('a')
            ->join(['decl_user b','a.uid = b.id'])
            ->field('a.uid,a.id,a.type,a.bill_num,a.batch_num,a.create_time,a.desc,b.user_name,b.id')
            ->where(['type'=>'E'])
            ->order('a.id desc')
            ->pagesJoins(8,$config);// 刷新输出


        /*$ps= Db::name('customs_batch')
            ->alias('a')
            ->join('decl_user b','a.uid=b.id')
            ->field('a.uid,a.id,a.type,a.bill_num,a.batch_num,a.create_time,a.desc,b.user_name,b.id')
            ->where('a.type','E')
            ->order('a.id desc')
            ->page(8,$config);*/

        //$this->assign('data',$data);
        echo '<pre>';
        print_r($ps);

        //return view('elec/test',['title'=>'电子订单申报']);
    }


    // 进度查询
    public function schedules() {
        $opera = null;
        $this->db = new Sysdb;
        $userInfo = Session('myUser');

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/schedules'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        if($userInfo['role'] == 1){
            //$data = $this->db->table('customs_batch')->where(['type'=>'E'])->order('id desc')->pages(8,$config);
            $data = $this->db->table('customs_batch')
                ->alias('a')
                ->join(['decl_user b','a.uid = b.id'])
                ->field('a.uid,a.succ_num,a.type,a.bill_num,a.batch_num,a.create_time,a.desc,b.user_name')
                ->where(['type'=>'E'])
                ->order('a.id desc')
                ->pagesJoins(9,$config);
        }

        $this->assign('data',$data);
        return view('elec/schedules',['title'=>'电子订单申报']);
    }


    // 验证失败
    public function yzerror() {

        $opera = null;
        $this->db = new Sysdb;
        $userInfo = Session('myUser');

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/yzerror'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        if($userInfo['role'] == 1) {
            $data = $this->db->table('customs_batch')
                ->alias('a')
                ->join(['decl_user b','a.uid = b.id'])
                ->field('a.uid,a.succ_num,a.type,a.bill_num,a.batch_num,a.create_time,a.desc,b.user_name')
                ->where(['type'=>'E','status'=>8])
                ->order('a.id desc')
                ->pagesJoins(9,$config);
        }

        $this->assign('data',$data);
        return view('elec/yzerror',['title'=>'身份验证失败']);
    }


    //查看购买风险，审核通过   2019-11-20
    public function riskshow() {

        $opera = null;
        $this->db = new Sysdb;
        $userInfo = Session('myUser');

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/risk'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        if($userInfo['role'] == 1) {

            $datas = $this->db->table('customs_batch')
                ->alias('a')
                ->join(['decl_user b','a.uid = b.id'])
                ->field('a.uid,a.succ_num,a.type,a.bill_num,a.batch_num,a.create_time,a.desc,a.ver_pay,b.user_name')
                ->where(['type'=>'E','check_status'=>4])
                ->order('a.id desc')
                ->pagesJoins(14,$config);
        }

        $this->assign('datas',$datas);
        return view('elec/riskshow',['title'=>'购买风险']);
    }

    // 获取购买风控信息 2019-11-20
    public function getRiskd(Request $request) {
        // 批次号
        $batch = $request->get('batch');

        $opera = null;
        $this->db = new Sysdb;
        //$userInfo = Session('myUser');

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/getRisk','batch'=>$batch],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $datas = $this->db->table('customs_elec_order_risk')
            ->field('*')
            ->where(['batch_num'=>$batch])
            ->pages(14,$config);

        $type = [
            'ArtNo'     =>'同品多购',
            'monthMoney'=>'月超金额',
            'yearMoney' =>'年超金额',
            'WayNum'    =>'一单多品',
            'orderPurch'=>'一人多票',
        ];

        $this->assign('datas',$datas);
        $this->assign('type',$type);
        return view('elec/getRisk',['title'=>'购买风险']);
    }

    // 确认风险  2019-11-20
    public function okRisk(Request $req) {
        $batch = $req->post('batch');
        $up = Db::name('customs_batch')->where(['batch_num'=>$batch])->update(['check_status'=>1]);
        if(!$up) {
            return json(['code'=>0,'msg'=>'确认风险失败，请稍后操作']);
        }
        return json(['code'=>1,'msg'=>'确认风险完成!']);
    }

    // 待申报
    public function dsb() {

        $opera = null;
        $this->db = new Sysdb;
        $userInfo = Session('myUser');

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/dsb'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        if($userInfo['role'] == 1) {
            $data = $this->db->table('customs_batch')
                ->alias('a')
                ->join(['decl_user b','a.uid = b.id'])
                ->field('a.uid,a.succ_num,a.type,a.bill_num,a.batch_num,a.create_time,a.desc,a.ver_pay,b.user_name')
                ->where(['type'=>'E','status'=>1])
                ->order('a.id desc')
                ->pagesJoins(9,$config);
        }

        $verPay = $this->payList();
        $this->assign('data',$data);
        $this->assign('verPay',$verPay);
        return view('elec/dsb',['title'=>'身份验证失败']);
    }

    // 支付企业列表
    private function payList(){
        $verPay = [
            'newpay'=>'新生支付有限公司',
            //'helpay'=>'邦付宝支付科技有限公司',
            'quickpay'=>'广州商物通网络科技有限公司',
            'Bfbpay'=>'邦付宝支付科技有限公司',
            'Sumpay'=>'商盟商务服务有限公司',
            'sandpay'=>'杉德支付网络服务发展有限公司'
        ];
        return $verPay;
    }

    // 已申报   GC2019030519531313704220
    public function ysb() {

        $opera = null;
        $this->db = new Sysdb;
        $userInfo = Session('myUser');

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/ysb'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        if($userInfo['role'] == 1) {
            $data = $this->db->table('customs_batch')
                ->alias('a')
                ->join(['decl_user b','a.uid = b.id'])
                ->field('a.uid,a.succ_num,a.type,a.bill_num,a.batch_num,a.create_time,a.desc,a.pay_id,b.user_name')
                ->where(['type'=>'E','status'=>4])
                ->order('a.id desc')
                ->pagesJoins(9,$config);
        }
        $verPay = $this->payList();
        $this->assign('verPay',$verPay);

        $this->assign('data',$data);
        return view('elec/ysb',['title'=>'身份验证失败']);
    }


    //admin/elec/expro_err&amp;batch_num

    // 查看进度
    public function schedulesQuery() {
        $this->db = new Sysdb;
        // 批次号
        $batch_num = input('batch_num');
//       $batch_num = 'E999-508527012XX01';
        $data = []; // ->field('succ_num,err_num,status')
        $batch = $this->db->table('customs_batch')->where(['batch_num'=>$batch_num])->item();
        // 待申报 ims_customs_elec_order_detail  sdf
        $dsb = $this->db->table('customs_elec_order_detail')->where(['batch_num'=>$batch_num,'PayStatus'=>0,'elecStatus'=>0])->lists();
        $dsbs = !empty($dsb) ? count($dsb) : 0;

        if(empty($batch)) {

            // 预提
            $data['btch_tj'] = 0;
            $data['btch_sb'] = 0;
            $data['btch_jy'] = 0;

            // 支付
            $data['pay_tj'] = 0;
            $data['pay_sb'] = 0;
            $data['pay_jy'] = 0;

            // 转发
            $data['zf_tj'] = 0;
            $data['zf_sb'] = 0;
            $data['zf_jy'] = 0;

            // 申报
            $data['sb_tj'] = 0;
            $data['sb_sb'] = 0;
            $data['sb_jy'] = 0;

            // 物流申报
            $data['waybill_tj'] = 0;// 已经支付的  1000 未支付，  已提交
            $data['waybill_sb'] = 0;// 待申报
            $data['waybill_jy'] = 0;// 待校验


        } else {

            // 计算申报数量  未支付的
            //$tj = $this->db->table('customs_realname_general')->where(['title' => $batch_num])->counts();
            $jy = $this->db->table('customs_realname_error')->where(['title' => $batch_num])->counts();

            // 已上传的票数
            $ps = $this->db->table('customs_elec_order_detail')->where(['batch_num' => $batch_num])->counts();
            $jysb = $this->db->table('decl_fail_decl_batch')->where(['batch_num' => $batch_num])->counts();
            //$djy = $this->db->table('customs_batch')->field('succ_num')->where(['batch_num'=>$batch_num])->item();
            //$tjs = !empty($tj) ? $tj : 0;
            $jysb = !empty($jysb) ? $jysb : 0;
            $jys = !empty($jy) ? $jy : 0;

            //$tmp = ($tj+$tjs);
            //$djys = ($djy['succ_num'] - $tmp) > 0 ? abs($djy['succ_num'] - $tmp) : 0;

            // 订单预提
            $data['btch_tj'] = $ps    ? $ps  : 0;   // 已校验  校验成功数据
            $data['btch_sb'] = $jys   ? $jys : 0;   // 校验失败；待校验  校验数量-身份验证成功数量
            $data['btch_jy'] = $jysb  ? $jysb  : 0;   // 格式校验失败

            // 支付状态
            $pay_tj = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'PayStatus'=>0])->counts();// 未付款
            $pay_sb = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'PayStatus'=>1])->counts();//已支付
            $pay_jy = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'PayStatus'=>2])->counts();//支付失败
            $pay_tj = !empty($pay_tj) ? $pay_tj : 0;
            $pay_sb = !empty($pay_sb) ? $pay_sb : 0;
            $pay_jy = !empty($pay_jy) ? $pay_jy : 0;
            // 支付
            $data['pay_tj'] = $pay_sb;
            $data['pay_sb'] = $pay_tj;
            $data['pay_jy'] = $pay_jy;


            // 转发
            $zf_tj = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'elecStatus'=>0])->counts();//待转发
            $zf_sb = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'elecStatus'=>1])->counts();//已转发
            $zf_jy = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'elecStatus'=>2])->counts();// 转发失败
            $zf_tj = !empty($zf_tj) ? $zf_tj : 0;
            $zf_sb = !empty($zf_sb) ? $zf_sb : 0;
            $zf_jy = !empty($zf_jy) ? $zf_jy : 0;
            // 转发
            $data['zf_tj'] = $zf_sb;
            $data['zf_sb'] = $zf_tj;
            $data['zf_jy'] = $zf_jy;

            // 申报状态
            $sb_tj = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'sbStatus'=>0])->counts();
            $sb_sb = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'sbStatus'=>1])->counts();
            //$sb_jy = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'PayStatus'=>1,'elecStatus'=>1,'sbStatus'=>2])->counts();
            $sb_jy = $this->db->table('customs_elec_order_detail')->field('EntOrderNo')->where(['batch_num'=>$batch_num,'sbStatus'=>2])->counts();
            $sb_tj = !empty($sb_tj) ? $sb_tj : 0;
            $sb_sb = !empty($sb_sb) ? $sb_sb : 0;
            $sb_jy = !empty($sb_jy) ? $sb_jy : 0;
            // 申报
            $data['sb_tj'] = $sb_sb;
            $data['sb_sb'] = $sb_tj;
            $data['sb_jy'] = $sb_jy;

            // 物流申报
            $data['waybill_tj'] = 0;// 已经支付的  1000 未支付，  已提交
            $data['waybill_sb'] = 0;// 待申报
            $data['waybill_jy'] = 0;// 待校验
        }

        $this->assign('data',$data);
        return view('elec/schedulesQuery',['title'=>'电子订单申报']);
    }


    // 风险查看
    /**
     * 1、开发步骤，获取风险配置
     * 2、获取该批次下的所有用户
     * 3、获取当月该用户的数据，获取当年用户的数据
     * 4、数据值超过了设置，显示红色
     */
    public function Risk()
    {
        $batch_num = input('batch_num');
        $bnum      = trim($batch_num);

        $opera = null;
        $this->db = new Sysdb;

        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/Risk','batch_num'=>$bnum],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        // 计算自然月
        $day   = date('Y-m-01 00:00:01',time());
        $start = strtotime($day);
        $end   = strtotime(date('Y-m-d 23:59:59',strtotime("$day+1 month-1 day")));
        // 获取自然年
        $Year1 = strtotime(date('Y-01-01 00:00:01',time()));
        $Year2 = strtotime(date('Y-12-31 23:59:59',time()));

        // 获取额度配置
        //$conf = $this->db->table('customs_Riskconfig')->where(['id'=>1])->item();
        $conf = DB::name('customs_riskconfig')->find();

        //OrderGoodTotal
        $opera = $this->db->table('customs_elec_order_detail')->field('RecipientName,OrderDocId,OrderDocTel,batch_num')->where(['batch_num'=>$bnum])->groups('OrderDocId')->pages(10,$config);

        foreach ($opera['lists'] as $k=>&$v) {
            // ims_customs_Riskconfig
            $where1 = ['OrderDocId'=>$v['OrderDocId'],'create_at'=>['between',[$start,$end]],'sbStatus'=>1];// 按月查询
            $where2 = ['OrderDocId'=>$v['OrderDocId'],'create_at'=>['between',[$Year1,$Year2]],'sbStatus'=>1];// 按年查询
            // 月票数：monthNum,月超标状态：monthStatus,月额度：monthMoney,月总额超标：monthMStatus
            // 年票数: yearNum,年超标状态：yearStatus,年额度：yearMoney,年总额超标：yearMStatus
            // 统计月订单数量
            $monthNum = $this->db->table('customs_elec_order_detail')->where($where1)->counts();
            $status      = $monthNum > $conf['monthNum'] ? 1 : 2;
            $v['monthNum']   = $monthNum;// 订单票数
            $v['monthStatus']= $status;// 1 超过，2未超过

            // 统计月的总额 5000
            $monthMoney = $this->db->table('customs_elec_order_detail')->field('OrderGoodTotal')->where($where1)->lists();
            $sum = 0;
            if(!empty($monthMoney)) {
                foreach($monthMoney as $T) {
                    $sum += sprintf("%.2f",$T['OrderGoodTotal']);
                }
            }

            $monthMStatus    = $sum > $conf['monthMoney'] ? 1 : 2;
            $v['monthMoney'] = $sum;// 月总额
            $v['monthMStatus']= $monthMStatus;// 月总额超标状态

            // 统计年的订单数量
            $yearNum = $this->db->table('customs_elec_order_detail')->where($where2)->counts();
            $status  = $yearNum > $conf['yearNum'] ? 1 : 2;
            $v['yearNum']  = $yearNum;// 年总票数
            $v['yearStatus'] = $status;// 年超标状态，票数
            // 统计年的总额 26000
            $yearMoney = $this->db->table('customs_elec_order_detail')->field('OrderGoodTotal')->where($where2)->lists();
            $sum = 0;
            if(!empty($yearMoney)) {
                foreach ($yearMoney as $T) {
                    $sum += sprintf("%.2f", $T['OrderGoodTotal']);
                }
            }
            $yearMStatus    = $sum > $conf['yearMoney'] ? 1 : 2;
            $v['yearMoney'] = $sum;// 年总额
            $v['yearMStatus']= $yearMStatus;// 年超标状态
        }
        $this->assign('data',$opera);// 改变
        return view("elec/risk",['title'=>'风控列表']);
    }


    // 获取用户所属的产品链接
    public function getrisk()
    {
        $idCard = input('idCard');
        // 通过用户身份证id  查询所属订单编号，根据订单编号获取全部产品链接
        $ordersn = DB::name('customs_elec_order_detail')->field('EntOrderNo')->where(['OrderDocId'=>$idCard])->select();
        $tmp = [];
        foreach($ordersn as $v) {
            $tmp[] = $v['EntOrderNo'];
        }
        unset($ordersn);

        $links = DB::name('customs_payment_cusinquirs')->field('itemLink')->where('orderNo','in',$tmp)->select();
        $linkd = [];
        foreach($links as $l) {
            $link = explode(' | ',$l['itemLink']);
            foreach($link as $nk) {
                if(!empty($nk)){
                    $linkd[] = $nk;
                }
            }
        }
        unset($links);
        $this->assign('data',$linkd);
        $this->assign('idCard',$idCard);
        return view('elec/risklist',['title'=>'订购产品列表']);
    }


    /**
     * 身份验证失败列表
     *
     * @return void
     */
    public function idCardVerifFail(Request $request)
    {
        $title = '身份验证失败';
        $batchNum = $request->get('batch_num');
        $total = Db::name('customs_elec_order_identify')->where('batch_num',$batchNum)->count('id');
        $data = Db::name('customs_elec_order_identify')
        ->where('batch_num',$batchNum)
        ->paginate(10,$total,[
            'type' =>'Layui',
            'query'=>['s'=>'admin/elec/idCardVerifFail&batch_num='.$batchNum],
            'var_page'=>'page',
            'newstyle'=>true
        ]);
        $page = $data->render();
        $list = $data->toArray()['data'];
        return view('elec/idcard_verif_fail',compact('title','list','page','batchNum'));
    }

    /**
     * 确认身份风控
     *
     * @param Request $request
     * @return void
     */
    public function idCardVerifConfirm(Request $request)
    {
        $batchNum = $request->get('batch_num');
        Db::startTrans();
        try{
            Db::name('customs_batch')->where('batch_num',$batchNum)->update([
                'check_status'=>3,
                'status'      =>1,
                'desc'        =>'待申报'
            ]);
            Db::commit();
        }catch(\Exception $e){
            Db::rollback();
            return json(['code'=>-1,'message'=>$e->getMessage()]);
        }
      return json(['code'=>0,'message'=>'完成']);
    }


    public function editOrderSender(Request $request)
    {
        $list=CustomsElecOrderDetailModel::where("batch_num='".$request->get('batch_num')."' and senderName=''")->field(['EntOrderNo','WaybillNo'])->select();
        $uid=CustomsBatchModel::where('batch_num',$request->get('batch_num'))->field('uid')->find();
        $senderInfo=CustomsCommonShipperinfoModel::where('uid',$uid->uid)->select();
        $senderSel='<select><option>请选择</option>';
        foreach ($senderInfo as $value){
            $senderSel.='<option value="'.$value->id.'">'.$value->shipper_name.'</option>';
        }
        $senderSel.='</select>';
        return view('elec/edit_order_sender',['list'=>$list,'senderSel'=>$senderSel]);
    }

    public function saveEditOrderSender(Request $request)
    {
        if (!is_numeric($request->post('sid'))){
            return json(['code'=>1,'msg'=>'参数错误']);
        }
        $senderInfo=CustomsCommonShipperinfoModel::where('id',$request->post('sid'))->find();
        CustomsElecOrderDetailModel::where('EntOrderNo',$request->post('oid'))->update([
            'senderName'=>$senderInfo->shipper_name,
            'senderTel'=>$senderInfo->shipper_tel,
            'senderAddr'=>$senderInfo->shipper_address,
            'senderCountry'=>$senderInfo->shipper_country,
            'senderProvincesCode'=>$senderInfo->shipper_pcd,
        ]);
        return json(['code'=>0,'msg'=>'更新成功']);
    }

    /**2021.08.13新增**/
    //国内收款账户管理
    public function mainlandAccount(Request $request){
        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();

            $search = input('search');
            if($search)
            {
                $map['a.account_name'] = ['like','%'.$search.'%'];
            }
            $list = Db::name('decl_user_account a')
                ->join('decl_user u','a.uid=u.id','left')
                ->where('a.account_type2=4 OR a.account_type2=5 OR a.account_type2=6 OR a.account_type2=7')
                ->where($map)
                ->field('a.id,a.account_type2,a.money_type,a.account_name,a.bank_name,a.account,a.bind_time,a.status,u.user_name,u.user_tel')
                ->order($order)
                ->limit($limit)
                ->select();
            foreach ($list as $k => $v) {
                //状态
                if($v['status']==0){
                    $list[$k]['status'] = '待审核';
                }else if($v['status']==1){
                    $list[$k]['status'] = '已审核';
                }else if($v['status']==2){
                    $list[$k]['status'] = '审核不通过';
                }
                //账户类型
                if($v['account_type2']==4){
                    $list[$k]['account_type2'] = '商户私户';
                }elseif($v['account_type2']==5){
                    $list[$k]['account_type2'] = '商户公户';
                }elseif($v['account_type2']==6){
                    $list[$k]['account_type2'] = '往来私户';
                }elseif($v['account_type2']==7){
                    $list[$k]['account_type2'] = '往来公户';
                }

                $list[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".')" class="btn btn-primary btn-xs">审核</button>';
            }
            $total = Db::name('decl_user_account')->where('account_type2=4 OR account_type2=5 OR account_type2=6 OR account_type2=7')->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view('elec/mainland_account',['title'=>'国内收款账户管理']);
        }
    }

    //国内收款账户管理-审核
    public function mainlandAccountEdit(Request $request){
        if($request->isPost() || $request->isAjax()){
            $data = input();
            if($data['status']==1){
                $data['check_remark'] = '';//通过就清除原因
            }
            $res = Db::name('decl_user_account')->where(['id'=>$data['id']])->update([
                'status'=>$data['status'],
                'check_remark'=>$data['check_remark']
            ]);
            if($res){
                return json(['code'=>1,'msg'=>'审核成功']);
            }else{
                return json(['code'=>0,'msg'=>'审核失败']);
            }
        }else{
            $id = $request->get('id');
            $data = Db::name('decl_user_account')->where(['id'=>$id])->find();
            $data['intercourse_file'] = explode(',',$data['intercourse_file']);

            $this->assign('data',$data);
            return view('elec/mainland_account_edit',['title'=>'账户配置审核']);
        }
    }

    //离岸收款账户管理
    public function offshoreAccount(Request $request){
        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();

            $search = input('search');
            if($search)
            {
                $map['a.account_name'] = ['like','%'.$search.'%'];
            }
            $list = Db::name('decl_user_account a')
                ->join('decl_user u','a.uid=u.id','left')
                ->where('a.account_type2=9')
                ->where($map)
                ->field('a.id,a.account_type2,a.money_type,a.account_name,a.bank_name,a.account,a.bind_time,a.status,u.user_name,u.user_tel,a.open_other')
                ->order($order)
                ->limit($limit)
                ->select();
            foreach ($list as $k => $v) {
                //状态
                if($v['status']==0){
                    $list[$k]['status'] = '待审核';
                }else if($v['status']==1){
                    $list[$k]['status'] = '已审核';
                }else if($v['status']==2){
                    $list[$k]['status'] = '审核不通过';
                }
                //账户类型
                $list[$k]['account_type2'] = '商户离岸账户';
                //离岸账户
                if($v['open_other']==1){
                    $list[$k]['open_other'] = '已有账户';
                }else if($v['open_other']==2){
                    $list[$k]['open_other'] = '未有账户';
                }
                
                $list[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".')" class="btn btn-primary btn-xs">审核</button>';
            }
            $total = Db::name('decl_user_account')->where('account_type2=9')->where($map)->count();
            
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view('elec/offshore_account',['title'=>'离岸收款账户管理']);
        }
    }

    //离岸收款账户管理-审核
    public function offshoreAccountEdit(Request $request){
        if($request->isPost() || $request->isAjax()){
            $data = input();
            if($data['status']==1){
                $data['check_remark'] = '';//通过就清除原因
            }
            $res = Db::name('decl_user_account')->where(['id'=>$data['id']])->update([
                'status'=>$data['status'],
                'check_remark'=>$data['check_remark']
            ]);
            if($res){
                return json(['code'=>1,'msg'=>'审核成功']);
            }else{
                return json(['code'=>0,'msg'=>'审核失败']);
            }
        }else{
            $id = $request->get('id');
            $data = Db::name('decl_user_account')->where(['id'=>$id])->find();
            $data['intercourse_file'] = explode(',',$data['intercourse_file']);
            $this->assign('data',$data);
            return view('elec/offshore_account_edit',['title'=>'账户配置审核']);
        }
    }
    
    //买卖管理-买卖配置审批
    public function buysellApprove(Request $request){
        if($request->isPost() || $request->isAjax()){
            if(input('type')==1){
                $list = getBuyerFormUser(); 
            }else if(input('type')==2){
                $list = getSellerFormUser();
            }
       
            return json(["status" => 0, "message" => "","rows" => $list]);
        }else{
            return view('elec/buysell_approve',['title'=>'买卖配置审批']);
        }
    }
    
    //买卖管理-买卖配置审批-审核
    public function buysellApproveEdit(Request $request){
        if($request->isPost() || $request->isAjax()){
            $data = input();
            $type = $data['type'];
            $id = $data['id'];
            if($data['status']==1){
                $data['check_remark'] = '';//通过就清除原因
            }
            
            if($type==1){
                $res = Db::name('decl_user_enterprise_buyer')->where('id',$id)->update([
                        'status'=>$data['status'],
                        'check_remark'=>$data['check_remark']
                    ]);
            }else if($type==2){
                $res = Db::name('decl_user_personal_buyer')->where('id',$id)->update([
                        'status'=>$data['status'],
                        'check_remark'=>$data['check_remark']
                    ]);
            }else if($type==3){
                $res = Db::name('decl_user_enterprise_seller')->where('id',$id)->update([
                        'status'=>$data['status'],
                        'check_remark'=>$data['check_remark']
                    ]);
            }else if($type==4){
                $res = Db::name('decl_user_personal_seller')->where('id',$id)->update([
                        'status'=>$data['status'],
                        'check_remark'=>$data['check_remark']
                    ]);
            }
            
            if($res){
                return json(['code'=>1,'msg'=>'审核成功']);
            }else{
                return json(['code'=>0,'msg'=>'审核失败']);
            }
        }else{
            $id = $request->get('id');
            $type = $request->get('type');
            
            if($type==1){
                $data = Db::name('decl_user_enterprise_buyer')->where('id',$id)->find();
            }else if($type==2){
                $data = Db::name('decl_user_personal_buyer')->where('id',$id)->find();
            }else if($type==3){
                $data = Db::name('decl_user_enterprise_seller')->where('id',$id)->find();
            }else if($type==4){
                $data = Db::name('decl_user_personal_seller')->where('id',$id)->find();
            }
            
            if(!empty($data['company_file'])){
                $data['company_file'] = explode(',',$data['company_file']);    
            }else if(!empty($data['contract_file']) || !empty($data['inquiry_file'])){
                $data['contract_file'] = explode(',',$data['contract_file']);    
                $data['inquiry_file'] = explode(',',$data['inquiry_file']);    
            }else if(!empty($data['diy_file'])){
                $data['diy_file'] = explode(',',$data['diy_file']);
//                foreach($data['diy_file'] as $kk=>$vv){
//
//                }
            }
            
            $this->assign('data',$data);
            return view('elec/buysell_approve_edit',['title'=>'买卖配置审核','type'=>$type]);
        }
    }

    //交易管理-单证主体关联审批
    public function documentApprove(Request $request){
        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();

            $search = input('search');
            if($search)
            {
                $map['a.account_name'] = ['like','%'.$search.'%'];
            }
            $list = Db::name('decl_user_entrust_trade_other a')
                ->join('decl_user u','a.uid=u.id','left')
                ->where('a.uid','<>',0)
                ->where($map)
                ->field('a.id,a.createtime,a.status,u.user_name,u.user_tel,a.ordersn')
                ->order($order)
                ->limit($limit)
                ->select();
            foreach ($list as $k => $v) {
                //状态
                if($v['status']==0){
                    $list[$k]['status'] = '待审核';
                }else if($v['status']==1){
                    $list[$k]['status'] = '双方主体一致';
                }else if($v['status']==2){
                    $list[$k]['status'] = '双方主体异常';
                }

                $list[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".')" class="btn btn-primary btn-xs">审核</button>';
            }
            $total = Db::name('decl_user_entrust_trade_other')->where('uid','<>',0)->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view('elec/document_approve',['title'=>'单证主体关联审批']);
        }
    }

    //交易管理-单证主体关联审批审核
    public function documentApproveEdit(Request $request){
        if($request->isPost() || $request->isAjax()){
            $data = input();
            if($data['status']==1){
                $data['need_upload'] = '';
            }
            $res = Db::name('decl_user_entrust_trade_other')->where(['id'=>$data['id']])->update([
                'status'=>$data['status'],
                'need_upload'=>$data['need_upload']
            ]);
            if($res){
                return json(['code'=>1,'msg'=>'审核成功']);
            }else{
                return json(['code'=>0,'msg'=>'审核失败']);
            }
        }else{
            $id = $request->get('id');
            $data = Db::name('decl_user_entrust_trade_other')->where(['id'=>$id])->find();
            if(!empty($data['shop_order_file'])){
                $data['shop_order_file'] = explode(',',$data['shop_order_file']);
            }
            if(!empty($data['inquiry_record_file'])){
                $data['inquiry_record_file'] = explode(',',$data['inquiry_record_file']);
            }
            if(!empty($data['trade_contract_file'])){
                $data['trade_contract_file'] = explode(',',$data['trade_contract_file']);
            }
            if(!empty($data['public_service_file'])){
                $data['public_service_file'] = explode(',',$data['public_service_file']);
            }
            if(!empty($data['single_window_file'])){
                $data['single_window_file'] = explode(',',$data['single_window_file']);
            }
            if(!empty($data['decl_msg_file'])){
                $data['decl_msg_file'] = explode(',',$data['decl_msg_file']);
            }
            if(!empty($data['logistics_manifest_file'])){
                $data['logistics_manifest_file'] = explode(',',$data['logistics_manifest_file']);
            }
            if(!empty($data['logistics_lad_file'])){
                $data['logistics_lad_file'] = explode(',',$data['logistics_lad_file']);
            }
            if(!empty($data['express_file'])){
                $data['express_file'] = explode(',',$data['express_file']);
            }
            if(!empty($data['warehouse_file'])){
                $data['warehouse_file'] = explode(',',$data['warehouse_file']);
            }
            if(!empty($data['entrust_purchase_goods_file'])){
                $data['entrust_purchase_goods_file'] = explode(',',$data['entrust_purchase_goods_file']);
            }
            if(!empty($data['entrust_consign_goods_file'])){
                $data['entrust_consign_goods_file'] = explode(',',$data['entrust_consign_goods_file']);
            }
            if(!empty($data['entrust_decl_customs_file'])){
                $data['entrust_decl_customs_file'] = explode(',',$data['entrust_decl_customs_file']);
            }

            $this->assign('data',$data);
            return view('elec/document_approve_edit',['title'=>'单证主体关联审核']);
        }
    }

    //交易管理-平台订单关联审批
    public function shoporderApprove(Request $request){
        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();

            $search = input('search');
            if($search)
            {
                $map['a.ordersn'] = ['like','%'.$search.'%'];
            }
            $list = Db::name('decl_user_trade_platform a')
                ->join('sz_yi_order o','o.trade_id=a.id','left')
                ->join('decl_user u','a.uid=u.id','left')
                ->where($map)
                ->whereOr('a.status',0)
                ->whereOr('a.status',2)
                ->field('a.good_ids,a.id,a.ordersn,a.create_at,a.status,a.logistics_status,a.buyer_type,a.buyer_id,o.ordersn as shopordersn,u.user_name,u.user_tel')
                ->order($order)
                ->limit($limit)
                ->select();
            foreach ($list as $k => $v) {
                //状态
                if($v['status']==0){
                    $list[$k]['status'] = '待审核';
                }else if($v['status']==1){
                    $list[$k]['status'] = '已通过';
                }else if($v['status']==2){
                    $list[$k]['status'] = '不通过';
                }
                //买家
                if($v['buyer_type']==1){
                    $dat = Db::name('decl_user_enterprise_buyer')->where('id',$v['buyer_id'])->field(['company_name as name,company_tel as tel'])->find();
                    $list[$k]['name'] = $dat['name'];
                    $list[$k]['tel'] = $dat['tel'];
                }else if($v['buyer_type']==2){
                    $dat = Db::name('decl_user_personal_buyer')->where('id',$v['buyer_id'])->field(['first_name,last_name,tel'])->find();
                    $list[$k]['name'] = $dat['first_name'].$dat['last_name'];
                    $list[$k]['tel'] = $dat['tel'];
                }

                $list[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".')" class="btn btn-primary btn-xs">审核</button>';
            }
            $total = Db::name('decl_user_trade_platform')->whereOr('status',0)->whereOr('status',2)->where($map)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else{
            return view('elec/shoporder_approve',['title'=>'平台订单关联审批']);
        }
    }

    //交易管理-平台订单关联审批审核
    public function shoporderApproveEdit(Request $request){
        if($request->isPost() || $request->isAjax()){
            $data = input();
            if($data['status']==1){
                $data['check_remark'] = '';
            }
            $res = Db::name('decl_user_trade_platform')->where(['id'=>$data['id']])->update([
                'status'=>$data['status'],
                'check_remark'=>$data['check_remark']
            ]);
            if($res){
                return json(['code'=>1,'msg'=>'审核成功']);
            }else{
                return json(['code'=>0,'msg'=>'审核失败']);
            }
        }else{
            $id = $request->get('id');
            $data = Db::name('decl_user_trade_platform a')
                ->join('sz_yi_order o','o.trade_id=a.id','left')
                ->join('decl_user u','a.uid=u.id','left')
                ->where('a.id','=',$id)
                ->field('a.good_ids,a.status,a.check_remark,a.id')
                ->find();

            if(!empty($data['good_ids'])){
                $goods = explode(',',$data['good_ids']);
                foreach($goods as $k => $v){
                    $data['goods'][$k] = Db::name('sz_yi_goods')->where('id',$v)->field('title,id,uniacid')->find();
                }
            }

            $this->assign('data',$data);
            return view('elec/shoporder_approve_edit',['title'=>'平台订单关联审核']);
        }
    }

    //账户管理-离岸账户文件管理
    public function offshoreAccountDocument(Request $request){
        if($request->isPost() || $request->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();

//            $search = input('search');
//            if($search)
//            {
//                $map['a.ordersn'] = ['like','%'.$search.'%'];
//            }
            $list = Db::name('kvb_client_upload_document a')
                    ->join('decl_user u','a.uid=u.id','left')
                    ->where('a.id','>',0)
                    ->field('a.id,a.status,a.create_at,u.user_name,u.user_tel,a.type')
                    ->order($order)
                    ->limit($limit)
                    ->select();
            foreach ($list as $k => $v) {
                //状态
                if ($v['status'] == 0) {
                    $list[$k]['status'] = '待审核';
                } else if ($v['status'] == 1) {
                    $list[$k]['status'] = '后台已审核，第三方审查';
                } else if ($v['status'] == 3) {
                    $list[$k]['status'] = '已通过';
                } else if ($v['status'] == 2) {
                    $list[$k]['status'] = '不通过';
                }
                if ($v['type'] == 2) {
                    $list[$k]['type'] = '线下开户';
                }else{
                    $list[$k]['type'] = '线上开户';
                }
                $list[$k]['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                $list[$k]['manage'] = '<button type="button" onclick="Edit('."'".$v['id']."'".')" class="btn btn-primary btn-xs">审核</button>';
            }
            $total = Db::name('kvb_client_upload_document')->where('id','>',0)->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);

        }else{
            return view('elec/offshore_account_document',['title'=>'离岸账户文件管理']);
        }
    }

    //账户管理-离岸账户文件审批
    public function offshoreAccountDocumentEdit(Request $request){
        if($request->isPost() || $request->isAjax()){
            $data = input();
            if($data['status']==1){
                $data['check_remark'] = '';

                //调用接口，开始提交kvb AML check
                $dom_info = Db::name('kvb_client_upload_document')->where(['id'=>$data['id']])->find();
                $post = postJson('http://declare.gogo198.cn/api/kvb/upload_document',$dom_info);
                print_r($post);die;
            }

            $res = Db::name('kvb_client_upload_document')->where(['id'=>$data['id']])->update([
                'status'=>$data['status'],
                'check_remark'=>$data['check_remark']
            ]);
            if($res){

                return json(['code'=>1,'msg'=>'审核成功']);
            }else{
                return json(['code'=>0,'msg'=>'审核失败']);
            }
        }else{
            $id = $request->get('id');
            $data = Db::name('kvb_client_upload_document a')
                ->join('decl_user u','a.uid=u.id','left')
                ->where('a.id','=',$id)
                ->field('a.post_data,a.status,a.check_remark,a.id,a.type,a.company_name,a.company_name_en,a.zip_url')
                ->find();

            $data['post_data'] = json_decode($data['post_data'],true);
            $data['post_data']['business_file'] = explode(',',$data['post_data']['business_file']);
            $data['post_data']['business_association'] = explode(',',$data['post_data']['business_association']);
            $data['post_data']['corporate_idcard'] = explode(',',$data['post_data']['corporate_idcard']);
            $data['post_data']['shareholder_idcard'] = explode(',',$data['post_data']['shareholder_idcard']);
            $data['post_data']['transaction_authorizer_idcard'] = explode(',',$data['post_data']['transaction_authorizer_idcard']);
            $data['post_data']['tax_payment_certificate'] = explode(',',$data['post_data']['tax_payment_certificate']);
            $data['post_data']['monthly_bank_statement'] = explode(',',$data['post_data']['monthly_bank_statement']);
            $data['post_data']['va_application'] = explode(',',$data['post_data']['va_application']);
            $data['post_data']['service_agreement'] = explode(',',$data['post_data']['service_agreement']);
            $data['post_data']['search_repord'] = explode(',',$data['post_data']['search_repord']);
            $data['post_data']['certificate'] = explode(',',$data['post_data']['certificate']);
            $data['post_data']['registration_certificate'] = explode(',',$data['post_data']['registration_certificate']);
            $data['post_data']['shareholder_idcard_address_info'] = explode(',',$data['post_data']['shareholder_idcard_address_info']);

            //上传规则
            $need_upload_document = Db::name('kvb_client_need_upload_document')->where('type',$data['type'])->field(['need_upload_document'])->find();
            $need_upload_document['need_upload_document'] = explode(',',$need_upload_document['need_upload_document']);
            $qxb_company_info = '';
            if($data['company_name']){
                $qxb_company_info = Db::name('enterprise_basicinfo')->where('name',$data['company_name'])->find();
            }
            $this->assign('need_upload_document',$need_upload_document);
            $this->assign('data',$data);
            $this->assign('qxb_company_info',$qxb_company_info);
            return view('elec/offshore_account_document_edit',['title'=>'离岸账户文件审核']);
        }
    }

    //账号管理-离岸账户文件上传管理
    public function offshoreAccountDocumentManage(Request $request){
        $dat = input();
        if($request->isPost() || $request->isAjax()) {
            $dat['need_upload_document'] = substr($dat['need_upload_document'],1);
            $res = Db::name('kvb_client_need_upload_document')->where('type',$dat['type'])->update(['need_upload_document'=>$dat['need_upload_document']]);
            if($res){
                return json(['code'=>1,'msg'=>'修改成功']);
            }else{
                return json(['code'=>0,'msg'=>'修改失败']);
            }
        }else{
            $data = Db::name('kvb_client_need_upload_document')->where('type',$dat['type'])->find();
            $data['need_upload_document'] = explode(',',$data['need_upload_document']);
            $this->assign('data',$data);
            $this->assign('type',$dat['type']);
            return view('elec/offshore_account_document_manage',['title'=>'离岸账户文件上传管理']);
        }
    }

    //2021-10-28
    //设备管理
    public function device(Request $request){
        $total = Db::name('hjxssl_device')->count('id');
        $data = Db::name('hjxssl_device')
            ->order('createtime','desc')
            ->paginate(20,$total,[
                'type' =>'Layui',
                'query'=>['s'=>'admin/elec/device'],
                'var_page'=>'page',
                'newstyle'=>true
            ]);
        $page = $data->render();
        $list = $data->toArray()['data'];
        foreach($list as $k=>$v){
            if($v['type']==1){
                $list[$k]['type'] = '汉印打印机';
            }
        }

        return view('elec/device',['title'=>'设备管理','data'=>$list,'page'=>$page]);
    }

    public function addDevice(Request $request){
        $dat = input();
        if($request->isPost() || $request->isAjax()) {

            if($dat['id']>0){
                $id = $dat['id'];
                unset($dat['id']);
                $dat['address']=trim($dat['address']);
                $dat['device_info']=trim($dat['device_info']);

                $res = Db::name('hjxssl_device')->where('id',$id)->update($dat);
                if($res){
                    return json(['code'=>1,'msg'=>'修改成功']);
                }else{
                    return json(['code'=>0,'msg'=>'修改失败']);
                }
            }else{
                unset($dat['id']);
                $dat['createtime'] = time();
                $dat['address']=trim($dat['address']);
                $dat['device_info']=trim($dat['device_info']);

                $res = Db::name('hjxssl_device')->insert($dat);
                if($res){
                    return json(['code'=>1,'msg'=>'添加成功']);
                }else{
                    return json(['code'=>0,'msg'=>'添加失败']);
                }
            }
        }else{
            if(isset($dat['id'])){
                if($dat['id']>0){
                    $data = Db::name('hjxssl_device')->where('id',$dat['id'])->find();
                    $data['device_info'] = explode(',',$data['device_info']);
                    $this->assign('data',$data);
                }
            }

            return view('elec/add_device',['title'=>'添加设备']);
        }
    }

    public function delDevice(Request $request){
        $dat = input();
        if($dat['id']>0){
            $res = Db::name('hjxssl_device')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code'=>1,'msg'=>'删除成功']);
            }else{
                return json(['code'=>0,'msg'=>'删除失败']);
            }
        }
    }

    //快递公司
    public function express(Request $request){
        $total = Db::name('hjxssl_express')->count('id');
        $data = Db::name('hjxssl_express')
            ->order('createtime','desc')
            ->paginate(20,$total,[
                'type' =>'Layui',
                'query'=>['s'=>'admin/elec/express'],
                'var_page'=>'page',
                'newstyle'=>true
            ]);
        $page = $data->render();
        $list = $data->toArray()['data'];
        foreach($list as $k=>$v){
            if(!empty($v['code'])){
                $list[$k]['code_name'] = Db::name('customs_express_company_code')->where('code',$v['code'])->field('name')->find()['name'];
            }
        }

        return view('elec/express',['title'=>'快递公司管理','data'=>$list,'page'=>$page]);
    }

    public function addExpress(Request $request){
        $dat = input();
        if($request->isPost() || $request->isAjax()) {

            if($dat['id']>0){
                $id = $dat['id'];
                unset($dat['id']);
                $dat['code']=trim($dat['code']);
                $dat['temp_id']=trim($dat['temp_id']);
                $dat['api_address']=trim($dat['api_address']);

                $res = Db::name('hjxssl_express')->where('id',$id)->update($dat);
                if($res){
                    return json(['code'=>1,'msg'=>'修改成功']);
                }else{
                    return json(['code'=>0,'msg'=>'修改失败']);
                }
            }else{
                unset($dat['id']);
                $dat['createtime'] = time();
                $dat['code']=trim($dat['code']);
                $dat['temp_id']=trim($dat['temp_id']);
                $dat['api_address']=trim($dat['api_address']);

                $res = Db::name('hjxssl_express')->insert($dat);
                if($res){
                    return json(['code'=>1,'msg'=>'添加成功']);
                }else{
                    return json(['code'=>0,'msg'=>'添加失败']);
                }
            }
        }else{
            if(isset($dat['id'])){
                if($dat['id']>0){
                    $data = Db::name('hjxssl_express')->where('id',$dat['id'])->find();
                    $this->assign('data',$data);
                }
            }
            $express = Db::name('customs_express_company_code')->order('id','desc')->select();
            return view('elec/add_express',['title'=>'添加快递公司','express'=>$express]);
        }
    }

    public function delExpress(Request $request){
        $dat = input();
        if($dat['id']>0){
            $res = Db::name('hjxssl_express')->where('id',$dat['id'])->delete();
            if($res){
                return json(['code'=>1,'msg'=>'删除成功']);
            }else{
                return json(['code'=>0,'msg'=>'删除失败']);
            }
        }
    }

}


?>
