<?php


namespace app\admin\controller;

use think\Db;
use think\Request;
use app\admin\model\CustomsHscodeTariffScheduleModel;
use app\admin\model\CustomsHsCodeRateModel;
use Excel5;
use PHPExcel_IOFactory;

/**
 * 海关商品编码进出口税则管理
 * Class CCCImportAndExportTariffSchedule
 */
class HscodeTariffSchedule extends \app\admin\controller\Auth {


    /**
     * list manage
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function index() {
        $title = '海关商品编码进出口税则管理';
        return view('CustomsSystem/hscode_tariff_schedule/index', compact('title'));
    }


    /**
     * fetch hscode list
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getList(Request $request) {

        $where = [];
        $limit = $request->get('limit');
        $page  = $request->get('page');
        $page  = ($page - 1) * $limit;
        if ($request->get('code') != "") {
            $code  = $request->get('code');
            $where = "two='" . $code . "' or four='" . $code . "' or five='" . $code . "' or six='" . $code . "' or seven='" . $code . "' or eight='" . $code . "'";
        }
        $total = CustomsHscodeTariffScheduleModel::where($where)->count();
        $res   = CustomsHscodeTariffScheduleModel::where($where)->limit($page, $limit)->select();
        return json([
            "code"  => 0, //解析接口状态
            "msg"   => "完成", //解析提示文本
            "count" => $total, //解析数据长度
            "data"  => $res
        ]); //解析数据列表]);
    }


    public function getCodeRate(Request $request) {
        if ($request->get('code') == "") {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        $result = CustomsHsCodeRateModel::where('code', $request->get('code'))->field('rate')->find();
        return json(['code' => 0, 'msg' => '', 'result' => $result['rate']]);
    }

    /**
     * 更新费率
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateRate(Request $request) {
        if ($request->get('code') == "" && $request->get('rate') == "") {
            return json(['code' => 1, 'msg' => '参数错误']);
        }
        $result = CustomsHsCodeRateModel::where('code', $request->get('code'))->find();
        if ($result) {
            CustomsHsCodeRateModel::where('code', $request->get('code'))->update(['rate' => $request->get('rate')]);
        } else {
            CustomsHsCodeRateModel::create([
                'code' => $request->get('code'),
                'rate' => $request->get('rate'),
            ]);
        }
        return json(['code' => 0, 'msg' => '保存完成']);

    }

    /**
     * 上传海关商品编码表
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|\think\response\View
     */
    public function importExcelFile(Request $request) {
        // return view('CustomsSystem/hscode_tariff_schedule/import_excel_file', ['title' => '上传']);
        // $this->saveUploadExcelFile($request);
        // $result = Db::name("customs_hscode_tariffschedule")->where('hscode','0101210010')->select();
        // print_r($result);
    }
    // 申报要素字段更新
    public function saveUploadExcelFile(Request $request) {
//        return json(['code' => 1, 'msg' => '上传失败']);
        $file = $request->file('file');
        if (!$file) {
            return json(['code' => 1, 'msg' => '上传失败']);
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
        if (!$saveResult) {
            return json(['code' => -1, 'message' => $file->getError()]);
        }
        $fileName = $path.'/'.$saveResult->getSaveName();
        $data = [];
        $datas = [];
        $inputFileType = PHPExcel_IOFactory::identify($fileName);
        $objRead = PHPExcel_IOFactory::createReader($inputFileType);
        $objRead->setReadDataOnly(true);
        $PHPRead = $objRead->load($fileName);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        $model = new CustomsHscodeTariffScheduleModel();
        $newii=0;
        $updateii=0;
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $hscode = $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue();
            $ciq_name = $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue();
            $export_tax_rate = $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue();
            $declaration_elements = $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue();
            $anti_declaration = $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue();
            $data["hscode"] = $hscode;
            $data["ciq_name"] = $ciq_name;
            $data["declaration_elements"] = $declaration_elements;
            $data["anti_declaration"] = $anti_declaration;
            $data["export_tax_rate"] = $export_tax_rate;
            $result = $model->where('hscode',$hscode)->find();
            if (!empty($result)) {
                $data["id"]=$result["id"];
                array_push($datas,$data);
                $updateii++;
            }else{
                //为空
                 $data["hscode"]=$hscode;
                 array_push($datas,$data);
                 $newii++;
             }
        }
        $model->saveAll($datas);
        @unlink($fileName);
        return json(['code' =>0, 'msg' => '完成[更新'.$updateii.'条,新加'.$newii.'条]']);
    }
    // cicq字段更新
    public function saveUploadExcelFile2(Request $request) {
        $file = $request->file('file');
        if (!$file) {
            return json(['code' => 1, 'msg' => '上传失败']);
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
        if (!$saveResult) {
            return json(['code' => -1, 'message' => $file->getError()]);
        }
        $fileName = $path.'/'.$saveResult->getSaveName();
        $data = [];
        $datas = [];
        $inputFileType = PHPExcel_IOFactory::identify($fileName);
        $objRead = PHPExcel_IOFactory::createReader($inputFileType);
        $objRead->setReadDataOnly(true);
        $PHPRead = $objRead->load($fileName);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        $model = new CustomsHscodeTariffScheduleModel();
        $newii=0;
        $updateii=0;
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $hscode = $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue();
            $ciq_code = $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue();
            $ciq_name = $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue();
            $data["hscode"] = $hscode;
            $data["ciq_code"] = $ciq_code;
            $data["ciq_name"] = $ciq_name;
            $result = $model->where('ciq_code',$ciq_code)->find();
            if (!empty($result)) {
                $data["id"]=$result["id"];
                array_push($datas,$data);
                $updateii++;
            }
            // $data["hscode"]=$hscode;
            //     array_push($datas,$data);
            //     $newii++;
            // }
        }
        $model->saveAll($datas);
        @unlink($fileName);
        return json(['code' =>0, 'msg' => '完成[更新'.$updateii.'条,新加'.$newii.'条]']);
    }
    // hs表更新
    public function saveUploadExcelFile3(Request $request) {
        $file = $request->file('file');
        if (!$file) {
            return json(['code' => 1, 'msg' => '上传失败']);
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
        if (!$saveResult) {
            return json(['code' => -1, 'message' => $file->getError()]);
        }
        $fileName = $path.'/'.$saveResult->getSaveName();
        $data = [];
        $datas = [];
        $inputFileType = PHPExcel_IOFactory::identify($fileName);
        $objRead = PHPExcel_IOFactory::createReader($inputFileType);
        $objRead->setReadDataOnly(true);
        $PHPRead = $objRead->load($fileName);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        $model = new CustomsHscodeTariffScheduleModel();
        $newii=0;
        $updateii=0;
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $hscode = $PHPRead->getActiveSheet()->getCell("A".$currentRow)->getValue();
            $mfn_tax_rate = (float)($PHPRead->getActiveSheet()->getCell("I".$currentRow)->getValue())/100;
            $ordinary_tax_rate = (float)($PHPRead->getActiveSheet()->getCell("J".$currentRow)->getValue())/100;
            $vat_rate = (float)($PHPRead->getActiveSheet()->getCell("K".$currentRow)->getValue())/100;
            $exit_tax_rate = (float)($PHPRead->getActiveSheet()->getCell("N".$currentRow)->getValue())/100;
            $provisional_tariff_rate = (float)($PHPRead->getActiveSheet()->getCell("O".$currentRow)->getValue())/100;
                // 第二位
            $data["two"] =substr($hscode,0,2);
                // 第四位
            $data["four"] = substr($hscode,0,4);
                // 第五位
            $data["five"] = substr($hscode,0,5);
                // 第六位
            $data["six"] = substr($hscode,0,6);
                // 第七位
            $data["seven"] = substr($hscode,0,7);
                // 第八位
            $data["eight"] = substr($hscode,0,8);
                //商品名字
            $data["ciq_name"] = $PHPRead->getActiveSheet()->getCell("B".$currentRow)->getValue();
                // 法定第一单位名字
            $data["legal_first_unit"] = $PHPRead->getActiveSheet()->getCell("D".$currentRow)->getValue();
                // 法定第一单位数据
            $data["legal_first_unit_value"] = $PHPRead->getActiveSheet()->getCell("C".$currentRow)->getValue();
                // 法定第二单位名字
            $data["legal_second_unit"] = $PHPRead->getActiveSheet()->getCell("F".$currentRow)->getValue();
                // 法定第二单位数据
            $data["legal_second_unit_value"] = $PHPRead->getActiveSheet()->getCell("E".$currentRow)->getValue();
                // 监管条件
            $data["supervision_factor"] = $PHPRead->getActiveSheet()->getCell("G".$currentRow)->getValue();
                // 检疫条件
            $data["quarantine_conditions"] = $PHPRead->getActiveSheet()->getCell("H".$currentRow)->getValue();
                // 最惠国进口税率
            $data["mfn_tax_rate"] = $mfn_tax_rate;
                // 普通进口税率
            $data["ordinary_tax_rate"] = $ordinary_tax_rate;
                // 增值税率
            $data["vat_rate"] = $vat_rate;
                // 出口关税率
            $data["exit_tax_rate"] = $exit_tax_rate;
                // 暂定出口关税率
            $data["provisional_tariff_rate"] = $provisional_tariff_rate;
            $result = $model->where('hscode',$hscode)->find();
            if (!empty($result)) {
                $data["id"]=$result["id"];
                $updateii++;
            }
            array_push($datas,$data);
            // $data["hscode"]=$hscode;
            //     array_push($datas,$data);
            //     $newii++;
            // }
        }
        $model->saveAll($datas);
        @unlink($fileName);
        return json(['code' =>0, 'msg' => '完成[更新'.$updateii.'条,新加'.$newii.'条]']);
    }
}