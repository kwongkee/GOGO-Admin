<?php
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;

$openid=$_W['openid'];
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';

if($op=='display'){
    //商品海关编码列表
    if($_GPC['pa']==1){
        $limit = intval($_GPC['limit']);
        $page = intval($_GPC['page']) - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keywords = trim($_GPC['keywords']);
        if(empty($keywords)){
            $list = pdo_fetchall('select * from '.tablename('customs_hscode_tariffschedule_ssl').' where 1 limit '.$page.",".$limit);
            $count = pdo_fetch('select count(id) as c from '.tablename('customs_hscode_tariffschedule_ssl').' where 1');
        }else{
            $keywords = explode(' ',rtrim($keywords,' '));
            $keywords_fenci = '';
            foreach($keywords as $k=>$v){
                $keywords_fenci .= '(`name` like "%' . $v. '%" or hscode like "%'.$v.'%") or ';
            }
            $keywords_fenci = substr($keywords_fenci,0,-3);

            $list = pdo_fetchall('select * from '.tablename('customs_hscode_tariffschedule_ssl').' where '.$keywords_fenci.' limit '.$page.",".$limit);
            $count = pdo_fetch('select count(id) as c from '.tablename('customs_hscode_tariffschedule_ssl').' where '.$keywords_fenci);

//            $list = pdo_fetchall('select * from '.tablename('customs_hscode_tariffschedule_ssl').' where (`name` like "%'.$keywords.'%" or hscode like "%'.$keywords.'%") limit '.$page.",".$limit);
//            $count = pdo_fetch('select count(id) as c from '.tablename('customs_hscode_tariffschedule_ssl').' where (`name` like "%'.$keywords.'%" or hscode like "%'.$keywords.'%")');
        }

        die(json_encode(['code'=>0,'count'=>$count['c'],'data'=>$list]));
    }
    include $this->template('hscode/index');
}elseif($op=='info'){
    //海关编码详情
    $id=intval($_GPC['id']);
    $list = pdo_fetch('select * from '.tablename('customs_hscode_tariffschedule_ssl').' where id=:id',[':id'=>$id]);
    $list['basic_info'] = json_decode($list['basic_info'],true);
    $list['tax_info'] = json_decode($list['tax_info'],true);
    $list['declaration_elements'] = json_decode($list['declaration_elements'],true);
    $list['regulatory_conditions'] = json_decode($list['regulatory_conditions'],true);
    $list['inspect_quarantine'] = json_decode($list['inspect_quarantine'],true);
    $list['treaty_tax_rate'] = json_decode($list['treaty_tax_rate'],true);
    $list['rcep_tax_rate'] = json_decode($list['rcep_tax_rate'],true);
    $list['ciq_code_info'] = json_decode($list['ciq_code_info'],true);
    $list['chapter_info'] = json_decode($list['chapter_info'],true);
    include $this->template('hscode/info');
}elseif($op=='info2'){
    //海关编码详情
    $hscode=trim($_GPC['hscode']);
    $list = pdo_fetch('select * from '.tablename('customs_hscode_tariffschedule_ssl').' where hscode=:hscode',[':hscode'=>$hscode]);
    $list['basic_info'] = json_decode($list['basic_info'],true);
    $list['tax_info'] = json_decode($list['tax_info'],true);
    $list['declaration_elements'] = json_decode($list['declaration_elements'],true);
    $list['regulatory_conditions'] = json_decode($list['regulatory_conditions'],true);
    $list['inspect_quarantine'] = json_decode($list['inspect_quarantine'],true);
    $list['treaty_tax_rate'] = json_decode($list['treaty_tax_rate'],true);
    $list['rcep_tax_rate'] = json_decode($list['rcep_tax_rate'],true);
    $list['ciq_code_info'] = json_decode($list['ciq_code_info'],true);
    $list['chapter_info'] = json_decode($list['chapter_info'],true);
    include $this->template('hscode/info');
}elseif($op=='regulatory_list'){
    //监管条件列表
    if($_GPC['pa']==1) {
        $list = pdo_fetchall('select * from '.tablename('customs_supervision').' where 1');
        die(json_encode(['code'=>0,'data'=>$list]));
    }
    include $this->template('hscode/regulatory_list');
}elseif($op=='inspection_quarantine_list'){
    //检验检疫类别列表
    if($_GPC['pa']==1) {
        $list = pdo_fetchall('select * from '.tablename('customs_inspection_quarantine').' where 1');
        die(json_encode(['code'=>0,'data'=>$list]));
    }
    include $this->template('hscode/inspection_quarantine_list');
}