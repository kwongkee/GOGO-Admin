<?php

namespace app\admin\controller;
use think\Request;
use think\Validate;
use think\Db;

/**
 * Class Packages
 * @package app\admin\controller
 * 商户计费配置
 */
class Charging extends Auth
{
    public $req;

    public function __construct(Request $request)
    {
        $this->req = $request;
    }

    /**
     * 开发思路：
     * 1、显示配置列表页面，
     * 2、点击配置，唤起配置页面；（选择公众号，商户）
     * 3、点击确认，添加配置；
     * 4、编辑配置，删除；
     *
    /*
     * 包材供应企业
     */
    public function index()
    {
        $config = [
            'type' => 'Layui',
            'query' => ['s' => 'admin/charging/index'],//参数添加
            'var_page' => 'page',
            'newstyle' => true,
        ];

        $data = null;
        // 链表查询数据
        $data = DB::name('customs_charging')->paginate(12, false, $config);

        return view('cross/charging/index', [
            'data' => $data->toArray(),
            'page' => $data->render(),
            'title' => '商户计费配置'
        ]);
    }



    // 添加
    public function add()
    {
        return view('cross/charging/add', [
            'charging'  =>  $this->getCharging2(),
            'account'   =>  $this->getAccount(),
            'logics'    =>  $this->getLogics(),
            'express'   =>  $this->getExpress(),
        ]);
    }

    // 编辑
    public function edit()
    {
        $id = $this->req->get('id');

        $data = Db::name('customs_charging')->where('id',$id)->find();
        // $tmpArr = [];
        // if(!empty($data['merid'])) {
        //     $merArr = explode(',',$data['merid']);
        //     $merArr = array_filter($merArr);
        //
        //     if(is_array($merArr)) {
        //         foreach ($merArr as $v){
        //             $tmpArr[$v] = $v;
        //         }
        //         unset($merArr);
        //     }
        // }
        $serviceFun = $data['merid'];
        $data['merid']= json_decode($data['merid'],true);
        return view('cross/charging/edit', [
            // 'tmpArr'    =>  $data,
            'charging'  =>  $this->getCharging2(),
            'account'   =>  $this->getAccount(),
            'logics'    =>  $this->getLogics(),
            'express'   =>  $this->getExpress(),
            'data'      =>  $data,
            'service_func'=>$serviceFun
        ]);
    }


    // 添加操作
    public function doAdd() {

        $data = $this->req->post();

        if(empty($data)) {
            return json_encode(['code'=>0,'msg'=>'提交数据不能为空']);
        }

        $publicId  = (int)$data['publicId'];
        $merchatId = (int)$data['merchatId'];
        // $merchatIds = $data['merchatIds'];

        // 添加操作
        if($data['Method'] == 'add') {
            // 检验公众号下的商户是否已经配置；
            $isConfig = Db::name('customs_charging')->where(['publicId'=>$publicId,'merchatId'=>$merchatId])->field('id')->find();
            if(!empty($isConfig)) {
                return json_encode(['code'=>0,'msg'=>'该商户已配置，请勿重复配置！']);
            }
            $data['serviceConf']=json_decode($data['serviceConf'],true);
            $serviceFunc =[];
            foreach ($data['serviceConf'] as $key=>$item){
                // var_dump($item);
                $serviceFunc[$item['mid']][]=[$item['keys']=>$item['val']];
            }
            $serviceFunc = json_encode($serviceFunc);
            // 写入
            $ins = [
                'publicId'=>$publicId,
                'merchatId'=>$merchatId,
                'merid'=>$serviceFunc,
                // // 物流收费
                // 'expressId'=>isset($data['expressId']) ? trim($data['expressId']):'',
                // 'isLogisFee'=>isset($data['isLogisFee']) ? trim($data['isLogisFee']):'',
                // 'logisPayer'=>isset($data['logisPayer']) ? trim($data['logisPayer']):'',
                // 'logisId'=>isset($data['logisId']) ? trim($data['logisId']):'',
                //
                // // 快递收费
                // 'isExpressFee'=>isset($data['isExpressFee']) ? trim($data['isExpressFee']):'',
                // 'expressPayer'=>isset($data['expressPayer']) ? trim($data['expressPayer']):'',
                // 'isPackageFee'=>isset($data['isPackageFee']) ? trim($data['isPackageFee']):'',
                // 'packagePayer'=>isset($data['packagePayer']) ? trim($data['packagePayer']):'',
                // 'packageMoney'=>isset($data['packageMoney']) ? trim($data['packageMoney']):'',
                //
                // // 快递投保
                // 'isPremium'=>isset($data['isPremium']) ? trim($data['isPremium']):'',
                // 'isRatio'=>isset($data['isRatio']) ? trim($data['isRatio']):'',
                // 'ratioMoney'=>isset($data['ratioMoney']) ? trim($data['ratioMoney']):'',
                // 'bili'=>isset($data['bili']) ? $data['bili'] : '',
                // 'expMoney' => $data['expMoney'] >0 ? ($data['expMoney'] / 100) : '',
                // 'shopMoney'=> $data['shopMoney'] >0 ? ($data['shopMoney'] / 100) : '',
                //
                // // 清关计费
                // 'isShut'=>isset($data['isShut']) ? trim($data['isShut']):'',
                // 'payers'=>isset($data['payers']) ? trim($data['payers']):'',
                // 'payersFee'=>isset($data['payersFee']) ? trim($data['payersFee']):'',// 计费方式：1商品价款比例、2包裹订单定额
                // // 如果清关收费；并且按商品价款比例；就转换成百分比；否则按定额
                // 'payFee'=> ($data['isShut']==1 && $data['payersFee']==1) ? ($data['payFee'] / 100) : $data['payFee'],
                //
                //
                // 'isTax'=>isset($data['isTax']) ? trim($data['isTax']):'',
                // 'taxPay'=>isset($data['taxPay']) ? trim($data['taxPay']):'',
                // 'taxCross'=>isset($data['taxCross']) ? trim($data['taxCross']):'',
                // 'taxPerson'=>isset($data['taxPerson']) ? trim($data['taxPerson']):'',
                // 'carMoney'=>isset($data['carMoney']) ? trim($data['carMoney']):'',
                //
                //  // 商品计费
                // 'isShop'=>isset($data['isShop']) ? trim($data['isShop']):'',
                // 'ifFull'=>isset($data['ifFull']) ? trim($data['ifFull']):'',
                // 'fullFee'=> $data['ifFull'] ==1 ? ($data['fullFee'] / 100) : 0.00,
                // // 支付类型；1在线支付，2扫码支付；
                // 'payType'=>isset($data['payType']) ? trim($data['payType']):'',
                'createTime'=>time(),
            ];

            $inr = Db::name('customs_charging')->insertGetId($ins);
            if(!$inr) {
                return json_encode(['code'=>0,'msg'=>'商户配置失败，请稍后重试！']);
            }
            return json_encode(['code'=>1,'msg'=>'商户配置成功!']);
        } else if($data['Method'] == 'edit') {
            // 更新条件
            $id = trim($data['id']);
            $data['serviceConf']=json_decode($data['serviceConf'],true);
            $serviceFunc =[];
            foreach ($data['serviceConf'] as $key=>$item){
                // var_dump($item);
                $serviceFunc[$item['mid']][]=[$item['keys']=>$item['val']];
            }
            $serviceFunc = json_encode($serviceFunc);
            // 更新内容
            // 写入
            $upd = [
                'publicId'=>$publicId,
                'merchatId'=>$merchatId,
                'updateTime'=>time(),
                'merid'=>$serviceFunc
                // 'merid'=>$merchatIds,
                //
                // // 物流收费
                // 'expressId' =>isset($data['expressId']) ? trim($data['expressId']):'',
                // 'isLogisFee'=>isset($data['isLogisFee']) ? trim($data['isLogisFee']):'',
                // 'logisPayer'=>isset($data['logisPayer']) ? trim($data['logisPayer']):'',
                // 'logisId'   =>isset($data['logisId']) ? trim($data['logisId']):'',
                //
                // // 快递收费
                // 'isExpressFee'=>isset($data['isExpressFee']) ? trim($data['isExpressFee']):'',
                // 'expressPayer'=>isset($data['expressPayer']) ? trim($data['expressPayer']):'',
                // 'isPackageFee'=>isset($data['isPackageFee']) ? trim($data['isPackageFee']):'',
                // 'packagePayer'=>isset($data['packagePayer']) ? trim($data['packagePayer']):'',
                // 'packageMoney'=>isset($data['packageMoney']) ? trim($data['packageMoney']):'',
                //
                // // 快递投保
                // 'isPremium' =>isset($data['isPremium']) ? trim($data['isPremium']):'',
                // 'isRatio'   =>isset($data['isRatio']) ? trim($data['isRatio']):'',
                // 'ratioMoney'=>isset($data['ratioMoney']) ? trim($data['ratioMoney']):'',
                // 'bili'      =>isset($data['bili']) ? $data['bili'] : '',
                // 'expMoney'  => $data['expMoney'] >0 ? ($data['expMoney'] / 100) : '',
                // 'shopMoney' => $data['shopMoney'] >0 ? ($data['shopMoney'] / 100) : '',
                //
                // // 清关计费
                // 'isShut'    =>isset($data['isShut']) ? trim($data['isShut']):'',
                // 'payers'    =>isset($data['payers']) ? trim($data['payers']):'',
                // 'payersFee' =>isset($data['payersFee']),// 计费方式：1商品价款比例、2包裹订单定额
                // // 如果清关收费；并且按商品价款比例；就转换成百分比；否则按定额
                // 'payFee'=> ($data['isShut']==1 && $data['payersFee']==1) ? ($data['payFee'] / 100) : $data['payFee'],
                //
                // 'isTax'     =>isset($data['isTax']) ? trim($data['isTax']):'',
                // 'taxPay'    =>isset($data['taxPay']) ? trim($data['taxPay']):'',
                // 'taxCross'  =>isset($data['taxCross']) ? trim($data['taxCross']):'',
                // 'taxPerson' =>isset($data['taxPerson']) ? trim($data['taxPerson']):'',
                // 'carMoney'  =>isset($data['carMoney']) ? trim($data['carMoney']):'',
                //
                // // 商品计费
                // 'isShop'=>isset($data['isShop']) ? trim($data['isShop']):'',
                // 'ifFull'=>isset($data['ifFull']) ? trim($data['ifFull']):'',
                // 'fullFee'=>$data['ifFull'] ==1 ? ($data['fullFee'] / 100) : 0.00,
                // // 支付类型；1在线支付，2扫码支付；
                // 'payType'=>isset($data['payType']) ? trim($data['payType']):'',
               
            ];

            $up = Db::name('customs_charging')->where('id',$id)->update($upd);
            if($up) {
                return json_encode(['code'=>1,'msg'=>'编辑更新操作成功！']);
            }
            return json_encode(['code'=>0,'msg'=>'编辑更新操作失败！']);
        }

        return json_encode(['code'=>0,'msg'=>'暂无操作！']);
    }


    // 删除操作
    public function Del(){
        $id = $this->req->post('id');
        $del = Db::name('customs_charging')->where('id',$id)->delete();
        if(!$del){
            return json_encode(['code'=>0,'msg'=>'数据删除失败,请稍后重试!']);
        }
        return json_encode(['code'=>1,'msg'=>'数据删除成功!']);
    }


    // 获取所有计费商户
    private function getCharging() {
        return Db::name('customs_charging')->field('merchatId')->select();
    }

    private  function getCharging2(){
        return Db::name('sz_yi_perm_user')->field('uid,username')->select();
    }
    // 获取所有的公众号
    private function getAccount() {
        return Db::name('account_wechats')->field('uniacid,name')->select();
    }


    // 货物物流企业
    private function getLogics() {
        return Db::name('customs_logistics')->where('pid',0)->field('id,pid,logisticName')->select();
    }

    // 获取物流线路
    public function getWaybill() {
        $pid = $this->req->post('id');
        $res = Db::name('customs_logistics')->where('pid',$pid)->field('id,routeName')->select();
        if(!empty($res)) {
            return json_encode(['code'=>1,'msg'=>'获取成功','data'=>$res]);
        }
        return json_encode(['code'=>0,'msg'=>'获取失败','data'=>'noData']);
    }

    // 获取快递企业
    public function getExpress() {
        return Db::name('customs_express')->where('pid',0)->field('id,expName')->select();
    }
    
    
    /**
     * 获取服务商提供的服务
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getServiceProviderService()
    {
        $data = Db::name('customs_merchant_service')->whereIn('m_id',trim($this->req->get('m_id'),','))->select();
        return json(['code'=>0,'message'=>'完成','data'=>$data]);
    }

}