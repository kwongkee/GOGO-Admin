define(['core', 'tpl'], function(core, tpl) {
	
	var modal = {
		params: {}
	};
	//接收params参数
	modal.init = function(params) {
		console.log(params);
		var defaults = {
			orderid: 0,
			wechat: {
				success: false
			},
			cash: {
				success: false
			},
			alipay: {
				success: false
			},
		};
		modal.params = $.extend(defaults, params || {});
		//给pay-btn解除click绑定
		$('.pay-btn').unbind('click').click(function() {
			var btn = $(this);
			
			core.json('order/pay/check', {id: modal.params.orderid}, function(pay_json) {
				
				if (pay_json.status == 1) {
					modal.pay(btn)
				} else {
					FoxUI.toast.show(pay_json.result.message)
				}
			}, false, true)
		});
		
		if (modal.params.wechat.jie == 1) {
			$('.pay-btn[data-type="wechat"]').click()
		}
	};
	
	modal.pay = function(btn) {
		//获取data-type='credit'  或   data-type='wechat'  或  data-type='alipay' 属性！
		var type = btn.data('type') || '';
		if (type == '') {
			return
		}
		if (btn.attr('stop')) {
			return
		}
		btn.attr('stop', 1);
		if (type == 'wechat') {
			if (core.ish5app()) {
				appPay('wechat', null, null, true);
				return
			}
			modal.payWechat(btn)
		} else if (type == 'alipay') {
			if (core.ish5app()) {
				appPay('alipay', null, null, true);
				return
			}
			modal.payAlipay(btn)
		} else if (type == 'credit') {
			FoxUI.confirm('确认要支付吗?', '提醒', function() {
				modal.complete(btn, type)
			}, function() {
				btn.removeAttr('stop')
			})
		} else if (type == 'peerpay') {
			location.href = core.getUrl('order/pay/peerpay', {
				id: modal.params.orderid
			});
			return
		}else if (type == 'tgwechat') {//通莞聚合支付微信
			if (core.ish5app()) {
				appPay('tgwechat', null, null, true);
				return
			}
			modal.tgpayWechat(btn);
		} else if (type == 'tgalipay') {//通莞聚合支付支付宝
			if (core.ish5app()) {
				appPay('tgalipay', null, null, true);
				return
			}
			modal.tgpayAlipay(btn)
		} else if (type == 'tgwechath5') {//通莞聚合H5支付
//			if (core.ish5app()) {
//				appPay('tgwechath5', null, null, true);
//				return
//			}
			modal.tgpayWechath5(btn);
		} else {
			modal.complete(btn, type)
		}
	};
	
	
	//聚合支付微信H5支付
	modal.tgpayWechath5 = function(btn) {		
		$.ajax({
			type:"post",
			url:"http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.tgpay",
			async:true,
			data:{
				orderid:modal.params.orderid,
				type:'tgwechath5',
				http:'ok',
			},
			success:function(purl) {				
				console.log(purl);
			}			
		});		
	};
	
	
	//聚合支付微信支付
	modal.tgpayWechat = function(btn) {
		
		var tgwechat = modal.params.tgwechat;		
		if (!tgwechat.success) {
			return false;
		}
		if(modal.params.uniacid == 14){//如果公众号ID等于14：喜柏停车，就执行下面的代码
//			$.ajax({
//				type:"post",//授权GOGO公众号号，获取GOGO公众号openid;
//				url:"http://shop.gogo198.cn/payment/oauth/oauth.php",
////				async:true,
//				dataType:'jsonp',
//				data:{
//					orderid:modal.params.orderid,
//					type:'tgwechat',
//					http:'ok',
//					opid:modal.params.opid,
//				},
//				success:function(purl) {
//					console.log(purl);
//					alert(purl);
//				}			
//			});

			
//			var url = "http://shop.gogo198.cn/payment/oauth/oauth.php?orderid="+modal.params.orderid+"&oauth=yes&type=tgwechat&http=ok&opid="+modal.params.opid;
			var url = "http://shop.gogo198.cn/addons/ewei_shopv2/payment/oauth/oauth.php?orderid="+modal.params.orderid+"&oauth=yes&type=tgwechat&http=ok&opid="+modal.params.opid;
			location.href = url;
			
			
		}else{
			
			$.ajax({
				type:"post",
				url:"http://shop.gogo198.cn/app/index.php?i="+modal.params.uniacid+"&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.tgpay",
				async:true,
				data:{
					orderid:modal.params.orderid,//当前订单编号ID
					type:'tgwechat',
					http:'ok',
					openid:modal.params.opid,//当前用户Openid
					uniacid:modal.params.uniacid,//当前公众号ID
				},
				success:function(purl) {
//					console.log(purl);
					location.href = purl;
				}			
			});
		}
	};
	
	
	//聚合支付微信支付
//	modal.tgpayWechat = function(btn) {
//		
//		var tgwechat = modal.params.tgwechat;
//		if (!tgwechat.success) {
//			return false;
//		}
//		$.ajax({
//			type:"post",
//			url:"http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.tgpay",
//			async:true,
//			data:{
//				orderid:modal.params.orderid,
//				type:'tgwechat',
//				http:'ok',
//			},
//			success:function(purl) {
//				console.log(purl);
//				location.href = purl;
//			}			
//		});		
//	};
	
	
	//聚合支付,支付宝支付
	modal.tgpayAlipay = function(btn) {
		var alipay = modal.params.tgalipay;
		console.dir(modal.params);
		
		if (!alipay.success) {
			return false;
		}
		
		if(modal.params.uniacid == 14){
			$.ajax({
				type:"post",
				url:"http://shop.gogo198.cn/app/index.php?i=14&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.tgpay",
				async:true,
				data:{
					orderid:modal.params.orderid,
					type:'tgalipay',
					http:'ok',
				},
				success:function(purl) {
					//跳转链接,order/tgpay/tgwechat?orderid&type=0&url=alipay.url;
					location.href = core.getUrl('order/tgpay/tgalipay', {
	//					url: purl,
					})
					console.log(purl);
				}
				
			});
		}else {
			$.ajax({
				type:"post",
				url:"http://shop.gogo198.cn/app/index.php?i="+modal.params.uniacid+"&c=entry&m=ewei_shopv2&do=mobile&r=order.tgpay.tgpay",
				async:true,
				data:{
					orderid:modal.params.orderid,
					type:'tgalipay',
					http:'ok',
				},
				success:function(purl) {
					//跳转链接,order/tgpay/tgwechat?orderid&type=0&url=alipay.url;
					location.href = core.getUrl('order/tgpay/tgalipay', {
	//					url: purl,
					})
					console.log(purl);
				}
				
			});
		}
		
	};
	//2017-12-12
	
	
	//微信支付
	modal.payWechat = function(btn) {
		var wechat = modal.params.wechat;
		if (!wechat.success) {
			return
		}
		if (wechat.weixin) {
			function onBridgeReady() {
				WeixinJSBridge.invoke('getBrandWCPayRequest', {
					'appId': wechat.appid ? wechat.appid : wechat.appId,
					'timeStamp': wechat.timeStamp,
					'nonceStr': wechat.nonceStr,
					'package': wechat.package,
					'signType': wechat.signType,
					'paySign': wechat.paySign
				}, function(res) {
					if (res.err_msg == 'get_brand_wcpay_request:ok') {
						modal.complete(btn, 'wechat')
					} else if (res.err_msg == 'get_brand_wcpay_request:cancel') {
						FoxUI.toast.show('取消支付')
					} else {
						FoxUI.toast.show(res.err_msg)
					}
					btn.removeAttr('stop')
				})
			}
			if  (typeof WeixinJSBridge  ==  "undefined") {
				if ( document.addEventListener ) {
					document.addEventListener('WeixinJSBridgeReady',  onBridgeReady,  false)
				} else  if  (document.attachEvent) {
					document.attachEvent('WeixinJSBridgeReady',  onBridgeReady);
					document.attachEvent('onWeixinJSBridgeReady',  onBridgeReady)
				}
			} else {
				onBridgeReady()
			}
		}
		if (wechat.weixin_jie || wechat.jie == 1) {
			modal.payWechatJie(btn, wechat)
		}
	};
	
	
	modal.payWechatJie = function(btn, wechat) {
		var img = core.getUrl('index/qr', {
			url: wechat.code_url
		});
		$('#qrmoney').text(modal.params.money);
		$('.order-weixinpay-hidden').show();
		$('#btnWeixinJieCancel').unbind('click').click(function() {
			btn.removeAttr('stop');
			clearInterval(settime);
			$('.order-weixinpay-hidden').hide()
		});
		var settime = setInterval(function() {
			$.getJSON(core.getUrl('order/pay/orderstatus'), {
				id: modal.params.orderid
			}, function(data) {
				if (data.status >= 1) {
					clearInterval(settime);
					location.href = core.getUrl('order/pay/success', {
						id: modal.params.orderid
					})
				}
			})
		}, 1000);
		$('.verify-pop').find('.close').unbind('click').click(function() {
			$('.order-weixinpay-hidden').hide();
			btn.removeAttr('stop');
			clearInterval(settime)
		});
		$('.verify-pop').find('.qrimg').attr('src', img).show()
	};
	
	//支付宝支付
	modal.payAlipay = function(btn) {
		var alipay = modal.params.alipay;
		if (!alipay.success) {
			return
		}
		//跳转链接,order/pay_alipay?orderid&type=0&url=alipay.url;
		location.href = core.getUrl('order/pay_alipay', {
			orderid: modal.params.orderid,
			type: 0,
			url: alipay.url
		})
	};
	
	modal.complete = function(btn, type) {
		var peerpay = $('#peerpay').text();
		var peerpaymessage = $('#peerpaymessage').val();
		FoxUI.loader.show('mini');
		setTimeout(function() {
			core.json('order/pay/complete', {
				id: modal.params.orderid,
				type: type,
				peerpay: peerpay,
				peerpaymessage: peerpaymessage
			}, function(pay_json) {
				if (pay_json.status == 1) {
					location.href = core.getUrl('order/pay/success', {
						id: modal.params.orderid,
						result: pay_json.result.result
					});
					return
				}
				FoxUI.loader.hide();
				btn.removeAttr('stop');
				FoxUI.toast.show(pay_json.result.message)
			}, false, true)
		}, 1000)
	};
	return modal
});