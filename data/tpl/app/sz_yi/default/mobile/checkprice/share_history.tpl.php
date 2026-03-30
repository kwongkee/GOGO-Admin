<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>分享记录</title>
<style>
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
                <div class="layui-card-header">
                    分享记录
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
            ,url: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=share_history&pa=1&id=<?php  echo $id;?>"
            ,cellMinWidth: 200
            ,cols: [[
                {field:'id', width: 80, title: '编号'}
                ,{field:'realname', title: '商户名称'}
                ,{field:'email', title: '电子邮箱'}
                ,{field:'phone', title: '手机号码'}
                ,{field:'createtime', title: '分享日期'}
                // ,{align:'center',  title: '操作',fixed:'right',width:80, templet: function(d){
                //         if(d.status==0){
                //             return [
                //                 '<a onclick="openWindow('+"'"+ d.id +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">修改</a>',
                //             ].join('');
                //         }else{
                //             return [
                //                 '<a onclick="openWindow('+"'"+ d.id +"'" +','+"2"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">物流管理</a>',
                //             ].join('');
                //         }
                //     } }
            ]]
            ,page: true
        });
    });


    function openWindow(id,typ)
    {
        if(typ==1){
            //修改订单
            window.location.href="./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=buyer&op=edit_order&id="+id;
        }else if(typ==2){
            //编辑
            window.location.href="./index.php?i=3&c=entry&do=behalf&m=sz_yi&p=merchant&op=save_product&id="+id;
        }else if(typ==3){
            //删除
            layer.confirm('确认删除？', {
                btn: ['确认','取消'] //按钮
            }, function() {
                $.ajax({
                    url:"./index.php?i=3&c=entry&do=behalf&p=merchant&m=sz_yi&op=del_product",
                    method:'post',
                    data:{'id':id},
                    dataType:'JSON',
                    success:function(res2){
                        layer.closeAll('loading');
                        layer.msg(res2.result.msg,{time:3000}, function () {
                            if(res2.status == 0)
                            {
                                window.location.reload();
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:3000});
                    }
                });
            },function(){
                layer.closeAll('loading');
            });
        }
    }
</script>