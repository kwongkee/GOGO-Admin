<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>询价详情</title>
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

    .layui-input-block{line-height: 38px;}
    .layui-btn{background:<?php  echo $website['color'];?>;color:<?php  echo $website['color_word'];?>;border:1px solid <?php  echo $website['color_word'];?>;}
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
                <div class="layui-card-body">
                    <fieldset class="layui-elem-field  template_box">
                        <legend>询价详情</legend>
                        <div class="layui-field-box">
                            <?php  if(!empty($info['content'])) { ?>
                                <?php  if(is_array($info['form'])) { foreach($info['form'] as $idx => $vo) { ?>
                                    <div class="layui-form-item">
                                        <div class="layui-form-label"><?php  echo $vo['label_name'];?></div>
                                        <div class="layui-input-block">
                                            <?php  echo $info['content'][$idx];?>
                                        </div>
                                    </div>
                                <?php  } } ?>
                            <?php  } ?>
                            <?php  if(!empty($info['text'])) { ?>
                                <div class="layui-form-item">
                                    <div class="layui-form-label">询价信息</div>
                                    <div class="layui-input-block">
                                        <p><?php  echo $info['text'];?></p>
                                    </div>
                                </div>
                            <?php  } ?>

                            <?php  if(!empty($info['files'])) { ?>
                            <div class="layui-form-item">
                                <label class="layui-form-label">我的文件</label>
                                <div class="layui-input-block layui-select-fscon">
                                    <?php  if(is_array($info['files'])) { foreach($info['files'] as $k => $v) { ?>
                                    <a href="https://www.gogo198.net<?php  echo $v['files'];?>" target="_blank"><?php  echo $v['filenames'];?></a>
                                    <?php  } } ?>
                                </div>
                            </div>
                            <?php  } ?>
                        </div>
                    </fieldset>

                    <?php  if(!empty($info['quote_id'])) { ?>
                    <fieldset class="layui-elem-field  template_box">
                        <legend>报价详情</legend>
                        <div class="layui-field-box">
                            <?php  if(!empty($quote['content'])) { ?>
                            <?php  if(is_array($quote['form'])) { foreach($quote['form'] as $idx => $vo) { ?>
                            <div class="layui-form-item">
                                <div class="layui-form-label"><?php  echo $vo['label_name'];?></div>
                                <div class="layui-input-block">
                                    <?php  echo $quote['content'][$idx];?>
                                </div>
                            </div>
                            <?php  } } ?>
                            <?php  } ?>
                            <?php  if(!empty($quote['text'])) { ?>
                            <div class="layui-form-item">
                                <div class="layui-form-label">询价信息</div>
                                <div class="layui-input-block">
                                    <p><?php  echo $quote['text'];?></p>
                                </div>
                            </div>
                            <?php  } ?>
                        </div>
                    </fieldset>
                    <?php  } ?>
                </div>
                <!--<div class="layui-form-item" style="margin-top: 25px;text-align: center;">-->
                <!--    <div>-->
                <!--        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element2">提交询价</button>-->
                <!--    </div>-->
                <!--</div>-->
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
    });
</script>