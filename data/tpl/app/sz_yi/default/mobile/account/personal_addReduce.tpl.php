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
    .table thead td{background-color: #ecf6fc !important;}
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
            人员增减列表
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
    </div>

    <!--安排电邮-->
    <div class="mask" style="position: fixed; margin: 0px; padding: 0px;background: rgba(0, 0, 0,0.6); z-index: 999; width: 100%; height: 100%; transition: all 0.2s ease 0s;display:none;left: 0;top: 0;"></div>
    <div class="download_box">

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
                }
            });
        });
        changeDom(1,"<?php  echo $_GPC['date'];?>");
        $('.page_head').find('.left').click(function(){
            window.history.back(-1);
        });
        $('.mask').click(function(){
            $('.sign_box').hide();
            $('.download_box').hide();
            $('.mask').hide();
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
            url: "<?php  echo $this->createMobileUrl('account/manage');?>",
            type: 'POST',
            dataType: 'json',
            data: {'op': 'personal_addReduce', 'typ': index + 1,'date':date},
            success: function (json) {
                if (json.status == 1) {
                    var dat = json.result.data;
                    var html = '';

                    if (index == 0) {
                        //待增减
                        html += '<div class="infos table-responsive">\n' +
                            '            <table class="table table-striped" style="table-layout:fixed;word-break: break-all;text-align: center;">\n' +
                            '                <thead>\n' +
                            '                <tr>\n' +
                            '                    <td>商户</td>\n' +
                            '                    <td>月份</td>\n' +
                            '                    <td>项目</td>\n' +
                            '                    <td>类别</td>\n' +
                            '                    <td>操作</td>\n' +
                            '                </tr>\n' +
                            '                </thead>\n' +
                            '                <tbody>\n';
                            var isshow=0;
                            for(var ii=0;ii<dat['list'].length;ii++){
                                isshow=1;
                                html += '                <tr>\n' +
                                    '                    <td>'+dat['list'][ii].user_name+'</td>\n' +
                                    '                    <td>'+dat['list'][ii].opera_date+'</td>\n' +
                                    '                    <td>'+dat['list'][ii].typeName+'</td>\n' +
                                    '                    <td>'+dat['list'][ii].operatypeName+'</td>\n' +
                                    '                    <td>\n' +
                                    '                       <div class="see" onclick=\'down('+dat['list'][ii].id+')\' id="down'+dat['list'][ii].id+'" style="background:#125526;padding:4px 10px;">下载</div>\n'+
                                    '                    </td>\n' +
                                    '                </tr>\n';
                            }
                            if(isshow==0){
                                html += '<tr><td colspan="5" align="center">暂无信息</td></tr>';
                            }
                        html += '                </tbody>\n' +
                            '            </table>\n' +
                            '        </div>';
                    }else if(index ==1 ){
                        //已增减

                        html += '<div class="infos table-responsive">\n' +
                            '            <table class="table table-striped" style="table-layout:fixed;word-break: break-all;text-align: center;">\n' +
                            '                <thead>\n' +
                            '                <tr>\n' +
                            '                    <td>商户</td>\n' +
                            '                    <td>月份</td>\n' +
                            '                    <td>项目</td>\n' +
                            '                    <td>类别</td>\n' +
                            '                    <td>状态</td>\n' +
                            '                </tr>\n' +
                            '                </thead>\n' +
                            '                <tbody>\n';
                            var isshow=0;
                            for(var ii=0;ii<dat['list'].length;ii++) {
                                isshow=1;
                                html += '                <tr>\n' +
                                    '                    <td>'+dat['list'][ii].user_name+'</td>\n' +
                                    '                    <td>'+dat['list'][ii].opera_date+'</td>\n' +
                                    '                    <td>' + dat['list'][ii].typeName + '</td>\n' +
                                    '                    <td>' + dat['list'][ii].operatypeName + '</td>\n' +
                                    '                    <td>' + dat['list'][ii].statusName + '</td>\n' +
                                    '                </tr>\n';
                            }
                        if(isshow==0){
                            html += '<tr><td colspan="5" align="center">暂无信息</td></tr>';
                        }
                        html += '                </tbody>\n' +
                            '            </table>\n' +
                            '        </div>';
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

</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>