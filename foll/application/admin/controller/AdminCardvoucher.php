<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Request;
use think\Session;
use think\Validate;
use think\Loader;

class AdminCardvoucher extends Auth
{


    //卡券审核
    public function AdminCardvoucherCheck()
    {
        $check_status          = ['待审核', '已通过', '已拒绝'];
        $pay_status            = ['未支付', '已支付', '支付失败'];
        $issued_type           = ['等待审核', '按发行金额X%', '按发行数量：Y元/张', '收取一次性费用'];
        $AdminCardvoucherModel = Loader::model('AdminCardvoucher', 'model');
        $total                 = $AdminCardvoucherModel->getTableCount('foll_coupon');
        $list                  = $AdminCardvoucherModel->getCardvoucherList($total, 15);
        return view("cardvoucher/cardvouchercheck", [
            'title'           => '卡券审核',
            'CardvoucherList' => $list,
            'page'            => $list->render(),
            'list'            => $list->toArray(),
            'check'           => $check_status,
            'payStatus'       => $pay_status,
            'issued'          => $issued_type,
            'total'           => $total,
        ]);
    }


    //卡券审核不通过
    public function CardvoucherPassOrReject(Request $request)
    {
        $clogic = Loader::model('AdminCardvoucher', 'model');
        if ($request->isGET()) {
            if (empty($request->get('id')) && empty($request->get('check_status'))) {
                $this->error("处理异常", Url("admincardvoucher/check"));
            }
            $clogic->updateCheckStatus($request->get('id'), $request->get('check_status'));
            $this->success("审核结果完成", Url("admincardvoucher/check"));
        }

        if (!$request->isPOST() && empty($request->post('type'))) {
            $this->error("审核失败", Url("admincardvoucher/check"));
        }

        if ($request->post('id') == '') {
            $this->error("审核失败", Url("admincardvoucher/check"));
        }

        try {
            $bid = $clogic->getBusinIdById($request->post('id'))['busin_id'];
            $clogic->passOrReject([
                'coupon_id'    => $request->post('id'),
                'issued_type'  => $request->post('type'),
                'issued_price' => $request->post('money'),
                'busin_id'     => $bid,
            ]);
            $clogic->updateCheckStatus($request->post('id'), 1);
        } catch (\Exception $e) {
            $this->error("处理异常" . $e->getMessage(), Url("admin/index"));
        }

        $this->success("审核成功", Url("admin/index"));

    }


}
