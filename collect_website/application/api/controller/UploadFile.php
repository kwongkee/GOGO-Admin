<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

header("Access-Control-Allow-Origin:*");
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');

class UploadFile extends Controller
{
    // 通用上传
    public function upload_file(Request $request)
    {
        // 跨域问题
        header("Access-Control-Allow-Origin:*");
        header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
        set_time_limit(0);

        $data = input();
        $path = ROOT_PATH . 'public' . DS . 'uploads' . DS . $data['folder']. DS .$data['type'];
        $this->mkdirs($path);
        $file = request()->file('file');
        $is_up=0;
        if(empty($file)){
            $is_up=1;
            $file = $data['file'];
        }

        if( $file )
        {
            if($is_up==0){
                $info = $file->rule('uniqid')->move($path);
                if( $info )
                {
                    return json(["code" => 1, "message" => "上传成功", "file_path" => 'collect_website/public/uploads/'.$data['folder'].'/'.$data['type'].'/'.$info->getSaveName() ]);
                }else{
                    return json(["code" => 0, "message" => "上传失败", "path" => "" ]);
                }
            }
            elseif($is_up==1){
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_file_name = 'fb_'.date('ymdhis').'.' . $file_extension;

                if(copy($file['tmp_name'], $path.'/'.$new_file_name)){
                    return json(["code" => 1, "message" => "上传成功", "file_path" => 'collect_website/public/uploads/'.$data['folder'].'/'.$data['type'].'/'.$new_file_name ]);
                }else{
                    return json(["code" => 0, "message" => "上传失败", "path" => "" ]);
                }
            }
        }else{
            return json(["code" => 0, "message" => "请先上传文件！"]);
        }
    }

    //判断文件夹是否存在，没有则新建。
    public function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) {
            return true;
        }
        if (!mkdirs(dirname($dir), $mode)) {
            return false;
        }
        return @mkdir($dir, $mode);
    }
}