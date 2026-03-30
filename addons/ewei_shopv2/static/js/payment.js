$(function() {
// 	//放弃按钮点击事件
// 	$('#giveUpBtn').click(function() {
// 		layer.confirm('你确定放弃支付吗？', {
// 			btn: ['确定', '取消'], //按钮
// 		}, function() {
// 			window.location.href = "index.html";
// 		});
// 	})
	//积分抵扣点击事件
	// $('#openIcon').click(function() {
	// 	if($(this).parent().height() > 75) {
	// 		$(this).css({
	// 			'transform': 'rotate(0deg)'
	// 		});
	// 		$(this).parent().animate({
	// 			height: "75px",
	// 		}, "slow");
	// 	} else {
	// 		$(this).css({
	// 			'transform': 'rotate(180deg)'
	// 		});
	// 		$(this).parent().animate({
	// 			height: "181px",
	// 		}, "slow");
	// 	}
	// })
	//加减时长按钮点击事件
	// var numCount = 0; //抵扣时长初始化数字
	// $('#reduceBtn').click(function() {
	// 	if(numCount > 0) {
	// 		$('#timeNum').html(--numCount);
	// 		$('.integralNum').html(numCount * 10); //统计计算所需花费积分
	// 	} else {
	// 		numCount = 0;
	// 		$('#timeNum').html(numCount);
	// 		$('.integralNum').html(numCount * 10); //统计计算所需花费积分
	// 	}
	// })
	// $('#plusBtn').click(function() {
	// 	$('#timeNum').html(++numCount);
	// 	$('.integralNum').html(numCount * 10); //统计计算所需花费积分
	// })
	//
	// 选择支付事件
	$('#paysure div').each(function(i){
		$(this).click(function(index) {
			console.dir($(this).index());

			$('#paysure div').siblings().removeClass("pays");//siblings是循环遍历
			$('.weui_icon_success').remove();//siblings是循环遍历
			$('#paysure div').siblings().css("position",'');//siblings是循环遍历

	        $(this).addClass("pays");
	        $(this).css('position','relative');
	        $(this).prepend('<i class="weui_icon_success" style="position:absolute;padding-left:30px"></i>');

			var types = '';
	        switch($(this).index()){
	        	case 0:
	        		types = 'alipay'
	        		$(this).append('<input type="hidden" value="'+types+'" id="'+types+'" class="payType"/>');
	        		$('#wechat').remove();
	        		$('#unpay').remove();
	        		$('#bestpay').remove();
	        	break;
	        	case 1:
	        		types = 'wechat'
	        		$(this).append('<input type="hidden" value="'+types+'" id="'+types+'" class="payType"/>');
	        		$('#alipay').remove();
	        		$('#unpay').remove();
	        		$('#bestpay').remove();
	        	break;
	        	case 2:
	        		types = 'unionpay'
	        		$(this).append('<input type="hidden" value="'+types+'" id="'+types+'" class="payType"/>');
	        		$('#alipay').remove();
	        		$('#wechat').remove();
	        		$('#bestpay').remove();
	        	break;
	        	case 3:
	        		types = 'bestpay'
	        		$(this).append('<input type="hidden" value="'+types+'" id="'+types+'" class="payType"/>');
	        		$('#alipay').remove();
	        		$('#wechat').remove();
	        		$('#unpay').remove();
	        	break;
	        }
	        return false;
		})
	})
	//结算按钮点击事件
	$('#goPayBtn').click(function() {
		$('.payConfirmBox').fadeIn();
	})
	//支付确认按钮点击事件
	$('.cancleBtn').click(function() {
		$('.payConfirmBox').fadeOut();
		// alert("支付取消");
	})
	$('.payBtn').click(function(){
		$.ajax({
			type:'POST',
			url : url,
			async : false,
			dataType:'text',
			data: {
				payType:$(".payType").val(),
				oid:$("#oid").val(),
				order:$("#orders").val(),
			},
			success : function(json) {
				
//				console.dir(json);
			 	var data = jQuery.parseJSON(json);
//			 	console.log(data.payUrl);

			 	if(data.msg == 'success') {
			 		window.location.href = data.payUrl;
			 	}else {
			 		alert(data.msg);
			 	}

//				$("#sign_form").attr("action",'http://ilazypay.com:8080/access/unionpay/signedBankard');
//				$("#signedxml").val(data.msg);
//				$("#sign_form").submit();
			}
	  	});
//		alert('支付成功！');
	})
})
