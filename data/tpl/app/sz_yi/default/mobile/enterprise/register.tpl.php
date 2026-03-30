<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>尽职调查注册</title>
<style type="text/css">
    body {margin:0px; background:#fff; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;position: relative;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:14px; line-height:44px; color:#fff;}
    .register {float:right;width:46%;height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:14px; line-height:44px; color:#fff;}
    .nobindmobile {clear:both;height:44px; margin:14px 5px; background:#ccc; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .hide {display: none;}
    .info_sub, .info_price .sub{background: #2a9df8 !important;}
    .logo{width: 100%;background: url(../addons/sz_yi/template/mobile/default/enterprise/static/images/login_bg.jpg) no-repeat;background-size: 100%; height: 15rem;}
    .logo-txt{/*font-size: 1.8rem; color: #217bc1; */text-align: center; padding-top: 1rem;}
    .logo-txt img{width: 9rem;}
    .logoimg img {
        width: 60%;
        margin: 13% 0 0 20%;
    }
    .address_sub1, .address_sub2, .info_sub, .refund_sub1, .register{
        margin: 30px 2% 0 !important;
    }
    .footer{width: 100%; position: fixed; bottom: 0; height: 2.6rem; line-height: 2.6rem; color: #666; text-align: center;font-size: 0.8rem; background: white; box-shadow: 1px 1px 5px rgb(0,0,0,0.5);}
    .footer img{width: 6rem; position: relative; top: 0.2rem;}

</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../addons/sz_yi/static/js/dist/area/cascade.js"></script>
<div id="container"></div>
<script id="member_info" type="text/html">
    <!--<div class="page_topbar">
    <a href="javascript:;" class="back" onclick="history.back()"><i class="fa fa-angle-left"></i></a>
    <div class="title">绑定手机</div>
</div>-->
    <div class="logo">
        <div class="logoimg"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo2.png" alt="" /></div>
        <div class="logo-txt"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/txt.png" alt="" /></div>
    </div>
    <div class="info_main">
        <div class="line"><div class="title">注册类别</div><div class='info'><div class='inner'>
            <select name="reg_type" id="reg_type">
                <option value="1">企业注册</option>
                <option value="2">个人注册</option>
            </select>
        </div></div></div>
        <div class="line realname" style="display:none;"><div class="title">姓名</div><div class='info'><div class='inner'><input type="text" id='realname' placeholder="请输入您的真实姓名"  value="" /></div></div></div>
        <div class="line"><div class="title">身份证号</div><div class='info'><div class='inner'><input type="text" id='idcard' placeholder="请输入法人的身份证号"  value="" /></div></div></div>
        <div class="line"><div class="title">手机号码</div><div class='info'><div class='inner'><input type="number" id='mobile' placeholder="请输入法人的手机号码"  value="" /></div></div></div>


        <div class="line"><div class="title">验证码</div><div class='info'><div class='inner'><input type="text" id='code' placeholder="请输入验证码"  value="" /><input id="btnSendCode" style="position: absolute;right: 0;top: 0;" type="button" value="发送验证码"  /></div></div></div>

        <div class="hide">
            <div class="line"><div class="title">设置密码</div><div class='info'><div class='inner'><input type="password" id='password' placeholder="请输入您的密码"  value="" /></div></div></div>
            <div class="line"><div class="title">确认密码</div><div class='info'><div class='inner'><input type="password" id='cpassword' placeholder="请确认密码"  value="" /></div></div></div>
        </div>


    </div>
    <!--<div id="aa">11</div>-->
    <div class="info_sub">注册</div>
    <div class="footer">
        <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt=""> &nbsp;&nbsp;技术支持

    </div>
</script>
<!--<form id='alipaysubmit' name='alipaysubmit' action='https://openapi.alipay.com/gateway.do?charset=UTF-8' method='POST'><input type='hidden' name='biz_content' value='{  "certify_id":"375c0abf3035eaac6eea0a08115d8ea3"}'/><input type='hidden' name='app_id' value='2021003130690298'/><input type='hidden' name='version' value='1.0'/><input type='hidden' name='format' value='json'/><input type='hidden' name='sign_type' value='RSA2'/><input type='hidden' name='method' value='alipay.user.certify.open.certify'/><input type='hidden' name='timestamp' value='2022-05-25 16:22:48'/><input type='hidden' name='alipay_sdk' value='alipay-sdk-PHP-4.11.14.ALL'/><input type='hidden' name='charset' value='UTF-8'/><input type='hidden' name='sign' value='D7Dm2HYUbr7DcduZdyw/2MfF4BnOr6JhrZsVcaZ84X6NCRKGbdCP/FcZYFUE4BsuVxgRBLNlfdClYidChFdC2GWwv8Q6TUi3jsoeCkRWSLRoNy+B2jSsJmURaCBqoXTXaGCgLsl7mlQwUfxmJdF7JgFx9Cc8Dw3zGAcI7NMHHBR3stvkkv5BAvHNlTwTJmXbZNhyCiI2VAKVZZ2jQT6X9S0CWm5ACF0iejTNdjmoMQQEZlsW54a+Z72AMuPCIagPu36TI2HOAko3msghJCxNBHFsMj2nI1N9oPPLnT5wRp88TMkQCPvfl2ZGgmCLWRa+9lxj357u0Y77IwTFB6MZ2A=='/><input type='submit' value='ok' style='display:none;''></form><script>document.forms['alipaysubmit'].submit();</script>-->
<div class="zfb_identify_verify" style="display:none;"></div>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        $('#container').html(tpl('member_info'));

        $('#reg_type').change(function(){
           let reg_type = $(this).val();
           if(reg_type==1){
               //企业
                $('#idcard').attr('placeholder','请输入法人的身份证号');
                $('#mobile').attr('placeholder','请输入法人的手机号码');
                $('.realname').hide();
           }else if(reg_type==2){
               //个人
               $('#idcard').attr('placeholder','请输入您的身份证号');
               $('#mobile').attr('placeholder','请输入您的手机号码');
               $('.realname').show();
           }
        });

        var InterValObj; //timer变量，控制时间
        var count = 60; //间隔函数，1秒执行
        var curCount;//当前剩余秒数

        $('#btnSendCode').click(function(){
            if(!$('#mobile').isMobile()){
                core.tip.show('请输入正确手机号码!');
                return;
            }

            $.ajax({
                url:'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=sendcode',
                method : 'post',
                data : {
                    'mobile': $('#mobile').val(),
                    'op':'ismobile'
                },
                dataType: 'JSON',
                success:function(res){

                },
                error:function(res){
                    core.json('enterprise/sendcode', {
                        'mobile': $('#mobile').val(),
                        'op':'ismobile'
                    }, function(json) {
                        if(json.status==1){
                            // $('.hide').show();
                            hasmobile = true;
                        }
                    },true,true);
                }
            });



            curCount = count;
            //向后台发送处理数据
            $.ajax({
                url:'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=sendcode',
                method : 'post',
                data : {
                    'mobile': $('#mobile').val(),
                    'op':'bindmobilecode'
                },
                dataType: 'JSON',
                success:function(json){
                    // $('#aa').text(json.status);
                    if(json.status==1){
                        //设置button效果，开始计时
                        $("#btnSendCode").attr("disabled", "true");
                        $("#btnSendCode").val("请在" + curCount + "秒内输入验证码");
                        InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
                    }else{

                    }
                },
                error:function(res){
                    core.json('enterprise/sendcode', {
                        'mobile': $('#mobile').val(),
                        'op' : "bindmobilecode"
                    }, function(json) {
                        if(json.status==1){
                            //设置button效果，开始计时
                            $("#btnSendCode").attr("disabled", "true");
                            $("#btnSendCode").val("请在" + curCount + "秒内输入验证码");
                            InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
                        }else{
                            core.tip.show(json.result);
                        }

                    },true,true);
                }
            })

        });

        //timer处理函数
        function SetRemainTime() {
            if (curCount == 0) {
                window.clearInterval(InterValObj);//停止计时器
                $("#btnSendCode").removeAttr("disabled");//启用按钮
                $("#btnSendCode").val("重新发送验证码");
            }
            else {
                curCount--;
                $("#btnSendCode").val("请在" + curCount + "秒内输入验证码");
            }
        }
        //检验验证码
        function checkcode()
        {
            core.json('enterprise/sendcode', {
                'code': $('#code').val(),
                'op':'checkcode'
            }, function(json) {

                if(json.status == 0)
                {
                    core.tip.show(json.result);
                    return;
                }
            },true,true);
        }

        $('.info_sub').click(function() {
            if(!$('#mobile').isMobile()){
                core.tip.show('请输入正确手机号码!');
                return;
            }
            if(!$('#type').val()==1 && $('#idcard').isEmpty()){
                core.tip.show('请输入法人的身份证号码!');
                return;
            }
            if(!$('#type').val()==2 && $('#idcard').isEmpty()){
                core.tip.show('请输入您的身份证号码!');
                return;
            }
            if( $('#code').isEmpty()){
                core.tip.show('请输验证码!');
                return;
            }

            //core.json不行就用这个请求
            $.ajax({
                url: 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=sendcode',
                method: 'post',
                data: {
                    'code': $('#code').val(),
                    'op': 'checkcode'
                },
                dataType: 'JSON',
                success: function (res) {

                    if (res.status == 0) {
                        alert(res.result);
                        return;
                    }

                    $.ajax({
                        url: "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=bindmobile",
                        method: 'post',
                        data: {
                            'memberdata': {
                                'mobile': $('#mobile').val(),
                                'idcard': $('#idcard').val(),
                                'realname': $('#realname').val(),
                                'reg_type': $('#reg_type').val(),
                            }
                        },
                        dataType: 'JSON',
                        success: function (res) {

                            core.tip.show('绑定成功');
                            //console.log(json.result.preurl);

                            if($('#reg_type').val()==1){
                                //企业
                                location.href = "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=confirm";
                            }else if($('#reg_type').val()==2){
                                //个人
                                $.ajax({
                                    url: 'https://decl.gogo198.cn/api/auth_verify',
                                    method: 'post',
                                    data: {
                                        'mobile': $('#mobile').val(),
                                        'idcard': $('#idcard').val(),
                                        'realname': $('#realname').val(),
                                        'reg_type':2,
                                    },
                                    dataType: 'JSON',
                                    success: function (res) {
                                        $('.zfb_identify_verify').html(res);
                                    }
                                });
                            }

                        },error:function(res){
                            core.tip.show('发送失败');
                        }
                    });
                },error:function(){
                    core.json('enterprise/sendcode', {
                        'code': $('#code').val(),
                        'op':'checkcode'
                    }, function(json) {

                        if(json.status == 0)
                        {
                            core.tip.show(json.result);
                            return;
                        }
                        core.json('enterprise/bindmobile', {
                            'memberdata':{
                                'mobile': $('#mobile').val(),
                            }

                        }, function(json) {
                            if(json.status==1){
                                core.tip.show('绑定成功');
                                //console.log(json.result.preurl);
                                location.href="https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=confirm";
                            }
                            else{
                                core.tip.show('该手机已经绑定其它微信号了!');
                            }

                        },true,true);
                    },true,true);
                }
            })
        });
    })
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
