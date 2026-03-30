<?php
namespace app\api\controller;
use think\Controller;
use think\Request;
use think\Loader;
use think\Cache;
class AcceptGzeportCallBack extends Controller{
    protected $gCode = array('KJ881101'=>'commodityRec','KJ881111'=>'electronicOrderRec','KJDOCREC'=>'isDocrecError');
    protected $argvs;

    /*
     * 统一接受数据分发处理
     */
    public function accpetReceiptInfo(Request $request){
//        $inputData = file_get_contents('php://input');
        $inputData = $request->param("messageText");
        @file_put_contents('../runtime/log/gzeportlog/'.date('Ymd',time()).'_roll.log',date('Y-m-d H:i:s',time())."---".base64_encode($request->param("messageText"))."\n",FILE_APPEND);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://declare.gogo198.cn/api/customs/receipt",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $inputData,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/xml",
                "cache-control: no-cache"
            ),
        ));
        curl_exec($curl);
        curl_close($curl);
        return 'success';
//        libxml_disable_entity_loader(true);
//        $decodeData = simplexml_load_string($inputData, 'SimpleXMLElement', LIBXML_NOCDATA);
//        $this->argvs= json_decode(json_encode($decodeData),true);
//        return call_user_func([$this,$this->gCode[$this->argvs['Declaration']['OrgMessageType']]]);
    }
    /*
     * 商品备案回执
     */
    protected function commodityRec()
    {
        if(!isset($this->argvs['Declaration']['GoodsRegRecList'])){
            if ($this->argvs['Status']=='F'){
                send_mail('13809703680@qq.com','gogo','申报回执失败', $this->argvs['Declaration']['Notes']);
                return "success";
            }
        }
        $RecLogic = Loader::model('RecGoodsUpdate','logic');
        if($RecLogic->updateGoodsReglistInfo($this->argvs))return json(['status'=>1]);
        return "success";
    }

    /*
     * 电子订单回执
     */
    protected function electronicOrderRec()
    {
        $recOrderLogic = Loader::model('RecOrderUpdate','logic');
        switch ($this->argvs['Declaration']['RespondBy']){
            case '01':
                
                if ($this->argvs['Declaration']['Status']=='F'){
                    send_mail('13809703680@qq.com','gogo','申报回执失败', $this->argvs['Declaration']['Notes'].",报文编号：".$this->argvs['Declaration']['OrgMessageID']);
                }
                //预留
            return "success";
            
            
            case '02':
                if($this->argvs['Declaration']['Status'] != 'S'){
                    $recOrderLogic->updateElecStatus($this->argvs['Declaration']['DetailNo'],4);
                    $email = $recOrderLogic->getUserEmail($this->argvs['Declaration']['DetailNo']);
                    $email = is_null($email)?'13809703680@qq.com':$email;
                    send_mail($email,'gogo','申报回执失败', $this->argvs['Declaration']['Notes'].",订单编号：".$this->argvs['Declaration']['DetailNo'].",报文编号：".$this->argvs['Declaration']['OrgMessageID']);
                    return "success";
                }
                $recOrderLogic->updateElecStatus($this->argvs['Declaration']['DetailNo'],2);
                Cache::set($this->argvs['Declaration']['OrgMessageID'],$this->argvs['Declaration']['DetailNo']);
                return "success";
                
            case '03':
                $detailNo = Cache::get($this->argvs['Declaration']['OrgMessageID']);
                
                if($this->argvs['Declaration']['Status'] != 'S'){
                    $recOrderLogic->updateElecStatus($detailNo,4);
                    $email = $recOrderLogic->getUserEmail($detailNo);
                    $email = is_null($email)?'13809703680@qq.com':$email;
                    send_mail($email,'gogo','申报回执失败', $this->argvs['Declaration']['Notes'].",报文编号：".$this->argvs['Declaration']['OrgMessageID'].",订单编号：".$detailNo);
                }else{
                    if (!empty($detailNo)){
                        $recOrderLogic->updateElecStatus($detailNo,2);
                    }
                }
                
                Cache::rm($this->argvs['Declaration']['OrgMessageID']);
                return "success";
        }
        return "success";
    }

    protected function isDocrecError()
    {
        if($this->argvs['Declaration']['Status'] != 'S'){
            send_mail('13809703680@qq.com','gogo','申报回执失败', $this->argvs['Declaration']['Notes'].",报文编号：".$this->argvs['Declaration']['OrgMessageID']);
        }
        return "success";
    }
    
    
}
