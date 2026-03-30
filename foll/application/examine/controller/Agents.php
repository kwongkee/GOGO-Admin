<?php

namespace app\examine\controller;

use think\Db;
use think\Controller;
use think\Request;

class Agents extends Controller
{

    public function index()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $data = input();
            $id = $data['id'];
            unset($data['id']);

            if(!isset($data['status'])) {
                return ['code'=>0,'msg'=>'请选择通过或者拒绝'];
            }

            $ddata = Db::name('customs_agents_admin')->where('id',$id)->find();
            if($ddata['status'] == $data['status'])
            {
                return ['code'=>0,'msg'=>'当前已是该状态！'];
            }

            $up = Db::name('customs_agents_admin')->where(['id'=>$id])->update($data);
            if(!$up) {
                return ['code'=>0,'msg'=>'数据更新失败'];
            }

            $msg = '';
            // 通过审核
            if($data['status'] == 2) {
                $msg = '恭喜，您的代理商注册管理员审核通过！';
                // 发送短信给商户
            } else if($data['status'] == 3) {
                $msg = '您的代理商注册管理员审核不通过，请与相关人员联系！';
            }

            // 发送短信
            if($msg != '') {
                $send = $this->sendMsg($data['uphone'],$msg);
                if($send['status']<=0) {
                    return ['code'=>0,'msg'=>'手机号码格式不正确，发送失败！'];
                }
            }

            // 发送微信通知

            return ['code'=>1,'msg'=>'数据更新成功'];

        }else{
            $id = input('id');
            if( !$id )
            {
                echo '缺少参数';
            }else{
                $group = Db::name('customs_agents_group')->select();
                $data = Db::name('customs_agents_admin')->where('id',$id)->find();
                if(!$data)
                {
                    echo '暂无数据';
                }
            }
            $this->assign('data', $data);
            $this->assign('group', $group);
            return view();
        }
    }

    // 发送短信消息
    private function sendMsg($phone,$msg)
    {
        // 验证手机号码格式是否正确
        if(!$this->Mobile($phone)) {
            return ['status'=>0,'msg'=>'手机号码格式不正确'];
        }

        $config=[
            'SingnName'     =>  'Gogo购购网',
            'submittime'    =>  date('Y-m-d H:i:s',time()),
            'status'        =>  $msg,
            'tel'           =>  $phone,
            'TemplateCode'  =>  'SMS_165412505',//'SMS_35030091'
        ];

        //Session::set("yzm",$code);
        sendReg($config);

        return ['status'=>1,'msg'=>'发送成功'];

    }

    // 验证手机号码
    private function Mobile($mobile)
    {
        if(!is_numeric($mobile)) {
            return false;
        }
        return preg_match('/^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$/',$mobile) ? true : false;
    }
}