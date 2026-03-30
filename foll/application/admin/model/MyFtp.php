<?php
namespace app\admin\model;
	class MyFtp {
	var $connector;
	var $getback;
	//连接FTP
	function connect($ftp_server, $uname, $passwd){
	$this->connector = @ftp_connect($ftp_server);
	$this->login_result = @ftp_login($this->connector, "$uname", "$passwd");
        if ((!$this->connector)&&(!$this->login_result))
        {
            echo "FTP connection has failed! \n";
            echo "Attempted to connect to $ftp_server for user $uname \n";
            die;
        } else {
            echo "Connected to $ftp_server, for user $uname \n";
        }
    }
    function lastmodtime($value){
        $getback = ftp_mdtm ($this->connector,$value);
        return $getback;
    }
    //更改当前目录
    function changedir($targetdir){
        $getback = ftp_chdir($this->connector, $targetdir);
        return $getback;
    }
    //获取当前目录
    function getdir(){
        $getback = ftp_pwd($this->connector);
        return $getback;
    }
    //获取文件列表
    function get_file_list($directory){
        $getback = ftp_nlist($this->connector, $directory);
        return $getback;
    }
    //获取文件
    function get_file($file_to_get, $mode, $mode2){
        $realfile = basename($file_to_get);
        $filename = $realfile;
        $checkdir = @$this->changedir($realfile);
        if ($checkdir == TRUE){
            ftp_cdup($this->connector);
            echo "\n[DIR] $realfile";
        }else{
            echo "..... ". $realfile ."\n";
            $getback = ftp_get($this->connector, $filename, $realfile, $mode);
            if ($mode2){
                $delstatus = ftp_delete($this->connector, $file_to_get);
                if ($delstatus == TRUE){
                    echo "File $realfile on $host deleted \n";
                }
            }
        }
        return $getback;
    }
    function mode($pasvmode){
        $result = ftp_pasv($this->connector, $pasvmode);
    }
    //退出
    function ftp_bye(){
        ftp_quit($this->connector);
        return $getback;
    }
}
?>