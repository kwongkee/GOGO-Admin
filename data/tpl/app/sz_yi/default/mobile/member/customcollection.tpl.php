<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>自定义收款</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;font-size:15px;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:15px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#1E9FFF !important; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .trade_goods,.trade_project,.advance_collection,.regular_collection,.every_day,.every_week,.every_month,.every_year,.end_time{display:none;}
    .disf{display:flex;}
    .trans_form .tf_active{border:1px solid #1E9FFF;color:#1E9FFF;}
    .trans_form span{border:1px solid #999;}

    /**合同文件**/
    .info_main .images {float: left; width:auto;height:30px;margin-top:7px;}
    .info_main .images .img { float:left; position:relative;width:30px;height:30px;border:1px solid #e9e9e9;margin-right:5px;}
    .info_main .images .img img { position:absolute;top:0; width:100%;height:100%;}
    .info_main .images .img .minus { position:absolute;color:red;width:8px;height:12px;top:-18px;right:-1px;}
    .info_main .plus { float:left; width:30px;height:30px;border:1px solid #e9e9e9; color:#dedede;; font-size:18px;line-height:30px;text-align:center;margin-top:4px;}
    .info_main .plus i { left:7px;top:7px;}

    /**服务条款**/
    .send_service{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .send_service .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .send_service .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .send_service .confirm>span:nth-child(1){color: red;}
    .send_service .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .send_service .title2{text-align: center;border-bottom: 1px solid #eee;margin-bottom: 10px;}
    .send_service .title2>p{text-align: center;font-size: 16px;font-weight: bold;}
    .send_service .important{color: #1E9FFF ;}
    .send_service .service_content{min-height:200px;height:200px;max-height:250px;overflow: scroll;}
    .service_btn{display:flex;align-items:center;justify-content: space-evenly;font-size:15px;margin-top:10px;margin-bottom:10px;}
    .service_btn .service_add{padding:6px 14px;background:#1E9FFF;color:#fff;}
    .service_btn .service_del{padding:6px 14px;background:#ff5555;color:#fff;}
</style>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2-zh.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.core-2.5.2.css" rel="stylesheet" type="text/css" />
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.animation-2.5.2.css" rel="stylesheet" type="text/css" />
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.datetime-2.5.1-zh.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.js" type="text/javascript"></script>
<link href="../addons/sz_yi/static/js/dist/mobiscroll/mobiscroll.android-ics-2.5.2.css" rel="stylesheet" type="text/css" />

<link href="../addons/sz_yi/template/mobile/default/static/js/star-rating.css" media="all" rel="stylesheet" type="text/css"/>
<script src="../addons/sz_yi/template/mobile/default/static/js/star-rating.js" type="text/javascript"></script>
<script src="../addons/sz_yi/static/js/dist/ajaxfileupload.js" type="text/javascript"></script>

<div id="container">
    <div class="page_topbar">
        <div class="title">自定义收款</div>
    </div>
    <div class="info_main">
        <div class="line">
            <div class="title">交易形式</div>
            <div class='info'>
                <div class='inner'>
                    <span class="trans_form" data-val="1"><i class="fa fa-check-circle-o" style="color:#0c9;"></i>立即收款</span>
                    <span class="trans_form" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>预约收款</span>
                    <span class="trans_form" data-val="3" style="margin-left:5px;"><i class="fa fa-circle-o"></i>定期收款</span>
                    <input type="text" name="trans_form" id="trans_form" value="1" style="display: none;">
                </div>
            </div>
        </div>
        <!--预约收款-->
        <div class="line advance_collection">
            <div class="title">选择时间</div>
            <div class="info"><div class="inner">
                <input type="text" id="advance_day" placeholder="点击选择日期" readonly value=''/>
            </div></div>
        </div>
        <!--定期收款-->
        <div class="line regular_collection">
            <div class="title">定期方式</div>
            <div class='info'>
                <div class='inner'>
                    <span class="regular_type" data-val="1"><i class="fa fa-circle-o"></i>每日</span>
                    <span class="regular_type" data-val="2" style="margin-left:5px;"><i class="fa fa-circle-o"></i>每周</span>
                    <span class="regular_type" data-val="3" style="margin-left:5px;"><i class="fa fa-circle-o"></i>每月</span>
                    <span class="regular_type" data-val="4" style="margin-left:5px;"><i class="fa fa-circle-o"></i>每年</span>
                    <input type="text" name="regular_type" id="regular_type" value="" style="display: none;">
                </div>
            </div>
        </div>
        <!--定期收款~每日-->
        <div class="line every_day">
            <div class="title">选择时间</div>
            <div class="info"><div class="inner">
                <input type="text" id="every_day" placeholder="点击选择时间" readonly value=''/>
            </div></div>
        </div>
        <!--定期收款~每周-->
        <div class="line every_week">
            <div class="title">选择时间</div>
            <div class="info"><div class="inner disf">
                <select name="week" id="week" style="width:50%;border: 0px;">
                    <option value="">请选择</option>
                    <option value="1">周一</option>
                    <option value="2">周二</option>
                    <option value="3">周三</option>
                    <option value="4">周四</option>
                    <option value="5">周五</option>
                    <option value="6">周六</option>
                    <option value="7">周日</option>
                </select>
                <input type="text" id="every_week" placeholder="点击选择时间" readonly value='' style="width:50%;"/>
            </div></div>
        </div>
        <!--定期收款~每月-->
        <div class="line every_month">
            <div class="title">选择时间</div>
            <div class="info"><div class="inner disf">
                <select name="month" id="month" style="width:50%;border: 0px;">
                    <option value="">请选择（如当月没有当日，系统自动跳过当月）</option>
                    <option value="1">一号</option><option value="2">二号</option><option value="3">三号</option><option value="4">四号</option><option value="5">五号</option><option value="6">六号</option><option value="7">七号</option><option value="8">八号</option><option value="9">九号</option><option value="10">十号</option><option value="11">十一号</option><option value="12">十二号</option><option value="13">十三号</option><option value="14">十四号</option><option value="15">十五号</option><option value="16">十六号</option><option value="17">十七号</option><option value="18">十八号</option><option value="19">十九号</option><option value="20">二十号</option><option value="21">二十一号</option><option value="22">二十二号</option><option value="23">二十三号</option><option value="24">二十四号</option><option value="25">二十五号</option><option value="26">二十六号</option><option value="27">二十七号</option><option value="28">二十八号</option><option value="29">二十九号</option><option value="30">三十号</option><option value="31">三十一号</option>
                </select>
                <input type="text" id="every_month" placeholder="点击选择时间" readonly value='' style="width:50%;"/>
            </div></div>
        </div>
        <!--定期收款~每年-->
        <div class="line every_year">
            <div class="title">选择时间</div>
            <div class="info"><div class="inner disf">
                <input type="text" id="every_year" placeholder="点击选择时间" readonly value=''/>
            </div></div>
        </div>
        <!--定期收款~截止时间-->
        <div class="line end_time">
            <div class="title">截止时间</div>
            <div class="info"><div class="inner">
                <input type="text" id="end_time" placeholder="点击选择日期" readonly value=''/>
            </div></div>
        </div>
        <div class="line">
            <!--根据商品或交易服务选择-->
            <div class="title">交易类型</div>
            <div class="inner">
                <span class="type" data-val="1"><i class="fa fa-circle-o"></i> 商品</span>&nbsp;&nbsp;
                <span class="type" data-val="2"><i class="fa fa-circle-o"></i> 项目</span>&nbsp;&nbsp;
                <span class="type" data-val="3"><i class="fa fa-circle-o"></i> 服务</span>
                <input type="text" name="type" id="type" style="display: none;">
            </div>
        </div>
        <div class="line trade_goods"><div class="title">交易商品</div><div class="info"><div class="inner">
            <select name="goods_name" id="goods_name" style="width:100%;background: #fff;border:0;">
                <?php  if(empty($new_goods)) { ?>
                    <option value="">请先在后台上传产品</option>
                <?php  } else { ?>
                    <?php  if(is_array($new_goods)) { foreach($new_goods as $k => $v) { ?>
                    <optgroup label="<?php  echo $k;?>">
                        <?php  if(is_array($v)) { foreach($v as $v2) { ?>
                        <option value="<?php  echo $v2['id'];?>"><?php  echo $v2['title'];?></option>
                        <?php  } } ?>
                    </optgroup>
                    <?php  } } ?>
                <?php  } ?>
            </select>
        </div></div></div>
        <div class="line trade_project"><div class="title">交易项目</div><div class='info'><div class='inner'>
            <select name="project_name" id="project_name" style="width:100%;background: #fff;border:0;">
                <?php  if(empty($list)) { ?>
                    <option value="">请先配置交易项目</option>
                <?php  } else { ?>
                    <option value="">请选择交易项目</option>
                    <?php  if(is_array($list)) { foreach($list as $val) { ?>
                    <option value="<?php  echo $val['id'];?>"><?php  echo $val['project_name'];?></option>
                    <?php  } } ?>
                <?php  } ?>
            </select>
        </div></div></div>
        <div class="line"><div class="title">交易金额</div><div class='info'><div class='inner'><input type="number" id='trade_price' placeholder="请输入交易金额"  value="" onkeyup="value=value.replace(/^\D*(\d*(?:\.\d{0,2})?).*$/g, '$1')"/></div></div></div>
        <div class="line"><div class="title">付款人名称</div><div class='info'><div class='inner'><input type="text" id='payer_name' placeholder="请输入付款人名称"  value="" /></div></div></div>
        <div class="line"><div class="title">付款人电话</div><div class='info'><div class='inner'><input type="number" id='payer_tel' placeholder="请输入付款人电话"  value="" /></div></div></div>
<!--        <div class="line"><div class="title">身份证</div><div class='info'><div class='inner'><input type="text" id='idcard' placeholder="请输入付款人身份证号"  value="" /></div></div></div>-->
    </div>
    <div class="info_main">
        <div class="line"><div class="title">付款依据</div>
        <div class="inner">
            <span class="basic" data-val="1"><i class="fa fa-circle-o"></i> 合同</span>&nbsp;&nbsp;
            <span class="basic" data-val="2"><i class="fa fa-circle-o"></i> 订单</span>&nbsp;&nbsp;
            <span class="basic" data-val="3"><i class="fa fa-circle-o"></i> 说明</span>
            <input type="text" name="basic" id="basic" style="display: none;">
        </div></div>
        <div class="basic_contract" style="display:none;">
            <div class="line"><div class="title">合同编号</div><div class='info'><div class='inner'><input type="text" id='contract_num' placeholder="请输入合同编号"  value="" /></div></div></div>
            <div class="line"><div class="title">合同文件</div><div class='info'><div class='inner'>
                <div class="pic img_info" data-ogid='0' data-max='3'>
                    <div class="images">
                        <!--<div data-img="" class="img">-->
                        <!--<img src="">-->
                        <!--<div class="minus minus_del">-->
                        <!--<i class="fa fa-minus-circle"></i>-->
                        <!--</div>-->
                        <!--</div>-->
                    </div>

                    <div class="plus" style="position:relative;" ><i class="fa fa-plus" style="position:absolute;"></i>
                        <input type="file" name='imgFile0' id='imgFile0'  style="position:absolute;width:30px;height:30px;-webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;" />
                    </div>
                </div>
            </div></div></div>
        </div>
        <div class="basic_order" style="display:none;">
            <div class="line"><div class="title">订单编号</div><div class='info'><div class='inner'><input type="text" id='orderno' placeholder="请输入订单编号"  value="" /></div></div></div>
            <div class="line"><div class="title">订单链接</div><div class='info'><div class='inner'><input type="text" id='orderurl' placeholder="请输入订单链接"  value="" /></div></div></div>
            <div class="line"><div class="title">订单文件</div><div class='info'><div class='inner'>
                <div class="pic img_info" data-ogid='1' data-max='3'>
                    <div class="images">
                        <!--<div data-img="" class="img">-->
                        <!--<img src="">-->
                        <!--<div class="minus minus_del">-->
                        <!--<i class="fa fa-minus-circle"></i>-->
                        <!--</div>-->
                        <!--</div>-->
                    </div>

                    <div class="plus" style="position:relative;" ><i class="fa fa-plus" style="position:absolute;"></i>
                        <input type="file" name='imgFile1' id='imgFile1'  style="position:absolute;width:30px;height:30px;-webkit-tap-highlight-color: transparent;filter:alpha(Opacity=0); opacity: 0;" />
                    </div>
                </div>
            </div></div></div>
        </div>
        <div class="basic_explain" style="display:none;">
            <div class="line"><div class="title">自定义说明</div><div class='info'><div class='inner'><input type="text" id='description' placeholder="请输入自定义说明"  value="" /></div></div></div>
        </div>
        <div class="line"><div class="title">付款期限</div><div class='info'><div class='inner'><input type="number" id='pay_term' placeholder="单位（天）"  value="" /></div></div></div>
        <div class="line"><div class="title">逾期费用</div><div class='info'><div class='inner'><input type="number" id='pay_fee' style="width:30px;" placeholder="0"  value="" />%/天</div></div></div>
    </div>
    <div class="info_sub">提交</div>
</div>
<!--mask--二维码-->
<div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
<div class="qrcode" style="transform: translate(-50%,-50%);transition: all 0.2s;transition: all 0.2s ease 0s;display:none;width:100%;height:100%;position: absolute;left: 50%;top: 80%;z-index:1000;text-align:center;">
    <img src="./img/qrcode_for_gogo.jpg" alt="">
    <div style="text-align:center;color:#fff;font-size:16px;margin-top:8px;">长按保存二维码</div>
</div>
<!--服务-->
<div class="send_service" id="send_service">
    <div class="title2">
        <p class="important">请添加服务条款</p>
    </div>
    <div class="service_content">
        <div class="info_main service_info">
            <div class="line"><div class="title" style="width:40px;">项目</div>
                <div class="info"><div class="inner" style="margin-left:40px;">
                    <input type="text" name="service_name" placeholder="请填写项目名称" value="">
                </div></div></div>
            <div class="line"><div class="title" style="width:40px;">摘要</div>
                <div class="info"><div class="inner" style="margin-left:40px;">
                    <input type="text" name="service_abstract" placeholder="请填写摘要内容" value="">
                </div></div></div>
            <div class="line"><div class="title" style="width:40px;">金额</div>
                <div class="info"><div class="inner" style="margin-left:40px;">
                    <input type="number" name="service_price" placeholder="请输入该项目金额" value=''/>
                </div></div></div>
        </div>
    </div>

    <div class="service_btn">
        <div class="service_add">增加</div>
        <div class="service_del">删除</div>
    </div>
    <div class="confirm">
        <span class="close-but" onClick="fnClose(1)">关闭</span>
        <span class="close-but" onClick="fnClose(2)">确定</span>
    </div>
</div>
<script id="tpl_img" type="text/html">
    <div class='img' data-img='<%filename%>'>
        <img src='<%url%>' />
        <div class='minus'><i class='fa fa-minus-circle'></i></div>
    </div>
</script>
<script language="javascript">
    require(['tpl', 'core'], function(tpl, core) {
        //时间
        var currYear = (new Date()).getFullYear();
        var opt = {};
        opt.date = {preset: 'date'};
        opt.datetime = {preset: 'datetime'};
        opt.datetime2 = {preset: 'datetime'};
        opt.time = {preset: 'time'};
        opt.default = {
            theme: 'android-ics light',
            display: 'modal',
            mode: 'scroller',
            lang: 'zh',
            startYear: currYear,
            endYear: currYear+1
        };
        opt.default2 = {
            theme: 'android-ics light',
            display: 'modal',
            mode: 'scroller',
            lang: 'zh',
            dateFormat:'mm-dd',
            startYear: currYear,
            endYear: currYear
        };
        $("#advance_day").scroller('destroy').scroller($.extend(opt['datetime'], opt['default']));
        $("#every_day").scroller('destroy').scroller($.extend(opt['time'], opt['default']));
        $("#every_week").scroller('destroy').scroller($.extend(opt['time'], opt['default']));
        $("#every_month").scroller('destroy').scroller($.extend(opt['time'], opt['default']));
        $("#every_year").scroller('destroy').scroller($.extend(opt['datetime'], opt['default2']));
        $("#end_time").scroller('destroy').scroller($.extend(opt['datetime2'], opt['default']));

        //交易类型
        $('.type').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.type').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#type').val(val);
            if(val==1){
                //商品
                $('.trade_goods').show();
                $('.trade_project').hide();
            }else if(val==2){
                //项目
                $('.trade_goods').hide();
                $('.trade_project').show();
            }else if(val==3){
                //服务
                $('.mask').show();
                $('.send_service').show();
                $('.trade_goods').hide();
                $('.trade_project').hide();
            }
        });

        //服务条款-添加
        window.ser_btn_times = 1;//数量
        $('.service_add').click(function(){
            var html = '<div class="info_main service_info">\n' +
                '            <div class="line"><div class="title" style="width:40px;">项目</div>\n' +
                '                <div class="info"><div class="inner" style="margin-left:40px;">\n' +
                '                    <input type="text" name="service_name" placeholder="请填写项目名称" value="">\n' +
                '                </div></div></div>\n' +
                '            <div class="line"><div class="title" style="width:40px;">摘要</div>\n' +
                '                <div class="info"><div class="inner" style="margin-left:40px;">\n' +
                '                    <input type="text" name="service_abstract" placeholder="请填写摘要内容" value="">\n' +
                '                </div></div></div>\n' +
                '            <div class="line"><div class="title" style="width:40px;">金额</div>\n' +
                '                <div class="info"><div class="inner" style="margin-left:40px;">\n' +
                '                    <input type="number" name="service_price" placeholder="请输入该项目金额" value=\'\'/>\n' +
                '                </div></div></div>\n' +
                '        </div>';
            $('.send_service').find('.service_content').find('.service_info').last().after(html);
            window.ser_btn_times+=1;
        });
        //服务条款-删除
        $('.service_del').click(function(){
            if(window.ser_btn_times==1){

            }else{
                window.ser_btn_times-=1;
                $('.send_service').find('.service_content').find('.service_info').last().remove();
            }
        });

        //付款依据
        $('.basic').click(function(){
            var $this = $(this);
            var val = $this.data('val');
            $('.basic').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#basic').val(val);
            if(val==1){
                $('.basic_contract').show();
                $('.basic_order').hide();
                $('.basic_explain').hide();
            }else if(val==2){
                $('.basic_contract').hide();
                $('.basic_order').show();
                $('.basic_explain').hide();
            }else if(val==3){
                $('.basic_contract').hide();
                $('.basic_order').hide();
                $('.basic_explain').show();
            }
        });

        //交易形式
        $('.trans_form').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.trans_form').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#trans_form').val(val);
            if(val==1){
                $('.advance_collection').hide();
                $('.regular_collection').hide();
                $('.every_day').hide();$('.every_week').hide();$('.every_month').hide();$('.every_year').hide();$('.end_time').hide();
            }else if(val==2){
                $('.advance_collection').show();
                $('.regular_collection').hide();
                $('.every_day').hide();$('.every_week').hide();$('.every_month').hide();$('.every_year').hide();$('.end_time').hide();
            }else{
                $('.advance_collection').hide();
                $('.regular_collection').show();
                $('.end_time').show();
            }
        });

        //定期方式
        $('.regular_type').click(function() {
            var $this = $(this);
            var val = $this.data('val');
            $('.regular_type').find('i').css('color', '#999').removeClass('fa-check-circle-o').addClass('fa-circle-o');
            $(this).find('i').removeClass('fa-circle-o').addClass('fa-check-circle-o').css('color', '#0c9');
            $('#regular_type').val(val);
            if(val==1){
                //每日
                $('.every_day').show();
                $('.every_week').hide();
                $('.every_month').hide();
                $('.every_year').hide();
            }else if(val==2){
                //每周
                $('.every_day').hide();
                $('.every_week').show();
                $('.every_month').hide();
                $('.every_year').hide();
            }else if(val==3){
                //每月
                $('.every_day').hide();
                $('.every_week').hide();
                $('.every_month').show();
                $('.every_year').hide();
            }else{
                //每年
                $('.every_day').hide();
                $('.every_week').hide();
                $('.every_month').hide();
                $('.every_year').show();
            }
        });

        //合同文件
        $('.minus_del').click(function() {
            $(this).parent().remove();
            core.json('util/uploader', {op: 'remove', file: $(this).parent().data('img')}, function(rjson) {
                if (rjson.status == 1) {

                }
                $('.plus').show();
            }, false, true);
        });

        $('.plus input').change(function() {
            core.loading('正在上传');

            var comment =$(this).closest('.img_info');
            var ogid = comment.data('ogid');
            var max = comment.data('max');

            $.ajaxFileUpload({
                url: core.getUrl('util/uploader'),
                data: {file: "imgFile" + ogid,'op':'uploadFile'},
                secureuri: false,
                fileElementId: 'imgFile' + ogid,
                dataType: 'json',
                success: function(res, status) {
                    core.removeLoading();
                    var obj = $(tpl('tpl_img', res));
                    $('.images',comment).append(obj);

                    $('.minus',comment).click(function() {
                        core.json('util/uploader', {op: 'remove', file: $(obj).data('img')}, function(rjson) {
                            if (rjson.status == 1) {
                                $(obj).remove();
                            }
                            $('.plus',comment).show();
                        }, false, true);
                    });

                    if ($('.img',comment).length >= max) {
                        $('.plus',comment).hide();

                    }
                }, error: function(data, status, e) {
                    core.removeLoading();
                    core.tip.show('上传失败!');
                }
            });
        });

        $('.mask').click(function(){
            $('.mask').hide();
            $('.qrcode').hide();
            $('.send_service').hide();
        })
    });

    function fnClose(typ){
        if(typ==2){
            let price = 0;
            $('input[name="service_price"]').each(function(){
                if($(this).val()!='' || $(this).val()>0){
                    price+=parseFloat($(this).val());
                }
            });
            $('#trade_price').val(price);
        }
        $('.mask').hide();
        $('.send_service').hide();
    }

    $(function(){
        $('.info_sub').click(function(){
            let trans_form = $('#trans_form').val();//交易形式
            let trade_price = $('#trade_price').val();
            let trade_type = $('#type').val();
            var service_name = '';
            var service_abstract = '';
            var service_price = '';
            var pay_fee = $('#pay_fee').val();
            if($('#pay_fee').isEmpty()){
                alert('请填写逾期费用');return false;
            }
            if(trade_type==3){
                //服务项目名称
                $('input[name="service_name"]').each(function(){
                    if($(this).val()!=''){
                        service_name += $(this).val()+',';
                    }else{
                        alert('服务项目名称有空处，请检查后重新提交！');return false;
                    }

                });
                //服务项目摘要
                $('input[name="service_abstract"]').each(function(){
                    if($(this).val()!=''){
                        service_abstract += $(this).val()+',';
                    }else{
                        alert('服务项目摘要有空处，请检查后重新提交！');return false;
                    }
                });
                //服务项目金额
                $('input[name="service_price"]').each(function(){
                    if($(this).val()!=''){
                        service_price += $(this).val()+',';
                    }else{
                        alert('服务项目金额有空处，请检查后重新提交！');return false;
                    }
                });

            }

            let goods_name = $('#goods_name').val();
            let project_name = $('#project_name').val();
            let payer_name = $('#payer_name').val();
            let payer_tel = $('#payer_tel').val();
            let idcard = $('#idcard').val();

            let basic = $('#basic').val();//付款依据
            let contract_num = $('#contract_num').val();
            var contract = [];
            $('.img_info[data-ogid=0]').find('.img').each(function(){
                contract.push($(this).data('img'));
            });
            let orderno = $('#orderno').val();
            let orderurl = $('#orderurl').val();
            var orderdemo = [];
            $('.img_info[data-ogid=1]').find('.img').each(function(){
                orderdemo.push($(this).data('img'));
            });
            let description = $('#description').val();
            let pay_term = $('#pay_term').val();
            let end_time = $('#end_time').val();
            if(trans_form==2){
                //预约收款
                var advance_day = $('#advance_day').val();
                if($('#advance_day').isEmpty()){
                    alert('请选择预约收款时间!');
                    return;
                }
            }else if(trans_form==3){
                var regular_type = $('#regular_type').val();
                if($('#regular_type').isEmpty()){
                    alert('请选择定期方式!');
                    return;
                }else{
                    if(regular_type==1){
                        var every_day = $('#every_day').val();
                        if($('#every_day').isEmpty()){
                            alert('请选择定期收款的每日时间!');
                            return;
                        }
                    }else if(regular_type==2){
                        var week = $('#week').find('option:selected').val();
                        var every_week = $('#every_week').val();
                        if($('#week').find('option:selected').isEmpty()){
                            alert('请选择定期收款的每周周数!');
                            return;
                        }
                        if($('#every_week').isEmpty()){
                            alert('请选择定期收款的每周时间!');
                            return;
                        }
                    }else if(regular_type==3){
                        var month = $('#month').find('option:selected').val();
                        var every_month = $('#every_month').val();
                        if($('#month').find('option:selected').isEmpty()){
                            alert('请选择定期收款的每月日期!');
                            return;
                        }
                        if($('#every_month').isEmpty()){
                            alert('请选择定期收款的每月时间!');
                            return;
                        }
                    }else if(regular_type==4){
                        var every_year = $('#every_year').val();
                        if($('#every_year').isEmpty()){
                            alert('请选择定期收款的每年时间!');
                            return;
                        }
                    }
                }
                if($('#end_time').isEmpty()){
                    alert('请选择定期截止时间!');
                    return;
                }
            }

            if( $('#trade_price').isEmpty()){
                alert('请输入交易金额!');
                return;
            }
            if(parseFloat(trade_price)<0.01){
                alert('请输入正确的交易金额!');
                return;
            }
            if($('#type').isEmpty()){
                alert('请选择交易类型!');
                return;
            }
            if(trade_type==1){
                if( $('#goods_name').isEmpty()){
                    alert('请选择交易商品!');
                    return;
                }
            }else if(trade_type==2){
                if( $('#project_name').isEmpty()){
                    alert('请选择交易项目!');
                    return;
                }
            }
            if( $('#payer_name').isEmpty()){
                alert('请输入付款人名称!');
                return;
            }
            if(!$('#payer_tel').isMobile()){
                alert('请输入正确手机号码!');
                return;
            }
            // if($('#idcard').isEmpty()){
            //     alert('请输入付款人身份证号!');
            //     return;
            // }
            if($('#pay_term').isEmpty()){
                alert('请输入付款期限天数!');
                return;
            }

            if(basic==''){
                alert('请选择付款依据!');
                return;
            }else if(basic==1){
                if( $('#contract_num').isEmpty()){
                    alert('请输入合同编号!');
                    return;
                }
                if( contract.length=='' || contract.length==0){
                    alert('请上传合同图片!');
                    return;
                }
            }else if(basic==2){
                if( $('#orderno').isEmpty()){
                    alert('请输入订单编号!');
                    return;
                }
                if( $('#orderurl').isEmpty()){
                    alert('请输入订单链接!');
                    return;
                }
                if( orderdemo.length=='' || orderdemo.length==0){
                    alert('请上传订单图片!');
                    return;
                }
            }else if(basic==3){
                if($('#description').isEmpty()){
                    alert('请输入自定义说明!');
                    return;
                }
            }

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('member/customcollection');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'createorder','trade_price':trade_price,'trade_type':trade_type,'good_id':goods_name,'project_id':project_name,'payer_name':payer_name,'payer_tel':payer_tel,'trans_form':trans_form,'advance_day':advance_day,'regular_type':regular_type,'every_day':every_day,'week':week,'every_week':every_week,'month':month,'every_month':every_month,'every_year':every_year,'basic':basic,'contract_num':contract_num,'pay_term':pay_term,'contract_file':contract,'orderno':orderno,'orderurl':orderurl,'orderdemo':orderdemo,'description':description,'end_time':end_time,'service_name':service_name,'service_abstract':service_abstract,'service_price':service_price,'pay_fee':pay_fee,'idcard':idcard},
                success:function(json) {
                    if(json.status==-1){
                        alert(json.result.msg);
                        //找不到用户就弹出二维码，叫用户保存二维码去发给朋友关注公众号。
                        $('.mask').show();
                        $('.qrcode').show();
                    }else{
                        alert('创建成功！');
                        setTimeout(function(){
                            window.location.reload();
                        },2000)
                    }
                },error:function(json){
                    alert('数据出错！');
                }
            });
        });
    })
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>