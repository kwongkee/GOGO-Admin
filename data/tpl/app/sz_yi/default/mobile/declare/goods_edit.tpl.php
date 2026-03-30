<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>商品编辑</title>
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
    .disf{display:flex;align-items: center;justify-content: space-evenly;}
    .layui-tab-title li{min-width:50px;}
    .layui-table img{max-width:50px;}
    .layui-upload-list img{max-width:50px;}
    .layui-form-label{padding:9px 0;width:90px;}
</style>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">商品编辑</div>
        <div class="layui-card-body" style="padding: 0px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="hidden" name="id" value="<?php  echo $data['id'];?>">

                <div class="layui-form-item">
                    <label class="layui-form-label">企业商品货号</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="itemNo" lay-verify="required" placeholder="请输入商品货号" autocomplete="off" class="layui-input" value="<?php  echo $data['itemNo'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商品名称</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="itemName" lay-verify="required" placeholder="请输入名称" autocomplete="off" class="layui-input" value="<?php  echo $data['itemName'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商品编码</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="gcode" lay-verify="required" placeholder="请输入商品编码" autocomplete="off" class="layui-input" value="<?php  echo $data['gcode'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">币制</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="currency" lay-verify="required" placeholder="请输入币制" autocomplete="off" class="layui-input" value="<?php  echo $data['currency'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">申报数量</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="qty" lay-verify="required" placeholder="请输入申报数量" autocomplete="off" class="layui-input" value="<?php  echo $data['qty'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">法定数量</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="qty1" lay-verify="required" placeholder="请输入法定数量" autocomplete="off" class="layui-input" value="<?php  echo $data['qty1'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">规格型号</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="gmodel" lay-verify="required" placeholder="请输入规格型号" autocomplete="off" class="layui-input" value="<?php  echo $data['gmodel'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">FOB单价</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="price" lay-verify="required" placeholder="请输入FOB单价" autocomplete="off" class="layui-input" value="<?php  echo $data['price'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">FOB总价</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="totalPrice" lay-verify="required" placeholder="请输入FOB总价" autocomplete="off" class="layui-input" value="<?php  echo $data['totalPrice'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">收款金额</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="charge" lay-verify="required" placeholder="请输入收款金额" autocomplete="off" class="layui-input" value="<?php  echo $data['charge'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">到账时间</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="chargeDate" lay-verify="required" placeholder="请输入到账时间" id="ie_date" autocomplete="off" class="layui-input" value="<?php  echo $data['chargeDate'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">物流运单编号</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="logisticsNo" lay-verify="required" placeholder="请输入物流运单编号" autocomplete="off" class="layui-input" value="<?php  echo $data['logisticsNo'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">运费</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="freight" lay-verify="required" placeholder="请输入运费" autocomplete="off" class="layui-input" value="<?php  echo $data['freight'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">保价费</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="insuredFee" lay-verify="required" placeholder="请输入保价费" autocomplete="off" class="layui-input" value="<?php  echo $data['insuredFee'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">条形码</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="barCode" lay-verify="required" placeholder="请输入条形码，为空填写“无”" autocomplete="off" class="layui-input" value="<?php  echo $data['barCode'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">毛重</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="grossWeight" lay-verify="required" placeholder="请输入毛重" autocomplete="off" class="layui-input" value="<?php  echo $data['grossWeight'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">净重</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="netWeight" lay-verify="required" placeholder="请输入净重" autocomplete="off" class="layui-input" value="<?php  echo $data['grossWeight'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">件数</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="packNo" lay-verify="required" placeholder="请输入件数" autocomplete="off" class="layui-input" value="<?php  echo $data['packNo'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">商品信息</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="text" name="goodsInfo" lay-verify="required" placeholder="请输入商品信息" autocomplete="off" class="layui-input" value="<?php  echo $data['goodsInfo'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">申报计量单位</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="unit" lay-verify="required" placeholder="请输入申报计量单位" autocomplete="off" class="layui-input" value="<?php  echo $data['unit'];?>">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">法定计量单位</label>
                    <div class="layui-input-block layui-select-fscon">
                        <input type="number" name="unit1" lay-verify="required" placeholder="请输入法定计量单位" autocomplete="off" class="layui-input" value="<?php  echo $data['unit1'];?>">
                    </div>
                </div>

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo1">立即提交</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
            var  $ = layui.$
                ,layer = layui.layer
                , form = layui.form
                ,element = layui.element
                ,laydate = layui.laydate
                ,upload = layui.upload
                , table = layui.table;

            laydate.render({
                elem: '#ie_date'
                ,format: 'yyyyMMdd'
            });

            form.render(null, 'component-form-demo1');
            // element.render('breadcrumb', 'breadcrumb');

            /* 监听提交 */
            form.on('submit(component-form-demo1)', function(data){

                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('declare/index');?>&op=goods_edit&id=<?php  echo $id;?>",
                    method:'post',
                    data:data.field,
                    dataType:'JSON',
                    success:function(res){
                        layer.msg(res.result.msg,{time:2000}, function () {
                            if(res.status == 1)
                            {
                                var index = parent.layer.getFrameIndex(window.name);
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:2000});
                    }
                });
                return false;
            });
        });
    });

    function openWindow(title,url)
    {
        var layer = layui.layer;
        var index = layer.open({
            type: 2,
            title: title,
            content: url
        });
        layer.full(index);
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>