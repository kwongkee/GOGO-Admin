<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>预提管理</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{
        font-size: 20px;
        line-height: 20px;
        padding-right: 10px;
        vertical-align: middle;
    }
    .layui-layer-adminRight{
        top : 0px !important;
    }
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}

    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    .layui-table-fixed{z-index:999;display:block !important;}
    .layui-table-fixed .layui-table-body{height:100% !important;}

    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
</style>
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-1);">
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
                        <li class="layui-this">已预提未申报</li>
                        <li>已预提被退回</li>
                        <li>已预提已申报</li>
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
                            <!--<div style="height:40px;">-->
                                <!--<div class="main-table-reload-btn" style="margin-bottom: 10px;float: right;">-->
                                    <!--搜索关键词：-->
                                    <!--<div class="layui-inline">-->
                                        <!--<input class="layui-input" placeholder="要搜索的关键词"  id="keywords2" autocomplete="off">-->
                                    <!--</div>-->
                                    <!--<button class="layui-btn layui-btn-normal" data-type="reload2">搜索</button>-->
                                <!--</div>-->
                            <!--</div>-->
                            <table class="layui-hide" id="mainTable2"></table>
                        </div>
                        <div class="layui-tab-item">
                            <!--<div style="height:40px;">-->
                                <!--<div class="main-table-reload-btn" style="margin-bottom: 10px;float: right;">-->
                                    <!--搜索关键词：-->
                                    <!--<div class="layui-inline">-->
                                        <!--<input class="layui-input" placeholder="要搜索的关键词"  id="keywords3" autocomplete="off">-->
                                    <!--</div>-->
                                    <!--<button class="layui-btn layui-btn-normal" data-type="reload3">搜索</button>-->
                                <!--</div>-->
                            <!--</div>-->
                            <table class="layui-hide" id="mainTable3"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'laydate','element'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload
                , table = layui.table;

            table.render({
                elem: '#mainTable'
                ,url: "<?php  echo $this->createMobileUrl('declare/accrual');?>&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'pre_batch_num', title: '预提编号'}
                    ,{field:'withhold_status', title: '状态'}
                    ,{field:'check_remark', title: '原因'}
                    // ,{align:'center', title: '操作',fixed:'right',width:80, templet: function(d){
                    //         return [
                    //             '<a onclick="openWindow('+ "'"+d.gcode+"的信息'" +',' + "'"+ d.gcode +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                    //             // '<a onclick="javascript:del('+"'"+d.pre_batch_num+"'"+');" class="layui-btn layui-btn-xs layui-btn-danger">剔除</a>',
                    //         ].join('');
                    //     } }
                ]]
                ,page: true
            });

            table.render({
                elem: '#mainTable2'
                ,url: "<?php  echo $this->createMobileUrl('declare/accrual');?>&pa=2"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'pre_batch_num', title: '预提编号'}
                    ,{field:'withhold_status', title: '状态'}
                    ,{field:'check_remark', title: '原因'}
                    // ,{align:'center', title: '操作',fixed:'right',width:80, templet: function(d){
                    //         return [
                    //             '<a onclick="openWindow('+ "'"+d.gcode+"的信息'" +',' + "'"+ d.gcode +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                    //             // '<a onclick="javascript:del('+"'"+d.pre_batch_num+"'"+');" class="layui-btn layui-btn-xs layui-btn-danger">剔除</a>',
                    //         ].join('');
                    //     } }
                ]]
                ,page: true
            });

            table.render({
                elem: '#mainTable3'
                ,url: "<?php  echo $this->createMobileUrl('declare/accrual');?>&pa=3"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'pre_batch_num', title: '预提编号'}
                    ,{field:'withhold_status', title: '状态'}
                    ,{field:'check_remark', title: '原因'}
                    // ,{align:'center', title: '操作',fixed:'right',width:80, templet: function(d){
                    //         return [
                    //             '<a onclick="openWindow('+ "'"+d.gcode+"的信息'" +',' + "'"+ d.gcode +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                    //             // '<a onclick="javascript:del('+"'"+d.pre_batch_num+"'"+');" class="layui-btn layui-btn-xs layui-btn-danger">剔除</a>',
                    //         ].join('');
                    //     } }
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
                }
            };

            $('.main-table-reload-btn .layui-btn').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        });
    });

    function openWindow(title,hscode,typ,itemNo)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //查看详情
            var url = "<?php  echo $this->createMobileUrl('hscode/index');?>&op=info2&hscode="+hscode;
            var index = layer.open({
                type: 2,
                title: title,
                content: url,
            });
            layer.full(index);
        }else if(typ==2){
            //上传涉证文件
            var url = "<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=invo_file&pre_batch_num=<?php  echo $batch_num;?>&hscode="+hscode+"&itemNo="+itemNo;
            var index = layer.open({
                type: 2,
                title: title,
                content: url,
                area:["90%","80%"]
            });

        }else if(typ==3){
            //上传涉检文件
            var url = "<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=insp_file&pre_batch_num=<?php  echo $batch_num;?>&hscode="+hscode+"&itemNo="+itemNo;
            var index = layer.open({
                type: 2,
                title: title,
                content: url,
                area:["90%","80%"]
            });
        }

    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>