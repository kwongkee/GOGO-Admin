<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_delivery_header", TEMPLATE_INCLUDEPATH));?>
<body style="font-size: 12px;">
<style>
    #test10{
        width: 100%;height: 100%;
    }
    #test11{
        width: 100%;height: 100%;
    }
    .layui-m-layercont{
        height: 22%;
    }
    #layui-layer100001{
        top:20px;
    }
</style>
<header>
    <div class="logo"><img src="../addons/sz_yi/static/images/logo.png" alt=""></div>
    <div class="plane"><img src="../addons/sz_yi/static/images/plane.png" alt=""></div>
</header>
<div class="warp">
    <div class="content">
        <div class="warp-con">
            <div class="details-title dis">
                <div class="fl" style="width: 40%;margin-top: 3%;"><span class="details-line">|</span>选择已购商店</div>
            </div>
            <div class="details-title">
                <div class="choice">
                    <ul>
                        <li><img src="../addons/sz_yi/static/images/icon_12.png" alt=""></li>
                        <li>请选择直邮方式</li>
                        <li style="width: 30%;">
                            <select name="directmailType" id="directmail" onchange="directmailType(this);">
                                <option value="">请选择</option>
                                <option value="1">保税直邮</option>
                                <option value="2">境外直邮</option>
                            </select>
                        </li>
                    </ul>
                    <ul>
                        <li><img src="../addons/sz_yi/static/images/icon_05.png" alt=""></li>
                        <li>请选择购买店铺</li>
                        <li style="width: 88%;" class="scroll">
                            <div class="scroll-list">
                                <ul>
                                    <?php  if(is_array($res['merchantList'])) { foreach($res['merchantList'] as $item) { ?>
                                    <li>
                                        <div class="check-box">
                                            <label for="checkbox1"></label>
                                            <input type="checkbox" id="checkbox1" class="mer" value="<?php  echo $item['uid'];?>"/>
                                        </div>
                                        <span><?php  echo $item['username'];?></span>
                                    </li>
                                    <?php  } } ?>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            <div id="g"></div>
        </div>
    </div>
    <div class="content">
        <div class="warp-con">
            <div class="details-title">
                <span class="details-line">|</span>申报信息
            </div>
            <div class="details-num">
                <p>实际购买人
                    <select name="buy" id="buy">
                        <option value="">请选择</option>
                        <?php  if(is_array($res['recipient'])) { foreach($res['recipient'] as $item) { ?>
                        <option value="<?php  echo $item['id'];?>"><?php  echo $item['name'];?></option>
                        <?php  } } ?>
                    </select>
                <p style="margin-left: 58%;float: left;margin-top: -34px;font-size: 27px;position: relative;left: 0;"><a href='#' onclick="locaAddRecipient();">+</a></p>

                </p>
                <p>实际收件人
                    <select name="recipient" id="recipient">
                        <option value="">请选择</option>
                        <?php  if(is_array($res['recipient'])) { foreach($res['recipient'] as $item) { ?>
                        <option value="<?php  echo $item['id'];?>"><?php  echo $item['name'];?></option>
                        <?php  } } ?>
                    </select>
                <p style="margin-left: 58%;float: left;margin-top: -34px;font-size: 27px;position: relative;left: 0;"><a href="#" onclick="locaAddRecipient();">+</a></p>
                </p>
                <p>实际发件人
<!--                    <select name="merchType" id="merchType">-->
<!--                        <option value="">请选择</option>-->
<!--                        <option value="volvo">购物商户</option>-->
<!--                        <option value="volvo">合作商户</option>-->
<!--                    </select>-->
                    <select name="merchant" id="merchant" onchange="clearPackMaterial();">
                        <option value="">请选择</option>
                        <?php  if(is_array($res['merchantList'])) { foreach($res['merchantList'] as $item) { ?>
                            <option value="<?php  echo $item['uid'];?>"><?php  echo $item['username'];?></option>
                        <?php  } } ?>
                    </select>
                </p>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="warp-con">
            <div class="details-title">
                <span class="details-line">|</span>包裹详情
            </div>
            <div class="details-num" style="display: inline-block;">
                <div class="pack-details" style="width: 100%;">
                    <p>包裹总数：<span id="showNum">0</span></p>
                    <p>包裹总值：¥ <span id="showTotalPrice">0</span></p>
<!--                    <p style="margin-top: 10px; padding-top: 5px; border-top: 1px solid #d0d0d0;">保价直邮：-->
<!--                        <select>-->
<!--                            <option value="需要保价">需要保价</option>-->
<!--                            <option value="无需保价">无需保价</option>-->
<!--                        </select>-->
<!--                    <p>保费金额：-->
<!--                        <select>-->
<!--                            <option value="1">1</option>-->
<!--                            <option value="2">2</option>-->
<!--                        </select>-->
<!--                    </p>-->
<!--                    <p>可保价值：¥ 1.00</p>-->
<!--                    <p>保费金额/百分比：<input type="text" name="safeFree" id="safeFree" value="0" style="border: 1px solid #cccc;line-height: 22px;width: 46px;"></p>-->
<!--                    </p>-->


<!--                    <p style="margin-top: 10px; padding-top: 5px; border-top: 1px solid #d0d0d0;">包材选择：-->
<!--                        <select name="isMaterial" id="isMaterial" onchange="getPackageMaterial(this);">-->
<!--                            <option value="Y">需要包材</option>-->
<!--                            <option value="N" selected="selected">无需包材</option>-->
<!--                        </select>-->
<!--                    <p>包材名称：-->
<!--                        <select name="material" id="material" onchange="showMaterialPrice();">-->
<!--                            <option value="">请选择</option>-->
<!--                        </select>-->
<!--                    </p>-->
<!--                    <p id="packFree">包材费用：¥ 0.00</p>-->
<!--                    </p>-->

                    <!-- <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <select>
                               <option value="volvo">商户提供</option>
                               <option value="volvo">自费购买</option>
                        </select>
                    </p>
                    <p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <select>
                               <option value="volvo">纸箱</option>
                               <option value="volvo">塑料袋</option>
                        </select>
                    </p> -->
                </div>
                <div class="pack_but fl" style="text-align: center; top:0px; left: 6%; margin-left: 0%; width: 88%;">
                    <button id="alertBtn" onclick="pay();return false;" type="button" class="zbox-btn zbox-btn-blue zbox-btn-outlined but-red" style="border-radius:15px;">
                    <img src="../addons/sz_yi/static/images/打包发货.png" style="height: 32px;width: 32px;" alt="">集中打包
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH)) : (include template("common/easy_deliver_footer", TEMPLATE_INCLUDEPATH));?>
<!-- 加减数量 -->
<!--<link href="../addons/sz_yi/static/css/framework7.bundle.min.css" rel="stylesheet" type="text/css"/>-->
<script type="text/javascript" src="../addons/sz_yi/static/js/framework7.bundle.min.js"></script>
<script src="../addons/sz_yi/static/js/layer.js" type="text/javascript"></script>
<script src="https://www.layuicdn.com/layui-v2.5.6/layui.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/layui-upload.js" type="text/javascript"></script>
<script>
    var App = new Framework7();
    var $$ = Dom7;

    const numAdd = ()=>{
        $$('.num-jian').each(function(){
            $$(this).on('click',function(){
                var maxNum = $$(this).attr('data-num');
                var input_num = $$(this).parent().next().find('.input-num');
                if(input_num.val()<=1){
                    input_num.val(1);
                }else{
                    var num = parseInt($$('#showNum').html()) - 1;
                    var p = parseFloat($$('#showTotalPrice').html());
                    input_num.val(parseInt(input_num.val()) - 1);
                    if (p>0){
                        if ($(this).parent().parent().parent().parent().parent().prev().find('#checkbox1').prop('checked')) {
                            $$('#showNum').html(num);
                            $$('#showTotalPrice').html(parseFloat(p - parseFloat($$(this).parent().parent().parent().parent().parent().parent().attr('data-price'))));
                        }
                    }
                }
                $$(input_num).parent().parent().parent().next().children().html('待打包:' + (maxNum - input_num.val()));
            });
        });

        $$('.num-jia').each(function(){
            $$(this).on('click',function(){
                var maxNum = $$(this).attr('data-num');
                var input_num = $$(this).parent().prev().find('.input-num');
                if(input_num.val()>=maxNum){
                    input_num.val(maxNum);
                }else{
                    input_num.val(parseInt(input_num.val()) + 1);
                    var num = parseInt($('#showNum').html()) + 1;
                    var p = parseFloat($('#showTotalPrice').html());
                    if ($(this).parent().parent().parent().parent().parent().prev().find('#checkbox1').prop('checked')) {
                        $('#showTotalPrice').html(parseFloat(p + parseFloat($$(this).parent().parent().parent().parent().parent().parent().attr('data-price'))));
                        $('#showNum').html(num);
                    }
                }
                $$(input_num).parent().parent().parent().next().children().html('待打包:' + (parseInt(maxNum) - parseInt(input_num.val())));
            });
        });
        $$('#input-num').change(function () {
            var maxNum = $$(this).parent().next().find('#num-jia').attr('data-num');
            if ($$(this).val()==0){
                $$(this).val(1);
            }else if($$(this).val()>=maxNum){
                $$(this).val(maxNum);
            }
        });
    }

    const bandGoodsCheck = ()=>{
        $$('.goods').each(function () {
            $$(this).on('click',function () {
                if($$(this).prop('checked')){
                    selGoods($$(this).val(),this);
                }else{
                    cancleGoods(this);
                }
            });
        });
    }

    //选择商品
    const selGoods = (gid,object)=>{
        var gprice = parseFloat($$(object).parent().parent().attr('data-price'));
        var num = parseInt($$(object).parent().next().find('#input-num').val());
        var showNum = parseInt($$('#showNum').html());
        var showTotalPrice = parseFloat($$('#showTotalPrice').html());
        $$('#showNum').html(showNum+num);
        $$('#showTotalPrice').html(parseFloat((gprice*num).toFixed(2))+showTotalPrice);
    }
    //取消商品
    const cancleGoods = (object)=>{
        var gprice = parseFloat($$(object).parent().parent().attr('data-price'));
        var num = parseInt($$(object).parent().next().find('#input-num').val());
        var showNum = parseInt($$('#showNum').html());
        var showTotalPrice = parseFloat($$('#showTotalPrice').html());
        $$('#showNum').html(showNum-num);
        $$('#showTotalPrice').html(parseFloat(showTotalPrice-(gprice*num).toFixed(2)));
    }


    const directmailType = (obj)=>{
        App.request.json("<?php  echo $this->createMobileUrl('order/easy_deliver_centralizedpackage')?>&a=getGoods",{"directmailType":$(obj).val()},(data)=>{
            appendGoods(data);
        },error);
        return false;
    }

    $$('.mer').each(function(){
        $$(this).on("click",function(){
            var id = [];
            $$('.mer').each(function(){
                if($$(this).prop('checked')){
                    id.push($$(this).val());
                }

            });
            console.log(id.join(','));
            App.request.json("<?php  echo $this->createMobileUrl('order/easy_deliver_centralizedpackage')?>&a=getGoods",{"directmailType":$$('#directmail').val(),"merchant":id.join(',')},(data)=>{
            appendGoods(data);
        },error);
        });
    });
    const error=()=>{
        $.DialogByZ.Alert({Title: "提示", Content: "您的请求失败"})
    }



    const appendGoods = (data)=>{
        var h = '';
        if(data.result==undefined){
             $$('#g').html(h);
            return false;
        }
        for(var i=0;i<data.result.length;i++){
            var title = data.result[i].title;
            var num = data.result[i].total;
            var price =data.result[i].price;
            h+='<div class="pack-warp" data-price="'+price+'" ><div class="check-box"><label for="checkbox1"></label><input type="checkbox" id="checkbox1"   class="goods" value="'+data.result[i].id+'"/></div><div class="pack-list"><p>商品名称：'+title+'</p><ul class="btn-numbox"><li><span class="number">打包数量：</span></li><li><ul class="count"><li><span id="num-jian" class="num-jian" data-num="'+num+'" style="border-radius:5px 0 0 5px;">-</span></li><li><input type="text" class="input-num" id="input-num" value="'+num+'" /></li><li><span id="num-jia" class="num-jia" data-num="'+num+'" style="border-radius:0 5px 5px 0;">+</span></li></ul></li><li><span class="kucun" style="margin-left: 10px; color: #bbb; font-size: 12px;">待打包:0</span></li></ul><p>商品价值：¥ '+price+'</p></div></div>';
        }
        $$('#g').html(h);
        numAdd();
        bandGoodsCheck();
        $$('#showNum').html(0);
        $$('#showTotalPrice').html(0);
    }

    //获取包材
    const getPackageMaterial = (obj)=>{
        if ($$(obj).val()=='Y'){
            App.request.json("<?php  echo $this->createMobileUrl('order/easy_deliver_centralizedpackage')?>&a=getPackageMaterial", {"merchantId":$$('#merchant').val()},(data)=>{
                if (data.status==1){
                    $$(obj).prop('selectedIndex',1);
                    $.DialogByZ.Alert({Title: "提示", Content: data.result});
                }else{
                    var sel = '<option value="">请选择</option>';
                    for (var i=0;i<data.result.length;i++){
                        sel +='<option value="'+data.result[i]['id']+'" data-price="'+data.result[i]['price']+'">'+ data.result[i]['packgeName'] + '(' + data.result[i]['speci'] +'</option>';
                    }
                    $$('#material').html(sel);
                }
            },error);
        }

    };
    const showMaterialPrice = ()=>{
        $('#packFree').html('包材费用：¥' + $("#material option:selected").attr('data-price'));
    }

    const clearPackMaterial=()=>{
        $$('#isMaterial').prop('selectedIndex',1);
        $$('#material').html('<option value="">请选择</option>');
    }

    var pageii;
    var localUrl;
    var isDone=false;
    const pay=()=>{
        var goods_list = {};
        $$('.goods:checked').each(function () {
            goods_list[$$(this).val()]=$$(this).parent().next().find('.input-num').val();
        });
        var buy = $$('#buy').val();
        var recipient = $$('#recipient').val();
        var merchant = $$('#merchant').val();
        var safeFree = $$('#safeFree').val();
        var isMaterial = $$('#isMaterial').val();
        var material = $('#material').val();
        App.request.post("<?php  echo $this->createMobileUrl('order/easy_deliver_centralizedpackage')?>&a=startPacket",{
            goods_list:goods_list,
            buy:buy,
            recipient:recipient,
            merchant:merchant,
            safeFree:safeFree,
            isMaterial:isMaterial,
            material:material
        },function (data){
            data = JSON.parse(data);
            if (data.status==1){
                $.DialogByZ.Alert({Title: "提示", Content: data.result});
            }else{
                if (data.result.type=='CC'){
                    openVerif();
                    localUrl=data.result.url;
                }else{
                    window.location.href=data.result.url;
                }
            }
        },error);
        return false;
    }

    const locaAddRecipient=()=>{
        window.location.href="<?php  echo $this->createMobileUrl('member/easy_deliver_family')?>";
    }
    const openVerif=()=>{
        pageii = layer.open({
            type: 1
            ,title:""
            ,content: $('#u')
            ,anim: false
            ,style: 'position:fixed; left:0; top:0; width:100%; height:100%; border: none; -webkit-animation-duration: .5s; animation-duration: .5s;'
        });
    }
    /* 如果在和后台做数据交互时，出现点击加减按钮的值无法传到后台的情况，可以用下面这种方式
    $("body").on("click", ".num-jian", function(m) {
        var obj = $(this).closest("ul").find(".input-num");
        if(obj.val() <= 0) {
             obj.val(0);
        } else {
             obj.val(parseInt(obj.val()) - 1);
        }
        obj.change();
     });

    $("body").on("click", ".num-jia", function(m) {
        var obj = $(this).closest("ul").find(".input-num");
        obj.val(parseInt(obj.val()) + 1);
        obj.change();
    });*/


    /*提示框*/




</script>

</body>
</html>
<div id="u" style="display: none;">
    <div class="layui-upload-drag" id="test10">
        <img class="priv1" src="../addons/sz_yi/static/images/身份证正面.png" style="margin-top: -25px;margin-left: -46px;">
        <p class="priv1" style="text-align: center;margin-top: 20px;margin-left: -54px;">点击上传正面照片</p>
        <div class="layui-hide" id="uploadDemoView">
            <!--            <hr>-->
            <img src="" alt="上传成功后渲染" style="max-width: 196px;height: 150px;width: 196px;margin-left: -42px;">
        </div>
    </div>
    <div class="layui-upload-drag" id="test11">
        <img class="priv2" src="../addons/sz_yi/static/images/身份证背面.png" style="margin-top: -25px;margin-left: -46px;">
        <p class="priv2" style="text-align: center;margin-top: 20px;margin-left: -54px;">点击上传反面照片</p>
        <div class="layui-hide" id="uploadDemoView2">
            <!--        <hr>-->
            <img src="" alt="上传成功后渲染" style="max-width: 196px;height: 150px;width: 196px;margin-left: -42px;">
        </div>
    </div>
    <button type="button" class="layui-btn layui-btn-fluid" style="background-color: #2a95d8;border:1px solid #2a95d8;margin-top:5px;" onclick="closeLayuiPage();">完成</button>
</div>
<script>
    layui.use('upload', function(){
        var upload = layui.upload;
        //普通图片上传
        var uploadInst = upload.render({
            elem: '#test10'
            , url: '<?php  echo $this->createPluginMobileUrl("directmailorder/uploadIdCard")?>' //改成您自己的上传接口
            ,data:{
                name:function () {
                    return  $('#recipient').val();
                },
                type:"face"
            }
            , before: function (obj) {
                //预读本地文件示例，不支持ie8
                obj.preview(function (index, file, result) {
                    $('#uploadDemoView img').attr('src', result); //图片链接（base64）
                    $('.priv1').addClass('layui-hide');
                    $('#uploadDemoView').removeClass('layui-hide').addClass('layui-show');
                });
                layer.load(2); //上传loading
            }
            , done: function (res) {
                //如果上传失败
                layer.closeAll('loading'); //关闭loading
                layer.msg(res.result);
                if (res.status <= 0) {
                    isDone=true;
                }
                //上传成功
            }
            , error: function () {
                //演示失败状态，并实现重传
                layer.closeAll('loading'); //关闭loading
                isDone=false;
                return layer.msg('上传失败');
            }
        });
        //普通图片上传
        var uploadInst1 = upload.render({
            elem: '#test11'
            , url: '<?php  echo $this->createPluginMobileUrl("directmailorder/uploadIdCard")?>' //改成您自己的上传接口
            ,data:{
                name:function () {
                    return  $('#recipient').val();
                },
                type:"back"
            }
            , before: function (obj) {
                console.dir(obj);
                //预读本地文件示例，不支持ie8
                obj.preview(function (index, file, result) {
                    $('#uploadDemoView2 img').attr('src', result); //图片链接（base64）
                    $('.priv2').addClass('layui-hide');
                    $('#uploadDemoView2').removeClass('layui-hide').addClass('layui-show');
                });
                layer.load(2); //上传loading
            }
            , done: function (res) {
                //如果上传失败
                layer.closeAll('loading'); //关闭loading
                layer.msg(res.result);
                if (res.status <= 0) {
                    isDone=true;
                }
                //上传成功
            }
            , error: function () {
                //演示失败状态，并实现重传
                layer.closeAll('loading'); //关闭loading
                isDone=false;
                return layer.msg('上传失败');
            }
        });
    });
    const closeLayuiPage =()=>{
        layer.close(pageii);
        //alert(localUrl);
        if (isDone){
            window.location.href=localUrl;
        }
        return false;
    }
</script>
