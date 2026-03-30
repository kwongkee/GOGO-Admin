<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>人工汇总列表</title>
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:0px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .submit{display:block;font-size:14px;background:#1E9FFF;border-radius:5px;margin-left:20px;color:#fff;width: 100px;height: 35px;line-height: 35px;cursor:pointer;}
    .see{color:#fff;width: 100px;height: 35px;line-height: 35px;background:#999;font-size:14px;border-radius:5px;cursor:pointer;}
    .table{width:100%;font-size:15px;}
    .table thead td{background-color: #ecf6fc !important;}
    .table tr{height:25px;}
    .table tr td{vertical-align: middle !important;}
    .table tr td:nth-of-type(2){width:20%;}
    .summary,.express{box-sizing: border-box;padding: 2px 4px;font-size: 15px;color: #fff;background: #1EA01E;text-align:center;}
    .noAdd{background:#ff5555;}
    .hui-segment{width:90% !important;margin:0 auto !important;}
    .hui-segment a{font-size:13px !important;}

    /**已寄快递**/
    .send_express,.submitMethod_box{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .confirm>span:nth-child(1){color: red;}
    .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .title{text-align: center;border-bottom: 1px solid #eee;margin-bottom: 10px;}
    .title>p{height: 40px;line-height: 40px;text-align: center;font-size: 18px;font-weight: bold;}
    .important{color: red;}

    .bigautocomplete-layout{background:#fff;position:absolute;height: 200px;overflow: scroll;border:1px solid #000;z-index:1002;}
    .bigautocomplete-layout table tr td div{height: 30px;line-height: 30px;}

    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    .green{color:#1EA01E;}
    .red{color:#ff5555;}
    .blue{color:#3388FF;}
    .voucher_number{width: 130px;max-width: 130px;word-break: break-word;}
    .load_more{width: 100%;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;}

    .check-style-unequal-width {width: 8px;height: 20px;border-color: #009933;border-style: solid;border-width: 0 3px 5px 0;transform: rotate(45deg);}
    a{text-decoration: none;text-underline: none;}
</style>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/static/css/bootstrap.min.css" />
<script type="text/javascript" src="../addons/sz_yi/template/pc/default/static/js/bootstrap.min.js"></script>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/static/travel_express/css/hui.css" />
<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui.js" charset="UTF-8"></script>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<script src="../addons/sz_yi/static/js/jquery.bigautocomplete.js"></script>

<div class="page_head">
    <div class="left">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header" style="padding:0 6px;">
                    <div id="toolbar" class="btn-group" style="display:flex;align-items:center;">
                        <input type="text" name="ie_date" class="layui-input" id="ie_date" placeholder="请选择月份搜索" value="<?php  echo $date;?>">
                        <button type="button" class="layui-btn layui-btn-normal ssl_add" style="margin-left:10px;border-radius:5px;font-size:15px;">
                            新增汇总
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="container">
    <div class="hui-wrap">
        <div style="padding-top:10px;">
            <div id="domsIn" style="padding-top:0px; text-align:center;">
                <div class="info_main" >

                </div>

                <div class="typ1" style="display:flex;align-items: center;justify-content:center;margin-top:10px;">
                    <!--<div class="submit" onClick="submit()" style="margin-right:20px;margin-left:0;">提交（0）</div>-->
                    <!--<div class="see" onClick="back()">返回</div>-->
                </div>
            </div>
        </div>
    </div>

    <!--选择快递-->
    <div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
    <div class="send_express" id="send_express">
        <div class="title">
            <p class="important">填写物流信息</p>
        </div>
        <div class="info_main">
            <div class="line"><div class="title">快递企业</div>
                <div class="info"><div class="inner">
                    <input type="text" name="express_id" id="express_id" placeholder="请搜索快递企业" class="layui-input">
                </div></div></div>
            <div class="line"><div class="title">快递单号</div>
                <div class="info"><div class="inner">
                    <input type="text" id="express_num" placeholder="请输入快递单号" value=''/>
                </div></div></div>
            <input type="text" name="express_reg_id" id="express_reg_id" value="" style="display:none;"/>
        </div>
        <div class="confirm">
            <span class="close-but" onClick="fnClose(1)">关闭</span>
            <span class="close-but" onClick="fnClose(2)">提交</span>
        </div>
    </div>
    <!--凭证类型不一致-->
    <div class="submitMethod_box" id="submitMethod_box">
        <div class="title">
            <p class="important">温馨提示</p>
        </div>
        <p style="padding:4px 6px;font-size:15px;">抱歉，该凭证的凭证类型与已汇总凭证的凭证类型不一致，无法合并汇总。</p>
        <div class="info_main" style="margin-bottom:5px;">
            <div class="line"><div class="title">选择确认</div>
                <div class="info"><div class="inner">
                    <select name="selectMethod" id="selectMethod">
                        <option value="">请选择</option>
                        <option value="1">修改该凭证的凭证类型</option>
                        <option value="2">放弃该凭证的汇总提交</option>
                        <option value="3">单独该凭证的汇总提交</option>
                    </select>
                </div></div></div>
        </div>
        <input type="text" style="display:none;" name="voucher_id" id="voucher_id" value="">
        <input type="text" style="display:none;" name="voucher_typ" id="voucher_typ" value="">
        <input type="text" style="display:none;" name="voucher_submitMethod" id="voucher_submitMethod" value="">
        <div class="confirm">
            <span class="close-but" onClick="submitMethod(1)">关闭</span>
            <span class="close-but" onClick="submitMethod(2)">确认</span>
        </div>
    </div>
</div>
<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script language="javascript">
    $(function(){
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload;

            // form.render(null, 'component-form-element1');

            laydate.render({
                elem: '#ie_date'
                , format: 'yyyy-MM'
                ,type:'month'
                ,max: "new Date()"
                ,done: function(value, date, endDate){
                    changeDom(1,value);
                }
            });
        });

        $('.page_head').find('.left').click(function(){
            window.history.back(-1);
        });

        changeDom(1);
        $('.ssl_add').click(function(){
            //查看当前月份记账端有无提交对账单

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/register');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'manualSummary','add_reg':2,'date':$('#ie_date').val()},
                success:function(json) {
                    if(json.status==1){
                        alert(json.result.msg);return false;
                    }else{
                        window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=manualSummary';
                    }
                }
            });

        });
    });
    require(['tpl', 'core'], function(tpl, core) {
    });

    window.ids = '';
    window.times = 0;
    window.submit_method = 0;

    //点击切换函数可以根据实际业务需求编写业务代码
    function changeDom(index,date="<?php  echo $date;?>"){
        index--;
        var html = '';

        $.ajax({
            url:"<?php  echo $this->createMobileUrl('account/register');?>",
            type:'POST',
            dataType:'json',
            data:{'op':'manualSummaryList','type':index+1,'date':date},
            success:function(json) {
                if(json.status==1) {
                    var dat = json.result.data;

                    if (index == 0) {
                        //已登记未汇总
                        $('.typ1').show();
                        // $('.submit').html('提交（0）').show();
                        html += '               <table class="table table-striped tab1" style="table-layout:fixed;word-break: break-all;">\n' +
                            '                        <thead>\n' +
                            '                        <tr>\n' +
                            '                            <td style="width:140px;">登记编号</td>\n' +
                            '                            <td>汇总分类</td>\n' +
                            '                            <td>汇总类型</td>\n' +
                            '                            <td>操作</td>\n' +
                            '                        </tr>\n' +
                            '                        </thead>\n' +
                            '                        <tbody>\n';
                        if(dat.length==0){
                            html += '<tr><td colspan="4" align="center">暂无信息</td></tr>';
                        }else{
                            for (var i2 = 0; i2 < dat.length; i2++) {
                                let typ = '';
                                let color = '';
                                if (dat[i2]['type'] == 1) {
                                    typ = '收款登记';
                                    color = 'green';
                                } else if (dat[i2]['type'] == 2) {
                                    typ = '付款登记';
                                    color = 'red';
                                } else if (dat[i2]['type'] == 3) {
                                    typ = '账单登记';
                                    color = 'blue';
                                }
                                let submit_method = '';
                                if (dat[i2]['submit_method'] == 1) {
                                    submit_method = '纸质文件';
                                } else if (dat[i2]['submit_method'] == 2) {
                                    submit_method = '电子文件';
                                }
                                let voucher = dat[i2]['reg_number'];
                                if (typeof (voucher) == 'object') {
                                    voucher = '-';
                                }
                                if (voucher == '') {
                                    voucher = '-';
                                }
                                html += '                  <tr class="voucherID' + dat[i2]['id'] + '">\n' +
                                    '                            <td class="voucher_number">' + voucher + '</td>\n' +
                                    '                            <td class="' + color + '">' + typ + '</td>\n' +
                                    '                            <td>' + submit_method + '</td>\n' +
                                    '                            <td style="display: flex;align-items: center;justify-content: center;">\n' +
                                    '<img src="../attachment/images/edit.png" style="width:20px;margin-right:10px;" onclick=\'see(' + dat[i2]['id'] + ',' + dat[i2]['type'] + ')\'>\n' +
                                    // '                                <div class="summary check-style-unequal-width" onclick=\'summary(this,' + dat[i2]['id'] + ',' + dat[i2]['submit_method'] + ',' + dat[i2]['type'] + ')\' style="background:unset;margin-left:5px;display:block;"></div>\n' +
                                    '                            </td>\n' +
                                    '                        </tr>\n';
                            }
                        }

                        html += '                        </tbody>\n' +
                            '                    </table>\n';

                    }
                    hui('#domsIn').find('.info_main').html(html);
                    hui('#cate a').eq(index).addClass('hui-segment-active').siblings().removeClass('hui-segment-active');
                }
            },error:function(json){
                alert('数据出错！');
            }
        });
    }

    function go(id){
        //查看凭证信息
        window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_info&id='+id;
    }

    //已提交，未确认
    function no_queren(t,id){
        let selected = $(t).val();
        if(selected==1){
            //撤回提交
            withdraw();
        }else if(selected==2){
            //已寄快递
            express('',id);
        }else if(selected==3){
            //待发电邮
            alert('当前凭证批次号正等待发出，请耐心等待！');
        }else if(selected==5){
            let remark = $(t).children(':selected').data('remark');
            alert('拒绝原因：'+remark);
        }else if(selected==6){
            window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=watch_voucher&opera=1&id="+id;
        }
    }

    //记账确认
    function book_queren(t,id){
        let selected = $(t).val();
        if(selected==1){
            //修改补充
            let typ = $(t).children(':selected').data('typ');
            see(id,typ);
        }else if(selected==2){
            //不予记账
            nobook(id);
        }
    }

    function hideall(){
        $('.typ1').hide();
        $('.summary').hide();
        $('.submit').html('提交（0）').hide();
        $('.voucher_sum').show();
        $('.cancel').hide();
        window.ids='';
        window.times=0;
        window.submit_method = 0;
    }

    //汇总不一致
    function submitMethod(typ){
        if(typ==1){
            $('.mask').hide();
            $('#submitMethod_box').hide();
            $('#voucher_id').val('');
            $('#voucher_typ').val('');
            $('#voucher_submitMethod').val('');
        }else if(typ==2){
            let selectMethod = $('#selectMethod').val();
            let voucher_id = $('#voucher_id').val();
            let voucher_typ = $('#voucher_typ').val();
            let voucher_submitMethod = $('#voucher_submitMethod').val();

            if(selectMethod==1){
                //修改该凭证的凭证类型
                if(voucher_typ==3){
                    window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=manualSummary&id="+voucher_id+"&type="+voucher_typ;
                }else{
                    window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=manualSummary&id="+voucher_id+"&type="+voucher_typ;
                }

            }else if(selectMethod==2){
                //放弃该凭证的汇总提交
                $('.mask').hide();
                $('#submitMethod_box').hide();
                $('#voucher_id').val('');
                $('#voucher_typ').val('');
                $('#selectMethod').val('');
            }else if(selectMethod==3){
                //单独该凭证的汇总提交
                $('.tab1').find('.voucherID'+voucher_id).siblings().find('.summary').removeClass('noAdd').text('汇总');
                $('.tab1').find('.voucherID'+voucher_id).find('.summary').addClass('noAdd').text('取消');
                window.times=1;
                window.ids=voucher_id+',';
                window.submit_method=voucher_submitMethod;
                $('.mask').hide();
                $('#submitMethod_box').hide();
                $('#voucher_id').val('');
                $('#voucher_typ').val('');
                $('#voucher_submitMethod').val('');
                $('.submit').html('提交（'+times+'）');
            }
        }
    }

    //添加汇总
    function summary(t,id,submit_method,typ){
        // console.log(window.submit_method);return;
        if(!$(t).hasClass('noAdd')){
            //汇总
            if(typeof(window.submit_method)=='undefined' || window.submit_method==0){
                window.submit_method=submit_method;
            }else{
                //多选
                if(submit_method!=window.submit_method){
                    $('.mask').show();
                    $('#submitMethod_box').show();
                    $('#voucher_id').val(id);
                    $('#voucher_typ').val(typ);
                    $('#voucher_submitMethod').val(submit_method);
                    return;
                }
            }

            if(typeof(window.ids)=='undefined'){
                window.ids='';
            }
            window.ids +=id+',';
            if(typeof(window.times)=='undefined'){
                window.times=1;
            }else{
                window.times = window.times+1;
            }

            $(t).addClass('noAdd').css('border-color','#ff5555');
        }else{
            //取消
            window.ids = window.ids.split(id+',').join('');
            window.times -=1;
            if(window.times==0){
                window.submit_method=0;
            }
            $(t).removeClass('noAdd').css('border-color','#009933');
        }
        $('.submit').html('提交（'+times+'）');
    }

    //编辑
    function see(id,typ) {
        if(typ==3){
            //对账单
            window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=manualSummary&id="+id+'&type='+typ;
        }else{
            window.location.href="./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=manualSummary&id="+id+'&type='+typ;
        }
    }

    //撤回提交
    function withdraw(id){
        if(confirm('确定撤回提交吗？')){
            $.ajax({
                url: "<?php  echo $this->createMobileUrl('account/register');?>",
                type: 'POST',
                dataType: 'json',
                data: {'op': 'withdraw','id':id},
                success: function (json) {
                    if (json.status == 1) {
                        alert(json.result.msg);
                        changeDom(2);
                    }
                }
            });
        }
    }

    //撤回快递
    function withdraw_express(id){
        if(confirm('确定撤回快递吗？')){
            $.ajax({
                url: "<?php  echo $this->createMobileUrl('account/register');?>",
                type: 'POST',
                dataType: 'json',
                data: {'op': 'voucher_manage','id':id,'type':8},
                success: function (json) {
                    if (json.status == 1) {
                        alert(json.result.msg);
                        changeDom(3);
                    }
                }
            });
        }
    }


    //取消选择物流信息
    function fnClose(typ){
        if(typ==1){
            $('#express_reg_id').val('');
            $('#express_time').val('');
            $('#express_num').val('');
            $('#express_id').val('');
            $('#send_express').hide();
            $('.mask').hide();
        }else if(typ==2){
            //提交
            let express_reg_id = $('#express_reg_id').val();
            // let express_time = $('#express_time').val();
            let express_num = $('#express_num').val();
            let express_id = $('#express_id').val();
            if($('#express_id').isEmpty()){
                alert('请选择快递企业');return;
            }
            // if($('#express_time').isEmpty()){
            //     alert('请选择快递时间');return;
            // }
            if($('#express_num').isEmpty()){
                alert('请选择运单号码');return;
            }

            // hui.upToast(msg);

            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/register');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'voucher_manage','type':6,'id':express_reg_id,'express_num':express_num,'express_id':express_id},
                success:function(json) {
                    if(json.status==1){
                        alert(json.result.msg);
                        $('#express_reg_id').val('');
                        $('#express_time').val('');
                        $('#express_num').val('');
                        $('#express_id').val('');
                        $('#send_express').hide();
                        $('.mask').hide();
                        changeDom(3);
                    }
                }
            });
        }

    }

    //凭证汇总
    function voucher_sum(){
        $('.summary').show();
        $('.submit').show();
        $('.cancel').show();
        $('.voucher_sum').hide();
    }

    //取消
    function cancel(){
        $('.summary').hide();
        $('.submit').html('提交（0）').hide();
        $('.voucher_sum').show();
        $('.cancel').hide();

        window.ids='';
        window.times=0;
        $('.summary').removeClass('noAdd').text('添加汇总');
    }

    function submit(){
        if(window.times==0 || typeof(window.times)=='undefined'){
            alert('请添加需要汇总的凭证');return;
        }

        if(confirm('确认提交？')){

            $.ajax({
                url: "<?php  echo $this->createMobileUrl('account/register');?>",
                type: 'POST',
                dataType: 'json',
                data: {'op': 'voucher_manage', 'type': 5,'ids':window.ids,'submit_method':window.submit_method},
                success: function (json) {
                    if (json.status == 1) {
                        alert(json.result.msg);
                        changeDom(1);
                        window.ids = '';
                        window.times = 0;
                        window.submit_method = 0;
                    }
                }
            });
        }
    }

    function back(){
        window.history.go(-1);
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>