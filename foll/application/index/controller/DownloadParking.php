<?php
namespace app\index\controller;
use app\index\controller;
use think\Request;
use think\Db;
use think\Session;

class DownloadParking 
{
    public function index()
    {
        return view("download_parking/index");
    }

    public function sendcode()
    {
        $email = input('email');
        $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
        if( $this->SendEmailali($code,$email) == true )
        {
            Session::set("downCode", $code);
            return ['code'=> 1, 'msg'=>'发送成功'];
        }else{
            return ['code'=> -1, 'msg'=>'发送失败'];
        }
    }

    public function down()
    {
        $email = input('email');
        $code = input('code');
        if( Session::get('downCode') == $code )
        {
            $filename = date('Y_m').'_28';
            if( file_exists('/www/web/default/foll/public/backups/Parking_MySQL_data_backup_'.$filename.'.sql') )
            {
                $url = 'http://shop.gogo198.cn/foll/public/backups/Parking_MySQL_data_backup_'.$filename.'.sql';
                Session::set('downCode', null);
                return ['code'=>1,'msg'=>'验证成功，开始下载','url'=>$url];
            }else{
                return ['code'=>0,'msg'=>'当月备份文件还没有生成'];
            } 
        }else{
            return ['code'=>0,'msg'=>'请输入正确的验证码'];
        }
    }

    public function SendEmailali($code,$email,$subject = '验证码') {
        $name    = $email;
        $content = "您的验证码为：".$code;
        $status  = send_mailAli($email,$name,$subject,$content);
        if($status) {
            return true;
        } else {
            return false;
        }
    }
}