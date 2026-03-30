(function($,window){
	var document = window.document,body = document.body;
	var bombboxHtml = '<section id="bombboxMain"></section>',
		bombboxCss = {
			'position':'absolute',
			'left':0,
			'right':0,
			'bottom':0,
			'top':0,
			'min-height':'100%',
			'transform':'translateY(100%)',
			'z-index':1,
			'background-color':'#fff'
		};
	var bombboxSelector = {
		list:'.quickSelectPanel_01 ul'
	};
	var trim = function(string) { return string.replace(/(^\s*)|(\s*$)/g,"");};
	var listHtml = '' +
		'<div class="quickSelectPanel_01">'+
			'<ul>' +
				'{{content; repeat(\'<li>' +
					'<a href="{{repeatItem.url}}"><span>{{repeatItem.name}}</span></a>' +
				'</li>\',content)}}' +
			'</ul>'+
		'</div>',
		searchSplitCode = ['☫','☭','♤'].map(function(s){
			return s;
		});

	var defaultConfig = {
		defaultContent: '',
		closeBtn: '',
		startSearch: function(){},
		endSearch: function(){}
	};
	
	var QS = function(input,config,data){
		this.init(input,config,data);
	};
	QS.prototype = {
		isShow: false,
		init: function(input,config,data){
			this.searchTimeout = null;

			this.input = $(input);
			this.data = data || [];
			this.config = config;
			this.closeBtn = $(this.config.closeBtn);
			this.hideCallBack = null;
			this.setup();
		},
		dataInit: function(data){
			data = data || this.data;
			this.data = data;
			this.length = 0;
			this.top = 0;
			this.getSearchKeywords();
		},
		setup: function(){
			this.openBoxHandler = this.openBox.bind(this);
			this.inputHandler = this.inputValue.bind(this);
			this.closeBoxHandler = this.closeBox.bind(this);
			this.bombbox = $(bombboxHtml);
			this.bombbox.css(bombboxCss);

			this.dataInit();
			this.bindEvents();
		},
		openBox: function(){
			if(this.isShow) return;
			this.isShow = true;
			this.bombbox.appendTo(body);
			this.top = document.body.scrollTop || document.documentElement.scrollTop;
			this.bombbox.css('transform','translateY(0)');
			this.config.startSearch();
			this.setDefault();
		},
		closeBox: function(){
			if(!this.isShow) return;
			var self = this;
			this.isShow = false;
			window.scrollTo(0,this.top);
			this.bombbox.css('transform','translateY(100%)');
			this.input.val();
			clearTimeout(this.hideCallBack);
			this.hideCallBack = setTimeout(function(){
				self.bombbox.remove();
			},300);
			this.config.endSearch();
		},
		getSearchKeywords: function(){
			var i = 0,keywords = '',key,word;
			for(; i< this.data.length; i++){
				key = [];
				word = this.data[i].name.toString();
				key.push(word.toLowerCase());
				key.push(GetPinyin.getFirstLetter(word).toString().toLowerCase());
				key.push(GetPinyin.getPinyin(word).toString().toLowerCase());
				keywords+=key.join(searchSplitCode[0])+searchSplitCode[1]+i+searchSplitCode[2];
			}
			this.keywords = keywords;
		},
		searchList: function(keyword){
			var reg = eval('/'+keyword+'.*?'+searchSplitCode[1]+'.+?'+searchSplitCode[2]+'/gi'),
				matchWords = this.keywords.match(reg),
				ids = {},i=0,key='';
			if(matchWords){
				for(; i<matchWords.length; i++){
					key = matchWords[i].replace(searchSplitCode[2],'').split(searchSplitCode[1]).pop();
					ids[key] = true;
				}
			}
			this.setList(ids);
		},
		setDefault: function(){
			this.bombbox.html(this.config.defaultContent);
		},
		setList: function(ids){
			var i,dom = '',data = {
				content: []
			};
			if(typeof ids === 'undefined'){
				ids = this.data;
			}
			for(i in ids){
				if(typeof this.data[i] !== 'undefined'){
					data.content.push(this.data[i]);
				}
			}
			dom = TemplateMod.createItem(listHtml,data);
			this.bombbox.html('');
			dom.appendTo(this.bombbox);
		},
		bindEvents: function(){
			var self = this;
			
			this.input.on('focus',this.openBoxHandler);
			this.input.on('input propertychange',this.inputHandler);
			this.closeBtn.on('click',this.closeBoxHandler);
		},
		inputValue: function(val){
			var keywords = typeof val === 'string' && trim(val) || trim(this.input.val()),
				keys = ['\\','|','[',']','{','}','(',')','.','/','^','=','?'],
				reg,self = this,
				i = 0;
			if(typeof val === 'string'){
				this.input.val(val);
				this.input.get(0).focus();
			}
			for(;i<keys.length;i++){
				keywords = keywords.split(keys[i]).join('\\'+keys[i]);
			}
			for(i = 0; i< searchSplitCode.length; i++){
				reg = eval('/'+searchSplitCode[i]+'/g');
				keywords = keywords.replace(reg,'');
			}
			clearTimeout(this.searchTimeout);
			if(keywords){
				this.searchTimeout = setTimeout(function(){self.searchList(keywords)},300);
			}else{
				this.searchTimeout = setTimeout(function(){self.setDefault()},300);
			}
		},
		unbindEvents: function(){
			this.input.off('focus',this.openBoxHandler);
			this.input.off('input propertychange',this.inputHandler);
		}
	};
	window.quickSelect = function(input,config,data){
		if(!input) return;
		var cfg = {}, i;
		config = config || {};
		data = data || null;
		for(i in config){
			if(config.hasOwnProperty(i)){
				cfg[i] = config[i];
			}
		}
		for(i in defaultConfig){
			if(defaultConfig.hasOwnProperty(i)){
				if(typeof cfg[i] === 'undefined'){
					cfg[i] = defaultConfig[i];
				}
			}
		}
		return new QS(input, cfg, data);
	};
})(PRselector,window);