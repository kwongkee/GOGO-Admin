<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;

class Authorization extends Auth{
    public function index(Request $request){
        //商户授权列表清单页
        if(request()->isPost() || request()->isAjax()){
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $app_type = [
                'platform_list'=>'平台清单',
                'declare_list'=>'清单申报',
                'logistics_dome'=>'物流单证',
                'offshore_collection'=>'离岸收款',
                'offshore_exchange'=>'离岸换汇',
                'withdrawal'=>'结汇提现',
                'offshore_transfer'=>'离岸转账',
            ];

            $list = Db::name('customer_authorization_app')->order('send_time','desc')->limit($limit)->select();
            foreach($list as $k=>&$v){
                $v['user_name'] = Db::name('decl_user')->where(['id'=>$v['uid'],'openid'=>$v['openid']])->field('user_name')->find()['user_name'];
                $v['send_time'] = date('Y-m-d H:i:s',$v['send_time']);
                if($v['create_at']){
                    $v['create_at'] = date('Y-m-d H:i:s',$v['create_at']);
                }

                if($v['status']==1){
                    $list[$k]['manage'] = '<a href="https://decl.gogo198.cn/mobile/login/authLogin?uid='.$v['uid'].'" target="_blank" class="btn btn-primary btn-xs">跳转</button>';
                }

                switch($v['status']){
                    case 0:
                        $v['status'] = '已发送';
                        break;
                    case 1:
                        $v['status'] = '已授权';
                        break;
                    case -1:
                        $v['status'] = '拒绝授权';
                        break;
                }
                $v['app_type'] = explode('/',$v['app_type']);
                foreach($v['app_type'] as $kk=>$vv){
                    $v['app_type'][$kk] = '';
                    $v['app_type'][$kk] .= '['.$app_type[$vv].'] ';
                }

                $v['customer_app'] = explode('/',$v['customer_app']);
                foreach($v['customer_app'] as $kk=>$vv){
                    $v['customer_app'][$kk] = '';
                    $v['customer_app'][$kk] .= '['.$app_type[$vv].'] ';
                }

            }
            $total = Db::name('customer_authorization_app')->count();


            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list,]);
        }else{
            return view();
        }
    }

    //发送授权请求
    public function send_auth_request(Request $request){
        $dat = input();
        if(request()->isPost() || request()->isAjax()){
            $data = [];
            $dat['user_id'] = explode('/',$dat['user_id']);
            $data['uid']=$dat['user_id'][0];
            $data['openid']=$dat['user_id'][1];
            if(empty($data['openid'])){
                return json(['code'=>-1,'message'=>'该商户openid不能为空！']);
            }
            if(empty($dat['app_type'])){
                return json(['code'=>-1,'message'=>'授权应用不能为空！']);
            }

            $data['app_type']= '';
            foreach($dat['app_type'] as $k=>$v){
                $data['app_type'] .= $k.'/';
            }
            $data['app_type'] = substr($data['app_type'],0,strlen($data['app_type'])-1);
            $data['status']=0;
            $data['send_time'] = time();

            try{
                //step1:插入数据
                $id = Db::name('customer_authorization_app')->insertGetId($data);

                //step2:发送微信通知客户进行授权
//                $msg = '<a href="https://shop.gogo198.cn/app/index.php?i=3&c=entry&p=app_authorization&do=member&m=sz_yi&op=display&id='.$id.'">購購网向你申请应用授权</a>';

                $post = json_encode([
//                    'call'   =>'sendTextToFans',
//                    'msg'=> $msg,
//                    'touser' => $data['openid']
                    'call'   =>'send_common_msg',
                    'title'  =>'标题',
                    'content'=> '購購网向你申请应用授权',
                    'url'    => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&p=app_authorization&do=member&m=sz_yi&op=display&id='.$id,
                    'openid' => $data['openid']
                ]);

                $res = httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post,array('Content-Type:application/json;charset=utf-8',));
                if($res){
                    return json(['code'=>1,'message'=>'发送成功']);
//                    show_json(1);
                }
            }catch(\Exception $e){
                return json(['code'=>-1,'message'=>$e->getMessage()]);
            }
        }else{
            //step1:获取商户
            $user = Db::name('decl_user')->where('user_status',0)->select();

            $this->assign('user',$user);
            return view();
        }
    }
}