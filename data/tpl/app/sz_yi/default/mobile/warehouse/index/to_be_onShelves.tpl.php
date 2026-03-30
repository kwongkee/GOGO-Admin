<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH));?>
<style>
    .banner img{width:100%;}
    div{overflow: visible;}

    .info_box_top{border-top:1px solid #eee;border-bottom:1px solid #eee;}
    .info_box{margin-top:10px;float:left;width:100%;}
    .info_box .layui-col-xs12{background:#fff;}
    .info_search_box .order_head{padding:10px;box-sizing:border-box;background:#1790FF;color:#fff;}
    .info_search_box .disf{padding:10px;box-sizing:border-box;color:#666;}
    .info_box .title{color:#666;padding:10px 10px;box-sizing: border-box;}
    .info_box .val{padding:10px 10px;box-sizing: border-box;}
    .info_box .line{width:100%;height:1px;background:#eee;}

    .info_box .info_box_bottom{border-top:1px solid #eee;padding:10px;box-sizing: border-box;}
    .info_box .info_box_bottom .btn_desc{color:#999;}
    .info_box .info_box_bottom .layui-col-xs4{text-align: right;}
    .info_box .title_bar{text-align:center;padding:10px 0;box-sizing: border-box;border-bottom:1px solid #eee;color:#fff;background:#1790FF;}

    /**layui框架**/
    .layui-icon-ok:before{content:"√"}
    .layui-form-checkbox{width:18px;height:18px;line-height: 18px;padding-right:18px;margin-right:2px;}
    .layui-form-checkbox i{width:18px;height:18px;border-left:1px solid #d2d2d2;}
    .layui-btn-normal{background:#1790FF;}
    .layui-layer-hui .layui-layer-content{color:#fff;}
</style>

<div class="layui-col-xs12">
    <form class="layui-form" lay-filter="component-form-element">
        <div class="info_box info_box_top">
            <div class="layui-col-xs12">
                <div class="layui-col-xs4 title disf" id="camera_call">快递单号<img src="../addons/sz_yi/static/images/camera.png" alt="" style="width:25px;margin-left:5px;"></div>
                <div class="layui-col-xs8 val disf" style="padding: 5px 10px 5px 0;box-sizing: border-box;">
                    <input type="text" name="express_no" id="express_no" class="layui-input" placeholder="请扫描快递条形码" lay-verify="required" readonly>

                </div>
            </div>
        </div>
    </form>
    <div class="info_search">
        <?php  if(empty($list)) { ?>
            <div class="layui-col-xs12" style="text-align:center;">
                <img src="../addons/sz_yi/static/warehouse/no_log.png" alt="" style="width:120px;">
                <p>暂无记录！</p>
            </div>
        <?php  } else { ?>
            <?php  if(is_array($list)) { foreach($list as $k => $v) { ?>
                <div class="info_box info_search_box">
                    <div class="layui-col-xs12 order_head">
                        快递单号：<?php  echo $v['express_no'];?>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        买家昵称：<?php  echo $v['name'];?>(ID:<?php  echo $v['id'];?>)
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        联系电话：<?php  echo $v['mobile'];?>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        状态：<span style="color:#ff2222;">待上架</span>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12 disf">
                        确认验货时间：<?php  echo date('Y-m-d H:i',$v['inspection_time'])?>
                    </div>
                    <div class="line layui-col-xs12"></div>
                    <div class="layui-col-xs12" style="padding:15px 10px;box-sizing: border-box;text-align: right;">
                        <div class="layui-btn layui-btn-primary" style="border:1px solid #1790FF;color:#1790FF;" onclick="on_shelf(<?php  echo $v['orderid'];?>,'<?php  echo $v['express_no'];?>')">立即上架</div>
                    </div>
                </div>
            <?php  } } ?>
        <?php  } ?>
    </div>
</div>

<script>
    layui.use(['layer','jquery','form','element','upload'],function() {
        var layer = layui.layer
            , $ = layui.jquery
            , form = layui.form
            , upload = layui.upload;

        form.render(null, 'component-form-element');

        form.on('submit(component-form-element1)', function(data){
            //layer.msg(JSON.stringify(data.field));

            $.ajax({
                url:"./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=sure_inspection",
                method:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.result.msg,{time:3000}, function () {
                        if(res.status == 0)
                        {
                            window.location.reload();
                            // window.location.href = "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=warehouse_goods_up_down"
                        }
                    });
                },
                error:function (data) {
                    layer.msg("系统错误",{time:3000});
                }
            });
            return false;
        });
    });
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<script>
    require(['https://res.wx.qq.com/open/js/jweixin-1.2.0.js'],function(wx){
        window.shareData = <?php  echo json_encode($_W['shopshare'])?>;

        jssdkconfig = <?php  echo json_encode($_W['account']['jssdkconfig']);?> || { jsApiList:[] };

        jssdkconfig.debug = false;
        jssdkconfig.jsApiList = ['checkJsApi','onMenuShareTimeline','onMenuShareAppMessage','onMenuShareQQ','onMenuShareWeibo','showOptionMenu','scanQRCode'];
        wx.config(jssdkconfig);

        $('#camera_call').click(function(){
            wx.scanQRCode({
                desc: 'scanQRCode desc',
                needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                scanType: ["barCode"], // 可以指定扫二维码还是一维码，默认二者都有    "qrCode",
                success: function (res) {
                    if(res.resultStr=='scan resultStr is here'){
                        alert('请使用手机打开扫描！');return;
                    }
                    var result = res.resultStr.split(',');
                    // 回调
                    $('#express_no').val(result[1]);

                    //查询快递单号是否已确认验货
                    if(result[1]){
                        express_info(result[1]);
                    }
                },
                error: function(res){
                    if(res.errMsg.indexOf('function_not_exist') > 0){
                        alert('版本过低请升级')
                    }
                }
            });
        });

        //默认
        // express_info(1245444444);
    });

    function express_info(express_no){
        var layer = layui.layer
            , $ = layui.jquery
            , form = layui.form;
        $.ajax({
            url:"./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_onShelves",
            method:'post',
            data:{'express_no':express_no,'type':2,'ispost':1},
            dataType:'JSON',
            success:function(res){
                layer.msg(res.result.msg,{time:3000}, function () {
                    if(res.status == 0) {
                        //有数据
                        var html = '<div class="info_box info_search_box">\n' +
                            '                <div class="layui-col-xs12 order_head">\n' +
                            '                    快递单号：'+res.result.data.express_no+'\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    买家昵称：'+res.result.data.name+'(ID:'+res.result.data.id+')\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    联系电话：'+res.result.data.mobile+'\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    状态：<span style="color:#ff2222;">待上架</span>\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    确认验货时间：'+res.result.data.inspection_time+'\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12" style="padding:15px 10px;box-sizing: border-box;text-align: right;">\n' +
                            '                    <div class="layui-btn layui-btn-primary" style="border:1px solid #1790FF;color:#1790FF;" onclick="on_shelf('+res.result.data.orderid+","+"'"+res.result.data.express_no+"'"+')">立即上架</div>\n' +
                            '                </div>\n' +
                            '            </div>';

                        $('.info_search').html(html);
                    }

                });
            },
            error:function (data) {
                layer.msg("系统错误",{time:2000});
            }
        });
        form.render(null, 'component-form-element');
    }

    function on_shelf(id,express_no){
        var layer = layui.layer,$ = layui.$;
        layer.confirm('确认快递单号['+express_no+']的货物上架吗？',function(){
            require(['https://res.wx.qq.com/open/js/jweixin-1.2.0.js'],function(wx) {
                window.shareData = <?php  echo json_encode($_W['shopshare'])?>;

                jssdkconfig = <?php  echo json_encode($_W['account']['jssdkconfig']);?> || { jsApiList:[] };

                jssdkconfig.debug = false;
                jssdkconfig.jsApiList = ['checkJsApi', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'showOptionMenu', 'scanQRCode'];
                wx.config(jssdkconfig);
                wx.scanQRCode({
                    desc: 'scanQRCode desc',
                    needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
                    scanType: ["barCode"], // 可以指定扫二维码还是一维码，默认二者都有    "qrCode",
                    success: function (res) {
                        if (res.resultStr == 'scan resultStr is here') {
                            alert('请使用手机打开扫描！');
                            return;
                        }
                        var result = res.resultStr.split(',');
                        // 回调
                        $('#express_no').val(result[1]);

                        if (result[1]) {
                            //货物上架，记录货架号
                            layer.confirm('确认将货物上架在货架号['+result[1]+']上吗？',function() {
                                $.ajax({
                                    url: "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_onShelves",
                                    method: 'post',
                                    data: {
                                        'express_no': express_no,
                                        'orderid': id,
                                        'ispost': 2,
                                        'shelf_number': result[1]
                                    },
                                    dataType: 'JSON',
                                    success: function (res) {
                                        layer.msg(res.result.msg, {time: 3000}, function () {
                                            if (res.status == 0) {
                                                window.location.reload();
                                            }
                                        })
                                    }
                                });
                            });
                        }
                    },
                    error: function (res) {
                        if (res.errMsg.indexOf('function_not_exist') > 0) {
                            alert('版本过低请升级')
                        }
                    }
                });
            });
        });
    }
</script>
