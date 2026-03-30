<?php

global $_W, $_GPC;

$filters = array();
require_once WXZ_SHOPPINGMALL . '/source/Fans.class.php';


if (checksubmit()) {
    if ($_FILES["credit"]) {
        $handle = @fopen($_FILES["credit"]["tmp_name"], "r");
        $result = array();
        $mobileFans = new Fans();

        if ($handle) {
            while (!feof($handle)) {
                $content = fgets($handle, 4096);
                if ($content) {
                    $content = explode(',', $content);
                    list($mobile, $credit, $mark) = $content;
                    $mark = trim(iconv("GBK", "UTF-8//IGNORE", $mark));
                    $fans = $mobileFans->getByMobile($mobile);
                    if (!is_numeric($credit)) {
                        continue;
                    }
                    if (!$fans) {
                        continue;
                    }
                    //添加积分
                    if ($credit > 0) {
                        $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set credit=credit+{$credit},left_credit=left_credit+{$credit} where uid='{$fans['uid']}'"; //添加积分
                    } else {
                        $update_mem = "UPDATE  " . tablename('wxz_shoppingmall_fans') . " set left_credit=left_credit+{$credit} where uid='{$fans['uid']}'"; //添加积分
                    }

                    $ret = pdo_query($update_mem);
                    if ($ret) {
                        //插入日志
                        $credit_log_data = array(
                            'uniacid' => $_W['uniacid'],
                            'fans_id' => $fans['uid'],
                            'type' => 1,
                            'event_type' => 4,
                            'event_desc' => '后台导入积分',
                            'num' => $credit,
                            'remark' => $mark,
                            'status' => 2,
                            'send_time' => time(),
                            'create_at' => time(),
                        );

                        if ($credit > 0) {
                            $credit_log_data['operate'] = 1;
                        } else {
                            $credit_log_data['operate'] = 2;
                        }

                        $ret = pdo_insert('wxz_shoppingmall_credit_log', $credit_log_data);

                        $result[] = array(
                            'username' => $fans['username'],
                            'mobile' => $fans['mobile'],
                            'credit' => $fans['left_credit'],
                            'left_credit' => $fans['left_credit'] + $credit,
                            'remark' => $mark,
                            'status' => '成功',
                        );
                    } else {
                        $result[] = array(
                            'username' => $fans['username'],
                            'mobile' => $fans['mobile'],
                            'credit' => $fans['left_credit'],
                            'left_credit' => $fans['left_credit'],
                            'remark' => $mark,
                            'status' => '失败',
                        );
                    }
                }
            }
            fclose($handle);
        }
    }
}
include $this->template('web/upload_credit');
?>
