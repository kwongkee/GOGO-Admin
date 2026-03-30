<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH));?>
<style>
    .banner img {
        width: 100%;
    }

    div {
        overflow: visible;
    }

    .info_box_top {
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
    }

    .info_box {
        margin-top: 10px;
        float: left;
        width: 100%;
    }

    .info_box .layui-col-xs12 {
        background: #fff;
    }

    .info_search_box .order_head {
        padding: 10px;
        box-sizing: border-box;
        background: #1790FF;
        color: #fff;
    }

    .info_search_box .disf {
        padding: 10px;
        box-sizing: border-box;
        color: #666;
    }

    .info_box .title {
        color: #666;
        padding: 10px 10px;
        box-sizing: border-box;
    }

    .info_box .val {
        padding: 10px 10px;
        box-sizing: border-box;
    }

    .info_box .line {
        width: 100%;
        height: 1px;
        background: #eee;
    }

    .info_box .info_box_bottom {
        border-top: 1px solid #eee;
        padding: 10px;
        box-sizing: border-box;
    }

    .info_box .info_box_bottom .btn_desc {
        color: #999;
    }

    .info_box .info_box_bottom .layui-col-xs4 {
        text-align: right;
    }

    .info_box .title_bar {
        text-align: center;
        padding: 10px 0;
        box-sizing: border-box;
        border-bottom: 1px solid #eee;
        color: #fff;
        background: #1790FF;
    }

    /**layui框架**/
    .layui-icon-ok:before {
        content: "√"
    }

    .layui-form-checkbox {
        width: 18px;
        height: 18px;
        line-height: 18px;
        padding-right: 18px;
        margin-right: 2px;
    }

    .layui-form-checkbox i {
        width: 18px;
        height: 18px;
        border-left: 1px solid #d2d2d2;
    }

    .layui-btn-normal {
        background: #1790FF;
    }

    .layui-layer-hui .layui-layer-content {
        color: #fff;
    }
</style>

<div class="layui-col-xs12">
    <div class="info_search">
        <?php  if(empty($list)) { ?>
        <div class="layui-col-xs12" style="text-align:center;">
            <img src="../addons/sz_yi/static/warehouse/no_log.png" alt="" style="width:120px;">
            <p>暂无记录！</p>
        </div>
        <?php  } else { ?>
        <?php  if(is_array($list)) { foreach($list as $k => $v) { ?>
        <div class="info_box info_search_box">
            <div class="layui-col-xs12 order_head">
                包裹单号：<?php  echo $v['ordersn'];?>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                买家昵称：<?php  echo $v['user_name'];?>(ID:<?php  echo $v['user_id'];?>)
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                联系电话：<?php  echo $v['mobile'];?>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                包裹状态：<span style="color:#ff2222;"><?php  echo $v['order_status'];?></span>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                取货方式：<?php  echo $v['method_name'];?>
            </div>
            <?php  if(!empty($v['merge_info']['remark']) && empty($v['merge_info']['opera_id'])) { ?>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                买家备注：<?php  echo $v['merge_info']['remark'];?>
            </div>
            <?php  } ?>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                创建时间：<?php  echo date('Y-m-d H:i',$v['createtime'])?>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12" style="padding:15px 10px;box-sizing: border-box;text-align: right;">
                <div class="layui-btn layui-btn-primary" style="border:1px solid #1790FF;color:#1790FF;" onclick="chayan(<?php  echo $v['id'];?>,'<?php  echo $v['ordersn'];?>',<?php  echo $v['status2'];?>,<?php  echo $v['method_id'];?>)">立即发货
                </div>
            </div>
        </div>
        <?php  } } ?>
        <?php  } ?>
    </div>
</div>

<script>
    layui.use(['layer', 'jquery', 'form', 'element', 'upload'], function () {
        var layer = layui.layer
            , $ = layui.jquery
            , form = layui.form
            , upload = layui.upload;

    });
    function chayan(id,ordersn,status2,method_id) {
        var layer = layui.layer, $ = layui.$;

        layer.open({
            type: 2,
            title:"立即发货["+ordersn+"]",
            area: ['100%', '100%'], //宽高
            content: './index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=show_exWarehouse_box&orderid='+id+'&ordersn='+ordersn+'&status2='+status2+'&method_id='+method_id
        });
        // $('.inspection_box').find('input[name="id"]').val(id);
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>