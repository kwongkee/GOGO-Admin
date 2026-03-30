(function(doc, win) {
    var docEl = doc.documentElement,
        resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
        recalc = function() {
            var clientWidth = docEl.clientWidth < 768 ? docEl.clientWidth : 768;
            if (!clientWidth) return;
            docEl.style.fontSize = 100 * (clientWidth / 375) + 'px';
            fontSize = 100 * (clientWidth / 375);
        };
    recalc();
    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
})
(document, window);
function timer(opj){
    $(opj).find('ul').animate({
        marginTop : "-0.4rem"
    },500,function(){
        $(this).css({marginTop : "0rem"}).find("li:first").appendTo(this);
    })
};
$(function(){
    var height = $(window).height();
    $(".wx_box").css({"min-height":height});
    $(".notice em").click(function(){
        $(this).parents(".notice").remove();
    });
    var num = $('.notice').find('li').length;
    if(num > 1){
        var time=setInterval('timer(".notice")',3500);
        $(".notice ul li a").on('touchstart',function(event){
            clearInterval(time);
        });
        $(".notice ul li a").on('touchend',function(event){
            time = setInterval('timer(".notice")',3500);
        });
    };

});

