(function($,window){
	var document = window.document,body = document.body;
	var html = {
		elem:"<div id='notification'></div>",
		text:"<span></span>",
		style:"<style></style>"
	},
	style = [
		"#notification{ position:fixed; left:50%; top:1em; padding:0.5em 1.2em; line-height:2em; z-index:25; -webkit-transform:translateX(-50%); -moz-transform:translateX(-50%); -ms-transform:translateX(-50%); transform:translateX(-50%); background-color:rgba(0,0,0,0.8); color:#fff; border-radius:0.5em;}",
		"#notification span{ display:block; font-size:1.4em; white-space:nowrap; max-width:12em; overflow:hidden;}"
	].join(''),
	defaultConfig = {
		content: "预览效果"
	},
	NF = function(config){
		this.config = config;
		this.elem = $(html.elem);
		this.text = $(html.text);
		this.style = $(html.style);
		this.setup();
	};
	NF.fn = NF.prototype = {
		setup: function(){
			this.text.html(this.config.content)
				.appendTo(this.elem);
			this.style.html(style).appendTo(body);
			this.elem.appendTo(body);
		},
		destory: function(){
			var i;
			this.style.html('').remove();
			this.elem.remove();
			for(i in this){
				delete this[i];
			}
		}
	}
	var notification = window.notification = function(config){
		var i;
		if(typeof config === 'undefined'){
			config = {};
		}else if(typeof config === 'string'){
			config = {content:config};
		}
		for(i in defaultConfig){
			if(typeof config[i] === 'undefined'){
				config[i] = defaultConfig[i];
			}
		}
		return new NF(config);
	}

})(PRselector,window)