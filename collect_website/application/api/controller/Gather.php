<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

header('Access-Control-Allow-Origin: *'); //设置http://www.baidu.com允许跨域访问
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header

//集运网接口
class Gather extends Controller
{
    public function get_link(Request $request){
        $dat = input();
        $link = get_link($dat['id']);
        return $link;
    }

    //获取分销对账业务的各种表头
    public function gettableinfo(Request $request){
        $dat = input();
        $list = '';
        if($dat['id']==1){
            #获取各国
            $no_need = isset($dat['no_need'])?$dat['no_need']:0;

//            if(session('country_list')==''){
                $list = get_country($no_need);//州-国
                $list = json_encode($list,true);

//                $country = get_country($no_need);//州-国
//                $list = get_country_area($country);
                session('country_list',$list);
//            }else{
//                $list = session('country_list');
//            }
        }elseif($dat['id']==2){
            #各国线路
            if(session('line_list')==''){
                $country = get_country();//州-国
                $list = get_country_line($country);
                session('line_list',$list);
            }else{
                $list = session('line_list');
            }
        }elseif($dat['id']==3){
            #货物类别
//            if(session('value_list')==''){
                $list = get_product();//州-国
                session('value_list',$list);
//            }else{
//                $list = session('value_list');
//            }
        }elseif($dat['id']==4){
            #获取国家的省，默认是中国
            if(session('province_list'.$dat['country_id'])=='') {
                $list = get_province($dat['country_id']);
                session('province_list'.$dat['country_id'],$list);
            }else{
                $list = session('province_list'.$dat['country_id']);
            }
        }elseif($dat['id']==5){
            #获取省下的市
            if(session('city_list'.$dat['province_id'])=='') {
                $list = get_city($dat['province_id']);
                session('city_list'.$dat['province_id'],$list);
            }else{
                $list = session('city_list'.$dat['province_id']);
            }
        }elseif($dat['id']==6){
            #获取该国的地区
            if(session('area_list')==''){
                $list = get_country_area($dat['id']);
                session('area_list',$list);
            }else{
                $list = session('area_list');
            }
        }

        return json(['code'=>0,'list'=>$list]);
    }
}