<?php
/**
 * [WeEngine System] Copyright (c) 2014 lotodo.com
 * WeEngine is NOT a free software, it under the license terms, visited http://www.lotodo.com/ for more details.
 */
$_W['page']['title'] = '关键字管理';

load()->model('cloud');

if (isset($_GPC['isdel'])&&$_GPC['isdel']!=""){
    pdo_delete('keyword',['id'=>$_GPC['isdel']]);
    message('删除完成！');
}

if (!$_W['ispost']){
    $pindex = max(1, intval($_GPC['page']));
    $psize = 20;
    $sql = 'SELECT * FROM ' . tablename('keyword') . " LIMIT " . ($pindex - 1) * $psize .',' .$psize;
    $lists = pdo_fetchall($sql);
    $total = pdo_fetchcolumn('SELECT COUNT(id) FROM ' . tablename('keyword'));
    $pager = pagination($total, $pindex, $psize);
    template('system/keyword');
}else{
    if ($_GPC['keyword']==""){
        exit(json_encode(['code'=>-1,'message'=>'请填写关键字']));
    }
    pdo_insert("keyword",["keyword"=>$_GPC['keyword'],'create_time'=>time()]);
    exit(json_encode(['code'=>0,'message'=>'完成']));
}


