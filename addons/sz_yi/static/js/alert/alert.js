var zIndex = 1000;
var jqueryAlert = function(opts){
    // 璁剧疆榛樿鍙傛暟
    var opt = {
        'style'        : 'wap', //绉诲姩绔拰PC绔�
        'title'        : '',    //鏍囬
        'content'      : '',	//鍐呭
        'contentTextAlign' : 'center', //鍐呭瀵归綈鏂瑰紡
        'width'        : 'auto', //瀹藉害
        'height'       : 'auto', //楂樺害
        'minWidth'     : '0', //鏈€灏忓搴�
        "className"    : '', //娣诲姞绫诲悕
        'position'     : 'fixed', //瀹氫綅鏂瑰紡
        'animateType'  : 'scale',
        'modal'        : false, //鏄惁瀛樺湪钂欏眰
        'isModalClose' : false, //鐐瑰嚮钂欏眰鏄惁鍏抽棴
        'bodyScroll'   : false, //鏄惁鍏抽棴body鐨勬粴鍔ㄦ潯
        'closeTime'    : 3000, //褰撴病鏈夋寜閽椂鍏抽棴鏃堕棿
        "buttons"      : {}, //鎸夐挳瀵硅薄</pre>
    }

    // 鍙傛暟鍚堝苟
    var option = $.extend({},opt,opts);

    var dialog = {

    }

    dialog.time = 450;//鍔ㄧ敾鍏抽棴鏃堕棿
    dialog.init = function(){
        dialog.framework();
    }

    // 浜嬩欢澶勭悊
    var isHaveTouch = "ontouchend" in document ? true : false;
    if(isHaveTouch){
        dialog.event = 'touchstart';
    }else{
        dialog.event = 'click';
    }

    var $modal = $("<div class='alert-modal'>")
    var $container = $("<div class='alert-container animated'>");
    var $title = $("<div class='alert-title'>"+option.title+"</div>");
    var $content = $("<div class='alert-content'>");
    var $buttonBox = $("<div class='alert-btn-box'>");
    var $closeBtn = $("<div class='alert-btn-close'>脳</div>");

    if(option.content[0].nodeType == 1){
        var $newContent = option.content.clone();
        $content.append($newContent)
    }else{
        $content.html(option.content);
    }

    dialog.framework = function(){

        dialog.buttons = [];
        for(var key in option.buttons){
            dialog.buttons.push(key);
        }
        dialog.buttonsLength = dialog.buttons.length;

        $container.append($title)
            .append($content);

        if(option.style == 'pc'){
            $container.append($closeBtn).addClass('pcAlert');
        }

        if(option.modal || option.modal == 'true'){
            $('body').append($modal)
            option.bodyScroll && $('body').css('overflow','hidden');
        }
        $('body').append($container)

        // 璁剧疆鍐呭鐨勫榻愭柟寮�
        $content.css({
            'text-align' : option.contentTextAlign
        })

        if(parseInt(option.minWidth) > parseInt($container.css('width'))){
            option.width = option.minWidth;
        }

        $modal.css('position',option.position);
        $modal.css('z-index',zIndex);

        ++zIndex;

        if(option.position == 'fixed'){
            $container.css({
                'position' : option.position,
                'left'     : '50%',
                'top'      : '50%',
                'z-index'  : zIndex,
            })
        }
        if(option.position == 'absolute'){
            $container.css({
                'position' : option.position,
                'left'     : $(window).width()/2,
                'top'      : $(window).height()/2 + $(window).scrollTop(),
                'z-index'  : zIndex,
            })
        }

        $container.css('width',option.width);
        $container.css('height',option.height);

        if(option.width == 'auto'){
            $container.css('width',$container[0].clientWidth + 10);
        }

        if(parseInt($(window).height()) <=  parseInt($container.css('height'))){
            $container.css('height',$(window).height());
        }

        // 璁剧疆class
        (!!option.className) && $container.addClass(option.className);

        // 璁剧疆button鍐呭
        for(var key in option.buttons){

            var $button = $("<p class='alert-btn-p'>"+ key +"</p>");
            if(option.style != 'pc'){
                $button.css({
                    'width' : Math.floor(($container[0].clientWidth - 3) / dialog.buttonsLength),
                })
            }
            //缁戝畾鐐瑰嚮鍚庣殑浜嬩欢
            $button.bind(dialog.event,option.buttons[key]);
            $buttonBox.append($button);

        }

        if(dialog.buttonsLength > 0){
            $container.append($buttonBox);
            $content.css('padding-bottom','46px');
        }

        if(option.title != ''){
            $content.css('padding-top','42px');
        }

        if(dialog.buttonsLength <= 0 && option.title == ''){
            $container.addClass('alert-container-black');
        }

        // 璁剧疆瀹氫綅
        $container.css({
            'margin-left' : -parseInt($container.css('width'))/2,
            'margin-top' : -parseInt($container.css('height'))/2,
        });

        if(option.animateType == 'scale'){
            $container.addClass('bounceIn');
        }

        if(option.animateType == 'linear'){
            $container.addClass('linearTop');
        }

        isSelfClose();

    };

    // 鍒ゆ柇鏄惁婊¤冻鑷姩鍏抽棴鐨勬潯浠�
    function isSelfClose(){
        if(dialog.buttonsLength <= 0 && option.style != 'pc'){
            setTimeout(function(){
                $container.fadeOut(300);
                $modal.fadeOut(300);
                option.bodyScroll && $('body').css('overflow','auto');
            },option.closeTime)
        }
    }

    dialog.toggleAnimate = function(){
        if(option.animateType == 'scale'){
            return $container.removeClass('bounceIn').addClass('bounceOut');
        }else if(option.animateType == 'linear'){
            return $container.removeClass('linearTop').addClass('linearBottom');
        }else{
            return $container;
        }
    }

    dialog.close = function(){
        dialog.toggleAnimate().fadeOut(dialog.time);
        $modal.fadeOut(dialog.time);
        option.bodyScroll && $('body').css('overflow','auto');
    };

    option.style == 'pc' && $closeBtn.bind(dialog.event,dialog.close);
    option.isModalClose && $modal.bind(dialog.event,dialog.close);

    dialog.destroy = function(){
        dialog.toggleAnimate().fadeOut(dialog.time);
        setTimeout(function(){
            $container.remove();
            $modal.remove();
            option.bodyScroll && $('body').css('overflow','auto');
        },dialog.time)
    }
    dialog.show = function(){

        $modal.css('z-index',zIndex);

        ++zIndex;

        $container.css({
            'z-index'  : zIndex,
        })

        if(option.animateType == 'scale'){
            $container.fadeIn().removeClass('bounceOut').addClass('bounceIn');
        }else if(option.animateType == 'linear'){
            $container.fadeIn().removeClass('linearBottom').addClass('linearTop');
        }else{
            $container.fadeIn()
        }

        if(option.position == 'absolute'){
            $container.css({
                'top'      : $(window).height()/2 + $(window).scrollTop(),
            })
        }

        $modal.fadeIn();
        option.bodyScroll && option.modal && $('body').css('overflow','hidden');
        isSelfClose();
    }

    dialog.init();

    return dialog;

}