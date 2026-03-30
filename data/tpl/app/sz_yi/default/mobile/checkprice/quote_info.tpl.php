<?php defined('IN_IA') or exit('Access Denied');?><!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
        <link rel="stylesheet" href="../addons/sz_yi/static/warehouse/static_file/style.css" media="all">
        <link rel="stylesheet" href="../addons/sz_yi/static/warehouse/static_file/layui.css" media="all">
        <link rel="stylesheet" type="text/css" href="../addons/sz_yi/static/warehouse/static_file/hui.css" />
        <script type="text/javascript" src="../addons/sz_yi/static/warehouse/static_file/hui.js" charset="UTF-8"></script>
        <script src="../addons/sz_yi/static/warehouse/static_file/jquery-3.2.1.min.js"></script>
        <script src="../addons/sz_yi/static/warehouse/static_file/layui.js"></script>
        <title>报价详情</title>
        <style>
            *{font-size:15px;}
            .disf{display:flex;align-items: center;}
            .layui-btn-normal{background:#1790FF;}
            div{overflow: visible;}
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
        </style>
    </head>
    <body>
        <div class="page_head">
            <div class="left" onclick="javascript:window.history.back(-1);">
                <div class="back"></div>
                <div style="font-size:15px;padding-top:2px;">返回</div>
            </div>
        </div>
        <div class="layui-fluid">
            <div class="layui-card">
                <div class="layui-card-header">
                    报价详情[<?php  echo $order['ordersn'];?>]
                </div>
                <div class="layui-card-body" style="padding:0;padding-bottom: 10px;">
                    <form class="layui-form" action="" lay-filter="component-form-demo1">
                        <input type="number" name="id" value="<?php  echo $order['id'];?>" style="display: none;">
                        
                        <fieldset class="layui-elem-field template_box">
                            <legend>询价详情</legend>
                            <div class="layui-field-box">
                                <?php  if(!empty($order['content'])) { ?>
                                    <?php  if(is_array($order['form'])) { foreach($order['form'] as $k => $v) { ?>
                                        <div class="layui-form-item">
                                            <label class="layui-form-label"><?php  echo $v['label_name'];?></label>
                                            <div class="layui-input-block layui-select-fscon">
                                                <?php  echo $order['content'][$k];?>
                                            </div>
                                        </div>
                                    <?php  } } ?>
                                <?php  } ?>

                                <?php  if(!empty($order['text'])) { ?>
                                    <div class="layui-form-item">
                                        <label class="layui-form-label">询价信息</label>
                                        <div class="layui-input-block layui-select-fscon">
                                            <?php  echo $order['text'];?>
                                        </div>
                                    </div>
                                <?php  } ?>
                                
                                <?php  if(!empty($order['files'])) { ?>
                                    <div class="layui-form-item">
                                        <label class="layui-form-label">客户文件</label>
                                        <div class="layui-input-block layui-select-fscon">
                                            <?php  if(is_array($order['files'])) { foreach($order['files'] as $k => $v) { ?>
                                                <a href="https://www.gogo198.net<?php  echo $v['files'];?>" target="_blank"><?php  echo $v['filenames'];?></a>
                                            <?php  } } ?>
                                        </div>
                                    </div>
                                <?php  } ?>
                            </div>
                        </fieldset>
                        
                        <?php  if(!empty($quote_info)) { ?>
                            <fieldset class="layui-elem-field quote_box">
                                <legend style="width:unset;border-bottom:0;">报价区</legend>
                                <div class="layui-field-box">
                                    <?php  if(!empty($quote_info['content'])) { ?>
                                        <?php  if(is_array($quote_info['form'])) { foreach($quote_info['form'] as $idx => $vo) { ?>
                                            <div class="layui-form-item">
                                                <div class="layui-form-label"><?php  echo $vo['label_name'];?></div>
                                                <div class="layui-input-block">
                                                    <?php  echo $quote_info['content'][$idx];?>
                                                </div>
                                            </div>
                                        <?php  } } ?>
                                    <?php  } ?>
                                    <?php  if(!empty($quote_info['text'])) { ?>
                                    <div class="layui-form-item">
                                        <div class="layui-form-label">报价信息</div>
                                        <div class="layui-input-block">
                                            <?php  echo $quote_info['text'];?>
                                        </div>
                                    </div>
                                    <?php  } ?>
                                </div>
                            </fieldset>
                        <?php  } else { ?>
                            <div class="layui-form-item">
                                <label class="layui-form-label">报价模板</label>
                                <div class="layui-input-block">
                                    <select name="template_id" lay-search lay-verify="required" lay-filter="template_id">
                                        <option value="">请选择报价模板</option>
                                        <option value="-1">无合适模板，自定义模板</option>
                                        <?php  if(is_array($quote_temp)) { foreach($quote_temp as $idx => $vo) { ?>
                                            <option value="<?php  echo $vo['id'];?>">模板<?php  echo $idx+1;?></option>
                                        <?php  } } ?>
                                    </select>
                                </div>
                            </div>
                            <fieldset class="layui-elem-field quote_box">
                                <legend style="width:unset;border-bottom:0;">报价区</legend>
                                <div class="layui-field-box">
                                    
                                </div>
                            </fieldset>
                        <?php  } ?>
                        
                        <div class="layui-form-item layui-layout-admin">
                            <div class="layui-input-block">
                                <div class="layui-footer" style="left: 0;text-align:center;">
                                    <?php  if(empty($quote_info)) { ?>
                                        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element2">确认报价</button>
                                    <?php  } ?>
                                    <?php  if($order['quote_id']==$quote_info['id'] && $quote_info['status']==2) { ?>
                                        <button class="layui-btn layui-btn-normal" lay-submit lay-filter="component-form-element3">确认接单</button>
                                    <?php  } ?>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js" type="text/javascript"></script>
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
                
                form.on('select(template_id)',function(data){
                    let val = data.value;
                    if(val==-1){
                        layer.open({
                            type: 2,
                            title: '添加模板',
                            shadeClose: true,
                            shade: 0.3,
                            area: ['100%', '100%'],
                            content: "./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=save_template&buss_id=<?php  echo $order['buss_id'];?>",
                        });
                    }else{
                        $.ajax({
                            url:"./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=select_template",
                            method:'post',
                            data:{'id':val},
                            dataType:'JSON',
                            success:function(res){
                                if(res.status==0){
                                    $('.quote_box').find('.layui-field-box').html('');
                                    let html = '';
                                    for(let i=0;i<res.result.content.length;i++){
                                        
                                        html += '<div class="layui-form-item">\n' +
                                        '                            <div class="layui-form-label">'+res.result.content[i]['label_name']+'</div>\n' +
                                        '                            <div class="layui-input-block">\n';
                                        if(res.result.content[i]['label_value']==1){
                                            html += '<input type="text" name="content[]" class="layui-input" value="" placeholder="" lay-verify="required"/>\n';
                                        }else if(res.result.content[i]['label_value']==2){
                                            html += '<input type="number" name="content[]" class="layui-input" value="" placeholder="" lay-verify="required"/>\n';
                                        }else if(res.result.content[i]['label_value']==3){
                                            html += ' <input type="text" name="content[]" class="layui-input" id="date'+res.result.content[i]['label_rand']+'" value="" placeholder="yymmddhhii" lay-verify="required">\n';
                                        }else if(res.result.content[i]['label_value']==4){
                                            
                                            html += '<select name="content[]" lay-search lay-verify="required">\n';
                                            for(let j=0;j<res.result.content[i]['label_select'].length;j++){
                                                html += '<option value="'+res.result.content[i]['label_select'][j]+'">'+res.result.content[i]['label_select'][j]+'</option>\n';
                                            }
                                            html += '</select>\n';
                                        }
                                        html += '                            </div>\n' +
                                            '                        </div>';
                                    }
                                    
                                    $('.quote_box').find('.layui-field-box').append(html);
                                    form.render(null,'component-form-demo1');
                                    for(let i=0;i<res.result.content.length;i++){
                                        if(res.result.content[i]['label_value']==3){
                                            laydate.render({
                                                elem: '#date'+res.result.content[i]['label_rand'] //指定元素
                                            });
                                        }
                                    }
                                }
                            }
                        });
                    }
                });
                
                form.on('submit(component-form-element2)', function(data){
                    // JSON.stringify()
                    // console.log(data.field);return false;
                    $.ajax({
                        url:"./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=quote_info",
                        method:'post',
                        data:data.field,
                        dataType:'JSON',
                        success:function(res){
                            layer.msg(res.result.msg,{time:2000}, function () {
                                if(res.status == 0)
                                {
                                    window.location.reload();
                                    // window.location.href="./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=quote_list";
                                }else if(res.status == -1){
                                    layer.open({
                                        type: 2,
                                        title: '请先登录',
                                        shadeClose: true,
                                        shade: 0.3,
                                        area: ['100%', '100%'],
                                        content: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=login&open=3",
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
                
                form.on('submit(component-form-element3)', function(data){
                    $.ajax({
                        url:"./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=quote_info",
                        method:'post',
                        data:{'id':"<?php  echo $id;?>",'check':1},
                        dataType:'JSON',
                        success:function(res){
                            layer.msg(res.result.msg,{time:2000}, function () {
                                if(res.status == 0)
                                {
                                    //跳转到相应业务管理系统
                                    window.location.reload();
                                }else if(res.status == -1){
                                    window.location.href="./index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=merchant_reg&open=3";
                                }else if(res.status==-2){
                                    
                                }else if(res.status==-3){
                                    layer.open({
                                        type: 2,
                                        title: '请先登录',
                                        shadeClose: true,
                                        shade: 0.3,
                                        area: ['100%', '100%'],
                                        content: "./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=login&open=3",
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
                
                
                /*带图片的微信分享*/
                let imgUrl = "https://shop.gogo198.cn/collect_website/public/logo.png";
            	let lineLink = "https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=quote&m=sz_yi&op=quote_info&id=<?php  echo $order['id'];?>";
            	let descContent = "请打开询价详情，进行报价吧！";
            	let shareTitle = "GOGO邀请你报价";
            	wx.config({
            		debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            		appId: "<?php  echo $jssdkconfig['appId'];?>", // 必填，公众号的唯一标识
            		timestamp: '<?php  echo $jssdkconfig["timestamp"];?>', // 必填，生成签名的时间戳
            		nonceStr: '<?php  echo $jssdkconfig["nonceStr"];?>', // 必填，生成签名的随机串
            		signature: '<?php  echo $jssdkconfig["signature"];?>',// 必填，签名，见附录1
            		jsApiList: ['checkJsApi','updateAppMessageShareData'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            	});
            	wx.checkJsApi({
        			jsApiList: ['updateAppMessageShareData'],
        			success: function (res) {
        				// console.log(JSON.stringify(res));
        				// alert(JSON.stringify(res.checkResult.getLocation));
        				if (res.checkResult.updateAppMessageShareData == false) {
        					alert('你的微信版本太低，不支持微信JS接口，请升级到最新的微信版本！');
        					return;
        				}
        			}
        		});
            	wx.ready(function(){
        		    wx.updateAppMessageShareData({ 
                        title: shareTitle, // 分享标题
                        desc: descContent, // 分享描述
                        link: lineLink, // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                        imgUrl: imgUrl, // 分享图标
                        success: function () {
                          // 设置成功
                        //   layer.msg('分享成功');
                        },
                        fail: function (erres) {
                            // alert('失败：', erres)
                        }
                    });
            	});
            	wx.error(function(res){
                  // config信息验证失败会执行error函数，如签名过期导致验证失败，具体错误信息可以打开config的debug模式查看，也可以在返回的res参数中查看，对于SPA可以在这里更新签名。
                  console.log(res);
                });
            });
        </script>
    </body>
</html>