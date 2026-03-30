<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH));?>
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">结算列表</div>
                <div class="layui-card-body" style="padding:0;">
                    <table class="layui-hide" id="mainTable"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , upload = layui.upload
                , table = layui.table;

            table.render({
                elem: '#mainTable'
                ,url: "<?php  echo $this->createMobileUrl('warehouse/sales');?>&op=settlement&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    // {field:'id', width: 80, title: '编号'}
                    {field:'ordersn', title: '订单号'}
                    ,{field:'name', title: '买家名称'}
                    ,{field:'money', title: '支付金额(HK$)'}
                    ,{field:'paytype', title: '支付方式'}
                    ,{field:'payitem', title: '支付条目'}
                    ,{field:'createtime', title: '支付时间'}
                    // ,{align:'center',  title: '操作', templet: function(d){
                    //         return [
                    //             '<a onclick="openWindow('+ "'"+d.pre_batch_num+"新增清单'" +',' + "'"+ d.id +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">新增清单</a>',
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
                }
            };
        });
    });

    function openWindow(title,id,typ)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //新增清单
            url="<?php  echo $this->createMobileUrl('declare/index');?>&op=list_add&id="+id;
        }else if(typ==2){
            //修改清单
            url="<?php  echo $this->createMobileUrl('declare/index');?>&op=list_edit&id="+id;
        }else if(typ==3){
            //修改提单
            url="<?php  echo $this->createMobileUrl('declare/index');?>&op=lading_edit&id="+id;
        }
        var index = layer.open({
            type: 2,
            title: title,
            content: url
        });
        layer.full(index);
    }
</script>
