<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>询价列表</title>
<style>
    body,.layui-footer{background:<?php  echo $website['color'];?>;}
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .layui-table-cell{padding:0 2px;}
    .laytable-cell-1-0-2{height:auto;min-height:auto;}
    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}

    .layui-layer-hui .layui-layer-content{color:#fff;}
    .email{display:none;}
    .layui-btn-normal{background:<?php  echo $website['color'];?>;color:<?php  echo $website['color_word'];?>;border:1px solid <?php  echo $website['color_word'];?>;}
</style>

<div class="layui-fluid" style="margin-top:15px;">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <a href="javascript:history.back(-1);">
                <div style="display:flex;align-items:center;border: 1px solid #fff;width: fit-content;padding: 8px 15px;border-radius: 7px;background: #8f8b8b;margin-bottom:15px;color:#fff;">
                    <img class="" src="../addons/sz_yi/static/images/back.png" alt="" style="width: 18px;margin-right: 10px;">
                    返回
                </div>
            </a>
            <div class="layui-card">
                <div class="layui-card-header">
                    <div class="layui-btn layui-btn-normal layui-btn-md" onclick="openWindow('./index.php?i=3&c=entry&do=checkprice&m=sz_yi&p=index&op=save_inquiry&buss_id=<?php  echo $id;?>',1)">发起询价</div>
                </div>
                <div class="layui-card-body">
                    <table class="layui-hide" id="mainTable"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
            ,url: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=inquiry_list&pa=1&id=<?php  echo $id;?>"
            ,cellMinWidth: 200
            ,cols: [[
                {field:'buss_name', title: '业务名称'}
                ,{field:'ordersn', title: '订单编号'}
                // ,{field:'platform', title: '订单渠道'}
                ,{field:'statusname', title: '订单状态'}
                ,{field:'createtime', title: '询价时间'}
                ,{align:'center',  title: '操作',fixed:'right',width:120, templet: function(d){
                        return [
                            '<a onclick="openWindow('+"'"+ d.id +"'" +','+"2"+ ');" class="layui-btn layui-btn-primary layui-btn-xs">询价详情</a>',
                            '<a onclick="openWindow('+"'"+ d.id +"'" +','+"3"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">查看报价</a>',
                        ].join('');
                    } }
            ]]
            ,page: true
        });
    });

    function openWindow(url,typ){
        if(typ==1){
            layer.confirm('请选择询价方式', {
                btn: ['模板询价','文本询价']
                // ,btn3: function(index, layero){
                //     //按钮【按钮三】的回调
                //
                // }
            }, function(){
                window.location.href=url+'&type=0';
            }, function(){
                window.location.href=url+'&type=1';
            });
        }else if(typ==2){
            window.location.href="./index.php?i=3&c=entry&do=checkprice&m=sz_yi&p=index&op=inquiry_detail&id="+url;
        }else if(typ==3){
            window.location.href="./index.php?i=3&c=entry&do=checkprice&m=sz_yi&p=index&op=inquiry_quote&id="+url;
        }
    }
</script>