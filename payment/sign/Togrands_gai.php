<?php
define('IN_MOBILE', true);
define('PDO_DEBUG', true);
require_once '../../framework/bootstrap.inc.php';
require_once '../../app/common/bootstrap.app.inc.php';
load()->app('common');
load()->app('template');
load()->func('diysend');
global $_W;
global $_GPC;

	$tel = trim($_GPC['Tel']);
	$Tel =empty($tel)?0:$tel;//会员注册手机号码；
	
	$phone = pdo_get('parking_authorize',array('mobile'=>$Tel),array('id','auth_status'));
	if(empty($phone))
	{
		$url = "http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.reg";
		header("Location:".$url);
		exit;
	}
	if($phone['auth_status'] == 1 ) {
		
		$url = "http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth";
		header("Location:".$url);
		exit;
	}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=no">
    <title>银行卡授权签约</title>
    <meta name="keywords" content="HTML,响应式">
    <meta name="description" content="最新版本开发的扁平化主题">
    <link rel="shortcut icon" href="favicon.ico"> <link href="./css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="./css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="./css/sweetalert.css" rel="stylesheet">
    <link href="./css/animate.min.css" rel="stylesheet">
    <link href="./css/Togrands.css" rel="stylesheet">
    <!--<link href="css/style.min862f.css?v=4.1.0" rel="stylesheet">-->
    <!--<script type="text/javascript" src="./js/jquery.min.js"></script>-->
    <script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="./js/address.js"></script>
    
    <script type="text/javascript" src="https://shop.gogo198.cn/addons/ewei_shopv2/static/js/new-js/jquery-2.2.1.min.js"></script>
    <script type="text/javascript" src="https://shop.gogo198.cn/addons/ewei_shopv2/static/js/new-js/resize.js"></script>
    <script type="text/javascript" src="https://shop.gogo198.cn/addons/ewei_shopv2/static/js/new-js/swipe.js" ></script>
    <!-- 车牌号码 JS -->
    <!--<script type="text/javascript" src="http://gogoshop.oss-cn-shenzhen.aliyuncs.com/address.js?spm=5176.8466032.bucket-object.dopenurl.19311450nNvjx7&Expires=1523865148&OSSAccessKeyId=TMP.AQH3EVR1ru1_ggNxtm1GefXOihW-PPZPrMXMiJ2ytjbnG1pMyoQO1fXJBZxqADAtAhUAkcYPE6OAWctrAj1TghYVjlAT7JcCFE_t5a9dS2DD12yX2t21rkjB8Fqr&Signature=yHhCsxobPLOXyLyDoKA4cqsTbhk%3D"></script>-->

<style type="text/css">
	*{    font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;}
	    .wx_box{ width:100%; max-width:768px; margin:0 auto; position:relative;}
	.banner{width: 100%; position: relative;}
	.notice_b{width: 100%;height:auto; overflow:hidden;position: absolute;bottom: 0; z-index: 99; opacity: 0.8;}
	.notice{ padding:0.09rem 0.42rem 0.08rem 0.44rem; background:#000 url(https://shop.gogo198.cn/addons/ewei_shopv2/static/css/images/img/tips_icon.png) no-repeat 0.15rem 0.12rem; background-size:0.16rem auto; color:#fff; font-size:0.13rem; line-height:0.2rem; overflow:hidden; position:relative; height:0.5rem; }
	.notice>div{ height:0.4rem; overflow:hidden;}
	.notice ul{ float:left; overflow:hidden;}
	.notice ul li{ padding-right:0.16rem; text-align:justify;}
	.notice ul li a{ color:#fff; opacity: 1;}
	.notice em{ display:block; width:0.42rem; position:absolute; right:0; top:0.06rem; height:0.4rem; text-align:center; line-height:0.4rem; background:url(https://shop.gogo198.cn/addons/ewei_shopv2/static/images/img/close.png) no-repeat center;
	background-size:0.16rem auto; border-left:1px solid rgba(255,255,255,0.23);}
	.focus{overflow:hidden;position:relative; height:1.6rem; background:#ccc;}
	.focus img{ width:100%;height:1.6rem; vertical-align:middle;}
	.focus ul{-webkit-padding-start:0px;}
	.focus>ol{height:0.06rem;position:absolute;z-index:10;text-align:center; width:100%; bottom:0.1rem; font-size:0;}
	.focus>ol>li{display:inline-block;width:0.06rem;height:0.06rem;background-color:#D5D0CC;border-radius:0.08rem; margin-right:0.06rem; border:1px solid #D5D0CC;}
	.focus>ol>li.on{background-color:#ffffff; border:1px solid #3D8CE8;}
	.focus>ol>li:last-child{ margin-right:0;}
	.wx_logo{background: #fff; height: 0.5rem; width: 100%; text-align: center; line-height: 0.5rem; font-size: 0.18rem; color: #1b7ab6;}
	.wx_logo span{position: absolute; left: 0.1rem; top: 0.05rem;}
	.wx_logo img{width: 0.4rem; }
	.navv{ height:0.65rem; line-height:0.5rem;overflow:hidden; background:#fff; font-size:0.14rem; padding: 2% 0 0;}
	.navv li{ float:left; box-sizing:border-box; width:33%; background-size:0.15rem 0.15rem !important; text-indent:0rem; }
	.navv li:nth-child(1){/* width:0.4rem; height: 0.4rem;*/ border-left:none; border-right:none; /*border-radius: 50%;*/ background: url(https://shop.gogo198.cn/addons/ewei_shopv2/static/css/images/img/lisicon_05.png) no-repeat 0.53rem 0.09rem;}
	.navv li:nth-child(2){/* width:0.4rem; height: 0.4rem;*/ border-left:none; border-right:none; /*border-radius: 50%; */background: url(https://shop.gogo198.cn/addons/ewei_shopv2/static/css/images/img/lisicon_06.png) no-repeat 0.53rem 0.09rem;}
	.navv li:nth-child(3){ /*width:0.4rem; height: 0.4rem;*/ border-left:none; border-right:none; /*border-radius: 50%;*/ background: url(https://shop.gogo198.cn/addons/ewei_shopv2/static/css/images/img/lisicon_07.png) no-repeat 0.53rem 0.09rem;}
	.navv li a{color: #333;}
	.nav_icon01{ background:none;}
	.nav_icon02{ background:none;}
	.nav_icon03{ background:none;}
	.nav_icon01 div{width:0.3rem; height: 0.3rem;border-radius: 50%; border: 1px solid #4aa8e4; margin: 0 0.45rem;}
	.nav_icon02 div{width:0.3rem; height: 0.3rem;border-radius: 50%; border: 1px solid #e44a4a; margin: 0 0.45rem;}
	.nav_icon03 div{width:0.3rem; height: 0.3rem;border-radius: 50%; border: 1px solid #e4c94a; margin: 0 0.45rem;}
	.nav_p{text-align: center; line-height: 30px;}
	.footer{ line-height:0.61rem; height:0.61rem;font-family: PingFang-SC-Bold;font-size:0.12rem;color: #333333;background:none;
background-size:0.48rem auto;width:100%; position:absolute; bottom:0px; text-indent:1.22rem;}
	.footer_placeholder{ height:0.61rem; width:100%;}
	
/*底部滚动*/	
	.mes{ width: 100%;height:35px; /*background:#000;*/ overflow:hidden;position: absolute;bottom: 0;opacity:.8;filter:alpha(opacity=60);}
	.t_news{ height:19px; color:#fff; padding-left:10px; margin:8px 0; overflow:hidden; position:relative;}
	.t_news b{ line-height:19px; font-weight:bold; display:inline-block;}
	.t_news b img{width: 20%;}
	.news_li,.swap{ line-height:19px; display:inline-block; position:absolute; top:0; left:0;}
	.news_li a,.swap a{color:#333;}
	.swap{top:19px;}	
	
	
	.form-control {
	
    display: block;
    width: 100%;
    height: 0.4rem;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    color: #333;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    border-radius: 4px;
    -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
    -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
    -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
    transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
}
	.lefts {left: 5px;}
	.form_border{width: 95%;border-bottom: 1px solid #ccc;padding-top: 15px;padding-bottom: 15px;margin: 0 auto;}
	.form-horizontal .form-group{margin: 0 auto;}
	#CarColor{text-indent: 0;border-radius: 5px; margin-right: 5px; width: 0.8rem; margin-left: 16px;}
	#id_type{text-indent: 0;border-radius: 5px; margin-right: 5px; width: 0.8rem; }
	.btn-primary {
    color: #fff;
    background-color: #1b7ab6;
    border-color: #1b7ab6;
}
.btn-danger {
    color: #fff;
    background-color: #c6000b;
    border-color: #c6000b;
}
.bot{width: 100%; text-align: center; position: absolute;bottom: 40px;}
	.bot img{width: 20%; display: initial;}
</style>

</head>
<div class="wx_logo"><span><img src="https://shop.gogo198.cn/addons/ewei_shopv2/static/css/images/img/LOGO0523.png" alt="" /></span>银行卡授权签约</div>
	<body class="gray-bg" onload="loadAddress();" style="background-color: #F6F7F9;">
		
		<div class="wx_box">
    <div class="banner">
    	
    <!--{if !empty($announcement)}-->
    <div class="notice_b">
	    <div class="notice">
	        <div>
	            <ul>
	                <!--{loop $announcement $index $item}-->
	                <!--<li><a href="#">{$item['msg']}</a></li>-->
	                <li><a href="#">伦教停车已营运,车主停车时请扫/输码确认,离开时系统智能计/免费,服务热线:4009313038</a></li>
	                <li><a href="#">伦教停车已营运,车主停车时请扫/输码确认,离开时系统智能计/免费,服务热线:4009313038</a></li>
	               <!-- {/loop}-->
	            </ul>
	        </div>
	        <em></em>
	    </div>
  	</div>
   <!-- {/if}-->
    <!--focus-->
    <div class="focus" id="focus">
        <ul>
            <!--{if !empty($carousel)}
            {loop $carousel $index $item}
            <li><a href="{$item}"><img src="https://shop.gogo198.cn/addons/ewei_shopv2/static/images/img/{$index}" style="width:100%;"/></a>
            </li>
            {/loop}
            {else}-->
            <li><a href="#"><img src="https://shop.gogo198.cn/addons/ewei_shopv2/static/images/img/0.jpg" style="width:100%;" /></a></li>
            <li><a href="#"><img src="https://shop.gogo198.cn/addons/ewei_shopv2/static/images/img/002.jpeg" style="width:100%;" /></a></li>
            <li><a href="#"><img src="https://shop.gogo198.cn/addons/ewei_shopv2/static/images/img/003.jpeg" style="width:100%;" /></a></li>
           <!-- {/if}-->
        </ul>
        <ol>
            <li class="on"></li>
            <li></li>
            <li></li>
        </ol>
    </div>
    <!--focus end-->
    
    
    
	</div>

    <ul class="navv">
        <li class="nav_icon01"><a href="https://www.sobot.com/chat/pc/index.html?sysNum=0e3544a96d5e41f4919655542ba43428"><div></div><p class="nav_p">在线客服</p></a></li>
        <li class="nav_icon02"><div></div><p class="nav_p">服务导航</p></li>
        <li class="nav_icon03"><div></div><p class="nav_p">用户须知</p></li>
    </ul>
		
		<div class="wrapper wrapper-content animated fadeInUp" style="overflow: hidden;">
        	<div class="row" style="background: #fff;margin-top: 0.08rem;">
            	<div class="col-sm-12">
						<form name="form1" onsubmit="return myCheck()" class="form-horizontal" style="text-align: left;">						
							  <div class="form-group">
							    <!--<div class="col-sm-offset-2 col-sm-3">-->
							      <!--<h3 align="center"></h3>-->
							       <!--<button type="button" class="btn btn-primary btn-lg btn-block" style="border-radius: 1px;background: #1086c8;">银行卡授权签约</button>
							    </div>-->
							  </div>
							    <input type="hidden" class="form-control" id="Tel" maxlength="11" name="Tel" placeholder="会员注注册手机号" value="<?php echo $Tel?$Tel:'';?>">
							  <!-- 签约银行卡  -->
							<div class="form-group form_border">
							  	<label for="inputPassword3" class="col-sm-2 control-label lefts">签约银行卡:</label>
							    <div class="input-group col-sm-2">
							        <div class="input-group-btn">
							            <select id="carType" name="carType" class="form-control input-sm fonsIndex" style="height: 0.4rem;border-radius: 5px;width: 92%;margin: 0 4%;text-indent: 0;">
							                <option value="" disabled="disabled">请选择银行卡类型</option>
							                <option value="Ucard" selected="selected">银联标识 信用卡</option>
	        								<option value="DCard" >顺德农商 借记卡</option>							               
							            </select>
							        </div>
							    </div>
							</div>
							  
							<!-- 银行卡号码 -->
							<div class="form-group form_border">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">银行卡号码:</label>
							    <div class="col-sm-2">
							      <input type="text" class="form-control" id="CardNo" maxlength="16" name="CardNo" placeholder="持卡人银行卡号" value="">
							      <span id="CardNotext">您的银行卡号码输入格式不正确</span>
							    </div>
							</div>


							<!-- 持卡人姓名 -->
							  <div class="form-group form_border">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">持卡人姓名:</label>
							    <div class="col-sm-2">
							      <input type="text" class="form-control" id="UserName" name="UserName" placeholder="持卡人姓名" value="" >
							       <span id="UserNametext">请输入中文姓名</span>
							    </div>
							  </div>
							  	
							  	<!-- 持卡人证件 -->
							  <div class="form-group form_border">
							  	<label for="inputPassword3" class="col-sm-2 control-label lefts">持卡人证件:</label>
							    <div class="input-group col-sm-2">
							        <div class="input-group-btn">
							            <select id="IDcard" name="IDcard" class="form-control input-sm fonsIndex" style="height: 0.4rem;border-radius: 5px;width: 92%;margin: 0 4%;text-indent: 0;">
							                <option value="" disabled="disabled">请选择持卡人证件</option>
							                <option value="ID_card" selected="selected">国内 居民身份证</option>
									        <option value="TA_card">军籍 军官/文职/义务兵/士官/职工证</option>
									        <option value="HK_card">港澳 居民来往内地通行证</option>
									        <option value="TW_card">台湾 居民来往大陆通行证</option>						               
							            </select>
							        </div>
							    </div>
							</div>
							  
							  <div class="form-group form_border">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">证件号码:</label>
							    <div class="col-sm-2">
							      <input type="text" class="form-control" id="CertNo" name="CertNo" maxlength="18" placeholder="持卡人证件号" value="">
							      <span id="CertNotext">您的证件号码输入不正确</span>
							      <!--<input type="text" class="form-control" id="CertNo" name="CertNo" placeholder="证件号" value="44010519900314086x">-->
							    </div>
							  </div>
							  
							  <div class="form-group form_border">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">银行预留手机:+86</label>
							    <div class="col-sm-2">
							      <input type="text" class="form-control" id="Phone" maxlength="11" name="Phone" placeholder="持卡人银行卡预留手机号" value="">
							      <span id="Phonetext">您输入的预留手机号格式不正确</span>
							    </div>
							  </div>
							  
							  <div class="form-group form_border">
							  	<label for="inputPassword3" class="col-sm-2 control-label lefts">关联车辆号牌:</label>
							    <div class="input-group col-sm-4">
							    	
							    	<div class="input-group-btn">
							            <select id="CarColor" name="CarColor" class="form-control input-sm" style="height: 0.4rem;">
							                <option value="" disabled="disabled">请选择车牌颜色</option>
									        <option value="blue" selected="selected">蓝 色</option>
									        <option value="yellow">黄 色</option>
									        <option value="green">绿 色</option>
									        <option value="white">白 色</option>
									        <option value="black">黑 色</option>
							            </select>
							        </div>
							    	
							        <div class="input-group-btn">
							            <select id="id_type" name="id_type" class="form-control input-sm" style="height: 0.4rem;">
							                <option value="" disabled="disabled">请选择车籍</option>
							                <option value="22" selected="selected">粤X</option>							                
							            </select>
							        </div>
							        <input type="text" class="form-control" id="CarNo" name="CarNo" placeholder="持卡人车牌号"  value="" style="height: 0.4rem;border-radius: 5px;width: 1.55rem;">
							    </div>
							    <span id="CarNotext" style="margin:5px auto;margin-left: 10px;">您输入的车牌号码有误</span>
							</div>
							
							
							<!-- 咪表停车协议  -->
							<!--div class="form-group" id="show" style="display: none;">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">
							    	咪表停车协议
							    	<a href="./agreement.html" style="text-decoration: none;">《伦教停车咪表协议书》</a>
							    </label>
							    <input type="checkbox" id="sdcheck" aria-label="check" value=""> 同意协议 
							    <span id="showtext" style="margin:5px auto;margin-left: 10px;color: red;display: none;">请同意协议</span>
						  	</div -->

							<div class="form-group" style="margin: 30px 0;">
							    <div class="col-sm-offset-2 col-sm-3" align="center">
							      <input type="hidden" name="token" id="token" value="Sign" />
							      <button type="button" class="btn btn-primary" onclick="doSubmit()">同意授权，提交签约</button>&nbsp;
							      <input type="reset" name="" id="" value="信息有误，重置信息" class="btn btn-danger" />
							    </div>
							</div>
							  
						</form>
				</div>
				
				<form action="" method="post" id="sign_form">
					<input type="hidden" name="PACKET" id="signedxml" value=""/>
				</form>
				
				<form action="" method="post" id="bk_form">
					<input type="hidden" name="forward" id="bksign" value=""/>
				</form>

			</div>
		</div>
		
		<div class="bot">Technology by <img src="https://shop.gogo198.cn/addons/ewei_shopv2/static/images/img/footlogo.png" alt="" /></div>
		<div class="footer">
		<div class="mes">
	    	<div class="t_news">
				<ul class="news_li">
					<li><a href="" target="_blank">版权所有 © Gogo|購購網</a></li>
					<li><a href="" target="_blank">版权所有 © Gogo|任我停</a></li>
					<li><a href="" target="_blank">版权所有 © Gogo|直邮易</a></li>
				</ul>
				<ul class="swap"></ul>
			</div>
	    </div>
	</div>
    <!--<div class="footer">版权所有 © Gogo|購購網 </div>-->
    <div class="footer_placeholder"></div>
	
</div>
		<!--kk -->
	<script type="text/javascript" src="./js/Togrands.js"></script>
	
	<script type="text/javascript">
		/* 滚动图+通知*/    
          $(function () {
        new Swipe(document.getElementById('focus'), {
            speed: 1000,
            auto: 1000,
            callback: function () {
                var lis = $(this.element).next("ol").children();
                lis.removeClass("on").eq(this.index).addClass("on");
            }
        });
    });
    
/*底部滚动*/    
    function b(){	
		t = parseInt(x.css('top'));
		y.css('top','19px');	
		x.animate({top: t - 19 + 'px'},'slow');	//19为每个li的高度
		if(Math.abs(t) == h-19){ //19为每个li的高度
			y.animate({top:'0px'},'slow');
			z=x;
			x=y;
			y=z;
		}
		setTimeout(b,3000);//滚动间隔时间 现在是3秒
	}
	$(document).ready(function(){
		$('.swap').html($('.news_li').html());
		x = $('.news_li');
		y = $('.swap');
		h = $('.news_li li').length * 19; //19为每个li的高度
		setTimeout(b,3000);//滚动间隔时间 现在是3秒
		
	})  
	</script>
	</body>
</html>