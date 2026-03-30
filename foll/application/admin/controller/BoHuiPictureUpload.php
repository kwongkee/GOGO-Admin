<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;

class BoHuiPictureUpload extends Controller
{
    public function uploadView(Request $request)
    {
        return $this->fetch('bohui/bohui_picture_upload',['title'=>'批量导图']);
    }
}