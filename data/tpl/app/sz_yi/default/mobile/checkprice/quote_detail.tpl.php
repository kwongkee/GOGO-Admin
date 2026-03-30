<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>报价详情</title>
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
    .layui-input-block{line-height: 38px;}
</style>

<div class="layui-fluid" style="margin-top:15px;">
    <div class="layui-row layui-col-space15">
        <form class="layui-form" action="" method="post" lay-filter="component-form-element">
            <input type="text" name="inquiry_id" id="inquiry_id" value="<?php  echo $info['inquiry_id'];?>" style="display:none;"/>
            <input type="text" name="quote_id" id="quote_id" value="<?php  echo $info['id'];?>" style="display:none;"/>
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
                                <?php  if(!empty($info['inquiry_content'])) { ?>
                                <?php  if(is_array($info['inquiry_form'])) { foreach($info['inquiry_form'] as $idx => $vo) { ?>
                                <div class="layui-form-item">
                                    <div class="layui-form-label"><?php  echo $vo['label_name'];?></div>
                                    <div class="layui-input-block">
                                        <?php  echo $info['inquiry_content'][$idx];?>
                                    </div>
                                </div>
                                <?php  } } ?>
                                <?php  } ?>
                                <?php  if(!empty($info['inquiry_text'])) { ?>
                                <div class="layui-form-item">
                                    <div class="layui-form-label">询价信息</div>
                                    <div class="layui-input-block">
                                        <p><?php  echo $info['inquiry_text'];?></p>
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

                        <fieldset class="layui-elem-field  template_box">
                            <legend>报价详情</legend>
                            <div class="layui-field-box">
                                <?php  if(!empty($info['content'])) { ?>
                                    <?php  if(is_array($info['form'])) { foreach($info['form'] as $key => $vo) { ?>
                                    <div class="layui-form-item">
                                        <div class="layui-form-label"><?php  echo $vo['label_name'];?></div>
                                        <div class="layui-input-block">
                                            <?php  echo $info['content'][$key];?>
                                        </div>
                                    </div>
                                    <?php  } } ?>
                                <?php  } ?>

                                <?php  if(!empty($info['text'])) { ?>
                                    <div class="layui-form-item">
                                        <div class="layui-form-label">报价信息</div>
                                        <div class="layui-input-block">
                                            <?php  echo $info['text'];?>
                                        </div>
                                    </div>
                                <?php  } ?>
                            </div>
                        </fieldset>
                    </div>
                    <?php  if($info['status']==1) { ?>
                    <div class="layui-form-item" style="margin-top: 25px;text-align: center;">
                        <div>
                            <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element2">在线下单</button>
                        </div>
                    </div>
                    <?php  } ?>
                </div>
            </div>
        </form>
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

        form.on('submit(component-form-element2)', function(data){
            // JSON.stringify()
            // console.log(data.field);return false;
            layer.confirm('确认下单吗？',function(index){
                $.ajax({
                    url:"./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=quote_detail",
                    method:'post',
                    data:data.field,
                    dataType:'JSON',
                    success:function(res){
                        layer.msg(res.result.msg,{time:2000}, function () {
                            if(res.status == 0)
                            {
                                //跳转到相应业务系统
                                window.location.href='https://gather.gogo198.cn/gather/index';
                            }else if(res.status == -1){
                                layer.open({
                                    type: 2,
                                    title: '请先登录',
                                    shadeClose: true,
                                    shade: 0.3,
                                    area: ['100%', '100%'],
                                    content: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=login&open=3",
                                });
                            }else if(res.status == -2){
                                window.location.href="./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=account_reg&open=1";
                                // layer.open({
                                //     type: 2,
                                //     title: '实名认证',
                                //     shadeClose: true,
                                //     shade: 0.3,
                                //     area: ['100%', '100%'],
                                //     content: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=account_reg&open=1",
                                // });
                            }
                        });
                    },
                    error:function (data) {
                        layer.msg('系统错误',{time:2000});
                    }
                });
            });

            return false;
        });
    });

    function openWindow(id,typ){
        if(typ==1){
            window.location.href='./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=quote_detail&quote_id='+id;
        }
    }
</script>