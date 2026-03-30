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

    .logistics_1,.logistics_2{display:none;}
</style>

<div class="layui-col-xs12 inspection_box">
    <form class="layui-form" lay-filter="component-form-element">
        <input type="text" value="<?php  echo $data['orderid'];?>" name="orderid" style="display: none;">
        <input type="text" value="<?php  echo $data['status2'];?>" name="status2" style="display: none;">
        <input type="text" value="<?php  echo $merge_order['method_id'];?>" name="method_id" style="display: none;">

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
            <div class="layui-col-xs12 title_bar">包裹信息<span style="color:#ff2222;">*</span></div>
            <div class="line layui-col-xs12 spin_status"></div>
            <div class="layui-col-xs12 disf spin_status">
                <div class="layui-col-xs3 title">取货方式</div>
                <div class="layui-col-xs9 val">
                    <?php  echo $method_name;?>
                </div>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                <div class="layui-col-xs3 title">发往国家</div>
                <div class="layui-col-xs9 val">
                    <?php  echo $country['code_name'];?>
                </div>
            </div>
            <div class="line layui-col-xs12"></div>
            <?php  if($data['method_id']==1) { ?>
                <!--配送上门-->
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title">收货地址</div>
                    <div class="layui-col-xs9 val">
                        <p>
                            <?php  echo $address['user_name'];?><br/>
                            <?php  echo $address['mobile'];?><br/>
                            <?php  echo $address['address'];?><br/>
                        </p>
                    </div>
                </div>
            <?php  } ?>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 remark disf" style="display:none;">
                <div class="layui-col-xs3 title add_height">备注</div>
                <div class="layui-col-xs9 val">
                    <input type="text" class="layui-input" name="remark" id="remark" placeholder="备注（选填）">
                </div>
            </div>
            <!--未发货-->
            <?php  if($merge_order['status']==2) { ?>
                <!--选择物流方式-->
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title">物流方式</div>
                    <div class="layui-col-xs9 val">
                        <select name="logistics_method" id="logistics_method" lay-verify="required" lay-filter="logistics_method">
                            <option value="">请选择物流方式</option>
                            <option value="1">传统物流（提单）</option>
                            <option value="2">电商物流（运单）</option>
                        </select>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="logistics_1">
                    <!--选择运输方式-->
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">运输方式</div>
                        <div class="layui-col-xs9 val">
                            <select name="transport_method1" id="transport_method1" lay-search>
                                <option value="">请选择运输方式</option>
                                <?php  if(is_array($logistics_1)) { foreach($logistics_1 as $k => $v) { ?>
                                <option value="<?php  echo $v['code_value'];?>"><?php  echo $v['code_name'];?></option>
                                <?php  } } ?>
                            </select>
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">提单编号</div>
                        <div class="layui-col-xs9 val">
                            <input type="text" class="layui-input" name="express_no1" value="" placeholder="请输入提单编号">
                        </div>
                    </div>
                </div>
                <div class="logistics_2">
                    <!--选择运输方式-->
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">运输方式</div>
                        <div class="layui-col-xs9 val">
                            <select name="transport_method2" id="transport_method2" lay-search>
                                <option value="">请选择运输方式</option>
                                <?php  if(is_array($logistics_2)) { foreach($logistics_2 as $k => $v) { ?>
                                <option value="<?php  echo $v['code_value'];?>"><?php  echo $v['code_name'];?></option>
                                <?php  } } ?>
                            </select>
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <!--选择超值路线-->
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">超值路线</div>
                        <div class="layui-col-xs9 val">
                            <select name="line_id" id="line_id" lay-search>
                                <option value="">请选择路线</option>
                                <option value="-1">无可用路线</option>
                                <?php  if(is_array($line)) { foreach($line as $k => $v) { ?>
                                <option value="<?php  echo $v['id'];?>"><?php  echo $v['name'];?></option>
                                <?php  } } ?>
                            </select>
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">快递公司</div>
                        <div class="layui-col-xs9 val">
                            <select name="express_id" id="express_id" lay-search>
                                <option value="">请选择快递公司</option>
                                <?php  if(is_array($express_company)) { foreach($express_company as $k => $v) { ?>
                                <option value="<?php  echo $v['id'];?>"><?php  echo $v['name'];?></option>
                                <?php  } } ?>
                            </select>
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">运单编号</div>
                        <div class="layui-col-xs9 val">
                            <input type="text" class="layui-input" name="express_no2" value="" placeholder="请输入运单编号">
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <?php  if(($data['method_id']==2 || $data['method_id']==3)) { ?>
                        <!--定点自提,仓库自提-->
                        <div class="line layui-col-xs12"></div>
                        <div class="layui-col-xs12 disf">
                            <div class="layui-col-xs3 title">自提地址</div>
                            <div class="layui-col-xs9 val">
                                <input type="text" name="pick_point_address" id="pick_point_address" value="" lay-verify="required">
                            </div>
                        </div>
                        <div class="line layui-col-xs12"></div>
                        <div class="layui-col-xs12 disf">
                            <div class="layui-col-xs3 title">联系人</div>
                            <div class="layui-col-xs9 val">
                                <input type="text" name="pick_point_contacter" id="pick_point_contacter" value="" lay-verify="required">
                            </div>
                        </div>
                        <div class="line layui-col-xs12"></div>
                        <div class="layui-col-xs12 disf">
                            <div class="layui-col-xs3 title">联系电话</div>
                            <div class="layui-col-xs9 val">
                                <input type="text" name="pick_point_mobile" id="pick_point_mobile" value="" lay-verify="required">
                            </div>
                        </div>
                    <?php  } ?>
                </div>
                <div class="info_box info_btn_box" style="margin: 10px 0 20px;text-align: center;">
                    <div>
                        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element1" style="width:90%;">立即提交</button>
                    </div>
                </div>
            <?php  } ?>

            <!--已发货-->
            <?php  if($merge_order['status']>=3) { ?>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title">物流方式</div>
                    <div class="layui-col-xs9 val">
                        <?php  echo $merge_order['transport_method'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <!--传统物流-->
                <?php  if($merge_order['logistics_method']==1) { ?>


                <?php  } ?>

                <!--电商物流-->
                <?php  if($merge_order['logistics_method']==2) { ?>
                    <!--超值路线-->
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">超值路线</div>
                        <div class="layui-col-xs9 val">
                            <?php  echo $line;?>
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">快递公司</div>
                        <div class="layui-col-xs9 val">
                            <?php  echo $merge_order['express_name'];?>
                        </div>
                    </div>
                <?php  } ?>

                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title"><?php  if($merge_order['logistics_method']==1) { ?>提单<?php  } else { ?>运单<?php  } ?>编号</div>
                    <div class="layui-col-xs9 val">
                        <?php  echo $merge_order['express_no'];?>
                    </div>
                </div>
                <?php  if(($data['method_id']==2 || $data['method_id']==3)) { ?>
                    <!--定点自提，仓库自提-->
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">自提地址</div>
                        <div class="layui-col-xs9 val">
                            <input type="text" name="pick_point_address" id="pick_point_address" value="<?php  echo $merge_order['pick_point_address'];?>" lay-verify="required">
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">联系人</div>
                        <div class="layui-col-xs9 val">
                            <input type="text" name="pick_point_contacter" id="pick_point_contacter" value="<?php  echo $merge_order['pick_point_contacter'];?>" lay-verify="required">
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        <div class="layui-col-xs3 title">联系电话</div>
                        <div class="layui-col-xs9 val">
                            <input type="text" name="pick_point_mobile" id="pick_point_mobile" value="<?php  echo $merge_order['pick_point_mobile'];?>" lay-verify="required">
                        </div>
                    </div>
                <?php  } ?>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12 disf">
                    <div class="layui-col-xs3 title">包裹状态</div>
                    <div class="layui-col-xs9 val" style="color:#ff2222;">
                        <?php  if($merge_order['status']==2) { ?>
                            待发货
                        <?php  } else if($merge_order['status']==3) { ?>
                            待收货
                        <?php  } else if($merge_order['status']==4) { ?>
                            已完成
                        <?php  } ?>
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

        form.on('select(logistics_method)',function(data) {
            let val = data.value;
            if(val==1){
                $('.logistics_1').show();
                $('.logistics_2').hide();
            }else if(val==2){
                $('.logistics_1').hide();
                $('.logistics_2').show();
            }
        });
        form.on('select(c_status)',function(data){
            let val = data.value;
            if(val==1){
                $('.remark').hide();
                $('.service_price').show();
            }else if(val==-1){
                $('.remark').show();
                $('.service_price').hide();
            }
            form.render(null, 'component-form-element');
        });

        form.on('submit(component-form-element1)', function (data) {
            //layer.msg(JSON.stringify(data.field));
            if((data.field['transport_method1']=='' || data.field['express_no1']=='' || typeof(data.field['express_no1'])=='undefined') && data.field['logistics_method']==1){
                layer.msg('请填写物流信息');return false;
            }
            if((data.field['transport_method2']=='' || data.field['line_id']=='' || data.field['express_id']=='' || data.field['express_no2']=='' || typeof(data.field['express_no2'])=='undefined') && data.field['logistics_method']==2){
                layer.msg('请填写物流信息');return false;
            }
            $.ajax({
                url: "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=sure_exWarehouse",
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