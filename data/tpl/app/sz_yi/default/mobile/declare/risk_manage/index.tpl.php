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
                ,url: "<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=display&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    {field:'pre_batch_num', title: '预报编号'}
                    ,{field:'createtime', title: '创建时间'}
                    ,{align:'center', title: '操作',fixed:'right',width:180, templet: function(d){
                            var content='';
                            if(d.withhold_status==0 || d.withhold_status==1){
                                content+='<a onclick="openWindow('+ "'"+d.pre_batch_num+"选择清单信息'" +',' + "'"+ d.pre_batch_num +"'" +','+"1"+ ');" class="layui-btn layui-btn-normal layui-btn-xs">商品列表</a><a onclick="openWindow('+ "'"+d.pre_batch_num+"确认申报'" +',' + "'"+ d.pre_batch_num +"'" + ','+"2"+');" class="layui-btn layui-btn-xs layui-btn-normal">申请预提</a>';
                            }else if(d.withhold_status==2){
                                content+='<a onclick="javascript:;" class="layui-btn layui-btn-xs layui-btn">已预提，未申报</a>';
                            }else if(d.withhold_status==3){
                                content+='<a onclick="javascript:;" class="layui-btn layui-btn-xs layui-btn" style="background:#ff2222;">已预提，被退回</a>';
                            }else if(d.withhold_status==4){
                                content+='<a onclick="javascript:;" class="layui-btn layui-btn-xs layui-btn" style="background:#009619;">已预提，已申报</a>';
                            }
                            return content;
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

    function openWindow(title,pre_batch_num,typ)
    {
        var layer = layui.layer
           ,table = layui.table;
        var url = '';
        if(typ==1){
            //选择清单数据
            //首先判断是否已选择
            let dataList={op:'is_select_glist',batch_num:pre_batch_num};
            $.post("<?php  echo $this->createMobileUrl('declare/risk_manage');?>",dataList,function(res){
                res = JSON.parse(res);
                if(res.status==1){
                    //已选择
                    url="<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=goods_list&pre_batch_num="+pre_batch_num;
                    var index = layer.open({
                        type: 2,
                        title: pre_batch_num+"商品列表",
                        content: url,
                        // area:['80%','40%']
                    });
                    layer.full(index);
                }else if(res.status==-1){
                    //未选择
                    url="<?php  echo $this->createMobileUrl('declare/risk_manage');?>&op=select_list&pre_batch_num="+pre_batch_num;
                    var index = layer.open({
                        type: 2,
                        title: title,
                        content: url,
                        // area:['80%','40%']
                    });
                    layer.full(index);
                }
            });
        }else if(typ==2){
            //申请预提
            layer.confirm('确认申请预提?',{
                btn:['确认','取消']
            },function(){
                url="<?php  echo $this->createMobileUrl('declare/risk_manage');?>";
                let data = {op:'declare',pre_batch_num:pre_batch_num};
                $.post(url,data,function(res){
                    res = JSON.parse(res);
                    layer.msg(res.result.msg,{time:3000},function(){
                        if(res.status==1){
                            table.reload('mainTable', {
                                page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                                ,where: {
                                    keywords: $("#keywords").val()
                                }
                            });
                        }
                    });
                });
                return false;
            },function(){

            });
        }

    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>