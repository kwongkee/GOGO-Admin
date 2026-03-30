(function(window,$){
	var Dialogue = function(){
		this.dom = $('<div class="dialogue"></div>');
		this.element = $('<div class="dialogueField"></div>');
		this.title = $('<h1></h1>');
		this.content = $('<div class="content"></div>');
		this.loadField = $('<div class="loading"><i><svg><use xlink:href = "#icon_loading_1"/></svg></i></div>');
		this.btnField = $('<div class="btns"></div>');
		this.btnHtml = '<button class="btn"></button>';
		this.picClass = 'pics';
		this.pic = $('<img src="" alt="" />');
		this.defaultSrc = 'data:image/PNG;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAEElEQVR42gEFAPr/AP///wAI/AL+Sr4t6gAAAABJRU5ErkJggg==';
		this.btnClass = {
			btn1:'btn1',
			btn2:'btn2'
		}
		this.btns = [];
		this.isShow = false;
		this._setup();
	}
	Dialogue.prototype = {
		dlShowPic: function(config){
			var _this = this;
			config = this._readConfig(config,'内容图片');
			if(!config.btns.length){
				config.btns = [{
					text:'关闭',
					fn:function(){
						_this.closeAll();
					}
				}]
			}
			this.closeAll(function(_this){
				_this.isShow = true;
				_this.dom.appendTo('body');
				_this.element.addClass(_this.picClass).appendTo(_this.dom);
				_this.title.html(config.title).appendTo(_this.element);
				_this.content.appendTo(_this.element);
				_this.pic.attr('src',config.content).appendTo(_this.content);
				_this._addBtn(config.btns);
				_this.dom.css('z-index','21').show();
			});
		},
		
		dlAlert: function(config){
			
			var _this = this;
			config = this._readConfig(config);
			if(!config.btns.length){
				config.btns = [{
					text:'确认',
					fn:function(){
						_this.closeAll();
					}
				}]
			}
			this.closeAll(function(_this){
				_this.isShow = true;
				_this.dom.appendTo('body');
				_this.element.appendTo(_this.dom);
				_this.title.html(config.title).appendTo(_this.element);
				if(config.content){
					_this.content.html(config.content).appendTo(_this.element);
				}
				_this._addBtn(config.btns);
				_this.dom.css('z-index','21').show();
			});
		},
		
		dlWarning: function(config){
			
			var _this = this;
			config = this._readConfig(config);
			if(!config.btns.length){
				config.btns = [{
					text:'重试',
					fn:function(){
						_this.closeAll();
					}
				}]
			}
			this.closeAll(function(_this){
				_this.isShow = true;
				_this.dom.appendTo('body');
				_this.element.appendTo(_this.dom);
				_this.title.html(config.title).appendTo(_this.element);
				if(config.content){
					_this.content.html(config.content).appendTo(_this.element);
				}
				_this._addBtn(config.btns);
				_this.dom.css('z-index','21').show();
			});
		},
		
		dlLoading: function(){
			var _this = this;
			//config = this._readConfig(config);
			this.closeAll(function(_this){
				_this.isShow = true;
				_this.dom.appendTo('body');
				//_this.title.html(config.title).appendTo(_this.element);
				_this.loadField.appendTo(_this.dom);
				_this.dom.css('z-index','21').show();
			});
		},
		
		closeAll: function(fn){
			
			var _this = this;
			fn = fn || function(){};
			var callBack = function(){
				_this.element.removeClass(_this.picClass).remove();
				_this.pic.attr('src',_this.defaultSrc);
				_this.pic.remove();
				_this.title.remove();
				_this.content.html('').remove();
				_this.loadField.remove();
				for(var i in _this.btns){
					_this.btns[i].unbind().remove();
				}
				_this.btns = [];
				_this.btnField.remove();
				_this.dom.remove();
				_this.isShow = false;
				fn(_this);
			}
			if(this.isShow){
				this.dom.hide();
				callBack();
			}else{
				callBack();
			}
		},
		
		_setup: function(){
		},
		
		_readConfig: function(config,title){
			title = title || '温馨提示';
			var configReturn = {};
			if(typeof config == 'string'){
				config = {title:title,content:config};
			}
			configReturn.title = config.title || title;
			configReturn.content = config.content || '';
			configReturn.btns = config.btns || [];
			return configReturn;
		},
		
		_addBtn: function(btns){
			var i = 0,k = 0,tempBtn;
			if(!!btns.length){
				this.btnField.appendTo(this.element);
			}
			for(;i < btns.length; i++){
				k = btns.length - i - 1;
				tempBtn = $(this.btnHtml);
				if(k == 0){
					tempBtn.addClass(btns[k].class || this.btnClass.btn1);
				}else{
					tempBtn.addClass(btns[k].class || this.btnClass.btn2);
				}
				var tempFn = btns[k].fn;
				tempBtn.html(btns[k].text).appendTo(this.btnField).bind('click',tempFn);
				this.btns.push(tempBtn);
			}
		}
	}
	var dialogue = null;
	if(window.top != window.self && window.top.dialogue){
		dialogue = window.top.dialogue;
	}else{
		dialogue = new Dialogue();
	}
	window.dialogue = dialogue;
})(window,PRselector);