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
	$Tel =empty($tel)? 0 : $tel;//会员注册手机号码；
	
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
    <title>微信实名授权签约</title>
    <meta name="keywords" content="停车">
    <meta name="description" content="基于Bootstrap3">
    <link rel="shortcut icon" href="favicon.ico"> <link href="./css/bootstrap.min14ed.css?v=3.3.6" rel="stylesheet">
    <link href="./css/font-awesome.min93e3.css?v=4.4.0" rel="stylesheet">
    <link href="./css/sweetalert.css" rel="stylesheet">
    <link href="./css/animate.min.css" rel="stylesheet">
    <link href="./css/Togrands.css" rel="stylesheet">
    <script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <script type="text/javascript" src="./js/address.js"></script>
    
    <link rel="stylesheet" href="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="http://cdn.static.runoob.com/libs/jquery/2.1.1/jquery.min.js"></script>
	<script src="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	
	<style type="text/css">
    .mydiv
    {
        text-align: center;font-size: 9px;z-index: 99;width:500px;height:300px;left: 50%;top: 39%;
        margin-left: -250px !important;margin-top: -150px !important;margin-top: 0px;position: fixed !important;
        position: absolute;
        _top: expression(eval(document.compatMode && 
        document.compatMode=='CSS1Compat') ? 
        documentElement.scrollTop + (document.documentElement.clientHeight-this.offsetHeight)/2 :/*IE6*/ 
        document.body.scrollTop + (document.body.clientHeight - this.clientHeight)/2); /*IE5 IE5.5*/
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
        _top: expression(eval(document.compatMode && document.compatMode=='CSS1Compat') ? 
        documentElement.scrollTop + (document.documentElement.clientHeight-this.offsetHeight)/2 :/*IE6*/ 
        document.body.scrollTop + (document.body.clientHeight - this.clientHeight)/2); /*IE5 IE5.5*/
    }
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
	<body class="gray-bg" onload="loadAddress();">
		
		<div class="wrapper wrapper-content animated fadeInUp" style="overflow: hidden;">
        	<div class="row">
            	<div class="col-sm-12">          		
            		
						<form name="form1" class="form-horizontal" style="text-align: left;">						
							
							<div class="form-group">
							    <div class="col-sm-offset-2 col-sm-3">
							      <!--<h3 align="center"></h3>-->
							       <button type="button" class="btn btn-primary btn-lg btn-block" style="border-radius: 1px;background: #1086c8;">微信实名授权签约</button>
							    </div>
							</div>
							<input type="hidden" class="form-control" id="Tel" maxlength="11" name="Tel" placeholder="会员注注册手机号" value="<?php echo $Tel?$Tel:'';?>">

							<!-- 持卡人姓名 -->
							<div class="form-group">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">真实姓名:</label>
							    <div class="col-sm-2">
							      	<input type="text" class="form-control" id="UserName" name="UserName" placeholder="请输入您的真实姓名" value="" >
							       	<span id="UserNametext">请输入中文姓名</span>
							    </div>
							</div>
							  
							  
							<div class="form-group">
							    <label for="inputPassword3" class="col-sm-2 control-label lefts">身份证号:</label>
							    <div class="col-sm-2">
							      <input type="text" class="form-control" id="CertNo" name="CertNo" maxlength="18" placeholder="请输入您的身份证号码" value="">
							      <span id="CertNotext">您的身份证号码输入不正确</span>
							      <!--<input type="text" class="form-control" id="CertNo" name="CertNo" placeholder="证件号" value="44010519900314086x">-->
							    </div>
							</div>
							  
							<div class="form-group">
							  	<label for="inputPassword3" class="col-sm-2 control-label lefts">关联车辆号牌:</label>
							    <div class="input-group col-sm-4">
							    	
							    	<div class="input-group-btn">
							            <select id="CarColor" name="CarColor" class="form-control input-sm" style="height: 34px;">
							                <option value="" disabled="disabled">请选择车牌颜色</option>
									        <option value="blue" selected="selected">蓝 色</option>
									        <option value="yellow">黄 色</option>
									        <option value="green">绿 色</option>
									        <option value="white">白 色</option>
									        <option value="black">黑 色</option>
							            </select>
							        </div>
							    	
							        <div class="input-group-btn">
							            <select id="id_type" name="id_type" class="form-control input-sm" style="height: 34px;">
							                <option value="" disabled="disabled">请选择车籍</option>
							                <option value="22" selected="selected">粤X</option>							                
							            </select>
							        </div>
							        <input type="text" class="form-control" id="CarNo" name="CarNo" placeholder="持卡人车牌号"  value="">
							    </div>
							    <p style="width: 100%;text-align: center;">
							    	<span id="CarNotext" style="margin:5px auto;">您输入的车牌号码有误</span>
							    </p>
							</div>
							
							<p style="width: 100%;text-align: center;">
								<span id="CarNotexts" style="color: red;"></span>
							</p>
							
							<div class="form-group">
							    <div class="col-sm-offset-2 col-sm-3" align="center">
							      	<input type="hidden" name="token" id="token" value="Sign" />
							      	<button type="button" class="btn btn-primary" onclick="doSubmit()">同意提交，前往微信</button>&nbsp;
						      		<input type="reset" value="信息有误，重置信息" class="btn btn-danger" />
							    </div>
							</div>
						</form>
				</div>
			</div>
		</div>
		
		
		
	<div >
		<!--<div onclick="showDiv()" style="display:block; cursor:pointer">点击弹出div</div>-->
		<div id="popDiv" class="mydiv" style="display: none;">
		    <div class="SContent-box">
		        <div class="Close_btn">
		        	<img width="50%" height="50%" src="./images/sign.png"/>
		            <!--<a href="#" onclick="closeDiv()"  style="display:block; cursor:pointer;z-index: 3;">点击关闭</a>-->
		            <!--<lable onclick="closeDiv()"  style="display:block; cursor:pointer;z-index: 3;">点击关闭</lable>-->
		        </div>
		    </div>
		</div>
		<div id="bg" class="bg" style="display: none;">
	</div>
		
	<script type="text/javascript" src="./js/Wxsfree.js"></script>
	</body>
</html>