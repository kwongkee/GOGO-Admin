<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
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

    .g_info{border-bottom:2px solid #1790FF;}
    .g_info:last-child{border-bottom:0;}
    .layui-tab{margin-bottom:0;}
    .layui-tab-brief>.layui-tab-title .layui-this{color:#1790FF;}
    .layui-tab-brief>.layui-tab-title .layui-this:after{border-bottom:2px solid #1790FF;}
    .layui-tab-title{background:#fff;}
</style>

<div class="layui-col-xs12 inspection_box">
    <form class="layui-form" lay-filter="component-form-element">
        <input type="text" value="<?php  echo $data['orderid'];?>" name="orderid" style="display: none;">
        <input type="text" value="<?php  echo $data['status2'];?>" name="status2" style="display: none;">
        <?php  if($data['status2']==92) { ?>
            <!--货物信息-->
            <div class="upload_info info_box">
                <div class="layui-col-xs12 title_bar">编辑货物查验信息<span style="color:#ff2222;">*</span></div>

                <div class="layui-tab layui-col-xs12 layui-tab-brief">
                    <ul class="layui-tab-title">
                        <?php  if(is_array($goods)) { foreach($goods as $k => $v) { ?>
                        <li class="<?php  if($k==0) { ?>layui-this<?php  } ?>"><?php  echo $v['name'];?></li>
                        <?php  } } ?>
                    </ul>
                    <div class="layui-tab-content">
                        <?php  if(is_array($goods)) { foreach($goods as $k => $v) { ?>
                            <div class="layui-tab-item <?php  if($k==0) { ?>layui-show<?php  } ?>">
                                <input type="text" name="gid[]" value="<?php  echo $v['id'];?>" style="display: none;">
                                <div class="layui-col-xs12 g_info">
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物名称</div>
                                        <div class="layui-col-xs9 val disf">
                                            <input type="text" class="layui-input" lay-verify="required" name="name[]" value="<?php  echo $v['name'];?>">
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物价值</div>
                                        <div class="layui-col-xs9 val disf">
                                            ￥ <?php  echo $v['money'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物品牌</div>
                                        <div class="layui-col-xs9 val disf">
                                            <input type="text" class="layui-input" lay-verify="required" name="brand[]" value="<?php  echo $v['brand'];?>">
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物类型</div>
                                        <div class="layui-col-xs9 val disf">
                                            <?php  echo $v['cate_item'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物属性</div>
                                        <div class="layui-col-xs9 val disf">
                                            <!--<div id="good_item$v['id']}" class="xm-select-demo"></div>-->
                                            <?php  echo $v['good_item'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物数量</div>
                                        <div class="layui-col-xs9 val disf">
                                            <div class="layui-col-xs6">
                                                <div class="hui-number-box" min="1" max="100">
                                                    <div class="reduce">-</div>
                                                    <input type="number" name="num[]" value="<?php  echo $v['num'];?>" lay-verify="required" class="layui-input" />
                                                    <div class="add">+</div>
                                                </div>
                                            </div>
                                            <div class="layui-col-xs6">
                                                <select name="unit[]" lay-verify="required" lay-search>
                                                    <?php  if(is_array($unit)) { foreach($unit as $kk => $vv) { ?>
                                                    <option value="<?php  echo $vv['code_value'];?>" <?php  if($v['unit']==$vv['code_value']) { ?>selected<?php  } ?>><?php  echo $vv['code_name'];?></option>
                                                    <?php  } } ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物单件净重(kg)</div>
                                        <div class="layui-col-xs9 val disf">
                                            <input type="text" class="layui-input" lay-verify="required" name="netwt[]" value="<?php  echo $v['netwt'];?>">
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物单件毛重(kg)</div>
                                        <div class="layui-col-xs9 val disf">
                                            <input type="text" class="layui-input" lay-verify="required" name="grosswt[]" value="<?php  echo $v['grosswt'];?>">
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物总体积(cm)</div>
                                        <div class="layui-col-xs9 val disf">
                                            <div class="layui-col-xs4">
                                                <input type="text" class="layui-input" lay-verify="required" name="length[]" value="" placeholder="单件长度">
                                            </div>
                                            <div class="layui-col-xs4">
                                                <input type="text" class="layui-input" lay-verify="required" name="width[]" value="" placeholder="单件宽度">
                                            </div>
                                            <div class="layui-col-xs4">
                                                <input type="text" class="layui-input" lay-verify="required" name="height[]" value="" placeholder="单件高度">
                                            </div>
                                        </div>
                                    </div>
                                    <!--<div class="line layui-col-xs12"></div>-->
                                    <!--<div class="layui-col-xs12">-->
                                        <!--<div class="layui-col-xs3 title">货物真实毛重(kg)</div>-->
                                        <!--<div class="layui-col-xs9 val disf">-->
                                            <!--<input type="text" class="layui-input" lay-verify="required" name="true_grosswt[]" value="">-->
                                        <!--</div>-->
                                    <!--</div>-->
                                </div>
                            </div>
                        <?php  } } ?>
                    </div>
                </div>
            </div>
        <?php  } ?>
        <!--if empty($order['kaibao_apply'])-->
            <!--&lt;!&ndash;没有进行开包查验申请&ndash;&gt;-->
            <!--<div class="info_box" style="margin-top:10px;">-->
                <!--<div class="layui-col-xs12 title_bar">预设收费<span style="color:#ff2222;">*</span></div>-->
                <!--<div class="layui-tab layui-col-xs12 layui-tab-brief">-->
                    <!--<ul class="layui-tab-title">-->
                        <!--loop $consolidation_status $k $v-->
                        <!--<li class="if $k==0layui-this/if">$v['name']</li>-->
                        <!--/loop-->
                    <!--</ul>-->
                    <!--<div class="layui-tab-content">-->
                        <!--loop $consolidation_status $k $v-->
                            <!--<div class="layui-tab-item if $k==0layui-show/if">-->
                                <!--loop $v['child'] $kk $vv-->
                                    <!--<div class="layui-col-xs12">-->
                                        <!--<div class="layui-col-xs3 title">$vv['name']（HK$）</div>-->
                                        <!--<div class="layui-col-xs9 val"><input name="service_$vv['code']" lay-verify="required" class="layui-input" placeholder="请输入$vv['name']费用"></div>-->
                                    <!--</div>-->
                                    <!--<div class="line layui-col-xs12"></div>-->
                                <!--/loop-->
                            <!--</div>-->
                        <!--/loop-->
                    <!--</div>-->
                <!--</div>-->
            <!--</div>-->
        <!--/if-->
        <?php  if($data['status2']==91) { ?>
            <!--外包查验-->
            <div class="upload_info info_box">
                <div class="layui-col-xs12 title_bar">填写包裹查验信息<span style="color:#ff2222;">*</span></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">包裹总体积(cm)</div>
                    <div class="layui-col-xs9 val disf">
                        <div class="layui-col-xs4">
                            <input type="number" class="layui-input" lay-verify="required" name="length"
                                   placeholder="长度">
                        </div>
                        <div class="layui-col-xs4">
                            <input type="number" class="layui-input" lay-verify="required" name="width"
                                   placeholder="宽度">
                        </div>
                        <div class="layui-col-xs4">
                            <input type="number" class="layui-input" lay-verify="required" name="height"
                                   placeholder="高度">
                        </div>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">包裹重量(kg)</div>
                    <div class="layui-col-xs9 val">
                        <input type="text" class="layui-input" lay-verify="required" name="true_grosswt"
                               placeholder="请输入包裹重量">
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-input-block layui-col-xs12" style="margin:0;padding:10px;box-sizing:border-box;">
                    <div class="layui-upload" style="text-align:left;">
                        <button type="button" class="layui-btn" id="pic_file-upload">上传图片</button>
                        <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                            预览图：
                            <div class="layui-upload-list" id="pic_file-upload-list"></div>
                        </blockquote>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title add_height">损毁状态</div>
                    <div class="layui-col-xs9 val">
                        <select name="damage_status" id="damage_status">
                            <option value="0">无损毁</option>
                            <?php  if(is_array($damage_status)) { foreach($damage_status as $k => $v) { ?>
                            <option value="<?php  echo $v['code'];?>"><?php  echo $v['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title add_height">备注</div>
                    <div class="layui-col-xs9 val">
                        <input type="text" class="layui-input" name="remark" placeholder="备注（选填）">
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title add_height">服务收费（HK$）</div>
                    <div class="layui-col-xs9 val">
                        <input type="number" class="layui-input" name="service_price" placeholder="请填写服务收费金额（HK$）" value="1" lay-verify="required">
                    </div>
                </div>
                <div class="info_box info_btn_box" style="margin: 10px 0 20px;text-align: center;">
                    <div>
                        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element1" style="width:90%;">立即查验</button>
                    </div>
                </div>
            </div>
        <?php  } ?>
        <?php  if($data['status2']==92) { ?>
            <!--开包查验-->
            <div class="upload_info info_box">
                <div class="layui-col-xs12 title_bar">填写包裹查验信息<span style="color:#ff2222;">*</span></div>
                <div class="layui-input-block layui-col-xs12" style="margin:0;padding:10px;box-sizing:border-box;">
                    <div class="layui-upload" style="text-align:left;">
                        <button type="button" class="layui-btn" id="pic_file-upload">上传图片</button>
                        <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                            预览图：
                            <div class="layui-upload-list" id="pic_file-upload-list"></div>
                        </blockquote>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title add_height">损毁状态</div>
                    <div class="layui-col-xs9 val">
                        <select name="damage_status" id="damage_status">
                            <option value="0">无损毁</option>
                            <?php  if(is_array($damage_status)) { foreach($damage_status as $k => $v) { ?>
                            <option value="<?php  echo $v['code'];?>"><?php  echo $v['name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title add_height">备注</div>
                    <div class="layui-col-xs9 val">
                        <input type="text" class="layui-input" name="remark" placeholder="备注（选填）">
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title add_height">服务收费（HK$）</div>
                    <div class="layui-col-xs9 val">
                        <input type="number" class="layui-input" name="service_price" placeholder="请填写服务收费金额（HK$）" value="1" lay-verify="required">
                    </div>
                </div>
                <div class="info_box info_btn_box" style="margin: 10px 0 20px;text-align: center;">
                    <div>
                        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element1" style="width:90%;">立即查验</button>
                    </div>
                </div>
            </div>
        <?php  } ?>
    </form>
</div>

<!--<script type="text/javascript" src="../addons/sz_yi/static/js/layui/xm-select.js"></script>-->
<!--<script src="https://decl.gogo198.cn/layuiadmin/layui/xm-select.js"></script>-->

<script>
    //一句话即可完成js工作
    hui.numberBox();

    layui.use(['layer', 'jquery', 'form', 'element', 'upload'], function () {
        var layer = layui.layer
            , $ = layui.jquery
            , form = layui.form
            , upload = layui.upload
            , element = layui.element;
        form.render(null, 'component-form-element');

        upload.render({
            elem: '#pic_file-upload'
            ,url: 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=util&m=sz_yi&p=uploader'
            ,accept: 'images'
            ,acceptMime: 'image/*'
            ,data: {file: "file",'op':'upload'}
            ,multiple: true
            ,before:function(res){
                layer.load();
            }
            ,done: function(res){
                layer.closeAll('loading'); //关闭loading
                if(res.status == 'success')
                {
                    var length = $('#pic_file-upload-list').children().length;
                    $('#pic_file-upload-list').append('<div style="display: inline-block;margin-top:10px;"><img onclick="seePic(this);" src="/attachment/'+ res.filename +'" class="layui-upload-img" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" style="width:30px;"><button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button><input type="hidden" name="pic_file['+length+']" value="'+res.filename+'"></div>')
                }
            }
        });

        form.on('submit(component-form-element1)', function (data) {
            //layer.msg(JSON.stringify(data.field));

            $.ajax({
                url: "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=sure_inspection",
                method: 'post',
                data: data.field,
                dataType: 'JSON',
                success: function (res) {
                    layer.msg(res.result.msg, {time: 3000}, function () {
                        if (res.status == 0) {
                            parent.location.reload();
                            // window.location.href = "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=warehouse_goods_up_down"
                        }
                    });
                },
                error: function (data) {
                    layer.msg("系统错误", {time: 3000});
                }
            });
            return false;
        });

        // loop $goods $k $v
            // var cate_item = xmSelect.render({
            //     el: '#cate_item$v['id']}',
            //     paging: true,
            //     pageSize: 5,
            //     filterable: true,
            //     toolbar: {
            //         show: true,
            //     },
            //     prop: {
            //         name: 'catName',
            //         value: 'id',
            //     },
            //     autoRow: true,
            //     data: <?php  echo $cate_item;?>,
            //     name: 'cate_item[]',
            //     layVerify: 'required',
            //     layVerType: 'msg',
            //     initValue:[<?php  echo $v['cateid'];?>]
            // });
            // var good_item = xmSelect.render({
            //     el: '#good_item$v['id']}',
            //     paging: true,
            //     pageSize: 5,
            //     filterable: true,
            //     toolbar: {
            //         show: true,
            //     },
            //     prop: {
            //         name: 'title',
            //         value: 'id',
            //     },
            //     autoRow: true,
            //     data: <?php  echo $good_item;?>,
            //     name: 'good_item[]',
            //     layVerify: 'required',
            //     layVerType: 'msg',
            //     initValue:[<?php  echo $v['itemid'];?>]
            // });
        // /loop

    });
</script>