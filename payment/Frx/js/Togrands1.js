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
			$("#Tel").keydown(function(){
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
			
			
			$("#CardNo").blur(function() {//信用卡号
				
				//判断选择银行卡类型
				var obj = document.getElementById("carType");//获取选中选中下拉的值
			    var index = obj.selectedIndex;//获取当前选中的index
				var carNo = obj.options[index].value;//获取当前选中的值
				
				var t = $('#CardNo').val();
				if ($.trim($('#CardNo').val()).length == 0) {				
					$('#CardNo').val('').focus().val(t);
					
					$('#CardNotext').html('请输入带有银联标识的信用卡号');
					$('#CardNotext').css('display','block');
					
				} else {
					if(carNo == 'Ucard'){//验证信用卡 无感
						
						if (isCardNo($.trim($('#CardNo').val())) == false) {
							$('#CardNo').val('').focus().val(t);						
							$('#CardNotext').html('您的银联信用卡号输入不正确');
							$('#CardNotext').css('display','block');
						}else{
							$('#CardNotext').css('display','none');
						}
						
					}else if(carNo == 'DCard'){//验证借记卡，顺德农商
						
						if (!jjCard($.trim($('#CardNo').val()))) {//  is_CardNo
							
							$('#CardNo').val('').focus().val(t);
							$('#CardNotext').html('您输入的顺德农商借记卡号错误请重新输入！');
							$('#CardNotext').css('display','block');
							
						}else{
							
//							console.log(jjCard($('#CardNo').val()));							
//							if(!jjCard($('#CardNo').val())){//正则判断卡号是否正确；
//								$('#CardNotext').html('您输入的顺德农商借记卡号错误请重新输入！');
//								$('#CardNotext').css('display','block');
//								$('#CardNo').val('').focus().val(t);
//							}else {
								$('#CardNotext').css('display','none');
//							}
						}
					}
					
				}
			});
			
			
			//信用卡号输入； 按键按下
			$("#CardNo").keyup(function(){
				
				//判断选择银行卡类型
				var obj = document.getElementById("carType");//获取选中选中下拉的值
			  	var index = obj.selectedIndex;//获取当前选中的index
				var carNo = obj.options[index].value;//获取当前选中的值

				var t = $('#CardNo').val();
				var str=t.substring(0,1);
				console.dir(str)
				
				if(carNo == 'Ucard'){//无感银联信用卡
					$('#CardNo').maxLength(16);//控制输入长度						
					if(str != 6){//控制输入开头
						$('#CardNotext').html('请输入以6开头的银联信用卡号');
						$('#CardNotext').css('display','block');
						$('#CardNo').val('').focus().val('');
					}else{
						$('#CardNotext').css('display','none');
					}
				} else if(carNo == 'DCard'){//顺德农商借记卡；

					switch(parseInt(str)){
						case 6:
							$('#CardNotext').css('display','none');	
							$('#CardNo').maxLength(16);//控制输入长度
						break;
						default:
							$('#CardNotext').html('请输入以6开头的顺德农商借记卡');
							$('#CardNotext').css('display','block');
							$('#CardNo').val('').focus().val('');	
					}
					
				}
			});
			
			
			/**
			 * 证件卡号   当输入域失去焦点 (blur)
			 */
			$("#CertNo").blur(function() {//证件卡号
				
				//获取证件号中的内容
				var t = $('#CertNo').val();
				//如果没有输入
				if ($.trim($('#CertNo').val()).length == 0) {
					//光标重定位
					$('#CertNo').val('').focus().val(t);
					$('#CertNotext').html('请输入持卡人证件号');
					$('#CertNotext').css('display','block');
					
				} else {//已经输入了

					//判断选择银行卡类型
					var Cert = document.getElementById("IDcard");//获取选中选中下拉的值
				  	var Certindex = Cert.selectedIndex;//获取当前选中的index
					var IDcard = Cert.options[Certindex].value;//获取当前选中的值  证件类型
					switch(IDcard){//判断用户选择
						case 'ID_card'://国内 居民身份证
							if(!checkCertNo(t)){//正则判断用户输入身份证								
								$('#CertNo').val('').focus().val(t);
								$('#CertNotext').html('您输入的证件号不正确！请重新输入...');
								$('#CertNotext').css('display','block');								
							}else {
								$('#CertNotext').css('display','none');
							}
						break;
						case 'TA_card'://军籍 军官/文职/义务兵/士官/职工证
							if(!Officer(t)) {
								$('#CertNo').val('').focus().val(t);
								$('#CertNotext').html('您输入的军官证件号不正确！请重新输入...');
								$('#CertNotext').css('display','block');
							} else {
								$('#CertNotext').css('display','none');
							}
							
						break;
						case 'HK_card'://港澳 居民来往内地通行证
						
							if(!HK_Card(t)){
								
								$('#CertNo').val('').focus().val(t);
								$('#CertNotext').html('您输入的证件号不正确！请重新输入...');
								$('#CertNotext').css('display','block');
								
							}else{$('#CertNotext').css('display','none');}
							
						break;
						case 'TW_card'://台湾 居民来往大陆通行证
							if(!TW_Card(t)){
								
								$('#CertNo').val('').focus().val(t);
								$('#CertNotext').html('您输入的证件号不正确！请重新输入...');
								$('#CertNotext').css('display','block');
								
							}else{$('#CertNotext').css('display','none');}
						break;
					}
				}
			});
			
			
			//证件卡号输入；
			$("#CertNo").keydown(function(){
				//判断选择银行卡类型
				var Cert = document.getElementById("IDcard");//获取选中选中下拉的值
			  	var Certindex = Cert.selectedIndex;//获取当前选中的index
				var IDcard = Cert.options[Certindex].value;//获取当前选中的值  证件类型
				switch(IDcard){//判断用户选择
					case 'ID_card'://国内 居民身份证
						$('#CertNo').maxLength(18);//控制输入长度
					break;
					case 'TA_card'://军籍 军官/文职/义务兵/士官/职工证
						$('#CertNo').maxLength(12);//控制输入长度
					break;
					case 'HK_card'://港澳 居民来往内地通行证
						$('#CertNo').maxLength(11);//控制输入长度
					break;
					case 'TW_card'://台湾 居民来往大陆通行证
						$('#CertNo').maxLength(8);//控制输入长度
					break;
				}
				
			});

			
			/**
			 * 车牌号输入
			 */
			$("#CarNo").keydown(function() {
				
				//判断
				var Cert = document.getElementById("CarColor");//获取选中选中下拉的值
			  	var Certindex = Cert.selectedIndex;//获取当前选中的index
				var IDcard = Cert.options[Certindex].value;//获取当前选中的值  证件类型
				if(IDcard == 'green'){//绿色车牌可以输入6位号码
					$('#CarNo').maxLength(6);//控制输入长度
				}else{
					$('#CarNo').maxLength(5);//控制输入长度
				}
				
			});
			
			/**
			 * 失去焦点事件，车牌号
			 */
			$("#CarNo").keyup(function() {//车牌号
				//车牌所属；
				var obj = document.getElementById("id_type");//获取选中选中下拉的值
            	var index = obj.selectedIndex;//获取当前选中的index
            	var carNo = obj.options[index].text;//获取当前选中的值
            	var StrCarNo = (carNo+($("#CarNo").val().toUpperCase()));//将字符串中的所有字符都转换成大写
            	
				//获取证件号中的内容
				var t = $('#CarNo').val();
				//如果没有输入
				if ($.trim($('#CarNo').val()).length == 0) 
				{
					//光标重定位
					$('#CarNo').val('').focus().val(t);
					$('#CarNotext').html('请输入车牌号');
					$('#CarNotext').css('display','block');
					
				} else {//已经输入了

					//判断
					var Cert = document.getElementById("CarColor");//获取选中选中下拉的值
				  	var Certindex = Cert.selectedIndex;//获取当前选中的index
					var IDcard = Cert.options[Certindex].value;//获取当前选中的值  证件类型
					if(IDcard == 'green')//绿色车牌可以输入6位号码
					{
						$('#CarNo').maxLength(6);//控制输入长度
	                	//车牌验证
	                	var res = isVehicleNumber2(StrCarNo);
	                	if(!res){
	                		$('#CarNo').val('').focus().val(t);
							$('#CarNotext').html('您的车牌输入错误，请重新输入。。。');
							$('#CarNotext').css('display','block');
	                	}else{
	                		$('#CarNotext').css('display','none');
	                	}
	                	
					} else {
						
						$('#CarNo').maxLength(5);//控制输入长度
	                	//车牌验证
	                	var res = isVehicleNumber(StrCarNo);
	                	if(!res){
	                		$('#CarNo').val('').focus().val(t);
							$('#CarNotext').html('您的车牌输入错误，请重新输入。。。');
							$('#CarNotext').css('display','block');
	                	}else{
	                		$('#CarNotext').css('display','none');
	                	}
					}
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
			
		
		//身份证判断正则；
		function checkCertNo(obj)
		{
	        var aCity = {11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙 江", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖 北", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西 藏", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国 外"};
	        var iSum = 0;
	        //var info = "";
	        var strIDno = obj;
	        var idCardLength = strIDno.length;
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
		
		// 验证顺德农商行   借记卡号码
//		function jjCard(cardNo) {
//			var pattern = /^985262\d{13}$/;
//			return pattern.test(cardNo);
//		}
		
		function jjCard(cardNo) {
//			var pattern= /^(98|94)\d{17}|62\d{14}$/;
			var pattern= /^622322\d{10}$/;
			return pattern.test(cardNo);
		}
		
		// 验证港澳通行证
		function HK_Card(cardNo) {
			var pattern = /^H|M\d{10}$/;
			return pattern.test(cardNo);
		}
		
		// 验证台湾通行证
		function TW_Card(cardNo) {
			var pattern = /^\d{8}$/;
			return pattern.test(cardNo);
		}
		
		//军官证正则 
		function Officer(cardNo) {
//			var validator = /(^\s*)|(\s*$)/g;
			var reg = /南字第(\d{8})号|北字第(\d{8})号|沈字第(\d{8})号|兰字第(\d{8})号|成字第(\d{8})号|济字第(\d{8})号|广字第(\d{8})号|海字第(\d{8})号|空字第(\d{8})号|参字第(\d{8})号|政字第(\d{8})号|后字第(\d{8})号|装字第(\d{8})号/;
			cardNo = cardNo.replace(/(^\s*)|(\s*$)/g," ");
			return reg.test(cardNo);
		}
		
		//字符长度控制；
		jQuery.fn.maxLength = function(max){  
		        this.each(function(){  
		            var type = this.tagName.toLowerCase();  
		            var inputType = this.type? this.type.toLowerCase() : null;  
		            if(type == "input" && inputType == "text" || inputType == "password"){  
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
		  
	
	//同意授权，提交签约
    function  doSubmit() {
        	      	
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
		var Cert = document.getElementById("IDcard");//获取选中选中下拉的值
		var Certindex = Cert.selectedIndex;//获取当前选中的index
		var resct = Cert.options[Certindex].value;//获取当前选中的值
		var certType;//证件类型
		switch(resct){
			case 'ID_card'://身份证
				certType = 1;
			break;
			case 'TA_card'://军官证
				certType = 2;
			break;
			case 'HK_card'://港澳
				certType = 3;
			break;
			case 'TW_card'://台湾
				certType = 4;
			break;
		}

		
		if(carNo == 'Ucard'){//银联标识 信用卡
			
			if(checkForm()){//判断是否
				$.ajax({
				type:'POST',
				url :'Togrand.php',
				async : false,
				dataType:'text',
				data: {
						Tel:	$("#Tel").val(),//注册手机号
						CardNo:	$("#CardNo").val(),//信用卡号
						UserName:	$("#UserName").val(),//用户名
						CertType:	certType,//证件类型
						CertNo:	$("#CertNo").val(),//证件号码
						Phone:	$("#Phone").val(),//签约手机号
						CarNo:	StrCarNo,
						token:	$("#token").val(),//签约标识   sing 
						Color:	CarColor,//车牌颜色
					},success : function(json) {
					 	var data = jQuery.parseJSON(json);
					 	if(data.msg == 'success') {		    				 		
					 		alert('签约成功！,您可以使用停车服务了！')
					 	}else {
					 		$("#sign_form").attr("action",'http://ilazypay.com/access/unionpay/signedBankard');
							$("#signedxml").val(data.msg);
							$("#sign_form").submit();
						}
					}
				});
			}else {
				alert('请填写所有必填选项！')
			}
			
		}else if(carNo == 'DCard'  && checkForm() ) {
			//顺德农商 借记卡    checkForm判断表单是否有空值			
			$.ajax({
				type:"post",
				url:"RsaSign.php",
				async:false,
				dataType:'text',
				data:{
					Tel:	$("#Tel").val(),//注册手机号
					CardNo:	$("#CardNo").val(),//信用卡号
					UserName:	$("#UserName").val(),//用户名
					CertType:	certType,//
					CertNo:	$("#CertNo").val(),//证件号码
					Phone:	$("#Phone").val(),//签约手机号
					CarNo:	StrCarNo,
					token:	$("#token").val(),//签约标识   sing 
					Color:	CarColor,//车牌颜色
				}
				,success:function(res){
					
					var data = jQuery.parseJSON(res);
					if(data.msg == 'success') {
				 		alert('签约成功！,您可以使用停车服务了！')
				 	}else {//http://test2.gslb.sdebank.com/ajax.php/ParkingPay/subscribe?forward=
				 		
				 		$("#bk_form").attr("action",'http://test2.gslb.sdebank.com/ajax.php/ParkingPay/subscribe');
						$("#bksign").val(data.sign);
						$("#bk_form").submit();
					}
				},
				error:function() {
					alert('field');
				}
			});
			
		}else {
			alert('请选择银行卡类型')
		}				
	};
        		
    		//判断表单中是否有空值
			function myCheck()
		    {
		       for(var i=0;i<document.form1.elements.length-1;i++)
		       {
		          if(document.form1.elements[i].value == " ")
		          {
		             document.form1.elements[i].focus();
		             return false;
		          }
		       }
		       return true;
		    }
        	
        	//判断表单是否有空值
        	function checkForm()
        	{
        		var inputs = document.form1.getElementsByTagName("input");
				for(var i = 0; i < inputs.length; i++) {
				    if(inputs[i].value == ''){
				    	return false;
				    }
				}
				return true;
        	}
        	
        	
	        //验证车牌号 7位车牌号
		    function isVehicleNumber(vehicleNumber) {
		        var result = false;
		        
		        if (vehicleNumber.length == 7){
		            var express = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$/;
		            result = express.test(vehicleNumber);
		        }
		      	return result;
		  	}
		  	
		  	 //验证车牌号 7位车牌号 验证八位数车牌号
		    function isVehicleNumber2(vehicleNumber) {
		        var result = false;		        
		        if (vehicleNumber.length == 8){
//		            var express = /^[京津沪渝冀豫云辽黑湘皖鲁新苏浙赣鄂桂甘晋蒙陕吉闽贵粤青藏川宁琼使领A-Z]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9挂学警港澳]{1}$/;
		            var express = /^(([\u4e00-\u9fa5][a-zA-Z]|[\u4e00-\u9fa5]{2}\d{2}|[\u4e00-\u9fa5]{2}[a-zA-Z])[-]?|([wW][Jj][\u4e00-\u9fa5]{1}[-]?)|([a-zA-Z]{2}))([A-Za-z0-9]{5}|[DdFf][A-HJ-NP-Za-hj-np-z0-9][0-9]{4}|[0-9]{5}[DdFf])$/;
		            
		            result = express.test(vehicleNumber);
		        }
		      	return result;
		  	}
		  	
		  	
		  	function loadAddress()
		  	{
		  		var _html ='';
	  			for(var index in address)
				{
					_html+='<option value="'+index+'">'+address[index]+'</option>';
				}
				$('#id_type').append(_html);
		  	}
	    