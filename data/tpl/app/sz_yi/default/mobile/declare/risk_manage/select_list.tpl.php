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
            <div class="layui-card">
                <!--<div class="layui-card-header">选择清单数据</div>-->
                <!--<div class="layui-card-body" style="border-bottom: 1px solid #f6f6f6;">-->
                    <div class="layui-form-item">
                        <div class="layui-inline" style="margin-bottom:5px;width:100%;">
                            <label class="layui-form-label" style="padding:9px;width:60px;">获取数据</label>
                            <div class="layui-input-block disf">
                                <select name="adjust_method" id="adjust_method" lay-verify="required" lay-filter="adjust_method">
                                    <?php  if(is_array($method)) { foreach($method as $key => $val) { ?>
                                    <option value="<?php  echo $val;?>"><?php  echo $val;?></option>
                                    <?php  } } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                <!--</div>-->
                <div class="layui-layout-admin">
                    <div class="layui-input-block" style="height: 36px;margin-left:0;text-align:center;">
                        <!--<div class="layui-footer" style="text-align:center;background:#fff;left: 0;">-->
                            <button type="button" class="layui-btn btn1" lay-submit lay-filter="component-form-element">提交</button>
                        <button type="button" class="layui-btn btn2" style="display:none;background:#8a8a8a;" >提交</button>
                        <!--</div>-->
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
            form.render(null, 'component-form-element');

            form.on('submit(component-form-element)', function(data){
                let dataList = {adjust_method:data.field['adjust_method'],pre_batch_num:"<?php  echo $batch_num;?>",op:'select_list',pa:1};
                $.post("<?php  echo $this->createMobileUrl('declare/risk_manage');?>",dataList,function(res){
                    res = JSON.parse(res);
                    if(res.status==-1){
                        layer.alert(res.result.msg);
                    }else if(res.status==1){
                        //跳转到商品列表
                        url="<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=goods_list&pre_batch_num=<?php  echo $batch_num;?>";
                        var index = layer.open({
                            type: 2,
                            title: '<?php  echo $batch_num;?>商品列表',
                            content: url,
                        });
                        layer.full(index);
                        $('.btn1').hide();
                        $('.btn2').show();
                    }
                });
            });
        });

        $('.list_down').click(function(){
            let ids = $('#ids').val();
            //导出清单
            window.location.href="<?php  echo $this->createMobileUrl('declare/report_risk');?>&op=download_list&type=<?php  echo $type;?>&adjust_method=<?php  echo $adjust_method;?>&ids="+ids;
        });
    });
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>