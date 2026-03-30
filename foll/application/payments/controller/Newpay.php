<?php
namespace app\payments\controller;
use think\Controller;
use Util\data\Sysdb;
use Util\data\Redis;
use think\Session;
use think\Sms;
use think\Request;
use think\Loader;
use Util\data\TgPay;
use Util\data\Newpays;
use think\Db;
// 新生支付 API
class Newpay extends Controller
{
    private $db;
    public function __construct(){
        $this->db = new Sysdb();
    }

    // 入口
    public function index() {

        $request = Request::instance();
        $param = $request->param();

        /*$fileName = '../runtime/log/Notify/ins'.date('Ymd').'.txt';
        file_put_contents($fileName,json_encode($param)."\r\n",FILE_APPEND);*/

        if($request->isPost()) {

            if(!empty($param['token'])) {

                $inArr = ['QuickPayment','OrderQuery','OrderPayQuery'];
                if(!in_array($param['token'],$inArr)) {
                    return json(['code'=>0,'data'=>[],'msg'=>'没有该查询方法，请检查！']);
                }

                // 分流处理
                switch($param['token']) { //跨境电商支付

                    case 'QuickPayment':// 支付订单  快捷支付

                        $set = $this->db->table('sz_yi_sysset')->field('sets')->where(['uniacid'=>$param['uniacid']])->item();
                        // 解析数据
                        $set = unserialize($set['sets']);
                        $status = $set['pay']['helpaystatus'];
                        $param['newpay'] = $set['pay']['helpay'];
                        unset($set);
                        if(!$status){
                            echo json(['code'=>0,'data'=>[],'msg'=>'该支付通道未开通！']);exit;
                        }

                        $res = $this->ImportRnbPay($param);
                        if($res['code'] > 0) {
                            echo json_encode(['code'=>1,'data'=>$res['data'],'pay_url'=>$res['pay_url'],'msg'=>'请求成功!']);exit;
                        }
                        echo json_encode(['code'=>0,'data'=>[],'msg'=>'请求失败!']);exit;

                        break;


                    case 'OrderQuery':// 订单查询

                        $set = $this->db->table('sz_yi_sysset')->field('sets')->where(['uniacid'=>$param['uniacid']])->item();
                        // 解析数据
                        $set = unserialize($set['sets']);
                        $status = $set['pay']['helpaystatus'];
                        $param['newpay'] = $set['pay']['helpay'];
                        unset($set);
                        if(!$status){
                            echo json(['code'=>0,'data'=>[],'msg'=>'该支付通道未开通！']);exit;
                        }

                        $res = $this->OrderQuery($param);
                        if($res['code'] > 0) {
                            return json(['code'=>1,'data'=>$res['data'],'msg'=>'请求成功!']);
                        }
                        return json(['code'=>0,'data'=>[],'msg'=>'请求失败!']);
                        break;

                    case 'OrderPayQuery'://提供海关查询  订单信息
                            if(empty($param['orderNo'])) {
                                return json(['code'=>0,'data'=>[],'msg'=>'订单编号不能为空!']);
                            }
                            // 海关查询接口
                            $res = $this->OrderPayQuery($param);
                            /*if($res['code'] > 0) {
                                return json(['code'=>1,'data'=>$res['data'],'msg'=>$res['msg']]);
                            }*/
                            //return json(['code'=>0,'data'=>[],'msg'=>$res['msg']]);
                            return json(['code'=>0,'data'=>[],'msg'=>$res]);
                        break;
                }
            }

        } else {

            /*$set = $this->db->table('sz_yi_sysset')->field('sets')->where(['uniacid'=>$param['uniacid']])->item();
            // 解析数据
            $set = unserialize($set['sets']);
            $status = $set['pay']['helpaystatus'];
            $param['newpay'] = $set['pay']['helpay'];
            unset($set);
            if(!$status){
                echo json(['code'=>0,'data'=>'','msg'=>'该支付通道未开通！']);exit;
            }
            //$param['orderId'] = $param['orderId'];
            // 输出数据
            $res = $this->OrderQuery($param);

            if($res['code']>0) {
                return json(['code'=>1,'data'=>$res['data'],'msg'=>'请求成功!']);
            }
            return json(['code'=>0,'data'=>'','msg'=>'请求失败!']);*/


            /*$str = 'acquiringTime=20181107122642&charset=1&completeTime=20181107122815&currencyCode=CNY&dealId=1021811071226015006&node=01&orderAmount=1&orderCurrencyCode=CNY&orderId=9919811071226425010010098897&partnerId=10000000050&payAmount=1&remark=5600&reserve1=&reserve2=&reserve3=&reserve4=&reserve5=&resultCode=1004&resultMsg=%E4%BA%A4%E6%98%93%E6%88%90%E5%8A%9F&settlementCurrencyCode=CNY&signMsg=24db98a35be1baf2f42e7c4201e47a53&signType=2&waybillNo=0';

            $rs = $this->AutoSign($str);
            echo '<pre>';
            print_r($rs);*/

            $nn = Newpays::instance();

            $rs = $nn->test();
            echo '<pre>';
            print_r($rs);


        }

    }

    // 海关订单查询
    private function OrderPayQuery($param) {

        //
        $order = $this->db->table('foll_payment_order')->field('initalRequest,initalResponse,goodsName')->where(['orderNo'=>$param['orderNo']])->item();
        if(empty($order)) {
            return ['code'=>0,'data'=>[],'msg'=>'没有查到该订单信息！'];
        }

        // 查询订单信息
        $ord = Db::table('ims_sz_yi_order')->alias('a')->join('ims_sz_yi_order_goods b','a.id=b.orderid','RIGHT')->field('b.goodsid,a.ordersn')->where(['a.ordersn_general'=>$param['orderNo']])->select();

        if(empty($ord)) {
            return ['code'=>0,'data'=>[],'msg'=>'没有该订单对应的产品信息！'];
        }

        // 当前域名
        $host   = $_SERVER['HTTP_HOST'];
        $gurl   = '';
        $orders = '';
        foreach($ord as $key=>$val) {
            $gurl .= "http://{$host}/app/index.php?i=3&c=entry&p=detail&id={$val['goodsid']}&do=shop&m=sz_yi | ";
            $orders .= $val['ordersn'].' |';
        }
        //$gurl  = "http://{$host}/app/index.php?i=3&c=entry&p=detail&id={$oid}&do=shop&m=sz_yi";

        $key = strtoupper(md5('ABCDEFG123456789'));

        $DataSen['payExchangeInfoHead'] = [
            'Guid'              =>'abcdefefwefawefewfwafwf',//系统唯一序号
            'initalRequest'     =>$order['initalRequest'],//原始请求      跨境电商平台企业向支付企业发送的原始信息   initalRequest   a
            'initalResponse'    =>$order['initalResponse'],//原始响应      支付企业向跨境电商平台企业反馈的原始信息   initalResponse  a
            'ebpCode'           =>'',//电商平台代码       ebpCode a
            'payCode'           =>'',//支付企业代码       460116T001  新生支付有限公司  payCode a
            'payTransactionId'  =>'123',//交易流水号     payTransactionNo    a
            'totalAmount'       =>'1123',//交易金额      payAmount           a
            'currency'          =>'3321',//币制         currCode
            'verDept'           =>'3',//验核机构    1-银联，2-网联，3-其他
            'payType'           =>'4',//支付类型  1-APP 2-PC 3-扫码 4-其他
            'tradingTime'       =>'1112',//交易成功时间   payTimeStr  a
        ];

        $DataSen['payExchangeInfoList'] = [
            'gname'       =>'354',//商品名称        // 通过订单号查询  b
            'itemLink'    => $gurl,//商品展示链接
            'orderNO'     => $orders,//交易平台的订单编号  // 报关订单编号
            'recpCode'    =>'91440605782023871F',//收款企业代码，境内统一信用代码  a
            'recpName'    =>'3356',//收款企业名称     a
            'recpAccount' =>'4545',//收款账号        a
        ];
        // a b c d e f g h i j k l m n o p q r s t u v w x y z  827C65B72B7A6B43FBFF9B37F16BEE7A  263741BB39BC2DA3A58E778F1E0A6D04
        $str = $this->ArrayToString($DataSen['payExchangeInfoList']);
        //$DataSen['str'] = $str;
        $DataSen['signMsg'] = strtoupper(md5($str.'key='.$key));

        return ['code'=>1,'data'=>$DataSen,'msg'=>'查询成功'];
    }


    /**
     * @param $url
     * @param $data
     * 问题：1、什么时候写入表数据
     * 1.1、报关成功写入，但获取不到产品ID，产品订单编号。
     * 2、根据什么参数写入；
     */


    // 表单自动提交请求
    private function Htmls($url,$data){
        $str = '';
        foreach($data as $key=>$val) {
            $str .="<input type='hidden' name='".$key."' value='".$val."'/>";
        }

        $forms = <<<EOT
		<form name='yunsubmit' action="$url" accept-charset='utf-8' method='post'>
			$str
		</form>
		<script>document.forms['yunsubmit'].submit();</script>
EOT;
        echo $forms;
    }



    // 前端页面地址
    public function welcoom(){
        $ins = file_get_contents("php://input");

        $fileName = '../runtime/log/Notify/welcoom'.date('Ymd').'.txt';
        file_put_contents($fileName,$ins."\r\n",FILE_APPEND);

        echo '前端页面';
    }


    // 进口人民币支付   收银台
    public function ImportRnbPay($param) {

        $orderid = $param['orderid'];// 订单ID
        // 根据订单ID 获取商品信息
        $title   = trim($param['titles']); // 商品标题
        // 2019-06-25  取消截取  2019-11-28
        //$title   = substr($title,0,30);
        // 截取字符串
        //$title   = $title.'...';

        $Count   = trim($param['Counts']); // 订单数量
        $Money   = (sprintf("%.2f",trim($param['moneys'])) * 100);

        // 请求地址
        //$url = 'https://uwebgatetest.hnapay.com/webgate/nativepay.htm';// 测试地址
        $url = 'https://uwebgate.hnapay.com/webgate/nativepay.htm';//   生产地址

        $ImportData['version'] 		         = '1.0';//版本号
        $insData['orderid']                  = $ImportData['orderId'] 		  = $this->NewOrders();//订单号 32
        //$ImportData['displayName'] 	  = '1.0';//下单商户显示名
        $insData['goodsName']                 = $ImportData['goodsName'] 	  = $title;//商品名称
        $insData['goodsCount']                = $ImportData['goodsCount'] 	  = $Count;//商品数量
        //$ImportData['goodsType'] 	  = '01';//商品分类  货物贸易下必填。 服装－00 食品－01电子产品－02 箱包－03 日用品－04 保健品－05 化妆品－06 家电－07
        $insData['submitTime']                = $ImportData['submitTime']     = date("YmdHis",time());//订单提交时间
        $ImportData['customerIp'] 	          = '120.78.202.118';//客户下单域名及 IP
        $ImportData['siteId'] 		          = 'www.gogo198.com';//商户网站域名
        $insData['orderAmount']               = $ImportData['orderAmount'] 	  = $Money;//订单金额  以分为单位
        $ImportData['orderCurrencyCode']      = 'CNY';//订单币种
        //交易类型 0001-担保交易，0002-即时付款，0003-跨境支付，0004-货物贸易，0005-酒店住宿，0006-机票旅游，0010-物流支付，0011-国际汇款，0012-平台交易
        $ImportData['tradeType']  	          = '0002';
        $ImportData['payType'] 	  	          = 'ALL';//付款方支付方式
        $ImportData['currencyCode']           = 'CNY';//交易币种
        $ImportData['settlementCurrencyCode'] = 'CNY';//结算币种
        $ImportData['directFlag'] 	          = '0';//是否直连   0：非直连 （默认） 1：直连
        $ImportData['borrowingMarked']        = '0';//资金来源借贷标识  0：无特殊要求（默认） 1：只借记  2：只贷记
        $ImportData['shareFlag'] 	          = '0';//分账标识   0：不分账（默认） 1：分账(仅当交易类型（tradeType）为0004 或者 0012 时可选。)
        //'http://shop.gogo198.cn/foll/public/index.php?s=newpay/welcoom';//商户回调地址  商户显示用回调地址，用于在客户支付完成后跳转回商户指定的 URL
        $ImportData['returnUrl'] 	          = 'http://shop.gogo198.cn/app/index.php?i=3&c=entry&do=order&m=sz_yi';
        //商户通知地址  通知商户订单处理状态的 URL
        $ImportData['noticeUrl'] 	          = 'http://shop.gogo198.cn/foll/public/index.php?s=newpay/notify';
        $ImportData['partnerId'] 	          = $param['newpay']['mchid'];//商户 ID
        $insData['os_id'] = $ImportData['remark']		  = $orderid;
        $ImportData['charset'] 	  	          = '1';//编码方式
        $ImportData['signType']   	          = '2';//签名类型  1：RSA 方式  2：MD5 方式
        $insData['pay_Type']                  =  'newpay';
        $insData['cate_Time']                 =  date('Y-m-d H:i:s',time());

        $ins = $ImportData;

		$fileName = '../runtime/log/Notify/Param'.date('Ymd').'.txt';
        file_put_contents($fileName,json_encode($ins)."\r\n");//,FILE_APPEND);
		
        try{
            // 写入流水表中
            $this->db->table('sz_yi_payment_old')->insert($insData);

        }catch (\Exception $e) {
            return ['code'=>0,'msg'=>'error'];

        }
        // 数组排序t
        ksort($ImportData);
        // 数组转pathinfo
        $str = $this->ArrayToString($ImportData);
        //$str = http_build_query($ImportData);
        $str .= 'pkey='.$param['newpay']['key'];
        $ImportData['signMsg'] 	  	  = md5($str);//签名字符串

        // 获取成功返回数据  2019-11-28
        return ['code'=>1,'data'=>$ImportData,'pay_url'=>$url];

        // 自动提交
        //$this->Htmls($url,$ImportData);

    }




    // 订单查询
    public function OrderQuery($param) {

        //$url = 'https://uwebgatetest.hnapay.com/webgate/orderQuery.htm';
        $url = 'https://uwebgate.hnapay.com/webgate/orderQuery.htm';

        $ImportData['version'] 		  = '1.0';//版本号
        //订单号 32
        $ImportData['queryOrderId']   = $this->NewOrders();
        //查询模式：1、单笔，2、批量
        $ImportData['mode'] 	      = 1;
        //查询类型：1、支付订单，2、退款订单，3、报关订单
        $ImportData['type'] 	      = 1;
        // 单笔查询时，商户请求时的订单号
        $ImportData['orderId']        = $param['orderId'];//'9919811081628065454102581595';
        //商户 ID
        $ImportData['partnerId']      = $param['newpay']['mchid'];
        // 扩展字段
        $ImportData['remark'] 	      = '扩展字段';
        //编码方式
        $ImportData['charset'] 	      = '1';
        //签名类型  1：RSA 方式  2：MD5 方式
        $ImportData['signType']       = '2';
        // 数组排序t
        ksort($ImportData);
        // 数组转pathinfo
        $str = $this->ArrayToString($ImportData);
        //$str = http_build_query($ImportData);
        $str .= 'pkey='.$param['newpay']['key'];
        //签名字符串
        $ImportData['signMsg'] 	     = md5($str);
        // 数据请求
        $res        = httpRequest($url,$ImportData);
        // 去除右边的1
        $res        = rtrim($res,'1');
        // 解析XML数据
        $objectxml  = simplexml_load_string($res,'SimpleXMLElement', LIBXML_NOCDATA);
        // 解析成对象
        $xmlJson    = json_encode($objectxml,JSON_UNESCAPED_UNICODE);
        // 解析成数组
        $xmlArray   = json_decode($xmlJson,true);
        // 返回数组数据
        return ['code'=>1,'data'=>$xmlArray];
    }


    // 生成新的订单号
    private function NewOrders(){
        $orderId = '99198'.date('mdHis').substr(implode(NULL, array_map('ord',str_split(substr(uniqid(),7,13),1))),0,8).mt_rand(11111,99999);
        return $orderId;
    }


    // 收银台异步通知
    public function Notify(){
        $ins = file_get_contents("php://input");

        $fileName = '../runtime/log/Notify/notiry'.date('Ymd').'.txt';
        file_put_contents($fileName,$ins."\r\n",FILE_APPEND);
        // 数据验签
        $Rins = $this->AutoSign($ins);
        if($Rins['code'] > 0) {
            $paytime = strtotime($Rins['data']['completeTime']);
            // 更新数据
            $up = [
                'pay_Time'=> $paytime,// 支付时间
                'dealId'  => trim($Rins['data']['dealId']),// 商户号
            ];
            // 更新条件  sz_yi_payment_old
            $orderId = trim($Rins['data']['orderId']);// 订单ID
            // 更新条件  sz_yi_order
            $oid     = trim($Rins['data']['remark']);//
            $status  = null;
            // 交易成功
            if(($Rins['data']['resultCode'] == '1004') && ($Rins['data']['resultMsg'] == '交易成功')) {
                $up['pay_Status'] = '100';
                $status = 1;
            } else { //交易失败
                $up['pay_Status'] = '102';
                $status = 0;
            }

            // 更新数据
            $orUp = [
                'status'=>$status,
                'paytime'=>$paytime,
                'paytype'=>'2',
                'pay_ordersn'=>trim($Rins['data']['dealId'])
            ];
            // asdb
            try{
                $this->db->startTranss();
                // 保存
                $this->db->table('sz_yi_payment_old')->where(['orderid'=>$orderId])->update($up);
                // 更新sz_yi_order 表
                $this->db->table('sz_yi_order')->where(['id'=>$oid])->update($orUp);

                $this->db->commits();

            }catch(\PDOException $e) {
                $this->db->rollbacks();
            }

            return 200;
        } else {
            return 400;
        }
    }


    //  数据回调 验签
    private function AutoSign($st) {
        // 秘钥
        $key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCIx+qqFEt2wDKcFGTWkgvHivJ3TwYMXkWtIrPZ4s/jT5E0ICGVz/QB/oqKw9Qbm+LUUX41dbouX5xJWVSiPrEihqYpuiqEfkewikE/Gd9b39cZrqhNYpT7jtoTUAiYB4dSrSOZ3/XJlGlYqIAahpIkdbjxvXauSwOFgYk8DlizSQIDAQAB';
        $rs = explode('&',$st);
        foreach($rs as $k=>$v) {
            // 字符串转换数组
            $tmp = explode('=',$v);
            if($tmp[1] != '' && $tmp[1]!= null){
                $new[$tmp[0]] = $tmp[1];
            }

        }
        // 数组排序
        ksort($new);
        // 中文urlencode
        $new['resultMsg'] = urldecode($new['resultMsg']);
        $signMsg = $new['signMsg'];
        unset($new['signMsg']);

        //print_r($new);
        // 数据拼接
        $str = $this->ArrayToString($new);
        $str .='pkey='.$key;
        $sign = md5($str);
        // 验签成功
        if($sign == $signMsg) {
            return ['code'=>1,'data'=>$new];
        }

        return ['code'=>0,'data'=>''];
        //return $sign.'  <>  '.$signMsg.' 》str》'.$str;
    }

    // 数组转pathinfo  ke=val&key1=val1
    public function ArrayToString($Addr){
        $str = '';
        foreach($Addr as $key=>$val){

            if($val == null && $val==''){
                continue;
            }
            $str .= $key.'='.$val.'&';
        }
        return $str;
    }


    public function curl_form($post_data,$sumbit_url,$http_url)
    {
        //初始化
        $ch = curl_init();
        //设置变量
        curl_setopt($ch,CURLOPT_URL, $sumbit_url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 0);//执行结果是否被返回，0是返回，1是不返回
        curl_setopt($ch,CURLOPT_HEADER, 0);//参数设置，是否显示头部信息，1为显示，0为不显示
        curl_setopt($ch,CURLOPT_REFERER, $http_url);
        //表单数据，是正规的表单设置值为非0
        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_TIMEOUT, 1);//设置curl执行超时时间最大是多少
        //使用数组提供post数据时，CURL组件大概是为了兼容@filename这种上传文件的写法
        //默认把content_type设为了multipart/form-data。虽然对于大多数web服务器并
        //没有影响，但是还是有少部分服务器不兼容。本文得出的结论是，在没有需要上传文件的
        //情况下，尽量对post提交的数据进行http_build_query，然后发送出去，能实现更好的兼容性，更小的请求数据包。
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        //执行并获取结果
        $output = curl_exec($ch);
        if($output === FALSE)// xiug
        {
            echo "<br/>","cUrl Error:".curl_error($ch);
        }
        //    释放cURL句柄
        curl_close($ch);
    }

    private function PostData($url,$Arra) {
        $ch = curl_init();
        // 设置变量
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,0);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_TIMEOUT,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$Arra);
        $output = curl_exec($ch);
        curl_close($ch);
        // 返回数据
        //echo $output;
        return $output;
        //return substr($output,0,38);
    }


}