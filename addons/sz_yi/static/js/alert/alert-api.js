$(function(){
    $('.alert-api-list').css('height',$(window).height());
    $(window).scroll(function(){

        if($(window).scrollTop() >= 224){
            $('.alert-api-list').css({
                'top' : $(window).scrollTop() - 224
            })
        }else{
            $('.alert-api-list').css({
                'top' : 0
            })
        }
    })

    $(document).delegate('.alert_list a','click',function(){
        $('.alert_list a').removeClass('alert-api-hover');
        $(this).addClass('alert-api-hover');
    })

    SyntaxHighlighter.all();

    var M = {

    }

    // 鎸夐挳涓€
    $(document).delegate(".alert-btn1",'click',function(){
        if(M.dialog1){
            return M.dialog1.show();
        }
        M.dialog1 = jqueryAlert({
            'content' : 'hello 绋嬪簭鍛�...',
            'closeTime' : 2000
        })
    })
    // 鎸夐挳浜�
    $(document).delegate(".alert-btn2",'click',function(){
        if(M.dialog2){
            return M.dialog2.show();
        }
        M.dialog2 = jqueryAlert({
            'content' : 'hello 绋� 搴� 鍛� ...',
            'modal'   : true,
            'buttons' :{
                '纭畾' : function(){
                    M.dialog2.close();
                }
            }
        })
    })
    // 鎸夐挳涓�
    $(document).delegate(".alert-btn3",'click',function(){
        if(M.dialog3){
            return M.dialog3.show();
        }
        M.dialog3 = jqueryAlert({
            'title'   : 'alertjs鎻愮ず',
            'content' : '娆㈣繋浣跨敤alertjs寮瑰眰 ...',
            'modal'   : true,
            'buttons' :{
                '纭畾' : function(){
                    M.dialog3.close();
                },
                '鎴戜笉鏄�' : function(){
                    if(M.dialog31){
                        return M.dialog31.show();
                    }
                    M.dialog31 = jqueryAlert({
                        'content' : '鎴戜笉鏄▼搴忓憳...'
                    })
                }
            }
        })
    })

    // 鎸夐挳鍥�
    $(document).delegate(".alert-btn4",'click',function(){
        if(M.dialog4){
            return M.dialog4.show();
        }
        M.dialog4 = jqueryAlert({
            'title'   : 'alertjs鎻愮ず',
            'content' : '娆㈣繋浣跨敤alertjs寮瑰眰 ...',
            'modal'   : true,
            'animateType' : '',
            'buttons' :{
                '纭畾' : function(){
                    M.dialog4.close();
                },
                '涓嶄娇鐢�' : function(){
                    if(M.dialog41){
                        return M.dialog41.show();
                    }
                    M.dialog41 = jqueryAlert({
                        'content' : '绁濇偍鎵惧埌鏇村ソ鐢ㄧ殑...'
                    })
                }
            }
        })
    })

    var alertContent = '<div class="protocol"><h3 align="center">鐢ㄦ埛娉ㄥ唽鍗忚</h3><p></p><h4>涓€銆佹€诲垯</h4>1.1  淇濆疂缃戠殑鎵€鏈夋潈鍜岃繍钀ユ潈褰掓繁鍦冲競姘稿叴鍏冪鎶€鏈夐檺鍏徃鎵€鏈夈€� <br>1.2  鐢ㄦ埛鍦ㄦ敞鍐屼箣鍓嶏紝搴斿綋浠旂粏闃呰鏈崗璁紝骞跺悓鎰忛伒瀹堟湰鍗忚鍚庢柟鍙垚涓烘敞鍐岀敤鎴枫€備竴鏃︽敞鍐屾垚鍔燂紝鍒欑敤鎴蜂笌淇濆疂缃戜箣闂磋嚜鍔ㄥ舰鎴愬崗璁叧绯伙紝鐢ㄦ埛搴斿綋鍙楁湰鍗忚鐨勭害鏉熴€傜敤鎴峰湪浣跨敤鐗规畩鐨勬湇鍔℃垨浜у搧鏃讹紝搴斿綋鍚屾剰鎺ュ彈鐩稿叧鍗忚鍚庢柟鑳戒娇鐢ㄣ€�  <br>1.3 鏈崗璁垯鍙敱淇濆疂缃戦殢鏃舵洿鏂帮紝鐢ㄦ埛搴斿綋鍙婃椂鍏虫敞骞跺悓鎰忔湰绔欎笉鎵挎媴閫氱煡涔夊姟銆傛湰绔欑殑閫氱煡銆佸叕鍛娿€佸０鏄庢垨鍏跺畠绫讳技鍐呭鏄湰鍗忚鐨勪竴閮ㄥ垎銆�<p></p> <p></p><h4>浜屻€佹湇鍔″唴瀹�</h4>2.1 淇濆疂缃戠殑鍏蜂綋鍐呭鐢辨湰绔欐牴鎹疄闄呮儏鍐垫彁渚涖€� <br>2.2 鏈珯浠呮彁渚涚浉鍏崇殑缃戠粶鏈嶅姟锛岄櫎姝や箣澶栦笌鐩稿叧缃戠粶鏈嶅姟鏈夊叧鐨勮澶�(濡備釜浜虹數鑴戙€佹墜鏈恒€佸強鍏朵粬涓庢帴鍏ヤ簰鑱旂綉鎴栫Щ鍔ㄧ綉鏈夊叧鐨勮缃�)鍙婃墍闇€鐨勮垂鐢�(濡備负鎺ュ叆浜掕仈缃戣€屾敮浠樼殑鐢佃瘽璐瑰強涓婄綉璐广€佷负浣跨敤绉诲姩缃戣€屾敮浠樼殑鎵嬫満璐�)鍧囧簲鐢辩敤鎴疯嚜琛岃礋鎷呫€俓 <p></p> <p> </p><h4>涓夈€佺敤鎴峰笎鍙�</h4>3.1 缁忔湰绔欐敞鍐岀郴缁熷畬鎴愭敞鍐岀▼搴忓苟閫氳繃韬唤璁よ瘉鐨勭敤鎴峰嵆鎴愪负姝ｅ紡鐢ㄦ埛锛屽彲浠ヨ幏寰楁湰绔欒瀹氱敤鎴锋墍搴斾韩鏈夌殑涓€鍒囨潈闄愶紱鏈粡璁よ瘉浠呬韩鏈夋湰绔欒瀹氱殑閮ㄥ垎浼氬憳鏉冮檺銆備繚瀹濈綉鏈夋潈瀵逛細鍛樼殑鏉冮檺璁捐杩涜鍙樻洿銆� <br>\3.2 鐢ㄦ埛鍙兘鎸夌収娉ㄥ唽瑕佹眰浣跨敤鐪熷疄濮撳悕锛屽強韬唤璇佸彿娉ㄥ唽銆傜敤鎴锋湁涔夊姟淇濊瘉瀵嗙爜鍜屽笎鍙风殑瀹夊叏锛岀敤鎴峰埄鐢ㄨ瀵嗙爜鍜屽笎鍙锋墍杩涜鐨勪竴鍒囨椿鍔ㄥ紩璧风殑浠讳綍鎹熷け鎴栨崯瀹筹紝鐢辩敤鎴疯嚜琛屾壙鎷呭叏閮ㄨ矗浠伙紝鏈珯涓嶆壙鎷呬换浣曡矗浠汇€傚鐢ㄦ埛鍙戠幇甯愬彿閬埌鏈巿鏉冪殑浣跨敤鎴栧彂鐢熷叾浠栦换浣曞畨鍏ㄩ棶棰橈紝搴旂珛鍗充慨鏀瑰笎鍙峰瘑鐮佸苟濡ュ杽淇濈锛屽鏈夊繀瑕侊紝璇烽€氱煡鏈珯銆傚洜榛戝琛屼负鎴栫敤鎴风殑淇濈鐤忓拷瀵艰嚧甯愬彿闈炴硶浣跨敤锛屾湰绔欎笉鎵挎媴浠讳綍璐ｄ换銆� \</div>'

    // 鎸夐挳浜�
    $(document).delegate(".alert-btn5",'click',function(){
        if(M.dialog5){
            return M.dialog5.show();
        }
        M.dialog5 = jqueryAlert({
            'content' : alertContent ,
            'modal'   : true,
            'contentTextAlign' : 'left',
            'width'   : '450px',
            'animateType' : 'linear',
            'buttons' :{
                '涓嶅悓鎰�' : function(){
                    M.dialog5.close();
                },
                '鍚屾剰' : function(){
                    if(M.dialog51){
                        return M.dialog51.show();
                    }
                    M.dialog51 = jqueryAlert({
                        'content' : '鍚屾剰涔熶笉鑳芥敞鍐屽摝...'
                    })
                }
            }
        })
    })

    // 鎸夐挳鍏�
    $(document).delegate(".alert-btn6",'click',function(){
        if(M.dialog6){
            return M.dialog6.show();
        }
        M.dialog6 = jqueryAlert({
            'style'   : 'pc',
            'title'   : '鎹曡幏椤�',
            'content' :  $("#alert-blockquote"),
            'modal'   : true,
            'contentTextAlign' : 'left',
            'width'   : 'auto',
            'animateType' : 'linear',
            'buttons' :{
                '鍏抽棴' : function(){
                    M.dialog6.close();
                },
            }
        })
    })

    // 鎸夐挳涓�
    $(document).delegate(".alert-btn7",'click',function(){
        if(M.dialog7){
            return M.dialog7.show();
        }
        M.dialog7 = jqueryAlert({
            'style'   : 'pc',
            'title'   : 'iframe灞�',
            'content' :  alertContent,
            'modal'   : true,
            'contentTextAlign' : 'left',
            'width'   : '400',
            'height'  : '300',
            'animateType' : 'linear',
            'buttons' :{
                '鍏抽棴' : function(){
                    M.dialog7.close();
                },
            }
        })
    })

    // 鎸夐挳鍏�
    $(document).delegate(".alert-btn8",'click',function(){
        if(M.dialog8){
            return M.dialog8.show();
        }
        M.dialog8 = jqueryAlert({
            'style'   : 'pc',
            'title'   : 'iframe绐�',
            'content' :  $(".alert-box"),
            'modal'   : true,
            'contentTextAlign' : 'left',
            'width'   : '90%',
            'height'  : '90%',
            'animateType': 'scale',
        })
    })


    // 鎸夐挳涔�
    $(document).delegate(".alert-btn9",'click',function(){
        if(M.dialog9){
            return M.dialog9.show();
        }
        M.dialog9 = jqueryAlert({
            'style'   : 'pc',
            'title'   : 'pc寮瑰眰',
            'content' :  'PC绔櫘閫氬脊灞傚懄鍛﹀懄...',
            'modal'   : true,
            'contentTextAlign' : 'left',
            'animateType': 'scale',
            'bodyScroll' : 'true',
            'buttons' : {
                '鍏抽棴' : function(){
                    M.dialog9.close();
                },
                '鍘婚椤�' : function(){
                    location.href="http://fy.035k.com";
                }
            }
        })
    })
})