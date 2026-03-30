<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>订单合计详情</title>
<link href="../addons/sz_yi/static/css/layui.css" rel="stylesheet">
<style>
    .layui-fluid{background:#f8f8f8;}
    .header{background:#fff;}
    .header .logo{width:100%;height:auto;}
    .white_bg{background:#fff;border-radius:5px;}
    .cen_notice{padding:10px 15px;box-sizing: border-box;border-bottom:1px solid rgb(229,229,229);font-size:13px;}
    .menu_box1{border-right:2px solid #f8f8f8;border-bottom:2px solid #f8f8f8;}
    .menu_box2{border-bottom:2px solid #f8f8f8;}
    .menu_box3{border-right:2px solid #f8f8f8;}
    .menu_part1{padding: 25px 0 25px 5px;justify-content: center;}
    .menu_part1 img{width:45px;margin-right:5px;}
    .menu_part1 .menu_part1_text p:nth-of-type(1){color:#717171;}
    .menu_part1 .menu_part1_text p:nth-of-type(2){font-size:13px;color:#9a9a9a;margin-top:12px;}

    .menu_part2_container{justify-content: space-between;}
    .menu_part2_container .layui-col-xs3{width:24%;}
    .menu_part2{text-align: center;background:#fff;padding:10px 0;box-sizing: border-box;border-radius:5px;}
    .menu_part2 img{width:35px;}
    .menu_part2 p{font-size:13px;}

    .line_part{width:100%;border-radius:5px;background:#fff;overflow: hidden;margin-bottom:10px;}
    .line_part .line_blue{background:#1790FF;width:5px;height:100px;max-height: 150px;min-height:100px;}
    .line_part .line_part_content{width:100%;padding:10px;box-sizing: border-box;}
    .line_part .line_part_content .line_title{font-weight: bold;max-width: 200px;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;-webkit-line-clamp: 1;}
    .line_part .line_part_content .line_time{color:#a9a9a9;font-size:13px;}
    .line_part .line_part_content .line_price{font-size:13px;margin:10px 0;}
    .line_part .line_part_content .line_price .line_price_red{color:#ff2222;}
    .line_part .line_part_content .line_accept{font-size:13px;width:100%;white-space: nowrap;overflow: hidden;text-overflow: ellipsis;-webkit-line-clamp: 1;}

    .layui-layer-hui .layui-layer-content{color:#fff;}
    .eventInfo,.payerInfo{display:none;}

    div{overflow: visible;}
    .line{width:100%;height:1px;background:#eee;margin:5px 0;}
    .layui-card{float:left;padding:5px 0;box-sizing:border-box;}
    .up{background:#009688;}
    .layui-card-header{font-size:16px;font-weight:bold;}
    .layui-col-xs12 .title{font-size:15px;font-weight:bold;}
    .disf{display:flex;align-items:center;}
    .event{float:left;border: 1px solid #efefef;margin-bottom: 10px;padding: 10px;}
    .layui-table th{font-size:14px;font-weight:bold;color:#000;}
    .layui-table thead tr{background: rgb(189,215,238);color:#000;font-weight: bold;}
    .layui-table tr td,.layui-table th{text-align:center;}

</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15" style="padding-top:5px;">
        <form class="layui-form" lay-filter="component-form-element1" style="padding-left:0;padding-right:0;">
            <input type="text" name="key" value="<?php  echo $key;?>" style="display: none;">
            <div class="layui-col-md12" style="float:left;background:#fff;">
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;display: flex;align-items: center;">
                    <div class="layui-col-xs3 title">
                        <img src="../addons/sz_yi/static/images/gogo_order_avatar.jpg" alt="" style="width:100px;">
                    </div>
                    <div class="layui-col-xs8 val" style="text-align: center;font-weight:bold;font-size:20px;">
                        合计详情
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;">
                    <div class="layui-col-xs3 title">付款客户</div>
                    <div class="layui-col-xs8 val" style="text-align: center;font-size:15px;font-weight: bold;">
                        <?php  echo $detail['payer_name'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;">
                    <div class="layui-col-xs3 title">应付金额是否含税</div>
                    <div class="layui-col-xs8 val" style="text-align: center;">
                        <?php  if($detail['is_tax']==1) { ?>是<?php  } ?>
                        <?php  if($detail['is_tax']==2) { ?>否<?php  } ?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;">
                    <div class="layui-col-xs3 title">应付金额</div>
                    <div class="layui-col-xs8 val" style="text-align: center;">
                        <?php  echo $detail['currency'];?>&nbsp;<?php  echo $detail['price'];?>
                    </div>
                </div>
                <?php  if($detail['is_tax']==2) { ?>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;">
                        <div class="layui-col-xs3 title">税费类型</div>
                        <div class="layui-col-xs8 val" style="text-align: center;">
                            <?php  if($detail['tax_type']==1) { ?>销售税<?php  } ?>
                            <?php  if($detail['tax_type']==2) { ?>增值税<?php  } ?>
                        </div>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;">
                        <div class="layui-col-xs3 title">税率</div>
                        <div class="layui-col-xs8 val" style="text-align: center;">
                            <?php  echo $detail['tax_num'];?>
                        </div>
                    </div>
                <?php  } ?>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;">
                    <div class="layui-col-xs3 title">开票税费</div>
                    <div class="layui-col-xs8 val" style="text-align: center;">
                        <?php  echo $detail['invoicing_tax'];?>
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;">
                    <div class="layui-col-xs3 title">实际应付</div>
                    <div class="layui-col-xs8 val" style="text-align: center;">
                        <?php  echo $detail['real_price'];?>
                    </div>
                </div>

                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;display: flex;align-items: center;justify-content: center;margin-bottom:30px;">
                    <div class="layui-btn layui-btn-md layui-btn-primary back">返回主页</div>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/layui/layui.js"></script>
<script>
    layui.use(['layer','jquery','element','laydate','form'], function() {
        var layer = layui.layer
            , $ = layui.jquery
            , element = layui.element
            , laydate = layui.laydate
            , form = layui.form;

        laydate.render({
            elem: '#event_date'
            ,type: 'date'
            ,format: 'MM月dd日'
        });

        form.render(null,'component-form-element1');
        form.render(null,'verify-element1');

        $('.back').click(function(){
            window.history.back(-1);
        });

        form.on('submit(component-form-element)', function(data){
            layer.load(); //上传loading
            $.ajax({
                url:"https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=warehouse&op=edit_order&m=sz_yi&key=<?php  echo $key;?>&orderid=<?php  echo $orderid;?>",
                type:'post',
                dataType:'JSON',
                data:data.field,
                success:function(res){
                    layer.closeAll('loading'); //关闭loading
                    layer.msg(res.msg,{time:3000}, function () {
                        if(res.code == 0)
                        {
                            window.location.reload();
                        }
                    });
                },
                error:function (data) {
                    layer.msg('系统错误',{time:2000});
                }
            });

            return false;
        });

        $('input[name="event_num"]').on("input propertychange",function(){
            let event_price = $('input[name="event_price"]').val();
            let allprice = $(this).val()*event_price;
            $('input[name="event_totalprice"]').val(allprice.toFixed(2));
        });

        $('input[name="event_price"]').on("input propertychange",function(){
            let event_price = $('input[name="event_num"]').val();
            let allprice = $(this).val()*event_price;
            $('input[name="event_totalprice"]').val(allprice.toFixed(2));
        });
    });

    function cancel(){
        var layer = layui.layer;

        layer.confirm('确定要删除此订单事项吗?',{
            icon:3,
            btn:['确定','取消']
        },function() {
            $.ajax({
                url: "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=warehouse&p=warehouse&op=del_order&m=sz_yi&key=<?php  echo $key;?>&orderid=<?php  echo $orderid;?>",
                type: 'post',
                dataType: 'JSON',
                data: {'id':"<?php  echo $id;?>",'sub':2},
                success: function (res) {
                    layer.msg(res.msg, {time: 3000}, function () {
                        if (res.code == 0) {
                            window.history.back(-1);
                        }
                    });
                },
                error: function (data) {
                    layer.msg('系统错误', {time: 2000});
                }
            });
        });
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>