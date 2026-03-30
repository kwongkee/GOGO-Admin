<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta http-equiv="Cache-Control" content="no-cache"/>
<title>会员登录 - <?php  echo $config['webtitle'];?></title>
<meta content="width=device-width, initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport" />
<meta name="MobileOptimized" content="240" />
<meta http-equiv="Expires" content="0" />
<meta http-equiv="Cache-Control" content="no-cache" />
<meta http-equiv="Pragma" content="no-cache" />
<meta content="yes" name="apple-mobile-web-app-capable" />
<meta content="black" name="apple-mobile-web-app-status-bar-style" />
<link rel="stylesheet" href="<?php  echo $templateurl;?>css/yun_wap_member.css" type="text/css"/>
<link rel="stylesheet" href="<?php  echo $templateurl;?>css/wap_css.css" type="text/css"/>
<script type="text/javascript">
	function ck_ckd(){					
		var username = document.getElementById('username').value;
		if (username == ""){
			alert("手机号/姓名不能为空!");
			document.getElementById('username').focus();
			return false; 
		}
		var password = document.getElementById('password').value;
		if (password == ''){
			alert("密码不能为空!");
			document.getElementById('password').focus();
			return false; 
		}
	}
</script>
</head>

<body>

<header>
  <div class="header_bg"> <a class="hd-lbtn" href="<?php  echo $this->createMobileUrl('index');?>"><i class="header_top_l iconfont"></i></a>
    <div class="header_h1">会员登录</div>
  </div>
</header>


<div class="login_body">
  <section class="list">
    <article>
      <div class=" ">
        <div class="login_body_cont">
          <form action="<?php  echo $urltk;?>" method="post" enctype="multipart/form-data" class="js-ajax-form" onSubmit="return ck_ckd();">
			<input type="hidden" name="token" value="<?php  echo $_W['token'];?>" />
            <dl class="forminputitem">
              <dd>
                <div class="c inputitem_w"> <i class="reg_icon_font login_icon_n1 "></i>
                  <input name="username" type="text" id="username" value="" placeholder="手机号/姓名" class="inputitemtxt">
                </div>
              </dd>
              <dd>
                <div class="c ico_eye_close inputitem_w"> <i class="reg_icon_font login_icon_n2 "></i>
                  <input name="password" type="password" id="password"  class="inputitemtxt" placeholder="请输入密码" >
                  <em class="viewpwd" id="showPwd" onclick="showPwd(this)"></em> </div>
              </dd>
              <dd>
                <div class="login_body_xc"> <span class="photochk">
				<?php  if($config['register_open']=='1') { ?>
                <a href="<?php  echo $this->createMobileUrl('registered');?>" class="getpwd" style="float:left;">免费注册</a>
				<?php  } ?>
                <a href="<?php  echo $this->createMobileUrl('wjpassword');?>" class="getpwd">忘记密码</a> </span></div>
              </dd>
              <dd>
                <input type="hidden" name="checkurl" value="" />
                <input type="submit" name="do_submit" id="sublogin"  value="登   录" class="inputSubmit">
              </dd>
            </dl>
          </form>
        </div>
      </div>
    </article>
  </section>
</div>





<!--<footer>
  <div class="clear"></div>
  <div class="footer_box_bot"></div>
  <div class="footer_sum">
    <div class="bottom_sum">
      <div class="bottom_con">
        <div class="classify "> <a href="课堂首页.html" id="indexclick" class="fotter_nav_link"> <i class="footer_icon iconfont_home"></i> <span class="fotter_nav_span">首页</span> </a> </div>
        <div class="classify"  > <a href="班级加入.html"  class="fotter_nav_link"> <em class="fotter_nav_link"> <i class="footer_icon iconfont_jobhome"></i> <span class="fotter_nav_span">加入班级</span> </em></a> </div>
        <div class="classify footer_nav_cur"> <a href="javascript:void(0);" id="jobclick" class="fotter_nav_link"> <i class="iconfont_homemore"></i> <span class="iconfont_homemore_bg"></span> <span class="fotter_nav_span">&nbsp;</span> </a> </div>
        <div class="classify"> <a href="未完成的作业.html" class="fotter_nav_link"> <em class="fotter_nav_link"> <i class="footer_icon iconfont_userhome"></i> <span class="fotter_nav_span">今日作业</span> </em> </a> </div>
        <div class="classify"> <a href="微信第一次登录.html" class="fotter_nav_link"> <i class="footer_icon iconfont_myhome"></i> <span class="fotter_nav_span">个人中心</span> </a> </div>
      </div>
    </div>
  </div>
  <div style="width:100%;height:100%; background:rgba(0,0,0,0.9); position:fixed;left:0px;right:0px;bottom:0px;top:0px ; z-index:10000;display:none" id="footerjob">
    <div class="foot_nav_close"></div>
    <div class="foot_nav_box" style="width:100%; position:absolute;">
      <ul class="foot_nav_box_list">
        <li><a href="未完成的作业.html" class="foot_nav_box_a"><span class="foot_nav_box_list_icon cor_3"><i class="nav_icon iconfont_part "></i></span>
          <div class="foot_nav_box_name">作业管理</div>
          </a> </li>
        <li> <a href="今日错题.html" class="foot_nav_box_a"><span class="foot_nav_box_list_icon cor_2"><i class="nav_icon iconfont_comp "></i></span>
          <div class="foot_nav_box_name">错题管理</div>
          </a> </li>
        <li> <a href="知识点首页.html" class="foot_nav_box_a"><span class="foot_nav_box_list_icon cor_4"><i class="nav_icon iconfontuser "></i></span>
          <div class="foot_nav_box_name">知识点管理</div>
          </a> </li>
        <li> <a href="班级管理.html" class="foot_nav_box_a"><span class="foot_nav_box_list_icon cor_6"><i class="nav_icon iconfont_zph "></i></span>
          <div class="foot_nav_box_name">班级管理</div>
          </a> </li>
        <li> <a href="英文作文.html" class="foot_nav_box_a"><span class="foot_nav_box_list_icon cor_5"><i class="nav_icon iconfont_map "></i></span>
          <div class="foot_nav_box_name">英语作文</div>
          </a></li>
        <li> <a href="作文.html" class="foot_nav_box_a"><span class="foot_nav_box_list_icon cor_8"><i class="nav_icon iconfont_news "></i></span>
          <div class="foot_nav_box_name">语文作文</div>
          </a> </li>
        <li> <a href="统计知识点.html" class="foot_nav_box_a"><span class="foot_nav_box_list_icon cor_wd"><i class="nav_icon iconfont_ask "></i></span>
          <div class="foot_nav_box_name">统计分析</div>
          </a> </li>
        <li> <a href="帮助说明.html" class="foot_nav_box_a"><span class="foot_nav_box_list_icon cor_jf"><i class="nav_icon iconfont_jf "></i></span>
          <div class="foot_nav_box_name">帮助说明</div>
          </a> </li>
      </ul>
    </div>
  </div>
</footer>
<script>

$(document).ready(function () {

    $('#jobclick').click('click', function () {

      $('#footerjob').toggle();

      $('#footerresume').hide();

    });  

	$('#footerjob').click('click', function () {

		$('#footerjob').hide();

    });

    $('#reg_mune_box').click('click', function () {

      $('#reg_mune').toggle();

      $('#footerresume').hide();

    });  

	$('#reg_mune').click('click', function () {

		$('#reg_mune').hide();

    });

	$('#reg_mune_boxs').click('click', function () {

      $('#reg_mune').toggle();

      $('#footerresume').hide();

    });  

	$('#reg_mune').click('click', function () {

		$('#reg_mune').hide();

    });

});



</script>-->
</body>
</html>