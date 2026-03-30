<?php

namespace app\payments\controller;

use think\Controller;
use think\route\Rule;
use Util\data\Sysdb;
use Util\data\Redis;
use think\Session;
use think\Sms;
use think\Request;
use think\Loader;
use Util\data\TgPay;

class Payments extends Controller
{
    public $db;
    // 前往http://shop.gogo198.cn/payment/wechat/Payments.php
    public function wxScode(Request $request)
    {
        $account = '101540254006';
        $key     = 'f8ee27742a68418da52de4fca59b999e';

        $inputs = $request->param();
        if(empty($inputs)){
            return json_encode(['code'=>0,'msg'=>'参数不能为空，请检查！']);
        }
        $inData = ['payMoney','ordersn','body','returnUrl','openid'];
        foreach($inputs as $k=>$v) {
            if(!in_array($k,$inData)) {
                return json_encode(['code'=>0,'msg'=>'您的参数有误，请检查！']);
            }
        }

        $package = array();
        $package['account']    = $account;
        $package['payMoney']   = $inputs['payMoney'];
        $package['lowOrderId'] = $inputs['ordersn'];
        $package['body'] 	   = $inputs['body'];
        $package['notifyUrl']  = 'http://shop.gogo198.cn/foll/public/index.php?s=Payments/Notifys';//后台回调地址
        $package['returnUrl']  = $inputs['returnUrl'];
        $package['openId'] 	   = $inputs['openid'];
        //转换key=value&key=value;
        $str = $this->tostring($package);
        //拼接加密字串
        $str .= '&key=' . $key;
        //MD5加密字串
        $sign = md5($str);
        //返回加密字串转换成大写字母
        $package['sign'] = strtoupper($sign);
        //数据包转换成json格式
        $data =  json_encode($package);
        //数据请求地址，post形式传输
        $url = 'https://ipay.833006.net/tgPosp/payApi/wxJspay';
        //数据请求地址，post形式传输
        $response = $this->ihttp_posts($url,$data);
        // json数据
        $response = json_decode($response,true);
        if($response['status'] == '100') {
            return json_encode(['code'=>1,'msg'=>$response['message'],'pay_url'=>$response['pay_url']]);
        } else {
            return json_encode(['code'=>0,'msg'=>$response['message'],'pay_url'=>'']);
        }

        echo '<pre>';
        print_r($response);

        /*
         * {
	"sign": "EF740C8A595C93403EA393816D202FAE",
	"pay_url": "https://pay.swiftpass.cn/pay/jspay?token_id=64d817fe2c046af420cc995bde8e71602&showwxtitle=1",
	"message": "获取成功",
	"status": 100,
	"upOrderId": "91090438387464273920",
	"pay_info": "{"appId":"wx0781c1dea664cd9a","timeStamp":"1548815745","nonceStr":"66329c1d259c4a4bb93f89c380de4ff2","package":"prepay_id=wx301035459797791dd14677413182739575","signType":"RSA","status":"0","paySign":"IxIY9RduH/TInVYgafRwvp3JHMvMekV1Q2WWl/RALsPCdySjwPKSxnfSYIsP5IvLUieAAIF0t2cL9/x8YLXt9fmO7ciiv532ogmslgbiMhdYR6S238fuuTB2m0Ak7hCF3ZlZwmiuFlMcs+FqM24kdMs36wLn3uD7pmGoRdDrKH2seDvnrstln9wlvLwCQhdvhC/EZI1LEEuFC//IK4adGghI5ZyDu0jso7rz/WgxgQ6PVT3Izks12pAR77eKmxX+STRdzbWnN/XSHy0nnELbOw61Uglm124PerCHNomJE8izY8VYWMfD/R6dZ/UGZDvRdcd+3j6vlFCWZ7ygEkLHfQ==","callback_url":"https://www.baidu.com"}"
}
         */

    }


    // 支付宝支付  前往http://shop.gogo198.cn/payment/wechat/Payments.php
    public function Alipays(Request $request)
    {
        $account = '101540254006';
        $key     = 'f8ee27742a68418da52de4fca59b999e';

        $inputs = $request->param();
        if(empty($inputs)){
            return json_encode(['code'=>0,'msg'=>'参数不能为空，请检查！']);
        }
        $inData = ['payMoney','ordersn','body','returnUrl','openid'];
        foreach($inputs as $k=>$v) {
            if(!in_array($k,$inData)) {
                return json_encode(['code'=>0,'msg'=>'您的参数有误，请检查！']);
            }
        }

        $package = array();
        $package['account'] 	=   $account;
        $package['payMoney'] 	= 	number_format($inputs['payMoney'],2);
        $package['lowOrderId']  = 	$inputs['ordersn'];
        $package['body'] 		= 	$inputs['body'];
        //$package['notifyUrl']	=   'http://shop.gogo198.cn/payment/Notify/TgNotify.php';
        $package['notifyUrl']	=   'http://shop.gogo198.cn/foll/public/index.php?s=Payments/Notifys';
        //$package['notifyUrl'] 	= 	'http://shop.gogo198.cn/payment/wechat/tgwechatnotify.php';//后台回调地址
        $package['payType'] 	= 	'1';//
        $package['returnUrl']   =  'https://www.baidu.com';
        //转换key=value&key=value;
        $str = $this->tostring($package);
        //拼接加密字串
        $str .= '&key=' . $key;
        //MD5加密字串
        $sign = md5($str);
        //返回加密字串转换成大写字母
        $package['sign'] = strtoupper($sign);
        //数据包转换成json格式
        $data =  json_encode($package);
        //数据请求地址，post形式传输
        $url = 'https://ipay.833006.net/tgPosp/services/payApi/unifiedorder';
        //数据请求地址，post形式传输
        $response = $this->ihttp_posts($url,$data);
        //解析json数据
        $response = json_decode($response,TRUE);
        //返回json数据参数（sign,message,status,codeUrl,account,orderID,lowOrderId)
        return $response;

    }


    // 门禁支付回调
    public function Notifys()
    {
        $this->db = new Sysdb;

        /*$ors = $this->db->table('pay_config')->field('config')->where(['uniacid'=>3])->item();
        echo '<pre>';
        print_r($ors);
        die;*/

        $input = file_get_contents('php://input');
        // 日志写入
        file_put_contents('../runtime/log/Notify/tgpay.txt', $input."\r\n",FILE_APPEND);
        //接收后台回调参数，并转换json格式
        $receive = json_decode($input,TRUE);
        // 数据不为空
        if (!empty($receive)) {
            // 日志写入
            //file_put_contents('../runtime/log/Notify/tgpay.txt', $input."\r\n",FILE_APPEND);
            $ordersn 	= $receive['lowOrderId'];
            $uporderid  = $receive['upOrderId'];
            //$payTime    = $receive['payTime'];
            $ptime		= strtotime($receive['payTime']);

            // 查询数据库中对应的数据
            $items = $this->db->table('door_orders')->field(['pStatus','returnUrl','ordersn'])->where(['ordersn'=>$ordersn])->item();
            if(!empty($items) && $items['pStatus'] == '2') {// 如果状态未支付的情况才修改状态
                // 支付成功
                if(($receive['state'] == '0') && ($receive['orderDesc'] == '支付成功')) {

                    $upData = ['upOrderId'=>$uporderid,'pStatus'=>1,'ptime'=>$ptime];
                    try{

                        $this->db->startTranss();//开启事务

                        $this->db->table('door_orders')->where(['ordersn'=>$ordersn])->update($upData);

                        $this->db->commits();// 事务提交
                    }catch(\Exception $e){
                        // 日志写入
                        file_put_contents('../runtime/log/Notify/tgpayError.txt', $e."\r\n",FILE_APPEND);
                        $this->db->rollbacks();//订单回滚
                    }


                } else { // 支付失败

                    $upData = ['upOrderId'=>$uporderid,'paystatus'=>3,'ptime'=>$ptime];
                    try{

                        $this->db->startTranss();//开启事务

                        $this->db->table('door_orders')->where(['ordersn'=>$ordersn])->update($upData);

                        $this->db->commits();// 事务提交
                    }catch(\Exception $e){
                        // 日志写入
                        file_put_contents('../runtime/log/Notify/tgpayError.txt', $e."\r\n",FILE_APPEND);
                        $this->db->rollbacks();//订单回滚
                    }

                }

                $retData['payMoney']	= 	$receive['payMoney'];//交易金额
                $retData['orderDesc']	=	$receive['orderDesc'];//提示消息
                $retData['state']		=	$receive['state'];	//支付状态
                $retData['openid']		=	isset($receive['openid']) ? $receive['openid'] : ''; //用户OPENID
                $retData['payTime']		=	$receive['payTime'];//支付时间
                $retData['upOrderId']	=	$uporderid;//支付商户号
                $retData['lowOrderId']	=	$items['ordersn'];//下游订单号
                // 回调数据
                $jsonData = json_encode($retData);

                //file_put_contents('../runtime/log/Notify/retData.txt',$jsonData."\r\n",FILE_APPEND);
                //$this->ihttp_posts($items['returnUrl'],$jsonData);
                exit('SUCCESS');


            } else { // 否则就是支付成功直接返回成功
                exit('SUCCESS');
            }
        } else {
            exit('FAIL');
        }
    }


    /**
     * 字符串拼接
     * @arrs :数组数据
     */
    public function tostring($arrs) {
        ksort($arrs, SORT_STRING);
        $str = '';
        foreach ($arrs as $key => $v ) {
            if ($v=='' || $v == null) {
                continue;
            }
            $str .= $key . '=' . $v . '&';
        }
        $str = trim($str,'&');
        return $str;
    }

    /**
     * @数据请求提交POST json
     * @$url:请求地址
     * @post_data:请求数据
     */
    public function ihttp_posts($url,$post_data) {
        //初始化
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0); // 跳过证书检查
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($post_data)));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}



?>