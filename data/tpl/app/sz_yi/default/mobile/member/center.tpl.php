<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>个人中心</title>
<style type="text/css">
    body {margin:0px; background:#f9f9f9; -moz-appearance:none;font: 12px/1.5 tahoma, "microsoft yahei", "\5FAE\8F6F\96C5\9ED1";}
    a {text-decoration:none;}
    .header {height: auto;padding:0px; background:#f15353;background-size: 100% 100%; padding:10px;position: relative;}
    .header .user {height:100px;text-align: center;}
    .header .user .user-head {height:48px; width:48px; background:#fff; border-radius:50%; border:2px solid #fff;margin: 6px auto;}
    .header .user .user-head img {height:48px; width:48px; border-radius:50%;}
    .header .user .user-info {height:48px; width:100%; float:left;color:#fff;}
    .header .user .user-info .user-name {height:20px; width:auto; font-size:14px; line-height:20px;}
    .header .user .user-info .user-other {height:24px; width:auto; font-size:12px;}
    .header .user-gold {height:35px; width:94%; padding:5px 3%; border-bottom:1px solid #e3e3e3; background:#fff; font-size:14px; line-height:35px;}
    .header .user-gold .title {height:35px; width:auto; float:left; color:#666;}
    .header .user-gold .num {height:35px; width:auto; float:left; color:#f90;}

    .header .user-gold .draw {width:80px; height:30px; background:#6c9; float:right;}
    .header .user .set {position: absolute;right: 10px;top: 10px;color: #fff;font-size: 14px}

    .header .user-op { height:35px; width:94%; padding:5px 3%; border-bottom:1px solid #e3e3e3; background:#fff; font-size:14px; line-height:35px; }
    .take {height:22px; width:auto; padding:0 12px; line-height:22px; font-size:12px; float:right;margin-top: 4px;border-radius:50px;color:#fff;border: 1px #fff solid}
.order {height:116px; width:100%; background:#fff; margin-top:10px; border-bottom:1px solid #e3e3e3;}
    .order_all {height:100px; width:100%; padding:14px 0px; color:#666;}
.order_all a i{font-size:20px;margin-bottom: 4px}
    .order_pub {height:38px; float:left; border-left:1px solid #eee; padding-top:2px; text-align:center; color:#8c8c8c; position:relative;font-size: 14px}
    .order_pub span {height:14px; background:#f30; border-radius:8px; position:absolute; top:0; right:6%;padding: 0 5px;font-size:12px; color:#fff; line-height:14px;}
    .order_1 {width:24%;}
    .order_2 {width:25%;}
    .order_3 {width:25%;}
    .order_4 {width:25%;}
    .list1 {height:44px; width:97%; background:#fff; padding:0 0 0 3%; margin-top:10px; border-top:1px solid #e3e3e3;font-size: 16px;line-height:44px; color:#242424;}
    .list1 i {font-size:16px; margin-right:6px;color: #929292;width: 20px;height: 20px;text-align: center;}
    .allorder {float:right; color:#909090; margin-right:10px; text-decoration:none;font-size: 12px}

    .cart {height:auto; width:100%; background:#fff; margin-top:10px; border-bottom:1px solid #e3e3e3;}
    .address {height:38px; width:100%; background:#fff; margin-top:10px; border-bottom:1px solid #e3e3e3; border-top:1px solid #e3e3e3; line-height:38px;}

    .copyright {height:40px; width:100%; text-align:center; line-height:40px; font-size:12px; color:#999; margin-top:10px;}

    span.count {float:right; margin-top:16px; margin-right: 4px; height:14px; background:#f30; border-radius:8px; font-size:12px; color:#fff; line-height:14px; text-align: center;padding: 0 5px}
</style>

<div id="container"></div>
<script id="member_center" type="text/html">
    <div class="header">
        <div class="user">
            <div class="user-head">
                <%if member.avatar.length > 0%>
                <img src="<%member.avatar%>" />
                <%else%>
                <img src="../addons/sz_yi/template/mobile/default/static/images/photo-mr.jpg" />
                <%/if%>
            </div>
                <div class="user-info">
                <div class="user-name"><%member.nickname%>[会员ID:<%member.id%>]</div>
                <div class="user-other" <?php  if(!empty($set['shop']['levelurl'])) { ?>onclick='location.href="<?php  echo $set['shop']['levelurl'];?>"'<?php  } ?>>
                [<%level.levelname%>]
                <%if member.level > 0 %> <span id="countdown_nums"></span><%/if%>

                 <?php  if(!empty($set['shop']['levelurl'])) { ?><i class='fa fa-question-circle' ></i><?php  } ?>
                    <%if shop_set.shop.isreferrer%>
                        [推荐人：<%referrer.realname%>]
                    <%/if%>
                </div>
            </div>
            <div class="set" onclick='location.href="<?php  echo $this->createMobileUrl('member/info')?>"'><i class="iconfont icon-cog"></i></div>
        </div>
    </div>
<div class="money-center">
    <div class="list1" style="margin-top:0px;border-bottom: 1px solid #ECECEC;">
            <i class="iconfont icon-virtual" style="float:left; line-height:44px;"></i>
            <span style="float:left;">我的帐号</span>
            <div class="allorder"><?php  if(empty($set['trade']['closerecharge'])) { ?>
            <a href="#" class="recharge-ybut" onclick="location.href='<?php  echo $this->createMobileUrl('member/recharge',array('openid'=>$openid))?>'">充值</a>
        <?php  } ?>
        <?php  if($member['credit2'] > 0 && empty($set['trade']['transfer'])) { ?>
            <a href="#" class="recharge-ybut" onclick="location.href='<?php  echo $this->createMobileUrl('member/transfer',array('openid'=>$openid,'op'=>'transfer'))?>'">转账</a>
        <?php  } ?> 
        <?php  if(!empty($yunbiset['isbot'])) { ?>
        <?php  if($member['virtual_currency'] > 0 && p('yunbi')) { ?>
            <a href="#" class="recharge-ybut" onclick="location.href='<?php  echo $this->createMobileUrl('member/transfer',array('openid'=>$openid,'op'=>'transfer', 'yunbi' => 1))?>'"><?php  if(!empty($shopset['yunbi_title'])) { ?><?php  echo $shopset['yunbi_title']?><?php  } else { ?>云币<?php  } ?>转账</a>
        <?php  } ?>
        <?php  } ?>
</div>
    </div>
    <ul>
        <li><a href="#"><span><%member.credit2%></span><br/><?php  if($shopset['credit']) { ?><?php  echo $shopset['credit'];?><?php  } else { ?>余额<?php  } ?></a></li>
        <?php  if($shopset['isyunbi']) { ?>
            <li><a href="#"><span><%member.virtual_currency%></span><br/><?php  echo $yunbi_title;?></a></li>
        <?php  } ?>
        <li><a href="#"><span><%member.credit1%></span><br/><?php  if($shopset['credit1']) { ?><?php  echo $shopset['credit1'];?><?php  } else { ?>积分<?php  } ?></a></li>
        <li><a href="#"><span><%counts.couponcount%></span><br/>优惠券</a></li>
    </ul>
</div>
<div class="order">
    <a href="<?php  echo $this->createMobileUrl('order')?>" class="a-center">
        <div class="list1" style="margin-top:0px;border-bottom: 1px solid #ECECEC;">
            <i class="fa fa-reorder" style="float:left; line-height:44px;"></i>
            <span style="float:left;">我的订单</span>
            <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i>
            <div class="allorder">查看我的全部订单</div>
        </div>
    </a>
    <div class="order_all">
        <a href="<?php  echo $this->createMobileUrl('order',array('status'=>0))?>"><div class="order_pub order_1" style="border:0px;"><i class="fa"></i><br>待付款<%if order.status0>0%><span><%order.status0%></span><%/if%></div></a>
        <a href="<?php  echo $this->createMobileUrl('order',array('status'=>1))?>"><div class="order_pub order_2"><i class="fa"></i><br>待发货<%if order.status1>0%><span><%order.status1%></span><%/if%></div></a>
        <a href="<?php  echo $this->createMobileUrl('order',array('status'=>2))?>"><div class="order_pub order_3"><i class="fa"></i><br>待收货<%if order.status2>0%><span><%order.status2%></span><%/if%></div></a>
        <a href="<?php  echo $this->createMobileUrl('order',array('status'=>4))?>"><div class="order_pub order_4"><i class="fa"></i><br>待退款<%if order.status4>0%><span><%order.status4%></span><%/if%></div></a>
    </div>
</div>
<?php  if(p('hotel')) { ?>
<%if memberhotel==1%>
<div class="order">
    <a href="<?php  echo $this->createMobileUrl('order',array('type'=>'hotel'))?>">
        <div class="list1" style="border-bottom: 1px solid #ECECEC;">
            <i class="fa fa-reorder" style="float:left; line-height:44px;"></i>
            <span style="float:left;">我的酒店订单</span>
            <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i>
            <div class="allorder">查看我的全部订单</div>
        </div>
    </a>
      <div class="order_all">
        <a href="<?php  echo $this->createMobileUrl('order',array('status'=>0,'type'=>'hotel'))?>"><div class="order_pub order_1" style="border:0px;"><i class="fa" ></i><br>待付款<%if orderhotel.status0>0%><span><%orderhotel.status0%></span><%/if%></div></a>
        <a href="<?php  echo $this->createMobileUrl('order',array('status'=>1,'type'=>'hotel'))?>"><div class="order_pub order_2"><i class="fa" ></i><br>待确认<%if orderhotel.status1>0%><span><%orderhotel.status1%></span><%/if%></div></a>
        <a href="<?php  echo $this->createMobileUrl('order',array('status'=>2,'type'=>'hotel'))?>"><div class="order_pub order_3"><i class="fa"></i><br>待入住<%if orderhotel.status6>0%><span><%orderhotel.status6%></span><%/if%></div></a>
        <a href="<?php  echo $this->createMobileUrl('order',array('status'=>4,'type'=>'hotel'))?>"><div class="order_pub order_4"><i class="fa"></i><br>待退款<%if orderhotel.status4>0%><span><%orderhotel.status4%></span><%/if%></div></a>
    </div>
</div>
<%/if%>
<?php  } ?>

<div class="cart new-ullist">
    <ul>
        <?php  if($hascom && is_weixin_show()) { ?>
            <li>
                <a href="<?php  echo $this->createPluginMobileUrl('commission')?>">
                    <i class="fa iconfont icon-sitemap" style="background:#f15353"></i><br/><?php  echo $pset['texts']['center']?>
                </a>
            </li>
        <?php  } ?>
        <?php  if($issupervisor) { ?>
            <li>
                <a href="<?php  echo $this->createPluginMobileUrl('verify', array('id' => $verify['id']))?>">
                    <i class="fa iconfont icon-sitemap" style="background:#f15353"></i><br/>店长中心
                </a>
            </li>
        <?php  } ?>
        <li>
        <a href="<?php  echo $this->createMobileUrl('member/info')?>">
        <i class="fa iconfont icon-user" style="background:#BC7BFF"></i><br/>我的资料
        </a>
        </li>
        <li>
        <a href="<?php  echo $this->createMobileUrl('shop/cart')?>">
        <i class="fa iconfont icon-cart" style="background:#43BFF1"></i><br/>购物车
        </a>
        </li>
        <?php  if(isset($set['trade']) && $set['trade']['withdraw']==1) { ?>
            <li>
                <a href="javascript:;" id="btnwithdraw">
                    <i class="fa iconfont icon-cny" style="background:#ff6e03"></i><br/><?php  if($shopset['credit']) { ?><?php  echo $shopset['credit'];?><?php  } else { ?>余额<?php  } ?>提现
                </a>
            </li>
        <?php  } ?>
        <li>
        <a href="<?php  echo $this->createMobileUrl('shop/favorite')?>">
        <i class="fa iconfont icon-heart" style="background:#58d5d9"></i><br/>我的收藏
        </a>
        </li>
        <li>
        <a href="<?php  echo $this->createMobileUrl('shop/history')?>">
        <i class="fa iconfont icon-paw" style="background:#ffb13b"></i><br/>我的足迹
        </a>
        </li>
        <?php  if($hascoupon) { ?>
        <li>
            <a href="<?php  echo $this->createPluginMobileUrl('coupon/my')?>">
                <i class="fa iconfont icon-tags" style="background:#ff3877"></i><br/>优惠券
            </a>
        </li>
        <?php  } ?>
<?php  if($open_creditshop) { ?>
        <li>
        <a href="#" onclick="location.href='<?php  echo $this->createPluginMobileUrl('creditshop')?>'">
        <i class="fa iconfont icon-database" style="background:#28c870"></i><br/>积分商城
        </a>
        </li>
<?php  } ?>
        <li>
            <a href='<?php  echo $this->createMobileUrl("member/easy_deliver_index")?>'>
                <i class="fa iconfont icon-user" style="background:#ff3877"></i><br/>服务中心
            </a>
        </li>
    </ul>

</div>

<?php  if($pindiana) { ?>
    <div class="cart">
        <a href="<?php  echo $this->createPluginMobileUrl('indiana/order')?>">
            <div class="list1">
                <i class="iconfont icon-duobaojilu"></i>夺宝记录
                <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right;line-height: 44px; "></i>
            </div>
        </a>
        <a href="<?php  echo $this->createPluginMobileUrl('indiana/order',array('op'=>'lucky'))?>">
            <div class="list1" style="margin:0px;">
                <i class="iconfont icon-xingyunjilu" ></i>幸运记录
                <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i>
            </div>
        </a>
        <a href="<?php  echo $this->createPluginMobileUrl('indiana/order_list')?>">
            <div class="list1" style="margin:0px;">
                <i class="iconfont icon-duobaodingdan" ></i>夺宝订单
                <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i>
            </div>
        </a>
    </div>
<?php  } ?>

<?php  if(p('recharge')) { ?>
    <div class="cart">
        <a href="<?php  echo $this->createPluginMobileUrl('recharge/detail')?>">
            <div class="list1">
                <i class="fa fa-mobile"></i>充值专区
                <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right;line-height: 44px; "></i>
            </div>
        </a>
    </div>
<?php  } ?>

<div class="cart">
    <?php  if(!$member['isbindmobile'] && is_weixin()) { ?>
    <a href="<?php  echo $this->createMobileUrl('member/bindmobile')?>"><div class="list1" style="margin:0px;"><i class="fa fa-mobile"></i>绑定手机<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>
    <?php  if($member['isbindmobile']) { ?>
    <a href="<?php  echo $this->createMobileUrl('member/editmobile')?>"><div class="list1" style="margin:0px;"><i class="fa fa-mobile"></i>修改绑定手机<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>
    <?php  if(p('supplier')) { ?>
        <?php  if($shopset['switch'] == 1 && empty($shopset['af_result']) && empty($issupplier)) { ?>
            <a href="<?php  echo $this->createPluginMobileUrl('supplier/af_supplier')?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-file" ></i>供应商申请<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
        <?php  } ?>
        <?php  if(!empty($issupplier) && $shopset['switch_centre'] == 1) { ?>
            <a href="<?php  echo $this->createPluginMobileUrl('supplier/orderj')?>"><div class="list1" style="margin:0px;"><i class="fa fa-road"></i>供应商中心<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
        <?php  } ?>
    <?php  } ?>
    <?php  if(p('channel')) { ?>
    <?php  if(!empty($ischannel)) { ?>
        <a href="<?php  echo $this->createPluginMobileUrl('channel/index')?>"><div class="list1" style="margin:0px; "><i class="iconfont icon-channel" ></i>渠道中心<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>
    <?php  if(empty($ischannel) && $channel_set['become_condition'] == 1 && $member['isagent'] == 1 && $member['status'] == 1) { ?>
        <a href="<?php  echo $this->createPluginMobileUrl('channel/af_channel')?>"><div class="list1" style="margin:0px;"><i class="fa fa-arrow-up"></i>申请渠道商<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>
    <?php  } ?>
    <?php  if($pluginbonus && is_weixin_show()) { ?>
        <?php  if(!empty($shopset['bonus_start'])) { ?>
            <?php  if(empty($bonus_set['bonushow'])) { ?>
                <a href="<?php  echo $this->createPluginMobileUrl('bonus/index')?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-fh"></i><?php echo empty($bonus_set['texts']['center']) ? "分红中心" : $bonus_set['texts']['center'];?><i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
            <?php  } ?>
        <?php  } ?>
    <?php  } ?>
    <?php  if(is_weixin_show()) { ?>
    <%if shopset.isreturn%>
    <a href="<?php  echo $this->createPluginMobileUrl('return/return_queue')?>"><div class="list1" style="margin:0px;"><i class="fa fa-piechart" ></i>全返明细<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a> 
    <%/if%>
    <?php  } ?>
        <?php  if($shopset['isbeneficence']) { ?>
            <a href="<?php  echo $this->createPluginMobileUrl('beneficence/beneficence')?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-donation" ></i><?php  echo $beneficencename;?><i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
           
        <?php  } ?> 
        <?php  if($shopset['isyunbi']) { ?>
            <a href="<?php  echo $this->createPluginMobileUrl('yunbi/yunbi_log',array('type'=>'1'))?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-coin" ></i><?php  echo $yunbi_title;?>明细<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
           
        <?php  } ?> 
    <?php  if($fund_start) { ?>
    <a href="<?php  echo $this->createMobileUrl('order/list', array('plugin' => 'fund'))?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-article"></i><?php  echo $fund_text;?><i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>
     <%if shopset.isarticle == 1%>
    <a href="<?php  echo $this->createPluginMobileUrl('article/article')?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-article"></i><%shopset.article_text%><i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  if(p('helper')) { ?>
    <a href="<?php  echo $this->createPluginMobileUrl('article/article', array('is_helper' => 1))?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-article"></i>帮助中心<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a> 
    <?php  } ?>
    <%/if%>
 
    <?php  if(p('merchant') && !empty($iscenter)) { ?>
    <a href="<?php  echo $this->createPluginMobileUrl('merchant/index')?>"><div class="list1" style="margin:0px; "><i class="fa fa-globe"></i>招商中心<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>

     <%if shopset.isranking == 1%>
    <a href="<?php  echo $this->createPluginMobileUrl('ranking/ranking')?>"><div class="list1" style="margin:0px; border-bottom:0px;"><i class="iconfont icon-ranking"></i><%shopset.rankingname%><i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a> 
    <%/if%>
    <?php  if(isset($allset['verify'])) { ?>
        <?php  if($allset['verify']['centershow'] == 1) { ?>
        <a href="<?php  echo $this->createPluginMobileUrl('verify/ranking')?>"><div class="list1" style="margin:0px; border-bottom:0px;"><i class="iconfont icon-ranking"></i>门店销售排行榜<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
        <?php  } ?>
    <?php  } ?>
    <?php  if(isset($set['trade']) && ($set['trade']['withdraw']==1 || empty($set['trade']['closerecharge']))) { ?>
    <a href="<?php  echo $this->createMobileUrl('member/log')?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-cny"></i><?php  if(isset($set['trade']) && $set['trade']['withdraw']==1) { ?><?php  if($shopset['credit']) { ?><?php  echo $shopset['credit'];?><?php  } else { ?>余额<?php  } ?>明细<?php  } else { ?>充值记录<?php  } ?>
            <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
   <?php  } ?>
   <?php  if(p('love')) { ?>
   <a href="<?php  echo $this->createMobileUrl('member/rechange_log')?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-cny"></i>分期记录
    <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>
    <?php  if(is_app()) { ?>

    <?php  if($app['accept'] == 1) { ?>

    <a href="<?php  echo $this->createMobileUrl('member/referral')?>"><div class="list1" style="margin:0px;"><i class="fa fa-barcode" ></i>我的推荐码<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>

    <?php  } ?>

    <a href="<?php  echo $this->createMobileUrl('member/messagelist')?>"><div class="list1" style="margin:0px;"><i class="fa fa-volume-up" ></i>订单通知<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>

    <a href="<?php  echo $this->createMobileUrl('member/pushlist')?>"><div class="list1" style="margin:0px;"><i class="fa fa-share-square-o" ></i>系统消息<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>

    <?php  } ?>
    <?php  if(p('discuz') && isset($uc['status']) && $uc['status'] == '1') { ?>
    <?php  if(!empty($ucUser)) { ?>
    <a href="<?php  echo $this->createPluginMobileUrl('discuz/uc', array('foo' => 'unbind', 'openid' => $openid))?>"><div class="list1" style="margin:0px;"><i class="fa fa-share-square-o" ></i>已绑定<?php  echo $uc['title'];?>账号(<?php  echo $ucUser['username'];?>), 点击解除绑定<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } else { ?>
    <a href="<?php  echo $this->createPluginMobileUrl('discuz/uc', array('foo' => 'bind', 'openid' => $openid))?>"><div class="list1" style="margin:0px;"><i class="fa fa-share-square-o" ></i>绑定<?php  echo $uc['title'];?>账号<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>
    <?php  } ?>
</div>
<?php  if($hascoupon) { ?>
<!--
<div class="cart">
     <?php  if($hascouponcenter) { ?>
    <a href="<?php  echo $this->createPluginMobileUrl('coupon')?>"><div class="list1" style="margin:0px; border-bottom:0px;"><i class="fa fa-tags"></i>领取优惠券 <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i> </div></a>
    <?php  } ?>
    <a href="<?php  echo $this->createPluginMobileUrl('coupon/my')?>"><div class="list1" style="margin:0px; border-bottom:0px;"><i class="fa fa-gift"></i>我的优惠券 <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i> <span class="count"><%counts.couponcount%></span></div></a>

</div>-->
<?php  } ?>
<!--
<div class="cart">
    <a href="<?php  echo $this->createMobileUrl('shop/cart')?>"><div class="list1" style="margin:0px; border-bottom:0px;"><i class="fa fa-shopping-cart"></i>我的购物车<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i> <span class="count"><%counts.cartcount%></span></div></a>
    <a href="<?php  echo $this->createMobileUrl('shop/favorite')?>"><div class="list1" style="margin:0px; border-bottom:0px;"><i class="fa fa-heart"></i>我的收藏<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i><span class="count"><%counts.favcount%></span></div></a>
    <a href="<?php  echo $this->createMobileUrl('shop/history')?>"><div class="list1" style="margin:0px; border-bottom:0px;"><i class="fa fa-list"></i>我的足迹<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
</div>-->
    <!--<?php  if(isset($set['trade']) && $set['trade']['withdraw']==1) { ?>
    <a href="javascript:;" id="btnwithdraw"><div class="list1" style="border-bottom: 0px;"><i class="fa fa-money"></i><?php  if($shopset['credit']) { ?><?php  echo $shopset['credit'];?><?php  } else { ?>余额<?php  } ?>提现<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
    <?php  } ?>
    <?php  if(isset($set['trade']) && ($set['trade']['withdraw']==1 || empty($set['trade']['closerecharge']))) { ?>
    <a href="<?php  echo $this->createMobileUrl('member/log')?>"><div class="list1" style="<?php  if(isset($set['trade']) && $set['trade']['withdraw']==1) { ?>margin:0px;<?php  } ?>"><i class="fa fa-file-text"></i><?php  if(isset($set['trade']) && $set['trade']['withdraw']==1) { ?>充值记录<?php  } ?>
            <i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
   <?php  } ?>
-->
<div class="cart">
<a href="<?php  echo $this->createMobileUrl('member/notice')?>"><div class="list1"><i class="iconfont icon-volume-up"></i>消息提醒设置<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; "></i></div></a>
<a href="<?php  echo $this->createMobileUrl('shop/address')?>"><div class="list1" style="margin:0px;"><i class="iconfont icon-street-view" ></i>收货地址管理<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
</div>
<?php  if(p('merchant') && !empty($ismerchant)) { ?>
    <a href="<?php  echo $this->createPluginMobileUrl('merchant/orderj')?>"><div class="list1" style="margin:0px; "><i class="fa fa-globe" style="color:#f10;"></i>招商员<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
<?php  } ?>
<?php  if($has_cashier) { ?>
    <a href="<?php  echo $this->createPluginMobileUrl('cashier/qrcode_list')?>"><div class="list1" style="margin:0px; "><i class="iconfont icon-web-price"></i>收银台<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
<?php  } ?>
<?php  if(!is_weixin()) { ?>
<div class="cart">
<a href="<?php  echo $this->createMobileUrl('member/logout')?>"><div class="list1"><i class="iconfont icon-sign-out"></i>退出<i class="fa fa-angle-right" style="color:#999; font-size:26px; float:right; line-height:44px;"></i></div></a>
</div>
<?php  } ?>

<div class="copyright">版权所有 ©<?php  if(empty($set['shop']['copyright'])) { ?><?php  echo $set['shop']['name'];?><?php  } else { ?><?php  echo $set['shop']['copyright'];?><?php  } ?> </div>
</script>
<script type="text/javascript">
    $(document).ready(function(){

    var d_time = '<?php  echo $dtimes;?>';
    var now_time = '<?php  echo $member['upgradeleveltime'];?>';
    var termtime = '<?php  echo $termtime;?>';
    prefix = "会员倒计时: ";
    function FreshTimes(){
        var lefttime =  parseInt(termtime) + parseInt(now_time) - parseInt(d_time);
        if (lefttime > 0) {
                var dd = parseInt(lefttime / 60 / 60 / 24, 10);//计算剩余的天数
                var hh = parseInt(lefttime  / 60 / 60 % 24, 10);//计算剩余的小时数
                var mm = parseInt(lefttime  / 60 % 60, 10);//计算剩余的分钟数
                var ss = parseInt(lefttime % 60, 10);//计算剩余的秒数
                dd = checkTime(dd);
                hh = checkTime(hh);
                mm = checkTime(mm);
                ss = checkTime(ss);
                $('#countdown_nums').html(prefix + "<span>" + dd + "</span>天<span>" + hh + "</span>时<span>" + mm + "</span>分<span>" + ss + "</span>秒");
        }else{
            //location.reload();
            return false;
        }
        d_time = parseInt(d_time) + parseInt(1);
    }

    var memberlevel = "<?php  echo $member['level'];?>";
    if (memberlevel > 0) {
        FreshTimes();
        var sh;
        sh=setInterval(FreshTimes,1000);
    }

    function checkTime(i)    
    {    
       if (i < 10) {    
           i = "0" + i;    
        }    
       return i;    
    } 
});


    require(['tpl', 'core'], function(tpl, core) {

        core.json('member/center',{},function(json){

           $('#container').html(  tpl('member_center',json.result) );
           var withdrawmoney = <?php echo empty($set['trade']['withdrawmoney'])?0:floatval($set['trade']['withdrawmoney'])?>;
           $('#btnwithdraw').click(function(){

               if(json.result.member.credit2<=0){
                   core.tip.show('无<?php  if($shopset['credit']) { ?><?php  echo $shopset['credit'];?><?php  } else { ?>余额<?php  } ?>可提!');
                   return;
               }
               if(withdrawmoney>0 && json.result.member.credit2<withdrawmoney){
                   core.tip.show('满' +withdrawmoney + "元才能提现!" );
                   return;
               }
               location.href = core.getUrl('member/withdraw');
           })

        },true);

    })
</script>
<?php  $show_footer=true?>
<?php  $footer_current='member'?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
