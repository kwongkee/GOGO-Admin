<?php
namespace app\admin\controller;

//use think\Controller;
use app\admin\controller;
use think\Db;
use think\Request;

class Task extends  Auth{
    //平台客服列表
    public function notice_list(Request $request){
        $dat = input();
        if($request->isAjax()) {
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count=DB::name('centralize_system_servicer')->count();
            $rows = DB::name('centralize_system_servicer')
                ->limit($limit)
                ->order($order)
                ->select();

            $_notice = ['1'=>'手机号码','2'=>'电子邮箱'];
            $_status = ['-1'=>'拒绝邀请','0'=>'待确认','1'=>'已确认'];
            $_identify = ['0'=>'员工','1'=>'管理员'];
            foreach($rows as $k=>$v){
                $rows[$k]['notice_type'] = $_notice[$v['verify_type']];
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
//                $rows[$k]['suretime'] = date('Y-m-d H:i',$v['suretime']);
                $rows[$k]['statusname'] = $_status[$v['status']];
                $rows[$k]['identify'] = $_identify[$v['isadmin']];
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }
        else{
            return view('');
        }
    }

    //添加平台客服
    public function notice(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $oid = isset($dat['oid'])?intval($dat['oid']):0;#变更的原客服id

        if($request->isAjax()){
            //原来的版本
//            $is_have = Db::name('centralize_system_notice')->where(['system_type'=>1])->find();
//            if($dat['notice_type']==1){
//                #微信
//                $dat['account'] = Db::name('website_user')->where(['id'=>$dat['member_id']])->find()['openid'];
//            }
//            elseif($dat['notice_type']==3){
//                #邮箱
//                $dat['account'] = Db::name('website_user')->where(['id'=>$dat['member_id']])->find()['email'];
//            }
//            if($is_have){
//                Db::name('centralize_system_notice')->where(['system_type'=>1])->update([
//                    'notice_type'=>intval($dat['notice_type']),
//                    'account'=>trim($dat['account']),
//                    'member_id'=>$dat['member_id']
//                ]);
//            }else{
//                Db::name('centralize_system_notice')->insert([
//                    'uid'=>0,
//                    'system_type'=>1,
//                    'notice_type'=>intval($dat['notice_type']),
//                    'account'=>trim($dat['account']),
//                    'member_id'=>$dat['member_id']
//                ]);
//            }

            //20260327新版本，发送邀请链接，点击确认成为平台客服。
            $phone = '';$email = '';$verify_type = '';
            if($dat['verify_type']==1){
                $phone = trim($dat['phone']);
                if(empty($phone)){
                    return json(['code'=>-1,'msg'=>'请填写手机']);
                }
            }elseif($dat['verify_type']==2){
                $email = trim($dat['email']);
                if(empty($email)){
                    return json(['code'=>-1,'msg'=>'请填写邮箱']);
                }
            }
            $verify_type = $dat['verify_type'];

            if(intval($dat['isadmin']) == 1){
                Db::name('centralize_system_servicer')->where(['isadmin'=>1])->update(['isadmin'=>0]);
            }

            if($oid>0){
                #变更客服就是删除原客服，新增新客服
                Db::name('centralize_system_servicer')->where(['oid'=>$oid])->delete();
            }

            $servicer_id = Db::name('centralize_system_servicer')->insertGetId([
                'name'=>trim($dat['name']),
                'verify_type'=>intval($dat['verify_type']),
                'phone'=>intval($dat['verify_type'])==1?trim($dat['phone']):'',
                'email'=>intval($dat['verify_type'])==2?trim($dat['email']):'',
                'status'=>0,
                'isadmin'=>intval($dat['isadmin']),
                'createtime'=>time()
            ]);

            if($servicer_id){
                if($verify_type==1){
                    #手机通知
                    send_msg(['phone'=>$phone,'email'=>''],['msg'=>'Gogo购购网邀请您成为平台运营商客服，请点击验证链接：https://www.gogo198.cn/become_servicer?id='.$servicer_id]);
                }elseif($verify_type==2){
                    #邮箱通知
                    send_msg(['phone'=>'','email'=>$email],['msg'=>'Gogo购购网邀请您成为平台运营商，请点击验证链接：https://www.gogo198.cn/become_servicer?id='.$servicer_id]);
                }
            }
            return json(['code'=>0,'msg'=>'邀请通知发送成功']);
        }else{
//            $data = Db::name('centralize_system_notice')->where(['system_type'=>1])->find();
//            $member = Db::name('website_user')->select();
            $data = ['name'=>'','verify_type'=>1,'phone'=>'','email'=>'','openid'=>'','notice'=>0,'isadmin'=>0];
            if($id>0){
                $data = Db::name('centralize_system_servicer')->where(['id'=>$id])->find();
                $data['openid'] = Db::name('website_user')->where(['id'=>$data['uid']])->find()['openid'];
            }

            return view('',compact('data','member','id','oid'));
        }
    }

    //删除平台客服
    public function del_notice(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $servicer = Db::name('centralize_system_servicer')->where(['id'=>$id])->find();
        $res = Db::name('centralize_system_servicer')->where(['id'=>$id])->delete();
        if($res){
//            Db::name('centralize_system_notice')->where(['uid'=>0,'member_id'=>$servicer['uid']])->delete();
            return json(['code'=>-1,'msg'=>'删除成功']);
        }
    }

    //任务管理
    public function task_manage(Request $request){
        $dat = input();
        if($request->isAjax()){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            if($dat['type']==1){
                $count=DB::name('centralize_task')->where(['status'=>0])->count();
                $rows = DB::name('centralize_task')
                    ->alias('a')
                    ->join('centralize_process_list b','b.id=a.task_id')
                    ->where(['a.status'=>0])
                    ->limit($limit)
                    ->order($order)
                    ->field(['a.*','b.content as task_name2'])
                    ->select();

                foreach($rows as $k=>$v){
                    if($v['type']==2){
                        #客户端
                        $person = Db::name('centralize_manage_person')->where(['id'=>$v['user_id']])->field(['name','gogo_id'])->find();
                        $rows[$k]['originator'] = $person['name'].'(ID:'.$person['gogo_id'].')';
                        $rows[$k]['type_name'] = '服务商';
                    }elseif($v['type']==3){
                        #消费者
                        $person = Db::name('website_user')->where(['id'=>$v['user_id']])->find();
                        $rows[$k]['originator'] = $person['custom_id'];
                        $rows[$k]['type_name'] = '寄件人';
                    }
                }
            }elseif($dat['type']==2){
                $count=DB::name('centralize_task')->where(['status'=>1])->count();
                $rows = DB::name('centralize_task')
                    ->alias('a')
                    ->join('centralize_process_list b','b.id=a.task_id')
                    ->where(['a.status'=>1])
                    ->limit($limit)
                    ->order($order)
                    ->field(['a.*','b.content as task_name2'])
                    ->select();

                foreach($rows as $k=>$v){
                    if($v['type']==2){
                        #客户端
                        $person = Db::name('centralize_manage_person')->where(['id'=>$v['user_id']])->field(['name','gogo_id'])->find();
                        $rows[$k]['originator'] = $person['name'].'(ID:'.$person['gogo_id'].')';
                        $rows[$k]['type_name'] = '服务商';
                    }elseif($v['type']==3){
                        #消费者
                        $person = Db::name('website_user')->where(['id'=>$v['user_id']])->find();
                        $rows[$k]['originator'] = $person['custom_id'];
                        $rows[$k]['type_name'] = '寄件人';
                    }
                }
            }elseif($dat['type']==3){
                $count=DB::name('centralize_task')->where(['status'=>-1])->count();
                $rows = DB::name('centralize_task')
                    ->alias('a')
                    ->join('centralize_process_list b','b.id=a.task_id')
                    ->where(['a.status'=>-1])
                    ->limit($limit)
                    ->order($order)
                    ->field(['a.*','b.content as task_name2'])
                    ->select();

                foreach($rows as $k=>$v){
                    if($v['type']==2){
                        #客户端
                        $person = Db::name('centralize_manage_person')->where(['id'=>$v['user_id']])->field(['name','gogo_id'])->find();
                        $rows[$k]['originator'] = $person['name'].'(ID:'.$person['gogo_id'].')';
                        $rows[$k]['type_name'] = '服务商';
                    }elseif($v['type']==3){
                        #消费者
                        $person = Db::name('website_user')->where(['id'=>$v['user_id']])->find();
                        $rows[$k]['originator'] = $person['custom_id'];
                        $rows[$k]['type_name'] = '寄件人';
                    }
                }
            }
            foreach($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('');
        }
    }

    //编辑工单
    public function edit_workorder(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        if($request->isAjax()){
            $order_info = [];
            foreach($dat['express_id'] as $k=>$v){
                $order_info[] = [
                    'express_id'=>$v,
                    'fee_name'=>trim($dat['fee_name'][$k]),
                    'fee_profit'=>trim($dat['fee_profit'][$k]),
                    'fee_service'=>trim($dat['fee_service'][$k]),
                    'fee_total'=>trim($dat['fee_total'][$k]),
                ];
            }
            $res = Db::name('centralize_workorder_order')->where(['pid'=>$id])->update(['order_info'=>json_encode($order_info,true)]);
            if($res){
                return json(['code'=>0,'msg'=>'修改成功']);
            }
        }else{
            $list = Db::name('centralize_workorder a')
                ->join('centralize_workorder_order b','b.pid=a.id')
                ->where(['a.id'=>$id])
                ->field(['b.*'])
                ->find();
            $list['order_info'] = json_decode($list['order_info'],true);
            $totalprice = 0;
            foreach($list['order_info'] as $k=>&$v){
                $v['express_no'] = Db::name('centralize_workorder_express')->where('id',$v['express_id'])->find()['express'];
                $totalprice += $v['fee_total'];
            }

            return view('',compact('id','list','totalprice'));
        }
    }

    //获取流转类型
    public function roam_type(Request $request){
        $dat = input();
        $list = [];
        if($dat['type']==1){
            #服务商
            $list = Db::name('centralize_manage_person')->where(['pid'=>0])->select();
            foreach($list as $k=>$v){
                $list[$k]['custom_id'] = $v['gogo_id'];
                $list[$k]['nickname'] = $v['name'];
            }
        }elseif($dat['type']==2){
            #消费者
            $list = Db::name('website_user')->select();
        }
        return json(['code'=>0,'list'=>$list]);
    }

    #发送通知,$type=1服务商，$type=2寄件人
    public function now_notice($id,$text,$uid,$type,$taskname='',$opera=''){
        $data = Db::name('centralize_task')
            ->alias('a')
            ->join('centralize_workorder b','b.pid=a.id')
            ->join('centralize_process_list c','c.id=a.task_id')
            ->where(['a.id'=>$id])
            ->field(['c.content as task_name','a.id as task_id','a.serial_number','a.package_id','a.order_id'])
            ->find();
        if($type==2){
            $user = Db::name('website_user')->where(['id'=>$uid])->find();
        }elseif($type==1){
            $user = Db::name('centralize_manage_person')->where(['id'=>$uid])->find();
            $user['merch_status']=2;
        }
        if($user['merch_status']==2){
            $notice_type = Db::name('centralize_system_notice')->where(['uid'=>$user['id']])->find();
            if($notice_type['notice_type']==3){
                $user['email'] = $notice_type['account'];
            }
        }
        $res = '';
        if($user['merch_status']==2){
            #服务商
            if($user['email']!=''){
                $res = cklein_mailAli(trim($user['email']), '', '尊敬的服务商', $text);
            }
//            $servicers = Db::name('centralize_system_servicer')->where(['status'=>1])->select();
//            foreach($servicers as $k=>$v) {
//                $muser = Db::name('website_user')->where(['id'=>$v['uid']])->find();
//                if (!empty($muser['email'])) {
//                    $res = cklein_mailAli(trim($user['memail']), '', '尊敬的服务商', $text);
//                }
//            }
        }elseif($user['merch_status']==0){
            #寄件人
            if($user['sns_openid']!='' && $user['merch_status']==0){
                $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
                $res = file_get_contents($url);
                $result = json_decode($res, true);
                $post2 = json_encode([
                    'template_id'=>'GRa2BGkGrqU8g7IgMAVh6vx2iDD08uJSdK316TINQ7s',
                    'page'=>'pages/gather/index?id='.$data['package_id'],
                    'touser' =>$user['sns_openid'],
                    'data'=>['thing1'=>['value'=>$taskname],'phrase2'=>['value'=>$opera],'time4'=>['value'=>date('Y年m月d日 H:i')]],
                    'miniprogram_state'=>'formal',
                    'lang'=>'zh_CN',
                ]);
                $res = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
            }elseif($user['email']!=''){
                $res = cklein_mailAli(trim($user['email']), '', '尊敬的客户', $text);
            }elseif($user['phone']!=''){
                $post_data = [
                    'spid'=>'254560',
                    'password'=>'J6Dtc4HO',
                    'ac'=>'1069254560',
                    'mobiles'=>$user['phone'],
                    'content'=>$text.'【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ),1);
            }
        }

        return $res;
    }

    //流转工单
    public function roam(Request $request){
        $dat = input();
        $workorder_id = intval($dat['task_id']);
        if($request->isAjax()){
//            dd($dat);
            $res = Db::name('centralize_workorder')->where(['id'=>$workorder_id])->update([
                'roam_uid'=>intval($dat['user_id']),
                'roam_type'=>intval($dat['roam_type']),
                'status'=>1,
                'is_roam'=>1
            ]);

            if($res){
                $data = Db::name('centralize_workorder')
                    ->alias('a')
                    ->join('centralize_task b','b.id=a.pid')
                    ->join('centralize_process_list c','c.id=b.task_id')
                    ->where(['a.id'=>$workorder_id])
                    ->field(['a.workorder_number','a.pid as task_id','a.event_id','a.type','b.order_id','c.content as task_name','b.order_id','b.package_id'])
                    ->find();
                if($dat['roam_type']==2){
                    $user = Db::name('website_user')->where(['id'=>$dat['user_id']])->find();
                }elseif($dat['roam_type']==1){
                    $user = Db::name('centralize_manage_person')->where(['id'=>$dat['user_id']])->find();
                    $user['merch_status']=2;
                }
                $text = '';
                if($user['merch_status']!=2){
                    $ordersn = '';
                    if($data['package_id']>0){
                        $ordersn = Db::name('centralize_parcel_order_package')->where(['id'=>$data['package_id']])->find()['express_no'];
                    }elseif($data['order_id']>0){
                        $ordersn = Db::name('centralize_parcel_order')->where(['id'=>$data['order_id']])->find()['ordersn'];
                    }
                    $text = '您提交的任务事项['.$data['task_name'].']，业务编号（'.$ordersn.'）状态已变更！';
                    $this->now_notice($workorder_id,$text,$dat['user_id'],$dat['roam_type'],$data['task_name'],'已确认');
                }else{
                    $text = '您有工单号['.$data['workorder_number'].']的状态发生变化，请到任务管理进行查看！';
                    $this->now_notice($workorder_id,$text,$dat['user_id'],$dat['roam_type']);
                }

                return json(['code'=>0,'msg'=>'流转成功']);
            }
        }else{
            return view('',compact('workorder_id'));
        }
    }

    //确认任务
    public function confirm(Request $request){
        $dat = input();
        $data = Db::name('centralize_task')
            ->alias('a')
            ->join('centralize_process_list c','c.id=a.task_id')
            ->where(['a.id'=>intval($dat['id'])])
            ->field(['c.content as task_name','a.id as task_id','a.serial_number','a.user_id','a.order_id','a.package_id'])
            ->find();
        $ordersn = '';
        if($data['package_id']>0){
            $ordersn = Db::name('centralize_parcel_order_package')->where(['id'=>$data['package_id']])->find()['express_no'];
        }elseif($data['order_id']>0){
            $ordersn = Db::name('centralize_parcel_order')->where(['id'=>$data['order_id']])->find()['ordersn'];
        }
        $res = $this->now_notice($dat['id'],'您提交的任务事项['.$data['task_name'].']，业务编号（'.$ordersn.'）经审核已被平台确认！',$data['user_id'],2,$data['task_name'],'已确认');

        if($res){
            Db::name('centralize_task')->where(['id'=>$dat['id']])->update(['status'=>1]);
            $rolename = Db::name('centralize_backstage_role')->where(['id'=>Session('myUser')['role']])->field(['name'])->find()['name'];
            Db::name('centralize_workorder')->insert([
                'user_id'=>Session('myUser')['id'],
                'pid'=>$dat['id'],
                'type'=>1,
                'workorder_number'=>'MO'.date('ymdHis',time()).'01',
                'event_name'=>Session('myUser')['username'].'('.$rolename.')在'.date('Y-m-d H:i:s').'进行“确认任务”操作',
                'status'=>1,
                'createtime'=>time()
            ]);
            return json(['code'=>0,'msg'=>'确认成功']);
        }
    }

    //拒绝任务
    public function reject(Request $request){
        $dat = input();

        $data = Db::name('centralize_task')
            ->alias('a')
            ->join('centralize_process_list c','c.id=a.task_id')
            ->where(['a.id'=>intval($dat['id'])])
            ->field(['c.content as task_name','a.id as task_id','a.serial_number','a.user_id'])
            ->find();
        $ordersn = '';
        if($data['package_id']>0){
            $ordersn = Db::name('centralize_parcel_order_package')->where(['id'=>$data['package_id']])->find()['express_no'];
        }elseif($data['order_id']>0){
            $ordersn = Db::name('centralize_parcel_order')->where(['id'=>$data['order_id']])->find()['ordersn'];
        }
        $res = $this->now_notice($dat['id'],'您提交的任务事项['.$data['task_name'].']，业务编号（'.$ordersn.'）经审核已被平台拒绝！',$data['user_id'],2,$data['task_name'],'已拒绝');

        if($res){
            Db::name('centralize_task')->where(['id'=>$dat['id']])->update(['status'=>-1]);
            $rolename = Db::name('centralize_backstage_role')->where(['id'=>Session('myUser')['role']])->field(['name'])->find()['name'];
            Db::name('centralize_workorder')->insert([
                'user_id'=>Session('myUser')['id'],
                'pid'=>$data['task_id'],
                'type'=>1,
                'workorder_number'=>'MO'.date('ymdHis'),
                'event_name'=>Session('myUser')['username'].'('.$rolename.')在'.date('Y-m-d H:i:s').'进行“拒绝任务”操作',
                'status'=>1,
                'createtime'=>time()
            ]);
            return json(['code'=>0,'msg'=>'拒绝成功']);
        }
    }

    public function workorder_list(Request $request){
        $dat = input();
        $task_id = intval($dat['task_id']);
        if(isset($dat['pa'])){
            // 排序
            $order = input('sort') . ' ' . input('order');
            // 分页
            $limit = input('offset') . ',' . input('limit');

            $count = Db::name('centralize_workorder')->where(['pid'=>$task_id,'status'=>$dat['status']])->count();
            $rows = Db::name('centralize_workorder')
                ->where(['pid'=>$task_id,'status'=>$dat['status']])
                ->limit($limit)
                ->order($order)
                ->select();
            $_status = [0=>'未完成',1=>'已完成'];
            $_type = [1=>'总后台',2=>'服务商',3=>'消费者'];
            foreach($rows as $k=>$v){
                $rows[$k]['status_name'] = $_status[$v['status']];
                $rows[$k]['type_name'] = $_type[$v['type']];
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $rows[$k]['have_detail'] = 1;
                if($v['type']==2){
                    $ishave = Db::name('centralize_workorder_express')->where(['pid'=>$v['id']])->find();
                    if(empty($ishave)){
                        $rows[$k]['have_detail'] = 0;
                    }
                }
            }
            return json(['message' => "", 'status' => 0, 'total' => $count, 'rows' => $rows]);
        }else{
            return view('',compact('task_id'));
        }
    }

    public function order_info(Request $request){
        $dat = input();
        $task_id = intval($dat['id']);
        if($request->isAjax()){
            $orderid = intval($dat['oid']);
            $order = Db::name('centralize_parcel_order')->where(['id'=>$orderid])->find();
//            substr_replace('92300MMDD09DDC02','kk',3,2)
            $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>$dat['warehouse_id']])->find();
            $res = Db::name('centralize_parcel_order')->where(['id'=>$orderid])->update([
                'warehouse_id'=>$dat['warehouse_id'],
                #修改订单编号
                'ordersn'=>substr_replace($order['ordersn'],substr($warehouse['warehouse_code'],-2),3,2),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'提交确认成功']);
            }
        }else{
            $data = Db::name('centralize_parcel_order')
                ->alias('a')
                ->join('centralize_user b','a.user_id=b.id')
                ->join('centralize_task c','c.order_id=a.id')
                ->where(['c.id'=>$task_id])
                ->field(['a.*','b.name as user_name','b.id as user_id','b.gogo_id'])
                ->find();

            $task = Db::name('centralize_task')
                ->alias('a')
                ->join('centralize_task_list b','a.task_id=b.id')
                ->where(['a.order_id'=>$data['id'],'a.type'=>3])
                ->order('a.id','desc')
                ->field(['b.name','a.status','a.id'])
                ->find();
            $_status = [0=>'预报订单',1=>'到仓订单',2=>'在仓订单',3=>'集运订单'];
            #订单商品
            $data['order_goods'] = Db::name('centralize_parcel_order_goods')->where(['orderid'=>$data['id']])->select();
            if(!empty($data['order_goods'])){
                foreach($data['order_goods'] as $k=>$v){
                    if(!empty($v['express_id'])){
                        $data['order_goods'][$k]['express_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$v['express_id']])->find()['param2'];
                    }else{
                        $data['order_goods'][$k]['express_name'] = '自送入仓，无运输企业';
                    }
                    #商品类别
                    if(!empty($v['itemid'])){
                        $data['order_goods'][$k]['itemid'] = Db::name('centralize_hscode_list')->where(['id'=>$v['itemid']])->find()['name'];
                    }
                    #商品属性
                    if(!empty($v['valueid'])){
                        $gval = Db::name('centralize_gvalue_list')->where('id in ('.$v['valueid'].') ')->field('name')->select();
                        $data['order_goods'][$k]['valueid'] = $gval;
                    }
                    if(!empty($v['package'])) {
                        $data['order_goods'][$k]['package'] = Db::name('packing_type')->where(['code_value' => $v['package']])->find()['code_name'];#包装材质
                    }
                    #包裹数量单位
                    if(!empty($v['unit'])){
                        $data['order_goods'][$k]['unit'] = Db::name('unit')->where(['code_value'=>$v['unit']])->find()['code_name'];
                    }
                    if($data['prediction_id']==2){
                        $data['order_goods'][$k]['inspection_matter'] = json_decode($v['inspection_matter'],true);
                    }
                }
            }
            #订单事项
            $data['createtime'] = date('Y-m-d H:i:s',$data['createtime']);
            $data['status_name'] = $task['name'];
            $data['task_id'] = $task['id'];
            #买家选择的线路信息
            if(!empty($data['line_id'])){
                $data['line_info'] = Db::name('centralize_line_country')
                    ->alias('a')
                    ->join('centralize_line_list b','b.id=a.pid')
                    ->where(['a.id'=>$data['line_id']])
                    ->field(['a.*','b.name'])
                    ->find();
            }

            #仓库信息
            $warehouse = Db::name('centralize_warehouse_list')->where(['status'=>0])->select();

            return view('',compact('data','warehouse'));
        }
    }
}
