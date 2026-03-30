<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/easy_delivery_header', TEMPLATE_INCLUDEPATH)) : (include template('common/easy_delivery_header', TEMPLATE_INCLUDEPATH));?>
<body style="font-size:12px;">
<header>
    <div class="logo">
        <img src="../addons/sz_yi/static/images/logo.png" alt="">
    </div>
    <div class="plane">
        <img src="../addons/sz_yi/static/images/plane.png" alt="">
    </div>
</header>
<div class="warp">
    <div class="content">
        <form action="">
            <div class="warp-con">
                <?php  if(is_array($list)) { foreach($list as $item) { ?>
                <div class="shop-title">
                    <div class="check-box buss" data-id="<?php  echo $item['uid'];?>">
                        <label for="merchant"></label>
                        <input type="checkbox" id="merchant"/>
                    </div>
                    <div class="shop-name">
                        <img src="../addons/sz_yi/static/images/icon_05.png" alt="">
                        <?php  echo $item['name'];?>
                        <!--                        <span>[ 已选购：22，拟订购：22，待订购：22 ]</span>-->
                    </div>
                </div>
                <?php  if(is_array($item['goods_list'])) { foreach($item['goods_list'] as $good) { ?>
                <div class="shop-list" data-price="<?php  echo $good['marketprice'];?>" data-Pay="<?php  echo $good['paymentType'];?>">
                    <div class="check-box" style="margin-top:26px;">
                        <label for="goods1"></label>
                        <input type="checkbox" id="goods1" value="<?php  echo $good['id'];?>"/>
                    </div>
                    <div class="pic"><img src="../addons/sz_yi/static/images/shop.jpg" alt=""></div>
                    <div class="goods-title">
                        <h3 style="font-size:13px;"><?php  echo $good['title'];?> </h3>
                        <div class="goods-norms"><?php  echo $good['value'];?></div>
                        <div class="goods-tab">
                            <ul>
                                <li style="font-size:10px;"><?php  if($good['paymentType']==1) { ?>在线支付<?php  } else { ?>线下支付<?php  } ?></li>
                                <li style="font-size:10px;"><?php  if($good['packingType']==1) { ?>单品打包<?php  } else { ?>混品打包<?php  } ?></li>
                                <li style="font-size:10px;"><?php  if($good['directType']==1) { ?>保税直邮<?php  } else { ?>境外直邮<?php  } ?></li>
                            </ul>
<!--                            <ul>-->
<!--                                <li>未含邮费</li>-->
<!--                                <li>未含税费</li>-->
<!--                            </ul>-->
                        </div>
                    </div>
                    <div class="gooods-price">
                        <p class="p1">¥ <?php  echo $good['marketprice'];?></p>
                        <p class="p2">x <?php  echo $good['total'];?></p>
                    </div>
                </div>
                <?php  } } ?>
                <?php  } } ?>

                <div class="price-all">

                    <ul onclick="sel_coupon(this);">
                        <li class="price-text1">选择优惠卷</li>
                        <li class="price-text3 coupon_sel"><img style="width: 37px;height: 20px;float: right;margin-right: -11px;" src="../addons/sz_yi/static/images/右箭头.png"></li>
                    </ul>

                    <ul style="display: none;">
                        <li class="price-text1">订单总额</li>
                        <li class="price-text2">¥ 0</li>
                    </ul>

                    <ul>
                        <li class="price-text1">买家留言</li>
                        <li class="price-text3"><input type="text" name="" id="remark" placeholder="买家留言"></li>
                    </ul>
                </div>
                <div class="price-pay" style="padding:2% 3px;">
                    <ul style="margin-left: 6px;">
<!--                        <li class="price-text1"><span style="padding-left: 29%;">其中：</span></li>-->
                        <li class="price-text4" style="width: 97%;"><p>线上支付：<span style="float: right;">¥ 0</span></p>
                            <p>线下支付：<span style="float: right;">¥ 0</span></p></li>
<!--                        <li class="price-text5">-->

<!--                        </li>-->
                    </ul>
                    <input type="hidden" name="isCoupon" id="isCoupon" value="">
                    <button id="alertBtn" type="button" class="zbox-btn zbox-btn-blue zbox-btn-outlined" style="border-radius:15px;width: 88%;background-color: #2a95d8;border: 1px solid #2a95d8;line-height:2.42;"><img
                            src="../addons/sz_yi/static/images/订购.png" alt="">订购
                    </button>
                </div>
            </div>
        </form>
        <div class="pack-bg" style="display: none;"></div>
        <div class="pack-con" class="animated" style="display: none; padding-bottom: 10px;">
            <img class="dialogIco" src="../addons/sz_yi/static/images/pack.png" alt="" />
            <div class="dialogTop">
                <a href="javascript:;" class="claseDialogBtn" style="margin-right: -7px;">关闭</a>
            </div>
            <!-- <form action="" method="post" id="editForm"> -->
            <h2 class="pack-title">请选择支付方式</h2>
            <ul class="editInfos">
                <li><input type="submit" value="微信" class="focusBtn" onclick="offlinePay(this,'wechat');return false;" /></li>
                <li><input type="submit" value="支付宝" class="locallyBtn" onclick="onlinePay(this,'ali');return false;" /></a></li>
            </ul>
        </div>
    </div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/easy_deliver_footer', TEMPLATE_INCLUDEPATH)) : (include template('common/easy_deliver_footer', TEMPLATE_INCLUDEPATH));?>
<link rel="stylesheet" href="../addons/sz_yi/static/css/pack.css">
<link rel="stylesheet" href="../addons/sz_yi/static/css/toast.css">
<link rel="stylesheet" href="../addons/sz_yi/static/css/centermenu.css">
<script src="../addons/sz_yi/static/js/toast.js"></script>
<script src="../addons/sz_yi/static/js/centermenu.js"></script>
<script>
    var totalPrice = 0;
    var offlineAmount=0;
    var onlineAmount =0;
    var isBuss = {};
    $('.buss').each(function () {
        $(this).bind('click', function () {
            var id = $(this).attr('data-id');
            var b = this;
            isBuss[id] = id;
            if (Object.getOwnPropertyNames(isBuss).length > 1) {
                delete isBuss[id];
                $.DialogByZ.Alert({Title: "提示", Content: "包含其他商家"});
                return false;
            }
            $(this).parent('.shop-title').nextAll().each(function () {
                var c = $(this).attr('class');
                var isPay = $(this).attr('data-pay');
                if (c == 'shop-title' || c == 'price-all' || c == 'price-pay') {
                    return false;
                }
                if (!$(b).children().next().prop('checked')) {
                    totalPrice =totalPrice<1?0.00: (totalPrice - parseFloat($(this).attr("data-price"))).toFixed(2);
                    if (isPay=='1'){
                        onlineAmount = onlineAmount<1?0.00:(parseFloat(onlineAmount) - parseFloat($(this).attr("data-price"))).toFixed(2);
                    }else{
                        offlineAmount =offlineAmount<1?0.00: (parseFloat(offlineAmount) - parseFloat($(this).attr("data-price"))).toFixed(2);
                    }
                    $(this).children().find('input').prop('checked', false);
                    isBuss = {};
                } else {
                    if (!$(this).children().children().next().prop('checked')){
                        totalPrice =(parseFloat(totalPrice)+parseFloat($(this).attr("data-price"))).toFixed(2);
                        if (isPay=='1'){
                            onlineAmount = (parseFloat(onlineAmount) + parseFloat($(this).attr("data-price"))).toFixed(2);
                        }else{
                            offlineAmount = (parseFloat(offlineAmount) + parseFloat($(this).attr("data-price"))).toFixed(2);
                        }
                    }

                    $(this).children().find('input').prop('checked', true);
                }
            });
            $('.price-text2').html('¥ ' + totalPrice);
            $('.price-text4').children().children().html('¥ ' + onlineAmount);
            $('.price-text4').children().next().children().html('¥ ' + offlineAmount);
        });
    });
    $('.shop-list input[type=checkbox]').each(function () {
        $(this).bind('click', function () {
            var $this = $(this);
            var e = false;
            $(this).parent().parent().prevAll().each(function () {
                if ($(this).attr('class') == 'shop-title') {
                    var id = $(this).children().attr('data-id');
                    isBuss[id] = id;
                    if (!$this.prop('checked')&&!$(this).children().children().next().prop('checked')) {
                        delete isBuss[id];
                        return false;
                    }
                    if (Object.getOwnPropertyNames(isBuss).length > 1) {
                        e = true;
                        $this.prop('checked', false);
                        $.DialogByZ.Alert({Title: "提示", Content: "包含其他商家"});
                        delete isBuss[id];
                    }
                    return false; //跳出循环
                }
            });
            var pr =parseFloat($this.parent().parent().attr('data-price'));
            var isPay = $this.parent().parent().attr('data-pay');

            if ($this.prop('checked')) {
                totalPrice = (parseFloat(totalPrice)+pr).toFixed(2);
                if (isPay=='1'){
                    onlineAmount = (parseFloat(onlineAmount)+pr).toFixed(2);
                }else{
                    offlineAmount = (parseFloat(offlineAmount)+pr).toFixed(2);
                }

            } else {
                if (!e) {
                    totalPrice = (parseFloat(totalPrice)-pr).toFixed(2);
                    if (isPay=='1'){
                        onlineAmount = (parseFloat(onlineAmount)-pr).toFixed(2);
                    }else{
                        offlineAmount = (parseFloat(offlineAmount)-pr).toFixed(2);
                    }
                }
            }
            $('.price-text2').html('¥ ' + totalPrice);
            $('.price-text4').children().children().html('¥ ' + onlineAmount);
            $('.price-text4').children().next().children().html('¥ ' + offlineAmount);
            // $('.price-text5').find('span').html('¥ ' + offlineAmount);
        });
    });

    //关闭支付选择
    $('.claseDialogBtn').click(function(){
        $('.pack-bg').css('display','none');
        $('.pack-con').css('display','none');
    });
    
    /*提示框*/
    $("#alertBtn").click(function () {
       $('.pack-bg').css('display','block');
        $('.pack-con').css('display','block');
    });

    //在线支付
    function onlinePay(obj,type) {
        var list ={};
        var mer = {};
        $(".shop-list input[type='checkbox']:checked").each(function(i){//把所有被选中的复选框的值存入数组
            list[i] =$(this).val();
        });
        $(".shop-title input[type='checkbox']:checked").each(function(i){//把所有被选中的复选框的值存入数组
            var v = $(this).parent().attr('data-id');
            mer[v] =v;
        });
        req({isCoupon:$('#isCoupon').val(),list:list,type:type,remark:$('#remark').val(),buss:isBuss.length<1?mer:isBuss});
        return false;
    }

    //线下支付
    function offlinePay(obj,type) {
        var list ={};
        var mer ={};
        $(".shop-list input[type='checkbox']:checked").each(function(i){//把所有被选中的复选框的值存入数组
            list[i] =$(this).val();
        });
        $(".shop-title input[type='checkbox']:checked").each(function(i){//把所有被选中的复选框的值存入数组
            var v = $(this).parent().attr('data-id');
            mer[v] =v;
        });
        req({isCoupon:$('#isCoupon').val(),list:list,type:type,remark:$('#remark').val(),buss:isBuss.length<1?mer:isBuss});
        return false;
    }
    function req(data) {
        $.ajax({
            url:"<?php  echo $this->createMobileUrl('order/easy_deliver_pay')?>",
            type:"POST",
            dataType:"json",
            data:{data},
            success:function (res) {
                console.log(res);
                if (res.status==1){
                    $.DialogByZ.Alert({Title: "错误", Content: res.result});
                }else if(res.status==2){
                    postcall(res.result.url,res.result);
                }else if(res.status==3){
                  $.ajax({
                      url:res.result.ajaxReqUrl,
                      type:"POST",
                      dataType:"json",
                      data:res.result.payParam,
                      success:function (ret) {
                          if (ret.code>=1){
                              postcall(res.result.locaUrl,{pay_url:ret.pay_url,returnUrl:ret.returnUrl},'','get');
                          }
                      },error:function () {
                          $.DialogByZ.Alert({Title: "错误", Content: "支付失败"});
                      }
                  });
                } else{
                    window.location.href=res.result;
                }
            },error:function (xhr) {
                $.DialogByZ.Alert({Title: "错误", Content: "支付失败"});
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

    var isDis=0;
    function sel_coupon(obj) {
        $('body').centermenu({
            duration:600,
            source:<?php  echo $coupon_str;?>,
            liWidth:300,
            liHeight:50,
            click:function (ret) {
                if (onlineAmount==0){
                    return;
                }
                $('.cpt_selectCenterMenu').remove();
                $('.cpt-dw-mask').remove();
                $('body,html').css({height: 'auto', overflow: 'auto'});
                $(document.body).css({'border-right': 'none',});
                if (!ret.ele.context.lastChild.dataset.cid==""){
                    var coid =ret.ele.context.lastChild.dataset.cid;
                    if (coid==isDis){
                        isDis = 0;
                        $('.coupon_sel').html('<img style="width: 37px;height: 20px;float: right;margin-right: -11px;" src="../addons/sz_yi/static/images/右箭头.png">');
                        $(ret.ele.context).removeClass('w');
                        $('.price-text4').children(':first').children().html('¥ ' + onlineAmount);
                        return false;
                    }
                    isDis = coid;
                    $('.coupon_sel').html('<span style="float: right; color: #ccc;">'+ret.ele.context.lastChild.innerText+'>'+'</span>');
                    $('#isCoupon').val(coid);
                    $(ret.ele.context).attr('class','w');
                    $.ajax({
                        url:"<?php  echo $this->createMobileUrl('shop/easy_deliver_cart')?>",
                        type:"POST",
                        dataType:"json",
                        data:{receive_id:coid,amount:onlineAmount},
                        success:function (res) {
                            if (ret.status==1){
                                $.DialogByZ.Alert({Title:"提示",Content: "使用失败"});
                            }else{
                                $('.price-text4').children(':first').children().html('¥ ' + res.result);
                            }
                        },error:function () {
                            $.DialogByZ.Alert({Title:"提示",Content: "使用失败"});
                        }
                    });
                }
            }
        });
    }
</script>
</body>
</html>