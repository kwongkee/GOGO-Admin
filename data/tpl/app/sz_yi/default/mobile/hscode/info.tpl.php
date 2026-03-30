<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>hsCode列表</title>
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

    .red{color:red;}
    .green{color:green;}
    .inline-block{width:48%;}
    .disf{display:flex;align-items: baseline;justify-content: space-between;}
    .layui-card-header{padding-right:0;}
</style>
<!--<div class="page_head">-->
    <!--<div class="left" onclick="javascript:window.history.back(-1);">-->
        <!--<div class="back"></div>-->
        <!--<div style="font-size:15px;padding-top:2px;">返回</div>-->
    <!--</div>-->
<!--</div>-->
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header"><?php  echo $list['hscode'];?>的基本信息</div>
                <div class="layui-card-body" style="padding:0;">
                    <!--<div class="main-table-reload-btn" style="margin-bottom: 10px;">-->
                        <!--搜索关键词：-->
                        <!--<div class="layui-inline">-->
                            <!--<input class="layui-input" placeholder="要搜索的关键词或hscode"  id="keywords" autocomplete="off">-->
                        <!--</div>-->
                        <!--<button class="layui-btn layui-btn-normal" data-type="reload">搜索</button>-->
                    <!--</div>-->
                    <table class="layui-table layui-form  layui-table-view" id="mainTable">
                        <tbody style="width:100%;">
                            <?php  if(is_array($list['basic_info'])) { foreach($list['basic_info'] as $k => $vo) { ?>
                            <tr>
                                <td><?php  echo $vo['0'];?></td>
                                <td class="<?php  if($vo['1']=='作废') { ?>red<?php  } ?> <?php  if($vo['1']=='正常') { ?>green<?php  } ?>"><?php  echo $vo['1'];?></td>
                            </tr>
                            <?php  } } ?>
                        </tbody>
                    </table>
                </div>

                <div class="disf">
                    <div class="inline-block">
                        <div class="layui-card-header" style="color:#DD4B39;font-weight:bold">税率信息</div>
                        <div class="layui-card-body" style="padding:0;">
                            <table class="layui-table layui-form  layui-table-view" id="mainTable2">
                                <tbody style="width:100%;">
                                <?php  if(is_array($list['tax_info'])) { foreach($list['tax_info'] as $k => $vo) { ?>
                                <tr>
                                    <td><?php  echo $vo['0'];?></td>
                                    <td><?php  echo $vo['1'];?></td>
                                </tr>
                                <?php  } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="inline-block">
                        <div class="layui-card-header" style="color:#DD4B39;font-weight:bold">申报要素</div>
                        <div class="layui-card-body" style="padding:0;">
                            <table class="layui-table layui-form  layui-table-view" id="mainTable3">
                                <tbody style="width:100%;">
                                    <?php  if(is_array($list['declaration_elements'])) { foreach($list['declaration_elements'] as $k => $vo) { ?>
                                    <tr>
                                        <td><?php  echo $vo['0'];?></td>
                                        <td><?php  echo $vo['1'];?></td>
                                    </tr>
                                    <?php  } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="disf">
                    <div class="inline-block">
                        <div class="layui-card-header" style="color:#DD4B39;font-weight:bold">监管条件[<a style="color:blue;" href="javascript:openWindow('监管条件',0,2);">?</a>]</div>
                        <div class="layui-card-body" style="padding:0;">
                            <table class="layui-table layui-form  layui-table-view" id="mainTable4">
                                <tbody style="width:100%;">
                                <?php  if(empty($list['regulatory_conditions'])) { ?>
                                    <tr>
                                        <td colspan="2">无</td>
                                    </tr>
                                <?php  } else { ?>
                                    <?php  if(is_array($list['regulatory_conditions'])) { foreach($list['regulatory_conditions'] as $k => $vo) { ?>
                                    <tr>
                                        <td><?php  echo $vo['0'];?></td>
                                        <td><?php  echo $vo['1'];?></td>
                                    </tr>
                                    <?php  } } ?>
                                <?php  } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="inline-block">
                        <div class="layui-card-header" style="color:#DD4B39;font-weight:bold">检验检疫类别[<a style="color:blue;" href="javascript:openWindow('检验检疫类别',0,3);">?</a>]</div>
                        <div class="layui-card-body" style="padding:0;">
                            <table class="layui-table layui-form  layui-table-view" id="mainTable5">
                                <tbody style="width:100%;">
                                <?php  if(empty($list['inspect_quarantine'])) { ?>
                                <tr>
                                    <td colspan="2">无</td>
                                </tr>
                                <?php  } else { ?>
                                    <?php  if(is_array($list['inspect_quarantine'])) { foreach($list['inspect_quarantine'] as $k => $vo) { ?>
                                    <tr>
                                        <td><?php  echo $vo['0'];?></td>
                                        <td><?php  echo $vo['1'];?></td>
                                    </tr>
                                    <?php  } } ?>
                                <?php  } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="layui-card-header">协定税率</div>
                <div class="layui-card-body" style="padding:0;">
                    <table class="layui-table layui-form  layui-table-view" id="mainTable6">
                        <tbody style="width:100%;">
                            <tr>
                                <?php  if(is_array($list['treaty_tax_rate'])) { foreach($list['treaty_tax_rate'] as $k => $vo) { ?>
                                <td><?php  echo $vo['0'];?></td>
                                <?php  } } ?>
                            </tr>
                            <tr>
                                <?php  if(is_array($list['treaty_tax_rate'])) { foreach($list['treaty_tax_rate'] as $k => $vo) { ?>
                                <td><?php  echo $vo['1'];?></td>
                                <?php  } } ?>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="layui-card-header">RCEP税率</div>
                <div class="layui-card-body" style="padding:0;">
                    <table class="layui-table layui-form  layui-table-view" id="mainTable7">
                        <tbody style="width:100%;">
                            <tr>
                                <?php  if(is_array($list['rcep_tax_rate'])) { foreach($list['rcep_tax_rate'] as $k => $vo) { ?>
                                    <td><?php  echo $vo['0'];?></td>
                                <?php  } } ?>
                            </tr>
                            <tr>
                                <?php  if(is_array($list['rcep_tax_rate'])) { foreach($list['rcep_tax_rate'] as $k => $vo) { ?>
                                    <td><?php  echo $vo['1'];?></td>
                                <?php  } } ?>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="disf">
                    <div class="inline-block">
                        <div class="layui-card-header" >CIQ代码表(13位海关编码)</div>
                        <div class="layui-card-body" style="padding:0;">
                            <table class="layui-table layui-form  layui-table-view" id="mainTable8">
                                <tbody style="width:100%;">
                                <tr>
                                    <td>代码</td>
                                    <td>名称</td>
                                </tr>
                                <?php  if(is_array($list['ciq_code_info'])) { foreach($list['ciq_code_info'] as $k => $vo) { ?>
                                <tr>
                                    <td style="font-weight: bold;"><?php  echo $vo['0'];?></td>
                                    <td><?php  echo $vo['1'];?></td>
                                </tr>
                                <?php  } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="inline-block">
                        <div class="layui-card-header" >所属章节信息</div>
                        <div class="layui-card-body" style="padding:0;">
                            <table class="layui-table layui-form  layui-table-view" id="mainTable9">
                                <tbody style="width:100%;">
                                <?php  if(is_array($list['chapter_info'])) { foreach($list['chapter_info'] as $k => $vo) { ?>
                                <tr>
                                    <td style="font-weight: bold;"><?php  echo $vo['0'];?></td>
                                    <td><?php  echo $vo['1'];?></td>
                                </tr>
                                <?php  } } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
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

    function openWindow(title,id,typ)
    {
        var layer = layui.layer;
        var url = '';
        if(typ==1){
            //新增清单
            url="<?php  echo $this->createMobileUrl('hscode/index');?>&op=info&id="+id;
        }else if(typ==2){
            url="<?php  echo $this->createMobileUrl('hscode/index');?>&op=regulatory_list"
        }else if(typ==3){
            url="<?php  echo $this->createMobileUrl('hscode/index');?>&op=inspection_quarantine_list"
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