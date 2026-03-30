<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>操作员配置</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #1E9FFF;color:#fff; }
    .required{color: red;font-size: 1.3rem;right: 3px;position: relative;top: 7px;}
    .icon-right{font-size: 20px;line-height: 20px;padding-right: 10px;vertical-align: middle;}
    .layui-layer-adminRight{top : 0px !important;}
    .layui-layer-btn .layui-layer-btn0{border-color: #F7931E!important;background-color: #F7931E!important;}
    .disf{display:flex;align-items: center;justify-content: space-evenly;}
    .layui-tab-title li{min-width:50px;}
    .layui-table img{max-width:50px;}
    .layui-upload-list img{max-width:50px;}
    .layui-form-radio i {top: 0;width: 16px;height: 16px;line-height: 16px;border: 1px solid #d2d2d2;font-size: 12px;border-radius: 2px;background-color: #fff;color: #fff !important;}
    .layui-form-radioed i {position: relative;width: 18px;height: 18px;border-style: solid;background-color: #5FB878;color: #5FB878 !important;}
    /* 使用伪类画选中的对号 */
    .layui-form-radioed i::after, .layui-form-radioed i::before {content: "";position: absolute;top: 8px;left: 5px;display: block;width: 12px;height: 2px;border-radius: 4px;background-color: #fff;-webkit-transform: rotate(-45deg);transform: rotate(-45deg);}
    .layui-form-radioed i::before {position: absolute;top: 10px;left: 2px;width: 7px;transform: rotate(-135deg);}
    .element_display{display:none;}
    .search_box{position:relative;width: 100%;}
    .search_refuse{position: absolute;background: #fff;box-shadow: 0px 0px 15px 2px #bdbcbc;width: 100%;font-size: 14px;padding: 5px 0px;overflow: scroll;height: 120px;z-index: 9;}
    .license input{display:inline-block;}

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
                <!--<div class="disf">-->
                    <div class="layui-btn layui-btn-sm layui-btn-normal" onclick="openWindow('添加人员','',1)">添加人员</div>
                <!--</div>-->
                <div class="layui-card-body" style="padding:0;">
                    <div style="height:40px;">
                        <div class="main-table-reload-btn" style="margin-bottom: 10px;float: right;">
                            搜索手机号：
                            <div class="layui-inline">
                                <input class="layui-input" placeholder="要搜索的手机号"  id="keywords" autocomplete="off">
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
            var timer;//定义一个计时器

            table.render({
                elem: '#mainTable'
                ,url: "<?php  echo $this->createMobileUrl('enterprise/config');?>&op=display&pa=1"
                ,cellMinWidth: 200
                ,cols: [[
                    // {type:'checkbox','width':40,'fixed':'left'}
                    {field:'job_name',width:100, title: '岗位名称'}
                    ,{field:'name',width:100, title: '姓名'}
                    ,{field:'mobile',width:150, title: '手机号'}
                    ,{align:'center', width:100, title: '操作',fixed:'right', templet: function(d){
                            return [
                                // '<a onclick="openWindow('+ "'"+d.name+"修改'" +',' + "'"+ d.id +"'" +','+2+');" class="layui-btn layui-btn-normal layui-btn-xs">修改</a>',
                                '<a onclick="openWindow('+ "'"+d.name+"删除'" +',' + "'"+ d.id +"'" +','+3+');" class="layui-btn layui-btn-danger layui-btn-xs">删除</a>',
                            ].join('');
                        }}
                ]]
                ,page: true
                ,done : function(res, curr, count) {
                    // $('.layui-table').rowspan(3);
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

            $('.main-table-reload-btn .layui-btn').on('click', function(){
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
        });
    });

    function openWindow(title,id,typ){
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //添加
            url="<?php  echo $this->createMobileUrl('enterprise/config');?>&op=save_info&type=1";
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }else if(typ==2){
            //修改
            url="<?php  echo $this->createMobileUrl('enterprise/config');?>&op=save_info&type=2&id="+id;
            var index = layer.open({
                type: 2,
                title: title,
                content: url
            });
            layer.full(index);
        }else if(typ==3){
            //删除
            layer.confirm('确认删除吗？',{
                btn:['确认','取消']
            }, function(index,layero){
                //确认
                $.ajax({
                    url:"<?php  echo $this->createMobileUrl('enterprise/config')?>",
                    type:"GET",
                    data:{
                        'op':'del_staff',
                        'id':id
                    },
                    dataType:"json",
                    success:function (res) {
                        layer.msg(res.result.msg, {time: 2000}, function () {
                            if (res.status == 1) {
                                window.location.reload();
                            }
                        });
                    }
                });
            }, function(index){

            });
        }
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
