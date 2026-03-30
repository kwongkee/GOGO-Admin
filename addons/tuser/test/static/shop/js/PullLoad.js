(function($){
	$.extend({
		pullLoadGroup: [],
		pullLoad:function(config){
			return $.PullLoad(config);
		},
		PullLoad:function(config){
			if(!config) return false;
			return new PL(config);
		}
	});
	var PL = function(config){
		this.list = $(config.list);
		this.loader = $(config.loader);
		this.afterAppendNodes = config.afterAppendNodes || function(){};
		this.useStorage = config.useStorage || false;
		if(this.useStorage){
			this.storageChild = [];
			this.otherStorage = '';
			this.setOtherStorage = config.setStorage || function(){};
		}else{
			this.setOtherStorage = function(){};
		}
		this.emptyInfo = config.emptyInfo || '暂无数据';
		this.fn = config.fn;
		this.loadPage = 0;
		this.end = false;
		this.emptyDom = $([
			'<div class = "emptyInfo">',
				'<div class = "emptyIcon">',
					'<svg><use xlink:href="#icon_fail_1"></use></svg>',
				'</div>',
				'<div class = "emptyText">',
					'<h6>'+this.emptyInfo+'</h6>',
				'</div>',
			'<div>'
		].join(''));
		this.storageHash = config.storageHash || 'pullLoadStorage';
		var _this = this;
		this.checkLoadHandler = function(){
			_this.checkLoad.call(_this);
		};
		this.pullLoadGroupNum = $.pullLoadGroup.length;
		$.pullLoadGroup.push(this);
		this.setup();
	};
	PL.prototype = {
		setup: function(){
			this.isLoading = false;
			this.code = new Date().getTime() + '' + Math.random();
			if(this.useStorage) this.setStorage();
			this.bindEvents();
			this.checkLoadHandler();
		},
		setStorage: function(){
			if(window.location.href.indexOf('http')!=0){
				this.useStorage = false;
				return;
			}
			if(!window.localStorage) return;
			var key = encodeURIComponent(window.location.href.replace(window.location.hash,'')+this.pullLoadGroupNum).replace(/\%/g,'_').replace(/\./g,'-');
			if(key.length>255){
				key = key.substr(key.length-255);
			}
			this.key = key;
			if(window.location.hash.indexOf(this.storageHash)===-1) return;
			this.resetStorageHash();
			var storage = window.localStorage,data,dataStorage;

			data = storage.getItem(this.key);
			if(data && data!='null'){
				data = eval('('+data+')');
			}
			if(typeof this.useStorage === 'function'){
				dataStorage = (data && typeof data === 'object' && typeof data.other !== 'undefined')? data.other:data;
				this.useStorage(dataStorage);
			}
			if(data && data!='null'){
				this.readStorage(data);
			}
			this.resetStorageHash();
			storage.setItem(this.key,'');
			this.setOtherStorageHandler();
		},
		setOtherStorageHandler: function(){
			var callback = (function(storage){
				this.otherStorage = storage;
			}).bind(this);
			this.setOtherStorage(callback);
		},
		readStorage: function(data){
			var _this = this,
				storageChild;
			this.loadPage = data.loadPage;
			this.end = data.end;
			this.scrollTop = data.scrollTop;
			storageChild = data.child;
			this.appendChild(storageChild);
			setTimeout(function(){
				window.scrollTo(0,_this.scrollTop);
			},50);
		},
		resetStorageHash: function(){
			var hash = window.location.hash,
				url = window.location.origin+window.location.pathname + window.location.search,
				title = window.document.title,
				state;
			hash = hash.replace('#'+this.storageHash,'').replace('&'+this.storageHash,'');
			if(url.indexOf('http')!=0){
				window.location.hash = hash;
			}else{
				url = url + hash;
				state = {
					title: title,
					url:url
				};
				history.replaceState(state, title, url);
			}
		},
		setStorageHash: function(){
			var hash = window.location.hash,
				url = window.location.origin+window.location.pathname + window.location.search,
				title = window.document.title,
				state;
			if(!hash || hash == '#'){
				hash = '#'+this.storageHash;
			}else{
				hash += '&'+this.storageHash;
			}
			if(url.indexOf('http')!=0){
				window.location.hash = hash;
			}else{
				url = url+hash;
				state = {
					title: title,
					url:url
				};
				history.replaceState(state, title, url);
			}
		},
		bindEvents: function(){
			var _this = this;
			$(window).on('load',this.checkLoadHandler);
			$(document).on('scroll',this.checkLoadHandler);
			if(this.useStorage){
				this.list.off().on('click','a',function(e,target){
					target = target || this;
					var data;
					if(target.href && target.href.indexOf('#')!=0 && target.href.indexOf('javascript:')==-1){
						data = {
							loadPage : _this.loadPage,
							end : _this.end,
							scrollTop :  document.body.scrollTop || document.documentElement.scrollTop,
							child : _this.storageChild,
							other : _this.otherStorage
						};
						_this.setStorageHash();
						window.localStorage.setItem(_this.key,JSON.stringify(data));
					}
				});
			}
		},
		unbindEvents: function(){
			$(window).off('load',this.checkLoadHandler);
			$(document).off('scroll',this.checkLoadHandler);
		},
		checkLoad: function(){
			if(this.isLoading || this.end) return;
			if(this.getScrollTop() + document.documentElement.clientHeight > this.loader.position().top - this.loader.height()*3){
				this.loadList();
			}
		},
		getScrollTop: function(){
			return document.body.scrollTop || document.documentElement.scrollTop;
		},
		loadList: function(){
			var _this = this,callback = {};
			this.isLoading = true;
			
			callback.code = this.code;
			callback.fn = (function(_this){
				return function(child,end){
					_this.end = typeof end === 'undefined'? false: !!end;
					if(!child || !child.length && _this.end){
						_this.checkEmpty();
					}else{
						_this.appendChild.call(_this,child);
					}
					_this.setOtherStorageHandler();
				}
			})(this);
			
			this.fn(this.loadPage,function(child,end){
				if(callback.code === _this.code){
					callback.fn(child,end);
				}
			});
		},
		checkEmpty: function(){
			if(this.loadPage == 0){
				this.loader.remove();
				this.unbindEvents();
				this.emptyDom.insertAfter(this.list);
			}
		},
		appendChild: function(child){
			var i = 0,tempChild;
			if(typeof child === 'string'){
				child = [child];
			}
			if(this.useStorage){
				this.storageChild = this.storageChild.concat(child);
			}
			for(; i< child.length; i++){
				tempChild = $(child[i]);
				this.list.append(tempChild);
				if(typeof window.LazyLoad != 'undefined'){
					LazyLoad(tempChild.find('img'));
				}
				tempChild = null;
			}
			this.isLoading = false;
			this.loadPage++;
			this.afterAppendNodes(child,this.loadPage);
			if(!this.end){
				this.checkLoad();
			}else{
				this.loader.remove();
				this.unbindEvents();
			}
		},
		resetList: function(){
			this.list.html('');
			this.storageChild = [];
			this.isLoading = false;
			this.end = false;
			this.loadPage = 0;
			this.code = new Date().getTime() + '' + Math.random();
			this.unbindEvents();
			this.bindEvents();
			this.emptyDom.remove();
			this.loader.insertAfter(this.list);
			this.checkLoadHandler();
		}
	}
})(window.PRselector || window.jQuery);