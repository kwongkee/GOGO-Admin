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
    /*.table tr td:nth-of-type(1){width:88%;overflow: hidden;white-space: nowrap;text-overflow: ellipsis;}*/
    .table tr td:nth-of-type(2){width:20%;}
    .see{box-sizing: border-box;padding: 2px 4px;font-size: 15px;color: #fff;background: #1E9FFF;text-align:center;width:fit-content;}
    .sign_box,.download_box{display:none;width: 80%;position: absolute;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
    .download_box .pic_div{height: 200px;max-height: 200px;min-height: 200px;overflow: scroll;padding:4px;width:100%;text-align:center;}
    /**电邮地址**/
    .send_express,.submitMethod_box{display:none;width: 80%;position: relative;background: #ffffff;border-radius: 5px;z-index:1000;transform:translate(-50%,-50%);top: 50%;left:50%;}
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
            人员增减
        </div>
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
        <a id="dom1" href="javascript:changeDom(1);" data-i="1" class="hui-segment-active">待增减列表</a>
        <a id="dom2" href="javascript:changeDom(2);" data-i="2">已增减列表</a>
    </div>
    <input type="text" id="down_id" value="" style="display:none;">
    <div class="info_main table-responsive add_reduct">
        <?php  if(empty($list)) { ?>
        <div class="line" style="text-align:center;">暂无信息</div>
        <?php  } else { ?>

        <?php  } ?>
    </div>

    <!--安排电邮-->
    <div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
    <div class="download_box">

    </div>
    <div class="sign_box">
        <div class="title">
            <p class="important">操作</p>
        </div>
        <input type="text" name="check_id" id="check_id" value="" style="display:none;"/>
        <div class="info_main" style="margin-bottom:14px;">
            <div class="line">
                <div class="title">审核状态</div>
                <div class="info"><div class="inner">
                    <select name="status" id="status">
                        <option value="">请选择审核状态</option>
                        <option value="1">确认增减</option>
                        <option value="2">增减失败</option>
                    </select>
                </div></div>
            </div>
            <div class="remark" style="display:none;text-align: center;margin-top:10px;">
                <textarea name="remark" id="remark" style="width:90%;height:100px;" placeholder="请输入失败原因"></textarea>
            </div>
        </div>
        <div class="confirm">
            <span class="close-but" onClick="fnClose(1)">取消</span>
            <span class="close-but" onClick="fnClose(2)">确认</span>
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
                    changeDom($('.hui-segment-active').data('i'),value);
                }
            });
        });
    });
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
        changeDom(1,"<?php  echo $_GPC['date'];?>");
        $('.page_head').find('.left').click(function(){
            window.history.back(-1);
        });
        $('.mask').click(function(){
            $('.sign_box').hide();
            $('.download_box').hide();
            $('.mask').hide();
        });

        $('#status').change(function(){
            var selected = $(this).children('option:selected').val();

            if(selected==2){
                $('.remark').show();
            }else{
                $('.remark').hide();
                $('.remark').val("");
            }
        });
    })
    require(['tpl', 'core'], function(tpl, core) {

    })

    function changeDom(index,date){
        index--;
        if(date=='' || typeof(date)=='undefined'){
            date = $('#ie_date').val();
        }
        $.ajax({
            url: "<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
            type: 'POST',
            dataType: 'json',
            data: {'op': 'socialTax_info', 'type': index + 1,'date':date},
            success: function (json) {
                if (json.status == 1) {
                    var dat = json.result.data;
                    var html = '';

                    if (index == 0) {
                        //待增减
                        if(dat!=''){
                            html += '<div class="infos table-responsive">\n' +
                                '            <table class="table table-striped" style="table-layout:fixed;word-break: break-all;">\n' +
                                '                <thead>\n' +
                                '                <tr>\n' +
                                '                    <td>商户</td>\n' +
                                '                    <td>增减月份</td>\n' +
                                '                    <td>项目</td>\n' +
                                '                    <td>类别</td>\n' +
                                '                    <td>操作</td>\n' +
                                '                </tr>\n' +
                                '                </thead>\n' +
                                '                <tbody>\n';
                            for (var i=0;i<dat.length;i++){
                                    html += '                <tr>\n' +
                                    '                    <td>'+dat[i].user_name+'</td>\n' +
                                    '                    <td>'+dat[i].opera_date+'</td>\n' +
                                    '                    <td>'+dat[i].typeName+'</td>\n' +
                                    '                    <td>'+dat[i].operatypeName+'</td>\n' +
                                    '                    <td>\n';
                                    if(dat[i].status==1){
                                        html += '                       <div class="see" onclick=\'down('+dat[i].id+')\' id="down'+dat[i].id+'" style="background:#125526;padding:4px 10px;">下载</div>\n';
                                    }else{
                                        html += '                       <div class="see" onclick=\'exam('+dat[i].id+')\'  style="background:#3388FF;padding:4px 10px;">审核</div>\n';
                                    }
                                    html += '                    </td>\n' +
                                    '                </tr>\n';
                            }
                            html += '                </tbody>\n' +
                                '            </table>\n' +
                                '        </div>';
                        }else{
                            html += '<div class="line" style="text-align:center;">暂无信息</div>';
                        }
                    }else if(index ==1 ){
                        //已增减
                        if(dat!=''){
                            html += '<div class="infos table-responsive">\n' +
                                '            <table class="table table-striped" style="table-layout:fixed;word-break: break-all;">\n' +
                                '                <thead>\n' +
                                '                <tr>\n' +
                                '                    <td>商户</td>\n' +
                                '                    <td>增减月份</td>\n' +
                                '                    <td>项目</td>\n' +
                                '                    <td>类别</td>\n' +
                                '                    <td>状态</td>\n' +
                                '                    <td>操作</td>\n' +
                                '                </tr>\n' +
                                '                </thead>\n' +
                                '                <tbody>\n';
                            for (var i=0;i<dat.length;i++){
                                html += '                <tr>\n' +
                                '                    <td>'+dat[i].user_name+'</td>\n' +
                                '                    <td>'+dat[i].opera_date+'</td>\n' +
                                '                    <td>'+dat[i].typeName+'</td>\n' +
                                '                    <td>'+dat[i].operatypeName+'</td>\n' +
                                '                    <td>'+dat[i].statusName+'</td>\n' +
                                '                   <td><div class="see" onclick=\'down('+dat[i].id+')\' id="down'+dat[i].id+'" style="background:#125526;padding:4px 10px;">下载</div></td>\n'+
                                '                </tr>\n';
                            }
                            html += '                </tbody>\n' +
                                '            </table>\n' +
                                '        </div>';
                        }else{
                            html += '<div class="line" style="text-align:center;">暂无信息</div>';
                        }
                    }

                    $('.add_reduct').html(html);
                    hui('#cate a').eq(index).addClass('hui-segment-active').siblings().removeClass('hui-segment-active');
                }
            }
        });
    }

    function down(id){
        //下载增减员文件
        $('.mask').show();
        $('.download_box').show();
        $('#down_id').val(id);

        $.ajax({
            url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
            type:'POST',
            dataType:'json',
            data:{'op':'socialTax_info','id':id,type:3},
            success:function(json) {
                if(json.status==1){
                    let data = json.result.data;
                    var html = '';
                    if(typeof(data) != 'undefined' || data != ''){
                        html += '<div class="title">\n' +
                            '        <p class="important">增/减员信息表下载</p>\n' +
                            '    </div>\n'+
                            '        <div class="pic_div">\n';
                        for(var i=0;i<data['info_file'].length;i++) {
                                html += '<div style="margin:0 10px 10px 0;box-shadow: 0px 0px 4px 0px #999;display:inline-block;">' +
                                    '<a href="https://shop.gogo198.cn/attachment/'+data['info_file'][i]+'" target="_blank"><img src="https://shop.gogo198.cn/attachment/'+data['info_file'][i]+'" style="width:100px;height:100px;" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" ><p style="text-align:center;margin:0;">点击保存</p></a>\n' +
                                    '</div>\n';
                        }
                        html += '        </div>\n' +
                            '<div class="confirm">\n' +
                            '        <span class="close-but" onClick="fnClose2(1)">关闭</span>\n' +
                            '    </div>';
                        $('.download_box').html(html);
                    }

                    $('.download_box').show();
                    $('.mask').show();
                }
            }
        });
    }

    function fnClose2(){
        $('.download_box').hide();
        $('.mask').hide();
    }

    function exam(id){
        $('#check_id').val(id);
        $('.sign_box').show();
        $('.mask').show();
    }

    //审核
    function fnClose(typ){
        // hui.upToast(msg);
        let check_id = $('#check_id').val();//凭证id
        let remark = $('#remark').val();
        let status = $('#status').val();
        if(typ==1){
            $('.sign_box').hide();
            $('.mask').hide();
        }else if(typ==2){
            $.ajax({
                url:"<?php  echo $this->createMobileUrl('account/bookkeeping');?>",
                type:'POST',
                dataType:'json',
                data:{'op':'socialTax_info','type':4,'id':check_id,'remark':remark,'status':status},
                success:function(json) {
                    if(json.status==1){
                        alert(json.result.msg);
                        $('.sign_box').hide();
                        $('.mask').hide();
                        if(typ==2){
                            setTimeout(function(){
                                window.location.reload();
                            },2000);
                        }
                    }
                }
            });
        }
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>