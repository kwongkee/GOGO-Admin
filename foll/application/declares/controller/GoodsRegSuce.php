<?php

namespace app\declares\controller;

use think\Request;
use think\Loader;


class GoodsRegSuce extends BaseAdmin
{
    
    public function index ()
    {
        return view('goodsreg/upload_goods');
    }
    
    public function saveGoodsRegSuce ( Request $request )
    {
        $dirPath = ROOT_PATH . '/public/uploads/excel/';
        $fileObj = $request->file('file');
        if ( !is_object($fileObj) ) {
            $this->error('未知错误', Url('goodsregsuce/index'));
        }
        
        $moveInfo = $fileObj->move($dirPath);
        
        if ( !$moveInfo ) {
            $this->error($fileObj->getError(), Url('goodsregsuce/index'));
        }
        
        $readGdServer = Loader::model('ReadGoodsFile', 'logic');
        $err          = $readGdServer->handleFile($dirPath . $moveInfo->getSaveName(), $request->post());
        if (!is_null($err)){
            $this->error($err,Url('goodsregsuce/index'));
        }
        $this->success('完成',Url('goodsregsuce/index'));
    }
}