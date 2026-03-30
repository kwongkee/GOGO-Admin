<?php
if (!(defined('IN_IA'))) {
    exit('Access Denied');
}

class Card_EweiShopV2Page extends WebPage {
    const TEMPLATEID = 'nXuU7WtKzVn-WZJoroWaO8Dev6yi__lEHB-gP__PTr0';

    public function main()
    {
    }

    public function AuditList()
    {
        global $_GPC;
        global $_W;
        if (empty($_GPC['username'])) {
            $total = pdo_fetchall("select count(id) as num from" . tablename("parking_imagename") . "where uniacid=" . $_W['uniacid'])['0'];
            $pageindex = $_GPC['page'] < 1 ? 1 : intval($_GPC['page']);
            $pagesize = 10;
            $pager = pagination($total['num'], $pageindex, $pagesize);
            $p = ($pageindex - 1) * $pagesize;
            $data = pdo_fetchall("select a.name ,b.* from " . tablename('parking_imagename') . " as a left join " . tablename('parking_verified') . " as b on  a.openid=b.openid where b.status=0 and a.uniacid={$_W['uniacid']} limit {$p},{$pagesize}");
        } else {
            $data = pdo_fetchall("select a.name,b.* from " . tablename('parking_verified') . " as b left join " . tablename('parking_imagename') . " as a on b.openid=a.openid where b.uname like '%" . $_GPC['username'] . "%' and b.uniacid={$_W['uniacid']}");
        }
        include $this->template("parking/audit_list");
    }

    public function Audited()
    {
        global $_GPC;
        global $_W;
        $sex = [0 => '女', 1 => '男'];
        $total = pdo_fetchall("select count(id) as num from " . tablename("parking_verified") . " where uniacid=" . $_W['uniacid'] . " and status=1")['0'];
        $pageindex = $_GPC['page'] < 1 ? 1 : intval($_GPC['page']);
        $pagesize = 10;
        $pager = pagination($total['num'], $pageindex, $pagesize);
        $p = ($pageindex - 1) * $pagesize;
        $data = pdo_fetchall("select * from " . tablename("parking_verified") . "where uniacid={$_W['uniacid']} and status=1 limit {$p},{$pagesize}");
        include $this->template("parking/audited");
    }

    public function updateStatusId()
    {
        global $_GPC;
        global $_W;
        $id = trim($_GPC['id'], ',');
        if(empty($_GPC['moneys'])){
            $data = array('uniacid' => $_W['uniacid'], 'openid' => $_GPC['openid'], 'parspaces' => $_GPC['monthnum'], 'period' => $_GPC['monthtime'], 'endtime' => $_GPC['cardtime'], 'createtime' => time());
        }else{
            $data = array('uniacid' => $_W['uniacid'], 'openid' => $_GPC['openid'], 'parspaces' => $_GPC['monthnum'], 'period' => $_GPC['monthtime'],'money'=>$_GPC['moneys'], 'endtime' => $_GPC['cardtime'], 'createtime' => time());
        }
        $UserVerified = pdo_insert("parking_monthcard", $data);
        if (!empty($UserVerified)) {
            if ($_GPC['sf'] == '2') {
                $bool = pdo_query("UPDATE " . tablename('parking_verified') . " SET status=1,audit_status=1 WHERE id in('".$id."') and uniacid={$_W['uniacid']}");
                $this->SendSuccessMessage($_GPC['openid']);//发生中签结果
            } else {
                $bool = pdo_query("UPDATE " . tablename('parking_verified') . " SET status=1 WHERE id in('".$id."') and uniacid={$_W['uniacid']}");
            }
            if ($bool) {
                echo 'success';
            }
        }

    }

    public function DelUserInfo()
    {
        global $_W;
        global $_GPC;
        load()->func('file');
        $openid = $_GPC['oid'];
        $result = pdo_delete('parking_verified', array('openid' => $openid, 'uniacid' => $_W['uniacid']));
        if (!empty($result)) {
            $fileName = pdo_get("parking_imagename", array("openid" => $openid, "uniacid" => $_W['uniacid']), array('name'));
            $fileNameArray = explode(',', $fileName['name']);
            unset($fileName);
            foreach ($fileNameArray as $key => $value) {
                file_delete(MODULE_ROOT . "/static/images/" . $value . ".jpg");
            }
            pdo_delete('parking_imagename', array('openid' => $openid, 'uniacid' => $_W['uniacid']));
            echo 'success';
        }
    }

    public function CardLottery()
    {
        global $_W;
        global $_GPC;
        load()->func("common");
        if (empty($_GPC['number']) || empty($_GPC['money'])) {
            show_json(0, '请填写必要参数');
        }
        $UserData = pdo_fetchall("select openid from " . tablename("parking_verified") . " where uniacid={$_W['uniacid']} and status=1");
        $res = lottery($UserData, $_GPC['number']);
        $openId = null;
        if ($res) {
            foreach ($res['yes'] as $key => $value) {
                $openId .= $value['openid'] . ',';
            }
            $openId = trim($openId, ',');
            pdo_query("UPDATE " . tablename("parking_verified") . " set audit_status=1 where openid in(" . $openId . ")");  //更新中签状态
            pdo_query("UPDATE " . tablename("parking_monthcard") . "set money=" . $_GPC['money'] . " where uniacid=" . $_W['uniacid']);//设置购买金额
            $this->SendSuccessMessage($res['yes']);
            $this->SendErrorMessage($res['no']);
            show_json(1);
        } else {
            show_json(0, '错误失败');
        }
    }

    protected function SendSuccessMessage($data)
    {
        $urls = mobileUrl("parking/paycard");
        $conten = "通过";
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $this->WechatTemplateEdit($val['openid'], $urls, $conten);
            }
        } else {
            $this->WechatTemplateEdit($data, $urls, $conten);
        }
    }

    protected function SendErrorMessage($data)
    {
        $urls = '';
        $conten = "未通过";
        foreach ($data as $key => $val) {
            $this->WechatTemplateEdit($val['openid'], $urls, $conten);
        }
    }

    protected function WechatTemplateEdit($openid = null, $url = null, $text = null)
    {
        load()->classs('weixin.account');
        load()->func('logging');
        $template = [
            'first' => array(
                'value' => "您好，你的申请审核结果如下",
                'color' => 'black'),
            'keyword1' => array(
                'value' => $text,
                'color' => 'black'),
            'keyword2' => array(
                'value' => date('Y-m-d H:i:s', time()),
                'color' => 'black'),
            'keyword3' => array(
                'value' => '审核通过的，需点击进行支付',
                'color' => 'black'
            ),
            'remark' => array(
                'value' => "请点击支付,谢谢!",
                'color' => 'black')
        ];//消息模板
        $account_api = WeAccount::create();
        $status = $account_api->sendTplNotice($openid, TEMPLATEID, $template, $url);
        logging_run($status);
    }
}