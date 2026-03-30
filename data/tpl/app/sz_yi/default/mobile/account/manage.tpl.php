<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<!--<title>项目配置中心</title>-->
<style type="text/css">
    body {margin:0px; background:#efefef; font-family:'微软雅黑'; -moz-appearance:none;}
    .info_main {height:auto;  background:#fff; margin-top:14px; border-bottom:1px solid #e8e8e8; border-top:1px solid #e8e8e8;}
    .info_main .line {margin:0 10px; height:40px; border-bottom:1px solid #e8e8e8; line-height:40px; color:#999;}
    .info_main .line .title {height:40px; width:80px; line-height:40px; color:#444; float:left; font-size:14px;}
    .info_main .line .info { width:100%;float:right;margin-left:-80px; }
    .info_main .line .inner { margin-left:80px; }
    .info_main .line .inner input {height:40px; width:100%;display:block; padding:0px; margin:0px; border:0px; float:left; font-size:14px;}
    .info_main .line .inner .user_sex {line-height:40px;}
    .info_sub {height:44px; margin:14px 5px; background:#31cd00; border-radius:4px; text-align:center; font-size:16px; line-height:44px; color:#fff;}
    .select { border:1px solid #ccc;height:25px;}
    .table{width:100%;font-size:15px;}
    .table tr td{
        vertical-align: middle !important;
    }
    .table tr{height:25px;}
    .table thead td{background-color: #ecf6fc !important;}
    .table tr td:nth-of-type(2){width:20%;}
    .see{box-sizing: border-box;padding: 2px 4px;font-size: 15px;color: #fff;background: #1E9FFF;text-align:center;}

    /**电邮地址**/
    .send_express,.submitMethod_box{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .confirm{height: 50px;border-top: 1px solid #eee;display: flex;}
    .confirm>span{flex: 1;height: 50px;line-height: 50px;font-size: 16px;text-align: center;}
    .confirm>span:nth-child(1){color: red;}
    .confirm>span:nth-child(2){border-left: 1px solid #eee;}
    .title{text-align: center;border-bottom: 1px solid #eee;margin-bottom: 10px;}
    .title>p{height: 40px;line-height: 40px;text-align: center;font-size: 18px;font-weight: bold;}
    .important{color: red;}

    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;display:flex;align-items:center;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    .page_head .right{width:80%;text-align:center;padding-right:80px;font-size:15px;padding-top:2px;}
    .green{color:#1EA01E;}
    .red{color:#ff5555;}
    .blue{color:#1E9FFF;}
    #email1,#email2{border-bottom:1px solid #666;}
    a{text-decoration: none;text-underline: none;}
</style>

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/template/pc/default/static/css/bootstrap.min.css" />

<link type="text/css" rel="stylesheet" href="../addons/sz_yi/static/travel_express/css/hui.css" />
<script type="text/javascript" src="../addons/sz_yi/template/mobile/default/enterprise/static/js/hui.js" charset="UTF-8"></script>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">

<div id="container">
    <div class="page_head">
        <div class="left">
            <div class="back"></div>
            <div style="font-size:15px;padding-top:2px;">返回</div>
        </div>
        <div class="right">
            待寄快递
        </div>
    </div>
    <!--<div class="page_topbar">-->
    <!--    <div class="title">待寄快递</div>-->
    <!--</div>-->
</div>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header" style="padding:0 6px;">
                    <div id="toolbar" class="btn-group" style="display:flex;align-items:center;">
                        <input type="text" name="ie_date" class="layui-input" id="ie_date" placeholder="请选择月份搜索" value="<?php  echo $_GPC['date'];?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <input type="text" style="display:none;" id="domid" value="1">
    <div class="hui-segment" id="cate" style="margin-top: 15px;">
        <a id="dom1" href="javascript:changeDom(1);" data-i="1" class="hui-segment-active">待确认</a>
        <a id="dom2" href="javascript:changeDom(2);" data-i="2">已确认</a>
    </div>
    <div class="info_main table-responsive">
        <?php  if(empty($list)) { ?>
            <div class="line" style="text-align:center;">暂无信息</div>
        <?php  } else { ?>

        <?php  } ?>
    </div>

    <!--拒绝原因-->
    <div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
    <div class="send_express" id="send_express">
        <div class="title">
            <p class="important">拒绝原因</p>
        </div>
        <textarea name="remark" id="remark" style="width:100%;height:100px;" placeholder="请输入拒绝原因"></textarea>
        <input type="text" id="batch_id" style="display:none;" value="">
        <div class="confirm">
            <span class="close-but" onClick="fnClose(1)">关闭</span>
            <span class="close-but" onClick="fnClose(2)">提交</span>
        </div>
    </div>
</div>
<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>

<script language="javascript">
    window.addEventListener('pageshow', function (e) {
        if(e.persisted || (window.performance && window.performance.navigation.type == 2)){
            var domid = $('#domid').val();

            if(domid!='' && typeof(domid)!='undefined'){
                if(domid==1){
                    document.getElementById('dom1').click();
                }else if(domid==2){
                    document.getElementById('dom2').click();
                }
            }
        }
    });
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
                    changeDom($('.hui-segment-active').data('i'),value);
                    window.location.replace('./index.php?i=3&c=entry&do=account&m=sz_yi&p=manage&date='+value);
                }
            });
        });
        $('.page_head').find('.left').click(function(){
              window.history.back(-1); 
        });
        changeDom(1);
    })
    require(['tpl', 'core'], function(tpl, core) {
        
    })
    function go(id){
        //查看凭证信息
        window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=bookkeeping&op=voucher_info&id='+id;
    }

    function changeDom(index,date){
        index--;
        $('#domid').val(index+1);
        if(date=='' || typeof(date)=='undefined'){
            date = $('#ie_date').val();
        }
        $.ajax({
            url: "<?php  echo $this->createMobileUrl('account/manage');?>",
            type: 'POST',
            dataType: 'json',
            data: {'op': 'display', 'type': index + 1,'date':date},
            success: function (json) {
                if (json.status == 1) {
                    var dat = json.result.data;
                    var html = '';

                    if (index == 0) {
                        //待确认
                        if(dat!=''){
                            html += '<table class="table table-striped table-bordered" style="margin-top:10px;margin-bottom:0;table-layout:fixed;word-break: break-all;">\n'+
                                '<thead>\n'+
                                '<tr>\n' +
                                '<td style="white-space:wrap;">批次号</td>\n'+
                                '<td style="width:80px;">商户</td>\n'+
                                '<td style="width:125px;">日期</td>\n'+
                                '<td style="width:80px;">操作</td>\n'+
                                '</tr>\n'+
                                '</thead>\n';
                            for (var i=0;i<dat.length;i++){
                                html += '<tr>\n' +
                                            '<td style="white-space:pre-line;">'+dat[i].batch_num+'</td>\n'+
                                            '<td  style="width:100%; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">'+dat[i].user_name+'</td>\n'+
                                            '<td  style="width:100%; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">'+dat[i].createtime+'</td>\n'+
                                            '<td>';
                                            if(dat[i].submit_method==1){
                                                html += '<select name="send_queren" onchange="send_queren(this,'+dat[i].id+',\''+dat[i].ids+'\')" style="margin-right:10px;">\n'+
                                                    '  <option>请选择</option>\n'+
                                                    '  <option value="1">确认快递</option>\n'+
                                                    '  <option value="2">拒绝快递</option>\n';
                                                    if(dat[i].method==1){
                                                        html+='  <option value="6">查看凭证</option>\n';
                                                    }else{
                                                        html+='  <option value="5">查看凭证</option>\n';
                                                    }

                                                    html+='</select>';
                                            }
                                            if(dat[i].submit_method==2){
                                                html += '<select name="send_queren" onchange="send_queren(this,'+dat[i].id+',\''+dat[i].ids+'\')" style="margin-right:10px;">\n'+
                                                    '  <option>请选择</option>\n'+
                                                    '  <option value="3">确认微信</option>\n'+
                                                    '  <option value="4">拒绝微信</option>\n';
                                                    if(dat[i].method==1){
                                                        html+='  <option value="6">查看凭证</option>\n';
                                                    }else{
                                                        html+='  <option value="5">查看凭证</option>\n';
                                                    }

                                                    html+='</select>';
                                            }
                                            html+='</td>\n';
                                        '</tr>\n';
                            }
                            html += '</table>\n';
                        }else{
                            html += '<div class="line" style="text-align:center;">暂无信息</div>';
                        }
                    }else if(index ==1 ){
                        //已确认
                        if(dat!=''){
                            html += '<table class="table table-striped table-bordered" style="margin-top:10px;margin-bottom:0;table-layout:fixed;word-break: break-all;">\n'+
                                '<thead>\n'+
                                '<tr>\n' +
                                '<td>批次号</td>\n'+
                                '<td style="width:80px;">商户</td>\n'+
                                '<td style="width:125px;">日期</td>\n'+
                                '<td style="width:80px;">操作</td>\n'+
                                '</tr>\n'+
                                '</thead>\n';
                            for (var i=0;i<dat.length;i++){
                                    html += '<tr>\n' +
                                                '<td style="white-space: pre-line;">'+dat[i].batch_num+'</td>\n'+
                                                '<td style="width:100%; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">'+dat[i].user_name+'</td>\n'+
                                                '<td  style="width:100%; overflow:hidden; white-space:nowrap; text-overflow:ellipsis;">'+dat[i].createtime+'</td>\n'+
                                                '<td>\n'+
                                                    '<select name="send_queren" onchange="send_queren(this,'+dat[i].id+',\''+dat[i].ids+'\')" style="margin-right:10px;">\n'+
                                                        '  <option>请选择</option>\n';
                                                        if(dat[i].method==1){
                                                            html+='  <option value="6">查看凭证</option>\n';
                                                        }else{
                                                            html+='  <option value="5">查看凭证</option>\n';
                                                        }

                                                        html+='</select>'+
                                                '</td>\n'+
                                            '</tr>\n';
                            }
                            html += '</table>\n';
                        }else{
                            html += '<div class="line" style="text-align:center;">暂无信息</div>';
                        }
                    }

                    $('.info_main').html(html);
                    hui('#domsIn').find('.info_main').html(html);
                    hui('#cate a').eq(index).addClass('hui-segment-active').siblings().removeClass('hui-segment-active');
                }
            }
        });
    }

    //发送微信或快递
    function send_queren(t,id,ids){
        let selected = $(t).val();
        if(selected==1){
            //确认快递
            see(id,1);
        }else if(selected==2){
            //拒绝快递
            see(id,2);
        }else if(selected==3){
            //确认微信
            see(id,3);
        }else if(selected==4){
            //拒绝微信
            see(id,4);
        }else if(selected==5){
            //查看凭证
            see(id,5);
        }else if(selected==6){
            //查看批次号-人工汇总
            see(id,6,ids);
        }
    }

    function see(id,typ,ids=0) {
        if(typ==1){
            if(confirm('本次凭证汇总的提交方式为［快递提交］，请确认并安排快递。')){
                $.ajax({
                    url: "<?php  echo $this->createMobileUrl('account/manage');?>",
                    type: 'POST',
                    dataType: 'json',
                    data: {'op': 'display','id':id,'type':3},
                    success: function (json) {
                        if (json.status == 1) {
                            alert(json.result.msg);
                            setTimeout(function(){
                                window.location.reload();
                            },2000);
                        }
                    }
                });
            }
        }else if(typ==2 || typ==4){
            $('.mask').show();
            $('.send_express').show();
            $('#batch_id').val(id);
        }else if(typ==3){
            if(confirm('本次凭证汇总的提交方式为［微信提交］，请确认。')){
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('account/manage');?>",
                    type:'POST',
                    dataType:'json',
                    data:{'op':'email','batch_id':id},
                    success:function(json) {
                        if(json.status==1){
                            alert(json.result.msg);
                            $('#send_express').hide();
                            $('.mask').hide();
                            setTimeout(function(){
                                window.location.reload();
                            },2000);
                        }else{
                            alert(json.result.msg);
                        }
                    }
                });
            }
        }else if(typ==5){
            //查看该批次号凭证
            window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=watch_voucher&opera=1&id='+id;
        }else if(typ==6){
            //查看该批次号-人工汇总
            window.location.href='./index.php?i=3&c=entry&do=account&m=sz_yi&p=register&op=voucher_info&id='+ids;
        }
    }

    //安排电邮
    function fnClose(typ){
        if(typ==1){
            $('#send_express').hide();
            $('.mask').hide();
            $('#batch_id').val("");
        }else if(typ==2){
            //提交
            // hui.upToast(msg);
            let remark = $('#remark').val();
            let id = $('#batch_id').val();

            if($('#remark').isEmpty()){
                alert('请输入拒绝原因');return;
            }

            $.ajax({
                url: "<?php  echo $this->createMobileUrl('account/manage');?>",
                type: 'POST',
                dataType: 'json',
                data: {'op': 'display','id':id,'type':4,'remark':remark},
                success: function (json) {
                    if (json.status == 1) {
                        alert(json.result.msg);
                        setTimeout(function(){
                            window.location.reload();
                        },2000);
                    }
                }
            });
        }

    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>