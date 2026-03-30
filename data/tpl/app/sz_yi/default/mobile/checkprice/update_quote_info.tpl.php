<?php defined('IN_IA') or exit('Access Denied');?><!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
    <link rel="stylesheet" href="../addons/sz_yi/static/warehouse/static_file/style.css" media="all">
    <link rel="stylesheet" href="../addons/sz_yi/static/warehouse/static_file/layui.css" media="all">
    <link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/warehouse/static_file/hui.css" />
    <script type="text/javascript" src="../addons/sz_yi/static/warehouse/static_file/hui.js" charset="UTF-8"></script>
    <script src="../addons/sz_yi/static/warehouse/static_file/jquery-3.2.1.min.js"></script>
    <script src="../addons/sz_yi/static/warehouse/static_file/layui.js"></script>
    <title>修改报价</title>
    <style>
        *{font-size:15px;}
        .disf{display:flex;align-items: center;}
        .layui-btn-normal{background:#1790FF;}
        div{overflow: visible;}
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
        .disf{display:flex;align-items: center;}
        .user_tel,.user_email{display:none;}
        .show{display:block;}
        .layui-input-block{line-height:38px;}
    </style>
</head>
<body>
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-1);">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            报价详情[<?php  echo $order['ordersn'];?>]
        </div>
        <div class="layui-card-body" style="padding:0;padding-bottom: 10px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="id" value="<?php  echo $quote_id;?>" style="display: none;">

                <fieldset class="layui-elem-field template_box">
                    <legend>询价详情</legend>
                    <div class="layui-field-box">
                        <?php  if(!empty($order['content'])) { ?>
                            <?php  if(is_array($order['form'])) { foreach($order['form'] as $k => $v) { ?>
                            <div class="layui-form-item">
                                <label class="layui-form-label"><?php  echo $v['label_name'];?></label>
                                <div class="layui-input-block layui-select-fscon">
                                    <?php  echo $order['content'][$k];?>
                                </div>
                            </div>
                            <?php  } } ?>
                        <?php  } ?>

                        <?php  if(!empty($order['text'])) { ?>
                            <div class="layui-form-item">
                                <label class="layui-form-label">询价信息</label>
                                <div class="layui-input-block layui-select-fscon">
                                    <?php  echo $order['text'];?>
                                </div>
                            </div>
                        <?php  } ?>

                        <?php  if(!empty($order['files'])) { ?>
                        <div class="layui-form-item">
                            <label class="layui-form-label">客户文件</label>
                            <div class="layui-input-block layui-select-fscon">
                                <?php  if(is_array($order['files'])) { foreach($order['files'] as $k => $v) { ?>
                                <a href="https://www.gogo198.net<?php  echo $v['files'];?>" target="_blank"><?php  echo $v['filenames'];?></a>
                                <?php  } } ?>
                            </div>
                        </div>
                        <?php  } ?>
                    </div>
                </fieldset>


                <fieldset class="layui-elem-field quote_box">
                    <legend style="width:unset;border-bottom:0;">报价区</legend>
                    <div class="layui-field-box">
                        <?php  if(!empty($quote_info['content'])) { ?>
                            <?php  if(is_array($quote_info['form'])) { foreach($quote_info['form'] as $key => $vo) { ?>
                            <div class="layui-form-item">
                                <div class="layui-form-label"><?php  echo $vo['label_name'];?></div>
                                <div class="layui-input-block">
                                    <?php  if($vo['label_value']==1) { ?>
                                    <input type="text" class="layui-input" name="content[]" value="<?php  echo $quote_info['content'][$key];?>" placeholder=""/>
                                    <?php  } ?>
                                    <?php  if($vo['label_value']==2) { ?>
                                    <input type="number" class="layui-input" name="content[]" value="<?php  echo $quote_info['content'][$key];?>" placeholder=""/>
                                    <?php  } ?>
                                    <?php  if($vo['label_value']==3) { ?>
                                    <input type="text" name="content[]" class="layui-input" id="date<?php  echo $vo['label_rand'];?>" value="<?php  echo $quote_info['content'][$key];?>" placeholder="yymmddhhii">
                                    <?php  } ?>
                                    <?php  if($vo['label_value']==4) { ?>
                                    <select name="content[]" lay-search>
                                        <?php  if(is_array($vo['label_select2'])) { foreach($vo['label_select2'] as $vo2) { ?>
                                        <option value="<?php  echo $vo2;?>" <?php  if($vo2==$quote_info['content'][$key]) { ?>selected<?php  } ?>><?php  echo $vo2;?></option>
                                        <?php  } } ?>
                                    </select>
                                    <?php  } ?>
                                </div>
                            </div>
                            <?php  } } ?>
                        <?php  } ?>
                        <?php  if(!empty($quote_info['text'])) { ?>
                            <div class="layui-form-item">
                                <div class="layui-form-label">报价信息</div>
                                <div class="layui-input-block">
                                    <textarea name="text" id="text" lay-verify="required" class="layui-textarea"><?php  echo $quote_info['text'];?></textarea>
                                </div>
                            </div>
                        <?php  } ?>
                    </div>
                </fieldset>
                <?php  if($quote_info['is_notice']==0) { ?>
<!--                <div class="layui-form-item" style="text-align: center;">-->
<!--                    <div class="layui-btn layui-btn-wanring layui-btn-md notice_buyer" style="background:#f0ad4e;">通知买家</div>-->
<!--                </div>-->
                <?php  } ?>

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element2">确认修改并通知C端</button>
                        </div>
                    </div>
                </div>
            </form>
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

        form.render(null, 'component-form-demo1');

        <?php  if(!empty($quote_info['quote_form'])) { ?>
            <?php  if(is_array($quote_info['quote_form'])) { foreach($quote_info['quote_form'] as $vo) { ?>
                <?php  if($vo['label_value']==3) { ?>
                laydate.render({
                    elem: "#date<?php  echo $vo['label_rand'];?>" //指定元素
                });
                <?php  } ?>
            <?php  } } ?>
        <?php  } ?>

        form.on('submit(component-form-element2)', function(data){
            // JSON.stringify()
            // console.log(data.field);return false;
            $.ajax({
                url:"./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=update_quote_info&upd=1",
                method:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.result.msg,{time:2000}, function () {
                        if(res.status == 0)
                        {
                            window.location.reload();
                            // window.location.href="./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=quote_list";
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
</script>
</body>
</html>