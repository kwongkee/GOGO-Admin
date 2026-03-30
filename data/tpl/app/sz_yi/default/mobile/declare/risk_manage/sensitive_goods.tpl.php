<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>风险管理</title>
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
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">敏感商品列表</div>
                <div class="layui-card-body" style="padding:0;">
                    <div style="height:40px;">
                        <div class="main-table-reload-btn" style="margin-bottom: 10px;float: right;">
                            搜索关键词：
                            <div class="layui-inline">
                                <input class="layui-input" placeholder="要搜索的关键词"  id="keywords" autocomplete="off">
                            </div>
                            <button class="layui-btn layui-btn-normal" data-type="reload">搜索</button>
                        </div>    
                    </div>
                    <table class="layui-hide" id="mainTable"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
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
                ,url: "<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=sensitiveGoods&pre_batch_num=<?php  echo $batch_num;?>&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'gname', title: '品名'}
                    ,{field:'gcode', title: '编码'}
                    ,{field:'reason', title: '原因'}
                    ,{align:'center', title: '操作',fixed:'right',width:80, templet: function(d){
                            return [
                                '<a onclick="openWindow('+ "'"+d.gcode+"的信息'" +',' + "'"+ d.gcode +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">查看详情</a>',
                    //             '<a onclick="javascript:del('+"'"+d.pre_batch_num+"'"+');" class="layui-btn layui-btn-xs layui-btn-danger">剔除</a>',
                            ].join('');
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

    function openWindow(title,hscode,typ)
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

        }

    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>