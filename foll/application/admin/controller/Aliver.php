<?php

namespace app\admin\controller;

use think\Request;
use think\Db;
use think\loader;

class Aliver extends Auth
{
    // 阿里接口验证
    public function ali()
    {
        //admin/Aliver/ali
        /**
         * 开发流程：余额不足时：A为阿里云验证，B为支付企业验证
         * 获取验证失败表数据：customs_realname_error   类型为：error_type = A  的数据
         * 以数组的键来区分批次，统计验证失败数量
         * 生成列表，
         */
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/Aliver/ali'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $data = DB::name('customs_realname_error')
            ->field('a.title,b.user_name,a.status')
            ->alias('a')
            ->join('decl_user b','b.id=a.uid')
            ->where('a.error_type','A')//  需要修改为：a.error_type='A
            ->paginate(50,true,$config);

        $page = $data->render();
        $data = $data->toArray();
        $newArr = [];
        // 进行分组
        foreach($data['data'] as $k=>$v) {
            $newArr[$v['title']] = $v;
        }
        unset($data['data']);
        // 添加失败数量
        foreach ($newArr as$kk=>$vv){
            $count = DB::name('customs_realname_error')->where(['title'=>$vv['title'],'error_type'=>'A'])->count();
            $newArr[$kk]['num'] = $count ? : 0;
        }
        // 输出数据
        return view('Aliver/ali',[
            'title'=>'阿里余额不足',
            'data' =>$newArr,
            'page' =>$page,
        ]);
    }


    // 支付企业验证失败
    public function helver()
    {
        //admin/Aliver/helver
        $config = [
            'type' =>'Layui',
            'query'=>['s'=>'admin/Aliver/helver'],
            'var_page'=>'page',
            'newstyle'=>true
        ];

        $data = DB::name('customs_realname_error')
            ->field('a.title,b.user_name,a.status')
            ->alias('a')
            ->join('decl_user b','b.id=a.uid')
            ->where('a.error_type','B') //需要修改为：a.error_type='B'
            ->paginate(50,true,$config);

        $page = $data->render();
        $data = $data->toArray();
        $newArr = [];
        // 进行分组
        foreach($data['data'] as $k=>$v) {
            $newArr[$v['title']] = $v;
        }
        unset($data['data']);
        // 添加失败数量
        foreach ($newArr as$kk=>$vv){
            $count = DB::name('customs_realname_error')->where(['title'=>$vv['title'],'error_type'=>'B'])->count();
            $newArr[$kk]['num'] = $count ? : 0;
        }
        // 输出数据
        return view('Aliver/helver',[
            'title'=>'支付余额不足',
            'data' =>$newArr,
            'page' =>$page,
        ]);
    }


    /**
     * 验证列表
     */
    public function verlist()
    {
        $config = [
            'type'=>'layui',
            'query'=>['s'=>'admin/Aliver/verlist'],
            'ver_page'=>'page',
            'newstyle'=>true,
        ];

        $data = DB::name('customs_fomats')
            ->order('id','desc')
            ->paginate(12,false,$config);

        return view('Aliver/list',[
            'title'=>'已验证列表',
            'data' =>$data->toArray(),
            'page' =>$data->render(),
        ]);

    }

    // 处理验证
    public function store(Request $request)
    {
        // 获取批次号
        $postData['batch_num'] = $request->post('batch_num');
        $postData['type']      = $request->post('type');

        /**
         * 根据返回的数据请求给跨境申报平台，加入队列执行；
         */
        // 请求地址

        //$url = 'http://declare.gogo198.cn/api/ver/getPost';
        $url = 'http://39.108.11.214/api/ver/getPost';
        return $this->postDatas($url,$postData);

        //return json_encode(['code'=>1,'msg'=>'已加入验证队列']);

        //admin/Aliver/store
    }

    // 请求数据
    private function postDatas($url,$post_data)
    {
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
        curl_setopt($curl,CURLOPT_POSTFIELDS,$post_data);
        //执行命令  并返回结果
        $res = curl_exec($curl);
        //关闭连接
        curl_close($curl);
        //返回数据
        return $res;
    }
}


?>