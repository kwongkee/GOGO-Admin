<?php
namespace app\declares\logic;
use think\Model;
use think\Loader;
class GetElecOrder extends Model
{
    protected  $excelObj;
    protected $postData;
    protected $id;
    public function inits($file,$postData)
    {
        $this->postData = $postData;
        $fileLogic = Loader::model('ReadElcOrderFile','logic');
        $this->excelObj = $fileLogic->moveFile($file)->PHPExcelObj();
        return $this;
    }
    public function getDatas()
    {
        $reqHeader    = $this->getReqHead();
        $orderDetails = $this->excelObj->getTwoSheet($this->postData);
        return ['Head'=>$reqHeader,'datas'=>$orderDetails];
    }

    protected function getReqHead()
    {
        $this->id = $this->postData['MessageType'] . '_' . $this->postData['EDI'] . '_' . date("YmdHis", time()) . mt_rand(11111, 999999);
        return ['MessageID' => $this->id, 'MessageType' => $this->postData['MessageType'], 'Sender' => $this->postData['EDI'], 'Receiver' => $this->postData['DeclEntNo'], 'SendTime' => date('YmdHis',time()), 'FunctionCode' => count($this->postData['FunctionCode']) == 2 ? 'BOTH' : $this->postData['FunctionCode'][0], 'SignerInfo' => '待定', 'Version' => '3.0'
        ];
    }
    
}