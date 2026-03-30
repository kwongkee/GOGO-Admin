<?php

namespace app\job\command;

use think\Controller;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;
use Util\data\Helpay;
use think\Log;

class OrderElecJob extends Command
{
    
    protected function configure ()
    {
        $this->setName('OrderElecJob')->setDescription('订单申报');
    }
    
    protected function execute ( Input $input, Output $output )
    {
        $output->writeln('Date Crontab job start...');
        //        @file_put_contents(getcwd().'/runtime/log/test.txt',date('H:i:s',time())."\n",FILE_APPEND);
        $this->handleOrderData();
        $output->writeln('Date Crontab job end...');
    }
    
    public function handleOrderData ()
    {
        $order = $this->getOrder();
        if ( empty($order) ) {
            exit();
        }
        $this->updateQueueStatus($order['id'], 2);
        $order['h_conten']                              = json_decode($order['h_conten'], true);
        $order['h_conten']['MessageID']                 = $order['h_conten']['MessageType'] . '_' . $order['h_conten']['Sender'] . '_' . date("YmdHis", time()) . mt_rand(11111, 999999);
        $order['ord_body']                              = json_decode($order['ord_body'], true);
        $order['ord_body']['OrderHead']['request_head'] = json_encode($order['h_conten']);
        $order['ord_body']['OrderHead']['uid']          = $order['uid'];
        $order['ord_body']['OrderHead']['DeclTime']     = date('YmdHis', time());
        unset($order['h_conten']);
        try {
            $email = $this->getEmail($order['uid']);
            if ( !$this->IdCard($order['ord_body']['OrderDocId']) ) {
                $this->updateQueueStatus($order['id'], 2,'身份证不正确');
                if ( !is_null($email['conmpanyemail']) ) {
                    send_mail($email['conmpanyemail'], '申报平台','', '身份证信息不正确,物流单号：' . $order['ord_body']['WaybillNo']);
                }
                return false;
            }
            if ( !$this->isPhone($order['ord_body']['OrderDocTel']) ) {
                $this->updateQueueStatus($order['id'], 2,'手机格式不正确');
                if ( !is_null($email['conmpanyemail']) ) {
                    send_mail($email['conmpanyemail'], '申报平台','', '手机格式不正确,物流单号：' . $order['ord_body']['WaybillNo']);
                }
                return false;
            }
            //            if ( $order['ord_body']['OrderGoodTotal'] >= 1500 ) {
            //                $this->updateQueueStatus($order['ord_body']['WaybillNo'], 2,'订单金额大于1500');
            //                if ( !is_null($email['conmpanyemail']) ) {
            //                    send_mail($email['conmpanyemail'], '申报平台','', '订单金额大于1500,物流单号：' . $order['ord_body']['WaybillNo']);
            //                }
            //                return false;
            //            }
            
            return $this->addAndPay($order, $email);
        } catch (\Exception $exception) {
            $this->updateQueueStatus($order['id'], 0);
            send_mail('kali20@126.com', '小艾2','', $exception->getMessage());
            @file_put_contents(getcwd() . '/runtime/log/gzeportlog/' . date('Y-m-d', time()) . '.log', json_encode(['errmsg' => $exception->getMessage(), 'errcode' => $exception->getCode(), 'file' => $exception->getFile(), 'line' => $exception->getLine()]) . "\n", FILE_APPEND);
            
        }
    }
    
    
    protected function getOrder ()
    {
        return Db::name('foll_order_electmp')->where(['is_queue' => 0])->find();
    }
    
    protected function IdCard ( $idc )
    {
        $chars = "/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}(\d|x|X)$/";
        return preg_match($chars, $idc) ? true : false;
    }
    
    protected function isPhone ( $tel )
    {
        $chars = "/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$|17[0-9]{1}[0-9]{8}$/";
        return preg_match($chars, $tel) ? true : false;
    }
    
    
    protected function getEmail ( $uid )
    {
        $sub = Db::name('foll_cross_border')->where('uid', $uid)->field('subject')->find();
        $sub = json_decode($sub['subject'], true);
        return $sub;
    }
    
    
    protected function addAndPay ( $data, $emil )
    {
        $hid     = $this->inserHead($data['ord_body']['OrderHead']);
        $isGoods = false;
        $qty     = 0;
        try {
            foreach ($data['ord_body']['goods_list'] as $val) {
                if ( is_null($this->getGoodsInfo($val['goodNo'])) ) {
                    $isGoods = true;
                    send_mail($emil['conmpanyemail'], '申报平台','', '该商品编号未在平台上架:' . $val['goodNo'] . ',物流单号：' . $data['ord_body']['WaybillNo']);
                    break;
                }
                $qty += $val['num'];
            }
            if ( $isGoods ) {
                $this->updateQueueStatus($data['id'], 2,'商品未在平台上架');
                return false;
            }
            $data['ord_body']['Qty'] = $qty;
            $this->inserBody($data, $hid, $emil);
            $result = $this->orderPay($data);
            if ( $result['code'] == 1 ) {
                $this->updateQueueStatus($data['id'], 1);
                return true;
            }else{
                $this->delOrder($hid);
                $errmsg = isset($result['info']['resultMsg']) ? $result['info']['resultMsg'] : '';
                $this->updateQueueStatus($data['id'], 2,'支付失败:'.$errmsg."|方法返回:".$result['msg']);
                send_mail($emil['conmpanyemail'], '申报平台','', '订单处理支付失败:' . $result['msg'] . $errmsg . ',物流单号：' . $data['ord_body']['WaybillNo']);
                return false;
            }
            
            
            
        } catch (\Exception $exception) {
            $this->delOrder($hid);
            throw new \Exception($exception->getMessage());
        }
    }
    
    
    protected function inserHead ( $head )
    {
        return Db::name('foll_elec_order_head')->insertGetId($head);
    }
    
    
    protected function inserBody ( $body, $hid, $ema )
    {
        if ( empty($body) ) {
            return false;
        }
        $data = ['head_id' => $hid, 'EntOrderNo' => $body['ord_body']['EntOrderNo'], 'goodsNo' => json_encode($body['ord_body']['goods_list']), 'GoodsName' => $body['ord_body']['GoodsType'], 'OrderGoodTotal' => $body['ord_body']['OrderGoodTotal'], 'OrderGoodTotalCurr' => $body['ord_body']['OrderGoodTotalCurr'], 'Freight' => $body['ord_body']['Freight'], 'Tax' => $body['ord_body']['Tax'], 'OtherPayment' => 0, 'OtherPayNotes' => null, 'ActualAmountPaid' => $body['ord_body']['OrderGoodTotal'], 'RecipientName' => $body['ord_body']['RecipientName'], 'RecipientAddr' => $body['ord_body']['RecipientAddr'], 'RecipientTel' => $body['ord_body']['RecipientTel'], 'RecipientCountry' => $body['ord_body']['RecipientCountry'], 'RecipientProvincesCode' => $body['ord_body']['RecipientProvincesCode'], 'OrderDocAcount' => $body['ord_body']['OrderDocTel'], 'OrderDocName' => $body['ord_body']['OrderDocName'], 'OrderDocId' => $body['ord_body']['OrderDocId'], 'OrderDocTel' => $body['ord_body']['OrderDocTel'], 'OrderDate' => date('y-m-d H:i:s', strtotime($body['ord_body']['OrderDate'])), 'EHSEntNo' => $ema['gzeportCode'], 'EHSEntName' => $ema['enterprisename'], 'WaybillNo' => $body['ord_body']['WaybillNo'], 'senderName' => $body['ord_body']['senderName'], 'senderTel' => $body['ord_body']['senderTel'], 'senderAddr' => $body['ord_body']['senderAddr'], 'senderCountry' => $body['ord_body']['senderCountry'], 'senderProvincesCode' => $body['ord_body']['senderProvincesCode'], 'packageType' => $body['ord_body']['packageType'], 'Province' => $body['ord_body']['Province'], 'city' => $body['ord_body']['city'], 'county' => $body['ord_body']['county'], 'create_at' => time()];
        try {
            Db::name('foll_elec_order_detail')->insert($data);
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
    
    protected function getGoodsInfo ( $gid )
    {
        return Db::name('foll_goodsreglist')->where('EntGoodsNo', $gid)->find();
    }
    
    protected function orderPay ( $data )
    {
        $pay = Helpay::instance();
        return $pay->Ordersubmits($data, $data['uid']);
    }
    
    protected function updateQueueStatus ( $wayNo, $status, $remk=null )
    {
        Db::name('foll_order_electmp')->where('id', $wayNo)->update(['is_queue' => $status,'remk'=>$remk]);
    }
    
    /**
     * @param $hid
     */
    protected function delOrder ( $hid )
    {
        Db::name('foll_elec_order_head')->where('id', $hid)->delete();
        Db::name('foll_elec_order_detail')->where('head_id', $hid)->delete();
    }
    
    
}