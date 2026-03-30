<?php

/**
 * 积分相关
 */
class Credit {

    /**
     * 获取积分途径
     * @var type 
     */
    public static $getTypes = array(
    );

    /**
     * 使用积分途径
     * @var type 
     */
    public static $userTypes = array(
    );
    public $recommen_fans_reg_credit = 10; //推荐注册获取10积分

    public function save($entity) {
        global $_W;
        $rec = array_elements(array('fans_id', 'recommen_fans_id', 'type', 'event_desc', 'event_type', 'visit_phone', 'num', 'works_path', 'status', 'remark'), $entity);
        $rec['uniacid'] = $_W['uniacid'];
        $rec['create_time'] = time();

        if ($entity['event_type'] == 1) {
            //推荐好友注册
            $entity['num'] = 10;
            $filter = array();
            $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$entity['num']} where uid={$entity['fans_id']}"; //添加积分
            $ret = pdo_query($update_mem);
            if ($ret) {
                $rec['status'] = 1;
                $ret = pdo_insert('wxz_shoppingmall_credit_log', $rec);
                if (!empty($ret)) {
                    return pdo_insertid();
                } else {
                    return error(-1, '数据保存失败, 请稍后重试');
                }
            }
        }

        $sql = 'SELECT * FROM ' . tablename('wxz_shoppingmall_fans') . ' WHERE `uniacid`=:uniacid AND `openid`=:openid';
        $pars = array();
        $pars[':uniacid'] = $rec['uniacid'];
        $pars[':openid'] = $rec['openid'];
        $exists = pdo_fetch($sql, $pars);
        if (!empty($exists)) {
            $filter = array();
            $filter['uniacid'] = $_W['uniacid'];
            $filter['uid'] = $exists['uid'];
            $ret = pdo_update('wxz_shoppingmall_fans', $rec, $filter);
            if ($ret !== false) {
                return $exists['uid'];
            } else {
                return error(-2, '数据更新失败, 请稍后重试');
            }
        }
        $ret = pdo_insert('wxz_shoppingmall_fans', $rec);
        if (!empty($ret)) {
            return pdo_insertid();
        } else {
            return error(-1, '数据保存失败, 请稍后重试');
        }
    }

    /**
     * 推荐用户注册
     * @param type $fans_id
     * @param type $recommen_fans_id
     */
    public function recommen_fans($fans_id, $recommen_fans_id) {
        global $_W;
        if (($fans_id == $recommen_fans_id ) || !$fans_id || !$recommen_fans_id) {
            return false;
        }
//已经推荐过
        $recommen_count = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('wxz_shoppingmall_credit_log') . ' WHERE `recommen_fans_id`=:recommen_fans_id AND `fans_id`=:fans_id', array(':recommen_fans_id' => $recommen_fans_id, ':fans_id' => $fans_id));
        if ($recommen_count) {
            return false;
        }
        $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$this->recommen_fans_reg_credit},left_credit=left_credit+{$this->recommen_fans_reg_credit} where uid={$recommen_fans_id}"; //添加积分
        $ret = pdo_query($update_mem);
        if ($ret) {
            $credit_log_data = array(
                'uniacid' => $_W['uniacid'],
                'fans_id' => $fans_id,
                'recommen_fans_id' => $recommen_fans_id,
                'type' => 1,
                'event_desc' => "推荐好友注册，获取{$this->recommen_fans_reg_credit}积分",
                'event_type' => 1,
                'num' => $this->recommen_fans_reg_credit,
                'status' => 1,
                'send_time' => time(),
                'create_time' => time(),
            );
            $ret = pdo_insert('wxz_shoppingmall_credit_log', $credit_log_data);
        }
    }

    /**
     * 投稿积分
     * @param type $fans_id
     * @param type $works_path
     * @return boolean
     */
    public function contribute($fans_id, $works_path, $works_topic, $works_message) {
        global $_W;
        if (!$fans_id) {
            return false;
        }

        $credit_log_data = array(
            'uniacid' => $_W['uniacid'],
            'fans_id' => $fans_id,
            'type' => 1,
            'event_type' => 3,
            'works_path' => $works_path,
            'works_topic' => $works_topic,
            'works_message' => $works_message,
            'status' => 2,
            'create_time' => time(),
        );
        $ret = pdo_insert('wxz_shoppingmall_credit_log', $credit_log_data);
    }

    /**
     * 
     * 用户增加积分
     */
    public function userAddCredit($uid, $credit) {
        global $_W;
        require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';
        $modelFans = new Fans();
        $user = $modelFans->getOne($uid);
        $nowDate = date('Y-m-d'); //
        //生日翻倍积分
        if ($_W['module_setting']['credit_birth_credit'] > 0 && $user['birthday'] == $nowDate) {
            $credit = $_W['module_setting']['credit_birth_credit'] * $credit;
        }
        $update_fans = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$credit},left_credit=left_credit+{$credit} where uid='{$uid}'"; //添加积分
        return pdo_query($update_fans);
    }

}
