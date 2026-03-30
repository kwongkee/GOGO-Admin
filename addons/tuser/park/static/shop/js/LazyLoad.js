(function($){
	var defaultConfig = {
		event: 'scroll',
		loadDelay: 150,
		checkTranslate: false,
		checkX:false,
		checkY:true,
		checkMotion: false,
		preload:'1screen'
	},defaultSrc = 'data:image/PNG;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAEElEQVR42gEFAPr/AP///wAI/AL+Sr4t6gAAAABJRU5ErkJggg==',
	lazyLoadArray = [],
	LazyLoad = window.LazyLoad = function(img,config){
		var i = 0, j, k, l, flag = false,eq = true,currentObj,newLazyLoad;
		if(!img) return;
		img = $(img);
		config = (typeof config !== 'object')? {}: config;
		
		for(k in defaultConfig){
			if(typeof config[k] === 'undefined'){
				config[k] = defaultConfig[k];
			}
		}
		for(; i<lazyLoadArray.length; i++){
			eq = true;
			for(j in lazyLoadArray[i].config){
				if(config[j]!=lazyLoadArray[i].config[j]){
					eq = false; 
					break;
				}
			}
			for(l in config){
				if(config[l]!=lazyLoadArray[i].config[l]){
					eq = false; 
					break;
				}
			}
			if(eq){
				currentObj = lazyLoadArray[i];
				flag = true;
				break;
			}
		}
		if(!flag){
			newLazyLoad = new LA(img,config);
			lazyLoadArray.push(newLazyLoad);
			return newLazyLoad;
		}else{
			currentObj.addImg(img);
			return currentObj;
		}
	},
	LA = function(img,config){
		this.loadTimeout = null;
			
		this.config = config;
		this.img = [];
		this.imgInit(img);
		this.setup();
		this.checkLoadHandler();
	},
	fn = LA.prototype = {
		setup: function(){
			this.bindEvents();
		},
		imgInit: function(img){
			var _this = this;
			img.each(function(){
				var position;
				if(!this.src && (!!this.dataset.src || !!this.dataset.bg)){
					this.src = defaultSrc;
					if(_this.config.checkMotion){
						if(this.offsetWidth && this.offsetHeight){
							position = $(this).position();
							this.prLeft = position.left;
							this.prTop = position.top;
						}
					}
					_this.img.push(this);
				}
			});
			
		},
		bindEvents: function(){
			var _this = this;
			$(document).on(this.config.event,function(){
				_this.checkLoadHandler();
			});
			$(window).on('load',function(){
				_this.checkLoadHandler();
			});
		},
		addImg: function(img){
			var _this = this;
			this.imgInit(img);
			this.checkLoadHandler();
		},
		checkLoadHandler: function(){
			var _this = this;
			clearTimeout(this.loadTimeout);
			this.loadTimeout = setTimeout(function(){_this.checkLoad.call(_this)},this.config.loadDelay);
		},
		checkLoad: function(checkTranslate){
			var i = 0,parent,onShow = false;
			if(typeof checkTranslate !== 'boolean'){
				checkTranslate = this.config.checkTranslate;
			}
			for(;i<this.img.length; i++){
				if(!this.img[i].offsetWidth || !this.img[i].offsetHeight) continue;
				onShow = false;
				if(this.config.parent && this.config.parentSelector){
					if(typeof this.config.parent === 'string'){
						parent = $(this.img[i]).parents(this.config.parent);
					}
					if(parent.is(this.config.parentSelector)){
						onShow = true;
					}
				}else if(this.isShow(this.img[i],checkTranslate)){
					onShow = true;
				}
				if(onShow){
					this.loadImg(this.img[i]);
					this.img.splice(i,1);
					i--;
				}
			}
		},
		
		loadImg: function(obj){
			obj.loadOk = true;
			if(typeof obj.dataset.src !== 'undefined'){
				obj.src = obj.dataset.src;
			}else if(typeof obj.dataset.bg !== 'undefined'){
				obj.style.width = '100%';
				obj.style.height = '100%';
				obj.style.backgroundPosition = '50% 50%';
				obj.style.backgroundRepeat = 'no-repeat';
				obj.style.backgroundSize = 'cover';
				obj.style.backgroundImage = 'url('+obj.dataset.bg+')';
			}
		},
		
		isShow: function(obj,checkTranslate){
			var totalX = 0, totalY = 0,
				onShow = true,
				browser = ['-webkit-','-moz-','-ms-','-o-',''],
				bodyLeft = document.body.scrollLeft || document.documentElement.scrollLeft,
				bodyTop = document.body.scrollTop || document.documentElement.scrollTop,
				bodyWidth = document.documentElement.clientWidth,
				bodyHeight = document.documentElement.clientHeight,
				preload = (typeof this.config.preload !== 'number' && this.config.preload.indexOf('screen')!=-1)? parseFloat(this.config.preload.replace('screen',''))*window.innerHeight : parseInt(this.config.preload);
			
			if(checkTranslate){
				$(obj).parents.each(function(){
					var translateCode;
					for(var i in browser){
						translateCode = $(this).css(browser[i]+'transform')
						if(translateCode) break;
					}
					if(translateCode){
						translateCode = translateCode.split('(')[1].split(',');
						for(var i in translateCode){
							translateCode[i] = parseFloat(translateCode[i]);
						}
						if(config.checkX){
							totalX = totalX*translateCode[0] + translateCode[4];
						}
						if(config.checkY){
							totalY = totalY*translateCode[3] + translateCode[5];
						}
					}
				});
			}
			if(this.config.checkX){
				totalX += obj.prLeft || $(obj).position().left;
				if(totalX - bodyLeft < -obj.offsetWidth || totalX - bodyLeft - preload > bodyWidth){
					onShow = false;
				}
			}
			if(this.config.checkY){
				totalY += obj.prTop || $(obj).position().top;
				if(totalY - bodyTop < -obj.offsetHeight || totalY - bodyTop - preload > bodyHeight){
					onShow = false;
				}
			}
			return onShow;
		}
	};
	LazyLoad.checkLoad = function(){
		var i = 0;
		for(; i<lazyLoadArray.length; i++){
			lazyLoadArray[i].checkLoad();
		}
	};
})(PRselector);