<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'].'';
$uni = $_W['uniacid'];
$data = $_GPC;

$company_id = pdo_fetchcolumn('select id from '.tablename('decl_user').' where openid=:openid and user_status=0',[':openid'=>$openid]);
$authority = 0;
if(empty($company_id)){
    $staff = pdo_fetch('select decl_id,role_id from '.tablename('approval_staff_list').' where openid=:openid',[':openid'=>$openid]);
    if(empty($staff)){
        $authority = 3;
    }else{
        $company_id = $staff['decl_id'];
        $authority = m('common')->check_approval_role($staff['role_id'],$staff['decl_id'],intval($data['func']));//0超级管理员 1可读可写 2可读禁写 3禁读禁写
    }
}


if($op=='display'){
    //配置管理+发起管理

    include $this->template('approval/index');
}
elseif($op=='setting'){
    //人员配置+审批配置

    include $this->template('approval/setting/index');
}
elseif($op=='person_set') {
    //人员配置

    include $this->template('approval/setting/person_set');
}
elseif($op=='role_list') {
    //角色列表
    if($data['pa']==1){
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($data['keywords']) ? trim($data['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = ' and name like "%'.$keyword.'%"';
        }
        $list = pdo_fetchall('select * from '.tablename('approval_role').' where decl_id=:decl_id '.$condition.' order by id desc limit '.$page.",".$limit,[':decl_id'=>$company_id]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetchcolumn('select count(id) from '.tablename('approval_role').' where decl_id=:decl_id'.$condition,[':decl_id'=>$company_id]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    $title='角色列表';
    include $this->template('approval/setting/role_list');
}
elseif($op=='save_role') {
    //保存角色信息
    if($data['edit']==1){
        $authList = [];
        foreach($data['authId'] as $k => $v){
            array_push($authList,['authId'=>$v,'authType'=>$data['authType'][$k]]);
        }

        if(empty($data['id'])){
            pdo_insert('approval_role',[
                'name'=>trim($data['name']),
                'pid'=>intval($data['pid']),
                'authList'=>json_encode($authList,true),
                'decl_id'=>$company_id,
                'createtime'=>time()
            ]);
        }else{
            pdo_update('approval_role',[
                'name'=>trim($data['name']),
                'pid'=>intval($data['pid']),
                'authList'=>json_encode($authList,true),
            ],['id'=>$data['id']]);
        }
        die(json_encode(['code'=>0,'msg'=>'保存成功!']));
    }else{
        if($data['id']>0){
            $title='编辑角色';
            $info = pdo_fetch('select * from '.tablename('approval_role').' where id=:id and decl_id=:decl_id',[':id'=>$data['id'],':decl_id'=>$company_id]);
            $info['authList'] = json_decode($info['authList'],true);

            //获取除自己外的所有角色
            $p_role = pdo_fetchall('select id,name from '.tablename('approval_role').' where id<>:id and decl_id=:decl_id',[':id'=>$data['id'],':decl_id'=>$company_id]);
        }else{
            $title='添加角色';
            $info=[];

            //获取所有角色
            $p_role = pdo_fetchall('select id,name from '.tablename('approval_role').' where decl_id=:decl_id',[':decl_id'=>$company_id]);
        }

        //获取应用列表
//        $app = pdo_fetchall('select * from '.tablename('site_nav').' where position=1 and status=1 and uniacid=:uni and multiid=45 order by displayorder,id desc',[':uni'=>$uni]);
//        $app = json_encode($app,true);
        $app = pdo_fetchall('select * from '.tablename('approval_function').' where 1 order by id asc');


        include $this->template('approval/setting/save_role');
    }
}
elseif($op=='del_role'){
    //删除角色
    $res = pdo_delete('approval_role',['id'=>intval($data['id'])]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'删除成功!']));
    }else{
        die(json_encode(['code'=>0,'msg'=>'删除失败，缺少参数!']));
    }
}
elseif($op=='event_list'){
    //事项列表
    if($data['pa']==1){
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($data['keywords']) ? trim($data['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = ' and name like "%'.$keyword.'%"';
        }
        $list = pdo_fetchall('select * from '.tablename('approval_event').' where decl_id=:decl_id '.$condition.' order by id desc limit '.$page.",".$limit,[':decl_id'=>$company_id]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetchcolumn('select count(id) from '.tablename('approval_event').' where decl_id=:decl_id'.$condition,[':decl_id'=>$company_id]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    $title='事项列表';
    include $this->template('approval/setting/event_list');
}
elseif($op=='save_event') {
    //保存角色信息
    if($data['edit']==1){
        if(empty($data['id'])){
            pdo_insert('approval_event',[
                'name'=>trim($data['name']),
                'decl_id'=>$company_id,
                'createtime'=>time()
            ]);
        }else{
            pdo_update('approval_event',[
                'name'=>trim($data['name']),
            ],['id'=>$data['id']]);
        }
        die(json_encode(['code'=>0,'msg'=>'保存成功!']));
    }else{
        if($data['id']>0){
            $title='编辑事项';
            $info = pdo_fetch('select * from '.tablename('approval_event').' where id=:id and decl_id=:decl_id',[':id'=>intval($data['id']),':decl_id'=>$company_id]);
        }else{
            $title='添加事项';
            $info=[];
        }

        #查询当前用户有无该事项的修改权限
        $ishave_auth = pdo_fetchcolumn('select id from '.tablename('approval_staff_list').' where openid=:openid and decl_id=:decl_id and find_in_set('.intval($data['id']).',event_ids)',[':openid'=>$openid,':decl_id'=>$company_id]);
        if(empty($ishave_auth)){
            $is_manager = pdo_fetchcolumn('select id from '.tablename('decl_user').' where openid=:openid and user_status=0',[':openid'=>$openid]);
            if($is_manager>0){
                $ishave_auth=1;
            }else{
                $ishave_auth=0;
            }
        }
        include $this->template('approval/setting/save_event');
    }
}
elseif($op=='del_event'){
    //删除角色
    $res = pdo_delete('approval_event',['id'=>intval($data['id'])]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'删除成功!']));
    }else{
        die(json_encode(['code'=>0,'msg'=>'删除失败，缺少参数!']));
    }
}
elseif($op=='person_list'){
    //人员列表
    if($data['pa']==1){
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($data['keywords']) ? trim($data['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = 'and name like "%'.$keyword.'%"';
        }
        $list = pdo_fetchall('select * from '.tablename('approval_staff_list').' where decl_id=:decl_id '.$condition.' order by id desc limit '.$page.",".$limit,[':decl_id'=>$company_id]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('approval_staff_list').' where decl_id=:decl_id '.$condition,[':decl_id'=>$company_id]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count['c']]));
    }
    $title='人员列表';
    include $this->template('approval/setting/person_list');
}
elseif($op=='save_person'){
    //编辑人员
    if($data['edit']==1){
        if($data['type']==1){
            //信息修改
            $upd_data = [
                'name'=>trim($data['user_name']),
                'tel'=>trim($data['user_tel']),
                'email'=>trim($data['user_email']),
            ];
        }elseif($data['type']==2){
            //角色修改
            $upd_data = [
                'role_id'=>intval($data['role_id'])
            ];
        }elseif($data['type']==3){
            //事项修改
            $upd_data = [
                'event_ids'=>trim($data['event_ids'])
            ];
        }

        $res = pdo_update('approval_staff_list',$upd_data,['id'=>$data['id'],'decl_id'=>$company_id]);
        if($res){
            die(json_encode(['code'=>0,'msg'=>'保存成功!']));
        }
    }else{
        $info = pdo_fetch('select * from '.tablename('approval_staff_list').' where id=:id and decl_id=:decl_id',[':id'=>intval($data['id']),':decl_id'=>$company_id]);
        if($data['typ']==1){
            $title='信息修改';
        }elseif($data['typ']==2){
            $title='角色修改';
        }elseif($data['typ']==3){
            $title='事项修改';
        }

        //角色
        $role = pdo_fetchall('select * from '.tablename('approval_role').' where decl_id=:decl_id order by id desc',[':decl_id'=>$company_id]);

        //事项
        $event = pdo_fetchall('select * from '.tablename('approval_event').' where decl_id=:decl_id order by id desc',[':decl_id'=>$company_id]);
        $event = json_encode($event,true);
        include $this->template('approval/setting/save_person');
    }
}
elseif($op=='get_qrcode'){
    #获取分享二维码
    $info = pdo_fetch('select id,approval_qrcode from '.tablename('approval_staff_list').' where openid=:openid',[':openid'=>$openid]);

    $qrcode = '';
    $folder = '/addons/sz_yi/static/QRcode/approval/';
    $url = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=index&op=add_person&decl_id='.$company_id;

    if(empty($info['id'])){
        $info = pdo_fetch('select approval_qrcode from '.tablename('decl_user').' where openid=:openid',[':openid'=>$openid]);
        $info['id'] = 0;
        if(empty($info['approval_qrcode'])){
            $url .= '&pid='.$info['id'];
            $qrcode = m('common')->generate_qrcode($url,$folder);
            pdo_update('decl_user',['approval_qrcode'=>$qrcode],['openid'=>$openid]);
        }else{
            $qrcode = $info['approval_qrcode'];
        }
    }else{
        if(empty($info['approval_qrcode'])){
            $url .= '&pid='.$info['id'];
            $qrcode = m('common')->generate_qrcode($url,$folder);
            pdo_update('approval_staff_list',['approval_qrcode'=>$qrcode],['openid'=>$openid]);
        }else{
            $qrcode = $info['approval_qrcode'];
        }
    }

    die(json_encode(['code'=>0,'approval_qrcode'=>$qrcode]));
}
elseif($op=='del_person'){
    //删除角色
    $res = pdo_update('decl_user',['user_status'=>1],['id'=>intval($data['id'])]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'禁用成功!']));
    }else{
        die(json_encode(['code'=>0,'msg'=>'禁用失败，缺少参数!']));
    }
}
elseif($op=='add_person'){
    if($data['pa']==1){
        $time = time();
        $res = pdo_insert('approval_staff_list',[
            'openid'=>$openid,
            'name'=>trim($data['name']),
            'tel'=>trim($data['tel']),
            'email'=>trim($data['email']),
            'pid'=>intval($data['pid']),//上级id
            'decl_id'=>intval($data['decl_id']),//公司id
            'createtime'=>$time
        ]);

        if($res){
            //发送通知上级
            $up_openid = '';
            if(empty(intval($data['pid']))){
                $up_openid = pdo_fetchcolumn('select openid from '.tablename('decl_user').' where id=:decl_id and user_status=0',[':decl_id'=>intval($data['decl_id'])]);
            }else{
                $up_openid = pdo_fetchcolumn('select openid from '.tablename('approval_staff_list').' where decl_id=:decl_id and id=:pid',[':decl_id'=>intval($data['decl_id']),':pid'=>intval($data['pid'])]);
            }
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'first' =>'您好，人员['.trim($data['name']).']已加入，请进入人员列表对该人员进行配置！',
                'keyword1' => '新增人员',
                'keyword2' => '已加入',
                'keyword3' => date('Y-m-d H:i:s',$time),
                'remark' => '点击查看详情',
                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&p=index&m=sz_yi&op=display#wechat_redirect',
                'openid' => $up_openid,
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

            die(json_encode(['code'=>0,'msg'=>'注册加入成功!']));
        }
    }else{
        $ishave_staff = pdo_fetch('select id from '.tablename('approval_staff_list').' where openid=:openid',[':openid'=>$openid]);
        if(empty($ishave_staff['id'])){
            #获取公司名称、邀请人名称
            if(empty($data['pid'])){
                $info = pdo_fetch('select a.user_name as inviter_name,c.name as company_name from '.tablename('decl_user').' a left join '.tablename('enterprise_members').' b on b.openid=a.openid left join '.tablename('enterprise_basicinfo').' c on c.member_id=b.id where a.id=:decl_id',[':decl_id'=>intval($data['decl_id'])]);
            }else{
                $info = pdo_fetch('select a.name as inviter_name,d.name as company_name from '.tablename('approval_staff_list').' a left join '.tablename('decl_user').' b on b.id=a.decl_id left join '.tablename('enterprise_members').' c on c.openid=b.openid left join '.tablename('enterprise_basicinfo').' d on d.member_id=b.id where a.id=:pid and a.decl_id=:decl_id',[':pid'=>intval($data['pid']),':decl_id'=>intval($data['decl_id'])]);
            }
            $title = $info['inviter_name'].'邀请您加入'.$info['company_name'];
        }
        include $this->template('approval/setting/add_person');
    }
}
elseif($op=='approval_set'){
    //审批配置

    if($data['pa']==1){
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($data['keywords']) ? trim($data['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = 'and name like "%'.$keyword.'%"';
        }
        $list = pdo_fetchall('select * from '.tablename('approval_event').' where decl_id=:decl_id '.$condition.' order by id desc limit '.$page.",".$limit,[':decl_id'=>$company_id]);
        foreach($list as $k=>$v){
            $list[$k]['role_id'] = pdo_fetchcolumn('select name from '.tablename('approval_role').' where id=:id and decl_id=:decl_id',[':id'=>$v['role_id'],':decl_id'=>$company_id]);
            if(!$list[$k]['role_id']){
                $list[$k]['role_id'] = '';
            }
        }
        $count = pdo_fetch('select count(id) as c from '.tablename('approval_event').' where decl_id=:decl_id '.$condition,[':decl_id'=>$company_id]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count['c']]));
    }
    $title='审批配置';
    include $this->template('approval/setting/approval_set');
}
elseif($op=='save_setting'){
    //配置事项
    if($data['edit']==1){
//        $diyFormData = [];
//        foreach($data['form_name'] as $k => $v){
//            array_push($diyFormData,['label_name'=>$v,'label_val'=>$data['form_val'][$k]]);
//        }
        $upd_data = [
            'role_id'=>intval($data['role_id']),
            'approval_type'=>intval($data['approval_type']),
            'role_ids'=>trim($data['role_ids']),
            'copyInfoFor_roleIds'=>intval($data['copyInfoFor_roleIds']),
            'diyformid'=>intval($data['diyformid']),
            'authid'=>intval($data['authid']),
        ];

        $res = pdo_update('approval_event',$upd_data,['id'=>intval($data['id']),'decl_id'=>$company_id]);
        if($res){
            die(json_encode(['code'=>0,'msg'=>'配置成功!']));
        }
    }else{
        #事件
        $info = pdo_fetch('select * from '.tablename('approval_event').' where id=:id and decl_id=:decl_id',[':id'=>$data['id'],':decl_id'=>$company_id]);
//        $info['diyFormData'] = json_decode($info['diyFormData'],true);
        #角色
        $role = pdo_fetchall('select * from '.tablename('approval_role').' where decl_id=:decl_id order by id desc',[':decl_id'=>$company_id]);
        $roles = json_encode($role,true);
        //获取应用列表
//        $app = pdo_fetchall('select * from '.tablename('site_nav').' where position=1 and status=1 and uniacid=:uni and multiid=45 order by displayorder,id desc',[':uni'=>$uni]);
//        $app = json_encode($app,true);
        //获取表单
        $diyForm = pdo_fetchall('select id,name from '.tablename('approval_diyform').' where ( decl_id=:decl_id or decl_id=0 ) and status=0 order by id desc',[':decl_id'=>$company_id]);

        $title='配置事务';
        include $this->template('approval/setting/save_setting');
    }
}
elseif($op=='diyForm_set'){
    #表单配置
    if($data['pa']==1){
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($data['keywords']) ? trim($data['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = 'and name like "%'.$keyword.'%"';
        }
        $list = pdo_fetchall('select * from '.tablename('approval_diyform').' where ( decl_id=:decl_id or decl_id=0 ) '.$condition.' and status=0 order by id desc limit '.$page.",".$limit,[':decl_id'=>$company_id]);
        $count = pdo_fetch('select count(id) as c from '.tablename('approval_diyform').' where ( decl_id=:decl_id or decl_id=0 ) '.$condition.'  and status=0',[':decl_id'=>$company_id]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count['c']]));
    }
    $title = '表单配置';
    include $this->template('approval/setting/diyForm_set');
}
elseif($op=='save_diyForm'){
    #保存表单
    if($data['edit']==1){
        $diyFormData = [];
        foreach($data['param'] as $k=>$v){
            array_push($diyFormData,[
                'param'=>$v,
                'label_name'=>$data['label_name'][$k],
                'select_value'=>$data['select_value'][$k],
                'can_add'=>$data['can_add'][$k],
            ]);
        }
        $diyFormData = json_encode($diyFormData,true);
        if($data['id']>0){
            #编辑
            $res = pdo_update('approval_diyform',[
                'name'=>trim($data['name']),
                'diy_form_fields'=>$diyFormData,
            ],['id'=>intval($data['id'])]);
        }else{
            #新增
            $res = pdo_insert('approval_diyform',[
                'openid'=>0,//$openid
                'decl_id'=>$company_id,//$company_id
                'name'=>trim($data['name']),
                'diy_form_fields'=>$diyFormData,
                'createtime'=>time(),
                'status'=>0
            ]);
        }

        if($res){
            die(json_encode(['code'=>0,'msg'=>'保存成功！']));
        }
    }
    else{
        if($data['id']>0){
            $issee = intval($data['issee']);
            if($issee==1){
                $title = '查看';
            }else{
                $title = '编辑';
            }
            $info = pdo_fetch('select * from '.tablename('approval_diyform').' where id=:id',[':id'=>intval($data['id'])]);
            $info['diy_form_fields'] = json_decode($info['diy_form_fields'],true);

            $can_add = [];//将可新增的数据放在可新增参数中
            foreach($info['diy_form_fields'] as $k=>$v){
                $info['diy_form_fields'][$k]['random_name'] = rand(1111,9999);

                if($v['param']==2){
                    $info['diy_form_fields'][$k]['select_value2'] = explode(',',$v['select_value']);
                }elseif($v['param']==3){
                    $select_value = explode(',',$v['select_value']);
                    $select_value2 = [];
                    foreach($select_value as $k2=>$v2){
                        array_push($select_value2,['name'=>$v2,'id'=>$v2]);
                    }

                    $info['diy_form_fields'][$k]['select_value2'] = json_encode($select_value2,true);
                }

                if($v['can_add']==2){
                    #可新增
                    array_push($can_add,$info['diy_form_fields'][$k]);
                }
            }

        }else{
            $title = '新增';
            $info = [];
            $issee=0;
        }

        include $this->template('approval/setting/save_diyForm');
    }
}
elseif($op=='del_diyForm'){
    $id = $data['id'];
    $res = pdo_update('approval_diyform',['status'=>1],['id'=>$id]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'删除成功！']));
    }
}