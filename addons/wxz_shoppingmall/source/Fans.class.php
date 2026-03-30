<?php

class Fans {

    /**
     * 会员等级
     * @var type 
     */
    public static $levels = array(
        1 => '普通',
        2 => '中级',
        3 => '高级',
    );

    public static function getLevels() {
        global $_W;
        return explode(',', $_W['module_setting']['fans_levels']);
    }

    /**
     * 保存一条用户记录至用户表中, 如果OpenID存在, 则更新记录
     * @param array $entity     用户数据
     * @return int|error        成功返回用户编号, 失败返回错误信息
     */
    public function save($entity) {
        global $_W;
        $rec = array_elements(array('openid', 'nickname', 'gender', 'state', 'city', 'country', 'avatar', 'client_ip', 'create_at'), $entity);
        $rec['uniacid'] = $_W['uniacid'];
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

    public function remove($uid, $isOpenid = false) {
        global $_W;
        $pars = array();
        $pars['uniacid'] = $_W['uniacid'];
        if ($isOpenid) {
            $pars['openid'] = $uid;
        } else {
            $pars['uid'] = intval($uid);
        }
        $del = pdo_delete('wxz_shoppingmall_fans', $pars);
        if ($del !== false) {
            return true;
        } else {
            return error(-1, '数据删除失败, 请稍后重试');
        }
    }

    public function modify($uid, $entity, $isOpenid = false) {
        global $_W;
        $rec = array_elements(array('unionid', 'nickname', 'gender', 'state', 'city', 'country', 'avatar'), $entity);
        $rec['uniacid'] = $_W['uniacid'];
        $filter = array();
        if ($isOpenid) {
            $filter['openid'] = $uid;
        } else {
            $filter['uid'] = intval($uid);
        }
        $ret = pdo_update('wxz_shoppingmall_fans', $rec, $filter);
        if ($ret !== false) {
            return true;
        } else {
            return error(-1, '数据更新失败, 请稍后重试');
        }
    }

    public function getOne($uid, $isOpenid = false) {
        global $_W;
        $pars = array();
        $pars[':uniacid'] = $_W['uniacid'];
        if ($isOpenid) {
            $pars[':openid'] = $uid;
            $sql = 'SELECT * FROM ' . tablename('wxz_shoppingmall_fans') . ' WHERE `uniacid`=:uniacid AND `openid` =:openid';
        } else {
            $pars[':uid'] = intval($uid);
            $sql = 'SELECT * FROM ' . tablename('wxz_shoppingmall_fans') . ' WHERE `uniacid`=:uniacid AND `uid` =:uid';
        }

        $ret = pdo_fetch($sql, $pars);
        if (!empty($ret)) {
            return $ret;
        } else {
            return array();
        }
    }

    public function getAll($filters = array(), $pindex = 0, $psize = 15, &$total = 0) {
        global $_W;
        $condition = '`f`.`uniacid`=:uniacid';
        $pars = array();
        $pars[':uniacid'] = $_W['uniacid'];
        if (!empty($filters['nickname'])) {
            $condition .= ' AND  `f`.`nickname` LIKE :nickname';
            $pars[':nickname'] = "%{$filters['nickname']}%";
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] == 'success') {
                $condition .= " AND `r`.`status`='success'";
            } else {
                $condition .= " AND (`r`.`status`!='success' OR `r`.`status` IS NULL)";
            }
        }
        $sql = 'FROM ' . tablename('wxz_shoppingmall_fans') . ' AS `f` LEFT JOIN ' . tablename('mbrp_records') . ' AS `r` ON (`f`.`uid`=`r`.`uid`) WHERE ';
        $sql .= $condition;
        if ($pindex > 0) {
            $total = pdo_fetchcolumn("SELECT COUNT(*) {$sql}", $pars);
            $start = ($pindex - 1) * $psize;
            $sql .= " ORDER BY `f`.`uid` DESC LIMIT {$start},{$psize}";
            $ds = pdo_fetchall("SELECT `f`.*,`r`.`status`,`r`.`id`,`r`.`type`,`r`.`fee`,`r`.`status` AS `send` {$sql}", $pars);
        } else {
            $sql .= " ORDER BY `f`.`uid` DESC";
            $ds = pdo_fetchall("SELECT `f`.*,`r`.`id`,`r`.`type`,`r`.`fee`,`r`.`status` AS `send` {$sql}", $pars);
        }
        if (!empty($ds)) {
            foreach ($ds as &$row) {
                $row['helps'] = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('mbrp_helps') . ' WHERE `uniacid`=:uniacid AND `from`=:uid', array(':uniacid' => $_W['uniacid'], ':uid' => $row['uid']));
            }
            unset($row);
        }
        return $ds;
    }

    public function update_by_id($id, $entity) {
        $ret = pdo_update('wxz_shoppingmall_fans', $entity, array('uid' => $id));
        if ($ret) {
            return true;
        }
        return false;
    }

    /**
     * 通过手机号获取用户
     * @param type $mobile
     */
    public function getByMobile($mobile) {
        global $_W;
        if ($mobile) {
            $sql = "SELECT * FROM " . tablename('wxz_shoppingmall_fans') . " WHERE `uniacid`={$_W['uniacid']} AND `mobile` ='{$mobile}'";
            $ret = pdo_fetch($sql);
        }

        if (!empty($ret)) {
            return $ret;
        } else {
            return array();
        }
    }

    /**
     * 用户积分操作
     * @param type $uid
     * @param type $credit
     * @param type $operate 1添加 2减去
     */
    public function updateCredit($uid, $credit, $operate) {
        global $_W;
        if ($operate == 1) {
            $update_fans = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$credit},left_credit=left_credit+{$credit} where uid='{$uid}' AND `uniacid`={$_W['uniacid']}"; //添加积分
        } else if ($operate == 2) {
            $update_fans = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set left_credit=left_credit-{$credit},use_credit=use_credit+{$credit} where uid='{$uid}'"; //减少积分
        }
        return pdo_query($update_fans);
    }

    /**
     * 用户余额操作
     * @param type $uid
     * @param type $money
     * @param type $operate 1添加 2减去
     */
    public function updateAccount($uid, $money, $operate) {
        global $_W;
        if ($operate == 1) {
            $update_fans = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set account=account+{$money} where uid='{$uid}' AND `uniacid`={$_W['uniacid']}"; //添加积分
        } else if ($operate == 2) {
            $update_fans = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set account=account-{$money} where uid='{$uid}' AND `uniacid`={$_W['uniacid']}"; //减少积分
        }
        return pdo_query($update_fans);
    }

}
