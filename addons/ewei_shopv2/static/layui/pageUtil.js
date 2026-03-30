Date.prototype.format = function(format) {
	var o = {
		"M+" : this.getMonth()+1, //month
		"d+" : this.getDate(),    //day
		"h+" : this.getHours(),   //hour
		"m+" : this.getMinutes(), //minute
		"s+" : this.getSeconds(), //second
	}
	
	if (/(y+)/.test(format)) format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
	for(var k in o) if (new RegExp("(" + k + ")").test(format)) format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
	return format;
};

// *** 生成orderId等日期时间相关数据 ***
function genOrderId(oIdName, t1Name, t2Name){
	var myDate = new Date();
	$("input[name=" + oIdName +"]").val(myDate.getTime());
	
	if(t1Name !== '') {
		var x=myDate.format('yyyyMMddhhmmss'); 
		$("input[name=" + t1Name +"]").val(x);
	}
	if(t2Name !== '') {
		myDate.setFullYear(myDate.getFullYear()+1);
		var y=myDate.format('yyyyMMddhhmmss');
		$("input[name=" + t2Name +"]").val(y);
	}
}


// *** 生成签名原文signData ***
function genSignData(paramNames){
	var signField = paramNames.split(',');
 	var signData = "";

  for(var k in signField) {
  	var pName = signField[k];
   	var pVal = $("input[name=" + pName +"]").val();
  	if(pVal !== null && pVal !== undefined && jQuery.trim(pVal) !== '') signData += pName + "=" + pVal + "&"; 	
  }
  
  if(signData.length > 0) signData = signData.substr(0 , signData.length-1);
	return signData;
}

/**
 *
 * *** ajax方式提交请求 ***
 * signStr: 页面拼接起来的签名原文串儿
 * bgReqFlag：1,走后台post请求; 0,走页面表单提交
 * requestUrl：后台post请求的提交地址
 * bizType：业务类型
 */
function submitAjax(signStr, bgReqFlag, requestUrl, bizType) {
	
	$("#submitBtn").attr('disabled',true);//禁用按钮
	var signType= $("#signType").val();//签名方式
	
	$.ajax({
		type: "post",
		url: "../helper.php",
		//		签名方式，签名原文串，请求地址，post请求，业务类型；
		data: {"signType":signType, "signStr":signStr, "requestUrl":requestUrl, "bgReqFlag":bgReqFlag, "bizType":bizType},
		dataType:'text',
		async:false,
		success: function(result) {
			result=result.trim();
			//如为后台post请求，直接提示后台处理返回的结果；否则赋值给表单元素signMsg
			bgReqFlag==1 ? alert(result) : $("#signMsg").val(result);
		},
		error: function(XMLHttpRequest, textStatus, errorThrown) {
			alert("status:"+XMLHttpRequest.status);
			alert("readyState:"+XMLHttpRequest.readyState);
			alert("textStatus:"+textStatus);
			alert('请求提交失败!');
		}
	});

	$("#submitBtn").attr('disabled',false);
	return true;
}