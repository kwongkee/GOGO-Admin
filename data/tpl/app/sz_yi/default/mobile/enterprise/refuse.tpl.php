<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8" />
<title>调查结束</title>
<!--手机端需要添加-->
<meta name="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no">
<!--手机端需要添加---->
<link rel="stylesheet" href="../addons/sz_yi/template/mobile/default/enterprise/static/css/test_rx.css">
<script src="../addons/sz_yi/template/mobile/default/enterprise/static/js/jquery-1.8.3.min.js"></script>
<script src="../addons/sz_yi/template/mobile/default/enterprise/static/js/jQuery.fontFlex.js"></script>
<script>
$(document).ready(function(e) {
  //320宽度的时候html字体大小是20px;、640宽度的时候html字体大小是40px;
  $('html').fontFlex(20, 40, 16);   
  
});
</script>
</head>
<body>

<div class="wjdt_title">
  <div class="header">
  	<h3>尽职调查</h3>
  </div>
<div class="dtks_box" >

   <!--题目-->
  <div class="finish">
      <div class="pic"><img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/icon2.png" alt=""></div>
      <div class="finish-txt">
        <h3>抱歉，未合要求！</h3>
        <p>此次问卷调查结束。</p>
        <p style="padding-top: 0;">感谢您的配合。祝您生活愉快！</p>
      </div>
  </div>
</div>
</div>
<!--结束------------------------------------------>

<div class="footer">
	<img src="../addons/sz_yi/template/mobile/default/enterprise/static/images/logo.png" alt=""> &nbsp;&nbsp;技术支持
</div>

<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui.js" charset="UTF-8"></script>
<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui-accordion.js"></script>
<script type="text/javascript">
	hui.accordion(true, true);
</script>
</body>
</html>

