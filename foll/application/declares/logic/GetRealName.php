<?php

namespace app\declares\logic;

use think\Model;
use think\Loader;
use think\Db;
class GetRealName extends Model
{
    
    protected $excelObj;
    
    protected $postData;
    
    protected $id;
    
    public function inits ( $file, $postData )
    {
        $this->postData = $postData;
        $fileLogic      = Loader::model('RealNameFile', 'logic');
        $this->excelObj = $fileLogic->moveFile($file)->PHPExcelObj();
        return $this;
    }
    
    protected function getReqHead ()
    {
        $this->id = $this->postData['MessageType'] . '_' . $this->postData['EDI'] . '_' . date("YmdHis", time()) . mt_rand(11111, 999999);
        return ['MessageID' => $this->id, 'MessageType' => $this->postData['MessageType'], 'Sender' => $this->postData['EDI'], 'Receiver' => $this->postData['DeclEntNo'], 'SendTime' => date('YmdHis', time()), 'FunctionCode' => count($this->postData['FunctionCode']) == 2 ? 'BOTH' : $this->postData['FunctionCode'][0], 'SignerInfo' => '待定', 'Version' => '3.0'];
    }
    
    public function getDatas ()
    {
        $reqHeader    = $this->getReqHead();
        $reqHeader    = json_encode($reqHeader);
        $orderDetails = $this->excelObj->getTwoSheet($this->postData);
        $goods = $this->excelObj->getGoodsSheet();
        $uid = Session('admin.id');
        foreach ($orderDetails['OrderList'] as &$val){
            $val['goods_list'] = $goods[$val['WaybillNo']];
            $val['OrderHead'] = $orderDetails['OrderHead'];
            $this->saveInfo($reqHeader,$val,$uid);
        }
        unset($orderDetails,$goods);
        return null;
    }
    
  
    public function saveInfo($head,$data,$uid)
    {
        Db::name('foll_realname_general')->insert([
           'WaybillNo' => $data['WaybillNo'],
            'h_conten' => $head,
            'ord_body' => json_encode($data),
            'uid'      => $uid,
            'remk'     => null,
            'create_at'=>time()
        ]);
    }
    
}