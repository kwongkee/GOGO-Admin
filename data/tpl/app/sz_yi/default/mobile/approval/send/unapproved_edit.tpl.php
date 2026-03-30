<?php defined('IN_IA') or exit('Access Denied');?><script src="../addons/sz_yi/static/warehouse/static_file/xm-select.js"></script>
<?php (!empty($this) && $this instanceof WeModuleSite) ? (include $this->template('common/warehouse_header', TEMPLATE_INCLUDEPATH)) : (include template('common/warehouse_header', TEMPLATE_INCLUDEPATH));?>

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

    .layui-input-block{padding-right:15px;box-sizing: border-box;}

    .content_box .layui-form-label{padding:0 15px;}

    /**表单样式**/
    .content_box .layui-form-label{line-height:38px;text-overflow: ellipsis;-webkit-line-clamp: 1;overflow: hidden;width: 20%;display: -webkit-box;-webkit-box-orient: vertical;}
    .event_table .content_box{margin-bottom:5px;margin-top:5px;}
    .can_add_box{background: #eee;padding: 10px;box-sizing: border-box;}
    .can_add_box .detail_column .del{color:#ff2222;text-align:right;}
    /*.can_add_box .content_box{margin-bottom:10px;}*/
    .can_add_box .content_box .layui-form-label{line-height:38px;text-overflow: ellipsis;-webkit-line-clamp: 1;overflow: hidden;width: 20%;display: -webkit-box;-webkit-box-orient: vertical;}
    .can_add_box .new_add{background:#1790FF;color:#fff;font-size:15px;text-align: center;padding:7px 0;box-sizing: border-box;border-radius:15px;margin-top:5px;}
    .can_add_box .can_add_content{background:#fff;}
    .can_add_box .detail_column{margin-top:10px;margin-bottom:5px;}
    .layui-upload-list img{max-width:50px;}
    
    .new_add{display:none;}
    /*.event_table .can_add_box:nth-last-child(3) .new_add{display:block;}*/
    /*.event_table .can_add_box:last-child .new_add{display:block;}*/

</style>

<div class="layui-fluid">
    <div class="layui-card">
        <!--<div class="layui-card-header">-->
        <!--    编辑-->
        <!--</div>-->
        <div class="layui-card-body" style="padding: 0px;">
            <form class="layui-form" action="" lay-filter="component-form-demo1">
                <input type="number" name="pa" value="1" style="display: none;">
                <input type="number" name="id" value="<?php  echo $info['id'];?>" style="display: none;">
                <input type="number" name="event_id" value="<?php  echo $info['event_id'];?>" style="display: none;">
                <div class="event_table layui-col-xs12">
                    <?php  $count=0;?>
<!--                    php $added=0;-->
                    <?php  if(is_array($info['template'])) { foreach($info['template'] as $k => $v) { ?>
                        <?php  if($v['can_add']==1) { ?>

<!--                            loop $content['value'] $k3 $v3-->
<!--                                if $added==$k3-->
                                    <?php  if($v['param']==1) { ?>
                                        <div class="layui-col-xs12 content_box">
                                            <label class="layui-form-label">
                                                <?php  echo $v['label_name'];?>
                                            </label>
                                            <div class="layui-input-block layui-select-fscon">
                                                <input class="layui-input" placeholder="请输入<?php  echo $v['label_name'];?>" name="value[]" lay-verify="required" value="<?php  echo $v['valued'];?>">
                                            </div>
                                        </div>
                                    <?php  } ?>
                                    <?php  if($v['param']==2) { ?>
                                        <div class="layui-col-xs12 content_box">
                                            <label class="layui-form-label">
                                                <?php  echo $v['label_name'];?>
                                            </label>
                                            <div class="layui-input-block layui-select-fscon">
                                                <select name="value[]" lay-verify="required">
                                                    <option value="">请选择<?php  echo $v['label_name'];?></option>
                                                        <?php  if(is_array($v['select_value2'])) { foreach($v['select_value2'] as $kk => $vv) { ?>
                                                            <option value="<?php  echo $vv;?>" <?php  if($v['valued']==$vv) { ?>selected<?php  } ?>><?php  echo $vv;?></option>
                                                        <?php  } } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php  } ?>
                                    <?php  if($v['param']==3) { ?>
                                        <div class="layui-col-xs12 content_box">
                                            <label class="layui-form-label">
                                                <?php  echo $v['label_name'];?>
                                            </label>
                                            <div class="layui-input-block layui-select-fscon">
                                                <div id="xm-select-demo<?php  echo $v['random_name'];?>" class="xm-select-demo"></div>
                                                <input type="text" name="xm_demo1" value='<?php  echo $v["select_value2"];?>' style="display:none;">
                                            </div>
                                        </div>
                                    <?php  } ?>
                                    <?php  if($v['param']==4) { ?>
                                        <div class="layui-col-xs12 content_box">
                                            <label class="layui-form-label">
                                                <?php  echo $v['label_name'];?>
                                            </label>
                                            <div class="layui-input-block layui-select-fscon">
                                                <input type="text" name="value[]" class="layui-input" id="date_demo<?php  echo $v['random_name'];?>" placeholder="请选择<?php  echo $v['label_name'];?>"  lay-verify="required" value="<?php  echo $v['valued'];?>">
                                            </div>
                                        </div>
                                    <?php  } ?>
<!--                                /if-->
<!--                                php $added+=1;-->
<!--                            /loop-->
                            <?php  if($v['param']==5) { ?>
                                <div class="layui-col-xs12 content_box">
                                    <label class="layui-form-label">
                                        <?php  echo $v['label_name'];?>
                                    </label>
                                    <div class="layui-input-block layui-select-fscon">
                                        <div class="layui-input-block" style="margin-left:0;">
                                            <div class="layui-upload" style="text-align:left;">
                                                <button type="button" class="layui-btn" id="file-upload<?php  echo $v['random_name'];?>">上传文件</button>
                                                <blockquote class="layui-elem-quote layui-quote-nm yulan" style="margin-top: 10px;">
                                                    预览图：
                                                    <div class="layui-upload-list" id="file-upload-list<?php  echo $v['random_name'];?>">
                                                        <?php  if(is_array($content['list_file'])) { foreach($content['list_file'] as $f1 => $f2) { ?>
                                                        <div style="display: inline-block;margin-top:10px;">
                                                            <img onclick="seePic(this);" src="/attachment/<?php  echo $f2;?>" class="layui-upload-img" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png"><button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button><input type="hidden" name="list_file[]" value="<?php  echo $f2;?>">
                                                            </div>
                                                        <?php  } } ?>
                                                    </div>
                                                </blockquote>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php  } ?>
                        <?php  } ?>
                        <?php  if($v['can_add']==2) { ?>
                            <?php  if($count==0) { ?>
                                <?php  if(is_array($content['add_value'])) { foreach($content['add_value'] as $key => $val) { ?>
                                <div class="can_add_box layui-col-xs12">
                                    <div class="layui-col-xs12 detail_column">
                                        <div class="layui-col-xs6 name">
                                            <input name="info_name" class="info_name" value="<?php  echo $info['name'];?>" style="display:none;">
                                            <input name="info_num" class="info_num" value="<?php  echo $key+1;?>" style="display:none;">
                                            <?php  echo $info['name'];?>明细 <?php  echo $key+1;?>
                                        </div>
                                        <?php  if($key>0) { ?>
                                            <div class="layui-col-xs6 del" onclick="javascript:del_detail(this);">删除</div>
                                        <?php  } ?>
                                    </div>
                                    <div class="can_add_content layui-col-xs12">
                                        <?php  if(is_array($can_add)) { foreach($can_add as $k2 => $v2) { ?>
                                            <?php  if($v2['param']==1) { ?>
                                                <div class="layui-col-xs12 content_box">
                                                    <label class="layui-form-label">
                                                        <?php  echo $v2['label_name'];?>
                                                    </label>
                                                    <div class="layui-input-block layui-select-fscon">
                                                        <input class="layui-input" placeholder="请输入<?php  echo $v2['label_name'];?>" name="add_value<?php  echo $key;?>[]" lay-verify="required" value="<?php  echo $val[$k2]['value'];?>">
                                                    </div>
                                                </div>
                                            <?php  } ?>
                                            <?php  if($v2['param']==2) { ?>
                                                <div class="layui-col-xs12 content_box">
                                                    <label class="layui-form-label">
                                                        <?php  echo $v2['label_name'];?>
                                                    </label>
                                                    <div class="layui-input-block layui-select-fscon">
                                                        <select name="add_value<?php  echo $key;?>[]" lay-verify="required">
                                                            <option value="">请选择<?php  echo $v2['label_name'];?></option>
                                                                <?php  if(is_array($v2['select_value2'])) { foreach($v2['select_value2'] as $kk => $vv) { ?>
                                                                    <option value="<?php  echo $vv;?>" <?php  if($val[$k2]['value']==$vv) { ?>selected<?php  } ?>><?php  echo $vv;?></option>
                                                                <?php  } } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            <?php  } ?>
                                            <?php  if($v2['param']==3) { ?>
                                                <div class="layui-col-xs12 content_box">
                                                    <label class="layui-form-label">
                                                        <?php  echo $v2['label_name'];?>
                                                    </label>
                                                    <div class="layui-input-block layui-select-fscon">
                                                        <div id="xm-select-demo<?php  echo $val[$k2]['random_name'];?>" class="xm-select-demo"></div>
                                                        <input type="text" name="xm_demo[<?php  echo $key;?>]" value='<?php  echo $v2["select_value2"];?>' style="display:none;">
                                                        <input type="text" name="initValue[<?php  echo $key;?>]" value='<?php  echo $val[$k2]["initValue"];?>' style="display:none;">
                                                    </div>
                                                </div>
                                            <?php  } ?>
                                            <?php  if($v2['param']==4) { ?>
                                                <div class="layui-col-xs12 content_box">
                                                    <label class="layui-form-label">
                                                        <?php  echo $v2['label_name'];?>
                                                    </label>
                                                    <div class="layui-input-block layui-select-fscon">
                                                        <input type="text" name="add_value<?php  echo $key;?>[]" class="layui-input" id="date_demo<?php  echo $val[$k2]['random_name'];?>" placeholder="请选择<?php  echo $v2['label_name'];?>"  lay-verify="required" value="<?php  echo $val[$k2]['value'];?>">
                                                    </div>
                                                </div>
                                            <?php  } ?>
                                        <?php  } } ?>
                                    </div>
                                    <input name="add_value_num" value="<?php  echo $key;?>" style="display:none;">
                                    <div class="new_add layui-col-xs12" onclick="javascript:add();">新增</div>
                                </div>

                                <?php  } } ?>
                            <?php  } ?>
                            <?php  $count += 1;?>
                        <?php  } ?>
                    <?php  } } ?>
                </div>
                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;text-align:center;">
                            <button class="layui-btn sub" lay-submit="" lay-filter="component-form-demo1">修改</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('.page_head').find('.left').click(function () {
            window.history.back(-1);
        });

        //只显示一个新增
        last_add_show();
    });

    function last_add_show(){
        let new_add_length = $('.new_add').length;
        for(let i=0;i<new_add_length;i++){
            if(i+1==new_add_length){
                $('.new_add').eq(i).show();
            }else{
                $('.new_add').eq(i).hide();
            }
        }
    }

    layui.use(['layer', 'form', 'table', 'upload', 'laydate','jquery'], function () {
        var layer = layui.layer
            , form = layui.form
            , element = layui.element
            , laydate = layui.laydate
            , upload = layui.upload
            , table = layui.table
            , $ = layui.jquery;
        
        
        <?php  $count=0;?>
        <?php  if(is_array($info['template'])) { foreach($info['template'] as $k => $v) { ?>
            <?php  if($v['can_add']==1) { ?>
                <?php  if($v['param']==3) { ?>
                    xmSelect.render({
                        el: '#xm-select-demo'+"<?php  echo $v['random_name'];?>",
                        paging: true,
                        pageSize: 5,
                        filterable: true,
                        prop: {
                            name: 'name',
                            value: 'id',
                        },
                        autoRow: true,
                        data: $.parseJSON($('input[name="xm_demo1"]').val()),
                        name: 'value[]',
                        layVerify: 'required',
                        layVerType: 'msg',
                    });
                <?php  } ?>
                <?php  if($v['param']==4) { ?>
                    laydate.render({
                        elem: '#date_demo'+"<?php  echo $v['random_name'];?>"
                        ,type: 'datetime'
                        ,format: 'yyyy-MM-dd HH:mm:ss'
                    });
                <?php  } ?>
                <?php  if($v['param']==5) { ?>
                    upload.render({
                        elem: '#file-upload'+"<?php  echo $v['random_name'];?>"
                        ,url: 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=util&m=sz_yi&p=uploader'
                        ,accept: 'file'
                        ,data: {file: "approval_file-upload",'op':'uploadDeclFile'}
                        ,multiple: true
                        ,before: function(obj){
                            layer.load(); //上传loading
                        }
                        ,done: function(res){
                            layer.closeAll('loading'); //关闭loading
                            if(res.status == 'success')
                            {
                                var length = $('#file-upload-list'+"<?php  echo $v['random_name'];?>").children().length;
                                $('#file-upload-list<?php  echo $v["random_name"];?>').append('<div style="display: inline-block;margin-top:10px;"><img onclick="seePic(this);" src="/attachment/'+ res.filename +'" class="layui-upload-img" onerror=src="https://shop.gogo198.cn/attachment/images/default_file.png"><button type="button" onclick="delPic(this);" class="layui-btn layui-btn-xs layui-btn-danger" style="position: relative;left: -20px;top: -13px;">删除</button><input type="hidden" name="list_file['+length+']" value="'+res.filename+'"></div>');
                            }else{
                                layer.msg(res.msg,{time:3000});
                            }
                        }
                    });
                <?php  } ?>
            <?php  } ?>
            <?php  if($v['can_add']==2) { ?>
                <?php  if($count==0) { ?>
                    <?php  if(is_array($content['add_value'])) { foreach($content['add_value'] as $key => $val) { ?>
                        <?php  if(is_array($can_add)) { foreach($can_add as $kk => $vv) { ?>
                            <?php  if($vv['param']==3) { ?>
                                xmSelect.render({
                                    el: '#xm-select-demo<?php  echo $val[$kk]["random_name"];?>',
                                    paging: true,
                                    pageSize: 5,
                                    filterable: true,
                                    prop: {
                                        name: 'name',
                                        value: 'id',
                                    },
                                    autoRow: true,
                                    data: $.parseJSON($('input[name="xm_demo[<?php  echo $key;?>]"]').val()),
                                    name: 'add_value<?php  echo $key;?>[]',
                                    layVerify: 'required',
                                    layVerType: 'msg',
                                    initValue:$.parseJSON($('input[name="initValue[<?php  echo $key;?>]"]').val())
                                });
                            <?php  } ?>
                            <?php  if($vv['param']==4) { ?>
                                laydate.render({
                                    elem: '#date_demo<?php  echo $val[$kk]["random_name"];?>'
                                    ,type: 'datetime'
                                    ,format: 'yyyy-MM-dd HH:mm:ss'
                                });
                            <?php  } ?>
                        <?php  } } ?>
                    <?php  } } ?>
                <?php  } ?>
                <?php  $count += 1;?>
            <?php  } ?>
        <?php  } } ?>
        
        form.render(null, 'component-form-demo1');

            /* 监听提交 */
            form.on('submit(component-form-demo1)', function(data){

                // if(data.field['form_val'][0] == '' || typeof(data.field['form_val'][0]) == 'undefined'){
                //     layer.msg('非常抱歉，您暂未有该事项的发起权限，请联系管理员添加！');
                //     return false;
                // }
                // console.log(data.field);return false;
                $.ajax({
                    url:"./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=unapproved_edit",
                    method:'post',
                    data:data.field,
                    dataType:'JSON',
                    success:function(res){
                        layer.msg(res.msg,{time:3000}, function () {
                            if(res.code == 0)
                            {
                                parent.location.reload();
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

    //新增明细
    function add(ress){
        var layer = layui.layer
            , form = layui.form
            , element = layui.element
            , laydate = layui.laydate
            , upload = layui.upload
            , table = layui.table
            , $ = layui.jquery;
        let event_id = $('input[name="event_id"]').val();

        //获取新增内容表单
        $.ajax({
            url: "./index.php?i=3&c=entry&do=approval&p=send&m=sz_yi&op=initiate_approval&pa=3",
            method: 'post',
            data: {'event_id': event_id},
            dataType: 'JSON',
            success: function (res) {
                let count = 0;
                let html = '';

                //获取当前是多少个明细
                let info_name = $('.info_name').val();
                let now_info_num = parseInt($('.info_num:last').val())+1;
                let add_value_num = $('input[name="add_value_num"]:last').val();
                add_value_num = parseInt(add_value_num) + 1;
                if(count==0){
                    html +=
                        '                            <div class="layui-col-xs12 detail_column">\n' +
                        '                                <div class="layui-col-xs6 name">\n' +
                        '                                     <input name="info_num" class="info_num" value="'+now_info_num+'" style="display:none;">\n'+
                        '                                    '+info_name+'明细 '+now_info_num+'\n' +
                        '                                </div>\n' +
                        '                                <div class="layui-col-xs6 del" onclick="javascript:del_detail(this);">删除</div>\n' +
                        '                            </div>\n'+
                        '                            <div class="can_add_content layui-col-xs12">';
                    for(var i2=0;i2<res.can_add.length;i2++){
                        if(res.can_add[i2].param==1){
                            html += '                        <div class="layui-col-xs12 content_box">\n' +
                                '                            <label class="layui-form-label">\n' +
                                res.can_add[i2].label_name+
                                '                            </label>\n' +
                                '                            <div class="layui-input-block layui-select-fscon">\n' +
                                '                                    <input class="layui-input" placeholder="请输入'+res.can_add[i2].label_name+'" name="add_value'+add_value_num+'[]">\n' +
                                '                            </div>\n' +
                                '                        </div>\n';
                        }
                        else if(res.can_add[i2].param==2){
                            html += '                        <div class="layui-col-xs12 content_box">\n' +
                                '                            <label class="layui-form-label">\n' +
                                res.can_add[i2].label_name+
                                '                            </label>\n' +
                                '                            <div class="layui-input-block layui-select-fscon">\n' +
                                '                                    <select name="add_value'+add_value_num+'[]" lay-verify="required">\n'+
                                '                                                    <option value="">请选择'+res.can_add[i2].label_name+'</option>\n';
                            for(let i3=0;i3<res.can_add[i2].select_value2.length;i3++){
                                html += '                                        <option value="'+res.can_add[i2].select_value2[i3]+'">'+res.can_add[i2].select_value2[i3]+'</option>\n';
                            }
                            html += '                                    </select>\n'+
                                '                            </div>\n' +
                                '                        </div>\n';
                        }
                        else if(res.can_add[i2].param==3){
                            html += '                        <div class="layui-col-xs12 content_box">\n' +
                                '                            <label class="layui-form-label">\n' +
                                res.can_add[i2].label_name+
                                '                            </label>\n' +
                                '                            <div class="layui-input-block layui-select-fscon">\n' +
                                '                                    <div id="xm-select-demo'+res.can_add[i2].random_name+'" class="xm-select-demo"></div>\n' +
                                '                            </div>\n' +
                                '                        </div>\n';
                        }
                        else if(res.can_add[i2].param==4){
                            html += '                        <div class="layui-col-xs12 content_box">\n' +
                                '                            <label class="layui-form-label">\n' +
                                res.can_add[i2].label_name+
                                '                            </label>\n' +
                                '                            <div class="layui-input-block layui-select-fscon">\n' +
                                '                                    <input type="text" name="add_value'+add_value_num+'[]" class="layui-input" id="date_demo'+res.can_add[i2].random_name+'" placeholder="请选择'+res.can_add[i2].label_name+'">\n' +
                                '                            </div>\n' +
                                '                        </div>\n';
                        }
                    }
                    html += '    </div>\n'+
                    '            <input name="add_value_num" value="'+add_value_num+'" style="display:none;">\n'+
                    '            <div class="new_add layui-col-xs12" onclick="javascript:add();">新增</div>\n';
                }
                count += 1;

                $('.can_add_box:last').after('<div class="can_add_box layui-col-xs12">'+html+'</div>');
                form.render(null, 'component-form-demo1');

                //已添加可新增数量
                // let add_value_num = $('input[name="add_value_num"]:last').val();
                // add_value_num = parseInt(add_value_num) + 1;
                // $('input[name="add_value_num"]:last').val(add_value_num);

                //生成各组件
                // var now_info_num2 = now_info_num = parseInt($('.info_num:first').val())+1;
                for(var i2=0;i2<res.can_add.length;i2++) {
                    if (res.can_add[i2].param == 3) {
                        let xm_data = res.can_add[i2].select_value2;
                        let xm_random = res.can_add[i2].random_name;

                        xmSelect.render({
                            el: '#xm-select-demo'+xm_random,
                            paging: true,
                            pageSize: 5,
                            filterable: true,
                            prop: {
                                name: 'name',
                                value: 'id',
                            },
                            autoRow: true,
                            data: xm_data,
                            name: 'add_value'+add_value_num+'[]',
                            layVerify: 'required',
                            layVerType: 'msg',
                        });
                        //now_info_num2 += 1;
                    }
                    else if (res.can_add[i2].param == 4) {
                        let date_random = res.can_add[i2].random_name;
                        laydate.render({
                            elem: '#date_demo'+date_random
                            ,type: 'datetime'
                            ,format: 'yyyy-MM-dd HH:mm:ss'
                        });
                    }
                }

                //只显示一个新增
                last_add_show();
            },
            error:function (data) {
                layer.msg('系统错误',{time:3000});
            }
        });
    }

    //删除明细
    function del_detail(thi){
        var layer = layui.layer,$ = layui.$;
        layer.confirm('确认要删除该明细吗？',{
            btn: ['删除','取消']
        }, function(){
            $(thi).parent().parent().remove();
            layer.closeAll('');
            //只显示一个新增
            last_add_show();
        }, function(){

        });
    }

    function delPic(obj)
    {
        var layer = layui.layer,$ = layui.$;
        layer.confirm('确认要删除该附件？', {
            btn: ['删除','取消']
        }, function(){
            $(obj).parent().remove();
            layer.closeAll();
        }, function(){

        });
    }
</script>