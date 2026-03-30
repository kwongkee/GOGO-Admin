<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>任务详情</title>
<style>
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
    .image_box{}
    .image_box .image_item{}
    .image_box .image_item .remove_image{color:#fff;font-size:15px;}
</style>
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-1);">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            订仓编号[<?php  echo $order['ordersn'];?>]
        </div>
        <div class="layui-card-body" style="padding:0;padding-bottom: 10px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="id" value="<?php  echo $order_id;?>" style="display: none;">
                
                <div class="layui-form-item">
                    <label class="layui-form-label">订仓方式</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  if($order['prediction_id']==1) { ?>直接转运<?php  } ?><?php  if($order['prediction_id']==2) { ?>集货转运<?php  } ?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">集运渠道</label>
                    <div class="layui-input-block layui-select-fscon">
                       <?php  if($order['channel']==1) { ?>国际快递<?php  } ?><?php  if($order['channel']==2) { ?>国际邮政<?php  } ?><?php  if($order['channel']==3) { ?>国际专线<?php  } ?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">线路信息</label>
                    <div class="layui-input-block layui-select-fscon">
<!--                        <table class="layui-table" border="0">-->
<!--                            <thead>-->
<!--                                <th>线路名称</th>-->
<!--                                <th>运输方式</th>-->
<!--                                <th>签收时效(日)</th>-->
<!--                                <th>接受货物属性</th>-->
<!--                            </thead>-->
<!--                            <tbody>-->
<!--                                <tr>-->
<!--                                    <td><?php  echo $order['line_info']['name'];?></td>-->
<!--                                    <td><?php  echo $order['line_info']['transport_method'];?></td>-->
<!--                                    <td><?php  echo $order['line_info']['sign_time'];?></td>-->
<!--                                    <td><?php  echo $order['line_info']['accept_product'];?></td>-->
<!--                                </tr>-->
<!--                            </tbody>-->
<!--                        </table>-->
                    </div>
                </div>
                <div class="layui-form-item">
<!--                    <label class="layui-form-label"></label>-->
                    <div class="layui-input-block layui-select-fscon" style="margin-left:0;">
                        <table class="layui-table" border="0">
                            <thead>
                            <th>线路名称</th>
                            <th>运输方式</th>
                            <th>签收时效(日)</th>
                            <th>接受货物属性</th>
                            </thead>
                            <tbody>
                            <tr>
                                <td><?php  echo $order['line_info']['name'];?></td>
                                <td><?php  echo $order['line_info']['transport_method'];?></td>
                                <td><?php  echo $order['line_info']['sign_time'];?></td>
                                <td><?php  echo $order['line_info']['accept_product'];?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">选择仓库</label>
                    <div class="layui-input-block layui-select-fscon">
                            <!--<input type="text" class="layui-input" name="content[]" value="" placeholder="" lay-verify="required"/>-->
                        <select name="warehouse_id" id="warehouse_id" lay-filter="warehouse_id" lay-search lay-verify="required">
                            <option value="">请选择仓库</option>
                            <option value="-1">暂无仓库合适，去配置仓库</option>
                            <?php  if(is_array($warehouse)) { foreach($warehouse as $k => $v) { ?>
                            <option value="<?php  echo $v['id'];?>"><?php  echo $v['warehouse_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>

                <?php  if(empty($order['warehouse_id'])) { ?>
                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo">确认提交</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>
                <?php  } ?>
            </form>
        </div>
    </div>
</div>

<!--新增配置仓库-->
<div class="add_warehouse" style="display: none;">
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15">
            <form class="layui-form" action="" method="post" lay-filter="component-form-element2">
                <input type="text" style="display:none;" name="id" id="id" value="<?php  echo $id;?>">
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-body">
                            <div class="layui-form-item">
                                <div class="layui-form-label">选择物流商</div>
                                <div class="layui-input-block">
                                    <select name="uid" id="uid" lay-verify="required" lay-search>
                                        <?php  if(is_array($merchant)) { foreach($merchant as $k => $v) { ?>
                                        <option value="<?php  echo $v['id'];?>"><?php  echo $v['name'];?></option>
                                        <?php  } } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-form-label">仓库名称</div>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" lay-verify="required" name="warehouse_name" id="warehouse_name" value="">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">仓库编号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="warehouse_code" lay-verify="required" placeholder="请输入仓库编号" autocomplete="off" class="layui-input" value="">
                                </div>
                            </div>
                            <div class="layui-form-item" style="display: none;">
                                <div class="layui-form-label">收件人名称</div>
                                <div class="layui-input-block">
                                    <input type="text" class="layui-input" lay-verify="" name="name" id="name" value="">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-form-label">联系电话</div>
                                <div class="layui-input-block">
                                    <div class="layui-col-xs6">
                                        <input type="number" name="area_code" lay-verify="required" placeholder="国家区号" autocomplete="off" class="layui-input" value="">
                                    </div>
                                    <div class="layui-col-xs6">
                                        <input type="number" name="mobile" lay-verify="required" placeholder="仓库联系电话" autocomplete="off" class="layui-input" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-form-label">邮政编码</div>
                                <div class="layui-input-block">
                                    <input type="number" class="layui-input" lay-verify="" name="postal_code" id="postal_code" value="">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <div class="layui-form-label">国家编码</div>
                                <div class="layui-input-block">
                                    <select name="country_code" id="country_code" lay-verify="required" lay-search>
                                        <?php  if(is_array($country_code)) { foreach($country_code as $k => $v) { ?>
                                        <option value="<?php  echo $v['id'];?>"><?php  echo $v['param2'];?></option>
                                        <?php  } } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">省/州</label>
                                <div class="layui-input-block">
                                    <select name="province_code" id="province_code" lay-verify="required" lay-search>
                                        <?php  if(is_array($province_code)) { foreach($province_code as $k => $v) { ?>
                                        <option value="<?php  echo $v['id'];?>"><?php  echo $v['param2'];?></option>
                                        <?php  } } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">市</label>
                                <div class="layui-input-block">
                                    <select name="city_code" id="city_code" lay-verify="required" lay-search>
                                        <?php  if(is_array($city_code)) { foreach($city_code as $k => $v) { ?>
                                        <option value="<?php  echo $v['id'];?>"><?php  echo $v['param2'];?></option>
                                        <?php  } } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="layui-form-item addr">
                                <label class="layui-form-label">地址1</label>
                                <div class="layui-input-block disf">
                                    <input type="text" name="address1" lay-verify="required" placeholder="请输入地址" autocomplete="off" class="layui-input" value="">
                                    <div class="layui-btn layui-btn-success" onclick="add_addr()">+</div>
                                </div>
                            </div>
                            <div class="addrs">

                            </div>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item" style="margin-top: 25px;text-align: center;">
                    <div>
                        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element2">立即提交</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
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

        form.on('select(warehouse_id)',function(data){
            let val = data.value;
            if(val==-1){
                layer.open({
                    type: 1,
                    title: '配置仓库',
                    area:['100%','100%'],
                    content: $('.add_warehouse')
                });
            }
        });

        /* 监听提交 */
        form.on('submit(component-form-demo)', function(data){
            if(data.field['warehouse_id']==-1){
                layer.msg('请选择仓库');return false;
            }
            $.ajax({
                url:"./index.php?i=3&c=entry&do=gather&p=index&m=sz_yi&op=gather_info",
                type:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.result.msg,{time:3000}, function () {
                        if(res.status == 0)
                        {
                            window.location.reload();
                        }else if(res.status==-1){
                            
                        }
                    });
                },
                error:function (data) {
                    layer.msg('系统错误',{time:3000});
                }
            });
            return false;
        });

        form.on('submit(component-form-element2)', function(data){
            $.ajax({
                url:"./index.php?i=3&c=entry&do=gather&p=index&m=sz_yi&op=add_warehouse",
                type:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.result.msg,{time:3000}, function () {
                        if(res.status == 0)
                        {
                            window.location.reload();
                        }else if(res.status==-1){

                        }
                    });
                },
                error:function (data) {
                    layer.msg('系统错误',{time:3000});
                }
            });
            return false;
        });
    });

    function add_addr(){
        var $ = layui.$
            , layer = layui.layer
            , form = layui.form;

        let num = $('.addr').length + 1;

        let html = '<div class="layui-form-item addr">\n' +
            '                                <label class="layui-form-label">地址'+num+'</label>\n' +
            '                                <div class="layui-input-block disf">\n' +
            '                                    <input type="text" name="addresss[]" lay-verify="required" placeholder="请输入地址" autocomplete="off" class="layui-input" value="">\n' +
            '                                    <div class="layui-btn layui-btn-danger" onclick="del_addr(this)">-</div>\n' +
            '                                    <div class="layui-btn layui-btn-success" onclick="add_addr()">+</div>\n' +
            '                                </div>\n' +
            '                            </div>';

        $('.addrs').append(html);
        form.render(null,'component-form-element2');
    }

    function del_addr(t){
        var $ = layui.$
            , layer = layui.layer
            , form = layui.form;
        var idx = layer.confirm('确认删除吗？',function(index){
            $(t).parent().parent().remove();
            form.render(null,'component-form-element2');
            layer.close(idx);
        },function(){

        });
    }
</script>