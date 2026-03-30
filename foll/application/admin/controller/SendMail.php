<?php

namespace app\admin\controller;

use app\admin\controller;
use think\Db;
use think\Request;


class SendMail extends Auth
{



    //数据列表
    public function datalist()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            // 排序
            $order = input('sort').' '.input('order');
            // 分页
            $limit = input('offset').','.input('limit');

            $map = array();

            $search = input('search');
            if($search)
            {
                $map['email|send_title'] = ['like','%'.$search.'%'];
            }

            $list = Db::name('email_send_list')->where($map)->order($order)->limit($limit)->select();
            foreach ($list as $k => $v) {
                if($v['status'])
                {
                    $list[$k]['status'] = '发送成功';
                }else {
                    $list[$k]['status'] = '发送失败';
                }
                $list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);

            }
            $total = Db::name('email_send_list')->count();
            return json(["status" => 0, "message" => "", "total" => $total, "rows" => $list]);
        }else {
            return view("sendmail/datalist",[
                'title' => '数据列表',
            ]);
        }
    }


    //配置权限
    public function gosend()
    {
        if ( request()->isPost() || request()->isAjax())
        {
            $email = input('email');
            $title = input('title');
            $content = input('content');
            $see_code = input('see_code');
            $completeCount = 0;

            $email_all = explode(';',$email);
            foreach ($email_all as $k => $v) {
                //$see_code_div = '<iframe width="1%" src="http://www.qq.com/"></iframe>';
                $map = array();
                $map['email'] = $v;
                $map['send_title'] = $title;
                $map['send_content'] = $content;
                $map['create_time'] = time();

                $sendId = Db::name('email_send_list')->insertGetId($map);

                if($sendId)
                {
                    $send = cklein_mailAli($v, '尊敬的客户', $title, $content);
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

        }else {
            $isSendCom = 0;
            if(input('isSendMyCompany')==1){
                $isSendCom = 1;
            }
            $see_code = $this->OrderNo();
            return view("sendmail/gosend",[
                'title' => '新增发送',
                'see_code' =>$see_code,
                'isSendCom'=>$isSendCom
            ]);
        }
    }

    // 获取毫秒
    private  function getMillisecond(){
        list($s1,$s2)=explode(' ',microtime());
        return (float)sprintf('%.0f',(floatval($s1)+floatval($s2))*1000);
    }

    // 生成唯一订单号
    private function OrderNo() {
        return 'Mail'.str_pad(mt_rand(1,999999),5,'0',STR_PAD_LEFT).substr(microtime(),2,6).$this->getMillisecond();
    }





}

?>
