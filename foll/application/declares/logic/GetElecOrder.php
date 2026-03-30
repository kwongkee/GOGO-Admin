<?php

namespace app\declares\logic;

use think\Model;
use think\Loader;
use think\Db;
use think\Cache;

class GetElecOrder extends Model
{
    
    protected $excelObj;
    
    protected $postData;
    
    protected $id;
    
    public function inits ( $file, $postData )
    {
        try{
            $this->postData = $postData;
            $fileLogic      = Loader::model('ReadElcOrderFile', 'logic');
            $this->excelObj = $fileLogic->moveFile($file)->PHPExcelObj();
            return $this;
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
        
    }
    
    
    protected function getReqHead ()
    {
        $this->id = $this->postData['MessageType'] . '_' . $this->postData['EDI'] . '_' . date("YmdHis", time()) . mt_rand(11111, 999999);
        return ['MessageID' => $this->id, 'MessageType' => $this->postData['MessageType'], 'Sender' => $this->postData['EDI'], 'Receiver' => $this->postData['DeclEntNo'], 'SendTime' => date('YmdHis', time()), 'FunctionCode' => count($this->postData['FunctionCode']) == 2 ? 'BOTH' : $this->postData['FunctionCode'][0], 'SignerInfo' => '待定', 'Version' => '3.0'];
    }
    
    public function getDatas ()
    {
        Db::startTrans();
        try{
            $reqHeader    = $this->getReqHead();
            $reqHeader    = json_encode($reqHeader);
            $orderDetails = $this->excelObj->getTwoSheet($this->postData);
            $goods = $this->excelObj->getGoodsSheet();
            $uid = Session('admin.id');
            $this->addCount(count($orderDetails['OrderList']),$uid);
            foreach ($orderDetails['OrderList'] as &$val){
                if (isset($goods[$val['WaybillNo']])){
                    $val['goods_list'] = $goods[$val['WaybillNo']];
                    $val['OrderHead'] = $orderDetails['OrderHead'];
                    $this->saveInfo($reqHeader,$val,$uid);
                }
            }
            Db::commit();
            unset($orderDetails,$goods);
            return null;
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage().$e->getLine());
        }
   
    }
    
    
  
    public function saveInfo($head,$data,$uid)
    {
        Db::name('foll_order_electmp')->insert([
           'WaybillNo' => $data['WaybillNo'],
            'h_conten' => $head,
            'ord_body' => json_encode($data),
            'uid'      => $uid,
            'remk'     => null,
            'create_at'=>time(),
            'batch_num'=>$this->postData['batch_num']
        ]);
    }
    
    /**
     * 添加总数
     * @param $num
     */
    public function addCount($num,$uid)
    {
        Cache::set('batchCount:'.$this->postData['batch_num'],$num,21600);
        Db::name('foll_elec_count')->insert([
            'uid'    => $uid,
            'batch_num'=> $this->postData['batch_num'],
            'total_count'=>$num,
            'batchCount'=>$num
        ]);
    }
}