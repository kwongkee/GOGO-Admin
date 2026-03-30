<?php

namespace app\api\controller;
use think\Controller;
use think\Db;

class ParkingBackup extends Controller
{
    public function index()
    {
        $day = date('d');
        if( $day == 28 )
        {
            $filename = date('Y_m_d');
            $dumpFileName = "/www/web/default/foll/public/backups/Parking_MySQL_data_backup_".$filename.".sql";
            $this->SendEmailali('http://shop.gogo198.cn/foll/public/?s=DownloadParking/index','198@gogo198.net','数据备份提醒');
            $this->SendEmailali('http://shop.gogo198.cn/foll/public/?s=DownloadParking/index','392953685@qq.com','数据备份提醒');
        }
        
        // $fileName = 'Parking_MySQL_data_backup_' . date('YmdHis') . '.sql';
        // $dumpFileName= "/www/web/default/foll/public/backups/".$fileName;
        // if( $this->backup() == true )
        // {
        //     //$this->SendEmailali('198@gogo198.net','数据备份提醒',$dumpFileName);
        // }
    }


    public function backup()
    {
        $DbHost = 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com';
        $DbUser  = 'gogo198';
        $DbPwd   = 'Gogo@198';
        $DbName  = 'shop';
        $fileName = 'Parking_MySQL_data_backup_' . date('YmdHis') . '.sql';
        $dumpFileName = "/www/web/default/foll/public/backups/".$fileName;

        // header("Content-Disposition: attachment; filename=" . $fileName);
        // header("Content-type: application/octet-stream");
        // header("Pragma:no-cache"); 
        // header("Expires:0");

        $cmd = "mysqldump -h {$DbHost} -u {$DbUser} -p{$DbPwd} {$DbName} > {$dumpFileName} ";

        //echo $cmd;
        // exec($cmd, $result, $var);
        // var_dump($result);
        // var_dump($var);
        // $hd = fopen($dumpFileName, 'rb');
        // fread($hd, filesize($dumpFileName));
        // fclose($hd);
        return true;
    }

    public function SendEmailali($url,$email,$subject = '数据备份提醒',$path='true') {
        $name    = '系统管理员';
        $content = "提示：".date('Ymd')."数据备份成功！您可点击链接下载【<a href='".$url."'>点击下载备份文件</a>】";
        if($path == 'true'){//没有数据发送
            $status  = send_mailAli($email,$name,$subject,$content);
        } else {
            $status  = send_mailAli($email,$name,$subject,$content,['0'=>$path]);
        }
        if($status) {
            return true;
        } else {
            return false;
        }
    }
}