<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('member/center', TEMPLATE_INCLUDEPATH)) : (include template('member/center', TEMPLATE_INCLUDEPATH));?>
<title>订单详情</title>
<style type="text/css">
    body {margin:0px; background:#efefef; -moz-appearance:none;}


    .detail_topbar {height:45px; background:#5f6e8b; padding:15px;box-sizing: content-box;}
    .detail_topbar .ico {height:44px; width:40px; line-height:34px; float:left; font-size:26px; text-align:center; color:#fff;margin: 4px}
    .detail_topbar .tips {height:34px;  margin-left:10px; font-size:13px; color:#fff; line-height:17px;position:relative;}
    .detail_topbar .tips  span{line-height: 22px;}
    
    .detail_user,.store{box-sizing: content-box;height:40px;  background:#fff; padding:5px;}
    .detail_user .info .ico { float:left;  height:40px; width:30px; line-height:50px; font-size:26px; text-align:center; color:#666}
    .detail_user .info .info1,.store .info .info1{height:40px; width:100%; float:left;line-height: 40px;position: relative;}
/*    .detail_user .info .info1 .inner { margin-left:10px;margin-right:10px;overflow:hidden;}
    .detail_user .info .info1 .inner span,.store .info .info1 .inner span{display: inline-block;width: 48px;font-size: 12px;color: #828282;vertical-align: top;}
    .detail_user .info .info1 .inner i,.store .info .info1 .inner i{display: inline-block;font-size: 12px;color: #222;vertical-align: top;padding-left: 14px;font-style: normal;}*/
/*    .detail_user .info .info1 .inner .user {height:30px; width:100%; font-size:16px; color:#333; line-height:35px;overflow:hidden;}
    .detail_user .info .info1 .inner .address {height:20px; width:100%; font-size:14px; color:#999; line-height:20px;overflow:hidden;border: 0}
    .detail_user .info .ico2 {height:50px; width:30px;padding-top:15px; float:right; font-size:16px; text-align:right; color:#999;}*/
    
    .detail_user {height:54px;  background:#fff; padding:5px; border-bottom:1px solid #eaeaea;}
    .detail_user .info .ico { float:left;  height:50px; width:30px; line-height:50px; font-size:26px; text-align:center; color:#666}
    .detail_user .info .info1 {height:54px; width:100%; float:left;margin-left:-30px;margin-right:-30px;}
    .detail_user .info .info1 .inner { margin-left:30px;margin-right:30px;overflow:hidden;}
    .detail_user .info .info1 .inner .user {height:30px; width:100%; font-size:16px; color:#333; line-height:35px;overflow:hidden;}
    .detail_user .info .info1 .inner .address {height:20px; width:100%; font-size:14px; color:#999; line-height:20px;overflow:hidden;margin:0;border:0;}
    .detail_user .info .ico2 {height:50px; width:30px;padding-top:15px; float:right; font-size:16px; text-align:right; color:#999;}

    .detail_exp {height:42px; width:94%; background:#fff; padding:0px 3%; border-bottom:1px solid #eaeaea; line-height:42px; font-size:16px; color:#333;}
    .detail_exp .t1 {height:42px; width:auto; float:left;}
    .detail_exp .t2 {height:42px; width:auto; float:right;}
    .detail_exp .ico {height:42px; width:10%; float:right;text-align:right;color:#999; font-size:16px;margin-top:5px; }
    
    .detail_good {height:auto;padding:0 10px 10px;background:#fff;}
    .detail_good .ico {height:6px; /*width:10%; */line-height:36px; float:left; text-align:center;}
    .detail_good .shop {font-size:12px; color:#666;display: inline-block;padding-left: 15px}
    .detail_good .good {box-sizing: content-box;height:50px; width:100%; padding:10px 0px; border-bottom:1px solid #eaeaea;}
    .detail_good .img {height:50px; width:50px; float:left;}
    .detail_good .img img {height:100%; width:100%;}
    .detail_good .info {width:100%;float:left; margin-left:-50px;margin-right:-60px;}
    .detail_good .info .inner { margin-left:60px;margin-right:60px; }
    .detail_good .info .inner .name {height:32px; width:100%; float:left; font-size:12px; color:#555;overflow:hidden;}
    .detail_good .info .inner .option {height:16px; width:100%; float:left; font-size:12px; color:#888;overflow:hidden;word-break: break-all}
    .detail_good span { color:#666;}
    .detail_good .price { float:right;width:60px;;height:54px;margin-left:-60px;;}
    .detail_good .price .pnum { height:20px;width:100%;text-align:right;font-size:14px; }
    .detail_good .price .num { height:20px;width:100%;text-align:right;}
    
    .detail_price {height:auto; padding:10px 14px;background:#fff;padding-top:0; }
    .detail_price .price {height:130px; width:fit-content;display: inline-block; }
    .detail_price .price .line {/**padding-left: 70%;**/height:26px; width:32.5%; font-size:13px; color:#000; line-height:10px;display: inline-block;text-align:left;}
    .detail_price .price .line span {height:26px; width:auto; /**float:right;**/}
    
   
    .detail_pay {height:60px; width:100%; background:#fff; padding:0px 1%; margin-top:30px; border-top:1px solid #eaeaea;position:absolute;bottom:0px}
    .detail_pay span {height:60px; width:auto; margin-right:16px; float:right; line-height:60px; color:#ff771b; font-size:14px;}
    .detail_pay .paysub {height:36px; width:auto;margin-left:5px; background:#ff771b; padding:0px 10px; margin-top:10px; border-radius:5px; color:#fff; line-height:36px; float:right;}
    
    .detail_pay .paysub1 {height:36px; width:auto; margin-left:5px;background:#fff; padding:0px 10px; margin-top:10px; border-radius:5px; color:#000; line-height:36px; float:right;border:1px solid #000;font-size:13px;}
       
       
    .chooser {height: 100%; width: 100%; background:#efefef; position: fixed; top: 0px; right: -100%; z-index: 1;}
    .chooser .address {height:50px; width:94%; background:#fff; padding:10px 3%; border-bottom:1px solid #eaeaea;}
    .chooser .address .ico {height:50px; width:10%; line-height:50px; float:left; font-size:20px; text-align:center; color:#999;}
    .chooser .address .info {height:50px; width:77%; margin-right:3%; float:left;}
    .chooser .address .info .name {height:28px; width:100%; font-size:16px; color:#666; line-height:28px;}
    .chooser .address .info .addr {height:22px; width:100%; font-size:14px; color:#999; line-height:22px;}
    .chooser .address .edit {height:50px; width:10%; float:left; }

    .chooser .add_address {height:44px; width:94%; background:#fff; padding:0px 3%; border-bottom:1px solid #eaeaea; line-height:44px; font-size:16px; color:#666;}
    
    .detail_nav { height:30px; width:94%;padding:10px;}
    .detail_nav .nav { padding:2px 5px 2px 5px;; border:1px solid #5f6e8b; color:#5f6e8b; background:#fff; float:left; margin-left:10px;}
    .detail_nav .selected { border:1px solid #ff6600; color:#ff6600; }
    
.address_main {height:100%; width:94%; background:#fff; padding:0px 3%;  position: fixed; top: 0px; right: -100%; z-index: 1;}
.address_main .line {height:44px; width:100%; border-bottom:1px solid #f0f0f0; line-height:44px;}

.address_main .line input {float:left; height:44px; width:100%; padding:0px; margin:0px; border:0px; outline:none; font-size:16px; color:#666;padding-left:5px;}
.address_main .line select  { border:none;height:25px;width:100%;color:#666;font-size:16px;}
.address_main .address_sub1 {height:44px; width:94%; margin:14px 3% 0px; background:#ff4f4f; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
.address_main .address_sub2 {height:44px; width:94%; margin:14px 3% 0px; background:#ddd; border-radius:4px; text-align:center; font-size:18px; line-height:44px; color:#666; border:1px solid #d4d4d4;}
select { width:80px;height:38px;position:absolute;left:0; -webkit-appearance: none;background:#fff; -webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;};
 
.stores {overflow:hidden;background:#fff;}
/*.store {height:65px;  background:#fff; padding:5px; border-bottom:1px solid #eaeaea;}*/
.store .info .ico { float:left;  height:50px; width:30px; line-height:30px; font-size:16px; text-align:center; color:#666}
.store .info .info1 .inner { margin-left:10px;margin-right:10px;overflow:hidden;}

.store .info .info1 .inner .user {height:25px; width:100%; font-size:14px; color:#333; line-height:25px;overflow:hidden;}
.store .info .info1 .inner .tel {height:20px; width:100%; font-size:13px; color:#999; line-height:20px;overflow:hidden;}
.store .info .info1 .inner .address {height:20px; width:100%; font-size:13px; color:#999; line-height:20px;overflow:hidden;}
.store .info .ico2 {height:40px; width:30px;float:right; font-size:24px; text-align:center; color:#ccc;}
.store .info .ico3 {height:40px; width:30px;float:right; font-size:24px; text-align:center; color:#ccc;} 
.store_title {height:44px; width:98%; overflow: hidden; background:#fff; padding:0px 5px; margin:0 auto; border-bottom:1px solid #eaeaea; border-top:1px solid #eaeaea; line-height:44px; color:#666; font-size:14px;} 
.store_more {height:20px;  background:#fff; font-size:14px; color:#999; line-height:20px; padding:5px; border-bottom:1px solid #eaeaea; text-align: center;}
.page_topbar .nav { position:absolute;right:5px;;color:#333;}

.detail_good .text { padding:10px; color:#666;font-size:13px;}


    .diyform_info {height:auto;background:#fff;padding: 20px 0;border-top: 1px solid #e5e5e5;width: 98%;margin: 0 auto}
    .diyform_info .dline {margin:4px 5px; height:24px;line-height:24px; color:#666;}
    .diyform_info .dline .dtitle {height:24px; width:90px; line-height:24px; color:#828282; float:left; font-size:12px;}
    .diyform_info .dline .dinfo { width:100%;float:right;margin-left:-90px; position: relative; font-size:14px; color:#666; line-height:24px; height:24px; }
    .diyform_info .dline .dinner { margin-left:70px;font-size: 12px}
    .diyform_info .dline1  { height:auto;overflow:hidden;}
     .diyform_info .dline2 .dinfo img  { margin-top:5px;}
   .diyform_info .dline1 .dinfo { height:auto; line-height:35px;}
   .diyform_info1 { border:none; margin-top:0px; border:1px solid #efefef; border-top:none;  }
   .diyform_info1 .dline { margin:0;}
   .diyform_info1 .dline .dtitle { padding-left:10px;width:80px;font-size:13px;}
   .diyform_info1 .dline .dinner { font-size:13px;}
   .diyform_info .btn { text-decoration: none;  display: block; background:#f0f0f0; width:100%; text-align: center; color: #999;padding:3px; border-radius:1px; line-height:25px;  }
.goods-list {color: #828282 !important;display: inline-block;}
.detail-title{ width: 100%;box-sizing: content-box;height: 20px;padding-bottom: 15px;padding-top: 15px;font-size: 12px;border-top: 1px solid #e5e5e5;padding-left: 5px}
.good-info{ width: 100%;height: 122px;overflow: hidden;max-height:auto;}
.the-goods-photo {text-align: left;padding-bottom: 15px;vertical-align: top;}
.goods-img-title {margin-top: 15px;margin-left: 20px;}
.goods-list-tb{width: 100%;border-collapse: collapse;border-spacing: 0;border: 1px solid #e5e5e5;}
.goods-list-tb thead tr {
    height: 30px;
    border: 1px solid #e5e5e5;
    border-left: none;
    border-right: none;
    background-color: #f9f9f9;
}
.goods-list-tb thead tr th {
    font-weight: normal;
    color: #000;
    display: table-cell;
    text-align: center;font-size: 13px;
}
.goods-list-tb tbody tr td{text-align: center;color: #000;font-size: 13px;padding-bottom:15px;}
.goods-img-title .pic {
    height: 58px;
    width: 58px;
    border: 1px solid #e5e5e5;
    float: left;
    overflow: hidden;
    display: inline-block;
}
.goods-img-title .pic img {
    height: 58px;
    width: 58px;
    overflow: hidden;
}
.goods-img-title .title {
    float: left;
    margin-left: 15px;
    display: inline-block;
}
.goods-img-title .title p {
    padding-bottom: 5px;
    text-align: left;
}
.goods-img-title .title .goods-title {
    /*max-width: 260px;*/
    max-width: 450px;
    height: 36px;
    display:block;
    color: #000;
    font-size: 13px;
    padding: 10px 0;

}
.goods-list-tb tbody tr td .prop{margin-top: 15px}
.order_detail_prop {margin-top: 15px;text-align: center;}
.right-zt{ font-size: 18px;line-height: 44px;position:relative;}
.right-ico{position: absolute;right: 0;top: 0}

/* 财款信息 */
.floort{
    width: 100%;
    /* background: #66ccff; */
    height: 200px;
    margin:20px 0;
}
.floort .left{
    width: 47%;
    height: 200px;
    border: 1px solid #ddd;
    float: left;
    padding:10px 0;
    margin:0 7px;
}
.floort .left .top{
    width: 100%;
    height: 30px;
    padding:0 15px;
    border-bottom: 1px solid #ddd;
}
.floort .left .content{
    width: 100%;
    padding:0 15px;
    display: flex;
    flex-direction: column;
    margin-top:10px;
}
.floort .left .content .text{
    margin-bottom:10px;
    font-size: 14px;
    color: #666;
}
/* 财款信息end */

/* 发货信息 */
.floort .right{
    width: 50%;
    height: 200px;
    border: 1px solid #ddd;
    /* border-left: 0; */
    float: left;
    padding:10px 0;
    margin:0 7px;
}
.floort .right .top{
    width: 100%;
    height: 30px;
    padding:0 15px;
    border-bottom: 1px solid #ddd;
}
.floort .right .content{
    width: 100%;
    padding:0 15px;
    display: flex;
    flex-direction: column;
    margin-top:10px;
}
.floort .right .content .text{
    margin-bottom:10px;
    font-size: 14px;
    color: #666;
}
/* 发货信息end */


/* 订单编号 */
/* 卖家 */
.floort2{
    width: 98.5%;
    /* background: #66ccff; */
    height: 182px;
    /* margin-bottom:20px 0; */
    margin: 0 auto;
    border:1px solid #ddd;
}
.floort2 .left{
    width: 50%;
    height: 40px;
    padding:0 15px;
    float: left;
}
.floort2 .left .top{
    width: 100%;
    line-height: 40px;
}
.floort2 .left .content{
    width: 100%;
    display: flex;
    flex-direction: column;
}
.floort2 .left .content .text{
    margin-bottom:10px;
}
/* 卖家end */

/* 买家 */
.floort2 .right{
    width: 50%;
    height: 40px;
    padding:0 15px;
    float: left;
}
.floort2 .right .top{
    width: 100%;
    line-height: 40px;
}
.floort2 .right .content{
    width: 100%;
    display: flex;
    flex-direction: column;
}
.floort2 .right .content .text{
    margin-bottom:10px;
}
/* 买家 */
/* 订单编号end */

.msg{
    font-weight: 400;
    font-size: 13px;
}
.x-div:before{
    background: #fff;
    content: "";
    position: absolute;
    left: -34%;
    top: 34%;
    width: 2px;
    height: 12px;
}
</style>
<div id="container" class="rightlist"></div></div>

<script id='tpl_detail' type='text/html'>
<div class="page_topbar">
    <!-- <a href="<?php  echo $this->createMobileUrl('order')?>" class="back"><i class="fa fa-angle-left"></i></a> -->
    <%if order.status==1 && order.isverify=='1' && order.verifyied!='1'%><a href="javascript:;" class="btn" onclick="VerifyHandler.verify('<?php  echo $_GPC['id'];?>')"><i class="fa fa-qrcode"></i></a><%/if%>
    <div class="title">订单详情</div>
</div>
<div class="detail_topbar">
    <div class="ico"><i class="fa fa-file-text-o"></i></div>
    <div class="tips">     
        <div class="fl">
            <span>订单金额: ￥<%order.price%>&nbsp;+ 运费：￥<%order.olddispatchprice%><span><br/>
            <span style="background:#f60;padding:4px 8px;">应付：(CNY) <%order.price%>元</span>
            <span style="background:hsl(194, 66%, 61%);padding:4px 8px;">未付：(CNY) <%order.price%>元</span>
            <span style="background:#46CB2F;padding:4px 8px;">已付：(CNY) 0.00  元</span>
            <span style="background:#1ab394;padding:4px 8px;">付款方式：
                <?php  if($orderisyb['paytype'] == 0) { ?>未支付<?php  } ?>
                <?php  if($orderisyb['paytype'] == 1) { ?>余额支付<?php  } ?>
                <?php  if($orderisyb['paytype'] == 11) { ?>后台付款<?php  } ?>
                <?php  if($orderisyb['paytype'] == 21) { ?>微信支付<?php  } ?>
                <?php  if($orderisyb['paytype'] == 22) { ?>支付宝支付<?php  } ?>
                <?php  if($orderisyb['paytype'] == 23) { ?>银联支付<?php  } ?>
                <?php  if($orderisyb['paytype'] == 3) { ?>货到付款<?php  } ?>
                <?php  if($orderisyb['paytype'] == 29) { ?>paypal支付<?php  } ?>
				<?php  if($orderisyb['paytype'] == 35) { ?>通联微信支付<?php  } ?>
            </span>
            <!--<span>运费: ￥<%order.dispatchprice%><span><br/>-->
            <!--<span>买家: busayarueangrat Piyasak<span>-->
        </div>
        
        <div class="fr right-zt x-div">
            <%if order.status==0 && order.paytype!=3%>等待付款<%/if%>
            <%if order.paytype==3 && order.status==0%>货到付款，等待发货<%/if%>
            <%if order.status==1%>买家已付款<%/if%>
            <%if order.status==2 %>卖家已发货<%/if%>
            <%if order.status==3%>交易完成<%/if%>
            <%if order.status==-1%>交易关闭<%/if%>
        </div>
    </div>
</div>
  <%if show==1%>
    <%if order.isverify==1 || order.virtual!='0'%>
    
    <div class="detail_user">
        <div class="info" >
            <!--<div class="ico"><i class="fa fa-user"></i></div>-->
                <div class='info1'>
                     <div class='inner'>
                        <span>收货信息</span><i><%carrier.carrier_realname%></i><i><%carrier.carrier_mobile%></i>
                     </div>
                 </div>
            </div>
          </div>
    </div>
    <%/if%>
<%/if%>

    <%if order.isverify==1%>
<!--     <div class="store_title" onclick="showStores(this)" show="1" >适用的门店
         <i class="fa fa-angle-down" style="float:right; line-height:44px; font-size:26px;"></i>
    </div>
      <div class="stores">
      <%each stores as store index%>
     <%if index<=1%>
     <div class="store" >
             <div class="info"> -->
             <!--<div class="ico"><i class="fa fa-building-o"></i></div>-->
            <!--  <div class='info1'>
                 <div class='inner'>
                     <span>收货信息</span>
                     <i><%store.storename%></i><i><%store.address%></i><i><%store.tel%></i>
                 </div>
                 <div class="right-ico">
                 <a href="http://api.map.baidu.com/marker?location=<%store.lat%>,<%store.lng%>&title=<%store.storename%>&name=<%store.storename%>&content=<%store.address%>&output=html"><div class="ico2"><i class='fa fa-map-marker'></i></div></a>
                <a href="tel:<%store.tel%>"><div class="ico3" ><i class='fa fa-phone'></i></div></a>
                </div>
             </div>
             
        </div>
       </div>
     <%/if%>
     <%/each%> 
         <div id='store_more' style="display:none">
      <%each stores as store index%>
     <%if index>1%>
     <div class="store" >
             <div class="info"> -->
             <!--<div class="ico"><i class="fa fa-building-o"></i></div>-->
             <!-- <div class='info1'>
                 <div class='inner'>
                     <span>收货信息</span>
                     <i><%store.storename%></i><i><%store.address%></i><i><%store.tel%></i>
                     <div class="right-ico">
                     <a href="http://api.map.baidu.com/marker?location=<%store.lat%>,<%store.lng%>&title=<%store.storename%>&name=<%store.storename%>&content=<%store.address%>&output=html"><div class="ico2"><i class='fa fa-map-marker'></i></div></a>
                     <a href="tel:<%store.tel%>"><div class="ico3" ><i class='fa fa-phone'></i></div></a>
                     </div>
                 </div>
             </div>
             
        </div>
       </div>
     <%/if%>
     <%/each%> 
         </div>
    <%if stores.length>=3%>
     <div class="store_more" onclick="$('#store_more').show();$(this).remove()">显示更多 <i class="fa fa-angle-double-down"></i></div>
     <%/if%> 
      </div> -->
         <%if order.dispatchtype==1%>
             <div class="detail_user">
                 <input type='hidden' id='carrierindex' value='0' />
                <div class="info" id='carrier_select' >
                    <!--<div class="ico"><i class="fa fa-map-marker"></i></div>-->
                        <div class='info1'>
                             <div class='inner'>
                                    <div class="user">自提地点：<span id='address_realname'><%carrier.realname%></span>(<span id='address_mobile'><%carrier.mobile%></span>)</div>
                                    <div class="address"><span id='address_address'><%carrier.address%></span></div>
                             </div>
                     </div>
                </div>

             </div>
             <div class="detail_user">
                <div class="info" id='carrier_select' >
                    <!--<div class="ico"><i class="fa fa-map-marker"></i></div>-->
                        <div class='info1'>
                             <div class='inner'>
                                    <div class="user">提货人姓名：<span id='address_realname'><%carrier.carrier_realname%></span></div>
                                    <div class="user">提货人手机：<span id='address_mobile'><%carrier.carrier_mobile%></span></div>
                             </div>
                     </div>
                </div>
             </div>      
         <%/if%>
         
         <%if order.addressid!=0%>
            <div class="detail_user">
                <input type='hidden' id='addressid' value='<%address.id%>' />
                <div class="info">
                    <div class="ico"><i class="fa fa-map-marker"></i></div>
                     <div class='info1'>
                             <div class='inner'>
                                    <div class="user">收件人：<span id='address_realname'><%address.realname%></span>(<span id='address_mobile'><%address.mobile%></span>)</div>
                                    <div class="address"><span id='address_address'><%address.address%></span></div>
                             </div>
                         </div>
               
                </div>
            </div>
         <%/if%>

   
    <%else%>

         
            <%if order.addressid!=0%>
                <!--<div class="detail_user">
                    <input type='hidden' id='addressid' value='<%address.id%>' />
                    <div class="info">
                        <div class="ico"><i class="fa fa-map-marker"></i></div>
                         <div class='info1'>
                                 <div class='inner'>
                                        <div class="user">收件人：<span id='address_realname'><%address.realname%></span>(<span id='address_mobile'><%address.mobile%></span>)</div>
                                        <div class="address"><span id='address_address'><%address.address%></span></div>
                                 </div>
                             </div>
                   
                    </div>
                </div>-->
             <%/if%>
                
 

    <%/if%>

    
    <div class='floort' style="display:none;">
        <!-- 财款信息 -->
        <div class="left">
            <div class="top">财款信息</div>
            <div class="content">
                <!--币种 xxx 元-->
                <div class="text">应付：(CNY) 169.00元</div>
                <div class="text">未付：(CNY) 169.00元</div>
                <div class="text">已付：(CNY) 0.00  元</div>
                <div class="text">付款方式：货到付款</div>
            </div>
        </div>
        <!-- 财款信息end -->
        
        <!-- 发货信息 -->
        <div class="right">
            <div class="top">发货状态</div>
            <div class="content">
                <div class="text">状态：已发货</div>
                <div class="text">约定发货时间：2021-07-17之内发货</div>
                <div class="text">运输方式：海运</div>
                <div class="text">运单号：9919820210714000</div>
            </div>
        </div>
        <!-- 发货信息end -->
    </div>

    <!-- 订单编号 -->
    <div class='floort2' style="margin-top:7px;">
        
        <!--<div class="top">订单详情</div>-->

        <!-- 卖家 -->
        <div class="left" style="display:none;">
            <div class="top">[中国]卖家：</div>
            <div class="content">
                <div class="text" style="font-weight:bold;"><?php  echo $seller['company_name'];?></div> 
                <!--<div class="text">联系人姓名：<?php  echo $seller['user_name'];?></div> -->
                <!--<div class="text" style="height:40px;">住所地址：<span class="msg"><?php  echo $seller['address'];?></span></div>-->
                <div class="text">注册编号：<?php  echo $seller['company_num'];?></div>
                <div class="text">联系电话：<span class="msg"><?php  echo $seller['user_tel'];?></span></div> 
                <div class="text">联系邮箱：<span class="msg"><?php  echo $seller['user_email'];?></span></div> 
                <!--<div class="text">状态：已发货</div>
                <div class="text">约定发货时间：2021-07-17之内发货</div>
                <div class="text">运输方式：海运</div>
                <div class="text">运单号：9919820210714000</div>-->
            </div>
        </div>
        <!-- 卖家end -->
        
       <!-- 买家 -->
        <div class="right" style="display:none;">
            <div class="top">[香港]买家：</div>
            <div class="content">
                <div class="text" style="font-weight:bold;"><?php  echo $user['realname'];?></div> 
                <!--<div class="text" style="height:40px;">住所地址：<span class="msg"><?php  echo $user['address'];?></span></div> -->
                <div class="text">注册编号：<?php  echo $user['company_code'];?></div>
                <div class="text">联系电话：<span class="msg"><?php  echo $user['mobile'];?></span></div> 
                <div class="text">联系邮箱：<span class="msg"><?php  echo $user['email'];?></span></div> 
                
                <!--<div class="text">状态：已委托收货</div>
                <div class="text">约定收货时间：2021-07-17之内收货</div>
                <div class="text">备注：已委托香港跨境倉有限公司收货</div>-->
                <!--<div class="text">运单号：9919820210714000</div>-->
            </div>
        </div>
       <!-- 买家end -->
       
       <!--中国境内-->
       <div class="left">
            <div class="top" style="font-weight:650;">中国境内</div>
            <div class="content">
                <div class="text">生产销售：<?php  echo $seller['company_name'];?></div> 
                <!--<div class="text"><?php  echo $seller['user_name'];?></div> -->
                
                <div class="text">信用代码：<?php  echo $seller['creditNo'];?></div>
                <div class="text">联系信息：<span class="msg"><?php echo !empty($seller['user_email'])?$seller['user_email']:$seller['user_tel'];?></span></div> 
                <div class="text" style="height:40px;">住所地址：<span class="msg"><?php  echo $seller['address'];?></span></div>
            </div>
        </div>
       <!--中国境内end-->
       
       <!--中国境外-->
       <div class="left">
            <div class="top" style="font-weight:650;">中国境外</div>
            <div class="content">
                <div class="text">货物订购：[<?php  echo $user['country'];?>]<?php  echo $user['realname'];?></div> 
                <div class="text">注册编码：<?php  echo $user['company_code'];?></div>
                <div class="text">联系信息：<span class="msg"><?php echo !empty($user['email'])?$user['email']:$user['mobile']?></span></div> 
                <div class="text" style="height:40px;">住所地址：[<?php  echo $user['country'];?>]<span class="msg"><?php  echo $user['address'];?></span></div>
            </div>
        </div>
       <!--中国境外end-->
    </div>
    
    <!-- 报关单位&付款单位 -->
    <div class='floort2' style="margin-top:7px;height:210px;padding-top: 10px;">
       <!--报关单位-->
       <div class="left">
            <!--<div class="top">中国境内</div>-->
            <div class="content">
                <div class="text">报关单位：<?php  echo $declare['decl_name'];?></div> 
                <div class="text">信用代码：<?php  echo $declare['creditNo'];?></div>
                <div class="text">申报海关：<?php  echo $declare['customs_name'];?></div> 
                <div class="text">出境海关：<?php  echo $declare['port_name'];?></div>
                <div class="text">监管方式：<?php  echo $declare['trade_way'];?></div>
                <div class="text">贸易国地：<?php  echo $declare['country_name'];?></div>
            </div>
        </div>
       <!--报关单位end-->
       
       <!--付款单位-->
       <div class="left">
            <div class="content">
                <div class="text">付款单位：[<?php  echo $user['country'];?>]<?php  echo $user['company_name'];?>  <?php  if($user['realname']==$user['company_name']) { ?>[自付]<?php  } else { ?>[代付]<?php  } ?></div> 
                <div class="text">成交方式：<?php  echo $trade_pdf['trans_mode'];?></div>
                <div class="text">付款方式：<?php  echo $trade_pdf['pay_term'];?></div> 
                <div class="text">成交币制：<?php  echo $trade_pdf['transaction_currency'];?></div>
                <div class="text">付款币制：<?php  echo $trade_pdf['payment_currency'];?></div>
                <div class="text">约定汇率：<?php  echo $trade_pdf['exchange_rate'];?></div>
            </div>
        </div>
       <!--付款单位end-->
    </div>
    
    <div class='floort2' style="margin-top:7px;height: 195px;">

        <!-- 发货单位 -->
        <div class="left">
            <div class="top">发货单位：[<?php  echo $deliver['country'];?>]<?php  echo $deliver['company_name'];?></div>
            <div class="content">
                <div class="text">信用代码：<?php  echo $deliver['creditNo'];?></div>
                <div class="text">运输方式：<?php  echo $declare['transport'];?></div>
                <div class="text">船航车次：<?php  echo $declare['traf_name'];?>/<?php  echo $declare['voyage_no'];?></div>
                <div class="text">离境口岸：<?php  echo $declare['port_name'];?></div>
            </div>
        </div>
        <!-- 发货单位end -->
        
       <!-- 收货单位 -->
        <div class="left">
            <div class="top">收货单位：[<?php  echo $receive['country'];?>]<?php  echo $receive['company_name'];?></div>
            <div class="content">
                <div class="text">合同编号:<?php  echo $trade_po_pdf['pdf_sn'];?></div> 
                <div class="text">提运单号:<?php  echo $trade_order['logistics_no'];?></div> 
                <div class="text">运抵国地:<?php  echo $declare['country_name'];?></div> 
                <div class="text">指运港口:<?php  echo $declare['pod_name'];?></div> 
            </div>
        </div>
       <!-- 收货单位end -->

    </div>
    <!-- 订单编号end -->

    <!-- 运输条款 -->
    <div class='floort2' style="height:180px;margin-top:7px;border:0;display:none;">
        <table border="1" style="width: 100%;border: solid #ddd 1px;">
            <tbody>
                <tr>
                  <th style="padding: 10px 0px 10px 10px;">运输方式:&nbsp;<span class="msg"><?php  echo $trade_pdf['shipping_type'];?><span></th>
                  <th style="padding: 10px 0px 10px 10px;">发货日期:&nbsp;<span class="msg"><?php  echo $trade['logistics_date'];?><span></th>
                  <th style="padding: 10px 0px 10px 10px;">交易方式:&nbsp;
                  <!--$trade_pdf['trans_mode']-->
                  <span class="msg">
                    <?php  echo $transaction_mode['trans_mode'];?>
                  <span></th>
                  <th style="padding: 10px 0px 10px 10px;">运费:&nbsp;<span class="msg"> ￥<%order.olddispatchprice%><span></th>
                </tr>
                <tr>
                    <th colspan="2" style="padding: 10px 0px 10px 10px;">发货方式:&nbsp;<span class="msg"><?php  echo $fahuo;?><span></th>
                    <th colspan="2" style="padding: 10px 0px 10px 10px;">收货方式:&nbsp;<span class="msg"><?php  echo $shouhuo;?><span></th>
                </tr>
                <tr>
                <!--$user['address']-->
                    <th colspan="4" style="padding: 10px 0px 10px 10px;">收货地址:&nbsp;<span class="msg">[<?php  echo $yundi['code_name'];?>]<?php  echo $orderisyb['address'];?></span></th>
                </tr>
                <tr>
                    <th colspan="2" style="padding: 10px 0px 10px 10px;">物流运单:&nbsp;<span class="msg"><?php  echo $trade_order['logistics_no'];?><span></th>
                    <th colspan="2" style="padding: 10px 0px 10px 10px;">物流状态:&nbsp;<span class="msg">已代收货<span></th>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- 运输条款end -->
    
<!--<span>diyform</span>-->
<?php  if($diyform_flag == 1 && count($goods)==1) { ?>
<?php  $datas = iunserializer($goods[0]['diyformdata'])?>
<div class="diyform_info">
<?php  if(is_array($goods[0]['diyformfields'])) { foreach($goods[0]['diyformfields'] as $value) { ?>
<div class='dline <?php  echo $value['tp_css'];?>'>
        <div class='dtitle'><?php  echo $value['tp_name'];?>：</div>
        <div class='dinfo'>
			<div class='dinner'>
		           <?php  echo $value['tp_value'];?>
			</div>
        </div>
</div>
<?php  } } ?>
</div>
<?php  } ?>	
<div class="detail_good">
    <!--<div class="ico"><i class="fa fa-gift" style="color:#666; font-size:20px;"></i></div>-->
    <div class="detail-title">
        <span class="goods-list" style="color:#000 !important;font-size:13px;text-align:center;border-bottom:1px solid #666;width:100%;">商品清单</span>
         <!--<span class="shop">商家：<%set.name%></span> -->
    </div>
    
    <%each goods as g%>
    <div class="good-info">
        <table class="goods-list-tb">
            <thead>
                <tr>
                    <th width="56%" style="color:#000 !important;font-size:14px;">商品信息</th>
                    <th width="20%">规格</th>
                    <th width="8%">单价</th>
                    <th width="8%">数量</th>
                    <th width="8%">总计</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="the-goods-photo">
                        <div class="goods-img-title">
                            <a target="_blank" class="pic" href="#"><img src="<%g.thumb%>"> </a>
                            <div class="title">
                                <p><a class="goods-title"  href="#"><%g.title%></a></p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <%if g.optionid!='0'%>规格:  <%g.optiontitle%><%else%><%g.optiontitle%><%/if%>
                    </td>
                    <td>
                        ￥<%g.price%>
                    </td>
                    <td>
                        ×<%g.total%>
                    </td>
                    <td>
                        ￥<%g.sum_price%>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
     <!--<div class="good">
            <div class="img"  onclick="location.href='<?php  echo $this->createMobileUrl('shop/detail')?>&id=<%g.goodsid%>'"><img src="<%g.thumb%>"/></div>
            <div class='info' onclick="location.href='<?php  echo $this->createMobileUrl('shop/detail')?>&id=<%g.goodsid%>'">
                <div class='inner'>
                       <div class="name"><%g.title%></div>     
                       <div class='option'><%if g.optionid!='0'%>规格:  <%g.optiontitle%><%/if%></div>
                </div>
            </div>
            <div class="price">
                <div class='pnum'><span class='marketprice'>￥<%g.price%></span></div>
                <div class='pnum'><span class='total'>×<%g.total%></span></div>
            </div>
        </div>-->
	<?php  if(count($goods)>1) { ?>
	<%if g.diyformfields.length>0%>
	 
	<div class="diyform_info diyform_info1">
	        <a href='javascript:;' class='btn' onclick='showDiyInfo(this)' hide='1'>查看提交的资料</a>
	        <div style='display:none'>
		<%each g.diyformfields as v %>
			<div class='dline  <%=v.tp_css%>'>
				 <div class='dtitle'><%=v.tp_name%>：</div>
				 <div class='dinfo'>
					 <div class='dinner'>
						 <%=v.tp_value%>
					 </div>
				 </div>
		   </div> <%/each%>
		 </div>
	      
          </div>
		<%/if%>
		
		<?php  } ?>

    <%/each%>
</div> 
 <%if order.virtual_str!=''%>
<div class="detail_good" style='margin-bottom:10px;'>
    <div class="ico"><i class="fa fa-cubes" style="color:#666; font-size:20px;"></i></div>
    <div class="shop">发货信息</div>
    <div class='text'><%=order.virtual_str%></div>
</div> 
 
 <%/if%>
<div class="detail_price" >
    <input type="hidden" id="weight" value="<%weight%>" />
    <div class="title" style="text-align:center;padding-bottom:5px;margin-bottom:5px;border-bottom:1px solid #666;">订单备注</div>
    <div class="price">
	    <div class="line">订单编号：<span><span class='goodsprice'><%order.ordersn%></span></span></div>
        <div class="line">实付金额：<span><?php  echo $trade_order_currency;?>/<span class='goodsprice'><%order.goodsprice%></span></span></div>
        <!--<div class="line">运费:<span>￥<span class='dispatchprice'><%order.olddispatchprice%></span></span></div>-->

        <%if order.discountprice>0%>
        <div class="line">优惠:<span>-￥<span class='discountprice'><%order.discountprice%></span></span></div>
        <%/if%>
        <%if order.deductprice>0%>
        <div class="line"><?php  if($shopset['credit1']) { ?><?php  echo $shopset['credit1'];?><?php  } else { ?>积分<?php  } ?>抵扣:<span>-￥<span class='deductprice'><%order.deductprice%></span></span></div>
        <%/if%>
        <%if order.deductcredit2>0%>
        <div class="line"><?php  if($shopset['credit']) { ?><?php  echo $shopset['credit'];?><?php  } else { ?>余额<?php  } ?>抵扣:<span>-￥<span class='deductprice2'><%order.deductcredit2%></span></span></div>
        <%/if%>
        <%if order.deductenough!=0%>
        <div class="line">满额立减：<span><%if order.deductenough>0%>-<%/if%>￥<span class='deductenough'><%order.deductenough%></span></span></div>
        <%/if%>
        <%if order.changeprice!=0%>
        <div class="line">改价优惠:<span><%if order.changeprice>0%>+<%/if%>￥<span class='changeprice2'><%order.changeprice%></span></span></div>
        <%/if%>
        
        <%if order.changedispatchprice!=0%>
        <div class="line">运费改价:<span><%if order.changedispatchprice>0%>+<%/if%>￥<span class='changedispatchprice2'><%order.changedispatchprice%></span></span></div>
        <%/if%>

        <%if order.couponprice!=0%>
        <div class="line">优惠券优惠:<span><%if order.couponprice>0%>-<%/if%>￥<span class='changedispatchprice2'><%order.couponprice%></span></span></div>
        <%/if%>
        
        <!--<div class="line">实付金额:<span><span class='dispatchprice' style='color:#ff6600'>￥<%order.price%></span></span></div>-->

        <%if order.status==0 && order.paytype!=3%>
            <!--<div class="line">下单时间:<span><%order.createtime%></span></div>-->
        <%else if order.status==1%>
            <!--<div class="line">下单时间:<span><%order.createtime%></span></div>
                <div class="line">付款时间:<span><%order.paytime%></span></div>-->
        <%else if order.status==2%>
            <!--<div class="line">下单时间:<span><%order.createtime%></span></div>
                <div class="line">发货时间:<span><%order.sendtime%></span></div>
                <div class="line">实付货币:<span>USD美元</span></div>-->
        <%else if order.status==3%>
            <!--<div class="line">下单时间:<span><%order.createtime%></span></div>
                <div class="line">付款时间:<span><%order.paytime%></span></div>
                <div class="line">发货时间:<span><%order.sendtime%></span></div>
                <div class="line">实付货币:<span>USD美元</span></div>
                <div class="line">完成时间:<span><%order.finishtime%></span></div>-->
        <%/if%>
        
        <div class="line">发货时间：<span><%order.sendtime%></span></div>
        <div class="line">下单时间：<span><%order.createtime%></span></div>
        <div class="line">付款时间：<span><%order.paytime%></span></div>
        <div class="line">订单状态：<span><%order.status%></span></div>
        <!--<div class="line">实付货币:<span>USD美元</span></div>-->
        <!--<div class="line">完成时间:<span><%order.finishtime%></span></div>-->
      </div>
</div>
     
<div class="detail_pay">
      <%if order.status==0%>
	  <%if order.paytype!=3%>
		<div class="paysub" onclick="location.href ='<?php  echo $this->createMobileUrl('order/pay')?>&orderid=<%order.id%>&openid=<?php  echo $openid;?>'">付款</div>
           <%/if%>
           <div class="paysub1 order_cancel" style='position:relative;width:80px;'>
               <span style='position: absolute;
    display: block;
    width: 80px;
    top: 0;
    color: #666;
    height: 38px;
    left: 0;
    line-height: 38px;
    text-align: center;'>取消订单</span>
           <select>
               <option value="">不取消了</option>
               <option value="我不想买了">我不想买了</option>
               <option value="信息填写错误，重新拍">信息填写错误，重新拍</option>
               <option value="同城见面交易">同城见面交易</option>
               <option value="其他原因">其他原因</option>
           </select>
             </div>
      <%/if%>
  
      
      <%if order.status==2 %>
             <div class="paysub order_complete">确认收货</div>
			 <%if order.expresssn!=''%>
             <div class="paysub1 order_express">查看物流</div>
			 <%/if%>
      <%/if%>
      <%if order.status==3 && order.iscomment==0%>
             <div class="paysub1 order_comment">评价</div>
      <%/if%>
      <%if order.status==3 && order.iscomment==1%>
             <div class="paysub1 order_comment">追加评价</div>
      <%/if%>
      <%if order.status==3  || order.status==-1%>
             <div class="paysub1 order_delete">删除订单</div>
      <%/if%>
      <%if order.canrefund%>
         <div class="paysub order_refund"><%order.refund_button%></div>
      <%/if%> 
       <%if order.isverify=='1' %>
              <%if order.verified!='1'%>
                      <%if order.status==1%>
                       <div class="paysub1" onclick="VerifyHandler.verify('<?php  echo $_GPC['id'];?>')" style='float:left'><i class="fa fa-qrcode"></i> 确认使用</div>
                       <%/if%>
            <%/if%>
      <%/if%>
</div>
</script>
<?php  if(p('verify')) { ?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('verify/pop', TEMPLATE_INCLUDEPATH)) : (include template('verify/pop', TEMPLATE_INCLUDEPATH));?>
<?php  } ?>

<script type="text/javascript">
	function showDiyInfo(obj){
				var hide = $(obj).attr('hide');
				$(obj).next().toggle('fadeIn');
				$(obj).attr('hide',hide=='1'?'0':'1');
			}
			
     function showStores(obj){
        if($(obj).attr('show')=='1'){
            $(obj).next('div').slideUp(100);
            $(obj).removeAttr('show').find('i').removeClass('fa-angle-down').addClass('fa-angle-up');
        }
        else{
            $(obj).next('div').slideDown(100);
            $(obj).attr('show','1').find('i').removeClass('fa-angle-up').addClass('fa-angle-down');
        }
    }
    require(['tpl', 'core'], function(tpl, core) {
    
	function is_weixin(){
		
	}
        core.json('order/detail',{id:'<?php  echo $_GPC['id'];?>'},function(json){
                 
                 if(json.status==0){
                     core.message('订单已取消或不存在，无法查看!',"<?php  echo $this->createMobileUrl('order')?>" ,'error');
                     return;
                 }
                 $('#container').html(  tpl('tpl_detail',json.result) );
				 
				 
				     var ua = navigator.userAgent.toLowerCase();
						var isWX = ua.match(/MicroMessenger/i) == "micromessenger";
						var z = []; 
						$(".diyform_info img").each(function() {
							 z.push($(this).attr("src"));
						 });
						 var current;
						 if (isWX) {
							 $(".diyform_info img").click(function(e) {
								 e.preventDefault();
								 var startingIndex = $(".diyform_info img").index($(e.currentTarget));
								 var current = null;
								 $(".diyform_info img").each(function(B, A) {
									 if (B === startingIndex) {
										 current = $(A).attr("src");
									 }
								 });
								 WeixinJSBridge.invoke("imagePreview", {
									 current: current,
									 urls: z
								 });
							 });
						 }
			 
                 $("#verifycode").html( json.result.order.verifycode);
                 $(".order_cancel").find('select').change(function(){
                        var reason = $(this).val();

                        if(reason!=''){
                             core.json('order/op',{'op':'cancel', orderid:'<?php  echo $_GPC['id'];?>',reason:reason},function(json){

                                 if(json.status==1){
                                      location.href = core.getUrl('order');
                                      return;
                                 }
                                 else{
                                      core.tip.show(json.result);
                                 }
                             },true,true);
                        }
                 });
             
                 $('.order_refund').click(function(){
                       location.href = core.getUrl('order/op',{op:'refund',orderid:'<?php  echo $_GPC['id'];?>'});
                  });
                    $('.order_express').click(function(){
                       location.href = core.getUrl('order/express',{id:'<?php  echo $_GPC['id'];?>'});
                  });
                
                 $(".order_complete").click(function(){
  
                      core.tip.confirm('确认您已经收货?',function(){
                      
                         core.json('order/op',{'op':'complete', orderid:'<?php  echo $_GPC['id'];?>'},function(json){
                                 if(json.status==1){
                                      location.reload();
                                      return;
                                 }
                                 core.tip.show(json.result);
                             },true,true);
                       });
                 });
               
                 $(".order_comment").click(function(){
                             location.href = core.getUrl('order/op',{op:'comment',orderid:'<?php  echo $_GPC['id'];?>'});
                 });
            
                 $(".order_delete").click(function(){
                         core.json('order/op',{'op':'delete', orderid:'<?php  echo $_GPC['id'];?>'},function(json){

                              if(json.status==1){
                                   location.href = core.getUrl('order');
                                   return;
                               }
                              core.tip.show(json.result);
                         },true,true);
                 });

         }, true, true);
   });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
