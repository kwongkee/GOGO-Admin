<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>跨境代购</title>
<link href="https://shop.gogo198.cn/app/resource/css/common.min.css" rel="stylesheet">
<link href="https://shop.gogo198.cn/app/resource/css/bootstrap.min.css" rel="stylesheet">
<style type="text/css">
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
</style>
<style type="text/css">
    body{
        font: ;
        color:#555;
        padding:0;
        margin:0;
        background-image:url('https://shop.gogo198.cn/attachment/images/3/2021/01/cXWtGBJCnTvIGQ5PGEfP7IpI1Zgtag.jpeg');
        background-size:cover;
        background-color:#fbf5df;
    }
    a{color:#ffffff; text-decoration:none;}
    .container{padding:0 !important;}
    .home-container .box-item{background:unset !important;}
    .home-container{margin: 7.6em .3em .6em !important; width: 100% !important;}
    .home-container .box-item {width: 8em !important; height: 8em !important;}
    .home-container i{height: 80px !important; width: 80px !important;}.home-container{width:60%;overflow:hidden;margin:.6em .3em;}
    .home-container .box-item{float:left;display:block;text-decoration:none;outline:none;width:4em;height:6em;margin:.1em;background:rgba(0, 0, 0, 0.3);text-align:center;color:#ccc;}
    .home-container i{display:block;height:45px; margin: 5px auto; font-size:35px; padding-top:10px; width:45px;}
    .home-container span{color:#ffffff;display:block; width:90%; margin:0 5%;  overflow:hidden; height:20px; line-height:20px;}
    .footer{color:#dddddd;}
    .home-container ul li{background-color:rgba(0, 0, 0, 0.3);padding:0 10px;margin:1%;display: inline-block;height:45px;width:100%;}
    .home-container ul li a{text-decoration: none;}
    .home-container .title{color:#ccc;}
    .home-container .createtime{color:#999;font-size:12px}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />
<div class="container container-fill">
    <div class="home-container clearfix">
        <a href="javascript:history.back(-1);" class="box-item" style="width:120px !important;position:relative;">
            <i style="background:url('https://shop.gogo198.cn/attachment/back_up.png') no-repeat;background-size:100%;" class="icon"></i>
            <span style="color:;" title="返回上一页">返回上一页</span>
        </a>
        <a href="./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=merchant" class="box-item" style="width:120px !important;position:relative;">
            <i style="background:url('https://shop.gogo198.cn/attachment/images/3/2021/01/D55WDd00o87R8mDDRXW500rjDMom7W.png') no-repeat;background-size:100%;" class="icon"></i>
            <span style="color:;" title="卖家入口">卖家入口</span>
        </a>
        <a href="./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=buyer" class="box-item" style="width:120px !important;position:relative;">
            <i style="background:url('https://shop.gogo198.cn/attachment/images/3/2021/01/D55WDd00o87R8mDDRXW500rjDMom7W.png') no-repeat;background-size:100%;" class="icon"></i>
            <span style="color:;" title="买家入口">买家入口</span>
        </a>
    </div>
</div>


<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {

    })
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>