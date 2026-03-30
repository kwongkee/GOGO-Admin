<?php
namespace app\admin\controller;

use think\Request;
use app\admin\controller;
use think\Loader;
use think\Session;

class AdminVideo extends Auth{
	public function index(){
		return view('adminVideo/index',
		['title'=>'视频上传',
		]);
	}
	
	//上传视频
    public function video() {
        $data = array(
            "files" => $_FILES,
        );

        $rs = $this->upload($data);
        echo json_encode($rs);
    }

    public function upload($data) {

    	$uoload_path=dirname(dirname(dirname(dirname(__FILE__)))).'/public/';
        $code = 'video';
        $pics = $data['files'];

        $path = $uoload_path."uploads/" . $code . "/"; //上传路径
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $error_name = $error_size = $error_type = 0;
        $typeArr = array("mpg","m4v","mp4","flv","3gp","mov","avi","rmvb","mkv","wmv"); //允许上传文件格式
        $size_max = 300000 * 1024; //最大上传文件大小
        foreach ($pics as $v) {
            $name = $v['name'];
            $size = $v['size'];
            $name_tmp = $v['tmp_name'];
            $type = strtolower(substr(strrchr($name, '.'), 1)); //获取文件类型
            if ($name == '') {
                $error_name ++;
            }
            if ($size > $size_max) {
                $error_size++;
            }

            if (!in_array($type, $typeArr)) {
                $error_type++;
            }
        }

        if ($error_name > 0) {
            echo json_encode(array("code" => "no_upload_pic", "result" => "请选择上传视频"));
            exit;
        }
        if ($error_size > 0) {
            echo json_encode(array("code" => "over_size", "result" => "视频大小已超过30M！"));
            exit;
        }
        if ($error_type > 0) {
            echo json_encode(array("code" => "wrong_types", "result" => "清上传视频格式的文件！"));
            exit;
        }
        $times_success = 0;
        foreach ($pics as $v) {
            $name = $v['name'];
            $size = $v['size'];
            $name_tmp = $v['tmp_name'];

            $type = strtolower(substr(strrchr($name, '.'), 1)); //获取文件类型
            $pic_name = time() . rand(10000, 99999) . "." . $type; //文件名称
            $pic_url = $path . $pic_name; //上传后视频路径+名称
            if (move_uploaded_file($name_tmp, $pic_url)) { //临时文件转移到目标文件夹
                $times_success++;
            }
        }
        if ($times_success > 0) {
            $rs = array("code" => "0", "result" => "ok", "url" => $pic_url,"name"=>$pic_name);
        } else {
            $rs = array("code" => "config", "result" => "上传出错，请稍候再试！");
        }
        return $rs;
    }
	
}

?>