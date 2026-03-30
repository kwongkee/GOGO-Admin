<?php

	class FtpClass{
		private $ftpObj;
		private $ftpHost = '';
		private $ftpPort = '';
		private $ftpUser = '';
		private $ftpPassword = '';
		private $localBase = '';
		
		function __construct($initData = array()){
			if(isset($initData['ftpHost']) && $initData['ftpHost']){
				$this->ftpHost = $initData['ftpHost'];
			}
			
			if(isset($initData['ftpPort']) && $initData['ftpPort']){
				$this->ftpHost = $initData['ftpPort'];
			}
			
			if(isset($initData['ftpUser']) && $initData['ftpUser']){
				$this->ftpHost = $initData['ftpUser'];
			}
			
			if(isset($initData['ftpPassword']) && $initData['ftpPassword']){
				$this->ftpHost = $initData['ftpPassword'];
			}
			
			if(isset($initData['localBase']) && $initData['localBase']){
				$this->ftpHost = $initData['localBase'];
			}
		}
		//ftp连接登录
		function ftp_connect(){
			if(!$this->ftpObj){
				$this->ftpObj = ftp_connect($this->ftpHost,$this->ftpPort);
				if($this->ftpObj){
					if(ftp_login($this->ftpObj,$this->ftpUser,$this->ftpPassword)){
						return true;
					} else {
						return false;
					}
				} else {
					return false;
				}
			}
		}
		
		function ftp_download_file($fileName) {
			//获取FTP路径
			$ftpPath = dirname($fileName).'/';
			//获取文件名
			$selectFile = basename($fileName);
			//进入指定路径
			if(@ftp_chdir($this->ftpObj,$ftpPath)){
				//$localBase 如果不存在，新创建目录，务必确保 有创建权限
				if(!is_dir($this->localBase)){
					mkdir($this->localBase,0777);
				}$toFile = $this->localBase.$fileName;
				//下载指定的FTP文件到指定的本地文件
				if(ftp_get($this->ftpObj,$toFile,$selectFile,FTP_BINARY)){
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
		//退出FTP
		function ftp_quit() {
			if($this->ftpObj){
				ftp_quit($this->ftpObj);
			}
		}
	}
	
	$ftpArr = [
		'ftpHost'=>'117.48.196.171',
		'ftpPort'=>21,
		'ftpUser'=>'qs',
		'ftpPassword'=>'ylink!qaz',
		'localBase'=>'/checkFile/access/7000000000000049/20171228/70000000000000492017122801.TXT'
	];
	$ftp = new FtpClass($ftpArr);
	$ftp->ftp_download_file();

//https://mp.weixin.qq.com/mp/video?__biz=MzUyMzMzODgyMw==&
//tempkey=OTU2X1lJamNJR0FxSy8vM1BlMWZ6NEJvcFlEdzVWTjNnTlpzOFFjSUN3LUduQ2luaWNZblBwUFpPQWs5REJTVXhQTFh0RnV4Wm1JbnZ5eHpPSHBSanZKVlZBcGp0NUlsRW1zeGs3QkpBZk05ekJ3QWFoVy1xMjNtcGJ6d3hOWlFOR2VlLU1BQzhGVWNJN1E3Uy1Pa2RyRFQ1Q2pFc3JfbXVPbVVaaDhOTVF%2Bfg%3D%3D&
//chksm=7a3f543d4d48dd2b095788b020c7af020cb0a9bd0bd4cda918c21ee45c1b37222cfc42b32af1&
//scene=0&previewkey=yCT%252BKnG1pFE%252BeqZ0NRtL18NS9bJajjJKzz%252F0By7ITJA%253D&
//version=6206021b&ascene=1&session_us=gh_b0a35f793e25&lang=zh_CN&devicetype=Windows+7&winzoom=1



//https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx01af4897eca4527e&secret=497ccae978cb1bca589d587d60da8f8d
?>