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
        <form class="layui-form" lay-filter="component-form-element1">
            <div class="layui-col-md12">
                <table class="layui-table">
                    <tr>
                        <td>选择管理</td>
                        <td>
                            <select name="manage_id" id="manage_id">
                                <option value="1">货值管理</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <div class="layui-form-item" style="margin-top: 15px;text-align: center;">
                    <button class="layui-btn layui-btn-normal submit" lay-submit="" lay-filter="component-form-element" style="background:#F7931E !important;">确定</button>
                </div>
            </div>
        </form>
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
            form.render(null, 'component-form-element1');

            form.on('submit(component-form-element)', function(data){
                if(data.field['manage_id']==1){
                    openWindow('货值管理',1);
                }
                return false;
            });
        });
    });

    function openWindow(title,typ)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //货值管理
            url="<?php  echo $this->createMobileUrl('declare/risk');?>&op=value_manage&id=<?php  echo $id;?>";
        }else if(typ==2){
            //
            url="<?php  echo $this->createMobileUrl('declare/risk');?>&op=transaction_manage&id="+id;
        }
        var index = parent.layer.open({
            type: 2,
            title: title,
            content: url
        });
        var index2=parent.layer.getFrameIndex(window.name); //获取当前窗口的name
        parent.layer.close(index2);
        parent.layer.full(index);
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>