<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>绑定集运商</title>
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
    <div class="logo">
        <div class="logoimg"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo2.png" alt="" /></div>
        <div class="logo-txt"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/txt.png" alt="" /></div>
    </div>
    <div class="info_main">
        <?php  if($enterprise_members['id']>0) { ?>
            <p style="font-size:16px;text-align:center;font-weight:600;color:#06bb14;">已绑定集运商账号！请自行退出。</p>
        <?php  } else { ?>
            <div class="line"><div class="title">手机号</div><div class='info'><div class='inner'><input type="text" id='tel' placeholder="请输入法人的手机号"  value="" /></div></div></div>
            <div class="info_sub">立即绑定</div>
        <?php  } ?>
    </div>
    <div class="footer">
        <img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt=""> &nbsp;&nbsp;技术支持

    </div>
</script>

<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        $('#container').html(tpl('member_info'));

        $('.info_sub').click(function() {
            if(!$('#tel').isMobile()){
                core.tip.show('请输入正确手机号码!');
                return;
            }

            $.ajax({
                url: 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=enterprise&m=sz_yi&p=get_openid',
                method: 'post',
                data: {
                    'tel': $('#tel').val(),
                },
                dataType: 'JSON',
                success: function (res) {
                    alert(res.msg);
                    if (res.code == 0) {
                        setTimeout(function(){
                            window.location.reload();
                        },1000);
                        return false;
                    }
                }
            });
        });
    });
</script>