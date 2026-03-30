<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>
<title>发起询价</title>
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
<link rel="stylesheet" href="../addons/sz_yi/static/css/ajaxupload/font-awesome.min.css?x=123">
<link rel="stylesheet" href="../addons/sz_yi/static/css/ajaxupload/style.css?x=2323">
<div class="page_head">
    <div class="left" onclick="javascript:window.history.back(-1);">
        <div class="back"></div>
        <div style="font-size:15px;padding-top:2px;">返回</div>
    </div>
</div>
<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">
            <?php  echo $template['name'];?>服务询价
        </div>
        <div class="layui-card-body" style="padding:0;padding-bottom: 10px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="buss_id" value="<?php  echo $buss_id;?>" style="display: none;">
                <input type="number" name="type" value="<?php  echo $type;?>" style="display: none;">
                <?php  if(empty($type)) { ?>
                    <?php  if(is_array($template['content'])) { foreach($template['content'] as $vo) { ?>
                        <div class="layui-form-item">
                            <label class="layui-form-label"><?php  echo $vo['label_name'];?></label>
                            <div class="layui-input-block layui-select-fscon">
                                <?php  if($vo['label_value']==1) { ?>
                                    <input type="text" class="layui-input" name="content[]" value="" placeholder="" lay-verify="required"/>
                                <?php  } ?>
                                <?php  if($vo['label_value']==2) { ?>
                                    <input type="number" class="layui-input" name="content[]" value="" placeholder="" lay-verify="required"/>
                                <?php  } ?>
                                <?php  if($vo['label_value']==3) { ?>
                                    <input type="text" class="layui-input" name="content[]" id="date<?php  echo $vo['label_rand'];?>" value="" placeholder="yymmddhhii" lay-verify="required">
                                <?php  } ?>
                                <?php  if($vo['label_value']==4) { ?>
                                    <select name="content[]" lay-search>
                                        <?php  if(is_array($vo['label_select2'])) { foreach($vo['label_select2'] as $vo2) { ?>
                                            <option value="<?php  echo $vo2;?>"><?php  echo $vo2;?></option>
                                        <?php  } } ?>
                                    </select>
                                <?php  } ?>
                            </div>
                        </div>
                    <?php  } } ?>
                <?php  } ?>
                <?php  if(!empty($type)) { ?>
                    <div class="layui-form-item">
                        <label class="layui-form-label">询价信息</label>
                        <div class="layui-input-block layui-select-fscon">
                            <textarea name="text" id="text" class="layui-textarea" placeholder="请输入询价文本信息" lay-verify="required"></textarea>
                        </div>
                    </div>
                <?php  } ?>
                <!--提交询价文件-->
                <div class="layui-form-item">
                    <div class="layui-form-label">提交文件</div>
                    <div class="layui-input-block">
                        <div class="uploadbox" style="margin-top:15px;">
    	                    <div id="image_box" style="margin-bottom:15px;"></div>
	                        <div class="fileupload_box">
        						<label class="fileupload">
        							<div class="pic_show" style="background:#0e2e68;">
        								
        								<div class="text">
        									<i class="icon icon_plus"></i>
        									<span class="f12" style="color:#fff;">上传文件</span>
        								</div>
        								
        								<img id="image_file_image" src=""  />
        								<input type="button" class="filebox" name="image_file" id="image_file" value="" />	
        								<div class="fileuploading"></div>
        							</div>
        						</label>
        					</div>
    	                </div>
                    </div>
                </div>
                
                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn" lay-submit="" lay-filter="component-form-demo">提交询价</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/jquery-1.8.2.min.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/ajaxupload.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/edit_deal_item.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/juUI.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/plupload.full.min.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/script.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/jquery.bgiframe.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/jquery.weebox.js"></script>
<script type="text/javascript" src="../addons/sz_yi/static/js/ajaxupload/touch.js"></script>
<script>
    layui.use(['layer', 'form', 'table', 'upload', 'laydate'], function () {
        var $ = layui.$
            , layer = layui.layer
            , form = layui.form
            , element = layui.element
            , laydate = layui.laydate
            , upload = layui.upload
            , table = layui.table;

        get_file_fun("image_file");
        
        form.render(null, 'component-form-demo1');

        <?php  if(!empty($template['content'])) { ?>
            <?php  if(is_array($template['content'])) { foreach($template['content'] as $vo) { ?>
                <?php  if($vo['label_value']==3) { ?>
                    laydate.render({
                        elem: "#date<?php  echo $vo['label_rand'];?>" //指定元素
                    });
                <?php  } ?>
            <?php  } } ?>
        <?php  } ?>

        upload.render({
            elem: '#pic_list-upload'
            ,url: "./index.php?i=3&c=entry&do=util&m=sz_yi&p=uploader"
            ,accept: 'image'
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

        /* 监听提交 */
        form.on('submit(component-form-demo)', function(data){
            $.ajax({
                url:"./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=save_inquiry",
                type:'post',
                data:data.field,
                dataType:'JSON',
                success:function(res){
                    layer.msg(res.result.msg,{time:3000}, function () {
                        if(res.status == 0)
                        {
                            // window.location.href="./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=thanks";
                            window.history.back(-1);
                        }else if(res.status==-1){
                            window.location.href="./index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=login&open=1&inquiry_id="+res.result.inquiry_id;
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

    var MAX_FILE_SIZE = "3MB";
    var UPLOAD_URL ='https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=checkprice&p=index&m=sz_yi&op=upload_file';
	var UPLOAD_SWF='../addons/sz_yi/static/js/ajaxupload/Moxie.swf';
	var UPLOAD_XAP='../addons/sz_yi/static/js/ajaxupload/Moxie.xap';
	var ALLOW_IMAGE_EXT= "gif,jpg,jpeg,png,bmp,xls,excel,xlsx,word,pdf";
	var MAX_IMAGE_SIZE= "3MB";
	function get_file_fun(name){
		$("#"+name).ui_upload({
		    multi:true,
			FileUploaded:function(ajaxobj){
			    let name = $("#image_box .image_item").length+1;
				if($("#image_box .image_item").length>=100) {
					alert("最多只能上传100个文件");
				}
 				else if(ajaxobj.error==0) {
					alert(ajaxobj.message);
				}
				else {
					$("#image_box").append(
		   				'<div class="image_item" style="display: inline-block;margin-right: 15px;width:90px;text-align:center;margin-bottom:15px;">'+
							'<div class="remove_image" style="color:#000;font-size:15px;"><i class="fa fa-remove"></i></div>'+
							'<img src="'+ajaxobj.file_path+'" width=90 height=90 class="b_radius6" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png" />'+
							'<input type="hidden" name="file[]" class="files" value="'+ajaxobj.file_path+'"  />'+
							'<input type="text" name="filename[]" class="filenames" value="文件'+name+'" placeholder="填写文件名称" style="width:100%;height:30px;border: 1px solid #D2A778;">'+
						'</div>'
					);
		   			bind_del_image(); // 删除已上传的图片
		   			if($("#image_box .image_item").length>=100) {
		   			    hide_imgupload(); // 上传5张图片后，隐藏上传图片按钮
		   			}
 				}
			},Error:function(error) {
				alert(error.message);
	 		}
	 	});
	}
</script>
