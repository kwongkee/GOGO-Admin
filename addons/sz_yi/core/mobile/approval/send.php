<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'].'1';
$uni = $_W['uniacid'];
$data = $_GPC;

$company_id = pdo_fetchcolumn('select id from '.tablename('decl_user').' where openid=:openid and user_status=0',[':openid'=>$openid]);
if(empty($company_id)){
    $company_id = pdo_fetchcolumn('select decl_id from '.tablename('approval_staff_list').' where openid=:openid',[':openid'=>$openid]);
}

if($op=='display'){
    //发起管理
    include $this->template('approval/send/index');
}
elseif($op=='initiate_approval'){
    #发起审批
    if($data['pa']==1){
        #发起审批提交
//        approval_initiate
//        approval_list
        $time = time();

        #数组组装
        $diyFormData = [
            'list_file'=>$data['list_file'],
            'value'=>$data['value'],//单个属性
        ];
        if(isset($data['add_value_num'])){
            if($data['add_value_num']>0){
                for($i=0;$i<=$data['add_value_num'];$i++){
                    if($i==0){
                        $diyFormData['add_value'][$i] = [];
                        foreach($data['add_value'] as $k=>$v){
                            array_push($diyFormData['add_value'][$i],['value'=>$v,'random_name'=>rand(1111,9999)]);
                        }
                    }else{
                        $diyFormData['add_value'][$i] = [];
                        $c = intval($i)+1;
                        foreach($data['add_value'.strval($c)] as $k=>$v){
                            array_push($diyFormData['add_value'][$i],['value'=>$v,'random_name'=>rand(1111,9999)]);
                        }
                    }
                }
            }else{
                $diyFormData['add_value'] = [];
                foreach($data['add_value'] as $k=>$v){
                    array_push($diyFormData['add_value'],['value'=>$v,'random_name'=>rand(1111,9999)]);
                }
            }
        }
        $diyFormData = json_encode($diyFormData,true);
        
        $res = pdo_insert('approval_initiate',[
            'openid'=>$openid,
            'event_id'=>intval($data['event_id']),
            'diyFormData'=>$diyFormData,
            'status'=>1,
            'opera'=>0,
            'createtime'=>$time,
        ]);
        $insert_id = pdo_insertid();
        if($res){
            #根据这一事项分别通知审批人员
            $event = pdo_fetch('select * from '.tablename('approval_event').' where id=:id',[':id'=>intval($data['event_id'])]);
            $role_ids = explode(',',$event['role_ids']);

            #发起人信息
            $initiate_info = pdo_fetch('select * from '.tablename('approval_staff_list').' where openid=:openid and decl_id=:decl_id',[':openid'=>$openid,':decl_id'=>$company_id]);

            $notice_man = '';
            if($event['approval_type']==1){
                #串联,按顺序先通知该角色人员
                $notice_man = $role_ids[0];

                #获取该角色下的人员信息
                $approval_info = pdo_fetchall('select * from '.tablename('approval_staff_list').' where role_id=:role_id and decl_id=:decl_id',[':role_id'=>$notice_man,':decl_id'=>$company_id]);
                if(!empty($approval_info)){
                    foreach($approval_info as $k=>$v){
                        #将通知插入数据表
                        pdo_insert('approval_list',[
                            'initiate_id'=>$insert_id,
                            'approval_method'=>$event['approval_type'],
                            'openid'=>$v['openid'],
                            'createtime'=>$time,
                        ]);
                        $post = json_encode([
                            'call'=>'confirmCollectionNotice',
                            'first' =>'您好，人员['.trim($initiate_info['name']).']已提交事项['.$event['name'].']，请进入系统进行审批！',
                            'keyword1' => '事项提交',
                            'keyword2' => '已提交',
                            'keyword3' => date('Y-m-d H:i:s',$time),
                            'remark' => '点击查看详情',
                            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=my_approval#wechat_redirect',
                            'openid' => $v['openid'],
                            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                        ]);
                        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                    }
                }
            }elseif($event['approval_type']==2){
                #并联

                #根据各角色查找各人员
                $notice_man = $role_ids;
                foreach($notice_man as $k=>$v){
                    #获取该角色下的人员信息
                    $approval_info = pdo_fetchall('select * from '.tablename('approval_staff_list').' where role_id=:role_id and decl_id=:decl_id',[':role_id'=>$v,':decl_id'=>$company_id]);
                    if(!empty($approval_info)){
                        foreach($approval_info as $kk=>$vv){
                            #将通知插入数据表
                            pdo_insert('approval_list',[
                                'initiate_id'=>$insert_id,
                                'approval_method'=>$event['approval_type'],
                                'openid'=>$vv['openid'],
                                'createtime'=>$time,
                            ]);

                            $post = json_encode([
                                'call'=>'confirmCollectionNotice',
                                'first' =>'您好，人员['.trim($initiate_info['name']).']已提交事项['.$event['name'].']，请进入系统进行审批！',
                                'keyword1' => '事项提交',
                                'keyword2' => '已提交',
                                'keyword3' => date('Y-m-d H:i:s',$time),
                                'remark' => '点击查看详情',
                                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=my_approval#wechat_redirect',
                                'openid' => $vv['openid'],
                                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                            ]);
                            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                        }
                    }
                }
            }

            die(json_encode(['code'=>0,'msg'=>'提交审批成功，请等待管理员审核！']));
        }
    }
    elseif($data['pa']==2){
        #查看用户是否有该事项的权限并获取事项详情表单
        $event_id = intval($data['event_id']);
        //获取事项角色id、事项表单
        $event = pdo_fetch('select role_id,diyformid from '.tablename('approval_event').' where id=:id and decl_id=:decl_id',[':id'=>$event_id,':decl_id'=>$company_id]);
        //判断当前用户是不是该角色
        $decl_user = pdo_fetchcolumn('select id from '.tablename('approval_staff_list').' where openid=:openid and role_id=:role_id',[':openid'=>$openid,':role_id'=>$event['role_id']]);
        if(!empty($decl_user)){
            #有权限
            $info = pdo_fetch('select * from '.tablename('approval_diyform').' where id=:id',[':id'=>$event['diyformid']]);
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
//                    $info['diy_form_fields'][$k]['select_value2'] = json_encode($select_value2,true);
                    $info['diy_form_fields'][$k]['select_value2'] = $select_value2;
                }

                if($v['can_add']==2){
                    #可新增
                    array_push($can_add,$info['diy_form_fields'][$k]);
                }
            }
            die(json_encode(['code'=>0,'msg'=>'获取成功！请填写该事项信息。','info'=>$info,'can_add'=>$can_add]));
        }else{
            #无权限
            die(json_encode(['code'=>-1,'msg'=>'非常抱歉，您暂未有该事项的发起权限，请联系管理员添加！']));
        }
    }
    elseif($data['pa']==3){
        #获取可新增表单信息
        $event_id = intval($data['event_id']);
        //获取事项角色id、事项表单
        $event = pdo_fetch('select role_id,diyformid from '.tablename('approval_event').' where id=:id and decl_id=:decl_id',[':id'=>$event_id,':decl_id'=>$company_id]);
        $info = pdo_fetch('select * from '.tablename('approval_diyform').' where id=:id',[':id'=>$event['diyformid']]);
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
//                    $info['diy_form_fields'][$k]['select_value2'] = json_encode($select_value2,true);
                $info['diy_form_fields'][$k]['select_value2'] = $select_value2;
            }

            if($v['can_add']==2){
                #可新增
                array_push($can_add,$info['diy_form_fields'][$k]);
            }
        }
        die(json_encode(['code'=>0,'msg'=>'获取新增表单信息成功！','can_add'=>$can_add]));
    }
    else{
        $event = pdo_fetchall('select * from '.tablename('approval_event').' where decl_id=:decl_id order by id desc',[':decl_id'=>$company_id]);
    }
    $title='发起审批';
    include $this->template('approval/send/initiate_approval');
}
elseif($op=='manage'){
    #管理
    include $this->template('approval/send/manage');
}
elseif($op=='my_initiate'){
    #我的发起

    include $this->template('approval/send/my_initiate');
}
elseif($op=='unapproved'){
    #未审批
    $title = '未审批';

    if($data['pa']==1){
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }

        $list = pdo_fetchall('select a.*,b.name from '.tablename('approval_initiate').' a left join '.tablename('approval_event').' b on b.id=a.event_id where a.openid=:openid and a.status=1 order by a.id desc limit '.$page.",".$limit,[':openid'=>$openid]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            if($v['opera']>0){
                switch ($v['opera']){
                    case 1:
                        $list[$k]['opera'] = '已撤回';
                        break;
                    case 2:
                        $list[$k]['opera'] = '已终止';
                        break;
                    case 3:
                        $list[$k]['opera'] = '已暂停';
                        break;
                }
            }else{
                $list[$k]['opera'] = '待审批';
            }
        }
        $count = pdo_fetchcolumn('select count(id) from '.tablename('approval_initiate').' where openid=:openid and status=1',[':openid'=>$openid]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    elseif($data['pa']==2){
        #撤回
        $res = pdo_update('approval_initiate',[
            'opera'=>1
        ],['id'=>intval($data['id'])]);
        if($res){
            die(json_encode(['code'=>0,'msg'=>'撤回成功！']));
        }
    }
    include $this->template('approval/send/unapproved');
}
elseif($op=='unapproved_edit'){
    #发起详情修改
    $id = intval($data['id']);

    if($data['pa']==1){
        #数组组装
        $diyFormData = [
            'list_file'=>$data['list_file'],
            'value'=>$data['value'],//单个属性
        ];
        if(isset($data['add_value_num'])){
            if($data['add_value_num']>0){
                for($i=0;$i<=$data['add_value_num'];$i++){
//                    if($i==0){
//                        $diyFormData['add_value'][$i] = [];
//                        foreach($data['add_value'] as $k=>$v){
//                            array_push($diyFormData['add_value'][$i],['value'=>$v,'random_name'=>rand(1111,9999)]);
//                        }
//                    }else{
                        $diyFormData['add_value'][$i] = [];
                        $c = intval($i);
                        foreach($data['add_value'.strval($c)] as $k=>$v){
                            array_push($diyFormData['add_value'][$i],['value'=>$v,'random_name'=>rand(1111,9999)]);
                        }
//                    }
                }
            }else{
                $diyFormData['add_value'] = [];
                foreach($data['add_value'] as $k=>$v){
                    array_push($diyFormData['add_value'],['value'=>$v,'random_name'=>rand(1111,9999)]);
                }
            }
        }
        $diyFormData = json_encode($diyFormData,true);

        $res = pdo_update('approval_initiate',['diyFormData'=>$diyFormData],['openid'=>$openid,'id'=>intval($data['id'])]);
        if($res){
            die(json_encode(['code'=>0,'msg'=>'修改成功！']));
        }
    }
    else{
        $info = pdo_fetch('select a.*,b.name,c.diy_form_fields as template from '.tablename('approval_initiate').' a left join '.tablename('approval_event').' b on b.id=a.event_id left join '.tablename('approval_diyform').' c on c.id=b.diyformid where a.id=:id and a.openid=:openid',[':id'=>$id,':openid'=>$openid]);
        $content = json_decode($info['diyFormData'],true);//提交表单的内容
        $info['template'] = json_decode($info['template'],true);//自定义表单的内容

        $can_add = [];//将可新增的数据放在可新增参数中
        foreach($info['template'] as $k=>$v){
            $info['template'][$k]['random_name'] = rand(1111,9999);
            $info['template'][$k]['select_value2'] = '';
            if($v['param']==2){
                $info['template'][$k]['select_value2'] = explode(',',$v['select_value']);
            }elseif($v['param']==3){
                $select_value = explode(',',$v['select_value']);
                $select_value2 = [];
                foreach($select_value as $k2=>$v2){
                    array_push($select_value2,['name'=>$v2,'id'=>$v2]);
                }

                $info['template'][$k]['select_value2'] = json_encode($select_value2,true);
                //$info['template'][$k]['select_value2'] = $select_value2;
            }

            if($v['can_add']==2){
                #可新增
                array_push($can_add,$info['template'][$k]);
            }
        }
    }

    #插入的数据
    $val_i = 0;
    foreach($content['value'] as $kk=>$vv){
        foreach($info['template'] as $k=>$v){
            if($v['can_add']==1 && $v['param']<5 && !isset($info['template'][$k]['valued'])){
                #不可新增
                $info['template'][$k]['valued'] = $content['value'][$val_i];
                $val_i=$kk+1;
                break;
            }
        }
    }
    if(isset($content['add_value'])){
        foreach($content['add_value'] as $k=>$v){
            foreach($v as $k2=>$v2){
                $content['add_value'][$k][$k2]['initValue'] = '';
                $content['add_value'][$k][$k2]['initValue'] = json_encode(explode(',',$v2['value']),true);
            }
        }
    }
//    print_r($info['template']);die;
    include $this->template('approval/send/unapproved_edit');
}
elseif($op=='approved'){
    #已审批

    if(isset($data['pa'])){
        #被退回
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }

        $list = pdo_fetchall('select a.*,b.name from '.tablename('approval_initiate').' a left join '.tablename('approval_event').' b on b.id=a.event_id where a.openid=:openid and a.status=:pa order by a.id desc limit '.$page.",".$limit,[':openid'=>$openid,':pa'=>intval($data['pa'])]);
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            if($v['opera']>0){
                switch ($v['opera']){
                    case 1:
                        $list[$k]['opera'] = '已撤回';
                        break;
                    case 2:
                        $list[$k]['opera'] = '已终止';
                        break;
                    case 3:
                        $list[$k]['opera'] = '已暂停';
                        break;
                }
            }else{
                $list[$k]['opera'] = '已审批';
            }
        }
        $count = pdo_fetchcolumn('select count(id) from '.tablename('approval_initiate').' where openid=:openid and status=:pa',[':openid'=>$openid,':pa'=>intval($data['pa'])]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }

    $title = '已审批';
    include $this->template('approval/send/approved');
}
elseif($op=='approved_detail'){
    #已同意-查看详情
    $id = intval($data['id']);
    $data['is_copy'] = 2;#人员自己查看
    $info = pdo_fetch('select a.*,b.name,c.diy_form_fields as template from '.tablename('approval_initiate').' a left join '.tablename('approval_event').' b on b.id=a.event_id left join '.tablename('approval_diyform').' c on c.id=b.diyformid where a.id=:id and a.openid=:openid',[':id'=>$id,':openid'=>$openid]);
    $content = json_decode($info['diyFormData'],true);//提交表单的内容
    $info['template'] = json_decode($info['template'],true);//自定义表单的内容

    #插入的数据
    $val_i = 0;
    foreach($content['value'] as $kk=>$vv){
        foreach($info['template'] as $k=>$v){
            if($v['can_add']==1 && $v['param']<5 && !isset($info['template'][$k]['valued'])){
                #不可新增
                $info['template'][$k]['valued'] = $content['value'][$val_i];
                $val_i=$kk+1;
                break;
            }
        }
    }

    $can_add = [];//将可新增的数据放在可新增参数中
    foreach($info['template'] as $k=>$v){
        if($v['can_add']==2){
            #可新增
            array_push($can_add,$info['template'][$k]);
        }
    }

    include $this->template('approval/send/approval_detail');
}
elseif($op=='resend'){
    #重发
    #需要通知多次
    $res = pdo_update('approval_initiate',[
        'status'=>1,
        'remark'=>'',
        'opera'=>0
    ],['id'=>intval($data['id'])]);
    if($res){
        $time = time();
        $initiate = pdo_fetch('select * from '.tablename('approval_initiate').' where id=:id and openid=:openid',[':id'=>$data['id'],':openid'=>$openid]);
        #根据这一事项分别通知审批人员
        $event = pdo_fetch('select * from '.tablename('approval_event').' where id=:id',[':id'=>intval($initiate['event_id'])]);
        $role_ids = explode(',',$event['role_ids']);

        #发起人信息
        $initiate_info = pdo_fetch('select * from '.tablename('approval_staff_list').' where openid=:openid and decl_id=:decl_id',[':openid'=>$openid,':decl_id'=>$company_id]);

        $notice_man = '';
        if($event['approval_type']==1){
            #串联,按顺序先通知该角色人员
            $notice_man = $role_ids[0];

            #获取该角色下的人员信息
            $approval_info = pdo_fetchall('select * from '.tablename('approval_staff_list').' where role_id=:role_id and decl_id=:decl_id',[':role_id'=>$notice_man,':decl_id'=>$company_id]);
            if(!empty($approval_info)){
                foreach($approval_info as $k=>$v){
                    #更改通知数据表
                    pdo_update('approval_list',['status'=>0,'remark'=>''],['initiate_id'=>$initiate_info['id'],'openid'=>$v['openid']]);
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'您好，人员['.trim($initiate_info['name']).']已重发事项['.$event['name'].']，请进入系统进行审批！',
                        'keyword1' => '事项提交',
                        'keyword2' => '已重发',
                        'keyword3' => date('Y-m-d H:i:s',$time),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&p=index&m=sz_yi&op=display#wechat_redirect',
                        'openid' => $v['openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }
            }
        }elseif($event['approval_type']==2){
            #并联

            #根据各角色查找各人员
            $notice_man = $role_ids;
            foreach($notice_man as $k=>$v){
                #获取该角色下的人员信息
                $approval_info = pdo_fetchall('select * from '.tablename('approval_staff_list').' where role_id=:role_id and decl_id=:decl_id',[':role_id'=>$v,':decl_id'=>$company_id]);
                if(!empty($approval_info)){
                    foreach($approval_info as $kk=>$vv){
                        #更改通知数据表
                        pdo_update('approval_list',['status'=>0,'remark'=>''],['initiate_id'=>$initiate_info['id'],'openid'=>$vv['openid']]);

                        $post = json_encode([
                            'call'=>'confirmCollectionNotice',
                            'first' =>'您好，人员['.trim($initiate_info['name']).']已重发事项['.$event['name'].']，请进入系统进行审批！',
                            'keyword1' => '事项提交',
                            'keyword2' => '已重发',
                            'keyword3' => date('Y-m-d H:i:s',$time),
                            'remark' => '点击查看详情',
                            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&p=index&m=sz_yi&op=display#wechat_redirect',
                            'openid' => $vv['openid'],
                            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                        ]);
                        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                    }
                }
            }
        }
        die(json_encode(['code'=>0,'msg'=>'重发成功，请等待管理员审批！']));
    }
}
elseif($op=='withdraw'){
    #撤回
    $res = pdo_update('approval_initiate',[
        'opera'=>1
    ],['id'=>intval($data['id'])]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'撤回成功！']));
    }
}
elseif($op=='termination'){
    #终止
    $res = pdo_update('approval_initiate',[
        'opera'=>2
    ],['id'=>intval($data['id'])]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'终止成功！']));
    }
}
elseif($op=='pause'){
    #暂停
    $res = pdo_update('approval_initiate',[
        'opera'=>3
    ],['id'=>intval($data['id'])]);
    if($res){
        die(json_encode(['code'=>0,'msg'=>'暂停成功！']));
    }
}
elseif($op=='my_approval'){
    #我的审批

    #table获取数据
    if(isset($data['pa'])){
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }

        $list = pdo_fetchall('select a.*,c.name,d.name as staff_name from '.tablename('approval_list').' a left join '.tablename('approval_initiate').' b on b.id=a.initiate_id left join '.tablename('approval_event').' c on c.id=b.event_id left join '.tablename('approval_staff_list').' d on d.openid=b.openid where a.openid=:openid and a.status=:pa and b.opera=0 and a.is_copy<>1 order by a.id desc limit '.$page.",".$limit,[':openid'=>$openid,':pa'=>intval($data['pa'])]);

        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            if($v['status']>0){
                switch ($v['status']){
                    case 0:
                        $list[$k]['status'] = '待审批';
                        break;
                    case 1:
                        $list[$k]['status'] = '已退回';
                        break;
                    case 2:
                        $list[$k]['status'] = '已拒绝';
                        break;
                    case 3:
                        $list[$k]['status'] = '已同意';
                        break;
                }
            }else{
                $list[$k]['status'] = '待审批';
            }
        }
        $count = pdo_fetchcolumn('select count(a.id) from '.tablename('approval_list').' a left join '.tablename('approval_initiate').' b on b.id=a.initiate_id where a.openid=:openid and a.status=:pa and b.opera=0',[':openid'=>$openid,':pa'=>intval($data['pa'])]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }

    #form提交数据
    if(isset($data['approval'])){
        $my_select = intval($data['my_select']);
        $remark = trim($data['remark']);
        $id = intval($data['id']);
        $time = time();

        if($my_select!=3 && empty($remark)){
            die(json_encode(['code'=>-1,'msg'=>'请输入退回/拒绝原因']));
        }
        if($my_select==3){
            #同意
            $remark='';
        }

        $approval_info = pdo_fetch('select a.*,c.role_ids,c.copyInfoFor_roleIds as admin_role_ids,b.openid as init_openid,b.id as init_id,c.name as event_name,d.name as init_name from '.tablename('approval_list').' a left join '.tablename('approval_initiate').' b on b.id=a.initiate_id left join '.tablename('approval_event').' c on c.id=b.event_id left join '.tablename('approval_staff_list').' d on d.openid=b.openid where a.openid=:openid and a.id=:id',[':id'=>$id,':openid'=>$openid]);
        $my_role = pdo_fetch('select role_id from '.tablename('approval_staff_list').' where openid=:openid and decl_id=:decl_id',[":openid"=>$openid,':decl_id'=>$company_id]);
        #修改自己的审批
        pdo_update('approval_list',['status'=>$my_select,'operatime'=>$time,'remark'=>$remark],['id'=>$id,'openid'=>$openid]);
        #查询同个发起id下，有无通知其他同事，有的话修改is_mate_approval字段为1
//        $common_initiate = pdo_fetchall('select id from '.tablename('approval_list').' where initiate_id=:init_id and openid<>:openid and pid=:pid',[':init_id'=>$approval_info['initiate_id'],':openid'=>$openid,':pid'=>$approval_info['pid']]);
//        if(!empty($common_initiate)){
//            foreach($common_initiate as $k=>$v){
//                pdo_update('approval_list',['is_mate_approval'=>1],['initiate_id'=>$approval_info['initiate_id'],'id'=>$v['id']]);
//            }
//        }

        if($my_select!=3){
            #退回或拒绝
            #通知发起人，并修改其发起数据
            $status=0;
            $status_name='';
            switch($my_select){
                case 1:
                    $status=2;#退回
                    $status_name='被退回';
                    break;
                case 2:
                    $status=3;#拒绝
                    $status_name='被拒绝';
                    break;
            }
            $res = pdo_update('approval_initiate',['status'=>$status,'remark'=>$remark],['id'=>$approval_info['init_id']]);
            if($res){
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，您提交的事项['.$approval_info['event_name'].']，经审核已'.$status_name.'！',
                    'keyword1' => $approval_info['event_name'],
                    'keyword2' => $status_name,
                    'keyword3' => date('Y-m-d H:i:s',$time),
                    'remark' => $remark,
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=approved#wechat_redirect',
                    'openid' => $approval_info['init_openid'],
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

                die(json_encode(['code'=>0,'操作成功！']));
            }
        }
        else{
            #同意
            #1、判断该事项是串联还是并联
            #2、若是并联则查找该角色下所有抄送人并通知
            #3、若是串联则通知下一个审批员
            #4、修改发起人的事件为正流转

            pdo_update('approval_initiate',['status'=>4],['id'=>$approval_info['init_id']]);#4

            if($approval_info['approval_method']==1){
                #串联
                #获取事件角色审批顺序，先判断自己当前的角色是第几个，然后按顺序获取下一个
                #若当前角色有n人，则所有人点击同意后才可进行通知下一角色
                #当已是最后一个时，则通知抄送人,当抄送人角色无任何人，则通知管理员（企业老板）

                $sort_roles = explode(',',$approval_info['role_ids']);//4,3,6

                #获取当前角色的人
                #判断当前发起事项ID的审批角色有无都点击同意
                $common_role_staff = pdo_fetchall('select * from '.tablename('approval_staff_list').' where decl_id=:decl_id and role_id=:role_id and openid<>"'.$openid.'"',[':decl_id'=>$company_id,':role_id'=>$my_role['role_id']]);

                $can_notice = 1;#1可通知下个角色，0不可通知下个角色
                if(!empty($common_role_staff)){
                    foreach($common_role_staff as $k=>$v){
                        $isHave_noAgree = pdo_fetch('select id,openid from '.tablename('approval_list').' where openid=:openid and initiate_id=:init_id and status<>3',[':openid'=>$v['openid'],':init_id'=>$approval_info['init_id']]);
                        if(!empty($isHave_noAgree['id'])){
                            $can_notice = 0;
                        }
                    }
                }

                if($can_notice==1){
                    $next_roles = 0;
                    #找到要通知的下一个角色
                    if(!empty($my_role)){
                        foreach($sort_roles as $k=>$v){
                            if($v==$my_role['role_id']){
                                if(isset($sort_roles[$k+1])){
                                    $next_roles = $sort_roles[$k+1];
                                }
                            }
                        }
                    }

                    if(empty($next_roles)){
                        #通知企业老板或抄送员
                        $final_admin_roles = explode(',',$approval_info['admin_role_ids']);
                        $final_staff = [];
                        foreach($final_admin_roles as $k=>$v){
                            $list = pdo_fetchall('select * from '.tablename('approval_staff_list').' where role_id=:role_id and decl_id=:decl_id',[':decl_id'=>$company_id,':role_id'=>$v]);
                            $final_staff = array_merge($final_staff,$list);
                        }
                        if(empty($final_staff)){
                            #当抄送人没注册时，查找商户表，然后通知他
                            $final_staff_openid = pdo_fetchcolumn('select openid from '.tablename('decl_user').' where id=:decl_id',[':decl_id'=>$company_id]);
                            if(!empty($final_staff_openid)){
                                #插入抄送数据
                                pdo_insert('approval_list',[
                                    'initiate_id'=>$approval_info['initiate_id'],
                                    'pid'=>$approval_info['id'],
                                    'approval_method'=>$approval_info['approval_method'],
                                    'openid'=>$final_staff_openid,
                                    'status'=>3,
                                    'is_copy'=>1,
                                    'createtime'=>$time,
                                ]);
                                $post = json_encode([
                                    'call'=>'confirmCollectionNotice',
                                    'first' =>'您好，人员['.$approval_info['init_name'].']已完成事项['.$approval_info['event_name'].']！',
                                    'keyword1' => $approval_info['event_name'],
                                    'keyword2' => '已完成',
                                    'keyword3' => date('Y-m-d H:i:s',$time),
                                    'remark' => '点击查看详情',
                                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=my_copy#wechat_redirect',
                                    'openid' => $final_staff_openid,
                                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                                ]);
                                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                            }
                        }
                        else{
                            #有抄送员，并每个通知
                            foreach($final_staff as $k=>$v){
                                #插入抄送数据
                                pdo_insert('approval_list',[
                                    'initiate_id'=>$approval_info['initiate_id'],
                                    'pid'=>$approval_info['id'],
                                    'approval_method'=>$approval_info['approval_method'],
                                    'openid'=>$v['openid'],
                                    'status'=>3,
                                    'is_copy'=>1,
                                    'createtime'=>$time,
                                ]);
                                $post = json_encode([
                                    'call'=>'confirmCollectionNotice',
                                    'first' =>'您好，人员['.$approval_info['init_name'].']已完成事项['.$approval_info['event_name'].']！',
                                    'keyword1' => $approval_info['event_name'],
                                    'keyword2' => '已完成',
                                    'keyword3' => date('Y-m-d H:i:s',$time),
                                    'remark' => '点击查看详情',
                                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=my_copy#wechat_redirect',
                                    'openid' => $v['openid'],
                                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                                ]);
                                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                            }
                        }

                        #修改发起人的发起信息
                        pdo_update('approval_initiate',['status'=>5],['id'=>$approval_info['init_id']]);
                        #通知发起人
                        $post = json_encode([
                            'call'=>'confirmCollectionNotice',
                            'first' =>'您好，您提交的事项['.$approval_info['event_name'].']经审核已同意！',
                            'keyword1' => $approval_info['event_name'],
                            'keyword2' => '已同意',
                            'keyword3' => date('Y-m-d H:i:s',$time),
                            'remark' => '点击查看详情',
                            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=approved#wechat_redirect',
                            'openid' => $approval_info['init_openid'],
                            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                        ]);
                        ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                    }
                    else{
                        #有获取到下一个角色有所有人员数据，然后逐个通知
                        $next_role_staffs = pdo_fetchall('select * from '.tablename('approval_staff_list').' where role_id=:role_id and decl_id=:decl_id',[':decl_id'=>$company_id,':role_id'=>$next_roles]);
                        if(!empty($next_role_staffs)){
                            #插入数据表，并通知
                            foreach($next_role_staffs as $k=>$v){
                                pdo_insert('approval_list',[
                                    'pid'=>$approval_info['id'],
                                    'initiate_id'=>$approval_info['initiate_id'],
                                    'approval_method'=>$approval_info['approval_method'],
                                    'openid'=>$v['openid'],
                                    'createtime'=>$time,
                                ]);
                                $post = json_encode([
                                    'call'=>'confirmCollectionNotice',
                                    'first' =>'您好，人员['.$approval_info['init_name'].']已提交事项['.$approval_info['event_name'].']，请进入系统进行审批！',
                                    'keyword1' => '事项提交',
                                    'keyword2' => '已提交',
                                    'keyword3' => date('Y-m-d H:i:s',$time),
                                    'remark' => '点击查看详情',
                                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=my_approval#wechat_redirect',
                                    'openid' => $v['openid'],
                                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                                ]);
                                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                            }
                        }
                    }
                }
            }
            elseif($approval_info['approval_method']==2){
                #并联

                #获取当前事项可审批的角色
                #判断当前发起事项ID的审批角色有无都点击同意
                $all_can_approval_role = explode(',',$approval_info['role_ids']);#所有并联审批角色
                $can_notice = 1;#1可通知抄送员，0不可通知抄送员
                foreach($all_can_approval_role as $kk=>$vv){
                    $common_role_staff = pdo_fetchall('select * from '.tablename('approval_staff_list').' where decl_id=:decl_id and role_id=:role_id and openid<>"'.$openid.'"',[':decl_id'=>$company_id,':role_id'=>$vv]);
                    if(!empty($common_role_staff)){
                        foreach($common_role_staff as $k=>$v){
                            $isHave_noAgree = pdo_fetch('select id,openid from '.tablename('approval_list').' where openid=:openid and initiate_id=:init_id and status<>3',[':openid'=>$v['openid'],':init_id'=>$approval_info['init_id']]);
                            if(!empty($isHave_noAgree['id'])){
                                $can_notice = 0;
                            }
                        }
                    }
                }

                if($can_notice==1){
                    #抄送员角色
                    $final_admin_roles = explode(',',$approval_info['admin_role_ids']);
                    $final_staff = [];
                    foreach($final_admin_roles as $k=>$v){
                        $list = pdo_fetchall('select * from '.tablename('approval_staff_list').' where role_id=:role_id and decl_id=:decl_id',[':decl_id'=>$company_id,':role_id'=>$v]);
                        $final_staff = array_merge($final_staff,$list);
                    }
                    if(empty($final_staff)){
                        #当抄送人没注册时，查找商户表，然后通知他
                        $final_staff_openid = pdo_fetchcolumn('select openid from '.tablename('decl_user').' where id=:decl_id',[':decl_id'=>$company_id]);
                        if(!empty($final_staff_openid)){
                            #插入抄送数据
                            pdo_insert('approval_list',[
                                'initiate_id'=>$approval_info['initiate_id'],
                                'pid'=>$approval_info['id'],
                                'approval_method'=>$approval_info['approval_method'],
                                'openid'=>$final_staff_openid,
                                'status'=>3,
                                'is_copy'=>1,
                                'createtime'=>$time,
                            ]);
                            $post = json_encode([
                                'call'=>'confirmCollectionNotice',
                                'first' =>'您好，人员['.$approval_info['init_name'].']已完成事项['.$approval_info['event_name'].']！',
                                'keyword1' => $approval_info['event_name'],
                                'keyword2' => '已完成',
                                'keyword3' => date('Y-m-d H:i:s',$time),
                                'remark' => '点击查看详情',
                                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=my_copy#wechat_redirect',
                                'openid' => $final_staff_openid,
                                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                            ]);
                            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                        }
                    }
                    else{
                        #有抄送员，并每个通知
                        foreach($final_staff as $k=>$v){
                            #插入抄送数据
                            pdo_insert('approval_list',[
                                'initiate_id'=>$approval_info['initiate_id'],
                                'pid'=>$approval_info['id'],
                                'approval_method'=>$approval_info['approval_method'],
                                'openid'=>$v['openid'],
                                'status'=>3,
                                'is_copy'=>1,
                                'createtime'=>$time,
                            ]);
                            $post = json_encode([
                                'call'=>'confirmCollectionNotice',
                                'first' =>'您好，人员['.$approval_info['init_name'].']已完成事项['.$approval_info['event_name'].']！',
                                'keyword1' => $approval_info['event_name'],
                                'keyword2' => '已完成',
                                'keyword3' => date('Y-m-d H:i:s',$time),
                                'remark' => '点击查看详情',
                                'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=my_copy#wechat_redirect',
                                'openid' => $v['openid'],
                                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                            ]);
                            ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                        }
                    }

                    #修改发起人的发起信息
                    pdo_update('approval_initiate',['status'=>5],['id'=>$approval_info['init_id']]);
                    #通知发起人
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'您好，您提交的事项['.$approval_info['event_name'].']经审核已同意！',
                        'keyword1' => $approval_info['event_name'],
                        'keyword2' => '已同意',
                        'keyword3' => date('Y-m-d H:i:s',$time),
                        'remark' => '点击查看详情',
                        'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=approved#wechat_redirect',
                        'openid' => $approval_info['init_openid'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);
                    ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }
            }
            die(json_encode(['code'=>0,'msg'=>'操作成功！']));
        }
    }

    $title = '我的审批';
    include $this->template('approval/send/my_approval');
}
elseif($op=='approval_detail'){
    #审批详情
    $id = intval($data['id']);

    $info = pdo_fetch('select a.*,b.name,c.diy_form_fields as template,d.name as init_name,d.tel as init_mobile from '.tablename('approval_list').' al left join '.tablename('approval_initiate').' a on a.id=al.initiate_id left join '.tablename('approval_event').' b on b.id=a.event_id left join '.tablename('approval_diyform').' c on c.id=b.diyformid left join '.tablename('approval_staff_list').' d on d.openid=a.openid where al.id=:id and al.openid=:openid',[':id'=>$id,':openid'=>$openid]);
    $content = json_decode($info['diyFormData'],true);//提交表单的内容
    $info['template'] = json_decode($info['template'],true);//自定义表单的内容

    #插入的数据
    $val_i = 0;
    foreach($content['value'] as $kk=>$vv){
        foreach($info['template'] as $k=>$v){
            if($v['can_add']==1 && $v['param']<5 && !isset($info['template'][$k]['valued'])){
                #不可新增
                $info['template'][$k]['valued'] = $content['value'][$val_i];
                $val_i=$kk+1;
                break;
            }
        }
    }

    $can_add = [];//将可新增的数据放在可新增参数中
    foreach($info['template'] as $k=>$v){
        if($v['can_add']==2){
            #可新增
            array_push($can_add,$info['template'][$k]);
        }
    }

    include $this->template('approval/send/approval_detail');
}
elseif($op=='my_copy'){
    #我的抄送
    if($data['pa']==1){
        $limit = intval($data['limit']);
        $page = intval($data['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = trim($data['keywords']) ? trim($data['keywords']) : '';
        $condition = '';
        if($keyword){
            $condition = ' and d.name like "%'.$keyword.'%"';
        }
        $list = pdo_fetchall('select a.*,c.name,d.name as staff_name from '.tablename('approval_list').' a left join '.tablename('approval_initiate').' b on b.id=a.initiate_id left join '.tablename('approval_event').' c on c.id=b.event_id left join '.tablename('approval_staff_list').' d on d.openid=b.openid where a.openid=:openid and a.is_copy=1 '.$condition.' order by a.id desc limit '.$page.",".$limit,[':openid'=>$openid]);

        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            $list[$k]['status'] = '已同意';
        }
        $count = pdo_fetchcolumn('select count(a.id) from '.tablename('approval_list').' a left join '.tablename('approval_initiate').' b on b.id=a.initiate_id left join '.tablename('approval_staff_list').' d on d.openid=b.openid where a.openid=:openid and a.is_copy=1'.$condition,[':openid'=>$openid]);
        die(json_encode(['code'=>0,'data'=>$list,'count'=>$count]));
    }
    elseif($data['pa']==2){
        #下载审批（EXCEL）
        #1、获取审批流程（谁审批）
        #2、获取当前人员的邮箱

        $approval_info = pdo_fetch('select a.*,c.role_ids,c.copyInfoFor_roleIds as admin_role_ids,b.openid as init_openid,b.id as init_id,c.name as event_name,d.name as init_name from '.tablename('approval_list').' a left join '.tablename('approval_initiate').' b on b.id=a.initiate_id left join '.tablename('approval_event').' c on c.id=b.event_id left join '.tablename('approval_staff_list').' d on d.openid=b.openid where a.openid=:openid and a.id=:id',[':id'=>intval($data['id']),':openid'=>$openid]);

        #审核人员名称，角色，抄送，接收时间，审核时间
        $approval_list = pdo_fetchall('select a.id,b.name,c.name as role_name,a.is_copy,a.createtime,a.operatime from '.tablename('approval_list').' a left join '.tablename('approval_staff_list').' b on b.openid=a.openid left join '.tablename('approval_role').' c on c.id=b.role_id where a.initiate_id=:init_id and b.decl_id=:decl_id order by a.id asc',[':init_id'=>intval($approval_info['initiate_id']),':decl_id'=>$company_id]);

        if(!empty($approval_list)){
            foreach($approval_list as $k=>$v){
                if($v['is_copy']==1){
                    $approval_list[$k]['is_copy'] = '是';
                }else{
                    $approval_list[$k]['is_copy'] = '否';
                }
                $approval_list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                if(!empty($v['operatime'])){
                    $approval_list[$k]['operatime'] = date('Y-m-d H:i',$v['operatime']);
                }
            }

            $columns = array(
                array(
                    'title' => '序号',
                    'field' => 'id',
                    'width' => 12
                ),
                array(
                    'title' => '审核员名称',
                    'field' => 'name',
                    'width' => 16
                ),
                array(
                    'title' =>'角色名称',
                    'field' => 'role_name',
                    'width' => 16
                ),
                array(
                    'title' =>'抄送',
                    'field' => 'is_copy',
                    'width' => 12
                ),
                array(
                    'title' =>'接收时间',
                    'field' => 'createtime',
                    'width' => 20
                ),
                array(
                    'title' =>'审核时间',
                    'field' => 'operatime',
                    'width' => 20
                ),
            );
            $excel_url = m('excel')->exportToFloder($approval_list , array(
                "title" => '关于人员（'.$approval_info['init_name'].'）的（'.$approval_info['event_name'].'）事项的审批详情',
                "columns" => $columns
            ));
            $excel_url = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn',$excel_url);
        }

        $notice_info = pdo_fetch('select email from '.tablename('approval_staff_list').' where openid=:openid and decl_id=:decl_id',[':openid'=>$openid,':decl_id'=>$company_id]);
        if(empty($notice_info)){
            #没有则通知企业老板的邮箱
            $notice_info = pdo_fetch('select user_email as email from '.tablename('decl_user').' where openid=:openid and id=:decl_id',[':openid'=>$openid,':decl_id'=>$company_id]);
        }

        load()->func('communication');
        $url = 'https://shop.gogo198.cn/foll/public/?s=api/sendemail/index';
        //发送电子邮件
        $post_data = [
            'title' =>'关于人员['.$approval_info['init_name'].']的['.$approval_info['event_name'].']事项的审批详情',//关于[人员名称]的[xx]事项的审批详情
            'email' =>$notice_info['email'],
            'content' => '打开链接下载excel文件：'.$excel_url,//excel地址
        ];

        $ch = curl_init($url);//地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");//请求方法
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);//请求数据
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//成功返回时返回数据，失败返回false
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//禁止curl验证对等证书
        //curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: text/plain'));// 必须声明请求头
        $output = curl_exec($ch);//执行curl
        if (curl_errno($ch)) {
            //echo 'Curl error: ' . curl_error($ch);
            $code = curl_errno($ch);
            $msg = curl_error($ch);
            return ['result' => false, 'errorCode' => $code, 'description' => $msg];
        }
        curl_close($ch);//关闭句柄

        $res =  json_decode($output, true);

        if($res['status']==1){
            die(json_encode(['code'=>0,'msg'=>'已发送至邮箱['.$notice_info['email'].']']));
        }
    }
    $title = '我的抄送';
    include $this->template('approval/send/my_copy');
}