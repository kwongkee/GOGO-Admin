var _staDomain = 'http://vip.capitaland.com.cn';


urlParameters = (function (script) {
    var l = script.length;
    for (var i = 0; i < l; i++) {
        me = !!document.querySelector ? script[i].src : script[i].getAttribute('src', 4);
        if (me.substr(me.lastIndexOf('/')).indexOf('menu_hover') !== -1) {
            break;
        }
    }
    return me.split('?')[1];
})(document.getElementsByTagName('script'))
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

Protocol = function () {
    if (urlAddress) {
        var _kdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
        var temp = urlAddress.substr(me.indexOf('/') + 2);
        return _kdhmProtocol + temp.substr(0, temp.indexOf('/'));
    } else {
        return "";
    }
}


GetJsParameters = function (name) {
    if (urlParameters) {
        var parame = urlParameters.split('&'), i = 0, l = parame.length, arr;
        for (var i = 0 ; i < l; i++) {
            arr = parame[i].split('=');
            if (name === arr[0]) {
                return arr[1];
            }
        }
    }
    return "";
}



GetUrlParameters = function (name,url) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    if (!url)
        url =window.location.search.substr(1)
    var r = url.match(reg);
    if (r != null) return unescape(r[2]); return "";
}

//GetUrlParameters = function (name,url) {
//    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
//    //reg.lastIndex()
//    alert(url);
//    var r = url.match(reg);
//    if (r != null) return unescape(r[2]); return "";
//}

function getVipMallid() {
    var vip_mall_id_name = "vip_mall_id";
    var vip_mall_id = GetUrlParameters(vip_mall_id_name);
    if (!vip_mall_id)
        vip_mall_id = GetUrlParameters(vip_mall_id_name, decodeURIComponent(GetUrlParameters("returnUrl")));
    return vip_mall_id;
}

function getMallTag()
{
    return location.href.split("/", 4)[3];
}

var CeilingIsGod = {
    name: GetJsParameters('n'),
    ac: GetJsParameters('ac'),
    type: GetJsParameters('t'),
    url: window.location.href,

    refferUrl:document.referrer.replace("&", "|"),

    op: '&',
    po: '=',

    title: window.document.title,

    insertlog: function (_name, _value) {
        return (this.op + _name + this.po + _value);
    },
    onerror: function (actionT, objID, mall_tag, seacrh_key, seconds, hit_count) {

        if (actionT)
            this.ac = actionT;

        var error = _staDomain + "/ashx/statistics.ashx?ac=" + this.ac;
        error += this.insertlog("tpID", encodeURI(this.type));
        error += this.insertlog("pName", encodeURI(this.name || this.title));
        error += this.insertlog("url", encodeURI(this.url));
        error += this.insertlog("objID", encodeURI(objID));
        error += this.insertlog("refferUrl", encodeURI(this.refferUrl.replace("&", "|")));
        error += this.insertlog("mallTag", encodeURI(mall_tag));
        error += this.insertlog("s_name", encodeURI(seacrh_key));
        error += this.insertlog("s_seconds", encodeURI(seconds));
        error += this.insertlog("hit_count", encodeURI(hit_count));        
        return error;

    },
    nothings: function (actionT, objID, mall_tag, seacrh_key, seconds,hit_count) {
        var bgnsm = false;
        if (window.XMLHttpRequest) {
            bgnsm = new XMLHttpRequest();
            if (bgnsm.overrideMimeType) {
                bgnsm.overrideMimeType("text/xml");
            }
        } else if (window.ActiveXObject) {
            try {
                bgnsm = new ActiveXObject("Msxml2.XMLHTTP");
            }
            catch (e) {
                try {
                    bgnsm = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {
                }
            }
        }
        if (!bgnsm) {
            console.log("can not create XMLHttpRequest object.");
            return false;
        }
        bgnsm.open("GET", this.onerror(actionT, objID, mall_tag, seacrh_key, seconds, hit_count));
        bgnsm.withCredentials = true;
        bgnsm.send();
        bgnsm.onreadystatechange = function () {
            if (bgnsm.readyState == 4 && bgnsm.status == 200) {
//                console.log(bgnsm.response);
            } else {
//                console.log(bgnsm.response);
            }
        }
    }
}

//CeilingIsGod.nothings();






