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

<div class="layui-card">
    <div class="layui-card-body">
        <form class="layui-form" action="" lay-filter="component-form-element">
            <input type="hidden" name="ids" id="ids" value="<?php  echo $ids;?>">
            <div class="layui-card">
                <div class="layui-card-header">下载缺省清单 <div class="layui-btn layui-btn-normal layui-btn-sm list_down">清单下载</div></div>
                <div class="layui-card-body" style="border-bottom: 1px solid #f6f6f6;">
                    <div class="layui-upload">
                        <button type="button" class="layui-btn layui-btn-normal" id="upload">选择文件</button><input class="layui-upload-file" type="file" accept=".xlsx,.xls,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name="file">
                    </div>
                </div>
                <div class="layui-card-body">
                    <div class="layui-upload" style="text-align: center;">
                        <button type="button" class="layui-btn" id="uploadAction">提交</button>
                    </div>
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

            upload.render({
                elem:'#upload',
                url:"<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=upload_list&type=<?php  echo $type;?>&pre_batch_num=<?php  echo $batch_num;?>",
                auto:false,
                accept:'file',
                exts:'xls|excel|xlsx'
                ,acceptMime: '.xlsx,.xls,.csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ,bindAction: '#uploadAction'
                ,data: {
                    ids: function(){
                        return $('#ids').val();
                    },
                }
                ,before: function(obj){
                    layer.load(); //上传loading
                }
                ,done: function(res){
                    layer.closeAll('loading'); //关闭loading
                    layer.alert(res.result.msg);
                }
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

        $('.list_down').click(function(){
            let ids = $('#ids').val();
            //导出清单
            window.location.href="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=download_list&type=<?php  echo $type;?>&adjust_method=<?php  echo $adjust_method;?>&ids="+ids;
        });
    });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>