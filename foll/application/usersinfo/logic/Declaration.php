<?php

namespace app\declares\logic;
use think\Model;
use think\Db;
use LSS\Array2XML;
define("JAVA_DEBUG", false); //调试设置
define("JAVA_HOSTS", "127.0.0.1:8881");
define("JAVA_LOG_LEVEL", 2);
class Declaration extends Model
{
    /*
     * 获取所有需要商品备案数据
     */
    public function getAllData($table,$where)
    {
        return Db::name($table)->where($where)->select();
    }

    /*
     * 格式化备案商品数组
     */
    public function formatGoodsArray($data)
    {
        $newArray = ['GoodsContent'=>[]];
        foreach ($data as $val){
            $tmp = [];
           $tmp = [
               'Seq'            =>$val['Seq'],
               'EntGoodsNo'     =>$val['EntGoodsNo'],
               'ShelfGName'     =>$val['ShelfGName'],
               'NcadCode'       =>$val['NcadCode'],
               'HSCode'         =>$val['HSCode'],
               'GoodsName'      =>$val['GoodsName'],
               'GoodsStyle'     =>$val['GoodsStyle'],
               'Brand'          =>$val['Brand'],
               'GUnit'          =>$val['GUnit'],
               'StdUnit'        =>$val['StdUnit'],
               'SecUnit'        =>$val['SecUnit'],
               'RegPrice'       =>$val['RegPrice'],
               'GiftFlag'       =>$val['GiftFlag'],
               'OriginCountry'  =>$val['OriginCountry'],
               'QualityCertify' =>$val['QualityCertify'],
               'Manufactory'    =>$val['Manufactory'],
               'NetWt'          =>$val['NetWt'],
               'GrossWt'        =>$val['GrossWt'],
               'EmsNo'          =>$val['EmsNo'],
               'ItemNo'         =>$val['ItemNo'],
               'BarCode'        =>$val['BarCode'],
               'Quality'        =>$val['Quality'],
               'Notes'          =>$val['Notes'],
           ];
           array_push($newArray['GoodsContent'],$tmp);
        }
        unset($data);
        return $newArray;
    }

    /*
     * 生成xml
     */
    public function generateXml($data)
    {
        $xml = Array2XML::createXML('InternationalTrade', $data);
        return $xml->saveXml();
    }

    /*
     * 商品备案申报
     */

    public function goodsReg($hid=nill)
    {
        $head_data = null;
        if(!($head_data = $this->checkStatus('foll_goodsreghead',['id'=>$hid,'g_check'=>1]))){
            return ['result'=>false,'msg'=>'请待定审核'];
        }
        $messageHead = json_decode($head_data['message_header'],true);
        $key = $messageHead['key'];
        unset($messageHead['key']);

        $body_data = $this->getAllData('foll_goodsreglist',['head_id'=>$hid]);
        $body_data = $this->formatGoodsArray($body_data);

        unset($head_data['id'],$head_data['uid'],$head_data['declare_status'],$head_data['g_check'],$head_data['message_header']);
        $head_data['InputDate'] = date("YmdHis",strtotime($head_data['InputDate']));
        $head_data['DeclTime'] = date("YmdHis",strtotime($head_data['DeclTime']));

        $conten = array();

        $conten['Head'] = $messageHead;
        $conten['Declaration']['GoodsRegHead'] = $head_data;
        $conten['Declaration']['GoodsRegList'] = $body_data;
        $xmlBody = $this->generateXml($conten);
        $xmlBody = base64_encode($xmlBody);
        $origin = file_get_contents('./uploads/encryxml/GzeportTransfer_origin.xml');
        $originFIlePath = getcwd() . '/uploads/encryxml/' . $messageHead['MessageID'] . '_origin.xml';
        $origin = simplexml_load_string($origin);
        $origin->Data = $xmlBody;
        $origin->Head->MessageID = $messageHead['MessageID'];
        $origin->Head->MessageType = $messageHead['MessageType'];
        $origin->Head->Sender = $messageHead['Sender'];
        $origin->Head->Receivers->Receiver[0] = $messageHead['Receiver'];
        $origin->Head->Receivers->Receiver[1] = $messageHead['Receiver'];
        $origin->Head->SendTime = date("YmdHis",time());
        $origin->Head->Version = '3.0';
        $origin->saveXML($originFIlePath);
        $sigPath = $this->rsaSigture($originFIlePath,$messageHead['MessageID']);
        $repResult = $this->requestReport(file_get_contents($sigPath),$messageHead['Receiver'],$key);
        $repResult = json_decode($repResult);
        @unlink($originFIlePath);
        if(!$repResult->result){
            $this->updateDescStatus($hid,1);
            return ['result'=>false,'msg'=>$repResult->description];
        }
        $this->updateDescStatus($hid,0);
        return ['result'=>true,'msg'=>$repResult->description];
    }

    /*
     * 调用java加签
     */
    public function rsaSigture($orginPath,$mid)
    {
        include('../javaBridge/Java.inc');
        java_require('../javaBridge/XmlDigitalSignatureGenerator.jar');
        $pri = getcwd() . '/../key/privatekey.key';
        $pub = getcwd() . '/../key/publickey.key';
        $signedXmlFilePath = getcwd() . '/uploads/encryxml/' . $mid . '_sign.xml';
        try {
            $xmlSig = new \java("com.ddlab.rnd.xml.digsig.XmlDigitalSignatureGenerator");
            $xmlSig->generateXMLDigitalSignature($orginPath, $signedXmlFilePath, $pri, $pub);
            unset($xmlSig, $xmlData);
            return $signedXmlFilePath;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /*
     * 发送报文
     */
    protected function requestReport ( $data,$cli,$key )
    {
        $url = 'https://open.singlewindow.gz.cn/swcbes/client/declare/sendMessage.action?clientid=' .$cli . '&key=' . $key . '&messageType=KJ881101';
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_SSL_VERIFYPEER=>false,
            CURLOPT_SSL_VERIFYHOST=>false,
            CURLOPT_HTTPHEADER => array(
                "Content-Type:text/xml;charset=UTF-8",
                "Connection:Keep-Alive"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ( $err ) {
            throw new \Exception('curl error' . $err);
        } else {
            return $response;
        }
    }

    public function updateDescStatus($id,$status)
    {
        Db::name('foll_goodsreghead')->where('id',$id)->update(['declare_status'=>$status]);
    }

    /*
     * 判断是否审核
    */
    protected function checkStatus($table,$where=array())
    {
        return Db::name($table)->where($where)->find();
    }

}