<?php

namespace app\declares\logic;

use think\Model;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;

class ErrorOrderExp extends Model
{
    
    /**
     * 导出错误提交支付订单
     * @return string
     */
    public function expErrorOrder($num,$uid)
    {
        $data = $this->getErrData($num,$uid);
        $email = $this->getEmail($uid);
        if (!$data){
            return '没有数据';
        }
        
        $PHPExcel  = new PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('Sheet1'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1','批次');
        $PHPSheet->setCellValue('B1','运单号');
        $PHPSheet->setCellValue('C1','用户名称');
        $PHPSheet->setCellValue('D1','错误信息');
        $n=2;
        foreach ($data as $val){
            $PHPSheet->setCellValue('A'.$n,$val['batch_num'])
                ->setCellValue('B'.$n,$val['WaybillNo'])
                ->setCellValue('C'.$n,$val['user_name'])
                ->setCellValue('D'.$n,$val['err_msg']);
            $n+=1;
        }
        
        $PHPWrite = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');
        $fileName = md5(date('YmdHis', time()) . mt_rand(1111, 9999));
        $path       = './uploads/excel/' . $fileName . '.xlsx';
        $PHPWrite->save($path);
        if (is_null($email)){
            return '请填写邮箱';
        }
        $email = json_decode($email['subject'],true);
        if (empty($email['conmpanyemail'])){
            return '请填写邮箱';
        }
        $this->sendMail($path, $email['conmpanyemail']);
        return '请查收邮件';
    }
    
    
    protected function getErrData($num,$uid)
    {
        return Db::name('foll_err_order_elec')
            ->where(['batch_num'=>$num,'uid'=>$uid])
            ->field(['batch_num','WaybillNo','user_name','err_msg'])
            ->select();
    }
    
    protected function getEmail($uid){
        return Db::name('foll_cross_border')->where('uid',$uid)->field('subject')->find();
    }
    
    protected function sendMail ( $path, $email )
    {
        $name    = '系统';
        $subject = '错误订单';
        $content = '请查收';
        $status  = send_mail($email, $name, $subject, $content, ['0' => $path]);
        if ( $status ) {
            unlink($path);
        }
    }
}