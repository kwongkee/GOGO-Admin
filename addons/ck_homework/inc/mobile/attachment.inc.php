<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}

$picname = $_FILES['mypic']['name'];
$picsize = $_FILES['mypic']['size']; //获取图片的大小

if ($picname != "") {

	//图片大小的限制
	if ($picsize > 5120000) {
		exit('大小不能超过5M');
	}

	//设置上传格式
	$allowpictype = array('jpg','gif','png');
	//判断后缀
	$fileext = strtolower(trim(substr(strrchr($_FILES['mypic']['name'], '.'), 1)));
	if(!in_array($fileext, $allowpictype)) {
		exit("$upload_file_ext:上传格式不正确！");
	}
	
	$path = "images/{$_GET['i']}/" . date('Y/m/');
	$attach_dir = IA_ROOT . "/addons/{$_GET['m']}/data/" . $path;
	if (!is_dir($attach_dir)) {
		load()->func('file');
		mkdirs($attach_dir);
	}

	$type = strstr($picname, '.');

	//设置上传文件名
	$rand = rand(100, 999);
	$pics = date("YmdHis") . $rand . $type;

	//上传目录
	$uploadPath = $attach_dir . $pics;
	move_uploaded_file($_FILES['mypic']['tmp_name'], $uploadPath);
	
	$size = round($picsize/1024,2);
	$arr = array(
		'name' => $picname,
		'src' => $path . $pics,
		'size' => $size
	);
	
	echo json_encode($arr);
	exit;
}else{
	
	exit('上传文件不能为空！');
}