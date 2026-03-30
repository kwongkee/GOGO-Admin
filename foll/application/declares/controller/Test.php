<?php
namespace app\declares\controller;
use think\Controller;
use Util\data\Helpay;
use Util\data\AliRealname;
use think\Session;
use think\Request;
use think\Loader;
use think\log;
use CURLFile;

class Test extends BaseAdmin{
	
	public function index() {
	
		echo '身份认证测试';
		$params['idcard']  = '452424199003141634';//身份证
		$params['realname'] = urldecode('赵金如');//姓名
		$host = "http://api.nuozhengtong.com/idcardinfo/server";
		$res  = $this->APISTORE($host,$params, '504fd5f6a735437c97cd117e61cb4a24');
		print_r($res);
	  	echo 'end';
		
		
		//$helpay = AliRealname::instance();
		
		//身份验证接口
		//$helpay->test();
		
		//查询订单接口
		//$helpay->checkeOrder();
		
		/*for($i=1;$i<=100;$i++) {
			$status = send_mail('805929498@qq.com','系统管理员','有测试数据',"发送测试：{$i} 次");
			if($status){
				echo "发送成功：{$i}条数据";
			} else {
				echo "发送失败：{$i}条数据";
			}
		}*/
		
		/*$this->admin = session('admin');//登录数据信息
		
		$helpay = Helpay::instance();
		$helpay->test($this->admin);
		
		
		$num = 123.6534;
		$format = sprintf("%.2f",$num);
		echo ($format*100);*/
	}
	
/**
 * APISTORE 获取数据
 * @param $url 请求地址
 * @param array $params 请求的数据
 * @param $appCode 您的APPCODE
 * @param $method
 * @return array|mixed
 */
public function APISTORE($url, $params = array(), $appCode, $method = "GET")
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $method == "POST" ? $url : $url . '?' . http_build_query($params));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Authorization:APPCODE ' . $appCode
    ));
    //如果是https协议
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //CURL_SSLVERSION_TLSv1
        curl_setopt($curl, CURLOPT_SSLVERSION, 1);
    }
    //超时时间
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//  return array("url" => $url, "param" => $params,'appcode'=>$appCode);
    //通过POST方式提交
    if ($method == "POST") {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
    }
    //返回内容
    $callbcak = curl_exec($curl);
    //http status
    $CURLINFO_HTTP_CODE = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    //关闭,释放资源
    curl_close($curl);
    //如果返回的不是200,请参阅错误码 https://help.aliyun.com/document_detail/43906.html
    if ($CURLINFO_HTTP_CODE == 200)
        return json_decode($callbcak, true);
    else if ($CURLINFO_HTTP_CODE == 403)
        return array("error_code" => $CURLINFO_HTTP_CODE, "reason" => "剩余次数不足");
    else if ($CURLINFO_HTTP_CODE == 400)
        return array("error_code" => $CURLINFO_HTTP_CODE, "reason" => "APPCODE错误");
    else
        return array("error_code" => $CURLINFO_HTTP_CODE, "reason" => "APPCODE错误");
}
	
	/**
	 * 订单提交测试；
	 */
	public function PostData(){
		//实例化类
		$helpay = Helpay::instance();
		$this->admin = session('admin');//登录数据信息
		$userId  = $this->admin['id'];//当前用户ID
		
		//订单数据
		$SendData['Head'] = [
            'MessageID' => 'KJ881111_JCJUMING_20180810120008360917',
            'MessageType' => 'KJ881111',
            'Sender' => 'JCJUMING',
            'Receiver' => 'C011000000332982',
            'SendTime' => '20180730080001',
            'FunctionCode' => 'BOTH',
            'SignerInfo' => '待定',
            'Version' => '3.0',
        ];

    	$SendData['datas'] = [
            'OrderHead' =>[
                    'DeclEntNo' => 'C011000000332982',
                    'DeclEntName' => '佛山市钜铭商务资讯服务有限公司',
                    'EBEntNo' => 'C011000000332982',
                    'EBEntName' => '佛山市钜铭商务资讯服务有限公司',
                    'EBPEntNo' => 'C011000000332982',
                    'EBPEntName' => '佛山市钜铭商务资讯服务有限公司',
                    'InternetDomainName' => 'www.gogo198.com',
                    'DeclTime' => '20180730080001',
                    'OpType' => 'A',
                    'IeFlag' => 'I',
                    'CustomsCode' => '5107',
                    'CIQOrgCode' => '441200',
                ],
            'OrderList' =>[
                    'EntOrderNo' => 'GCKK20180810120008859005',
                    'OrderDate' => date('Y-m-d H:i:s',time()),
                    'WaybillNo' => '9977977832524',
                    'goodsNo' => 'QZJC61790',
                    'BarCode' => '',
                    'GoodsName' => 'MK挎包~~~测试数据！',
                    'GoodsStyle' => '20680659*1个',
                    'OriginCountry' => '502',
                    'packageType' => '纸箱',
                    'unit' => '007',
                    'Qty' => '1',
                    'Price' => '385.2',
                    'OrderGoodTotal' => '385.2',
                    'Tax' => '0',
                    'OrderGoodTotalCurr' => '142',
                    'Freight' => '0',
                    'insuredFree' => '0',
                    'OrderDocAcount' => '赵金如',
                    'OrderDocName' => '赵金如',
                    'OrderDocId' => '452424199003141634',
                    'weight' => '0.68',
                    'grossWeight' => '0.83',
                    'OrderDocTel' => '18735639430',
                    'Province' => '山西省',
                    'city' => '晋城市',
                    'county' => '城区',
                    'RecipientAddr' => '山西省晋城市城区凤鸣小区70号楼3单元401室',
                    'RecipientCountry' => '142',
                    'RecipientProvincesCode' => '140502',
                    'senderName' => 'Express',
                    'senderTel' => '18924685180',
                    'senderAddr' => '312 bay ridge ave brooklyn NY11220',
                    'senderCountry' => '美国',
                    'senderProvincesCode' => 'USA',
                    'ActualAmountPaid' => '385.2',
                    'RecipientName' => '赵金如',
                    'RecipientTel' => '18735639430',
                ]
        ];
        
        $res = $helpay->Ordersubmits($SendData,$userId);
        echo '<pre>';
        print_r($res);
                
	}
}
?>
