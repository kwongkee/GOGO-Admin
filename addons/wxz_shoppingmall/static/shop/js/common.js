
var vipUrl = "http://vip.companycn.net";
var passport_url = "http://passport.companycn.net";

//当前url地址
urlAddress = (function (script) {
    var l = script.length;
    for (var i = 0; i < l; i++) {
        me = !!document.querySelector ? script[i].src : script[i].getAttribute('src', 4);
        if (me.substr(me.lastIndexOf('/')).indexOf('menu_hover') !== -1) {
            break;
        }
    }
    return me;
})(document.getElementsByTagName('script'))

//获取当前域名地址
currentDomain = function () {
    if (urlAddress) {
        var _kdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
        var temp = urlAddress.substr(me.indexOf('/') + 2);
        return _kdhmProtocol + temp.substr(0, temp.indexOf('/'));
    } else {
        return "";
    }
}

//获取url地址的字符串参数
function GetQueryString(key) {
    var reg = new RegExp("(^|&)" + key + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]); return "";
}

//获取url地址的数字参数
function GetQueryInt(key, defValue) {
    var reg = new RegExp("(^|&)" + key + "=([^&]*)(&|$)");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        var v = parseInt(unescape(r[2]));
        if (!v) {
            if (typeof (defValue) == "undefined") {
                defValue = 0;
            }
            return defValue
        }
        return v;
    } else {
        if (arguments.length == 2)
            return defValue;
        return 0;
    }
}

function GoToMemberLogin(returnUrl) {

    location.href = "/jumpindex?tp=login&returnUrl=" + escape(returnUrl);
}

//返回会员中心
function GoToMemberCenter() {
    location.href = "/jumpindex";
}


//返回消息中心
function GoToMyMessage() {
    location.href = vipUrl + "/titlenav/usermessagelist";
}

//返回首页
function GoToHome(tp) {
    if (arguments.length == 1) {
        location.href = "/jumpindex?tp=index";
    } else {
        location.href = "../index.html";
    }

}

/** 
* 对日期进行格式化， 
* @param date 要格式化的日期 
* @param format 进行格式化的模式字符串
*     支持的模式字母有： 
*     y:年, 
*     M:年中的月份(1-12), 
*     d:月份中的天(1-31), 
*     h:小时(0-23), 
*     m:分(0-59), 
*     s:秒(0-59), 
*     S:毫秒(0-999),
*     q:季度(1-4)
* @return String
* @author yanis.wang
* @see	http://yaniswang.com/frontend/2013/02/16/dateformat-performance/
*/
template.helper('dateFormat', function (date, format) {


    if (date == null || date == "") {
        return "";
    }
    date = date.replace("T", " ").replace(/-/g, "/");
    //删除毫秒   new Date(date)会报错
    if (date.indexOf('.') > -1) {
        date = date.substr(0, date.indexOf('.'))
    }

    date = new Date(date);

    var map = {
        "M": date.getMonth() + 1, //月份 
        "d": date.getDate(), //日 
        "h": date.getHours(), //小时 
        "m": date.getMinutes(), //分 
        "s": date.getSeconds() //秒 

    };

    format = format.replace(/([yMdhms])+/g, function (all, t) {
        var v = map[t];
        if (v !== undefined) {
            if (all.length > 1) {
                v = '0' + v;
                v = v.substr(v.length - 2);
            }
            return v;
        }
        else if (t === 'y') {
            return (date.getFullYear() + '').substr(4 - all.length);
        }
        return all;
    });
    return format;
});
template.helper('avgRating', function (avg_rating) {

    var result = avg_rating;
    if (avg_rating == 1.5) {
        result = "1_5";
    } else if (avg_rating == 2.5) {
        result = "2_5";
    } else if (avg_rating == 3.5) {
        result = "3_5";
    } else if (avg_rating == 4.5) {
        result = "4_5";
    }

    return result;
});//雅诗阁Js(zp 2016-03-21)//返回会员中心function GoToAscottMemberCenter() {
    var location_code = $("#hid_location_code").val();
    location.href = vipUrl + "/userCenter?location_code=" + location_code + "&ascott=" + location_code;
}