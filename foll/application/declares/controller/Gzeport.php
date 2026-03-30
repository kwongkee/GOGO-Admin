<?php

namespace app\declares\controller;
use app\declares\controller;
use think\Request;
use think\Loader;

class Gzeport extends BaseAdmin{
    const EXCELPATH = 'public/uploads/excel/';

    /*
  * 列出备案商品
  */
    public function gzeport_list(){
        $getGoodsRegList = Loader::model("GetGoodsRegList",'logic');
        $result = $getGoodsRegList->get_list();
        $status = ['申报成功','申报失败','未申报'];
        $OpType = ['A'=>'新增','M'=>'修改','D'=>'取消备案'];
        $cStatus = ['待审核','通过','拒绝'];
        return view(
            "gzeport/gzeport_list",
            [
                'page'=>$result->render(),
                'data'=>$result->toArray(),
                'status'=>$status,
                'optype' =>$OpType,
                'cStatus' =>$cStatus,
            ]
        );
    }


    public function fillMessage(){
        return view('gzeport/gzeport_add');
    }



    public function getPostData(Request $request){
        $file = $request->file('file');

        if(!is_object($file)){
            $this->error('请上传文件',Url('Gzeport/fillMessage'));
        }

        $verifResult = $file->validate(['ext'=>'xls','size'=>10485760])->rule('md5')->move( './uploads/excel');

        if($verifResult===false){
            $this->error($file->getError(),Url('Gzeport/fillMessage'));
        }

        $gzeportLogic = Loader::model("Gzeport",'logic');

        //read commodity data from xls file and return result generate xml format
        $xmlResult = $gzeportLogic->getExcelData(
            ROOT_PATH.self::EXCELPATH,
            $verifResult->getSaveName(),
            $request->post()
        );

        if($xmlResult['result']===false){
            $this->error($xmlResult['description'],Url('Gzeport/fillMessage'));
        }

        $this->success($xmlResult['description'],Url('Gzeport/fillMessage'));
    }



    public function getAllGoodsInfo(Request $request){
        $goodsReglisgModel = Loader::model("GetGoodsRegList",'logic');
        $infoResult = $goodsReglisgModel->get_all($request->get('id'));
        return view(
            'gzeport/goods_all_info',
            [
                'data' => $infoResult->toArray(),
                'page' => $infoResult->render(),
                'isFlag' => ['是','否'],
                'RegStatus' =>['C'=>'成功备案','N'=>'备案不成功'],
                'type'  =>['新增','变更','删除']
            ]
        );
    }

    public function goodsPictureUpload(Request $request)
    {
        if($request->isGet()){
            return view("gzeport/goods_picture_upload");
        }
        if($request->isPost()) {
           return $this->batchWithPicture($request);
        }
    }

    /*
     * 批量处理上传图片
     */
    protected function batchWithPicture($request)
    {
        $files = $request->file('file');
        if(!is_object($files))return json(['code'=>-1,'msg'=>'异常']);
        $info = $files->validate(['ext'=>'png,jpg'])
            ->rule(function ($files){
                return $files->getInfo('info')['name'];
            })
            ->move('../../attachment/images/'.Session('admin')['uniacid'].'/'.date('Y',time()).'/'.date('m',time()));
        if($info){
            return json(['code'=>0,'msg'=>'上传成功','info'=>$info]);
        }else{
            return json(['code'=>-1,'msg'=>$files->getError()]);
        }
        // http://shop.gogo198.cn/attachment/images/3/2018/09/QZJC83338.jpg
    }

    public function generateExcelAndDown(Request $request)
    {
//        $goodsReclLogic = Loader::model("GenerateGoodsInfoExcel","logic");
//        $file = $goodsReclLogic->getAllGoodsNum($request->get('hid'));
//        $data = $goodsReclLogic->getAllGoodsRecInfo(Session('admin')['id']);
//        dump($data);
//        $file = $goodsReclLogic->writeExcelFile($goodsReclLogic->getAllGoodsRecInfo(Session('admin')['id']));
    }

    /*
     * 申报海关
     */

    public function goodsReg(Request $request)
    {
       $result = Loader::model('Declaration','logic')->goodsReg($request->get('hid'));
       if(!$result['result']){
           $this->error($result['msg'],Url('Gzeport/gzeport_list'));
       }
       $this->success($result['msg'],Url('Gzeport/gzeport_list'));
    }

}