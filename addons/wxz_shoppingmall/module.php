<?php

/**
 * test_demo模块定义
 *
 * @author sdfdasfdas
 * @url http://bbs.we7.cc/
 */
defined('IN_IA') or exit('Access Denied');

class Wxz_shoppingmallModule extends WeModule {

    public function fieldsFormDisplay($rid = 0) {
        global $_W;
        if (!empty($rid)) {
            $setting = pdo_fetch('SELECT * FROM ' . tablename('wxz_shoppingmall_reply_setting') . ' WHERE `uniacid` = :uniacid and `rid` = :rid AND isdel=0', array(':uniacid' => $_W['uniacid'], ':rid' => $rid));
        }
        load()->web('tpl');
        include $this->template('form');
    }

    public function fieldsFormValidate($rid = 0) {
//规则编辑保存时，要进行的数据验证，返回空串表示验证无误，返回其他字符串将呈现为错误提示。这里 $rid 为对应的规则编号，新增时为 0
        return '';
    }

    public function fieldsFormSubmit($rid) {
        global $_GPC, $_W;
        $aid = intval($_GPC['aid']);


        $data = array(
            'uniacid' => $_W['uniacid'],
            'title' => $_GPC['title'],
            'img' => $_GPC['img'],
            'desc' => $_GPC['desc'],
            'link' => $_GPC['link'],
        );

        if ($aid) {
            pdo_update('wxz_shoppingmall_reply_setting', $data, array('rid' => $rid));
        } else {
            $data['rid'] = $rid;
            pdo_insert('wxz_shoppingmall_reply_setting', $data);
        }
    }

    public function ruleDeleted($rid) {
//删除规则时调用，这里 $rid 为对应的规则编号
    }

    public function settingsDisplay($settings) {
        global $_W, $_GPC;
        load()->func('tpl');
//点击模块设置时将调用此方法呈现模块设置页面，$settings 为模块设置参数, 结构为数组。这个参数系统针对不同公众账号独立保存。
//在此呈现页面中自行处理post请求并保存设置参数（通过使用$this->saveSettings()来实现）
        $settings = $this->module['config'];

//已发放金额
        if (checksubmit()) {
            $fanslevels = explode(',', $_GPC['fans_levels']);
            foreach ($fanslevels as $fanslevel) {
                $retFansLevel = trim($fanslevel);
                if ($retFansLevel) {
                    $retFansLevels[] = $retFansLevel;
                }
            }
            if ($retFansLevels) {
                $retFansLevels = implode(',', $retFansLevels);
            }

//字段验证, 并获得正确的数据$dat
            $data = array(
                'name' => $_GPC['name'],
                'logo_system' => $_GPC['logo_system'],
                'logo_buy' => $_GPC['logo_buy'],
                'sms_appkey' => $_GPC['sms_appkey'],
                'sms_appSecret' => $_GPC['sms_appSecret'],
                'sms_tpl' => $_GPC['sms_tpl'],
                'sms_sign' => $_GPC['sms_sign'],
                'sms_max_send_msg_num' => $_GPC['sms_max_send_msg_num'],
                'attach_url' => $_GPC['attach_url'], //远程附件链接
                'credit_reg2_credit' => $_GPC['credit_reg2_credit'], //array  1完善资料送积分  2完善资料送优惠券
                'credit_reg2_credit_num' => (int) $_GPC['credit_reg2_credit_num'],
                'credit_reg2_credit_coupon_id' => (int) $_GPC['credit_reg2_credit_coupon_id'],
                'reg2_credit_coupon_start_date' => $_GPC['reg2_credit_coupon_start_date'], //从何时注册的新会员送优惠券
                'reg2_credit_coupon_end_date' => $_GPC['reg2_credit_coupon_end_date'], //
                'credit_birth_credit' => (int) $_GPC['credit_birth_credit'],
                'fans_levels' => $retFansLevels, //粉丝等级
//强制关注
                'force_follow' => (int) $_GPC['force_follow'],
                'force_follow_url' => (string) $_GPC['force_follow_url'], //强制关注链接
            );
            $data = array_merge($settings, $data);

            if ($this->saveSettings($data)) {
                message('保存成功', 'refresh');
            }
        }
        include $this->template('setting');
//这个操作被定义用来呈现 管理中心导航菜单
    }

}
