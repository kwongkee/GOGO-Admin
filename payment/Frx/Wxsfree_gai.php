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
	
	// 禁止除了微信浏览器外的浏览器访问
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	if (strpos($user_agent, 'MicroMessenger') === false) {
		// 非微信浏览器禁止浏览
		exit("HTTP/1.1 401 Unauthorized");
	} /*else {
		// 微信浏览器，允许访问
		echo "MicroMessenger";
		// 获取版本号
		preg_match('/.*?(MicroMessenger\/([0-9.]+))\s*//*', $user_agent, $matches);
		echo 'Version:'.$matches[2];
	}*/
	
	$tel = trim($_GPC['Tel']);
	$Tel = empty($tel)? 0 : $tel;//会员注册手机号码；
	// 添加用户OPENID
	$phone = pdo_get('parking_authorize',array('mobile'=>$Tel),array('id','auth_status','openid','CarNo'));
	if(empty($phone))
	{
		// 跳转注册
		$url = "http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.reg";
		header("Location:".$url);
		exit;
	}
	// 已签约跳转停车输码
	if($phone['auth_status'] == 1 ) {
		
		$sendData['Token']  = 'CheckCarNoSign';
		$sendData['inType'] = 'PARKING';
		$sendData['CarNo']  = $phone['CarNo'];
		$sendData['openid'] = $phone['openid'];
		$res = CheckCarNo($sendData);
		if($res['userState'] == 'NORMAL') {// 已签约状态
			if(empty($phone['auth_status']) || empty($phone['auth_type'])){
				$resd = pdo_update('parking_authorize',['auth_status'=>1,'auth_type'=>'a:1:{s:2:"wx";s:7:"Fwechat";}'], array('id' => $phone['id']));
			}
			
			$url = "http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=parking.auth";
			header("Location:".$url);
			exit;
		} else if(!empty($res['path']) && $res['userState'] == 'UNAUTHORIZED') {// 检测未签约状态，直接跳转前往签约
			if(empty($phone['auth_status']) || empty($phone['auth_type'])){
				$resd = pdo_update('parking_authorize',['auth_status'=>1,'auth_type'=>'a:1:{s:2:"wx";s:7:"Fwechat";}'], array('id' => $phone['id']));
			}
			$url = $res['path'];
			header("Location:".$url);
			exit;
		}
	}
	
	// 查询车主签约服务状态
	function CheckCarNo($sendData) {
		$url = 'http://shop.gogo198.cn/payment/Frx/Frx.php';
		$curlpost = new Curl;//实例化
		$res = $curlpost->post($url,$sendData);
		//$res = self::PostData($url,$sendData);
		// 返回数据
		$res = json_decode($res->response,true);
		return $res;
	}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=0.5, maximum-scale=2.0, user-scalable=no">
    <title>微信实名授权签约</title>
    <meta name="keywords" content="伦教停车">
    <meta name="description" content="基于Bootstrap">
    <link rel="shortcut icon" href="favicon.ico"> <link href="./css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="./css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="./css/sweetalert.css" rel="stylesheet">
    <link href="./css/animate.min.css" rel="stylesheet">
    <link href="./css/Togrands.css" rel="stylesheet">
    <!--script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script-->
    <script type="text/javascript" src="./js/address.js"></script>
    <link rel="stylesheet" href="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="http://cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	
	<!--<link rel="stylesheet" type="text/css" href="https://shop.gogo198.cn/addons/ewei_shopv2/static/css/newcss/basic.css">-->
    <script type="text/javascript" src="https://shop.gogo198.cn/addons/ewei_shopv2/static/js/new-js/jquery-2.2.1.min.js"></script>
    <script type="text/javascript" src="https://shop.gogo198.cn/addons/ewei_shopv2/static/js/new-js/resize.js"></script>
    <script type="text/javascript" src="https://shop.gogo198.cn/addons/ewei_shopv2/static/js/new-js/swipe.js" ></script>
	<style type="text/css">
	body{background-color: #F6F7F9;}
    .mydiv
    {
        text-align: center;font-size: 9px;z-index: 99;width:500px;height:300px;left: 50%;top: 39%;
        margin-left: -250px !important;margin-top: -150px !important;margin-top: 0px;position: fixed !important;
        position: absolute;
    }
    
    .SContent-box
    {
        width:500px;height:300px;/*background:red;*/
       	/*padding-top: 1%;*/
    }
    
    .bg
    {
        background-color: #666;width: 100%;height: 100%;left: 0;top: 0;
        filter: alpha(opacity=50); opacity: 0.5;z-index: 2;
        position: fixed !important; /*FF IE7*/position: absolute;
    }
        .sub{
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            border-radius: 4px;
            border: 1px solid transparent;
            color: #FFFFFF;
            background: #1b7ab6;
            border-color: #1b7ab6;
        }

        .res{
            display: inline-block;
            padding: 6px 12px;
            margin-bottom: 0;
            font-size: 14px;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            border-radius: 4px;
            border: 1px solid transparent;
            color: #FFFFFF;
            background: #c6000b;
            border-color: #c6000b;
        }
        
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
	.wx_logo span{position: absolute; left: 0.1rem; top: -0.01rem;}
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
	.bot{width: 100%; text-align: center; position: absolute;bottom: 40px;}
	.bot img{width: 20%; display: initial;}
</style>	

	<script language="javascript" type="text/javascript">
	    var Itemindex = 0;
	    function showDiv() {
	        document.getElementById('popDiv').style.display = 'block';
	        document.getElementById('bg').style.display = 'block';       
	    }
	    function closeDiv() {
	        document.getElementById('popDiv').style.display = 'none';
	        document.getElementById('bg').style.display = 'none';
	    }
	</script>

	
</head>
<div class="wx_logo"><span><img src="https://shop.gogo198.cn/addons/ewei_shopv2/static/css/images/img/LOGO0523.png" alt="" /></span>微信实名授权签约</div>
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
        	<div class="row" style=" background: #fff;">
            	<div class="col-sm-12">          		
            		
						<form name="form1" class="form-horizontal" style="text-align: left;">
							
							<div class="form-group">
							    <!--<div class="col-sm-offset-2 col-sm-3">-->
							      <!--<h3 align="center"></h3>-->
							       <!--<button type="button" class="btn btn-primary btn-lg btn-block" style="border-radius: 1px;background: #1086c8;">微信实名授权签约</button>
							    </div>-->
							</div>
							<input type="hidden" class="form-control" id="Tel" maxlength="11" name="Tel" placeholder="会员注注册手机号" value="<?php echo $Tel?$Tel:'';?>">
							<input type="hidden" class="form-control" id="opid" name="opid" value="<?php echo $phone['openid'];?>" />
							<!-- 持卡人姓名 -->
							<div class="form-group form_border">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">真实姓名:</label>
							    <div class="col-sm-2">
							      	<input type="text" class="form-control" id="UserName" name="UserName" placeholder="请输入您的真实姓名" value="" >
							       	<span id="UserNametext" style="color:red;">请输入中文姓名</span>
							    </div>
							</div>
							  
							  
							<div class="form-group form_border">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">身份证号:</label>
							    <div class="col-sm-2">
							      <input type="text" class="form-control" id="CertNo" name="CertNo" maxlength="18" placeholder="请输入您的身份证号码" value="">
							      <span id="CertNotext" style="color:red;">您的身份证号码输入不正确</span>
							      <!--<input type="text" class="form-control" id="CertNo" name="CertNo" placeholder="证件号" value="44010519900314086x">-->
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
							    <p style="width: 100%;text-align: center;">
							    	<span id="CarNotext" style="margin:5px auto;color:red;">您输入的车牌号码有误</span>
							    </p>
							</div>
							
							<p style="width: 100%;text-align: center;">
								<span id="CarNotexts"></span>
							</p>
							
							<div class="form-group" style="margin: 30px 0;">
							    <div class="col-sm-offset-2 col-sm-3" align="center">
							      	<input type="hidden" name="token" id="token" value="Sign" />
                                    <input type="button" class="sub" value="同意提交，前往微信" onclick="doSubmits()">
						      		<button type="reset" class="res" style="margin-left: 5px;">信息有误，重置信息</button>
							    </div>
							</div>

						</form>
				</div>
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


        <script>
            $("#Tel").blur(function() {
                var a = $("#Tel").val();
                if ($.trim($("#Tel").val()).length == 0) {
                    $("#Tel").val("").focus().val(a);
                    $("#Teltext").html("请输入会员注册手机号码");
                    $("#Teltext").css("display", "block")
                } else {
                    if (isPhoneNo($.trim($("#Tel").val())) == false) {
                        $("#Tel").val("").focus().val(a);
                        $("#Teltext").html("会员注册手机号码不正确");
                        $("#Teltext").css("display", "block")
                    } else {
                        $("#Teltext").css("display", "none")
                    }
                }
            });
            $("#Tel").keydown(function() {
                $("#Tel").maxLength(11)
            });


            $("#UserName").blur(function() {
                var a = $("#UserName").val();
                if ($.trim($("#UserName").val()).length == 0) {
                    $("#UserName").val("").focus().val(a);
                    $("#UserNametext").html("请输入持卡人姓名");
                    $("#UserNametext").css("display", "block")
                } else {
                    if (isChinaName($.trim($("#UserName").val())) == false) {
                        $("#UserName").val("").focus().val(a);
                        $("#UserNametext").html("持卡人姓名不正确");
                        $("#UserNametext").css("display", "block")
                    } else {
                        $("#UserNametext").css("display", "none")
                    }
                }
            });


            $("#CertNo").blur(function() {
                var b = $("#CertNo").val();
                if ($.trim($("#CertNo").val()).length == 0) {
                    $("#CertNo").val("").focus().val(b);
                    $("#CertNotext").html("请输入持卡人证件号");
                    $("#CertNotext").css("display", "block")
                } else {
                    if (!checkCertNo(b)) {
                        $("#CertNo").val("").focus().val(b);
                        $("#CertNotext").html("您输入的证件号不正确！请重新输入...");
                        $("#CertNotext").css("display", "block")
                    } else {
                        $("#CertNotext").css("display", "none")
                    }
                }
            });
            $("#CertNo").keyup(function() {
                var t = $('#CertNo').val();
                if ($.trim($('#CertNo').val()).length == 0) {
                    $('#CertNo').val('').focus().val(t);
                    $('#CertNotext').html('请输入持卡人证件号');
                    $('#CertNotext').css('display', 'block')
                } else if ($.trim($('#CertNo').val()).length == 18) {
                    if (!checkCertNo(t)) {
                        $('#CertNo').val('').focus().val(t);
                        $('#CertNotext').html('您输入的证件号不正确！请重新输入...');
                        $('#CertNotext').css('display', 'block')
                    } else {
                        $('#CertNotext').css('display', 'none')
                    }
                }
            });

            $("#CarNo").keydown(function() {
                var a = document.getElementById("CarColor");
                var b = a.selectedIndex;
                var c = a.options[b].value;
                if (c == "green") {
                    $("#CarNo").maxLength(6)
                } else {
                    $("#CarNo").maxLength(5)
                }
            });

            $("#CarNo").blur(function() {
                var a = document.getElementById("id_type");
                var b = a.selectedIndex;
                var h = a.options[b].text;
                var c = h + $("#CarNo").val();
                var i = $("#CarNo").val();
                if ($.trim($("#CarNo").val()).length == 0) {
                    $("#CarNo").val("").focus().val(i);
                    $("#CarNotext").html("请输入车牌号");
                    $("#CarNotext").css("display", "block")
                } else {
                    var e = document.getElementById("CarColor");
                    var g = e.selectedIndex;
                    var f = e.options[g].value;
                    if (f == "green") {
                        $("#CarNo").maxLength(6);
                        var d = isVehicleNumber2(c);
                        if (!d) {
                            $("#CarNo").val("").focus().val(i);
                            $("#CarNotext").html("您的车牌输入错误，请重新输入。。。");
                            $("#CarNotext").css("display", "block")
                        } else {
                            $("#CarNotext").css("display", "none")
                        }
                    } else {
                        $("#CarNo").maxLength(5);
                        var d = isVehicleNumber(c);
                        if (!d) {
                            $("#CarNo").val("").focus().val(i);
                            $("#CarNotext").html("您的车牌输入错误，请重新输入。。。");
                            $("#CarNotext").css("display", "block")
                        } else {
                            $("#CarNotext").css("display", "none")
                        }
                    }
                }
            });

            $("#CarNo").focus(function() {
                var c = document.getElementById("id_type");
                var b = c.selectedIndex;
                var a = c.options[b].text;
                if (a == "请选择车牌") {
                    alert("请选择车牌")
                }
            });

            function checkCertNo(g) {
                var c = {
                    11: "北京",
                    12: "天津",
                    13: "河北",
                    14: "山西",
                    15: "内蒙古",
                    21: "辽宁",
                    22: "吉林",
                    23: "黑龙 江",
                    31: "上海",
                    32: "江苏",
                    33: "浙江",
                    34: "安徽",
                    35: "福建",
                    36: "江西",
                    37: "山东",
                    41: "河南",
                    42: "湖 北",
                    43: "湖南",
                    44: "广东",
                    45: "广西",
                    46: "海南",
                    50: "重庆",
                    51: "四川",
                    52: "贵州",
                    53: "云南",
                    54: "西 藏",
                    61: "陕西",
                    62: "甘肃",
                    63: "青海",
                    64: "宁夏",
                    65: "新疆",
                    71: "台湾",
                    81: "香港",
                    82: "澳门",
                    91: "国 外"
                };
                var a = 0;
                var m = g;
                var b = m.length;
                if (!/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(m)) {
                    return false
                }
                if (c[parseInt(m.substr(0, 2))] == null) {
                    return false
                }
                if (b == 15) {
                    sBirthday = "19" + m.substr(6, 2) + "-" + Number(m.substr(8, 2)) + "-" + Number(m.substr(10, 2));
                    var h = new Date(sBirthday.replace(/-/g, "/"));
                    var n = h.getFullYear().toString() + "-" + (h.getMonth() + 1) + "-" + h.getDate();
                    if (sBirthday != n) {
                        return false
                    }
                    m = m.substring(0, 6) + "19" + m.substring(6, 15);
                    m = m + GetVerifyBit(m)
                }
                var l = m.substring(6, 10);
                if (l < 1900 || l > 2078) {
                    return false
                }
                m = m.replace(/x$/i, "a");
                sBirthday = m.substr(6, 4) + "-" + Number(m.substr(10, 2)) + "-" + Number(m.substr(12, 2));
                var h = new Date(sBirthday.replace(/-/g, "/"));
                if (sBirthday != (h.getFullYear() + "-" + (h.getMonth() + 1) + "-" + h.getDate())) {
                    return false
                }
                for (var f = 17; f >= 0; f--) {
                    a += (Math.pow(2, f) % 11) * parseInt(m.charAt(17 - f), 11)
                }
                if (a % 11 != 1) {
                    return false
                }
                var j = new Array();
                j = new Array("11111119111111111", "12121219121212121");
                for (var e = 0; e < j.length; e++) {
                    if (m.indexOf(j[e]) != -1) {
                        return false
                    }
                }
                return true
            }
            function isChinaName(a) {
                var b = /^[\u4E00-\u9FA5]{1,6}$/;
                return b.test(a)
            }
            function isCertNo(a) {
                var b = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
                return b.test(a)
            }
            function isCardNo(a) {
                var b = /^\d{16}$/g;
                return b.test(a)
            }
            function is_CardNo(a) {
                var b = /^\d{19}$/g;
                return b.test(a)
            }
            jQuery.fn.maxLength = function(a) {
                this.each(function() {
                    var c = this.tagName.toLowerCase();
                    var b = this.type ? this.type.toLowerCase() : null;
                    if (c == "input" && b == "text" || b == "password") {
                        this.maxLength = a
                    } else {
                        if (c == "textarea") {
                            this.onkeypress = function(h) {
                                var f = h || event;
                                var g = f.keyCode;
                                var d = document.selection ? document.selection.createRange().text.length > 0 : this.selectionStart != this.selectionEnd;
                                return !(this.value.length >= a && (g > 50 || g == 32 || g == 0 || g == 13) && !f.ctrlKey && !f.altKey && !d)
                            };
                            this.onkeyup = function() {
                                if (this.value.length > a) {
                                    this.value = this.value.substring(0, a)
                                }
                            }
                        }
                    }
                })
            };



            function doSubmits() {

                var c = document.getElementById("id_type");
                var d = c.selectedIndex;
                var e = c.options[d].text;
                var f = e + $("#CarNo").val();
                var g = document.getElementById("CarColor");
                var h = g.selectedIndex;
                var i = g.options[h].value;
                var opid = $('#opid').val();

                if (checkForm()) {

                    $.ajax({
                        type: 'POST',
                        url: 'WxsfreeSigntwo.php',
                        async: false,
                        dataType: 'text',
                        data: {
                            Tel: $("#Tel").val(),
                            CardNo: $("#CardNo").val(),
                            UserName: $("#UserName").val(),
                            CertNo: $("#CertNo").val(),
                            Phone: $("#Phone").val(),
                            CarNo: f,
                            token: $("#token").val(),
                            Color: i,
                            openid:opid,
                        },
                        success: function(a) {
                            var b = jQuery.parseJSON(a);

                            if (b.msg == 'success') {
                                var Itemindex = 0;
                                document.getElementById('CarNotexts').style.display = 'block';
                                $('#CarNotexts').css('color','green');
                                document.getElementById('CarNotexts').innerHTML = b.info;
                                // 设置跳转
                                setInterval(function () {
                                    window.location.href = b.path;
                                },800);

                            } else {
                                document.getElementById('CarNotexts').style.display = 'block';
                                $('#CarNotexts').css('color','red');;
                                document.getElementById('CarNotexts').innerHTML = b.info;
                            }
                        }
                    })

                } else {
                    document.getElementById('CarNotexts').style.display = 'block';
                    $('#CarNotexts').css('color','red');
                    document.getElementById('CarNotexts').innerHTML = '请填写所有必填选项！';
                }
            }


            function myCheck() {
                for (var a = 0; a < document.form1.elements.length - 1; a++) {
                    if (document.form1.elements[a].value == " ") {
                        document.form1.elements[a].focus();
                        return false
                    }
                }
                return true
            }

            function checkForm() {
                var a = document.form1.getElementsByTagName("input");
                for (var b = 0; b < a.length; b++) {
                    if (a[b].value == "") {
                        return false
                    }
                }
                return true
            }
            function isVehicleNumber(b) {
                var a = false;
                if (b.length == 7) {
                    var c = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$/;
                    a = c.test(b)
                }
                return a
            }
            function isVehicleNumber2(b) {
                var a = false;
                if (b.length == 8) {
                    var c = /^(([一-龥][a-zA-Z]|[一-龥]{2}\d{2}|[一-龥]{2}[a-zA-Z])[-]?|([wW][Jj][一-龥]{1}[-]?)|([a-zA-Z]{2}))([A-Za-z0-9]{5}|[DdFf][A-HJ-NP-Za-hj-np-z0-9][0-9]{4}|[0-9]{5}[DdFf])$/;
                    a = c.test(b)
                }
                return a
            }
            function loadAddress() {
                var a = "";
                for (var b in address) {
                    a += '<option value="' + b + '">' + address[b] + "</option>"
                }
                $("#id_type").append(a)
            };
            
            
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

        <!--script type="text/javascript" src="./js/Wxsfreetwo.js"></script-->

	</body>
</html>