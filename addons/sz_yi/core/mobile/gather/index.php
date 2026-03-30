<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
$time = TIMESTAMP;
$data = $_GPC;

if($op=='gather_info'){
    #预报详情
    $order_id = intval($data['id']);
    if($_W['isajax']){
        if(empty($order_id)){
            show_json(-1,['msg'=>'参数错误']);
        }
        $ordersn = pdo_fetchcolumn('select ordersn from '.tablename('centralize_parcel_order').' where id=:id',[':id'=>$order_id]);
//        $warehouse = pdo_fetch('select warehouse_code from '.tablename('centralize_warehouse_list').' where id=:id',[':id'=>$data['warehouse_id']]);
        #9+YY+（集货仓码）00+（入仓日期MMDD）+线路编码00+集运日期DD+终端（O、C、B）+（流程编码）00
//        $ordersn = substr_replace($ordersn,substr($warehouse['warehouse_code'],-2),3,2);
        $res = pdo_update('centralize_parcel_order',['warehouse_id'=>intval($data['warehouse_id'])],['id'=>$order_id]);
        $user = pdo_fetch('select b.openid,b.email,b.phone from '.tablename('centralize_parcel_order').' a left join '.tablename('website_user').' b on b.id=a.user_id where a.id=:id',[':id'=>$order_id]);
        if(!empty($user['openid'])){
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'订仓单号【'.$ordersn.'】信息已更新，请打开集运系统进行查看！',
                'keyword1' => '订仓单号【'.$ordersn.'】信息已更新，请打开集运系统进行查看！',
                'keyword2' => '仓库信息已更新',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => '点击查看详情',
                'url' => 'https://gather.gogo198.cn',
                'openid' => $user['openid'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            $res = httpRequest2('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        }elseif(!empty($user['email'])){
            #邮箱通知
            $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>'订仓单号【'.$ordersn.'】信息已更新','content'=>'请打开集运系统进行查看：https://gather.gogo198.cn']);
        }elseif(!empty($user['mobile'])){
            #短信通知
            $post_data = [
                'mobiles'=>$user['mobile'],
                'content'=>'订仓单号【'.$ordersn.'】信息已更新，请打开集运系统进行查看:https://gather.gogo198.cn 【GOGO】',
            ];
            $post_data = json_encode($post_data,true);
            $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
        }

        if($res){
            show_json(0,['msg'=>'提交成功，已通知客户']);
        }
    }else{
        $order = pdo_fetch('select a.*,b.serial_number from '.tablename('centralize_parcel_order').' a left join '.tablename('centralize_task').' b on b.id=a.task_id where a.id=:id',[':id'=>$order_id]);
        $warehouse = pdo_fetchall('select * from '.tablename('centralize_warehouse_list').' where status=0');
        if($order['line_id']){
            $order['line_info'] = pdo_fetch('select a.*,b.name from '.tablename('centralize_line_country').' a left join '.tablename('centralize_line_list').' b on b.id=a.pid where b.id=:id',[':id'=>$order['line_id']]);
        }
        $country_code = pdo_fetchall('select * from '.tablename('centralize_diycountry_content').' where pid=5');
        $province_code = pdo_fetchall('select * from '.tablename('centralize_diycountry_content').' where pid=7');
        $city_code = pdo_fetchall('select * from '.tablename('centralize_diycountry_content').' where pid=7');
        #物流商
        $merchant = pdo_fetchall('select * from '.tablename('centralize_manage_person').' where status=1');
        include $this->template('gather/gather_info');
    }
}
elseif($op=='add_warehouse'){
    $res = pdo_insert('centralize_warehouse_list',[
        'uid'=>$data['uid'],
        'warehouse_name'=>trim($data['warehouse_name']),
        'warehouse_code'=>trim($data['warehouse_code']),
        'name'=>trim($data['name']),
        'area_code'=>trim($data['area_code']),
        'mobile'=>trim($data['mobile']),
        'postal_code'=>intval($data['postal_code']),
        'country_code'=>$data['country_code'],
        'province_code'=>trim($data['province_code']),
        'city_code'=>trim($data['city_code']),
        'address1'=>trim($data['address1']),
        'addresss'=>isset($data['addresss'])?json_encode($data['addresss'],true):'',
        'createtime'=>time()
    ]);
    if($res){
        show_json(0,['msg'=>'配置成功，页面正在刷新']);
    }
}
elseif($op=='check_follow'){
    #检查用户有无关注公众号(关闭了公众号自动回复功能，不知道要不要重新打开)
    $fans_info = pdo_fetch('select follow from '.tablename('mc_mapping_fans').' where openid=:openid and uniacid=3',[':openid'=>$openid]);
//    dd($openid);
    if(isset($data['is_follow'])){
        if($data['type']==1){
            $info = pdo_fetch('select * from '.tablename('centralize_follow_jump').' where `type`=1 order by id desc');
            header('Location: '.$info['target_info'].'&openid='.$openid);
        }else{
            #默认会员注册
            header('Location: https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&m=sz_yi');
        }
    }else{
        if(empty($fans_info['follow'])){
            #未关注->跳转至微信公众号
            pdo_insert('centralize_follow_jump',[
                'type'=>$data['type'],
                'target_info'=>'https://gather.gogo198.cn/?s=gather/package_info&process1=19&process2=21&process3=21&id='.intval($data['id'])
            ]);
            header('Location: https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzA4MDQyMTMxMQ==&scene=110#wechat_redirect');
        }else{
            #已关注->跳转到指定系统
            if($data['type']==1){
                #集运网-管理预报
                header('Location: https://gather.gogo198.cn/?s=gather/package_info&process1=19&process2=21&process3=21&id='.intval($data['id']).'&openid='.$openid);
            }
        }
    }
}