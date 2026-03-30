<?php

namespace app\index\logic;

use think\Model;
use think\Loader;
use PHPExcel;
use PHPExcel_IOFactory;
use think\Db;

class Order extends Model
{

    public $model = null;

    public function __construct()
    {
        parent::__construct();
        $this->model = Loader::model('Order', 'model');
    }


    /**
     * @param $data
     * @return mixed
     * 条件查找订单
     */
    public function fetchOrder($data)
    {

        if (Session('UserResutlt.uniacid') == '') {
            return json(['code' => 0, 'msg' => '请登陆！', 'count' => 0, 'data' => '']);
        }
        $page   = isset($data['page']) ? $data['page'] : 1;
        $limit  = isset($data['limit']) ? $data['limit'] : 1;
        $offset = ($page - 1) * $limit;
        $where  = "foll.uniacid=" . Session('UserResutlt.uniacid') . " and foll.application='parking'";
        if (isset($data['orderid']) && $data['orderid'] != '') {
            return $this->fetchOrderUseOrderId($data['orderid']);
        }

        if (isset($data['time']) && $data['time'] != '') {
            $sliTime = explode("/", $data['time']);
            $where   .= " and foll.pay_time>=" . strtotime(trim($sliTime[0])) . " and foll.pay_time<=" . strtotime(trim($sliTime[1]));
        }

        if (isset($data['pay_status']) && $data['pay_status'] != '') {
            switch ($data['pay_status']) {
                case 0:
                    $where .= " and (parking.endtime=0 or parking.charge_status=0)";
                    break;
                case 1:
                    $where .= " and foll.pay_status=1 and parking.charge_status=1";
                    break;
                case 2:
                    $where .= " and foll.pay_status=2";
                    break;
                default:
                    break;
            }
        }

        if (isset($data['parkCode']) && $data['parkCode'] != '') {
            $where .= " and parking.number=" . $data['parkCode'];
        }


        if ((isset($data['st']) && $data['st'] != '') && (isset($data['et']) && $data['et'] != '')) {

            $where .= " and parking.starttime>=" . strtotime($data['st']) . " and parking.endtime<=" . strtotime($data['et']) . " and parking.endtime!=0";

        } else {
            if ((isset($data['st']) && $data['st'] != '') && $data['et'] == '') {

//            $data['st'] = strtotime($data['st']);
//            $todayFirstStart = mktime(0, 0, 0, date('m', $data['st']), date('d', $data['st']), date('Y', $data['st']));
//            $todayLastStart = mktime(23, 59, 59, date('m', $data['st']), date('d', $data['st']), date('Y', $data['st']));
                if (isset($data['sts']) && $data['sts'] != '') {
                    $where .= " and parking.starttime>=" . strtotime($data['st']) . " and parking.starttime<=" . strtotime($data['sts']);;
                }

            } else {
                if ((isset($data['et']) && $data['et'] != '') && $data['st'] == '') {

//            $data['et'] = strtotime($data['et']);
//            $todayFirstEnd = mktime(0, 0, 0, date('m', $data['et']), date('d', $data['et']), date('Y', $data['et']));
//            $todayLastEnd = mktime(23, 59, 59, date('m', $data['et']), date('d', $data['et']), date('Y', $data['et']));
                    if (isset($data['ets']) && $data['ets'] != '') {
                        $where .= " and parking.endtime>=" . strtotime($data['et']) . " and parking.endtime<=" . strtotime($data['ets']) . " and parking.endtime!=0";
                    }
                }
            }
        }

        if (isset($data['parkType']) && $data['parkType'] != '') {
            $where .= " and parking.charge_type=" . $data['parkType'];
        }

        if (isset($data['PayType']) && $data['PayType'] != '') {
            $where .= " and foll.pay_type='" . $data['PayType'] . "'";
        }

        if (isset($data['ExcepOrder']) && $data['ExcepOrder'] != '') {
            $where .= " and foll.isError=" . $data['ExcepOrder'];
        }

        if (isset($data['vioOrder']) && $data['vioOrder'] != '') {
            $where .= " and parking.is_violation=" . $data['vioOrder'];
        }

        if (isset($data['monthCard']) && $data['monthCard'] != '') {
            $where .= " and parking.moncard=" . $data['monthCard'];
        }

        if (isset($data['cardNo']) && $data['cardNo'] != '') {
            $where .= " and parking.CarNo='" . $data['cardNo'] . "'";
        }

        if (isset($data['mobile']) && $data['mobile'] != '') {
            $user_id = $this->model->GetUserIdByMobile($data['mobile']);
            $where   .= " and foll.user_id='" . $user_id['openid'] . "'";
        }
        if (isset($data['upOrderId'])&&$data['upOrderId']!=''){
            $where .= " and foll.upOrderId='" . $data['upOrderId'] . "'";
        }

//        dump($where);
        $orderRes = $this->seltTableByWhere($where, $offset, $limit);
        if (!empty($orderRes['res'])) {
            $orderFormatRes = $this->formatOrderRes($orderRes['res']);
            unset($orderRes['res']);
            return json(['code' => 0, 'msg' => '', 'count' => $orderRes['total'], 'data' => $orderFormatRes]);
        }
        return json(['code' => 0, 'msg' => '没有数据', 'count' => 0, 'data' => []]);

    }


    protected function seltTableByWhere($where, $offset, $limit)
    {
        return $this->model->selOrderTabByJoin($where, $offset, $limit);
    }


    /**格式化数据
     * @param $orderData
     * @return mixed
     */
    protected function formatOrderRes($orderData)
    {
        $parkId       = null;
        $parkCodeInfo = [];
        $payStatus    = ['未支付', '支付完成', '支付失败'];
        $payType      = [
            'Fwechat' => '微信免密',
            'wechat'  => '聚合支付--微信',
            'Parks'   => '银联无感',
            'alipay'  => '聚合支付--支付宝',
            'FAgro'   => '农商免密',
            'other'   => '其他',
        ];
        $parkType     = ['预付费', '后付费'];
        $isError      = ['异常订单', '正常'];
        $isVio        = ['否', '是'];
        foreach ($orderData as $val) {
            $parkId .= $val['number'] . ',';
        }
        $parkCodeAndAddr = $this->model->GetParkCodeAndAddr(trim($parkId, ','));
        foreach ($parkCodeAndAddr as $val) {
            $parkCodeInfo[$val['park_code']] = $val;
        }
        unset($parkCodeAndAddr);
        foreach ($orderData as $key => &$value) {
            $value['id']          = ($key + 1);
            $value['totalMinute'] = empty($value['endtime']) ? 0 : ceil(($value['endtime'] - $value['starttime']) / 60);
            $value['freeMinute']  = $value['totalMinute'] - $value['duration'];
            $value['freeMoney']   = $value['total'] - $value['pay_account'];
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            $value['starttime']   = date('Y-m-d H:i:s', $value['starttime']);

            $value['endtime'] = empty($value['endtime']) ? '空' : date('Y-m-d H:i:s', $value['endtime']);

            $value['pay_time'] = empty($value['pay_time']) ? '空' : date('Y-m-d H:i:s', $value['pay_time']);
            if (isset($parkCodeInfo[$value['number']])) {
                $value['addr'] = $parkCodeInfo[$value['number']]['Town'] . $parkCodeInfo[$value['number']]['Committee'] . $parkCodeInfo[$value['number']]['Road'] . $parkCodeInfo[$value['number']]['Road_num'] . '号';
            }
            $value['parkType']   = $parkType[$value['charge_type']];
            $value['pay_status'] = $payStatus[$value['pay_status']];
            if (isset($parkCodeInfo[$value['number']])) {
                $value['numbers'] = $parkCodeInfo[$value['number']]['numbers'];
            }
            $value['isError']      = $isError[$value['isError']];
            $value['is_violation'] = $isVio[$value['is_violation']];
            if (isset($payType[$value['pay_type']])) {
                $value['pay_type'] = $payType[$value['pay_type']];
            } else {
                $value['pay_type'] = '其他';
            }
        }
        return $orderData;
    }

    /**
     * 根据订单号查找订单
     * @param $oid
     * @return mixed
     */
    protected function fetchOrderUseOrderId($oid)
    {
        $payStatus = ['未支付', '支付完成', '支付失败'];
        $payType   = [
            'Fwechat' => '微信免密',
            'wechat'  => '聚合支付--微信',
            'Parks'   => '银联无感',
            'alipay'  => '聚合支付--支付宝',
            'FAgro'   => '农商免密',
            'other'   => '其他',
        ];
        $parkType  = ['预付费', '后付费'];
        $isError   = ['异常订单', '正常'];
        $isVio     = ['否', '是'];
        $orderData = $this->model->GetOrder($oid, Session('UserResutlt.uniacid'));
        if (empty($orderData)) {
            return json(['code' => -1, 'msg' => '未查询到结果', 'count' => 1, 'data' => '']);
        }
        $ParkCode = $this->model->GetParkCode($orderData['number']);
        $addr     = $this->model->GetParkAddr($ParkCode['pid']);

        $orderData['totalMinute']  = empty($orderData['endtime']) ? 0 : ceil(($orderData['endtime'] - $orderData['starttime']) / 60);
        $orderData['freeMinute']   = $orderData['totalMinute'] - $orderData['duration'];
        $orderData['freeMoney']    = $orderData['total'] - $orderData['pay_account'];
        $orderData['create_time']  = date('Y-m-d H:i:s', $orderData['create_time']);
        $orderData['starttime']    = date('Y-m-d H:i:s', $orderData['starttime']);
        $orderData['endtime']      = empty($orderData['endtime']) ? '空' : date('Y-m-d H:i:s', $orderData['endtime']);
        $orderData['pay_time']     = empty($orderData['pay_time']) ? '空' : date('Y-m-d H:i:s', $orderData['pay_time']);
        $orderData['addr']         = $addr['Town'] . $addr['Committee'] . $addr['Road'] . $addr['Road_num'] . '号';
        $orderData['parkType']     = $parkType[$orderData['charge_type']];
        $orderData['pay_status']   = $payStatus[$orderData['pay_status']];
        $orderData['numbers']      = $ParkCode['numbers'];
        $orderData['isError']      = $isError[$orderData['isError']];
        $orderData['is_violation'] = $isVio[$orderData['is_violation']];
        if (in_array($orderData['pay_type'], $payType)) {
            $orderData['pay_type'] = $payType[$orderData['pay_type']];
        } else {
            $orderData['pay_type'] = '其他';
        }

        return json(['code' => 0, 'msg' => '', 'count' => 1, 'data' => [$orderData]]);
    }


    /**
     * 发送验证码密码
     * @return mixed
     */
    public function SendAuthCode()
    {
        $openid = $this->model->getAdminOpenid(Session('UserResutlt.user_mobile'));
//        $code = mt_rand(1111, 9999);
        $code_o = 123456;
        $code_t = 800800;
        Session('orderAuthCode', $code);
        Session('orderAuthCode_t', $code_t);
//        $msg = '{"touser":"' . $openid . '","msgtype":"text","text":{"content":"订单用户资料查看授权码：' . $code . '"}}';
//        $token = RequestAccessToken(Session('UserResutlt.uniacid'));
//        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $token;
//        httpRequest($url, $msg, ["Content-Type: application/json"]);
        return json(['code' => 0, 'msg' => '完成']);
    }


    /**
     * 已经不使用了
     * 查询用户信息
     * @param $uid
     * @return mixed
     */
    public function FetchOrderUserInfo($uid)
    {

        $type = ['wg' => '银联免密', 'sd' => '农商免密', 'wx' => '微信免密'];
        list($info, $verif) = $this->model->getOrderUserInfo($uid);
        if ($info['auth_status'] == 1) {
            $info['auth_type'] = unserialize($info['auth_type']);
            foreach ($info['auth_type'] as $k => $v) {
                $info['auth'] = $type[$k];
            }
        } else {
            $info['auth'] = '未授权';
        }

        if (empty($verif)) {
            $info['idcard']  = '空';
            $info['uname']   = '空';
            $info['license'] = '空';
        }


        if (!empty($info) && !empty($verif)) {
            $data = array_merge($info, $verif);
        } else {
            if (!empty($info)) {
                $data = $info;
            } else {
                $data = $verif;
            }
        }

        return json(['code' => 0, 'msg' => '', 'data' => $data]);
    }

    /**导出订单
     * @param $time
     * @throws \Exception
     */
    public function exproOrder($data)
    {

        set_time_limit(0);
        $where = "foll.uniacid=" . Session('UserResutlt.uniacid') . " and foll.application='parking'";
        if (isset($data['time']) && $data['time'] != '') {
            $sliTime = explode("/", $data['time']);
            $where   .= " and foll.pay_time>=" . strtotime(trim($sliTime[0])) . " and foll.pay_time<=" . strtotime(trim($sliTime[1]));
        }

        if (isset($data['pay_status']) && $data['pay_status'] != '') {
            switch ($data['pay_status']) {
                case 0:
                    $where .= " and (parking.endtime=0 or parking.charge_status=0)";
                    break;
                case 1:
                    $where .= " and foll.pay_status=1 and parking.charge_status=1";
                    break;
                case 2:
                    $where .= " and foll.pay_status=2";
                    break;
                default:
                    break;
            }
        }

        if (isset($data['parkCode']) && $data['parkCode'] != '') {
            $where .= " and parking.number=" . $data['parkCode'];
        }

        if ((isset($data['st']) && $data['st'] != '') && (isset($data['et']) && $data['et'] != '')) {
            $where .= " and parking.starttime>=" . strtotime($data['st']) . " and parking.endtime<=" . strtotime($data['et']) . " and parking.endtime!=0";
        } else {
            if ((isset($data['st']) && $data['st'] != '') && $data['et'] == '') {
                if (isset($data['sts']) && $data['sts'] != '') {
                    $where .= " and parking.starttime>=" . strtotime($data['st']) . " and parking.starttime<=" . strtotime($data['sts']);;
                }
            } else {
                if ((isset($data['et']) && $data['et'] != '') && $data['st'] == '') {
                    if (isset($data['ets']) && $data['ets'] != '') {
                        $where .= " and parking.endtime>=" . strtotime($data['et']) . " and parking.endtime<=" . strtotime($data['ets']) . " and parking.endtime!=0";
                    }
                }
            }
        }


        if (isset($data['parkType']) && $data['parkType'] != '') {
            $where .= " and parking.charge_type=" . $data['parkType'];
        }

        if (isset($data['PayType']) && $data['PayType'] != '') {
            $where .= " and foll.pay_type='" . $data['PayType']."'";
        }

        if (isset($data['ExcepOrder']) && $data['ExcepOrder'] != '') {
            $where .= " and foll.isError=" . $data['ExcepOrder'];
        }

        if (isset($data['vioOrder']) && $data['vioOrder'] != '') {
            $where .= " and parking.is_violation=" . $data['vioOrder'];
        }

        if (isset($data['monthCard']) && $data['monthCard'] != '') {
            $where .= " and parking.moncard=" . $data['monthCard'];
        }

        if (isset($data['cardNo']) && $data['cardNo'] != '') {
            $where .= " and parking.CarNo='" . $data['cardNo'] . "'";
        }

        if (isset($data['mobile']) && $data['mobile'] != '') {
            $user_id = $this->model->GetUserIdByMobile($data['mobile']);
            $where   .= " and foll.user_id='" . $user_id['openid'] . "'";
        }

        $file             = null;
        $payStatus        = ['未支付', '已支付', '支付失败'];
        $payType          = ['Fwechat' => '微信免密', 'wechat'  => '聚合支付--微信', 'Parks'   => '银联无感', 'alipay'  => '聚合支付--支付宝', 'FAgro'   => '农商免密', 'other'   => '其他',];
        $setFieldNickName = [
            'A1' => '订单编号',
            'B1' => '创建时间',
            'C1' => '总金额',
            'D1' => '实付',
            'E1' => '支付时间',
            'F1' => '支付状态',
            'G1' => '支付类型',
            'H1' => '进入时间',
            'I1' => '离开时间',
            'J1' => '时长',
            'K1' => '泊位编号',
            'L1' => '后付费或预付',
            'M1' => '设备订单号',
            'N1' => '违规订单',
            'O1' => '地址',
            'P1' => '车牌号',
            'Q1' => '商户单号',
        ];
        $isVio            = ['否', '是'];


        $list = $this->model->getAllOrderInTime($where);
        if (empty($list)) {
            throw new \Exception('未查询到数据');
        }
        $parkCodeInfo = [];
        $parkId       = null;
        foreach ($list as $val) {
            $parkId .= $val['number'] . ',';
        }

        $parkCodeAndAddr = $this->model->GetParkCodeAndAddr(trim($parkId, ','));
        foreach ($parkCodeAndAddr as $val) {
            $parkCodeInfo[$val['park_code']] = $val;
        }

        try {

            $PHPExcel = new PHPExcel();
            $PHPExcel->setActiveSheetIndex(0);
            $PHPSheet = $PHPExcel->getActiveSheet();
            $PHPSheet->setTitle('sheet1');
            foreach ($setFieldNickName as $key => $val) {
                $PHPSheet->setCellValue($key, $val);
            }

            $n = 2;
            foreach ($list as $val) {
                $PHPSheet->setCellValue('A' . $n, $val['ordersn'])
                    ->setCellValue('B' . $n, date('Y-m-d H:i', $val['create_time']))
                    ->setCellValue('C' . $n, $val['total'])
                    ->setCellValue('D' . $n, $val['pay_account'])
                    ->setCellValue('E' . $n, empty($val['pay_time']) ? '空' : date('Y-m-d H:i', $val['pay_time']))
                    ->setCellValue('F' . $n, $payStatus[$val['pay_status']])
                    ->setCellValue('G' . $n, isset($payType[$val['pay_type']]) ? $payType[$val['pay_type']] : '暂无')
                    ->setCellValue('H' . $n, date('Y-m-d H:i:s', $val['starttime']))
                    ->setCellValue('I' . $n, $val['endtime'] == 0 ? '空' : date('Y-m-d H:i:s', $val['endtime']))
                    ->setCellValue('J' . $n, $val['duration'])
                    ->setCellValue('K' . $n, $val['number'])
                    ->setCellValue('L' . $n, $val['charge_type'] == 1 ? '后付' : '预付费')
                    ->setCellValue('M' . $n, $val['devs_ordersn'])
                    ->setCellValue('N' . $n, $isVio[$val['is_violation']])
                    ->setCellValue('O' . $n,
                        isset($parkCodeInfo[$val['number']]) ? $parkCodeInfo[$val['number']]['Town'] . $parkCodeInfo[$val['number']]['Committee'] . $parkCodeInfo[$val['number']]['Road'] . $parkCodeInfo[$val['number']]['Road_num'] . '号' : '空')
                    ->setCellValue('P' . $n, $val['CarNo'])
                    ->setCellValue('Q' . $n, $val['upOrderId']);
                $n += 1;
            }
            unset($list, $parkCodeAndAddr);
            $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
            $file     = date('Y-m-dHis', time()) . '.xlsx';
            ob_end_clean();  //清空缓存
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
            header("Content-Type:application/force-download");
            header("Content-Type:application/vnd.ms-execl");
            header("Content-Type:application/octet-stream");
            header("Content-Type:application/download");
            header('Content-Disposition:attachment;filename="' . $file . '"');
            header("Content-Transfer-Encoding:binary");
            $phpWrite->save('php://output');
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . '|行号：' . $e->getLine());
        }
    }


    /**
     * 变更试运营结束收费入场时间
     * @param $time
     * @throws \Exception
     */
    public function changeParkingStartTime($time)
    {
        if (empty($time['starttime'])) {
            throw new \Exception('请填写时间');
        }
        $unixTimestamp  = strtotime(trim($time['starttime']));
        $whereEnd       = strtotime(trim($time['end']));
        $allNotPayOrder = $this->model->fetchNotPayAllOrder($whereEnd);
        if (empty($allNotPayOrder)) {
            throw new \Exception('未查询到相关订单');
        }
        $orderId = null;
        foreach ($allNotPayOrder as $value) {
            $orderId .= $value['id'] . ',';
        }
        $orderId = trim($orderId, ",");

        Db::startTrans();
        try {
            $this->model->updateStartTimeField($orderId, $unixTimestamp);
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * 删除订单
     * @param $data
     * @throws \Exception
     */
    public function delOrder($data)
    {
        $id = null;
        foreach ($data as $val) {
            $id .= $val . ',';
        }
        $id = trim($id, ",");
        if (!empty($this->model->isOrderPayStatus($id))) {
            throw new \Exception('暂不支持删除已支付订单!');
        }
        Db::startTrans();
        try {
            $this->model->deleteFromOrderId($id);
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            throw new \Exception($exception->getMessage());
        }
    }


    /**
     * 修改订单
     * @param $data
     * @throws \Exception
     */
    public function modifyOrder($data)
    {
        if ($data['orderId'] == '') {
            throw new \Exception('订单号错误');
        }

        if ($data['money'] != '') {

            if ($data['yzm'] != Session('orderCode_t')) {
                throw new \Exception('验证码错误');
            }
            Db::startTrans();
            try {
                $sql = "ordersn='" . $data['orderId'] . "' and (pay_status=0 or pay_status=2)";
                $this->model->modifyFollOrderByOid($sql, ['pay_account' => $data['money']]);
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new \Exception($exception->getMessage());
            }
        }
        if ($data['payStatus']!=""){
            if ($data['yzm'] != Session('orderCode_t')) {
                throw new \Exception('验证码错误');
            }
            Db::startTrans();
            try {
                $sql = "ordersn='" . $data['orderId'] . "' and (pay_status=0 or pay_status=2)";
                $this->model->modifyFollOrderByOid($sql, ['pay_type' => $data['payType'],'pay_status'=>$data['payStatus']]);
                $this->model->modifyParkOrderByOid(['ordersn' => $data['orderId']], ['status' => "已结算",'charge_status'=>1]);
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new \Exception($exception->getMessage());
            }
        }
        if ($data['endtime']!=""){
            if ($data['yzm'] != Session('orderCode')) {
                throw new \Exception('验证码错误');
            }
            Db::startTrans();
            try {
                $this->model->modifyParkOrderByOid(['ordersn' => $data['orderId']], ['endtime' => strtotime($data['endtime'])]);
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new \Exception($exception->getMessage());
            }
        }
        if ($data['cardNo'] != '') {
            if ($data['yzm'] != Session('orderCode')) {
                throw new \Exception('验证码错误');
            }
            Db::startTrans();
            try {
                $this->model->modifyParkOrderByOid(['ordersn' => $data['orderId']], ['CarNo' => $data['cardNo']]);
                Db::commit();
            } catch (\Exception $exception) {
                Db::rollback();
                throw new \Exception($exception->getMessage());
            }
        }

    }

}
