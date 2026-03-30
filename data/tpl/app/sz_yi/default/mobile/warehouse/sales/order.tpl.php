<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH));?>
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .layui-table-body{height:100% !important;}
    .layui-table-fixed{display:block !important;}

    .mainTable2 .layui-table tbody tr{height:70px;}
    .mainTable2 .layui-table-fixed  tbody .layui-table-cell {height:70px;}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-tab layui-col-xs12 layui-tab-brief">
                <ul class="layui-tab-title">
                    <li class="layui-this">原始订单</li>
                    <li>合并订单</li>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <div class="main-table-reload-btn" style="margin-bottom: 10px;">
                            搜索关键词：
                            <div class="layui-inline">
                                <input class="layui-input" placeholder="要搜索的关键词"  id="keywords" autocomplete="off">
                            </div>
                            <button class="layui-btn layui-btn-normal" data-type="reload">搜索</button>
                        </div>
                        <table class="layui-hide" id="mainTable"></table>
                    </div>
                    <div class="layui-tab-item mainTable2">
                        <div class="main-table-reload-btn" style="margin-bottom: 10px;">
                            搜索关键词：
                            <div class="layui-inline">
                                <input class="layui-input" placeholder="要搜索的关键词"  id="keywords2" autocomplete="off">
                            </div>
                            <button class="layui-btn layui-btn-normal" data-type="reload">搜索</button>
                        </div>
                        <table class="layui-hide" id="mainTable2"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload','element'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , upload = layui.upload
                , table = layui.table;

            table.render({
                elem: '#mainTable'
                ,url: "<?php  echo $this->createMobileUrl('warehouse/sales');?>&op=order&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 60, title: '编号'}
                    ,{field:'ordersn', title: '包裹单号'}
                    ,{field:'express_no', title: '快递单号'}
                    ,{field:'nickname', title: '买家名称'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center',  title: '操作',fixed:'right',width:90, templet: function(d){
                            return [
                                '<a onclick="openWindow('+ "'"+d.ordersn+"查看详情'" +',' + "'"+ d.id +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                            ]
                        } }
                ]]
                ,page: true
            });

            table.render({
                elem: '#mainTable2'
                ,url: "<?php  echo $this->createMobileUrl('warehouse/sales');?>&op=order&pa=2"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 60, title: '编号'}
                    ,{field:'ordersn', title: '包裹合并单号'}
                    ,{field:'nickname', title: '买家名称'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center', title: '操作',fixed:'right',width:90, templet: function(d){
                            return [
                                '<p><a onclick="openWindow('+ "'"+d.ordersn+"查看详情'" +',' + "'"+ d.id +"'" +','+"2"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a></p>'+
                                '<p><a onclick="openWindow('+ "'"+d.ordersn+"导出货物'" +',' + "'"+ d.id +"'" +','+"3"+ ');" class="layui-btn layui-btn-success layui-btn-xs">导出货物</a></p>',
                            ]
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
            };

            $('.main-table-reload-btn .layui-btn').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        });
    });

    function openWindow(title,id,typ)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //查看详情
            url="<?php  echo $this->createMobileUrl('warehouse/sales');?>&op=order_detail&id="+id;
        }else if(typ==2){
            //查看详情
            url="<?php  echo $this->createMobileUrl('warehouse/sales');?>&op=merge_order_detail&id="+id;
        }else if(typ==3){
            //导出商品
            window.location.href="<?php  echo $this->createMobileUrl('warehouse/sales');?>&op=export_goods_excel&id="+id;
            // $.ajax({
            //     url: "<?php  echo $this->createMobileUrl('warehouse/sales');?>",
            //     type: "GET",
            //     dataType: "json",
            //     data: {
            //         "op":'export_goods_excel',
            //         "id": id,
            //     },
            //     success: function (res) {
            //         layer.msg(res.msg,{time:3000},function(){
            //             if(res.code==0){
            //
            //             }
            //         })
            //     },
            // });
            return false;
        }
        var index = layer.open({
            type: 2,
            title: title,
            content: url,
            area:['100%','100%']
        });
        // layer.full(index);
    }
</script>