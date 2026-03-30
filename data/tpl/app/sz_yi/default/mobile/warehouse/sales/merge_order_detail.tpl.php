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

    .line {
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

    .layui-table,.layui-table thead th{text-align: center;}
    .layui-table .spin_input{width:33.33%;}
</style>

<div class="layui-col-xs12 inspection_box">
    <form class="layui-form" lay-filter="component-form-element">
        <input type="text" name="orderid" value="<?php  echo $merge_order['id'];?>" style="display:none;">
        <div class="upload_info info_box">
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
                        <input type="text" name="gid[]" value="<?php  echo $v['id'];?>" style="display: none;">
                        <div class="layui-col-xs12 g_info">
                            <div class="layui-col-xs12">
                                <div class="layui-col-xs3 title">货物名称</div>
                                <div class="layui-col-xs9 val disf">
                                    <?php  echo $v['name'];?>
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
                                    <?php  echo $v['brand'];?>
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
                                    <?php  echo $v['num'];?>
                                </div>
                            </div>
                            <div class="line layui-col-xs12"></div>
                            <div class="layui-col-xs12">
                                <div class="layui-col-xs3 title">货物单件净重(kg)</div>
                                <div class="layui-col-xs9 val disf">
                                    <?php  echo $v['netwt'];?>
                                </div>
                            </div>
                            <div class="line layui-col-xs12"></div>
                            <div class="layui-col-xs12">
                                <div class="layui-col-xs3 title">货物单件毛重(kg)</div>
                                <div class="layui-col-xs9 val disf">
                                    <?php  echo $v['grosswt'];?>
                                </div>
                            </div>

                            <div class="line layui-col-xs12"></div>
                            <div class="layui-col-xs12">
                                <div class="layui-col-xs3 title">货物总体积(cm)</div>
                                <div class="layui-col-xs9 val disf">
                                    <?php  echo $v['true_volumn'];?>(长*宽*高)
                                </div>
                            </div>
                            <div class="line layui-col-xs12"></div>
                            <div class="layui-col-xs12">
                                <div class="layui-col-xs3 title">货架号</div>
                                <div class="layui-col-xs9 val disf">
                                    <?php  echo $v['shelf_number'];?>
                                </div>
                            </div>

                        </div>
                    </div>
                    <?php  } } ?>
                </div>
            </div>
        </div>

        <div class="upload_info info_box">
            <div class="layui-col-xs12 title_bar">订单信息<span style="color:#ff2222;">*</span></div>
            <div class="layui-col-xs12 g_info">
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">订单编号</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $merge_order['ordersn'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">运单编号</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $merge_order['express_no'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">发往国家</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $merge_order['country_code'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">买家信息</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $merge_order['username'];?>，<?php  echo $merge_order['mobile'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">取货方式</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $merge_order['method_name'];?>
                    </div>
                </div>
                <?php  if(empty($merge_order['express_info'])) { ?>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">收货地址</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $merge_order['address_info']['user_name'];?>,<?php  echo $merge_order['address_info']['mobile'];?>,<?php  echo $merge_order['address_info']['address'];?>,<?php  echo $merge_order['address_info']['country_name'];?>
                    </div>
                </div>
                <?php  } ?>
                <?php  if(!empty($merge_order['express_info'])) { ?>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">转运信息</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo $merge_order['express_info'];?>
                    </div>
                </div>
                <?php  } ?>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">订单状态</div>
                    <div class="layui-col-xs9 val disf" style="color:#ff2222;">
                        <?php  echo $merge_order['status_name'];?>（<?php  echo $merge_order['program_typeName'];?>）
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12">
                    <div class="layui-col-xs3 title">创建时间</div>
                    <div class="layui-col-xs9 val disf">
                        <?php  echo date('Y-m-d H:i',$merge_order['createtime']);?>
                    </div>
                </div>
                <?php  if($merge_order['status']!=-2 && $merge_order['status']!=4) { ?>
                    <?php  if($merge_order['status2']!=11 && $merge_order['status2']!=12 && $merge_order['status2']!=13 && $merge_order['status2']!=14) { ?>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12">
                        <div class="layui-col-xs3 title">损毁状态</div>
                        <div class="layui-col-xs9 val disf">
                            <select name="status2" id="status2">
                                <option value="">如包裹损毁时请选择</option>
                                <?php  if(is_array($consolidation_status)) { foreach($consolidation_status as $k => $v) { ?>
                                <option value="<?php  echo $v['code'];?>"><?php  echo $v['name'];?></option>
                                <?php  } } ?>
                            </select>
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12">
                        <div class="layui-col-xs3 title">包裹备注</div>
                        <div class="layui-col-xs9 val disf">
                            <input type="text" name="remark" class="layui-input" value="">
                        </div>
                    </div>
                    <?php  } ?>
                <?php  } ?>
                <div class="line layui-col-xs12"></div>
                <div class="info_box info_btn_box" style="margin: 10px 0 20px;text-align: center;">
                    <div>
                        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element1" style="width:90%;">立即提交</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<script>
    $(function() {
        layui.use(['layer', 'form', 'table', 'upload', 'element'], function () {
            var $ = layui.$
                , layer = layui.layer
                , form = layui.form
                , element = layui.element
                , upload = layui.upload
                , table = layui.table;

            form.render(null,'component-form-element');

            form.on('submit(component-form-element1)',function(data){
                data.field['pa']=1;
                $.ajax({
                    url: "./index.php?i=3&c=entry&p=sales&do=warehouse&m=sz_yi&op=merge_order_detail",
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
        })
    })
</script>