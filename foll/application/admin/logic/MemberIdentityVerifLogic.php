<?php

namespace app\admin\logic;


use PHPExcel;
use Excel5;
use PHPExcel_IOFactory;
use think\Db;

class MemberIdentityVerifLogic
{
    /**
     * 读取上传文件数据
     * @param $file
     * @return array
     * @throws \Exception
     */
    public function readExcelFile($file)
    {
        if (!$file) {
            throw new \Exception('请上传文件');
        }
        $path = ROOT_PATH . 'public' . DS . 'uploads';
        $saveResult = $file->validate(['ext' => 'xls,csv,xlsx'])->move($path);
        if (!$saveResult) {
            throw new \Exception($file->getError());
        }
        $fileName = $path . '/' . $saveResult->getSaveName();
        $inputFileType = PHPExcel_IOFactory::identify($fileName);
        $objRead = PHPExcel_IOFactory::createReader($inputFileType);
        $PHPRead = $objRead->load($fileName);
        $sheet = $PHPRead->getSheet(0);
        $allRow = $sheet->getHighestRow();
        $data = [];
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            array_push($data, [
                'batch_num' => $PHPRead->getActiveSheet()->getCell("E" . $currentRow)->getValue(),
                'waybill_no' => $PHPRead->getActiveSheet()->getCell("D" . $currentRow)->getValue(),
                'name' => $PHPRead->getActiveSheet()->getCell("A" . $currentRow)->getValue(),
                'identity_no' => $PHPRead->getActiveSheet()->getCell("B" . $currentRow)->getValue(),
                'mobile' => trim($PHPRead->getActiveSheet()->getCell("C" . $currentRow)->getValue()),
                'status' => 0,
                'create_time' => time()
            ]);
        }
        @unlink($fileName);
        return $data;
    }


    /**
     * 保存并发送手机通知消息
     * @param $data
     */
    public function saveAndSendMessage($data)
    {
        $this->saves($data);
        // $this->sendMobileMessage($data);
    }

    /**
     * $data
     * @param $data
     * @throws \Exception
     */
    protected function saves($data)
    {
        Db::startTrans();
        try {
            Db::name("customs_member_verifidentity")->insertAll($data);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $data
     */
    protected function sendMobileMessage($data)
    {
        foreach ($data as $value) {
            if (empty($value)) {
                continue;
            }
           newSendSms([
                'tel' => (string)$value['mobile'],
                'SingnName' => 'Gogo购购网',
                'parm' => ['name' => $value['name']],
                'TemplateCode' => 'SMS_169896822'
            ]);
        }
    }
}