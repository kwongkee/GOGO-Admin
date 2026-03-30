<?php


namespace app\admin\controller;

use think\Request;
use PHPExcel;
use PHPExcel_IOFactory;

/**
 * Class WaybillNumebrExtract
 * @package app\admin\controller
 */
class WaybillNumebrExtract extends Auth
{
    public function Ems(Request $request)
    {
        if ($request->isGET()) {
            return view('WaybillNumebrExtract/ems');
        }
        if (empty($request->post('num'))) {
            return json(['code' => 1, 'msg' => '请填写数量']);
        }
        $reqStr = '<?xml version="1.0" encoding="UTF-8"?><XMLInfo><sysAccount>90000010408227</sysAccount>';
        $reqStr .= '<passWord>123456</passWord>';
        $reqStr .= '<appKey>T9afBB3f558A38cAE4</appKey>';
        $reqStr .= '<businessType>9</businessType>';
        $reqStr .= '<billNoAmount>' . trim($request->post('num')) . '</billNoAmount>';
        $reqStr .= '</XMLInfo>';
        $result=file_get_contents("http://os.ems.com.cn:8081/zkweb/bigaccount/getBigAccountDataAction.do?method=getBillNumBySys&xml=".urlencode(base64_encode($reqStr)));
        $result=base64_decode($result);
        $objectXml = simplexml_load_string($result);//将文件转换成 对象
        $xmlJson = json_encode($objectXml);//将对象转换个JSON
        $xmlArray = json_decode($xmlJson, true);//将json转换成数组
        if ($xmlArray['result'] == 0) {
            return json(['code' => 1, 'msg' => $xmlArray['errorDesc']]);
        }
        $PHPExcel = new PHPExcel();
        $PHPExcel->setActiveSheetIndex(0);
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle('sheet1');
        $PHPSheet->setCellValue('A1', '运单号');
        $n = 2;
        foreach ($xmlArray['assignIds']['assignId'] as $val) {
            $PHPSheet->setCellValue('A' . $n, "'" . $val['billno']);
            $n++;
        }
        $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $file = 'ems运单号' . date('YmdHis', time()) . '.xlsx';
        $path = '/uploads/' . date('Ymd');
        $basePath = getcwd() . $path;
        if (!is_dir($basePath)) {
            @mkdir($basePath, 0775, true);
        }
        $phpWrite->save($basePath . '/' . $file);
        return json(['code' => 0, 'msg' => '', 'data' => $path . '/' . $file]);
    }
}