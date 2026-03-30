<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_innerpage_header', TEMPLATE_INCLUDEPATH));?>
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
</style>

<div class="layui-col-xs12">
    <form class="layui-form" lay-filter="component-form-element">
        <div class="info_box info_box_top">
            <div class="layui-col-xs12">
                <div class="layui-col-xs4 title disf" id="camera_call">快递单号<img
                        src="../addons/sz_yi/static/images/camera.png" alt="" style="width:25px;margin-left:5px;"></div>
                <div class="layui-col-xs8 val disf" style="padding: 5px 10px 5px 0;box-sizing: border-box;">
                    <input type="text" name="express_no" id="express_no" class="layui-input" placeholder="请扫描快递条形码"
                           lay-verify="required" readonly>

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
                包裹单号：<?php  echo $v['ordersn'];?>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
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
                附加方式：<span style="color:#ff2222;"><?php  echo $v['code_name'];?></span>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                附加状态：<span style="color:#ff2222;"><?php  echo $v['attach_status'];?></span>
            </div>
            <?php  if(!empty($v['remark'])) { ?>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                备注：<?php  echo $v['remark'];?>
            </div>
            <?php  } ?>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12 disf">
                申请附加时间：<?php  echo date('Y-m-d H:i',$v['createtime'])?>
            </div>
            <div class="line layui-col-xs12"></div>
            <div class="layui-col-xs12" style="padding:15px 10px;box-sizing: border-box;text-align: right;">
                <div class="layui-btn layui-btn-primary" style="border:1px solid #1790FF;color:#1790FF;" onclick="chayan(<?php  echo $v['orderid'];?>,'<?php  echo $v['express_no'];?>','<?php  echo $v['ordersn'];?>',<?php  echo $v['status2'];?>)">立即附加
                </div>
            </div>
        </div>
        <?php  } } ?>
        <?php  } ?>
    </div>
</div>



<script>
    layui.use(['layer', 'jquery', 'form', 'element', 'upload'], function () {
        var layer = layui.layer
            , $ = layui.jquery
            , form = layui.form
            , upload = layui.upload;

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
                            window.location.reload();
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

    function chayan(id, express_no,ordersn,status2) {
        var layer = layui.layer, $ = layui.$;

        layer.open({
            type: 2,
            title:"立即分拆["+ordersn+"]",
            area: ['100%', '100%'], //宽高
            content: './index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=show_attachment_box&orderid='+id+'&express_no='+express_no+'&ordersn='+ordersn+'&status2='+status2
        });
        // $('.inspection_box').find('input[name="id"]').val(id);
    }
</script>

<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>
<script>
    require(['https://res.wx.qq.com/open/js/jweixin-1.2.0.js'], function (wx) {
        window.shareData = <?php  echo json_encode($_W['shopshare'])?>;

        jssdkconfig = <?php  echo json_encode($_W['account']['jssdkconfig']);?> || { jsApiList:[] };

        jssdkconfig.debug = false;
        jssdkconfig.jsApiList = ['checkJsApi', 'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'showOptionMenu', 'scanQRCode'];
        wx.config(jssdkconfig);

        $('#camera_call').click(function () {
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

                    //查询快递单号是否已确认验货
                    if (result[1]) {
                        express_info(result[1]);
                    }
                },
                error: function (res) {
                    if (res.errMsg.indexOf('function_not_exist') > 0) {
                        alert('版本过低请升级')
                    }
                }
            });
        });

        //默认
        // express_info('SF0123456');
    });

    function express_info(express_no) {
        var layer = layui.layer
            , $ = layui.jquery
            , form = layui.form;
        $.ajax({
            url: "./index.php?i=3&c=entry&p=index&do=warehouse&m=sz_yi&op=to_be_attachment",
            method: 'post',
            data: {'express_no': express_no, 'type': 2, 'ispost': 1},
            dataType: 'JSON',
            success: function (res) {
                layer.msg(res.result.msg, {time: 3000}, function () {
                    if (res.status == 0) {
                        //有数据
                        var html = '<div class="info_box info_search_box">\n' +
                            '                <div class="layui-col-xs12 order_head">\n' +
                            '                    包裹单号：' + res.result.data.ordersn + '\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    快递单号：' + res.result.data.express_no + '\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    买家昵称：' + res.result.data.name + '(ID:' + res.result.data.id + ')\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    联系电话：' + res.result.data.mobile + '\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    附加方式：<span style="color:#ff2222;">' + res.result.data.code_name + '</span>\n' +
                            '                </div>\n'+
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    附加状态：<span style="color:#ff2222;">'+ res.result.data.attach_status +'</span>\n' +
                            '                </div>';

                            if(res.result.data.remark!=''){
                                html += '<div class="line layui-col-xs12"></div>\n' +
                                    '            <div class="layui-col-xs12 disf">\n' +
                                    '                买家备注：'+res.result.data.remark+'\n' +
                                    '            </div>';
                            }
                            html += '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12 disf">\n' +
                            '                    申请附加时间：' + res.result.data.createtime + '\n' +
                            '                </div>\n' +
                            '                <div class="line layui-col-xs12"></div>\n' +
                            '                <div class="layui-col-xs12" style="padding:15px 10px;box-sizing: border-box;text-align: right;">\n' +
                            '                    <div class="layui-btn layui-btn-primary" style="border:1px solid #1790FF;color:#1790FF;" onclick="chayan(' + res.result.data.orderid + "," + "'" + res.result.data.express_no + "'"+ "," + res.result.data.ordersn + "," + res.result.data.status2 +')">立即附加</div>\n' +
                            '                </div>\n' +
                            '            </div>';

                        $('.info_search').html(html);
                    }

                });
            },
            error: function (data) {
                layer.msg("系统错误", {time: 2000});
            }
        });
        form.render(null, 'component-form-element');
    }

</script>
