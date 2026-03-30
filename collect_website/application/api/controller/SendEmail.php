<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class SendEmail extends Controller
{
    public function index(){
        if ( request()->isPost() || request()->isAjax())
        {

            $email = input('email');
            $title = input('title');
            $content = input('content');

            $completeCount = 0;

            $email_all = explode(';',$email);
            foreach ($email_all as $k => $v) {
                // $see_code_div = '<iframe width="1%" src="'.$content.'"></iframe>';
                $map = array();
                $map['email'] = $v;
                $map['send_title'] = $title;
                $map['send_content'] = $content;
                $map['create_time'] = time();

                $sendId = Db::name('email_send_list')->insertGetId($map);

                if($sendId)
                {
                    $send = cklein_mailAli($v, '尊敬的客户', $title, $content);
                    if($send === 'You must provide at least one recipient email address.'){
                        return json_encode(['status'=>0,'message'=>'部分发送失败']);
                    }
                    if($send == true)
                    {
                        Db::name('email_send_list')->update(array('id'=>$sendId,'status'=>1));
                        $completeCount++;
                    }
                }
            }

            if ($completeCount==count($email_all))
            {
                return json(['status'=>1,'message'=>'全部发送成功']);
            }
            else
            {
                if ($completeCount>0)
                {
                    return json(['status'=>0,'message'=>'部分发送失败']);
                }
                else
                {
                    return json(['status'=>0,'message'=>'全部发送失败']);
                }
            }

        }
    }
}