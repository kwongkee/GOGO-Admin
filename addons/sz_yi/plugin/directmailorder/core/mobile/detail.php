<?php

global $_W;
global $_GPC;
$id = $_GPC['id'];
$openid=$_W['openid'];
if ($_GPC['a'] == '') {
    
    //是否微信，并验证unionid
    if (is_weixin()){
        $account_api = WeAccount::create();
        $fans_info = $account_api->fansQueryInfo($openid);
        $user = pdo_get('member',['unionid'=>$fans_info['unionid']]);
        if (!$user){
            //跳转认证
            $url = 'Location:/app/index.php?i=' . $_W['uniacid'] . '&c=entry&p=member_auth&do=member&m=sz_yi&from=app&mid=' . $_GPC['mid'];
            return header($url);
        }
        $isCertifiedOtherApp = pdo_get('sz_yi_member',['openid'=>$openid,'uniacid'=>$_W['uniacid']],['id','unionid','mobile','id_card']);
        //待新增其他公众认证添加本公众信息
        if ($isCertifiedOtherApp['unionid']==""){
            pdo_update('sz_yi_member',[
                'unionid'=>$fans_info['unionid'],
                'realname'=>$user['name'],
                'mobile'=>$user['phone'],
                'id_card'=>$isCertifiedOtherApp['id_card']
            ],[
                'id'=>$isCertifiedOtherApp['id']
            ]);
        }
    }
    
    if (!is_numeric($id)) {
        message('暂无商品');
    }
    $goods = pdo_get('sz_yi_goods', ['id' => $id]);
    if (empty($goods)) {
        message('查询不到商品');
    }
    
    $html = $goods['content'];
    preg_match_all('/<img.*?src=[\\\'| "](.*?(?:[\\.gif|\\.jpg]?))[\\\'|"].*?[\\/]?>/', $html, $imgs);
    if (isset($imgs[1])) {
        foreach ($imgs[1] as $img) {
            $im       = ['old' => $img, 'new' => tomedia($img)];
            $images[] = $im;
        }
        if (isset($images)) {
            foreach ($images as $img) {
                $html = str_replace($img['old'], $img['new'], $html);
            }
        }
        $goods['content'] = $html;
    }
    $goods['thumb'] = tomedia($goods['thumb']);
    $userIdCard = pdo_get('sz_yi_member',['openid'=>$openid],['realname','id_card']);
    $favorite = pdo_fetch("select * from ".tablename('sz_yi_member_favorite')." where uniacid={$_W['uniacid']} and goodsid={$id} and openid='{$openid}'");
    $goodsParam  = pdo_getall('sz_yi_goods_param', ['goodsid' => $goods['id']]);//商品属性
    $goodsAccess = pdo_fetchall("select headimgurl,nickname,content,createtime from ".tablename('sz_yi_order_comment')." where goodsid={$goods['id']} order by id desc limit 1,10");
    include $this->template("detail");
} else if ($_GPC['a'] == 'getGoodsAccess') {
        $limit       = 10;
        $page        = ($_GPC['page'] - 1) * $limit;
        $page = $page<=0?1:$page;
        $goodsAccess = pdo_fetchall("select headimgurl,nickname,content,createtime from ".tablename('sz_yi_order_comment')." where goodsid={$id} order by id desc limit {$page},{$limit}");
        foreach ($goodsAccess as &$item){
            $item['createtime']=date('Y-m-d H:i:s',$item['createtime']);
        }
        show_json(0, $goodsAccess);
}else if ($_GPC['a']=='setFavorite'){
    $id = intval($_GPC['id']);
    $goods = pdo_fetch('select id from ' . tablename('sz_yi_goods') . ' where uniacid=:uniacid and id=:id limit 1', array(':uniacid' => $_W['uniacid'], ':id' => $id));
    if (empty($goods)) {
        show_json(0, '商品未找到');
    }
    $data = pdo_fetch("select * from ".tablename('sz_yi_member_favorite')." where uniacid={$_W['uniacid']} and goodsid={$id} and openid='{$openid}'");
    if (empty($data)) {
        $data = array('uniacid' => $_W['uniacid'], 'openid' => $openid, 'goodsid' => $id,'deleted'=>0, 'createtime' => time());
        pdo_insert('sz_yi_member_favorite', $data);
        show_json(1, array('isfavorite' => true));
    } else if ($data['deleted']==0) {
        pdo_update('sz_yi_member_favorite', array('deleted' => 1), array('id' => $data['id']));
        show_json(1, array('isfavorite' => false));
    } else {
        pdo_update('sz_yi_member_favorite', array('deleted' => 0), array('id' => $data['id']));
        show_json(1, array('isfavorite' => true));
    }
}
