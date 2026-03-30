<?php
if (!defined('IN_IA')) {
    exit('Access Denied');
}
global $_W;
global $_GPC;

$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
$uni = $_W['uniacid'];
$data = $_GPC;

if($op=='display'){
    if($data['pa']==1){
        $val = '';
        if($data['type']==1){
            //办公楼
            $val = trim($data['name']);
            if(strstr($val,'办公楼') === false && strstr($val,'写字楼') === false){
                $val .= '写字楼';
            }
        }
        //$res = exec('python '.$_SERVER['DOCUMENT_ROOT']."/python_code/test.py ".$val." ".$openid);
        
        //2、插入查询表
        pdo_insert('office_search',['openid'=>$openid,'keywords'=>$val,'createtime'=>TIMESTAMP]);
        $piid = pdo_insertid();
        
        //3、判断关键字一周内有无查询过
        $is_null = 0;
        $have_keywords = pdo_fetch('select * from '.tablename('office_keywords').' where keywords=:kwds order by id desc',[':kwds'=>$val]);
        if(empty($have_keywords['id'])){
            $have_keywords['createtime'] = 0;
        }else{
            $of_id = pdo_fetch('select office_ids from '.tablename('office_search').' where id=:c_id',[':c_id'=>$have_keywords['c_id']]);
            if($of_id['office_ids'] == ''){
                $is_null = 1;#为空时要查询
            }
            $have_keywords['createtime'] += 604800;
        }
        
        #以往查询时间小于当前时间或上一条为空
        if($have_keywords['createtime'] < TIMESTAMP || !empty($is_null)){
            //4、从来无查询过和已超过1周时间没更新
            $res = ihttp_post('https://decl.gogo198.cn/api/v2/query_business_server',['val'=>$val,'openid'=>$openid,'id'=>$piid]);
            #exec('python '.$_SERVER['DOCUMENT_ROOT']."/python_code/start.py ".$val." ".$openid." ".pdo_insertid());
            if($res['code']==200 && empty($is_null)){
                //1、向管理员提醒，然后更换cookie
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，用户提交了租金分析资讯，请更换或查看cookie是否生效！',
                    'keyword1' => '租金分析资讯',
                    'keyword2' => '租金分析资讯',
                    'keyword3' => date('Y-m-d H:i:s',TIMESTAMP),
                    'remark' => '',
                    'url' => '',
                    'openid' => 'ov3-bt5vIxepEjWc51zRQNQbFSaQ',
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                
                //2、记录搜索地点，一周内不爬新的
                pdo_insert('office_keywords',['keywords'=>$val,'openid'=>$openid,'createtime'=>TIMESTAMP,'c_id'=>$piid]);
            }
        }else{
            //4、有查询过&还在1周时间内生效
            $up_search = pdo_fetch('select office_ids from '.tablename('office_search').' where id=:id and openid=:openid',[':id'=>$have_keywords['c_id'],':openid'=>$have_keywords['openid']]);
            $res = pdo_update('office_search',['office_ids'=>$up_search['office_ids']],['id'=>$piid,'openid'=>$openid]);
            
            if($res){
                $post = json_encode([
                    'call'=>'confirmCollectionNotice',
                    'first' =>'您好，系统查询完成,请打开查看!',
                    'keyword1' => '查询业务',
                    'keyword2' => '查询完成',
                    'keyword3' => date('Y-m-d H:i:s',TIMESTAMP),
                    'remark' => '点击查看详情。',
                    'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=fesearch&p=index&m=sz_yi&op=detail&log_id='.$piid,
                    'openid' => $openid,
                    'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                ]);
                ihttp_request('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
            }
        }
        
        die(json_encode(['code'=>0,'msg'=>'系统查询中，请留意公众号消息通知！']));
    }
    
    $title = '租金查询';
    include $this->template('fesearch/index');
}
elseif($op=='detail'){
    #数据筛查页面
    $info = pdo_fetch('select id,office_ids,keywords from '.tablename('office_search').' where id=:id and openid=:openid',[':id'=>intval($data['log_id']),':openid'=>$openid]);
    
    // $office_list = explode(',',rtrim($info['office_ids'],','));
    // foreach($office_list as $k=>$v){
    //     #building_name物业单位,wuye_name经纪公司+seller_name销售员,city+area2+town商圈
    //     $office_list[$k] = pdo_fetch('select title,price,building_name,address,staff,ifnull("面议",price) as price,wuye_name,seller_name,city,area2,town from '.tablename('office_building').' where id=:id',[':id'=>$v]);
    // }
    
    $office_list = explode(',',rtrim($info['office_ids'],','));
    $address = [];
    $agent = [];
    foreach($office_list as $k=>$v){
        #building_name物业单位,wuye_name经纪公司+seller_name销售员,city+area2+town商圈
        $exam = pdo_fetch('select address,wuye_name from '.tablename('office_building').' where id=:id',[':id'=>$v]);
        if(!in_array($exam['address'],$address)){
            array_push($address,$exam['address']);
        }
        if(!in_array($exam['wuye_name'],$agent)){
            array_push($agent,$exam['wuye_name']);
        }
    }
    
    $title = '['.$info['keywords'].']查询结果';
    
    include $this->template('fesearch/detail');
}
elseif($op=='screen'){
    #数据筛查逻辑
    $addr = $data['address'];
    $agent = $data['agent'];
    $id = $data['id'];
    
    $of_info = pdo_fetch('select office_ids from '.tablename('office_search').' where id=:id and openid=:openid',[':openid'=>$openid,':id'=>$id]);
    $office_ids = explode(',',rtrim($of_info['office_ids'],','));
    $list = [];#筛查数组
    $list_ids = '';#筛查ids
    $address_name = [];#(商圈)地址
    $building_name = [];#(大厦)物业单位
    $agent_name = [];#(经纪)代理单位
    if($data['pa']==1){
        foreach($office_ids as $k=>$v){
            #building_name物业单位,wuye_name经纪公司+seller_name销售员,city+area2+town商圈
            $item = pdo_fetch('select id,title,price,building_name,address,staff,ifnull("面议",price) as price,wuye_name,seller_name,city,area2,town from '.tablename('office_building').' where id=:id and address=:address',[':id'=>$v,':address'=>$addr]);
        
            if(!empty($item)){
                $list[] = $item;
                $list_ids .= $item['id'] . ',';
                if(!in_array($item['building_name'],$building_name)){
                    array_push($building_name,$item['building_name']);
                }
                if(!in_array($item['wuye_name'],$agent_name)){
                    array_push($agent_name,$item['wuye_name']);
                }
            }
        }
    }elseif($data['pa']==2){
        foreach($office_ids as $k=>$v){
            #building_name物业单位,wuye_name经纪公司+seller_name销售员,city+area2+town商圈
            $item = pdo_fetch('select id,title,price,building_name,address,staff,ifnull("面议",price) as price,wuye_name,seller_name,city,area2,town from '.tablename('office_building').' where id=:id and wuye_name=:wuye_name',[':id'=>$v,':wuye_name'=>$agent]);
        
            if(!empty($item)){
                $list[] = $item;
                $list_ids .= $item['id'] . ',';
                if(!in_array($item['address'],$address_name)){
                    array_push($address_name,$item['address']);
                }
                if(!in_array($item['building_name'],$building_name)){
                    array_push($building_name,$item['building_name']);
                }
            }
        }
    }
    die(json_encode(['code'=>0,'list'=>$list,'list_ids'=>$list_ids,'address_name'=>$address_name,'building_name'=>$building_name,'agent_name'=>$agent_name,'msg'=>'筛查成功']));
}
elseif($op=='screen_detail'){
    $ids = rtrim($data['ids'],',');
    $sort_type = $data['sort_type'];
    $pa = $data['pa'];
    $list = [];
    $condition = '';
    if($sort_type==1){
        $condition = ' order by price desc';
    }elseif($sort_type==2){
        $condition = ' order by price asc';
    }
    
    if($pa==1){
        #相同地址
        if($data['analysis_type']==1){
            $list = pdo_fetchall('select * from '.tablename('office_building').' where id in ('.$ids.') '.$condition);
        }elseif($data['analysis_type']==2){
            if(empty($data['an_2'])){
                die(json_encode(['code'=>-1,'msg'=>'请选择必填项!']));
            }
            $list = pdo_fetchall('select * from '.tablename('office_building').' where id in ('.$ids.') and building_name="'.$data['an_2'].'"'.$condition);
        }elseif($data['analysis_type']==3){
            if(empty($data['an_3'])){
                die(json_encode(['code'=>-1,'msg'=>'请选择必填项!']));
            }
            $list = pdo_fetchall('select * from '.tablename('office_building').' where id in ('.$ids.') and wuye_name="'.$data['an_3'].'"'.$condition);
        }
    }elseif($pa==2){
        #相同代理
        if($data['analysis_type']==1){
            if(empty($data['an_1'])){
                die(json_encode(['code'=>-1,'msg'=>'请选择必填项!']));
            }
            $list = pdo_fetchall('select * from '.tablename('office_building').' where id in ('.$ids.') and address="'.$data['an_1'].'"'.$condition);
        }elseif($data['analysis_type']==2){
            if(empty($data['an_2'])){
                die(json_encode(['code'=>-1,'msg'=>'请选择必填项!']));
            }
            $list = pdo_fetchall('select * from '.tablename('office_building').' where id in ('.$ids.') and building_name="'.$data['an_2'].'"'.$condition);
        }elseif($data['analysis_type']==3){
            $list = pdo_fetchall('select * from '.tablename('office_building').' where id in ('.$ids.')'.$condition);
        }
    }
    foreach($list as $k=>$v){
        $list[$k]['pic_list'] = explode(',',rtrim($v['pic_list'],','))[0];
        if(empty($v['price'])){
            $list[$k]['price'] = '租金面议';
        }
    }
    die(json_encode(['code'=>0,'msg'=>'筛查成功','list'=>$list]));
}
elseif($op=='info_detail'){
    $id = intval($data['id']);
    if(empty($id)){
        exit('参数错误!');
    }
    
    $info = pdo_fetch('select * from '.tablename('office_building').' where id=:id',[':id'=>$id]);
    if(empty($info['price'])){
        $info['price'] = '租金面议';
    }
    $info['pic_list'] = explode(',',rtrim($info['pic_list'],','));
    $info['descs'] = json_decode($info['descs'],true);
    $info['intro'] = json_decode($info['intro'],true);
    #print_r($info);die;
    include $this->template('fesearch/info_detail');
}