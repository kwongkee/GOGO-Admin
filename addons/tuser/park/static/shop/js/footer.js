
        var vip_url = 'http://vip.capitaland.com.cn';

var is_nomall = '<a href="../map/traffic">如何到达</a>';
if (location.href.indexOf("mall_list") > -1) {
    is_nomall = "";
}





var navConfig = function () {
    (function ($, window) {
        var resizeTimeout = null,
                browserWidth = 0,
                nav = $('#nav .menuField'),
                navBtn = $('#nav .menuField li'),
                minWidth = 100 * SetSize.getScreenRatio(),
                smallLinkWidth = 48 * SetSize.getScreenRatio(),
                hideDom = function () {
                    var linkCount = navBtn.length,
                            smallLinkCount = Math.ceil((linkCount * minWidth - browserWidth) / (minWidth - smallLinkWidth)),
                            i = 0;
                    navBtn.removeClass('single');
                    for (; i < smallLinkCount; i++) {
                        if (i % 2 == 0) {
                            navBtn.eq(linkCount - 1 - i / 2).addClass('single');
                        } else {
                            navBtn.eq((i - 1) / 2).addClass('single');
                        }
                    }
                },
                navConfigHandler = function () {
                    var tempWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
                    if (navBtn.length < 0 || tempWidth == browserWidth)
                        return;
                    browserWidth = tempWidth;
                    nav.hide();
                    resizeTimeout && clearTimeout(resizeTimeout);
                    resizeTimeout = setTimeout(function () {
                        hideDom();
                        nav.show();
                    }, 200);
                };
        if (navBtn.length > 0) {
            window.addEventListener('resize', navConfigHandler, false);
        }
        navConfigHandler();
    })(PRselector, window);
}


//加载头部菜单
function loadTopNavAndFooter(is_show_footer, is_like, like_num, back_url, home_url, _name, _img, _remark, is_appen_name) {


    var home = "../index.html";
    if (arguments.length >= 5 && home_url != "") {
        home = home_url;
    }

    var back = "javascript:history.go(-1);";
    if (arguments.length >= 4 && back_url != "") {
        back = back_url;
    }
    if (history.length <= 1) {
        back = home;
    }

    var isAct = location.href.indexOf("/sale/") > -1 || location.href.indexOf("activity") > -1;

    var top_nav_html = '<div class="linkField" >'
            + ' <ul>'
            + '<li>'
            + ' <a href="' + home + '"';
    if (!isAct)
        top_nav_html += ' class="current"';

    top_nav_html += '>'
            + ' <i><svg><use xlink:href = "#icon_index_1"/></svg></i>'
            + '<span>逛商场</span>'
            + ' </a>'
            + ' </li>'
            + '<li>'
            + ' <a href="../activity/mall_activity_list"';
    if (isAct)
        top_nav_html += ' " class="current"';

    top_nav_html += ' >'
            + '<i><svg><use xlink:href = "#icon_star_1"/></svg></i>'
            + '<span>活动</span>'
            + '</a>'
            + ' </li>';

    if (is_like) {
        top_nav_html += '<li>'
                + '<a href="javascript:like();">'
                + '<i><svg><use xlink:href="#icon_like_1"/></svg></i>'
                + ' <span id="nav_like_num">' + like_num + '</span>'
                + '</a>'
                + '</li>'
    }
    top_nav_html += '<li>'
            + '<a href="/integral_mall/integral_index">'
            + '<i><svg><use xlink:href = "#icon_gift_1"/></svg></i>'
            + '<span>积分商城</span>'
            + ' </a>'
            + ' </li>'
            + ' <li>'
            + '<a href="' + vip_url + '/userCenter">'
            + '<i><svg><use xlink:href = "#icon_user_2"/></svg></i>'
            + '<span>我</span>'
            + '</a>'
            + '</li>'
            + '</ul>'
            + '  </div>';


    var footer_html = ' <div class="linkField">'
            + '{{ if noLogin}}'
            + '<div class="link1">'
            + ' <a href="{{login_url}}">登录</a><a href="{{reg_url}}">注册</a>'
            + '</div>'
            + '{{/if}}'
            + '<div class="link2">'
            + is_nomall + ' <a href="javascript:window.scrollTo(0,0);">回到顶部</a>'
            + '</div>'
            + ' </div>'
            + ' <div class="copyrightField">'
            + '<p>Copyright @ 2015 CapitaLand China. 版权所有</p>'
            + '</div>';


    if ($("#header").length == 0)
    {
        var headerControl = '<header id="header">' +
                '<div class="titleField">' +
                '	<h2>' +
                document.title +
                '</h2>' +
                '</div>' +
                '<div class="btnField">' +
                '	<a href="javascript:history.go(-1);" class="btn back"><i></i></a>' +
                '	<a href="javascript:location.reload();" class="btn"><i><svg><use xlink:href = "#icon_reload_1"/></svg></i></a>' +
                '</div>' +
                '</header>';
        var sec = $("#main");
        $(headerControl).insertBefore(sec);
    }

//    $.ajax({
//        type: "POST",
//        url: "/api/artdata?tp=loginstatus",
//        timeout: 60000,
//        dataType: "json",
//        beforeSend: function () {
//        },
//        success: function (data) {
//            console.log(data);
//            if (is_show_footer) {
//                var render = template.compile(footer_html);
//                var html = render(data);
//                $("#footer").html(html);
//            }
//
//            if (data.mall_status==0) {
//                //开启预览浮动
//                notification();
//            }
//            var mall_id=data.mall_id;
//            if(mall_id==null || typeof(mall_id) == "undefined")
//                mall_id="";
//            data.mall_img = "http://file.capitaland.com.cn/webupload/share/share" + (mall_id) + ".jpg";
//            
//            if (_name != "" && typeof (_name) != "undefined") {
//                if (typeof (is_appen_name) == "undefined" || is_appen_name)
//                    data.mall_name += "-" + _name;
//                else 
//                    data.mall_name = _name;
//            }
//                
//
//            if (_img != "" && typeof (_img) != "undefined")
//                data.mall_img = _img;
//            if (typeof (_remark) != "undefined")
//                data.mall_remark = _remark;
//
//            var defaultConfig = {
//                url: window.location.href,
//                title: data.mall_name,
//                origin: window.location.origin || '',
//                description: data.mall_remark,
//                img: data.mall_img,
//                success: function () { },											//分享成功调用的函数
//                cancel: function () { }											//分享失败调用的函数，仅用于微信
//            }
//            sharebox(defaultConfig);
//
//
//            $("#nav").html(top_nav_html);
//            $("#a_share").click(function () {
//                sharebox.openBox();
//                if (CeilingIsGod)
//                    CeilingIsGod.nothings("share", _obj_id, getVipMallid());
//            })
//            navConfig();
//        },
//        error: function (data, status, e) {
//            $("#nav").html(top_nav_html);
//            $("#a_share").click(function () {
//
//                sharebox.openBox();
//            })
//            navConfig();
//        }
//    });


}








