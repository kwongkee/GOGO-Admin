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
                <div class="layui-card-header">客户列表</div>
                <div class="layui-card-body" style="padding:0;">
                    <div class="main-table-reload-btn" style="margin-bottom: 10px;">
                        搜索关键词：
                        <div class="layui-inline">
                            <input class="layui-input" placeholder="要搜索的关键词"  id="keywords" autocomplete="off">
                        </div>
                        <button class="layui-btn layui-btn-normal" data-type="reload">搜索</button>
                    </div>
                    <table class="layui-hide" id="mainTable"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , laydate = layui.laydate
                , upload = layui.upload
                , table = layui.table;

            table.render({
                elem: '#mainTable'
                ,url: "<?php  echo $this->createMobileUrl('warehouse/sales');?>&op=customer&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 80, title: '编号'}
                    ,{field:'name', title: '客户名称'}
                    ,{field:'realname', title: '真实名称'}
                    ,{field:'email', title: '邮箱'}
                    ,{field:'mobile', title: '手机号'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center',  title: '操作',fixed:'right',width:90, templet: function(d){
                            return [
                                '<a onclick="openWindow('+ "'"+d.name+"查看详情'" +',' + "'"+ d.id +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
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
                }
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
            url="<?php  echo $this->createMobileUrl('warehouse/sales');?>&op=customer_detail&id="+id;
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