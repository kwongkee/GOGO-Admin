<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$operation = (!empty($_GPC['op']) ? $_GPC['op'] : 'display');
$type = $_GPC['type'];
$openid = m('user')->getOpenid();
$uniacid = $_W['uniacid'];
$r_type = array('退款', '退货退款', '换货');
$plugin_yunbi = p('yunbi');

if ($plugin_yunbi) {
    $yunbi_set = $plugin_yunbi->getSet();
}

if ($_W['isajax']) {
    if ($operation == 'display') {
        $pindex = max(1, intval($_GPC['page']));
        $psize = 5;
        $status = $_GPC['status'];
        $condition = '';
        //$params = array(':uniacid' => $uniacid, ':openid' => $openid);

        if ($status != '') {
            if($status == 0){
                $condition .= ' and status= "等待揽收"';
            }
            else {
                $condition .= ' and status= "揽收完成"';
            }
        }

        $list = pdo_fetchall('select * from ' . tablename('package_collect') . ' where 1 ' . $condition . ' order by create_time desc LIMIT ' . (($pindex - 1) * $psize) . ',' . $psize, $params);
        $total = pdo_fetchcolumn('select count(*) from ' . tablename('package_collect') . ' where 1 ' . $condition, $params);
        //var_dump($list);
        foreach ($list as $key => &$row) {
          $list[$key]['create_time'] = date('Y-m-d H:i:s', $row['create_time']);
          $list[$key]['form_buss'] = pdo_get('total_merchant_account',['id'=>$row['form_buss_id']],['company_name']);
          $list[$key]['to_buss'] = pdo_get('total_merchant_account',['id'=>$row['to_buss_id']],['company_name']);
          $list[$key]['collectuser'] = pdo_get('mc_members',['uid'=>$row['collect_user']],['realname','mobile','nickname']);


        }

        unset($row);
        show_json(1, array('total' => $total, 'list' => $list, 'pagesize' => $psize));

    }
}

include $this->template('member/easydeliver_collect_manage');


?>
