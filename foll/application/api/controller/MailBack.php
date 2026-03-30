<?php

namespace app\api\controller;
use think\Controller;
use think\Db;

class MailBack extends Controller
{
    
    //生成订单
    public function index()
    {
        $userdata = $this->userdata();
        $addgood = [79073,79074,79075,79076,79077,79078,79079,79080,79081,79082,79083,79084,79085,79086,79087,79088,79089,79090,79091,79092];
        foreach ($userdata as $k => $v) {
            
            $goodid = $addgood[rand(0,19)];
            $realname = $v['name'];
            $mobile = $v['mobile'];
            $address = $v['address'];
            $province = $v['region'];
            $area = $v['city'];
            $orderInfo = Db::name('sz_yi_goods')->where(['id'=>$goodid])->find();
            $ordersn = $this->createOrderSn('SH');
            
            $order = [
                'uniacid' => 18,
                'openid' => '',
                'ordersn' => $ordersn,
                'price' => $orderInfo['marketprice'],
                'goodsprice' => $orderInfo['marketprice'],
                'discountprice' => '0.00',
                'status' => 2,
                'paytype' => 2,
                'createtime' => time(),
                'paytime' => time(),
                'expresscom' => '美国快递',
                'expresssn' => date('YmdHis'),
                'express' => 'meiguokuaidi',
                'sendtime' => time(),
                'addressid' => 146765,
                'address' => 'a:8:{s:2:"id";s:6:"146765";s:8:"realname";s:18:"'.$realname.'";s:6:"mobile";s:12:"'.$mobile.'";s:7:"address";s:24:"'.$address.'";s:8:"province";s:6:"'.$province.'";s:4:"city";s:9:"'.$province.'";s:4:"area";s:9:"'.$area.'";s:6:"street";s:0:"";}',
                'oldprice' => $orderInfo['marketprice'],
                'supplier_uid' => $orderInfo['supplier_uid'],
                'realprice' => $orderInfo['marketprice'],
                'ordersn_general' => $ordersn,
                'pay_ordersn' => $ordersn.'1',
                'address_send' => '1',

            ];
            $orderid = Db::name('sz_yi_order')->insertGetId($order);

            $ordergoods = [
                'uniacid' => 18,
                'orderid' => $orderid,
                'goodsid' => $orderInfo['id'],
                'price' => $orderInfo['marketprice'],
                'total' => 1,
                'createtime' => time(),
                'realprice' => $orderInfo['marketprice'],
                'oldprice' => $orderInfo['marketprice'],
                'supplier_uid' => $orderInfo['supplier_uid'],
                'openid' => '',
            ];

            Db::name('sz_yi_order_goods')->insert($ordergoods);
        }
        
        
        echo 'ok';
    }

    function geturl($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        //在需要用户检测的网页里需要增加下面两行
        //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        //curl_setopt($ch, CURLOPT_USERPWD, US_NAME.”:”.US_PWD);
        $contents = curl_exec($ch);
        curl_close($ch);
        
        //echo $contents;
        var_dump($contents);
    }

    public function getuserInfo()
    {
        $url = "https://www.fakeaddressgenerator.com/";
        $result = file_get_contents($url);
        preg_match_all('/<strong.*>(.*)<\/strong>/isU',$result,$arr);
        var_dump($arr);
    }

    public function createOrderSn($prefix)
    {
        $billno = date('YmdHis') . $this->random(6, true);

		while (1) {
            $count = Db::name('sz_yi_order')->where(['ordersn'=>$billno])->count();

			if ($count <= 0) {
				break;
			}

			$billno = date('YmdHis') . $this->random(6, true);
		}

		return $prefix . $billno;
    }

    public function random($length, $numeric = false) {
        $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        if ($numeric) {
            $hash = '';
        } else {
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            --$length;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $hash .= $seed[mt_rand(0, $max)];
        }
    
        return $hash;
    }

    public function userdata()
    {
        return array(
            
        );
    }

}
