(function ($, window) {
    var document = window.document, body = document.body;
    var dom = {
        html: [
			'<div class="shareBox">',
				'<div class="shareField">',
					'<div class="title">',
						'<div class="btns">',
							'<a href="javascript:;" class="btn close"><i></i></a>',
						'</div>',
						'<h2>分享到：</h2>',
					'</div>',
					'<div class="list">',
						'<ul>',
							'<li>',
								'<a href="#" class="weibo">',
									'<i></i>',
									'<span>微博</span>',
								'</a>',
							'</li>',
							'<li>',
								'<a href="#" class="tWeibo">',
									'<i></i>',
									'<span>腾讯微博</span>',
								'</a>',
							'</li>',
							'<li>',
								'<a href="#" class="renren">',
									'<i></i>',
									'<span>人人网</span>',
								'</a>',
							'</li>',
							'<li>',
								'<a href="#" class="qZone">',
									'<i></i>',
									'<span>QQ空间</span>',
								'</a>',
							'</li>',
							'<li>',
								'<a href="#" class="tieba">',
									'<i></i>',
									'<span>百度贴吧</span>',
								'</a>',
							'</li>',
							'<li>',
								'<a href="#" class="weixin">',
									'<i></i>',
									'<span>分享给朋友</span>',
								'</a>',
							'</li>',
						'</ul>',
					'</div>',
				'</div>',
				'<div class="weixinShareField">',
					'<div class="info">',
						'<i><svg><use xlink:href="#icon_shareArrow_1"/></svg></i>',
						'<p>请点击右上角<br>将它发送到指定朋友<br>或分享到朋友圈</p>',
					'</div>',
					'<div class="btns">',
						'<a href="#" class="btn close">',
							'<span></span>',
							'<i></i>',
						'</a>',
					'</div>',
				'</div>',
			'</div>'
        ].join(''),
        seletor: {
            totalClose: '.shareField .close',
            weibo: '.weibo',
            tWeibo: '.tWeibo',
            renren: '.renren',
            qZone: '.qZone',
            tieba: '.tieba',
            weixin: '.weixin',
            weixinShare: '.weixinShareField',
            weixinClose: '.weixinShareField .close'
        },
        class: {
            weinxinOpen: 'show'
        }
    },
	linkRule = {
	    weibo: "javascript:sharebox.gotoShareLink('http://service.weibo.com/share/mobile.php?title={title}{description}&url={url}&source=bookmark&pic={img}&ralateUid=&sudaref={origin}','weibo');",
	    tWeibo: "javascript:sharebox.gotoShareLink('http://share.v.t.qq.com/index.php?c=share&a=index&title={title}{description}&url={url}&pic={img}','tWeibo');",
	    renren: "javascript:sharebox.gotoShareLink('http://widget.renren.com/dialog/share?resourceUrl={url}&srcUrl={url}&title={title}&pic={img}&description={description}','renren');",
	    qZone: "javascript:sharebox.gotoShareLink('http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url={url}&showcount=0&summary={description}&title={title}&pics={img}&pics={img}','qZone');",
	    tieba: "javascript:sharebox.gotoShareLink('http://tieba.baidu.com/f/commit/share/openShareApi?title={title}&desc={description}&comment=&pic={img}&url={url}','tieba');",
	    weixin: "javascript:sharebox.openWeixin();"
	},
	isShow = false,
	isWeixinShow = false,
	emptyFn = function () { },
	defaultConfig = {
	    url: window.location.href,
	    title: document.title,
	    origin: window.location.origin || '',
	    description: $('meta[name="description"]').attr('content') || '',
	    img: '',
	    success: emptyFn,
	    cancel: emptyFn,

	    //下面跟微信AK有关
	    nowUrl: encodeURIComponent(window.location.href),
	    wxUrl: 'http://res.wx.qq.com/open/js/jweixin-1.0.0.js',
	    weixinCode: 'http://kdrelay.companycn.net/api/relay.ashx?mt=getSignatureJsonP&appid=wx50745c48b4621970&callbackparam={callback}&url={nowUrl}',
	    weixinCallback: 'weixinCodeCallback'
	},
	SB = function (config) {
	    this.config = {};
	    for (var i in defaultConfig) {
	        if (typeof config[i] === 'undefined') {
	            this.config[i] = defaultConfig[i];
	        } else {
	            this.config[i] = config[i];
	        }
	    }
	    this.config.url = encodeURIComponent(this.config.url);
	    this.isWeixin = this.setWX();
	}
    SB.prototype = {
        setWX: function () {
            var ua = navigator.userAgent.toLowerCase();
            if (ua.match(/MicroMessenger/i) != "micromessenger") {
                return false;
            }

            var script = document.createElement('script'),
				_this = this;


            script.onerror = function () {
                _this.isWeixin = false;
                script.onerror = null;
                script.onload = null;
                script.remove();
            }

            script.onload = function () {
                script.onerror = null;
                script.onload = null;
                _this.setWeixin();
            }

            body.appendChild(script);
            script.src = this.config.wxUrl;
            return true;
        },
        setWeixin: function () {
            var callbackName = this.config.weixinCallback,
				link = this.config.weixinCode,
				nowUrl = this.config.nowUrl,
				script = document.createElement('script'),
				_this = this;

            script.onerror = function () {
                _this.isWeixin = false;
                script.onerror = null;
                script.remove();
                delete window[callbackName];
            }

            window[callbackName] = function (data) {
                delete window[callbackName];

                wx.config({
                    debug: false,
                    appId: 'wx50745c48b4621970',
                    timestamp: data.data.stramp,
                    nonceStr: data.data.nonceStr,
                    signature: data.data.signature,
                    jsApiList: ["onMenuShareTimeline", "onMenuShareAppMessage", "onMenuShareQQ", "onMenuShareWeibo", "onMenuShareQZone"]
                });

                _this.setWeixinShare();
            }

            body.appendChild(script);
            script.src = link.replace('{callback}', callbackName).replace('{nowUrl}', nowUrl);
        },
        setWeixinShare: function () {
            var success = (typeof this.config.success === 'function') ? this.config.success : (typeof this.config.success.weixin === 'function') ? this.config.success.weixin : emptyFn,
				cancel = (typeof this.config.cancel === 'function') ? this.config.cancel : (typeof this.config.cancel.weixin === 'function') ? this.config.cancel.weixin : emptyFn,
				pengyouquanSuccess = (typeof this.config.success === 'function') ? this.config.success : (typeof this.config.success.pengyouquan === 'function') ? this.config.success.pengyouquan : success,
				pengyouquanCancel = (typeof this.config.cancel === 'function') ? this.config.cancel : (typeof this.config.cancel.pengyouquan === 'function') ? this.config.cancel.pengyouquan : cancel,
				_this = this,
				title = _this.config.title,
				link = decodeURIComponent(_this.config.url),
				summary = _this.config.description,
				shareLogo = _this.config.logo || _this.config.img;

            wx.ready(function () {
                wx.onMenuShareTimeline({
                    title: title,
                    link: link,
                    imgUrl: shareLogo,
                    success: function () {
                        success();
                    },
                    cancel: function () {
                        cancel();
                    }
                });

                wx.onMenuShareAppMessage({
                    title: title,
                    desc: summary,
                    link: link,
                    imgUrl: shareLogo,
                    success: function () {
                        success();
                    },
                    cancel: function () {
                        cancel();
                    }
                });
            });
        },
        openBox: function () {
            this.setDom();
            this.setLink();
            this.hideWeixin();
            this.bindEvents();
            isShow = true;
        },
        setDom: function () {
            this.dom = $(dom.html + '');
            this.dom.appendTo(body);
        },
        setLink: function () {
            var i, j, reg, rule;
            for (i in linkRule) {
                rule = linkRule[i] + '';
                for (j in this.config) {
                    reg = eval('/{' + j + '}/g');
                    rule = rule.replace(reg, encodeURIComponent(this.config[j]));
                }
                this.dom.find(dom.seletor[i]).attr('href', rule);
            }
        },
        gotoShareLink: function (url, way) {
            var success = (typeof this.config.success === 'function') ? this.config.success : (typeof this.config.success[way] === 'function') ? this.config.success[way] : emptyFn;
            success(way);
            window.location.href = url;
        },
        hideWeixin: function () {
            if (!this.isWeixin) {
                this.dom.find(dom.seletor.weixin).hide();
            }
        },
        bindEvents: function () {
            var _this = this;
            this.dom.find(dom.seletor.totalClose).on('click', function () {
                _this.closeAll();
            });
            this.dom.find(dom.seletor.weixinClose).on('touchend click', function () {
                _this.closeWeixin();
            });
        },
        unbindEvents: function () {
            this.dom.find(dom.seletor.totalClose).off();
            this.dom.find(dom.seletor.weixinClose).off();
        },
        openWeixin: function () {
            if (!isWeixinShow) {
                this.dom.find(dom.seletor.weixinShare).addClass(dom.class.weinxinOpen);
                isWeixinShow = true;
            }
        },
        closeWeixin: function () {
            if (isWeixinShow) {
                this.dom.find(dom.seletor.weixinShare).removeClass(dom.class.weinxinOpen);
                isWeixinShow = false;
            }
        },
        closeAll: function () {
            this.unbindEvents();
            this.dom.remove();
            delete this.dom;
            isShow = false;
        }
    }
    var nowSb,
	sharebox = window.sharebox = function (config) {
			    if (nowSb) return false;
	    config = config || {};
	    if (typeof config === 'string') {
	        if (config.indexOf('http://') == 0) {
	            config = {
	                url: config
	            }
	        } else {
	            config = {
	                title: config
	            }
	        }
	    }
	    nowSb = new SB(config);
	    return nowSb;
	}
    sharebox.openWeixin = function () {
        if (isShow && !isWeixinShow) {
            nowSb.openWeixin();
        }
    }
    sharebox.gotoShareLink = function (url, way) {
        if (isShow) {
            nowSb.gotoShareLink(url, way);
        }
    }
    sharebox.openBox = function () {
        if (!isShow) {
            nowSb.openBox();
        }
    }

})(PRselector, window)