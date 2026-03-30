<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title><?php  echo $title;?></title>
<style>
    body{background:#f8f8f8;}
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}
    .laytable-cell-1-0-2{height:auto;min-height:auto;}
    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .center{text-align: center;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}

    .box{display:inline-block;width: 49%;text-align: center;margin-bottom:20px;}
    .box .box2{font-size: 15px;width: 80%;margin: 0 auto;box-shadow: 0px 0px 2px #a7a5a5;border-radius: 5px;box-sizing: border-box;padding:15px 20px;}

    .layui-layer-hui .layui-layer-content{color:#fff;}
    #select_edit{padding:10px;box-sizing: border-box;}
    #select_edit .pick_div{padding:10px 20px;box-sizing: border-box;text-align: center;border-radius: 7px;color:#fff;font-size:15px;}
    #select_edit .pick_1{background:#1790FF;}
    #select_edit .pick_2{background:#009688;margin:15px 0;}
    #select_edit .pick_3{background:#F7931E;}
</style>
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
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief" style="margin-top:15px;">
                    <ul class="layui-tab-title">
                        <li class="layui-this">待审批</li>
                        <li>已退回</li>
                        <li>已拒绝</li>
                        <li>已同意</li>
                    </ul>
                    <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show">
                            <!--<div style="height:40px;">-->
                            <!--<div class="main-table-reload-btn" style="margin-bottom: 10px;float: right;">-->
                            <!--搜索关键词：-->
                            <!--<div class="layui-inline">-->
                            <!--<input class="layui-input" placeholder="要搜索的关键词"  id="keywords" autocomplete="off">-->
                            <!--</div>-->
                            <!--<button class="layui-btn layui-btn-normal" data-type="reload">搜索</button>-->
                            <!--</div>-->
                            <!--</div>-->
                            <table class="layui-hide" id="mainTable"></table>
                        </div>
                        <div class="layui-tab-item">
                            <table class="layui-hide" id="mainTable2"></table>
                        </div>
                        <div class="layui-tab-item">
                            <table class="layui-hide" id="mainTable3"></table>
                        </div>
                        <div class="layui-tab-item">
                            <table class="layui-hide" id="mainTable4"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="remark_box" style="display:none;">
    <form class="layui-form" action="" lay-filter="component-form-demo1">
        <input type="number" name="approval" value="1" style="display: none;">
        <input type="number" name="id" value="" style="display: none;">
        <div class="layui-col-xs12" style="padding:15px;box-sizing: border-box;">
            <div class="layui-col-xs12">
                <select name="my_select" id="my_select" lay-verify="required" lay-filter="my_select">
                    <option value="">请选择审批操作</option>
                    <option value="1">退回</option>
                    <option value="2">拒绝</option>
                    <option value="3">同意</option>
                </select>
            </div>
            <div class="layui-col-xs12 my_remark" style="display:none;margin-top:15px;">
                <textarea name="remark" id="remark" class="layui-textarea" placeholder="请输入退回/拒绝原因"></textarea>
            </div>
        </div>
        <div class="layui-col-xs12" style="text-align:center;margin-top:30px;">
            <button class="layui-btn sub" lay-submit="" lay-filter="component-form-demo1">立即提交</button>
            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
        </div>
    </form>
</div>

<script>
    $(function() {
        $('.page_head').find('.left').click(function () {
            window.history.back(-1);
        });

        layui.use(['layer', 'form', 'table', 'upload', 'laydate','element'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload
                , table = layui.table;

            form.render(null, 'component-form-demo1');

            table.render({
                elem: '#mainTable'
                ,url: "./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=my_approval&pa=0"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 80, title: '编号'}
                    ,{field:'staff_name', title: '人员名称'}
                    ,{field:'name', title: '事项名称'}
                    ,{field:'status', title: '事项状态'}
                    ,{field:'remark', title: '原因'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center',  title: '操作',fixed:'right',width:100, templet: function(d){
                            if(d.status=='待审批'){
                                return [
                                    '<a onclick="openWindow('+"'"+ d.id +"'" +','+"1"+ ','+"'"+d.name+"'"+');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                                    '<a onclick="openWindow('+"'"+ d.id +"'" +','+"2"+ ','+"'"+d.name+"'"+');" class="layui-btn layui-btn-success layui-btn-xs">立即审批</a>',
                                    // '<a  class="layui-btn layui-btn-xs layui-btn-danger" onclick="openWindow('+"'"+ d.id +"'" +','+"2"+');">拒绝</a>',
                                    // '<a  class="layui-btn layui-btn-xs layui-btn-success" onclick="openWindow('+"'"+ d.id +"'" +','+"3"+');">同意</a>',
                                ].join('');
                            }else{
                                return ['无可操作'].join('');
                            }
                        } }
                ]]
                ,page: true
            });

            table.render({
                elem: '#mainTable2'
                ,url: "./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=my_approval&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 80, title: '编号'}
                    ,{field:'staff_name', title: '人员名称'}
                    ,{field:'name', title: '事项名称'}
                    ,{field:'status', title: '事项状态'}
                    ,{field:'remark', title: '原因'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center',  title: '操作',fixed:'right',width:100, templet: function(d){
                            if(d.status=='已退回'){
                                return [
                                    '<a onclick="openWindow('+"'"+ d.id +"'" +','+"1"+ ','+"'"+d.name+"'"+','+"1"+');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                                ].join('');
                            }else{
                                return ['无可操作'].join('');
                            }
                        } }
                ]]
                ,page: true
            });

            table.render({
                elem: '#mainTable3'
                ,url: "./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=my_approval&pa=2"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 80, title: '编号'}
                    ,{field:'staff_name', title: '人员名称'}
                    ,{field:'name', title: '事项名称'}
                    ,{field:'status', title: '事项状态'}
                    ,{field:'remark', title: '原因'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center',  title: '操作',fixed:'right',width:100, templet: function(d){
                            if(d.status=='已拒绝'){
                                return [
                                    '<a onclick="openWindow('+"'"+ d.id +"'" +','+"1"+ ','+"'"+d.name+"'"+','+"1"+');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                                ].join('');
                            }else{
                                return ['无可操作'].join('');
                            }
                        } }
                ]]
                ,page: true
            });

            table.render({
                elem: '#mainTable4'
                ,url: "./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=my_approval&pa=3"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 80, title: '编号'}
                    ,{field:'staff_name', title: '人员名称'}
                    ,{field:'name', title: '事项名称'}
                    ,{field:'status', title: '事项状态'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center',  title: '操作',fixed:'right',width:100, templet: function(d){
                            if(d.status=='已同意'){
                                return [
                                    '<a onclick="openWindow('+"'"+ d.id +"'" +','+"1"+ ','+"'"+d.name+"'"+','+"1"+');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                                ].join('');
                            }else{
                                return ['无可操作'].join('');
                            }
                        } }
                ]]
                ,page: true
            });

            var $ = layui.$, active = {
                reload: function(){
                    //执行重载
                    table.reload('mainTable', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        ,where: {
                            keywords: $("#keywords").val()
                        }
                    });
                },
                reload2: function(){
                    //执行重载
                    table.reload('mainTable2', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        ,where: {
                            keywords: $("#keywords2").val()
                        }
                    });
                },
                reload3: function(){
                    //执行重载
                    table.reload('mainTable3', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        ,where: {
                            keywords: $("#keywords3").val()
                        }
                    });
                },
                reload4: function(){
                    //执行重载
                    table.reload('mainTable4', {
                        page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        ,where: {
                            keywords: $("#keywords4").val()
                        }
                    });
                }
            };

            $('.main-table-reload-btn .layui-btn').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });

            form.on('select(my_select)',function(data){
                let val = data.value;

                if(val==3){
                    $('.my_remark').hide();
                }else{
                    $('.my_remark').show();
                }
            });

            /* 监听提交 */
            form.on('submit(component-form-demo1)', function(data){

                // if(data.field['form_val'][0] == '' || typeof(data.field['form_val'][0]) == 'undefined'){
                //     layer.msg('非常抱歉，您暂未有该事项的发起权限，请联系管理员添加！');
                //     return false;
                // }
                // console.log(data.field);return false;
                $.ajax({
                    url:"./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=my_approval",
                    method:'post',
                    data:data.field,
                    dataType:'JSON',
                    success:function(res){
                        layer.msg(res.msg,{time:3000}, function () {
                            if(res.code == 0)
                            {
                                window.location.reload();
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:3000});
                    }
                });
                return false;
            });
        });
    });

    function openWindow(id,typ,name='',is_check=0)
    {
        var $ = layui.$
            , layer = layui.layer;

        if(typ==1){
            //查看详情
            var url='';
            if(is_check==0){
                url = './index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=approval_detail&is_copy=0&id='+id;
            }else if(is_check==1){
                url = './index.php?i=3&c=entry&do=approval&m=sz_yi&p=send&op=approval_detail&is_copy=2&id='+id;
            }
            layer.open({
                type:2,
                title:'查看详情['+name+']',
                area:['100%','100%'],
                content:url
            });
        }
        else if(typ==2){
            //审批操作
            $('#remark_box').find('input[name="id"]').val(id);
            layer.open({
                type:1,
                title:name,
                area:['90%','55%'],
                content:$('#remark_box')
            });
        }
        // else if(typ==3){
        //     //同意
        //     layer.confirm('确认同意吗？',function(){
        //         $.ajax({
        //             url:"./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=termination",
        //             method:'post',
        //             data:{'id':id},
        //             dataType:'JSON',
        //             success:function(res) {
        //                 layer.closeAll('loading');
        //                 layer.msg(res.msg,{time:3000}, function () {
        //                     if (res.code == 0) {
        //                         window.location.reload();
        //                     }
        //                 });
        //             }
        //         });
        //     });
        // }
    }

</script>