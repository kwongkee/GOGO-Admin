<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>订单详情</title>
<style>
    .layui-table td, .layui-table th{ text-align: center;}
    .layui-table th{ background-color: #ecf6fc; }
    .page_head{width:100%;background:#fff;margin-bottom:5px;box-shadow:0 0 4px #ddd;padding:10px 0 10px;}
    .page_head .left{width:20%;display:flex;align-items:center;font-size:15px;}
    .page_head .left .back{width:13px;height:13px;border-top:2px solid #000;border-left:2px solid #000;transform:rotate(-50deg);margin-left:15px;margin-right:5px;}
    .layui-layer-hui .layui-layer-content{color:#fff;}
    .disf{display:flex;align-items: center;}
    .show{display:block;}
    .layui-select-fscon{line-height: 38px;}
    .layui-form-item{margin-bottom:0px;}
    .order_detail{overflow-x:scroll;width:100%;white-space: nowrap;display:none;}

    .layui-form .layui-form-item{margin-bottom:15px;}
    .layui-form .layui-form-label{width:60px;}
    .layui-form .layui-input-block{margin-left: 95px;}
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
            收款详情
        </div>
        <div class="layui-card-body" style="padding: 0px;">
            <?php  if($order['order_type']==1) { ?>
                <!--订单-->
                <div class="layui-form-item">
                    <label class="layui-form-label">订单编号</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $order['order_detail']['ordersn'];?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">待付金额</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $order['order_detail']['totalmoney'];?>
                    </div>
                </div>
            <?php  } else if(($order['order_type']==2)) { ?>
                <!--账单-->
                <div class="layui-form-item">
                    <label class="layui-form-label">订单编号</label>
                    <div class="layui-input-block layui-select-fscon">
                        <?php  echo $order['order_detail']['ordersn_flow'];?>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">收款详情</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="layui-btn layui-btn-normal layui-btn-xs" onclick="show_detail()">查看详情</div>
                    </div>
                </div>
                <div class="order_detail">
                    <table class="layui-table">
                        <thead>
                            <th>费用日期</th>
                            <th>费用类别</th>
                            <th>费用名称</th>
                            <th>计价单位</th>
                            <th>单价</th>
                            <th>次/周/天</th>
                            <th>立方/件/托</th>
                            <th>金额</th>
                            <th>摘要备注</th>
                        </thead>
                        <tbody>
                            <?php  if(is_array($order['order_detail']['fee_content'])) { foreach($order['order_detail']['fee_content'] as $k => $v) { ?>
                                <tr>
                                    <td><?php  echo $v['event_date'];?></td>
                                    <td><?php  if($v['event_type']==1) { ?>
                                            应付费用
                                        <?php  } else if(($v['event_type']==2)) { ?>
                                            代付费用
                                        <?php  } else if(($v['event_type']==3)) { ?>
                                            其它费用
                                        <?php  } ?></td>
                                    <td><?php  echo $v['event_name'];?></td>
                                    <td><?php  echo $v['price_unit'];?>/<?php  echo $v['price_unit2'];?></td>
                                    <td><?php  echo $v['price'];?></td>
                                    <td><?php  echo $v['cycle'];?></td>
                                    <td><?php  echo $v['piece'];?></td>
                                    <td><?php  echo $v['event_price'];?></td>
                                    <td><?php  echo $v['event_remark'];?></td>
                                </tr>
                            <?php  } } ?>
                        </tbody>
                        <tfooter>
                            <tr>
                                <td colspan="3">应付合计：<?php  echo $order['order_detail']['totalmoney'];?></td>
                                <td colspan="3">开票税费：<?php  echo $order['order_detail']['invoicemoney'];?></td>
                                <td colspan="3">已付费用：<?php  echo $order['order_detail']['alreadypay'];?></td>
                            </tr>
                            <tr>
                                <td colspan="3">应付实付：<?php  echo $order['order_detail']['shouldpay'];?></td>
                                <td colspan="3">税费实付：<?php  echo $order['order_detail']['invoicemoney2'];?></td>
                                <td colspan="3">开票实付：<?php  echo $order['order_detail']['finalmoney'];?></td>
                            </tr>
                        </tfooter>
                    </table>
                </div>
            <?php  } ?>
            <div class="layui-form-item">
                <label class="layui-form-label">订单状态</label>
                <div class="layui-input-block layui-select-fscon">
                    <?php  echo $order['statusname'];?>
                </div>
            </div>
            <?php  if($order['status']==4) { ?>
            <div class="layui-form-item">
                <label class="layui-form-label">确认状态</label>
                <div class="layui-input-block layui-select-fscon">
                    <?php  echo $order['statusname2'];?>
                </div>
            </div>
            <?php  } ?>
            <?php  if($order['type']==1) { ?>
                <?php  if($order['type2']==1) { ?>
                    <!--银行收款账号-->
                    <div class="layui-form-item">
                        <label class="layui-form-label">银行名称</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['account_info']['bank_name'];?>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">银行账号</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['account_info']['bank_account'];?>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">账号名称</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['account_info']['name'];?>
                        </div>
                    </div>
                <?php  } else if(($order['type2']==2)) { ?>
                    <!--平台收款账号-->
                    <div class="layui-form-item">
                        <label class="layui-form-label">收款平台</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['account_info']['platform_name'];?>
                        </div>
                    </div>
                    <?php  if($order['account_info']['type2']==1) { ?>
                        <div class="layui-form-item">
                            <label class="layui-form-label">收款条码</label>
                            <div class="layui-input-block layui-select-fscon">
                                <img src="https://shop.gogo198.cn/<?php  echo $order['account_info']['platform_code'];?>" alt="" style="width:100px;height:100px;">
                                <br>
                                <span style="color:#ff2222;">(请长按保存至手机)</span>
                            </div>
                        </div>
                    <?php  } else if(($order['account_info']['type2']==2)) { ?>
                        <div class="layui-form-item">
                            <label class="layui-form-label">平台账号</label>
                            <div class="layui-input-block layui-select-fscon">
                                <?php  echo $order['account_info']['platform_account'];?>
                            </div>
                        </div>
                    <?php  } ?>
                <?php  } ?>
            <?php  } else if(($order['type']==2)) { ?>
                <!--平台收款账号-->
                <?php  if($order['type3']==1) { ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">银行名称</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['account_info']['bank_name'];?>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">银行账号</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['account_info']['bank_account'];?>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">账号名称</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['account_info']['name'];?>
                        </div>
                    </div>
                <?php  } ?>
            <?php  } ?>
            <?php  if(!empty($order['payinfo'])) { ?>
                <div class="layui-form-item">
                    <label class="layui-form-label">付款详情</label>
                    <div class="layui-input-block layui-select-fscon">
                        <div class="layui-btn layui-btn-normal layui-btn-xs" onclick="pay_detail()">查看详情</div>
                    </div>
                </div>
                <div class="pay_detail" style="display:none;">
                    <div class="layui-form-item">
                        <label class="layui-form-label">已付金额</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['payinfo']['totalmoney'];?>
                        </div>
                    </div>
                    <?php  if(!empty($order['payinfo']['transfer_date'])) { ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">付款日期</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['payinfo']['transfer_date'];?>
                        </div>
                    </div>
                    <?php  } ?>
                    <?php  if(!empty($order['payinfo']['pay_platform'])) { ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">付款平台</label>
                        <div class="layui-input-block layui-select-fscon">
                            <?php  echo $order['payinfo']['pay_platform'];?>
                        </div>
                    </div>
                    <?php  } ?>
                    <?php  if(!empty($order['payinfo']['pic_file'])) { ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">付款记录</label>
                        <div class="layui-input-block layui-select-fscon">
                            <div class="layui-upload" style="text-align:left;">
                                <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                                    预览图：
                                    <div class="layui-upload-list" id="pic_list-upload-list">
                                        <?php  if(is_array($order['payinfo']['pic_file'])) { foreach($order['payinfo']['pic_file'] as $v) { ?>
                                        <div style="display: inline-block;margin-top:10px;overflow:visible;">
                                            <img onclick="seePic(this);" src="<?php  echo $v;?>" class="layui-upload-img"  style="width:30px;">
                                        </div>
                                        <?php  } } ?>
                                    </div>
                                </blockquote>
                            </div>
                        </div>
                    </div>
                    <?php  } ?>

                </div>
            <?php  } ?>
            <?php  if($order['status']<3) { ?>
            <hr>
            <div style="text-align: center;padding:10px 0;">
                <div class="layui-btn layui-btn-success" onclick="sure_pay(<?php  echo $order['type'];?>,<?php  echo $order['type2'];?>,<?php  echo $order['type3'];?>)">确认付款</div>
            </div>
            <div class="online_pay" style="display: none;text-align:center;">
                <div class="layui-btn layui-btn-success order_sub12" style="width:80%;margin:25px auto;">微信支付</div>
                <div class="layui-btn layui-btn-normal order_sub13" style="width:80%;margin-left:0;">支付宝支付</div>
            </div>
            <?php  } ?>
            <div class="diyform_pay" style="display:none;">

            </div>
        </div>
    </div>
</div>

<!-- 在线支付 -->
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('order/pay/wechat_jie', TEMPLATE_INCLUDEPATH)) : (include template('order/pay/wechat_jie', TEMPLATE_INCLUDEPATH));?>
<form action="" id="wecpay" method="post">
    <input type="hidden" name="tid" id="tid" value="" />
    <input type="hidden" name="opid" id="opid" value="" />
    <input type="hidden" name="fee" id="fee" value="" />
    <input type="hidden" name="title" id="title" value="" />
    <input type="hidden" name="acc" id="acc" value="" />
    <input type="hidden" name="ky" id="ky" value="" />
    <input type="hidden" name="uniacid" id="uniacid" value="" />
    <input type="hidden" name="to" id="to" value="" />
    <input type="hidden" name="project" id="project" value="" />
</form>

<script>
    layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
        var $ = layui.$
            , layer = layui.layer
            , form = layui.form
            , element = layui.element
            , laydate = layui.laydate
            , upload = layui.upload
            , table = layui.table;

        /* 监听提交 */
        form.on('submit(component-form-demo)', function(data){
            $.ajax({
                url:"./index.php?i=3&c=entry&do=onlinepay&p=index&m=sz_yi&op=sure_pay",
                method:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.result.msg,{time:3000}, function () {
                        if(res.status == 0)
                        {
                            window.location.reload();
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

    function delPic(obj)
    {
        var layer = layui.layer,$ = layui.$;

        layer.confirm("确认删除？", {
            btn: ["删除","取消"]
        }, function(){
            $(obj).parent().remove();
            layer.close(layer.index);
        }, function(){

        });
    }

    function seePic(obj)
    {
        var layer = layui.layer,$ = layui.$;
        layer.open({
            type: 1,
            title: false,
            closeBtn: 1,
            area: ['auto'],
            skin: 'layui-layer-nobg',
            shadeClose: true,
            content: '<img width="100%" src="' + $(obj)[0].src + '" />'
        });
    }

    function show_detail(){
        var layer = layui.layer,$ = layui.$;

        layer.open({
            type: 1,
            title:"收款详情",
            area: ['100%', '100%'], //宽高
            content: $('.order_detail')
        });
    }

    function pay_detail(){
        var layer = layui.layer,$ = layui.$;

        layer.open({
            type: 1,
            title:"付款详情",
            area: ['100%', '100%'], //宽高
            content: $('.pay_detail')
        });
    }

    //typ1,1自有账户收款，2委托平台账户
    //typ2,1银行收款账号，2平台收款账号
    //typ3,1购购银行账户，2购购在线支付
    function sure_pay(typ1,typ2,typ3,oid="<?php  echo $order['id'];?>") {
        var layer = layui.layer,$ = layui.$,form = layui.form,laydate = layui.laydate,upload = layui.upload;
        let isshow = 0;
        let html = '<form class="layui-form" action="" lay-filter="component-form-demo1">\n' +
            '                    <input type="text" name="oid" value="'+oid+'" style="display:none;">\n'+
            '                    <div class="layui-form-item">\n' +
            '                        <label class="layui-form-label">已付金额</label>\n' +
            '                        <div class="layui-input-block layui-select-fscon">\n' +
            '                            <input type="text" name="payinfo[totalmoney]" lay-verify="required" placeholder="请输入已付金额" autocomplete="off" class="layui-input" value="">\n' +
            '                        </div>\n' +
            '                    </div>\n';

        if(typ1==1){
            if(typ2==1){
                isshow = 1;
                html += '                <div class="layui-form-item">\n' +
                    '                        <label class="layui-form-label">转账日期</label>\n' +
                    '                        <div class="layui-input-block layui-select-fscon">\n' +
                    '                            <input type="text" name="payinfo[transfer_date]" id="transfer_date" placeholder="请选择转账日期" autocomplete="off" class="layui-input" value="" lay-verify="required">\n' +
                    '                        </div>\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-form-item">\n' +
                    '                        <label class="layui-form-label">转账凭证</label>\n' +
                    '                        <div class="layui-input-block layui-select-fscon">\n' +
                    '                            <div class="layui-upload" style="text-align:left;">\n' +
                    '                                <button type="button" class="layui-btn pic_file_active" id="pic_list-upload">上传</button>\n' +
                    '                                <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">\n' +
                    '                                    预览图：\n' +
                    '                                    <div class="layui-upload-list" id="pic_list-upload-list">\n' +
                    '                                    </div>\n' +
                    '                                </blockquote>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                    </div>\n';
            }else if(typ2==2){
                isshow = 1;
                html += '                <div class="layui-form-item">\n' +
                    '                        <label class="layui-form-label">付款平台</label>\n' +
                    '                        <div class="layui-input-block layui-select-fscon">\n' +
                    '                            <input type="text" name="payinfo[pay_platform]" lay-verify="required" placeholder="请输入付款平台" autocomplete="off" class="layui-input" value="">\n' +
                    '                        </div>\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-form-item">\n' +
                    '                        <label class="layui-form-label">付款记录</label>\n' +
                    '                        <div class="layui-input-block layui-select-fscon">\n' +
                    '                            <div class="layui-upload" style="text-align:left;">\n' +
                    '                                <button type="button" class="layui-btn pic_file_active" id="pic_list-upload">上传</button>\n' +
                    '                                <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">\n' +
                    '                                    预览图：\n' +
                    '                                    <div class="layui-upload-list" id="pic_list-upload-list">\n' +
                    '                                    </div>\n' +
                    '                                </blockquote>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                    </div>\n';
            }
        }
        else if(typ1==2){
            if(typ3==1){
                isshow = 1;
                html += '                <div class="layui-form-item">\n' +
                    '                        <label class="layui-form-label">转账日期</label>\n' +
                    '                        <div class="layui-input-block layui-select-fscon">\n' +
                    '                            <input type="text" name="payinfo[transfer_date]" id="transfer_date" placeholder="请选择转账日期" autocomplete="off" class="layui-input" value="" lay-verify="required">\n' +
                    '                        </div>\n' +
                    '                    </div>\n' +
                    '                    <div class="layui-form-item">\n' +
                    '                        <label class="layui-form-label">转账凭证</label>\n' +
                    '                        <div class="layui-input-block layui-select-fscon">\n' +
                    '                            <div class="layui-upload" style="text-align:left;">\n' +
                    '                                <button type="button" class="layui-btn pic_file_active" id="pic_list-upload">上传</button>\n' +
                    '                                <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">\n' +
                    '                                    预览图：\n' +
                    '                                    <div class="layui-upload-list" id="pic_list-upload-list">\n' +
                    '                                    </div>\n' +
                    '                                </blockquote>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                    </div>\n';
            }
        }
        html += '                    <div class="layui-form-item layui-layout-admin">\n' +
        '                        <div class="layui-input-block">\n' +
        '                            <div class="layui-footer" style="left: 0;text-align:center;">\n' +
        '                                <button class="layui-btn" lay-submit="" lay-filter="component-form-demo">立即提交</button>\n' +
        '                                <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->\n' +
        '                            </div>\n' +
        '                        </div>\n' +
        '                    </div>\n' +
        '                </form>';

        if(isshow==1){
            //银行或个人收款码汇款
            $('.diyform_pay').html(html);
            laydate.render({
                elem: '#transfer_date'
                ,type: 'date'
                ,format: 'MM月dd日'
            });
            form.render(null, 'component-form-demo1');
            upload.render({
                elem: '#pic_list-upload'
                ,url: "./index.php?i=3&c=entry&do=util&m=sz_yi&p=uploader"
                ,accept: 'file'
                ,data: {file: "pic_list-upload",'op':'uploadDeclFile'}
                ,multiple: false
                ,number:1
                ,done: function(res,indexs,upload){
                    layer.closeAll('loading'); //关闭loading
                    if(res.status == 'success')
                    {
                        // onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png"
                        var length = $('#pic_list-upload-list').children().length;
                        $('#pic_list-upload-list').append('<div style="display: inline-block;margin-top:10px;overflow:visible;"><img onclick="seePic(this);" src="'+ res.url +'" class="layui-upload-img"  style="width:30px;"><button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button><input type="hidden" name="pic_file['+length+']" value="'+res.url+'"></div>');
                    }else{
                        layer.msg(res.message,{time:3000});
                    }
                }
            });
            layer.open({
                type: 1,
                title:"确认付款",
                area: ['100%', '100%'], //宽高
                content: $('.diyform_pay')
            });
        }
        else{
            //在线支付
            $.ajax({
                url:"./index.php?i=3&c=entry&do=onlinepay&p=index&m=sz_yi&op=pay",
                method:'post',
                data:{orderid:"<?php  echo $orderid;?>"},
                dataType:'JSON',
                success:function(json){
                    var result = json.result;
                    if(json.status!=1){
                        layer.msg(result,{time:3000}, function () {});
                        return;
                    }

                    //通莞微信支付  2017-11-01
                    if(result.tgwechat.success){
                        $('.order_sub12').click(function() {
                            $.ajax({
                                url: "./index.php?i=3&c=entry&do=onlinepay&p=index&m=sz_yi&op=tgpay",
                                method: 'post',
                                data: {orderid: "<?php  echo $orderid;?>",type:'tgwechat',overdue_money:"<?php  echo $order['totalmoney'];?>"},
                                dataType: 'JSON',
                                success: function (rjson) {
                                    if(rjson.status!=1) {
                                        $('.button').removeAttr('submitting');
                                        layer.msg(rjson.result,{time:3000}, function () {});
                                        return;
                                    }

                                    var tgw = rjson.result.tgwechat;

                                    //2018-08-21
                                    $('#wecpay').attr('action', 'https://shop.gogo198.cn/addons/sz_yi/payment/tgwechat/Paymentess.php');
                                    $('#tid').val(tgw.tid);
                                    $('#opid').val(tgw.openid);
                                    $('#title').val(tgw.title);
                                    $('#fee').val(tgw.fee);
                                    $('#acc').val(tgw.account);
                                    $('#ky').val(tgw.key);
                                    $('#to').val(tgw.token);
                                    $('#uniacid').val(tgw.uniacid);
                                    $('#project').val('onlinepayment');
                                    $('#wecpay').submit();
                                }
                            });
                        })
                    }
                    //2017-11-01
                    //通莞支付宝 2017-11-20
                    if(result.tgalipay.success){

                        $('.order_sub13').click(function(){
                            //数据请求order/pay/op = pay & type = alipay;  没有数据返回：只返回状态 status = 1;
                            location.href = './index.php?i=3&c=entry&do=onlinepay&p=index&m=sz_yi&op=tgpay&type=tgalipay&orderid=<?php  echo $orderid;?>&overdue_money=<?php  echo $order["totalmoney"];?>';
                        })
                    }
                },
                error:function (data) {
                    layer.msg('系统错误',{time:3000});
                }
            });

            layer.open({
                type: 1,
                title:"在线支付",
                area: ['80%', '30%'], //宽高
                content: $('.online_pay')
            });
        }
    }
</script>
