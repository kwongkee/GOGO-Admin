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

$Tel = '';//会员注册手机号码；
if(!empty($_GPC) && $_GPC['Tel']){
	$Tel = $_GPC['Tel'];
}else {
	$Tel = '';
}

//表单提交页面；
//获取车牌全部数据  
$res = pdo_fetchall("SELECT ID,CITYCODE  FROM ".tablename('parking_bas_car_homearea'), array());

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title>银行卡授权签约</title>
  <link rel="stylesheet" href="./layui/css/layui.css">
  <script src="./layui/layui.js"></script>
  <style type="text/css">
  	.show{display: none;}
  	#color{color: red;}
  	.layui-input{width: 80%;}
  	.layui-form-select .layui-edge{right: 28px;}
  	.layui-form-label{width: 106px;}
  	.layui-form-item .layui-input-inline{left: 0px;}
  </style>
</head>
<body>
 
 
<!-- 你的HTML代码 -->
	<form class="layui-form" action="" name="form1" onsubmit="return myCheck()">
		
		  <button class="layui-btn layui-btn-fluid" style="margin-bottom: 12px;"> <h3>银行卡授权签约</h3> </button>
		  <input type="hidden" name="reqPath" id="reqPath" value="http://ilazypay.com:8080/access/unionpay/signedBankard"/>
		  <!--<input type="hidden" id="transId" name="transId" placeholder="代码" value="60301">-->
		  <!--<input type="hidden" id="key" name="key" placeholder="秘钥" value="kBL1dICpPBNxomAR">-->
		  <!--<input type="hidden" id="AccessCode" name="AccessCode" placeholder="商户号" value="7000000000000004">-->
		  <input type="hidden" name="token" id="token" value="Sign" />
		  
		  
		  <div class="layui-form-item">
		    <label class="layui-form-label">会员注册手机号:</label>
		    <div class="layui-input-inline">
		      <input type="text" name="Tel" id="Tel"  maxlength="11" value="<?php echo $Tel?$Tel:'';?>" required lay-verify="required" placeholder="请输入会员注册手机号" autocomplete="off" class="layui-input">
		    </div>
		    <div class="layui-form-mid layui-word-aux show" id="Teltext">辅助文字</div>
		  </div>
		  
	  <div class="layui-form-item">
	    <label class="layui-form-label">签约银行卡:</label>
	    <div class="layui-input-block">
	      <select name="carType" id="carType" lay-verify="required">
	        <option value="">请选择银行卡类型</option>
	        <option value="Ucard" selected="selected">银联标识 信用卡</option>
	        <option value="DCard">顺德农商 借记卡</option>
	      </select>
	    </div>
	  </div>
	  
	  <div class="layui-form-item">
	    <label class="layui-form-label">银行卡号码:</label>
	    <div class="layui-input-inline">
	      <input type="text" name="CardNo" id="CardNo" value="" required lay-verify="required" placeholder="请输入银行卡号码" autocomplete="off" class="layui-input">
	    </div>
	    <div class="layui-form-mid layui-word-aux show" id="CardNotext">辅助文字</div>
	  </div>
	  
	  <div class="layui-form-item">
	    <label class="layui-form-label">持卡人姓名:</label>
	    <div class="layui-input-inline">
	      <input type="text" name="UserName" id="UserName" value="" required lay-verify="required" placeholder="请输入持卡人姓名" autocomplete="off" class="layui-input">
	    </div>
	    <div class="layui-form-mid layui-word-aux show" id="UserNametext">辅助文字</div>
	  </div>
	  
	  <div class="layui-form-item">
	    <label class="layui-form-label">证件类型:</label>
	    <div class="layui-input-block">
	      <select name="CertType" id="CertType" lay-verify="required">
	        <option value="">请选择证件类型</option>
	        <option value="ID_card" selected="selected" >国内 居民身份证</option>
	        <option value="TA_card">军籍 军官/文职/义务兵/士官/职工证</option>
	        <option value="HK_card">港澳 居民来往内地通行证</option>
	        <option value="TW_card">台湾 居民来往大陆通行证</option>
	      </select>
	    </div>
	  </div>
	  
	  <div class="layui-form-item">
	    <label class="layui-form-label">证件号码:</label>
	    <div class="layui-input-inline">
	      <input type="text" name="CertNo" id="CertNo" value="" required lay-verify="required" placeholder="请输入证件号码" autocomplete="off" class="layui-input">
	    </div>
	    <div class="layui-form-mid layui-word-aux show" id="CertNotext">辅助文字</div>
	  </div>
	  
	  <div class="layui-form-item">
	    <label class="layui-form-label">银行预留手机:</label>
	    <div class="layui-input-inline">
	      <input type="text" name="Phone" id="Phone" value="" maxlength="11" required lay-verify="required" placeholder="请输入银行预留手机" autocomplete="off" class="layui-input">
	    </div>
	    <div class="layui-form-mid layui-word-aux show" id="Phonetext">辅助文字</div>
	  </div>

		<!--车牌颜色 -->
		<div class="layui-form-item">
		    <label class="layui-form-label">车牌颜色:</label>
		    <div class="layui-input-block">
		      <select name="CarColor" id="CarColor" lay-verify="required">
		        <option value="">请选择车牌颜色</option>
		        <option value="blue" selected="selected">蓝 色</option>
		        <option value="yellow">黄 色</option>
		        <option value="green">绿 色</option>
		        <option value="white">白 色</option>
		        <option value="black">黑 色</option>
		      </select>
		    </div>
	  </div>
	  
	  <!-- 关联车辆号牌 -->
	  <div class="layui-form-item">
		    <label class="layui-form-label">关联车籍</label>
		    <div class="layui-input-block">
		      <select name="id_type" id="id_type" lay-verify="requireds">
		        <option value="">请选择车籍</option>
            <option value="22" selected="selected">粤X</option>
            <?php foreach($res as $key=>$val){?>
            	<option value="<?php echo $val['ID'];?>"><?php echo $val['CITYCODE'];?></option>
            <?php }?>
		      </select>
		    </div>
		    
		    <!-- 输入车牌号 -->
		    <div class="layui-form-item">
			    <label class="layui-form-label">关联车辆号牌:</label>
			    <div class="layui-input-inline">
			      <input type="text" name="CarNo" id="CarNo" value="" required lay-verify="required" placeholder="请输入车牌号" autocomplete="off" class="layui-input">
			    </div>
			    <div class="layui-form-mid layui-word-aux show" id="CarNotext">辅助文字</div>
			</div>
	  </div>
	  
	  <!-- 按钮部分 -->
	  <div class="layui-form-item">
	    <div class="layui-input-block" style="margin: 0px 2%;text-align: center;">
	    	<!--lay-filter="formDemo"-->
	      <button type="button" id="sub" lay-submit class="layui-btn"> 同意授权，提交签约  </button>
	      <button type="reset" class="layui-btn layui-btn-primary">信息有误，重置信息</button>
	    </div>
	  </div>
	  
	</form>
	
	
	<!-- 银联无感支付  -->
	<form action="" method="post" id="sign_form" name="form2">
			<input type="hidden" name="PACKET" id="signedxml" value=""/>
	</form>
	
	
	<script type="text/javascript" src="./js/jquery.min.js"></script>
	
	<script type="text/javascript">
		
		$('#sub').click(function(){
			//获取银行类型标识
			var obj = document.getElementById("carType");//获取选中选中下拉的值
		  	var index = obj.selectedIndex;//获取当前选中的index
			var carNo = obj.options[index].value;//获取当前选中的值
				
			//车牌编号
	    	var ids = document.getElementById("id_type");//获取选中选中下拉的值
	    	var idsindex = ids.selectedIndex;//获取当前选中的index
	    	var carNos = ids.options[idsindex].text;//获取当前选中的值
	    	var StrCarNo = carNos+$("#CarNo").val();
	    	
	    	//车牌颜色
	    	var col = document.getElementById("CarColor");//获取选中选中下拉的值
	    	var colindex = col.selectedIndex;//获取当前选中的index
	    	var CarColor = col.options[colindex].value;//获取当前选中的值
	    	
	    	//证件类型
	    	var Cert = document.getElementById("CertType");//获取选中选中下拉的值
	    	var Certindex = Cert.selectedIndex;//获取当前选中的index
	    	var resct = Cert.options[Certindex].value;//获取当前选中的值
	    	var certType;//证件类型
				switch(resct){
						case 'ID_card':
							certType = 1;
						break;
						case 'TA_card':
							certType = 2;
						break;
						case 'HK_card':
							certType = 3;
						break;
						case 'TW_card':
							certType = 4;
						break;
				}
				
				
				
				if(carNo == 'Ucard' && myCheck() ){//银联标识 信用卡
//						console.log($('.layui-form').serialize())
//						var repath=$("#reqPath").val();
						$.ajax({
							type:'POST',
							url :'Togrand.php',
							async : false,
							dataType:'text',
		        			data: {
//			        				transId:	$("#transId").val(),
//			        				keys:	$("#key").val(),
//			        				AccessCode:	$("#AccessCode").val(),			        				
		        				Tel:	$("#Tel").val(),
								CardNo:	$("#CardNo").val(),
		        				UserName:	$("#UserName").val(),
		        				CertType:	certType,
		        				CertNo:	$("#CertNo").val(),
		        				Phone:	$("#Phone").val(),
		        				CarNo:	StrCarNo,
		        				token:	$("#token").val(),
		        				Color:	CarColor,
		        			},success : function(json) {
		    				 	var data = jQuery.parseJSON(json);
		    				 	console.log(data);
		    				 	console.log(data.msg);
		    				 	if(data.msg == 'success') {		    				 		
		    				 		alert('签约成功！,您可以使用停车服务了！')
		    				 	}else {
		    				 		$("#sign_form").attr("action",'http://ilazypay.com/access/unionpay/signedBankard');
		        					$("#signedxml").val(data.msg);
		        					$("#sign_form").submit();
		    				 	}
		        			}
					  	});
						
				}else if(carNo == 'DCard'  && myCheck() ){//顺德农商 借记卡
						console.dir('借记卡')
				}else {
						console.dir('请选择银行卡类型')
				}
			});
			
			//判断表单中是否有空值
		function myCheck()
	    {
	       for(var i=0;i<document.form1.elements.length-1;i++)
	       {
	          if(document.form1.elements[i].value=="")
	          {
//	             alert("当前表单不能有空项");
	             document.form1.elements[i].focus();
	             return false;
	          }
	       }
	       return true;
	      
	    }
			
			//获取select 选中的值
			function getSelectVal(ids,state = 'false') {
				
				var obj = document.getElementById("ids");//获取选中选中下拉的值
	    	var index = obj.selectedIndex;//获取当前选中的index
	    	alert(index)
	    	var carNo;
	    	if(state){
	    		carNo = obj.options[index].text;//获取当前选中的值
	    	}
	    	carNo = obj.options[index].value;//获取当前选中的值
	    	return carNo;
			}
   
			//会员注册手机号码
			$("#Tel").blur(function() {//会员注册手机号码
				var t = $('#Tel').val();
				if ($.trim($('#Tel').val()).length == 0) {					
					$('#Tel').val('').focus().val(t);
					$('#Teltext').html('请输入会员注册手机号码');
					$('#Teltext').css('display','block');
				} else {
					if (isPhoneNo($.trim($('#Tel').val())) == false) {
						$('#Tel').val('').focus().val(t);
						$('#Teltext').html('会员注册手机号码不正确');
						$('#Teltext').css('display','block');
					}else{
						$('#Teltext').css('display','none');
					}
				}
			});
			//会员注册手机号码控制；
			$("#Tel").keydown(function() {
//					console.log($("#Tel").val());
					$('#Tel').maxLength(11);//控制输入长度
						
			});
			
			//持卡人银行卡预留手机号
			$("#Phone").blur(function() {//持卡人银行卡预留手机号
				var t = $('#Phone').val();
				if ($.trim($('#Phone').val()).length == 0) {					
					$('#Phone').val('').focus().val(t);
					$('#Phonetext').html('请输入持卡人银行卡预留手机号');
					$('#Phonetext').css('display','block');
				} else {
					if (isPhoneNo($.trim($('#Phone').val())) == false) {
						$('#Phone').val('').focus().val(t);
						$('#Phonetext').html('您的输入的手机号码格式不正确，请重新输入！');
						$('#Phonetext').css('display','block');
					}else{
						$('#Phonetext').css('display','none');
					}
				}
			});
			//预留手机号码控制；
			$("#Phone").keydown(function(){
				$('#Phone').maxLength(11);//控制输入长度	
			});
			
			
			//持卡人姓名
			$("#UserName").blur(function() {//持卡人姓名
				var t = $('#UserName').val();
				if ($.trim($('#UserName').val()).length == 0) {					
					$('#UserName').val('').focus().val(t);
					
					$('#UserNametext').html('请输入持卡人姓名');
					$('#UserNametext').css('display','block');
					
				} else {
					if (isChinaName($.trim($('#UserName').val())) == false) {
						$('#UserName').val('').focus().val(t);
						
						$('#UserNametext').html('持卡人姓名不正确');
						$('#UserNametext').css('display','block');
					}else{
						$('#UserNametext').css('display','none');
					}
				}
			});
			
			
			$("#CardNo").blur(function() {//信用卡号&借记卡
				//判断选择银行卡类型
				var obj = document.getElementById("carType");//获取选中选中下拉的值
			  var index = obj.selectedIndex;//获取当前选中的index
				var carNo = obj.options[index].value;//获取当前选中的值
				console.dir(carNo)
				
				var t = $('#CardNo').val();
				if ($.trim($('#CardNo').val()).length == 0) {				
					$('#CardNo').val('').focus().val(t);
					
					$('#CardNotext').html('请输入银联信用卡号');
					$('#CardNotext').css('display','block');
					
				} else {
						if(carNo == 'Ucard'){//验证信用卡
								if (isCardNo($.trim($('#CardNo').val())) == false) {
									$('#CardNo').val('').focus().val(t);						
									$('#CardNotext').html('您的银联信用卡号输入不正确');
									$('#CardNotext').css('display','block');
								}else{
									$('#CardNotext').css('display','none');
								}
						}else if(carNo == 'DCard'){//验证借记卡，顺德农商
								if (is_CardNo($.trim($('#CardNo').val())) == false) {
									$('#CardNo').val('').focus().val(t);						
									$('#CardNotext').html('您的借记卡卡号输入不正确');
									$('#CardNotext').css('display','block');
								}else{
									$('#CardNotext').css('display','none');
								}
						}
					
				}
			});
			
			
			
			//信用卡号输入；
			$("#CardNo").keydown(function() {
				//判断选择银行卡类型
				var obj = document.getElementById("carType");//获取选中选中下拉的值
			  var index = obj.selectedIndex;//获取当前选中的index
				var carNo = obj.options[index].value;//获取当前选中的值
				console.dir(carNo)
				var t = $('#CardNo').val();
				var str=t.substring(0,1);
				
				if(carNo == 'Ucard'){
						$('#CardNo').maxLength(16);//控制输入长度						
						if(str != 6){//控制输入开头
							$('#CardNotext').html('请输入以6开头的银联信用卡号');
							$('#CardNotext').css('display','block');
							$('#CardNo').val('').focus().val('');
						}else{
							$('#CardNotext').css('display','none');
						}
				}else if(carNo == 'DCard'){
					
						$('#CardNo').maxLength(19);//控制输入长度						
						if(str != 9){//控制输入开头
							$('#CardNotext').html('请输入以9开头的银联信用卡号');
							$('#CardNotext').css('display','block');
							$('#CardNo').val('').focus().val('');
						}else{
							$('#CardNotext').css('display','none');
						}
				}
				
			});
			
			
			
			
			
			
		//证件卡号==============================
		$("#CertNo").blur(function() {//证件卡号
				
				var t = $('#CertNo').val();
				if ($.trim($('#CertNo').val()).length == 0) {
					$('#CertNo').val('').focus().val(t);
					$('#CertNotext').html('请输入持卡人证件号');
					$('#CertNotext').css('display','block');
					
				} else {
					
//					if (isCertNo($.trim($('#CertNo').val())) == false) {
//						$('#CertNo').val('').focus().val(t);						
//						$('#CertNotext').html('您的证件号输入不正确');
//						$('#CertNotext').css('display','block');
//					}else{
//						$('#CertNotext').css('display','none');
//					}

					//判断选择银行卡类型
					var obj = document.getElementById("CertType");//获取选中选中下拉的值
				  var index = obj.selectedIndex;//获取当前选中的index
					var carNo = obj.options[index].value;//获取当前选中的值
					switch(carNo){
						case 'ID_card'://国内身份证
							if(!checkCertNo(t))
							{
								$('#CertNo').val('').focus().val(t);					
								$('#CertNotext').html('您输入的证件号不正确！请重新输入...');
								$('#CertNotext').css('display','block');
							}else {
								$('#CertNotext').css('display','none');
							}
						break;
						case 'TA_card'://军官/文职/义务兵/士官/职工证
							$('#CertNotext').html('您暂不支持军官证，请重新选择证件类型');
						break;
						case 'HK_card'://港澳通行证
							$('#CertNotext').html('您暂不支持港澳通行证，请重新选择证件类型');
						break;
						case 'TW_card'://台湾通行证
							$('#CertNotext').html('您暂不支持台湾通行证，请重新选择证件类型');
						break;
					}
				}
		});
			
			//证件卡号输入；
			$("#CertNo").keydown(function() {
				$('#CertNo').maxLength(18);//控制输入长度
			});

			////选择车牌 控制车牌输入长度
			$("#CarNo").keydown(function() {
				$('#CarNo').maxLength(6);//控制输入长度		
			});
			
			
			//车牌号判断
			$("#CarNo").blur(function() {//输入车牌号失去焦点验证车牌是否正确
					
					var obj = document.getElementById("id_type");//获取选中选中下拉的值
				  var index = obj.selectedIndex;//获取当前选中的index
					var carNo = obj.options[index].text;//获取当前选中的值
          var StrCarNo = carNo+$("#CarNo").val();
          console.dir(StrCarNo.length)
					var res = isVehicleNumber(StrCarNo);
//					console.dir(res)
		    	if(!res){
		    		$("#CarNo").focus();
		    		$('#CarNo').val('').focus().val($('#CarNo').val());					
						$('#CarNotext').html('请输入正确的车牌号码...');
						$('#CarNotext').css('display','block');
		    		return false;
		    	}else{
		    		$('#CarNotext').css('display','none');
		    	}
		    	
			});
			//选择车牌
			$("#CarNo").focus(function() {//选择车牌
				
					var obj = document.getElementById("id_type");//获取选中选中下拉的值
		    	var index = obj.selectedIndex;//获取当前选中的index
		    	var carNo = obj.options[index].text;//获取当前选中的值
					if(carNo == '请选择车牌'){
						alert('请选择车牌');
					}
				
			});
			
			
			
			
		//地区选择器；
		function checkCertNo(obj)
		{
	        var aCity = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙 江", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖 北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西 藏", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国 外"};
	        var iSum = 0;
	        //var info = "";
	        var strIDno = obj;
	        var idCardLength = strIDno.length;
//	        if (!/^\d{17}(\d|x)$/i.test(strIDno) && !/^\d{15}$/i.test(strIDno))
	        if(!/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(strIDno))
	            return false; //非法身份证号
	
	        if (aCity[parseInt(strIDno.substr(0, 2))] == null)
	            return false;// 非法地区
	
	        // 15位身份证转换为18位
	        if (idCardLength == 15)
	        {
	            sBirthday = "19" + strIDno.substr(6, 2) + "-" + Number(strIDno.substr(8, 2)) + "-" + Number(strIDno.substr(10, 2));
	            var d = new Date(sBirthday.replace(/-/g, "/"))
	            var dd = d.getFullYear().toString() + "-" + (d.getMonth() + 1) + "-" + d.getDate();
	            if (sBirthday != dd)
	                return false; //非法生日
	            strIDno = strIDno.substring(0, 6) + "19" + strIDno.substring(6, 15);
	            strIDno = strIDno + GetVerifyBit(strIDno);
	        }
	
	        // 判断是否大于2078年，小于1900年  1990
	        var year = strIDno.substring(6, 10);
	        if (year < 1900 || year > 2078)
	            return false;//非法生日
	
	        //18位身份证处理
	
	        //在后面的运算中x相当于数字10,所以转换成a
	        strIDno = strIDno.replace(/x$/i, "a");
	
	        sBirthday = strIDno.substr(6, 4) + "-" + Number(strIDno.substr(10, 2)) + "-" + Number(strIDno.substr(12, 2));
	        var d = new Date(sBirthday.replace(/-/g, "/"))
	        if (sBirthday != (d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate()))
	            return false; //非法生日
	        // 身份证编码规范验证
	        for (var i = 17; i >= 0; i --)
	            iSum += (Math.pow(2, i) % 11) * parseInt(strIDno.charAt(17 - i), 11);
	        if (iSum % 11 != 1)
	            return false;// 非法身份证号
	
	        // 判断是否屏蔽身份证
	        var words = new Array();
	        words = new Array("11111119111111111", "12121219121212121");
	
	        for (var k = 0; k < words.length; k++) {
	            if (strIDno.indexOf(words[k]) != -1) {
	                return false;;
	            }
	        }
	        return true;
    	}
			
		/*姓名身份证，手机号提交*/
		function isChinaName(name) {
			var pattern = /^[\u4E00-\u9FA5]{1,6}$/;
			return pattern.test(name);
		}
		
		
		// 验证身份证
		function isCertNo(card) {
			var pattern = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
			return pattern.test(card);
		}
		
		// 验证银行卡
		function isCardNo(card) {
			var pattern = /^\d{16}$/g;
			return pattern.test(card);
		}
		
		// 验证银行卡 借记卡
		function is_CardNo(card) {
			var pattern = /^\d{19}$/g;
			return pattern.test(card);
		}
		
		// 验证手机号
		function isPhoneNo(phone) {
			var pattern = /^1[34578]\d{9}$/;
			return pattern.test(phone);
		}
		//字符长度控制；
		jQuery.fn.maxLength = function(max){  
        this.each(function() {
            var type = this.tagName.toLowerCase();  
            var inputType = this.type? this.type.toLowerCase() : null;  
            if(type == "input" && inputType == "text" || inputType == "password"){  
                //Apply the standard maxLength  
                this.maxLength = max;  
            }  
            else if(type == "textarea"){  
                this.onkeypress = function(e){  
                    var ob = e || event;  
                    var keyCode = ob.keyCode;  
                    var hasSelection = document.selection? document.selection.createRange().text.length > 0 : this.selectionStart != this.selectionEnd;  
                    return !(this.value.length >= max && (keyCode > 50 || keyCode == 32 || keyCode == 0 || keyCode == 13) && !ob.ctrlKey && !ob.altKey && !hasSelection);  
                };  
                this.onkeyup = function(){  
                    if(this.value.length > max){  
                        this.value = this.value.substring(0,max);  
                    }  
                };  
            }  
        });  
   };
		   
		    //验证车牌号
	    function isVehicleNumber(vehicleNumber) {
	        var result = false;
	        var express = '';
	        if (vehicleNumber.length == 7){	        		
	            express = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$/;
							result = express.test(vehicleNumber);
//	            express = /^[\u4E00-\u9FA5][\da-zA-Z]{6}$/.test(vehicleNumber);
//	            result = express;
	        }else if(vehicleNumber.length == 8){
	        		express = /^[\u4E00-\u9FA5][\da-zA-Z]{7}$/.test(vehicleNumber);
	            result = express;
	        }
	        
	      	return result;
	  	}
		</script>
 
<script>
//Demo
layui.use('form', function(){
  var form = layui.form;
  
  //监听提交
  form.on('submit(formDemo)', function(data){
    layer.msg(JSON.stringify(data.field));
//  console.dir(data.field);
    return false;
  });
});
</script>




<script>
//一般直接写在一个js文件中
//layui.use(['layer', 'form'], function(){
//var layer = layui.layer
//,form = layui.form;
  
//layer.msg('Hello World');
//});
</script> 
</body>
</html>