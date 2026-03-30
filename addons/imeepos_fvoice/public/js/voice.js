var voice = {};
var recording = false;
var localId = null;
var timer = null;
var post = {};
post.timelong = 0;
posting = false;

voice.show = function(){
    console.log(_follow);
    console.log(_openid);
    if(_follow == 0 || _openid.length < 4){

        $.modal({
            title:'请扫码关注后发言',
            text: '<img src="'+_qrcode+'" style="width:100%;"/>',
            buttons: []
        });
        return '';
    }
    wx.ready(function(){
        $.modal({
            title:'语音录制',
            text: $('#voicePanel').html(),
            buttons: []
        });
    });
}

voice.play = function(){
    if(!post.localId){
        $.toast("你说话了么？我怎么没听到！");
        return '';
    }
    wx.ready(function(){
        wx.playVoice({
            localId: post.localId,
            success: function(e) {
                playing = true;
            },
            error:function(){
                $.toast("开启录音失败");
                return '';
            }
        });
        wx.onVoicePlayEnd({
            complete: function(e) {
                post.localId = res.localId;
                playing = false;
                $.toast("录音时间已达上限60秒");
                return '';
            }
        });
    });
}

voice.restart= function(){
    post.timelong = 0;
    recording = false;
    voice.start();
}

voice.stop = function(callback){
    wx.ready(function(){
        wx.stopRecord({
            success: function (res) {
                post.localId = res.localId;
                recording = false;
                if(callback){
                    callback();
                }
                setTimeout(function(){
                    $('.weui_dialog .weui_dialog_title').html('停止录音成功');
                },1000);
            }
        });
    });
}

voice.close = function(){
    $('.weui_dialog').hide();
    $('.weui_mask').removeClass('weui_mask_visible');
}

/*$(function(){
 voice.timer();
 })*/

voice.timer = function(){
    return setTimeout(function(){
        post.timelong = post.timelong + 1;
        $('.weui_dialog .weui_dialog_title').html('录音中：'+post.timelong+'s');
        if(recording){
            timer = voice.timer();
        }
    },1000);
}

voice.start = function(e){
    console.log(e);
    if(recording){
        voice.stop();
        $('.fa-pause').addClass('fa-play').removeClass('fa-pause');
    }
    wx.ready(function(){
        wx.startRecord({
            cancel: function() {
                $.toast("你拒绝了录音");
                return ;
            },
            success: function() {
                recording = true;
                $('.fa-play').addClass('fa-pause').removeClass('fa-play');
                timer = voice.timer();
            }
        });
        wx.onVoiceRecordEnd({
            complete: function(e) {
                post.localId = e.localId;
                recording = false;
                $(e).find('.fa').removeClass('fa-pause');
                $(e).find('.fa').addClass('fa-play');
                $('.weui_dialog .weui_dialog_title').html('录制已达最大时间限制');
            }
        });
    })
}


