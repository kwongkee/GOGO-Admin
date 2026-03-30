<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>商户提现费率</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:95%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:15px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .back{height: 44px;margin: 14px 5px;background: #1E9FFF;border-radius: 4px;text-align: center;font-size: 16px;line-height: 44px;color: #fff;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />

<div id="container">
    <div class="page_topbar">
        <div class="title">结算提现费率</div>
    </div>
    <?php  if(empty($fee)) { ?>
        <div class="line" style="text-align:center;">请联系管理员配置费率</div>
    <?php  } else { ?>
        <div class="info_main">
            <div class="line"><div class="title">行业</div><div class='info'><div class='inner'>
                <input type="text" name="fee" id="industry" value="<?php  echo $fee['industry'];?>" readonly>
            </div></div></div>
            <div class="line"><div class="title">储蓄卡费率</div><div class='info'><div class='inner'>
                <input type="text" name="fee" id="bankcard_rate" value="<?php  echo $fee['bankcard_rate'];?>" readonly>
            </div></div></div>
            <div class="line"><div class="title">信用卡费率</div><div class='info'><div class='inner'>
                <input type="text" name="fee" id="creditcard_rate" value="<?php  echo $fee['creditcard_rate'];?>" readonly>
            </div></div></div>
            <div class="line"><div class="title" style="white-space:nowrap;">费率封顶金额</div><div class='info'><div class='inner'>
                <input type="text" name="fee" id="rate_limit" value="CNY <?php  echo $fee['rate_limit'];?>" readonly>
            </div></div></div>
        </div>
    <?php  } ?>
    <div class="button back" onclick="javascript:history.back(-1);">返回</div>
</div>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {

    })
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>