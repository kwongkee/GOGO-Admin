    var clock_index=0,currentPlaying=null,room_id=user_info.room_id,avatar=user_info.headimgurl,nickname=user_info.nickname;
	var clock_handler=null,current_voiclocalid=null,globalObj=new Object();
	WEB_SOCKET_DEBUG = true;globalObj.chat_images=[];
	var ws, name=user_info.nickname, client_list={};		  
	//当前正在播放语音信息
	function startRec(){
		$(".second_dd var").eq(0).text(clock_index);
		clock_index++;
		if(clock_index==58){
			clock_index=60;
			$("#btnStopRec").click();
		}
	}
	
	/*赞赏*/
	function reward(){
		/*赞赏功能开始*/
		$(".btn_ilike").unbind();
		  $(".btn_ilike").click(function(){
			  var head=$(this).parent().children('.head_portrait').children('img').attr('src');
			  $(".live_headpic").children('img').attr('src',head.replace('46','132'));
			  $(".redbagBox").attr("msg_id",$(this).parents('.left_bubble').attr('msg_id'));
			  $(".redbagBox").show();
			  var name=$(this).parent().children('.speaker_name').children('b').text();
			  //设置全局赞赏数据
			  globalObj.reward={to_name:name,to_avatar:head,from_name:user_info.nickname,from_avatar:user_info.headimgurl};
		  });
		  $(".redbag_cancel").unbind();
		  $(".redbag_cancel").click(function(){
			  $(".redbagBox").hide();
		  });
		  $(".live_othermoney").unbind();
		  $(".live_othermoney").click(function(){
			  $(".otherRedmoneyBox").show();
		  });
		  $(".payli").unbind();
		  $(".payli").click(function(){
			  var money=$(this).text();
			  var msg_id=$(this).parents('.redbagBox').attr('msg_id');
			  console.dir(msg_id);
			  globalObj.reward.money=money;
			  var reward_content=JSON.stringify(globalObj.reward);
			  globalObj.reward_last = {type:"say",target:"main",msgtype:"reward",headimg:avatar, to_client_id: 'all',to_client_name:"all",content:reward_content};
			  $.getJSON(reward_url, { pay: money,msg_id:msg_id }, function(json){
				   callpay(json);
			  });
		  });
		  
		  $(".otherRedmoneyBox .gene_confirm").unbind();
		  $(".otherRedmoneyBox .gene_confirm").click(function(){
			  var money=$("#money").val();
			  if(money==''){
				  return;
			  }
			  var msg_id=$('.redbagBox').attr('msg_id');
			  globalObj.reward.money=money;
			  var reward_content=JSON.stringify(globalObj.reward);
			  globalObj.reward_last = {type:"say",target:"main",msgtype:"reward",headimg:avatar, to_client_id: 'all',to_client_name:"all",content:reward_content};
			  $.getJSON(reward_url, { pay: money,msg_id:msg_id }, function(json){
				  $(".geneBox").hide();
				  callpay(json);
			  });
		  });
		  
		  $(".lm_main").unbind();
		  $(".lm_main").click(function(){
			  $(".thank_money").children('var').text($(this).attr('attr-money'));
			  var to_headimg=$(this).attr('attr-imgurl');
			  to_headimg=to_headimg.replace('46','132');
			  $(".LmTipsBox").find(".live_headpic").children('img').attr('src',to_headimg);
			  var reward_text=$(this).attr('from-name')+"赞赏了"+$(this).attr('attr-toname');
			  $(".LmTipsBox").find(".live_towhy").text(reward_text);
			  $(".LmTipsBox").show();
		  });
		  $(".redbag_cancel").click(function(){
			  $(".redbag_box").hide();
		  });
	    /*赞赏功能结束*/
	}
	/*微信支付开始*/
	 function jsApiCall(parameters){
			WeixinJSBridge.invoke(
				'getBrandWCPayRequest',
				 parameters,
				function(res){
					WeixinJSBridge.log(res.err_msg);
					if(res.err_msg == "get_brand_wcpay_request:ok") {
						if(globalObj.reward_last){
							sentContent(globalObj.reward_last);
						}
	                } else if(res.err_msg == "get_brand_wcpay_request:cancel"){
	                    //alert("已取消赞赏!");
	                }else{
	                	alert(res.err_msg);
	                }
				}
			);
	}

	function callpay(parameters){
			if (typeof WeixinJSBridge == "undefined"){
			    if( document.addEventListener ){
			        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
			    }else if (document.attachEvent){
			        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
			        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
			    }
			}else{
			    jsApiCall(parameters);
			}
	}	
    /*微信支付结束*/
		
	function get_contenthtml(tcpdata){
    	var text='<dd class="left_bubble hasTime" msg_id="'+tcpdata.msg_id+'">';
			text+='<div class="speak_time">'+tcpdata.time+'</div>';
			text+='<div class="head_portrait"><img src="'+tcpdata.headimg+'"></div>';
			text+='<div class="speaker_name"><b>'+tcpdata.from_client_name+'</b></div>';
			text+='<div class="bubble_content "><p>'+tcpdata.content+'</p></div>';
			if(topic_setting.reward_status==1)
			  text+='<a class="btn_ilike" href="javascript:;">赏</a>';
			text+='</dd>';
		
		if(tcpdata.msgtype=='voice'){
			       var voice_class=getRecordClass(tcpdata.last);
				   text='<dd class="left_bubble hasTime" msg_id="'+tcpdata.msg_id+'">';
				   text+='<div class="speak_time">'+tcpdata.time+'</div>';
				   text+='<div class="head_portrait"><img src="'+tcpdata.headimg+'"></div>';
				   text+='<div class="speaker_name"><b>'+tcpdata.from_client_name+'</b></div>';
				   text+='<div class="bubble_content recordingMsg isReaded '+voice_class+'">';
				   text+='<i class="audio_info" attr-src="'+tcpdata.content+'"></i><var>'+tcpdata.last+'</var></div>';
				   if(topic_setting.reward_status==1)
				      text+='<a class="btn_ilike" href="javascript:;">赏</a>';
				   text+='</dd>';
		}
		
		if(tcpdata.msgtype=='image'){
			text='<dd class="left_bubble hasTime" msg_id="'+tcpdata.msg_id+'">';
			text+='<div class="speak_time">'+tcpdata.time+'</div>';
			text+='<div class="head_portrait"><img src="'+tcpdata.headimg+'"></div>';
			text+='<div class="speaker_name"><b>'+tcpdata.from_client_name+'</b></div>';
			text+='<div class="bubble_content ">';
			text+='<img id="sub_'+tcpdata.msg_id+'" class="chat_img" mediaid="'+tcpdata.content+'" src="'+tcpdata.content+'"></div>';
			if(topic_setting.reward_status==1)
			  text+='<a class="btn_ilike" href="javascript:;">赏</a>';
			text+='</dd>';
		}
		
		if(tcpdata.msgtype=='ask'){
			var Question=JSON.parse(tcpdata.content);
			text='<dd class="left_bubble hasTime" msg_id="'+tcpdata.msg_id+'">';
			text+='<div class="speak_time">'+tcpdata.time+'</div>';
			text+='<div class="head_portrait"><img src="'+tcpdata.headimg+'"></div>';
			text+='<div class="speaker_name"><b>'+tcpdata.from_client_name+'</b></div>';
			text+='<div class="bubble_content "><p><b>'+Question.N+':</b><em class="ask_label">问</em>'+Question.Q+'</p><p><b>回复:</b>'+Question.replay+'</p></div>';
			if(topic_setting.reward_status==1)
			  text+='<a class="btn_ilike" href="javascript:;">赏</a>';
			text+='</dd>';
		}
		
		//赞赏
		if(tcpdata.msgtype=='reward'){
			var Reward=JSON.parse(tcpdata.content);
			var text='<dt class="luckyMoney" msg_id="'+tcpdata.msg_id+'">';
			text+='<div class="lm_main" attr-imgurl="'+Reward.to_avatar+'" attr-money="'+Reward.money+'" attr-toname="'+Reward.to_name+'" from-name="'+tcpdata.from_client_name+'" from-avatar="'+tcpdata.headimg+'">';
			  text+='<var>'+tcpdata.from_client_name+'</var> 赞赏了 <var>'+Reward.to_name+'</var> 一个<b>红包</b>';
			  text+='</div>';
			  text+='</dt>';
		}		
		return text;
	}
	
	//获取评论信息分页
	function get_discontenthtml(tcpdata){
		var text='<dd class="comment_dd"  msg_id="'+tcpdata.msg_id+'">';
		text+='<span class="right_info">'; 
		if(user_info.is_manager){
			text+='<i class="btn_wall">上墙</i>';
		}
		text+='<i class="btn_like">0</i> <i class="btn_hate">0</i></span>';
		text+='<a class="avatar"><img src="'+tcpdata.headimg+'"></a><a class="author_name">'+tcpdata.from_client_name+'</a>';
		text+='<b class="time">'+tcpdata.time+'</b>';
		if(user_info.is_manager){
			text+='<i class="commentManage">管理<span class="commentManageTab" style="display:none;">';
			text+='<a class="delCommentMsg">删除</a><a class="shutUpSet">禁言</a></span></i>';
		}
		if(tcpdata.is_ask){
			tcpdata.content='<em class="ask_label">问</em>'+tcpdata.content;
		}	
		text+='<p class="content">'+tcpdata.content+'</p></dd>';
		return text;
	}

	//根据时间长度获取对应显示语音宽度
	function getRecordClass(last_second){
		if(last_second<12)
			return "recordwid1";
		else if(last_second>=12 && last_second<24)
			return "recordwid2";
		else if(last_second>=24 && last_second<36)
			return "recordwid3";
		else if(last_second>=36 && last_second<48)
			return "recordwid4";
		else if(last_second>48)
			return "recordwid5";
		else 
			return "recordwid1";
	}
	
	/*保存信息*/
	function sentContent(json_data){
		var posturl=location.href;
		$.post(posturl,json_data,function(result){
			if(result.success==-1){
				alert(result.data);
				return;
			}
			json_data.msg_id=result.msg_id;
			ws.send(JSON.stringify(json_data));
			globalObj.reward=null;
			globalObj.reward_last=null;
		});
	}
	
	/*声音播放完毕事件*/
	function voicePlayOver(){
		if(globalObj.voicePlaying){
			globalObj.voicePlaying.removeClass("isPlaying");
			globalObj.voicePlaying.addClass("isReaded ");
			globalObj.isPlaying=false;
			var msg_id=globalObj.voicePlaying.parents('dd').attr('msg_id');
			$(".recordingMsg").each(function(){
				if($(this).parents('dd').attr('msg_id')>msg_id){
					$(this).click();
					return false;
				}
			});
		}
	}
	/*下载语音事件*/
	function voiceDown(){
		$(".recordingMsg").unbind();
		$(".recordingMsg").click(function(){
    		var recordVoice=$(this).children("i");
    		var recordMsg=$(this);
    		$(".isPlaying").removeClass('isPlaying');
    		var attr_voice=recordVoice.attr('attr-src');
    		if(attr_voice&&attr_voice.indexOf('http')==0){
    			if($("#audioPlayer").attr('src')!=attr_voice)
    		  	   $("#audioPlayer").attr('src',attr_voice);
    		  	var media = $('#audioPlayer')[0];
    		  	if(media.paused) { 
    		  		media.play();
    		  		recordMsg.addClass("isPlaying");
    		  		globalObj.isPlaying=true;
    		    } else {  
    		    	media.pause(); 
    		    	recordMsg.removeClass('isPlaying')
    		    }
    		  	globalObj.voicePlaying=recordMsg;
    		  	media.removeEventListener("ended",voicePlayOver,false);
    		  	media.addEventListener("ended",voicePlayOver,false);
    		  	
    		  	return;
    		} 			  
		 });
	}
	
	/*分页获取信息课程*/
	var page_object={sub_pindex:1,sub_pages:sub_pages,com_pindex:1,com_pages:discuss_pages,loaded:[],loadeddis:[]};
	function ajax_content(pindex,dir){
		if($.inArray(pindex,page_object.loaded)==-1){
			page_object.loaded.push(pindex);
		}else{
			$(".btnLoadSpeakEnd").removeClass("on");
		    $(".btnLoadSpeak").removeClass("on");
			return;
		}
		var geturl=location.href;
		$.getJSON(geturl,{pindex:pindex,target:'main'}, function(data){
			page_object.sub_pages=data.total;
			page_object.sub_pindex=data.pindex;
			var last_content="";
			$.each(data.rows, function(i,item){
				if(item.msgtype=='image'){
					if($.inArray(item.content,globalObj.chat_images)==-1)
					    globalObj.chat_images.push(item.content);
				}
				last_content+=get_contenthtml(item);	   
		    });
			if(dir=='down')
			  $("#speakBubbles").append(last_content);   
			else
			  $("#speakBubbles").prepend(last_content);   
	    	voiceDown();
	    	$(".chat_img").unbind();
	    	$(".chat_img").click(function(){
	    		wx.previewImage({
	    		    current: $(this).attr('src'), // 当前显示图片的http链接
	    		    urls: globalObj.chat_images // 需要预览的图片http链接列表
	    		});
	    	});
	    	if(dir=='down')
	    	    $(".speakContentBox").scrollTop($(".speakContentBox")[0].scrollHeight-530);
	    	else
	    		$(".speakContentBox").scrollTop(18);
	    	$(".btnLoadSpeakEnd").removeClass("on");
		    $(".btnLoadSpeak").removeClass("on"); 
		    
		    /*赞赏功能开始*/
		     reward();
		    /*赞赏功能结束*/
		});
	}
	
	function ajax_discuss_content(pindex){
		if($.inArray(pindex,page_object.loadeddis)==-1){
			page_object.loadeddis.push(pindex);
		}else{
			$(".btnLoadComment ").removeClass("on");
			return;
		}
		var geturl=location.href;
		$.getJSON(geturl,{pindex:pindex,target:'discuss'}, function(data){
			page_object.com_pages=data.total;
			page_object.com_pindex=data.pindex;
			if(pindex==1&&data.rows.length>0){
				$("#loadNone").hide();		
			}
			var last_content="";
			$.each(data.rows, function(i,item){
				last_content+=get_discontenthtml(item);	   
		    });
			
			$("#commentDl").append(last_content);   
			
			$(".btn_wall").click(function(){
				  var ask_name= $(this).parents('dd').children('.author_name').text();
				  var ask_question=$(this).parents('dd').children('.content').text();
				  if(ask_question.indexOf('问')==0){
					  ask_question=ask_question.replace('问','');
				  }
				  globalObj.Question={N:ask_name,Q:ask_question};
				  $(".commentReplyBox").show();
			});
			
			$(".commentManage").unbind();
			$(".commentManage").click(function(){
				var display=$(this).children().css('display');
				if(display=='none'){
					$(this).children().show();
				}else{
					$(this).children().hide();
				}
			});
			
			$(".delCommentMsg").unbind();
			$(".delCommentMsg").click(function(){
				var conf=confirm("确认要删除吗?");
				if(conf){
					var msg_id=$(this).parents('dd').attr('msg_id');
					var msg_dd=$(this).parents('dd');
					$.post(location.href,{msg_id:msg_id,op:'del'},function(result){
						if(result.success==1){
							msg_dd.remove();
						}
					});
				}
			});
			
			$(".shutUpSet").unbind();
			$(".shutUpSet").click(function(){
				var conf=confirm("确认要对此用户禁言吗?");
				if(conf){
					var msg_id=$(this).parents('dd').attr('msg_id');
					var msg_dd=$(this).parents('dd');
					$.post(location.href,{msg_id:msg_id,op:'shutup'},function(result){
						if(result.success==1){
							alert(result.data);
						}
					});
				}
			});
			
	    	$(".commentContentBox").scrollTop($(".commentContentBox")[0].scrollHeight-530);
	    	$(".btnLoadComment ").removeClass("on");  
		});
	}
    
	//上传语音接口
    function uploadVoic(localId){
		wx.uploadVoice({
		    localId: localId, // 需要上传的音频的本地ID，由stopRecord接口获得
		    isShowProgressTips: 1, // 默认为1，显示进度提示
		    success: function (res) {
		    var serverId = res.serverId; // 返回音频的服务器端ID
		  	  $.post(down_url,{'mid':serverId,"mtype":'voice'},function(result){
 	        	  var img_url=result.data;
 	        	  var jsonObj = {type:"say",target:"main",msgtype:"voice",headimg:avatar,"last":clock_index, to_client_id: 'all',to_client_name:"all",content:img_url};
				  sentContent(jsonObj);
				  clock_index=0;
 	           });
		  	    
		    }
		});
	}
	  //初始化连接
  function TcpSocketConnect() {
	       ws = new WebSocket("ws://toupiao.duoguan.com:7272");
	       ws.onopen = onopen;
	       ws.onmessage = onmessage; 
	       ws.onclose = function() {
	          TcpSocketConnect();
	       };
	       ws.onerror = function() {
	     	  console.log("出现错误");
	       };
  }

	    // 连接建立时发送登录信息
  function onopen(){
	        if(!name){
	            name=nickname;
	        }
	        var jsonObj = {type:"login",headimg:avatar, client_name: name, room_id:room_id};
	        ws.send(JSON.stringify(jsonObj));
 }

 // 服务端发来消息时
 function onmessage(e){
	    	console.log(e.data);
	        var data = JSON.parse(e.data);
	        switch(data['type']){
	            case 'ping':
	                ws.send('{"type":"pong"}');
	                break;
	            case 'login':
	            	if(user_info.is_manager||user_info.is_guest)
	            	   $(".qlOLPeople").text(data['users']);
	            	else
	            	   $(".qlOLPeople").text(parseInt($(".qlOLPeople").text())+1);
	                break;
	            case 'say':
	            	if(data.target=='main')
	                    say(data);
	            	else
	            		say_discuss(data);
	                break;
	            case 'logout':
	                break;
	            case 'tipmsg':
	            	if(user_info.is_manager||user_info.is_guest)
		            	 $(".qlOLPeople").text(data['users']);
	        }
	    }
 
 		//讨论
		function say_discuss(tcpdata){
			$("#loadNone").hide();			
			var text='<dd class="comment_dd " msg_id="'+tcpdata.msg_id+'">';
				text+='<span class="right_info">'; 
				if(user_info.is_manager){
					text+='<i class="btn_wall">上墙</i>';
				}
				text+='<i class="btn_like">0</i> <i class="btn_hate">0</i></span>';
				text+='<a class="avatar"><img src="'+tcpdata.headimg+'"></a><a class="author_name">'+tcpdata.from_client_name+'</a>';
				text+='<b class="time">'+tcpdata.time+'</b>';
				
				if(user_info.is_manager){
					text+='<i class="commentManage">管理<span class="commentManageTab" style="display:none;">';
					text+='<a class="delCommentMsg">删除</a><a class="shutUpSet">禁言</a></span></i>';
				}
				
				if(tcpdata.is_ask){
					tcpdata.content='<em class="ask_label">问</em>'+tcpdata.content;
				}
				
				text+='<p class="content">'+tcpdata.content+'</p></dd>';
				$("#commentDl").prepend(text);
				$(".commentContentBox").scrollTop(15);
				$(".btn_wall").unbind();
				$(".btn_wall").click(function(){
					  var ask_name= $(this).parents('dd').children('.author_name').text();
					  var ask_question=$(this).parents('dd').children('.content').text();
					  if(ask_question.indexOf('问')==0){
						  ask_question=ask_question.replace('问','');
					  }
					  globalObj.Question={N:ask_name,Q:ask_question};
					  $(".commentReplyBox").show();
				});
				$(".commentManage").unbind();
				$(".commentManage").click(function(){
					var display=$(this).children().css('display');
					if(display=='none'){
						$(this).children().show();
					}else{
						$(this).children().hide();
					}
				});
				
				$(".delCommentMsg").unbind();
				$(".delCommentMsg").click(function(){
					var conf=confirm("确认要删除吗?");
					if(conf){
						var msg_id=$(this).parents('dd').attr('msg_id');
						var msg_dd=$(this).parents('dd');
						$.post(location.href,{msg_id:msg_id,op:'del'},function(result){
							if(result.success==1){
								msg_dd.remove();
							}
						});
					}
				});
				
				$(".shutUpSet").unbind();
				$(".shutUpSet").click(function(){
					var conf=confirm("确认要对此用户禁言吗?");
					if(conf){
						var msg_id=$(this).parents('dd').attr('msg_id');
						var msg_dd=$(this).parents('dd');
						$.post(location.href,{msg_id:msg_id,op:'shutup'},function(result){
							if(result.success==1){
								alert(result.data);
							}
						});
					}
				});
		}
	    // 发言
	    function say(tcpdata){

	    	var text='<dd class="left_bubble hasTime" msg_id="'+tcpdata.msg_id+'">';
				text+='<div class="speak_time">'+tcpdata.time+'</div>';
				text+='<div class="head_portrait"><img src="'+tcpdata.headimg+'"></div>';
				text+='<div class="speaker_name"><b>'+tcpdata.from_client_name+'</b></div>';
				text+='<div class="bubble_content "><p>'+tcpdata.content+'</p></div>';
				if(topic_setting.reward_status==1)
				text+='<a class="btn_ilike" href="javascript:;">赏</a>';
				text+='</dd>';
				
			if(tcpdata.msgtype=='voice'){
			       var voice_class=getRecordClass(tcpdata.last);
				   text='<dd class="left_bubble hasTime" msg_id="'+tcpdata.msg_id+'">';
				   text+='<div class="speak_time">'+tcpdata.time+'</div>';
				   text+='<div class="head_portrait"><img src="'+tcpdata.headimg+'"></div>';
				   text+='<div class="speaker_name"><b>'+tcpdata.from_client_name+'</b></div>';
				   text+='<div class="bubble_content recordingMsg '+voice_class+'">';
				   text+='<i class="audio_info" attr-src="'+tcpdata.content+'"></i><var>'+tcpdata.last+'</var></div>';
				   if(topic_setting.reward_status==1)
				   text+='<a class="btn_ilike" href="javascript:;">赏</a>';
				   text+='</dd>';
			}
			
			if(tcpdata.msgtype=='image'){
				text='<dd class="left_bubble hasTime" msg_id="'+tcpdata.msg_id+'">';
				text+='<div class="speak_time">'+tcpdata.time+'</div>';
				text+='<div class="head_portrait"><img src="'+tcpdata.headimg+'"></div>';
				text+='<div class="speaker_name"><b>'+tcpdata.from_client_name+'</b></div>';
				text+='<div class="bubble_content ">';
				text+='<img class="chat_img" mediaid="'+tcpdata.content+'" src="'+tcpdata.content+'"></div>';
				if(topic_setting.reward_status==1)
				text+='<a class="btn_ilike" href="javascript:;">赏</a>';
				text+='</dd>';
			}
			
			if(tcpdata.msgtype=='ask'){
				var Question=JSON.parse(unescape(tcpdata.content));
				text='<dd class="left_bubble hasTime" msg_id="'+tcpdata.msg_id+'">';
				text+='<div class="speak_time">'+tcpdata.time+'</div>';
				text+='<div class="head_portrait"><img src="'+tcpdata.headimg+'"></div>';
				text+='<div class="speaker_name"><b>'+tcpdata.from_client_name+'</b></div>';
				text+='<div class="bubble_content "><p><b>'+Question.N+':</b><em class="ask_label">问</em>'+Question.Q+'</p><p><b>回复:</b>'+Question.replay+'</p></div>';
				if(topic_setting.reward_status==1)
				text+='<a class="btn_ilike" href="javascript:;">赏</a>';
				text+='</dd>';
			}
			//赞赏
			if(tcpdata.msgtype=='reward'){
				var Reward=JSON.parse(unescape(tcpdata.content));
				text='<dt class="luckyMoney" msg_id="'+tcpdata.msg_id+'">';
				  text+='<div class="lm_main" attr-imgurl="'+Reward.to_avatar+'" attr-money="'+Reward.money+'" attr-toname="'+Reward.to_name+'" from-name="'+tcpdata.from_client_name+'" from-avatar="'+tcpdata.headimg+'">';
				  text+='<var>'+tcpdata.from_client_name+'</var> 赞赏了 <var>'+Reward.to_name+'</var> 一个<b>红包</b>';
				  text+='</div>';
				  text+='</dt>';
			}
			//讨论区禁言
			if(tcpdata.msgtype=='shutup'){
				if(tcpdata.content=='on'){
				  text='<dt class="bubble_dt"><b>讨论区现在禁止发言</b></dt>';
				  $(".commentBox").find(".comment_input_box").html('<b class="shutupbox">本直播当前为禁言状态</b>');
				}
				else{
				  text='<dt class="bubble_dt"><b>讨论区现在允许发言</b></dt>';
				  $(".commentBox").find(".comment_input_box").html('<b class="commentInput">来说点什么吧...</b>');
				  $(".commentInput").unbind();
				  $(".commentInput").click(function(){
				      $(".commentBox").addClass('typing');
					  $(".danmuBottom").show();
					  $(".danmuBottom").css("max-height",'25rem');
					  $(".qlDanmuBg").show();
				  });
				}
			}
									
	    	$("#speakBubbles").append(text); 
	    	
	    	$(".speakContentBox").scrollTop($(".speakContentBox")[0].scrollHeight);
	    	
	    	if(tcpdata.msgtype=='voice'){
		    	voiceDown();
		    	if(!globalObj.isPlaying||globalObj.isPlaying==false){
		    	   $(".recordingMsg").last().click();
		    	}
	    	}
	    	$(".chat_img").unbind();
	    	$(".chat_img").click(function(){
	    		if($.inArray($(this).attr('src'),globalObj.chat_images)==-1){
	    			globalObj.chat_images.push($(this).attr('src'));
	    		}
	    		wx.previewImage({
	    		    current: $(this).attr('src'), // 当前显示图片的http链接
	    		    urls: globalObj.chat_images // 需要预览的图片http链接列表
	    		});
	    	 });
	    	//赞赏
	    	reward();
	  }

	  $(function(){
	    	TcpSocketConnect();
	    	if(istopic_end==0){
	    		page_object.sub_pindex=page_object.sub_pages;
	    	}

	    	ajax_content(page_object.sub_pindex,'down');
	    	ajax_discuss_content(page_object.com_pindex);
	    	
	    	$(".btn_ask").click(function(){
	    		$(this).toggleClass('on');
	    	});
	    		    	
	    	$(".btnLiveTalk").click(function(){
	    	   var inputText = $(".speakInput").val();
	    	   if(inputText==''){
	    		   return;
	    	   }
	    	   var jsonObj = {type:"say",target:"main",msgtype:"text",headimg:avatar, to_client_id: 'all',to_client_name:"all",content:inputText};
	    	   sentContent(jsonObj);
	  	       $(".speakInput").val('');
	    	});
	    	//开始录音
	    	$("#btnStartRec").click(function(){
	    		$(".rec_Title_box").hide();
	    		$(".tab_recordingType").hide();
	    		$(".btn_dd").addClass('startRec');
	    		$(".second_dd").show();
	    		wx.startRecord();
	    		startRec();
	    		$("#btnStopRec").show();
	    		clock_handler=setInterval(startRec,1000);
	    	});
	    	//停止录音 
	    	$("#btnStopRec").click(function(){
	    		clearInterval(clock_handler);
	    		$(".btn_dd").removeClass('startRec');
	    		$(".btn_dd").addClass('stopRec');
	    		$("#btnStopRec").hide();
	    		$("#btnSentRec").show();
	    		wx.stopRecord({
	    		    success: function (res) {
	    		    	current_voiclocalid = res.localId;
	    		    }
	    		});
	    	});
	    	//发送录音 
	    	$("#btnSentRec").click(function(){
	    		if(current_voiclocalid!=null){
	    		   uploadVoic(current_voiclocalid);
	    		}
	    		$("#btnSentRec").hide();
	    		$(".btn_dd").removeClass('stopRec');
	    		$(".second_dd var").eq(0).text(0);
	    		
	    		$(".rec_Title_box").show();
	    		$(".tab_recordingType").show();
	    		$(".second_dd").hide();
	    	});	 
	    	
	    	$("#btnCancelRec").click(function(){
	    		current_voiclocalid==null
	    		$("#btnSentRec").hide();
		    	$(".btn_dd").removeClass('stopRec');
		    	$(".second_dd var").eq(0).text(0);
		    		
		    	$(".rec_Title_box").show();
		    	$(".tab_recordingType").show();
		    	$(".second_dd").hide();
		    	 clock_index=0;
	    	});
	    	
	    	$(".btn_img").click(function(){
	    		 wx.chooseImage({
	    			    count: 1, 
	    			    sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
	    			    sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
	    			    success: function (res) {
	    			       var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
	    			       var i = 0, length = 1;
	    			       globalObj.localIds=localIds;
	    			       function upload(){
			    			     var firstId=globalObj.localIds[i].toString();
			    			      wx.uploadImage({
			    			    	    localId: firstId, // 需要上传的图片的本地ID，由chooseImage接口获得
			    			    	    isShowProgressTips: 1, // 默认为1，显示进度提示
			    			    	    success: function (res) {
			    			    	        var serverId = res.serverId; // 返回图片的服务器端ID
			    			    	        $.post(down_url,{'mid':serverId,"mtype":'image'},function(result){
			    			    	        	 var img_url=result.data;
			    			    	        	 var jsonObj = {type:"say",target:"main",msgtype:"image",headimg:avatar,to_client_id: 'all',to_client_name:"all",content:img_url};
					    					  	 sentContent(jsonObj);
			    			    	        });
			    			    	       
			    					  	    i++;
			    					  	    if (i < 1) {
			    					            upload();
			    					        }
			    			    	    }
			    			    });
	    			       }
	    			       
	    			       upload();		    			    
	    			    }
	    		}); 
	    		return false;
	    	});
	    	
	    //评论框加载事件
	     $(".commentContentBox").scroll(function(){
	    	 var scrollTop = $(this).scrollTop();
	            var scrollHeight = $(this)[0].scrollHeight;
	            var windowHeight = $(this)[0].clientHeight; 
	            console.dir(scrollTop + windowHeight +"__"+ scrollHeight);
	            if (scrollTop + windowHeight+1 >= scrollHeight) {  //滚动到底部执行事件
	            	if(page_object.com_pindex<page_object.com_pages){
	            	   page_object.com_pindex=page_object.com_pindex+1;
	            	   $(".btnLoadComment ").addClass("on"); 
	            	   ajax_discuss_content(page_object.com_pindex);
	            	}
	            }
	      });
	     $(".speakContentBox").scroll(function() {
	    	    var scrollTop = $(this).scrollTop();
	            var scrollHeight = $(this)[0].scrollHeight;
	            var windowHeight = $(this)[0].clientHeight; 
	            if (scrollTop + windowHeight+2 >= scrollHeight) {  //滚动到底部执行事件
	            	if(page_object.sub_pindex<sub_pages){ 
	            	  $(".btnLoadSpeakEnd").addClass("on");
	            	  page_object.sub_pindex=page_object.sub_pindex+1;
	            	  ajax_content(page_object.sub_pindex,"down");
	            	}
	            }
	            
	            if (scrollTop < 3 ) {  //滚动到头部部执行事件
	            	 if(page_object.sub_pindex>1){
	            	   $(".btnLoadSpeak").addClass("on");
	            	   page_object.sub_pindex=page_object.sub_pindex-1;
	            	   ajax_content(page_object.sub_pindex,"up")
	            	 }
	            }
	    	   
	      });	 
	     
	     /*上墙*/
	      $(".btn_wall").click(function(){
			  var ask_name= $(this).parents('dd').children('.author_name').text();
			  var ask_question=$(this).parents('dd').children('.content').html();
			  globalObj.Question={N:ask_name,Q:ask_question};
			  $(".commentReplyBox").show();
		  });
		  $(".gene_cancel").click(function(){
			  $(".geneBox").hide()
		  });

		  $(".commentManage").first().click(function(){
				var display=$(this).children().css('display');
				if(display=='none'){
					$(this).children().show();
				}else{
					$(this).children().hide();
				}
			});
		  
		  //上墙确认按钮
		  $(".commentReplyBox .gene_confirm").click(function(){
			  var reply_text=$(".commentReplyBox .reply_textarea").val();
			  if(reply_text==''){
				  return;
			  }
			  if(!globalObj.Question){
				  return;
			  }
			  //给赋值
			  globalObj.Question.replay=reply_text;
			  var jsonObj = {type:"say",target:"main",msgtype:"ask",headimg:avatar, to_client_id: 'all',to_client_name:"all",content:JSON.stringify(globalObj.Question)};
	    	  sentContent(jsonObj);
	    	  //ws.send(JSON.stringify(jsonObj));
	  	      $(".reply_textarea").val('');
			  $(".commentReplyBox").hide();
		  });
		  /*取消*/
		  $(".redbag_cancel").click(function(){
			  $(".redbag_box").hide();
		  });
		  
		  /*讨论区禁言*/
		  $("#allShutup").click(function(){
			  $(this).toggleClass('swon');  
			  if($(this).hasClass('swon')){
					 var jsonObj = {type:"say",target:"main",msgtype:"shutup",headimg:avatar, to_client_id: 'all',to_client_name:"all",content:"on"};
					 sentContent(jsonObj);
				 }else{
					 var jsonObj = {type:"say",target:"main",msgtype:"shutup",headimg:avatar, to_client_id: 'all',to_client_name:"all",content:"off"};
					 sentContent(jsonObj);
				 }
		  });
		  /*自动播放*/
		  $("#btnAutoPlay").click(function(){
			 
			 $(this).toggleClass('swon');  
		  });
   });
	  
$(function(){	  
	  $(".btnBackVoice").click(function(){
	      $(".speakBox").removeClass("textBottom");
		  $(".speakBox").addClass("hasTabBottom");
	  });
	  		
	  $(".tabToComment,.write_dan_a").click(function(){
	      $(".commentBox").show();
	  });

      $(".backToLive").click(function(){
	      $(".commentBox").hide();
	  });
      
      $(".commentInput").click(function(){
	      $(".commentBox").addClass('typing');
		  $(".danmuBottom").show();
		  $(".danmuBottom").css("max-height",'25rem');
		  $(".qlDanmuBg").show();
	  });
	 
	  $(".btnCommentCancel").click(function(){
	       $(".commentBox").removeClass('typing');
		  $(".danmuBottom").hide();
		  $(".danmuBottom").css("max-height",'0rem');
		  $(".qlDanmuBg").hide();
	  });
	  
	  $("#btn_discuss_send").click(function(){
		 var text=$(".danmuInput").val(); 
		 if(text==''){
			 return;
		 }
		 var jsonObj = {type:"say",target:"discuss",msgtype:"text",headimg:avatar, to_client_id: 'all',to_client_name:"all",content:text};
  	     if($('.btn_ask').hasClass('on')){
  	    	jsonObj.is_ask=true;
  	     }
		 sentContent(jsonObj);
	     $(".danmuInput").val('');
	     $('.btn_ask').removeClass('on')
	  });

	  $(".isdan_btn_a").click(function(){
		  $(".danmu_bar").toggleClass("on");
		  if($(this).text()=='关'){$(this).text('弹');}else{$(this).text('关');}
	  });
	  
	  $(".btn_gtb_close").click(function(){
		  $(".setGuestTips").hide();
	  });

	  $(".tab_others").click(function(){
		 if(!$(".speakBox").hasClass('othersBottom'))
		     $(".speakBox").removeClass("hasTabBottom");
	      else
	    	 $(".speakBox").addClass("hasTabBottom");
	     $(".speakBox").removeClass("textBottom");
	     $(".speakBox").removeClass("voiceBottom");
		 $(".speakBox").toggleClass("othersBottom");
	 });
	 
	 $(".tab_text").click(function(){
		 if(!$(".speakBox").hasClass('textBottom'))
		     $(".speakBox").removeClass("hasTabBottom");
	      else
	    	 $(".speakBox").addClass("hasTabBottom");
	     $(".speakBox").removeClass("othersBottom");
	     $(".speakBox").removeClass("voiceBottom");
		 $(".speakBox").toggleClass("textBottom");
	  });

	 $(".tab_voice").click(function(){
	      $(".speakBox").removeClass("othersBottom");
	      $(".speakBox").removeClass("textBottom");
	      if(!$(".speakBox").hasClass('voiceBottom'))
		     $(".speakBox").removeClass("hasTabBottom");
	      else
	    	  $(".speakBox").addClass("hasTabBottom");
		  $(".speakBox").toggleClass("voiceBottom");
	 });
	 
	  $(".btnControlBox").click(function(){
	     $(".cbox_main").css("margin-bottom","0px");
		 $(".control_box").addClass("on");
	  });

	  $(".btn_closeCBox").click(function(){
	      $(".cbox_main").css("margin-bottom","-250px");
		  $(".control_box").removeClass("on");
	  });
	  
	  $(".close_elt").click(function(){
		  $(".qlMsgTips").hide();
	  });

	  
	  $(".btn_finish_live").click(function(){
		  var topic_id=$(this).attr('attr-topicid');
		  var conf=confirm("确定要结束吗?");
		  if(conf){
			  $.post(topic_overurl,{topic_id:topic_id},function(result){
				  if(result.success==1){
					  location.href=location.href+"&r=1";
				  }else{
					  alert(result.data);
				  }
			  });
		  }
	  });
});