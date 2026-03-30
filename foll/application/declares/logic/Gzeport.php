<?php

namespace app\declares\logic;

use Couchbase\Exception;
use think\Model;
use PHPExcel;
use PHPExcel_IOFactory;
use app\declares\model\GoodsRegList;
use app\declares\model\GoodsRegHead;
use think\Session;
use think\Db;
//define("JAVA_DEBUG", true); //调试设置
//define("JAVA_HOSTS", "127.0.0.1:8881");
//define("JAVA_LOG_LEVEL", 2);

class Gzeport extends Model
{
    const PATHX = './uploads/encryxml/';
    protected $dsig;
    protected $objPHPExcel = null;
    protected $time = null;
    protected $inputData = null;
    protected $id;
    protected $XMLObject;


    /*
  * 获取excel文件内容
  */
    public function getExcelData ( $dirPath, $fileName, $inputData )
    {
        $fileName = explode('/', $fileName);
        $fileNames = $dirPath . $fileName[0] . '/' . $fileName[1];
        $this->inputData = $inputData;
        $this->time = date('YmdHis', time());
        $this->id = $this->inputData['MessageType'] . '_' . $this->inputData['EDI'] . '_' . date("YmdHis", time()) . mt_rand(11111, 999999);
        try {
            $reader = PHPExcel_IOFactory::createReader("Excel2007");
            if ( !$reader->canRead($fileNames) ) {
                $reader = PHPExcel_IOFactory::createReader("Excel5");
            }
            $reader->setReadDataOnly(true);
            $this->objPHPExcel = $reader->load($fileNames);
            //getAllSheetName
            $sheetnames = $this->objPHPExcel->getSheetNames();
            //switch sheet one
            $this->objPHPExcel->setActiveSheetIndexByName($sheetnames[0]);
            //read Header Data from xls
            $filingHead = $this->getHeadFromXls();
            //switch sheet two
            $objSheet = $this->objPHPExcel->setActiveSheetIndexByName($sheetnames[1]);
            //read body Data from xls
            $filingBody = $this->getBodyFromXls($objSheet->getHighestRow(), $objSheet->getHighestColumn());
            unset($this->objPHPExcel, $objSheet, $reader);
            //预留

            $ids = null;
            foreach ($filingBody as $key => $value){
                $result = GoodsRegList::get(function ($query) use ($value){
                   $query->where([
                       'Brand'      => $value['Brand'],
                       'Manufactory'=> $value['Manufactory'],
                       'GoodsStyle' => $value['GoodsStyle'],
                       'GrossWt'    => $value['GrossWt'],
                       'HSCode'     => $value['HSCode'],
                       'OriginCountry'=> $value['OriginCountry']
                   ]);
                });
               if(!is_null($result)){
                   $ids .= $value['Seq'].',';
                   unset($filingBody[$key]);
               }
            }
            @unlink($fileNames);
            @rmdir($dirPath.$fileNames);
            if(!empty($filingBody)){
                $filingHead['message_header'] = json_encode($this->returnHeadInfo());
                $this->insertGoodsDataTable($filingHead, $filingBody);
                send_mail('13809703680@qq.com','商品备案','商品备案','有新的备案需要审核');
                return ['result'=>true,'description'=>'上传成功等待审核，重复备案商品序号：'.$ids];
            }else{
                return ['result'=>false,'description'=>'全部重复备案商品序号：'.$ids];
            }
        } catch (\Exception $e) {
           return ['result'=>false,'description'=>$e->getMessage()];
        }
    }

    /*
     * 获取xls备案头数据
     */
    protected function getHeadFromXls ()
    {
        $data = array();
        $data['DeclEntNo'] = $this->inputData['DeclEntNo'];
        $data['DeclEntName'] = $this->inputData['DeclEntName'];
        $data['EBEntNo'] = $this->objPHPExcel->getActiveSheet()->getCell('B2')->getValue();
        $data['EBEntName'] = $this->objPHPExcel->getActiveSheet()->getCell('D2')->getValue();
        $data['OpType'] = 'A';
        $data['CustomsCode'] = (int)$this->objPHPExcel->getActiveSheet()->getCell('F2')->getValue();
        $data['CIQOrgCode'] = (int)$this->objPHPExcel->getActiveSheet()->getCell('B3')->getValue();
        $data['EBPEntNo'] = $this->objPHPExcel->getActiveSheet()->getCell('D3')->getValue();
        $data['EBPEntName'] = $this->objPHPExcel->getActiveSheet()->getCell('F3')->getValue();
        $data['CurrCode'] = (int)$this->objPHPExcel->getActiveSheet()->getCell('B4')->getValue();
        $data['BusinessType'] = (int)$this->objPHPExcel->getActiveSheet()->getCell('D4')->getValue();
        $data['InputDate'] = $this->time;
        $data['DeclTime'] = $this->time;
        $data['IeFlag'] = $this->objPHPExcel->getActiveSheet()->getCell('F4')->getValue();
        $data['Notes'] = $this->objPHPExcel->getActiveSheet()->getCell('B5')->getValue();
        return $data;
    }

    //return body Data from xls
    protected function getBodyFromXls ( $RowNum, $ColNum )
    {
        $data = array();
        $argc = ['A' => 'Seq', 'B' => 'EntGoodsNo', 'C' => 'ShelfGName', 'D' => 'NcadCode', 'E' => 'HSCode', 'F' => 'GoodsName', 'G' => 'GoodsStyle', 'H' => 'Brand', 'I' => 'GUnit', 'J' => 'StdUnit', 'K' => 'SecUnit', 'L' => 'RegPrice', 'M' => 'GiftFlag', 'N' => 'OriginCountry', 'O' => 'QualityCertify', 'P' => 'Manufactory', 'Q' => 'NetWt', 'R' => 'GrossWt', 'S' => 'EmsNo', 'T' => 'ItemNo', 'U' => 'BarCode', 'V' => 'Quality', 'W' => 'Notes'];
        for ($i = 2; $i <= $RowNum; $i++) {
            $tmp = [];
            for ($j = 'A'; $j <= $ColNum; $j++) {
                $tmp[$argc[$j]] = $this->objPHPExcel->getActiveSheet()->getCell($j . $i)->getValue();
            }
            array_push($data, $tmp);
        }
        return $data;
    }


    /*
     * return head data
     */
    protected function returnHeadInfo ( )
    {
        return [
            'MessageID' => $this->id,
            'MessageType' => $this->inputData['MessageType'],
            'Sender' => $this->inputData['EDI'],
            'Receiver' => $this->inputData['DeclEntNo'],
            'SendTime' => $this->time,
            'FunctionCode' => count($this->inputData['FunctionCode']) == 2 ? 'BOTH' : $this->inputData['FunctionCode'][0],
            'SignerInfo' => '待定',
            'Version' => '3.0',
            'key'     => $this->inputData['key']
        ];
    }
    /*
    * 存入数据库
    */
    public function insertGoodsDataTable ( $headData = null, $data = null )
    {
        Db::startTrans();
        try {
            $GoodsRegHead = new GoodsRegHead();
            $headData['uid'] = Session::get('admin')['id'];
            $GoodsRegHead->data($headData);
            $GoodsRegHead->save();
            $h_id = $GoodsRegHead->id;
            foreach ($data as $key => &$val) {
                $val['head_id'] = $h_id;
            }
            $GoodsRegList = new GoodsRegList();
            $GoodsRegList->saveAll($data);
            Db::commit();
        } catch (\Exception $exception) {
            Db::rollback();
            throw new \Exception($exception->getMessage());
        }

    }

    /*
    *  generate xml format document
    *
   public function getFormatXml ( $data = array() )
   {
       $this->id = $this->inputData['MessageType'] . '_' . $this->inputData['EDI'] . '_' . date("YmdHis", time()) . mt_rand(11111, 999999);
       $this->XMLObject = new \DOMDocument('1.0', 'UTF-8');
       $this->XMLObject->formatOutput = true;
       $InternationalTrade = $this->XMLObject->createElement('InternationalTrade');
       //        $InternationalTrade->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
       $this->XMLObject->appendChild($InternationalTrade);
       //head
       $Head = $this->XMLObject->createElement('Head');
       $InternationalTrade->appendChild($Head);
       $cons = $this->returnHeadInfo($this->id);
       foreach ($cons as $key => $val) {
           $keys = $this->XMLObject->createElement($key);
           $keys->nodeValue = $val;
           $Head->appendChild($keys);
       }
       $Declaration = $this->XMLObject->createElement('Declaration');
       $InternationalTrade->appendChild($Declaration);
       $GoodsRegHead = $this->XMLObject->createElement('GoodsRegHead');
       foreach ($data['head'] as $key => $val) {
           $keys = $this->XMLObject->createElement($key);
           $keys->nodeValue = $val;
           $GoodsRegHead->appendChild($keys);
       }
       $Declaration->appendChild($GoodsRegHead);
       $GoodsRegList = $this->XMLObject->createElement('GoodsRegList');
       $Declaration->appendChild($GoodsRegList);
       foreach ($data['body'] as $value) {
           $GoodsContent = $this->XMLObject->createElement('GoodsContent');
           $GoodsRegList->appendChild($GoodsContent);
           foreach ($value as $kk => $vv) {
               $keys = $this->XMLObject->createElement($kk);
               $keys->nodeValue = $vv;
               $GoodsContent->appendChild($keys);
           }
       }
       return $this->XMLObject->saveXML();
   }
*/

    /*
     * 发送报文
     *
    protected function generateXMLDigitalSignature ( $xmlData = null )
    {
        include('../javaBridge/Java.inc');
        java_require('../javaBridge/XmlDigitalSignatureGenerator.jar');
        try {
            $GzeportTransfer_origin = file_get_contents('./uploads/encryxml/GzeportTransfer_origin.xml');
            $gzeportTransfer_origin = simplexml_load_string($GzeportTransfer_origin);
            unset($GzeportTransfer_origin);
        } catch (\Exception $exception) {
            return false;
        }
        $gzeportTransfer_origin->Data = $xmlData;
        $gzeportTransfer_origin->Head->MessageID = $this->id;
        $gzeportTransfer_origin->Head->MessageType = $this->inputData['MessageType'];
        $gzeportTransfer_origin->Head->Sender = $this->inputData['EDI'];
        $gzeportTransfer_origin->Head->Receivers->Receiver[0] = $this->inputData['DeclEntNo'];
        $gzeportTransfer_origin->Head->Receivers->Receiver[1] = $this->inputData['DeclEntNo'];
        $gzeportTransfer_origin->Head->SendTime = $this->time;
        $gzeportTransfer_origin->Head->Version = '3.0';
        $originFIlePath = getcwd() . '/uploads/encryxml/' . $this->id . '_origin.xml';
        $signedXmlFilePath = getcwd() . '/uploads/encryxml/' . $this->id . '_sign.xml';
        $privateKeyFilePath = getcwd() . '/../key/privatekey.key';
        $publicKeyFilePath = getcwd() . '/../key/publickey.key';
        $gzeportTransfer_origin->saveXML($originFIlePath);
        try {
            $xmlSig = new \java("com.ddlab.rnd.xml.digsig.XmlDigitalSignatureGenerator");
            $xmlSig->generateXMLDigitalSignature($originFIlePath, $signedXmlFilePath, $privateKeyFilePath, $publicKeyFilePath);
            unset($xmlSig, $xmlData);
            return ['originFIlePath' => $originFIlePath, 'signedXmlFilePath' => $signedXmlFilePath];
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

    }
    */
    /*
     * request data api
     *
    protected function RequestReport ( $data )
    {
        $url = 'https://open.singlewindow.gz.cn/swcbes/client/declare/sendMessage.action?clientid=' . $this->inputData['DeclEntNo'] . '&key=' . $this->inputData['key'] . '&messageType=KJ881101';
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
*/


}