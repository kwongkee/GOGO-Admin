/**
 * Created by baobaobao on 2016/3/31.
 */
$(document).ready(function () {
    var persons = $('.persons');
    var otherPages = $('.otherPages');

    setPageHeight(persons);
    setPageHeight(otherPages);
    setIndex();


    goback(persons,otherPages);

    addLiClick('m-myTravel');
    addLiClick('m-own');
    addLiClick('m-suggest');
    callus();
});



//设置页面高度
function setPageHeight(obj) {

    var height = document.body.scrollHeight;
    obj.css({
       'height':height+'px'
    });
}

//设置显示列表项 索引
var liPages = $('.otherPages>div');
function  setIndex() {
    var li = $('.g-aboutMe li');
   
    for(var i=0,len=li.length;i<len;i++){
        li[i].index = i;
    }
    for(i=0,len=liPages.length;i<len-1;i++){
        liPages[i+1].index = i;
    }
}

/* 添加列表点击效果 */
function addLiClick(objName) {
    var title = ['我的自由行','我的收藏','我的资料','我的优惠券','意见及反馈'];

    var pdiv = $('.'+objName);
    var li = pdiv.find('li');

    var textName = $('.m-title span');
    var persons = $('.persons');
    var otherPages = $('.otherPages');

    li.on('click',function () {
        var index = this.index;
        console.log('go');
        console.log('page'+index);

        textName.text(title[index]); //title设置
        $(liPages).removeClass('active');
        $(liPages[index+1]).addClass('active'); //内容盒子显示
        /* 这里从服务器获取数据显示 空白页(列表项)/列表项 */
        /* 未写*/

        setPageHeight(otherPages);   //设置整个页面高度
        otherPages.fadeIn(100);      //整体页面载入
        persons.fadeOut(100);        //整体页面淡出
    });
}

/* 返回点击效果 */
function goback(page1,page2){
    var backBtn = $('#goBack');
    backBtn.on('click',function () {
        console.log('back');
        page2.fadeOut(100);
        page1.fadeIn(100);
    });
}

/* 咨询事件 */
function callus() {
    var calBtn = $('#cal');
    var overlay = $('.f-overlay');
    var tel = $('.f-tel');
    var cancelBtn = $('#cancel');

    calBtn.on('click',function () {
        overlay.show();
        tel.fadeIn(200);
    });

    overlay.on('click',function () {
        tel.fadeOut(200);
        overlay.hide();
    });

    cancelBtn.on('click',function () {
        tel.fadeOut(200);
        overlay.hide();
    });

}

