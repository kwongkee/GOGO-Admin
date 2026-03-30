<?php

/**
 * 根据订单编号查询订单
 */

    $data = [
        'Token'=>'Query',
        'orderSn'=>'G9919820190718707670886',
    ];

    // 请求路径
    $url = 'http://shop.gogo198.cn/payment/Frx/Frx.php';

    // 执行请求
    $res = posts($url,$data);

    echo '<pre>';
    print_r($res);




    // 请求方法
    function posts($url ,$data) {
        //初始化
        $curl = curl_init();
        //设置捉取Url
        curl_setopt($curl,CURLOPT_URL,$url);
        //设置头文件的信息
        curl_setopt($curl,CURLOPT_HEADER,0);
        //设置获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        //设置超时
        curl_setopt($curl,CURLOPT_TIMEOUT,65);
        //设置post方式提交
        curl_setopt($curl,CURLOPT_POST,1);
        //设置post数据
        //设置请求参数
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        //执行命令  并返回结果
        $res = curl_exec($curl);
        //关闭连接
        curl_close($curl);
        $res = json_decode($res);
        //返回数据
        return $res;
    }

?>