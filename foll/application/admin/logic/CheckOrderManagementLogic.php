<?php

namespace app\admin\logic;

use think\Model;
use think\Db;
use think\MongoDB;
use think\Config;
use PHPExcel;
use PHPExcel_IOFactory;

class CheckOrderManagementLogic extends Model
{
    /**
     * 提单列表
     * @return mixed
     */
    public function getBillList()
    {
        $total = Db::name('decl_bol')->count();
        $billRes = Db::name('decl_bol')
            ->alias('a1')
            ->join('decl_user a2','a1.user_id=a2.id','left')
            ->field(['a1.bill_num','a2.user_name','a2.company_name'])
            ->order('a1.id','desc')
            ->paginate(10,$total,['query' => ['s' => 'order/CheckOrderManagement'], 'var_page' => 'page']);
        return $billRes;
    }

    /**
     * 获取订单信息
     * @param $billNum
     * @return mixed
     */
    public function getCheckOrderInfoFromBillNum($billNum)
    {

        $mgo = MongoDB::init(Config::load(APP_PATH . 'config/config.php')['order_mongo_dsn']);
        $query = ['bill_num' => $billNum]; // your typical MongoDB query
        $querys = new \MongoDB\Driver\Query($query,[] );
        $cursor = $mgo->executeQuery("order.order", $querys)->toArray(); // retrieve the results
        $data = [];
        foreach ($cursor as $doc){
            $data[] = $doc;
        }
        return $data;
    }



    public function exportRawPayInfoLogic($billNUm)
    {
        $data = [];
        $PHPExcel = new PHPExcel();
        $PHPExcel->setActiveSheetIndex(0);
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle('sheet1');
        $n = 2;
        array_push($data,[
            '系统唯一序号','原始请求','原始响应','电商平台代码','支付企业代码',
            '交易流水号','交易金额','币制','验核机构','支付类型','交易成功时间','订单编号','商品信息','收款账号','收款企业代码','收款企业名称'
        ]);
        $PHPSheet->setCellValue('A1', '系统唯一序号')
            ->setCellValue('B1', '原始请求')
            ->setCellValue('C1' ,'原始响应')
            ->setCellValue('D1', '电商平台代码')
            ->setCellValue('E1',  '支付企业代码')
            ->setCellValue('F1', '交易流水号')
            ->setCellValue('G1',  '交易金额')
            ->setCellValue('H1', '币制')
            ->setCellValue('I1', '验核机构')
            ->setCellValue('J1', '支付类型')
            ->setCellValue('K1','交易成功时间')
            ->setCellValue('L1',  '订单编号')
            ->setCellValue('M1',  '商品信息')
            ->setCellValue('N1','收款账号')
            ->setCellValue('O1','收款企业代码')
            ->setCellValue('P1', '收款企业名称');
        $batchList = Db::name('customs_batch')->where(['bill_num'=>$billNUm,'status'=>4])->field('batch_num')->select();
        if(empty($batchList)){
            throw new \Exception('查询不到数据');
        }

        foreach ($batchList as $value){
            $orderInfo = Db::name('customs_elec_order_detail')
                ->alias('a1')
                ->join('customs_payment_cusinquirs a2','a1.EntOrderNo=a2.orderNo','left')
                ->where('a1.batch_num',$value['batch_num'])
                ->field(['a2.*','a1.goodsNo'])
                ->select();
            if (empty($orderInfo)){
                continue;
            }
            foreach ($orderInfo as $value){
                $PHPSheet->setCellValue('A' . $n, $this->Tokens())
                    ->setCellValue('B' . $n, $value['initalRequest'])
                    ->setCellValue('C' . $n,$value['initalResponse'])
                    ->setCellValue('D' . $n, $value['ebpCode'])
                    ->setCellValue('E' . $n,  $value['payCode'])
                    ->setCellValue('F' . $n, $value['payTransactionId'])
                    ->setCellValue('G' . $n,  $value['totalAmount'])
                    ->setCellValue('H' . $n, $value['currency'])
                    ->setCellValue('I' . $n, $value['verDept'])
                    ->setCellValue('J' . $n,  $value['payType'])
                    ->setCellValue('K' . $n,$value['tradingTime'])
                    ->setCellValue('L' . $n,  $value['orderNo'])
                    ->setCellValue('M' . $n,  $this->getOrderGoodsItemInfo($value['goodsNo']))
                    ->setCellValue('N' . $n,  $value['recpAccount'])
                    ->setCellValue('O' . $n, $value['recpCode'])
                    ->setCellValue('P' . $n, $value['recpName']);
                    $n += 1;
            }

        }
        $phpWrite = PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $file     = date('Y-m-dHis', time()) . '.xlsx';
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $file . '"');
        header("Content-Transfer-Encoding:binary");
        $phpWrite->save('php://output');
    }


    /**
     * 获取商品名称以及商城链接
     * @param $goodres string
     * @return false|string
     */
    protected function getOrderGoodsItemInfo($goodres)
    {
        $good = [];
        $goodId = '';
        $goodres = json_decode($goodres,true);
        foreach ($goodres as $val){
            $goodId.=$val['goodNo'].',';
        }
        $goodId = trim($goodId,',');
        $result = Db::name('foll_goodsreglist')->where('EntGoodsNo','in',$goodId)->field('GoodsName')->group('EntGoodsNo')->select();
        foreach ($result as $value){
            array_push($good,['gname'=>$value['GoodsName'],'itemLink'=>'http://www.gogo198.com/']);
        }
        return json_encode($good);
    }


    // 生成唯一Token值
    private function Tokens()
    {
        $arr     = array_merge(range('A', 'Z'), range('a', 'z'), range('A', 'Z'));
        $str     = '';
        $arr_len = count($arr);
        for ($i = 0; $i < 36; $i++) {
            $rand = mt_rand(0, $arr_len - 1);
            $str  .= $arr[$rand];
        }
        return strtoupper($str);
    }

}
