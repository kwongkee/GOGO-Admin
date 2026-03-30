<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>支付<?php  echo $data['project_name'];?></title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:39px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub,.info_sub2,.info_sub3 {height:44px; margin:14px 5px; background:#f15353; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .order_sub12,.btn1-w{height: 44px;margin: 14px 5px;background: rgb(6,192,95);border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
    .order_sub13,.btn1-z{height: 44px;margin: 14px 5px;background: #2e78d0;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
    .order_sub14,.btn1-y{height: 44px;margin: 14px 5px;background: #1790ff;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
    .order_otherpay,.btn1-o{height: 44px;margin: 14px 5px;background: #f15900;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}

    .footBox{width:100%;display:flex;align-items: center;justify-content: space-evenly;margin-top:10px;}
    .check_collect{box-sizing:border-box;padding:5px 15px;font-size:16px;color:#fff;background:#2e78d0;border-radius:4px;}
    .nocheck_collect{box-sizing:border-box;padding:5px 15px;font-size:16px;color:#fff;background:#f15900;border-radius:4px;}

    .paymode_show{display:block;}
    .paymode_hide{display:none;}
    .fa-check-circle-o{color:#0c9;}

    /**上传凭证**/
    .info_main .images {float: left; width:auto;height:30px;margin-top:7px;}
    .info_main .images .img { float:left; position:relative;width:30px;height:30px;border:1px solid #e9e9e9;margin-right:5px;}
    .info_main .images .img img { position:absolute;top:0; width:100%;height:100%;}
    .info_main .images .img .minus { position:absolute;color:red;width:8px;height:12px;top:-18px;right:-1px;}
    .info_main .plus { float:left; width:30px;height:30px;border:1px solid #e9e9e9; color:#dedede;; font-size:18px;line-height:30px;text-align:center;margin-top:4px;}
    .info_main .plus i { left:7px;top:7px;}
    button, input, optgroup, select, textarea{font:unset !important;color:black !important;line-height:1.5 !important;}
    .red{color:#ff5555;}
    .green{color:#05c504;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />

<link href="../addons/sz_yi/template/mobile/default/static/js/star-rating.css" media="all" rel="stylesheet" type="text/css"/>
<script src="../addons/sz_yi/template/mobile/default/static/js/star-rating.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/ajaxfileupload.js" type="text/javascript"></script>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/static/css/bootstrap.min.css" />
<script type="text/javascript" src="../addons/sz_yi/template/pc/default/static/js/bootstrap.min.js"></script>

<div id="container" style="padding-bottom:15px;">

    <div class="page_topbar" style="display: flex;align-items: center;">
        <div class="title" style="margin-left:110px;">支付<?php  echo $order['ordersn'];?></div>
    </div>
    <div class="info_main">
        <div class="line"><div class="title">交易单号</div><div class='info'><div class='inner'><?php  echo $order['ordersn'];?></div></div></div>
        <div class="line"><div class="title">交易类型</div><div class='info'><div class='inner'>集运服务</div></div></div>
        <div class="line"><div class="title">交易名称</div><div class='info'><div class='inner'><?php  echo $order['service_statusname'];?></div></div></div>
    </div>

    <div class="info_main">
        <div class="line"><div class="title">交易总额</div><div class='info'><div class='inner'>CNY <?php  echo $order['service_price'];?></div></div></div>
        <div class="line"><div class="title">实付金额</div><div class='info'><div class='inner'>CNY <?php  echo $order['service_price'];?></div></div></div>
        <div class="line"><div class="title">支付状态</div><div class='info'><div class='inner' style="color:#ff2222;"><?php  echo $order['order_statusname'];?></div></div></div>
    </div>
<div class="info_main">
    <div class="line"><div class="title">收款人名称</div><div class='info'><div class='inner'>佛山市钜铭商务资讯服务有限公司</div></div></div>
    <div class="line"><div class="title">收款人电话</div><div class='info'><div class='inner'>0757-86329911</div></div></div>
    <div class="line"><div class="title">付款人名称</div><div class='info'><div class='inner'><?php  echo $user['realname'];?></div></div></div>
    <div class="line"><div class="title">付款人电话</div><div class='info'><div class='inner'><?php  echo $user['phone'];?></div></div></div>
</div>

<div class="info_main">
    <div class="line"><div class="title">生成时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$order['createtime'])?></div></div></div>
    <?php  if($pay_status==1) { ?>
    <div class="line"><div class="title">支付时间</div><div class='info'><div class='inner'><?php  echo date('Y-m-d H:i:s',$order['paytime'])?></div></div></div>
    <?php  } ?>
</div>
<?php  if($pay_status==0 && $_GPC['isadmin']!=1) { ?>
<div class="button btn1-w order_sub12" >微信支付</div>
<div class="button btn1-z order_sub13" >支付宝支付</div>
<div class="button btn1-y order_sub14" >余额支付（￥<?php  echo $user['balance'];?>）</div>
    <?php  if($order['check_status']==0) { ?>
<!--        <div class="button btn1-o orderdelaypay">申请赊账</div>-->
    <?php  } ?>
<!--<div class="button btn1-o order_otherpay">我已/要通过其它方式付款</div>-->
<?php  } ?>
</div>
<div class="mask" style="display:none;position: fixed; margin: 0px; padding: 0px; opacity: 0.6; background: rgb(0, 0, 0); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;left:0;top:0;"></div>
<div class="complete_baseinfo" style="display:none;width:80%;height:200px;z-index: 1000;position:fixed;top:50%;left:50%;transform: translate(-50%,-50%);">
    <div class="info_main" style="width:100%;padding:20px;box-sizing: border-box;">
        <?php  if(empty($user['phone'])) { ?>
        <div class="line"><div class="title">手机号</div><div class='info'><div class='inner'><input type="text" name="phone" id="phone" value="" placeholder="请输入您的手机号"></div></div></div>
        <?php  } ?>
        <?php  if(empty($user['email'])) { ?>
        <div class="line"><div class="title">邮箱号</div><div class='info'><div class='inner'><input type="text" name="email" id="email" value="" placeholder="请输入您的邮箱号"></div></div></div>
        <?php  } ?>
        <?php  if(empty($user['idcard'])) { ?>
        <div class="line"><div class="title">身份证号</div><div class='info'><div class='inner'><input type="text" name="idcard" id="idcard" value="" placeholder="请输入您的身份证号"></div></div></div>
        <?php  } ?>
        <?php  if(empty($user['realname'])) { ?>
        <div class="line"><div class="title">真实姓名</div><div class='info'><div class='inner'><input type="text" name="realname" id="realname" value="" placeholder="请输入您的真实姓名"></div></div></div>
        <?php  } ?>
        <div class="button btn1-z baseinfo_btn">提交信息</div>
        <div class="button btn1-o close_btn">关闭</div>
    </div>
</div>
<div id="bankaccount_qrcode" style="display: none;transform: translate(-50%,-50%);transition: all 0.2s;top: 50%;left: 50%;position: fixed;z-index:1000;"></div>
<div class="saveaspng" style="color:#fff;display:none;transform: translate(-50%,-50%);transition: all 0.2s;top: 70%;left: 50%;position: fixed;z-index:1000;">长按二维码保存</div>
<script id="tpl_img" type="text/html">
    <div class='img' data-img='<%filename%>'>
        <img src='<%url%>'  onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png">
        <div class='minus'><i class='fa fa-minus-circle'></i></div>
    </div>
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('order/pay/wechat_jie', TEMPLATE_INCLUDEPATH)) : (include template('order/pay/wechat_jie', TEMPLATE_INCLUDEPATH));?>
<!-- wechat pay -->
<form action="" id="wecpay" method="post">
    <input type="hidden" name="tid" id="tid" value="" />
    <input type="hidden" name="opid" id="opid" value="" />
    <input type="hidden" name="fee" id="fee" value="" />
    <input type="hidden" name="title" id="title" value="" />
    <input type="hidden" name="acc" id="acc" value="" />
    <input type="hidden" name="ky" id="ky" value="" />
    <input type="hidden" name="uniacid" id="uniacid" value="" />
    <input type="hidden" name="to" id="to" value="" />
    <input type="hidden" name="project" id="project" value="" />
</form>
<!--<script src="../addons/sz_yi/template/pc/default/static/js/jquery.min.js"></script>-->
<script src="../addons/sz_yi/static/resource/js/lib/jquery.qrcode.min.js"></script>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {

        //赊账支付
        $('.orderdelaypay').click(function(){
            core.json('gather/pay',{orderid:"<?php  echo $order['id'];?>",openid:"<?php  echo $openid;?>",op:'delaypay',pa:3},function(json){
                if(json.status==-1){
                    core.tip.show(json.result.msg);
                    //弹出完善资料框
                    $('.mask').show();
                    $('.complete_baseinfo').show();
                }else if(json.status==-2){
                    core.tip.show(json.result.msg);
                    setInterval(function(){
                        $.ajax({
                            url: 'https://decl.gogo198.cn/api/auth_verify',
                            method: 'post',
                            data: {
                                'mobile': "<?php  echo $user['phone'];?>",
                                'idcard': "<?php  echo $user['idcard'];?>",
                                'realname': "<?php  echo $user['realname'];?>",
                                'reg_type':2,
                                'is_merch':2
                            },
                            dataType: 'JSON',
                            success: function (rres) {
                                $.ajax({
                                    url: 'https://decl.gogo198.cn/api/record_person',
                                    method: 'post',
                                    data: {
                                        'id':"<?php  echo $info['id'];?>",
                                        'form': rres,
                                    },
                                    dataType: 'JSON',
                                    success: function (rres2) {
                                        window.location.href=rres2.url;
                                    }
                                });
                            }
                        });
                    },2000);
                }else if(json.status==0 || json.status==-3){
                    core.tip.show(json.result.msg);
                    if(json.status==0){
                        setInterval(function(){
                            window.location.reload();
                        },2000);
                    }
                }
            });
        });
        //关闭赊账
        $('.close_btn').click(function(){
            $('.mask').hide();
            $('.complete_baseinfo').hide();
        });
        //提交基本信息
        $('.baseinfo_btn').click(function(){
            let phone = $('#phone').val();
            <?php  if(empty($user['phone'])) { ?>
                if(phone==''){
                    core.tip.show('请输入手机号');return false;
                }
            <?php  } ?>
            let email = $('#email').val();
            <?php  if(empty($user['email'])) { ?>
                if(email==''){
                    core.tip.show('请输入邮箱号');return false;
                }
            <?php  } ?>
            let idcard = $('#idcard').val();
            <?php  if(empty($user['idcard'])) { ?>
            if(idcard==''){
                core.tip.show('请输入身份证号');return false;
            }
            <?php  } ?>
            let realname = $('#realname').val();
            <?php  if(empty($user['realname'])) { ?>
            if(realname==''){
                core.tip.show('请输入真实姓名');return false;
            }
            <?php  } ?>
            core.json('gather/pay',{orderid:"<?php  echo $order['id'];?>",openid:"<?php  echo $openid;?>",op:'delaypay',pa:1,phone:phone,email:email,idcard:idcard,realname:realname},function(json){
                if(json.status==-1){
                    core.tip.show(json.result.msg);
                }else{
                    core.tip.show(json.result.msg);
                    setInterval(function(){
                        window.location.reload();
                    },2000);
                }
            });
        });

        //获取支付信息和提交支付
        core.json('gather/pay',{orderid:"<?php  echo $order['id'];?>",openid:"<?php  echo $openid;?>",op:'pay'},function(json){
            var result = json.result;
            if(json.status!=1){
                core.tip.show(result);
                return;
            }
            //通莞微信支付  2017-11-01
            if(result.tgwechat.success){
                $('.order_sub12').click(function() {
                    core.json('gather/pay', {op: 'tgpay',type: 'tgwechat', orderid:"<?php  echo $order['id'];?>",openid:"<?php  echo $openid;?>"}, function (rjson) {

                        if(rjson.status!=1) {
                            $('.button').removeAttr('submitting');
                            core.tip.show(rjson.result);
                            return;
                        }

                        var tgw = rjson.result.tgwechat;

                        //2018-08-21
                        $('#wecpay').attr('action', 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Paymentess.php');
                        $('#tid').val(tgw.tid);
                        $('#opid').val(tgw.openid);
                        $('#title').val(tgw.title);
                        $('#fee').val(tgw.fee);
                        $('#acc').val(tgw.account);
                        $('#ky').val(tgw.key);
                        $('#to').val(tgw.token);
                        $('#uniacid').val(tgw.uniacid);
                        $('#project').val('gatherpay');
                        $('#wecpay').submit();

                    },true,true);
                })
            }
            //2017-11-01
            //通莞支付宝 2017-11-20
            if(result.tgalipay.success){

                $('.order_sub13').click(function(){
                    //数据请求order/pay/op = pay & type = alipay;  没有数据返回：只返回状态 status = 1;
                    location.href = core.getUrl('gather/pay',{op: 'tgpay',type: 'tgalipay', orderid:"<?php  echo $order['id'];?>",openid:"<?php  echo $openid;?>"});
                })
            }

            //余额支付
            $('.order_sub14').click(function(){
                core.json('gather/pay',{orderid:"<?php  echo $order['id'];?>",op:'balancepay'},function(json) {
                    if (json.status == -1) {
                        core.tip.show(json.result.msg);
                    }else if(json.status == 0){
                        core.tip.show(json.result.msg);
                        setInterval(function(){
                            window.location.reload();
                        },2000);
                    }
                });
            });
        },true);

        //收款审核
        $('.check_collect').click(function (){
            //确认收款
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'examine',orderid:"<?php  echo $data['id'];?>",type:1},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result);
                    }else{
                        alert('审核成功！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });
        $('.nocheck_collect').click(function (){
            //未予收款
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'examine',orderid:"<?php  echo $data['id'];?>",type:2},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result);
                    }else{
                        alert('审核成功，消息已下发！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        $('#receipt_status').change(function(){
            let selected = $(this).val();
            if(selected==1 || selected==2){
                $('.should_payMoney').hide();
            }else if(selected==3){
                $('.should_payMoney').show();
            }
        });
        $('.pay_sure').click(function(){
            window.location.href="./index.php?i=3&c=entry&do=member&p=custompayment&op=sure_attestation&m=sz_yi&oid=<?php  echo $data['id'];?>";
        });
        $('.info_sub3').click(function(){
            let receipt_status = $('#receipt_status').val();
            let should_payMoney = $('#should_payMoney').val();
            if(receipt_status==3 && $('#should_payMoney').isEmpty()){
                alert('请输入应到账金额');return;
            }
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'examine',orderid:"<?php  echo $data['id'];?>",type:receipt_status,should_payMoney:should_payMoney},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result);
                    }else{
                        alert('审核成功，消息已下发！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        //现金支付时间
        var currYear = (new Date()).getFullYear();
        var opt = {};
        opt.date = {preset: 'date'};
        opt.datetime = {preset: 'datetime'};
        opt.time = {preset: 'time'};
        opt.default = {
            theme: 'android-ics light',
            display: 'modal',
            mode: 'scroller',
            lang: 'zh',
            startYear: currYear,
            endYear: currYear+1
        };
        $("#cash_paytime").scroller('destroy').scroller($.extend(opt['datetime'], opt['default']));

        //上传凭证
        $('.plus input').change(function() {
            core.loading('正在上传');

            var comment =$(this).closest('.img_info');
            var ogid = comment.data('ogid');
            var max = comment.data('max');

            $.ajaxFileUpload({
                url: core.getUrl('util/uploader'),
                data: {file: "imgFile" + ogid,'op':'uploadFile'},
                secureuri: false,
                fileElementId: 'imgFile' + ogid,
                dataType: 'json',
                success: function(res, status) {
                    core.removeLoading();
                    var obj = $(tpl('tpl_img', res));
                    $('.images',comment).append(obj);

                    $('.minus',comment).click(function() {
                        core.json('util/uploader', {op: 'remove', file: $(obj).data('img')}, function(rjson) {
                            if (rjson.status == 1) {
                                $(obj).remove();
                            }
                            $('.plus',comment).show();
                        }, false, true);
                    });

                    if ($('.img',comment).length >= max) {
                        $('.plus',comment).hide();
                    }
                }, error: function(data, status, e) {
                    core.removeLoading();
                    core.tip.show('上传失败!');
                }
            });
        });

        //其它支付
        $('.order_otherpay').click(function(){
            $('.otherpay_btn').show();
            $('.otherpay_btn').css('display','flex');
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay_btn').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //我已通过xxxx
        $('.btn_left').click(function(){
            $('.otherpay_bankaccount').hide();
            $('.otherpay').show();
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //我要通过xxxx
        $('.btn_right').click(function(){
            $('.otherpay').hide();
            $('.otherpay_bankaccount').show();
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay_bankaccount').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //付款方式
        $('.payment_mode').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.payment_mode').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#payment_mode').val(val);
            if(val==1){
                $('.transfer_type').show();
                $('.cash_type').hide();
            }else if(val==2) {
                $('.cash_type').show();
                $('.transfer_type').hide();
            }
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //账号类型
        $('.bankaccount_mode').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.bankaccount_mode').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#bankaccount_mode').val(val);
            if(val==1){
                $('#bank_account1').show();
                $('#bank_account2').hide();
            }else if(val==2) {
                $('#bank_account2').show();
                $('#bank_account1').hide();
            }
            var h=$(window).scrollTop(); //获取当前滚动条距离顶部的位置
            var op_height = $('.otherpay_bankaccount').height();
            $("html,body").animate({ scrollTop: h+op_height }, 800);//点击按钮向下移动800px，时间为800毫秒
        });

        //其他方式提交
        $('.info_sub').click(function(){
            let payment_mode = $('#payment_mode').val();
            //转账
            let pay_account = $('#pay_account').val();
            let transfer_price = $('#transfer_price').val();
            let collect_account = $('#collect_account').val();
            let transfer_demo = [];
            $('.img_info[data-ogid=0]').find('.img').each(function(){
                transfer_demo.push($(this).data('img'));
            });
            //现金
            let true_pay_price = $('#true_pay_price').val();
            let cash_paytime = $('#cash_paytime').val();
            let collect_staff = $('#collect_staff').val();
            let collect_demo = [];
            $('.img_info[data-ogid=1]').find('.img').each(function(){
                collect_demo.push($(this).data('img'));
            });

            if(payment_mode==1){
                //转账
                if($('#pay_account').isEmpty()){
                    alert('请输入付款账户!');
                    return;
                }
                if($('#transfer_price').isEmpty()){
                    alert('请输入转账金额!');
                    return;
                }
                if($('#collect_account').isEmpty()){
                    alert('请输入收款账户!');
                    return;
                }
                if( transfer_demo.length=='' || transfer_demo.length==0){
                    alert('请上传转账凭证!');
                    return;
                }
            }else if(payment_mode==2){
                //现金
                if($('#true_pay_price').isEmpty()){
                    alert('请输入实际支付额!');
                    return;
                }
                if($('#cash_paytime').isEmpty()){
                    alert('请选择支付时间!');
                    return;
                }
                if($('#collect_staff').isEmpty()){
                    alert('请输入收款职员名称!');
                    return;
                }
                if( collect_demo.length=='' || collect_demo.length==0){
                    alert('请上传收款凭证!');
                    return;
                }
            }

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'otherpay',orderid:"<?php  echo $data['id'];?>",'payment_mode':payment_mode,'pay_account':pay_account,'transfer_price':transfer_price,'collect_account':collect_account,'transfer_demo':transfer_demo,'true_pay_price':true_pay_price,'cash_paytime':cash_paytime,'collect_staff':collect_staff,'collect_demo':collect_demo},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result.msg);
                    }else{
                        alert('提交成功！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });

        $('.mask').click(function(){
            $('#bankaccount_qrcode').hide();
            $('.mask').hide();
            $('.saveaspng').hide();
        });

        //银行账户提交
        $('.info_sub2').click(function(){
            let bankaccount_mode = $('#bankaccount_mode').val();
            if(bankaccount_mode==1){
                var bank_account = $('#bank_account1').val();
            }else if(bankaccount_mode==2){
                var bank_account = $('#bank_account2').val();
            }
            if(bank_account=='' || typeof(bank_account)=='undefined'){
                alert('请选择账户');return false;
            }

            //下载pdf\
            $('#bankaccount_qrcode').html('');
            jQuery('#bankaccount_qrcode').qrcode({
                render: "canvas",
                width: 250,
                height: 250,
                text: "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&op=save_bankaccount_pdf&bankMode="+bankaccount_mode+"&bankAcc="+bank_account
                // text: "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&m=sz_yi&p=bind_decl"
            });

            var canvas = document.getElementsByTagName('canvas');
            var image = new Image();
            image.src = canvas[0].toDataURL("image/png");
            $('#bankaccount_qrcode').html(image);
            $('#bankaccount_qrcode').show();
            $('.saveaspng').show();
            $('.mask').show();


            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/custompayment');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'otherpay_bankaccount',orderid:"<?php  echo $data['id'];?>",'bankaccount_mode':bankaccount_mode,'bank_account':bank_account},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result.msg);
                    }else{
                        alert('提交成功！');
                        // setTimeout(function(){
                        //     window.location.reload();
                        // },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });
    })
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>