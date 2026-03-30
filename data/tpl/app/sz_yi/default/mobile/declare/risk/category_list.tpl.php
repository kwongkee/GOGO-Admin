<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
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
    .laytable-cell-1-0-2{height:auto;min-height:auto;}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
                    <ul class="layui-tab-title">
                        <li class="layui-this">同品同重同价</li>
                        <li>同品异重异价</li>
                        <li>异品异重异价</li>
                    </ul>
                    <div class="layui-tab-content">
                        <div class="layui-tab-item layui-show">
                            <table class="layui-hide" id="mainTable"></table>
                        </div>
                        <div class="layui-tab-item">
                            <table class="layui-hide" id="mainTable2"></table>
                        </div>
                        <div class="layui-tab-item">
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
                ,url: "<?php  echo $this->createMobileUrl('declare/risk');?>&op=category_list&pa=1&batch_num=<?php  echo $batch_num;?>&logisticsNo=<?php  echo $logisticsNo;?>"
                ,cellMinWidth: 200
                ,cols: [[
                     {field:'itemName', title: '原始品名'}
                    ,{field:'title', title: '新品名'}
                    ,{field:'grossWeight', title: '重量(Kgs)'}
                    ,{field:'price', title: '单价'}
                    // ,{field:'all_money', title: '总值', templet: function(d){
                    //         var html = '';
                    //         for(var i=0;i<d.all_money.length;i++){
                    //             html += '<div class="layui-table-cell laytable-cell-1-0-2">'+d.all_money[i]+'</div>';
                    //         }
                    //         return html;
                    //   }}
                ]]
                ,page: false
            });

            table.render({
                elem: '#mainTable2'
                ,url: "<?php  echo $this->createMobileUrl('declare/risk');?>&op=category_list&pa=2&batch_num=<?php  echo $batch_num;?>&logisticsNo=<?php  echo $logisticsNo;?>"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'itemName', title: '原始品名'}
                    ,{field:'title', title: '新品名'}
                    ,{field:'grossWeight', title: '重量(Kgs)'}
                    ,{field:'price', title: '单价'}
                    // ,{field:'all_money', title: '总值', templet: function(d){
                    //         var html = '';
                    //         for(var i=0;i<d.all_money.length;i++){
                    //             html += '<div class="layui-table-cell laytable-cell-1-0-2">'+d.all_money[i]+'</div>';
                    //         }
                    //         return html;
                    //   }}
                ]]
                ,page: false
            });

            table.render({
                elem: '#mainTable3'
                ,url: "<?php  echo $this->createMobileUrl('declare/risk');?>&op=category_list&pa=3&batch_num=<?php  echo $batch_num;?>&logisticsNo=<?php  echo $logisticsNo;?>"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'itemName', title: '原始品名'}
                    ,{field:'title', title: '新品名'}
                    ,{field:'grossWeight', title: '重量(Kgs)'}
                    ,{field:'price', title: '单价'}
                    // ,{field:'all_money', title: '总值', templet: function(d){
                    //         var html = '';
                    //         for(var i=0;i<d.all_money.length;i++){
                    //             html += '<div class="layui-table-cell laytable-cell-1-0-2">'+d.all_money[i]+'</div>';
                    //         }
                    //         return html;
                    //   }}
                ]]
                ,page: false
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
                }
            };
        });
    });

    function openWindow(title,id,typ)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //订单概览
            url="<?php  echo $this->createMobileUrl('declare/risk');?>&op=order_list&id="+id;
        }else if(typ==2){
            //交易管理
            url="<?php  echo $this->createMobileUrl('declare/risk');?>&op=transaction_manage&id="+id;
        }
        var index = layer.open({
            type: 2,
            title: title,
            content: url
        });
        layer.full(index);
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>