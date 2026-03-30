<?php

namespace app\admin\controller;

use think\Db;
use think\Request;

// 身份验证接口
class Vertify
{
    public function ali()
    {
        //return $this->fetch();
        return view('vertify/ali');
    }

    // 进行阿里验证
    public function DoAli(Request $request)
    {
        // 获取表单数据
        $data = $request->param();

        if(!empty($data['uname']) && !empty($data['idCard'])) {
            $res = $this->GetAli($data);
            // 返回数据
            return json([
                'name'  => $res['name'],
                'idCard'=> $res['idCard'],
                'code'  => $res['status'],
                'msg'   => $res['msg'],
                'time'  => date('Y-m-d H:i:s',time()),
            ]);
        } else {
            return json([
                'name'  => '无',
                'idCard'=> '无',
                'code'  => 00,
                'msg'   => '用户名或证件号码为空',
                'time'  => date('Y-m-d H:i:s',time()),
            ]);
        }

    }

    // 获取阿里云身份验证
    private function GetAli($Arr)
    {
        $userId  = trim($Arr['idCard']);//身份证
        $realname = urldecode(trim($Arr['uname']));//姓名
        $host = "https://idcert.market.alicloudapi.com";
        $path = "/idcard";
        $method = "GET";
        $appcode = "504fd5f6a735437c97cd117e61cb4a24";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "idCard={$userId}&name={$realname}";
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        //curl_setopt($curl, CURLOPT_HEADER, true); 如不输出json, 请打开这行代码，打印调试头部状态码。
        //状态码: 200 正常；400 URL无效；401 appCode错误； 403 次数用完； 500 API网管错误
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $out_put = curl_exec($curl);
        // 数据解析

        // 获取状态码
        $code = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        $request = [];
        if($code == 200) {

            $body = json_decode($out_put,true);
            return $body;

            if($body['status'] == '01') {
                $request['code'] = 1000;
                $request['msg']  = $body['msg'];
            } else {
                $request['code'] = 1001;
                $request['msg']  = $body['msg'];
            }

        } else if($code == 403) {// 次数用完
            $request['code'] = 1002;
            $request['msg']  = '系统异常';
        }
        // 返回数据
        return $request;
    }


    public function helver()
    {
        echo '邦付宝验证';
    }



}