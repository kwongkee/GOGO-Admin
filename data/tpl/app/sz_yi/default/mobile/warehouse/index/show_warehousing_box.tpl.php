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

    .add_goods{background: #fff;text-align: center;width: 80%;margin: 10px auto;padding: 10px 0;border-radius: 5px;border: 1px solid #1790FF;font-size: 15px;}
    .goods_info .del{display:none;}
    .goods_info .del .del_box{color:#1790FF;justify-content: center;padding:10px;box-sizing:border-box;}

    .layui-tab{margin-bottom:0;}
    .layui-tab-brief>.layui-tab-title .layui-this{color:#1790FF;}
    .layui-tab-brief>.layui-tab-title .layui-this:after{border-bottom:2px solid #1790FF;}
    .layui-tab-title{background:#fff;}
</style>

<div class="layui-col-xs12 warehousing_box">
    <form class="layui-form" lay-filter="component-form-element">
        <input type="text" value="<?php  echo $data['orderid'];?>" name="orderid" style="display: none;">
        <input type="text" value="<?php  echo $data['uid'];?>" name="uid" style="display: none;">
        <div class="upload_info info_box">
            <div class="layui-col-xs12 title_bar">填写入库信息<span style="color:#ff2222;">*</span></div>
            <div class="layui-col-xs12 disf">
                <div class="layui-col-xs3 title">货架号</div>
                <div class="layui-col-xs9 val">
                    <input type="text" class="layui-input" lay-verify="required" name="shelf_number"
                           placeholder="请填写包裹存放的货架号" value="<?php  echo $order['shelf_number'];?>">
                </div>
            </div>
            <?php  if($order['program_type']!=6) { ?>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                <div class="layui-col-xs3 title add_height">服务收费（HK$）</div>
                <div class="layui-col-xs9 val">
                    <input type="number" class="layui-input" name="service_price" placeholder="请填写服务收费金额（HK$）" value="1" lay-verify="required">
                </div>
            </div>
            <?php  } ?>

            <div class="layui-col-xs12 title_bar">包裹信息<span style="color:#ff2222;">*</span></div>
            <div class="goods_info layui-col-xs12">
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">包裹单号</div>
                    <div class="layui-col-xs9 val">
                        <?php  echo $order['ordersn'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">快递单号</div>
                    <div class="layui-col-xs9 val">
                        <?php  echo $order['express_no'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">买家信息</div>
                    <div class="layui-col-xs9 val">
                        <?php  echo $order['name'];?>(ID:<?php  echo $order['id'];?>)
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">包裹状态</div>
                    <div class="layui-col-xs9 val" style="color:#ff2222;">
                        <?php  echo $order['consolidation_status'];?>
                    </div>
                </div>
            </div>

            <div class="layui-col-xs12 title_bar">货物信息<span style="color:#ff2222;">*</span></div>
            <div class="layui-tab layui-col-xs12 layui-tab-brief">
                <ul class="layui-tab-title">
                    <?php  if(is_array($goods)) { foreach($goods as $k => $v) { ?>
                    <li class="<?php  if($k==0) { ?>layui-this<?php  } ?>"><?php  echo $v['name'];?></li>
                    <?php  } } ?>
                </ul>
                <div class="layui-tab-content">
                    <?php  if(is_array($goods)) { foreach($goods as $k => $v) { ?>
                        <div class="layui-tab-item <?php  if($k==0) { ?>layui-show<?php  } ?>">
                            <div class="g_info">
                                <div class="goods_info layui-col-xs12">
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物名称</div>
                                        <div class="layui-col-xs9 val">
                                            <?php  echo $v['name'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物价值</div>
                                        <div class="layui-col-xs9 val">
                                            ￥ <?php  echo $v['money'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物品牌</div>
                                        <div class="layui-col-xs9 val">
                                            <?php  echo $v['brand'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物类型</div>
                                        <div class="layui-col-xs9 val">
                                            <?php  echo $v['cate_item'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物属性</div>
                                        <div class="layui-col-xs9 val">
                                            <?php  echo $v['good_item'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物数量</div>
                                        <div class="layui-col-xs9 val">
                                            <?php  echo $v['num'];?>(<?php  echo $v['unit'];?>)
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物净重(kg)</div>
                                        <div class="layui-col-xs9 val">
                                            <?php  echo $v['netwt'];?>
                                        </div>
                                    </div>
                                    <div class="line layui-col-xs12"></div>
                                    <div class="layui-col-xs12">
                                        <div class="layui-col-xs3 title">货物毛重(kg)</div>
                                        <div class="layui-col-xs9 val">
                                            <?php  echo $v['grosswt'];?>
                                        </div>
                                    </div>
                                    <?php  if(!empty($v['true_volumn'])) { ?>
                                        <div class="line layui-col-xs12"></div>
                                        <div class="layui-col-xs12">
                                            <div class="layui-col-xs3 title">货物总体积(cm)</div>
                                            <div class="layui-col-xs9 val">
                                                <?php  echo $v['true_volumn'];?>(长*宽*高)
                                            </div>
                                        </div>
                                    <!--<div class="layui-col-xs12">-->
                                        <!--<div class="layui-col-xs3 title">货物真实毛重(kg)</div>-->
                                        <!--<div class="layui-col-xs9 val">-->
                                            <!--$v['true_grosswt']-->
                                        <!--</div>-->
                                    <!--</div>-->
                                    <?php  } ?>
                                </div>
                            </div>
                        </div>
                    <?php  } } ?>
                </div>
            </div>

            <?php  if($order['true_grosswt']>0) { ?>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">包裹总体积(cm)</div>
                    <div class="layui-col-xs9 val">
                        <?php  echo $order['true_volumn'];?>(长*宽*高)
                    </div>
                </div>
                <div class="line layui-col-xs12">
                    <div class="layui-col-xs3 title">包裹真实毛重(kg)</div>
                    <div class="layui-col-xs9 val">
                        <?php  echo $order['true_grosswt'];?>
                    </div>
                </div>
            <?php  } ?>
            <?php  if($order['program_type']!=6) { ?>
            <div class="info_box info_btn_box" style="margin: 0px 0 20px;text-align: center;border-top: 2px solid #eee;padding-top: 20px;">
                <div>
                    <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element1" style="width:90%;">立即入库</button>
                </div>
            </div>
            <?php  } ?>
        </div>
    </form>
</div>

<script>
    layui.use(['layer', 'jquery', 'form', 'element', 'upload'], function () {
        var layer = layui.layer
            , $ = layui.jquery
            , form = layui.form
            , upload = layui.upload
            , element = layui.element;

        form.render(null, 'component-form-element');

        form.on('submit(component-form-element1)', function (data) {
            //layer.msg(JSON.stringify(data.field));

            $.ajax({
                url: "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=sure_warehousing",
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
    });
</script>