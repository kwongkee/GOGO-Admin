<?php
/**
 * User: PFC内容对比
 * Date: 2023/3/12
 * Time: 14:11
 */
// 模块LTD提供
if (!defined('IN_IA')) {
    exit('Access Denied');
}

global $_W;
global $_GPC;
$op = !empty($_GPC['op'])?trim($_GPC['op']):'display';
$openid = $_W['openid'];
$time = TIMESTAMP;
$data = $_GPC;

if($op=='display'){
    $menu = pdo_fetchall('select * from '.tablename('centralize_pfc_menu_list').' where 1');
    $change_ids = [];
    foreach($menu as $k=>$v){
        #获取该标题最新的2条记录
        $content = pdo_fetchall('select * from '.tablename('centralize_pfc_list').' where pid=:id order by id desc limit 2',[':id'=>$v['id']]);
        if(count($content)==2){
            //进行对比
            foreach($content as $k2=>$v2){
                $content[$k2]['content'] = json_decode($v2['content'],true);
            }
            //引用对比文件
            $dir = $_SERVER['DOCUMENT_ROOT'].'/foll/vendor/htmldiff';
            require_once($dir."/html_diff.php");

            //开始对比(上一条，最新一条)
            $con = html_diff($content[1]['content'],$content[0]['content'],true);
            //判断有无出现class="diff-html-added/diff-html-removed"
            if(strpos($con,"diff-html-added") || strpos($con,"diff-html-removed")){
                //记录ids
                $change_ids[$k]['title'] = $v['title'];
                $change_ids[$k]['ids'] = $content[1]['id'].','.$content[0]['id'];
            }
        }
    }  
    include $this->template('pfcexpress/index/index');
}
elseif($op=='detail'){
    $ids = explode(',',$data['ids']);
    $content = [];
    foreach($ids as $k=>$v){
        $content[] = pdo_fetch('select content from '.tablename('centralize_pfc_list').' where id=:id ',[':id'=>$v]);
    }
    
    foreach($content as $k2=>$v2){
        $content[$k2]['content'] = json_decode($v2['content'],true);
    }
    //引用对比文件
    $dir = $_SERVER['DOCUMENT_ROOT'].'/foll/vendor/htmldiff';
    require_once($dir."/html_diff.php");
    $con = html_diff($content[0]['content'],$content[1]['content'],true);
    
    include $this->template('pfcexpress/index/detail');
}