<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>预报列表</title>
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
                <div class="layui-card-header">预报列表</div>
                <div class="layui-card-body" style="padding:0;">
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
                ,url: "<?php  echo $this->createMobileUrl('declare/index');?>&op=pre_declare_list&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'id', width: 80, title: '编号'}
                    ,{field:'pre_batch_num', title: '预报编号'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center',  title: '操作', templet: function(d){
                            return [
                                '<a onclick="openWindow('+ "'"+d.pre_batch_num+"新增清单'" +',' + "'"+ d.id +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">新增清单</a>',
                                '<a  class="layui-btn layui-btn-xs layui-btn-normal" onclick="openWindow('+"'"+ d.pre_batch_num +"'"+','+"'"+ d.id +"'" +','+"2"+');">修改清单</a>',
                                '<a onclick="openWindow('+ "'"+d.pre_batch_num+"修改提单'" +',' + "'"+ d.id +"'" + ','+"3"+');" class="layui-btn layui-btn-xs layui-btn-normal">修改提单</a>',
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
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>