<?php

namespace app\index\controller;

use app\index\controller;
use think\Db;
use think\Request;
use think\Session;
use think\Validate;
use think\Loader;

class Cardvoucher extends CommonController
{
    
    //卡券添加页面
    public function CardvoucherAdd ( Request $request )
    {
        $CardvoucherModel = Loader::model('Cardvoucher', 'model');
        return view("cardvoucher/cardvoucheradd", ['title' => '商户卡券添加', 'businessList' => $CardvoucherModel->getBusiness(),]);
    }
    
    //卡券发布
    public function CardvoucherSave ( Request $request )
    {
        $CardvoucherModel = Loader::model('Cardvoucher', 'logic');
        try {
            $data = json_decode($request->post('data'), true);
            $CardvoucherModel->checkField($data)->inserCouponData($data);
            return json(['code' => 0, 'message' => '请等待审核']);
        } catch (\Exception $e) {
            return json(['code' => -1, 'message' => $e->getMessage()]);
        }
    }
    
    //卡券管理
    public function CardvoucherManage ( Request $request )
    {
        return view("cardvoucher/cardvouchermanage", ['title' => '商户卡券管理',]);
    }
    
    //ajax请求返回
    public function get_coupon_list ( Request $request )
    {
        $app_status       = ['完成', '未提交', '已提交', '已关闭'];
        $check_status     = ['待审核', '已通过', '拒绝'];
        $c_status         = ['1' => '有效', '2' => '核销', '3' => '失效'];
        $pl_sett          = ['未结算', '已结算'];
        $pay_status       = ['未支付', '已支付', '支付失败'];
        $issued_type      = ['等待审核', '按发行金额X%', '按发行数量：Y元/张', '收取一次性费用'];
        $use_type         = ['1' => '主动使用', '2' => '被动使用'];
        $CardvoucherModel = Loader::model('Cardvoucher', 'model');
        $page             = ((int)$request->get('page') - 1) * (int)$request->get('limit');
        $list             = $CardvoucherModel->getList(['uniacid' => Session::get('UserResutlt.uniacid')], $page, $request->get('limit'));
        $total            = $CardvoucherModel->get_total('parking_coupon', ['uniacid' => Session::get('UserResutlt.uniacid')], 'id');
        foreach ($list as $key => &$value) {
            $value['apply_status']  = $app_status[$value['apply_status']];
            $value['check_status']  = $check_status[$value['check_status']];
            $value['c_status']      = $c_status[$value['c_status']];
            $value['platform_sett'] = $pl_sett[$value['platform_sett']];
            $value['pay_status']    = $pay_status[$value['pay_status']];
            $value['issued_type']   = $issued_type[$value['issued_type']];
            $value['use_type']      = $use_type[$value['use_type']];
            $value['create_time']   = date('Y-m-d H:i:s', $value['create_time']);
            $value['s_time']        = date('Y-m-d H:i:s',$value['s_time']);
            $value['e_time']        = date('Y-m-d H:i:s',$value['e_time']);
        }
        return json(['code' => 0, 'msg' => '', 'count' => $total, 'data' => $list]);
    }
    
    
    public function CardvoucherCheck ( Request $request )
    {
        return view("cardvoucher/cardvouchercheck", ['title' => '商户卡券验核',]);
    }
    
    
    public function payCoupon ( Request $request )
    {
        dump($request->post());
    }
}

?>