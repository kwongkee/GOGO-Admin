<?php

namespace app\coupon\controller;


use Illuminate\Contracts\View\Factory;
use Illuminate\View\View;
use think\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\Request;

class Coupon extends Base
{
    protected $couponModel;
    protected $commonModel;

    public function __construct()
    {
        parent::__construct();
        $this->couponModel = model('Coupon', 'model');
        $this->commonModel = model('Common', 'model');
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function coupon_manage_list()
    {
        return view('coupon/index', ['user' => Session('business.user_name')]);
    }
    
    
    /**
     * 显示创建资源表单页
     * @return Factory|View|\think\response\View
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function create()
    {
        $publicAccoount = $this->commonModel->getAllWechatsAccount();
        $suid = Db::name('decl_user')->where(['tid'=>Session('business.tid')])->field('supplier')->find();
        $serList = Db::name('customs_merchant_service')->where('m_id',$suid['supplier'])->field(['id','service_name'])->select();
        return view('coupon/create',compact('publicAccoount','serList'));
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        $data = $request->post();
        unset($data['input-select'], $data['input-select2'],$data['input-service']);
        if (empty($data['coupon_app'])){
            $this->error('适用业务必填', Url('coupon/coupon/create'));
        }
        if ($data['coupon_app']=== 'directmail' &&$data['service_id']=== ''){
            $this->error('适用服务功能必填', Url('coupon/coupon/create'));
        }
        try {
            $pricePng                 = cookie('png');
            $priceJpg                 = cookie('jpg');
            $data['img_smill_url']    = $priceJpg;
            $data['img_big_url']      = $pricePng;
            $data['create_time']      = time();
            $data['busin_id']         = Session('business.id');
            $data['coupon_num']       = mt_rand(1111, 9999) . mt_rand(1111, 9999);
            $data['coupon_stime']     = strtotime($data['coupon_stime']);
            $data['coupon_etime']     = strtotime($data['coupon_etime']);
            $data['coupon_get_stime'] = strtotime($data['coupon_get_stime']);
            $data['coupon_get_etime'] = strtotime($data['coupon_get_etime']);
            $data['stock']            = $data['total'];
            $this->couponModel->insertCouponTable($data);
        } catch (\Exception $e) {
            $this->error('新增失败' . $e->getMessage(), Url('coupon/coupon/create'));
        }
        cookie('png', null);
        cookie('jpg', null);
        $this->success('新增成功', Url('coupon/coupon/create'));
    }


    /**
     * 卡卷图片上传处理
     * @param Request $request
     * @return mixed
     */
    public function upload_image(Request $request)
    {
        if (empty($request->post('img'))) {
            return json(['code' => -1, 'msg' => '请上传图片']);
        }
        if (empty($request->post('fix'))) {
            return json(['code' => -1, 'msg' => '参数错误！']);
        }
        try {
            $path = '../../attachment/images/' . Session('business.busin_num');
            if (!is_dir($path)) {
                mkdir($path, 0777);
            }
            $fileName = md5(mt_rand(1111, 9999) . mt_rand(1111, 9999)) . '.' . $request->post('fix');
            file_put_contents($path . '/' . $fileName, base64_decode($request->post('img')));
            cookie($request->post('fix'), 'images/' . Session('business.busin_num') . '/' . $fileName);
        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }

        return json(['code' => 0, 'msg' => $request->post()]);
    }

    /**
     * 优惠券管理列表
     * @param Request $request
     * @return mixed
     */
    public function coupon_list(Request $request)
    {
        $where = 'busin_id=' . Session('business.id');
        $list  = $this->couponModel->getAllCoupon($where);
        $data  = $list->toArray()['data'];
        $data  = $this->format_parm($data);
        return view('coupon/list', ['page' => $list->render(), 'list' => $data]);
    }

    /**
     * 优惠券详情
     * @param Request $request
     * @return mixed
     */
    public function coupon_detail(Request $request)
    {
        if (empty($request->get('id'))) {
            abort(404, '页面不存在');
        }
        $where          = 'id=' . $request->get('id') . ' and busin_id=' . Session('business.id');
        $list           = $this->couponModel->getAllCoupon($where);
        $data           = $list->toArray()['data'];
        $data           = $this->format_parm($data);
        $publicAccArray = [];
        if (!empty($data['0']['coupon_buisin'])) {
            $publicAccName            = $this->couponModel->getPublicAccountByUniacid(trim($data[0]['coupon_buisin'],
                ','));
            $data[0]['coupon_buisin'] = explode(',', $data[0]['coupon_buisin']);
        }

        if (!empty($publicAccName)) {
            foreach ($publicAccName as $val) {
                $publicAccArray[$val['uniacid']] = $val['name'];
            }
        }

        unset($list, $publicAccName);
        return view('coupon/detail_list', ['list' => $data, 'publicAccArray' => $publicAccArray]);
    }


    /**
     * 取消优惠券发布
     * @param Request $request
     * @return mixed
     */
    public function closeRel(Request $request)
    {
        if (empty($request->get('id'))) {
            return json(['code' => -1, 'msg' => 'id不能为空']);
        }
        try {
            $this->couponModel->closeRel($request->get('id'), Session('business.id'));
        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
        return json(['code' => 0, 'msg' => '操作成功']);
    }


    /**
     * 设置优惠券生效失效
     * @param Request $request
     * @return mixed
     */
    public function disable(Request $request)
    {
        $id     = $request->get('id');
        $status = $request->get('status');
        if ($id == '' || $status == '') {
            return json(['code' => -1, 'msg' => '操作失败']);
        }
        try {
            $this->couponModel->Disable(['id' => $id], ['coupon_status' => $status]);
        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
        return json(['code' => 0, 'msg' => '操作成功']);
    }

    /**
     * 删除未发布的优惠券
     * @param Request $request
     * @return mixed
     */
    public function deleteCoupon(Request $request)
    {
        $id = $request->get('id');
        if ($id == '') {
            return json(['code' => -1, 'msg' => '操作失败']);
        }
        try {
            $this->couponModel->deleteCoupon($id);
        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
        return json(['code' => 0, 'msg' => '操作成功']);
    }


    /**
     * 确认发布，调用支付二维码发起支付
     * 1.生成订单
     * 2.请求支付
     * 3.返回支付url前端生成二维码
     * @param Request $request
     * @return mixed
     */
    public function confirmPush(Request $request)
    {
        $id        = $request->get('id');
        $res       = null;
        $countType = ['1' => '按发行金额X%', '2' => '按发行数量：Y元/张', '3' => '收取一次性费用'];
        if ($id == '' || !is_numeric($id)) {
            return json(['code' => -1, 'msg' => '操作失败']);
        }
        $millisecond = round(explode(" ", microtime())[0] * 1000);
        $order_id    = 'G99198' . date('Ymd', time()) . str_pad($millisecond, 3, '0', STR_PAD_RIGHT) . mt_rand(111,
                999) . mt_rand(111, 999);
        try {
            $_cData    = $this->couponModel->getCouponInfoById(['id' => $id, 'busin_id' => Session('business.id')]);
            $_issData  = $this->couponModel->getCouponIssceFeeByCid([
                'coupon_id' => $id,
                'busin_id'  => Session('business.id'),
            ]);
            $pay_moeny = $this->calculatePayMoney($_cData, $_issData);
            $parm      = ['pay_totals' => $pay_moeny, 'order_id' => $order_id];
            $conf      = [
                'url'      => $request->domain() . '/foll/public/index.php?s=PaymentsApi/pay',
                'req_data' => json_encode([
                    'uniacId'   => Session('business.busin_num'),
                    'payMoney'  => $pay_moeny * 100,
                    'orderSn'   => $order_id,
                    'payType'   => 'tgWxScan',
                    'notifyUrl' => $request->domain() . '/foll/public/?s=api_v2/CouponPayCallBack',
                    'body'      => '优惠券发行费用',
                ]),
            ];
            $this->couponModel->updateIssceFeeTable(['coupon_id' => $id, 'busin_id' => Session('business.id')], $parm);
            $res = $this->getPayUrlByPayApi($conf);
            @file_put_contents('../runtime/log/pay/coupon_pay.log', $res . "\n", FILE_APPEND);
            $res = json_decode($res, true);
            if ($res['status'] != '100') {
                return json(['code' => -1, 'msg' => $res['msg']]);
            }
        } catch (\Exception $e) {
            return json(['code' => -1, 'msg' => $e->getMessage()]);
        }
        return json([
            'code' => 0,
            'msg'  => $countType[$_issData['issued_type']] . ',支付费用:' . $pay_moeny,
            'data' => ['url' => $res['info']['codeUrl'], 'orderId' => $order_id],
        ]);
    }

    /**
     * 计算发行费用
     * @param null $cData
     * @param null $issData
     * @return int
     */
    protected function calculatePayMoney($cData = null, $issData = null)
    {
        $money = 0;
        switch ($issData['issued_type']) {
            case 1:
                $money = ($cData['total'] * $cData['coupon_money']) * ($issData['issued_price'] / 100);
                break;
            case 2:
                $money = $cData['total'] * $issData['issued_price'];
                break;
            case 3:
                $money = $issData['issued_price'];
                break;
            default:
                break;
        }
        return $money;
    }

    /**
     * 获取支付url
     * @param $config
     * @return null
     */
    protected function getPayUrlByPayApi($config)
    {
        $res = null;
        $res = httpRequest($config['url'], $config['req_data'], ['conten-Type' => 'Application-json']);
        return $res;
    }


    /**
     * 前端轮训检测支付状态
     * @param Request $request
     * @return mixed
     */
    public function checkPayStatus(Request $request)
    {
        $orderId = $request->get('order_id');
        if (empty($orderId)) {
            return json(['code' => -1, 'msg' => '参数错误', 'data' => '']);
        }
        $status = $this->couponModel->getPayStatus($orderId);
        return json(['code' => 0, 'msg' => '', 'data' => $status['pay_status']]);
    }

    /**
     * 优惠券领用管理
     * @return mixed
     */
    public function useManage(Request $request)
    {
        $where    = 'a.busin_id=' . Session('business.id');
        $_stime   = $request->get('time1');
        $_etime   = $request->get('time2');
        $_uStatus = $request->get('status');
        if ($_stime != '' && $_etime != '') {
            if ($_stime <= $_etime) {
                $where .= ' and a.create_time>=' . strtotime($_stime) . " and a.create_time<=" . strtotime($_etime);
            }
        }

        if ($_uStatus != '') {
            $where .= ' and a.status=' . $_uStatus;
        }

        $status         = ['未使用', '已使用', '已过期'];
        $result         = $this->couponModel->fetchCouponUseInfo($where);
        $list           = $result->toArray()['data'];
        $num['receNum'] = $this->couponModel->receiveNum(Session('business.id'));
        $num['useNum']  = $this->couponModel->useNum(Session('business.id'));
        return view('coupon/use_list',
            ['page' => $result->render(), 'list' => $list, 'status' => $status, 'num' => $num]);
    }

    /**
     * 核销管理
     * @return mixed
     */
    public function couponVerificationSheet(Request $request)
    {
        $type     = ['1' => '主动使用', '2' => '被动使用'];
        $app      = ['parking' => '停车', 'shop' => '商城', 'offline' => '线下','directmail'=>'直邮易'];
        $where    = 'a.busin_id=' . Session('business.id');
        $_type    = $request->get('type');
        $_orderId = $request->get('order_id');
        if ($_type != '') {
            $where .= ' and b.use_type=' . $_type;
        }

        if ($_orderId != '') {
            $where .= ' and a.order_id=' . $_orderId;
        }
        $result = $this->couponModel->fetchCouponUse($where);
        $list   = $result->toArray()['data'];
        return view('coupon/coupon_verifsheet', [
            'page' => $result->render(),
            'list' => $list,
            'type' => $type,
            'app'  => $app,
        ]);
    }

    /**
     * 优惠券结算管理待完成
     * @param Request $request
     * @return mixed
     */
    public function settleAccouManage(Request $request)
    {
        return view('coupon/settle_list');
    }


    /**
     * @param $data
     * @param $val
     * @return mixed
     */
    protected function format_parm($data)
    {
        $useTerm = ['满X元使用', '每X元使用', '无条件'];
        $useType = ['1' => '主动使用', '2' => '被动使用'];
        $status  = ['待发布', '生效中', '失效', '已放弃'];
        $check   = ['审核中', '已审核', '审核失败'];
        $pla     = ['未结算', '已结算'];
        foreach ($data as &$val) {
            $val['coupon_stime']     = date('Y-m-d H:i', $val['coupon_stime']);
            $val['coupon_etime']     = date('Y-m-d H:i', $val['coupon_etime']);
            $val['coupon_get_stime'] = date('Y-m-d H:i', $val['coupon_get_stime']);
            $val['coupon_get_etime'] = date('Y-m-d H:i', $val['coupon_get_etime']);
            $val['coupon_use_term']  = $useTerm[$val['coupon_use_term']];
            $val['use_type']         = $useType[$val['use_type']];
            $val['coupon_status']    = $status[$val['coupon_status']];
            $val['check_status']     = $check[$val['check_status']];
            $val['platform_sett']    = $pla[$val['platform_sett']];
        }
        return $data;
    }
}
