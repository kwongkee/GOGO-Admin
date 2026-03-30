<?php
error_reporting(7);
if ($_SERVER['SERVER_ADDR'])exit;//禁止在web服务器下运行
$_GET['exclude'] = array('number.txt','uploads','Zend','docs','cache','You','managesdk'); //排除上传目录,定义为全局变量
$fileobj = new FilerFile();
//$path = "/data/longtu/"; //项目目录的根目录
$path = '/checkFile/access/7000000000000049/20171228/';
$files = $fileobj->Zip($path); //过滤出最新的修改文件
$config = array(
  'hostname' => '117.48.196.171', //ftp服务器 地址
  'username' => 'qs',     //ftp用户
  'password' => 'ylink!1qaz', //ftp密码
  'port' => 21         //端口
);

$ftp = new Ftp();
//链接服务器
echo $ftp->connect($config);
//获取文件列表
$a=$ftp->filelist();
var_dump($a);
//下载文件
$ftp->download('/checkFile/access/7000000000000049/20171228/700000000000004920171228.TXT','ftp_download.log');
die;
//$LOCAL_ROOT = realpath(dirname(__DIR__)."/../../");
//chdir($LOCAL_ROOT);
//foreach ($files as $k=>$v){
//$f = $path.$v;
//$tmp = $ftp->upload($f, $f);
//if($tmp){
//	echo "upload $f -> success \n";
//}
//}


//$ftp->upload('ftp_err.log','ftp_upload.log');
//$ftp->download('ftp_upload.log','ftp_download.log');
/*
 *
 *
 * $dir = "/test";
 if(@ftp_chdir($conn, $dir))
  判断是否为文件夹
 * Enter description here ...
 * @author Administrator
 *
 */
class FilerFile
{
	 var $time_path;
	 private $fctimes = array();
	 function Zip($dir)
	 {
	  $this->time_path = rtrim($dir,"/")."/.~~~time.php";
	  //@unlink($this->time_path);
	  $filelist = $this -> GetFileList($dir);
	  file_put_contents($this->time_path,"<?php \n return ".var_export($this->fctimes,true).";");
	  return $filelist;
	 }
	 
	 function appendFilectime($file)
	 {
	  $time_file_path = $this->time_path;
	  $ftime = @include($time_file_path);
	  $ftime = $ftime ? $ftime : array();
	  $time = filectime($file);
	  if(!file_exists($time_file_path))file_put_contents($time_file_path,"<?php \n");
	 }
	 
	 function getFileByFilectime($file)
	 {
	  static $time_data ;
	  $time_file_path = $this->time_path;
	  if (!$time_data){
	   $time_data= @include_once($time_file_path);
	  }
	  $time_data = $time_data ? $time_data : array();
	  //var_dump($file,$time_data[$file] == filectime($file));
	  //echo $file."\t".$time_data[$file]."\n";
	  if ($time_data[$file] == filemtime($file)){
	   return false;
	  }else{
	   return $file;
	  }
	 }
	 
 	function GetFileList($dir,$path="")
 	{
	  static $tmpp = "";
	  if ($path=="" && !$tmpp){
	   $tmpp = $dir;
	  }
	  $d = dir($dir);
	  $files = array();
	  if ($d)
	  {
	   $pathP=str_replace($tmpp,"",$dir);
	   $pathP=str_replace(array("\\\\","/"),DIRECTORY_SEPARATOR,$pathP);
	   $pathP=str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR,$pathP);
	   while($f = $d->read())
	   {
	    if ($f == '.' || $f=='..' || $f{0}=='.' || $f=='Zend' || in_array($f, $_GET['exclude']))continue;
	    $newdir = rtrim($dir,"/")."/".$f;
	    if (is_dir($newdir)){
	     $files = array_merge($files,$this->GetFileList($newdir,$newdir));
	    }else{
	     $abspath_file = (rtrim($dir,"/") ? rtrim($dir,"/")."/" : "").$f;
	     $this->fctimes[$abspath_file] = filemtime($abspath_file);
	     if (!$this->getFileByFilectime($abspath_file))continue;
	     $file = (rtrim($pathP,"/") ? rtrim($pathP,"/")."/" : "").$f;
	     $files[] = $file;
	    }
	   }
	  }
	  return $files;
 	}
}
	
	
/**
 * 仿写CodeIgniter的FTP类
 * FTP基本操作：
 * 1) 登陆;     connect
 * 2) 当前目录文件列表; filelist
 * 3) 目录改变;   chgdir
 * 4) 重命名/移动;   rename
 * 5) 创建文件夹;    mkdir
 * 6) 删除;       delete_dir/delete_file
 * 7) 上传;       upload
 * 8) 下载        download
 *
 * @author quanshuidingdang
 */
class Ftp {
 private $hostname  = '';
 private $username  = '';
 private $password  = '';
 private $port  = 21;
 private $passive = TRUE;
 private $debug   = TRUE;
 private $conn_id = FALSE;
 /**
  * 构造函数
  *
  * @param  array  配置数组 : $config = array('hostname'=>'','username'=>'','password'=>'','port'=>''...);
  */
 public function __construct($config = array()) {
  if(count($config) > 0) {
   $this->_init($config);
  }
 }
 /**
  * FTP连接
  *
  * @access public
  * @param  array  配置数组
  * @return boolean
  */
 public function connect($config = array()) {
  if(count($config) > 0) {
   $this->_init($config);
  }
  if(FALSE === ($this->conn_id = @ftp_connect($this->hostname,$this->port))) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_connect");
   }
   return FALSE;
  }
  if( ! $this->_login()) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_login");
   }
   return FALSE;
  }
  if($this->passive === TRUE) {
   ftp_pasv($this->conn_id, TRUE);
  }
  return TRUE;
 }
 /**
  * 文件夹是否存在
  * @param unknown_type $path
  */
 public function is_dir_exists($path)
 {
  return $this->chgdir($path);
 }
 /**
  * 目录改变
  *
  * @access public
  * @param  string 目录标识(ftp)
  * @param  boolean 
  * @return boolean
  */
 public function chgdir($path = '', $supress_debug = FALSE) {
  if($path == '' OR ! $this->_isconn()) {
   return FALSE;
  }
  $result = @ftp_chdir($this->conn_id, $path);
  if($result === FALSE) {
   if($this->debug === TRUE AND $supress_debug == FALSE) {
    $this->_error("ftp_unable_to_chgdir:dir[".$path."]");
   }
   return FALSE;
  }
  return TRUE;
 }
 /**
  * 目录生成
  *
  * @access public
  * @param  string 目录标识(ftp)
  * @param  int  文件权限列表 
  * @return boolean
  */
 public function mkdir($path = '', $permissions = NULL) {
  if($path == '' OR ! $this->_isconn()) {
   return FALSE;
  }
  $result = @ftp_mkdir($this->conn_id, $path);
  if($result === FALSE) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_mkdir:dir[".$path."]");
   }
   return FALSE;
  }
  if( ! is_null($permissions)) {
   $this->chmod($path,(int)$permissions);
  }
  return TRUE;
 }
 /**
  * 上传
  *
  * @access public
  * @param  string 本地目录标识
  * @param  string 远程目录标识(ftp)
  * @param  string 上传模式 auto || ascii
  * @param  int  上传后的文件权限列表 
  * @return boolean
  */
 public function upload($localpath, $remotepath, $mode = 'auto', $permissions = NULL) {
  if( ! $this->_isconn()) {
   return FALSE;
  }
  if( ! file_exists($localpath)) {
   if($this->debug === TRUE) {
    $this->_error("ftp_no_source_file:".$localpath);
   }
   return FALSE;
  }
  if($mode == 'auto') {
   $ext = $this->_getext($localpath);
   $mode = $this->_settype($ext);
  }
  $mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;
  $result = @ftp_put($this->conn_id, $remotepath, $localpath, $mode);
  if($result === FALSE) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_upload:localpath[".$localpath."]/remotepath[".$remotepath."]");
   }
   return FALSE;
  }
  if( ! is_null($permissions)) {
   $this->chmod($remotepath,(int)$permissions);
  }
  return TRUE;
 }
 /**
  * 下载
  *
  * @access public
  * @param  string 远程目录标识(ftp)
  * @param  string 本地目录标识
  * @param  string 下载模式 auto || ascii 
  * @return boolean
  */
 public function download($remotepath, $localpath, $mode = 'auto') {
  if( ! $this->_isconn()) {
   return FALSE;
  }
  if($mode == 'auto') {
   $ext = $this->_getext($remotepath);
   $mode = $this->_settype($ext);
  }
  $mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;
  $result = @ftp_get($this->conn_id, $localpath, $remotepath, $mode);
  if($result === FALSE) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_download:localpath[".$localpath."]-remotepath[".$remotepath."]");
   }
   return FALSE;
  }
  return TRUE;
 }
 /**
  * 重命名/移动
  *
  * @access public
  * @param  string 远程目录标识(ftp)
  * @param  string 新目录标识
  * @param  boolean 判断是重命名(FALSE)还是移动(TRUE) 
  * @return boolean
  */
 public function rename($oldname, $newname, $move = FALSE) {
  if( ! $this->_isconn()) {
   return FALSE;
  }
  $result = @ftp_rename($this->conn_id, $oldname, $newname);
  if($result === FALSE) {
   if($this->debug === TRUE) {
    $msg = ($move == FALSE) ? "ftp_unable_to_rename" : "ftp_unable_to_move";
    $this->_error($msg);
   }
   return FALSE;
  }
  return TRUE;
 }
 /**
  * 删除文件
  *
  * @access public
  * @param  string 文件标识(ftp)
  * @return boolean
  */
 public function delete_file($file) {
  if( ! $this->_isconn()) {
   return FALSE;
  }
  $result = @ftp_delete($this->conn_id, $file);
  if($result === FALSE) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_delete_file:file[".$file."]");
   }
   return FALSE;
  }
  return TRUE;
 }
 /**
  * 删除文件夹
  *
  * @access public
  * @param  string 目录标识(ftp)
  * @return boolean
  */
 public function delete_dir($path) {
  if( ! $this->_isconn()) {
   return FALSE;
  }
  //对目录宏的'/'字符添加反斜杠'\'
  $path = preg_replace("/(.+?)\/*$/", "\\1/", $path);
  //获取目录文件列表
  $filelist = $this->filelist($path);
  if($filelist !== FALSE AND count($filelist) > 0) {
   foreach($filelist as $item) {
    //如果我们无法删除,那么就可能是一个文件夹
    //所以我们递归调用delete_dir()
    if( ! @delete_file($item)) {
     $this->delete_dir($item);
    }
   }
  }
  //删除文件夹(空文件夹)
  $result = @ftp_rmdir($this->conn_id, $path);
  if($result === FALSE) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_delete_dir:dir[".$path."]");
   }
   return FALSE;
  }
  return TRUE;
 }
 /**
  * 修改文件权限
  *
  * @access public
  * @param  string 目录标识(ftp)
  * @return boolean
  */
 public function chmod($path, $perm) {
  if( ! $this->_isconn()) {
   return FALSE;
  }
  //只有在PHP5中才定义了修改权限的函数(ftp)
  if( ! function_exists('ftp_chmod')) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_chmod(function)");
   }
   return FALSE;
  }
  $result = @ftp_chmod($this->conn_id, $perm, $path);
  if($result === FALSE) {
   if($this->debug === TRUE) {
    $this->_error("ftp_unable_to_chmod:path[".$path."]-chmod[".$perm."]");
   }
   return FALSE;
  }
  return TRUE;
 }
 /**
  * 获取目录文件列表
  *
  * @access public
  * @param  string 目录标识(ftp)
  * @return array
  */
	public function filelist($path = '.') {
  	if( ! $this->_isconn()) {
  		return FALSE;
  	}
  
  	return ftp_nlist($this->conn_id, $path);
	}
 
 
 /**
  * 关闭FTP
  *
  * @access public
  * @return boolean
  */
 public function close() {
  if( ! $this->_isconn()) {
   return FALSE;
  }
  return @ftp_close($this->conn_id);
 }
 /**
  * FTP成员变量初始化
  *
  * @access private
  * @param  array  配置数组  
  * @return void
  */
 private function _init($config = array()) {
  foreach($config as $key => $val) {
   if(isset($this->$key)) {
    $this->$key = $val;
   }
  }
  //特殊字符过滤
  $this->hostname = preg_replace('|.+?://|','',$this->hostname);
 }
 /**
  * FTP登陆
  *
  * @access private
  * @return boolean
  */
 private function _login() {
  return @ftp_login($this->conn_id, $this->username, $this->password);
 }
 /**
  * 判断con_id
  *
  * @access private
  * @return boolean
  */
 private function _isconn() {
  if( ! is_resource($this->conn_id)) {
   if($this->debug === TRUE) {
    $this->_error("ftp_no_connection");
   }
   return FALSE;
  }
  return TRUE;
 }
 /**
  * 从文件名中获取后缀扩展
  *
  * @access private
  * @param  string 目录标识
  * @return string
  */
 private function _getext($filename) {
  if(FALSE === strpos($filename, '.')) {
   return 'txt';
  }
  $extarr = explode('.', $filename);
  return end($extarr);
 }
 /**
  * 从后缀扩展定义FTP传输模式 ascii 或 binary
  *
  * @access private
  * @param  string 后缀扩展
  * @return string
  */
 private function _settype($ext) {
  $text_type = array (
       'txt',
       'text',
       'php',
       'phps',
       'php4',
       'js',
       'css',
       'htm',
       'html',
       'phtml',
       'shtml',
       'log',
       'xml'
    );
  	return (in_array($ext, $text_type)) ? 'ascii' : 'binary';
 }
 /**
  * 错误日志记录
  *
  * @access prvate
  * @return boolean
  */
 private function _error($msg) {
  return @file_put_contents('/tmp/ftp_err.log', "date[".date("Y-m-d H:i:s")."]-hostname[".$this->hostname."]-username[".$this->username."]-password[".$this->password."]-msg[".$msg."]\n", FILE_APPEND);
 }
}
/*End of file ftp.php*/
/*Location /Apache Group/htdocs/ftp.php*/