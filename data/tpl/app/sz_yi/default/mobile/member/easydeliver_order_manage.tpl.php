<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>我的直邮购订单</title>
<style type="text/css">
    body {margin:0px; background:#efefef; -moz-appearance:none; -webkit-appearance: none;}
    .order_topbar {height:44px; width:100%; background:#fff; border-bottom:1px solid #e3e3e3;}
    .order_topbar .nav {height:44px; <?php  if($_GPC['status']!=4 && $_GPC['status']!=5 ) { ?>width:20%;<?php  } else { ?>width:33%;<?php  } ?> line-height:44px; text-align:center; font-size:14px; float:left; color:#666;}
    /*.order_topbar .nav {height:44px; <?php  if($_GPC['status']!=4) { ?>width:20%;<?php  } else { ?>width:50%;<?php  } ?> line-height:44px; text-align:center; font-size:14px; float:left; color:#666;}*/
    .order_topbar .on {height:42px; color:#f15353; border-bottom:2px solid #f15353;}
    .order_noinfo {height:20px; width:150px; background:url(img/order_img1.png) top center no-repeat; margin:50px auto 0px; padding-top:100px; line-height:20px; font-size:14px; text-align:center; color:#c9c9c9;}
    .order_main {height:auto; width:94%; background:#fff; padding:0px 3%; margin-top:16px; border-bottom:1px solid #e2e2e2; border-top:1px solid #e2e2e2;}
    .order_main .title {height:42px; width:100%; border-bottom:1px solid #e2e2e2; font-size:14px; line-height:42px; color:#666;}
    .order_main .title span {height:42px; width:auto; float:right; color:#f15353;}
    .order_main .good {height:50px; width:100%; padding:10px 0px; border-bottom:1px solid #eaeaea;}
    .order_main .good .img {height:50px; width:50px; float:left;}
    .order_main .good  .img img {height:100%; width:100%;}
    .order_main .good  .info {width:100%;float:left; margin-left:-50px;margin-right:-60px;}
    .order_main .good .info .inner { margin-left:60px;margin-right:60px; }
    .order_main .good .info .inner .name {height:32px; width:100%; float:left; font-size:12px; color:#555;overflow:hidden;}
    .order_main .good .info .inner .option {height:18px; width:100%; float:left; font-size:12px; color:#888;overflow:hidden;word-break: break-all}
    .order_main .good span { color:#666;}
    .order_main .good  .price { float:right;width:60px;;height:54px;margin-left:-60px;;}
    .order_main .good  .price .pnum { height:20px;width:100%;text-align:right;font-size:14px; }
    .order_main .good  .price .num { height:20px;width:100%;text-align:right;}
    .order_main .info1 {height:42px; width:100%; border-bottom:1px solid #e2e2e2; font-size:14px; color:#999; line-height:42px; text-align:right;}
    .order_main .info1 span {color:#666;}

    .order_main .sub {height:50px; width:100%;}
    .order_main .sub1 {height:30px; width:auto; padding:0px 10px; border:1px solid #ff771b; float:right; border-radius:5px; line-height:30px; font-size:14px; margin:10px 5px 10px 0px; color:#fff; background:#ff771b;}
    .order_main .sub2 {height:30px; width:auto; padding:0px 10px; border:1px solid #5f6e8b; float:right; border-radius:5px; line-height:30px; font-size:14px; margin:10px 5px 10px 0px; color:#5f6e8b;}
    select { width:80px;height:30px;position:absolute;left:0; filter:alpha(Opacity=0); opacity: 0;-webkit-appearance: none;background:#fff; -webkit-tap-highlight-color: transparent };
    .order_no {height:40px; width:100%;  padding-top:180px; margin:50px 0px;}

    .order_no {height:100px; width:100%; margin:50px 0px 60px; color:#BFBFBF; font-size:12px; text-align:center;}
    .order_no_menu {height:40px; width:100%; text-align:center;}
    .order_no_nav {height:34px;padding:6px 20px;background:#eee; border:1px solid #d4d4d4; border-radius:5px; text-align:center; line-height:34px; color:#666;font-size: 16px}
    #order_loading { width:94%;padding:10px;color:#666;text-align: center;}
    .no-icon .fa{ font-size: 80px;padding-bottom: 20px}
    .no-icon span{color: #929292;line-height: 20px;font-size: 14px}
    
    /*弹框*/
    #popBox{position: fixed;display:none;width:70%;height:180px;min-height: 180px; left:15%;top:30%;z-index:11; background: #fff; border-radius: 5px; font-size: 16px;}
	#popLayer{position: absolute;display:none;left:0;top:0;z-index:10;background:#000;-moz-opacity: 0.6;opacity:.60;filter: alpha(opacity=60);width: 100%; height: auto;/* 只支持IE6、7、8、9 */}
	.popcon{font-size: 14px; color: #4a4a4a; line-height: 24px; /*width: 95%; */margin: 5px auto;text-align: center; }
	.popcon p{padding: 15px 0; border-bottom: 1px solid #e8e8e8; width: 100%;}
	.closed{text-align: right; width: 100%; height: 60px;/* border-bottom: 1px solid #ccc; background: #eee;*/ border-top-left-radius:5px; border-top-right-radius:5px;background-image: url('../addons/sz_yi/static/images/pack.png'); background-size:100% 100%;}
	.closed span{position: absolute;left: 0;top: 50px;line-height: 40px; height: 40px; color: #4a4a4a; width: 100%;text-align: center; font-weight: bold;}
	.closed a{color: #4a4a4a; line-height: 30px; margin-right: 10px;/*font-size: 14px;*/}
	.laybut{background: none; border: none; color: white;}
	.popcon button{padding: 10px 20px; margin: 15% 15px; border-radius: 5px; border: none; font-size: 14px; color: #fff;}
	.wx{background-color: #00cc0f;}
	.zfb{background-color: #1b7ab6;}
</style>
<div id="popLayer" ></div>
<div id="popBox" >
	<div class="closed"><span>付款方式</span><a href="javascript:void(0)" onclick="closeBox()">关闭</a></div>
	<div class="popcon">
		<button class="wx" onclick="wx_pay();">微&nbsp;&nbsp;信</button>
		<button class="zfb" onclick="ali_pay();">支付宝</button>
	</div>
</div>
<div id='container'></div>


<script id='tpl_order_list' type='text/html'>

    <div class="order_topbar">
        <?php  if($_GPC['status']!=4) { ?>
        <div class="nav <?php  if($_GPC['status']=='') { ?>on<?php  } ?>" data-status="">全部</div>
        <div class="nav <?php  if($_GPC['status']=='0') { ?>on<?php  } ?>" data-status="0">待付款</div>
        <div class="nav <?php  if($_GPC['status']=='1') { ?>on<?php  } ?>"  data-status="1">待发货</div>
        <div class="nav <?php  if($_GPC['status']=='2') { ?>on<?php  } ?>"  data-status="2">待收货</div>
        <div class="nav <?php  if($_GPC['status']=='3') { ?>on<?php  } ?>"  data-status="3">已完成</div>
        <?php  } else { ?>
        <div class="nav <?php  if($_GPC['status']=='') { ?>on<?php  } ?>" data-status="">其他订单</div>
        <div class="nav <?php  if($_GPC['status']=='4') { ?>on<?php  } ?>"  data-status="3">退款订单</div>
        <div class="nav <?php  if($_GPC['status']=='5') { ?>on<?php  } ?>"  data-status="5">已退款订单</div>		<!-- peng 20170331 修补-->
        <?php  } ?>
    </div>
    <div id='order_container'></div>
</script>
<script id='tpl_order' type='text/html'>
    <%each list as order%>
    <div class="order_main" data-orderid="<%order.id%>">
        <div class="title">
            订单号：<%order.ordersn_general%>
            <span style="margin-left:9%;overflow:auto;"><?php  if($_GPC['status']==5) { ?>已退款<?php  } else { ?><%order.statusstr%><?php  } ?></span>
        </div> <!-- peng 20170331 修补-->
        <%each order.goods as g%>
        <div class="good">
            <%if order.cashier==1%>
            <div class="img"  ><img src="<%order.name.thumb%>"/></div>
            <div class='info' >
                <%else%>
                <div class="img" onclick="location.href='<?php  echo $this->createMobileUrl('shop/detail')?>&id=<%g.goodsid%>'"><img src="<%g.thumb%>"/></div>
                <div class='info'onclick="location.href='<?php  echo $this->createMobileUrl('shop/detail')?>&id=<%g.goodsid%>'">
                    <%/if%>
                    <div class='inner'>
                        <div class="name"><%if order.cashier==1%><%order.name.name%><span style="color:red">(收银台支付)</span><%else%><%g.title%><%/if%></div>
                        <div class='option'><%if g.optionid!='0'%>规格:  <%g.optiontitle%><%/if%></div>
                    </div>
                </div>
                <div class="price">
                    <div class='pnum'><span class='marketprice'>￥<%g.price%></span></div>
                    <div class='pnum'><span class='total'>×<%g.total%></span></div>
                </div>
            </div>
            <%/each%>
            <!-- peng 20170331 !-->
            <?php  if($_GPC['status'] ==5) { ?>
            <div class="info1">订单已退还￥<%order.price%>，积分已扣除</div>
            <?php  } else { ?>
            <div class="info1">共 <%order.goodscount%> 件商品&nbsp;实付：<span>￥<%order.price%></span>&nbsp;<?php  if($plugin_yunbi) { ?><%if order.deductyunbi%><?php  if(!empty($yunbi_set['yunbi_title'])) { ?><?php  echo $yunbi_set['yunbi_title']?><?php  } else { ?>云币<?php  } ?>抵扣:<span>￥<%order.deductyunbimoney%></span>&nbsp;元<%/if%><?php  } ?></div>
            <?php  } ?>
            <!-- peng 20170331 !-->
            <div class="sub">
                <%if order.status==0%>
                <%if order.paytype!=3%>
				<!--<div class="sub1" onclick="location.href='<?php  echo $this->createMobileUrl('order/pay')?>&orderid=<%order.id%>&openid=<?php  echo $openid;?>'">付款</div>-->
				<div class="sub1"><button id="Button1" onclick="popBox('<%order.id%>')" class="laybut">付款</button></div>
				
                <%/if%>
                <div class="sub2 order_cancel" style='position:relative;width:56px;'>
                    <span style='position:absolute;display:block;width:56px;'>取消订单</span>
                    <select>
                        <option value="">不取消了</option>
                        <option value="我不想买了">我不想买了</option>
                        <option value="信息填写错误，重新拍">信息填写错误，重新拍</option>
                        <option value="同城见面交易">同城见面交易</option>
                        <option value="其他原因">其他原因</option>
                    </select>
                </div>
                <%/if%>
                <%if order.status==1 && order.isverify=='1' && order.verifyied!='1'%>
                <div class="sub2" style="float:left;" onclick="VerifyHandler.verify('<%order.id%>')"><i class="fa fa-qrcode"></i> 确认使用</div>
                <%/if%>

                <%if order.status==2%>
                <div class="sub1 order_complete">确认收货</div>
                <%if order.expresssn!=''%>
                <div class="sub2 order_express">查看物流</div>
                <%/if%>

                <%/if%>
                <%if order.status==3 && order.iscomment==0%>
                <div class="sub2 order_comment">评价</div>
                <%/if%>
                <%if order.status==3 && order.iscomment==1%>
                <div class="sub2 order_comment">追加评价</div>
                <%/if%>
                <%if order.status==3 || order.status==-1%>
                <div class="sub2 order_delete">删除订单</div>
                <%/if%>
                <%if order.canrefund%>
                <div class="sub1 order_refund"><%order.refund_button%></div>
                <%/if%>


            </div>
        </div>
        <%/each%>
</script>
<script id='tpl_empty' type='text/html'>
    <div class="order_no no-icon"><i class="fa fa-file-text-o"></i><br><span>您还没有相关订单</span>
<!--        <br>可以去看看哪些想买的-->
    </div>
<!--    <div class="order_no_menu">-->

<!--        <span class="order_no_nav"  onclick="location.href='<?php  echo $this->getUrl()?>'">随便逛逛</span>-->
<!--    </div>-->
</script>
<script language='javascript'>

    var page = 1;
    require(['tpl', 'core'], function(tpl, core) {

        function bindEvents(){

            $('.order_main .good').unbind('click').click(function() {

                var orderid = $(this).closest('.order_main').data('orderid');
                location.href = core.getUrl('order/easy_deliver_orderdetail', {id: orderid});
            });


            $(".order_cancel").find('select').unbind('change').change(function() {
                var reason = $(this).val();
                var orderid = $(this).closest('.order_main').data('orderid');

                if (reason != '') {
                    core.json('order/op', {'op': 'cancel', orderid: orderid, reason: reason}, function(json) {

                        if (json.status == 1) {
                            core.tip.show('取消成功！');
                            $(".order_main[data-orderid='" + orderid + "']").remove();
                        }
                        else {
                            core.tip.show(json.result);
                        }
                    }, true, true);
                }
            });

            $('.order_refund').unbind('click').click(function() {

                var orderid = $(this).closest('.order_main').data('orderid');
                location.href = core.getUrl('order/op', {op: 'refund', orderid: orderid});

            });
            $('.order_express').unbind('click').click(function() {

                var orderid = $(this).closest('.order_main').data('orderid');
                location.href = core.getUrl('order/express', {id: orderid});

            });

            $(".order_complete").unbind('click').click(function(){
                var orderid = $(this).closest('.order_main').data('orderid');
                core.tip.confirm('确认您已经收货?',function(){

                    core.json('order/op',{'op':'complete', orderid:orderid},function(json){
                        if(json.status==1){
                            location.reload();
                            return;
                        }
                        core.tip.show(json.result);
                    },true,true);
                });
            });

            $(".order_comment").unbind('click').click(function(){
                var orderid = $(this).closest('.order_main').data('orderid');
                location.href = core.getUrl('order/op',{op:'comment',orderid:orderid});
            });

            $(".order_delete").unbind('click').click(function(){
                var orderid = $(this).closest('.order_main').data('orderid');
                core.json('order/op',{'op':'delete', orderid:orderid},function(json){

                    if(json.status==1){
                        $(".order_main[data-orderid='" + orderid + "']").remove();
                        return;
                    }
                    core.tip.show(json.result);
                },true,true);
            });
        }
        core.json('member/easydeliver_order_manage', {page:page, status: '<?php  echo $_GPC['status'];?>'}, function(json) {

            $("#container").html(tpl('tpl_order_list'));
            $('.nav').click(function() {
                var status = $(this).data('status');
                location.href = core.getUrl('member/easydeliver_order_manage', {status: status});
            })
            if (json.result.list.length <= 0) {
                $("#order_container").html(tpl('tpl_empty'));
                return;
            }
            $("#order_container").html(tpl('tpl_order', json.result));
            bindEvents();


            var loaded = false;
            var stop=true;
            $(window).scroll(function(){
                if(loaded){
                    return;
                }
                totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
                if($(document).height() <= totalheight){

                    if(stop==true){
                        stop=false;
                        $('#order_container').append('<div id="order_loading"><i class="fa fa-spinner fa-spin"></i> 正在加载...</div>');
                        page++;
                        core.json('member/easydeliver_order_manage', {page:page, status: '<?php  echo $_GPC['status'];?>'}, function(morejson) {
                            stop = true;
                            $('#order_loading').remove();
                            $("#order_container").append(tpl('tpl_order', morejson.result));
                            bindEvents();
                            if (morejson.result.list.length <morejson.result.pagesize) {
                                $('#order_container').append('<div id="order_loading">已经加载全部订单</div>');
                                loaded = true;
                                return;
                            }
                        },true);
                    }
                }
            });
        }, true);

    });
    
    var orderid = null;
    function popBox(oid){
        orderid = oid;
        var popBox = document.getElementById('popBox');
        var popLayer = document.getElementById('popLayer');

        popLayer.style.width = document.body.scrollWidth + "px";
        popLayer.style.height = document.body.scrollHeight + "px";

        popLayer.style.display = "block";
        popBox.style.display = "block";
    }//end func popBox()
	//关闭隐藏层
    function closeBox(){
        var popBox = document.getElementById('popBox');
        var popLayer = document.getElementById('popLayer');

        popLayer.style.display = "none";
        popBox.style.display = "none";
    }

    function wx_pay(){
        $.ajax({
            url:"<?php  echo $this->createMobileUrl('member/easydeliver_order_manage')?>&op=pay",
            type:"GET",
            dataType:"json",
            data:{orderid:orderid,type:'wx'},
            success:function(ret){
                console.dir(ret);
                if(ret.status==1){
                    alert(ret.result);
                    return;
                }
                postcall(ret.result.url,ret.result);
            }
        });
        return false;
    }
    function ali_pay(){
        console.log('支付宝支付'+orderid);
        $.ajax({
            url:"<?php  echo $this->createMobileUrl('member/easydeliver_order_manage')?>&op=pay",
            type:"GET",
            dataType:"json",
            data:{orderid:orderid,type:'ali'},
            success:function(ret){
                console.dir(ret);
                if(ret.status==1){
                    alert(ret.result);
                    return;
                }
                $.ajax({
                    url:ret.result.ajaxReqUrl,
                    type:"POST",
                    dataType:"json",
                    data:ret.result.payParam,
                    success:function (res) {
                    if (res.code>=1){
                        postcall(ret.result.locaUrl,{pay_url:res.pay_url,returnUrl:res.returnUrl},'','get');
                    }
                    },error:function () {
                        alert('支付失败');
                    }
                });
            }
        });        
    }
    function postcall( url, params, target,menth='post'){
        var tempform = document.createElement("form");
        tempform.action = url;
        tempform.method = menth;
        tempform.style.display="none"
        if(target) {
            tempform.target = target;
        }

        for (var x in params) {
            var opt = document.createElement("input");
            opt.name = x;
            opt.value = params[x];
            tempform.appendChild(opt);
        }

        var opt = document.createElement("input");
        opt.type = "submit";
        tempform.appendChild(opt);
        document.body.appendChild(tempform);
        tempform.submit();
        document.body.removeChild(tempform);
    }
</script>


<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>我的直邮购订单</title>
<link rel="stylesheet" href="../addons/sz_yi/static/css/layui.css">
<link rel="stylesheet" href="../addons/sz_yi/static/css/common_1.css">
<style type="text/css">
    body {margin:0px; background:#efefef; -moz-appearance:none; -webkit-appearance: none;}
    .order_topbar {height:44px; width:100%; background:#fff; border-bottom:1px solid #e3e3e3;}
    .order_topbar .nav {height:44px; <?php  if($_GPC['status']!=4 && $_GPC['status']!=5 ) { ?>width:20%;<?php  } else { ?>width:33%;<?php  } ?> line-height:44px; text-align:center; font-size:14px; float:left; color:#666;}
    /*.order_topbar .nav {height:44px; <?php  if($_GPC['status']!=4) { ?>width:20%;<?php  } else { ?>width:50%;<?php  } ?> line-height:44px; text-align:center; font-size:14px; float:left; color:#666;}*/
    .order_topbar .on {height:42px; color:#f15353; border-bottom:2px solid #f15353;}
    .order_noinfo {height:20px; width:150px; background:url(img/order_img1.png) top center no-repeat; margin:50px auto 0px; padding-top:100px; line-height:20px; font-size:14px; text-align:center; color:#c9c9c9;}
    .order_main {height:auto; width:94%; background:#fff; padding:0px 3%; margin-top:16px; border-bottom:1px solid #e2e2e2; border-top:1px solid #e2e2e2;}
    .order_main .title {height:42px; width:100%; border-bottom:1px solid #e2e2e2; font-size:14px; line-height:42px; color:#666;}
    .order_main .title span {height:42px; width:auto; float:right; color:#f15353;}


    .order_main .good {height:50px; width:100%; padding:10px 0px; border-bottom:1px solid #eaeaea;}
    .order_main .good .img {height:50px; width:50px; float:left;}
    .order_main .good  .img img {height:100%; width:100%;}
    .order_main .good  .info {width:100%;float:left; margin-left:-50px;margin-right:-60px;}
    .order_main .good .info .inner { margin-left:60px;margin-right:60px; }
    .order_main .good .info .inner .name {height:32px; width:100%; float:left; font-size:12px; color:#555;overflow:hidden;}
    .order_main .good .info .inner .option {height:18px; width:100%; float:left; font-size:12px; color:#888;overflow:hidden;word-break: break-all}
    .order_main .good span { color:#666;}
    .order_main .good  .price { float:right;width:60px;;height:54px;margin-left:-60px;;}
    .order_main .good  .price .pnum { height:20px;width:100%;text-align:right;font-size:14px; }
    .order_main .good  .price .num { height:20px;width:100%;text-align:right;}
    .order_main .info1 {height:42px; width:100%; border-bottom:1px solid #e2e2e2; font-size:14px; color:#999; line-height:42px; text-align:right;}
    .order_main .info1 span {color:#666;}

    .order_main .sub {height:50px; width:100%;}
    .order_main .sub1 {height:30px; width:auto; padding:0px 10px; border:1px solid #ff771b; float:right; border-radius:5px; line-height:30px; font-size:14px; margin:10px 5px 10px 0px; color:#fff; background:#ff771b;}
    .order_main .sub2 {height:30px; width:auto; padding:0px 10px; border:1px solid #5f6e8b; float:right; border-radius:5px; line-height:30px; font-size:14px; margin:10px 5px 10px 0px; color:#5f6e8b;}
    select { width:80px;height:30px;position:absolute;left:0; filter:alpha(Opacity=0); opacity: 0;-webkit-appearance: none;background:#fff; -webkit-tap-highlight-color: transparent };
    .order_no {height:40px; width:100%;  padding-top:180px; margin:50px 0px;}

    .order_no {height:100px; width:100%; margin:50px 0px 60px; color:#BFBFBF; font-size:12px; text-align:center;}
    .order_no_menu {height:40px; width:100%; text-align:center;}
    .order_no_nav {height:34px;padding:6px 20px;background:#eee; border:1px solid #d4d4d4; border-radius:5px; text-align:center; line-height:34px; color:#666;font-size: 16px}
    #order_loading { width:94%;padding:10px;color:#666;text-align: center;}
    .no-icon .fa{ font-size: 80px;padding-bottom: 20px}
    .no-icon span{color: #929292;line-height: 20px;font-size: 14px}
</style>
<div id='container'></div>
<script id='tpl_order_list' type='text/html'>
    <div class="order_topbar">
        <?php  if($_GPC['status']!=4) { ?>
        <div class="nav <?php  if($_GPC['status']=='') { ?>on<?php  } ?>" data-status="">全部</div>
        <div class="nav <?php  if($_GPC['status']=='0') { ?>on<?php  } ?>" data-status="0">待付款</div>
        <div class="nav <?php  if($_GPC['status']=='1') { ?>on<?php  } ?>"  data-status="1">待发货</div>
        <div class="nav <?php  if($_GPC['status']=='2') { ?>on<?php  } ?>"  data-status="2">待收货</div>
        <div class="nav <?php  if($_GPC['status']=='3') { ?>on<?php  } ?>"  data-status="3">已完成</div>
        <?php  } else { ?>
        <div class="nav <?php  if($_GPC['status']=='') { ?>on<?php  } ?>" data-status="">其他订单</div>
        <div class="nav <?php  if($_GPC['status']=='4') { ?>on<?php  } ?>"  data-status="3">退款订单</div>
        <div class="nav <?php  if($_GPC['status']=='5') { ?>on<?php  } ?>"  data-status="5">已退款订单</div>		<!-- peng 20170331 修补-->
        <?php  } ?>
    </div>
    <div id='order_container'></div>
</script>
<script id='tpl_order' type='text/html'>
    <%each list as order%>
    <div class="order_main" data-orderid="<%order.id%>">
        <div class="title">订单号：<%order.ordersn_general%><span><?php  if($_GPC['status']==5) { ?>已退款<?php  } else { ?><%order.statusstr%><?php  } ?></span></div> <!-- peng 20170331 修补-->
        <%each order.goods as g%>
        <div class="good">
            <%if order.cashier==1%>
            <div class="img"  ><img src="<%order.name.thumb%>"/></div>
            <div class='info' >
                <%else%>
                <div class="img" onclick="location.href='<?php  echo $this->createMobileUrl('shop/detail')?>&id=<%g.goodsid%>'"><img src="<%g.thumb%>"/></div>
                <div class='info'onclick="location.href='<?php  echo $this->createMobileUrl('shop/detail')?>&id=<%g.goodsid%>'">
                    <%/if%>
                    <div class='inner'>
                        <div class="name"><%if order.cashier==1%><%order.name.name%><span style="color:red">(收银台支付)</span><%else%><%g.title%><%/if%></div>
                        <div class='option'><%if g.optionid!='0'%>规格:  <%g.optiontitle%><%/if%></div>
                    </div>
                </div>
                <div class="price">
                    <div class='pnum'><span class='marketprice'>￥<%g.price%></span></div>
                    <div class='pnum'><span class='total'>×<%g.total%></span></div>
                </div>
            </div>
            <%/each%>
            <!-- peng 20170331 !-->
            <?php  if($_GPC['status'] ==5) { ?>
            <div class="info1">订单已退还￥<%order.price%>，积分已扣除</div>
            <?php  } else { ?>
            <div class="info1">共 <%order.goodscount%> 件商品&nbsp;实付：<span>￥<%order.price%></span>&nbsp;<?php  if($plugin_yunbi) { ?><%if order.deductyunbi%><?php  if(!empty($yunbi_set['yunbi_title'])) { ?><?php  echo $yunbi_set['yunbi_title']?><?php  } else { ?>云币<?php  } ?>抵扣:<span>￥<%order.deductyunbimoney%></span>&nbsp;元<%/if%><?php  } ?></div>
            <?php  } ?>
            <!-- peng 20170331 !-->
            <div class="sub">
                <%if order.status==0%>
                <%if order.paytype!=3%>
                <div class="sub1" onclick="location.href='<?php  echo $this->createMobileUrl('order/pay')?>&orderid=<%order.id%>&openid=<?php  echo $openid;?>'">付款</div>
                <%/if%>
                <div class="sub2 order_cancel" style='position:relative;width:56px;'>
                    <span style='position:absolute;display:block;width:56px;'>取消订单</span>
                    <select>
                        <option value="">不取消了</option>
                        <option value="我不想买了">我不想买了</option>
                        <option value="信息填写错误，重新拍">信息填写错误，重新拍</option>
                        <option value="同城见面交易">同城见面交易</option>
                        <option value="其他原因">其他原因</option>
                    </select>
                </div>
                <%/if%>
                <%if order.status==1 && order.isverify=='1' && order.verifyied!='1'%>
                <div class="sub2" style="float:left;" onclick="VerifyHandler.verify('<%order.id%>')"><i class="fa fa-qrcode"></i> 确认使用</div>
                <%/if%>

                <%if order.status==2%>
                <div class="sub1 order_complete">确认收货</div>
                <%if order.expresssn!=''%>
                <div class="sub2 order_express">查看物流</div>
                <%/if%>

                <%/if%>
                <%if order.status==3 && order.iscomment==0%>
                <div class="sub2 order_comment">评价</div>
                <%/if%>
                <%if order.status==3 && order.iscomment==1%>
                <div class="sub2 order_comment">追加评价</div>
                <%/if%>
                <%if order.status==3 || order.status==-1%>
                <div class="sub2 order_delete">删除订单</div>
                <%/if%>
                <%if order.canrefund%>
                <div class="sub1 order_refund"><%order.refund_button%></div>
                <%/if%>


            </div>
        </div>
        <%/each%>

</script>
<script id='tpl_empty' type='text/html'>
    <div class="order_no no-icon"><i class="fa fa-file-text-o"></i><br><span>您还没有相关订单</span>
<!--        <br>可以去看看哪些想买的-->
    </div>
<!--    <div class="order_no_menu">-->
<!--        <span class="order_no_nav"  onclick="location.href='<?php  echo $this->getUrl()?>'">随便逛逛</span>-->
<!--    </div>-->
</script>
<script language='javascript'>

    var page = 1;
    require(['tpl', 'core'], function(tpl, core) {

        function bindEvents(){

            $('.order_main .good').unbind('click').click(function() {

                var orderid = $(this).closest('.order_main').data('orderid');
                location.href = core.getUrl('order/easy_deliver_orderdetail', {id: orderid});

            });


            $(".order_cancel").find('select').unbind('change').change(function() {
                var reason = $(this).val();
                var orderid = $(this).closest('.order_main').data('orderid');

                if (reason != '') {
                    core.json('order/op', {'op': 'cancel', orderid: orderid, reason: reason}, function(json) {

                        if (json.status == 1) {
                            core.tip.show('取消成功！');
                            $(".order_main[data-orderid='" + orderid + "']").remove();
                        }
                        else {
                            core.tip.show(json.result);
                        }
                    }, true, true);
                }
            });

            $('.order_refund').unbind('click').click(function() {

                var orderid = $(this).closest('.order_main').data('orderid');
                location.href = core.getUrl('order/op', {op: 'refund', orderid: orderid});

            });
            $('.order_express').unbind('click').click(function() {

                var orderid = $(this).closest('.order_main').data('orderid');
                location.href = core.getUrl('order/express', {id: orderid});

            });

            $(".order_complete").unbind('click').click(function(){
                var orderid = $(this).closest('.order_main').data('orderid');
                core.tip.confirm('确认您已经收货?',function(){

                    core.json('order/op',{'op':'complete', orderid:orderid},function(json){
                        if(json.status==1){
                            location.reload();
                            return;
                        }
                        core.tip.show(json.result);
                    },true,true);
                });
            });

            $(".order_comment").unbind('click').click(function(){
                var orderid = $(this).closest('.order_main').data('orderid');
                location.href = core.getUrl('order/op',{op:'comment',orderid:orderid});
            });

            $(".order_delete").unbind('click').click(function(){
                var orderid = $(this).closest('.order_main').data('orderid');
                core.json('order/op',{'op':'delete', orderid:orderid},function(json){

                    if(json.status==1){
                        $(".order_main[data-orderid='" + orderid + "']").remove();
                        return;
                    }
                    core.tip.show(json.result);
                },true,true);
            });
        }
        core.json('member/easydeliver_order_manage', {page:page, status: '<?php  echo $_GPC['status'];?>'}, function(json) {

            $("#container").html(tpl('tpl_order_list'));
            $('.nav').click(function() {
                var status = $(this).data('status');
                location.href = core.getUrl('member/easydeliver_order_manage', {status: status});
            })
            if (json.result.list.length <= 0) {
                $("#order_container").html(tpl('tpl_empty'));
                return;
            }
            $("#order_container").html(tpl('tpl_order', json.result));
            bindEvents();


            var loaded = false;
            var stop=true;
            $(window).scroll(function(){
                if(loaded){
                    return;
                }
                totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop());
                if($(document).height() <= totalheight){

                    if(stop==true){
                        stop=false;
                        $('#order_container').append('<div id="order_loading"><i class="fa fa-spinner fa-spin"></i> 正在加载...</div>');
                        page++;
                        core.json('member/easydeliver_order_manage', {page:page, status: '<?php  echo $_GPC['status'];?>'}, function(morejson) {
                            stop = true;
                            $('#order_loading').remove();
                            $("#order_container").append(tpl('tpl_order', morejson.result));
                            bindEvents();
                            if (morejson.result.list.length <morejson.result.pagesize) {
                                $('#order_container').append('<div id="order_loading">已经加载全部订单</div>');
                                loaded = true;
                                return;
                            }
                        },true);
                    }
                }
            });
        }, true);

    });
</script>