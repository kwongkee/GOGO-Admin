<?php
/**
 * User: 仓储系统
 * Date: 2022/7/25
 * Time: 14:11
 */
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

$openid = $_W['openid'];
$notice_manage = 'ov3-bt5vIxepEjWc51zRQNQbFSaQ';//ov3-bt8keSKg_8z9Wwi-zG1hRhwg  ov3-bt5vIxepEjWc51zRQNQbFSaQ

if($op=='display'){
    //登录注册

    $warehouse_list = pdo_fetchall('select * from '.tablename('centralize_warehouse_list').' where 1 order by id desc');

    include $this->template('warehouse/login/index');
}
elseif($op=='register'){
    //注册
    $data = $_GPC['data'];
    $code = $_SESSION['warehouse_code'];
    if(mb_strlen($data['pwd'])<10){
        show_json(0,['msg'=>'请输入至少10位密码']);
    }

    if($data['name'] == '') {
        show_json(0,['msg'=>'请输入姓名']);
    }

    if($data['mobile'] == '') {
        show_json(0,['msg'=>'请输入手机号码']);
    }

    if(!Mobile($data['mobile'])) {
        show_json(0,['msg'=>'手机号码格式不正确']);
    }

    // 查看该仓库管理员是否已经注册
    $dl = pdo_fetch('select id from '.tablename('warehouse_manager').' where mobile=:mobile',[':mobile'=>trim($data['mobile'])]);
    if(!empty($dl)) {
        show_json(0,['msg'=>'该手机号已注册，请勿重复注册']);
    }

    if($code != trim($data['code'])) {
        show_json(0,['msg'=>'短信验证码不正确']);
    }

    $ins_data = [
        'name'=>trim($data['name']),
        'openid'=>$_W['openid'],
        'type'=>intval($data['type']),
        'pwd'=>md5($data['pwd']),
        'mobile'=>trim($data['mobile']),
        'warehouse_id'=>intval($data['warehouse_id']),
        'status'=>0,
        'createtime'=>TIMESTAMP
    ];

    $ins = pdo_insert('warehouse_manager',$ins_data);
    $ins_id = pdo_insertid();
    if(!$ins){
        show_json(0,['msg'=>'注册失败']);
    }

    $is_have_mem = pdo_fetch('select id from '.tablename('sz_yi_member').' where openid=:openid',[':openid'=>$_W['openid']]);
    if(empty($is_have_mem['id'])){
        pdo_insert('sz_yi_member',[
            'uniacid'=>$_W['uniacid'],
            'openid'=>$_W['openid'],
            'realname'=>$ins_data['name'],
            'nickname'=>$ins_data['name'],
            'mobile'=>$ins_data['mobile'],
            'pwd'=>$ins_data['pwd'],
            'createtime'=>TIMESTAMP
        ]);
    }

    //向管理員发送消息
    $job = '';
    switch($ins_data['type']){
        case 1:
            $job = '国内仓库管理员';
            break;
        case 2:
            $job = '香港仓库管理员';
            break;
        case 3:
            $job = '国外仓库管理员';
            break;
    }

    $post2 = json_encode([
        'call'=>'confirmCollectionNotice',
        'first' =>'管理员您好，['.$ins_data['name'].']申请成为['.$job.']岗位，请及时审核！',
        'keyword1' => '['.$job.']岗位',
        'keyword2' => '待审核',
        'keyword3' => date('Y-m-d H:i:s',time()),
        'remark' => '',
        'url' => 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&p=warehouse_account_check&uid='.$ins_id.'&do=member&m=sz_yi',
        'openid' => $notice_manage,
        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
    ]);

    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post2);

    show_json(1,['msg'=>'注册成功，请等待管理员审核...']);
}
elseif($op=='sendcode'){
    $mobile = $_GPC['mobile'];
    if (empty($mobile)) {
        show_json(0, '请填入手机号');
    }
    $code = rand(1000, 9999);
//    $code = 6666;
    $_SESSION['codetime'] = time();
    $_SESSION['warehouse_code'] = $code;
    $_SESSION['code_mobile'] = $mobile;
    $issendsms = $this->sendSms($mobile, $code);
//    $issendsms['result']['success']=1;
    $set = m('common')->getSysset();

    if ($set['sms']['type'] == 1) {
        if ($issendsms['SubmitResult']['code'] == 2) {
            show_json(1);
            return 1;
        }

        show_json(0, $issendsms['SubmitResult']['msg']);
        return 1;
    }

    if (isset($issendsms['result']['success'])) {
        show_json(1);
        return 1;
    }

    if (!$issendsms) {
        show_json(1);
        return 1;
    }else{
        show_json(0, $issendsms['msg'] . '/' . $issendsms['sub_msg']);
        return 1;
    }
}
elseif($op=='login'){
    $data = $_GPC;

    $account = pdo_fetch('select * from '.tablename('warehouse_manager').' where pwd=:pwd and mobile=:mobile and openid=:openid',[':pwd'=>md5($data['acc_pwd']),':mobile'=>trim($data['acc']),':openid'=>$openid]);

    if(empty($account)){
        show_json(0,['msg'=>'找不到该手机号']);
    }

    if(empty($account['status'])){
        show_json(0,['msg'=>'请等待管理员审核']);
    }

    $_SESSION['warehouse_manager'] = $account;

    show_json(1,['msg'=>'登录成功']);
}
elseif($op=='forget'){
    if($_GPC['ispost']==1){
        $data = $_GPC['data'];
        $code = $_SESSION['warehouse_code'];

        if($data['mobile'] == '') {
            show_json(0,['msg'=>'请输入手机号码']);
        }

        if(!Mobile($data['mobile'])) {
            show_json(0,['msg'=>'手机号码格式不正确']);
        }

        if($code != trim($data['code'])) {
            show_json(0,['msg'=>'短信验证码不正确']);
        }

        $res = pdo_update('warehouse_manager',['pwd'=>md5(trim($data['pwd']))],['mobile'=>trim($data['mobile']),'openid'=>$_W['openid']]);
        pdo_update('sz_yi_member',['pwd'=>md5(trim($data['pwd']))],['mobile'=>trim($data['mobile']),'openid'=>$_W['openid']]);
        if(!$res){
            show_json(0,['msg'=>'该手机号不存在']);
        }
        show_json(1,['msg'=>'操作成功，请登录']);
    }else{
        include $this->template('warehouse/login/forget');
    }
}
