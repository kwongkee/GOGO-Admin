<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<title>拖车订单确认信息</title>
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

    div{overflow: visible;}
    .line{width:100%;height:1px;background:#eee;margin:5px 0;}
    .layui-card{float:left;padding:5px 0;box-sizing:border-box;}
    .up{background:#009688;}
    .layui-card-header{font-size:16px;font-weight:bold;}
    .layui-col-xs12 .title{font-size:15px;font-weight:bold;}
    .disf{display:flex;align-items:center;}
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15" style="padding-top:5px;">
        <form class="layui-form" lay-filter="component-form-element1" style="padding-left:0;padding-right:0;">
            <div class="layui-col-md12" style="background:#fff;">
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;display: flex;align-items: center;">
                    <div class="layui-col-xs3 title">
                        <img src="../addons/sz_yi/static/images/gogo_order_avatar.jpg" alt="" style="width:100px;">
                    </div>
                    <div class="layui-col-xs8 val" style="text-align: center;font-weight:bold;font-size:20px;">
                        业务账单
                    </div>
                </div>
                <div class="line layui-col-xs12"></div>
                <div class="layui-col-xs12" style="padding:10px;box-sizing: border-box;">
                    <div class="layui-col-xs3 title">付款客户</div>
                    <div class="layui-col-xs8 val" style="text-align: center;font-size:15px;font-weight: bold;">
                        <?php  echo $order['payer_name'];?>
                    </div>
                </div>

                <div class="layui-col-xs12">
                    <table class="layui-table" style="margin-top:0;">
                        <thead>
                        <tr style="background: rgb(189,215,238);">
                            <th style="width:10%;">序号</th>
                            <th style="width:28%;">主提单号</th>
                            <th style="width:21%;">港口</th>
                            <th>目的港</th>
                            <th>订单详情</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td><?php  echo $order['lading_no'];?></td>
                            <td><?php  echo $order['fPort'];?></td>
                            <td><?php  echo $order['destination_port'];?></td>
                            <td>
                                <div class="layui-btn layui-btn-xs" onclick="info(<?php  echo $order['id'];?>)">查看详情</div>
                            </td>
                        </tr>
                        <tr style="background: rgb(189,215,238);color:#000;font-weight: bold;">
                            <td rowspan="3">合计</td>
                            <td>应付合计</td>
                            <td>人民币</td>
                            <td colspan="2"><?php  echo $order['price'];?></td>
                        </tr>
                        <tr style="background: rgb(189,215,238);color:#000;font-weight: bold;">
                            <td>开票税费</td>
                            <td>人民币</td>
                            <td colspan="2"><?php  echo $order['invoicing_tax'];?></td>
                        </tr>
                        <tr style="background: rgb(189,215,238);color:#000;font-weight: bold;">
                            <td>实际应付</td>
                            <td>人民币</td>
                            <td colspan="2"><?php  echo $order['real_price'];?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>


                <div class="layui-form-item" style="margin-top: 15px;text-align: center;">
                    <?php  if($order['status']==1) { ?>
                        <button class="layui-btn layui-btn-normal submit" lay-submit="" lay-filter="component-form-element" style="background:#F7931E !important;margin-bottom:15px;">订单确认</button>
<!--                        <div class="layui-btn layui-btn-danger" onclick="cancel()" style="margin-bottom:15px;">冻结</div>-->
                    <?php  } ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="layui-fluid" style="margin-bottom:30px;">
    <div class="layui-row layui-col-space15" style="text-align: center;">
        <div class="layui-btn layui-btn-lg" onclick="chat()" style="display:flex;align-items: center;justify-content: center;font-weight: bold;border-radius:30px;"><img src="../addons/sz_yi/static/images/chat.png" alt="" style="width:30px;margin-right:10px;">我要留言</div>
    </div>
</div>

<div id="real_verify" style="display:none;">
    <!--实名认证-->
    <div class="layui-fluid">
        <div class="layui-row layui-col-space15" style="padding-top:5px;">
            <div class="layui-col-xs12 search_info">
                <form class="layui-form" lay-filter="verify-element1">
                    <div class="layui-form-item">
                        <div class="layui-col-xs4 label_name">
                            身份证号
                        </div>
                        <div class="layui-col-xs8 label_val">
                            <input type="text" class="layui-input" name="idcard" value="" lay-verify="required" placeholder="请输入法人的身份证号">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-col-xs4 label_name">
                            手机号码
                        </div>
                        <div class="layui-col-xs8 label_val">
                            <input type="number" class="layui-input" name="tel" lay-verify="required" placeholder="请输入法人的手机号码" value="">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <div class="layui-col-xs4 label_name">
                            企业名称
                        </div>
                        <div class="layui-col-xs8 label_val">
                            <input type="text" class="layui-input" name="enterprise_name" value="" lay-verify="required" placeholder="请输入企业名称">
                        </div>
                    </div>
                    <div class="layui-col-xs12">
                        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="verify-element" style="width:100%;">立即查询</button>
                    </div>
                </form>
            </div>


            <form class="layui-form check_form" lay-filter="component-form-element2">
                <div class="layui-col-xs12 title_text" id="title_text"></div>
                <!--工商照面-->
                <div class="layui-col-xs12 question_1">

                </div>
            </form>
        </div>
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
            elem: '#etd_date'
            ,format: 'yyyy-MM-dd'
        });

        laydate.render({
            elem: '#making_date'
            ,type: 'datetime'
            ,format: 'yyyy-MM-dd HH:mm'
        });

        laydate.render({
            elem: '#end_date'
            ,type: 'datetime'
            ,format: 'yyyy-MM-dd HH:mm'
        });

        form.render(null,'component-form-element1');
        form.render(null,'verify-element1');

        form.on('select(is_penalty)',function(data){
            var val = data.value;

            if(val==1){
                $('.is_penalty2').hide();
            }else if(val==2){
                $('.is_penalty2').show();
            }
        });

        form.on('select(is_baoshui)',function(data){
            var val = data.value;

            if(val==1){
                $('.is_beian').hide();
            }else if(val==2){
                $('.is_beian').show();
            }
        });

        form.on('select(data_service)',function(data){
            var val = data.value;

            if(val==1 || val==2){
                $('.data_service3').hide();
            }else if(val==3){
                $('.data_service3').show();
            }
        });

        form.on('select(is_wait)',function(data){
            var val = data.value;

            if(val==1){
                $('.is_wait2').hide();
            }else if(val==2){
                $('.is_wait2').show();
            }
        });

        //上一页
        $('.up').click(function(){
            let pnum = $('.pageNum').val();
            let num = parseInt(pnum) - 1;
            $('.pageNum').val(num);

            if(num==1){
                $('.ladInfo').show();
                $('.factoryInfo').hide();
                $('.boxInfo').hide();
                $('.orderInfo').hide();

                $('.up').hide();
                $('.down').show();
                $('.submit').hide();
            }else if(num==2){
                $('.ladInfo').hide();
                $('.factoryInfo').show();
                $('.boxInfo').hide();
                $('.orderInfo').hide();
                $('.submit').hide();
            }else if(num==3){
                $('.ladInfo').hide();
                $('.factoryInfo').hide();
                $('.boxInfo').show();
                $('.orderInfo').hide();

                $('.down').show();
                $('.submit').hide();
            }else if(num==41){
                $('.ladInfo').hide();
                $('.factoryInfo').hide();
                $('.boxInfo').hide();
                $('.orderInfo').show();
            }
        });

        //下一页
        $('.down').click(function(){
            let pnum = $('.pageNum').val();
            let num = 1 + parseInt(pnum);
            $('.pageNum').val(num);

            if(num==2){
                $('.ladInfo').hide();
                $('.factoryInfo').show();
                $('.boxInfo').hide();
                $('.orderInfo').hide();
                $('.up').show();
            }else if(num==3){
                $('.ladInfo').hide();
                $('.factoryInfo').hide();
                $('.boxInfo').show();
                $('.orderInfo').hide();
                $('.up').show();
            }else if(num==4){
                $('.ladInfo').hide();
                $('.factoryInfo').hide();
                $('.boxInfo').hide();
                $('.orderInfo').show();
                $('.down').hide();
                $('.up').show();
                $('.submit').show();
            }
        });

        form.on('submit(component-form-element)', function(data){
            layer.load(); //上传loading
            $.ajax({
                url:"https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=freight&op=sureOrderList&m=sz_yi&id=<?php  echo $id;?>&sub=1",
                type:'post',
                dataType:'JSON',
                data:data.field,
                success:function(res){
                    layer.closeAll('loading'); //关闭loading
                    layer.msg(res.msg,{time:3000}, function () {
                        if(res.code == 0)
                        {
                            window.location.href=res.link;
                        }else if(res.code==-1){
                            //进行实名认证
                            layer.open({
                                type: 2,
                                title: '用实名认证',
                                content: res.link,
                                area:['100%','100%']
                            });
                        }
                    });
                },
                error:function (data) {
                    layer.msg('系统错误',{time:2000});
                }
            });

            return false;
        });

        $('#lading_no').bind('input propertychange', function() {
            $('#lading_no2').val($(this).val());
        });
    });

    function cancel(){
        var layer = layui.layer;

        layer.confirm('确定要冻结此拖车订单吗?',{
            icon:3,
            btn:['确定','取消']
        },function() {
            $.ajax({
                url: "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=freight&op=sureOrderList&m=sz_yi",
                type: 'post',
                dataType: 'JSON',
                data: {'id':"<?php  echo $id;?>",'sub':2},
                success: function (res) {
                    layer.msg(res.msg, {time: 3000}, function () {
                        if (res.code == 0) {
                            window.location.reload();
                        }
                    });
                },
                error: function (data) {
                    layer.msg('系统错误', {time: 2000});
                }
            });
        });
    }

    function chat(){
        var layer = layui.layer;

        window.location.href='https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=freight&op=chat_page&m=sz_yi&id='+<?php  echo $order['id'];?>;
    }

    function info(id){
        var layer = layui.layer
            , $ = layui.jquery;

        window.location.href='https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=freight&p=freight&op=order_detail&m=sz_yi&id='+id;
    }
</script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>