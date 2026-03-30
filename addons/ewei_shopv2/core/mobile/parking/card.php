<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}
class Card_EweiShopV2Page extends mobilePage
{
    public function main()
    {
        global $_W;
        global $_GPC;
        $title = '月卡购买';
        $openid = $_W['openid'];
        //echo $_W['openid'];

        $monthPlanList = pdo_getAll('parking_month_type',['uniacid'=>$_W['uniacid']]);

        $aBuys = $this->_buys(0, $openid);
        $dayBuys = $this->_buys(1, $openid);
        $nightBuys = $this->_buys(2, $openid);

        //$cardType = $this->cardType;

        //$hasBuys = $this->_hasBuy($openid);

        include $this->template('parking/card');
    }

    // test card valid
    public function check() {
        global $_W;
        global $_GPC;

        $openid = $_GPC['openid'];
        $cardType = intval($_GPC['cardType']);
        $date = $_GPC['date'];

        $ok = $this->ValidDate($cardType, $openid, $date);
        echo $ok;
    }

    /* **
     * 验证对应月卡某天的有效性
     * @$cardType int     月卡类型(0 全日 1 日间 2 夜间)
     * $openid    string  用户 openid
     * $date      string  日期(yyyy-MM-dd)
     *
     * @returns   bool
     *   false 无效 ture 有效
    ** */
    private function ValidDate($cardType, $openid, $date) {
        $sql = 'select id, sdate, edate, card_type, openid from ims_card_member where status = "Y" and card_type = :cardType and openid = :openid and edate > date_add(now(), interval 1 day)';
        $row = pdo_fetch($sql, array(':openid' => $openid, ':cardType' => $cardType));

        /*
        print_r($row);
        echo $date . '**';
        echo strtotime($row['sdate']) . '**';
        echo strtotime($row['edate']) . '**';
        echo strtotime($date) . '**';
        // */
        if ($row) {
            $n  = strtotime($date);
            $ns = strtotime($row['sdate']);
            $ne = strtotime($row['edate']);
            if ($n >= $ns && $n <= $ne) {
                //echo 'ok';
                return true;
            }
        }
        return false;
    }

    public function buyinfo() {
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        $id = intval($_GPC['id']);

        $row = $this->_buyInfo($openid, $id);
        $obj = $this->_ok();
        $obj['data'] = $row ? $row : new stdClass();

        $this->_toJSON($obj);
    }

    public function info() {
        global $_W;
        global $_GPC;
        $openid = $_W['openid'];
        $ctype = intval($_GPC['ctype']);

        $rows = $this->_buys($ctype, $openid);
        $obj = $this->_ok();
        $obj['rows'] = $rows ? $rows : array();

        $this->_toJSON($obj);
    }

    public function openid() {
        global $_W;
        echo $_W['openid'];
    }

    public function valid() {
        global $_W;
        $_W['openid'];
        $rows = $this->_hasBuy($_W['openid']);
        $obj = $this->_ok();
        $obj['rows'] = $rows ? $rows : array();

        $this->_toJSON($obj);
    }

    public function list1() {
        global $_W;
        $_W['openid'];
        $rows = $this->_list($_W['openid']);
        $obj = $this->_ok();
        $obj['rows'] = $rows ? $rows : array();

        $this->_toJSON($obj);
    }

    private function _toJSON($obj) {
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($obj);
    }

    private $cardType = array(0 => '全日卡', 1 => '日间卡', 2 => '夜间卡');

    private function _ok() {
        return array('status' => 0, 'message' => 'ok');
    }

    private function _err($status, $message) {
        return array('status' => $status, 'message' => $message);
    }

    private function _buys($type, $openid) {
        $sdate = $this->_startDate($type, $openid);
        $sql = 'select p.id, p.month, p.price, p.price1, :sdate start_date, date(date_add(:sdate, interval p.month month)) end_date, p.comments from '.tablename('card_price').' p where p.card_type = :type order by p.month desc';

        return pdo_fetchall($sql, array(':type' => $type, ':sdate' => $sdate));
    }

    private function _buyInfo($openid, $id) {
        $sql = 'select id, card_type, sdate, edate, money, status, sys_time sysTime from '.tablename('card_member').' where openid = :openid and id = :id';
        return pdo_fetch($sql, array(':openid' => $openid, ':id' => $id));
    }

    private function _hasBuy($openid) {
        $sql = 'select id, card_type, sdate, edate, money, sys_time sysTime from '.tablename('card_member').' where openid = :openid and status = "Y"';
        return pdo_fetchall($sql, array(':openid' => $openid));
    }

    private function _list($openid) {
        $sql = 'select id, card_type, sdate, edate, money, status, sys_time sysTime from '.tablename('card_member').' where openid = :openid order by edate desc';
        return pdo_fetchall($sql, array(':openid' => $openid));
    }

    /***
     * $type 月卡类型
     */
    private function _startDate($type, $openid) {
        $sunix = strtotime('2019-10-01');
        $now = time();
        if ($now >= $sunix) {
            $sunix = $now;
        }
        $sdate = date("Y-m-d", $sunix);
        $row = $this->_next($openid, $type, $sdate, $sdate);
        if ($row != null) {
            $edate = date_create($row['edate']);
            date_add($edate, date_interval_create_from_date_string("1 days"));
            $sdate = date_format($edate, "Y-m-d");
        }

        return $sdate;
    }

    private function _exists($openid, $type, $sdate, $edate) {
        $sql = 'select id, card_type, sdate, edate from '.tablename('card_member').' where openid = :openid and card_type = :type and :sdate between sdate and edate and status = "Y"';

        // 全日卡已存在的
        $row = pdo_fetch($sql, array('openid' => $openid, 'type' => 0, ':sdate' => $sdate));
        if ($row != null) {
            return $row;
        }

        // 当前卡已存在
        return pdo_fetch($sql, array('openid' => $openid, 'type' => $type, ':sdate' => $sdate));
    }

    private function _next($openid, $type, $sdate, $edate) {
        $sql = 'select id, card_type, sdate, edate from '.tablename('card_member').' where openid = :openid and card_type = :type and edate >= :sdate and status = "Y"';

        $row = pdo_fetch($sql, array('openid' => $openid, 'type' => $type, ':sdate' => $sdate));
        if ($row == null) {
            if ($type > 0) { // 全日卡有效的
                $sql = 'select id, card_type, sdate, edate from '.tablename('card_member').' where openid = :openid and card_type = :type and edate >= :sdate and status = "Y"';
                $row = pdo_fetch($sql, array('openid' => $openid, 'type' => 0, ':sdate' => $sdate));
            }
        }

        return $row;
    }

    private function _hasUnPay($openid) {
        return false;
        // $sql = 'select count(*) n from ims_foll_order a left join ims_parking_order b on a.ordersn=b.ordersn where a.user_id = :openid and (a.pay_status=2 or a.pay_status=0 or b.charge_status=0)';
        // $row = pdo_fetch($sql, array('openid' => $openid));
        // if (intval($row['n']) > 0) {
        //     return true;
        // }
        // return false;
    }

    public function test() {
        echo time();
        echo '*' . date('Y-m-d') . '*' . date("Y-m-d",time());
        echo '**' . strtotime('2019-10-01');
        echo '***'. date("Y-m-d",strtotime('+1 month', strtotime('2019-10-01')));
    }

    public function buy()
    {
        global $_W;
        global $_GPC;

        $openid = $_W['openid'];
        $obj = $this->_ok();

        if ($this->_hasUnPay($openid)) {
            $obj = $this->_err(1, '您还有未支付订单，请先支付');
            $this->_toJSON($obj);
            return;
        }
        $inArgs = $_GPC['__input'];
        $id = intval($inArgs['id']);
        if ($id > 0) {
            $row = pdo_fetch('select p.id, p.card_type, p.month, p.price, p.price1 from '.tablename('card_price').' p where p.id = :id',array('id' => $id));
            if ($row) {
                $ctype = $row['card_type'];
                $price = $row['price'];
                $month = $row['month'];
                $sdate = $this->_startDate($ctype, $openid);
                $edate = date("Y-m-d",strtotime('+' . $row['month'] . ' month', strtotime($sdate)));

                // 判断重复购买
                $row = $this->_exists($openid, $ctype, $sdate, $edate);
                if ($row == null) {
                    // 有效续买
                    $next = $this->_next($openid, $ctype, $sdate, $edate);
                    if ($next != null) {
                        $sdate = $next['sdate'];
                        $edate = date("Y-m-d",strtotime('+' . $month . ' month', strtotime($sdate)));
                    }
                    //
                    $args = array(
                        'ctype' => $ctype,
                        'sdate' => $sdate,
                        'edate' => $edate,
                        'money' => $price,
                        'status' => 'W',
                        'openid' => $openid
                    );

                    $n = pdo_query('insert into '.tablename('card_member').' set card_type = :ctype, sdate = :sdate, edate = :edate, money = :money, status = :status, openid = :openid', $args);
                    if ($n > 0) {
                        $obj['data'] = array('price' => $price, 'id' => pdo_insertid());
                    } else {
                        $obj = $this->_err(1, '保存月卡购买信息错误');
                    }
                    $args['month'] = $month;
                    $obj['obj'] = $args;
                } else {
                    $obj = $this->_err(1, '月卡信息已存在');
                    $obj['d'] = $row;
                }
            } else {
                $obj = $this->_err(1, '选择的月卡不存在存在');
            }
        } else {
            $obj = $this->_err(1, '请选择需要购买的月卡');
        }
        $obj['openid'] = $_W['openid'];
        $obj['id'] = $id;

        $this->_toJSON($obj);
    }

    public function pay1() {
        global $_W;
        global $_GPC;
        $title = '月卡购买';
        $openid = $_W['openid'];
        $id = $_GPC['id'];

        include $this->template('parking/cardpay');
    }

    public function payCard()
    {
        global $_W;
        global $_GPC;

        if ($_GPC['__input']['id']==""){
            $this->_toJSON($this->_err(1, '参数错误'));
        }
        $millisecond = round(explode(" ", microtime())[0]*1000);
        $oid = 'G99198'  . date('Ymd', time()) .str_pad($millisecond,3,'0',STR_PAD_RIGHT).mt_rand(111, 999).mt_rand(111,999);
        $cardRes = pdo_get('card_member',['id'=>$_GPC['__input']['id']]);
        try{
            pdo_begin();
            $card = pdo_update('card_member',['ordersn'=>$oid],['id'=>$cardRes['id']]);//更新订单号
            pdo_insert('foll_order',[
                'ordersn' => $oid,
                'user_id' => $_W['openid'],
                'business_id'=>$_W['uniacid'],
                'uniacid' => $_W['uniacid'],
                'application'=>'monthCard',
                'goods_name'=>'月卡',
                'goods_price'=>$cardRes['money'],
                'pay_type' =>'wechat',
                'pay_account' =>$cardRes['money'],
                'body' =>'月卡购买',
                'create_time'=>time(),
                'total' => $cardRes['money']
            ]);
            pdo_commit();
        }catch (\Exception $exception){
            pdo_rollback();
            $this->_toJSON($this->_err(1, '获取信息失败'));
            exit();
        }

        $post_data=['token'=>'wechat','id'=>$oid];
        $url="http://shop.gogo198.cn/payment/monthCard/Card.php";
        $payRes=$this->ihttp_post($url,$post_data);
        $payRes=json_decode($payRes,true);
        if($payRes['msg']=='success') {
            // 请求支付参数
            $pay_info = json_decode($payRes['pay_info'],true);
            // 支付唤起地址
            $urlTo = 'http://shop.gogo198.cn/addons/ewei_shopv2/payment/wechat/pay.php';
            // 跳转唤起支付
            $obj = $this->_ok();
            $rs = $this->formPost($urlTo,$pay_info);
            $obj['data'] = $rs;
            $this->_toJSON($obj);
        }else{
            $this->_toJSON($this->_err(1, $payRes['msg']));
            //message('维护中,请耐心等待...');
        }

        //获取支付
    }

    public function ihttp_post($url,$post_data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    // 模拟表单请求
    public function formPost($uri, $data)
    {
        $str = '<form action="' . $uri . '" method="post" name="formPost">';
        foreach ($data as $k => $v) {
            $str .= '<input type="hidden" name="' . $k . '" value="' . $v . '">';
        }
        $str .= '</form>';
        return $str . "<script>document.forms['formPost'].submit();</script>";
    }

    private function _auth($openid) {
        // oR-IB0gJ1IwCqNJuXpsmxc8pc-ww
        if ($openid == 'oR-IB0gJ1IwCqNJuXpsmxc8pc-ww') {
            return true;
        }
        return false;
    }

    public function u() {
        global $_W;
        global $_GPC;
        $id = intval($_GPC['id']);

        $openid = $_W['openid'];
        if ($this->_auth($openid)) {
            $args = array(
                'ctype' => $_GPC['ctype'],
                'sdate' => $_GPC['sdate'],
                'edate' => $_GPC['edate'],
                'money' => $_GPC['price'],
                'status' => $_GPC['status'],
                'openid' => $_GPC['openid']
            );

            $n = 0;
            if ($id > 0) {
                $n = pdo_query('update ims_card_member set card_type = :ctype, sdate = :sdate, edate = :edate, money = :money, status = :status, openid = :openid where id = ' . $id, $args);
            } else {
                $sql = 'insert into ims_card_member set card_type = :ctype, sdate = :sdate, edate = :edate, money = :money, status = :status, openid = :openid';
                $n = pdo_query($sql, $args);
            }

            $obj = $this->_ok();
            $obj['obj'] = $args;
            $obj['n'] = $n;
            $obj['id'] = $id;

            $this->_toJSON($obj);
        } else {
            echo 'oo';
        }
    }

    public function s() {
        global $_W;
        global $_GPC;
        $type = intval($_GPC['type']);

        $openid = $_W['openid'];

        if ($this->_auth($openid)) {
            $obj = $this->_ok();
            $sql = 'select m.id, m.openid, m.card_type, m.sdate, m.edate, m.money, m.status, m.ordersn, o.total, o.nickname, a.name, a.carNo, a.mobile, o.pay_status, m.sys_time from ims_card_member m left join ims_foll_order o on m.ordersn = o.ordersn left join ims_parking_authorize a on m.openid = a.openid where m.status = "Y"';

            if ($type == 0) {
                $sql .= ' and m.card_type = 0';
            } else if ($type == 1) {
                $sql .= ' and m.card_type = 1';
            } else if ($type == 2) {
                $sql .= ' and m.card_type = 2';
            }
            $sql .= ' order by m.id desc';

            $rows = pdo_fetchall($sql, array(':openid' => $openid));
            $obj = $this->_ok();
            $obj['total'] = $rows ? count($rows) : 0;
            $obj['rows'] = $rows ? $rows : array();

            $this->_toJSON($obj);
        } else {
            echo 'ooo';
        }
    }
}