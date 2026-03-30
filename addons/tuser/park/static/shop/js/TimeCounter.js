// JavaScript Document
;(function (factory) {
	if (typeof define === "function" && define.amd) {
		// AMD模式
		define([ "PRselector" ], factory);
	} else {
		// 全局模式
		window.countdown = factory(PRselector);
	}
}(function(){
	var tcGroup = [],
	tcDomGroup = [],
	tcTimeout = null,
	tcStart = false,
	
	tcPrecision = {
		second:60,
		minute:60,
		hour:24,
		day:9999
	},
	
	defaultConfig = {
		precision : 'day'
	},
	
	tcReadConfig = function(config){
		var i;
		if(typeof config !== 'object'){
			config = {time:config};
		}
		for(i in defaultConfig){
			if(typeof config[i] === 'undefined'){
				config[i] = defaultConfig[i];
			}
		}
		return config;
	},
	
	
	tcReadValue = function(){
		var i = 0, nowTime = new Date().getTime();
		clearTimeout(tcTimeout);
		for(; i<tcGroup.length; i++){
			tcGroup[i].readValue(nowTime);
		}
		tcTimeout = setTimeout(tcReadValue,1000);
	},
	
	
	TC = function(config,callback,finishHandler){
		this.time = config.time;
		if(typeof tcPrecision[config.precision]!== 'undefined'){
			this.precision = config.precision;
		}else{
			this.precision = defaultConfig.precision;
		}
		this.callback = callback;
		this.finish = finishHandler;
		this.setup();
	};
	
	TC.prototype = {
		setup: function(){
			this.time = this.readDate(this.time).getTime();
			tcGroup.push(this);
			if(!tcStart){
				tcStart = true;
				tcReadValue();
			}
		},
		readDate: function(str){
			var rDate,total, date, time;
			if(typeof str === 'number' || parseInt(str)+'' == str.replace(/\s/g,'')){
				rDate = new Date(parseInt(str)+(new Date()).getTime());
			}else{
				rDate = new Date();
				total = str.split(' ');
				date = total[0].split('-');
				time = total[1] && total[1].split(':') || [0,0,0,0];

				rDate.setUTCFullYear(date[0], date[1] - 1, date[2]);
				rDate.setUTCHours(time[0], (time[1] || 0), (time[2] || 0), (time[3] || 0));
			}
			return rDate;
		},
		readValue: function(nowTime){
			var count = this.time - nowTime,
				countValue = {},
				returnValue = {},i,value;
			if(count<=0){
				count = 0;
			}
			
			countValue.second = Math.max(Math.floor(count/1000),0);
			countValue.minute = Math.floor(countValue.second/60);
			countValue.hour = Math.floor(countValue.minute/60);
			countValue.day = Math.floor(countValue.hour/24);
			
			for(i in tcPrecision){
				if(i == this.precision){
					returnValue[i] = countValue[i]<10?'0'+countValue[i]:''+countValue[i];
					break;
				}else{
					value = countValue[i] % tcPrecision[i];
					returnValue[i] = value<10?'0'+value:''+value;
				}
			}
			
			this.callback(returnValue);
			if(count <= 0){
				this.finish();
				this.destory();
			}
		},
		destory: function(){
			var i = 0, j;
			for(; i< tcGroup.length; i++){
				if(tcGroup[i] === this){
					tcGroup.splice(i,1);
					break;
				}
			}
			for(j in this){
				delete this[j];
			}

		}
	};
	
	var timeCount = function(config,callback,finishCallback){
		if(!config || !callback) return;
		finishCallback = finishCallback || function(){};
		config = tcReadConfig(config);
		return new TC(config,callback,finishCallback)
	};
	var setValue = function(obj,value){
		obj.each(function(){
			var showNum, length = value.length;
			if($(this).attr('data-showcountdown')){
				showNum = parseInt($(this).attr('data-showcountdown'));
				if(showNum < 0){
					showNum = length + showNum;
				}
				$(this).html(value[showNum]);
			}else{
				$(this).html(value);
			}
		})
	};

	var setCountDown = function(obj,selectors){
		obj = $(obj);
		var countdown = obj.attr('data-time'),
			callback = obj.attr('data-timeOver') || '',
			finishCallback = function(){
				this.timeCount = null;
				eval(callback);
			},
			finishCB = finishCallback.bind(obj.get(0));
		obj.timeCount = timeCount(countdown,function(value){
			var i;
			for(i in selectors){
				if(typeof value[i]!=='undefined'){
					setValue(obj.find(selectors[i]),value[i]);
				}
			}
		},finishCB);
	};

	return function(selectors){
		$(selectors.main).each(function(){
			if(tcDomGroup.indexOf(this)== -1){
				tcDomGroup.push(this);
				setCountDown(this,selectors);
			}
		})
	};

}));