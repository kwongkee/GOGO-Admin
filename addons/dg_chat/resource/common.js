/**
 * 描述：TODO 推送类
 * 构建组：app-push
 * 作者：zhongjinyou
 * 邮箱: jinyou.zhong@qlchat.com
 * 日期:Dec 26, 2015-10:07:22 PM
 * 版权：千聊 公司版权所有
 * 
 * @param {Object} data {dir:"论坛：FROUM/直播：LIVE",id:论坛ID/直播ID,msgType:消息类型:public/private,reqSleep:请求间隔时间(默认3秒),funcSleep:回调方法间隔时间（默认为0）,callback:回调函数}
 */



var PushMessageListener = {
	wsUrl:CtxPath.replace('http','ws').replace('m.','ws.'),
	onLineNum:"",
	idx:0,
	prevTime:'',
	init : function(data) {
		var _self = this;
		this.callback = data.callback;
		this.callEver = data.callEver;
		this.reqSleep = 3000;
		this.funcSleep = 0;
		this.topicId = data.id;
		this.msgType = data.msgType;
		this.dir = data.dir;
		if (data.reqSleep) {
			this.reqSleep = data.reqSleep;
		}
		if (data.funcSleep) {
			this.funcSleep = data.funcSleep;
		}
		this.receive = function() {
			$.ajax({
				type : "GET",
				url : CtxPath + "/push/receive.htm",
				data:{idx:_self.idx,prevTime:_self.prevTime},
				success : function(result) {
					var data = eval("(" + result + ")");
					PushMessageListener.onLineNum = data.onLineNum;
					if (data.status == "200") {
						_self.idx = data.idx;
						_self.prevTime = data.prevTime;
						var execSleep=0;
						if (_self.funcSleep > 0) {
							for (var i = 0;i<data.list.length;i++) {
								execSleep = execSleep + _self.funcSleep;
								var dataObj = eval("(" + data.list[i] + ")");
								setTimeout(function() {
									_self.callback(dataObj);
								}, execSleep);
							}
						} else {
							for (var i = 0;i<data.list.length;i++) {
								var dataObj = eval("(" + data.list[i] + ")");
								_self.callback(dataObj);
							}
						}
					}
					_self.callEver();
					_self.timer = setTimeout(_self.receive, _self.reqSleep);
				},error:function(XMLHttpRequest, textStatus, errorThrown){
					if (textStatus == 'timeout' || textStatus == 'error') {
						clearTimeout(_self.timer);
						_self.timer = setTimeout(_self.receive, _self.reqSleep);
					}
				},cache:false,timeout:5000
			});
		}
		
		this.initHTTPPush = function(){
			$.ajax({
			type : "POST",
			url : CtxPath + "/push/init.htm",
			data : {
				dir : _self.dir,
				id : _self.topicId,
				msgType : _self.msgType
			},
			success : function(result) {
				var data = eval("(" + result + ")");
				console.info(data.status + ":" + data.msg);
				if (data.status == '200') {
					PushMessageListener.onLineNum = data.onLineNum;
					_self.callEver();
					_self.receive();
				}
			},cache:false
			});
		}
		
		this.openSocket = function (){
			_self.websocket = new WebSocket(_self.wsUrl +"/socket.ws?_time="+new Date().getTime());
			_self.websocket.onerror = function(event){
          		//console.error(event);
            };
			//连接成功建立的回调方法
			_self.websocket.onopen = function(event){
				$.ajax({
				type : "GET",
				url : CtxPath + "/socket/init.htm",
				success : function(result) {
					_self.websocket.send(JSON.stringify({type:'LIVE_INIT',msg:result,topicId:_self.topicId,prevTime:_self.prevTime,idx:_self.idx}));
				},cache:false
			});
				setInterval(function(){
					$.ajax({type : "GET",url : CtxPath + "/socket/refresh.htm",success : function(result) {},cache:false});
				},1000*60*20);
				clearInterval(_self.intervalTimer);
			}
			//接收到消息的回调方法
			_self.websocket.onmessage = function(event){
				//console.info(event.data)
          		var data = eval("(" + event.data + ")");
					PushMessageListener.onLineNum = data.onLineNum;
					
					if (data.status == "200") {
						_self.idx = data.idx;
						_self.prevTime = data.prevTime;
						for (var i = 0;i<data.list.length;i++) {
							//var dataObj = eval("(" + data.list[i] + ")");
							_self.callback(data.list[i]);
						}
					} else if (data.status == 'SWITCH_HTTP') {
						_self.pushType = data.status;
						_self.initHTTPPush();
					}
					_self.callEver();
					setTimeout(function(){_self.websocket.send(JSON.stringify({type:'LIVE_RMSG',prevTime:_self.prevTime,idx:_self.idx}));},_self.reqSleep);
			}
		    //连接关闭的回调方法
			_self.websocket.onclose = function(event){
				if (_self.pushType != 'SWITCH_HTTP') {
					setTimeout(function(){
					_self.openSocket()
					},_self.reqSleep);
				}
				console.info(event);
          	}
          	window.onbeforeunload = function(){
          		_self.websocket.close();
      		}
		}
		
		
		/*try {
			_self.openSocket();
		} catch (e) {*/
			_self.initHTTPPush();
		/*}*/
		
	}
}; 

