<?php


namespace app\api_v2\controller;


use think\Db;
use think\Request;

/**
 * 优惠卷接口
 * Class Coupon
 * @package app\api_v2\controller
 */
class Coupon
{


    /**
     * 获取所有优惠卷信息
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCoupon(Request $request)
    {
        $parmas = $request->post();
        if ($parmas['uniacid'] == "" || $parmas['unionid'] == "") {
            return json(['status' => 1, 'message' => '参数错误']);
        }
        $page = ($parmas['pageNum'] - 1) * $parmas['pageSize'];
        $totalSize = Db::name('foll_coupon')
            ->where('coupon_status', '=', 1)
            ->where('coupon_get_etime', '>', time())
            ->where("coupon_buisin", 'like', "%{$parmas['uniacid']}%")
            ->limit($page, $parmas['pageSize'])
            ->count();
        $res = Db::name('foll_coupon')
            ->where('coupon_status', '=', 1)
            ->where('coupon_get_etime', '>', time())
            ->where("coupon_buisin", 'like', "%{$parmas['uniacid']}%")
            ->limit($page, $parmas['pageSize'])
            ->order('id', 'desc')
            ->select();
        foreach ($res as &$val) {
            $isRev = Db::name('foll_receive_coupon')
                ->where(['user_id' => $parmas['unionid'], 'c_id' => $val['id']])
                ->field(['user_id'])
                ->find();
            if (!empty($isRev) && $val['max_limit'] === 1) {
                $val['isRev'] = true;
            } else {
                $val['isRev'] = false;
            }
            // $val['coupon_buisin']= explode(',',$val['coupon_buisin']);
            // if (!in_array($parmas['uniacid'],$val['coupon_buisin'])){
            //     unset($res[$k]);
            // }
        }
        return json(['status' => 0, 'message' => '', 'result' => ['data' => $res, 'totalSize' => $totalSize]]);
    }


    /**
     * 获取优惠卷详情
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCouponDeteil(Request $request)
    {
        if ($request->get('cid') == '') {
            return json(['status' => 1, 'message' => '参数错误']);
        }
        $res = Db::name('foll_coupon')->where(['id' => $request->get('id')])->find();
        return json(['status' => 0, 'message' => '', 'result' => $res]);
    }


    /**
     * 领取
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function receive(Request $request)
    {
        //用户id，卡卷id 领取时间
        $params = $request->post();
        if (!is_numeric($params['cid']) || $params['unionid'] == "" || $params['receive_time'] == "") {
            return json(['status' => 1, 'message' => '参数错误']);
        }
        $conpon = Db::name('foll_coupon')->where(['id' => $params['cid'], 'coupon_status' => 1])->field([
            'id',
            'busin_id',
            'max_limit',
            'coupon_get_stime',
            'coupon_get_etime',
            'total',
            'receive_limit'
        ])->find();
        if (empty($conpon)) {
            return json(['status' => 1, 'message' => '卡劵无法领取']);
        }
        if ($conpon['total'] == $conpon['receive_limit']) {
            return json(['status' => 1, 'message' => '卡劵已领取完']);
        }
        if (time() < $conpon['coupon_get_stime']) {
            return json(['status' => 1, 'message' => '活动时间还没开始哦']);
        }
        if (time() > $conpon['coupon_get_etime']) {
            return json(['status' => 1, 'message' => '活动时间已结束']);
        }

        $isReceive = Db::name('foll_coupon_receive')->where(['user_id' => $params['unionid'], 'coupon_id' => $params['cid']])->select();
        if (!empty($isReceive)) {
            if (($conpon['max_limit'] == 1)||(count($isReceive)>=$conpon['max_limit'])) {
                return json(['status' => 1, 'message' => '已超领取上限']);
            }
        }
        $rece_num = hexdec(uniqid());
        if ($rece_num % 2 == 0) {
            $rece_num = $rece_num + 1;
        }
        $rece_num = $rece_num . mt_rand(1111, 9999);
        Db::startTrans();
        try {
            Db::name('foll_coupon_receive')->insert([
                'user_id' => $params['unionid'],
                'coupon_id' => $params['cid'],
                'create_time' => $params['receive_time'],
                'busin_id' => $conpon['busin_id'],
                'rece_num' => $rece_num
            ]);
            Db::name('foll_coupon')->where('id', $params['cid'])->setInc('receive_limit', 1);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            return json(['status' => 1, 'message' => '领取失败']);
        }
        return json(['status' => 0, 'message' => '领取成功']);
    }


    /**
     * 获取领取到优惠卷列表
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getReceiveList(Request $request)
    {
        $params = $request->post();
        if (!is_numeric($params['uniacid'])||!is_numeric($params['status'])||$params['unionid']==""||$params['time']==""||$params['app']==""){
            return json(['status'=>1,'message'=>'参数错误']);
        }

        $res = Db::name('foll_coupon_receive')
            ->alias('a1')
            ->join('foll_coupon a2','a1.coupon_id=a2.id','left')
            ->where("a1.user_id='{$params['unionid']}' and a1.status={$params['status']} and a2.coupon_app like '%{$params['app']}%' and a2.coupon_buisin like '%{$params['uniacid']}%'")
            ->field(['a1.id','a2.coupon_name','a2.coupon_use_term','a2.enough','a2.coupon_money','a2.coupon_status','a2.service_id'])
            ->select();
        return  json(['status'=>0,'message'=>'','result'=>$res]);
    }


    /**
     * 计算优惠金额
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function deductibleDiscountAmount(Request $request)
    {
        $params = $request->post();
        $amount = 0;
        if ($params['unionid']==""&&!is_numeric($params['receive_id'])&&$params['amount']==""){
            return json(['status'=>1,'message'=>'参数错误']);
        }
        $cid  =Db::name('foll_coupon_receive')->where(['id'=>$params['receive_id'],'user_id'=>$params['unionid'],'status'=>0])->field('coupon_id')->find();
        if (empty($cid)){
            return json(['status'=>0,'message'=>'完成','result'=>['amount'=>$params['amount']]]);
        }
        $conponRes = Db::name('foll_coupon')->where('id',$cid['coupon_id'])->find();
        switch ($conponRes['coupon_use_term']){
            case 0:
                if ($params['amount']>=$conponRes['enough']){
                    $amount = $params['amount']-$conponRes['coupon_money'];
                }
                break;
            case 1:
                if ($params['amount']==$conponRes['enough']){
                    $amount = $params['amount']-$conponRes['coupon_money'];
                }
                break;
            case 2:
                $amount = $params['amount']>=$conponRes['coupon_money']?$params['amount']-$conponRes['coupon_money']:$conponRes['coupon_money']-$params['amount'];
                break;
        }
        return json(['status'=>0,'message'=>'完成','result'=>['amount'=>$amount]]);
    }


    /**
     * 使用
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function saveUseCoupon(Request $request)
    {
        $params = $request->post();
        $cid = Db::name('foll_coupon_receive')->where('id',$params['reid'])->field(['coupon_id','busin_id'])->find();
        $params['coupon_id']=$cid['coupon_id'];
        $params['busin_id']= $cid['busin_id'];
        Db::name('foll_coupon_receive')->where('id',$params['reid'])->update(['status'=>1]);
        unset($params['reid']);
        Db::name('foll_coupon_use')->insert($params);
        return json(['status'=>0,'message'=>'完成']);
    }


}